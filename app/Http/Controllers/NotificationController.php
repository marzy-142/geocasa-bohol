<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the authenticated user
     */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(20);

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.',
                'unread_count' => 0
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        if ($request->expectsJson()) {
            $unreadCount = auth()->user()->unreadNotifications()->count();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.',
                'unread_count' => $unreadCount,
                'notification_id' => $notificationId
            ]);
        }

        return back();
    }

    /**
     * Delete a specific notification
     */
    public function delete($notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->delete();
        }

        return back()->with('success', 'Notification deleted.');
    }

    /**
     * Get notification settings for the authenticated user
     */
    public function getSettings(Request $request)
    {
        $preferences = auth()->user()->getNotificationPreferences();
        
        // Return JSON for API requests, Inertia view for web requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($preferences->toArray());
        }
        
        return inertia('Notifications/Settings', [
            'settings' => $preferences->toArray()
        ]);
    }

    /**
     * Update notification settings for the authenticated user
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'email_new_inquiry' => 'boolean',
            'email_transaction_updates' => 'boolean',
            'email_messages' => 'boolean',
            'email_seller_requests' => 'boolean',
            'email_broker_assignments' => 'boolean',
            'email_frequency' => 'in:immediate,hourly,daily,weekly',
            'phone_number' => 'nullable|string|max:20',
            'sms_urgent_inquiries' => 'boolean',
            'sms_transaction_milestones' => 'boolean',
            'browser_notifications' => 'boolean',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
        ]);

        $preferences = auth()->user()->getNotificationPreferences();
        $preferences->update($validated);

        return back()->with('success', 'Notification settings updated successfully.');
    }
}