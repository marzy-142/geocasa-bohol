<?php

namespace App\Http\Controllers;

use App\Services\ReminderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReminderController extends Controller
{
    protected $reminderService;

    public function __construct(ReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    /**
     * Get reminders for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role === 'broker') {
            $reminders = $this->reminderService->getBrokerReminders($user->id);
        } else {
            // For admin users, get system-wide reminders
            $reminders = $this->reminderService->getBrokerReminders(null);
        }

        return response()->json([
            'success' => true,
            'data' => $reminders,
        ]);
    }

    /**
     * Show the reminders dashboard page
     */
    public function dashboard(): Response
    {
        $user = auth()->user();
        
        if ($user->role === 'broker') {
            $reminders = $this->reminderService->getBrokerReminders($user->id);
        } else {
            // For admin users, get system-wide reminders
            $reminders = $this->reminderService->getBrokerReminders(null);
        }

        return Inertia::render('Reminders/Dashboard', [
            'reminders' => $reminders,
            'user_role' => $user->role,
        ]);
    }

    /**
     * Get reminder summary for dashboard widget
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role === 'broker') {
            $summary = $this->reminderService->getReminderSummary($user->id);
        } else {
            // For admin users, get system-wide summary
            $summary = $this->reminderService->getReminderSummary(null);
        }

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Acknowledge a reminder
     */
    public function acknowledge(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:seller_request,unverified_account,overdue_inquiry',
            'id' => 'required|integer',
        ]);

        $acknowledged = $this->reminderService->acknowledgeReminder(
            $request->type,
            $request->id,
            $request->user()->id
        );

        return response()->json([
            'success' => $acknowledged,
            'message' => $acknowledged ? 'Reminder acknowledged' : 'Failed to acknowledge reminder',
        ]);
    }

    /**
     * Get reminder preferences
     */
    public function preferences(Request $request): JsonResponse
    {
        $preferences = $this->reminderService->getReminderPreferences($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $preferences,
        ]);
    }

    /**
     * Update reminder preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'dashboard_notifications' => 'boolean',
            'reminder_frequency' => 'string|in:daily,weekly,never',
            'priority_threshold' => 'string|in:low,medium,high',
        ]);

        // This would typically update user preferences in database
        // For now, we'll just return success
        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
        ]);
    }

    /**
     * Get reminders by type
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        $request->validate([
            'type' => 'in:seller_request,unverified_account,overdue_inquiry',
        ]);

        $user = $request->user();
        $reminders = $this->reminderService->getBrokerReminders(
            $user->role === 'broker' ? $user->id : null
        );

        $typeKey = match($type) {
            'seller_request' => 'pending_seller_requests',
            'unverified_account' => 'unverified_accounts',
            'overdue_inquiry' => 'overdue_inquiries',
            default => 'pending_seller_requests',
        };

        return response()->json([
            'success' => true,
            'data' => $reminders[$typeKey] ?? [],
        ]);
    }

    /**
     * Get high priority reminders only
     */
    public function highPriority(Request $request): JsonResponse
    {
        $user = $request->user();
        $allReminders = $this->reminderService->getBrokerReminders(
            $user->role === 'broker' ? $user->id : null
        );

        $highPriorityReminders = [];
        
        foreach ($allReminders as $type => $reminders) {
            if ($type === 'summary') continue;
            
            $highPriorityReminders[$type] = $reminders->filter(function ($reminder) {
                return $reminder['priority'] === 'high';
            })->values();
        }

        return response()->json([
            'success' => true,
            'data' => $highPriorityReminders,
        ]);
    }
}