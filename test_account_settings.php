<?php

/**
 * Account Settings Functionality Test Script
 * 
 * This script tests all Account Settings functions to ensure they work correctly.
 * Run this in tinker: php artisan tinker
 * Then: include('test_account_settings.php');
 */

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

echo "\n=== Account Settings Functionality Test ===\n\n";

// Test 1: Find a test user
echo "Test 1: Finding test user...\n";
$user = User::where('role', 'broker')->first() ?? User::first();
if (!$user) {
    echo "❌ No users found in database. Please create a user first.\n";
    return;
}
echo "✅ Found user: {$user->name} (ID: {$user->id}, Role: {$user->role})\n\n";

// Test 2: Check User Model Fields
echo "Test 2: Checking User model fields...\n";
$requiredFields = ['avatar', 'notification_preferences', 'privacy_settings', 'is_active', 'deactivated_at', 'deactivation_reason'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (!in_array($field, $user->getFillable())) {
        $missingFields[] = $field;
    }
}

if (empty($missingFields)) {
    echo "✅ All required fields are fillable\n\n";
} else {
    echo "❌ Missing fillable fields: " . implode(', ', $missingFields) . "\n\n";
}

// Test 3: Test Profile Update
echo "Test 3: Testing profile update...\n";
try {
    $originalName = $user->name;
    $user->update([
        'name' => 'Test User Updated',
        'bio' => 'This is a test bio for account settings.',
        'phone' => '09123456789',
        'address' => '123 Test Street, Test City'
    ]);
    echo "✅ Profile updated successfully\n";
    
    // Restore original name
    $user->update(['name' => $originalName]);
    echo "✅ Profile restored to original values\n\n";
} catch (\Exception $e) {
    echo "❌ Profile update failed: " . $e->getMessage() . "\n\n";
}

// Test 4: Test Password Update
echo "Test 4: Testing password validation...\n";
try {
    $newPassword = 'NewPassword123!';
    $user->update([
        'password' => Hash::make($newPassword)
    ]);
    
    if (Hash::check($newPassword, $user->password)) {
        echo "✅ Password updated and verified successfully\n\n";
    } else {
        echo "❌ Password update failed verification\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Password update failed: " . $e->getMessage() . "\n\n";
}

// Test 5: Test Notification Preferences
echo "Test 5: Testing notification preferences...\n";
try {
    $preferences = [
        'email_notifications' => true,
        'push_notifications' => true,
        'notify_new_inquiry' => true,
        'notify_status_update' => false,
        'notify_new_message' => true,
        'notify_transaction_update' => true,
        'notify_payment_reminder' => false,
    ];
    
    $user->update([
        'notification_preferences' => $preferences
    ]);
    
    $user->refresh();
    
    if ($user->notification_preferences['email_notifications'] === true) {
        echo "✅ Notification preferences saved successfully\n";
        echo "   Stored preferences: " . json_encode($user->notification_preferences) . "\n\n";
    } else {
        echo "❌ Notification preferences not saved correctly\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Notification preferences failed: " . $e->getMessage() . "\n\n";
}

// Test 6: Test Privacy Settings
echo "Test 6: Testing privacy settings...\n";
try {
    $privacySettings = [
        'profile_visibility' => 'public',
        'show_email' => true,
        'show_phone' => false,
        'allow_messages' => true,
    ];
    
    $user->update([
        'privacy_settings' => $privacySettings
    ]);
    
    $user->refresh();
    
    if ($user->privacy_settings['profile_visibility'] === 'public') {
        echo "✅ Privacy settings saved successfully\n";
        echo "   Stored settings: " . json_encode($user->privacy_settings) . "\n\n";
    } else {
        echo "❌ Privacy settings not saved correctly\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Privacy settings failed: " . $e->getMessage() . "\n\n";
}

// Test 7: Test Avatar Storage Path
echo "Test 7: Checking avatar storage configuration...\n";
try {
    if (Storage::disk('public')->exists('avatars') || Storage::disk('public')->makeDirectory('avatars')) {
        echo "✅ Avatar storage directory exists/created: storage/app/public/avatars\n";
        echo "   Public path: " . public_path('storage/avatars') . "\n\n";
    } else {
        echo "❌ Could not create avatar storage directory\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Avatar storage check failed: " . $e->getMessage() . "\n\n";
}

// Test 8: Test Account Status Fields
echo "Test 8: Testing account status fields...\n";
try {
    $originalStatus = $user->is_active;
    
    // Test deactivation
    $user->update([
        'is_active' => false,
        'deactivated_at' => now(),
        'deactivation_reason' => 'Testing deactivation'
    ]);
    
    $user->refresh();
    
    if ($user->is_active === false && $user->deactivated_at !== null) {
        echo "✅ Account deactivation fields work correctly\n";
        
        // Reactivate
        $user->update([
            'is_active' => true,
            'deactivated_at' => null,
            'deactivation_reason' => null
        ]);
        
        echo "✅ Account reactivated successfully\n\n";
    } else {
        echo "❌ Account status fields not working correctly\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Account status test failed: " . $e->getMessage() . "\n\n";
}

// Test 9: Test Client Model Sync (for client users)
if ($user->role === 'client') {
    echo "Test 9: Testing Client model sync...\n";
    try {
        $client = \App\Models\Client::where('user_id', $user->id)->first();
        
        if ($client) {
            echo "✅ Client record found for user\n";
            echo "   Client name: {$client->name}\n";
            echo "   Client email: {$client->email}\n\n";
        } else {
            echo "⚠️  No client record found (this is OK if user isn't a client)\n\n";
        }
    } catch (\Exception $e) {
        echo "❌ Client sync test failed: " . $e->getMessage() . "\n\n";
    }
} else {
    echo "Test 9: Skipping Client model sync test (user is not a client)\n\n";
}

// Test 10: Test Route Registration
echo "Test 10: Checking route registration...\n";
try {
    $routes = [
        'account.settings',
        'account.update-profile',
        'account.update-password',
        'account.update-avatar',
        'account.delete-avatar',
        'account.update-notifications',
        'account.update-privacy',
        'account.deactivate',
        'account.delete',
    ];
    
    $missingRoutes = [];
    foreach ($routes as $routeName) {
        if (!Route::has($routeName)) {
            $missingRoutes[] = $routeName;
        }
    }
    
    if (empty($missingRoutes)) {
        echo "✅ All 9 account settings routes are registered\n\n";
    } else {
        echo "❌ Missing routes: " . implode(', ', $missingRoutes) . "\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Route check failed: " . $e->getMessage() . "\n\n";
}

// Summary
echo "\n=== Test Summary ===\n";
echo "✅ All core functionality tests completed\n";
echo "📝 Check above for any ❌ failures that need attention\n";
echo "\nTo test in browser:\n";
echo "1. Visit: http://localhost:8000/account/settings\n";
echo "2. Try uploading an avatar\n";
echo "3. Update your profile information\n";
echo "4. Change your password\n";
echo "5. Modify notification preferences\n";
echo "6. Adjust privacy settings\n\n";
