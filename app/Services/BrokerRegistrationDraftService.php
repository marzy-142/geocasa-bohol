<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BrokerRegistrationDraftService
{
    protected $cachePrefix = 'broker_draft_';
    protected $expirationHours = 72; // 3 days

    /**
     * Save draft registration data
     */
    public function saveDraft(string $sessionId, array $data): array
    {
        try {
            $draftKey = $this->cachePrefix . $sessionId;
            $draftData = [
                'id' => $sessionId,
                'data' => $data,
                'created_at' => now(),
                'updated_at' => now(),
                'step' => $data['current_step'] ?? 1,
                'progress' => $this->calculateProgress($data),
                'expires_at' => now()->addHours($this->expirationHours),
            ];

            Cache::put($draftKey, $draftData, now()->addHours($this->expirationHours));

            Log::info('Broker registration draft saved', [
                'session_id' => $sessionId,
                'step' => $draftData['step'],
                'progress' => $draftData['progress']
            ]);

            return [
                'success' => true,
                'draft_id' => $sessionId,
                'expires_at' => $draftData['expires_at'],
                'progress' => $draftData['progress'] ?? 0
            ];

        } catch (\Exception $e) {
            Log::error('Failed to save broker registration draft', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to save draft'
            ];
        }
    }

    /**
     * Retrieve draft registration data
     */
    public function getDraft(string $sessionId): ?array
    {
        $draftKey = $this->cachePrefix . $sessionId;
        $draft = Cache::get($draftKey);

        if ($draft && $this->isDraftValid($draft)) {
            return $draft;
        }

        return null;
    }

    /**
     * Update draft registration data
     */
    public function updateDraft(string $sessionId, array $newData): array
    {
        $existingDraft = $this->getDraft($sessionId);
        
        if (!$existingDraft) {
            return $this->saveDraft($sessionId, $newData);
        }

        // Merge with existing data
        $mergedData = array_merge($existingDraft['data'], $newData);
        $mergedData['updated_at'] = now();

        return $this->saveDraft($sessionId, $mergedData);
    }

    /**
     * Delete draft registration data
     */
    public function deleteDraft(string $sessionId): bool
    {
        try {
            $draftKey = $this->cachePrefix . $sessionId;
            Cache::forget($draftKey);

            Log::info('Broker registration draft deleted', [
                'session_id' => $sessionId
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete broker registration draft', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get all drafts for a user (if authenticated)
     */
    public function getUserDrafts(string $userId): array
    {
        // This would require storing user_id with drafts
        // For now, we'll implement a simple version
        return [];
    }

    /**
     * Clean up expired drafts
     */
    public function cleanupExpiredDrafts(): int
    {
        // This would need to be implemented with a proper storage mechanism
        // For Cache-based storage, expired items are automatically cleaned up
        return 0;
    }

    /**
     * Calculate progress percentage
     */
    protected function calculateProgress(array $data): int
    {
        $requiredFields = [
            'first_name', 'last_name', 'email', 'phone', 'address',
            'license_number', 'prc_id', 'experience_years', 'specializations',
            'services_offered', 'languages', 'availability', 'hourly_rate'
        ];

        $completedFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($data[$field])) {
                $completedFields++;
            }
        }

        return round(($completedFields / count($requiredFields)) * 100);
    }

    /**
     * Check if draft is still valid
     */
    protected function isDraftValid(array $draft): bool
    {
        return isset($draft['expires_at']) && 
               now()->isBefore($draft['expires_at']);
    }

    /**
     * Generate session ID for anonymous users
     */
    public function generateSessionId(): string
    {
        return Str::uuid()->toString();
    }

    /**
     * Get draft summary
     */
    public function getDraftSummary(string $sessionId): ?array
    {
        $draft = $this->getDraft($sessionId);
        
        if (!$draft) {
            return null;
        }

        return [
            'id' => $draft['id'],
            'step' => $draft['step'],
            'progress' => $draft['progress'],
            'created_at' => $draft['created_at'],
            'updated_at' => $draft['updated_at'],
            'expires_at' => $draft['expires_at'],
            'preview' => [
                'name' => trim(($draft['data']['first_name'] ?? '') . ' ' . ($draft['data']['last_name'] ?? '')),
                'email' => $draft['data']['email'] ?? null,
                'license_number' => $draft['data']['license_number'] ?? null,
            ]
        ];
    }

    /**
     * Validate draft data
     */
    public function validateDraftData(array $data, int $step): array
    {
        $errors = [];

        switch ($step) {
            case 1: // Personal Information
                $errors = array_merge($errors, $this->validatePersonalInfo($data));
                break;
            case 2: // Professional Information
                $errors = array_merge($errors, $this->validateProfessionalInfo($data));
                break;
            case 3: // Services & Specializations
                $errors = array_merge($errors, $this->validateServicesInfo($data));
                break;
            case 4: // Availability & Rates
                $errors = array_merge($errors, $this->validateAvailabilityInfo($data));
                break;
        }

        return $errors;
    }

    /**
     * Validate personal information step
     */
    protected function validatePersonalInfo(array $data): array
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        if (empty($data['phone'])) {
            $errors['phone'] = 'Phone number is required';
        }

        return $errors;
    }

    /**
     * Validate professional information step
     */
    protected function validateProfessionalInfo(array $data): array
    {
        $errors = [];

        if (empty($data['license_number'])) {
            $errors['license_number'] = 'PRC license number is required';
        }
        if (empty($data['prc_id'])) {
            $errors['prc_id'] = 'PRC ID is required';
        }
        if (empty($data['experience_years']) || $data['experience_years'] < 0) {
            $errors['experience_years'] = 'Years of experience must be a positive number';
        }

        return $errors;
    }

    /**
     * Validate services information step
     */
    protected function validateServicesInfo(array $data): array
    {
        $errors = [];

        if (empty($data['specializations']) || !is_array($data['specializations'])) {
            $errors['specializations'] = 'At least one specialization is required';
        }
        if (empty($data['services_offered']) || !is_array($data['services_offered'])) {
            $errors['services_offered'] = 'At least one service is required';
        }

        return $errors;
    }

    /**
     * Validate availability information step
     */
    protected function validateAvailabilityInfo(array $data): array
    {
        $errors = [];

        if (empty($data['availability']) || !is_array($data['availability'])) {
            $errors['availability'] = 'Availability schedule is required';
        }
        if (empty($data['hourly_rate']) || $data['hourly_rate'] <= 0) {
            $errors['hourly_rate'] = 'Hourly rate must be greater than 0';
        }

        return $errors;
    }
}
