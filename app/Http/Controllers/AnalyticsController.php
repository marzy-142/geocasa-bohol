<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function trackVirtualTourView(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'session_id' => 'required|string',
            'duration' => 'nullable|integer', // seconds
            'hotspots_clicked' => 'nullable|array'
        ]);
        
        DB::table('virtual_tour_analytics')->insert([
            'property_id' => $validated['property_id'],
            'session_id' => $validated['session_id'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'duration' => $validated['duration'] ?? 0,
            'hotspots_clicked' => json_encode($validated['hotspots_clicked'] ?? []),
            'created_at' => now()
        ]);
        
        return response()->json(['status' => 'tracked']);
    }
    
    public function getVirtualTourStats(Property $property)
    {
        $stats = DB::table('virtual_tour_analytics')
            ->where('property_id', $property->id)
            ->selectRaw('
                COUNT(*) as total_views,
                AVG(duration) as avg_duration,
                COUNT(DISTINCT session_id) as unique_viewers
            ')
            ->first();
            
        return response()->json($stats);
    }
}