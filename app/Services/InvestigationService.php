<?php

namespace App\Services;

use App\Models\ComplianceReport;
use App\Models\InvestigationLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvestigationService
{
    /**
     * Start an investigation for a compliance report
     */
    public function startInvestigation(ComplianceReport $report, array $data = []): InvestigationLog
    {
        // Update report status to under_review if not already
        if ($report->status === 'pending') {
            $report->update([
                'status' => 'under_review',
                'reviewed_at' => now(),
                'assigned_to' => Auth::id(),
            ]);
        }

        return $this->logAction($report, 'status_changed', 'Investigation started', [
            'previous_status' => 'pending',
            'new_status' => 'under_review',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Collect evidence for an investigation
     */
    public function collectEvidence(ComplianceReport $report, array $data): InvestigationLog
    {
        $evidenceFiles = [];
        
        // Handle file uploads if any
        if (isset($data['files'])) {
            foreach ($data['files'] as $file) {
                $path = $file->store('investigations/evidence', 'private');
                $evidenceFiles[] = [
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        return $this->logAction($report, 'evidence_collected', $data['description'], [
            'evidence_type' => $data['evidence_type'] ?? 'document',
            'source' => $data['source'] ?? null,
            'collected_by' => Auth::user()->name,
        ], $evidenceFiles);
    }

    /**
     * Conduct an interview
     */
    public function conductInterview(ComplianceReport $report, array $data): InvestigationLog
    {
        return $this->logAction($report, 'interview_conducted', $data['description'], [
            'interviewee_type' => $data['interviewee_type'], // 'reporter', 'subject', 'witness'
            'interviewee_id' => $data['interviewee_id'] ?? null,
            'interview_method' => $data['interview_method'] ?? 'in_person', // 'phone', 'email', 'video_call'
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'key_findings' => $data['key_findings'] ?? null,
            'follow_up_required' => $data['follow_up_required'] ?? false,
        ]);
    }

    /**
     * Add investigation notes
     */
    public function addNote(ComplianceReport $report, string $note, array $metadata = []): InvestigationLog
    {
        return $this->logAction($report, 'note_added', $note, $metadata);
    }

    /**
     * Escalate an investigation
     */
    public function escalateInvestigation(ComplianceReport $report, array $data): InvestigationLog
    {
        // Update report severity if escalating
        if (isset($data['new_severity'])) {
            $report->update(['severity' => $data['new_severity']]);
        }

        // Assign to supervisor or admin if specified
        if (isset($data['escalate_to'])) {
            $report->update(['assigned_to' => $data['escalate_to']]);
        }

        return $this->logAction($report, 'escalated', $data['reason'], [
            'escalated_to' => $data['escalate_to'] ?? null,
            'previous_severity' => $report->getOriginal('severity'),
            'new_severity' => $data['new_severity'] ?? $report->severity,
            'urgency_level' => $data['urgency_level'] ?? 'high',
        ]);
    }

    /**
     * Resolve an investigation
     */
    public function resolveInvestigation(ComplianceReport $report, array $data): InvestigationLog
    {
        $report->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => $data['resolution_notes'],
        ]);

        return $this->logAction($report, 'resolved', 'Investigation completed and resolved', [
            'resolution_type' => $data['resolution_type'] ?? 'confirmed', // 'confirmed', 'unfounded', 'inconclusive'
            'actions_taken' => $data['actions_taken'] ?? [],
            'preventive_measures' => $data['preventive_measures'] ?? null,
            'follow_up_required' => $data['follow_up_required'] ?? false,
        ]);
    }

    /**
     * Get investigation timeline
     */
    public function getInvestigationTimeline(ComplianceReport $report): array
    {
        $logs = $report->investigationLogs()->with('investigator')->get();
        
        return $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'action_type' => $log->action_type,
                'description' => $log->description,
                'investigator' => $log->investigator->name,
                'action_taken_at' => $log->action_taken_at,
                'metadata' => $log->metadata,
                'evidence_files' => $log->evidence_files,
                'badge_class' => $log->action_type_badge,
            ];
        })->toArray();
    }

    /**
     * Get investigation statistics
     */
    public function getInvestigationStats(User $investigator = null): array
    {
        $query = InvestigationLog::query();
        
        if ($investigator) {
            $query->where('investigator_id', $investigator->id);
        }

        $recentLogs = $query->where('action_taken_at', '>=', now()->subDays(30))->get();

        return [
            'total_actions' => $recentLogs->count(),
            'evidence_collected' => $recentLogs->where('action_type', 'evidence_collected')->count(),
            'interviews_conducted' => $recentLogs->where('action_type', 'interview_conducted')->count(),
            'cases_resolved' => $recentLogs->where('action_type', 'resolved')->count(),
            'cases_escalated' => $recentLogs->where('action_type', 'escalated')->count(),
            'avg_resolution_time' => $this->calculateAverageResolutionTime($investigator),
        ];
    }

    /**
     * Log an investigation action
     */
    private function logAction(
        ComplianceReport $report, 
        string $actionType, 
        string $description, 
        array $metadata = [], 
        array $evidenceFiles = []
    ): InvestigationLog {
        return InvestigationLog::create([
            'compliance_report_id' => $report->id,
            'investigator_id' => Auth::id(),
            'action_type' => $actionType,
            'description' => $description,
            'metadata' => $metadata,
            'evidence_files' => $evidenceFiles,
            'action_taken_at' => now(),
        ]);
    }

    /**
     * Calculate average resolution time for investigations
     */
    private function calculateAverageResolutionTime(User $investigator = null): ?float
    {
        $query = ComplianceReport::where('status', 'resolved')
            ->whereNotNull('resolved_at')
            ->whereNotNull('reviewed_at');

        if ($investigator) {
            $query->where('assigned_to', $investigator->id);
        }

        $resolvedReports = $query->get();

        if ($resolvedReports->isEmpty()) {
            return null;
        }

        $totalHours = $resolvedReports->sum(function ($report) {
            return $report->resolved_at->diffInHours($report->reviewed_at);
        });

        return round($totalHours / $resolvedReports->count(), 2);
    }
}