# Account Settings - Quick Start Guide

## Accessing Account Settings

For all user types (Admin, Broker, Client), access account settings at:

```
http://localhost:8000/account/settings
```

## Quick Test Scenarios

### Test 1: Profile Picture Upload

1. Log in as any user
2. Navigate to `/account/settings`
3. Click "Change Photo" button
4. Select an image file (JPG/PNG/GIF, max 2MB)
5. Preview should appear immediately
6. Click "Upload" button
7. ✅ Success message should appear
8. ✅ Avatar should be visible in the UI

### Test 2: Update Profile Information

1. Stay on Profile tab
2. Edit your name, email, or phone
3. Add an address and bio
4. Click "Save Changes"
5. ✅ Success message: "Profile updated successfully"
6. Refresh the page
7. ✅ Changes should persist

### Test 3: Change Password

1. Click "Security" tab in sidebar
2. Enter your current password
3. Enter a new strong password:
    - At least 8 characters
    - Mix of uppercase and lowercase
    - At least one number
    - At least one special character (@, !, #, etc.)
4. Confirm the new password
5. Click "Update Password"
6. ✅ Success message should appear
7. Log out and try logging in with the new password
8. ✅ Login should work with new password

### Test 4: Notification Preferences

1. Click "Notifications" tab
2. Toggle "Email Notifications" on/off
3. Enable "New Messages" notification
4. Disable "Payment Reminders" notification
5. Click "Save Preferences"
6. ✅ Success message should appear
7. Refresh page
8. ✅ Your selections should be remembered

### Test 5: Privacy Settings

1. Click "Privacy" tab
2. Change "Profile Visibility" to "Contacts Only"
3. Uncheck "Show Email Address"
4. Keep "Show Phone Number" checked
5. Click "Save Settings"
6. ✅ Success message should appear
7. ✅ Settings should persist after refresh

### Test 6: Avatar Removal

1. Go back to "Profile" tab
2. Click "Remove" button under avatar
3. Confirm the action
4. ✅ Avatar should be removed
5. ✅ Initials badge should appear instead

### Test 7: Account Deactivation (Optional - be careful!)

1. Click "Danger Zone" tab
2. Scroll to "Deactivate Account" section
3. Enter your password
4. Optionally add a reason
5. Click "Deactivate Account"
6. Confirm in the popup
7. ✅ You should be logged out
8. ✅ Try logging back in - account should reactivate

## Testing as Different User Roles

### As Admin

1. All features available
2. Try updating profile with admin privileges
3. Check that you cannot delete your account if you're the only admin

### As Broker

1. Notice "New Inquiries" option in Notifications tab
2. Upload a professional profile picture
3. Update your bio with broker-specific information

### As Client

1. Update your contact information
2. Notice that Client model also gets updated
3. Set privacy preferences for contact visibility

## Common Issues and Fixes

### Issue: Avatar not uploading

**Fix:**

```bash
# Run this command to create symbolic link
php artisan storage:link

# Check folder permissions
# Make sure storage/app/public/avatars exists and is writable
```

### Issue: Password validation fails

**Check:**

-   Current password is correct
-   New password meets all requirements:
    -   ✓ 8+ characters
    -   ✓ Uppercase letter
    -   ✓ Lowercase letter
    -   ✓ Number
    -   ✓ Special character

### Issue: Changes not saving

**Fix:**

-   Clear browser cache
-   Check browser console for errors (F12)
-   Verify you're logged in
-   Check network tab for failed requests

### Issue: Page not loading

**Fix:**

```bash
# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Rebuild frontend
npm run dev
```

## Verification Checklist

After implementing, verify:

-   [ ] Can access `/account/settings` while logged in
-   [ ] Cannot access settings page while logged out (redirects to login)
-   [ ] All 5 tabs are visible (Profile, Security, Notifications, Privacy, Danger Zone)
-   [ ] Profile tab shows current user data
-   [ ] Avatar upload works and shows preview
-   [ ] Avatar removal works
-   [ ] Profile form validates email uniqueness
-   [ ] Password form enforces strong password rules
-   [ ] Notification preferences save correctly
-   [ ] Privacy settings persist
-   [ ] Success messages appear after each update
-   [ ] Validation errors display properly
-   [ ] Loading states work (buttons disabled during processing)
-   [ ] Deactivation logs user out
-   [ ] Role-specific features appear correctly

## Database Verification

Check that data is saving correctly:

```bash
# Open Tinker
php artisan tinker

# Check a user's settings
$user = User::find(1);
$user->avatar;                      // Should show file path
$user->notification_preferences;    // Should show array
$user->privacy_settings;           // Should show array
$user->is_active;                  // Should be true
```

## Next Steps

1. **Add to Navigation:** Link to settings from user dropdown menu
2. **Middleware:** Add check for `is_active` status in authentication
3. **Email Notifications:** Send email when profile is updated
4. **Audit Log:** Track all settings changes
5. **Account Recovery:** Implement reactivation flow

## Support

If you encounter issues:

1. Check the browser console (F12) for JavaScript errors
2. Check `storage/logs/laravel.log` for backend errors
3. Verify database migrations ran successfully: `php artisan migrate:status`
4. Ensure all dependencies are installed: `composer install && npm install`

---

**Ready to test!** Start with Test 1 (Profile Picture Upload) and work through each scenario.
