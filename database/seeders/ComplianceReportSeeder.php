<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ComplianceReport;
use App\Models\User;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Transaction;

class ComplianceReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users for reporting (any role can report)
        $users = User::take(5)->get();
        $brokers = User::where('role', 'broker')->take(3)->get();
        $properties = Property::take(10)->get();
        $inquiries = Inquiry::take(8)->get();
        $transactions = Transaction::take(5)->get();

        if ($users->isEmpty() || $properties->isEmpty()) {
            $this->command->warn('Not enough users or properties to create compliance reports. Skipping...');
            return;
        }

        $reportTypes = array_keys(ComplianceReport::REPORT_TYPES);
        $severities = array_keys(ComplianceReport::SEVERITIES);
        $statuses = array_keys(ComplianceReport::STATUSES);

        // Create compliance reports for properties
        foreach ($properties->take(5) as $property) {
            ComplianceReport::create([
                'reportable_type' => Property::class,
                'reportable_id' => $property->id,
                'report_type' => $reportTypes[array_rand($reportTypes)],
                'severity' => $severities[array_rand($severities)],
                'status' => $statuses[array_rand($statuses)],
                'description' => $this->getRandomDescription($property->title),
                'evidence' => $this->getRandomEvidence(),
                'reported_by' => $users->random()->id,
                'assigned_to' => $brokers->isNotEmpty() ? $brokers->random()->id : null,
                'admin_notes' => $this->getRandomAdminNotes(),
                'reported_at' => now()->subDays(rand(1, 30)),
                'reviewed_at' => now()->subDays(rand(1, 15)),
                'resolved_at' => now()->subDays(rand(1, 5)),
            ]);
        }

        // Create compliance reports for inquiries
        foreach ($inquiries->take(3) as $inquiry) {
            ComplianceReport::create([
                'reportable_type' => Inquiry::class,
                'reportable_id' => $inquiry->id,
                'report_type' => $reportTypes[array_rand($reportTypes)],
                'severity' => $severities[array_rand($severities)],
                'status' => $statuses[array_rand($statuses)],
                'description' => $this->getRandomDescription('inquiry'),
                'evidence' => $this->getRandomEvidence(),
                'reported_by' => $users->random()->id,
                'assigned_to' => $brokers->isNotEmpty() ? $brokers->random()->id : null,
                'admin_notes' => $this->getRandomAdminNotes(),
                'reported_at' => now()->subDays(rand(1, 20)),
                'reviewed_at' => now()->subDays(rand(1, 10)),
                'resolved_at' => now()->subDays(rand(1, 3)),
            ]);
        }

        // Create compliance reports for transactions
        foreach ($transactions->take(2) as $transaction) {
            ComplianceReport::create([
                'reportable_type' => Transaction::class,
                'reportable_id' => $transaction->id,
                'report_type' => $reportTypes[array_rand($reportTypes)],
                'severity' => $severities[array_rand($severities)],
                'status' => $statuses[array_rand($statuses)],
                'description' => $this->getRandomDescription('transaction'),
                'evidence' => $this->getRandomEvidence(),
                'reported_by' => $users->random()->id,
                'assigned_to' => $brokers->isNotEmpty() ? $brokers->random()->id : null,
                'admin_notes' => $this->getRandomAdminNotes(),
                'reported_at' => now()->subDays(rand(1, 15)),
                'reviewed_at' => now()->subDays(rand(1, 8)),
                'resolved_at' => now()->subDays(rand(1, 2)),
            ]);
        }

        $this->command->info('Compliance reports seeded successfully!');
    }

    private function getRandomDescription($context)
    {
        $descriptions = [
            "Suspicious activity detected in {$context}. Multiple users reported unusual behavior patterns.",
            "Potential policy violation in {$context}. Content appears to violate community guidelines.",
            "Fake listing suspected for {$context}. Property details seem inconsistent with reality.",
            "Spam content reported in {$context}. Automated or repetitive content detected.",
            "Inappropriate content found in {$context}. Content may be offensive or inappropriate.",
            "Suspicious user behavior in {$context}. User actions raise red flags.",
            "Data inconsistency in {$context}. Information doesn't match expected format.",
            "Potential fraud in {$context}. Financial transactions appear suspicious.",
            "Unauthorized access attempt in {$context}. Security breach suspected.",
            "Misleading information in {$context}. Details may be intentionally false."
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function getRandomEvidence()
    {
        $evidenceTypes = ['document', 'screenshot', 'testimony', 'physical', 'digital'];
        $evidence = [];

        for ($i = 0; $i < rand(1, 3); $i++) {
            $evidence[] = [
                'name' => 'Evidence_' . ($i + 1) . '.pdf',
                'type' => $evidenceTypes[array_rand($evidenceTypes)],
                'source' => 'User Report',
                'description' => 'Supporting evidence for compliance report',
                'uploaded_at' => now()->subDays(rand(1, 10))->toISOString()
            ];
        }

        return $evidence;
    }

    private function getRandomAdminNotes()
    {
        $notes = [
            "Initial review completed. Investigation required.",
            "Evidence collected and under review.",
            "Contacted relevant parties for additional information.",
            "Escalated to senior management for review.",
            "Additional documentation requested from reporter.",
            "Case assigned to investigation team.",
            "Follow-up scheduled with involved parties.",
            "External expert consultation recommended.",
            "Internal audit initiated.",
            "Legal team notified for compliance review."
        ];

        return rand(0, 1) ? $notes[array_rand($notes)] : null;
    }
}
