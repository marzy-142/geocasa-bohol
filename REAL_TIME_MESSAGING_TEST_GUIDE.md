# ✅ Real-Time Messaging - Testing Guide

## What Was Fixed

**Problem:** Messages required page refresh to appear.

**Root Cause:** The `MessageSent` event used `ShouldBroadcast` which queues broadcasts instead of sending them immediately.

**Solution:** Changed to `ShouldBroadcastNow` for instant broadcasting + added debug logging.

---

## Testing Steps

### Step 1: Open Two Browser Windows

**Option A:** Use two different browsers

-   Window 1: Chrome
-   Window 2: Firefox/Edge

**Option B:** Use incognito + normal window

-   Window 1: Normal Chrome window
-   Window 2: Chrome Incognito (Ctrl+Shift+N)

### Step 2: Login as Different Users

**Window 1:** Login as Maria Santos (Broker)

-   Email: `maria@geocasabohol.com`
-   Go to: Messages/Conversations

**Window 2:** Login as Mariella (Client)

-   Email: `mariella@geocasabohol.com`
-   Go to: Messages/Conversations

### Step 3: Open Same Conversation

Both windows should open the **same conversation** between Maria and Mariella.

Example URL: `http://localhost/conversations/5`

### Step 4: Check Browser Console

**In BOTH windows:**

1. Press **F12** to open Developer Tools
2. Go to **Console** tab
3. Look for these messages:
    - `✅ Echo WebSocket connected successfully` (from bootstrap.js)
    - `🔌 Listening for messages on conversation.X` (from Show.vue)

If you see red errors, note them down.

### Step 5: Test Real-Time Messaging

1. **In Window 1 (Maria):** Type and send a message

    - Example: "Hello, testing real-time!"

2. **Watch Window 2 (Mariella):**

    - The message should appear **INSTANTLY** without refreshing
    - Console should show: `📨 New message received via Echo:`

3. **In Window 2 (Mariella):** Reply with a message

    - Example: "Received! This is amazing!"

4. **Watch Window 1 (Maria):**
    - The reply should appear **INSTANTLY**

---

## Expected Console Output

### When page loads:

```
✅ Echo WebSocket connected successfully
🔌 Listening for messages on conversation.5
```

### When message is received:

```
📨 New message received via Echo: {message: {...}, conversation_id: 5}
```

### When leaving page:

```
🔌 Leaving conversation.5 channel
```

---

## Troubleshooting

### ❌ Messages still require refresh

**Check 1: Is Reverb running?**

```powershell
netstat -ano | findstr ":8080"
```

Should show "LISTENING" on port 8080.

If not, start Reverb:

```powershell
php artisan reverb:start
```

**Check 2: Console errors?**

Look for red errors in browser console:

-   `Echo is not initialized` → Reverb not running
-   `401 Unauthorized` → Authentication issue
-   `Connection refused` → Reverb server down

**Check 3: Clear browser cache**

-   Press Ctrl+Shift+Delete
-   Clear cache and reload page (Ctrl+F5)

---

## Manual Test Script

If you want to test broadcasting manually:

```powershell
php test_broadcast.php
```

This will:

1. Find the most recent message
2. Broadcast it again
3. Show which channel it's broadcasting to

While running this, keep a conversation page open with F12 console visible. You should see the message event arrive.

---

## Success Criteria

✅ Messages appear instantly in both windows without refreshing
✅ Console shows connection and message events
✅ No errors in browser console
✅ No need to refresh to see new messages

---

## Additional Notes

-   **Reverb must be running** for real-time features to work
-   Messages are saved to database first, then broadcast
-   If broadcast fails, messages still appear after refresh
-   Desktop notifications should also appear for messages from others

---

## Quick Reference Commands

Start Reverb:

```powershell
php artisan reverb:start
```

Check if Reverb is running:

```powershell
netstat -ano | findstr ":8080"
```

Test broadcast manually:

```powershell
php test_broadcast.php
```

Monitor services:

```powershell
.\monitor_realtime.bat
```
