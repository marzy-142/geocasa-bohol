# Testing Guide: Inquiry Response Email & Messaging System

## Prerequisites

Before testing, ensure:

1. ✅ Mail configuration is set in `.env`
2. ✅ Development server is running (`php artisan serve`)
3. ✅ Frontend is compiled (`npm run dev`)
4. ✅ Database is migrated and seeded

## Quick Test Scenario

### Step 1: Create Test Data (if needed)

Run this in your terminal to create test data:

```bash
php artisan tinker
```

Then paste this code:

```php
// Create a broker user
$broker = App\Models\User::factory()->create([
    'email' => 'broker@test.com',
    'name' => 'Test Broker',
    'role' => 'broker',
    'password' => bcrypt('password')
]);

// Create a property
$property = App\Models\Property::factory()->create([
    'broker_id' => $broker->id,
    'title' => 'Beautiful Beachfront Villa',
    'municipality' => 'Panglao',
    'province' => 'Bohol',
    'total_price' => 5000000,
    'status' => 'active'
]);

// Create a client user (optional - for testing logged-in client)
$clientUser = App\Models\User::factory()->create([
    'email' => 'client@test.com',
    'name' => 'Test Client',
    'role' => 'client',
    'password' => bcrypt('password')
]);

$client = App\Models\Client::factory()->create([
    'user_id' => $clientUser->id,
    'email' => 'client@test.com',
    'name' => 'Test Client'
]);

// Create an inquiry
$inquiry = App\Models\Inquiry::create([
    'property_id' => $property->id,
    'client_id' => $client->id,
    'user_id' => $clientUser->id,
    'name' => 'Test Client',
    'email' => 'client@test.com', // Use YOUR real email to receive test emails
    'phone' => '09171234567',
    'message' => 'I am interested in this property. Can we schedule a viewing?',
    'inquiry_type' => 'viewing',
    'status' => 'new'
]);

echo "✅ Test data created!\n";
echo "Inquiry ID: " . $inquiry->id . "\n";
echo "Property ID: " . $property->id . "\n";
```

**💡 Tip:** Replace `'client@test.com'` with YOUR actual email address to receive the test email!

### Step 2: Log in as Broker

1. Go to: `http://localhost:8000/login`
2. Login with:
    - Email: `broker@test.com`
    - Password: `password`

### Step 3: Navigate to the Inquiry

1. Go to **Inquiries** section from the dashboard
2. Click on the inquiry you just created
3. You should see the inquiry details page

### Step 4: Send a Response

1. Click **"Reply to Inquiry"** button (blue button)
2. Response form will appear
3. Type a message, for example:
    ```
    Thank you for your interest in our beachfront villa!
    I would be happy to arrange a viewing.
    Are you available this weekend?
    ```
4. Select a status (e.g., "Contacted" or "Scheduled")
5. Click **"Send Response"**

### Step 5: Verify What Happened

Check the following:

#### ✅ 1. Database Update

```bash
php artisan tinker
```

```php
$inquiry = App\Models\Inquiry::find(1); // Use your inquiry ID
echo "Status: " . $inquiry->status . "\n";
echo "Response: " . $inquiry->broker_response . "\n";
echo "Has conversation: " . ($inquiry->conversation ? 'YES' : 'NO') . "\n";
```

#### ✅ 2. Email Sent

Check your email inbox (the email you used in the inquiry)

-   Subject: "Response to Your Property Inquiry - Beautiful Beachfront Villa"
-   Contains broker's message
-   Has "Continue Conversation" button (if you're logged in)
-   Has property details

#### ✅ 3. Conversation Created

On the inquiry details page, you should now see:

-   ✅ **Green "Open Conversation" button** at the top

#### ✅ 4. Success Message

You should see a flash message:

-   "Response sent successfully and client has been notified via email."

### Step 6: Test the Conversation

1. Click **"Open Conversation"** button
2. You should be redirected to the conversation page
3. Send a test message
4. Message should appear in real-time

### Step 7: Test Client View (Optional)

1. Log out from broker account
2. Log in as client:
    - Email: `client@test.com`
    - Password: `password`
3. Go to **My Inquiries**
4. Click on the inquiry
5. You should see:
    - ✅ **Green "Message Broker" button**
    - Broker's response displayed
6. Click "Message Broker"
7. Send a message back

---

## Testing Email Specifically

### Option 1: Use Mailtrap (Recommended for Development)

1. Go to [mailtrap.io](https://mailtrap.io) and create free account
2. Get SMTP credentials from your inbox
3. Update `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@geocasa-bohol.test
MAIL_FROM_NAME="GeoCasa Bohol"
```

4. Run: `php artisan config:clear`
5. Follow steps above - emails will appear in Mailtrap inbox

### Option 2: Use Gmail (For Real Testing)

1. Enable 2FA on your Gmail account
2. Generate App Password: [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
3. Update `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="GeoCasa Bohol"
```

4. Run: `php artisan config:clear`
5. Use your own email in inquiry to receive real emails

### Option 3: Use Log Driver (Quick Test)

1. Update `.env`:

```env
MAIL_MAILER=log
```

2. Run: `php artisan config:clear`
3. Follow test steps above
4. Check email in: `storage/logs/laravel.log`
5. Search for: "Message-ID" to find email content

---

## Troubleshooting

### Email Not Sending?

**Check Laravel Log:**

```bash
tail -f storage/logs/laravel.log
```

Look for errors like:

-   "Failed to send inquiry response email"
-   "Connection refused"
-   "Authentication failed"

**Common Issues:**

1. **SMTP connection failed**

    - Verify `.env` credentials are correct
    - Check if firewall blocking port 587/465
    - Run: `php artisan config:clear`

2. **Email sent but not received**

    - Check spam folder
    - Verify email address is correct
    - Use Mailtrap to confirm email is being sent

3. **Route error in email**
    - Check if `public.properties.show` route exists
    - Email will still send, just without property link

### Conversation Not Created?

**Check in tinker:**

```bash
php artisan tinker
```

```php
$inquiry = App\Models\Inquiry::find(1);
$conversation = $inquiry->conversation;

if ($conversation) {
    echo "Conversation ID: " . $conversation->id . "\n";
    echo "Participants: " . $conversation->participants . "\n";
    echo "Messages: " . $conversation->messages->count() . "\n";
} else {
    echo "No conversation found\n";
}
```

**Manual Fix:**

```php
$conversation = App\Models\Conversation::createForInquiry($inquiry->fresh(['property', 'client']));
echo "Created conversation ID: " . $conversation->id;
```

### "Open Conversation" Button Not Showing?

1. **Hard refresh browser:** `Ctrl + Shift + R` (Windows) or `Cmd + Shift + R` (Mac)
2. **Check if conversation exists:**

```php
$inquiry = App\Models\Inquiry::with('conversation')->find(1);
dd($inquiry->conversation);
```

3. **Clear cache:**

```bash
php artisan cache:clear
php artisan view:clear
```

---

## Quick Verification Checklist

After sending a response, verify:

-   [ ] Inquiry status updated in database
-   [ ] `broker_response` field populated
-   [ ] `responded_at` timestamp set
-   [ ] Email appears in inbox/Mailtrap/logs
-   [ ] Conversation record created in database
-   [ ] System message added to conversation
-   [ ] "Open Conversation" button visible on broker view
-   [ ] "Message Broker" button visible on client view
-   [ ] Flash message shows success
-   [ ] No errors in `storage/logs/laravel.log`

---

## Advanced Testing

### Test Conversation Flow

1. Broker sends response → Conversation created
2. Broker clicks "Open Conversation" → Can send messages
3. Client logs in → Sees "Message Broker" button
4. Client sends message → Broker receives notification
5. Messages appear in real-time
6. Create transaction → Conversation persists

### Test Email Content

Verify email includes:

-   Client's name in greeting
-   Broker's response message
-   Broker's name and signature
-   Property details (title, type, location, price)
-   Client's original message (in quote)
-   "Continue Conversation" button (if conversation exists)
-   "View Property Details" button
-   Proper formatting and styling

---

## Need Help?

If something's not working:

1. Check `storage/logs/laravel.log` for errors
2. Verify `.env` mail configuration
3. Run `php artisan config:clear`
4. Clear browser cache with hard refresh
5. Check database for conversation record
6. Verify routes with: `php artisan route:list | grep conversation`

**Still stuck?** Share the error from logs and I can help debug!
