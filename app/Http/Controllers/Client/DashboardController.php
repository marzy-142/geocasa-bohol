<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Client;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Display client dashboard
     */
    public function index()
    {
        return $this->dashboard();
    }

    /**
     * Display client dashboard (original method - kept for backup)
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Get or create client record - check both user_id and email
        $client = Client::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();
        
        if (!$client) {
            // Create client record without auto-assigning a broker
            // Brokers should only be assigned when:
            // - Client makes an inquiry about a property
            // - Admin manually assigns a broker
            // - Client selects a broker from the directory
            $client = Client::create([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => null,
                'broker_id' => null, // Don't auto-assign broker
                'user_id' => $user->id
            ]);
        } else {
            // If client exists but doesn't have user_id, link it
            if (!$client->user_id) {
                $client->update(['user_id' => $user->id]);
            }
        }

        // Get client statistics - only relevant metrics
        $activeInquiries = Inquiry::where(function($query) use ($client, $user) {
                $query->where('client_id', $client->id)
                      ->orWhere('user_id', $user->id);
            })
            ->whereIn('status', ['new', 'contacted', 'scheduled'])
            ->count();
            
        $pendingInquiries = Inquiry::where(function($query) use ($client, $user) {
                $query->where('client_id', $client->id)
                      ->orWhere('user_id', $user->id);
            })
            ->where('status', 'new')
            ->count();
            
        $savedPropertiesCount = $client->savedProperties()->count();
        
        // Get seller requests count
        $sellerRequestsCount = \App\Models\SellerRequest::where('client_id', $client->id)->count();
        $pendingSellerRequests = \App\Models\SellerRequest::where('client_id', $client->id)
            ->where('status', 'pending')
            ->count();
        
        $currentStats = [
            'savedProperties' => $savedPropertiesCount,
            'activeInquiries' => $activeInquiries,
            'pendingInquiries' => $pendingInquiries,
            'sellerRequests' => $sellerRequestsCount,
            'pendingSellerRequests' => $pendingSellerRequests,
        ];

        // No trends - removed for cleaner dashboard
        $stats = $currentStats;

        // Get recent inquiries - check both client_id and user_id
        $recentInquiries = Inquiry::with(['property'])
            ->where(function($query) use ($client, $user) {
                $query->where('client_id', $client->id)
                      ->orWhere('user_id', $user->id);
            })
            ->latest()
            ->limit(5)
            ->get();

        // Get recommended properties (featured properties for now)
        $recommendedProperties = Property::with(['broker'])
            ->where('status', 'available')
            ->where('is_featured', true)
            ->latest()
            ->limit(6)
            ->get();

        // Get assigned broker information
        $broker = $client->broker ? $client->broker->only(['id', 'name', 'email', 'phone', 'rating', 'specialization']) : null;

        // Get recent activity from real data
        $recentActivity = [];
        
        // Add recent inquiries
        $recentInquiriesActivity = Inquiry::where('client_id', $client->id)
            ->orWhere('user_id', $user->id)
            ->with('property')
            ->latest()
            ->limit(3)
            ->get()
            ->map(function($inquiry) {
                return [
                    'id' => $inquiry->id,
                    'type' => 'inquiry',
                    'title' => 'Inquiry Status Updated',
                    'description' => "Your inquiry about {$inquiry->property->title} has been {$inquiry->status}",
                    'date' => $inquiry->updated_at->diffForHumans(),
                    'status' => $inquiry->status
                ];
            });
        
        // Add recent saved properties
        $recentSavedActivity = $client->savedProperties()
            ->latest('client_saved_properties.created_at')
            ->limit(2)
            ->get()
            ->map(function($property) {
                return [
                    'id' => 'saved_' . $property->id,
                    'type' => 'property',
                    'title' => 'Property Saved',
                    'description' => "You saved {$property->title} to favorites",
                    'date' => $property->pivot->created_at->diffForHumans(),
                    'status' => 'saved'
                ];
            });
        
        // Add recent meetings
        $recentMeetingsActivity = $client->meetings()
            ->latest()
            ->limit(2)
            ->get()
            ->map(function($meeting) {
                return [
                    'id' => 'meeting_' . $meeting->id,
                    'type' => 'meeting',
                    'title' => 'Meeting Scheduled',
                    'description' => "Meeting: {$meeting->title}",
                    'date' => $meeting->created_at->diffForHumans(),
                    'status' => $meeting->status
                ];
            });
        
        $recentActivity = $recentInquiriesActivity
            ->concat($recentSavedActivity)
            ->concat($recentMeetingsActivity)
            ->sortByDesc('date')
            ->take(5)
            ->values()
            ->toArray();

        // Get recent seller requests
        $recentSellerRequests = \App\Models\SellerRequest::where('client_id', $client->id)
            ->with(['assignedBroker:id,name'])
            ->latest()
            ->limit(3)
            ->get();

        return Inertia::render('Client/Dashboard', [
            'stats' => $stats,
            'recentInquiries' => $recentInquiries,
            'recommendedProperties' => $recommendedProperties,
            'broker' => $broker,
            'recentActivity' => $recentActivity,
            'recentSellerRequests' => $recentSellerRequests,
            'isFirstLogin' => $client->wasRecentlyCreated || $client->created_at->greaterThan(now()->subMinutes(5)),
        ]);
    }
    public function saveProperty(Request $request, Property $property)
    {
        $user = auth()->user();
        $client = Client::where('user_id', $user->id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }
        
        // Toggle save status
        $isSaved = $client->savedProperties()->where('property_id', $property->id)->exists();
        
        if ($isSaved) {
            $client->savedProperties()->detach($property->id);
            $message = 'Property removed from saved list';
            $saved = false;
        } else {
            $client->savedProperties()->attach($property->id);
            $message = 'Property saved successfully';
            $saved = true;
        }
        
        return response()->json([
            'message' => $message,
            'saved' => $saved,
            'savedCount' => $client->savedProperties()->count()
        ]);
    }
    
    public function trackPropertyView(Request $request, Property $property)
    {
        $user = auth()->user();
        $client = Client::where('user_id', $user->id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }
        
        // Track property view
        $client->viewedProperties()->syncWithoutDetaching([$property->id => [
            'viewed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]]);
        
        return response()->json(['message' => 'View tracked successfully']);
    }
    
    public function addFavoriteArea(Request $request)
    {
        $request->validate([
            'area' => 'required|string|max:255',
            'municipality' => 'required|string|max:255'
        ]);
        
        $user = auth()->user();
        $client = Client::where('user_id', $user->id)->first();
        
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }
        
        // Add favorite area
        $favoriteAreas = $client->favorite_areas ?: [];
        $newArea = $request->area . ', ' . $request->municipality;
        
        if (!in_array($newArea, $favoriteAreas)) {
            $favoriteAreas[] = $newArea;
            $client->update(['favorite_areas' => $favoriteAreas]);
        }
        
        return response()->json([
            'message' => 'Favorite area added successfully',
            'favoriteAreas' => $favoriteAreas
        ]);
    }
}
