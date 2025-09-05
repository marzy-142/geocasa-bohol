<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplianceReport;
use App\Models\InvestigationLog;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\User;
use App\Services\InvestigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ComplianceController extends Controller
{
    protected $investigationService;

    public function __construct(InvestigationService $investigationService)
    {
        $this->investigationService = $investigationService;
    }
    /**
     * Display compliance reports dashboard
     */
    public function index(Request $request)
    {
        $query = ComplianceReport::with(['reportable', 'reporter', 'assignedAdmin'])
            ->orderBy('reported_at', 'desc');

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->report_type) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->severity) {
            $query->where('severity', $request->severity);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhereHas('reporter', function ($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                               ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $reports = $query->paginate(15);

        // Get statistics
        $stats = [
            'total' => ComplianceReport::count(),
            'pending' => ComplianceReport::where('status', 'pending')->count(),
            'under_review' => ComplianceReport::where('status', 'under_review')->count(),
            'high_priority' => ComplianceReport::whereIn('severity', ['high', 'critical'])->count(),
        ];

        return Inertia::render('Admin/Compliance/Index', [
            'reports' => $reports,
            'stats' => $stats,
            'filters' => $request->only(['status', 'report_type', 'severity', 'search']),
            'reportTypes' => ComplianceReport::REPORT_TYPES,
            'severities' => ComplianceReport::SEVERITIES,
            'statuses' => ComplianceReport::STATUSES,
        ]);
    }

    /**
     * Show specific compliance report
     */
    public function show(ComplianceReport $report)
    {
        $report->load(['reportable', 'reporter', 'assignedAdmin', 'investigationLogs.investigator']);

        return Inertia::render('Admin/Compliance/Show', [
            'report' => $report,
            'investigationLogs' => $report->investigationLogs,
            'reportTypes' => ComplianceReport::REPORT_TYPES,
            'severities' => ComplianceReport::SEVERITIES,
            'statuses' => ComplianceReport::STATUSES,
        ]);
    }

    /**
     * Update compliance report status
     */
    public function updateStatus(Request $request, ComplianceReport $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,under_review,resolved,dismissed',
            'admin_notes' => 'nullable|string',
            'resolution_notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $updateData = [
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $report->admin_notes,
        ];

        // Set timestamps based on status
        if ($validated['status'] === 'under_review' && $report->status !== 'under_review') {
            $updateData['reviewed_at'] = now();
            $updateData['assigned_to'] = $validated['assigned_to'] ?? Auth::id();
        }

        if (in_array($validated['status'], ['resolved', 'dismissed']) && !in_array($report->status, ['resolved', 'dismissed'])) {
            $updateData['resolved_at'] = now();
            $updateData['resolution_notes'] = $validated['resolution_notes'];
        }

        $report->update($updateData);

        return redirect()->back()->with('success', 'Compliance report updated successfully.');
    }

    /**
     * Create a new compliance report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reportable_type' => 'required|string',
            'reportable_id' => 'required|integer',
            'report_type' => 'required|in:' . implode(',', array_keys(ComplianceReport::REPORT_TYPES)),
            'severity' => 'required|in:' . implode(',', array_keys(ComplianceReport::SEVERITIES)),
            'description' => 'required|string|max:1000',
            'evidence' => 'nullable|array',
        ]);

        // Verify the reportable item exists
        $reportableClass = 'App\\Models\\' . $validated['reportable_type'];
        if (!class_exists($reportableClass)) {
            return back()->withErrors(['reportable_type' => 'Invalid reportable type.']);
        }

        $reportableItem = $reportableClass::find($validated['reportable_id']);
        if (!$reportableItem) {
            return back()->withErrors(['reportable_id' => 'Reportable item not found.']);
        }

        ComplianceReport::create([
            'reportable_type' => $reportableClass,
            'reportable_id' => $validated['reportable_id'],
            'report_type' => $validated['report_type'],
            'severity' => $validated['severity'],
            'status' => 'pending',
            'description' => $validated['description'],
            'evidence' => $validated['evidence'],
            'reported_by' => Auth::id(),
            'reported_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Compliance report submitted successfully.');
    }

    /**
     * Bulk update compliance reports
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:compliance_reports,id',
            'action' => 'required|in:assign,update_status,dismiss',
            'status' => 'required_if:action,update_status|in:pending,under_review,resolved,dismissed',
            'assigned_to' => 'required_if:action,assign|exists:users,id',
        ]);

        $reports = ComplianceReport::whereIn('id', $validated['report_ids']);

        switch ($validated['action']) {
            case 'assign':
                $reports->update([
                    'assigned_to' => $validated['assigned_to'],
                    'status' => 'under_review',
                    'reviewed_at' => now(),
                ]);
                break;

            case 'update_status':
                $updateData = ['status' => $validated['status']];
                if ($validated['status'] === 'under_review') {
                    $updateData['reviewed_at'] = now();
                    $updateData['assigned_to'] = Auth::id();
                }
                if (in_array($validated['status'], ['resolved', 'dismissed'])) {
                    $updateData['resolved_at'] = now();
                }
                $reports->update($updateData);
                break;

            case 'dismiss':
                $reports->update([
                    'status' => 'dismissed',
                    'resolved_at' => now(),
                ]);
                break;
        }

        return redirect()->back()->with('success', 'Compliance reports updated successfully.');
    }

    /**
     * Start investigation for a compliance report
     */
    public function startInvestigation(Request $request, ComplianceReport $report)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->investigationService->startInvestigation($report, $validated);

        return redirect()->back()->with('success', 'Investigation started successfully.');
    }

    /**
     * Collect evidence for investigation
     */
    public function collectEvidence(Request $request, ComplianceReport $report)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'evidence_type' => 'required|string|in:document,screenshot,testimony,physical,digital',
            'source' => 'nullable|string|max:255',
            'files.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        $this->investigationService->collectEvidence($report, $validated);

        return redirect()->back()->with('success', 'Evidence collected successfully.');
    }

    /**
     * Conduct interview
     */
    public function conductInterview(Request $request, ComplianceReport $report)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'interviewee_type' => 'required|string|in:reporter,subject,witness,other',
            'interviewee_id' => 'nullable|exists:users,id',
            'interview_method' => 'required|string|in:in_person,phone,email,video_call',
            'duration_minutes' => 'nullable|integer|min:1|max:480',
            'key_findings' => 'nullable|string|max:2000',
            'follow_up_required' => 'boolean',
        ]);

        $this->investigationService->conductInterview($report, $validated);

        return redirect()->back()->with('success', 'Interview recorded successfully.');
    }

    /**
     * Add investigation note
     */
    public function addInvestigationNote(Request $request, ComplianceReport $report)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:2000',
            'priority' => 'nullable|string|in:low,medium,high,critical',
        ]);

        $this->investigationService->addNote($report, $validated['note'], [
            'priority' => $validated['priority'] ?? 'medium',
        ]);

        return redirect()->back()->with('success', 'Investigation note added successfully.');
    }

    /**
     * Escalate investigation
     */
    public function escalateInvestigation(Request $request, ComplianceReport $report)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'escalate_to' => 'nullable|exists:users,id',
            'new_severity' => 'nullable|string|in:low,medium,high,critical',
            'urgency_level' => 'required|string|in:low,medium,high,critical',
        ]);

        $this->investigationService->escalateInvestigation($report, $validated);

        return redirect()->back()->with('success', 'Investigation escalated successfully.');
    }

    /**
     * Resolve investigation
     */
    public function resolveInvestigation(Request $request, ComplianceReport $report)
    {
        $validated = $request->validate([
            'resolution_notes' => 'required|string|max:2000',
            'resolution_type' => 'required|string|in:confirmed,unfounded,inconclusive',
            'actions_taken' => 'nullable|array',
            'preventive_measures' => 'nullable|string|max:1000',
            'follow_up_required' => 'boolean',
        ]);

        $this->investigationService->resolveInvestigation($report, $validated);

        return redirect()->back()->with('success', 'Investigation resolved successfully.');
    }

    /**
     * Get investigation timeline
     */
    public function getInvestigationTimeline(ComplianceReport $report)
    {
        $timeline = $this->investigationService->getInvestigationTimeline($report);

        return response()->json([
            'timeline' => $timeline,
        ]);
    }

    /**
     * Investigation dashboard
     */
    public function investigationDashboard(Request $request)
    {
        $stats = $this->investigationService->getInvestigationStats();
        
        // Get recent investigation activities
        $recentActivities = InvestigationLog::with(['complianceReport', 'investigator'])
            ->orderBy('action_taken_at', 'desc')
            ->take(20)
            ->get();

        // Get active investigations
        $activeInvestigations = ComplianceReport::with(['reportable', 'assignedAdmin'])
            ->where('status', 'under_review')
            ->orderBy('reviewed_at', 'asc')
            ->paginate(10);

        return Inertia::render('Admin/Compliance/InvestigationDashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'activeInvestigations' => $activeInvestigations,
        ]);
    }
}
