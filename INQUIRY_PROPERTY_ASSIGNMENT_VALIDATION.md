# Inquiry Property Assignment Validation System

## Overview

This document describes the validation system implemented to prevent brokers from accidentally accepting inquiries for properties that already have assigned clients. The system enforces data consistency by checking property status before allowing new transactions or inquiry acceptance.

## Problem Statement

**Issue**: Brokers could previously accept inquiries and create transactions for properties that were already reserved, under negotiation, sold, or pending—leading to:

-   Data inconsistency
-   Client confusion
-   Wasted broker effort on unavailable properties
-   Potential double-booking scenarios

**Solution**: Multi-layer validation system that prevents inquiry acceptance at both backend and frontend levels.

---

## Implementation Details

### 1. Backend Implementation

#### A. Property Model (`app/Models/Property.php`)

**New Method: `hasAssignedClient()`**

```php
/**
 * Check if property has an assigned client (active transaction)
 * Returns true if property status indicates it's no longer available for new inquiries
 */
public function hasAssignedClient()
{
    return in_array($this->status, [
        'reserved',           // Property is reserved for a client
        'under_negotiation',  // Property is under negotiation with a client
        'sold',              // Property has been sold
        'pending'            // Offer accepted, deal in progress
    ]);
}
```

**New Accessor: `unavailable_reason`**

```php
/**
 * Get a user-friendly message explaining why property is unavailable for new inquiries
 */
public function getUnavailableReasonAttribute()
{
    if (!$this->hasAssignedClient()) {
        return null;
    }

    $messages = [
        'reserved' => 'This property is reserved and cannot accept new inquiries.',
        'under_negotiation' => 'This property is under negotiation with a client and cannot accept new inquiries.',
        'sold' => 'This property has been sold and cannot accept new inquiries.',
        'pending' => 'This property has an accepted offer and cannot accept new inquiries.'
    ];

    return $messages[$this->status] ?? 'This property is not available for new inquiries.';
}
```

**Usage**: These methods provide a clean, reusable way to check property availability throughout the application.

---

#### B. InquiryController (`app/Http/Controllers/InquiryController.php`)

**Updated Method: `accept()`**

Added validation before creating transaction:

```php
public function accept(Inquiry $inquiry)
{
    // ... existing authentication checks ...

    // IMPORTANT: Check if property already has an assigned client
    if ($inquiry->property && $inquiry->property->hasAssignedClient()) {
        return redirect()
            ->back()
            ->with('error', 'This property already has an assigned client and cannot accept new inquiries. ' . $inquiry->property->unavailable_reason);
    }

    // ... continue with transaction creation ...
}
```

**What it does**:

-   Checks property status before accepting inquiry
-   Redirects back with error message if property is unavailable
-   Prevents transaction creation for properties with assigned clients

---

#### C. TransactionController (`app/Http/Controllers/TransactionController.php`)

**Updated Method: `store()`**

Added validation before creating transaction:

```php
public function store(Request $request)
{
    // ... existing validation ...

    // IMPORTANT: Check if property already has an assigned client
    $property = Property::find($validated['property_id']);
    if ($property && $property->hasAssignedClient()) {
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'This property already has an assigned client and cannot accept new transactions. ' . $property->unavailable_reason);
    }

    // ... continue with transaction creation ...
}
```

**What it does**:

-   Validates property availability when manually creating transactions
-   Returns user input so broker can select a different property
-   Displays clear error message explaining why transaction cannot be created

---

### 2. Frontend Implementation

#### A. Inquiry Show Page (`resources/js/Pages/Inquiries/Show.vue`)

**New Computed Properties**:

```javascript
// Check if property has an assigned client
const propertyHasAssignedClient = computed(() => {
    if (!props.inquiry.property) return false;

    const unavailableStatuses = [
        "reserved",
        "under_negotiation",
        "sold",
        "pending",
    ];
    return unavailableStatuses.includes(props.inquiry.property.status);
});

// Get reason why property is unavailable
const propertyUnavailableReason = computed(() => {
    if (!propertyHasAssignedClient.value) return "";

    const status = props.inquiry.property.status;
    const messages = {
        reserved: "This property is reserved and cannot accept new inquiries.",
        under_negotiation:
            "This property is under negotiation with a client and cannot accept new inquiries.",
        sold: "This property has been sold and cannot accept new inquiries.",
        pending:
            "This property has an accepted offer and cannot accept new inquiries.",
    };

    return (
        messages[status] || "This property is not available for new inquiries."
    );
});
```

**UI Changes**:

1. **Warning Banner** (displayed when property has assigned client):

```vue
<div v-if="propertyHasAssignedClient" class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
    <div class="flex items-start">
        <svg class="w-5 h-5 text-amber-600 mr-3">...</svg>
        <div>
            <p class="text-sm font-medium text-amber-800">Property Not Available</p>
            <p class="text-sm text-amber-700 mt-1">{{ propertyUnavailableReason }}</p>
        </div>
    </div>
</div>
```

2. **Conditional Button Rendering**:

```vue
<!-- Show active button when property is available -->
<Link
    v-if="!inquiry.transaction && !propertyHasAssignedClient"
    :href="route('transactions.create', { inquiry_id: inquiry.id })"
    class="..."
>
    Start Transaction
</Link>

<!-- Show disabled button when property is unavailable -->
<button
    v-if="!inquiry.transaction && propertyHasAssignedClient"
    disabled
    class="... cursor-not-allowed opacity-60"
    title="Property already has an assigned client"
>
    Start Transaction (Unavailable)
</button>
```

---

#### B. Inquiry Index Page (`resources/js/Pages/Inquiries/Index.vue`)

**New Helper Function**:

```javascript
// Helper function to check if property has assigned client
const hasAssignedClient = (property) => {
    if (!property) return false;

    const unavailableStatuses = [
        "reserved",
        "under_negotiation",
        "sold",
        "pending",
    ];
    return unavailableStatuses.includes(property.status);
};
```

**UI Changes**:

1. **Warning Badge** (shown in inquiry card when property unavailable):

```vue
<div
    v-if="hasAssignedClient(inquiry.property)"
    class="flex-1 py-2 px-3 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg text-center"
>
    ⚠️ Property Unavailable
</div>
```

2. **Conditional "Start Deal" Button**:

```vue
<!-- Active button when property available -->
<Link
    v-if="!hasAssignedClient(inquiry.property)"
    :href="route('transactions.create', { inquiry_id: inquiry.id })"
    class="..."
>
    Start Deal
</Link>

<!-- Disabled button when property unavailable -->
<button
    v-else
    disabled
    class="... cursor-not-allowed opacity-60"
    title="Property already has an assigned client"
>
    Start Deal
</button>
```

---

## Property Status Reference

| Status              | Meaning                                    | Can Accept Inquiries? |
| ------------------- | ------------------------------------------ | --------------------- |
| `available`         | Property is actively listed and available  | ✅ Yes                |
| `reserved`          | Property is reserved for a specific client | ❌ No                 |
| `under_negotiation` | Property is being negotiated with a client | ❌ No                 |
| `sold`              | Property has been sold                     | ❌ No                 |
| `pending`           | Offer accepted, deal in progress           | ❌ No                 |
| `archived`          | Property removed from public view          | ✅ Yes (if re-listed) |
| `off_market`        | Property temporarily off the market        | ✅ Yes (if re-listed) |

---

## User Experience Flow

### Scenario 1: Broker Views Inquiry for Available Property

1. Broker navigates to inquiry details
2. **No warning** is displayed
3. "Start Transaction" button is **enabled** and clickable
4. Broker can proceed to create transaction

### Scenario 2: Broker Views Inquiry for Reserved Property

1. Broker navigates to inquiry details
2. **Amber warning banner** appears at top of actions section
3. Warning reads: "Property Not Available - This property is reserved and cannot accept new inquiries."
4. "Start Transaction" button is **disabled** and grayed out
5. Button shows tooltip: "Property already has an assigned client"
6. Broker **cannot** create transaction (frontend prevents click)

### Scenario 3: Broker Attempts Backend Acceptance (Edge Case)

1. Broker somehow bypasses frontend (e.g., direct API call, browser manipulation)
2. Request reaches `InquiryController@accept` method
3. Backend validation checks `$inquiry->property->hasAssignedClient()`
4. Returns with error: "This property already has an assigned client and cannot accept new inquiries. [specific reason]"
5. Broker redirected back to previous page with error flash message
6. **No transaction created** - data integrity maintained

---

## Testing Guide

### Manual Testing Checklist

#### Test 1: Available Property (Happy Path)

-   [ ] Create inquiry for property with status `available`
-   [ ] Navigate to inquiry details page
-   [ ] Verify no warning banner appears
-   [ ] Verify "Start Transaction" button is enabled
-   [ ] Click "Start Transaction"
-   [ ] Verify transaction creation page loads successfully

#### Test 2: Reserved Property

-   [ ] Update property status to `reserved`
-   [ ] Navigate to inquiry for that property
-   [ ] Verify amber warning banner appears
-   [ ] Verify warning message reads "This property is reserved..."
-   [ ] Verify "Start Transaction" button is disabled and grayed out
-   [ ] Hover over button - verify tooltip appears
-   [ ] Attempt to click button - verify nothing happens

#### Test 3: Property Under Negotiation

-   [ ] Update property status to `under_negotiation`
-   [ ] Navigate to inquiry for that property
-   [ ] Verify warning message reads "This property is under negotiation..."
-   [ ] Verify button is disabled

#### Test 4: Sold Property

-   [ ] Update property status to `sold`
-   [ ] Navigate to inquiry for that property
-   [ ] Verify warning message reads "This property has been sold..."
-   [ ] Verify button is disabled

#### Test 5: Pending Property

-   [ ] Update property status to `pending`
-   [ ] Navigate to inquiry for that property
-   [ ] Verify warning message reads "This property has an accepted offer..."
-   [ ] Verify button is disabled

#### Test 6: Inquiry List View

-   [ ] Navigate to broker inquiries index page
-   [ ] For inquiries with unavailable properties:
    -   [ ] Verify amber "⚠️ Property Unavailable" badge appears
    -   [ ] Verify "Start Deal" button is disabled
    -   [ ] Hover over button - verify tooltip

#### Test 7: Backend Validation (API Test)

-   [ ] Use browser DevTools or Postman
-   [ ] Attempt POST to `/inquiries/{id}/accept` for inquiry with reserved property
-   [ ] Verify response redirects with error message
-   [ ] Verify no transaction is created in database
-   [ ] Verify inquiry status remains unchanged

#### Test 8: Transaction Create Page

-   [ ] Navigate to transaction creation page
-   [ ] Select property with status `reserved`
-   [ ] Fill in all other required fields
-   [ ] Submit form
-   [ ] Verify redirect back to form with error message
-   [ ] Verify error message explains why property is unavailable
-   [ ] Verify form retains entered data
-   [ ] Verify no transaction created

---

### Automated Test Examples

#### Unit Test: Property Model

```php
/** @test */
public function it_correctly_identifies_properties_with_assigned_clients()
{
    $availableProperty = Property::factory()->create(['status' => 'available']);
    $reservedProperty = Property::factory()->create(['status' => 'reserved']);
    $soldProperty = Property::factory()->create(['status' => 'sold']);

    $this->assertFalse($availableProperty->hasAssignedClient());
    $this->assertTrue($reservedProperty->hasAssignedClient());
    $this->assertTrue($soldProperty->hasAssignedClient());
}

/** @test */
public function it_provides_correct_unavailable_reason()
{
    $reservedProperty = Property::factory()->create(['status' => 'reserved']);
    $this->assertEquals(
        'This property is reserved and cannot accept new inquiries.',
        $reservedProperty->unavailable_reason
    );
}
```

#### Feature Test: Inquiry Acceptance

```php
/** @test */
public function broker_cannot_accept_inquiry_for_reserved_property()
{
    $broker = User::factory()->broker()->create();
    $property = Property::factory()->create([
        'broker_id' => $broker->id,
        'status' => 'reserved'
    ]);
    $inquiry = Inquiry::factory()->create([
        'property_id' => $property->id,
        'assigned_broker_id' => $broker->id
    ]);

    $response = $this->actingAs($broker)->post(route('inquiries.accept', $inquiry));

    $response->assertRedirect()
             ->assertSessionHas('error');

    $this->assertDatabaseMissing('transactions', [
        'inquiry_id' => $inquiry->id
    ]);
}
```

#### Feature Test: Transaction Creation

```php
/** @test */
public function broker_cannot_create_transaction_for_property_under_negotiation()
{
    $broker = User::factory()->broker()->create();
    $client = Client::factory()->create(['broker_id' => $broker->id]);
    $property = Property::factory()->create([
        'broker_id' => $broker->id,
        'status' => 'under_negotiation'
    ]);

    $response = $this->actingAs($broker)->post(route('transactions.store'), [
        'property_id' => $property->id,
        'client_id' => $client->id,
        'offered_price' => $property->total_price,
        'inquiry_date' => now()->format('Y-m-d'),
    ]);

    $response->assertRedirect()
             ->assertSessionHas('error');

    $this->assertDatabaseMissing('transactions', [
        'property_id' => $property->id,
        'client_id' => $client->id
    ]);
}
```

---

## Technical Architecture

### Validation Layers

```
┌─────────────────────────────────────────┐
│         User Interface (Vue)            │
│  - Visual warnings                      │
│  - Disabled buttons                     │
│  - Tooltips                             │
└──────────────┬──────────────────────────┘
               │ Request
               ▼
┌─────────────────────────────────────────┐
│         Controller Layer (PHP)          │
│  - InquiryController::accept()          │
│  - TransactionController::store()       │
│  - Property status validation           │
└──────────────┬──────────────────────────┘
               │ Validated
               ▼
┌─────────────────────────────────────────┐
│         Model Layer (Eloquent)          │
│  - Property::hasAssignedClient()        │
│  - Property::unavailable_reason         │
└─────────────────────────────────────────┘
```

### Data Flow

1. **Page Load**:

    - Controller loads inquiry with property relationship
    - Property status included in response
    - Frontend receives property data

2. **Frontend Check**:

    - Computed property evaluates status
    - UI updates based on availability
    - User sees appropriate warnings/buttons

3. **Form Submission** (if user bypasses frontend):

    - Request sent to backend
    - Controller validates property status
    - If unavailable: redirect with error
    - If available: proceed with creation

4. **Database Integrity**:
    - Only available properties get new transactions
    - Property status remains authoritative source
    - No orphaned or conflicting records

---

## Benefits

### 1. Data Integrity

-   Prevents double-booking of properties
-   Ensures property status accurately reflects availability
-   Eliminates conflicting transaction records

### 2. User Experience

-   **Proactive**: Brokers see warnings before attempting action
-   **Clear**: Explanatory messages explain why action is blocked
-   **Efficient**: Saves broker time by preventing futile attempts

### 3. Business Logic

-   Enforces proper transaction workflow
-   Maintains clear client-property assignments
-   Supports accurate reporting and analytics

### 4. Maintainability

-   Centralized logic in Property model
-   Reusable methods across controllers
-   Consistent validation approach
-   Easy to extend for future statuses

---

## Future Enhancements

### Potential Improvements

1. **Email Notifications**:

    - Notify broker when property becomes unavailable
    - Send alerts if inquiry is for unavailable property

2. **Inquiry Auto-Closure**:

    - Automatically close inquiries when property status changes
    - Provide option to suggest alternative properties

3. **Admin Override**:

    - Allow administrators to force-accept in special cases
    - Require justification/notes for override

4. **Property Status Audit Log**:

    - Track all status changes with timestamps
    - Record who changed status and why

5. **Alternative Property Suggestions**:
    - When property unavailable, suggest similar available properties
    - AI-powered matching based on inquiry preferences

---

## Troubleshooting

### Common Issues

#### Issue: Warning appears but property shows as available

**Cause**: Frontend cache or stale data  
**Solution**: Hard refresh page (Ctrl+F5) to reload latest property status

#### Issue: Button still clickable despite warning

**Cause**: JavaScript error preventing computed property from working  
**Solution**: Check browser console for errors, ensure Vue is loaded properly

#### Issue: Backend validation not triggering

**Cause**: Property relationship not loaded  
**Solution**: Ensure `$inquiry->load('property')` is called before validation

#### Issue: Wrong status message displayed

**Cause**: Property status not in expected format  
**Solution**: Verify property status is one of: reserved, under_negotiation, sold, pending

---

## Contact & Support

For questions or issues with this validation system:

-   Review this documentation
-   Check automated tests for usage examples
-   Consult Property model for available methods
-   Test in staging environment before production changes

---

**Last Updated**: October 31, 2025  
**Version**: 1.0  
**Author**: GeoCasa Development Team
