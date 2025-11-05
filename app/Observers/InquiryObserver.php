<?php

namespace App\Observers;

use App\Models\Inquiry;
use Illuminate\Support\Facades\Log;

class InquiryObserver
{
    /**
     * Handle the Inquiry "updated" event.
     */
    public function updated(Inquiry $inquiry): void
    {
        // 1) Auto-create a Transaction when an inquiry is marked as completed with outcome 'won'
        // This makes the system resilient regardless of which controller path was used.
        try {
            if (
                // Newly set or currently set to 'completed'
                $inquiry->status === 'completed' &&
                // Outcome explicitly marked as won
                $inquiry->completion_outcome === 'won' &&
                // No transaction exists yet
                !$inquiry->transaction
            ) {
                $this->createTransactionFromInquiry($inquiry);
            }
        } catch (\Throwable $e) {
            Log::error('InquiryObserver failed to auto-create transaction', [
                'inquiry_id' => $inquiry->id,
                'error' => $e->getMessage(),
            ]);
        }

        // 2) Sync important fields to linked transaction when they change
        if ($inquiry->transaction && $this->hasRelevantChanges($inquiry)) {
            $this->syncToTransaction($inquiry);
        }
    }

    /**
     * Check if inquiry has changes relevant to transaction
     */
    protected function hasRelevantChanges(Inquiry $inquiry): bool
    {
        return $inquiry->isDirty(['scheduled_at', 'broker_notes', 'completion_notes', 'completion_outcome']);
    }

    /**
     * Sync inquiry fields to transaction
     */
    protected function syncToTransaction(Inquiry $inquiry): void
    {
        $transaction = $inquiry->transaction;
        $updates = [];

        // Sync viewing date
        if ($inquiry->isDirty('scheduled_at') && $inquiry->scheduled_at) {
            $updates['viewing_date'] = $inquiry->scheduled_at;
        }

        // Append new broker notes (don't overwrite existing transaction notes)
        if ($inquiry->isDirty('broker_notes') && $inquiry->broker_notes) {
            $existingNotes = $transaction->broker_notes ?? '';
            $newNote = "\n\n[Updated from inquiry - " . now()->format('M d, Y g:i A') . "]\n" . $inquiry->broker_notes;
            
            // Only append if this exact note doesn't already exist
            if (strpos($existingNotes, $inquiry->broker_notes) === false) {
                $updates['broker_notes'] = $existingNotes . $newNote;
            }
        }

        // Append completion notes
        if ($inquiry->isDirty('completion_notes') && $inquiry->completion_notes) {
            $existingNotes = $transaction->broker_notes ?? '';
            $completionNote = "\n\n[Inquiry Completion Notes - " . now()->format('M d, Y g:i A') . "]\n" . $inquiry->completion_notes;
            
            if (strpos($existingNotes, $inquiry->completion_notes) === false) {
                $updates['broker_notes'] = ($updates['broker_notes'] ?? $existingNotes) . $completionNote;
            }
        }

        // Update transaction without triggering events
        if (!empty($updates)) {
            $transaction->updateQuietly($updates);
            
            Log::info("Synced inquiry #{$inquiry->id} changes to transaction #{$transaction->id}", [
                'updated_fields' => array_keys($updates)
            ]);
        }
    }

    /**
     * Create a transaction from a completed/won inquiry.
     * Mirrors controller logic in a framework-agnostic place so all paths converge.
     */
    protected function createTransactionFromInquiry(Inquiry $inquiry): void
    {
        // Double-check no race condition
        if ($inquiry->transaction) {
            return;
        }

        $property = $inquiry->property; // may be null in edge cases
        $brokerId = $inquiry->assigned_broker_id ?? ($property?->broker_id);

        // Minimal required safety checks
        if (!$property || !$brokerId) {
            Log::warning('Skipping auto-create transaction due to missing associations', [
                'inquiry_id' => $inquiry->id,
                'has_property' => (bool) $property,
                'broker_id' => $brokerId,
            ]);
            return;
        }

        // Build a basic transaction payload
        $payload = [
            'inquiry_id' => $inquiry->id,
            'property_id' => $property->id,
            'client_id' => $inquiry->client_id,
            'broker_id' => $brokerId,
            'status' => 'offer_made',
            'transaction_number' => 'TXN-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'offered_price' => $property->total_price ?? 0,
            'inquiry_date' => $inquiry->created_at,
            'first_contact_date' => $inquiry->contacted_at,
            'viewing_date' => $inquiry->scheduled_at,
            'offer_date' => now(),
            'broker_notes' => "=== AUTO-CREATED FROM INQUIRY #{$inquiry->id} ===\n\n" .
                ($inquiry->broker_notes ? ("Broker Notes:\n{$inquiry->broker_notes}\n\n") : '') .
                ($inquiry->completion_notes ? ("Completion Notes:\n{$inquiry->completion_notes}\n\n") : '') .
                "Timeline:\n- Inquiry received: " . $inquiry->created_at->format('M d, Y g:i A') .
                ($inquiry->contacted_at ? "\n- First contact: " . $inquiry->contacted_at->format('M d, Y g:i A') : '') .
                ($inquiry->responded_at ? "\n- Broker responded: " . $inquiry->responded_at->format('M d, Y g:i A') : '') .
                "\n- Marked as won: " . now()->format('M d, Y g:i A'),
        ];

        \DB::transaction(function () use ($inquiry, $property, $payload) {
            // Create the transaction
            /** @var \App\Models\Transaction $transaction */
            $transaction = \App\Models\Transaction::create($payload);

            // Reflect inquiry state
            $inquiry->updateQuietly(['status' => 'in_transaction']);

            // Optionally reflect property state for clearer public signals
            // Use a conservative status that still displays availability but flags transaction state
            if (in_array($property->status, ['available'], true)) {
                $property->updateQuietly(['status' => 'under_negotiation']);
            }

            // Link any existing conversation to this transaction and notify via system message
            if ($inquiry->conversation) {
                $inquiry->conversation->updateQuietly(['transaction_id' => $transaction->id]);
                \App\Models\Message::create([
                    'conversation_id' => $inquiry->conversation->id,
                    'sender_id' => null,
                    'content' => "🎉 Transaction {$transaction->transaction_number} has been created automatically. You're now in the offer stage.",
                    'is_system_message' => true,
                ]);
            }

            // Broadcast an event for real-time dashboards if available
            try {
                event(new \App\Events\TransactionCreated($transaction));
            } catch (\Throwable $e) {
                // Non-fatal
                Log::warning('Failed to broadcast TransactionCreated from observer', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}
