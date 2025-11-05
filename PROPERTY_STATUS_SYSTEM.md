# Property Status Management System

## Overview
Implemented a hybrid status system that keeps sold properties visible for 90 days before auto-archiving them. This provides social proof, broker credibility, and market data while keeping listings clean.

---

## Status Flow

```
AVAILABLE
   ↓ (Offer accepted)
PENDING
   ↓ (Deal closes)
SOLD (visible for 90 days)
   ↓ (After 90 days - automatic)
ARCHIVED (removed from public view)
```

---

## Status Definitions

| Status | Description | Public Visibility | Broker Profile |
|--------|-------------|-------------------|----------------|
| **available** | Property is actively listed | ✅ Yes | Active Listings |
| **pending** | Offer accepted, deal in progress | ✅ Yes (with badge) | Active Listings |
| **sold** | Property sold, within 90 days | ❌ No (search) | Recent Sales |
| **archived** | Sold > 90 days ago | ❌ No | Total Sales Count |
| **reserved** | Temporarily held | ❌ No | - |
| **under_negotiation** | In negotiation | ✅ Yes | Active Listings |
| **off_market** | Not publicly listed | ❌ No | - |

---

## Database Changes

### Migration: `2025_10_25_131400_add_property_status_tracking.php`

**New Fields:**
- `pending_at` (timestamp) - When offer was accepted
- `sold_at` (timestamp) - When property was sold
- `archived_at` (timestamp) - When property was archived
- `sold_price` (decimal) - Final sale price (can differ from listing price)

**Updated Status Enum:**
```php
enum('status', [
    'available', 
    'pending',      // NEW
    'sold', 
    'archived',     // NEW
    'reserved', 
    'under_negotiation', 
    'off_market'
])
```

---

## Model Methods

### Property.php - Status Management

```php
// Mark property as pending
$property->markAsPending();

// Mark property as sold
$property->markAsSold($soldPrice, $transactionId, $clientId);

// Archive property
$property->archive();

// Check if should be archived
$property->shouldBeArchived(); // Returns true if sold > 90 days ago

// Get days since sold
$property->days_since_sold; // Accessor attribute
```

### Query Scopes

```php
// Get only available properties
Property::available()->get();

// Get only pending properties
Property::pending()->get();

// Get only sold properties
Property::sold()->get();

// Get active listings (available + pending)
Property::active()->get();

// Get public listings (exclude archived & off_market)
Property::public()->get();

// Get recently sold (last 6 months)
Property::recentlySold()->get();

// Get properties that should be archived
Property::shouldBeArchived()->get();
```

### Status Check Methods

```php
$property->isAvailable();  // true if status === 'available'
$property->isPending();    // true if status === 'pending'
$property->isSold();       // true if status === 'sold'
$property->isArchived();   // true if status === 'archived'
$property->isActive();     // true if available or pending
```

---

## Automated Tasks

### Command: `properties:archive-sold`

**Purpose:** Auto-archive properties that have been sold for more than 90 days

**Schedule:** Daily at 3:00 AM

**Location:** `app/Console/Commands/ArchiveSoldProperties.php`

**Manual Run:**
```bash
php artisan properties:archive-sold
```

**Registered in:** `routes/console.php`

---

## Controller Updates

### PublicController.php

**Updated Methods:**
- `home()` - Now uses `->available()` scope for featured properties
- `properties()` - Now uses `->active()` scope to show available + pending

**What Changed:**
```php
// Before
Property::where('status', 'available')

// After
Property::active() // Shows available + pending
Property::available() // Shows only available
```

---

## Frontend Implementation (Next Steps)

### 1. Property Listing Badges

**Pending Badge:**
```vue
<div v-if="property.status === 'pending'" class="badge badge-warning">
  Offer Pending
</div>
```

**Sold Badge (for detail pages):**
```vue
<div v-if="property.status === 'sold'" class="banner banner-sold">
  <CheckCircleIcon />
  SOLD - {{ property.sold_at.format('MMMM YYYY') }}
</div>
```

### 2. Broker Profile - Recent Sales Tab

**Show sold properties from last 6 months:**
```vue
<template>
  <div class="broker-sales">
    <div v-for="property in broker.recent_sales" class="sale-card">
      <img :src="property.image" class="blur-sm" />
      <h3>{{ property.title }}</h3>
      <p>{{ property.municipality }}, Bohol</p>
      <p class="text-sm">Sold: {{ property.sold_at.format('MMM DD, YYYY') }}</p>
      <Link :href="route('public.properties')">View Similar →</Link>
    </div>
  </div>
</template>
```

### 3. Broker Stats Update

```vue
<div class="broker-stats">
  <div>
    <h3>{{ broker.active_listings_count }}</h3>
    <p>Active Listings</p>
  </div>
  <div>
    <h3>{{ broker.completed_sales_count }}</h3>
    <p>Completed Sales</p>
  </div>
</div>
```

---

## Usage Examples

### When a transaction is finalized:

```php
// In TransactionController or wherever you finalize transactions
$transaction = Transaction::find($id);
$transaction->update(['status' => 'finalized']);

// Mark property as sold
$property = $transaction->property;
$property->markAsSold(
    soldPrice: $transaction->final_price,
    transactionId: $transaction->id,
    clientId: $transaction->client_id
);
```

### When an offer is accepted:

```php
$property = Property::find($id);
$property->markAsPending();
```

### Manual archive (if needed):

```php
$property = Property::find($id);
$property->archive();
```

---

## Benefits

✅ **Social Proof** - Shows active market  
✅ **Broker Credibility** - Displays successful sales  
✅ **Market Data** - Provides pricing insights  
✅ **Clean Listings** - Auto-removes old sold properties  
✅ **SEO Value** - Sold listings still indexed for 90 days  
✅ **Privacy** - Archived after 90 days  
✅ **Analytics** - All data kept in database forever  

---

## Testing

### Run Migration:
```bash
php artisan migrate
```

### Test Auto-Archive Command:
```bash
# Dry run to see what would be archived
php artisan properties:archive-sold

# Check scheduled tasks
php artisan schedule:list
```

### Test Scopes:
```php
// In tinker
php artisan tinker

// Test scopes
Property::active()->count();
Property::sold()->count();
Property::shouldBeArchived()->count();

// Test methods
$property = Property::first();
$property->markAsSold(5000000);
$property->shouldBeArchived();
```

---

## Next Steps

1. ✅ Run migration: `php artisan migrate`
2. ⏳ Update broker profile to show "Recent Sales" tab
3. ⏳ Add "Offer Pending" badge to property cards
4. ⏳ Add "SOLD" banner to sold property detail pages
5. ⏳ Update broker stats to show completed sales count
6. ⏳ Test auto-archive command
7. ⏳ Update property management dashboard to show status

---

## Files Modified/Created

**Created:**
- `database/migrations/2025_10_25_131400_add_property_status_tracking.php`
- `app/Console/Commands/ArchiveSoldProperties.php`
- `PROPERTY_STATUS_SYSTEM.md` (this file)

**Modified:**
- `app/Models/Property.php` - Added status methods and scopes
- `app/Http/Controllers/PublicController.php` - Updated to use new scopes
- `routes/console.php` - Added archive schedule

---

## Support

For questions or issues, refer to:
- Property Model: `app/Models/Property.php`
- Archive Command: `app/Console/Commands/ArchiveSoldProperties.php`
- This documentation: `PROPERTY_STATUS_SYSTEM.md`
