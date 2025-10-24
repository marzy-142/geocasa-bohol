<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\TransactionAuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionAuditService
{
    /**
     * Log a transaction status change
     */
    public function logStatusChange(
        Transaction $transaction, 
        string $oldStatus, 
        string $newStatus, 
        User $user, 
        string $reason = null
    ): void {
        try {
            TransactionAuditLog::create([
                'transaction_id' => $transaction->id,
                'action' => 'status_change',
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_name' => $user->name,
                'reason' => $reason,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'transaction_number' => $transaction->transaction_number,
                    'property_id' => $transaction->property_id,
                    'client_id' => $transaction->client_id,
                    'broker_id' => $transaction->broker_id,
                ],
                'created_at' => now(),
            ]);

            Log::info('Transaction status change logged', [
                'transaction_id' => $transaction->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'user_id' => $user->id,
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log transaction status change', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log a transaction field update
     */
    public function logFieldUpdate(
        Transaction $transaction,
        string $fieldName,
        mixed $oldValue,
        mixed $newValue,
        User $user,
        string $reason = null
    ): void {
        try {
            TransactionAuditLog::create([
                'transaction_id' => $transaction->id,
                'action' => 'field_update',
                'field_name' => $fieldName,
                'old_value' => $this->serializeValue($oldValue),
                'new_value' => $this->serializeValue($newValue),
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_name' => $user->name,
                'reason' => $reason,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'transaction_number' => $transaction->transaction_number,
                    'field_type' => $this->getFieldType($fieldName),
                ],
                'created_at' => now(),
            ]);

            Log::info('Transaction field update logged', [
                'transaction_id' => $transaction->id,
                'field' => $fieldName,
                'old_value' => $this->serializeValue($oldValue),
                'new_value' => $this->serializeValue($newValue),
                'user_id' => $user->id,
                'reason' => $reason
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log transaction field update', [
                'transaction_id' => $transaction->id,
                'field' => $fieldName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log client approval request
     */
    public function logApprovalRequest(
        Transaction $transaction,
        array $approval,
        User $requestedBy
    ): void {
        try {
            TransactionAuditLog::create([
                'transaction_id' => $transaction->id,
                'action' => 'approval_requested',
                'new_value' => json_encode($approval),
                'user_id' => $requestedBy->id,
                'user_role' => $requestedBy->role,
                'user_name' => $requestedBy->name,
                'reason' => 'Client approval requested',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'transaction_number' => $transaction->transaction_number,
                    'approval_id' => $approval['id'],
                    'approval_type' => $approval['type'],
                    'deadline' => $approval['deadline'],
                    'client_id' => $transaction->client_id,
                ],
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log approval request', [
                'transaction_id' => $transaction->id,
                'approval_id' => $approval['id'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log client approval response
     */
    public function logApprovalResponse(
        Transaction $transaction,
        string $approvalId,
        bool $approved,
        string $clientNotes = null,
        User $client = null
    ): void {
        try {
            TransactionAuditLog::create([
                'transaction_id' => $transaction->id,
                'action' => 'approval_responded',
                'new_value' => json_encode([
                    'approval_id' => $approvalId,
                    'approved' => $approved,
                    'client_notes' => $clientNotes,
                    'responded_at' => now()->toISOString(),
                ]),
                'user_id' => $client?->id,
                'user_role' => $client?->role ?? 'client',
                'user_name' => $client?->name ?? 'Client',
                'reason' => $approved ? 'Client approved' : 'Client rejected',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'transaction_number' => $transaction->transaction_number,
                    'approval_id' => $approvalId,
                    'approved' => $approved,
                    'client_id' => $transaction->client_id,
                    'broker_id' => $transaction->broker_id,
                ],
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log approval response', [
                'transaction_id' => $transaction->id,
                'approval_id' => $approvalId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log document upload
     */
    public function logDocumentUpload(
        Transaction $transaction,
        string $documentType,
        string $fileName,
        User $uploadedBy
    ): void {
        try {
            TransactionAuditLog::create([
                'transaction_id' => $transaction->id,
                'action' => 'document_uploaded',
                'new_value' => json_encode([
                    'document_type' => $documentType,
                    'file_name' => $fileName,
                    'uploaded_at' => now()->toISOString(),
                ]),
                'user_id' => $uploadedBy->id,
                'user_role' => $uploadedBy->role,
                'user_name' => $uploadedBy->name,
                'reason' => 'Document uploaded',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'transaction_number' => $transaction->transaction_number,
                    'document_type' => $documentType,
                    'file_name' => $fileName,
                ],
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log document upload', [
                'transaction_id' => $transaction->id,
                'document_type' => $documentType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get audit trail for a transaction
     */
    public function getAuditTrail(Transaction $transaction, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return TransactionAuditLog::where('transaction_id', $transaction->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit trail summary for a transaction
     */
    public function getAuditSummary(Transaction $transaction): array
    {
        $auditLogs = $this->getAuditTrail($transaction);
        
        $summary = [
            'total_actions' => $auditLogs->count(),
            'status_changes' => $auditLogs->where('action', 'status_change')->count(),
            'field_updates' => $auditLogs->where('action', 'field_update')->count(),
            'approval_requests' => $auditLogs->where('action', 'approval_requested')->count(),
            'approval_responses' => $auditLogs->where('action', 'approval_responded')->count(),
            'document_uploads' => $auditLogs->where('action', 'document_uploaded')->count(),
            'last_activity' => $auditLogs->first()?->created_at,
            'participants' => $auditLogs->groupBy('user_id')->map(function ($logs) {
                $firstLog = $logs->first();
                return [
                    'user_id' => $firstLog->user_id,
                    'user_name' => $firstLog->user_name,
                    'user_role' => $firstLog->user_role,
                    'action_count' => $logs->count(),
                    'first_activity' => $logs->last()?->created_at,
                    'last_activity' => $logs->first()?->created_at,
                ];
            })->values()->toArray(),
        ];

        return $summary;
    }

    /**
     * Serialize value for storage
     */
    private function serializeValue(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        
        return (string) $value;
    }

    /**
     * Get field type for metadata
     */
    private function getFieldType(string $fieldName): string
    {
        $fieldTypes = [
            'offered_price' => 'decimal',
            'final_price' => 'decimal',
            'commission_rate' => 'decimal',
            'commission_amount' => 'decimal',
            'status' => 'enum',
            'broker_notes' => 'text',
            'client_notes' => 'text',
            'client_approvals' => 'json',
            'client_documents' => 'json',
            'documents' => 'json',
        ];

        return $fieldTypes[$fieldName] ?? 'string';
    }
}
