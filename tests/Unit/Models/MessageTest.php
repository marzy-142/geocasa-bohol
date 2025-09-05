<?php

namespace Tests\Unit\Models;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_message_has_correct_fillable_attributes(): void
    {
        $fillable = [
            'conversation_id',
            'sender_id',
            'content',
            'type',
            'attachments',
            'read_at'
        ];

        $message = new Message();
        $this->assertEquals($fillable, $message->getFillable());
    }

    public function test_message_has_correct_casts(): void
    {
        $expectedCasts = [
            'id' => 'int',
            'attachments' => 'array',
            'read_at' => 'datetime'
        ];

        $message = new Message();
        $casts = $message->getCasts();

        foreach ($expectedCasts as $attribute => $expectedCast) {
            $this->assertArrayHasKey($attribute, $casts);
            $this->assertEquals($expectedCast, $casts[$attribute]);
        }
    }

    public function test_message_belongs_to_conversation(): void
    {
        $conversation = Conversation::factory()->create();
        $message = Message::factory()->create(['conversation_id' => $conversation->id]);

        $this->assertInstanceOf(Conversation::class, $message->conversation);
        $this->assertEquals($conversation->id, $message->conversation->id);
    }

    public function test_message_belongs_to_sender(): void
    {
        $sender = User::factory()->create();
        $message = Message::factory()->create(['sender_id' => $sender->id]);

        $this->assertInstanceOf(User::class, $message->sender);
        $this->assertEquals($sender->id, $message->sender->id);
    }

    public function test_message_can_be_created_with_required_attributes(): void
    {
        $conversation = Conversation::factory()->create();
        $sender = User::factory()->create();
        
        $messageData = [
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => 'Hello, I am interested in your property listing.',
            'message_type' => 'text'
        ];

        $message = Message::create($messageData);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('Hello, I am interested in your property listing.', $message->content);
        $this->assertEquals('text', $message->message_type);
        $this->assertEquals($conversation->id, $message->conversation_id);
        $this->assertEquals($sender->id, $message->sender_id);
    }

    public function test_message_can_be_created_with_all_attributes(): void
    {
        $conversation = Conversation::factory()->create();
        $sender = User::factory()->create();
        
        $messageData = [
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => 'Here are the property documents you requested.',
            'type' => 'file',
            'attachments' => ['document1.pdf', 'document2.pdf'],
            'read_at' => null
        ];

        $message = Message::create($messageData);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('Here are the property documents you requested.', $message->content);
        $this->assertEquals('file', $message->type);
        $this->assertEquals(['document1.pdf', 'document2.pdf'], $message->attachments);
        $this->assertNull($message->read_at);
    }

    public function test_message_factory_creates_valid_message(): void
    {
        $message = Message::factory()->create();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertNotNull($message->conversation_id);
        $this->assertNotNull($message->sender_id);
        $this->assertNotEmpty($message->content);
        $this->assertContains($message->type, ['text', 'image', 'file', 'system']);
    }

    public function test_message_can_be_marked_as_read(): void
    {
        $message = Message::factory()->create([
            'read_at' => null
        ]);
        
        $message->markAsRead();
        
        $this->assertNotNull($message->read_at);
        
        $this->assertDatabaseHas('messages', [
            'id' => $message->id
        ]);
        $this->assertDatabaseMissing('messages', [
            'id' => $message->id,
            'read_at' => null
        ]);
    }

    public function test_message_can_be_marked_as_unread(): void
    {
        $message = Message::factory()->create([
            'read_at' => now()
        ]);
        
        $message->markAsUnread();
        
        $this->assertNull($message->read_at);
        
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'read_at' => null
        ]);
    }

    public function test_message_scope_unread(): void
    {
        Message::factory()->count(3)->create(['read_at' => null]);
        Message::factory()->count(2)->create(['read_at' => now()]);

        $unreadMessages = Message::unread()->get();
        
        $this->assertCount(3, $unreadMessages);
        foreach ($unreadMessages as $message) {
            $this->assertNull($message->read_at);
        }
    }

    public function test_message_scope_read(): void
    {
        Message::factory()->count(2)->create(['read_at' => now()]);
        Message::factory()->count(3)->create(['read_at' => null]);

        $readMessages = Message::read()->get();
        
        $this->assertCount(2, $readMessages);
        foreach ($readMessages as $message) {
            $this->assertNotNull($message->read_at);
        }
    }

    public function test_message_scope_by_type(): void
    {
        Message::factory()->count(3)->create(['type' => 'text']);
        Message::factory()->count(2)->create(['type' => 'image']);
        Message::factory()->count(1)->create(['type' => 'file']);

        $textMessages = Message::byType('text')->get();
        
        $this->assertCount(3, $textMessages);
        foreach ($textMessages as $message) {
            $this->assertEquals('text', $message->type);
        }
    }

    public function test_message_scope_for_conversation(): void
    {
        $conversation1 = Conversation::factory()->create();
        $conversation2 = Conversation::factory()->create();
        
        Message::factory()->count(4)->create(['conversation_id' => $conversation1->id]);
        Message::factory()->count(2)->create(['conversation_id' => $conversation2->id]);

        $conversation1Messages = Message::forConversation($conversation1->id)->get();
        
        $this->assertCount(4, $conversation1Messages);
        foreach ($conversation1Messages as $message) {
            $this->assertEquals($conversation1->id, $message->conversation_id);
        }
    }

    public function test_message_scope_from_sender(): void
    {
        $sender1 = User::factory()->create();
        $sender2 = User::factory()->create();
        
        Message::factory()->count(3)->create(['sender_id' => $sender1->id]);
        Message::factory()->count(2)->create(['sender_id' => $sender2->id]);

        $sender1Messages = Message::fromSender($sender1->id)->get();
        
        $this->assertCount(3, $sender1Messages);
        foreach ($sender1Messages as $message) {
            $this->assertEquals($sender1->id, $message->sender_id);
        }
    }

    public function test_message_can_check_if_has_attachments(): void
    {
        $messageWithAttachments = Message::factory()->create([
            'attachments' => ['file1.pdf', 'file2.jpg']
        ]);
        
        $messageWithoutAttachments = Message::factory()->create([
            'attachments' => []
        ]);
        
        $this->assertTrue($messageWithAttachments->hasAttachments());
        $this->assertFalse($messageWithoutAttachments->hasAttachments());
    }

    public function test_message_can_get_attachment_count(): void
    {
        $message = Message::factory()->create([
            'attachments' => ['file1.pdf', 'file2.jpg', 'file3.png']
        ]);
        
        $this->assertEquals(3, $message->getAttachmentCount());
    }

    public function test_message_can_add_attachment(): void
    {
        $message = Message::factory()->create([
            'attachments' => ['existing_file.pdf']
        ]);
        
        $message->addAttachment('new_file.jpg');
        
        $this->assertContains('new_file.jpg', $message->attachments);
        $this->assertContains('existing_file.pdf', $message->attachments);
        $this->assertCount(2, $message->attachments);
        
        $this->assertDatabaseHas('messages', [
            'id' => $message->id
        ]);
    }

    public function test_message_can_remove_attachment(): void
    {
        $message = Message::factory()->create([
            'attachments' => ['file1.pdf', 'file2.jpg', 'file3.png']
        ]);
        
        $message->removeAttachment('file2.jpg');
        
        $this->assertNotContains('file2.jpg', $message->attachments);
        $this->assertContains('file1.pdf', $message->attachments);
        $this->assertContains('file3.png', $message->attachments);
        $this->assertCount(2, $message->attachments);
    }

    public function test_message_default_values(): void
    {
        $conversation = Conversation::factory()->create();
        $sender = User::factory()->create();
        
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => 'Test message',
            'message_type' => 'text'
        ]);

        $this->assertNull($message->read_at);
        $this->assertEquals([], $message->attachments);
    }

    public function test_message_validates_message_type(): void
    {
        $validTypes = ['text', 'image', 'file', 'system'];
        
        foreach ($validTypes as $type) {
            $message = Message::factory()->create(['type' => $type]);
            $this->assertEquals($type, $message->type);
        }
    }

    public function test_message_content_is_required(): void
    {
        $message = Message::factory()->create();
        
        $this->assertNotEmpty($message->content);
    }

    public function test_message_can_check_if_sent_by_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $message = Message::factory()->create(['sender_id' => $user->id]);
        
        $this->assertTrue($message->isSentBy($user->id));
        $this->assertFalse($message->isSentBy($otherUser->id));
    }

    public function test_message_can_get_formatted_content(): void
    {
        $message = Message::factory()->create([
            'content' => 'Hello, this is a test message with some content.',
            'message_type' => 'text'
        ]);
        
        $formattedContent = $message->getFormattedContent();
        
        $this->assertIsString($formattedContent);
        $this->assertNotEmpty($formattedContent);
    }

    public function test_message_can_get_time_ago(): void
    {
        $message = Message::factory()->create([
            'created_at' => now()->subMinutes(30)
        ]);
        
        $timeAgo = $message->getTimeAgo();
        
        $this->assertIsString($timeAgo);
        $this->assertStringContainsString('30', $timeAgo);
    }

    public function test_message_soft_deletes(): void
    {
        $message = Message::factory()->create();
        $messageId = $message->id;
        
        $message->delete();
        
        $this->assertSoftDeleted('messages', ['id' => $messageId]);
        $this->assertNull(Message::find($messageId));
        $this->assertNotNull(Message::withTrashed()->find($messageId));
    }

    public function test_message_can_be_restored(): void
    {
        $message = Message::factory()->create();
        $messageId = $message->id;
        
        $message->delete();
        $this->assertSoftDeleted('messages', ['id' => $messageId]);
        
        $message->restore();
        $this->assertDatabaseHas('messages', [
            'id' => $messageId,
            'deleted_at' => null
        ]);
    }

    public function test_message_can_search_content(): void
    {
        Message::factory()->create(['content' => 'Looking for a beach house property']);
        Message::factory()->create(['content' => 'Mountain view condo available']);
        Message::factory()->create(['content' => 'Interested in beachfront lots']);
        Message::factory()->create(['content' => 'City center apartment for rent']);

        $searchResults = Message::searchContent('beach')->get();
        
        $this->assertCount(2, $searchResults);
        foreach ($searchResults as $message) {
            $this->assertStringContainsStringIgnoringCase('beach', $message->content);
        }
    }

    public function test_message_can_get_recent_messages(): void
    {
        Message::factory()->count(5)->create(['created_at' => now()->subDays(2)]);
        Message::factory()->count(3)->create(['created_at' => now()->subHours(2)]);

        $recentMessages = Message::recent(24)->get(); // Last 24 hours
        
        $this->assertCount(3, $recentMessages);
        foreach ($recentMessages as $message) {
            $this->assertTrue($message->created_at->greaterThan(now()->subDay()));
        }
    }
}