<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\BrokerRegistrationRequest;
use App\Services\FileSecurityService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        // Use secure validation for broker registration
        if ($request->role === 'broker') {
            // Create a proper BrokerRegistrationRequest instance
            $brokerRequest = app(BrokerRegistrationRequest::class);
            $brokerRequest->replace($request->all());
            $brokerRequest->files->replace($request->allFiles());
            $brokerRequest->validateResolved();
            $request = $brokerRequest;
        } else {
            // Standard validation for client registration
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
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

            // Explicitly set broker approval fields to override database defaults
            $userData['is_approved'] = false;
            $userData['application_status'] = 'pending';

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
                $userData['additional_documents'] = json_encode($storedFiles['additional_documents']);
            }
        } else {
            // Regular users are auto-approved
            $userData['is_approved'] = true;
            $userData['application_status'] = 'approved';
            $userData['approved_at'] = now();
        }

        $user = User::create($userData);

        event(new Registered($user));

        Auth::login($user);

        // Redirect based on role and approval status
        if ($user->role === 'broker' && !$user->is_approved) {
            return redirect()->route('broker.pending-approval')
                ->with('success', 'Your broker application has been submitted for review.');
        }

        return redirect(route('dashboard', absolute: false));
    }
}
