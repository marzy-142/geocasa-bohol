# Professional Profile - Quick Testing Guide

## 🧪 Testing the Professional Profile Feature

### Prerequisites

-   Have a broker account ready
-   Clear browser cache
-   Ensure you're logged in as a broker

---

## Test Scenario 1: Navigation & Access

### Steps:

1. Log in as a broker
2. Navigate to **Dashboard** → **Account** → **Settings**
3. Look for **"Professional Profile"** tab in the sidebar

**Expected Results**:

-   ✅ Professional Profile tab appears between "Privacy" and "Danger Zone"
-   ✅ Tab has briefcase icon
-   ✅ Clicking tab shows professional profile form

**If you're NOT a broker**:

-   ❌ Professional Profile tab should NOT appear
-   ❌ Direct URL access should show error

---

## Test Scenario 2: Bio Field

### Steps:

1. Click on Professional Profile tab
2. Find the "Professional Bio" textarea
3. Type a sample bio (e.g., "Experienced real estate broker specializing in beachfront properties...")
4. Watch the character counter

**Expected Results**:

-   ✅ Textarea accepts input
-   ✅ Character counter updates live (e.g., "125/1000")
-   ✅ Placeholder text visible when empty
-   ✅ Can type up to 1000 characters
-   ✅ Form validation prevents submission if over 1000 chars

**Sample Bio to Copy**:

```
Experienced real estate broker with 5+ years in Bohol's property market. I specialize in helping clients find their dream beachfront properties and investment opportunities. Known for exceptional customer service and deep knowledge of Panglao and surrounding areas. Let's find your perfect property together!
```

---

## Test Scenario 3: Property Specializations

### Steps:

1. Scroll to "Property Specializations" section
2. Check 2-3 specializations (e.g., Residential, Beach & Resort, Investment)
3. Observe the selected count
4. Uncheck one
5. Check another

**Expected Results**:

-   ✅ Checkboxes toggle on/off
-   ✅ Selection count updates (e.g., "3 selected")
-   ✅ Background highlights on hover
-   ✅ Can select multiple options
-   ✅ Can deselect all (optional field)

**Recommended Selections for Testing**:

-   ☑ Residential Properties
-   ☑ Beach & Resort Properties
-   ☑ Investment Properties

---

## Test Scenario 4: Service Areas

### Steps:

1. Scroll to "Service Areas in Bohol" section
2. Check several municipalities (e.g., Tagbilaran City, Panglao, Dauis, Baclayon)
3. Scroll within the container to see all options
4. Observe the selected count

**Expected Results**:

-   ✅ Container scrollable (shows scroll bar if needed)
-   ✅ All 44 municipalities visible
-   ✅ Selection count updates (e.g., "4 municipality/municipalities selected")
-   ✅ Can select multiple
-   ✅ Checkboxes work correctly

**Recommended Selections for Testing**:

-   ☑ Tagbilaran City
-   ☑ Panglao
-   ☑ Dauis
-   ☑ Baclayon
-   ☑ Loboc

---

## Test Scenario 5: Online Presence

### Steps:

1. Scroll to "Online Presence" section
2. Enter a website URL: `https://mybrokeragesite.com`
3. Enter Facebook URL: `https://facebook.com/yourprofile`
4. Enter LinkedIn URL: `https://linkedin.com/in/yourprofile`
5. Try entering an invalid URL (e.g., "not-a-url")

**Expected Results**:

-   ✅ Valid URLs accepted
-   ✅ Invalid URLs show error message
-   ✅ All three fields optional (can leave blank)
-   ✅ URL validation works

**Sample URLs for Testing**:

```
Website: https://boholrealestate.com
Facebook: https://facebook.com/boholbroker
LinkedIn: https://linkedin.com/in/juandelacruz
```

---

## Test Scenario 6: Availability Status

### Steps:

1. Scroll to "Availability Status" section
2. Click on each radio button option:
    - 🟢 Available
    - 🟡 Limited Availability
    - 🔴 Unavailable
3. Observe the visual changes

**Expected Results**:

-   ✅ Only one option can be selected at a time
-   ✅ Selected option shows colored ring (green/yellow/red)
-   ✅ Selected option has highlighted background
-   ✅ Default is "Available"
-   ✅ Must select one (required field)

---

## Test Scenario 7: Form Submission

### Steps:

1. Fill out at least:
    - Bio (optional but recommended)
    - 2-3 Specializations
    - 3-5 Service Areas
    - Availability Status (required)
2. Click **"Update Professional Profile"** button
3. Observe the loading state
4. Wait for response

**Expected Results**:

-   ✅ Button shows spinner and "Saving..." text
-   ✅ Form disabled during submission
-   ✅ Success message appears: "Professional profile updated successfully. Changes will appear in the Broker Directory."
-   ✅ Page doesn't reload (preserves scroll position)
-   ✅ Form remains filled with saved data

---

## Test Scenario 8: Verify in Broker Directory

### Steps:

1. After saving, navigate to **Broker Directory**
2. Find your profile
3. Click to view full profile
4. Check for:
    - Bio displayed
    - Specializations shown as badges
    - Service areas listed
    - Social links active
    - Availability status badge

**Expected Results**:

-   ✅ All saved data appears in directory
-   ✅ Bio readable and formatted
-   ✅ Specializations displayed as badges/chips
-   ✅ Service areas shown
-   ✅ Social media links clickable
-   ✅ Availability status visible

---

## Test Scenario 9: Error Handling

### Test A: Bio Too Long

1. Type/paste >1000 characters in bio field
2. Try to submit

**Expected**: Error message under bio field

### Test B: Invalid URLs

1. Enter "not-a-url" in website field
2. Try to submit

**Expected**: Error message "Please enter a valid website URL"

### Test C: No Availability Status

1. Deselect all availability radio buttons (if possible via dev tools)
2. Try to submit

**Expected**: Error message "Availability status is required"

---

## Test Scenario 10: Data Persistence

### Steps:

1. Fill out and save professional profile
2. Navigate away from settings
3. Return to Settings → Professional Profile
4. Check if data is still there
5. Refresh the page
6. Check again

**Expected Results**:

-   ✅ Data persists after navigation
-   ✅ Data persists after refresh
-   ✅ All selections remain checked
-   ✅ Bio text preserved
-   ✅ URLs saved correctly

---

## Test Scenario 11: Responsive Design

### Steps:

1. Open Settings on desktop browser
2. Resize browser to mobile width (375px)
3. Check Professional Profile tab
4. Test form interactions on mobile

**Expected Results**:

-   ✅ Sidebar collapses on mobile
-   ✅ Form fields stack vertically
-   ✅ Checkboxes still clickable
-   ✅ Buttons full-width on mobile
-   ✅ No horizontal scrolling
-   ✅ Touch targets adequate size

---

## Test Scenario 12: Client/Admin Access

### Steps:

1. Log in as **Client** or **Admin** (not broker)
2. Navigate to Account → Settings
3. Look for Professional Profile tab

**Expected Results**:

-   ❌ Professional Profile tab NOT visible
-   ❌ Direct URL access blocked (`/account/professional-profile`)
-   ❌ Error message if attempting API call

---

## 🎯 Quick Smoke Test (5 Minutes)

**As Broker:**

1. ✅ Navigate to Settings → Professional Profile
2. ✅ Enter bio: "Test broker profile"
3. ✅ Check 2 specializations
4. ✅ Check 3 service areas
5. ✅ Select "Available" status
6. ✅ Click "Update Professional Profile"
7. ✅ See success message
8. ✅ Navigate to Broker Directory
9. ✅ Find your profile
10. ✅ Verify data appears

**As Non-Broker:**

1. ✅ Confirm Professional Profile tab hidden

---

## 🐛 Common Issues & Solutions

### Issue: Tab not appearing

**Solution**: Ensure logged in as broker, clear browser cache

### Issue: Form not submitting

**Check**:

-   Availability status selected?
-   Bio under 1000 chars?
-   URLs valid?
-   Console for errors?

### Issue: Changes not appearing in directory

**Solution**:

-   Refresh directory page
-   Clear cache
-   Check browser dev tools for API errors

### Issue: Checkboxes not toggling

**Solution**:

-   Clear browser cache
-   Check JavaScript console for errors
-   Try different browser

---

## 📊 Test Results Template

```
Test Date: _____________
Tester: _____________
Browser: _____________

✅ Navigation & Access
✅ Bio Field
✅ Specializations
✅ Service Areas
✅ Online Presence
✅ Availability Status
✅ Form Submission
✅ Broker Directory Integration
✅ Error Handling
✅ Data Persistence
✅ Responsive Design
✅ Access Control

Issues Found: _____________
Notes: _____________
```

---

## 🚨 Critical Tests (Must Pass)

1. **Role-based access**: Only brokers see the tab ✅
2. **Data saves correctly**: All fields persist ✅
3. **Appears in directory**: Changes visible publicly ✅
4. **Validation works**: Invalid data rejected ✅
5. **No errors in console**: Clean execution ✅

---

## 📞 Reporting Issues

If you find bugs, report with:

-   Browser & version
-   User role (broker/client/admin)
-   Steps to reproduce
-   Expected vs. actual behavior
-   Screenshots if applicable
-   Console errors (F12 → Console tab)

---

**Ready to Test?** Start with the Quick Smoke Test, then run through all scenarios if everything looks good! 🚀
