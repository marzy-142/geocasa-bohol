# Schedule Viewing & Mark as Complete - Detailed Flow Explanation

## 📅 SCHEDULE VIEWING Function

### Purpose

The "Schedule Viewing" function allows brokers to:

1. Mark an inquiry as having a scheduled property viewing/meeting
2. Record the specific date and time of the viewing
3. Update the inquiry status to "scheduled"
4. Track upcoming appointments with clients

### When to Use

-   Client has expressed interest and wants to see the property in person
-   Broker and client have agreed on a specific date/time for viewing
-   Moving from initial contact to concrete next step

---

## 🔄 Schedule Viewing Flow (Step-by-Step)

### **Step 1: Broker Opens Inquiry**

**Location:** `/inquiries/{id}` (Inquiry Show Page)

**Current State:**

```
Status: NEW or CONTACTED
Timeline:
  ● Received: Nov 2, 10:30 AM
  ● Contacted: Nov 2, 10:35 AM (if previously responded)
```

---

### **Step 2: Click "Send Response" Button**

The response form appears with status options.

---

### **Step 3: Select "Scheduled" Status**

**UI Changes:**

```
┌─────────────────────────────────────────────────────────┐
│  Update Status *                                        │
│  ┌───────────┐ ┌──────────┐ ┌───────────┐ ┌────────┐  │
│  │ Contacted │ │SCHEDULED │ │ Completed │ │ Closed │  │
│  └───────────┘ └──────────┘ └───────────┘ └────────┘  │
│                    ↑ CLICKED                            │
│                                                          │
│  📅 Scheduled Date/Time (optional)                      │
│  ┌──────────────────────────────────────┐              │
│  │ 2025-11-09 14:00                     │ ← datetime   │
│  └──────────────────────────────────────┘              │
└─────────────────────────────────────────────────────────┘
```

**Visual Changes:**

-   "Scheduled" button becomes highlighted (purple background)
-   Optional `scheduled_at` datetime picker field appears
-   Status badge preview shows purple "Scheduled"

**Frontend Code (Vue):**

```javascript
// resources/js/Pages/Inquiries/Show.vue
<button
    type="button"
    @click="responseForm.status = 'scheduled'"
    :class="[
        'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
        responseForm.status === 'scheduled'
            ? 'bg-purple-100 text-purple-800 border-2 border-purple-300'
            : 'bg-gray-50 text-gray-700 border-2 border-transparent hover:bg-gray-100',
    ]"
>
    Scheduled
</button>

<!-- Scheduled date field - appears when status = 'scheduled' -->
<div v-if="responseForm.status === 'scheduled'">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Scheduled Date/Time (optional)
    </label>
    <input
        type="datetime-local"
        v-model="responseForm.scheduled_at"
        class="w-full border-gray-300 rounded-lg shadow-sm"
    />
</div>
```

---

### **Step 4: Fill Response Form**

**Complete Form:**

```
Response Message *
┌─────────────────────────────────────────────────────────┐
│ Great! I'd be happy to show you this property. Let's   │
│ meet on Saturday, November 9th at 2:00 PM at the       │
│ property location. I'll send you the exact address.    │
└─────────────────────────────────────────────────────────┘

Update Status *
[Contacted] [SCHEDULED ✓] [Completed] [Closed]

📅 Scheduled Date/Time (optional)
┌──────────────────────────────────────┐
│ 2025-11-09 14:00                     │
└──────────────────────────────────────┘

Internal Notes (optional)
┌─────────────────────────────────────────────────────────┐
│ Client prefers afternoon. Will bring blueprints.       │
└─────────────────────────────────────────────────────────┘

[Cancel]                   [Send Response & Update]
```

**Required Fields:**

-   ✅ Response Message (will be emailed to client)
-   ✅ Status = "scheduled"

**Optional Fields:**

-   `scheduled_at` - Specific datetime (recommended but not required)
-   `broker_notes` - Internal notes not visible to client

---

### **Step 5: Submit Form**

**Form Submission:**

```javascript
// Frontend
const submitResponse = () => {
    responseForm.post(route("inquiries.respond", inquiry.id), {
        preserveScroll: true,
        onSuccess: () => {
            showResponseForm.value = false;
        },
    });
};
```

**Form Data Sent:**

```javascript
{
    broker_response: "Great! I'd be happy to show you...",
    status: "scheduled",
    scheduled_at: "2025-11-09 14:00:00",  // Optional
    broker_notes: "Client prefers afternoon..."
}
```

---

### **Step 6: Backend Processing**

**Route:** `POST /inquiries/{inquiry}/respond`

**Controller Method:** `InquiryController@respond`

```php
public function respond(Request $request, Inquiry $inquiry)
{
    // 1. VALIDATE INPUT
    $validated = $request->validate([
        'broker_response' => 'required|string',
        'status' => 'required|in:new,contacted,scheduled,completed,closed',
        'scheduled_at' => 'nullable|date',  // ← Schedule viewing field
        'completion_outcome' => 'nullable|in:won,lost,no_response,other',
        'completion_reason' => 'nullable|string|max:255',
        'completion_notes' => 'nullable|string|max:2000',
    ]);

    // 2. PREPARE UPDATE DATA
    $updateData = [
        'broker_response' => $validated['broker_response'],
        'status' => $validated['status'],  // 'scheduled'
        'responded_at' => now(),  // First response timestamp
    ];

    // 3. SET CONTACTED_AT (if first time contacting)
    if ($validated['status'] === 'contacted' && $inquiry->status !== 'contacted') {
        $updateData['contacted_at'] = now();
    }

    // 4. SET SCHEDULED_AT (if date/time provided)
    if (!empty($validated['scheduled_at'])) {
        $updateData['scheduled_at'] = $validated['scheduled_at'];
        // ↑ This saves: "2025-11-09 14:00:00"
    }

    // 5. UPDATE INQUIRY IN DATABASE
    $inquiry->update($updateData);
    // Database now has:
    // - status: 'scheduled'
    // - scheduled_at: '2025-11-09 14:00:00'
    // - broker_response: "Great! I'd be happy..."
    // - responded_at: '2025-11-02 10:40:00'

    // 6. CREATE CONVERSATION (auto)
    $conversation = $inquiry->conversation;
    if (!$conversation) {
        $conversation = Conversation::createForInquiry($inquiry->fresh());

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => null,
            'content' => "Conversation started. Maria Santos scheduled a viewing.",
            'is_system_message' => true,
        ]);
    }

    // 7. SEND EMAIL TO CLIENT
    Mail::to($inquiry->email)->send(
        new InquiryResponseMail(
            $inquiry,
            $validated['broker_response'],
            Auth::user()->name
        )
    );
    // Client receives:
    // Subject: Response to Your Inquiry
    // Body: "Great! I'd be happy to show you..."
    // + Property details
    // + Broker contact info
    // + Link to continue conversation

    // 8. BROADCAST REAL-TIME UPDATE
    broadcast(new InquiryStatusUpdated($inquiry));
    // All connected brokers see status change in real-time

    return redirect()->route('inquiries.show', $inquiry)
        ->with('success', 'Response sent and viewing scheduled!');
}
```

---

### **Step 7: Database State After Update**

**Inquiries Table Record:**

```sql
SELECT * FROM inquiries WHERE id = 123;

+----+-------------+-------------------+--------+------------+
| id | name        | status            | ...    | timestamps |
+----+-------------+-------------------+--------+------------+
| 123| John Doe    | scheduled         | ...    |            |
+----+-------------+-------------------+--------+------------+

Full record:
{
  id: 123,
  property_id: 45,
  client_id: 67,
  name: "John Doe",
  email: "john@example.com",
  phone: "+63 912 345 6789",
  message: "I'm interested in viewing...",
  inquiry_type: "viewing",
  status: "scheduled",  ← UPDATED

  // Timestamps
  created_at: "2025-11-02 10:30:00",
  contacted_at: "2025-11-02 10:35:00",
  responded_at: "2025-11-02 10:40:00",  ← SET
  scheduled_at: "2025-11-09 14:00:00",  ← SET

  // Response data
  broker_response: "Great! I'd be happy to show you...",
  broker_notes: "Client prefers afternoon. Will bring blueprints.",

  // Completion fields (null for scheduled)
  completion_outcome: null,
  completion_reason: null,
  completion_notes: null
}
```

---

### **Step 8: UI Updates After Submission**

**Inquiry Show Page Now Displays:**

```
┌─────────────────────────────────────────────────────────┐
│  ← Back to Inquiries                                    │
│                                                          │
│  John Doe                              [Scheduled]      │
│  Received 10 minutes ago                    ↑ Purple    │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  📬 Continue the conversation                           │
│  Chat with John Doe about this inquiry                  │
│                                    [Open Chat] ──────►  │
└─────────────────────────────────────────────────────────┘
  ↑ NEW: Conversation auto-created

LEFT COLUMN:
┌─────────────────────────────────────────────────────────┐
│  Your Response                                          │
├─────────────────────────────────────────────────────────┤
│  "Great! I'd be happy to show you this property..."    │
│                                                          │
│  Internal Notes                                         │
│  Client prefers afternoon. Will bring blueprints.      │
└─────────────────────────────────────────────────────────┘

RIGHT SIDEBAR:
┌──────────────────────────┐
│  Timeline                │
├──────────────────────────┤
│  ● Received              │
│    Nov 2, 10:30 AM       │
│                          │
│  ● Contacted             │
│    Nov 2, 10:35 AM       │
│                          │
│  ● Responded             │
│    Nov 2, 10:40 AM       │
│                          │
│  ● Scheduled    ← NEW    │
│    Nov 9, 2:00 PM        │
└──────────────────────────┘
```

---

### **Step 9: Client Receives Email**

**Email Content:**

```
From: broker@geocasa.com
To: john@example.com
Subject: Response to Your Inquiry - Residential Lot with Ocean View

Hi John,

Thank you for your inquiry about Residential Lot with Ocean View
in Dauis, Bohol.

Great! I'd be happy to show you this property. Let's meet on
Saturday, November 9th at 2:00 PM at the property location.
I'll send you the exact address.

---
Property Details:
- Title: Residential Lot with Ocean View
- Location: Dauis, Bohol
- Price: ₱2,500,000
- Area: 300 sqm

---
Broker Information:
Maria Santos
Licensed Real Estate Broker
Email: maria@geocasa.com
Phone: +63 912 345 6789

[Continue Conversation] ← Button links to conversation

Best regards,
GeoCasa Bohol Real Estate
```

---

### **Step 10: Follow-up Actions**

**Broker Can Now:**

1. ✅ Chat with client in real-time (conversation created)
2. ✅ Send property location/directions via chat
3. ✅ Add reminder to calendar for Nov 9, 2:00 PM
4. ✅ View inquiry in "Scheduled" filter on dashboard
5. ✅ After viewing happens, mark as "Completed" with outcome

**Client Can Now:**

1. ✅ Reply via email or conversation chat
2. ✅ Ask additional questions before viewing
3. ✅ Request to reschedule
4. ✅ Confirm attendance

---

## ✅ MARK AS COMPLETE Function

### Purpose

The "Mark as Complete" function allows brokers to:

1. Close out an inquiry with a final outcome
2. Track success metrics (won/lost)
3. Record reasons for completion
4. Generate analytics data for reporting

### When to Use

-   Viewing has occurred and client made a decision
-   Inquiry resulted in successful transaction (WON)
-   Client chose another property (LOST)
-   Client stopped responding (NO RESPONSE)
-   Any other final resolution (OTHER)

---

## 🔄 Mark as Complete Flow (Step-by-Step)

### **Step 1: Broker Opens Inquiry**

**Location:** `/inquiries/{id}`

**Current State:**

```
Status: SCHEDULED (or any previous status)
Timeline shows viewing was scheduled/occurred
```

---

### **Step 2: Click "Send Response" or Update Status**

Broker can update at any time, typically after viewing or final decision.

---

### **Step 3: Select "Completed" Status**

**UI Changes - Completion Form Appears:**

```
┌─────────────────────────────────────────────────────────┐
│  Update Status *                                        │
│  ┌───────────┐ ┌──────────┐ ┌───────────┐ ┌────────┐  │
│  │ Contacted │ │Scheduled │ │COMPLETED  │ │ Closed │  │
│  └───────────┘ └──────────┘ └───────────┘ └────────┘  │
│                                  ↑ CLICKED              │
│                                                          │
│  ┌─── COMPLETION DETAILS (Required Section) ────────┐  │
│  │                                                    │  │
│  │  Outcome * (REQUIRED)                             │  │
│  │  ┌──────────────────┐                             │  │
│  │  │ Won             ▼│  ← Must select one          │  │
│  │  ├──────────────────┤                             │  │
│  │  │ • Won            │  Client proceeded            │  │
│  │  │ • Lost           │  Client chose other          │  │
│  │  │ • No response    │  Client stopped responding   │  │
│  │  │ • Other          │  Other reason                │  │
│  │  └──────────────────┘                             │  │
│  │                                                    │  │
│  │  Reason (optional)                                │  │
│  │  ┌──────────────────────────────────────────────┐│  │
│  │  │ Client agreed to purchase                    ││  │
│  │  └──────────────────────────────────────────────┘│  │
│  │                                                    │  │
│  │  Completion Notes (optional)                      │  │
│  │  ┌──────────────────────────────────────────────┐│  │
│  │  │ Excellent client. Referred by previous       ││  │
│  │  │ customer. Ready to proceed with transaction. ││  │
│  │  │ Will start paperwork Monday.                 ││  │
│  │  └──────────────────────────────────────────────┘│  │
│  └────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
```

**Visual Changes:**

-   "Completed" button highlighted (green background)
-   Gray completion details box appears
-   Outcome dropdown becomes **REQUIRED** (red asterisk)
-   Reason and Notes fields appear (optional)

**Frontend Code (Vue):**

```javascript
// Completion Details section - only shows when status = 'completed' or 'closed'
<div
    v-if="['completed', 'closed'].includes(responseForm.status)"
    class="space-y-4 p-4 bg-gray-50 rounded-lg"
>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Outcome Dropdown -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Outcome
                <span v-if="responseForm.status === 'completed'" class="text-red-500">*</span>
            </label>
            <select
                v-model="responseForm.completion_outcome"
                class="w-full border-gray-300 rounded-lg"
                :required="responseForm.status === 'completed'"
            >
                <option value="">Select outcome</option>
                <option value="won">Won</option>
                <option value="lost">Lost</option>
                <option value="no_response">No response</option>
                <option value="other">Other</option>
            </select>
        </div>

        <!-- Reason Field -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Reason (optional)
            </label>
            <input
                type="text"
                maxlength="255"
                v-model="responseForm.completion_reason"
                placeholder="e.g., client chose another property"
            />
        </div>
    </div>

    <!-- Notes Field -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Completion Notes (optional)
        </label>
        <textarea
            rows="3"
            v-model="responseForm.completion_notes"
            placeholder="Additional details for reporting or follow-up"
        ></textarea>
    </div>
</div>
```

---

### **Step 4: Fill Completion Form**

**Example - WON Scenario:**

```
Response Message *
┌─────────────────────────────────────────────────────────┐
│ Congratulations! I'm excited to help you proceed with   │
│ this purchase. I'll prepare the transaction documents   │
│ and we can start the process this week.                 │
└─────────────────────────────────────────────────────────┘

Update Status *
[Contacted] [Scheduled] [COMPLETED ✓] [Closed]

Completion Details
  Outcome * (REQUIRED)
  ┌──────────────────┐
  │ Won             ▼│  ← SELECTED
  └──────────────────┘

  Reason (optional)
  ┌──────────────────────────────────────────────────────┐
  │ Client agreed to purchase after second viewing       │
  └──────────────────────────────────────────────────────┘

  Completion Notes (optional)
  ┌──────────────────────────────────────────────────────┐
  │ Client was very impressed with the ocean view and    │
  │ accessibility. Cash buyer, ready to proceed          │
  │ immediately. Will create transaction on Monday.      │
  └──────────────────────────────────────────────────────┘
```

**Example - LOST Scenario:**

```
Outcome: Lost
Reason: Client found similar property at lower price
Notes: Client was satisfied with our service but budget
       constraints led them elsewhere. Requested to be
       notified of future listings.
```

**Example - NO RESPONSE Scenario:**

```
Outcome: No response
Reason: Client stopped responding after first viewing
Notes: Followed up 3 times via email and phone. No reply
       since Nov 12. Moving to closed status.
```

---

### **Step 5: Submit Form**

**Form Data Sent:**

```javascript
{
    broker_response: "Congratulations! I'm excited to help...",
    status: "completed",
    completion_outcome: "won",  // REQUIRED
    completion_reason: "Client agreed to purchase after second viewing",
    completion_notes: "Client was very impressed with the ocean view..."
}
```

---

### **Step 6: Backend Validation & Processing**

```php
public function respond(Request $request, Inquiry $inquiry)
{
    // 1. VALIDATE
    $validated = $request->validate([
        'broker_response' => 'required|string',
        'status' => 'required|in:new,contacted,scheduled,completed,closed',
        'scheduled_at' => 'nullable|date',
        'completion_outcome' => 'nullable|in:won,lost,no_response,other',
        'completion_reason' => 'nullable|string|max:255',
        'completion_notes' => 'nullable|string|max:2000',
    ]);

    // 2. PREPARE BASE UPDATE
    $updateData = [
        'broker_response' => $validated['broker_response'],
        'status' => $validated['status'],
        'responded_at' => now(),
    ];

    // 3. ENFORCE OUTCOME REQUIREMENT FOR COMPLETED
    if ($validated['status'] === 'completed' && empty($request->completion_outcome)) {
        // VALIDATION FAILS - redirect back with error
        return redirect()->back()
            ->withErrors([
                'completion_outcome' => 'Outcome is required when marking an inquiry as completed.'
            ])
            ->withInput();
    }

    // 4. APPLY COMPLETION FIELDS
    if (in_array($validated['status'], ['completed', 'closed'])) {
        if ($request->filled('completion_outcome')) {
            $updateData['completion_outcome'] = $request->input('completion_outcome');
            // ↑ 'won', 'lost', 'no_response', or 'other'
        }
        if ($request->filled('completion_reason')) {
            $updateData['completion_reason'] = $request->input('completion_reason');
            // ↑ Free text up to 255 chars
        }
        if ($request->filled('completion_notes')) {
            $updateData['completion_notes'] = $request->input('completion_notes');
            // ↑ Free text up to 2000 chars
        }
    }

    // 5. UPDATE DATABASE
    $inquiry->update($updateData);

    // 6. CREATE/UPDATE CONVERSATION, SEND EMAIL, BROADCAST
    // (same as Schedule Viewing flow)

    return redirect()->route('inquiries.show', $inquiry)
        ->with('success', 'Inquiry marked as completed!');
}
```

---

### **Step 7: Database State After Completion**

**Inquiries Table Record:**

```sql
SELECT * FROM inquiries WHERE id = 123;

{
  id: 123,
  property_id: 45,
  client_id: 67,
  name: "John Doe",
  email: "john@example.com",
  status: "completed",  ← UPDATED

  // Timestamps
  created_at: "2025-11-02 10:30:00",
  contacted_at: "2025-11-02 10:35:00",
  responded_at: "2025-11-02 10:40:00",
  scheduled_at: "2025-11-09 14:00:00",
  updated_at: "2025-11-09 16:30:00",  ← When marked complete

  // Response data
  broker_response: "Congratulations! I'm excited to help...",
  broker_notes: "Internal notes from earlier...",

  // COMPLETION FIELDS (NEW)
  completion_outcome: "won",  ← SET
  completion_reason: "Client agreed to purchase after second viewing",  ← SET
  completion_notes: "Client was very impressed with the ocean view..."  ← SET
}
```

---

### **Step 8: UI After Marking Complete**

**Inquiry Show Page:**

```
┌─────────────────────────────────────────────────────────┐
│  ← Back to Inquiries                                    │
│                                                          │
│  John Doe                              [Completed]      │
│  Received Nov 2                             ↑ Green     │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  ✅ Inquiry Completed                                   │
│  Outcome: Won                                           │
│  Reason: Client agreed to purchase after second viewing │
└─────────────────────────────────────────────────────────┘

LEFT COLUMN:
┌─────────────────────────────────────────────────────────┐
│  Your Response                                          │
├─────────────────────────────────────────────────────────┤
│  "Congratulations! I'm excited to help you proceed..."  │
│                                                          │
│  Completion Details                                     │
│  Outcome: Won ✅                                        │
│  Reason: Client agreed to purchase after second viewing │
│  Notes: Client was very impressed with the ocean view...│
└─────────────────────────────────────────────────────────┘

RIGHT SIDEBAR:
┌──────────────────────────┐
│  Timeline                │
├──────────────────────────┤
│  ● Received              │
│    Nov 2, 10:30 AM       │
│  ● Contacted             │
│    Nov 2, 10:35 AM       │
│  ● Responded             │
│    Nov 2, 10:40 AM       │
│  ● Scheduled             │
│    Nov 9, 2:00 PM        │
│  ● Completed  ← NEW      │
│    Nov 9, 4:30 PM        │
└──────────────────────────┘

┌──────────────────────────┐
│  Actions                 │
├──────────────────────────┤
│  [Start Transaction]     │ ← If outcome = won
│  [View Completion Data]  │
└──────────────────────────┘
```

---

### **Step 9: Analytics & Reporting Impact**

**Metrics Updated:**

1. **Conversion Rate:**

```sql
-- Calculate win rate
SELECT
    COUNT(CASE WHEN completion_outcome = 'won' THEN 1 END) * 100.0 / COUNT(*) as win_rate
FROM inquiries
WHERE status = 'completed';

Result: 35% win rate
```

2. **Outcome Distribution:**

```sql
SELECT completion_outcome, COUNT(*)
FROM inquiries
WHERE status = 'completed'
GROUP BY completion_outcome;

won: 15
lost: 12
no_response: 8
other: 3
```

3. **Average Time to Complete:**

```sql
SELECT AVG(TIMESTAMPDIFF(DAY, created_at, updated_at)) as avg_days
FROM inquiries
WHERE status = 'completed';

Result: 7 days average
```

4. **Broker Performance:**

```sql
-- Brokers with best win rate
SELECT
    broker_id,
    COUNT(CASE WHEN completion_outcome = 'won' THEN 1 END) as wins,
    COUNT(*) as total_completed,
    COUNT(CASE WHEN completion_outcome = 'won' THEN 1 END) * 100.0 / COUNT(*) as win_rate
FROM inquiries
WHERE status = 'completed'
GROUP BY broker_id
ORDER BY win_rate DESC;
```

---

## 📊 Comparison: Schedule Viewing vs Mark as Complete

| Aspect                   | Schedule Viewing                       | Mark as Complete                        |
| ------------------------ | -------------------------------------- | --------------------------------------- |
| **Status**               | `scheduled`                            | `completed`                             |
| **Required Fields**      | Response message, Status               | Response message, Status, **Outcome**   |
| **Optional Fields**      | `scheduled_at` datetime                | `completion_reason`, `completion_notes` |
| **Purpose**              | Set up future meeting                  | Close inquiry with final result         |
| **Timeline Impact**      | Adds "Scheduled" entry                 | Adds "Completed" entry                  |
| **Next Actions**         | Conduct viewing, continue conversation | Start transaction (if won), archive     |
| **Analytics**            | Tracks scheduled rate                  | Tracks conversion metrics               |
| **Email Sent**           | Yes (confirmation)                     | Yes (final response)                    |
| **Conversation Created** | Yes (auto)                             | Yes (if not exists)                     |
| **Can Change Later**     | Yes (reschedule or complete)           | Yes (but discouraged - final state)     |

---

## 🎯 Real-World Scenarios

### Scenario 1: Successful Sale (Schedule → Complete Won)

```
1. Nov 2: Client submits inquiry → Status: NEW
2. Nov 2: Broker responds → Status: CONTACTED
3. Nov 2: Broker schedules viewing → Status: SCHEDULED
   - scheduled_at: Nov 9, 2:00 PM
4. Nov 9: Viewing happens (offline)
5. Nov 9: Client decides to purchase
6. Nov 9: Broker marks complete → Status: COMPLETED
   - completion_outcome: won
   - completion_reason: "Client loved the property"
   - completion_notes: "Cash buyer, ready immediately"
7. Nov 10: Broker creates Transaction from inquiry
8. Status automatically changes to: IN_TRANSACTION
```

### Scenario 2: Lost to Competitor (Schedule → Complete Lost)

```
1. Nov 2: Client submits inquiry → Status: NEW
2. Nov 2: Broker responds → Status: CONTACTED
3. Nov 5: Broker schedules viewing → Status: SCHEDULED
4. Nov 9: Viewing happens
5. Nov 10: Client emails: "Found another property, thank you"
6. Nov 10: Broker marks complete → Status: COMPLETED
   - completion_outcome: lost
   - completion_reason: "Client chose competitor property"
   - completion_notes: "Price was issue. Maintain relationship."
```

### Scenario 3: No Show (Schedule → Complete No Response)

```
1. Nov 2: Client inquiry → Status: NEW
2. Nov 2: Broker responds → Status: CONTACTED
3. Nov 5: Scheduled viewing → Status: SCHEDULED
4. Nov 9: Client doesn't show up
5. Nov 9-15: Broker follows up 3 times (no response)
6. Nov 16: Broker marks complete → Status: COMPLETED
   - completion_outcome: no_response
   - completion_reason: "No show for scheduled viewing"
   - completion_notes: "Tried email, phone, SMS. No reply."
```

---

## 🔑 Key Takeaways

### Schedule Viewing

✅ **Sets expectation** for in-person meeting  
✅ **Records specific datetime** (optional but recommended)  
✅ **Maintains inquiry active** - not final  
✅ **Enables conversation** for logistics  
✅ **Tracks appointments** for calendar

### Mark as Complete

✅ **Finalizes inquiry** with outcome  
✅ **Requires outcome selection** (won/lost/no_response/other)  
✅ **Captures analytics data** for reporting  
✅ **Documents reasons** for future reference  
✅ **Enables transaction creation** (if won)

---

## 📝 Best Practices

### For Scheduling

1. ✅ Always provide `scheduled_at` datetime
2. ✅ Confirm with client before scheduling
3. ✅ Add location/directions in message
4. ✅ Set calendar reminder
5. ✅ Follow up day before viewing

### For Completion

1. ✅ Mark complete promptly after decision
2. ✅ Always select accurate outcome (for analytics)
3. ✅ Provide detailed reason (helps future sales)
4. ✅ Add notes about client preferences
5. ✅ If won, create transaction immediately
6. ✅ If lost, document why for improvement

---

**End of Detailed Flow Explanation**
