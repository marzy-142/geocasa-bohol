<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Meeting;
use App\Models\Client;
use App\Services\MeetingSchedulingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MeetingController extends Controller
{
    protected MeetingSchedulingService $meetingService;

    public function __construct(MeetingSchedulingService $meetingService)
    {
        $this->middleware('role:client');
        $this->meetingService = $meetingService;
    }

    public function index()
    {
        $client = $this->getAuthenticatedClient();
        if (!$client) {
            abort(403, 'Unauthorized access.');
        }

        $meetingSchedule = $this->meetingService->getClientMeetingSchedule($client);

        return inertia('Client/Meetings/Index', [
            'meetingSchedule' => $meetingSchedule,
            'client' => $client,
        ]);
    }

    public function show(Meeting $meeting)
    {
        $client = $this->getAuthenticatedClient();
        if (!$client || $meeting->client_id !== $client->id) {
            abort(403, 'Unauthorized access to meeting.');
        }

        $meeting->load(['transaction.property', 'broker', 'createdBy']);

        return inertia('Client/Meetings/Show', [
            'meeting' => $meeting,
            'transaction' => $meeting->transaction,
        ]);
    }

    public function store(Request $request, Transaction $transaction)
    {
        $client = $this->getAuthenticatedClient();
        if (!$client || $transaction->client_id !== $client->id) {
            abort(403, 'Unauthorized access to transaction.');
        }

        $request->validate([
            'type' => 'required|string|in:property_viewing,contract_review,closing_meeting,consultation,other',
            'scheduled_at' => 'required|date|after:now',
            'location' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'reminder_minutes' => 'nullable|integer|min:15|max:10080', // 1 week max
        ]);

        try {
            $result = $this->meetingService->scheduleMeeting(
                $transaction,
                $request->type,
                Carbon::parse($request->scheduled_at),
                $request->location,
                $request->notes,
                [],
                $request->reminder_minutes ?? 60
            );

            if ($result['success']) {
                return back()->with('success', 'Meeting scheduled successfully.');
            } else {
                return back()->with('error', $result['error']);
            }

        } catch (\Exception $e) {
            Log::error('Failed to schedule meeting from client request', [
                'transaction_id' => $transaction->id,
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to schedule meeting. Please try again.');
        }
    }

    public function update(Request $request, Meeting $meeting)
    {
        $client = $this->getAuthenticatedClient();
        if (!$client || $meeting->client_id !== $client->id) {
            abort(403, 'Unauthorized access to meeting.');
        }

        $request->validate([
            'scheduled_at' => 'sometimes|date|after:now',
            'location' => 'sometimes|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'reminder_minutes' => 'nullable|integer|min:15|max:10080',
        ]);

        try {
            $updateData = $request->only(['scheduled_at', 'location', 'notes', 'reminder_minutes']);
            
            // Parse scheduled_at if provided
            if (isset($updateData['scheduled_at'])) {
                $updateData['scheduled_at'] = Carbon::parse($updateData['scheduled_at']);
            }

            $result = $this->meetingService->updateMeeting($meeting, $updateData);

            if ($result['success']) {
                return back()->with('success', 'Meeting updated successfully.');
            } else {
                return back()->with('error', $result['error']);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update meeting from client request', [
                'meeting_id' => $meeting->id,
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update meeting. Please try again.');
        }
    }

    public function cancel(Request $request, Meeting $meeting)
    {
        $client = $this->getAuthenticatedClient();
        if (!$client || $meeting->client_id !== $client->id) {
            abort(403, 'Unauthorized access to meeting.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $result = $this->meetingService->cancelMeeting($meeting, $request->reason);

            if ($result['success']) {
                return back()->with('success', 'Meeting cancelled successfully.');
            } else {
                return back()->with('error', $result['error']);
            }

        } catch (\Exception $e) {
            Log::error('Failed to cancel meeting from client request', [
                'meeting_id' => $meeting->id,
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to cancel meeting. Please try again.');
        }
    }

    public function getTransactionMeetings(Transaction $transaction)
    {
        $client = $this->getAuthenticatedClient();
        if (!$client || $transaction->client_id !== $client->id) {
            abort(403, 'Unauthorized access to transaction meetings.');
        }

        $meetings = $this->meetingService->getTransactionMeetings($transaction);

        return response()->json([
            'success' => true,
            'meetings' => $meetings,
        ]);
    }

    protected function getAuthenticatedClient(): ?Client
    {
        $user = auth()->user();
        return $user ? $user->client : null;
    }
}
