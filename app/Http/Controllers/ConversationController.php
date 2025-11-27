<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Inquiry;
use App\Models\Transaction;
use App\Events\MessageSent;
use App\Notifications\MessageNotification;
use App\Http\Requests\SendMessageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConversationController extends Controller
{
    /**
     * Display a listing of conversations for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $conversations = Conversation::forUser($user->id)
            ->active()
            ->with([
                'latestMessage.sender',
                'inquiry.property',
                'transaction.property',
                'participantUsers'
            ])
            ->withCount([
                'messages as unread_count' => function ($q) use ($user) {
                    $q->where('sender_id', '!=', $user->id)
                      ->whereNull('read_at');
                }
            ])
            ->orderBy('last_message_at', 'desc')
            ->paginate(20);

        return Inertia::render('Messages/Index', [
            'conversations' => $conversations,
            'totalUnread' => $user->getUnreadMessagesCount(),
        ]);
    }

    /**
     * Display a specific conversation
     */
    public function show(Conversation $conversation)
    {
        $user = Auth::user();
        
        // Check if user is a participant
        if (!$conversation->hasParticipant($user->id)) {
            abort(403, 'You are not authorized to view this conversation.');
        }

        // Load messages with sender information
        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        // Mark messages as read for current user
        $conversation->markAsReadForUser($user->id);

        // Load related data
        $conversation->load([
            'inquiry.property',
            'transaction.property.broker',
            'transaction.client',
            'participantUsers'
        ]);

        return Inertia::render('Messages/Show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'currentUser' => $user,
        ]);
    }

    /**
     * Send a new message in a conversation
     */
    public function sendMessage(SendMessageRequest $request, Conversation $conversation)
    {
        $user = Auth::user();
        
        // Check if user is a participant
        if (!$conversation->hasParticipant($user->id)) {
            abort(403, 'You are not authorized to send messages in this conversation.');
        }

        // Ensure dynamic participants (e.g., client registered after inquiry) are present before dispatch
        $conversation->ensureDynamicParticipants();

        $validated = $request->validated();

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('message-attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        $sanitizedContent = isset($validated['content']) ? strip_tags($validated['content']) : '';

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'content' => $sanitizedContent,
            'type' => !empty($attachments) ? 'file' : 'text',
            'attachments' => $attachments,
        ]);

        $message->load('sender');

        // Broadcast the message
        \Log::info('Broadcasting MessageSent event', [
            'message_id' => $message->id,
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'content_preview' => substr($message->content, 0, 50)
        ]);
        
        broadcast(new MessageSent($message))->toOthers();

        // Send notifications to other participants
        $otherParticipants = $conversation->participants()->where('users.id', '!=', $user->id)->get();
        
        foreach ($otherParticipants as $participant) {
            $participant->notify(new MessageNotification($message));
        }

        // Return JSON for API requests, redirect for web requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message->load(['conversation', 'sender']),
                'success' => true
            ]);
        }

        return back()->with('success', 'Message sent successfully.');
    }

    /**
     * Create a conversation for an inquiry
     */
    public function createForInquiry(Request $request, Inquiry $inquiry)
    {
        $user = Auth::user();
        
        // Load property relationship to prevent N+1 query
        $inquiry->load('property:id,title,broker_id');
        
        // Check if user is authorized (broker of the property or the inquirer)
        $property = $inquiry->property;
        $inquirerUserId = $inquiry->client?->user_id ?? $inquiry->user_id;
        if ($user->id !== $property->broker_id && $user->id !== $inquirerUserId) {
            abort(403, 'You are not authorized to create a conversation for this inquiry.');
        }

        // Check if conversation already exists
        $existingConversation = $inquiry->conversation;
        if ($existingConversation) {
            return redirect()->route('conversations.show', $existingConversation);
        }

        // Create new conversation
        $conversation = Conversation::createForInquiry($inquiry);
        
        // Create initial system message
    $propertyTitle = ($property->title) ?? 'Unknown Property';
        Message::createSystemMessage(
            $conversation->id,
            "Conversation started for inquiry about {$propertyTitle}",
            ['inquiry_id' => $inquiry->id]
        );

        return redirect()->route('conversations.show', $conversation);
    }

    /**
     * Create a conversation for a transaction
     */
    public function createForTransaction(Request $request, Transaction $transaction)
    {
        $user = Auth::user();
        
        // Load property relationship to prevent N+1 query
        $transaction->load('property:id,title,broker_id');
        
        // Check if user is authorized (broker or client in the transaction)
        if ($user->id !== $transaction->broker_id && $user->id !== $transaction->client_id) {
            abort(403, 'You are not authorized to create a conversation for this transaction.');
        }

        // Check if conversation already exists
        $existingConversation = $transaction->conversation;
        if ($existingConversation) {
            return redirect()->route('conversations.show', $existingConversation);
        }

        // Create new conversation
        $conversation = Conversation::createForTransaction($transaction);
        
        // Create initial system message
    $propertyTitle = ($transaction->property?->title) ?? 'Unknown Property';
        Message::createSystemMessage(
            $conversation->id,
            "Conversation started for transaction: {$propertyTitle}",
            ['transaction_id' => $transaction->id]
        );

        return redirect()->route('conversations.show', $conversation);
    }

    /**
     * Archive a conversation
     */
    public function archive(Conversation $conversation)
    {
        $user = Auth::user();
        
        if (!$conversation->hasParticipant($user->id)) {
            abort(403, 'You are not authorized to archive this conversation.');
        }

        $conversation->update(['is_archived' => true]);

        return back()->with('success', 'Conversation archived successfully.');
    }

    /**
     * Unarchive a conversation
     */
    public function unarchive(Conversation $conversation)
    {
        $user = Auth::user();
        
        if (!$conversation->hasParticipant($user->id)) {
            abort(403, 'You are not authorized to unarchive this conversation.');
        }

        $conversation->update(['is_archived' => false]);

        return back()->with('success', 'Conversation unarchived successfully.');
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead(Request $request, Conversation $conversation)
    {
        $user = Auth::user();
        
        if (!$conversation->hasParticipant($user->id)) {
            abort(403);
        }

        $conversation->markAsReadForUser($user->id);

        // Return JSON for API requests, redirect for web requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Conversation marked as read.');
    }
}