<?php

namespace App\Services;

use App\Models\SellerRequest;
use App\Models\User;
use App\Models\Inquiry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReminderService
{
    /**
     * Get all reminders for a specific broker or system-wide for admins
     */
    public function getBrokerReminders($brokerId = null): array
    {
        return [
            'pending_seller_requests' => $this->getPendingSellerRequests($brokerId),
            'unverified_accounts' => $this->getUnverifiedAccounts(),
            'overdue_inquiries' => $this->getOverdueInquiries($brokerId),
            'summary' => $this->getReminderSummary($brokerId),
        ];
    }

    /**
     * Get pending seller requests assigned to broker or all for admins
     */
    private function getPendingSellerRequests($brokerId = null): Collection
    {
        $query = SellerRequest::whereIn('status', ['pending', 'under_review'])
            ->with(['assignedBroker:id,name'])
            ->orderBy('created_at', 'asc');
            
        if ($brokerId) {
            $query->where('assigned_broker_id', $brokerId);
        }
        
        return $query->get()
            ->map(function ($request) {
                $daysOld = $request->created_at->diffInDays(now());
                return [
                    'id' => $request->id,
                    'type' => 'seller_request',
                    'title' => $request->property_title,
                    'description' => "Seller: {$request->seller_name}",
                    'status' => $request->status,
                    'priority' => $this->calculatePriority($daysOld, 'seller_request'),
                    'days_old' => $daysOld,
                    'created_at' => $request->created_at,
                    'asking_price' => $request->formatted_asking_price,
                    'location' => $request->property_location,
                    'seller_contact' => [
                        'name' => $request->seller_name,
                        'email' => $request->seller_email,
                        'phone' => $request->seller_phone,
                    ],
                ];
            });
    }

    /**
     * Get unverified broker accounts (admin-level reminder)
     */
    private function getUnverifiedAccounts(): Collection
    {
        return User::where('role', 'broker')
            ->where('application_status', 'pending')
            ->orWhere(function ($query) {
                $query->where('role', 'broker')
                      ->where('application_status', 'under_review')
                      ->where('prc_verified', false)
                      ->orWhere('business_permit_verified', false);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($user) {
                $daysOld = $user->created_at->diffInDays(now());
                return [
                    'id' => $user->id,
                    'type' => 'unverified_account',
                    'title' => "Broker Application: {$user->name}",
                    'description' => $user->email,
                    'status' => $user->application_status,
                    'priority' => $this->calculatePriority($daysOld, 'unverified_account'),
                    'days_old' => $daysOld,
                    'created_at' => $user->created_at,
                    'verification_status' => [
                        'prc_verified' => $user->prc_verified,
                        'business_permit_verified' => $user->business_permit_verified,
                        'has_prc_id' => !empty($user->prc_id),
                        'has_business_permit' => !empty($user->business_permit),
                    ],
                ];
            });
    }

    /**
     * Get overdue inquiries for broker or all for admins
     */
    private function getOverdueInquiries($brokerId = null): Collection
    {
        $query = Inquiry::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(2)) // Consider overdue after 2 days
            ->with(['property:id,title,price,broker_id', 'client:id,name,email'])
            ->orderBy('created_at', 'asc');
            
        if ($brokerId) {
            $query->whereHas('property', function ($subQuery) use ($brokerId) {
                $subQuery->where('broker_id', $brokerId);
            });
        } else {
            $query->with(['property.broker:id,name']);
        }
        
        return $query->get()
            ->map(function ($inquiry) {
                $daysOld = $inquiry->created_at->diffInDays(now());
                return [
                    'id' => $inquiry->id,
                    'type' => 'overdue_inquiry',
                    'title' => "Inquiry: {$inquiry->property->title}",
                    'description' => "Client: {$inquiry->client->name}",
                    'status' => $inquiry->status,
                    'priority' => $this->calculatePriority($daysOld, 'overdue_inquiry'),
                    'days_old' => $daysOld,
                    'created_at' => $inquiry->created_at,
                    'property_price' => '₱' . number_format($inquiry->property->price),
                    'client_contact' => [
                        'name' => $inquiry->client->name,
                        'email' => $inquiry->client->email,
                    ],
                ];
            });
    }

    /**
     * Calculate priority based on age and type
     */
    private function calculatePriority($daysOld, $type): string
    {
        $thresholds = [
            'seller_request' => ['high' => 7, 'medium' => 3],
            'unverified_account' => ['high' => 5, 'medium' => 2],
            'overdue_inquiry' => ['high' => 5, 'medium' => 3],
        ];

        $threshold = $thresholds[$type] ?? ['high' => 7, 'medium' => 3];

        if ($daysOld >= $threshold['high']) {
            return 'high';
        } elseif ($daysOld >= $threshold['medium']) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Get reminder summary statistics
     */
    private function getReminderSummary($brokerId = null): array
    {
        $pendingRequestsQuery = SellerRequest::whereIn('status', ['pending', 'under_review']);
        if ($brokerId) {
            $pendingRequestsQuery->where('assigned_broker_id', $brokerId);
        }
        $pendingRequests = $pendingRequestsQuery->count();

        $overdueInquiriesQuery = Inquiry::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(2));
        if ($brokerId) {
            $overdueInquiriesQuery->whereHas('property', function ($query) use ($brokerId) {
                $query->where('broker_id', $brokerId);
            });
        }
        $overdueInquiries = $overdueInquiriesQuery->count();

        $unverifiedAccounts = User::where('role', 'broker')
            ->where('application_status', 'pending')
            ->orWhere(function ($query) {
                $query->where('role', 'broker')
                      ->where('application_status', 'under_review')
                      ->where(function ($subQuery) {
                          $subQuery->where('prc_verified', false)
                                   ->orWhere('business_permit_verified', false);
                      });
            })
            ->count();

        $totalReminders = $pendingRequests + $overdueInquiries + $unverifiedAccounts;
        $highPriorityCount = $this->getHighPriorityCount($brokerId);

        return [
            'total_reminders' => $totalReminders,
            'pending_seller_requests' => $pendingRequests,
            'overdue_inquiries' => $overdueInquiries,
            'unverified_accounts' => $unverifiedAccounts,
            'high_priority_count' => $highPriorityCount,
            'has_urgent_items' => $highPriorityCount > 0,
        ];
    }

    /**
     * Get count of high priority items
     */
    private function getHighPriorityCount($brokerId = null): int
    {
        $highPriorityRequestsQuery = SellerRequest::whereIn('status', ['pending', 'under_review'])
            ->where('created_at', '<', now()->subDays(7));
        if ($brokerId) {
            $highPriorityRequestsQuery->where('assigned_broker_id', $brokerId);
        }
        $highPriorityRequests = $highPriorityRequestsQuery->count();

        $highPriorityInquiriesQuery = Inquiry::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(5));
        if ($brokerId) {
            $highPriorityInquiriesQuery->whereHas('property', function ($query) use ($brokerId) {
                $query->where('broker_id', $brokerId);
            });
        }
        $highPriorityInquiries = $highPriorityInquiriesQuery->count();

        $highPriorityAccounts = User::where('role', 'broker')
            ->where('application_status', 'pending')
            ->where('created_at', '<', now()->subDays(5))
            ->count();

        return $highPriorityRequests + $highPriorityInquiries + $highPriorityAccounts;
    }

    /**
     * Mark reminder as acknowledged
     */
    public function acknowledgeReminder($type, $id, $userId): bool
    {
        // This could be expanded to track acknowledgments in a separate table
        // For now, we'll just return true as acknowledgment is handled client-side
        return true;
    }

    /**
     * Get reminder preferences for user
     */
    public function getReminderPreferences($userId): array
    {
        // This could be expanded to include user-specific reminder preferences
        return [
            'email_notifications' => true,
            'dashboard_notifications' => true,
            'reminder_frequency' => 'daily',
            'priority_threshold' => 'medium',
        ];
    }
}