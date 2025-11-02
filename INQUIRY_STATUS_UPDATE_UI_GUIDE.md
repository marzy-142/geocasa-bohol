# Inquiry Status Update UI Guide

## How Brokers Update Inquiry Status - Visual Walkthrough

### 📍 Location

**URL:** `/inquiries/{id}` (Inquiry Show Page)

---

## 🎯 UI Overview

### **Step 1: View Inquiry**

When a broker clicks on an inquiry, they see:

```
┌─────────────────────────────────────────────────────────┐
│  ← Back to Inquiries                                    │
│                                                          │
│  John Doe                              [New] Badge      │
│  Received 2 hours ago                                   │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  📬 Continue the conversation                           │
│  Chat with John Doe about this inquiry                  │
│                                    [Open Chat] Button   │
└─────────────────────────────────────────────────────────┘
  ↑ This appears if conversation already exists
```

### **Step 2: Click "Send Response" Button**

Located in the right sidebar under "Actions":

```
┌────────────────────────────┐
│  Actions                   │
├────────────────────────────┤
│  [Send Response]  ← Click  │
│  [Start Transaction]       │
│  [Delete Inquiry]          │
└────────────────────────────┘
```

---

## ✍️ Response Form UI

### **Full Form Layout:**

```
┌─────────────────────────────────────────────────────────────────────┐
│  Send Response                                                      │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Quick templates                                                    │
│  ┌──────────────────┐ ┌───────────────┐ ┌─────────────────────┐  │
│  │Thanks+will reach│ │Schedule viewing│ │Preparing recommend.│   │
│  └──────────────────┘ └───────────────┘ └─────────────────────┘  │
│                                                                     │
│  Response Message *                                                 │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │ Type your response...                                       │  │
│  │                                                             │  │
│  │                                                             │  │
│  └─────────────────────────────────────────────────────────────┘  │
│  📧 This message will be sent to john@example.com                  │
│                                                                     │
│  Update Status *                                                    │
│  ┌───────────┐ ┌──────────┐ ┌───────────┐ ┌────────┐            │
│  │ Contacted │ │Scheduled │ │ Completed │ │ Closed │             │
│  └───────────┘ └──────────┘ └───────────┘ └────────┘            │
│   ↑ Click any status button                                        │
│                                                                     │
│  ┌─────────── ONLY SHOWS IF COMPLETED/CLOSED ───────────┐         │
│  │  Completion Details                                   │         │
│  │  ┌──────────────────┐  ┌──────────────────────────┐ │         │
│  │  │ Outcome *        │  │ Reason (optional)        │ │         │
│  │  │ [Select outcome] │  │ [text input...]          │ │         │
│  │  │  • Won           │  └──────────────────────────┘ │         │
│  │  │  • Lost          │                                │         │
│  │  │  • No response   │  Notes (optional)              │         │
│  │  │  • Other         │  ┌──────────────────────────┐ │         │
│  │  └──────────────────┘  │ [textarea...]            │ │         │
│  │                         │                          │ │         │
│  └─────────────────────────└──────────────────────────┘─┘         │
│                                                                     │
│  Internal Notes (optional)                                          │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │ Private notes not visible to client...                      │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  [Cancel]                                  [Send Response & Update]│
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🎨 Status Options Explained

### **1. Contacted** (Amber Badge)

-   **When to use:** Initial contact made with client
-   **Auto-fills:** `contacted_at` timestamp
-   **Creates:** Conversation thread automatically
-   **Email sent:** Yes

### **2. Scheduled** (Purple Badge)

-   **When to use:** Property viewing or meeting scheduled
-   **Auto-fills:** `scheduled_at` timestamp (optional field available)
-   **Creates:** Conversation thread automatically
-   **Email sent:** Yes

### **3. Completed** (Green Badge)

-   **When to use:** Successfully finalized (won/lost)
-   **Required fields:**
    -   ✅ Outcome dropdown (won/lost/no_response/other)
-   **Optional fields:**
    -   Reason (text)
    -   Notes (textarea)
-   **Email sent:** Yes

### **4. Closed** (Gray Badge)

-   **When to use:** Inquiry closed without completion
-   **Optional fields:**
    -   Outcome dropdown
    -   Reason (text)
    -   Notes (textarea)
-   **Email sent:** Yes

---

## 📊 What Happens After Submission

### Backend Processing:

```
1. ✅ Validates broker owns the property (or is admin)
2. 📝 Updates inquiry record:
   - status → new status
   - broker_response → message
   - responded_at → current timestamp
   - contacted_at → timestamp (if contacted)
   - scheduled_at → timestamp (if scheduled)
   - completion_* fields → if completed/closed
3. 💬 Creates/Gets conversation thread
4. 📧 Sends email to client with broker's response
5. 🔔 Broadcasts real-time update (if Echo running)
6. ↩️ Redirects back to inquiry page
```

---

## 🔄 Timeline Updates

After responding, the timeline on the right sidebar updates:

```
┌─────────────────────────┐
│  Timeline               │
├─────────────────────────┤
│  ● Received             │
│    Nov 2, 2025 10:30 AM │
│                         │
│  ● Contacted            │ ← NEW
│    Nov 2, 2025 12:45 PM │
│                         │
│  ● Responded            │ ← NEW
│    Nov 2, 2025 12:45 PM │
└─────────────────────────┘
```

---

## 🎯 Quick Templates

Pre-written messages for common scenarios:

1. **"Thanks + will reach out"**

    ```
    "Thanks for your inquiry! I'll reach out shortly to discuss details."
    ```

2. **"Schedule viewing"** (auto-sets status to "Scheduled")

    ```
    "Let's schedule a property viewing. Please share your availability."
    ```

3. **"Preparing recommendations"**
    ```
    "I've reviewed your inquiry and will prepare recommendations within 24 hours."
    ```

---

## 📧 Email Notification

The client receives an email with:

-   Broker's response message
-   Link to continue conversation
-   Property details
-   Broker contact information

---

## 🚀 Conversation Auto-Creation

Once a broker responds:

1. A **conversation thread** is automatically created
2. A **blue banner** appears at the top:
    ```
    ┌─────────────────────────────────────────────────────────┐
    │  Continue the conversation                              │
    │  Chat with John Doe about this inquiry                  │
    │                                    [Open Chat] Button   │
    └─────────────────────────────────────────────────────────┘
    ```
3. Broker and client can now chat in real-time

---

## 💡 Tips

-   ✅ **Use quick templates** for faster responses
-   ✅ **Select appropriate status** to track progress
-   ✅ **Add internal notes** for private broker records
-   ✅ **Complete all required fields** (marked with \*)
-   ✅ **Use "Completed" outcome** for better analytics

---

## 🔒 Permissions

-   **Brokers:** Can only respond to inquiries for their own properties
-   **Admins:** Can respond to any inquiry
-   **Clients:** Cannot access this feature (view-only)

---

## 📱 Mobile Responsive

The form adapts to mobile screens:

-   Status buttons stack vertically
-   Form fields take full width
-   Sidebar moves below main content
-   Touch-friendly button sizes
