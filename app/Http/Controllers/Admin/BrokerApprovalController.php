<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\BrokerApprovalNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class BrokerApprovalController extends Controller
{
    /**
     * Display pending broker approvals
     */
    public function index()
    {
        $pendingBrokers = User::pendingBrokers()
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admin/BrokerApprovals', [
            'pendingBrokers' => $pendingBrokers
        ]);
    }

    /**
     * Show broker application details
     */
    public function show(User $user)
    {
        // Simple admin check instead of policy for now
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        
        return Inertia::render('Admin/BrokerApplication', [
            'broker' => $user->load('approvedBy'),
            'credentials' => [
                'prc_id_file' => $user->prc_id_file ? Storage::url($user->prc_id_file) : null,
                'business_permit_file' => $user->business_permit_file ? Storage::url($user->business_permit_file) : null,
                'additional_documents' => $user->additional_documents ? 
                    collect($user->additional_documents)->map(fn($doc) => Storage::url($doc)) : []
            ]
        ]);
    }
    
    public function approve(Request $request, User $user)
    {
        $user->update([
            'is_approved' => true,
            'application_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
    
        // Send approval notification
        $user->notify(new BrokerApprovalNotification(true));
    
        return back()->with('success', 'Broker application approved successfully.');
    }
    
    public function reject(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);
    
        $user->update([
            'is_approved' => false,
            'application_status' => 'rejected',
            'rejection_reason' => $request->reason,
            'reviewed_at' => now(),
        ]);
    
        // Send rejection notification
        $user->notify(new BrokerApprovalNotification(false, $request->reason));
    
        return back()->with('success', 'Broker application rejected.');
    }
}
