<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\Message;
use App\Models\Property;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        // Update property status to under_negotiation when transaction starts
        $this->updatePropertyStatus($transaction);
        
        Log::info("Transaction #{$transaction->transaction_number} created, property #{$transaction->property_id} status updated");
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        // Sync transaction status changes to linked inquiry
        if ($transaction->inquiry_id && $transaction->isDirty('status')) {
            $this->syncInquiryStatus($transaction);
        }

        // Update property status based on transaction status changes
        if ($transaction->isDirty('status')) {
            $this->updatePropertyStatus($transaction);
        }
    }

    /**
     * Sync transaction status to inquiry status
     */
    protected function syncInquiryStatus(Transaction $transaction): void
    {
        $inquiry = $transaction->inquiry;
        if (!$inquiry) {
            return;
        }

        // Map transaction status to inquiry status
        $statusMap = [
            'inquiry' => 'in_transaction',
            'initial_contact' => 'in_transaction',
            'property_viewing' => 'in_transaction',
            'offer_made' => 'in_transaction',
            'negotiation' => 'in_transaction',
            'agreement_reached' => 'in_transaction',
            'contract_signing' => 'in_transaction',
            'documentation' => 'in_transaction',
            'payment_processing' => 'in_transaction',
            'finalized' => 'completed',
            'cancelled' => 'closed',
        ];

        $newInquiryStatus = $statusMap[$transaction->status] ?? 'in_transaction';

        // Update inquiry without triggering events (prevent loop)
        $inquiry->updateQuietly([
            'status' => $newInquiryStatus,
        ]);

        // Add system message to conversation if exists
        if ($inquiry->conversation) {
            Message::create([
                'conversation_id' => $inquiry->conversation->id,
                'sender_id' => null,
                'content' => "Transaction status updated: " . str_replace('_', ' ', ucfirst($transaction->status)) . " (Transaction #{$transaction->transaction_number})",
                'is_system_message' => true,
            ]);
        }

        Log::info("Synced inquiry #{$inquiry->id} status to '{$newInquiryStatus}' from transaction #{$transaction->id} status '{$transaction->status}'");
    }

    /**
     * Update property status based on transaction status
     */
    protected function updatePropertyStatus(Transaction $transaction): void
    {
        $property = $transaction->property;
        if (!$property) {
            return;
        }

        // Map transaction status to property status
        $statusMap = [
            // Early stages: under negotiation
            'inquiry' => 'under_negotiation',
            'initial_contact' => 'under_negotiation',
            'property_viewing' => 'under_negotiation',
            'offer_made' => 'under_negotiation',
            'negotiation' => 'under_negotiation',
            
            // Agreement reached: reserved
            'offer_accepted' => 'reserved',
            'accepted' => 'reserved',
            'contract_signed' => 'reserved',
            
            // Final stages: sold
            'finalized' => 'sold',
            
            // Cancelled: back to available
            'cancelled' => 'available',
        ];

        $newPropertyStatus = $statusMap[$transaction->status] ?? 'under_negotiation';
        $oldPropertyStatus = $property->status;

        // Only update if status actually changes
        if ($oldPropertyStatus !== $newPropertyStatus) {
            $property->updateQuietly([
                'status' => $newPropertyStatus,
            ]);

            // Add system message to conversation if exists
            if ($transaction->inquiry && $transaction->inquiry->conversation) {
                Message::create([
                    'conversation_id' => $transaction->inquiry->conversation->id,
                    'sender_id' => null,
                    'content' => "🏠 Property status updated: {$oldPropertyStatus} → {$newPropertyStatus}",
                    'type' => 'system',
                    'is_system_message' => true,
                ]);
            }

            Log::info("Updated property #{$property->id} status from '{$oldPropertyStatus}' to '{$newPropertyStatus}' due to transaction #{$transaction->transaction_number} status: {$transaction->status}");
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        // If transaction is deleted, revert property status to available
        if ($transaction->property_id) {
            $property = $transaction->property;
            if ($property) {
                $property->updateQuietly([
                    'status' => 'available',
                ]);

                Log::info("Transaction #{$transaction->transaction_number} deleted, property #{$property->id} status reverted to 'available'");
            }
        }

        // If transaction is deleted, update inquiry status back
        if ($transaction->inquiry_id) {
            $inquiry = $transaction->inquiry;
            if ($inquiry) {
                $inquiry->updateQuietly([
                    'status' => 'closed',
                ]);

                Log::info("Transaction #{$transaction->transaction_number} deleted, inquiry #{$inquiry->id} status reverted to 'closed'");
            }
        }
    }
}
