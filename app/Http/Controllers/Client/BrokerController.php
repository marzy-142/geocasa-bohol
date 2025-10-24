<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Models\Inquiry;
use App\Models\Meeting;
use App\Models\Conversation;
use App\Models\Message;
use App\Http\Requests\SendMessageRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BrokerController extends Controller
{
    /**
     * Display client's assigned broker information
     */
    public function show(Request $request)
    {
        $user = auth()->user();
        
        // Get client record
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$client) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Client profile not found');
        }

        // Get assigned broker with real stats
        $broker = $client->broker;
        
        if (!$broker) {
            return Inertia::render('Client/Broker', [
                'broker' => null,
                'client' => $client,
                'recentInquiries' => [],
                'scheduledMeetings' => [],
                'activeConversation' => null,
                'brokerStats' => null,
            ]);
        }

        // Calculate real broker stats
        $brokerStats = [
            'properties_sold' => \App\Models\Transaction::where('broker_id', $broker->id)
                ->whereIn('status', ['completed', 'closed'])
                ->count(),
            'active_clients' => \App\Models\Client::where('broker_id', $broker->id)->count(),
            'total_inquiries' => Inquiry::where('assigned_broker_id', $broker->id)->count(),
            'response_rate' => $this->calculateResponseRate($broker->id),
        ];

        // Get recent inquiries with this broker
        $recentInquiries = Inquiry::with(['property'])
            ->where('client_id', $client->id)
            ->where('assigned_broker_id', $broker->id)
            ->latest()
            ->limit(10)
            ->get();

        // Get scheduled meetings from real data
        $scheduledMeetings = $client->meetings()
            ->with('property:id,title')
            ->where('broker_id', $broker->id)
            ->orderBy('scheduled_date')
            ->get()
            ->map(function($meeting) {
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->title,
                    'date' => $meeting->scheduled_date->format('Y-m-d'),
                    'time' => $meeting->scheduled_date->format('H:i'),
                    'location' => $meeting->location,
                    'status' => $meeting->status,
                    'type' => $meeting->type ?? 'meeting',
                    'property' => $meeting->property,
                    'notes' => $meeting->notes,
                ];
            });

        // Find active conversation with broker (using pivot table)
        $activeConversation = Conversation::where('type', 'general')
            ->whereHas('participantUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereHas('participantUsers', function($q) use ($broker) {
                $q->where('user_id', $broker->id);
            })
            ->with('messages:id,conversation_id,created_at')
            ->first();

        return Inertia::render('Client/Broker', [
            'broker' => $broker->only(['id', 'name', 'email', 'phone', 'office_address', 'office_contact_number', 'years_experience']),
            'client' => $client,
            'recentInquiries' => $recentInquiries,
            'scheduledMeetings' => $scheduledMeetings,
            'activeConversation' => $activeConversation,
            'brokerStats' => $brokerStats,
        ]);
    }

    /**
     * Calculate broker response rate
     */
    private function calculateResponseRate($brokerId)
    {
        $totalInquiries = Inquiry::where('assigned_broker_id', $brokerId)->count();
        if ($totalInquiries === 0) return 100;
        
        $respondedInquiries = Inquiry::where('assigned_broker_id', $brokerId)
            ->whereNotNull('responded_at')
            ->count();
        
        return round(($respondedInquiries / $totalInquiries) * 100);
    }

    /**
     * Send message to broker - Now redirects to conversation system
     */
    public function sendMessage(SendMessageRequest $request)
    {
        $user = auth()->user();
        
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$client || !$client->broker) {
            return back()->with('error', 'Broker not assigned');
        }

        // Find or create a general conversation with broker (using pivot table)
        $conversation = Conversation::where('type', 'general')
            ->whereHas('participantUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereHas('participantUsers', function($q) use ($client) {
                $q->where('user_id', $client->broker_id);
            })
            ->first();

        if (!$conversation) {
            // Create new general conversation
            $conversation = Conversation::create([
                'title' => "Conversation with {$client->broker->name}",
                'type' => 'general',
                'participants' => [$user->id, $client->broker_id],
                'lifecycle_stage' => 'general',
            ]);
            
            // Sync participants to pivot table
            $conversation->participantUsers()->attach([$user->id, $client->broker_id]);
        }

        // Only create message if content is provided
        if ($request->filled('content')) {
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'content' => $request->content,
                'type' => Message::TYPE_TEXT,
            ]);
        }

        // Redirect to conversation
        return redirect()->route('conversations.show', $conversation->id);
    }

    /**
     * Schedule a meeting with broker
     */
    public function scheduleMeeting(Request $request)
    {
        $user = auth()->user();
        
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        if (!$client || !$client->broker) {
            return response()->json(['error' => 'Broker not assigned'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|string',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
            'property_id' => 'nullable|exists:properties,id',
            'inquiry_id' => 'nullable|exists:inquiries,id',
            'type' => 'nullable|in:viewing,consultation,document_review,virtual',
        ]);

        // Create meeting record in database
        $meeting = Meeting::create([
            'client_id' => $client->id,
            'broker_id' => $client->broker_id,
            'property_id' => $validated['property_id'] ?? null,
            'inquiry_id' => $validated['inquiry_id'] ?? null,
            'title' => $validated['title'],
            'scheduled_date' => $validated['date'] . ' ' . $validated['time'],
            'location' => $validated['location'],
            'notes' => $validated['notes'] ?? null,
            'type' => $validated['type'] ?? 'consultation',
            'status' => 'pending',
            'created_by' => 'client'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Meeting scheduled successfully',
            'meeting' => $meeting,
        ]);
    }

    /**
     * Cancel a meeting
     */
    public function cancelMeeting(Meeting $meeting)
    {
        $user = auth()->user();
        $client = Client::where('user_id', $user->id)->first();

        if (!$client || $meeting->client_id !== $client->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($meeting->status === 'completed') {
            return response()->json(['error' => 'Cannot cancel completed meeting'], 400);
        }

        $meeting->update([
            'status' => 'cancelled',
            'cancelled_by' => 'client',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Meeting cancelled successfully',
        ]);
    }
}



