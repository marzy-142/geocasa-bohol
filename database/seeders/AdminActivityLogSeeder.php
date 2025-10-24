<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\SellerRequest;
use App\Models\ComplianceReport;

class AdminActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin users
        $admins = User::where('role', 'admin')->get();
        if ($admins->isEmpty()) {
            $this->command->warn('No admin users found. Skipping admin activity logs...');
            return;
        }

        // Get other models for target references
        $properties = Property::take(10)->get();
        $transactions = Transaction::take(5)->get();
        $sellerRequests = SellerRequest::take(8)->get();
        $complianceReports = ComplianceReport::take(5)->get();

        $actions = array_keys(AdminActivityLog::ACTIONS);
        $targetTypes = array_keys(AdminActivityLog::TARGET_TYPES);

        // Generate activities for the last 30 days
        for ($i = 0; $i < 200; $i++) {
            $admin = $admins->random();
            $action = $actions[array_rand($actions)];
            $createdAt = now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            // Determine target based on action
            $targetType = null;
            $targetId = null;
            $details = [];

            switch ($action) {
                case 'user_created':
                case 'user_updated':
                case 'user_suspended':
                case 'user_reactivated':
                case 'user_approved':
                case 'user_rejected':
                    $targetType = 'App\\Models\\User';
                    $targetId = User::inRandomOrder()->first()->id;
                    $details = [
                        'user_name' => 'John Doe',
                        'user_email' => 'john@example.com',
                        'changes' => 'Status updated to active'
                    ];
                    break;

                case 'property_created':
                case 'property_updated':
                case 'property_deleted':
                    if ($properties->isNotEmpty()) {
                        $property = $properties->random();
                        $targetType = 'App\\Models\\Property';
                        $targetId = $property->id;
                        $details = [
                            'property_title' => $property->title,
                            'property_type' => $property->type,
                            'changes' => 'Property details updated'
                        ];
                    }
                    break;

                case 'transaction_created':
                case 'transaction_updated':
                    if ($transactions->isNotEmpty()) {
                        $transaction = $transactions->random();
                        $targetType = 'App\\Models\\Transaction';
                        $targetId = $transaction->id;
                        $details = [
                            'transaction_amount' => '₱' . number_format($transaction->total_amount ?? 0, 2),
                            'status' => $transaction->status ?? 'pending'
                        ];
                    }
                    break;

                case 'seller_request_approved':
                case 'seller_request_rejected':
                    if ($sellerRequests->isNotEmpty()) {
                        $request = $sellerRequests->random();
                        $targetType = 'App\\Models\\SellerRequest';
                        $targetId = $request->id;
                        $details = [
                            'property_title' => $request->property_title,
                            'asking_price' => '₱' . number_format($request->asking_price ?? 0, 2),
                            'reason' => $action === 'seller_request_rejected' ? 'Incomplete documentation' : 'All requirements met'
                        ];
                    }
                    break;

                case 'compliance_report_created':
                case 'compliance_report_updated':
                    if ($complianceReports->isNotEmpty()) {
                        $report = $complianceReports->random();
                        $targetType = 'App\\Models\\ComplianceReport';
                        $targetId = $report->id;
                        $details = [
                            'report_type' => $report->report_type,
                            'severity' => $report->severity,
                            'status' => $report->status
                        ];
                    }
                    break;

                case 'bulk_user_action':
                    $details = [
                        'action_type' => 'bulk_approve',
                        'affected_users' => rand(5, 25),
                        'criteria' => 'All pending broker applications'
                    ];
                    break;

                case 'system_settings_updated':
                    $details = [
                        'setting_name' => 'Email Configuration',
                        'old_value' => 'SMTP disabled',
                        'new_value' => 'SMTP enabled',
                        'section' => 'Email Settings'
                    ];
                    break;
            }

            // Generate random IP address
            $ipAddress = $this->generateRandomIP();

            AdminActivityLog::create([
                'admin_id' => $admin->id,
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'details' => $details,
                'ip_address' => $ipAddress,
                'user_agent' => $this->generateRandomUserAgent(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info('Admin activity logs seeded successfully!');
    }

    private function generateRandomIP()
    {
        return rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
    }

    private function generateRandomUserAgent()
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ];

        return $userAgents[array_rand($userAgents)];
    }
}
