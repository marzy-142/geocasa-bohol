<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Services\DatabaseMonitoringService;

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
        
        // Set up database monitoring
        if (config('app.env') !== 'testing') {
            $monitoringService = app(DatabaseMonitoringService::class);
            
            DB::listen(function ($query) use ($monitoringService) {
                $monitoringService->logQuery(
                    $query->sql,
                    $query->bindings,
                    $query->time
                );
            });
        }
    }
}
