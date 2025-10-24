<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Property;
use App\Models\Client;
use App\Models\User;
use App\Events\TransactionCompleted;
use App\Notifications\TransactionCompletedNotification;
use App\Notifications\PropertySoldNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TransactionCompletionService
{
    /**
     * Complete a finalized transaction
     */
    public function completeTransaction(Transaction $transaction): array
    {
        try {
            DB::beginTransaction();

            $results = [
                'property_updated' => false,
                'client_updated' => false,
                'broker_updated' => false,
                'reports_generated' => false,
                'notifications_sent' => false,
            ];

            // 1. Update property status to sold
            $results['property_updated'] = $this->updatePropertyStatus($transaction);
            
            // 2. Update client status to converted
            $results['client_updated'] = $this->updateClientStatus($transaction);
            
            // 3. Update broker statistics
            $results['broker_updated'] = $this->updateBrokerStatistics($transaction);
            
            // 4. Generate sales reports
            $results['reports_generated'] = $this->generateSalesReports($transaction);
            
            // 5. Send completion notifications
            $results['notifications_sent'] = $this->sendCompletionNotifications($transaction);
            
            // 6. Fire completion event
            event(new TransactionCompleted($transaction));

            DB::commit();

            Log::info('Transaction completed successfully', [
                'transaction_id' => $transaction->id,
                'property_id' => $transaction->property_id,
                'client_id' => $transaction->client_id,
                'broker_id' => $transaction->broker_id,
                'final_price' => $transaction->final_price,
                'commission_amount' => $transaction->commission_amount,
            ]);

            return [
                'success' => true,
                'message' => 'Transaction completed successfully',
                'results' => $results,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Transaction completion failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Transaction completion failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update property status to sold
     */
    private function updatePropertyStatus(Transaction $transaction): bool
    {
        try {
            $property = $transaction->property;
            
            if ($property && $property->status !== 'sold') {
                $property->update([
                    'status' => 'sold',
                    'sold_at' => now(),
                    'sold_price' => $transaction->final_price ?? $transaction->offered_price,
                    'sold_to_client_id' => $transaction->client_id,
                    'sold_via_transaction_id' => $transaction->id,
                ]);

                Log::info('Property status updated to sold', [
                    'property_id' => $property->id,
                    'transaction_id' => $transaction->id,
                    'sold_price' => $transaction->final_price ?? $transaction->offered_price,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to update property status', [
                'property_id' => $transaction->property_id,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Update client status to converted
     */
    private function updateClientStatus(Transaction $transaction): bool
    {
        try {
            $client = $transaction->client;
            
            if ($client && $client->status !== 'converted') {
                $client->update([
                    'status' => 'converted',
                    'converted_at' => now(),
                    'converted_via_transaction_id' => $transaction->id,
                    'notes' => ($client->notes ?? '') . "\n\n" . now()->format('Y-m-d H:i') . " - Client converted to buyer via Transaction #" . $transaction->transaction_number,
                ]);

                Log::info('Client status updated to converted', [
                    'client_id' => $client->id,
                    'transaction_id' => $transaction->id,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to update client status', [
                'client_id' => $transaction->client_id,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Update broker statistics
     */
    private function updateBrokerStatistics(Transaction $transaction): bool
    {
        try {
            $broker = $transaction->broker;
            
            if ($broker) {
                // Update broker's finalized transactions count
                $broker->increment('finalized_transactions_count');
                
                // Update total commission earned
                $broker->increment('total_commission_earned', $transaction->commission_amount ?? 0);
                
                // Update last sale date
                $broker->update(['last_sale_date' => now()]);

                Log::info('Broker statistics updated', [
                    'broker_id' => $broker->id,
                    'transaction_id' => $transaction->id,
                    'commission_amount' => $transaction->commission_amount ?? 0,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to update broker statistics', [
                'broker_id' => $transaction->broker_id,
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate sales reports
     */
    private function generateSalesReports(Transaction $transaction): bool
    {
        try {
            // Create sales record
            $salesReport = [
                'transaction_id' => $transaction->id,
                'property_id' => $transaction->property_id,
                'client_id' => $transaction->client_id,
                'broker_id' => $transaction->broker_id,
                'sale_date' => now(),
                'sale_price' => $transaction->final_price ?? $transaction->offered_price,
                'commission_rate' => $transaction->commission_rate,
                'commission_amount' => $transaction->commission_amount,
                'property_type' => $transaction->property->type ?? 'unknown',
                'property_location' => $transaction->property->municipality ?? 'unknown',
                'transaction_duration_days' => $transaction->created_at->diffInDays(now()),
            ];

            // Store in sales reports (you might want to create a dedicated table for this)
            Log::info('Sales report generated', $salesReport);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to generate sales reports', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send completion notifications
     */
    private function sendCompletionNotifications(Transaction $transaction): bool
    {
        try {
            // Notify broker
            $transaction->broker->notify(new TransactionCompletedNotification($transaction));
            
            // Notify client
            if ($transaction->client->user) {
                $transaction->client->user->notify(new TransactionCompletedNotification($transaction));
            }
            
            // Notify admin
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $admin->notify(new PropertySoldNotification($transaction));
            }

            Log::info('Completion notifications sent', [
                'transaction_id' => $transaction->id,
                'broker_id' => $transaction->broker_id,
                'client_id' => $transaction->client_id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send completion notifications', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if transaction can be completed
     */
    public function canComplete(Transaction $transaction): bool
    {
        return $transaction->status === 'finalized' && 
               $transaction->property && 
               $transaction->client && 
               $transaction->broker;
    }

    /**
     * Get completion summary
     */
    public function getCompletionSummary(Transaction $transaction): array
    {
        return [
            'transaction_id' => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'property_title' => $transaction->property->title ?? 'Unknown Property',
            'client_name' => $transaction->client->name ?? 'Unknown Client',
            'broker_name' => $transaction->broker->name ?? 'Unknown Broker',
            'final_price' => $transaction->final_price ?? $transaction->offered_price,
            'commission_amount' => $transaction->commission_amount,
            'completion_date' => now()->format('Y-m-d H:i:s'),
            'can_complete' => $this->canComplete($transaction),
        ];
    }
}
