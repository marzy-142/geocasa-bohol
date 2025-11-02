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
        // Sync important fields to linked transaction
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
}
