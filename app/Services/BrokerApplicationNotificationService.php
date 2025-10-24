<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\NewBrokerApplicationNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class BrokerApplicationNotificationService
{
    /**
     * Notify admins about new broker application
     */
    public function notifyNewApplication(array $applicationData): void
    {
        try {
            $admins = $this->getAdminUsers();
            
            if ($admins->isEmpty()) {
                Log::warning('No admin users found for broker application notification');
                return;
            }

            $notification = new NewBrokerApplicationNotification($applicationData);
            
            Notification::send($admins, $notification);

            Log::info('Broker application notifications sent', [
                'admin_count' => $admins->count(),
                'application_id' => $applicationData['id'] ?? 'unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send broker application notifications', [
                'error' => $e->getMessage(),
                'application_data' => $applicationData
            ]);
        }
    }

    /**
     * Notify admins about application status change
     */
    public function notifyStatusChange(array $applicationData, string $oldStatus, string $newStatus): void
    {
        try {
            $admins = $this->getAdminUsers();
            
            if ($admins->isEmpty()) {
                Log::warning('No admin users found for status change notification');
                return;
            }

            $notification = new \App\Notifications\BrokerApplicationStatusChangeNotification(
                $applicationData, 
                $oldStatus, 
                $newStatus
            );
            
            Notification::send($admins, $notification);

            Log::info('Broker application status change notifications sent', [
                'admin_count' => $admins->count(),
                'application_id' => $applicationData['id'] ?? 'unknown',
                'status_change' => "{$oldStatus} -> {$newStatus}"
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send broker application status change notifications', [
                'error' => $e->getMessage(),
                'application_data' => $applicationData
            ]);
        }
    }

    /**
     * Notify applicant about application status
     */
    public function notifyApplicant(array $applicationData, string $status, string $message = null): void
    {
        try {
            if (empty($applicationData['email'])) {
                Log::warning('No email address found for applicant notification');
                return;
            }

            $notification = new \App\Notifications\BrokerApplicationStatusNotification(
                $applicationData,
                $status,
                $message
            );

            Notification::route('mail', $applicationData['email'])
                ->notify($notification);

            Log::info('Broker application status notification sent to applicant', [
                'email' => $applicationData['email'],
                'status' => $status,
                'application_id' => $applicationData['id'] ?? 'unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send applicant notification', [
                'error' => $e->getMessage(),
                'email' => $applicationData['email'] ?? 'unknown'
            ]);
        }
    }

    /**
     * Send daily summary to admins
     */
    public function sendDailySummary(): void
    {
        try {
            $admins = $this->getAdminUsers();
            
            if ($admins->isEmpty()) {
                return;
            }

            $summary = $this->getDailyApplicationSummary();
            
            $notification = new \App\Notifications\BrokerApplicationDailySummaryNotification($summary);
            
            Notification::send($admins, $notification);

            Log::info('Daily broker application summary sent', [
                'admin_count' => $admins->count(),
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send daily summary', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get admin users
     */
    protected function getAdminUsers()
    {
        return User::where('role', 'admin')
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get daily application summary
     */
    protected function getDailyApplicationSummary(): array
    {
        $today = now()->startOfDay();
        $yesterday = $today->copy()->subDay();

        return [
            'date' => $today->format('Y-m-d'),
            'new_applications' => $this->getApplicationCount($today, $today->copy()->endOfDay()),
            'pending_applications' => $this->getApplicationCount(null, null, 'pending'),
            'approved_applications' => $this->getApplicationCount($yesterday, $yesterday->copy()->endOfDay(), 'approved'),
            'rejected_applications' => $this->getApplicationCount($yesterday, $yesterday->copy()->endOfDay(), 'rejected'),
        ];
    }

    /**
     * Get application count
     */
    protected function getApplicationCount($startDate = null, $endDate = null, $status = null): int
    {
        // This would query your broker applications table
        // For now, return mock data
        return rand(0, 10);
    }

    /**
     * Send urgent notification for failed PRC verification
     */
    public function notifyFailedPRCVerification(array $applicationData, array $verificationResult): void
    {
        try {
            $admins = $this->getAdminUsers();
            
            if ($admins->isEmpty()) {
                return;
            }

            $notification = new \App\Notifications\FailedPRCVerificationNotification(
                $applicationData,
                $verificationResult
            );
            
            Notification::send($admins, $notification);

            Log::warning('Failed PRC verification notification sent', [
                'admin_count' => $admins->count(),
                'license_number' => $applicationData['license_number'] ?? 'unknown',
                'verification_error' => $verificationResult['error'] ?? 'unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send PRC verification failure notification', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send notification for incomplete applications
     */
    public function notifyIncompleteApplications(): void
    {
        try {
            $admins = $this->getAdminUsers();
            
            if ($admins->isEmpty()) {
                return;
            }

            $incompleteCount = $this->getIncompleteApplicationCount();
            
            if ($incompleteCount > 0) {
                $notification = new \App\Notifications\IncompleteBrokerApplicationsNotification($incompleteCount);
                
                Notification::send($admins, $notification);

                Log::info('Incomplete applications notification sent', [
                    'admin_count' => $admins->count(),
                    'incomplete_count' => $incompleteCount
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send incomplete applications notification', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get count of incomplete applications
     */
    protected function getIncompleteApplicationCount(): int
    {
        // This would query your broker applications table for incomplete applications
        // For now, return mock data
        return rand(0, 5);
    }
}

