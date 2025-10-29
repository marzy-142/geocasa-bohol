<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AccountSettingsController extends Controller
{
    /**
     * Display the account settings page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Load role-specific data
        $additionalData = [];
        
        if ($user->role === 'client') {
            $client = Client::where('user_id', $user->id)->first();
            $additionalData['client'] = $client;
        }
        
        return Inertia::render('Account/Settings', [
            'user' => $user,
            'additionalData' => $additionalData,
        ]);
    }

    /**
     * Update profile information
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\.]+$/'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\-\+\(\)]+$/'],
                'address' => ['nullable', 'string', 'max:500'],
                'bio' => ['nullable', 'string', 'max:1000'],
            ], [
                'name.regex' => 'Name can only contain letters, spaces, hyphens, and periods.',
                'phone.regex' => 'Phone number format is invalid.',
                'email.unique' => 'This email address is already in use.',
            ]);

            DB::beginTransaction();
            
            try {
                // Update user
                $user->update($validated);

                // Update client record if exists
                if ($user->role === 'client') {
                    $client = Client::where('user_id', $user->id)->first();
                    if ($client) {
                        $client->update([
                            'name' => $validated['name'],
                            'email' => $validated['email'],
                            'phone' => $validated['phone'] ?? $client->phone,
                        ]);
                    }
                }
                
                DB::commit();
                
                Log::info('Profile updated', [
                    'user_id' => $user->id,
                    'updated_fields' => array_keys($validated)
                ]);

                return back()->with('success', 'Profile updated successfully.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Profile update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'error' => 'Failed to update profile. Please try again.'
            ])->withInput();
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols(),
                    'different:current_password'
                ],
            ], [
                'password.different' => 'New password must be different from current password.',
                'current_password.current_password' => 'Current password is incorrect.',
            ]);

            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
            
            Log::info('Password updated', ['user_id' => $user->id]);

            return back()->with('success', 'Password updated successfully. Please use your new password for future logins.');
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Password update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'error' => 'Failed to update password. Please try again.'
            ]);
        }
    }

    /**
     * Update avatar/profile picture
     */
    public function updateAvatar(Request $request)
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'avatar' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg,gif,webp',
                    'max:2048', // 2MB
                    'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'
                ],
            ], [
                'avatar.dimensions' => 'Image must be between 100x100 and 2000x2000 pixels.',
                'avatar.max' => 'Image size must not exceed 2MB.',
            ]);

            // Ensure avatars directory exists
            if (!Storage::disk('public')->exists('avatars')) {
                Storage::disk('public')->makeDirectory('avatars');
            }

            DB::beginTransaction();
            
            try {
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Store new avatar with unique name
                $file = $request->file('avatar');
                $filename = $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('avatars', $filename, 'public');
                
                // Update user
                $user->update([
                    'avatar' => $path,
                ]);
                
                DB::commit();
                
                Log::info('Avatar updated', [
                    'user_id' => $user->id,
                    'path' => $path
                ]);

                return back()->with('success', 'Profile picture updated successfully.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Avatar upload failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'avatar' => 'Failed to upload profile picture. Please try again.'
            ]);
        }
    }

    /**
     * Delete avatar/profile picture
     */
    public function deleteAvatar()
    {
        try {
            $user = Auth::user();
            
            if (!$user->avatar) {
                return back()->withErrors([
                    'avatar' => 'No profile picture to remove.'
                ]);
            }
            
            DB::beginTransaction();
            
            try {
                if (Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $user->update([
                    'avatar' => null,
                ]);
                
                DB::commit();
                
                Log::info('Avatar deleted', ['user_id' => $user->id]);

                return back()->with('success', 'Profile picture removed successfully.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Avatar deletion failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'avatar' => 'Failed to remove profile picture. Please try again.'
            ]);
        }
    }

    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'email_notifications' => ['sometimes', 'boolean'],
                'sms_notifications' => ['sometimes', 'boolean'],
                'push_notifications' => ['sometimes', 'boolean'],
                'notify_new_inquiry' => ['sometimes', 'boolean'],
                'notify_status_update' => ['sometimes', 'boolean'],
                'notify_new_message' => ['sometimes', 'boolean'],
                'notify_transaction_update' => ['sometimes', 'boolean'],
                'notify_payment_reminder' => ['sometimes', 'boolean'],
            ]);

            // Convert string booleans to actual booleans
            $preferences = array_map(function($value) {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }, $validated);

            $preferences = array_merge(
                $user->notification_preferences ?? [],
                $preferences
            );

            $user->update([
                'notification_preferences' => $preferences,
            ]);
            
            Log::info('Notification preferences updated', [
                'user_id' => $user->id,
                'preferences' => $preferences
            ]);

            return back()->with('success', 'Notification preferences updated successfully.');
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Notification preferences update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'error' => 'Failed to update notification preferences. Please try again.'
            ]);
        }
    }

    /**
     * Update privacy settings
     */
    public function updatePrivacySettings(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'profile_visibility' => ['sometimes', 'in:public,private,contacts'],
                'show_email' => ['sometimes', 'boolean'],
                'show_phone' => ['sometimes', 'boolean'],
                'allow_messages' => ['sometimes', 'boolean'],
            ]);

            // Convert string booleans to actual booleans for boolean fields
            if (isset($validated['show_email'])) {
                $validated['show_email'] = filter_var($validated['show_email'], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($validated['show_phone'])) {
                $validated['show_phone'] = filter_var($validated['show_phone'], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($validated['allow_messages'])) {
                $validated['allow_messages'] = filter_var($validated['allow_messages'], FILTER_VALIDATE_BOOLEAN);
            }

            $settings = array_merge(
                $user->privacy_settings ?? [],
                $validated
            );

            $user->update([
                'privacy_settings' => $settings,
            ]);
            
            Log::info('Privacy settings updated', [
                'user_id' => $user->id,
                'settings' => $settings
            ]);

            return back()->with('success', 'Privacy settings updated successfully.');
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Privacy settings update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'error' => 'Failed to update privacy settings. Please try again.'
            ]);
        }
    }

    /**
     * Deactivate account
     */
    public function deactivateAccount(Request $request)
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'password' => ['required', 'current_password'],
                'reason' => ['nullable', 'string', 'max:500'],
            ], [
                'password.current_password' => 'Password is incorrect.',
            ]);

            DB::beginTransaction();
            
            try {
                // Store deactivation info
                $user->update([
                    'is_active' => false,
                    'deactivated_at' => now(),
                    'deactivation_reason' => $request->reason,
                ]);
                
                DB::commit();
                
                Log::warning('Account deactivated', [
                    'user_id' => $user->id,
                    'reason' => $request->reason
                ]);

                // Log out user
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/')->with('success', 'Your account has been deactivated. Contact support to reactivate.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Account deactivation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'error' => 'Failed to deactivate account. Please try again.'
            ]);
        }
    }

    /**
     * Delete account permanently
     */
    public function deleteAccount(Request $request)
    {
        try {
            $user = Auth::user();
            
            $request->validate([
                'password' => ['required', 'current_password'],
                'confirmation' => ['required', 'in:DELETE'],
            ], [
                'password.current_password' => 'Password is incorrect.',
                'confirmation.in' => 'You must type DELETE to confirm account deletion.',
            ]);

            // Prevent admin deletion if they're the only admin
            if ($user->role === 'admin') {
                $adminCount = User::where('role', 'admin')
                    ->where('id', '!=', $user->id)
                    ->count();
                    
                if ($adminCount < 1) {
                    return back()->withErrors([
                        'deletion' => 'Cannot delete the only admin account. Please create another admin first.',
                    ]);
                }
            }

            DB::beginTransaction();
            
            try {
                $userId = $user->id;
                $userName = $user->name;
                
                // Delete avatar if exists
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                // Soft delete the user
                $user->delete();
                
                DB::commit();
                
                Log::warning('Account deleted', [
                    'user_id' => $userId,
                    'user_name' => $userName,
                    'role' => $user->role
                ]);
                
                // Log out user
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/')->with('success', 'Your account has been permanently deleted.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Account deletion failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors([
                'deletion' => 'Failed to delete account. Please try again or contact support.'
            ]);
        }
    }
}
