# Seller Request to Property Mapping Fix

## Issue Identified

Properties created from seller requests were showing inaccurate or missing information on the public property detail page. This was caused by field name mismatches during the conversion from SellerRequest to Property.

## Root Cause Analysis

### Field Mapping Issues

The conversion logic was using non-existent or incorrectly mapped fields:

**Incorrect Mappings (BEFORE):**

-   ❌ `property_location` → doesn't exist in SellerRequest
-   ❌ `property_address` → doesn't exist in SellerRequest
-   ❌ `city` → Property model doesn't have this in fillable
-   ❌ `state` → Property model doesn't have this in fillable
-   ❌ `zip_code` → Property model doesn't have this in fillable
-   ❌ `latitude` → Property expects `coordinates_lat`
-   ❌ `longitude` → Property expects `coordinates_lng`
-   ❌ `area_unit` → Property doesn't have this in fillable

### Property Model Expected Fields

According to `app/Models/Property.php`, the fillable fields are:

```php
protected $fillable = [
    'title', 'slug', 'description', 'type', 'status', 'price_per_sqm', 'total_price',
    'address', 'municipality', 'barangay', 'lot_area_sqm', 'lot_area_hectares',
    'title_type', 'title_number', 'tax_declaration_number', 'coordinates_lat',
    'coordinates_lng', 'road_access', 'water_source', 'electricity_available',
    'internet_available', 'nearby_landmarks', 'zoning_classification',
    'images', 'documents', 'is_featured', 'broker_id', 'client_id',
    // ...
];
```

### SellerRequest Model Available Fields

According to `app/Models/SellerRequest.php`, the available fields are:

```php
protected $fillable = [
    'client_id', 'name', 'email', 'phone', 'address',
    'property_title', 'property_description', 'property_type',
    'asking_price', 'city', 'province', 'postal_code',
    'municipality', 'barangay', 'lot_area', 'floor_area',
    'bedrooms', 'bathrooms', 'price_expectation', 'description',
    'contact_name', 'contact_email', 'contact_phone',
    'features', 'uploaded_images', 'images',
    'property_documents', 'documents', 'ownership_documents',
    // ...
];
```

## Solution Implemented

### Corrected Field Mappings

**Files Modified:**

-   `app/Http/Controllers/SellerRequestController.php`
    -   Method: `updateStatus()` (lines ~634-652)
    -   Method: `convertToProperty()` (lines ~732-750)

**Correct Mappings (AFTER):**

```php
Property::create([
    'slug' => Str::slug($sellerRequest->property_title . '-' . time()),
    'title' => $sellerRequest->property_title,
    'description' => $sellerRequest->property_description,
    'type' => $sellerRequest->property_type ?? 'residential_lot',
    'status' => 'available',
    'price_per_sqm' => $pricePerSqm ?? 0,
    'total_price' => $totalPrice ?? 0,
    'lot_area_sqm' => $lotAreaSqm ?? 0,
    'lot_area_hectares' => $lotAreaSqm ? round($lotAreaSqm / 10000, 4) : 0,
    'address' => $sellerRequest->address,                              // ✅ Using 'address' from SellerRequest
    'municipality' => $sellerRequest->municipality ?? $sellerRequest->city, // ✅ Using 'municipality' with fallback to 'city'
    'barangay' => $sellerRequest->barangay,                           // ✅ Using 'barangay'
    'images' => $sellerRequest->uploaded_images ?? $sellerRequest->images, // ✅ Using correct image field with fallback
    'broker_id' => $sellerRequest->assigned_broker_id ?? $user->id,
    'is_featured' => false
]);
```

### Key Changes

1. **Address Field:**

    - ❌ Before: `'address' => $sellerRequest->property_address`
    - ✅ After: `'address' => $sellerRequest->address`

2. **Municipality Field:**

    - ❌ Before: `'city' => $sellerRequest->city`
    - ✅ After: `'municipality' => $sellerRequest->municipality ?? $sellerRequest->city`

3. **Barangay Field:**

    - ❌ Before: Not mapped at all
    - ✅ After: `'barangay' => $sellerRequest->barangay`

4. **Lot Area in Hectares:**

    - ❌ Before: Not computed
    - ✅ After: `'lot_area_hectares' => $lotAreaSqm ? round($lotAreaSqm / 10000, 4) : 0`

5. **Images:**

    - ❌ Before: `'images' => $sellerRequest->uploaded_images`
    - ✅ After: `'images' => $sellerRequest->uploaded_images ?? $sellerRequest->images`

6. **Removed Non-Fillable Fields:**

    - Removed: `area_unit`, `location`, `city`, `state`, `zip_code`, `latitude`, `longitude`
    - These were being silently ignored by Laravel's mass assignment protection

7. **Default Property Type:**
    - ❌ Before: `'land'` (not a valid type in Property::TYPES)
    - ✅ After: `'residential_lot'` (valid default type)

## Public Display Impact

The public property detail page (`resources/js/Pages/Public/PropertyDetail.vue`) displays:

-   `property.municipality` → Now correctly populated ✅
-   `property.formatted_area` → Now correctly shows lot_area_sqm ✅
-   `property.title_type` → Available for future mapping
-   `property.zoning_classification` → Available for future mapping
-   Images via `safeImages` → Now correctly mapped ✅

## Testing Recommendations

### Test Case 1: New Seller Request Approval

1. Create a new seller request with complete information
2. Approve the request (auto-converts to property)
3. Verify the public property page shows:
    - Correct municipality
    - Correct lot area (e.g., "500 sqm" or "1.5 hectares")
    - Correct address and barangay
    - All uploaded images

### Test Case 2: Manual Property Conversion

1. Have a pending/approved seller request
2. Admin manually converts it via `convertToProperty()`
3. Verify all fields map correctly as above

### Test Case 3: Legacy Data

1. Properties created before this fix may have blank municipalities
2. Consider running a one-time migration to backfill from client data if needed

## Future Enhancements

### Recommended Additional Mappings

If the seller request form collects these fields, they should be mapped:

-   `title_type` → Property title type (e.g., "Titled", "Tax Declaration")
-   `zoning_classification` → Zoning info
-   `coordinates_lat` / `coordinates_lng` → GPS coordinates
-   `road_access`, `water_source`, `electricity_available` → Infrastructure details

### Seller Request Form Enhancement

Consider adding these fields to the seller request form:

-   Title type dropdown
-   Zoning classification
-   GPS coordinates (via map picker)
-   Infrastructure checkboxes (road access, utilities)

## Impact Summary

**Before Fix:**

-   Municipality: Empty/N/A
-   Lot Area: "0 sqm"
-   Address: Missing
-   Barangay: Missing
-   Type: Sometimes invalid default

**After Fix:**

-   Municipality: ✅ Correctly populated from seller data
-   Lot Area: ✅ Shows actual sqm/hectares
-   Address: ✅ Shows seller's address
-   Barangay: ✅ Included when available
-   Type: ✅ Valid property type with proper fallback

## Files Changed

1. `app/Http/Controllers/SellerRequestController.php`
    - `updateStatus()` method (auto-conversion on approval)
    - `convertToProperty()` method (manual admin conversion)

## Validation

-   ✅ No syntax errors
-   ✅ All mapped fields exist in both SellerRequest and Property models
-   ✅ Uses only fillable fields in Property model
-   ✅ Includes proper fallbacks for optional fields

---

**Fix Date:** January 2025  
**Issue:** Property data mapping inconsistency  
**Status:** ✅ Resolved
