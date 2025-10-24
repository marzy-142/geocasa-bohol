# 🧪 Messaging System Testing Guide

## Complete Testing Strategy for GeoCasa Bohol Messaging System

**Last Updated**: October 21, 2025  
**Status**: Production Testing Guide

---

## 📋 Table of Contents

1. [Manual Testing](#manual-testing)
2. [Automated Testing](#automated-testing)
3. [Test Scenarios](#test-scenarios)
4. [Database Verification](#database-verification)
5. [Performance Testing](#performance-testing)
6. [Security Testing](#security-testing)

---

## 🧑‍💻 Manual Testing

### **Prerequisites**

Before testing, ensure you have:
- ✅ At least 2 user accounts (1 client, 1 broker)
- ✅ At least 1 property in the database
- ✅ Browser with dev tools open (F12)
- ✅ Multiple browser windows/incognito for multi-user testing

### **Setup Test Users**

```bash
# Run this to create test users if needed
php artisan tinker
```

```php
// Create test client
$client = User::create([
    'name' => 'Test Client',
    'email' => 'client@test.com',
    'password' => bcrypt('password'),
    'role' => 'client',
    'is_approved' => true,
]);

// Create test broker
$broker = User::create([
    'name' => 'Test Broker',
    'email' => 'broker@test.com',
    'password' => bcrypt('password'),
    'role' => 'broker',
    'is_approved' => true,
]);

// Create client record
Client::create([
    'user_id' => $client->id,
    'name' => 'Test Client',
    'email' => 'client@test.com',
    'broker_id' => $broker->id,
]);
```

---

## 🎯 Test Scenarios

### **Scenario 1: Create Inquiry and Start Conversation**

**Steps**:
1. Login as **Client** (`client@test.com`)
2. Go to **Properties** page
3. Click **"Inquire"** on any property
4. Fill out inquiry form:
   - Message: "I'm interested in this property"
   - Inquiry type: "General Inquiry"
5. Click **"Submit Inquiry"**

**Expected Results**:
- ✅ Inquiry created successfully
- ✅ Redirected to inquiry details page
- ✅ Conversation automatically created
- ✅ Conversation type: `inquiry`
- ✅ Lifecycle stage: `inquiry`
- ✅ Participants: Client + Broker

**Verify in Database**:
```sql
-- Check inquiry
SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 1;

-- Check conversation
SELECT * FROM conversations ORDER BY created_at DESC LIMIT 1;

-- Check participants
SELECT * FROM conversation_participants WHERE conversation_id = [conversation_id];
```

---

### **Scenario 2: Send Messages in Conversation**

**Steps**:
1. Still logged in as **Client**
2. Go to **Conversations** page
3. Click on the conversation you just created
4. Type a message: "What's the price?"
5. Click **"Send"**
6. Wait 2 seconds
7. Send another message: "Can I schedule a viewing?"

**Expected Results**:
- ✅ Messages appear immediately in chat
- ✅ Messages show correct sender
- ✅ Timestamps are accurate
- ✅ Message count updates
- ✅ "Last message" updates in conversation list

**Verify in Database**:
```sql
-- Check messages
SELECT * FROM messages 
WHERE conversation_id = [conversation_id] 
ORDER BY created_at DESC;

-- Should see:
-- - sender_id = client's user_id
-- - type = 'text'
-- - content = your messages
```

---

### **Scenario 3: Broker Receives and Replies**

**Steps**:
1. Open **new browser window** (or incognito)
2. Login as **Broker** (`broker@test.com`)
3. Go to **Inquiries** page
4. Click on the inquiry from Test Client
5. Click **"View Conversation"** or go to Conversations
6. You should see the client's messages
7. Reply: "The price is ₱2,500,000. When would you like to view?"
8. Click **"Send"**

**Expected Results**:
- ✅ Broker sees all client messages
- ✅ Broker's reply appears immediately
- ✅ Conversation shows in broker's conversation list
- ✅ Unread count updates for client

**Multi-Window Test**:
- Keep both browser windows open
- Send message from Client → Should appear in Broker window (may need refresh)
- Send message from Broker → Should appear in Client window (may need refresh)

---

### **Scenario 4: Accept Inquiry (Transition to Transaction)**

**Steps**:
1. As **Broker**, go to **Inquiries** page
2. Click on the test inquiry
3. Click **"Accept Inquiry"** button
4. Confirm the action

**Expected Results**:
- ✅ Transaction created with unique number (TXN-XXXXXXXXXX)
- ✅ Inquiry status → `in transaction`
- ✅ Conversation type → `transaction`
- ✅ Conversation lifecycle_stage → `transaction`
- ✅ System message added: "🎉 Inquiry accepted! Transaction #TXN-XXX has been created."
- ✅ Conversation metadata updated with transaction details

**Verify in Database**:
```sql
-- Check transaction
SELECT * FROM transactions ORDER BY created_at DESC LIMIT 1;

-- Check inquiry status
SELECT id, status FROM inquiries WHERE id = [inquiry_id];

-- Check conversation transition
SELECT id, type, lifecycle_stage, transaction_id, metadata 
FROM conversations WHERE id = [conversation_id];

-- Check system message
SELECT * FROM messages 
WHERE conversation_id = [conversation_id] 
AND type = 'system' 
ORDER BY created_at DESC LIMIT 1;
```

---

### **Scenario 5: Continue Messaging After Transition**

**Steps**:
1. As **Client**, refresh the conversation
2. You should see the system message about transaction
3. Send message: "Great! What are the next steps?"
4. As **Broker**, reply: "Please prepare your documents..."

**Expected Results**:
- ✅ System message is visible and styled differently
- ✅ Can continue messaging after transition
- ✅ All messages linked to transaction
- ✅ Conversation context maintained

---

### **Scenario 6: Rate Limiting Test**

**Steps**:
1. As **Client**, go to any conversation
2. Send 10 messages rapidly (as fast as possible)
3. Try to send the 11th message

**Expected Results**:
- ✅ First 10 messages send successfully
- ✅ 11th message blocked with error
- ✅ Error message: "Too many messages. Please wait."
- ✅ Rate limit: 10 messages per minute

**Verify**:
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Should see rate limit hits
```

---

### **Scenario 7: Spam Detection Test**

**Steps**:
1. Try to send message with spam keywords:
   - "Buy VIAGRA now!"
   - "Click here: http://spam.com http://bad.com http://evil.com http://more.com"
   - "HELLO THIS IS ALL CAPS MESSAGE!!!"
   - "Aaaaaaaaaaaaa" (repeated characters)

**Expected Results**:
- ✅ Messages with spam keywords rejected
- ✅ Messages with too many links rejected
- ✅ Messages with excessive caps rejected
- ✅ Messages with repeated characters rejected
- ✅ Error message shown to user

---

### **Scenario 8: File Attachments**

**Steps**:
1. In conversation, click **"Attach File"** (if implemented)
2. Select a file (image, PDF, etc.)
3. Send message with attachment

**Expected Results**:
- ✅ File uploads successfully
- ✅ File stored in `storage/app/public/message-attachments`
- ✅ Attachment appears in message
- ✅ Recipient can download/view attachment
- ✅ File size limit enforced (10MB)
- ✅ Max 5 files per message

---

## 🤖 Automated Testing

### **Create Feature Test**

```bash
php artisan make:test MessagingSystemTest
```

Edit `tests/Feature/MessagingSystemTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MessagingSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $client;
    protected $broker;
    protected $clientRecord;
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
        ]);

        $this->client = User::factory()->create([
            'role' => 'client',
            'is_approved' => true,
        ]);

        // Create client record
        $this->clientRecord = Client::factory()->create([
            'user_id' => $this->client->id,
            'broker_id' => $this->broker->id,
        ]);

        // Create property
        $this->property = Property::factory()->create([
            'status' => 'available',
        ]);
    }

    /** @test */
    public function client_can_create_inquiry_and_conversation()
    {
        $response = $this->actingAs($this->client)
            ->post(route('client.inquiries.store'), [
                'property_id' => $this->property->id,
                'message' => 'I am interested in this property',
                'inquiry_type' => 'general',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('inquiries', [
            'property_id' => $this->property->id,
            'client_id' => $this->clientRecord->id,
            'status' => 'new',
        ]);

        $inquiry = Inquiry::latest()->first();
        $this->assertNotNull($inquiry->conversation);
        $this->assertEquals('inquiry', $inquiry->conversation->type);
    }

    /** @test */
    public function client_can_send_message_in_conversation()
    {
        $inquiry = Inquiry::factory()->create([
            'client_id' => $this->clientRecord->id,
            'property_id' => $this->property->id,
        ]);

        $conversation = Conversation::createForInquiry($inquiry);

        $response = $this->actingAs($this->client)
            ->post(route('conversations.messages.store', $conversation), [
                'content' => 'What is the price?',
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $this->client->id,
            'content' => 'What is the price?',
            'type' => 'text',
        ]);
    }

    /** @test */
    public function broker_can_accept_inquiry_and_create_transaction()
    {
        $inquiry = Inquiry::factory()->create([
            'client_id' => $this->clientRecord->id,
            'property_id' => $this->property->id,
            'assigned_broker_id' => $this->broker->id,
        ]);

        $conversation = Conversation::createForInquiry($inquiry);

        $response = $this->actingAs($this->broker)
            ->post(route('inquiries.accept', $inquiry));

        $response->assertRedirect();

        // Check inquiry status
        $this->assertDatabaseHas('inquiries', [
            'id' => $inquiry->id,
            'status' => 'in transaction',
        ]);

        // Check transaction created
        $this->assertDatabaseHas('transactions', [
            'inquiry_id' => $inquiry->id,
            'status' => 'initial_contact',
        ]);

        // Check conversation transitioned
        $conversation->refresh();
        $this->assertEquals('transaction', $conversation->type);
        $this->assertEquals('transaction', $conversation->lifecycle_stage);
        $this->assertNotNull($conversation->transaction_id);

        // Check system message
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'type' => 'system',
        ]);
    }

    /** @test */
    public function rate_limiting_blocks_excessive_messages()
    {
        $inquiry = Inquiry::factory()->create([
            'client_id' => $this->clientRecord->id,
        ]);

        $conversation = Conversation::createForInquiry($inquiry);

        // Send 10 messages (should succeed)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->actingAs($this->client)
                ->post(route('conversations.messages.store', $conversation), [
                    'content' => "Message $i",
                ]);
            $response->assertStatus(302);
        }

        // 11th message should be rate limited
        $response = $this->actingAs($this->client)
            ->post(route('conversations.messages.store', $conversation), [
                'content' => 'Message 11',
            ]);

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function spam_detection_blocks_spam_messages()
    {
        $inquiry = Inquiry::factory()->create([
            'client_id' => $this->clientRecord->id,
        ]);

        $conversation = Conversation::createForInquiry($inquiry);

        $spamMessages = [
            'Buy VIAGRA now!',
            'Click http://a.com http://b.com http://c.com http://d.com',
            'HELLO THIS IS ALL CAPS!!!',
            'Aaaaaaaaaaaaa',
        ];

        foreach ($spamMessages as $spam) {
            $response = $this->actingAs($this->client)
                ->post(route('conversations.messages.store', $conversation), [
                    'content' => $spam,
                ]);

            $response->assertSessionHasErrors();
        }
    }

    /** @test */
    public function conversation_metadata_tracks_transaction_details()
    {
        $inquiry = Inquiry::factory()->create([
            'client_id' => $this->clientRecord->id,
            'assigned_broker_id' => $this->broker->id,
        ]);

        $conversation = Conversation::createForInquiry($inquiry);

        $this->actingAs($this->broker)
            ->post(route('inquiries.accept', $inquiry));

        $conversation->refresh();
        $metadata = $conversation->metadata;

        $this->assertArrayHasKey('transaction_number', $metadata);
        $this->assertArrayHasKey('original_inquiry_id', $metadata);
        $this->assertArrayHasKey('transitioned_at', $metadata);
        $this->assertEquals($inquiry->id, $metadata['original_inquiry_id']);
    }
}
```

### **Run Tests**

```bash
# Run all messaging tests
php artisan test --filter=MessagingSystemTest

# Run specific test
php artisan test --filter=client_can_send_message_in_conversation

# Run with coverage
php artisan test --coverage
```

---

## 🗄️ Database Verification

### **Quick Database Checks**

```sql
-- 1. Check conversation structure
SELECT 
    c.id,
    c.title,
    c.type,
    c.lifecycle_stage,
    c.transaction_id,
    COUNT(m.id) as message_count
FROM conversations c
LEFT JOIN messages m ON c.id = m.conversation_id
GROUP BY c.id
ORDER BY c.created_at DESC;

-- 2. Check message flow
SELECT 
    m.id,
    m.conversation_id,
    u.name as sender,
    m.type,
    LEFT(m.content, 50) as content_preview,
    m.created_at
FROM messages m
LEFT JOIN users u ON m.sender_id = u.id
WHERE m.conversation_id = [conversation_id]
ORDER BY m.created_at ASC;

-- 3. Check inquiry-to-transaction flow
SELECT 
    i.id as inquiry_id,
    i.status as inquiry_status,
    c.id as conversation_id,
    c.type as conv_type,
    c.lifecycle_stage,
    t.id as transaction_id,
    t.transaction_number
FROM inquiries i
LEFT JOIN conversations c ON c.inquiry_id = i.id
LEFT JOIN transactions t ON t.inquiry_id = i.id
WHERE i.id = [inquiry_id];

-- 4. Check system messages
SELECT 
    id,
    conversation_id,
    content,
    metadata,
    created_at
FROM messages
WHERE type = 'system'
ORDER BY created_at DESC;

-- 5. Check participants
SELECT 
    c.id as conversation_id,
    c.title,
    u.name as participant,
    u.role
FROM conversations c
JOIN conversation_participants cp ON c.id = cp.conversation_id
JOIN users u ON cp.user_id = u.id
ORDER BY c.id, u.role;
```

---

## ⚡ Performance Testing

### **Test Message Load Time**

```javascript
// In browser console
console.time('Load Messages');
// Navigate to conversation
console.timeEnd('Load Messages');
// Should be < 500ms
```

### **Test Concurrent Users**

Use Apache Bench or similar:

```bash
# Install Apache Bench
# Windows: Download from Apache website
# Linux: sudo apt-get install apache2-utils

# Test sending messages
ab -n 100 -c 10 -p message.json -T application/json \
   -H "Cookie: laravel_session=YOUR_SESSION" \
   http://127.0.0.1:8000/conversations/1/messages
```

---

## 🔒 Security Testing

### **Test Authorization**

```bash
# Try to access conversation you don't own
# Should get 403 Forbidden
```

### **Test Input Validation**

```bash
# Try to send empty message
# Try to send very long message (> 5000 chars)
# Try to upload huge file (> 10MB)
# Try SQL injection in message content
```

### **Test CSRF Protection**

```bash
# Try to send message without CSRF token
# Should get 419 error
```

---

## ✅ Testing Checklist

### **Basic Functionality**
- [ ] Create inquiry → Conversation created
- [ ] Send message → Message appears
- [ ] Receive message → Message visible
- [ ] Accept inquiry → Transaction created
- [ ] System message → Appears correctly
- [ ] Conversation list → Shows all conversations
- [ ] Unread count → Updates correctly

### **Security**
- [ ] Rate limiting → Blocks after 10 messages/min
- [ ] Spam detection → Blocks spam content
- [ ] Authorization → Can't access others' conversations
- [ ] CSRF protection → Requires valid token
- [ ] File upload → Size/type validation

### **Data Integrity**
- [ ] Inquiry status → Updates correctly
- [ ] Conversation type → Transitions properly
- [ ] Metadata → Tracks transaction details
- [ ] Participants → Correct users linked
- [ ] Timestamps → Accurate

### **User Experience**
- [ ] Messages → Load quickly (< 500ms)
- [ ] UI → Responsive and intuitive
- [ ] Errors → Clear error messages
- [ ] Notifications → Work correctly (if implemented)

---

## 🐛 Common Issues & Solutions

### **Issue: Messages not appearing**
**Solution**: 
- Check browser console for errors
- Verify conversation_id is correct
- Check if user is participant
- Clear cache: `php artisan optimize:clear`

### **Issue: Rate limiting too strict**
**Solution**:
- Adjust in `RateLimitServiceProvider.php`
- Change from 10 to desired number

### **Issue: Spam detection false positives**
**Solution**:
- Review spam patterns in `SendMessageRequest.php`
- Adjust thresholds or remove overly strict rules

### **Issue: Transaction not created**
**Solution**:
- Check `offered_price` field is set
- Verify inquiry status enum values
- Check database constraints

---

## 📊 Success Metrics

After testing, you should see:

- ✅ **0 errors** in message sending/receiving
- ✅ **< 500ms** message load time
- ✅ **100%** conversation creation success
- ✅ **100%** inquiry-to-transaction transition success
- ✅ **Rate limiting** working at 10 msg/min
- ✅ **Spam detection** blocking malicious content
- ✅ **All database** relationships intact

---

## 🎯 Next Steps

After manual testing:

1. **Run automated tests**: `php artisan test`
2. **Check test coverage**: Aim for > 80%
3. **Performance test**: Load test with 100+ users
4. **Security audit**: Penetration testing
5. **User acceptance testing**: Real users test the system

---

## 📞 Support

If you encounter issues:

1. Check `storage/logs/laravel.log`
2. Review this testing guide
3. Run: `php artisan test:inquiry-workflow`
4. Check database integrity queries above

---

**Happy Testing!** 🎉

Remember: **Test early, test often, test thoroughly!**
