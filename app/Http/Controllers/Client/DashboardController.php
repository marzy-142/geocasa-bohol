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
        $user = auth()->user();
        
        // Get or create client record
        $client = Client::where('email', $user->email)->first();
        
        if (!$client) {
            // Find a default broker or the first available broker
            $defaultBroker = User::where('role', 'broker')
                ->where('is_approved', true)
                ->first();
            
            if (!$defaultBroker) {
                // If no approved brokers exist, create a placeholder or handle this case
                // For now, we'll skip creating the client record and show limited dashboard
                return Inertia::render('Client/Dashboard', [
                    'stats' => [
                        'savedProperties' => 0,
                        'activeInquiries' => 0,
                        'viewedProperties' => 0,
                        'favoriteAreas' => 0,
                    ],
                    'recentInquiries' => [],
                    'recommendedProperties' => Property::with(['broker'])
                        ->where('status', 'available')
                        ->where('is_featured', true)
                        ->latest()
                        ->limit(6)
                        ->get(),
                    'noBrokerAvailable' => true,
                ]);
            }
            
            $client = Client::create([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => null,
                'broker_id' => $defaultBroker->id
            ]);
        }

        // Get client statistics
        $stats = [
            'savedProperties' => 0, // TODO: Implement saved properties feature
            'activeInquiries' => Inquiry::where('client_id', $client->id)
                ->where('status', 'pending')
                ->count(),
            'viewedProperties' => 0, // TODO: Implement property view tracking
            'favoriteAreas' => 0, // TODO: Implement favorite areas feature
        ];

        // Get recent inquiries
        $recentInquiries = Inquiry::with(['property'])
            ->where('client_id', $client->id)
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

        return Inertia::render('Client/Dashboard', [
            'stats' => $stats,
            'recentInquiries' => $recentInquiries,
            'recommendedProperties' => $recommendedProperties,
        ]);
    }
}