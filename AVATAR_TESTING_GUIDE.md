# Testing Global Avatar System

## Quick Test Guide

**Date:** October 29, 2025

## Test Scenario 1: Upload Avatar

### Steps:

1. Navigate to **Account Settings** (click your name in the header → Account Settings)
2. Go to the **Profile** tab
3. Click **Choose File** under the avatar section
4. Select an image (JPG, PNG, GIF, or WebP)
5. Click **Upload Avatar**

### Expected Results:

✅ Success message appears: "Profile picture updated successfully"
✅ Avatar preview shows new image immediately
✅ **Navigation bar avatar** updates automatically (top-right corner)
✅ No page refresh required
✅ No console errors

### Verification:

-   Check navigation bar (top-right) - should show your new avatar
-   Open Messages (if you have any) - should show your avatar in message bubbles
-   Refresh the page - avatar should persist (not show initials)

---

## Test Scenario 2: Delete Avatar

### Steps:

1. In Account Settings → Profile tab
2. Click **Remove Avatar** button
3. Confirm deletion in popup

### Expected Results:

✅ Success message appears
✅ Avatar preview shows your **initials** (first letter of first and last name)
✅ Navigation bar shows initials with colored background
✅ All avatars across the system revert to initials

### Verification:

-   Navigation bar shows colored circle with initials
-   Messages show initials instead of photo
-   Refresh page - initials persist

---

## Test Scenario 3: Cache-Busting Test

### Steps:

1. Upload an avatar (any image)
2. Wait for success confirmation
3. Upload a **different** image
4. Wait for success confirmation

### Expected Results:

✅ Second image replaces first image **immediately**
✅ No delay or loading issues
✅ Browser doesn't show old/cached image
✅ URL includes timestamp parameter: `?v=1234567890`

### Verification:

-   Right-click avatar in navigation bar → Inspect Element
-   Check the image URL - should have `?v=` parameter with timestamp
-   Upload different image - timestamp should change

---

## Test Scenario 4: Global Propagation

### Steps:

1. Upload an avatar
2. **Without refreshing**, navigate to different pages:
    - Dashboard
    - Messages
    - Properties
    - Transactions
    - Back to Account Settings

### Expected Results:

✅ Avatar shows on **ALL pages** immediately
✅ No need to refresh any page
✅ Consistent avatar display everywhere
✅ Navigation bar always shows current avatar

---

## Test Scenario 5: File Validation

### Test Invalid Files:

#### Test 5A: File Too Large

1. Try to upload image > 2MB
2. **Expected:** Error message: "Image size must not exceed 2MB"

#### Test 5B: Wrong File Type

1. Try to upload PDF, DOCX, or other non-image file
2. **Expected:** Error message about file type

#### Test 5C: Image Too Small

1. Try to upload image < 100x100 pixels
2. **Expected:** Error message: "Image must be between 100x100 and 2000x2000 pixels"

#### Test 5D: Valid Upload

1. Upload JPEG, PNG, GIF, or WebP between 100x100 and 2000x2000 pixels, under 2MB
2. **Expected:** Successful upload

---

## Browser Testing

### Test in Multiple Browsers:

-   [ ] Chrome/Edge
-   [ ] Firefox
-   [ ] Safari
-   [ ] Mobile Safari (iOS)
-   [ ] Chrome Mobile (Android)

### For Each Browser:

1. Upload avatar
2. Verify it appears in navigation
3. Delete avatar
4. Verify initials appear
5. Check browser console (F12) - should have NO errors

---

## Mobile Responsive Testing

### On Mobile Device:

1. Open site on phone/tablet
2. Navigate to Account Settings
3. Upload avatar from camera or gallery
4. Verify avatar appears correctly sized
5. Check navigation menu shows avatar

### Expected:

✅ Avatar is properly sized for mobile
✅ Touch interactions work smoothly
✅ File picker works (camera option available on mobile)

---

## Performance Testing

### Test Load Speed:

1. Upload avatar
2. Navigate away and back to Account Settings
3. Time how long avatar takes to load

### Expected:

✅ Avatar loads in < 1 second
✅ No flickering between initials and avatar
✅ Smooth transitions

### Test Network:

1. Open DevTools → Network tab
2. Upload avatar
3. Check requests

### Expected:

✅ Only ONE image upload request
✅ Image URL has cache-busting parameter
✅ Subsequent page loads fetch fresh avatar

---

## Console Testing

### Before Starting Tests:

1. Press **F12** to open DevTools
2. Go to **Console** tab
3. Clear any existing messages

### During All Tests:

Monitor console for:

-   ❌ No 404 errors
-   ❌ No "Failed to load resource" errors
-   ❌ No JavaScript errors
-   ✅ Only normal Inertia navigation logs (if any)

---

## Troubleshooting

### If Avatar Doesn't Update:

**Step 1:** Hard Refresh Browser

-   Windows: `Ctrl + Shift + R`
-   Mac: `Cmd + Shift + R`

**Step 2:** Check Storage Link

```bash
php artisan storage:link
```

**Step 3:** Clear All Caches

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

**Step 4:** Check File Permissions

```bash
# On Linux/Mac
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# On Windows
# Ensure IIS_IUSRS or NETWORK SERVICE has write access
```

**Step 5:** Verify Storage Directory

-   Check that `storage/app/public/avatars` exists
-   Check that `public/storage` symlink exists
-   Verify uploaded files are in `storage/app/public/avatars`

### If Initials Don't Show:

**Check:**

1. User name is set correctly in database
2. Browser console for JavaScript errors
3. UserAvatar component is imported correctly

### If Cache-Busting Not Working:

**Check:**

1. URL has `?v=` parameter
2. Timestamp changes with each upload
3. Browser cache is not set to aggressive caching
4. CDN (if any) is configured to respect query parameters

---

## Success Criteria

All tests pass if:

✅ **Immediate Updates:** Avatar changes reflect across all pages instantly
✅ **No Caching Issues:** New avatars always show (never cached old versions)
✅ **Graceful Fallbacks:** Initials display when no avatar exists
✅ **Consistent Design:** Avatar size and style appropriate for each location
✅ **No Errors:** Browser console clean of errors
✅ **Mobile Compatible:** Works on all devices and screen sizes
✅ **Fast Performance:** Avatar loads quickly, no lag
✅ **Proper Validation:** Invalid files rejected with clear messages

---

## Common Issues & Solutions

| Issue                                 | Cause                     | Solution                              |
| ------------------------------------- | ------------------------- | ------------------------------------- |
| Avatar shows as initials after upload | Storage link broken       | Run `php artisan storage:link`        |
| Old avatar still showing              | Browser cache             | Hard refresh: Ctrl+Shift+R            |
| 404 error on avatar URL               | Symlink missing           | Check `public/storage` exists         |
| Upload fails silently                 | File too large/wrong type | Check file meets requirements         |
| Avatar updates only after refresh     | Cache-busting not working | Check `avatar_url` includes timestamp |
| Initials showing "?"                  | User name missing         | Update user profile with name         |

---

## Advanced Testing

### Test Concurrent Updates:

1. Open site in two browser windows
2. Upload avatar in Window 1
3. Refresh Window 2
4. **Expected:** Avatar shows in both windows

### Test Database Consistency:

```sql
SELECT id, name, avatar FROM users WHERE id = YOUR_USER_ID;
```

**Expected:** Avatar column shows path like `avatars/2_1730194532.jpg`

### Test File Cleanup:

1. Upload avatar A
2. Check `storage/app/public/avatars` - should have 1 file
3. Upload avatar B
4. Check directory - should still have 1 file (old deleted)
5. **Expected:** Old files automatically removed

---

## Test Results Log

**Tester:** ****\_\_\_****  
**Date:** ****\_\_\_****  
**Browser:** ****\_\_\_****

| Test               | Status          | Notes |
| ------------------ | --------------- | ----- |
| Upload Avatar      | ⬜ Pass ⬜ Fail |       |
| Delete Avatar      | ⬜ Pass ⬜ Fail |       |
| Cache-Busting      | ⬜ Pass ⬜ Fail |       |
| Global Propagation | ⬜ Pass ⬜ Fail |       |
| File Validation    | ⬜ Pass ⬜ Fail |       |
| Mobile Responsive  | ⬜ Pass ⬜ Fail |       |
| Performance        | ⬜ Pass ⬜ Fail |       |
| Console Clean      | ⬜ Pass ⬜ Fail |       |

**Overall Status:** ⬜ All Tests Passed ⬜ Issues Found

**Issues Found:**

---

---

---

---

## Next Steps After Testing

If all tests pass:

1. ✅ System is ready for production
2. ✅ Consider implementing recommended enhancements (see GLOBAL_AVATAR_SYSTEM_IMPLEMENTATION.md)
3. ✅ Monitor error logs for any upload issues

If tests fail:

1. ❌ Document specific errors
2. ❌ Check troubleshooting section
3. ❌ Review implementation files
4. ❌ Ask for support if needed
