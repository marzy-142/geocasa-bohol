<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Services\DatabaseMonitoringService;
use App\Models\Transaction;
use App\Models\Inquiry;
use App\Observers\TransactionObserver;
use App\Observers\InquiryObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        
        // Register model observers for inquiry-transaction sync
        Transaction::observe(TransactionObserver::class);
        Inquiry::observe(InquiryObserver::class);
        
        // Set up database monitoring - TEMPORARILY DISABLED FOR DEBUGGING
        // if (config('app.env') !== 'testing') {
        //     $monitoringService = app(DatabaseMonitoringService::class);
        //     
        //     DB::listen(function ($query) use ($monitoringService) {
        //         $monitoringService->logQuery(
        //             $query->sql,
        //             $query->bindings,
        //             $query->time
        //         );
        //     });
        // }
    }
}
