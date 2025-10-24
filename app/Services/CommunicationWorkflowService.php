<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\CommunicationWorkflow;
use App\Events\InquiryStatusUpdated;
use App\Notifications\InquiryEscalationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class CommunicationWorkflowService
{
    protected UnifiedBrokerAssignmentService $assignmentService;
    protected BrokerClientAnalyticsService $analyticsService;

    public function __construct(
        UnifiedBrokerAssignmentService $assignmentService,
        BrokerClientAnalyticsService $analyticsService
    ) {
        $this->assignmentService = $assignmentService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Create communication workflow for inquiry
     */
    public function createInquiryWorkflow(Inquiry $inquiry): CommunicationWorkflow
    {
        $workflow = CommunicationWorkflow::create([
            'workflowable_type' => Inquiry::class,
            'workflowable_id' => $inquiry->id,
            'broker_id' => $inquiry->assigned_broker_id,
            'client_id' => $inquiry->client_id,
            'workflow_type' => 'inquiry_response',
            'status' => 'pending',
            'scheduled_at' => now()->addHours(2), // 2-hour response expectation
            'workflow_data' => [
                'inquiry_status' => $inquiry->status,
                'priority' => $this->calculateInquiryPriority($inquiry),
                'escalation_threshold' => 24, // 24 hours for escalation
            ]
        ]);

        Log::info('Communication workflow created for inquiry', [
            'workflow_id' => $workflow->id,
            'inquiry_id' => $inquiry->id,
            'broker_id' => $inquiry->assigned_broker_id,
            'scheduled_at' => $workflow->scheduled_at
        ]);

        return $workflow;
    }

    /**
     * Create communication workflow for transaction
     */
    public function createTransactionWorkflow(Transaction $transaction): CommunicationWorkflow
    {
        $workflow = CommunicationWorkflow::create([
            'workflowable_type' => Transaction::class,
            'workflowable_id' => $transaction->id,
            'broker_id' => $transaction->broker_id,
            'client_id' => $transaction->client_id,
            'workflow_type' => 'transaction_update',
            'status' => 'pending',
            'scheduled_at' => $this->calculateNextTransactionUpdateTime($transaction),
            'workflow_data' => [
                'transaction_status' => $transaction->status,
                'last_update' => $transaction->updated_at,
                'next_milestone' => $this->getNextTransactionMilestone($transaction),
            ]
        ]);

        Log::info('Communication workflow created for transaction', [
            'workflow_id' => $workflow->id,
            'transaction_id' => $transaction->id,
            'broker_id' => $transaction->broker_id,
            'scheduled_at' => $workflow->scheduled_at
        ]);

        return $workflow;
    }

    /**
     * Process pending workflows
     */
    public function processPendingWorkflows(): array
    {
        $results = [
            'processed' => 0,
            'escalated' => 0,
            'completed' => 0,
            'errors' => 0,
        ];

        $pendingWorkflows = CommunicationWorkflow::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->with(['workflowable'])
            ->get();

        foreach ($pendingWorkflows as $workflow) {
            try {
                DB::beginTransaction();

                $result = $this->processWorkflow($workflow);
                $results[$result]++;

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $results['errors']++;
                
                Log::error('Failed to process communication workflow', [
                    'workflow_id' => $workflow->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Communication workflow processing completed', $results);
        return $results;
    }

    /**
     * Process individual workflow
     */
    protected function processWorkflow(CommunicationWorkflow $workflow): string
    {
        switch ($workflow->workflow_type) {
            case 'inquiry_response':
                return $this->processInquiryResponseWorkflow($workflow);
            
            case 'escalation':
                return $this->processEscalationWorkflow($workflow);
            
            case 'follow_up':
                return $this->processFollowUpWorkflow($workflow);
            
            case 'transaction_update':
                return $this->processTransactionUpdateWorkflow($workflow);
            
            default:
                $workflow->update(['status' => 'cancelled']);
                return 'completed';
        }
    }

    /**
     * Process inquiry response workflow
     */
    protected function processInquiryResponseWorkflow(CommunicationWorkflow $workflow): string
    {
        $inquiry = $workflow->workflowable;
        
        if (!$inquiry) {
            $workflow->update(['status' => 'cancelled']);
            return 'completed';
        }

        // Check if broker has responded
        $hasResponse = $this->hasBrokerResponded($workflow->broker_id, $inquiry);
        
        if ($hasResponse) {
            $workflow->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => 'Broker responded within expected timeframe'
            ]);
            return 'completed';
        }

        // Check if escalation is needed
        $hoursSinceInquiry = $inquiry->created_at->diffInHours(now());
        $escalationThreshold = $workflow->workflow_data['escalation_threshold'] ?? 24;

        if ($hoursSinceInquiry >= $escalationThreshold) {
            return $this->escalateInquiry($workflow, $inquiry);
        }

        // Schedule follow-up
        $this->scheduleFollowUp($workflow, $inquiry);
        return 'processed';
    }

    /**
     * Process escalation workflow
     */
    protected function processEscalationWorkflow(CommunicationWorkflow $workflow): string
    {
        $inquiry = $workflow->workflowable;
        
        if (!$inquiry) {
            $workflow->update(['status' => 'cancelled']);
            return 'completed';
        }

        // Find alternative broker
        $alternativeBroker = $this->assignmentService->reassignBroker(
            $inquiry,
            null, // Will be determined by assignment service
            'escalated_no_response',
            ['context' => 'escalated']
        );

        if ($alternativeBroker) {
            $workflow->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => "Inquiry escalated to broker {$alternativeBroker->name}"
            ]);

            // Send escalation notification
            $this->sendEscalationNotification($inquiry, $alternativeBroker);

            return 'escalated';
        }

        $workflow->update([
            'status' => 'cancelled',
            'notes' => 'No alternative broker available for escalation'
        ]);

        return 'completed';
    }

    /**
     * Process follow-up workflow
     */
    protected function processFollowUpWorkflow(CommunicationWorkflow $workflow): string
    {
        $inquiry = $workflow->workflowable;
        
        if (!$inquiry) {
            $workflow->update(['status' => 'cancelled']);
            return 'completed';
        }

        // Check if broker has responded since last follow-up
        $hasResponse = $this->hasBrokerRespondedSince($workflow->broker_id, $inquiry, $workflow->created_at);
        
        if ($hasResponse) {
            $workflow->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => 'Broker responded after follow-up reminder'
            ]);
            return 'completed';
        }

        // Send follow-up reminder
        $this->sendFollowUpReminder($workflow);

        // Schedule next follow-up or escalate
        $followUpCount = $this->getFollowUpCount($inquiry);
        
        if ($followUpCount >= 2) {
            return $this->escalateInquiry($workflow, $inquiry);
        }

        $this->scheduleFollowUp($workflow, $inquiry, $followUpCount + 1);
        return 'processed';
    }

    /**
     * Process transaction update workflow
     */
    protected function processTransactionUpdateWorkflow(CommunicationWorkflow $workflow): string
    {
        $transaction = $workflow->workflowable;
        
        if (!$transaction) {
            $workflow->update(['status' => 'cancelled']);
            return 'completed';
        }

        // Check if transaction status has changed
        $lastStatus = $workflow->workflow_data['transaction_status'] ?? 'unknown';
        
        if ($transaction->status !== $lastStatus) {
            $workflow->update([
                'status' => 'completed',
                'completed_at' => now(),
                'notes' => "Transaction status updated from {$lastStatus} to {$transaction->status}"
            ]);
            return 'completed';
        }

        // Check if update is overdue
        $daysSinceLastUpdate = $transaction->updated_at->diffInDays(now());
        
        if ($daysSinceLastUpdate >= 7) {
            $this->sendTransactionUpdateReminder($workflow);
        }

        // Schedule next update check
        $this->scheduleNextTransactionUpdate($workflow, $transaction);
        return 'processed';
    }

    /**
     * Escalate inquiry
     */
    protected function escalateInquiry(CommunicationWorkflow $workflow, Inquiry $inquiry): string
    {
        // Create escalation workflow
        $escalationWorkflow = CommunicationWorkflow::create([
            'workflowable_type' => Inquiry::class,
            'workflowable_id' => $inquiry->id,
            'broker_id' => $inquiry->assigned_broker_id,
            'client_id' => $inquiry->client_id,
            'workflow_type' => 'escalation',
            'status' => 'pending',
            'scheduled_at' => now()->addMinutes(15), // Process escalation in 15 minutes
            'workflow_data' => [
                'original_workflow_id' => $workflow->id,
                'escalation_reason' => 'No response within expected timeframe',
                'escalation_time' => now()->toISOString(),
            ]
        ]);

        $workflow->update([
            'status' => 'escalated',
            'notes' => "Escalated to workflow {$escalationWorkflow->id}"
        ]);

        // Update inquiry status
        $inquiry->update(['status' => 'escalated']);
        
        // Fire status update event
        event(new InquiryStatusUpdated($inquiry, 'contacted', 'escalated'));

        Log::info('Inquiry escalated', [
            'inquiry_id' => $inquiry->id,
            'original_workflow_id' => $workflow->id,
            'escalation_workflow_id' => $escalationWorkflow->id
        ]);

        return 'escalated';
    }

    /**
     * Schedule follow-up
     */
    protected function scheduleFollowUp(CommunicationWorkflow $workflow, Inquiry $inquiry, int $followUpNumber = 1): void
    {
        $followUpWorkflow = CommunicationWorkflow::create([
            'workflowable_type' => Inquiry::class,
            'workflowable_id' => $inquiry->id,
            'broker_id' => $workflow->broker_id,
            'client_id' => $inquiry->client_id,
            'workflow_type' => 'follow_up',
            'status' => 'pending',
            'scheduled_at' => now()->addHours(4 * $followUpNumber), // 4, 8, 12 hours
            'workflow_data' => [
                'follow_up_number' => $followUpNumber,
                'original_workflow_id' => $workflow->id,
            ]
        ]);

        $workflow->update([
            'status' => 'in_progress',
            'notes' => "Follow-up {$followUpNumber} scheduled for workflow {$followUpWorkflow->id}"
        ]);
    }

    /**
     * Schedule next transaction update
     */
    protected function scheduleNextTransactionUpdate(CommunicationWorkflow $workflow, Transaction $transaction): void
    {
        $nextUpdateTime = $this->calculateNextTransactionUpdateTime($transaction);
        
        $workflow->update([
            'status' => 'pending',
            'scheduled_at' => $nextUpdateTime,
            'workflow_data' => array_merge($workflow->workflow_data ?? [], [
                'transaction_status' => $transaction->status,
                'last_update' => $transaction->updated_at,
            ])
        ]);
    }

    /**
     * Check if broker has responded to inquiry
     */
    protected function hasBrokerResponded(int $brokerId, Inquiry $inquiry): bool
    {
        return Message::whereHas('conversation', function ($query) use ($inquiry) {
            $query->where('inquiry_id', $inquiry->id);
        })
        ->where('sender_id', $brokerId)
        ->where('created_at', '>', $inquiry->created_at)
        ->exists();
    }

    /**
     * Check if broker has responded since given time
     */
    protected function hasBrokerRespondedSince(int $brokerId, Inquiry $inquiry, Carbon $since): bool
    {
        return Message::whereHas('conversation', function ($query) use ($inquiry) {
            $query->where('inquiry_id', $inquiry->id);
        })
        ->where('sender_id', $brokerId)
        ->where('created_at', '>', $since)
        ->exists();
    }

    /**
     * Get follow-up count for inquiry
     */
    protected function getFollowUpCount(Inquiry $inquiry): int
    {
        return CommunicationWorkflow::where('workflowable_type', Inquiry::class)
            ->where('workflowable_id', $inquiry->id)
            ->where('workflow_type', 'follow_up')
            ->count();
    }

    /**
     * Calculate inquiry priority
     */
    protected function calculateInquiryPriority(Inquiry $inquiry): string
    {
        // High priority for inquiries with specific keywords or from repeat clients
        $highPriorityKeywords = ['urgent', 'asap', 'immediate', 'today'];
        $message = strtolower($inquiry->message ?? '');
        
        foreach ($highPriorityKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return 'high';
            }
        }

        // Check if client has multiple inquiries
        $clientInquiryCount = Inquiry::where('client_id', $inquiry->client_id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        if ($clientInquiryCount > 3) {
            return 'high';
        }

        return 'normal';
    }

    /**
     * Calculate next transaction update time
     */
    protected function calculateNextTransactionUpdateTime(Transaction $transaction): Carbon
    {
        $statusUpdateIntervals = [
            'inquiry' => 24, // 24 hours
            'initial_contact' => 48, // 48 hours
            'property_viewing' => 72, // 72 hours
            'offer_made' => 24, // 24 hours
            'negotiation' => 12, // 12 hours
            'offer_accepted' => 24, // 24 hours
            'contract_signed' => 48, // 48 hours
            'due_diligence' => 72, // 72 hours
            'financing' => 168, // 1 week
            'closing_preparation' => 24, // 24 hours
        ];

        $hours = $statusUpdateIntervals[$transaction->status] ?? 48;
        return now()->addHours($hours);
    }

    /**
     * Get next transaction milestone
     */
    protected function getNextTransactionMilestone(Transaction $transaction): string
    {
        $milestones = [
            'inquiry' => 'Initial Contact',
            'initial_contact' => 'Property Viewing',
            'property_viewing' => 'Offer Made',
            'offer_made' => 'Negotiation',
            'negotiation' => 'Offer Accepted',
            'offer_accepted' => 'Contract Signed',
            'contract_signed' => 'Due Diligence',
            'due_diligence' => 'Financing',
            'financing' => 'Closing Preparation',
            'closing_preparation' => 'Finalized',
        ];

        return $milestones[$transaction->status] ?? 'Unknown';
    }

    /**
     * Send escalation notification
     */
    protected function sendEscalationNotification(Inquiry $inquiry, User $newBroker): void
    {
        try {
            $newBroker->notify(new InquiryEscalationNotification($inquiry, $newBroker));
            
            Log::info('Escalation notification sent', [
                'inquiry_id' => $inquiry->id,
                'new_broker_id' => $newBroker->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send escalation notification', [
                'inquiry_id' => $inquiry->id,
                'new_broker_id' => $newBroker->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send follow-up reminder
     */
    protected function sendFollowUpReminder(CommunicationWorkflow $workflow): void
    {
        try {
            $broker = User::find($workflow->broker_id);
            if ($broker) {
                // Create system message in conversation
                $conversation = Conversation::where('inquiry_id', $workflow->workflowable_id)->first();
                if ($conversation) {
                    Message::createSystemMessage(
                        $conversation->id,
                        "🔔 Follow-up reminder: This inquiry is awaiting your response. Please respond within the next 4 hours to avoid escalation.",
                        [
                            'workflow_id' => $workflow->id,
                            'reminder_type' => 'follow_up'
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send follow-up reminder', [
                'workflow_id' => $workflow->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send transaction update reminder
     */
    protected function sendTransactionUpdateReminder(CommunicationWorkflow $workflow): void
    {
        try {
            $transaction = $workflow->workflowable;
            $broker = User::find($workflow->broker_id);
            
            if ($transaction && $broker) {
                $conversation = Conversation::where('transaction_id', $transaction->id)->first();
                if ($conversation) {
                    Message::createSystemMessage(
                        $conversation->id,
                        "📋 Transaction Update Reminder: Please provide an update on the transaction status. Last update was " . $transaction->updated_at->diffForHumans(),
                        [
                            'workflow_id' => $workflow->id,
                            'reminder_type' => 'transaction_update'
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send transaction update reminder', [
                'workflow_id' => $workflow->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync conversation with transaction status
     */
    public function syncConversationWithTransaction(Transaction $transaction): void
    {
        try {
            $conversation = Conversation::where('transaction_id', $transaction->id)->first();
            
            if ($conversation) {
                // Create system message about status change
                Message::createSystemMessage(
                    $conversation->id,
                    "📊 Transaction Status Update: {$transaction->status_label}",
                    [
                        'transaction_id' => $transaction->id,
                        'status_change' => true,
                        'previous_status' => $transaction->getOriginal('status'),
                        'new_status' => $transaction->status
                    ]
                );

                // Update conversation title if needed
                $newTitle = "Transaction: {$transaction->property->title} ({$transaction->status_label})";
                if ($conversation->title !== $newTitle) {
                    $conversation->update(['title' => $newTitle]);
                }

                Log::info('Conversation synced with transaction status', [
                    'transaction_id' => $transaction->id,
                    'conversation_id' => $conversation->id,
                    'new_status' => $transaction->status
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync conversation with transaction', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Auto-escalate unanswered inquiries
     */
    public function autoEscalateUnansweredInquiries(int $hoursThreshold = 24): array
    {
        $results = [
            'checked' => 0,
            'escalated' => 0,
            'errors' => 0,
        ];

        $cutoffTime = now()->subHours($hoursThreshold);
        
        $unansweredInquiries = Inquiry::where('status', 'new')
            ->where('created_at', '<=', $cutoffTime)
            ->with(['client', 'property'])
            ->get();

        foreach ($unansweredInquiries as $inquiry) {
            try {
                $results['checked']++;
                
                if (!$this->hasBrokerResponded($inquiry->assigned_broker_id, $inquiry)) {
                    $this->escalateInquiryDirectly($inquiry);
                    $results['escalated']++;
                }
                
            } catch (\Exception $e) {
                $results['errors']++;
                
                Log::error('Failed to auto-escalate inquiry', [
                    'inquiry_id' => $inquiry->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Auto-escalation process completed', $results);
        return $results;
    }

    /**
     * Escalate inquiry directly without workflow
     */
    protected function escalateInquiryDirectly(Inquiry $inquiry): void
    {
        try {
            DB::beginTransaction();

            // Find alternative broker
            $alternativeBroker = $this->assignmentService->reassignBroker(
                $inquiry,
                null,
                'auto_escalation_no_response',
                ['context' => 'escalated']
            );

            if ($alternativeBroker) {
                $inquiry->update(['status' => 'escalated']);
                
                // Send escalation notification
                $this->sendEscalationNotification($inquiry, $alternativeBroker);
                
                // Fire status update event
                event(new InquiryStatusUpdated($inquiry, 'new', 'escalated'));
            }

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get workflow statistics
     */
    public function getWorkflowStatistics(int $days = 7): array
    {
        $startDate = now()->subDays($days);
        
        $workflows = CommunicationWorkflow::where('created_at', '>=', $startDate)->get();
        
        return [
            'total_workflows' => $workflows->count(),
            'by_status' => $workflows->groupBy('status')->map->count(),
            'by_type' => $workflows->groupBy('workflow_type')->map->count(),
            'average_completion_time' => $this->calculateAverageCompletionTime($workflows),
            'escalation_rate' => $this->calculateEscalationRate($workflows),
            'response_rate' => $this->calculateResponseRate($workflows),
        ];
    }

    /**
     * Calculate average completion time
     */
    protected function calculateAverageCompletionTime($workflows): float
    {
        $completedWorkflows = $workflows->where('status', 'completed')
            ->whereNotNull('completed_at');

        if ($completedWorkflows->isEmpty()) {
            return 0;
        }

        $totalMinutes = $completedWorkflows->sum(function ($workflow) {
            return $workflow->created_at->diffInMinutes($workflow->completed_at);
        });

        return round($totalMinutes / $completedWorkflows->count(), 2);
    }

    /**
     * Calculate escalation rate
     */
    protected function calculateEscalationRate($workflows): float
    {
        $total = $workflows->count();
        $escalated = $workflows->where('status', 'escalated')->count();

        return $total > 0 ? round(($escalated / $total) * 100, 2) : 0;
    }

    /**
     * Calculate response rate
     */
    protected function calculateResponseRate($workflows): float
    {
        $inquiryWorkflows = $workflows->where('workflow_type', 'inquiry_response');
        $total = $inquiryWorkflows->count();
        $completed = $inquiryWorkflows->where('status', 'completed')->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }
}

