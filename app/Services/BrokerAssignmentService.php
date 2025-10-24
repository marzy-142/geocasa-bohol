<?php

namespace App\Services;

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BrokerAssignmentService
{
    /**
     * Check if a broker is available and not overloaded
     */
    protected function isBrokerAvailable(User $broker): bool
    {
        // Check if broker is active and approved
        if (!$broker->is_active || $broker->status !== 'approved') {
            return false;
        }

        // Check availability score (recent activity)
        $availabilityScore = $this->calculateAvailabilityScore($broker);
        if ($availabilityScore < 50) { // Less than 50% availability
            return false;
        }

        // Check workload - don't assign if severely overloaded
        $workloadScore = $this->calculateWorkloadScore($broker);
        if ($workloadScore < 20) { // Less than 20% capacity remaining
            return false;
        }

        return true;
    }

    /**
     * Automatically assign a broker to an inquiry based on workload and performance
     * Note: This preserves the original property broker ownership
     */
    public function assignBrokerToInquiry(Inquiry $inquiry): ?User
    {
        try {
            DB::beginTransaction();

            // If property already has a broker (original listing broker), prioritize them
            if ($inquiry->property && $inquiry->property->broker_id) {
                $originalBroker = User::find($inquiry->property->broker_id);
                
                // Check if original broker is available and not overloaded
                if ($originalBroker && $this->isBrokerAvailable($originalBroker)) {
                    // Assign the inquiry to the original broker without changing property ownership
                    $inquiry->update(['assigned_broker_id' => $originalBroker->id]);
                    
                    Log::info('Inquiry assigned to original property broker', [
                        'inquiry_id' => $inquiry->id,
                        'broker_id' => $originalBroker->id,
                        'assignment_method' => 'original_broker_priority'
                    ]);

                    DB::commit();
                    return $originalBroker;
                }
            }

            // If original broker is unavailable or overloaded, find alternative
            $broker = $this->findBestBrokerForInquiry($inquiry);
            
            if (!$broker) {
                Log::warning('No suitable broker found for inquiry', ['inquiry_id' => $inquiry->id]);
                DB::rollBack();
                return null;
            }

            // IMPORTANT: Assign broker to inquiry only - never change property ownership
            // Property ownership remains with the original listing broker
            $inquiry->update(['assigned_broker_id' => $broker->id]);
            
            Log::info('Broker assigned to inquiry (alternative to original)', [
                'inquiry_id' => $inquiry->id,
                'broker_id' => $broker->id,
                'original_property_broker' => $inquiry->property?->broker_id,
                'assignment_method' => 'alternative_broker'
            ]);

            DB::commit();
            return $broker;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign broker to inquiry', [
                'inquiry_id' => $inquiry->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Find the best broker for an inquiry based on multiple criteria
     */
    protected function findBestBrokerForInquiry(Inquiry $inquiry): ?User
    {
        $brokers = User::where('role', 'broker')
            ->where('status', 'approved')
            ->where('is_active', true)
            ->get();

        if ($brokers->isEmpty()) {
            return null;
        }

        $scoredBrokers = $brokers->map(function ($broker) use ($inquiry) {
            return [
                'broker' => $broker,
                'score' => $this->calculateBrokerScore($broker, $inquiry)
            ];
        })->sortByDesc('score');

        return $scoredBrokers->first()['broker'] ?? null;
    }

    /**
     * Calculate broker assignment score based on workload, performance, and location
     */
    protected function calculateBrokerScore(User $broker, Inquiry $inquiry): float
    {
        $workloadScore = $this->calculateWorkloadScore($broker);
        $performanceScore = $this->calculatePerformanceScore($broker);
        $locationScore = $this->calculateLocationScore($broker, $inquiry);
        $availabilityScore = $this->calculateAvailabilityScore($broker);

        // Weighted scoring system
        return ($workloadScore * 0.4) + 
               ($performanceScore * 0.3) + 
               ($locationScore * 0.2) + 
               ($availabilityScore * 0.1);
    }

    /**
     * Calculate workload score (higher score for lower workload)
     */
    protected function calculateWorkloadScore(User $broker): float
    {
        $activeInquiries = Inquiry::whereHas('property', function ($query) use ($broker) {
            $query->where('broker_id', $broker->id);
        })->whereIn('status', ['new', 'contacted', 'scheduled'])->count();

        $activeClients = Client::where('broker_id', $broker->id)
            ->where('status', 'active')
            ->count();

        $totalWorkload = $activeInquiries + ($activeClients * 0.5);

        // Normalize score (lower workload = higher score)
        return max(0, 100 - ($totalWorkload * 5));
    }

    /**
     * Calculate performance score based on recent activity
     */
    protected function calculatePerformanceScore(User $broker): float
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Response time score
        $avgResponseTime = DB::table('messages')
            ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
            ->join('inquiries', 'conversations.inquiry_id', '=', 'inquiries.id')
            ->join('properties', 'inquiries.property_id', '=', 'properties.id')
            ->where('properties.broker_id', $broker->id)
            ->where('messages.user_id', $broker->id)
            ->where('messages.created_at', '>=', $thirtyDaysAgo)
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, conversations.created_at, messages.created_at)'));

        $responseScore = $avgResponseTime ? max(0, 100 - ($avgResponseTime * 2)) : 50;

        // Conversion rate score
        $totalInquiries = Inquiry::whereHas('property', function ($query) use ($broker) {
            $query->where('broker_id', $broker->id);
        })->where('created_at', '>=', $thirtyDaysAgo)->count();

        $convertedInquiries = Inquiry::whereHas('property', function ($query) use ($broker) {
            $query->where('broker_id', $broker->id);
        })->where('status', 'completed')
          ->where('created_at', '>=', $thirtyDaysAgo)->count();

        $conversionScore = $totalInquiries > 0 ? ($convertedInquiries / $totalInquiries) * 100 : 50;

        return ($responseScore * 0.6) + ($conversionScore * 0.4);
    }

    /**
     * Calculate location score based on property location
     */
    protected function calculateLocationScore(User $broker, Inquiry $inquiry): float
    {
        if (!$inquiry->property || !$broker->preferred_locations) {
            return 50; // Neutral score
        }

        $propertyLocation = strtolower($inquiry->property->location ?? '');
        $preferredLocations = array_map('strtolower', json_decode($broker->preferred_locations, true) ?? []);

        foreach ($preferredLocations as $location) {
            if (str_contains($propertyLocation, $location) || str_contains($location, $propertyLocation)) {
                return 100;
            }
        }

        return 25; // Lower score for non-preferred locations
    }

    /**
     * Calculate availability score based on recent activity
     */
    protected function calculateAvailabilityScore(User $broker): float
    {
        $lastActivity = $broker->last_seen_at ?? $broker->updated_at;
        
        if (!$lastActivity) {
            return 25;
        }

        $hoursInactive = Carbon::now()->diffInHours($lastActivity);

        if ($hoursInactive <= 2) {
            return 100;
        } elseif ($hoursInactive <= 24) {
            return 75;
        } elseif ($hoursInactive <= 72) {
            return 50;
        } else {
            return 25;
        }
    }

    /**
     * Get broker workload statistics
     */
    public function getBrokerWorkload(User $broker): array
    {
        $activeInquiries = Inquiry::whereHas('property', function ($query) use ($broker) {
            $query->where('broker_id', $broker->id);
        })->whereIn('status', ['new', 'contacted', 'scheduled'])->count();

        $activeClients = Client::where('broker_id', $broker->id)
            ->where('status', 'active')
            ->count();

        $pendingTransactions = DB::table('transactions')
            ->where('broker_id', $broker->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        return [
            'active_inquiries' => $activeInquiries,
            'active_clients' => $activeClients,
            'pending_transactions' => $pendingTransactions,
            'total_workload' => $activeInquiries + $activeClients + $pendingTransactions
        ];
    }

    /**
     * Reassign inquiries when broker becomes unavailable
     * Note: This only reassigns inquiry handling, not property ownership
     */
    public function reassignBrokerInquiries(User $unavailableBroker): int
    {
        $reassignedCount = 0;

        try {
            DB::beginTransaction();

            // Find inquiries assigned to the unavailable broker
            $activeInquiries = Inquiry::where('assigned_broker_id', $unavailableBroker->id)
                ->whereIn('status', ['new', 'contacted', 'scheduled'])
                ->get();

            foreach ($activeInquiries as $inquiry) {
                $newBroker = $this->findBestBrokerForInquiry($inquiry);
                
                if ($newBroker && $newBroker->id !== $unavailableBroker->id) {
                    // Only reassign the inquiry, never change property ownership
                    $inquiry->update(['assigned_broker_id' => $newBroker->id]);
                    $reassignedCount++;

                    Log::info('Inquiry reassigned due to broker unavailability', [
                        'inquiry_id' => $inquiry->id,
                        'old_broker_id' => $unavailableBroker->id,
                        'new_broker_id' => $newBroker->id,
                        'property_broker_id' => $inquiry->property?->broker_id, // Original owner unchanged
                    ]);
                }
            }

            DB::commit();
            return $reassignedCount;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reassign broker inquiries', [
                'broker_id' => $unavailableBroker->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}