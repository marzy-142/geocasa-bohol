# Messaging System Audit - Testing Guide

This guide will help you verify all improvements implemented during the messaging audit.

## Prerequisites

1. **Run the new migration** (for system message support):

```powershell
php artisan migrate
```

2. **Ensure Laravel Reverb is running**:

```powershell
php artisan reverb:start
```

3. **Ensure Vite is running** (for hot reload):

```powershell
npm run dev
```

4. **Clear caches**:

```powershell
php artisan config:clear; php artisan cache:clear; php artisan view:clear
```

---

## Test Suite

### 1. Channel Security Test

**What we fixed**: Conversation channels now restricted to participants only.

**How to test**:

1. Login as User A (e.g., a client)
2. Start a conversation with a broker about a property inquiry
3. Note the conversation ID from the URL (e.g., `/messages/123`)
4. Open browser DevTools → Network tab
5. Look for WebSocket connection and verify you're subscribed to `private-conversation.123`
6. Login as User B (different account) in an incognito window
7. Try to manually navigate to `/messages/123`
8. **Expected**: User B cannot access the conversation (redirected or 403 error)
9. Open DevTools in User B's window
10. **Expected**: No subscription to `private-conversation.123` should appear

**Pass criteria**: ✅ Only participants can subscribe to conversation channels

---

### 2. Real-Time Message Delivery Test

**What we fixed**: Event name alignment (`MessageSent` instead of `message.sent`).

**How to test**:

1. Open two browser windows side-by-side
2. Login as User A (client) in Window 1
3. Login as User B (broker) in Window 2
4. User A creates an inquiry on a property
5. User B receives the inquiry and starts a conversation
6. Both users navigate to `/messages` and open the same conversation
7. User A types a message and sends it
8. **Expected in Window 2**: Message appears instantly without page refresh
9. User B replies
10. **Expected in Window 1**: Reply appears instantly
11. Open DevTools → Console in both windows
12. **Expected**: No errors related to Echo or event listeners

**Pass criteria**: ✅ Messages appear in real-time for both participants

---

### 3. Echo Listener Cleanup Test

**What we fixed**: Added `onUnmounted` hooks to prevent memory leaks.

**How to test**:

1. Login as a user with multiple conversations
2. Go to `/messages`
3. Open DevTools → Console
4. Type: `window.Echo.connector.channels` and press Enter
5. **Expected**: You should see active channels listed
6. Click on Conversation 1, then Conversation 2, then Conversation 3
7. After each navigation, run `window.Echo.connector.channels` again
8. **Expected**: Previous conversation channels should be removed (only current channel active)
9. Navigate away from `/messages` to dashboard
10. Run `window.Echo.connector.channels` again
11. **Expected**: Conversation channels cleaned up (only user notification channel remains)

**Pass criteria**: ✅ Old channels are properly unsubscribed when switching conversations

---

### 4. User Notification Channel Standardization Test

**What we fixed**: All user notifications now use `App.Models.User.{id}` channel.

**How to test**:

1. Login as a client
2. Open DevTools → Console
3. Navigate to `/notifications`
4. Type: `window.Echo.connector.channels` and press Enter
5. **Expected**: See channel named `private-App.Models.User.{your-user-id}`
6. Navigate to dashboard
7. Run `window.Echo.connector.channels` again
8. **Expected**: Same channel pattern `private-App.Models.User.{your-user-id}`
9. Have another user send you a message
10. **Expected**: Notification appears in real-time on both dashboard and notification page
11. Check DevTools Console
12. **Expected**: No duplicate subscriptions or channel errors

**Pass criteria**: ✅ Consistent channel naming across all pages; real-time notifications work

---

### 5. Message Validation Test

**What we fixed**: Tightened validation (content OR attachments required, max 2000 chars, file restrictions).

**Test 5a - Empty Message**:

1. Go to any conversation
2. Try to send a message with empty content and no attachments
3. **Expected**: Validation error "The content field is required when attachments is not present."

**Test 5b - Content Length**:

1. Type a very long message (>2000 characters)
2. Try to send
3. **Expected**: Validation error "The content field must not be greater than 2000 characters."

**Test 5c - File Type Restriction**:

1. Try to attach a `.exe`, `.zip`, or `.docx` file
2. Try to send
3. **Expected**: Validation error about invalid MIME type (only JPG, PNG, WEBP, PDF allowed)

**Test 5d - File Size Restriction**:

1. Try to attach a file larger than 10MB
2. Try to send
3. **Expected**: Validation error about file size limit

**Test 5e - File Count Restriction**:

1. Try to attach 6 or more files
2. Try to send
3. **Expected**: Validation error "The attachments field must not have more than 5 items."

**Test 5f - Valid Attachment Only**:

1. Send a message with NO text content but WITH a valid image (JPG/PNG)
2. **Expected**: Message sends successfully

**Pass criteria**: ✅ All validation rules enforced correctly

---

### 6. Content Sanitization Test

**What we fixed**: Server-side `strip_tags()` applied to message content.

**How to test**:

1. Go to any conversation
2. Type a message with HTML/script tags:
    ```
    Hello <script>alert('XSS')</script> world <b>bold</b> text
    ```
3. Send the message
4. **Expected**: Message displays as plain text: "Hello world text" (tags stripped)
5. Check database directly:
    ```powershell
    php artisan tinker
    ```
    ```php
    $message = App\Models\Message::latest()->first();
    echo $message->content;
    ```
6. **Expected**: No HTML tags in stored content

**Pass criteria**: ✅ HTML/script tags are stripped from messages

---

### 7. Rate Limiting Test

**What we fixed**: Verified 10 messages/minute rate limit.

**How to test**:

1. Go to any conversation
2. Quickly send 10 short messages (just type "test 1", "test 2", etc.)
3. After the 10th message, try to send an 11th message immediately
4. **Expected**: Error response "Too Many Attempts" or similar
5. Wait 60 seconds
6. Try sending another message
7. **Expected**: Message sends successfully (rate limit reset)

**Pass criteria**: ✅ Rate limit enforced at 10 messages per minute

---

### 8. Unread Count Performance Test

**What we fixed**: Switched from post-fetch transform to SQL `withCount()`.

**How to test**:

1. Have conversations with unread messages (ask another user to send you messages)
2. Navigate to `/messages`
3. Open DevTools → Network tab
4. Look for the request to `/messages` endpoint
5. Check the response JSON
6. **Expected**: Each conversation object has `unread_count` property with correct integer value
7. In DevTools → Console, check for any errors or warnings
8. **Expected**: No analyzer warnings about LengthAwarePaginator

**Database verification**:

```powershell
php artisan tinker
```

```php
$user = App\Models\User::find(YOUR_USER_ID);
$conversations = App\Models\Conversation::where('participants', 'like', "%\"$user->id\"%")
    ->withCount(['messages as unread_count' => function($query) use ($user) {
        $query->where('sender_id', '!=', $user->id)
              ->whereNull('read_at');
    }])
    ->first();
echo $conversations->unread_count;
```

**Pass criteria**: ✅ Unread counts accurate and computed via SQL

---

### 9. System Message Support Test

**What we fixed**: Made `sender_id` nullable for system messages.

**How to test**:

1. Use tinker to create a system message:

    ```powershell
    php artisan tinker
    ```

    ```php
    $conversation = App\Models\Conversation::first();

    App\Models\Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => null,  // System message
        'content' => 'This property has been marked as sold by the admin.',
        'is_system_message' => true
    ]);
    ```

2. Navigate to that conversation in the UI
3. **Expected**: System message displays correctly (possibly with different styling if implemented)
4. Check database:
    ```sql
    SELECT * FROM messages WHERE sender_id IS NULL;
    ```
5. **Expected**: Record exists with NULL sender_id and no FK constraint errors

**Pass criteria**: ✅ System messages can be created and displayed without errors

---

### 10. Self-Notification Bug Test

**What we fixed**: Uses correct `page.props.auth.user.id` instead of non-existent property.

**How to test**:

1. Login and go to any conversation
2. Send yourself a message (you're both sender and receiver in the conversation)
3. Open DevTools → Console
4. **Expected**: No JavaScript errors about "undefined currentUser"
5. **Expected**: No desktop notification appears for your own message (only for others)
6. Have another user send you a message in the same conversation
7. **Expected**: Desktop notification DOES appear for the other user's message

**Pass criteria**: ✅ No self-notifications; no console errors

---

## Quick Smoke Test Script

Run this script to verify basic functionality:

```powershell
# 1. Run migration
php artisan migrate

# 2. Check rate limiter configuration
php artisan route:list --path=messages | Select-String "throttle"

# 3. Test message creation (replace IDs with real values)
php artisan tinker
```

In tinker:

```php
// Test 1: Valid message
$user = App\Models\User::find(1);
$conversation = App\Models\Conversation::first();
$message = App\Models\Message::create([
    'conversation_id' => $conversation->id,
    'sender_id' => $user->id,
    'content' => 'Test message with <script>alert("XSS")</script> tags'
]);
echo $message->content; // Should NOT contain <script> tags

// Test 2: System message
$systemMsg = App\Models\Message::create([
    'conversation_id' => $conversation->id,
    'sender_id' => null,
    'content' => 'System notification test',
    'is_system_message' => true
]);
echo $systemMsg->sender_id; // Should be NULL

// Test 3: Unread count
$count = $conversation->messages()->where('sender_id', '!=', $user->id)->whereNull('read_at')->count();
echo "Unread: $count";

exit
```

---

## Regression Checklist

Ensure existing functionality still works:

-   [ ] Can create new inquiry conversations
-   [ ] Can create new transaction conversations
-   [ ] Can archive conversations
-   [ ] Can mark conversations as read
-   [ ] File attachments upload and display correctly
-   [ ] Pagination works on conversation list
-   [ ] Search/filter works on conversation list
-   [ ] Email notifications still sent for new messages
-   [ ] Database notifications created properly
-   [ ] Soft-deleted messages don't appear in UI

---

## Troubleshooting

### WebSocket Connection Issues

```powershell
# Check if Reverb is running
php artisan reverb:start

# Check .env configuration
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
```

### Echo Listener Not Working

1. Check browser console for JavaScript errors
2. Verify `resources/js/bootstrap.js` has Echo configured
3. Clear browser cache and hard reload (Ctrl+Shift+R)
4. Check Network tab for WebSocket connection (should show 101 Switching Protocols)

### Rate Limit Not Working

```powershell
# Verify middleware is applied
php artisan route:list --path=messages

# Check RateLimitServiceProvider is registered in bootstrap/providers.php
```

### Migration Fails

If you get "Unknown column type" error:

```powershell
# The migration uses raw SQL, ensure MySQL/MariaDB version is 5.7+
php artisan --version  # Check Laravel version
```

---

## Success Criteria Summary

✅ **All tests pass** = Messaging audit improvements successfully implemented

Key indicators:

-   No unauthorized access to conversation channels
-   Real-time messages appear instantly
-   No memory leaks (channels cleaned up properly)
-   Validation blocks invalid messages
-   HTML/script tags stripped from content
-   Rate limit enforced at 10/minute
-   Unread counts accurate and performant
-   System messages work without errors
-   No self-notification bugs

---

## Next Steps After Testing

If all tests pass:

1. Deploy to staging environment
2. Run tests again in staging
3. Monitor error logs for any edge cases
4. Consider optional enhancements:
    - Change FK to `onDelete('set null')` for message history preservation
    - Add antivirus scanning for attachments
    - Implement typing indicators
    - Add presence channels (online/offline status)

If tests fail:

1. Note which specific test failed
2. Check browser console and Laravel logs
3. Verify all migration ran successfully
4. Clear all caches again
5. Report the specific failure for debugging
