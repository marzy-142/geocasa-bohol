<?php

namespace App\Services;

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Client;
use App\Models\BrokerClientRelationship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UnifiedBrokerAssignmentService
{
    protected BrokerAssignmentService $legacyAssignmentService;
    protected BrokerClientAnalyticsService $analyticsService;

    public function __construct(
        BrokerAssignmentService $legacyAssignmentService,
        BrokerClientAnalyticsService $analyticsService
    ) {
        $this->legacyAssignmentService = $legacyAssignmentService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Unified method to assign broker to client based on context
     */
    public function assignBrokerToClient(
        Client $client, 
        string $context = 'inquiry',
        array $options = []
    ): ?User {
        try {
            DB::beginTransaction();

            $previousBroker = $client->broker;
            $newBroker = $this->determineBestBroker($client, $context, $options);

            if (!$newBroker) {
                Log::warning('No suitable broker found for client assignment', [
                    'client_id' => $client->id,
                    'context' => $context
                ]);
                DB::rollBack();
                return null;
            }

            // Handle different assignment contexts
            $relationshipType = $this->determineRelationshipType($context, $options);
            $assignmentMethod = $this->determineAssignmentMethod($options);
            $assignmentReason = $options['reason'] ?? $this->generateAssignmentReason($context, $newBroker);

            // Update client relationship
            $client->update([
                'broker_id' => $newBroker->id,
                'relationship_type' => $relationshipType,
                'relationship_established_at' => now(),
                'assignment_reason' => $assignmentReason,
                'relationship_metadata' => [
                    'context' => $context,
                    'assignment_method' => $assignmentMethod,
                    'assigned_by' => auth()->id(),
                    'previous_broker_id' => $previousBroker?->id,
                ]
            ]);

            // Record relationship history
            $this->recordRelationshipHistory($client, $newBroker, $previousBroker, $relationshipType, $assignmentMethod, $assignmentReason);

            // Handle context-specific logic
            $this->handleContextSpecificLogic($client, $newBroker, $context, $options);

            DB::commit();

            Log::info('Broker assigned to client via unified service', [
                'client_id' => $client->id,
                'broker_id' => $newBroker->id,
                'context' => $context,
                'relationship_type' => $relationshipType,
                'assignment_method' => $assignmentMethod
            ]);

            return $newBroker;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign broker to client', [
                'client_id' => $client->id,
                'context' => $context,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Assign broker to inquiry with enhanced tracking
     */
    public function assignBrokerToInquiry(
        Inquiry $inquiry, 
        string $context = 'property_broker',
        array $options = []
    ): ?User {
        try {
            DB::beginTransaction();

            $broker = $this->determineInquiryBroker($inquiry, $context, $options);

            if (!$broker) {
                Log::warning('No suitable broker found for inquiry assignment', [
                    'inquiry_id' => $inquiry->id,
                    'context' => $context
                ]);
                DB::rollBack();
                return null;
            }

            // Update inquiry with assignment context
            $inquiry->update([
                'assigned_broker_id' => $broker->id,
                'assignment_context' => $context,
                'assignment_timestamp' => now(),
                'assignment_metadata' => [
                    'assignment_method' => $this->determineAssignmentMethod($options),
                    'assigned_by' => auth()->id(),
                    'previous_broker_id' => $inquiry->assigned_broker_id,
                    'context_options' => $options
                ]
            ]);

            // Ensure client has proper broker relationship
            if ($inquiry->client) {
                $this->ensureClientBrokerRelationship($inquiry->client, $broker, $context, $options);
            }

            DB::commit();

            Log::info('Broker assigned to inquiry via unified service', [
                'inquiry_id' => $inquiry->id,
                'broker_id' => $broker->id,
                'context' => $context
            ]);

            return $broker;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign broker to inquiry', [
                'inquiry_id' => $inquiry->id,
                'context' => $context,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Reassign broker with proper context tracking
     */
    public function reassignBroker(
        $entity, // Client or Inquiry
        User $newBroker,
        string $reason = 'manual_reassignment',
        array $options = []
    ): bool {
        try {
            DB::beginTransaction();

            if ($entity instanceof Client) {
                return $this->reassignClientBroker($entity, $newBroker, $reason, $options);
            } elseif ($entity instanceof Inquiry) {
                return $this->reassignInquiryBroker($entity, $newBroker, $reason, $options);
            }

            DB::rollBack();
            return false;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reassign broker', [
                'entity_type' => get_class($entity),
                'entity_id' => $entity->id,
                'new_broker_id' => $newBroker->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get broker assignment recommendations for a client/inquiry
     */
    public function getBrokerRecommendations($entity, array $options = []): array
    {
        $brokers = User::where('role', 'broker')
            ->where('status', 'approved')
            ->where('is_active', true)
            ->get();

        $recommendations = $brokers->map(function ($broker) use ($entity, $options) {
            $score = $this->calculateComprehensiveScore($broker, $entity, $options);
            
            return [
                'broker' => $broker->only(['id', 'name', 'email']),
                'score' => $score,
                'workload' => $this->analyticsService->getBrokerWorkload($broker->id),
                'performance' => $this->analyticsService->getBrokerPerformance($broker->id),
                'availability' => $this->calculateAvailabilityScore($broker),
                'recommendation_reason' => $this->generateRecommendationReason($broker, $entity, $score)
            ];
        })->sortByDesc('score')->values();

        return $recommendations->toArray();
    }

    /**
     * Determine the best broker for a client based on context
     */
    protected function determineBestBroker(Client $client, string $context, array $options): ?User
    {
        // Handle manual assignment
        if (isset($options['broker_id'])) {
            return User::find($options['broker_id']);
        }

        // Handle escalation
        if ($context === 'escalated' && isset($options['previous_broker_id'])) {
            $previousBroker = User::find($options['previous_broker_id']);
            if ($previousBroker) {
                return $this->findAlternativeBroker($previousBroker, $client);
            }
        }

        // Use legacy service for complex scoring
        return $this->legacyAssignmentService->findBestBrokerForInquiry(
            new Inquiry(['client_id' => $client->id])
        );
    }

    /**
     * Determine broker for inquiry with enhanced logic
     */
    protected function determineInquiryBroker(Inquiry $inquiry, string $context, array $options): ?User
    {
        switch ($context) {
            case 'property_broker':
                // Prioritize property's original broker
                if ($inquiry->property && $inquiry->property->broker_id) {
                    $propertyBroker = User::find($inquiry->property->broker_id);
                    if ($this->isBrokerAvailable($propertyBroker)) {
                        return $propertyBroker;
                    }
                }
                break;

            case 'auto_assigned':
                // Use intelligent assignment
                return $this->legacyAssignmentService->findBestBrokerForInquiry($inquiry);

            case 'manual_assigned':
                // Use specified broker
                if (isset($options['broker_id'])) {
                    return User::find($options['broker_id']);
                }
                break;

            case 'escalated':
                // Find alternative broker for escalation
                if ($inquiry->assigned_broker_id) {
                    return $this->findAlternativeBroker(
                        User::find($inquiry->assigned_broker_id),
                        $inquiry->client
                    );
                }
                break;
        }

        return null;
    }

    /**
     * Determine relationship type based on context
     */
    protected function determineRelationshipType(string $context, array $options): string
    {
        return match($context) {
            'inquiry' => 'inquiry_specific',
            'transaction' => 'primary',
            'escalated' => $options['relationship_type'] ?? 'primary',
            'manual' => $options['relationship_type'] ?? 'primary',
            default => 'primary'
        };
    }

    /**
     * Determine assignment method
     */
    protected function determineAssignmentMethod(array $options): string
    {
        if (isset($options['broker_id'])) {
            return 'manual';
        }
        return $options['assignment_method'] ?? 'auto';
    }

    /**
     * Generate assignment reason
     */
    protected function generateAssignmentReason(string $context, User $broker): string
    {
        return match($context) {
            'inquiry' => "Auto-assigned for inquiry handling",
            'transaction' => "Assigned for transaction management",
            'escalated' => "Reassigned due to escalation",
            'manual' => "Manually assigned by admin",
            default => "System assignment"
        };
    }

    /**
     * Record relationship history
     */
    protected function recordRelationshipHistory(
        Client $client,
        User $newBroker,
        ?User $previousBroker,
        string $relationshipType,
        string $assignmentMethod,
        string $assignmentReason
    ): void {
        // End previous relationship if exists
        if ($previousBroker) {
            BrokerClientRelationship::where('client_id', $client->id)
                ->where('broker_id', $previousBroker->id)
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);
        }

        // Create new relationship record
        BrokerClientRelationship::create([
            'client_id' => $client->id,
            'broker_id' => $newBroker->id,
            'relationship_type' => $relationshipType,
            'assignment_method' => $assignmentMethod,
            'assignment_reason' => $assignmentReason,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'metadata' => [
                'previous_broker_id' => $previousBroker?->id,
                'context' => 'unified_assignment'
            ]
        ]);
    }

    /**
     * Handle context-specific logic after assignment
     */
    protected function handleContextSpecificLogic(
        Client $client,
        User $broker,
        string $context,
        array $options
    ): void {
        switch ($context) {
            case 'inquiry':
                // Ensure all active inquiries are assigned to this broker
                $client->inquiries()
                    ->whereIn('status', ['new', 'contacted', 'scheduled'])
                    ->whereNull('assigned_broker_id')
                    ->update(['assigned_broker_id' => $broker->id]);
                break;

            case 'transaction':
                // Ensure all active transactions are assigned to this broker
                $client->transactions()
                    ->whereNotIn('status', ['finalized', 'cancelled'])
                    ->update(['broker_id' => $broker->id]);
                break;
        }
    }

    /**
     * Ensure client has proper broker relationship
     */
    protected function ensureClientBrokerRelationship(
        Client $client,
        User $broker,
        string $context,
        array $options
    ): void {
        if (!$client->broker_id || $client->broker_id !== $broker->id) {
            $this->assignBrokerToClient($client, $context, array_merge($options, [
                'broker_id' => $broker->id
            ]));
        }
    }

    /**
     * Reassign client broker
     */
    protected function reassignClientBroker(
        Client $client,
        User $newBroker,
        string $reason,
        array $options
    ): bool {
        $previousBroker = $client->broker;

        $client->update([
            'broker_id' => $newBroker->id,
            'relationship_type' => $options['relationship_type'] ?? 'primary',
            'relationship_established_at' => now(),
            'assignment_reason' => $reason,
            'relationship_metadata' => array_merge(
                $client->relationship_metadata ?? [],
                [
                    'reassigned_from' => $previousBroker?->id,
                    'reassignment_reason' => $reason,
                    'reassigned_at' => now()->toISOString()
                ]
            )
        ]);

        $this->recordRelationshipHistory(
            $client,
            $newBroker,
            $previousBroker,
            $options['relationship_type'] ?? 'primary',
            'reassigned',
            $reason
        );

        return true;
    }

    /**
     * Reassign inquiry broker
     */
    protected function reassignInquiryBroker(
        Inquiry $inquiry,
        User $newBroker,
        string $reason,
        array $options
    ): bool {
        $inquiry->update([
            'assigned_broker_id' => $newBroker->id,
            'assignment_context' => 'manual_assigned',
            'assignment_timestamp' => now(),
            'assignment_metadata' => array_merge(
                $inquiry->assignment_metadata ?? [],
                [
                    'reassigned_from' => $inquiry->assigned_broker_id,
                    'reassignment_reason' => $reason,
                    'reassigned_at' => now()->toISOString()
                ]
            )
        ]);

        return true;
    }

    /**
     * Calculate comprehensive broker score
     */
    protected function calculateComprehensiveScore($broker, $entity, array $options): float
    {
        // Use legacy scoring as base
        $baseScore = $this->legacyAssignmentService->calculateBrokerScore(
            $broker,
            $entity instanceof Inquiry ? $entity : new Inquiry(['client_id' => $entity->id])
        );

        // Add context-specific adjustments
        $contextAdjustment = $this->getContextAdjustment($broker, $entity, $options);
        $relationshipBonus = $this->getRelationshipBonus($broker, $entity);

        return min(100, $baseScore + $contextAdjustment + $relationshipBonus);
    }

    /**
     * Get context-specific score adjustment
     */
    protected function getContextAdjustment($broker, $entity, array $options): float
    {
        $context = $options['context'] ?? 'general';
        
        return match($context) {
            'escalated' => -10, // Slight penalty for escalated assignments
            'inquiry' => 5,     // Bonus for inquiry-specific assignments
            'transaction' => 10, // Higher bonus for transaction assignments
            default => 0
        };
    }

    /**
     * Get relationship bonus for existing relationships
     */
    protected function getRelationshipBonus($broker, $entity): float
    {
        if ($entity instanceof Client) {
            $existingRelationship = BrokerClientRelationship::where('client_id', $entity->id)
                ->where('broker_id', $broker->id)
                ->whereNull('ended_at')
                ->first();

            if ($existingRelationship) {
                return match($existingRelationship->relationship_type) {
                    'primary' => 15,
                    'secondary' => 10,
                    'inquiry_specific' => 5,
                    default => 0
                };
            }
        }

        return 0;
    }

    /**
     * Find alternative broker for escalation
     */
    protected function findAlternativeBroker(User $currentBroker, ?Client $client = null): ?User
    {
        $brokers = User::where('role', 'broker')
            ->where('status', 'approved')
            ->where('is_active', true)
            ->where('id', '!=', $currentBroker->id)
            ->get();

        if ($brokers->isEmpty()) {
            return null;
        }

        // Find broker with lowest workload among alternatives
        $bestBroker = null;
        $lowestWorkload = PHP_INT_MAX;

        foreach ($brokers as $broker) {
            $workload = $this->analyticsService->getBrokerWorkload($broker->id);
            $totalWorkload = $workload['total_workload'];

            if ($totalWorkload < $lowestWorkload) {
                $lowestWorkload = $totalWorkload;
                $bestBroker = $broker;
            }
        }

        return $bestBroker;
    }

    /**
     * Check if broker is available
     */
    protected function isBrokerAvailable(?User $broker): bool
    {
        if (!$broker) {
            return false;
        }

        return $this->legacyAssignmentService->isBrokerAvailable($broker);
    }

    /**
     * Calculate availability score
     */
    protected function calculateAvailabilityScore(User $broker): float
    {
        return $this->legacyAssignmentService->calculateAvailabilityScore($broker);
    }

    /**
     * Generate recommendation reason
     */
    protected function generateRecommendationReason(User $broker, $entity, float $score): string
    {
        if ($score >= 90) {
            return "Excellent match - optimal workload and performance";
        } elseif ($score >= 75) {
            return "Good match - suitable workload and good performance";
        } elseif ($score >= 60) {
            return "Acceptable match - manageable workload";
        } else {
            return "Available but may have higher workload";
        }
    }
}

