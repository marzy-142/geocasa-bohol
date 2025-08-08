<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Property;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TransactionStatusNotification;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Transaction::with(['property', 'client', 'broker', 'inquiry']);
        
        // Role-based filtering
        if ($user->role === 'broker') {
            $query->where('broker_id', $user->id);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhereHas('property', function ($q) use ($search) {
                      $q->where('title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                  })
                  ->orWhereHas('client', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('inquiry_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('inquiry_date', '<=', $request->date_to);
        }
        
        // Property filter
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }
        
        $transactions = $query->orderBy('created_at', 'desc')->paginate(12);
        
        // Get filter options
        $properties = Property::select('id', 'title')->get();
        $statuses = [
            'inquiry' => 'Inquiry',
            'initial_contact' => 'Initial Contact',
            'property_viewing' => 'Property Viewing',
            'offer_made' => 'Offer Made',
            'negotiation' => 'Negotiation',
            'offer_accepted' => 'Offer Accepted',
            'contract_signed' => 'Contract Signed',
            'due_diligence' => 'Due Diligence',
            'financing' => 'Financing',
            'closing_preparation' => 'Closing Preparation',
            'finalized' => 'Finalized',
            'cancelled' => 'Cancelled'
        ];
        
        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'property_id']),
            'properties' => $properties,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Show the form for creating a new transaction
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get available data for form
        $properties = $user->role === 'admin' 
            ? Property::select('id', 'title', 'location', 'price')->get()
            : Property::where('broker_id', $user->id)->select('id', 'title', 'location', 'price')->get();
            
        $clients = $user->role === 'admin'
            ? Client::select('id', 'name', 'email', 'phone')->get()
            : Client::where('broker_id', $user->id)->select('id', 'name', 'email', 'phone')->get();
            
        $inquiries = $user->role === 'admin'
            ? Inquiry::with(['property:id,title', 'client:id,name'])
                ->select('id', 'property_id', 'client_id', 'inquiry_type')
                ->whereIn('status', ['new', 'contacted', 'scheduled'])
                ->get()
            : Inquiry::with(['property:id,title', 'client:id,name'])
                ->select('id', 'property_id', 'client_id', 'inquiry_type')
                ->where('broker_id', $user->id)
                ->whereIn('status', ['new', 'contacted', 'scheduled'])
                ->get();
        
        return Inertia::render('Transactions/Create', [
            'properties' => $properties,
            'clients' => $clients,
            'inquiries' => $inquiries,
        ]);
    }

    /**
     * Store a newly created transaction
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'client_id' => 'required|exists:clients,id',
            'inquiry_id' => 'nullable|exists:inquiries,id',
            'offered_price' => 'required|numeric|min:0',
            'commission_rate' => 'required|numeric|min:0|max:1',
            'inquiry_date' => 'required|date',
            'broker_notes' => 'nullable|string',
        ]);
        
        // Calculate commission amount
        $validated['commission_amount'] = $validated['offered_price'] * $validated['commission_rate'];
        $validated['broker_id'] = $user->id;
        $validated['status'] = 'inquiry';
        
        $transaction = Transaction::create($validated);
        
        // Update related inquiry status if provided
        if ($validated['inquiry_id']) {
            Inquiry::find($validated['inquiry_id'])->update(['status' => 'completed']);
        }
        
        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaction created successfully.');
    }

    /**
     * Display the specified transaction
     */
    public function show(Transaction $transaction)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role === 'broker' && $transaction->broker_id !== $user->id) {
            abort(403, 'Unauthorized access to transaction.');
        }
        
        $transaction->load(['property', 'client', 'broker', 'inquiry']);
        
        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Show the form for editing the specified transaction
     */
    public function edit(Transaction $transaction)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role === 'broker' && $transaction->broker_id !== $user->id) {
            abort(403, 'Unauthorized access to transaction.');
        }
        
        $transaction->load(['property', 'client', 'broker', 'inquiry']);
        
        // Get available data for form
        $properties = $user->role === 'admin' 
            ? Property::select('id', 'title', 'location', 'price')->get()
            : Property::where('broker_id', $user->id)->select('id', 'title', 'location', 'price')->get();
            
        $clients = $user->role === 'admin'
            ? Client::select('id', 'name', 'email', 'phone')->get()
            : Client::where('broker_id', $user->id)->select('id', 'name', 'email', 'phone')->get();
        
        $statuses = [
            'inquiry' => 'Inquiry',
            'initial_contact' => 'Initial Contact',
            'property_viewing' => 'Property Viewing',
            'offer_made' => 'Offer Made',
            'negotiation' => 'Negotiation',
            'offer_accepted' => 'Offer Accepted',
            'contract_signed' => 'Contract Signed',
            'due_diligence' => 'Due Diligence',
            'financing' => 'Financing',
            'closing_preparation' => 'Closing Preparation',
            'finalized' => 'Finalized',
            'cancelled' => 'Cancelled'
        ];
        
        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'properties' => $properties,
            'clients' => $clients,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Update the specified transaction
     */
    public function update(Request $request, Transaction $transaction)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role === 'broker' && $transaction->broker_id !== $user->id) {
            abort(403, 'Unauthorized access to transaction.');
        }
        
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'client_id' => 'required|exists:clients,id',
            'offered_price' => 'required|numeric|min:0',
            'final_price' => 'nullable|numeric|min:0',
            'commission_rate' => 'required|numeric|min:0|max:1',
            'status' => 'required|in:inquiry,initial_contact,property_viewing,offer_made,negotiation,offer_accepted,contract_signed,due_diligence,financing,closing_preparation,finalized,cancelled',
            'inquiry_date' => 'required|date',
            'first_contact_date' => 'nullable|date',
            'viewing_date' => 'nullable|date',
            'offer_date' => 'nullable|date',
            'acceptance_date' => 'nullable|date',
            'contract_date' => 'nullable|date',
            'closing_date' => 'nullable|date',
            'finalized_date' => 'nullable|date',
            'broker_notes' => 'nullable|string',
        ]);
        
        // Calculate commission amount based on final price or offered price
        $price = $validated['final_price'] ?? $validated['offered_price'];
        $validated['commission_amount'] = $price * $validated['commission_rate'];
        
        // Auto-set finalized_date if status is finalized
        if ($validated['status'] === 'finalized' && !$validated['finalized_date']) {
            $validated['finalized_date'] = now();
        }
        
        $oldStatus = $transaction->status;
        
        $transaction->update($validated);
        
        // Send notification if status changed
        if ($oldStatus !== $validated['status']) {
            $transaction->broker->notify(new TransactionStatusNotification($transaction, $oldStatus));
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Transaction $transaction)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role === 'broker' && $transaction->broker_id !== $user->id) {
            abort(403, 'Unauthorized access to transaction.');
        }
        
        $transaction->delete();
        
        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Update transaction status
     */
    public function updateStatus(Request $request, Transaction $transaction)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role === 'broker' && $transaction->broker_id !== $user->id) {
            abort(403, 'Unauthorized access to transaction.');
        }
        
        $validated = $request->validate([
            'status' => 'required|in:inquiry,initial_contact,property_viewing,offer_made,negotiation,offer_accepted,contract_signed,due_diligence,financing,closing_preparation,finalized,cancelled',
            'notes' => 'nullable|string',
        ]);
        
        $updateData = ['status' => $validated['status']];
        
        // Auto-set relevant dates based on status
        switch ($validated['status']) {
            case 'initial_contact':
                if (!$transaction->first_contact_date) {
                    $updateData['first_contact_date'] = now();
                }
                break;
            case 'property_viewing':
                if (!$transaction->viewing_date) {
                    $updateData['viewing_date'] = now();
                }
                break;
            case 'offer_made':
                if (!$transaction->offer_date) {
                    $updateData['offer_date'] = now();
                }
                break;
            case 'offer_accepted':
                if (!$transaction->acceptance_date) {
                    $updateData['acceptance_date'] = now();
                }
                break;
            case 'contract_signed':
                if (!$transaction->contract_date) {
                    $updateData['contract_date'] = now();
                }
                break;
            case 'finalized':
                if (!$transaction->finalized_date) {
                    $updateData['finalized_date'] = now();
                }
                break;
        }
        
        if ($validated['notes']) {
            $updateData['broker_notes'] = $transaction->broker_notes . "\n\n" . now()->format('Y-m-d H:i') . " - " . $validated['notes'];
        }
        
        $transaction->update($updateData);
        
        return back()->with('success', 'Transaction status updated successfully.');
    }
}