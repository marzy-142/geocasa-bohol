<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Client;
use App\Models\User;
use App\Models\Conversation;
use App\Enums\InquiryStatus;
use App\Events\NewInquiryReceived;
use App\Events\InquiryStatusUpdated;
use App\Notifications\NewInquiryNotification;
use App\Services\BrokerAssignmentService;
use App\Services\DuplicatePreventionService;
use App\Services\InquiryLinkingService;
use App\Services\InquiryMonitoringService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class InquiryService
{
    protected BrokerAssignmentService $brokerAssignmentService;
    protected DuplicatePreventionService $duplicatePreventionService;
    protected InquiryLinkingService $inquiryLinkingService;
    protected InquiryMonitoringService $monitoringService;

    public function __construct(
        BrokerAssignmentService $brokerAssignmentService,
        DuplicatePreventionService $duplicatePreventionService,
        InquiryLinkingService $inquiryLinkingService,
        InquiryMonitoringService $monitoringService
    ) {
        $this->brokerAssignmentService = $brokerAssignmentService;
        $this->duplicatePreventionService = $duplicatePreventionService;
        $this->inquiryLinkingService = $inquiryLinkingService;
        $this->monitoringService = $monitoringService;
    }
    /**
     * Create a new inquiry with business rule validation
     */
    public function createInquiry(array $data): array
    {
        $startTime = microtime(true);
        
        try {
            // Log inquiry creation start
            $this->monitoringService->logEvent('inquiry_creation_started', null, [
                'property_id' => $data['property_id'] ?? null,
                'email' => $data['email'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Step 1: Validate business rules
            $this->validateBusinessRules($data);

            // Step 2: Check for duplicates
            $duplicateCheck = $this->duplicatePreventionService->isDuplicate($data);
            
            if ($duplicateCheck['is_duplicate']) {
                $this->duplicatePreventionService->logDuplicateAttempt($data, $duplicateCheck);
                
                $this->monitoringService->logEvent('duplicate_inquiry_prevented', null, [
                    'property_id' => $data['property_id'] ?? null,
                    'email' => $data['email'] ?? null,
                    'duplicate_type' => $duplicateCheck['type'] ?? 'unknown',
                    'action' => $duplicateCheck['action'],
                    'original_inquiry_id' => $duplicateCheck['original_inquiry_id'] ?? null,
                ]);
                
                if ($duplicateCheck['action'] === 'block') {
                    throw new ValidationException('Duplicate inquiry detected and blocked.');
                }
                
                // Flag for review if medium severity
                $data['is_flagged'] = true;
                $data['flag_reason'] = $duplicateCheck['checks'];
            }

            DB::beginTransaction();

            // Step 3: Create the inquiry
            $inquiry = Inquiry::create(array_merge($data, [
                'status' => InquiryStatus::NEW->value,
                'created_at' => now(),
                'ip_address' => request()->ip()
            ]));

            // Log inquiry creation
            $this->monitoringService->logEvent('inquiry_created', $inquiry->id, [
                'property_id' => $inquiry->property_id,
                'email' => $inquiry->email,
                'source' => $inquiry->source ?? 'website',
            ]);

            // Step 4: Link to existing client if email matches
            if (!empty($data['email'])) {
                $linkingResult = $this->inquiryLinkingService->linkInquiryToClient($inquiry, $data['email']);
                if ($linkingResult['success']) {
                    Log::info('Inquiry linked to existing client', [
                        'inquiry_id' => $inquiry->id,
                        'client_id' => $linkingResult['client_id']
                    ]);
                    
                    $this->monitoringService->logEvent('client_linked', $inquiry->id, [
                        'client_id' => $linkingResult['client_id'],
                        'is_new_client' => $linkingResult['is_new_client'] ?? false,
                    ]);
                }
            }

            // Step 5: Auto-assign broker
            $assignedBroker = $this->brokerAssignmentService->assignBrokerToInquiry($inquiry);
            
            if ($assignedBroker) {
                $this->monitoringService->logEvent('broker_assigned', $inquiry->id, [
                    'broker_id' => $assignedBroker->id,
                    'broker_name' => $assignedBroker->name,
                    'assignment_method' => 'automatic',
                ]);
            }

            // Step 6: Send notifications
            try {
                $this->sendNewInquiryNotifications($inquiry, $assignedBroker);
                
                $this->monitoringService->logEvent('notifications_sent', $inquiry->id, [
                    'broker_id' => $assignedBroker?->id,
                    'notification_types' => ['broker', 'admin', 'property_owner'],
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send inquiry notifications', [
                    'inquiry_id' => $inquiry->id,
                    'error' => $e->getMessage()
                ]);
                
                $this->monitoringService->logEvent('notification_failed', $inquiry->id, [
                    'error' => $e->getMessage(),
                    'broker_id' => $assignedBroker?->id,
                ]);
            }

            // Step 7: Fire events
            event(new NewInquiryReceived($inquiry));

            DB::commit();

            // Log completion metrics
            $processingTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
            $this->monitoringService->logEvent('inquiry_creation_completed', $inquiry->id, [
                'processing_time_ms' => round($processingTime, 2),
                'broker_assigned' => $assignedBroker !== null,
                'client_linked' => !empty($linkingResult['success']),
            ]);

            Log::info('Inquiry created successfully', [
                'inquiry_id' => $inquiry->id,
                'broker_id' => $assignedBroker?->id,
                'is_flagged' => $inquiry->is_flagged ?? false,
                'processing_time_ms' => round($processingTime, 2),
            ]);

            return [
                'success' => true,
                'inquiry' => $inquiry,
                'broker' => $assignedBroker,
                'duplicate_check' => $duplicateCheck,
                'processing_time_ms' => round($processingTime, 2),
            ];

        } catch (ValidationException $e) {
            DB::rollBack();
            
            $processingTime = (microtime(true) - $startTime) * 1000;
            $this->monitoringService->logEvent('inquiry_creation_failed', null, [
                'property_id' => $data['property_id'] ?? null,
                'email' => $data['email'] ?? null,
                'error' => $e->getMessage(),
                'error_type' => 'validation',
                'processing_time_ms' => round($processingTime, 2),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'validation'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            $processingTime = (microtime(true) - $startTime) * 1000;
            $this->monitoringService->logEvent('inquiry_creation_failed', null, [
                'property_id' => $data['property_id'] ?? null,
                'email' => $data['email'] ?? null,
                'error' => $e->getMessage(),
                'error_type' => 'system',
                'processing_time_ms' => round($processingTime, 2),
            ]);
            
            Log::error('Failed to create inquiry', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return [
                'success' => false,
                'error' => 'Failed to create inquiry. Please try again.',
                'type' => 'system'
            ];
        }
    }

    /**
     * Update inquiry status with validation
     */
    public function updateStatus(Inquiry $inquiry, string $newStatus, ?string $notes = null, ?int $updatedBy = null): Inquiry
    {
        $previousStatus = $inquiry->status;
        $currentStatus = InquiryStatus::from($previousStatus);
        $targetStatus = InquiryStatus::from($newStatus);

        // Validate status transition
        if (!$currentStatus->canTransitionTo($targetStatus)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition from {$currentStatus->label()} to {$targetStatus->label()}"
            ]);
        }

        return DB::transaction(function () use ($inquiry, $newStatus, $notes, $updatedBy, $previousStatus) {
            // Update inquiry
            $updateData = ['status' => $newStatus];

            // Set timestamps based on status
            switch ($newStatus) {
                case InquiryStatus::CONTACTED->value:
                    $updateData['contacted_at'] = now();
                    break;
                case InquiryStatus::SCHEDULED->value:
                    $updateData['scheduled_at'] = now();
                    break;
                case InquiryStatus::COMPLETED->value:
                case InquiryStatus::CLOSED->value:
                    $updateData['responded_at'] = now();
                    break;
            }

            if ($notes) {
                $updateData['broker_notes'] = $notes;
            }

            $inquiry->update($updateData);

            // Log status change
            Log::info('Inquiry status updated', [
                'inquiry_id' => $inquiry->id,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'updated_by' => $updatedBy,
            ]);

            // Broadcast status change
            broadcast(new InquiryStatusUpdated($inquiry, $previousStatus, $newStatus, $updatedBy));

            return $inquiry->fresh();
        });
    }

    /**
     * Validate business rules for inquiry creation
     */
    protected function validateBusinessRules(array $data): void
    {
        // Required fields validation
        $requiredFields = ['name', 'email', 'message', 'property_id'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new ValidationException("The {$field} field is required.");
            }
        }

        // Email format validation
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Please provide a valid email address.');
        }

        // Phone format validation (if provided)
        if (!empty($data['phone']) && !preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $data['phone'])) {
            throw new ValidationException('Please provide a valid phone number.');
        }

        // Message length validation
        if (strlen($data['message']) < 10) {
            throw new ValidationException('Message must be at least 10 characters long.');
        }

        if (strlen($data['message']) > 2000) {
            throw new ValidationException('Message cannot exceed 2000 characters.');
        }

        // Property existence validation
        if (!Property::where('id', $data['property_id'])->where('status', 'active')->exists()) {
            throw new ValidationException('The selected property is not available for inquiries.');
        }

        // Business hours validation (if configured)
        $this->validateBusinessHours();

        // Rate limiting validation
        $this->validateRateLimit($data);
    }

    /**
     * Validate business hours
     */
    protected function validateBusinessHours(): void
    {
        $businessHoursEnabled = config('app.enforce_business_hours', false);
        
        if (!$businessHoursEnabled) {
            return;
        }

        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 6 = Saturday
        $hour = $now->hour;

        // Weekend check (Saturday = 6, Sunday = 0)
        if (in_array($dayOfWeek, [0, 6])) {
            throw new ValidationException('Inquiries can only be submitted during business hours (Monday-Friday, 8 AM - 6 PM).');
        }

        // Business hours check (8 AM - 6 PM)
        if ($hour < 8 || $hour >= 18) {
            throw new ValidationException('Inquiries can only be submitted during business hours (Monday-Friday, 8 AM - 6 PM).');
        }
    }

    /**
     * Validate rate limiting
     */
    protected function validateRateLimit(array $data): void
    {
        $ipAddress = request()->ip();
        $email = $data['email'];

        // IP-based rate limiting (5 inquiries per hour)
        $ipInquiries = Inquiry::where('ip_address', $ipAddress)
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->count();

        if ($ipInquiries >= 5) {
            throw new ValidationException('Too many inquiries from this location. Please try again later.');
        }

        // Email-based rate limiting (3 inquiries per day)
        $emailInquiries = Inquiry::where('email', $email)
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->count();

        if ($emailInquiries >= 3) {
            throw new ValidationException('You have reached the daily limit for inquiries. Please try again tomorrow.');
        }
    }

    /**
     * Get inquiry statistics for monitoring and reporting
     */
    public function getInquiryStatistics(array $filters = []): array
    {
        $query = Inquiry::query();
        
        // Default to last 30 days if no date filters provided
        if (!isset($filters['date_from']) && !isset($filters['date_to'])) {
            $filters['date_from'] = Carbon::now()->subDays(30)->toDateString();
        }

        // Apply filters
        if (isset($filters['broker_id'])) {
            $query->whereHas('property', function ($q) use ($filters) {
                $q->where('broker_id', $filters['broker_id']);
            });
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $total = $query->count();
        $byStatus = $query->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->pluck('count', 'status')
            ->toArray();

        $responseTime = $query->whereNotNull('responded_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg_hours')
            ->value('avg_hours') ?? 0;

        return [
            'total_inquiries' => $total,
            'by_status' => $byStatus,
            'average_response_time' => round($responseTime, 2),
            'conversion_rate' => $this->calculateConversionRate(Carbon::parse($filters['date_from'] ?? Carbon::now()->subDays(30))),
            'top_properties' => $this->getTopInquiredProperties(Carbon::parse($filters['date_from'] ?? Carbon::now()->subDays(30))),
            'broker_performance' => $this->getBrokerPerformanceStats(Carbon::parse($filters['date_from'] ?? Carbon::now()->subDays(30)))
        ];
    }

    /**
     * Calculate conversion rate (inquiries that became transactions)
     */
    protected function calculateConversionRate(Carbon $startDate): float
    {
        $totalInquiries = Inquiry::where('created_at', '>=', $startDate)->count();
        
        if ($totalInquiries === 0) {
            return 0;
        }

        $convertedInquiries = Inquiry::where('created_at', '>=', $startDate)
            ->where('status', InquiryStatus::COMPLETED->value)
            ->count();

        return round(($convertedInquiries / $totalInquiries) * 100, 2);
    }

    /**
     * Calculate average response time in hours
     */
    protected function calculateAverageResponseTime(Carbon $startDate): float
    {
        $avgResponseTime = DB::table('inquiries')
            ->join('conversations', 'inquiries.id', '=', 'conversations.inquiry_id')
            ->join('messages', 'conversations.id', '=', 'messages.conversation_id')
            ->where('inquiries.created_at', '>=', $startDate)
            ->where('messages.user_id', '!=', DB::raw('inquiries.user_id'))
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, inquiries.created_at, messages.created_at)) as avg_hours')
            ->value('avg_hours');

        return round($avgResponseTime ?? 0, 2);
    }

    /**
     * Get top inquired properties
     */
    protected function getTopInquiredProperties(Carbon $startDate): array
    {
        return Inquiry::where('created_at', '>=', $startDate)
            ->select('property_id', DB::raw('COUNT(*) as inquiry_count'))
            ->groupBy('property_id')
            ->orderByDesc('inquiry_count')
            ->limit(10)
            ->with('property:id,title,location,price')
            ->get()
            ->toArray();
    }

    /**
     * Get broker performance statistics
     */
    protected function getBrokerPerformanceStats(Carbon $startDate): array
    {
        return DB::table('inquiries')
            ->join('properties', 'inquiries.property_id', '=', 'properties.id')
            ->join('users', 'properties.broker_id', '=', 'users.id')
            ->where('inquiries.created_at', '>=', $startDate)
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(inquiries.id) as total_inquiries'),
                DB::raw('SUM(CASE WHEN inquiries.status = "completed" THEN 1 ELSE 0 END) as completed_inquiries'),
                DB::raw('ROUND(AVG(CASE WHEN inquiries.status = "completed" THEN 1 ELSE 0 END) * 100, 2) as conversion_rate')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_inquiries')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get or create client record
     */
    private function getOrCreateClient(array $data): Client
    {
        // First try to find by user_id if provided
        if (isset($data['user_id'])) {
            $client = Client::where('user_id', $data['user_id'])->first();
            if ($client) {
                return $client;
            }
        }

        // Then try to find by email
        $client = Client::where('email', $data['email'])->first();
        
        if (!$client) {
            // Create new client
            $client = Client::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'status' => 'active',
            ]);

            // Auto-assign broker if needed
            $this->autoAssignBroker($client);
        } elseif (isset($data['user_id']) && !$client->user_id) {
            // Link existing client to user
            $client->update(['user_id' => $data['user_id']]);
        }

        return $client;
    }

    /**
     * Auto-assign broker to client based on workload
     */
    private function autoAssignBroker(Client $client): void
    {
        // Find broker with lowest workload
        $broker = User::where('role', 'broker')
            ->where('is_approved', true)
            ->where('application_status', 'approved')
            ->withCount([
                'assignedClients as active_clients_count' => function ($query) {
                    $query->where('status', 'active');
                }
            ])
            ->orderBy('active_clients_count')
            ->first();

        if ($broker) {
            $client->update([
                'broker_id' => $broker->id,
                'assigned_at' => now(),
            ]);

            Log::info('Client auto-assigned to broker', [
                'client_id' => $client->id,
                'broker_id' => $broker->id,
                'broker_workload' => $broker->active_clients_count,
            ]);
        }
    }

    /**
     * Send notifications for new inquiry
     */
    protected function sendNewInquiryNotifications(Inquiry $inquiry, ?User $assignedBroker = null): void
    {
        try {
            // Notify assigned broker
            if ($assignedBroker) {
                $assignedBroker->notify(new NewInquiryNotification($inquiry));
            }

            // Notify admin users
            $adminUsers = User::where('role', 'admin')->where('is_active', true)->get();
            Notification::send($adminUsers, new NewInquiryNotification($inquiry));

            // Notify property owner if different from broker
            if ($inquiry->property && $inquiry->property->user_id && 
                $inquiry->property->user_id !== $assignedBroker?->id) {
                $propertyOwner = User::find($inquiry->property->user_id);
                if ($propertyOwner) {
                    $propertyOwner->notify(new NewInquiryNotification($inquiry));
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to send new inquiry notifications', [
                'inquiry_id' => $inquiry->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get overdue inquiries that need follow-up
     */
    public function getOverdueInquiries(): \Illuminate\Database\Eloquent\Collection
    {
        return Inquiry::with(['property.broker', 'client'])
            ->whereIn('status', [InquiryStatus::NEW->value, InquiryStatus::CONTACTED->value])
            ->where('created_at', '<=', now()->subHours(24))
            ->orderBy('created_at')
            ->get();
    }
}