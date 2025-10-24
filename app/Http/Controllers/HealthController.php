<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class HealthController extends Controller
{
    /**
     * Perform a comprehensive health check
     */
    public function check(Request $request)
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];

        $overall = collect($checks)->every(fn($check) => $check['status'] === 'ok');
        
        $response = [
            'status' => $overall ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
            'environment' => app()->environment(),
            'version' => config('app.version', '1.0.0'),
        ];

        $statusCode = $overall ? 200 : 503;

        return response()->json($response, $statusCode);
    }

    /**
     * Simple health check endpoint
     */
    public function ping()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Check database connectivity
     */
    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            
            // Test a simple query
            $result = DB::select('SELECT 1 as test');
            
            return [
                'status' => 'ok',
                'message' => 'Database connection successful',
                'response_time' => $this->measureResponseTime(fn() => DB::select('SELECT 1')),
            ];
        } catch (Exception $e) {
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Connection error',
            ];
        }
    }

    /**
     * Check cache system
     */
    protected function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            $value = 'test_value';
            
            // Test cache write and read
            Cache::put($key, $value, 60);
            $retrieved = Cache::get($key);
            Cache::forget($key);
            
            if ($retrieved === $value) {
                return [
                    'status' => 'ok',
                    'message' => 'Cache system working',
                    'driver' => config('cache.default'),
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'Cache read/write test failed',
            ];
        } catch (Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'error',
                'message' => 'Cache system error',
                'error' => config('app.debug') ? $e->getMessage() : 'Cache error',
            ];
        }
    }

    /**
     * Check storage system
     */
    protected function checkStorage(): array
    {
        try {
            $testFile = 'health_check_' . time() . '.txt';
            $testContent = 'Health check test';
            
            // Test file write and read
            Storage::disk('local')->put($testFile, $testContent);
            $retrieved = Storage::disk('local')->get($testFile);
            Storage::disk('local')->delete($testFile);
            
            if ($retrieved === $testContent) {
                return [
                    'status' => 'ok',
                    'message' => 'Storage system working',
                    'disk' => 'local',
                ];
            }
            
            return [
                'status' => 'error',
                'message' => 'Storage read/write test failed',
            ];
        } catch (Exception $e) {
            Log::error('Storage health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'error',
                'message' => 'Storage system error',
                'error' => config('app.debug') ? $e->getMessage() : 'Storage error',
            ];
        }
    }

    /**
     * Check queue system
     */
    protected function checkQueue(): array
    {
        try {
            $queueConnection = config('queue.default');
            
            if ($queueConnection === 'sync') {
                return [
                    'status' => 'ok',
                    'message' => 'Queue using sync driver',
                    'driver' => 'sync',
                ];
            }
            
            // For database queue, check if jobs table exists
            if ($queueConnection === 'database') {
                $tableExists = DB::getSchemaBuilder()->hasTable('jobs');
                
                return [
                    'status' => $tableExists ? 'ok' : 'warning',
                    'message' => $tableExists ? 'Queue table exists' : 'Queue table missing',
                    'driver' => 'database',
                ];
            }
            
            return [
                'status' => 'ok',
                'message' => 'Queue system configured',
                'driver' => $queueConnection,
            ];
        } catch (Exception $e) {
            Log::error('Queue health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'error',
                'message' => 'Queue system error',
                'error' => config('app.debug') ? $e->getMessage() : 'Queue error',
            ];
        }
    }

    /**
     * Measure response time for a given operation
     */
    protected function measureResponseTime(callable $operation): string
    {
        $start = microtime(true);
        $operation();
        $end = microtime(true);
        
        return round(($end - $start) * 1000, 2) . 'ms';
    }
}