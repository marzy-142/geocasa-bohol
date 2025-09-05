<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Display a listing of clients with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Client::with(['broker:id,name,email', 'inquiries', 'transactions'])
            ->select('id', 'name', 'email', 'phone', 'budget_min', 'budget_max', 'preferred_area', 'status', 'source', 'broker_id', 'created_at', 'updated_at');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('broker_id')) {
            if ($request->broker_id === 'unassigned') {
                $query->whereNull('broker_id');
            } else {
                $query->where('broker_id', $request->broker_id);
            }
        }

        if ($request->filled('assignment_status')) {
            if ($request->assignment_status === 'assigned') {
                $query->whereNotNull('broker_id');
            } elseif ($request->assignment_status === 'unassigned') {
                $query->whereNull('broker_id');
            }
        }

        // Budget range filter
        if ($request->filled('budget_min')) {
            $query->where('budget_max', '>=', $request->budget_min);
        }

        if ($request->filled('budget_max')) {
            $query->where('budget_min', '<=', $request->budget_max);
        }

        // Date range filter
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $clients = $query->paginate($request->get('per_page', 15));

        // Add computed fields
        $clients->getCollection()->transform(function ($client) {
            $client->formatted_budget = $client->formatted_budget;
            $client->status_color = $client->status_color;
            $client->source_label = $client->source_label;
            $client->total_inquiries = $client->total_inquiries;
            $client->active_inquiries = $client->active_inquiries;
            $client->total_transactions = $client->total_transactions;
            $client->completed_transactions = $client->completed_transactions;
            $client->last_activity = $client->last_activity;
            return $client;
        });

        return response()->json([
            'success' => true,
            'data' => $clients,
            'message' => 'Clients retrieved successfully'
        ]);
    }

    /**
     * Display the specified client
     */
    public function show(Client $client): JsonResponse
    {
        $client->load([
            'broker:id,name,email,phone',
            'inquiries' => function ($query) {
                $query->with('property:id,title,location')
                      ->orderBy('created_at', 'desc')
                      ->limit(10);
            },
            'transactions' => function ($query) {
                $query->with('property:id,title,location')
                      ->orderBy('created_at', 'desc')
                      ->limit(10);
            }
        ]);

        // Add computed fields
        $client->formatted_budget = $client->formatted_budget;
        $client->status_color = $client->status_color;
        $client->source_label = $client->source_label;
        $client->total_inquiries = $client->total_inquiries;
        $client->active_inquiries = $client->active_inquiries;
        $client->total_transactions = $client->total_transactions;
        $client->completed_transactions = $client->completed_transactions;
        $client->last_activity = $client->last_activity;

        return response()->json([
            'success' => true,
            'data' => $client,
            'message' => 'Client retrieved successfully'
        ]);
    }

    /**
     * Assign a broker to a client
     */
    public function assignBroker(Request $request, Client $client): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'broker_id' => [
                'required',
                'exists:users,id',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'broker')
                          ->where('status', 'approved');
                })
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $previousBroker = $client->broker;
            $newBroker = User::find($request->broker_id);

            $client->update([
                'broker_id' => $request->broker_id,
                'assigned_at' => now()
            ]);

            // Log the assignment change
            activity()
                ->performedOn($client)
                ->causedBy(auth()->user())
                ->withProperties([
                    'previous_broker' => $previousBroker ? $previousBroker->name : null,
                    'new_broker' => $newBroker->name,
                    'assigned_by' => auth()->user()->name
                ])
                ->log($previousBroker ? 'Client reassigned to new broker' : 'Client assigned to broker');

            DB::commit();

            $client->load('broker:id,name,email');

            return response()->json([
                'success' => true,
                'data' => $client,
                'message' => $previousBroker 
                    ? "Client successfully reassigned from {$previousBroker->name} to {$newBroker->name}"
                    : "Client successfully assigned to {$newBroker->name}"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign broker to client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign broker to multiple clients
     */
    public function bulkAssignBroker(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_ids' => 'required|array|min:1',
            'client_ids.*' => 'exists:clients,id',
            'broker_id' => [
                'required',
                'exists:users,id',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'broker')
                          ->where('status', 'approved');
                })
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $broker = User::find($request->broker_id);
            $clients = Client::whereIn('id', $request->client_ids)->get();
            
            $assignedCount = 0;
            $reassignedCount = 0;
            
            foreach ($clients as $client) {
                $hadBroker = !is_null($client->broker_id);
                
                $client->update([
                    'broker_id' => $request->broker_id,
                    'assigned_at' => now()
                ]);
                
                if ($hadBroker) {
                    $reassignedCount++;
                } else {
                    $assignedCount++;
                }
                
                // Log the assignment
                activity()
                    ->performedOn($client)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'broker' => $broker->name,
                        'assigned_by' => auth()->user()->name,
                        'bulk_assignment' => true
                    ])
                    ->log($hadBroker ? 'Client reassigned via bulk assignment' : 'Client assigned via bulk assignment');
            }

            DB::commit();

            $message = "Successfully processed {$clients->count()} clients: ";
            if ($assignedCount > 0) {
                $message .= "{$assignedCount} assigned";
            }
            if ($reassignedCount > 0) {
                $message .= ($assignedCount > 0 ? ", " : "") . "{$reassignedCount} reassigned";
            }
            $message .= " to {$broker->name}";

            return response()->json([
                'success' => true,
                'data' => [
                    'assigned_count' => $assignedCount,
                    'reassigned_count' => $reassignedCount,
                    'total_processed' => $clients->count(),
                    'broker' => $broker->only(['id', 'name', 'email'])
                ],
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk assign broker to clients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unassigned clients
     */
    public function getUnassigned(Request $request): JsonResponse
    {
        $query = Client::whereNull('broker_id')
            ->with(['inquiries', 'transactions'])
            ->select('id', 'name', 'email', 'phone', 'budget_min', 'budget_max', 'preferred_area', 'status', 'source', 'created_at');

        // Apply additional filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Priority sorting - most recent first, then by inquiry count
        $query->withCount(['inquiries', 'transactions'])
              ->orderBy('inquiries_count', 'desc')
              ->orderBy('created_at', 'desc');

        $clients = $query->paginate($request->get('per_page', 15));

        // Add computed fields
        $clients->getCollection()->transform(function ($client) {
            $client->formatted_budget = $client->formatted_budget;
            $client->status_color = $client->status_color;
            $client->source_label = $client->source_label;
            $client->priority_score = $client->inquiries_count * 2 + $client->transactions_count * 3;
            return $client;
        });

        return response()->json([
            'success' => true,
            'data' => $clients,
            'message' => 'Unassigned clients retrieved successfully'
        ]);
    }

    /**
     * Get broker analytics for assignment decisions
     */
    public function getBrokerAnalytics(Request $request): JsonResponse
    {
        $brokers = User::where('role', 'broker')
            ->where('status', 'approved')
            ->withCount([
                'assignedClients',
                'assignedClients as active_clients_count' => function ($query) {
                    $query->where('status', 'active');
                },
                'assignedClients as recent_assignments_count' => function ($query) {
                    $query->where('assigned_at', '>=', now()->subDays(30));
                }
            ])
            ->with([
                'assignedClients' => function ($query) {
                    $query->select('id', 'broker_id', 'status', 'assigned_at')
                          ->orderBy('assigned_at', 'desc')
                          ->limit(5);
                }
            ])
            ->get()
            ->map(function ($broker) {
                // Calculate performance metrics
                $totalClients = $broker->assigned_clients_count;
                $activeClients = $broker->active_clients_count;
                $recentAssignments = $broker->recent_assignments_count;
                
                // Calculate workload score (lower is better for new assignments)
                $workloadScore = $totalClients * 1.0 + ($totalClients - $activeClients) * 0.5;
                
                // Calculate availability score (higher is better)
                $maxRecommendedClients = 20; // Configurable
                $availabilityScore = max(0, ($maxRecommendedClients - $totalClients) / $maxRecommendedClients * 100);
                
                return [
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'email' => $broker->email,
                    'total_clients' => $totalClients,
                    'active_clients' => $activeClients,
                    'recent_assignments' => $recentAssignments,
                    'workload_score' => round($workloadScore, 2),
                    'availability_score' => round($availabilityScore, 2),
                    'recommended_for_assignment' => $availabilityScore > 20,
                    'recent_clients' => $broker->assignedClients->map(function ($client) {
                        return [
                            'id' => $client->id,
                            'status' => $client->status,
                            'assigned_at' => $client->assigned_at
                        ];
                    })
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $brokers,
            'message' => 'Broker analytics retrieved successfully'
        ]);
    }

    /**
     * Get assignment recommendations based on broker workload and client preferences
     */
    public function getAssignmentRecommendations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'sometimes|exists:clients,id',
            'client_ids' => 'sometimes|array',
            'client_ids.*' => 'exists:clients,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get client(s) for recommendation
        if ($request->filled('client_id')) {
            $clients = collect([Client::find($request->client_id)]);
        } elseif ($request->filled('client_ids')) {
            $clients = Client::whereIn('id', $request->client_ids)->get();
        } else {
            // Get all unassigned clients
            $clients = Client::whereNull('broker_id')->limit(10)->get();
        }

        // Get available brokers with their metrics
        $brokers = User::where('role', 'broker')
            ->where('status', 'approved')
            ->withCount([
                'assignedClients',
                'assignedClients as active_clients_count' => function ($query) {
                    $query->where('status', 'active');
                }
            ])
            ->get();

        $recommendations = $clients->map(function ($client) use ($brokers) {
            $brokerScores = $brokers->map(function ($broker) use ($client) {
                $score = 100; // Base score
                
                // Workload penalty (more clients = lower score)
                $workloadPenalty = $broker->assigned_clients_count * 5;
                $score -= $workloadPenalty;
                
                // Active clients penalty
                $activePenalty = $broker->active_clients_count * 3;
                $score -= $activePenalty;
                
                // Bonus for brokers with fewer than 15 clients
                if ($broker->assigned_clients_count < 15) {
                    $score += 20;
                }
                
                // Bonus for brokers with fewer than 10 clients
                if ($broker->assigned_clients_count < 10) {
                    $score += 30;
                }
                
                // Ensure score doesn't go below 0
                $score = max(0, $score);
                
                return [
                    'broker_id' => $broker->id,
                    'broker_name' => $broker->name,
                    'broker_email' => $broker->email,
                    'current_clients' => $broker->assigned_clients_count,
                    'active_clients' => $broker->active_clients_count,
                    'recommendation_score' => round($score, 2),
                    'recommendation_reason' => $this->getRecommendationReason($broker, $score)
                ];
            })->sortByDesc('recommendation_score')->values();
            
            return [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'client_budget' => $client->formatted_budget,
                'client_area' => $client->preferred_area,
                'recommended_brokers' => $brokerScores->take(3) // Top 3 recommendations
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $recommendations,
            'message' => 'Assignment recommendations generated successfully'
        ]);
    }

    /**
     * Get clients assigned to the authenticated broker
     */
    public function brokerClients(Request $request): JsonResponse
    {
        $broker = auth()->user();
        
        if ($broker->role !== 'broker') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only brokers can access this endpoint.'
            ], 403);
        }

        $query = Client::where('broker_id', $broker->id)
            ->with(['inquiries', 'transactions'])
            ->select('id', 'name', 'email', 'phone', 'budget_min', 'budget_max', 'preferred_area', 'status', 'source', 'broker_id', 'assigned_at', 'created_at', 'updated_at');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Budget range filter
        if ($request->filled('budget_min')) {
            $query->where('budget_max', '>=', $request->budget_min);
        }

        if ($request->filled('budget_max')) {
            $query->where('budget_min', '<=', $request->budget_max);
        }

        // Date range filter
        if ($request->filled('assigned_from')) {
            $query->whereDate('assigned_at', '>=', $request->assigned_from);
        }

        if ($request->filled('assigned_to')) {
            $query->whereDate('assigned_at', '<=', $request->assigned_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'assigned_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $clients = $query->paginate($request->get('per_page', 15));

        // Add computed fields
        $clients->getCollection()->transform(function ($client) {
            $client->formatted_budget = $client->formatted_budget;
            $client->status_color = $client->status_color;
            $client->source_label = $client->source_label;
            $client->total_inquiries = $client->total_inquiries;
            $client->active_inquiries = $client->active_inquiries;
            $client->total_transactions = $client->total_transactions;
            $client->completed_transactions = $client->completed_transactions;
            $client->last_activity = $client->last_activity;
            $client->days_since_assignment = $client->assigned_at ? $client->assigned_at->diffInDays(now()) : null;
            return $client;
        });

        return response()->json([
            'success' => true,
            'data' => $clients,
            'message' => 'Assigned clients retrieved successfully'
        ]);
    }

    /**
     * Show a specific client assigned to the authenticated broker
     */
    public function brokerShow(Client $client): JsonResponse
    {
        $broker = auth()->user();
        
        if ($broker->role !== 'broker') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only brokers can access this endpoint.'
            ], 403);
        }

        if ($client->broker_id !== $broker->id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. This client is not assigned to you.'
            ], 403);
        }

        $client->load([
            'broker:id,name,email,phone',
            'inquiries' => function ($query) {
                $query->with('property:id,title,location,price')
                      ->orderBy('created_at', 'desc');
            },
            'transactions' => function ($query) {
                $query->with('property:id,title,location,price')
                      ->orderBy('created_at', 'desc');
            }
        ]);

        // Add computed fields
        $client->formatted_budget = $client->formatted_budget;
        $client->status_color = $client->status_color;
        $client->source_label = $client->source_label;
        $client->total_inquiries = $client->total_inquiries;
        $client->active_inquiries = $client->active_inquiries;
        $client->total_transactions = $client->total_transactions;
        $client->completed_transactions = $client->completed_transactions;
        $client->last_activity = $client->last_activity;
        $client->days_since_assignment = $client->assigned_at ? $client->assigned_at->diffInDays(now()) : null;

        return response()->json([
            'success' => true,
            'data' => $client,
            'message' => 'Client details retrieved successfully'
        ]);
    }

    /**
     * Generate recommendation reason based on broker metrics
     */
    private function getRecommendationReason($broker, $score): string
    {
        if ($score >= 80) {
            return "Excellent choice - low workload ({$broker->assigned_clients_count} clients)";
        } elseif ($score >= 60) {
            return "Good choice - moderate workload ({$broker->assigned_clients_count} clients)";
        } elseif ($score >= 40) {
            return "Acceptable - higher workload ({$broker->assigned_clients_count} clients)";
        } else {
            return "High workload - consider other options ({$broker->assigned_clients_count} clients)";
        }
    }
}