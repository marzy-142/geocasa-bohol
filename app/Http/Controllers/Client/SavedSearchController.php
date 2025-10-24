<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SavedSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedSearchController extends Controller
{
    /**
     * Display saved searches
     */
    public function index()
    {
        $searches = Auth::user()->savedSearches()
            ->latest()
            ->get();

        return inertia('Client/SavedSearches/Index', [
            'searches' => $searches,
        ]);
    }

    /**
     * Store a new saved search
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'filters' => 'required|array',
            'notify_on_new' => 'boolean',
        ]);

        $search = Auth::user()->savedSearches()->create($validated);

        return back()->with('success', 'Search saved successfully! You will receive email notifications when new matching properties are listed.');
    }

    /**
     * Delete a saved search
     */
    public function destroy(SavedSearch $search)
    {
        // Ensure user owns this search
        if ($search->user_id !== Auth::id()) {
            abort(403);
        }

        $search->delete();

        return back()->with('success', 'Saved search deleted successfully.');
    }

    /**
     * Toggle notifications for a saved search
     */
    public function toggleNotifications(SavedSearch $search)
    {
        // Ensure user owns this search
        if ($search->user_id !== Auth::id()) {
            abort(403);
        }

        $search->update([
            'notify_on_new' => !$search->notify_on_new,
        ]);

        $message = $search->notify_on_new 
            ? 'Email notifications enabled for this search.'
            : 'Email notifications disabled for this search.';

        return back()->with('success', $message);
    }
}
