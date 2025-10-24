<?php

namespace Tests\Unit\Models;

use App\Models\Conversation;
use App\Models\Inquiry;
use App\Models\Message;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_conversation_has_correct_fillable_attributes(): void
    {
        $fillable = [
            'title',
            'type',
            'inquiry_id',
            'transaction_id',
            'participants',
            'last_message_at',
            'is_archived'
        ];

        $conversation = new Conversation();
        $this->assertEquals($fillable, $conversation->getFillable());
    }

    public function test_conversation_has_correct_casts(): void
    {
        $expectedCasts = [
            'id' => 'int',
            'participants' => 'array',
            'is_archived' => 'boolean',
            'last_message_at' => 'datetime'
        ];

        $conversation = new Conversation();
        $casts = $conversation->getCasts();

        foreach ($expectedCasts as $attribute => $expectedCast) {
            $this->assertArrayHasKey($attribute, $casts);
            $this->assertEquals($expectedCast, $casts[$attribute]);
        }
    }

    public function test_conversation_belongs_to_inquiry(): void
     {
         $inquiry = Inquiry::factory()->create();
         $conversation = Conversation::factory()->create(['inquiry_id' => $inquiry->id]);
 
         $this->assertInstanceOf(Inquiry::class, $conversation->inquiry);
         $this->assertEquals($inquiry->id, $conversation->inquiry->id);
     }
 
     public function test_conversation_belongs_to_transaction(): void
     {
         $transaction = Transaction::factory()->create();
         $conversation = Conversation::factory()->create(['transaction_id' => $transaction->id]);
  
         $this->assertInstanceOf(Transaction::class, $conversation->transaction);
         $this->assertEquals($transaction->id, $conversation->transaction->id);
     }

    public function test_conversation_has_many_messages(): void
    {
        $conversation = Conversation::factory()->create();
        $messages = Message::factory()->count(3)->create(['conversation_id' => $conversation->id]);

        $this->assertCount(3, $conversation->messages);
        foreach ($conversation->messages as $message) {
            $this->assertInstanceOf(Message::class, $message);
            $this->assertEquals($conversation->id, $message->conversation_id);
        }
    }

    public function test_conversation_can_be_created_with_required_attributes(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $conversationData = [
            'title' => 'Inquiry about Property Listing',
            'type' => 'inquiry',
            'participants' => [$user1->id, $user2->id]
        ];

        $conversation = Conversation::create($conversationData);

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals('Inquiry about Property Listing', $conversation->title);
        $this->assertEquals('inquiry', $conversation->type);
        $this->assertEquals([$user1->id, $user2->id], $conversation->participants);
    }

    public function test_conversation_can_be_created_with_all_attributes(): void
    {
        $inquiry = Inquiry::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $conversationData = [
            'title' => 'Property Purchase Discussion',
            'type' => 'inquiry',
            'inquiry_id' => $inquiry->id,
            'participants' => [$user1->id, $user2->id],
            'last_message_at' => now(),
            'is_archived' => false
        ];

        $conversation = Conversation::create($conversationData);

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals('Property Purchase Discussion', $conversation->title);
        $this->assertEquals('inquiry', $conversation->type);
        $this->assertEquals($inquiry->id, $conversation->inquiry_id);
        $this->assertEquals([$user1->id, $user2->id], $conversation->participants);
        $this->assertNotNull($conversation->last_message_at);
        $this->assertFalse($conversation->is_archived);
    }

    public function test_conversation_factory_creates_valid_conversation(): void
    {
        $conversation = Conversation::factory()->create();

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertNotEmpty($conversation->title);
        $this->assertContains($conversation->type, ['inquiry', 'transaction', 'general']);
        $this->assertIsArray($conversation->participants);
        $this->assertIsBool($conversation->is_archived);
    }

    public function test_conversation_can_add_participant(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        
        $conversation = Conversation::factory()->create([
            'participants' => [$user1->id, $user2->id]
        ]);

        $conversation->addParticipant($user3->id);

        $this->assertContains($user3->id, $conversation->participants);
    }

    public function test_conversation_can_remove_participant(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        
        $conversation = Conversation::factory()->create([
            'participants' => [$user1->id, $user2->id, $user3->id]
        ]);

        $conversation->removeParticipant($user3->id);

        $this->assertNotContains($user3->id, $conversation->participants);
    }

    public function test_conversation_can_check_participant(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        
        $conversation = Conversation::factory()->create([
            'participants' => [$user1->id, $user2->id]
        ]);

        $this->assertTrue($conversation->hasParticipant($user1->id));
        $this->assertFalse($conversation->hasParticipant($user3->id));
    }

    public function test_conversation_scope_active(): void
    {
        Conversation::factory()->count(2)->create(['is_archived' => false]);
        Conversation::factory()->count(3)->create(['is_archived' => true]);

        $activeConversations = Conversation::active()->get();
        
        $this->assertCount(2, $activeConversations);
        foreach ($activeConversations as $conversation) {
            $this->assertFalse($conversation->is_archived);
        }
    }

    public function test_conversation_scope_for_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        
        Conversation::factory()->create(['participants' => [$user1->id, $user2->id]]);
        Conversation::factory()->create(['participants' => [$user1->id, $user3->id]]);
        Conversation::factory()->create(['participants' => [$user2->id, $user3->id]]);

        $user1Conversations = Conversation::forUser($user1->id)->get();
        
        $this->assertCount(2, $user1Conversations);
        foreach ($user1Conversations as $conversation) {
            $this->assertContains($user1->id, $conversation->participants);
        }
    }

    public function test_conversation_can_get_latest_message(): void
    {
        $conversation = Conversation::factory()->create();
        
        $message1 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'created_at' => now()->subHours(2)
        ]);
        
        $message2 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'created_at' => now()->subHour()
        ]);
        
        $message3 = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'created_at' => now()
        ]);
        
        $latestMessage = $conversation->latestMessage;
        
        $this->assertInstanceOf(Message::class, $latestMessage);
        $this->assertEquals($message3->id, $latestMessage->id);
    }

    public function test_conversation_can_get_unread_count_for_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $conversation = Conversation::factory()->create();
        
        // Create messages from other user (unread by user)
        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $otherUser->id,
            'read_at' => null
        ]);
        
        // Create messages from user (should not count)
        Message::factory()->count(2)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'read_at' => null
        ]);
        
        $unreadCount = $conversation->getUnreadCountForUser($user->id);
        
        $this->assertEquals(3, $unreadCount);
    }

    public function test_conversation_can_mark_all_messages_as_read_for_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $conversation = Conversation::factory()->create();
        
        // Create unread messages from other user
        Message::factory()->count(3)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $otherUser->id,
            'read_at' => null
        ]);
        
        $conversation->markAsReadForUser($user->id);
        
        $unreadMessages = Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
            
        $this->assertEquals(0, $unreadMessages);
    }

    public function test_conversation_can_create_for_inquiry(): void
    {
        $client = \App\Models\Client::factory()->create();
        $broker = User::factory()->create();
        $property = \App\Models\Property::factory()->create(['broker_id' => $broker->id]);
        $inquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'client_id' => $client->id
        ]);

        $conversation = Conversation::createForInquiry($inquiry);

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals($inquiry->id, $conversation->inquiry_id);
        $this->assertEquals('inquiry', $conversation->type);
        $this->assertContains($client->id, $conversation->participants);
        $this->assertContains($broker->id, $conversation->participants);
    }

    public function test_conversation_can_create_for_transaction(): void
    {
        $client = \App\Models\Client::factory()->create();
        $broker = User::factory()->create();
        $property = \App\Models\Property::factory()->create(['broker_id' => $broker->id]);
        $transaction = Transaction::factory()->create([
            'property_id' => $property->id,
            'client_id' => $client->id,
            'broker_id' => $broker->id
        ]);

        $conversation = Conversation::createForTransaction($transaction);

        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals($transaction->id, $conversation->transaction_id);
        $this->assertEquals('transaction', $conversation->type);
        $this->assertContains($client->id, $conversation->participants);
        $this->assertContains($broker->id, $conversation->participants);
    }

    public function test_conversation_validates_required_fields(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Conversation::create([
            'type' => 'general'
            // Missing required title and participants
        ]);
    }

    public function test_conversation_validates_type_enum(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $conversation = Conversation::create([
            'title' => 'Test Conversation',
            'type' => 'general',
            'participants' => [$user1->id, $user2->id]
        ]);

        $this->assertContains($conversation->type, ['inquiry', 'transaction', 'general']);
    }

    public function test_conversation_title_is_required(): void
    {
        $conversation = Conversation::factory()->create();
        
        $this->assertNotEmpty($conversation->title);
    }

    public function test_conversation_can_get_message_count(): void
    {
        $conversation = Conversation::factory()->create();
        Message::factory()->count(5)->create(['conversation_id' => $conversation->id]);
        
        $messageCount = $conversation->getMessageCount();
        
        $this->assertEquals(5, $messageCount);
    }

    public function test_conversation_can_check_if_has_unread_messages_for_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $conversation = Conversation::factory()->create();
        
        // No messages yet
        $this->assertFalse($conversation->hasUnreadMessagesForUser($user->id));
        
        // Add unread message from other user
        Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $otherUser->id,
            'read_at' => null
        ]);
        
        $this->assertTrue($conversation->hasUnreadMessagesForUser($user->id));
    }

    public function test_conversation_soft_deletes(): void
    {
        $conversation = Conversation::factory()->create();
        $conversationId = $conversation->id;
        
        $conversation->delete();
        
        $this->assertSoftDeleted('conversations', ['id' => $conversationId]);
        $this->assertNull(Conversation::find($conversationId));
        $this->assertNotNull(Conversation::withTrashed()->find($conversationId));
    }

    public function test_conversation_can_be_restored(): void
    {
        $conversation = Conversation::factory()->create();
        $conversationId = $conversation->id;
        
        $conversation->delete();
        $this->assertSoftDeleted('conversations', ['id' => $conversationId]);
        
        $conversation->restore();
        $this->assertDatabaseHas('conversations', [
            'id' => $conversationId,
            'deleted_at' => null
        ]);
    }

    public function test_conversation_participant_users_relationship(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $conversation = Conversation::factory()->create([
            'participants' => [$user1->id, $user2->id]
        ]);

        $participantUsers = $conversation->participantUsers;

        $this->assertCount(2, $participantUsers);
        $this->assertTrue($participantUsers->contains('id', $user1->id));
        $this->assertTrue($participantUsers->contains('id', $user2->id));
    }
}