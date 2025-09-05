<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\BrokerApprovalNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BrokerApprovalController extends Controller
{
    /**
     * Display pending broker approvals
     */
    public function index()
    {
        $pendingBrokers = User::pendingApplications()
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
            ],
            'verification_status' => [
                'prc_verified' => $user->prc_verified ?? false,
                'business_permit_verified' => $user->business_permit_verified ?? false,
                'prc_verification_notes' => $user->prc_verification_notes,
                'business_permit_verification_notes' => $user->business_permit_verification_notes,
            ]
        ]);
    }
    
    /**
     * Update verification status for credentials
     */
    public function updateVerification(Request $request, User $user)
    {
        $request->validate([
            'credential_type' => ['required', Rule::in(['prc_id', 'business_permit'])],
            'verified' => 'required|boolean',
            'notes' => 'nullable|string|max:500'
        ]);
    
        $credentialType = $request->credential_type;
        
        // Fix field name mapping
        if ($credentialType === 'prc_id') {
            $verifiedField = 'prc_verified';
            $notesField = 'prc_verification_notes';
        } else {
            $verifiedField = 'business_permit_verified';
            $notesField = 'business_permit_verification_notes';
        }
    
        $user->update([
            $verifiedField => $request->verified,
            $notesField => $request->notes,
            'reviewed_at' => now(),
        ]);
    
        return back()->with('success', ucfirst(str_replace('_', ' ', $credentialType)) . ' verification updated successfully.');
    }

    public function approve(Request $request, User $user)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        // Check if PRC credentials are verified (removed business permit requirement)
        if (!$user->prc_verified) {
            return back()->withErrors([
                'verification' => 'PRC ID must be verified before approval.'
            ]);
        }

        $user->update([
            'is_approved' => true,
            'application_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);
    
        // Send approval notification
        $user->notify(new BrokerApprovalNotification(true));
    
        return back()->with('success', 'Broker application approved successfully.');
    }
    
    public function reject(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000'
        ]);
    
        $user->update([
            'is_approved' => false,
            'application_status' => 'rejected',
            'rejection_reason' => $request->reason,
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);
    
        // Send rejection notification
        $user->notify(new BrokerApprovalNotification(false, $request->reason));
    
        return back()->with('success', 'Broker application rejected.');
    }
}
