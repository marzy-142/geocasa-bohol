# 🧪 Manual Testing Guide for Transaction Completion Workflow

## Quick Test Steps

### 1. **Prerequisites Check**

First, make sure you have the required data:

```bash
# Run the database migrations first
php artisan migrate

# Check if you have test data
php artisan tinker
```

In tinker, run:

```php
// Check for required test data
$broker = User::where('role', 'broker')->where('is_approved', true)->first();
$client = Client::where('status', 'active')->first();
$property = Property::where('status', 'available')->first();

echo "Broker: " . ($broker ? $broker->name : 'None') . "\n";
echo "Client: " . ($client ? $client->name : 'None') . "\n";
echo "Property: " . ($property ? $property->title : 'None') . "\n";
```

### 2. **Create Test Transaction (if needed)**

If you don't have a test transaction, create one:

```php
// In tinker
$transaction = Transaction::create([
    'transaction_number' => 'TEST-' . time(),
    'property_id' => $property->id,
    'client_id' => $client->id,
    'broker_id' => $broker->id,
    'transaction_type' => 'sale',
    'offered_price' => 1000000,
    'final_price' => 1000000,
    'commission_rate' => 0.06,
    'commission_amount' => 60000,
    'status' => 'negotiation', // Start with negotiation status
    'inquiry_date' => now()->subDays(30),
]);

echo "Test transaction created: " . $transaction->transaction_number . "\n";
```

### 3. **Test the Completion Workflow**

#### Method A: Via Web Interface

1. **Login as a broker**
2. **Go to Transactions** → Find your test transaction
3. **Click "View Details"**
4. **Click "Update Status"**
5. **Change status to "Finalized"**
6. **Click "Update Status"**

#### Method B: Via Tinker (Direct Testing)

```php
// In tinker
$transaction = Transaction::where('transaction_number', 'TEST-xxxxx')->first(); // Use your test transaction number

// Test the completion service
$completionService = new App\Services\TransactionCompletionService();

// Check if it can complete
$canComplete = $completionService->canComplete($transaction);
echo "Can complete: " . ($canComplete ? 'Yes' : 'No') . "\n";

// Get completion summary
$summary = $completionService->getCompletionSummary($transaction);
print_r($summary);

// Run the completion workflow
$result = $completionService->completeTransaction($transaction);
echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
echo "Message: " . $result['message'] . "\n";
print_r($result['results']);
```

### 4. **Verify the Results**

After running the completion workflow, check these:

#### A. Transaction Status

```php
$transaction->refresh();
echo "Transaction status: " . $transaction->status . "\n";
echo "Finalized date: " . $transaction->finalized_date . "\n";
```

#### B. Property Status

```php
$property = $transaction->property;
$property->refresh();
echo "Property status: " . $property->status . "\n";
echo "Sold at: " . $property->sold_at . "\n";
echo "Sold price: ₱" . number_format($property->sold_price, 2) . "\n";
echo "Sold to client ID: " . $property->sold_to_client_id . "\n";
```

#### C. Client Status

```php
$client = $transaction->client;
$client->refresh();
echo "Client status: " . $client->status . "\n";
echo "Converted at: " . $client->converted_at . "\n";
echo "Converted via transaction ID: " . $client->converted_via_transaction_id . "\n";
```

#### D. Broker Statistics

```php
$broker = $transaction->broker;
$broker->refresh();
echo "Broker finalized transactions: " . $broker->finalized_transactions_count . "\n";
echo "Broker total commission: ₱" . number_format($broker->total_commission_earned, 2) . "\n";
echo "Broker last sale date: " . $broker->last_sale_date . "\n";
```

#### E. Notifications

```php
// Check broker notifications
$brokerNotifications = $broker->notifications()
    ->where('type', 'App\\Notifications\\TransactionCompletedNotification')
    ->count();
echo "Broker notifications: " . $brokerNotifications . "\n";

// Check client notifications (if client has user account)
if ($client->user) {
    $clientNotifications = $client->user->notifications()
        ->where('type', 'App\\Notifications\\TransactionCompletedNotification')
        ->count();
    echo "Client notifications: " . $clientNotifications . "\n";
}

// Check admin notifications
$admin = User::where('role', 'admin')->first();
if ($admin) {
    $adminNotifications = $admin->notifications()
        ->where('type', 'App\\Notifications\\PropertySoldNotification')
        ->count();
    echo "Admin notifications: " . $adminNotifications . "\n";
}
```

### 5. **Expected Results**

✅ **Transaction Status**: Should be "finalized"  
✅ **Property Status**: Should be "sold"  
✅ **Client Status**: Should be "converted"  
✅ **Broker Stats**: Should be updated with new counts and commission  
✅ **Notifications**: Should be sent to broker, client, and admin  
✅ **Success Message**: Should show "Transaction completed successfully!"

### 6. **Cleanup After Testing**

```php
// Revert the test transaction and related data
$transaction->update(['status' => 'cancelled']);
$property->update([
    'status' => 'available',
    'sold_at' => null,
    'sold_price' => null,
    'sold_to_client_id' => null,
    'sold_via_transaction_id' => null
]);
$client->update([
    'status' => 'active',
    'converted_at' => null,
    'converted_via_transaction_id' => null
]);
$broker->update([
    'finalized_transactions_count' => $broker->finalized_transactions_count - 1,
    'total_commission_earned' => $broker->total_commission_earned - 60000
]);

// Delete the test transaction
$transaction->delete();
```

## 🚨 Troubleshooting

### Common Issues:

1. **"No approved broker found"**

    - Create a broker user and approve them
    - Make sure `is_approved = true` and `application_status = 'approved'`

2. **"No active client found"**

    - Create a client with `status = 'active'`

3. **"No available property found"**

    - Create a property with `status = 'available'`

4. **"Completion workflow failed"**

    - Check the Laravel logs: `tail -f storage/logs/laravel.log`
    - Make sure all database migrations are run

5. **"Notifications not sent"**
    - Check if mail is configured properly
    - Check if broadcasting is set up for real-time notifications

## 📊 Automated Test Script

For a comprehensive automated test, run:

```bash
php test_transaction_completion.php
```

This script will:

-   ✅ Check for required test data
-   ✅ Create a test transaction if needed
-   ✅ Test the completion service
-   ✅ Verify all results
-   ✅ Clean up test data

## 🎯 Success Criteria

The workflow is working correctly if:

1. ✅ Transaction status changes to "finalized"
2. ✅ Property status changes to "sold"
3. ✅ Client status changes to "converted"
4. ✅ Broker statistics are updated
5. ✅ Notifications are sent to all parties
6. ✅ Success message is displayed
7. ✅ No errors in the Laravel logs
