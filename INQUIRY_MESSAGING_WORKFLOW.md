# Inquiry-to-Conversation Messaging Workflow

## Overview

The messaging functionality integrates seamlessly with the inquiry workflow, enabling real-time communication between brokers and clients after the initial response.

## Workflow Stages

### 1. **Inquiry Submission** (No Messaging Yet)

-   Client submits property inquiry through website
-   System creates inquiry record
-   **No conversation created yet** - keeps initial inquiry simple
-   Broker receives notification of new inquiry

### 2. **Broker Responds** (Conversation Created)

-   Broker reviews inquiry and sends response via response form
-   **System automatically creates conversation** when broker responds
-   Response is:
    -   Saved to database
    -   Emailed to client
    -   Conversation is initialized
-   System message added: "Conversation started. [Broker Name] responded to the inquiry about [Property Title]."

### 3. **Real-Time Messaging Active**

-   Both broker and client can now exchange messages
-   **"Open Conversation" button appears** on inquiry details page
-   **"Message Broker" button appears** in client's inquiry view
-   Email includes **"Continue Conversation" button** (for logged-in users)
-   Messages are real-time with notifications

### 4. **Transaction Creation** (Optional)

-   If broker creates transaction from inquiry
-   Existing conversation transitions to transaction
-   Conversation continues seamlessly
-   All message history preserved

## Implementation Details

### Backend Changes

#### InquiryController::respond()

```php
// After updating inquiry with response
$conversation = $inquiry->conversation;
if (!$conversation) {
    // Create conversation on first response
    $conversation = Conversation::createForInquiry($inquiry->fresh(['property', 'client']));

    // Add system message
    Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => null,
        'content' => "Conversation started. {$user->name} responded...",
        'is_system_message' => true,
    ]);
}
```

#### InquiryController::show()

-   Now loads `conversation` relationship
-   Passes to view for button visibility

### Frontend Changes

#### Broker View (Inquiries/Show.vue)

-   **"Open Conversation" button** appears if `inquiry.conversation` exists
-   Positioned first in quick actions bar
-   Green styling to indicate active messaging
-   Icon: speech bubble

#### Client View (Client/Inquiries/Show.vue)

-   **"Message Broker" button** appears if `inquiry.conversation` exists
-   Replaces old non-functional button
-   Only shown after broker has responded

### Email Template

#### HTML Email (inquiry-response.blade.php)

-   **"Continue Conversation" button** added
-   Only shown if conversation exists
-   Appears before "View Property Details" button
-   Links directly to conversation page

#### Text Email (inquiry-response-text.blade.php)

-   Includes conversation URL
-   Clean text format for email clients without HTML support

## User Experience

### For Clients:

1. Submit inquiry → Wait for response
2. Receive email with broker's response
3. Click "Continue Conversation" in email OR log in and click "Message Broker"
4. Exchange messages in real-time
5. Optional: Transaction created for serious buyers

### For Brokers:

1. Receive inquiry notification
2. Review inquiry details
3. Send initial response (creates conversation)
4. Click "Open Conversation" to continue discussion
5. Qualify lead through messaging
6. Create transaction when ready

## Benefits

### ✅ Progressive Engagement

-   Messaging activates only when needed
-   Avoids overwhelming new inquiries
-   Natural progression from email to real-time chat

### ✅ Seamless Communication

-   Email notification gets client's attention
-   Real-time messaging for ongoing discussion
-   No need to switch between email and platform

### ✅ Lead Qualification

-   Brokers can assess interest through conversation
-   Clients can ask follow-up questions
-   Better information exchange before transaction

### ✅ Message History

-   All communication logged
-   Conversations persist through transaction
-   Audit trail for compliance

## Technical Notes

### Conversation Participants

-   Broker (property owner)
-   Client (if has user account)
-   Guest inquiries: Can participate if they create account with same email

### Message Features Available

-   Real-time text messaging
-   File attachments
-   Read receipts
-   Typing indicators
-   Message notifications

### Security

-   Access control: Only participants can view conversation
-   Privacy: Messages only visible to broker and client
-   Data retention: Messages preserved with inquiry/transaction

## Future Enhancements

Potential improvements:

-   Auto-suggest responses based on inquiry type
-   SMS notifications for important messages
-   Video call scheduling within conversation
-   Property tour booking through chat
-   Contract sharing and e-signatures
-   AI chatbot for common questions

## Migration Note

Existing inquiries without conversations:

-   Will get conversation when broker responds again
-   OR can manually create via "Create Conversation" button (if we add one)
-   Old inquiries continue to work as before
