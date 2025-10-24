<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class TransactionStateMachine
{
    /**
     * Valid status transitions based on business logic
     */
    private array $validTransitions = [
        'inquiry' => ['initial_contact', 'cancelled'],
        'initial_contact' => ['property_viewing', 'cancelled'],
        'property_viewing' => ['offer_made', 'cancelled'],
        'offer_made' => ['offer_client_review', 'negotiation', 'cancelled'],
        'offer_client_review' => ['offer_accepted', 'client_rejected', 'negotiation', 'cancelled'],
        'negotiation' => ['offer_made', 'offer_client_review', 'cancelled'],
        'offer_accepted' => ['contract_review', 'cancelled'],
        'contract_review' => ['contract_signed', 'client_rejected', 'negotiation', 'cancelled'],
        'contract_signed' => ['due_diligence', 'cancelled'],
        'due_diligence' => ['financing', 'client_review_required', 'cancelled'],
        'financing' => ['closing_preparation', 'client_review_required', 'cancelled'],
        'closing_preparation' => ['client_final_approval', 'finalized', 'cancelled'],
        'client_final_approval' => ['finalized', 'client_rejected', 'cancelled'],
        'client_rejected' => ['negotiation', 'cancelled'],
        'client_review_required' => ['due_diligence', 'financing', 'closing_preparation', 'cancelled'],
        'finalized' => [], // Terminal state
        'cancelled' => [], // Terminal state
    ];

    /**
     * Statuses that require client approval before transition
     */
    private array $approvalRequiredStatuses = [
        'offer_client_review',
        'contract_review', 
        'client_final_approval',
    ];

    /**
     * Critical statuses that should never be auto-approved
     */
    private array $criticalStatuses = [
        'offer_client_review',
        'contract_review',
        'client_final_approval',
    ];

    /**
     * Check if a status transition is valid
     */
    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        $validNextStatuses = $this->validTransitions[$fromStatus] ?? [];
        return in_array($toStatus, $validNextStatuses);
    }

    /**
     * Get all valid next statuses from current status
     */
    public function getValidNextStatuses(string $currentStatus): array
    {
        return $this->validTransitions[$currentStatus] ?? [];
    }

    /**
     * Check if a status requires client approval
     */
    public function requiresClientApproval(string $status): bool
    {
        return in_array($status, $this->approvalRequiredStatuses);
    }

    /**
     * Check if a status is critical (should never be auto-approved)
     */
    public function isCriticalStatus(string $status): bool
    {
        return in_array($status, $this->criticalStatuses);
    }

    /**
     * Validate and perform status transition
     */
    public function transition(Transaction $transaction, string $newStatus, User $user, string $reason = null): array
    {
        try {
            $currentStatus = $transaction->status;
            
            // Validate transition
            if (!$this->canTransition($currentStatus, $newStatus)) {
                return [
                    'success' => false,
                    'error' => "Invalid status transition from '{$currentStatus}' to '{$newStatus}'",
                    'valid_transitions' => $this->getValidNextStatuses($currentStatus)
                ];
            }

            // Check if approval is required
            if ($this->requiresClientApproval($newStatus)) {
                return [
                    'success' => false,
                    'error' => "Status '{$newStatus}' requires client approval before transition",
                    'requires_approval' => true,
                    'approval_type' => $this->getApprovalTypeForStatus($newStatus)
                ];
            }

            // Perform the transition
            $oldStatus = $transaction->status;
            $transaction->update([
                'status' => $newStatus,
                'updated_at' => now()
            ]);

            // Log the transition
            $this->logTransition($transaction, $oldStatus, $newStatus, $user, $reason);

            return [
                'success' => true,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'transitioned_at' => now()
            ];

        } catch (Exception $e) {
            Log::error('Transaction state machine transition failed', [
                'transaction_id' => $transaction->id,
                'from_status' => $currentStatus,
                'to_status' => $newStatus,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to transition transaction status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Request client approval for a status transition
     */
    public function requestClientApproval(Transaction $transaction, string $targetStatus, User $requestedBy, array $data = []): array
    {
        try {
            if (!$this->requiresClientApproval($targetStatus)) {
                return [
                    'success' => false,
                    'error' => "Status '{$targetStatus}' does not require client approval"
                ];
            }

            $approvalService = app(ClientApprovalService::class);
            $approvalType = $this->getApprovalTypeForStatus($targetStatus);
            
            $approvalService->requestClientApproval(
                $transaction,
                $approvalType,
                array_merge($data, [
                    'target_status' => $targetStatus,
                    'requested_by' => $requestedBy->id,
                    'request_reason' => $data['reason'] ?? 'Status transition approval required'
                ]),
                $this->getApprovalDeadline($targetStatus)
            );

            return [
                'success' => true,
                'approval_type' => $approvalType,
                'target_status' => $targetStatus,
                'deadline' => now()->addDays($this->getApprovalDeadline($targetStatus))
            ];

        } catch (Exception $e) {
            Log::error('Failed to request client approval', [
                'transaction_id' => $transaction->id,
                'target_status' => $targetStatus,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to request client approval: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get approval type for a status
     */
    private function getApprovalTypeForStatus(string $status): string
    {
        return match($status) {
            'offer_client_review' => 'offer_submission',
            'contract_review' => 'contract_review',
            'client_final_approval' => 'final_approval',
            default => 'general_approval'
        };
    }

    /**
     * Get approval deadline in days for a status
     */
    private function getApprovalDeadline(string $status): int
    {
        return match($status) {
            'offer_client_review' => 3, // 3 days for offer review
            'contract_review' => 7, // 7 days for contract review
            'client_final_approval' => 2, // 2 days for final approval
            default => 5
        };
    }

    /**
     * Log status transition for audit trail
     */
    private function logTransition(Transaction $transaction, string $oldStatus, string $newStatus, User $user, string $reason = null): void
    {
        // Add to status history
        $statusHistory = $transaction->status_history ?? [];
        $statusHistory[] = [
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->name,
            'reason' => $reason,
            'timestamp' => now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];

        $transaction->update(['status_history' => $statusHistory]);

        Log::info('Transaction status transition', [
            'transaction_id' => $transaction->id,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'user_id' => $user->id,
            'user_role' => $user->role,
            'reason' => $reason
        ]);
    }

    /**
     * Get transaction flow diagram data
     */
    public function getFlowDiagram(): array
    {
        return [
            'nodes' => array_keys($this->validTransitions),
            'edges' => $this->validTransitions,
            'approval_required' => $this->approvalRequiredStatuses,
            'critical_statuses' => $this->criticalStatuses
        ];
    }

    /**
     * Validate current transaction state
     */
    public function validateTransactionState(Transaction $transaction): array
    {
        $issues = [];
        
        // Check if current status is valid
        if (!array_key_exists($transaction->status, $this->validTransitions)) {
            $issues[] = "Invalid current status: {$transaction->status}";
        }

        // Check for pending approvals on critical statuses
        if ($this->requiresClientApproval($transaction->status)) {
            $pendingApprovals = array_filter(
                $transaction->client_approvals ?? [],
                fn($approval) => $approval['status'] === 'pending'
            );
            
            if (empty($pendingApprovals)) {
                $issues[] = "Status '{$transaction->status}' requires client approval but none are pending";
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }
}
