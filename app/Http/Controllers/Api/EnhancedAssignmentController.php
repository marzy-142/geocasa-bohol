<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UnifiedBrokerAssignmentService;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EnhancedAssignmentController extends Controller
{
    protected UnifiedBrokerAssignmentService $assignmentService;

    public function __construct(UnifiedBrokerAssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Assign broker to client with enhanced tracking
     */
    public function assignBrokerToClient(Request $request, Client $client): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'broker_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $broker = User::find($value);
                        if (!$broker || !$broker->isBroker() || $broker->status !== 'approved') {
                            $fail('The selected broker is not valid or not approved.');
                        }
                    }
                }
            ],
            'context' => 'nullable|string|in:inquiry,transaction,escalated,manual',
            'relationship_type' => 'nullable|string|in:primary,secondary,inquiry_specific',
            'assignment_reason' => 'nullable|string|max:255',
            'options' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $context = $request->get('context', 'manual');
            $options = array_merge(
                $request->get('options', []),
                [
                    'broker_id' => $request->get('broker_id'),
                    'relationship_type' => $request->get('relationship_type'),
                    'reason' => $request->get('assignment_reason'),
                ]
            );

            $newBroker = $this->assignmentService->assignBrokerToClient($client, $context, $options);

            if (!$newBroker) {
                return response()->json([
                    'success' => false,
                    'message' => 'No suitable broker found for assignment'
                ], 400);
            }

            $client->load('broker:id,name,email');

            return response()->json([
                'success' => true,
                'message' => "Client successfully assigned to {$newBroker->name}",
                'data' => [
                    'client' => $client,
                    'broker' => $newBroker->only(['id', 'name', 'email']),
                    'assignment_context' => $context,
                    'relationship_type' => $client->relationship_type,
                    'assignment_reason' => $client->assignment_reason,
                    'assigned_at' => $client->relationship_established_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign broker to client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign broker to inquiry with enhanced tracking
     */
    public function assignBrokerToInquiry(Request $request, Inquiry $inquiry): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'context' => 'nullable|string|in:property_broker,auto_assigned,manual_assigned,escalated',
            'broker_id' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $broker = User::find($value);
                        if (!$broker || !$broker->isBroker() || $broker->status !== 'approved') {
                            $fail('The selected broker is not valid or not approved.');
                        }
                    }
                }
            ],
            'options' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $context = $request->get('context', 'auto_assigned');
            $options = array_merge(
                $request->get('options', []),
                [
                    'broker_id' => $request->get('broker_id'),
                ]
            );

            $newBroker = $this->assignmentService->assignBrokerToInquiry($inquiry, $context, $options);

            if (!$newBroker) {
                return response()->json([
                    'success' => false,
                    'message' => 'No suitable broker found for assignment'
                ], 400);
            }

            $inquiry->load(['broker:id,name,email', 'client:id,name']);

            return response()->json([
                'success' => true,
                'message' => "Inquiry successfully assigned to {$newBroker->name}",
                'data' => [
                    'inquiry' => $inquiry,
                    'broker' => $newBroker->only(['id', 'name', 'email']),
                    'assignment_context' => $inquiry->assignment_context,
                    'assignment_timestamp' => $inquiry->assignment_timestamp,
                    'assignment_metadata' => $inquiry->assignment_metadata,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign broker to inquiry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reassign broker with enhanced tracking
     */
    public function reassignBroker(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|string|in:client,inquiry',
            'entity_id' => 'required|integer',
            'broker_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:255',
            'options' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $entityType = $request->get('entity_type');
            $entityId = $request->get('entity_id');
            $brokerId = $request->get('broker_id');
            $reason = $request->get('reason');
            $options = $request->get('options', []);

            // Validate broker
            $broker = User::find($brokerId);
            if (!$broker || !$broker->isBroker() || $broker->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or unapproved broker selected'
                ], 400);
            }

            // Get entity
            $entity = $this->getEntity($entityType, $entityId);
            if (!$entity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entity not found'
                ], 404);
            }

            // Check authorization
            if (!$this->canReassignEntity(Auth::user(), $entity)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to reassign this entity'
                ], 403);
            }

            $success = $this->assignmentService->reassignBroker($entity, $broker, $reason, $options);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reassign broker'
                ], 500);
            }

            $message = match($entityType) {
                'client' => "Client successfully reassigned to {$broker->name}",
                'inquiry' => "Inquiry successfully reassigned to {$broker->name}",
                default => "Entity successfully reassigned to {$broker->name}"
            };

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'broker' => $broker->only(['id', 'name', 'email']),
                    'reason' => $reason,
                    'reassigned_at' => now(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reassign broker',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get broker assignment recommendations
     */
    public function getAssignmentRecommendations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|string|in:client,inquiry',
            'entity_id' => 'required|integer',
            'context' => 'nullable|string',
            'options' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $entityType = $request->get('entity_type');
            $entityId = $request->get('entity_id');
            $context = $request->get('context', 'general');
            $options = $request->get('options', []);

            // Get entity
            $entity = $this->getEntity($entityType, $entityId);
            if (!$entity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entity not found'
                ], 404);
            }

            $recommendations = $this->assignmentService->getBrokerRecommendations($entity, array_merge($options, [
                'context' => $context,
                'user_id' => Auth::id()
            ]));

            return response()->json([
                'success' => true,
                'data' => [
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'recommendations' => $recommendations,
                    'generated_at' => now(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get assignment recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign clients to broker
     */
    public function bulkAssignClients(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_ids' => 'required|array|min:1',
            'client_ids.*' => 'exists:clients,id',
            'broker_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $broker = User::find($value);
                    if (!$broker || !$broker->isBroker() || $broker->status !== 'approved') {
                        $fail('The selected broker is not valid or not approved.');
                    }
                }
            ],
            'context' => 'nullable|string|in:inquiry,transaction,manual',
            'assignment_reason' => 'nullable|string|max:255',
            'relationship_type' => 'nullable|string|in:primary,secondary,inquiry_specific',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $clientIds = $request->get('client_ids');
            $brokerId = $request->get('broker_id');
            $context = $request->get('context', 'manual');
            $reason = $request->get('assignment_reason', 'Bulk assignment');
            $relationshipType = $request->get('relationship_type', 'primary');

            $broker = User::find($brokerId);
            $clients = Client::whereIn('id', $clientIds)->get();

            $results = [
                'assigned' => 0,
                'reassigned' => 0,
                'failed' => 0,
                'errors' => []
            ];

            foreach ($clients as $client) {
                try {
                    $previousBroker = $client->broker;
                    
                    $options = [
                        'broker_id' => $brokerId,
                        'relationship_type' => $relationshipType,
                        'reason' => $reason,
                    ];

                    $assignedBroker = $this->assignmentService->assignBrokerToClient($client, $context, $options);

                    if ($assignedBroker) {
                        if ($previousBroker) {
                            $results['reassigned']++;
                        } else {
                            $results['assigned']++;
                        }
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "Failed to assign client {$client->name}";
                    }

                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Error assigning client {$client->name}: " . $e->getMessage();
                }
            }

            $message = "Bulk assignment completed: {$results['assigned']} assigned, {$results['reassigned']} reassigned";
            if ($results['failed'] > 0) {
                $message .= ", {$results['failed']} failed";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'broker' => $broker->only(['id', 'name', 'email']),
                    'total_clients' => count($clientIds),
                    'results' => $results,
                    'completed_at' => now(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get assignment history for entity
     */
    public function getAssignmentHistory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'entity_type' => 'required|string|in:client,broker',
            'entity_id' => 'required|integer',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $entityType = $request->get('entity_type');
            $entityId = $request->get('entity_id');
            $limit = $request->get('limit', 20);

            if ($entityType === 'client') {
                $history = \App\Models\BrokerClientRelationship::where('client_id', $entityId)
                    ->with(['broker:id,name,email', 'assignedBy:id,name'])
                    ->latest()
                    ->limit($limit)
                    ->get()
                    ->map(function ($relationship) {
                        return [
                            'id' => $relationship->id,
                            'broker_name' => $relationship->broker->name,
                            'broker_email' => $relationship->broker->email,
                            'relationship_type' => $relationship->relationship_type_label,
                            'assignment_method' => $relationship->assignment_method_label,
                            'assignment_reason' => $relationship->assignment_reason,
                            'assigned_by' => $relationship->assignedBy->name ?? 'System',
                            'assigned_at' => $relationship->assigned_at->format('M j, Y H:i'),
                            'ended_at' => $relationship->ended_at?->format('M j, Y H:i'),
                            'duration' => $relationship->duration,
                            'is_active' => $relationship->is_active,
                        ];
                    });
            } else {
                $history = \App\Models\BrokerClientRelationship::where('broker_id', $entityId)
                    ->with(['client:id,name,email', 'assignedBy:id,name'])
                    ->latest()
                    ->limit($limit)
                    ->get()
                    ->map(function ($relationship) {
                        return [
                            'id' => $relationship->id,
                            'client_name' => $relationship->client->name,
                            'client_email' => $relationship->client->email,
                            'relationship_type' => $relationship->relationship_type_label,
                            'assignment_method' => $relationship->assignment_method_label,
                            'assignment_reason' => $relationship->assignment_reason,
                            'assigned_by' => $relationship->assignedBy->name ?? 'System',
                            'assigned_at' => $relationship->assigned_at->format('M j, Y H:i'),
                            'ended_at' => $relationship->ended_at?->format('M j, Y H:i'),
                            'duration' => $relationship->duration,
                            'is_active' => $relationship->is_active,
                        ];
                    });
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'history' => $history,
                    'total_count' => $history->count(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve assignment history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get entity by type and ID
     */
    protected function getEntity(string $type, int $id)
    {
        return match($type) {
            'client' => Client::find($id),
            'inquiry' => Inquiry::find($id),
            default => null
        };
    }

    /**
     * Check if user can reassign entity
     */
    protected function canReassignEntity(User $user, $entity): bool
    {
        // Admin can reassign anything
        if ($user->isAdmin()) {
            return true;
        }

        // Broker can reassign entities assigned to them
        if ($user->isBroker()) {
            if ($entity instanceof Client) {
                return $entity->broker_id === $user->id;
            } elseif ($entity instanceof Inquiry) {
                return $entity->assigned_broker_id === $user->id;
            }
        }

        return false;
    }
}

