# 📊 Deal Progress System - How It Works

## Overview

The **Deal Progress Card** shows a visual representation of where a deal stands in the sales pipeline. The progress percentage is calculated based on completed stages, and each stage is triggered by specific broker actions.

## Progress Calculation Formula

```javascript
Progress % = (Completed Stages / Total Stages) × 100
Total Stages = 7
```

---

## The 7 Stages & Broker Actions

### Stage 1: Inquiry Received (0% → 14%)

**Status:** ✅ Always Completed  
**What triggers it:** When a client submits an inquiry form  
**Database field:** `inquiries.created_at`  
**Broker action required:** None (automatic)

---

### Stage 2: Initial Contact (14% → 28%)

**Status:** ✅ Completed when broker responds  
**What triggers it:**

1. Broker **responds to the inquiry** via the "Respond" button
2. Broker changes status to "Contacted"
3. Broker sends a message to the client

**Database field:** `inquiries.contacted_at`  
**Broker actions:**

-   Click "Respond" on the inquiry
-   Write a response message
-   System automatically sets `contacted_at = now()`

**Code location:**

```php
// InquiryController@respond
if ($validated['status'] === 'contacted' && $inquiry->status !== 'contacted') {
    $updateData['contacted_at'] = now();
}
```

---

### Stage 3: Viewing Scheduled (28% → 42%)

**Status:** ✅ Completed when viewing appointment is set  
**What triggers it:**

1. Broker fills in the "Scheduled At" date field when responding
2. Can be set when creating or updating the inquiry response

**Database field:** `inquiries.scheduled_at`  
**Broker actions:**

-   In the respond form, set the "Viewing Date" field
-   This is a manual date/time picker
-   Shows when the property viewing will happen

**Code location:**

```php
// InquiryController@respond
if (!empty($validated['scheduled_at'])) {
    $updateData['scheduled_at'] = $validated['scheduled_at'];
}
```

---

### Stage 4: Offer Made (42% → 57%)

**Status:** ✅ Completed when transaction is created  
**What triggers it:**

1. Broker marks inquiry as "Won" (completion_outcome = 'won')
2. System **automatically creates a transaction**
3. Transaction creation marks this stage as complete

**Database field:** `transactions.created_at` (exists = completed)  
**Broker actions:**

-   Mark inquiry as "Completed" with outcome "Won"
-   Click "Mark as Won" button
-   System auto-creates transaction with status "offer_made"

**Code location:**

```php
// InquiryController@markAsWon
$transaction = Transaction::create([
    'inquiry_id' => $inquiry->id,
    'status' => 'offer_made',
    // ... other fields
]);
```

**Visual indicator on page:**

```
🎉 Transaction Created Automatically!
Transaction #TXN-XXX has been created
```

---

### Stage 5: Negotiation (57% → 71%)

**Status:** ✅ Completed when transaction status reaches "negotiation" or beyond  
**What triggers it:**

1. In the Transaction page, broker updates status to "Negotiation"
2. System checks if status is in: negotiation, offer_accepted, contract_signed, or finalized

**Database field:** `transactions.status = 'negotiation'` (or later stages)  
**Broker actions:**

-   Go to Transaction detail page
-   Click "Update Status"
-   Select "Negotiation" from dropdown
-   Add notes about negotiation progress

**Code location:**

```php
// TransactionController@updateStatus
'status' => 'required|in:...,negotiation,...'
```

**Progress check:**

```javascript
status: transaction &&
["negotiation", "offer_accepted", "contract_signed", "finalized"].includes(
    transaction.status
)
    ? "completed"
    : "pending";
```

---

### Stage 6: Contract Signed (71% → 85%)

**Status:** ✅ Completed when contract is signed  
**What triggers it:**

1. Broker updates transaction status to "Contract Signed"
2. System automatically sets `contract_date = now()`

**Database field:** `transactions.contract_date`  
**Broker actions:**

-   In Transaction page, update status to "Contract Signed"
-   System auto-timestamps the contract date
-   Upload contract documents (if document feature exists)

**Code location:**

```php
// TransactionController@updateStatus
case 'contract_signed':
    if (!$transaction->contract_date) {
        $updateData['contract_date'] = now();
    }
    break;
```

---

### Stage 7: Finalized (85% → 100%)

**Status:** ✅ Completed when deal is closed  
**What triggers it:**

1. Broker updates transaction status to "Finalized"
2. System automatically sets `finalized_date = now()`
3. Property may be marked as sold

**Database field:** `transactions.finalized_date`  
**Broker actions:**

-   Update transaction status to "Finalized"
-   System records finalization date
-   Deal is complete! 🎉

**Code location:**

```php
// TransactionController@updateStatus
case 'finalized':
    if (!$transaction->finalized_date) {
        $updateData['finalized_date'] = now();
    }
    break;
```

---

## Example Progress Timeline

### 🔵 Example Deal at 43%

```
✅ Stage 1: Inquiry Received      (Nov 01, 2025) ← 14%
✅ Stage 2: Initial Contact       (Nov 01, 2025) ← 28%
✅ Stage 3: Viewing Scheduled     (Oct 31, 2025) ← 42%
🔵 Stage 4: Offer Made            (Current)      ← Next: Mark as Won
⚪ Stage 5: Negotiation
⚪ Stage 6: Contract Signed
⚪ Stage 7: Finalized

Total Progress: 3/7 = 43%
Next Action: "Mark the inquiry as won to create a transaction"
```

### 🟣 Example Deal at 71%

```
✅ Stage 1: Inquiry Received      (Nov 01, 2025) ← 14%
✅ Stage 2: Initial Contact       (Nov 01, 2025) ← 28%
✅ Stage 3: Viewing Scheduled     (Oct 31, 2025) ← 42%
✅ Stage 4: Offer Made            (Nov 02, 2025) ← 57%
🟣 Stage 5: Negotiation           (Current)      ← 71%
⚪ Stage 6: Contract Signed                      ← Next action
⚪ Stage 7: Finalized

Total Progress: 5/7 = 71%
Next Action: "Prepare and execute the purchase agreement"
```

---

## Status Badge vs Progress Percentage

### What You See on the Page

**Top Right Badge:**

-   Shows current **transaction status** (when transaction exists)
-   Examples: "In Negotiation", "Offer Made", "Contract Signed"
-   OR shows **inquiry status** (if no transaction)
-   Examples: "New", "Contacted", "Scheduled"

**Progress Card:**

-   Shows **overall progress** through the 7 stages
-   Percentage based on completed milestones
-   Visual progress bar (blue/yellow/green based on %)

### Why They Can Differ

**Example 1: Transaction in "Negotiation"**

-   Badge: "In Negotiation" (purple)
-   Progress: 71% (5 out of 7 stages)
-   ✅ Stages 1-5 completed, working on stage 6

**Example 2: Inquiry "Completed" but No Transaction**

-   Badge: "Completed" (green) ← Inquiry phase done
-   Progress: 43% (3 out of 7 stages)
-   ⚠️ This was confusing! Now fixed to show transaction status when it exists

---

## Broker Workflow Summary

### Path 1: Quick Overview (Inquiry Page)

1. **View inquiry details** → See client info, property, message
2. **Click "Respond"** → Opens response form
3. **Fill response** → Sets contacted_at automatically
4. **Set viewing date** → Sets scheduled_at
5. **Save response** → Client gets notified

### Path 2: Close the Deal (Transaction Page)

6. **Mark inquiry as "Won"** → Auto-creates transaction (Stage 4 ✅)
7. **Go to transaction page** → Update status to "Negotiation" (Stage 5 ✅)
8. **Negotiate terms** → Update pricing, notes
9. **Status: "Contract Signed"** → Sets contract_date (Stage 6 ✅)
10. **Status: "Finalized"** → Sets finalized_date (Stage 7 ✅)

---

## Database Tables & Fields Reference

### Inquiries Table

| Field                | Purpose                 | Set By                     |
| -------------------- | ----------------------- | -------------------------- |
| `created_at`         | Inquiry received date   | System (auto)              |
| `contacted_at`       | First contact timestamp | Broker response action     |
| `scheduled_at`       | Viewing appointment     | Broker sets date manually  |
| `responded_at`       | Broker responded        | Broker response action     |
| `status`             | Inquiry status          | Broker updates             |
| `completion_outcome` | won/lost/no_response    | Broker marks when complete |

### Transactions Table

| Field                | Purpose                     | Set By                           |
| -------------------- | --------------------------- | -------------------------------- |
| `created_at`         | Transaction created         | Auto (when marked as won)        |
| `status`             | Current transaction stage   | Broker updates status            |
| `first_contact_date` | Initial contact with client | Auto (from inquiry.contacted_at) |
| `viewing_date`       | Property viewing date       | Auto (from inquiry.scheduled_at) |
| `offer_date`         | Offer made date             | Auto (status → offer_made)       |
| `acceptance_date`    | Offer accepted date         | Auto (status → offer_accepted)   |
| `contract_date`      | Contract signed date        | Auto (status → contract_signed)  |
| `finalized_date`     | Deal closed date            | Auto (status → finalized)        |

---

## Color Coding

### Progress Bar Colors

-   🟢 **Green (100%)**: Deal finalized!
-   🔵 **Blue (70-99%)**: Advanced stages, almost there
-   🟡 **Yellow (40-69%)**: Mid-pipeline, making progress
-   🟣 **Indigo (<40%)**: Early stages, just started

### Stage Status Colors

-   🟢 **Green checkmark**: Completed stage
-   🔵 **Blue dot**: Current stage (in progress)
-   ⚪ **Gray number**: Pending stage (not started)

### Transaction Status Badges

-   🟡 **Yellow**: Pending
-   🔵 **Blue**: Offer Made
-   🟣 **Purple**: In Negotiation
-   🟢 **Green**: Offer Accepted
-   🔷 **Indigo**: Contract Signed
-   💚 **Emerald**: Deal Finalized
-   🔴 **Red**: Cancelled

---

## Next Actions System

The system provides **context-aware suggestions** for what to do next:

| Current Stage     | Next Action Suggestion                                                         |
| ----------------- | ------------------------------------------------------------------------------ |
| Initial Contact   | "Reach out to the client to discuss their interest and answer questions."      |
| Viewing Scheduled | "Schedule a property viewing appointment with the client."                     |
| Offer Made        | "Mark the inquiry as won to create a transaction and move to the offer stage." |
| Negotiation       | "Work with the client on price negotiations and terms."                        |
| Contract Signed   | "Prepare and execute the purchase agreement."                                  |
| Finalized         | "Complete the final paperwork and close the deal."                             |

These appear in a blue info box below the progress stages.

---

## Technical Implementation

### Frontend (Vue Component)

**File:** `resources/js/Components/DealProgressCard.vue`

```javascript
// Progress calculation
const progressPercentage = computed(() => {
    const completedStages = stages.value.filter(
        (s) => s.status === "completed"
    ).length;
    const totalStages = stages.value.length;
    return Math.round((completedStages / totalStages) * 100);
});
```

### Backend (Controller Actions)

**Files:**

-   `app/Http/Controllers/InquiryController.php` → respond(), markAsWon()
-   `app/Http/Controllers/TransactionController.php` → updateStatus()

### Auto-Transaction Creation

**File:** `app/Observers/InquiryObserver.php`

When inquiry is marked as won, observer automatically:

1. Creates transaction record
2. Copies inquiry data to transaction
3. Sets initial transaction status
4. Links transaction to inquiry

---

## Tips for Brokers

1. **Respond quickly** → Sets contacted_at, moves to 28%
2. **Schedule viewings** → Set scheduled_at, moves to 42%
3. **Mark as Won** → Auto-creates transaction, moves to 57%
4. **Update transaction status regularly** → Keeps progress accurate
5. **Use notes fields** → Document negotiations and decisions
6. **Watch the "Next Action" box** → Tells you exactly what to do next

---

## Common Questions

**Q: Why does it say "Completed" but progress is only 43%?**  
A: FIXED! Now shows transaction status when transaction exists. "Completed" was the inquiry status (inquiry phase done), but the actual deal is at 43% (3 of 7 stages).

**Q: How do I increase the progress percentage?**  
A: Complete the next stage's required action. Check the "Next Action" box for guidance.

**Q: Can I skip stages?**  
A: No, stages complete sequentially. But you can update transaction status to later stages, which auto-completes earlier ones.

**Q: What happens when I mark as Won?**  
A: System automatically creates a transaction with all inquiry data transferred. Progress jumps to at least 57%.

**Q: Do dates auto-fill?**  
A: Yes! When you update transaction status, relevant date fields are automatically set if empty.

---

## Summary

The Deal Progress system provides **real-time visibility** into where each deal stands. It's **automated** where possible (dates, timestamps) but requires **broker input** for key actions (responding, scheduling, updating status). The 7-stage pipeline matches the typical real estate transaction flow from first contact to closing.

**Key takeaway:** Progress is driven by broker actions, but timestamps are automatic. The system guides brokers through the sales process with clear next actions and visual progress tracking.
