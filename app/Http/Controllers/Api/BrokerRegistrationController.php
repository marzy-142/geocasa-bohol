<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BrokerRegistrationDraftService;
use App\Services\PRCVerificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class BrokerRegistrationController extends Controller
{
    protected $draftService;
    protected $prcService;

    public function __construct(
        BrokerRegistrationDraftService $draftService,
        PRCVerificationService $prcService
    ) {
        $this->draftService = $draftService;
        $this->prcService = $prcService;
    }

    /**
     * Save draft registration data
     */
    public function saveDraft(Request $request): JsonResponse
    {
        try {
            $sessionId = $request->input('session_id') ?? $this->draftService->generateSessionId();
            $step = $request->input('step', 1);
            $data = $request->except(['session_id', 'step']);
            $data['current_step'] = $step;

            // Validate data for current step
            $errors = $this->draftService->validateDraftData($data, $step);
            
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'errors' => $errors,
                    'message' => 'Validation failed for current step'
                ], 422);
            }

            $result = $this->draftService->saveDraft($sessionId, $data);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'draft_id' => $result['draft_id'],
                    'session_id' => $sessionId,
                    'progress' => $result['progress'],
                    'expires_at' => $result['expires_at'],
                    'message' => 'Draft saved successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save draft'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Failed to save broker registration draft', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the draft'
            ], 500);
        }
    }

    /**
     * Retrieve draft registration data
     */
    public function getDraft(Request $request): JsonResponse
    {
        try {
            $sessionId = $request->input('session_id');
            
            if (!$sessionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session ID is required'
                ], 400);
            }

            $draft = $this->draftService->getDraft($sessionId);

            if (!$draft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Draft not found or expired'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'draft' => $draft,
                'summary' => $this->draftService->getDraftSummary($sessionId)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve broker registration draft', [
                'error' => $e->getMessage(),
                'session_id' => $request->input('session_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the draft'
            ], 500);
        }
    }

    /**
     * Update draft registration data
     */
    public function updateDraft(Request $request): JsonResponse
    {
        try {
            $sessionId = $request->input('session_id');
            
            if (!$sessionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session ID is required'
                ], 400);
            }

            $step = $request->input('step', 1);
            $data = $request->except(['session_id', 'step']);
            $data['current_step'] = $step;

            // Validate data for current step
            $errors = $this->draftService->validateDraftData($data, $step);
            
            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'errors' => $errors,
                    'message' => 'Validation failed for current step'
                ], 422);
            }

            $result = $this->draftService->updateDraft($sessionId, $data);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'draft_id' => $result['draft_id'],
                    'session_id' => $sessionId,
                    'progress' => $result['progress'],
                    'expires_at' => $result['expires_at'],
                    'message' => 'Draft updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update draft'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update broker registration draft', [
                'error' => $e->getMessage(),
                'session_id' => $request->input('session_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the draft'
            ], 500);
        }
    }

    /**
     * Delete draft registration data
     */
    public function deleteDraft(Request $request): JsonResponse
    {
        try {
            $sessionId = $request->input('session_id');
            
            if (!$sessionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session ID is required'
                ], 400);
            }

            $success = $this->draftService->deleteDraft($sessionId);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Draft deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete draft'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Failed to delete broker registration draft', [
                'error' => $e->getMessage(),
                'session_id' => $request->input('session_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the draft'
            ], 500);
        }
    }

    /**
     * Verify PRC license
     */
    public function verifyPRCLicense(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'license_number' => 'required|string|max:50',
                'last_name' => 'required|string|max:100',
                'first_name' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                    'message' => 'Validation failed'
                ], 422);
            }

            $licenseNumber = $request->input('license_number');
            $lastName = $request->input('last_name');
            $firstName = $request->input('first_name');

            // Use mock verification in development, real API in production
            if (config('services.prc.mock_mode', true)) {
                $result = $this->prcService->mockVerification($licenseNumber, $lastName, $firstName);
            } else {
                $result = $this->prcService->verifyLicense($licenseNumber, $lastName, $firstName);
            }

            // Update verification status
            $this->prcService->updateVerificationStatus($licenseNumber, $result);

            return response()->json([
                'success' => $result['success'],
                'verified' => $result['verified'],
                'error' => $result['error'],
                'details' => $result['details'],
                'license_number' => $result['license_number']
            ]);

        } catch (\Exception $e) {
            Log::error('PRC license verification failed', [
                'error' => $e->getMessage(),
                'license_number' => $request->input('license_number')
            ]);

            return response()->json([
                'success' => false,
                'verified' => false,
                'error' => 'PRC verification service temporarily unavailable',
                'license_number' => $request->input('license_number'),
                'details' => null
            ], 500);
        }
    }

    /**
     * Get PRC verification status
     */
    public function getPRCVerificationStatus(Request $request): JsonResponse
    {
        try {
            $licenseNumber = $request->input('license_number');
            
            if (!$licenseNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'License number is required'
                ], 400);
            }

            $status = $this->prcService->getVerificationStatus($licenseNumber);

            return response()->json([
                'success' => true,
                'status' => $status
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get PRC verification status', [
                'error' => $e->getMessage(),
                'license_number' => $request->input('license_number')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving verification status'
            ], 500);
        }
    }

    /**
     * Generate new session ID
     */
    public function generateSessionId(): JsonResponse
    {
        try {
            $sessionId = $this->draftService->generateSessionId();

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'message' => 'Session ID generated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate session ID', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating session ID'
            ], 500);
        }
    }
}

