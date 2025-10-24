<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Client;
use App\Models\ClientTransactionEngagement;
use App\Notifications\ClientApprovalRequiredNotification;
use App\Notifications\ClientApprovalResponseNotification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClientApprovalService
{
    /**
     * Request client approval for a specific action
     */
    public function requestClientApproval(
        Transaction $transaction, 
        string $approvalType, 
        array $data,
        int $responseDays = 3
    ): void {
        try {
            $approval = [
                'id' => uniqid('approval_'),
                'type' => $approvalType,
                'data' => $data,
                'requested_at' => now()->toISOString(),
                'status' => 'pending',
                'deadline' => now()->addDays($responseDays)->toISOString(),
                'requested_by' => auth()->id() ?? null,
            ];
            
            $approvals = $transaction->client_approvals ?? [];
            $approvals[] = $approval;
            
            $transaction->update([
                'status' => 'client_approval_pending',
                'client_approvals' => $approvals,
                'requires_client_action' => true,
                'client_action_deadline' => now()->addDays($responseDays),
            ]);
            
            // Notify client
            $this->notifyClient($transaction->client, $approval, $transaction);
            
            // Create workflow for follow-up
            $this->createApprovalWorkflow($transaction, $approval);
            
            // Record engagement
            $this->recordApprovalRequest($transaction);
            
            Log::info('Client approval requested', [
                'transaction_id' => $transaction->id,
                'approval_type' => $approvalType,
                'client_id' => $transaction->client_id,
                'deadline' => $approval['deadline'],
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to request client approval', [
                'transaction_id' => $transaction->id,
                'approval_type' => $approvalType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Process client approval response
     */
    public function processClientApproval(
        Transaction $transaction, 
        string $approvalId, 
        bool $approved, 
        string $notes = null,
        int $clientId = null
    ): void {
        try {
            $clientId = $clientId ?? $transaction->client_id;
            $approvals = $transaction->client_approvals ?? [];
            $approvalFound = false;
            
            foreach ($approvals as &$approval) {
                if ($approval['id'] === $approvalId) {
                    $approval['status'] = $approved ? 'approved' : 'rejected';
                    $approval['responded_at'] = now()->toISOString();
                    $approval['client_notes'] = $notes;
                    $approvalFound = true;
                    break;
                }
            }
            
            if (!$approvalFound) {
                throw new \Exception("Approval with ID {$approvalId} not found");
            }
            
            // Update transaction
            $transaction->update([
                'client_approvals' => $approvals,
                'requires_client_action' => $this->hasPendingApprovals($approvals),
                'client_action_deadline' => $this->hasPendingApprovals($approvals) ? 
                    $transaction->client_action_deadline : null,
            ]);
            
            // Move to next status based on approval
            $this->moveToNextStatus($transaction, $approvalId, $approved);
            
            // Notify broker
            $this->notifyBroker($transaction, $approvalId, $approved, $notes);
            
            // Record engagement
            $this->recordApprovalResponse($transaction, $approved);
            
            Log::info('Client approval processed', [
                'transaction_id' => $transaction->id,
                'approval_id' => $approvalId,
                'approved' => $approved,
                'client_id' => $clientId,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to process client approval', [
                'transaction_id' => $transaction->id,
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    
    /**
     * Get pending approvals for a client
     */
    public function getPendingApprovals(Client $client): array
    {
        return Transaction::where('client_id', $client->id)
            ->where('requires_client_action', true)
            ->where('status', 'client_approval_pending')
            ->with(['property:id,title', 'broker:id,name'])
            ->get()
            ->map(function ($transaction) {
                $approvals = $transaction->client_approvals ?? [];
                $pendingApprovals = array_filter($approvals, fn($approval) => $approval['status'] === 'pending');
                
                return [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'property_title' => $transaction->property->title,
                    'broker_name' => $transaction->broker->name,
                    'approvals' => array_values($pendingApprovals),
                    'deadline' => $transaction->client_action_deadline,
                    'days_remaining' => $transaction->client_action_deadline ? 
                        now()->diffInDays($transaction->client_action_deadline, false) : null,
                    'is_overdue' => $transaction->client_action_deadline ? 
                        $transaction->client_action_deadline->isPast() : false,
                ];
            })
            ->toArray();
    }
    
    /**
     * Check if approval is overdue and handle escalation
     */
    public function handleOverdueApprovals(): array
    {
        $overdueTransactions = Transaction::where('requires_client_action', true)
            ->where('client_action_deadline', '<', now())
            ->where('status', 'client_approval_pending')
            ->with(['client', 'broker', 'property'])
            ->get();
            
        $results = [
            'processed' => 0,
            'escalated' => 0,
            'reminded' => 0,
        ];
        
        foreach ($overdueTransactions as $transaction) {
            try {
                $this->handleOverdueApproval($transaction);
                $results['processed']++;
            } catch (\Exception $e) {
                Log::error('Failed to handle overdue approval', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return $results;
    }
    
    /**
     * Handle individual overdue approval
     */
    private function handleOverdueApproval(Transaction $transaction): void
    {
        $approvals = $transaction->client_approvals ?? [];
        $overdueApprovals = array_filter($approvals, function ($approval) {
            return $approval['status'] === 'pending' && 
                   Carbon::parse($approval['deadline'])->isPast();
        });
        
        if (empty($overdueApprovals)) {
            return;
        }
        
        // Send reminder notification
        $this->sendOverdueReminder($transaction, $overdueApprovals);
        
        // Auto-approve or escalate based on approval type
        foreach ($overdueApprovals as $approval) {
            $this->handleOverdueApprovalType($transaction, $approval);
        }
    }
    
    /**
     * Handle overdue approvals with escalation instead of auto-approval
     */
    private function handleOverdueApprovalType(Transaction $transaction, array $approval): void
    {
        $stateMachine = app(\App\Services\TransactionStateMachine::class);
        
        // Check if this is a critical approval that should never be auto-approved
        if ($stateMachine->isCriticalStatus($transaction->status)) {
            // Escalate to broker first
            $this->escalateToBroker($transaction, $approval);
            return;
        }

        switch ($approval['type']) {
            case 'offer_submission':
                // Escalate offer submission instead of auto-approving
                $this->escalateOfferSubmission($transaction, $approval);
                break;
                
            case 'contract_review':
                // Escalate contract review
                $this->escalateContractReview($transaction, $approval);
                break;
                
            case 'final_approval':
                // Escalate final approval - never auto-approve
                $this->escalateFinalApproval($transaction, $approval);
                break;
                
            default:
                // Send final reminder and escalate
                $this->sendFinalReminder($transaction, $approval);
                $this->escalateToBroker($transaction, $approval);
                break;
        }
    }
    
    /**
     * Notify client about approval request
     */
    private function notifyClient(Client $client, array $approval, Transaction $transaction): void
    {
        try {
            if ($client->user_id) {
                $user = \App\Models\User::find($client->user_id);
                if ($user) {
                    $user->notify(new ClientApprovalRequiredNotification($approval, $transaction));
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify client about approval', [
                'client_id' => $client->id,
                'approval_id' => $approval['id'],
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Notify broker about approval response
     */
    private function notifyBroker(Transaction $transaction, string $approvalId, bool $approved, string $notes = null): void
    {
        try {
            $broker = $transaction->broker;
            if ($broker) {
                $broker->notify(new ClientApprovalResponseNotification($transaction, $approvalId, $approved, $notes));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify broker about approval response', [
                'transaction_id' => $transaction->id,
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Create approval workflow for follow-up
     */
    private function createApprovalWorkflow(Transaction $transaction, array $approval): void
    {
        try {
            // This will integrate with the CommunicationWorkflowService
            $workflowService = app(\App\Services\CommunicationWorkflowService::class);
            
            // Create a reminder workflow for the approval deadline
            // Note: This method will be implemented in the CommunicationWorkflowService
            // $workflowService->createApprovalReminderWorkflow($transaction, $approval);
            
        } catch (\Exception $e) {
            Log::error('Failed to create approval workflow', [
                'transaction_id' => $transaction->id,
                'approval_id' => $approval['id'],
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Move transaction to next status based on approval
     */
    private function moveToNextStatus(Transaction $transaction, string $approvalId, bool $approved): void
    {
        $approval = $this->findApprovalById($transaction, $approvalId);
        
        if (!$approval) {
            return;
        }
        
        $nextStatus = $this->getNextStatusFromApproval($approval, $approved);
        
        if ($nextStatus && $nextStatus !== $transaction->status) {
            $transaction->update(['status' => $nextStatus]);
            
            // Fire status update event
            event(new \App\Events\TransactionStatusUpdated($transaction, $transaction->getOriginal('status'), $nextStatus));
        }
    }
    
    /**
     * Find approval by ID
     */
    private function findApprovalById(Transaction $transaction, string $approvalId): ?array
    {
        $approvals = $transaction->client_approvals ?? [];
        
        foreach ($approvals as $approval) {
            if ($approval['id'] === $approvalId) {
                return $approval;
            }
        }
        
        return null;
    }
    
    /**
     * Get next status from approval
     */
    private function getNextStatusFromApproval(array $approval, bool $approved): ?string
    {
        if (!$approved) {
            return 'client_rejected';
        }
        
        return match ($approval['type']) {
            'offer_submission' => 'offer_made',
            'contract_review' => 'contract_signed',
            'price_negotiation' => 'negotiation',
            'property_viewing' => 'property_viewing',
            'final_approval' => 'finalized',
            default => null,
        };
    }
    
    /**
     * Check if there are pending approvals
     */
    private function hasPendingApprovals(array $approvals): bool
    {
        return !empty(array_filter($approvals, fn($approval) => $approval['status'] === 'pending'));
    }
    
    /**
     * Record approval request in engagement
     */
    private function recordApprovalRequest(Transaction $transaction): void
    {
        $engagement = ClientTransactionEngagement::where('transaction_id', $transaction->id)
            ->where('client_id', $transaction->client_id)
            ->first();
            
        if ($engagement) {
            $engagement->recordInteraction('approval_requested', [
                'transaction_id' => $transaction->id,
                'requires_action' => true,
            ]);
        }
    }
    
    /**
     * Record approval response in engagement
     */
    private function recordApprovalResponse(Transaction $transaction, bool $approved): void
    {
        $engagement = ClientTransactionEngagement::where('transaction_id', $transaction->id)
            ->where('client_id', $transaction->client_id)
            ->first();
            
        if ($engagement) {
            $engagement->recordInteraction('approval_response', [
                'transaction_id' => $transaction->id,
                'approved' => $approved,
                'requires_action' => false,
            ]);
        }
    }
    
    /**
     * Send overdue reminder
     */
    private function sendOverdueReminder(Transaction $transaction, array $overdueApprovals): void
    {
        // Implementation for sending overdue reminders
        Log::info('Sending overdue approval reminder', [
            'transaction_id' => $transaction->id,
            'overdue_count' => count($overdueApprovals),
        ]);
    }
    
    /**
     * Send final reminder
     */
    private function sendFinalReminder(Transaction $transaction, array $approval): void
    {
        // Implementation for sending final reminder
        Log::info('Sending final approval reminder', [
            'transaction_id' => $transaction->id,
            'approval_id' => $approval['id'],
        ]);
    }
    
    /**
     * Escalate contract review
     */
    private function escalateContractReview(Transaction $transaction, array $approval): void
    {
        // Implementation for escalating contract review
        Log::info('Escalating contract review', [
            'transaction_id' => $transaction->id,
            'approval_id' => $approval['id'],
        ]);
    }

    /**
     * Escalate offer submission to broker
     */
    private function escalateOfferSubmission(Transaction $transaction, array $approval): void
    {
        try {
            $broker = $transaction->broker;
            
            if ($broker) {
                // Notify broker about overdue offer approval
                $broker->notify(new \App\Notifications\OverdueApprovalNotification(
                    $transaction,
                    $approval,
                    'offer_submission_overdue'
                ));
                
                Log::info('Escalated overdue offer submission to broker', [
                    'transaction_id' => $transaction->id,
                    'approval_id' => $approval['id'],
                    'broker_id' => $broker->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to escalate offer submission', [
                'transaction_id' => $transaction->id,
                'approval_id' => $approval['id'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Escalate final approval to broker and admin
     */
    private function escalateFinalApproval(Transaction $transaction, array $approval): void
    {
        try {
            $broker = $transaction->broker;
            
            if ($broker) {
                // Notify broker about overdue final approval
                $broker->notify(new \App\Notifications\OverdueApprovalNotification(
                    $transaction,
                    $approval,
                    'final_approval_overdue'
                ));
            }
            
            // Also notify admins about critical overdue approval
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\CriticalOverdueApprovalNotification(
                    $transaction,
                    $approval,
                    'final_approval_critical'
                ));
            }
            
            Log::info('Escalated overdue final approval to broker and admins', [
                'transaction_id' => $transaction->id,
                'approval_id' => $approval['id'],
                'broker_id' => $broker?->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to escalate final approval', [
                'transaction_id' => $transaction->id,
                'approval_id' => $approval['id'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Escalate to broker for general overdue approvals
     */
    private function escalateToBroker(Transaction $transaction, array $approval): void
    {
        try {
            $broker = $transaction->broker;
            
            if ($broker) {
                $broker->notify(new \App\Notifications\OverdueApprovalNotification(
                    $transaction,
                    $approval,
                    'general_overdue'
                ));
                
                Log::info('Escalated overdue approval to broker', [
                    'transaction_id' => $transaction->id,
                    'approval_id' => $approval['id'],
                    'broker_id' => $broker->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to escalate to broker', [
                'transaction_id' => $transaction->id,
                'approval_id' => $approval['id'],
                'error' => $e->getMessage()
            ]);
        }
    }
}
