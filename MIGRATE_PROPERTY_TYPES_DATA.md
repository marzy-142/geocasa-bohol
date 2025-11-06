# Migrate Property Types Data

## Problem
Existing properties have data in the old `type` column but not in the new `types` JSON column, causing filters to return no results.

## Solution

### Step 1: Check Current State
```bash
php artisan tinker
```

```php
// Check a property
$prop = \App\Models\Property::first();
echo "Type: " . $prop->type . "\n";
echo "Types: " . json_encode($prop->types) . "\n";

// Count properties without types
$count = \App\Models\Property::whereNull('types')->count();
echo "Properties without types: " . $count . "\n";
exit
```

### Step 2: Run Migrations (if not done)
```bash
php artisan migrate
```

### Step 3: Manual Data Migration (if needed)

If properties still don't have `types` data, run this:

```bash
php artisan tinker
```

```php
// Migrate all properties with type but no types
DB::table('properties')
    ->whereNotNull('type')
    ->whereNull('types')
    ->update([
        'types' => DB::raw("JSON_ARRAY(type)")
    ]);

// Verify
$count = \App\Models\Property::whereNotNull('types')->count();
echo "Properties with types: " . $count . "\n";

// Check a sample
$prop = \App\Models\Property::first();
echo "Type: " . $prop->type . "\n";
echo "Types: " . json_encode($prop->types) . "\n";
exit
```

### Step 4: Verify Filtering Works

After migration, properties should have:
```json
{
  "type": "residential_lot",
  "types": ["residential_lot"]
}
```

### Alternative: Quick SQL Fix

If tinker doesn't work, use SQL directly:

```sql
-- Check current state
SELECT id, title, type, types FROM properties LIMIT 5;

-- Migrate data
UPDATE properties 
SET types = JSON_ARRAY(type) 
WHERE type IS NOT NULL AND types IS NULL;

-- Verify
SELECT id, title, type, types FROM properties LIMIT 5;
```

### Step 5: Test Filtering

1. Go to `/browse-properties`
2. Select a property type
3. Should now show filtered results ✅

## Expected Result

After migration:
- ✅ All properties have `types` JSON array
- ✅ Filtering works correctly
- ✅ No "No properties found" errors
