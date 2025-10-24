<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $authService = app(\App\Services\EnhancedAuthenticationService::class);
        
        $result = $authService->attemptLogin([
            'email' => $this->email,
            'password' => $this->password,
            'remember' => $this->boolean('remember'),
        ], $this);

        if ($result['success']) {
            // Link existing inquiries and clients to the authenticated user
            $user = $result['user'];
            $inquiryLinkingService = app(\App\Services\InquiryLinkingService::class);
            $linkingResult = $inquiryLinkingService->linkExistingInquiriesToUser($user);

            // Add linking result to session for display if any inquiries were linked
            if ($linkingResult['linked_inquiries'] > 0 || $linkingResult['linked_clients'] > 0) {
                session()->flash('inquiry_linking_success', $linkingResult['message']);
            }

            // Clear inquiry data from session after successful login
            if (session()->has('inquiry_data')) {
                session()->forget('inquiry_data');
            }
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
