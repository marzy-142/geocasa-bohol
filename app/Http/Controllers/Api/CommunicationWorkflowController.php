<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CommunicationWorkflowService;
use App\Models\CommunicationWorkflow;
use App\Models\Inquiry;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunicationWorkflowController extends Controller
{
    protected CommunicationWorkflowService $workflowService;

    public function __construct(CommunicationWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Get workflows for a broker
     */
    public function getBrokerWorkflows(Request $request, int $brokerId = null): JsonResponse
    {
        $brokerId = $brokerId ?? Auth::id();
        $status = $request->get('status');
        $type = $request->get('type');
        $limit = $request->get('limit', 20);

        // Check authorization
        if (!Auth::user()->isAdmin() && Auth::id() !== $brokerId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to workflows'
            ], 403);
        }

        try {
            $query = CommunicationWorkflow::where('broker_id', $brokerId)
                ->with(['workflowable', 'broker:id,name', 'client:id,name']);

            if ($status) {
                $query->where('status', $status);
            }

            if ($type) {
                $query->where('workflow_type', $type);
            }

            $workflows = $query->latest()
                ->limit($limit)
                ->get()
                ->map->summary;

            return response()->json([
                'success' => true,
                'data' => $workflows
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve workflows',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workflows for a client
     */
    public function getClientWorkflows(Request $request, int $clientId): JsonResponse
    {
        try {
            $query = CommunicationWorkflow::where('client_id', $clientId)
                ->with(['workflowable', 'broker:id,name']);

            $status = $request->get('status');
            if ($status) {
                $query->where('status', $status);
            }

            $workflows = $query->latest()
                ->get()
                ->map->summary;

            return response()->json([
                'success' => true,
                'data' => $workflows
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve client workflows',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workflow statistics
     */
    public function getWorkflowStatistics(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
        }

        $days = $request->get('days', 7);

        try {
            $statistics = $this->workflowService->getWorkflowStatistics($days);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve workflow statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get overdue workflows
     */
    public function getOverdueWorkflows(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
        }

        try {
            $overdueWorkflows = CommunicationWorkflow::overdue()
                ->with(['workflowable', 'broker:id,name', 'client:id,name'])
                ->latest('scheduled_at')
                ->get()
                ->map->summary;

            return response()->json([
                'success' => true,
                'data' => $overdueWorkflows
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve overdue workflows',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process pending workflows
     */
    public function processPendingWorkflows(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
        }

        try {
            $results = $this->workflowService->processPendingWorkflows();

            return response()->json([
                'success' => true,
                'message' => 'Workflow processing completed',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process workflows',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-escalate unanswered inquiries
     */
    public function autoEscalateInquiries(Request $request): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
        }

        $hoursThreshold = $request->get('hours_threshold', 24);

        try {
            $results = $this->workflowService->autoEscalateUnansweredInquiries($hoursThreshold);

            return response()->json([
                'success' => true,
                'message' => 'Auto-escalation completed',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-escalate inquiries',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create workflow for inquiry
     */
    public function createInquiryWorkflow(Request $request): JsonResponse
    {
        $request->validate([
            'inquiry_id' => 'required|exists:inquiries,id'
        ]);

        try {
            $inquiry = Inquiry::findOrFail($request->inquiry_id);
            
            // Check if user can create workflow for this inquiry
            if (!Auth::user()->isAdmin() && $inquiry->assigned_broker_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to create workflow for this inquiry'
                ], 403);
            }

            $workflow = $this->workflowService->createInquiryWorkflow($inquiry);

            return response()->json([
                'success' => true,
                'message' => 'Workflow created successfully',
                'data' => $workflow->summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create inquiry workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create workflow for transaction
     */
    public function createTransactionWorkflow(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id'
        ]);

        try {
            $transaction = Transaction::findOrFail($request->transaction_id);
            
            // Check if user can create workflow for this transaction
            if (!Auth::user()->isAdmin() && $transaction->broker_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to create workflow for this transaction'
                ], 403);
            }

            $workflow = $this->workflowService->createTransactionWorkflow($transaction);

            return response()->json([
                'success' => true,
                'message' => 'Workflow created successfully',
                'data' => $workflow->summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create transaction workflow',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update workflow status
     */
    public function updateWorkflowStatus(Request $request, int $workflowId): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,escalated,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $workflow = CommunicationWorkflow::findOrFail($workflowId);
            
            // Check authorization
            if (!Auth::user()->isAdmin() && $workflow->broker_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this workflow'
                ], 403);
            }

            $status = $request->get('status');
            $notes = $request->get('notes');

            switch ($status) {
                case 'completed':
                    $workflow->complete($notes);
                    break;
                case 'cancelled':
                    $workflow->cancel($notes);
                    break;
                case 'escalated':
                    $workflow->escalate($notes);
                    break;
                case 'in_progress':
                    $workflow->start();
                    break;
                default:
                    $workflow->update(['status' => $status]);
                    if ($notes) {
                        $workflow->update(['notes' => $notes]);
                    }
            }

            return response()->json([
                'success' => true,
                'message' => 'Workflow status updated successfully',
                'data' => $workflow->fresh()->summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update workflow status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync conversation with transaction
     */
    public function syncConversationWithTransaction(Request $request): JsonResponse
    {
        $request->validate([
            'transaction_id' => 'required|exists:transactions,id'
        ]);

        try {
            $transaction = Transaction::findOrFail($request->transaction_id);
            
            // Check authorization
            if (!Auth::user()->isAdmin() && $transaction->broker_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to sync this transaction'
                ], 403);
            }

            $this->workflowService->syncConversationWithTransaction($transaction);

            return response()->json([
                'success' => true,
                'message' => 'Conversation synced with transaction successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync conversation with transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get workflow details
     */
    public function getWorkflowDetails(int $workflowId): JsonResponse
    {
        try {
            $workflow = CommunicationWorkflow::with(['workflowable', 'broker:id,name,email', 'client:id,name,email'])
                ->findOrFail($workflowId);
            
            // Check authorization
            if (!Auth::user()->isAdmin() && $workflow->broker_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this workflow'
                ], 403);
            }

            $details = array_merge($workflow->summary, [
                'workflowable' => $workflow->workflowable ? [
                    'id' => $workflow->workflowable->id,
                    'type' => class_basename($workflow->workflowable_type),
                    'title' => $workflow->workflowable->title ?? $workflow->workflowable->name ?? 'Unknown'
                ] : null,
                'broker' => $workflow->broker ? $workflow->broker->only(['id', 'name', 'email']) : null,
                'client' => $workflow->client ? $workflow->client->only(['id', 'name', 'email']) : null,
                'workflow_data' => $workflow->workflow_data,
            ]);

            return response()->json([
                'success' => true,
                'data' => $details
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve workflow details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

