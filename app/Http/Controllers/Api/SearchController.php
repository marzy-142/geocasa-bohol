<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Property;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SearchController extends Controller
{
    /**
     * Perform global search across properties, clients, and transactions
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2|max:100',
            ]);

            $query = trim($request->input('query'));
            $user = $request->user();
            $results = [];

            // Search properties
            $properties = $this->searchProperties($query, $user);
            $results = array_merge($results, $properties);

            // Search clients (for brokers and admins)
            if (in_array($user->role, ['broker', 'admin'])) {
                $clients = $this->searchClients($query, $user);
                $results = array_merge($results, $clients);
            }

            // Search transactions (for brokers and admins)
            if (in_array($user->role, ['broker', 'admin'])) {
                $transactions = $this->searchTransactions($query, $user);
                $results = array_merge($results, $transactions);
            }

            // Sort results by relevance (you can implement more sophisticated scoring)
            usort($results, function ($a, $b) {
                return $b['relevance'] <=> $a['relevance'];
            });

            // Limit results to prevent overwhelming the UI
            $results = array_slice($results, 0, 20);

            return response()->json([
                'success' => true,
                'results' => $results,
                'total' => count($results),
                'query' => $query
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid search query',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Search properties based on user role and permissions
     */
    private function searchProperties(string $query, $user): array
    {
        $propertiesQuery = Property::query()
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('address', 'LIKE', "%{$query}%")
                  ->orWhere('city', 'LIKE', "%{$query}%")
                  ->orWhere('property_type', 'LIKE', "%{$query}%");
            });

        // Apply role-based filtering
        if ($user->role === 'broker') {
            $propertiesQuery->where('broker_id', $user->id);
        } elseif ($user->role === 'client') {
            $propertiesQuery->where('status', 'approved');
        }
        // Admin can see all properties

        $properties = $propertiesQuery->limit(10)->get();

        return $properties->map(function ($property) use ($query) {
            $relevance = $this->calculatePropertyRelevance($property, $query);
            
            return [
                'id' => $property->id,
                'type' => 'property',
                'title' => $property->title,
                'subtitle' => $property->address . ', ' . $property->city,
                'relevance' => $relevance,
                'meta' => [
                    'price' => $property->price,
                    'property_type' => $property->property_type,
                    'status' => $property->status,
                    'broker_name' => $property->broker->name ?? 'N/A'
                ]
            ];
        })->toArray();
    }

    /**
     * Search clients (for brokers and admins)
     */
    private function searchClients(string $query, $user): array
    {
        $clientsQuery = User::query()
            ->where('role', 'client')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%");
            });

        // Brokers can only see their assigned clients
        if ($user->role === 'broker') {
            $clientsQuery->where('broker_id', $user->id);
        }
        // Admin can see all clients

        $clients = $clientsQuery->limit(8)->get();

        return $clients->map(function ($client) use ($query) {
            $relevance = $this->calculateClientRelevance($client, $query);
            
            return [
                'id' => $client->id,
                'type' => 'client',
                'title' => $client->name,
                'subtitle' => $client->email . ($client->phone ? ' • ' . $client->phone : ''),
                'relevance' => $relevance,
                'meta' => [
                    'broker_name' => $client->broker->name ?? 'Unassigned',
                    'created_at' => $client->created_at->format('M d, Y')
                ]
            ];
        })->toArray();
    }

    /**
     * Search transactions (for brokers and admins)
     */
    private function searchTransactions(string $query, $user): array
    {
        $transactionsQuery = Transaction::query()
            ->with(['property', 'client', 'broker'])
            ->where(function ($q) use ($query) {
                $q->where('transaction_id', 'LIKE', "%{$query}%")
                  ->orWhereHas('property', function ($pq) use ($query) {
                      $pq->where('title', 'LIKE', "%{$query}%")
                         ->orWhere('address', 'LIKE', "%{$query}%");
                  })
                  ->orWhereHas('client', function ($cq) use ($query) {
                      $cq->where('name', 'LIKE', "%{$query}%");
                  });
            });

        // Brokers can only see their transactions
        if ($user->role === 'broker') {
            $transactionsQuery->where('broker_id', $user->id);
        }
        // Admin can see all transactions

        $transactions = $transactionsQuery->limit(8)->get();

        return $transactions->map(function ($transaction) use ($query) {
            $relevance = $this->calculateTransactionRelevance($transaction, $query);
            
            return [
                'id' => $transaction->id,
                'type' => 'transaction',
                'title' => 'Transaction #' . $transaction->transaction_id,
                'subtitle' => ($transaction->property->title ?? 'Property') . ' • ' . ($transaction->client->name ?? 'Client'),
                'relevance' => $relevance,
                'meta' => [
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'date' => $transaction->created_at->format('M d, Y')
                ]
            ];
        })->toArray();
    }

    /**
     * Calculate property search relevance score
     */
    private function calculatePropertyRelevance($property, string $query): float
    {
        $score = 0;
        $query = strtolower($query);
        
        // Title match (highest weight)
        if (stripos($property->title, $query) !== false) {
            $score += 10;
        }
        
        // Address match
        if (stripos($property->address, $query) !== false) {
            $score += 8;
        }
        
        // City match
        if (stripos($property->city, $query) !== false) {
            $score += 6;
        }
        
        // Property type match
        if (stripos($property->property_type, $query) !== false) {
            $score += 5;
        }
        
        // Description match (lower weight)
        if (stripos($property->description, $query) !== false) {
            $score += 3;
        }
        
        // Boost for featured properties
        if ($property->is_featured) {
            $score += 2;
        }
        
        return $score;
    }

    /**
     * Calculate client search relevance score
     */
    private function calculateClientRelevance($client, string $query): float
    {
        $score = 0;
        $query = strtolower($query);
        
        // Name match (highest weight)
        if (stripos($client->name, $query) !== false) {
            $score += 10;
        }
        
        // Email match
        if (stripos($client->email, $query) !== false) {
            $score += 8;
        }
        
        // Phone match
        if ($client->phone && stripos($client->phone, $query) !== false) {
            $score += 6;
        }
        
        return $score;
    }

    /**
     * Calculate transaction search relevance score
     */
    private function calculateTransactionRelevance($transaction, string $query): float
    {
        $score = 0;
        $query = strtolower($query);
        
        // Transaction ID match (highest weight)
        if (stripos($transaction->transaction_id, $query) !== false) {
            $score += 10;
        }
        
        // Property title match
        if ($transaction->property && stripos($transaction->property->title, $query) !== false) {
            $score += 8;
        }
        
        // Client name match
        if ($transaction->client && stripos($transaction->client->name, $query) !== false) {
            $score += 6;
        }
        
        // Property address match
        if ($transaction->property && stripos($transaction->property->address, $query) !== false) {
            $score += 4;
        }
        
        return $score;
    }
}