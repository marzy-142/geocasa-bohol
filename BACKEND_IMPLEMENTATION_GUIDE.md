# Backend Implementation Guide for Enhanced Admin Interfaces

## Quick Start Guide

This guide provides the essential backend code needed to support the new Admin Inquiries and Transactions interfaces.

---

## 1. Create Admin Inquiry Controller

**File**: `app/Http/Controllers/Admin/AdminInquiryController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AdminInquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inquiry::with([
            'property:id,title,municipality,total_price',
            'client:id,name,email,phone',
            'broker:id,name,email'
        ]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('broker_id')) {
            $query->where('assigned_broker_id', $request->broker_id);
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->filled('inquiry_type')) {
            $query->where('inquiry_type', $request->inquiry_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Response time filter
        if ($request->filled('response_time')) {
            switch ($request->response_time) {
                case 'under_1h':
                    $query->whereNotNull('responded_at')
                          ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, responded_at) < 1');
                    break;
                case 'under_24h':
                    $query->whereNotNull('responded_at')
                          ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, responded_at) < 24');
                    break;
                case 'over_24h':
                    $query->whereNotNull('responded_at')
                          ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, responded_at) >= 24');
                    break;
                case 'over_48h':
                    $query->where('status', 'new')
                          ->where('created_at', '<', now()->subHours(48));
                    break;
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        switch ($sortBy) {
            case 'priority':
                $query->orderByRaw('CASE 
                    WHEN inquiry_type = "purchase" THEN 1
                    WHEN DATEDIFF(NOW(), created_at) > 3 THEN 1
                    WHEN inquiry_type = "viewing" THEN 2
                    WHEN DATEDIFF(NOW(), created_at) > 1 THEN 2
                    ELSE 3
                END');
                break;
            case 'broker':
                $query->join('users', 'inquiries.assigned_broker_id', '=', 'users.id')
                      ->orderBy('users.name', $sortOrder);
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        $inquiries = $query->paginate(12);

        // Get brokers with inquiry counts
        $brokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->withCount('inquiries')
            ->get(['id', 'name']);

        // Get properties
        $properties = Property::select('id', 'title', 'municipality')->get();

        // Calculate system stats
        $systemStats = [
            'total_inquiries' => Inquiry::count(),
            'pending' => Inquiry::where('status', 'new')->count(),
            'overdue' => Inquiry::where('status', 'new')
                ->where('created_at', '<', now()->subDays(2))
                ->count(),
            'response_rate' => $this->calculateResponseRate(),
        ];

        // Calculate metrics
        $metrics = [
            'avg_response_time' => $this->calculateAvgResponseTime(),
            'response_time_trend' => $this->calculateResponseTimeTrend(),
            'conversion_rate' => $this->calculateConversionRate(),
            'conversion_trend' => $this->calculateConversionTrend(),
            'active_brokers' => User::where('role', 'broker')
                ->where('is_approved', true)
                ->whereHas('inquiries', function($q) {
                    $q->where('created_at', '>', now()->subDays(30));
                })
                ->count(),
            'broker_utilization' => $this->calculateBrokerUtilization(),
            'flagged_issues' => Inquiry::where('is_flagged', true)->count(),
        ];

        return Inertia::render('Admin/Inquiries/Index', [
            'inquiries' => $inquiries,
            'properties' => $properties,
            'brokers' => $brokers,
            'filters' => $request->only([
                'search', 'status', 'broker_id', 'property_id', 
                'priority', 'inquiry_type', 'date_from', 'date_to',
                'response_time', 'sort_by'
            ]),
            'systemStats' => $systemStats,
            'metrics' => $metrics,
        ]);
    }

    public function reassign(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'broker_id' => 'required|exists:users,id',
        ]);

        $oldBroker = $inquiry->broker;
        $newBroker = User::findOrFail($request->broker_id);

        $inquiry->update([
            'assigned_broker_id' => $request->broker_id,
        ]);

        // Log the reassignment
        activity()
            ->performedOn($inquiry)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_broker' => $oldBroker?->name,
                'new_broker' => $newBroker->name,
            ])
            ->log('inquiry_reassigned');

        return back()->with('success', 'Inquiry reassigned successfully.');
    }

    public function flag(Request $request, Inquiry $inquiry)
    {
        $inquiry->update([
            'is_flagged' => $request->is_flagged,
            'flagged_at' => $request->is_flagged ? now() : null,
            'flagged_by' => $request->is_flagged ? auth()->id() : null,
            'flag_reason' => $request->flag_reason,
        ]);

        return back()->with('success', 
            $request->is_flagged ? 'Inquiry flagged.' : 'Flag removed.'
        );
    }

    public function export(Request $request)
    {
        // Implement CSV/Excel export
        // Use Laravel Excel or similar package
        
        return response()->download($filePath);
    }

    // Helper methods
    private function calculateResponseRate()
    {
        $total = Inquiry::count();
        $responded = Inquiry::whereNotNull('responded_at')->count();
        
        return $total > 0 ? round(($responded / $total) * 100, 1) : 0;
    }

    private function calculateAvgResponseTime()
    {
        $avg = Inquiry::whereNotNull('responded_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg_hours')
            ->value('avg_hours');

        if ($avg < 1) return '< 1h';
        if ($avg < 24) return round($avg) . 'h';
        return round($avg / 24, 1) . 'd';
    }

    private function calculateResponseTimeTrend()
    {
        // Compare last 7 days vs previous 7 days
        $recent = Inquiry::whereNotNull('responded_at')
            ->where('created_at', '>', now()->subDays(7))
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg')
            ->value('avg');

        $previous = Inquiry::whereNotNull('responded_at')
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg')
            ->value('avg');

        if ($previous == 0) return 'No data';
        
        $change = (($recent - $previous) / $previous) * 100;
        
        if ($change < 0) return '↓ ' . abs(round($change)) . '% faster';
        if ($change > 0) return '↑ ' . round($change) . '% slower';
        return 'No change';
    }

    private function calculateConversionRate()
    {
        $total = Inquiry::count();
        $converted = Inquiry::whereHas('transactions', function($q) {
            $q->where('status', 'finalized');
        })->count();

        return $total > 0 ? round(($converted / $total) * 100, 1) : 0;
    }

    private function calculateConversionTrend()
    {
        // Similar to response time trend
        return 'Calculating...';
    }

    private function calculateBrokerUtilization()
    {
        $totalBrokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->count();

        $activeBrokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->whereHas('inquiries', function($q) {
                $q->where('created_at', '>', now()->subDays(30));
            })
            ->count();

        return $totalBrokers > 0 ? round(($activeBrokers / $totalBrokers) * 100, 1) : 0;
    }
}
```

---

## 2. Update Admin Transaction Controller

**File**: `app/Http/Controllers/Admin/TransactionController.php`

Add these methods to your existing controller:

```php
public function adminIndex(Request $request)
{
    $query = Transaction::with([
        'property:id,title,address,municipality,total_price',
        'client:id,name,email,phone',
        'broker:id,name,email',
        'inquiry:id,property_id,client_id'
    ]);

    // Apply filters
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('transaction_number', 'like', "%{$search}%")
              ->orWhereHas('property', function ($q) use ($search) {
                  $q->where('title', 'like', "%{$search}%");
              })
              ->orWhereHas('client', function ($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%");
              });
        });
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('broker_id')) {
        $query->where('broker_id', $request->broker_id);
    }

    if ($request->filled('property_id')) {
        $query->where('property_id', $request->property_id);
    }

    if ($request->filled('date_from')) {
        $query->whereDate('inquiry_date', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('inquiry_date', '<=', $request->date_to);
    }

    if ($request->filled('min_amount')) {
        $query->where('final_price', '>=', $request->min_amount);
    }

    if ($request->filled('max_amount')) {
        $query->where('final_price', '<=', $request->max_amount);
    }

    // Sorting
    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');
    
    switch ($sortBy) {
        case 'amount':
            $query->orderBy('final_price', $sortOrder);
            break;
        case 'commission':
            $query->orderBy('commission_amount', $sortOrder);
            break;
        case 'broker':
            $query->join('users', 'transactions.broker_id', '=', 'users.id')
                  ->orderBy('users.name', $sortOrder);
            break;
        default:
            $query->orderBy($sortBy, $sortOrder);
    }

    $transactions = $query->paginate(12);

    // Get brokers with transaction counts
    $brokers = User::where('role', 'broker')
        ->where('is_approved', true)
        ->withCount('transactions')
        ->get(['id', 'name']);

    // Get properties
    $properties = Property::select('id', 'title', 'municipality')->get();

    // Statuses
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
        'cancelled' => 'Cancelled',
    ];

    // Financial stats
    $financialStats = [
        'total_transactions' => Transaction::count(),
        'active' => Transaction::whereNotIn('status', ['finalized', 'cancelled'])->count(),
        'total_value' => Transaction::where('status', 'finalized')->sum('final_price'),
        'total_commission' => Transaction::where('status', 'finalized')->sum('commission_amount'),
    ];

    // Performance metrics
    $performanceMetrics = [
        'avg_deal_time' => $this->calculateAvgDealTime(),
        'deal_time_trend' => $this->calculateDealTimeTrend(),
        'success_rate' => $this->calculateSuccessRate(),
        'success_trend' => $this->calculateSuccessTrend(),
        'avg_commission' => Transaction::where('status', 'finalized')->avg('commission_amount'),
        'pending_review' => Transaction::whereIn('status', ['offer_made', 'negotiation'])->count(),
    ];

    return Inertia::render('Admin/Transactions/Index', [
        'transactions' => $transactions,
        'properties' => $properties,
        'brokers' => $brokers,
        'statuses' => $statuses,
        'filters' => $request->only([
            'search', 'status', 'broker_id', 'property_id',
            'date_from', 'date_to', 'min_amount', 'max_amount', 'sort_by'
        ]),
        'financialStats' => $financialStats,
        'performanceMetrics' => $performanceMetrics,
        'canCreate' => auth()->user()->can('create', Transaction::class),
    ]);
}

private function calculateAvgDealTime()
{
    $avg = Transaction::where('status', 'finalized')
        ->whereNotNull('finalized_date')
        ->selectRaw('AVG(DATEDIFF(finalized_date, inquiry_date)) as avg_days')
        ->value('avg_days');

    if (!$avg) return 'N/A';
    
    return round($avg) . ' days';
}

private function calculateDealTimeTrend()
{
    // Compare recent vs previous period
    return 'Stable';
}

private function calculateSuccessRate()
{
    $total = Transaction::count();
    $finalized = Transaction::where('status', 'finalized')->count();

    return $total > 0 ? round(($finalized / $total) * 100, 1) : 0;
}

private function calculateSuccessTrend()
{
    return 'Improving';
}

public function export(Request $request)
{
    // Implement export functionality
    return response()->download($filePath);
}
```

---

## 3. Add Routes

**File**: `routes/web.php`

```php
// Admin Inquiry Management
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index');
    Route::get('/inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('inquiries.show');
    Route::post('/inquiries/{inquiry}/reassign', [AdminInquiryController::class, 'reassign'])->name('inquiries.reassign');
    Route::post('/inquiries/{inquiry}/flag', [AdminInquiryController::class, 'flag'])->name('inquiries.flag');
    Route::get('/inquiries/export', [AdminInquiryController::class, 'export'])->name('inquiries.export');
    
    // Admin Transaction Management (update existing routes)
    Route::get('/transactions', [TransactionController::class, 'adminIndex'])->name('transactions.index');
    Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
});
```

---

## 4. Database Migration

**Create migration**: `php artisan make:migration add_admin_fields_to_inquiries_table`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->boolean('is_flagged')->default(false)->after('status');
            $table->timestamp('flagged_at')->nullable()->after('is_flagged');
            $table->foreignId('flagged_by')->nullable()->constrained('users')->after('flagged_at');
            $table->text('flag_reason')->nullable()->after('flagged_by');
            
            // Add indexes for performance
            $table->index(['assigned_broker_id', 'status']);
            $table->index(['created_at', 'status']);
            $table->index('is_flagged');
        });
    }

    public function down()
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['is_flagged', 'flagged_at', 'flagged_by', 'flag_reason']);
        });
    }
};
```

---

## 5. Update Models

**File**: `app/Models/Inquiry.php`

```php
protected $fillable = [
    // ... existing fields
    'is_flagged',
    'flagged_at',
    'flagged_by',
    'flag_reason',
];

protected $casts = [
    // ... existing casts
    'is_flagged' => 'boolean',
    'flagged_at' => 'datetime',
];

// Add relationship
public function flagger()
{
    return $this->belongsTo(User::class, 'flagged_by');
}
```

**File**: `app/Models/User.php`

```php
// Add relationships if not already present
public function inquiries()
{
    return $this->hasMany(Inquiry::class, 'assigned_broker_id');
}

public function transactions()
{
    return $this->hasMany(Transaction::class, 'broker_id');
}
```

---

## 6. Testing Commands

```bash
# Run migrations
php artisan migrate

# Test the routes
php artisan route:list | grep admin

# Clear caches
php artisan optimize:clear

# Test in browser
# Navigate to: /admin/inquiries
# Navigate to: /admin/transactions
```

---

## 7. Optional: Activity Logging

Install Laravel Activity Log:

```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

---

## Quick Checklist

- [ ] Create `AdminInquiryController.php`
- [ ] Update `TransactionController.php` with admin methods
- [ ] Add routes to `web.php`
- [ ] Run database migration
- [ ] Update Inquiry model
- [ ] Test inquiry reassignment
- [ ] Test inquiry flagging
- [ ] Test transaction filters
- [ ] Test financial modal
- [ ] Verify permissions/authorization

---

**Need Help?** Refer to `ADMIN_INTERFACE_ENHANCEMENTS.md` for detailed documentation.
