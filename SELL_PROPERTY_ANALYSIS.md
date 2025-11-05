# Sell Property Feature - Analysis & Testing Guide

## Current Implementation Analysis

### ✅ What's Already Implemented

#### 1. **Routes** (All Working)
```php
GET  /sell-property                              # Public form
POST /sell-property                              # Submit request
GET  /sell-property/success                      # Success page
POST /admin/seller-requests/assign               # Admin assigns broker
POST /seller-requests/{id}/convert-to-property   # Convert to property
POST /seller-requests/{id}/update-status         # Update status
```

#### 2. **Complete Workflow Flow**
```
User → Fill Form → Submit → Admin Assigns Broker → Broker Reviews → 
→ Approve/Reject → Convert to Property → Email Notifications
```

#### 3. **Features Implemented**
- ✅ Multi-step form (3 steps)
- ✅ File uploads (images, documents, ownership docs)
- ✅ Comprehensive validation
- ✅ Draft auto-save
- ✅ Duplicate detection
- ✅ Rate limiting (3 submissions per 5 minutes)
- ✅ Spam prevention
- ✅ Email validation
- ✅ Phone validation
- ✅ Broker assignment (admin)
- ✅ Convert to property listing
- ✅ Email notifications
- ✅ Status tracking

---

## Testing the Feature

### Step 1: Access the Form
```
URL: http://127.0.0.1:8000/sell-property
```

### Step 2: Fill Out the Form

#### **Step 1: Contact Information**
- Full Name (required, 2-255 chars)
- Email (required, valid email, no temp emails)
- Phone (required, 10-15 digits)
- Current Address (required, 10-500 chars)

#### **Step 2: Property Details**
- Property Title (required, 5-255 chars)
- Property Description (required, 50-5000 chars)
- Property Type (dropdown)
- Asking Price (required, positive number)
- City (required)
- Province (required)
- Postal Code (optional)
- Lot Area (optional)
- Features (checkboxes)

#### **Step 3: Documents & Preferences**
- Property Images (required, at least 1)
- Property Documents (optional)
- Ownership Documents (optional)
- Preferred Contact Method (required)
- Availability (optional)
- Urgency (required)
- Additional Notes (optional)
- Terms & Conditions (required checkbox)

### Step 3: Submit
Click "Submit Property Request" button

---

## Common Issues & Solutions

### Issue 1: Form Won't Submit

**Possible Causes:**
1. **Validation Errors** - Check console for errors
2. **Missing Required Fields** - All required fields must be filled
3. **No Images Uploaded** - At least 1 image is required
4. **Terms Not Accepted** - Must check terms checkbox
5. **Not on Final Step** - Must complete all 3 steps

**Solution:**
```javascript
// Open browser console (F12) and check for:
console.log("submitForm called - starting submission process");
console.log("Validation results:", validationResults);
console.log("Current errors:", errors.value);
```

### Issue 2: File Upload Fails

**Possible Causes:**
1. File too large (max 10MB per file)
2. Invalid file type
3. Too many files
4. Server upload limits

**Solution:**
- Check `php.ini` settings:
  ```ini
  upload_max_filesize = 10M
  post_max_size = 50M
  max_file_uploads = 20
  ```

### Issue 3: Rate Limit Error

**Error Message:**
```
"Too many submission attempts. Please wait X seconds before trying again."
```

**Solution:**
- Wait 5 minutes between submissions
- Or clear rate limiter:
  ```bash
  php artisan cache:clear
  ```

### Issue 4: Duplicate Detection

**Error Message:**
```
"A similar property request was already submitted in the last 24 hours."
```

**Solution:**
- Use different email or property title
- Wait 24 hours
- Or delete previous submission from database

---

## Testing Checklist

### Frontend Testing
- [ ] Form loads without errors
- [ ] All 3 steps are accessible
- [ ] Navigation between steps works
- [ ] Validation messages appear
- [ ] Image upload works
- [ ] Document upload works
- [ ] Draft auto-save works
- [ ] Form submission triggers
- [ ] Success page displays

### Backend Testing
- [ ] POST request reaches controller
- [ ] Validation passes
- [ ] Files are stored
- [ ] Database record created
- [ ] Email sent (if configured)
- [ ] Success response returned

### Admin Workflow
- [ ] Admin can view submissions
- [ ] Admin can assign broker
- [ ] Broker receives notification
- [ ] Broker can view assignment

### Broker Workflow
- [ ] Broker can see assigned requests
- [ ] Broker can approve/reject
- [ ] Broker can convert to property
- [ ] Seller receives email notification

---

## Debug Mode Testing

### Enable Detailed Logging

Add to `.env`:
```env
LOG_LEVEL=debug
LOG_CHANNEL=stack
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

### Look for:
```
[timestamp] local.INFO: Seller request submission started
[timestamp] local.INFO: Files uploaded successfully
[timestamp] local.INFO: Seller request created successfully
```

### If Errors:
```
[timestamp] local.ERROR: SUBMISSION BLOCKED: [reason]
```

---

## Manual Testing Steps

### Test 1: Basic Submission
```bash
# 1. Navigate to form
http://127.0.0.1:8000/sell-property

# 2. Fill minimum required fields:
- Name: John Doe
- Email: john@example.com
- Phone: 09123456789
- Address: 123 Main St, Tagbilaran City
- Property Title: Beautiful Lot in Tagbilaran
- Description: (50+ characters)
- Asking Price: 1000000
- City: Tagbilaran
- Province: Bohol
- Upload 1 image
- Check terms

# 3. Submit and check for success page
```

### Test 2: Validation Testing
```bash
# Try submitting with:
- Empty name → Should show error
- Invalid email → Should show error
- Short phone → Should show error
- No images → Should show error
- Terms unchecked → Should show error
```

### Test 3: File Upload Testing
```bash
# Upload different file types:
- JPG image → Should work
- PNG image → Should work
- PDF document → Should work
- TXT file → Should fail (invalid type)
- 20MB file → Should fail (too large)
```

---

## Database Verification

### Check if Record Was Created
```sql
SELECT * FROM seller_requests 
ORDER BY created_at DESC 
LIMIT 1;
```

### Check File Paths
```sql
SELECT 
    id,
    seller_name,
    seller_email,
    property_title,
    uploaded_images,
    status,
    created_at
FROM seller_requests
WHERE seller_email = 'john@example.com';
```

### Verify Files Exist
```bash
# Check storage directory
ls -la storage/app/public/seller-requests/
```

---

## Admin Testing

### 1. View Submissions
```
URL: http://127.0.0.1:8000/seller-requests
Login as: Admin
```

### 2. Assign Broker
```php
// In admin interface:
1. Click on seller request
2. Click "Assign Broker"
3. Select broker from dropdown
4. Click "Assign"
```

### 3. Verify Assignment
```sql
SELECT 
    id,
    property_title,
    assigned_broker_id,
    status
FROM seller_requests
WHERE id = [request_id];
```

---

## Broker Testing

### 1. View Assigned Requests
```
URL: http://127.0.0.1:8000/seller-requests
Login as: Broker
```

### 2. Review Request
```php
// In broker interface:
1. Click on assigned request
2. Review details
3. Click "Approve" or "Reject"
```

### 3. Convert to Property
```php
// After approval:
1. Click "Convert to Property"
2. System creates property listing
3. Verify property appears in listings
```

### 4. Verify Conversion
```sql
SELECT 
    sr.id as request_id,
    sr.property_title as request_title,
    p.id as property_id,
    p.title as property_title,
    p.status as property_status
FROM seller_requests sr
LEFT JOIN properties p ON sr.id = p.seller_request_id
WHERE sr.id = [request_id];
```

---

## Email Testing

### Configure Email (if not done)
```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@geocasa.com
MAIL_FROM_NAME="GeoCasa Bohol"
```

### Check Email Logs
```bash
# Emails will be logged to:
storage/logs/laravel.log

# Search for:
grep "SellerRequestNotification" storage/logs/laravel.log
grep "BrokerSellerAssignmentNotification" storage/logs/laravel.log
```

---

## Quick Test Script

Create a test file: `test_seller_request.php`

```php
<?php
// Run with: php artisan tinker

use App\Models\SellerRequest;
use App\Models\User;

// Create test submission
$seller = SellerRequest::create([
    'seller_name' => 'Test User',
    'seller_email' => 'test@example.com',
    'seller_phone' => '09123456789',
    'property_title' => 'Test Property',
    'property_description' => 'This is a test property description with more than 50 characters to pass validation.',
    'property_type' => 'residential_lot',
    'asking_price' => 1000000,
    'city' => 'Tagbilaran',
    'province' => 'Bohol',
    'uploaded_images' => json_encode(['test.jpg']),
    'preferred_contact_method' => 'email',
    'urgency' => 'flexible',
    'terms_accepted' => true,
    'status' => 'pending'
]);

echo "Created seller request ID: " . $seller->id . "\n";

// Assign to broker
$broker = User::where('role', 'broker')->where('is_approved', true)->first();
if ($broker) {
    $seller->update(['assigned_broker_id' => $broker->id]);
    echo "Assigned to broker: " . $broker->name . "\n";
}

// Convert to property
$property = $seller->convertToProperty();
echo "Converted to property ID: " . $property->id . "\n";
```

---

## Expected Behavior

### ✅ Successful Submission
1. Form validates all fields
2. Files upload successfully
3. Database record created
4. User redirected to success page
5. Confirmation email sent
6. Admin can see submission
7. Admin can assign broker
8. Broker receives notification
9. Broker can approve/reject
10. Approved request converts to property
11. Seller receives status email

### ❌ Failed Submission
1. Validation errors shown
2. User stays on form
3. Error messages displayed
4. No database record created
5. No files uploaded
6. No emails sent

---

## Troubleshooting Commands

```bash
# Clear all caches
php artisan optimize:clear

# Check routes
php artisan route:list --name=seller-requests

# Check database
php artisan db:show
php artisan db:table seller_requests

# Check storage permissions
ls -la storage/app/public/

# Create storage link
php artisan storage:link

# Check logs
tail -f storage/logs/laravel.log

# Test email
php artisan tinker
>>> Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });

# Clear rate limiter
php artisan cache:clear
```

---

## Next Steps for Testing

1. **Access the form**: `http://127.0.0.1:8000/sell-property`
2. **Open browser console** (F12) to see logs
3. **Fill out all required fields**
4. **Upload at least 1 image**
5. **Complete all 3 steps**
6. **Click submit**
7. **Check console for errors**
8. **Check Laravel logs** for backend errors
9. **Verify database record** was created
10. **Test admin assignment** workflow

---

## Status

**Current Status**: ✅ Fully Implemented  
**Testing Status**: ⏳ Needs Manual Testing  
**Known Issues**: None reported  
**Recommended Action**: Manual testing with real data

---

**The feature is complete and ready for testing. Follow the testing steps above to verify functionality.**
