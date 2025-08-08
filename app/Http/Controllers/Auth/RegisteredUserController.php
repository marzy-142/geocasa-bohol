<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:client,broker',
        ];

        // Add validation for broker credentials
        if ($request->role === 'broker') {
            $validationRules = array_merge($validationRules, [
                'prc_id' => 'required|string|max:255',
                'prc_id_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
                'business_permit' => 'nullable|string|max:255',
                'business_permit_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'additional_documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);
        }

        $request->validate($validationRules);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ];

        // Handle broker-specific data
        if ($request->role === 'broker') {
            $userData['prc_id'] = $request->prc_id;
            $userData['business_permit'] = $request->business_permit;
            $userData['is_approved'] = false;
            $userData['application_status'] = 'under_review';
            $userData['submitted_at'] = now();

            // Store uploaded files using 'local' disk instead of 'private'
            if ($request->hasFile('prc_id_file')) {
                $userData['prc_id_file'] = $request->file('prc_id_file')
                    ->store('credentials/prc', 'local');
            }

            if ($request->hasFile('business_permit_file')) {
                $userData['business_permit_file'] = $request->file('business_permit_file')
                    ->store('credentials/permits', 'local');
            }

            // Handle additional documents
            $additionalDocs = [];
            if ($request->hasFile('additional_documents')) {
                foreach ($request->file('additional_documents') as $file) {
                    $additionalDocs[] = $file->store('credentials/additional', 'local');
                }
                $userData['additional_documents'] = json_encode($additionalDocs);
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
