<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Client;
use App\Models\ClientTransactionEngagement;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display the client transaction dashboard
     */
    public function dashboard()
    {
        $client = $this->getAuthenticatedClient();
        
        if (!$client) {
            return redirect()->route('login')->with('error', 'Please log in to view your transactions.');
        }

        return Inertia::render('Client/Transactions/Dashboard', [
            'activeTransactions' => $this->getActiveTransactions($client),
            'milestones' => $this->getUpcomingMilestones($client),
            'requiredActions' => $this->getRequiredClientActions($client),
            'documents' => $this->getPendingDocuments($client),
            'meetings' => $this->getUpcomingMeetings($client),
            'engagement' => $this->getClientEngagement($client),
        ]);
    }

    /**
     * Show a specific transaction for the client
     */
    public function show(Transaction $transaction)
    {
        $client = $this->getAuthenticatedClient();
        
        if (!$client || $transaction->client_id !== $client->id) {
            abort(403, 'Unauthorized access to transaction.');
        }

        // Update client last viewed timestamp
        $transaction->update(['client_last_viewed' => now()]);
        
        // Record client engagement
        $this->recordClientView($transaction, $client);

        $transaction->load([
            'property:id,title,slug,address,municipality,total_price,type,status,images',
            'broker:id,name,email,phone,office_address',
            'inquiry:id,property_id,client_id,inquiry_type,status,message'
        ]);

        return Inertia::render('Client/Transactions/Show', [
            'transaction' => $transaction,
            'engagement' => $this->getTransactionEngagement($transaction),
            'conversation' => $this->getTransactionConversation($transaction),
            'timeline' => $this->getTransactionTimeline($transaction),
        ]);
    }

    /**
     * Get active transactions for the client
     */
    private function getActiveTransactions(Client $client): array
    {
        return Transaction::where('client_id', $client->id)
            ->whereNotIn('status', ['finalized', 'cancelled'])
            ->with([
                'property:id,title,slug,address,municipality,total_price,type',
                'broker:id,name,email',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'status' => $transaction->status,
                    'status_label' => $this->getStatusLabel($transaction->status),
                    'property' => $transaction->property,
                    'broker' => $transaction->broker,
                    'offered_price' => $transaction->offered_price,
                    'final_price' => $transaction->final_price,
                    'requires_action' => $transaction->requires_client_action,
                    'action_deadline' => $transaction->client_action_deadline,
                    'last_viewed' => $transaction->client_last_viewed,
                    'created_at' => $transaction->created_at,
                    'progress_percentage' => $this->calculateProgress($transaction),
                ];
            })
            ->toArray();
    }

    /**
     * Get upcoming milestones for the client
     */
    private function getUpcomingMilestones(Client $client): array
    {
        $transactions = Transaction::where('client_id', $client->id)
            ->whereNotIn('status', ['finalized', 'cancelled'])
            ->get();

        $milestones = [];

        foreach ($transactions as $transaction) {
            $nextMilestone = $this->getNextMilestone($transaction);
            if ($nextMilestone) {
                $milestones[] = [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'milestone' => $nextMilestone['name'],
                    'description' => $nextMilestone['description'],
                    'estimated_date' => $nextMilestone['estimated_date'],
                    'status' => $nextMilestone['status'],
                ];
            }
        }

        return $milestones;
    }

    /**
     * Get required client actions
     */
    private function getRequiredClientActions(Client $client): array
    {
        return [
            'approvals_pending' => $this->getPendingApprovals($client),
            'documents_required' => $this->getRequiredDocuments($client),
            'meetings_scheduled' => $this->getUpcomingMeetings($client),
            'feedback_needed' => $this->getFeedbackRequests($client),
        ];
    }

    /**
     * Get pending client approvals
     */
    private function getPendingApprovals(Client $client): array
    {
        return Transaction::where('client_id', $client->id)
            ->where('requires_client_action', true)
            ->where('status', 'client_approval_pending')
            ->with(['property:id,title'])
            ->get()
            ->map(function ($transaction) {
                $approvals = $transaction->client_approvals ?? [];
                $pendingApprovals = array_filter($approvals, fn($approval) => $approval['status'] === 'pending');
                
                return [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'property_title' => $transaction->property->title,
                    'approvals' => $pendingApprovals,
                    'deadline' => $transaction->client_action_deadline,
                    'days_remaining' => $transaction->client_action_deadline ? 
                        now()->diffInDays($transaction->client_action_deadline, false) : null,
                ];
            })
            ->toArray();
    }

    /**
     * Get required documents
     */
    private function getRequiredDocuments(Client $client): array
    {
        // This will be enhanced when we implement the document management system
        return Transaction::where('client_id', $client->id)
            ->where('requires_client_action', true)
            ->where('status', 'document_collection')
            ->with(['property:id,title'])
            ->get()
            ->map(function ($transaction) {
                return [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'property_title' => $transaction->property->title,
                    'required_documents' => $this->getDocumentRequirements($transaction),
                    'deadline' => $transaction->client_action_deadline,
                ];
            })
            ->toArray();
    }

    /**
     * Get upcoming meetings
     */
    private function getUpcomingMeetings(Client $client): array
    {
        return Meeting::where('client_id', $client->id)
            ->where('scheduled_date', '>=', now())
            ->with(['transaction.property:id,title'])
            ->orderBy('scheduled_date')
            ->get()
            ->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->title,
                    'type' => $meeting->meeting_type,
                    'scheduled_date' => $meeting->scheduled_date,
                    'location' => $meeting->location,
                    'transaction' => $meeting->transaction ? [
                        'id' => $meeting->transaction->id,
                        'property_title' => $meeting->transaction->property->title,
                    ] : null,
                ];
            })
            ->toArray();
    }

    /**
     * Get feedback requests
     */
    private function getFeedbackRequests(Client $client): array
    {
        return Transaction::where('client_id', $client->id)
            ->where('client_satisfaction', 'pending')
            ->whereIn('status', ['finalized', 'offer_accepted', 'contract_signed'])
            ->with(['property:id,title'])
            ->get()
            ->map(function ($transaction) {
                return [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'property_title' => $transaction->property->title,
                    'status' => $transaction->status,
                    'finalized_date' => $transaction->finalized_date,
                ];
            })
            ->toArray();
    }

    /**
     * Get client engagement data
     */
    private function getClientEngagement(Client $client): array
    {
        $engagement = ClientTransactionEngagement::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$engagement) {
            return [
                'level' => 'medium',
                'score' => 0,
                'login_count' => 0,
                'response_time' => null,
                'last_active' => null,
            ];
        }

        return [
            'level' => $engagement->engagement_level,
            'score' => $engagement->calculateEngagementScore(),
            'login_count' => $engagement->login_count,
            'response_time' => $engagement->response_time_formatted,
            'last_active' => $engagement->last_active_formatted,
            'satisfaction_score' => $engagement->satisfaction_score,
        ];
    }

    /**
     * Get authenticated client
     */
    private function getAuthenticatedClient(): ?Client
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'client') {
            return null;
        }

        return Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
    }

    /**
     * Record client view for engagement tracking
     */
    private function recordClientView(Transaction $transaction, Client $client): void
    {
        $engagement = ClientTransactionEngagement::firstOrCreate(
            [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
            ],
            [
                'engagement_level' => 'medium',
                'login_count' => 0,
                'total_interactions' => 0,
            ]
        );

        $engagement->recordInteraction('transaction_viewed', [
            'transaction_id' => $transaction->id,
            'status' => $transaction->status,
        ]);
    }

    /**
     * Get transaction engagement data
     */
    private function getTransactionEngagement(Transaction $transaction): array
    {
        $engagement = ClientTransactionEngagement::where('transaction_id', $transaction->id)
            ->where('client_id', $transaction->client_id)
            ->first();

        if (!$engagement) {
            return [
                'level' => 'medium',
                'score' => 0,
                'interactions' => 0,
                'last_active' => null,
            ];
        }

        return [
            'level' => $engagement->engagement_level,
            'score' => $engagement->calculateEngagementScore(),
            'interactions' => $engagement->total_interactions,
            'last_active' => $engagement->last_active,
            'response_time' => $engagement->response_time_formatted,
        ];
    }

    /**
     * Get transaction conversation
     */
    private function getTransactionConversation(Transaction $transaction)
    {
        $conversation = $transaction->conversation;
        
        if (!$conversation) {
            return null;
        }

        return [
            'id' => $conversation->id,
            'title' => $conversation->title,
            'last_message' => $conversation->latestMessage ? [
                'content' => $conversation->latestMessage->content,
                'sender' => $conversation->latestMessage->sender ? [
                    'name' => $conversation->latestMessage->sender->name,
                    'role' => $conversation->latestMessage->sender->role,
                ] : null,
                'created_at' => $conversation->latestMessage->created_at,
            ] : null,
            'unread_count' => $conversation->getUnreadCountForUser(Auth::id()),
        ];
    }

    /**
     * Get transaction timeline
     */
    private function getTransactionTimeline(Transaction $transaction): array
    {
        $timeline = [];

        // Add key dates
        if ($transaction->inquiry_date) {
            $timeline[] = [
                'date' => $transaction->inquiry_date,
                'event' => 'Inquiry Submitted',
                'description' => 'Initial inquiry was submitted',
                'type' => 'milestone',
            ];
        }

        if ($transaction->first_contact_date) {
            $timeline[] = [
                'date' => $transaction->first_contact_date,
                'event' => 'First Contact',
                'description' => 'Broker made first contact',
                'type' => 'contact',
            ];
        }

        if ($transaction->viewing_date) {
            $timeline[] = [
                'date' => $transaction->viewing_date,
                'event' => 'Property Viewing',
                'description' => 'Property was viewed',
                'type' => 'viewing',
            ];
        }

        if ($transaction->offer_date) {
            $timeline[] = [
                'date' => $transaction->offer_date,
                'event' => 'Offer Made',
                'description' => 'Offer was submitted',
                'type' => 'offer',
            ];
        }

        if ($transaction->finalized_date) {
            $timeline[] = [
                'date' => $transaction->finalized_date,
                'event' => 'Transaction Finalized',
                'description' => 'Transaction was completed',
                'type' => 'completion',
            ];
        }

        // Sort by date
        usort($timeline, fn($a, $b) => $a['date'] <=> $b['date']);

        return $timeline;
    }

    /**
     * Get next milestone for a transaction
     */
    private function getNextMilestone(Transaction $transaction): ?array
    {
        $milestones = [
            'inquiry' => [
                'name' => 'Initial Contact',
                'description' => 'Your broker will contact you within 24 hours',
                'estimated_date' => $transaction->created_at->addDay(),
                'status' => 'pending',
            ],
            'initial_contact' => [
                'name' => 'Property Viewing',
                'description' => 'Schedule a property viewing appointment',
                'estimated_date' => $transaction->first_contact_date ? $transaction->first_contact_date->addDays(3) : null,
                'status' => 'pending',
            ],
            'property_viewing' => [
                'name' => 'Offer Preparation',
                'description' => 'Prepare and submit an offer',
                'estimated_date' => $transaction->viewing_date ? $transaction->viewing_date->addDays(2) : null,
                'status' => 'pending',
            ],
            'offer_made' => [
                'name' => 'Seller Response',
                'description' => 'Waiting for seller response to your offer',
                'estimated_date' => $transaction->offer_date ? $transaction->offer_date->addDays(7) : null,
                'status' => 'in_progress',
            ],
            'negotiation' => [
                'name' => 'Contract Signing',
                'description' => 'Sign the purchase agreement',
                'estimated_date' => now()->addDays(5),
                'status' => 'pending',
            ],
        ];

        return $milestones[$transaction->status] ?? null;
    }

    /**
     * Get status label
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'inquiry' => 'Initial Inquiry',
            'initial_contact' => 'First Contact Made',
            'property_viewing' => 'Property Viewed',
            'offer_made' => 'Offer Submitted',
            'negotiation' => 'Negotiating',
            'offer_accepted' => 'Offer Accepted',
            'contract_signed' => 'Contract Signed',
            'due_diligence' => 'Due Diligence',
            'financing' => 'Financing',
            'closing_preparation' => 'Closing Preparation',
            'finalized' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucwords(str_replace('_', ' ', $status))
        };
    }

    /**
     * Calculate transaction progress percentage
     */
    private function calculateProgress(Transaction $transaction): int
    {
        $statuses = [
            'inquiry' => 10,
            'initial_contact' => 20,
            'property_viewing' => 30,
            'offer_made' => 40,
            'negotiation' => 50,
            'offer_accepted' => 60,
            'contract_signed' => 70,
            'due_diligence' => 80,
            'financing' => 85,
            'closing_preparation' => 90,
            'finalized' => 100,
            'cancelled' => 0,
        ];

        return $statuses[$transaction->status] ?? 0;
    }

    /**
     * Get document requirements for a transaction
     */
    private function getDocumentRequirements(Transaction $transaction): array
    {
        // This will be enhanced when we implement the document management system
        return [
            'Financial Documents',
            'Identification',
            'Property Documents',
        ];
    }

    /**
     * Update transaction (for client notes)
     */
    public function update(Request $request, Transaction $transaction)
    {
        $client = $this->getAuthenticatedClient();

        // Ensure the client is authorized to update this transaction
        if (!$client || $transaction->client_id !== $client->id) {
            abort(403, 'Unauthorized access to transaction.');
        }

        $request->validate([
            'client_notes' => 'nullable|string|max:1000',
        ]);

        $transaction->update([
            'client_notes' => $request->client_notes,
            'client_last_viewed' => now(),
        ]);

        // Update engagement score
        $engagement = ClientTransactionEngagement::where('transaction_id', $transaction->id)
            ->where('client_id', $client->id)
            ->first();
        
        if ($engagement) {
            $engagement->recordInteraction('notes_updated', [
                'notes_length' => strlen($request->client_notes ?? ''),
            ]);
        }

        return back()->with('success', 'Transaction notes updated successfully.');
    }

    /**
     * Process client approval
     */
    public function processApproval(Request $request, Transaction $transaction)
    {
        $client = $this->getAuthenticatedClient();

        // Ensure the client is authorized to approve for this transaction
        if (!$client || $transaction->client_id !== $client->id) {
            abort(403, 'Unauthorized access to transaction.');
        }

        $request->validate([
            'approval_id' => 'required|string',
            'approved' => 'required|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $approvalService = app(\App\Services\ClientApprovalService::class);
            $result = $approvalService->processClientApproval(
                $transaction,
                $request->approval_id,
                $request->approved,
                $request->notes
            );

            if ($result['success']) {
                $message = $request->approved 
                    ? 'Approval submitted successfully.' 
                    : 'Rejection submitted successfully.';
                
                return back()->with('success', $message);
            } else {
                return back()->with('error', $result['error'] ?? 'Failed to process approval.');
            }
        } catch (\Exception $e) {
            \Log::error('Client approval processing failed', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'An error occurred while processing your approval.');
        }
    }
}