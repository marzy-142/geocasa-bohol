<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\BrokerRegistrationRequest;
use App\Services\FileSecurityService;
use App\Services\InquiryLinkingService;
use App\Services\PRCVerificationService;
use App\Services\BrokerApplicationNotificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        // Get inquiry data from session for auto-population
        $inquiryData = session('inquiry_data');
        
        // Clear inquiry data older than 30 minutes to prevent stale data
        if ($inquiryData && isset($inquiryData['timestamp'])) {
            $thirtyMinutesAgo = now()->subMinutes(30)->timestamp;
            if ($inquiryData['timestamp'] < $thirtyMinutesAgo) {
                session()->forget('inquiry_data');
                $inquiryData = null;
            }
        }
        
        // Use simplified registration for regular users, enhanced for brokers
        $template = 'Auth/EnhancedRegister'; // Default to enhanced for now
        
        return Inertia::render($template, [
            'inquiryData' => $inquiryData
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Use enhanced authentication service for all registrations
        $authService = app(\App\Services\EnhancedAuthenticationService::class);
        
        // Use secure validation for broker registration
        if ($request->role === 'broker') {
            // Create a proper BrokerRegistrationRequest instance
            $brokerRequest = app(BrokerRegistrationRequest::class);
            $brokerRequest->replace($request->all());
            $brokerRequest->files->replace($request->allFiles());
            $brokerRequest->validateResolved();
            $request = $brokerRequest;
        } else {
            // Enhanced validation for client registration with same security standards
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
                'password' => \App\Rules\StandardPassword::rules(),
                'role' => 'required|in:client,broker',
            ]);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ];

        // Handle broker-specific data
        if ($request->role === 'broker') {
            $userData['birthdate'] = $request->birthdate;
            $userData['prc_id'] = $request->prc_id;
            $userData['prc_license_expiration'] = $request->prc_license_expiration; // New field
            $userData['phone'] = $request->phone; // Now required for brokers
            $userData['company_address'] = $request->company_address;
            $userData['brokerage_firm_name'] = $request->brokerage_firm_name; // New field
            $userData['office_address'] = $request->office_address; // New field
            $userData['office_contact_number'] = $request->office_contact_number; // New field
            $userData['years_experience'] = $request->years_experience;
            $userData['specialization'] = $request->specialization ? json_encode($request->specialization) : null;
            $userData['city'] = $request->city;
            $userData['province'] = $request->province;
            $userData['address'] = $request->address;
            $userData['postal_code'] = $request->postal_code;
            $userData['terms_accepted'] = $request->terms_accepted;
            $userData['privacy_policy_accepted'] = $request->privacy_policy_accepted;
            $userData['information_certified'] = $request->information_certified; // New field
            $userData['prc_verification_consent'] = $request->prc_verification_consent; // New field
            $userData['submitted_at'] = now();

            // PRC License Verification
            $prcVerificationResult = $this->verifyPRCLicense($request);
            $userData['prc_verification_status'] = $prcVerificationResult['verified'] ? 'verified' : 'failed';
            $userData['prc_verification_result'] = $prcVerificationResult;
            $userData['prc_verified_at'] = $prcVerificationResult['verified'] ? now() : null;

            // Explicitly set broker approval fields to override database defaults
            $userData['is_approved'] = false;
            $userData['application_status'] = $prcVerificationResult['verified'] ? 'pending' : 'prc_verification_failed';

            // Store uploaded files securely using the enhanced security service
            $storedFiles = $request->storeFilesSecurely([
                'prc_id_file',
                'business_permit_file',
                'additional_documents'
            ], 'credentials', 'local');

            if (isset($storedFiles['prc_id_file'])) {
                $userData['prc_id_file'] = $storedFiles['prc_id_file'];
            }

            if (isset($storedFiles['business_permit_file'])) {
                $userData['business_permit_file'] = $storedFiles['business_permit_file'];
            }

            if (isset($storedFiles['additional_documents'])) {
                // Ensure it's properly formatted as an array
                $userData['additional_documents'] = is_array($storedFiles['additional_documents']) 
                    ? $storedFiles['additional_documents'] 
                    : [$storedFiles['additional_documents']];
            }
        } else {
            // Regular users are auto-approved
            $userData['is_approved'] = true;
            $userData['application_status'] = 'approved';
            $userData['approved_at'] = now();
        }

        // Create user with email verification requirement
        $user = User::create($userData);

        // Link existing inquiries and clients to the new user
        $inquiryLinkingService = app(InquiryLinkingService::class);
        $linkingResult = $inquiryLinkingService->linkExistingInquiriesToUser($user);

        // Send email verification notification
        event(new Registered($user));
        $user->sendEmailVerificationNotification();

        // Handle broker-specific notifications and verification
        if ($user->role === 'broker') {
            $this->handleBrokerRegistrationNotifications($user, $prcVerificationResult ?? null);
        }

        // Only login if email is verified - require email verification for security
        if ($user->hasVerifiedEmail()) {
            Auth::login($user);
        } else {
            // Store user ID in session for post-verification login
            session(['pending_user_id' => $user->id]);
        }

        // Add linking result to session for display
        if ($linkingResult['linked_inquiries'] > 0 || $linkingResult['linked_clients'] > 0) {
            session()->flash('inquiry_linking_success', $linkingResult['message']);
        }

        // Redirect based on email verification status and role
        if (!$user->hasVerifiedEmail()) {
            // Redirect to email verification notice
            session()->forget('inquiry_data');
            // Flash the success message explicitly
            session()->flash('success', 'Registration successful! Please check your email and click the verification link to complete your registration.');
            // Pass the email as a query parameter for display
            return redirect()->route('verification.notice', ['registered' => '1', 'email' => $user->email]);
        }

        // Email is verified - proceed with normal flow
        if ($user->role === 'broker' && !$user->is_approved) {
            // Clear inquiry data from session if present
            if (session()->has('inquiry_data')) {
                session()->forget('inquiry_data');
            }
            return redirect()->route('broker.pending-approval')
                ->with('success', 'Your broker application has been submitted for review.');
        }

        // If inquiry data was present in session, clear it and redirect to client dashboard
        if (session()->has('inquiry_data')) {
            session()->forget('inquiry_data');
            if ($user->role === 'client') {
                return redirect()->route('client.dashboard');
            }
        }

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Verify PRC license using the verification service
     */
    protected function verifyPRCLicense(Request $request): array
    {
        try {
            $prcService = app(PRCVerificationService::class);
            
            // Use mock verification in development, real API in production
            if (config('services.prc.mock_mode', true)) {
                return $prcService->mockVerification(
                    $request->prc_id,
                    $request->last_name ?? '',
                    $request->first_name ?? ''
                );
            } else {
                return $prcService->verifyLicense(
                    $request->prc_id,
                    $request->last_name ?? '',
                    $request->first_name ?? ''
                );
            }
        } catch (\Exception $e) {
            Log::error('PRC verification failed during registration', [
                'error' => $e->getMessage(),
                'prc_id' => $request->prc_id ?? 'unknown'
            ]);

            return [
                'success' => false,
                'error' => 'PRC verification service unavailable',
                'verified' => false,
                'details' => null
            ];
        }
    }

    /**
     * Handle broker registration notifications
     */
    protected function handleBrokerRegistrationNotifications(User $user, ?array $prcVerificationResult): void
    {
        try {
            $notificationService = app(BrokerApplicationNotificationService::class);
            
            // Prepare application data for notifications
            $applicationData = [
                'id' => $user->id,
                'first_name' => explode(' ', $user->name)[0] ?? '',
                'last_name' => implode(' ', array_slice(explode(' ', $user->name), 1)) ?: '',
                'email' => $user->email,
                'phone' => $user->phone,
                'license_number' => $user->prc_id,
                'experience_years' => $user->years_experience,
                'application_status' => $user->application_status,
                'submitted_at' => $user->submitted_at,
            ];

            // Notify admins about new application
            $notificationService->notifyNewApplication($applicationData);

            // Handle PRC verification results
            if ($prcVerificationResult && !$prcVerificationResult['verified']) {
                $notificationService->notifyFailedPRCVerification($applicationData, $prcVerificationResult);
                
                // Notify applicant about verification failure
                $notificationService->notifyApplicant(
                    $applicationData,
                    'prc_verification_failed',
                    'Your PRC license could not be verified. Please check your license details and try again.'
                );
            }

            Log::info('Broker registration notifications sent', [
                'user_id' => $user->id,
                'email' => $user->email,
                'prc_verified' => $prcVerificationResult['verified'] ?? false
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send broker registration notifications', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
