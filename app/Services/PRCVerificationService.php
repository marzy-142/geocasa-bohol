<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PRCVerificationService
{
    protected $apiUrl;
    protected $apiKey;
    protected $timeout;

    public function __construct()
    {
        $this->apiUrl = config('services.prc.api_url', 'https://api.prc.gov.ph/verify');
        $this->apiKey = config('services.prc.api_key');
        $this->timeout = config('services.prc.timeout', 30);
    }

    /**
     * Verify PRC license number
     */
    public function verifyLicense(string $licenseNumber, string $lastName, string $firstName): array
    {
        // Check cache first
        $cacheKey = "prc_verification_{$licenseNumber}_{$lastName}_{$firstName}";
        $cached = Cache::get($cacheKey);
        
        if ($cached !== null) {
            Log::info("PRC verification cache hit for license: {$licenseNumber}");
            return $cached;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'license_number' => $licenseNumber,
                    'last_name' => $lastName,
                    'first_name' => $firstName,
                ]);

            $result = $this->processResponse($response, $licenseNumber);
            
            // Cache successful verifications for 24 hours
            if ($result['success']) {
                Cache::put($cacheKey, $result, now()->addHours(24));
            }
            
            return $result;

        } catch (\Exception $e) {
            Log::error('PRC verification failed', [
                'license_number' => $licenseNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'PRC verification service temporarily unavailable',
                'license_number' => $licenseNumber,
                'verified' => false,
                'details' => null
            ];
        }
    }

    /**
     * Process API response
     */
    protected function processResponse($response, string $licenseNumber): array
    {
        if (!$response->successful()) {
            Log::warning('PRC API returned non-successful status', [
                'status' => $response->status(),
                'body' => $response->body(),
                'license_number' => $licenseNumber
            ]);

            return [
                'success' => false,
                'error' => 'PRC verification service returned an error',
                'license_number' => $licenseNumber,
                'verified' => false,
                'details' => null
            ];
        }

        $data = $response->json();
        
        return [
            'success' => true,
            'error' => null,
            'license_number' => $licenseNumber,
            'verified' => $data['verified'] ?? false,
            'details' => [
                'name' => $data['name'] ?? null,
                'profession' => $data['profession'] ?? null,
                'validity_period' => $data['validity_period'] ?? null,
                'status' => $data['status'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'registration_date' => $data['registration_date'] ?? null,
            ]
        ];
    }

    /**
     * Mock verification for development/testing
     */
    public function mockVerification(string $licenseNumber, string $lastName, string $firstName): array
    {
        Log::info("Using mock PRC verification for license: {$licenseNumber}");
        
        // Simulate API delay
        usleep(500000); // 0.5 seconds
        
        // Mock response based on license number patterns
        $isValid = $this->isValidMockLicense($licenseNumber, $lastName, $firstName);
        
        return [
            'success' => true,
            'error' => null,
            'license_number' => $licenseNumber,
            'verified' => $isValid,
            'details' => $isValid ? [
                'name' => "{$firstName} {$lastName}",
                'profession' => 'Real Estate Broker',
                'validity_period' => '2023-2025',
                'status' => 'Active',
                'expiry_date' => '2025-12-31',
                'registration_date' => '2023-01-15',
            ] : null
        ];
    }

    /**
     * Check if mock license is valid
     */
    protected function isValidMockLicense(string $licenseNumber, string $lastName, string $firstName): bool
    {
        // Simple mock validation rules - allow international characters
        return strlen($licenseNumber) >= 6 && 
               mb_strlen($lastName) >= 2 && 
               mb_strlen($firstName) >= 2;
    }

    /**
     * Get verification status
     */
    public function getVerificationStatus(string $licenseNumber): array
    {
        $cacheKey = "prc_status_{$licenseNumber}";
        return Cache::get($cacheKey, [
            'last_verified' => null,
            'verification_count' => 0,
            'status' => 'not_verified'
        ]);
    }

    /**
     * Update verification status
     */
    public function updateVerificationStatus(string $licenseNumber, array $result): void
    {
        $cacheKey = "prc_status_{$licenseNumber}";
        $status = Cache::get($cacheKey, [
            'last_verified' => null,
            'verification_count' => 0,
            'status' => 'not_verified'
        ]);

        $status['last_verified'] = now();
        $status['verification_count']++;
        $status['status'] = $result['verified'] ? 'verified' : 'failed';
        $status['last_result'] = $result;

        Cache::put($cacheKey, $status, now()->addDays(30));
    }
}