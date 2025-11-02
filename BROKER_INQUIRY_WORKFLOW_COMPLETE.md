# Broker-Side Inquiry System Workflow - Complete Guide

## 📋 Table of Contents

1. [Overview](#overview)
2. [Inquiry Lifecycle](#inquiry-lifecycle)
3. [Inquiry Management Dashboard](#inquiry-management-dashboard)
4. [Detailed Workflow Steps](#detailed-workflow-steps)
5. [Real-Time Features](#real-time-features)
6. [Status Management](#status-management)
7. [Automated Features](#automated-features)
8. [Integration with Other Systems](#integration-with-other-systems)

---

## 🎯 Overview

The broker-side inquiry system is designed to manage client inquiries efficiently from receipt to completion. It provides brokers with tools to:

-   Monitor incoming inquiries in real-time
-   Respond to clients quickly
-   Track inquiry progress through various stages
-   Convert inquiries into transactions
-   Analyze inquiry metrics

---

## 🔄 Inquiry Lifecycle

### Status Flow Diagram

```
┌─────────┐
│   NEW   │ ← Client submits inquiry through property page
└────┬────┘
     │
     ▼
┌─────────────┐
│  CONTACTED  │ ← Broker makes initial contact/sends response
└─────┬───────┘
      │
      ▼
┌─────────────┐
│  SCHEDULED  │ ← Property viewing or meeting scheduled
└─────┬───────┘
      │
      ├──────────┐
      ▼          ▼
┌───────────┐ ┌────────┐
│ COMPLETED │ │ CLOSED │ ← Final outcomes
└───────────┘ └────────┘
      │          │
      ▼          ▼
┌──────────────────────┐
│ IN TRANSACTION       │ ← Converted to active transaction
│ (separate system)    │
└──────────────────────┘
```

---

## 📊 Inquiry Management Dashboard

### Location

**URL:** `/inquiries` (Broker Dashboard → Inquiries)

### Dashboard Components

#### 1. **Header Stats** (Real-time Metrics)

```
┌─────────────────────────────────────────────────────────┐
│  Inquiry Management                                     │
│  Manage and respond to client inquiries efficiently     │
│                                                          │
│  ┌──────────┐  ┌──────────────┐  ┌────────────┐       │
│  │    5     │  │      8       │  │     3      │        │
│  │   New    │  │ In Progress  │  │ Completed  │        │
│  │   ●      │  │              │  │            │        │
│  └──────────┘  └──────────────┘  └────────────┘       │
│                                                          │
│  [Mark All Read]  [Export]                              │
└─────────────────────────────────────────────────────────┘
```

**Metrics Calculated:**

-   **New:** Count of `status = 'new'`
-   **In Progress:** Count of `status IN ('contacted', 'scheduled')`
-   **Completed:** Count of `status = 'completed'` today

#### 2. **Status Tabs** (Quick Filtering)

```
[All] [New] [In Discussion] [Scheduled] [Done]
  ↑     ↑         ↑            ↑          ↑
 All  new    contacted     scheduled  completed+closed
```

#### 3. **Advanced Filters**

```
┌─────────────────────────────────────────────────────────┐
│  Search & Filter                    [Clear all filters] │
├─────────────────────────────────────────────────────────┤
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐  │
│  │ Search   │ │ Status   │ │ Type     │ │ Property │  │
│  │ Name...  │ │ All      │ │ All      │ │ All      │  │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘  │
│                                                          │
│  ┌──────────┐ ┌──────────┐                             │
│  │Date From │ │ Date To  │                             │
│  └──────────┘ └──────────┘                             │
└─────────────────────────────────────────────────────────┘
```

**Filter Options:**

-   **Search:** Name, email, message content, property title
-   **Status:** new, contacted, scheduled, completed, closed
-   **Type:** general, viewing, purchase, information
-   **Property:** Dropdown of broker's properties only
-   **Date Range:** created_at between dates

**Backend Implementation:**

```php
// InquiryController@index
$query = Inquiry::with(['property', 'client'])
    ->forBroker($user->id)  // Only broker's properties
    ->when($request->search, fn($q, $search) =>
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%")
          ->orWhere('message', 'like', "%{$search}%")
    )
    ->when($request->status, fn($q, $status) =>
        $q->where('status', $status)
    )
    // ... other filters
    ->orderBy('created_at', 'desc')
    ->paginate(12);
```

#### 4. **Inquiry Cards** (List View)

```
┌─────────────────────────────────────────────────────────┐
│  John Doe                              [New] Badge      │
│  Received Nov 2, 2025 10:30 AM          🔴 Overdue      │
├─────────────────────────────────────────────────────────┤
│  Property: Residential Lot with Ocean View              │
│  Location: Dauis, Bohol                                 │
│                                                          │
│  "I'm interested in viewing this property. Available    │
│   this weekend?"                                        │
├─────────────────────────────────────────────────────────┤
│  [Respond]  [View]                                      │
└─────────────────────────────────────────────────────────┘
```

**Card Information:**

-   Client name & timestamp
-   Status badge (color-coded)
-   Overdue indicator (if >24 hours without response)
-   Property details
-   Message preview (2 lines max)
-   Quick action buttons

**Button Logic:**

```javascript
// Primary action based on status
primaryActionLabel(inquiry):
  - new         → "Respond"
  - contacted   → "Chat"
  - scheduled   → "Chat"
  - completed   → null (no primary action)
  - closed      → null

handlePrimaryAction(inquiry):
  - new         → Open quick response modal
  - contacted   → Navigate to inquiry.show (conversation)
  - scheduled   → Navigate to inquiry.show (conversation)
```

---

## 📝 Detailed Workflow Steps

### Step 1: Inquiry Creation (Client Side)

**Location:** Public property page (`/properties/{slug}`)

**Client Actions:**

1. Views property listing
2. Clicks "Send Inquiry" button
3. Fills inquiry form:
    - Name
    - Email
    - Phone (optional)
    - Message
    - Inquiry type (general/viewing/purchase/information)
4. Submits form

**Backend Processing:**

```php
// PropertyController or InquiryController@store
Inquiry::create([
    'property_id' => $property->id,
    'user_id' => auth()->id(),
    'name' => $validated['name'],
    'email' => $validated['email'],
    'phone' => $validated['phone'],
    'message' => $validated['message'],
    'inquiry_type' => $validated['inquiry_type'],
    'status' => 'new',  // Initial status
]);
```

**Immediate Actions:**

1. ✅ Inquiry saved to database with `status = 'new'`
2. 📧 Email notification sent to property broker
3. 🔔 Real-time notification broadcast to broker (if online)
4. 📱 SMS notification (if Twilio configured)
5. ✅ Confirmation message shown to client

---

### Step 2: Broker Receives Notification

**Notification Channels:**

#### A. **Real-Time Browser Notification** (Laravel Echo + Pusher)

```javascript
// Broadcasted on channels:
- 'inquiries' (global)
- 'broker.{broker_id}' (individual broker)

Event: .inquiry.new
Payload: {
    inquiry: {
        id, name, email, property, message, status
    }
}

// Frontend handling:
window.Echo.private('broker.123')
    .listen('.inquiry.new', (e) => {
        // Show browser notification
        new Notification('New Inquiry', {
            body: `${e.inquiry.name} inquired about ${e.inquiry.property.title}`
        });

        // Auto-refresh inquiry list
        router.reload({ only: ['inquiries'] });

        // Increment "New" counter
        newInquiriesCount++;
    });
```

#### B. **Email Notification** (`NewInquiryNotification`)

```
To: broker@example.com
Subject: New Inquiry for [Property Title]

Hello [Broker Name],

You have received a new inquiry from [Client Name].

Property: [Property Title]
Location: [Address]
Inquiry Type: [Type]
Message: [Client Message]

Contact:
- Email: [Client Email]
- Phone: [Client Phone]

[View Inquiry Button] → /inquiries/{id}
```

#### C. **SMS Notification** (Optional - Twilio)

```
New inquiry from [Name] about [Property].
View: [short URL]
```

#### D. **Dashboard Indicators**

-   Red pulsing dot on "New" counter
-   "New" badge on inquiry card
-   Overdue indicator after 24 hours

---

### Step 3: Broker Views Inquiry

**Navigation:**

-   Dashboard → Inquiries → Click inquiry card
-   Or click notification link

**Page:** `/inquiries/{id}` (Inquiry Show Page)

**Displayed Information:**

```
┌─────────────────────────────────────────────────────────┐
│  ← Back to Inquiries                                    │
│                                                          │
│  John Doe                              [New]            │
│  Received 2 hours ago                                   │
└─────────────────────────────────────────────────────────┘

LEFT COLUMN:
┌─────────────────────────────────────────────────────────┐
│  Inquiry Message                              [General] │
├─────────────────────────────────────────────────────────┤
│  "I'm interested in this property. I'd like to          │
│   schedule a viewing this weekend if possible."         │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  Property                                               │
├─────────────────────────────────────────────────────────┤
│  Residential Lot with Ocean View                        │
│  Residential Lot • Dauis, Bohol                         │
│  ₱2,500,000                                             │
│  [View full property details →]                         │
└─────────────────────────────────────────────────────────┘

RIGHT SIDEBAR:
┌──────────────────────────┐
│  Contact                 │
├──────────────────────────┤
│  👤 John Doe             │
│     Client               │
│  📧 john@example.com     │
│  📱 +63 912 345 6789     │
│  [View client profile →] │
└──────────────────────────┘

┌──────────────────────────┐
│  Actions                 │
├──────────────────────────┤
│  [Send Response]         │ ← Primary
│  [Start Transaction]     │
│  [Delete Inquiry]        │
└──────────────────────────┘

┌──────────────────────────┐
│  Timeline                │
├──────────────────────────┤
│  ● Received              │
│    Nov 2, 10:30 AM       │
└──────────────────────────┘
```

---

### Step 4: Broker Responds to Inquiry

**Action:** Click "Send Response" button

**Response Form Opens:**

```
┌─────────────────────────────────────────────────────────┐
│  Send Response                                          │
├─────────────────────────────────────────────────────────┤
│  Quick templates                                         │
│  [Thanks + will reach out] [Schedule viewing] [Preparing]
│                                                          │
│  Response Message *                                      │
│  ┌─────────────────────────────────────────────────┐   │
│  │ Thank you for your interest! I'd be happy to    │   │
│  │ schedule a viewing this weekend...              │   │
│  └─────────────────────────────────────────────────┘   │
│  📧 This will be sent to john@example.com               │
│                                                          │
│  Update Status *                                         │
│  [Contacted] [Scheduled] [Completed] [Closed]           │
│   ↑ Selected                                            │
│                                                          │
│  Internal Notes (optional - not sent to client)         │
│  ┌─────────────────────────────────────────────────┐   │
│  │ Client seems serious. Follow up Friday.         │   │
│  └─────────────────────────────────────────────────┘   │
│                                                          │
│  [Cancel]                   [Send Response & Update]    │
└─────────────────────────────────────────────────────────┘
```

**Form Fields:**

1. **Quick Templates** (Click to auto-fill)

    - "Thanks for your inquiry! I'll reach out shortly..."
    - "Let's schedule a property viewing. Please share availability."
    - "I've reviewed your inquiry and will prepare recommendations..."

2. **Response Message\*** (Required)

    - Textarea, min 10 characters
    - Sent via email to client
    - Saved as `broker_response` in database

3. **Update Status\*** (Required)

    - **Contacted:** Initial contact made
    - **Scheduled:** Viewing/meeting scheduled
    - **Completed:** Successfully finalized (requires outcome)
    - **Closed:** Closed without completion

4. **Internal Notes** (Optional)

    - Private broker notes
    - NOT sent to client
    - Saved as `broker_notes` in database

5. **Completion Details** (Only if Completed/Closed selected)
    - **Outcome\*** (Required for Completed)
        - Won
        - Lost
        - No response
        - Other
    - **Reason** (Optional text)
    - **Notes** (Optional textarea)

**Backend Processing:**

```php
// InquiryController@respond
public function respond(Request $request, Inquiry $inquiry)
{
    // 1. Validate access
    if ($inquiry->property->broker_id !== auth()->id()) {
        abort(403);
    }

    // 2. Validate input
    $validated = $request->validate([
        'broker_response' => 'required|string',
        'status' => 'required|in:contacted,scheduled,completed,closed',
        'completion_outcome' => 'nullable|in:won,lost,no_response,other',
        // ...
    ]);

    // 3. Update inquiry
    $inquiry->update([
        'broker_response' => $validated['broker_response'],
        'status' => $validated['status'],
        'responded_at' => now(),
        'contacted_at' => ($validated['status'] === 'contacted') ? now() : null,
        // ...
    ]);

    // 4. Create/get conversation
    $conversation = $inquiry->conversation;
    if (!$conversation) {
        $conversation = Conversation::createForInquiry($inquiry);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => null,
            'content' => "Conversation started. {$user->name} responded.",
            'is_system_message' => true,
        ]);
    }

    // 5. Send email to client
    Mail::to($inquiry->email)->send(
        new InquiryResponseMail($inquiry, $validated['broker_response'], auth()->user()->name)
    );

    // 6. Broadcast status update
    broadcast(new InquiryStatusUpdated($inquiry));

    // 7. Redirect back
    return redirect()->route('inquiries.show', $inquiry)
        ->with('success', 'Response sent successfully!');
}
```

**Automated Actions:**

1. ✅ Inquiry updated in database
2. 📧 Email sent to client with response
3. 💬 Conversation thread created automatically
4. 🔔 Real-time broadcast: `inquiry.status.updated`
5. ⏰ Timestamps updated:
    - `responded_at` → current time
    - `contacted_at` → current time (if status = contacted)
    - `scheduled_at` → scheduled datetime (if provided)
6. 📊 Metrics updated (response time, status counts)

---

### Step 5: Conversation / Follow-up

**After responding, a banner appears:**

```
┌─────────────────────────────────────────────────────────┐
│  Continue the conversation                              │
│  Chat with John Doe about this inquiry                  │
│                                    [Open Chat] ──────►  │
└─────────────────────────────────────────────────────────┘
```

**Clicking "Open Chat" navigates to:**
`/conversations/{conversation_id}`

**Conversation Features:**

-   Real-time messaging (Laravel Echo)
-   Message history
-   File attachments
-   Read receipts
-   Typing indicators
-   System messages (status changes, transaction created, etc.)

**Example Conversation:**

```
┌─────────────────────────────────────────────────────────┐
│  Conversation with John Doe                             │
│  About: Residential Lot with Ocean View                 │
├─────────────────────────────────────────────────────────┤
│  [System] Nov 2, 10:35 AM                               │
│  Conversation started. Maria Santos responded to the    │
│  inquiry.                                               │
│                                                          │
│  [Broker] Nov 2, 10:35 AM                               │
│  Thank you for your interest! I'd be happy to schedule  │
│  a viewing this weekend...                              │
│                                                          │
│  [Client] Nov 2, 10:42 AM                               │
│  Great! I'm available Saturday afternoon.               │
│                                                          │
│  [Broker] Nov 2, 10:45 AM                               │
│  Perfect! Let's meet at the property at 2 PM Saturday.  │
│  I'll send you the exact location.                      │
├─────────────────────────────────────────────────────────┤
│  Type message...                          [Send]        │
└─────────────────────────────────────────────────────────┘
```

---

### Step 6: Schedule Viewing (Optional)

**If status updated to "Scheduled":**

**Additional field appears in form:**

```
Scheduled Date/Time
┌──────────────────────────────────┐
│  2025-11-09 14:00               │ ← datetime picker
└──────────────────────────────────┘
```

**Database Update:**

```php
$inquiry->update([
    'status' => 'scheduled',
    'scheduled_at' => $validated['scheduled_at'],
]);
```

**Timeline Updates:**

```
Timeline
● Received
  Nov 2, 10:30 AM
● Contacted
  Nov 2, 10:35 AM
● Responded
  Nov 2, 10:35 AM
● Scheduled         ← NEW
  Nov 9, 2:00 PM
```

**Calendar Integration** (Future Enhancement):

-   Add to broker's calendar
-   Send calendar invite to client
-   Reminder notifications

---

### Step 7: Complete or Close Inquiry

**When ready to finalize:**

#### Option A: **Mark as Completed** (Successful)

**Form Requirements:**

```
Status: Completed

Outcome * (Required)
┌──────────────────┐
│ Won             ▼│  ← Must select
└──────────────────┘

Reason (optional)
┌──────────────────────────────┐
│ Client agreed to purchase    │
└──────────────────────────────┘

Notes (optional)
┌──────────────────────────────┐
│ Great client. Referred by    │
│ previous customer.           │
└──────────────────────────────┘
```

**Outcome Options:**

-   **Won:** Client proceeded (purchase/rent)
-   **Lost:** Client chose another property
-   **No response:** Client stopped responding
-   **Other:** Other reason

**Backend:**

```php
if ($validated['status'] === 'completed') {
    if (empty($request->completion_outcome)) {
        return back()->withErrors([
            'completion_outcome' => 'Outcome required for completed inquiries'
        ]);
    }

    $inquiry->update([
        'status' => 'completed',
        'completion_outcome' => $validated['completion_outcome'],
        'completion_reason' => $validated['completion_reason'],
        'completion_notes' => $validated['completion_notes'],
        'completed_at' => now(),
    ]);
}
```

#### Option B: **Mark as Closed** (Unsuccessful)

**Similar to Completed but:**

-   Outcome is optional
-   Indicates inquiry ended without conversion
-   Still tracked for analytics

---

### Step 8: Convert to Transaction

**Action:** Click "Start Transaction" button

**Navigates to:** `/transactions/create?inquiry_id={id}`

**Pre-populated Data:**

-   Client information (name, email, phone)
-   Property details
-   Inquiry message
-   Inquiry type

**Transaction Creation Form:**

```
┌─────────────────────────────────────────────────────────┐
│  Create Transaction                                     │
├─────────────────────────────────────────────────────────┤
│  Client: John Doe ✓ (from inquiry)                     │
│  Property: Residential Lot ✓ (from inquiry)            │
│  Type: Sale / Rent                                      │
│  Offer Amount: ₱__________                              │
│  Terms: [textarea]                                      │
│  ...                                                     │
│  [Create Transaction]                                   │
└─────────────────────────────────────────────────────────┘
```

**Backend Processing:**

```php
// TransactionController@store
Transaction::create([
    'inquiry_id' => $inquiry->id,
    'property_id' => $inquiry->property_id,
    'client_id' => $inquiry->client_id,
    'broker_id' => auth()->id(),
    'type' => $validated['type'],
    'offer_amount' => $validated['offer_amount'],
    'status' => 'pending',
    // ...
]);

// Auto-update inquiry
$inquiry->update([
    'status' => 'in_transaction',
    'responded_at' => $inquiry->responded_at ?? now(),
]);
```

**Inquiry Updates:**

-   Status changes to "in_transaction"
-   Transaction reference saved
-   Inquiry shown in different color on list

---

## ⚡ Real-Time Features

### Laravel Echo Setup

**Channels Joined:**

```javascript
// Global inquiries channel (all brokers)
window.Echo.private("inquiries");

// Individual broker channel
window.Echo.private("broker.{broker_id}");
```

### Event Listeners

#### 1. **New Inquiry Event**

```javascript
.listen('.inquiry.new', (e) => {
    // Payload:
    {
        inquiry: {
            id: 123,
            name: "John Doe",
            email: "john@example.com",
            property: { title: "..." },
            message: "...",
            status: "new"
        }
    }

    // Actions:
    - Show browser notification
    - Play notification sound
    - Increment "New" counter
    - Auto-refresh inquiry list
    - Highlight new inquiry in list
})
```

#### 2. **Status Update Event**

```javascript
.listen('.inquiry.status.updated', (e) => {
    // Payload:
    {
        inquiry_id: 123,
        old_status: "new",
        new_status: "contacted",
        inquiry: { ... }
    }

    // Actions:
    - Update inquiry in current list
    - Update counters
    - Show notification
})
```

#### 3. **New Message Event** (in conversations)

```javascript
.listen('.message.new', (e) => {
    // Payload:
    {
        message: { content, sender, timestamp },
        conversation_id: 456
    }

    // Actions:
    - Append message to conversation
    - Play notification sound
    - Mark conversation as unread
})
```

### Connection Status Indicator

```javascript
window.Echo.connector.pusher.connection.bind("connected", () => {
    isConnected.value = true;
    // Show green indicator
});

window.Echo.connector.pusher.connection.bind("disconnected", () => {
    isConnected.value = false;
    // Show red indicator, attempt reconnect
});
```

---

## 📊 Status Management

### Status Definitions

| Status             | Meaning                           | Trigger                            | Timestamps Updated             |
| ------------------ | --------------------------------- | ---------------------------------- | ------------------------------ |
| **new**            | Just received, no response yet    | Client submits inquiry             | `created_at`                   |
| **contacted**      | Broker made initial contact       | Broker sends first response        | `responded_at`, `contacted_at` |
| **scheduled**      | Viewing/meeting scheduled         | Broker sets scheduled status       | `responded_at`, `scheduled_at` |
| **completed**      | Successfully finalized (won/lost) | Broker marks complete with outcome | `responded_at`, `completed_at` |
| **closed**         | Closed without conversion         | Broker closes inquiry              | `responded_at`                 |
| **in_transaction** | Converted to active transaction   | Transaction created from inquiry   | Auto-set                       |

### Status Badges

```css
new:         Blue   bg-blue-500    "New inquiry"
contacted:   Amber  bg-amber-500   "Initial contact made"
scheduled:   Purple bg-purple-500  "Viewing scheduled"
completed:   Green  bg-green-500   "Successfully completed"
closed:      Gray   bg-gray-500    "Closed"
in_transaction: Teal bg-teal-500   "Active transaction"
```

### Status Transitions (Allowed Flows)

```
new → contacted
new → scheduled
new → closed

contacted → scheduled
contacted → completed
contacted → closed

scheduled → completed
scheduled → closed

completed → (terminal)
closed → (terminal)

any → in_transaction (auto)
```

---

## 🤖 Automated Features

### 1. **Auto-Conversation Creation**

-   Triggered when broker first responds
-   Creates conversation thread automatically
-   Links inquiry ↔ conversation
-   Adds system message: "Conversation started..."

### 2. **Escalation Notifications**

-   If inquiry not responded to in 24 hours:
    ```php
    // InquiryEscalationNotification.php
    Mail::to($broker)->send(new InquiryEscalationNotification($inquiry));
    ```
-   Shows "Overdue" badge on inquiry card
-   Daily digest of pending inquiries

### 3. **Email Auto-Responses**

-   Client receives email when:
    -   Inquiry submitted (confirmation)
    -   Broker responds
    -   Status changes to scheduled/completed
    -   Transaction created

### 4. **Analytics Tracking**

-   Response time (created_at → responded_at)
-   Conversion rate (inquiries → transactions)
-   Status distribution
-   Broker performance metrics

### 5. **Data Validation**

-   Email format validation
-   Phone number validation
-   Required fields enforcement
-   SQL injection protection
-   XSS prevention

---

## 🔗 Integration with Other Systems

### 1. **Client Management System**

**Auto-Client Creation:**

```php
// When inquiry received from authenticated user
$client = Client::firstOrCreate(
    ['email' => $user->email],
    [
        'name' => $user->name,
        'user_id' => $user->id,
        'phone' => $validated['phone'],
    ]
);

$inquiry->update(['client_id' => $client->id]);
```

**Benefits:**

-   Single client record across inquiries
-   Client history tracking
-   Pre-filled contact info

### 2. **Property Management System**

**Linking:**

-   Inquiry always tied to specific property
-   Property broker automatically assigned
-   Property status affects inquiry (unavailable warnings)

**Example:**

```php
// Show warning if property unavailable
if (in_array($property->status, ['reserved', 'sold', 'under_negotiation'])) {
    // Display warning in inquiry show page
    // Disable "Start Transaction" button
}
```

### 3. **Transaction System**

**Workflow:**

```
Inquiry → Transaction
  ↓            ↓
status:    inquiry_id set
in_transaction
```

**Data Flow:**

```php
Transaction::create([
    'inquiry_id' => $inquiry->id,        // Link back
    'property_id' => $inquiry->property_id,
    'client_id' => $inquiry->client_id,
    'broker_id' => $inquiry->property->broker_id,
    // Pre-populated from inquiry
]);

$inquiry->update(['status' => 'in_transaction']);
```

### 4. **Messaging System**

**Conversation Structure:**

```
Inquiry (1) → Conversation (1)
             ↓
          Messages (many)
```

**Creation:**

```php
$conversation = Conversation::create([
    'inquiry_id' => $inquiry->id,
    'property_id' => $inquiry->property_id,
    'participants' => [
        $inquiry->client_id,
        $inquiry->property->broker_id
    ],
]);
```

### 5. **Notification System**

**Channels Used:**

-   Database (notification bell)
-   Mail (email)
-   Broadcast (real-time)
-   SMS (optional - Twilio)

**Notification Flow:**

```
New Inquiry
    ↓
├─→ Database Notification (for notification center)
├─→ Email Notification (inbox)
├─→ Broadcast Notification (real-time UI update)
└─→ SMS Notification (if enabled)
```

---

## 📈 Analytics & Reporting

### Metrics Tracked

1. **Response Time**

```sql
AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at))
```

2. **Conversion Rate**

```sql
(COUNT(transactions) / COUNT(inquiries)) * 100
```

3. **Status Distribution**

```sql
SELECT status, COUNT(*)
FROM inquiries
GROUP BY status
```

4. **Completion Outcomes**

```sql
SELECT completion_outcome, COUNT(*)
FROM inquiries
WHERE status = 'completed'
GROUP BY completion_outcome
```

### Export Functionality

**Route:** `/inquiries/export`

**Exports to Excel:**

-   All filtered inquiries
-   Includes: ID, Date, Client, Property, Status, Outcome, Response Time
-   Formatted for analysis

---

## 🎯 Best Practices for Brokers

### Response Time

✅ **Respond within 1 hour** for best conversion
✅ Use quick templates for common scenarios
✅ Enable browser notifications

### Communication

✅ **Be professional and courteous**
✅ Personalize responses (avoid generic templates)
✅ Provide specific next steps

### Status Management

✅ **Update status promptly** as inquiry progresses
✅ Add internal notes for context
✅ Mark completion outcome for analytics

### Follow-up

✅ **Set reminders** for scheduled viewings
✅ Follow up if no client response in 3 days
✅ Convert qualified inquiries to transactions quickly

---

## 🔒 Security & Permissions

### Access Control

-   **Brokers:** Can only see inquiries for their properties
-   **Admins:** Can see all inquiries
-   **Clients:** Can only see their own inquiries

### Data Protection

-   Email validation
-   CSRF protection
-   SQL injection prevention
-   XSS protection
-   Rate limiting on inquiry submission

### Privacy

-   Internal notes NOT sent to clients
-   Completion data only visible to broker/admin
-   Client data protected by GDPR compliance

---

## 📱 Mobile Responsiveness

-   Fully responsive design
-   Touch-friendly buttons
-   Mobile-optimized forms
-   Push notifications support
-   Offline indicator

---

## Summary: Complete Broker Workflow

```
1. CLIENT submits inquiry on property page
   ↓
2. BROKER receives notification (email, real-time, SMS)
   ↓
3. BROKER views inquiry in dashboard
   ↓
4. BROKER sends response
   - Chooses status (contacted/scheduled)
   - Writes message
   - Adds internal notes
   ↓
5. SYSTEM auto-creates conversation thread
   ↓
6. BROKER & CLIENT chat in real-time
   ↓
7. BROKER schedules viewing (optional)
   ↓
8. BROKER marks inquiry as completed/closed
   - Records outcome (won/lost)
   - Adds completion notes
   ↓
9. (Optional) BROKER converts to transaction
   ↓
10. SYSTEM tracks all metrics for reporting
```

---

**🎉 End of Broker-Side Inquiry Workflow Documentation**

For questions or support, contact the development team.
