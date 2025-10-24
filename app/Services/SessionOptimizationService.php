<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SessionOptimizationService
{
    /**
     * Get session statistics
     */
    public function getSessionStats(): array
    {
        $sessionData = Session::all();
        $sessionSize = strlen(serialize($sessionData));
        
        return [
            'session_id' => Session::getId(),
            'session_size' => $sessionSize,
            'session_size_kb' => round($sessionSize / 1024, 2),
            'session_count' => count($sessionData),
            'session_lifetime' => config('session.lifetime'),
            'session_driver' => config('session.driver'),
        ];
    }

    /**
     * Optimize session data
     */
    public function optimizeSessionData(): void
    {
        $sessionData = Session::all();
        $optimizedData = [];
        
        foreach ($sessionData as $key => $value) {
            // Only keep essential session data
            if ($this->isEssentialSessionData($key)) {
                $optimizedData[$key] = $value;
            }
        }
        
        // Clear and rebuild session
        Session::flush();
        foreach ($optimizedData as $key => $value) {
            Session::put($key, $value);
        }
        
        Log::info('Session data optimized', [
            'original_count' => count($sessionData),
            'optimized_count' => count($optimizedData),
            'space_saved' => count($sessionData) - count($optimizedData),
        ]);
    }

    /**
     * Check if session data is essential
     */
    private function isEssentialSessionData(string $key): bool
    {
        $essentialKeys = [
            'login_web',
            'password_hash_web',
            'remember_web',
            'user_id',
            'role',
            'permissions',
            'csrf_token',
        ];
        
        return in_array($key, $essentialKeys) || str_starts_with($key, 'auth.');
    }

    /**
     * Clear old session data
     */
    public function clearOldSessionData(int $maxAge = 3600): int
    {
        $clearedCount = 0;
        $sessionData = Session::all();
        $currentTime = time();
        
        foreach ($sessionData as $key => $value) {
            if (str_contains($key, '_timestamp')) {
                $timestamp = $value;
                if ($currentTime - $timestamp > $maxAge) {
                    Session::forget($key);
                    $clearedCount++;
                }
            }
        }
        
        Log::info("Cleared {$clearedCount} old session data entries");
        
        return $clearedCount;
    }

    /**
     * Compress session data
     */
    public function compressSessionData(): void
    {
        $sessionData = Session::all();
        $compressedData = [];
        
        foreach ($sessionData as $key => $value) {
            if (is_string($value) && strlen($value) > 100) {
                $compressed = gzcompress($value, 9);
                if ($compressed !== false) {
                    $compressedData[$key . '_compressed'] = base64_encode($compressed);
                    $compressedData[$key . '_is_compressed'] = true;
                } else {
                    $compressedData[$key] = $value;
                }
            } else {
                $compressedData[$key] = $value;
            }
        }
        
        // Clear and rebuild session
        Session::flush();
        foreach ($compressedData as $key => $value) {
            Session::put($key, $value);
        }
        
        Log::info('Session data compressed');
    }

    /**
     * Decompress session data
     */
    public function decompressSessionData(): void
    {
        $sessionData = Session::all();
        $decompressedData = [];
        
        foreach ($sessionData as $key => $value) {
            if (str_ends_with($key, '_is_compressed') && $value === true) {
                $originalKey = str_replace('_is_compressed', '', $key);
                $compressedKey = $originalKey . '_compressed';
                
                if (isset($sessionData[$compressedKey])) {
                    $compressed = base64_decode($sessionData[$compressedKey]);
                    $decompressed = gzuncompress($compressed);
                    
                    if ($decompressed !== false) {
                        $decompressedData[$originalKey] = $decompressed;
                    }
                }
            } elseif (!str_ends_with($key, '_compressed') && !str_ends_with($key, '_is_compressed')) {
                $decompressedData[$key] = $value;
            }
        }
        
        // Clear and rebuild session
        Session::flush();
        foreach ($decompressedData as $key => $value) {
            Session::put($key, $value);
        }
        
        Log::info('Session data decompressed');
    }

    /**
     * Get session performance metrics
     */
    public function getSessionPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test session read/write performance
        $testKey = 'performance_test_' . time();
        $testValue = str_repeat('test', 1000); // 4KB test data
        
        Session::put($testKey, $testValue);
        $readValue = Session::get($testKey);
        Session::forget($testKey);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'read_write_time' => $executionTime,
            'read_write_time_ms' => round($executionTime * 1000, 2),
            'test_data_size' => strlen($testValue),
            'test_successful' => $readValue === $testValue,
        ];
    }

    /**
     * Optimize session configuration
     */
    public function optimizeSessionConfiguration(): array
    {
        $currentConfig = [
            'driver' => config('session.driver'),
            'lifetime' => config('session.lifetime'),
            'expire_on_close' => config('session.expire_on_close'),
            'encrypt' => config('session.encrypt'),
            'files' => config('session.files'),
            'connection' => config('session.connection'),
            'table' => config('session.table'),
            'store' => config('session.store'),
            'lottery' => config('session.lottery'),
            'cookie' => config('session.cookie'),
            'path' => config('session.path'),
            'domain' => config('session.domain'),
            'secure' => config('session.secure'),
            'http_only' => config('session.http_only'),
            'same_site' => config('session.same_site'),
        ];
        
        $recommendations = [];
        
        // Check driver
        if ($currentConfig['driver'] === 'file') {
            $recommendations[] = 'Consider using Redis or database driver for better performance';
        }
        
        // Check lifetime
        if ($currentConfig['lifetime'] > 1440) { // 24 hours
            $recommendations[] = 'Session lifetime is very long. Consider reducing for security';
        }
        
        // Check encryption
        if (!$currentConfig['encrypt']) {
            $recommendations[] = 'Consider enabling session encryption for security';
        }
        
        // Check secure flag
        if (!$currentConfig['secure'] && app()->environment('production')) {
            $recommendations[] = 'Consider enabling secure flag for production';
        }
        
        return [
            'current_config' => $currentConfig,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions(): int
    {
        $cleanedCount = 0;
        
        try {
            $driver = config('session.driver');
            
            switch ($driver) {
                case 'database':
                    $cleanedCount = $this->cleanupDatabaseSessions();
                    break;
                case 'file':
                    $cleanedCount = $this->cleanupFileSessions();
                    break;
                case 'redis':
                    $cleanedCount = $this->cleanupRedisSessions();
                    break;
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to cleanup expired sessions: " . $e->getMessage());
        }
        
        return $cleanedCount;
    }

    /**
     * Cleanup database sessions
     */
    private function cleanupDatabaseSessions(): int
    {
        $table = config('session.table', 'sessions');
        $lifetime = config('session.lifetime', 120);
        
        $expired = \DB::table($table)
            ->where('last_activity', '<', time() - ($lifetime * 60))
            ->delete();
        
        return $expired;
    }

    /**
     * Cleanup file sessions
     */
    private function cleanupFileSessions(): int
    {
        $path = config('session.files');
        $lifetime = config('session.lifetime', 120);
        $cleanedCount = 0;
        
        $files = glob($path . '/*');
        $cutoffTime = time() - ($lifetime * 60);
        
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                unlink($file);
                $cleanedCount++;
            }
        }
        
        return $cleanedCount;
    }

    /**
     * Cleanup Redis sessions
     */
    private function cleanupRedisSessions(): int
    {
        $cleanedCount = 0;
        
        try {
            $redis = Cache::getRedis();
            $pattern = config('session.connection') . ':sessions:*';
            $keys = $redis->keys($pattern);
            
            foreach ($keys as $key) {
                $ttl = $redis->ttl($key);
                if ($ttl === -1) { // No expiration set
                    $redis->expire($key, config('session.lifetime', 120) * 60);
                } elseif ($ttl === -2) { // Key doesn't exist
                    $cleanedCount++;
                }
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to cleanup Redis sessions: " . $e->getMessage());
        }
        
        return $cleanedCount;
    }

    /**
     * Get session recommendations
     */
    public function getSessionRecommendations(): array
    {
        $stats = $this->getSessionStats();
        $recommendations = [];
        
        if ($stats['session_size_kb'] > 100) {
            $recommendations[] = 'Session size is large. Consider optimizing session data';
        }
        
        if ($stats['session_count'] > 50) {
            $recommendations[] = 'Many session variables. Consider consolidating or removing unused ones';
        }
        
        if ($stats['session_lifetime'] > 1440) {
            $recommendations[] = 'Session lifetime is long. Consider reducing for security';
        }
        
        if (empty($recommendations)) {
            $recommendations[] = 'Session configuration looks good';
        }
        
        return $recommendations;
    }
}

