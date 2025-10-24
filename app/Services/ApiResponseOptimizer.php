<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiResponseOptimizer
{
    /**
     * Optimize API response with caching
     */
    public function optimizeResponse(string $cacheKey, callable $callback, int $ttl = 60): JsonResponse
    {
        try {
            // Try to get from cache first
            if (Cache::has($cacheKey)) {
                $data = Cache::get($cacheKey);
                return response()->json($data)->header('X-From-Cache', 'true');
            }
            
            // Generate response
            $response = $callback();
            
            // Cache the response
            if ($response instanceof JsonResponse) {
                $data = $response->getData(true);
                Cache::put($cacheKey, $data, $ttl);
            }
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error("Failed to optimize API response: " . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Optimize paginated response
     */
    public function optimizePaginatedResponse(string $cacheKey, callable $callback, int $ttl = 60): JsonResponse
    {
        try {
            // Try to get from cache first
            if (Cache::has($cacheKey)) {
                $data = Cache::get($cacheKey);
                return response()->json($data)->header('X-From-Cache', 'true');
            }
            
            // Generate response
            $response = $callback();
            
            // Cache the response
            if ($response instanceof JsonResponse) {
                $data = $response->getData(true);
                Cache::put($cacheKey, $data, $ttl);
            }
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error("Failed to optimize paginated response: " . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Generate cache key for API response
     */
    public function generateCacheKey(string $endpoint, array $params = []): string
    {
        $key = $endpoint;
        
        if (!empty($params)) {
            ksort($params); // Sort for consistent cache keys
            $key .= ':' . md5(serialize($params));
        }
        
        return 'api_response:' . $key;
    }

    /**
     * Clear API response cache
     */
    public function clearApiCache(string $pattern = '*'): int
    {
        $clearedCount = 0;
        
        try {
            $keys = Cache::getRedis()->keys("api_response:{$pattern}");
            
            foreach ($keys as $key) {
                Cache::forget($key);
                $clearedCount++;
            }
            
            Log::info("Cleared {$clearedCount} API response cache entries");
            
        } catch (\Exception $e) {
            Log::error("Failed to clear API cache: " . $e->getMessage());
        }
        
        return $clearedCount;
    }

    /**
     * Get API cache statistics
     */
    public function getApiCacheStats(): array
    {
        try {
            $keys = Cache::getRedis()->keys('api_response:*');
            $totalKeys = count($keys);
            
            $totalSize = 0;
            foreach ($keys as $key) {
                $size = Cache::getRedis()->strlen($key);
                $totalSize += $size;
            }
            
            return [
                'total_keys' => $totalKeys,
                'total_size' => $totalSize,
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                'average_size' => $totalKeys > 0 ? round($totalSize / $totalKeys) : 0,
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to get API cache stats: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Optimize response data structure
     */
    public function optimizeDataStructure(array $data): array
    {
        // Remove null values
        $data = array_filter($data, function($value) {
            return $value !== null;
        });
        
        // Convert objects to arrays if needed
        $data = $this->convertObjectsToArrays($data);
        
        // Remove unnecessary fields
        $data = $this->removeUnnecessaryFields($data);
        
        return $data;
    }

    /**
     * Convert objects to arrays recursively
     */
    private function convertObjectsToArrays($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->convertObjectsToArrays($value);
            }
        }
        
        return $data;
    }

    /**
     * Remove unnecessary fields from response
     */
    private function removeUnnecessaryFields(array $data): array
    {
        $unnecessaryFields = [
            'created_at',
            'updated_at',
            'deleted_at',
            'remember_token',
            'email_verified_at',
            'password',
            'password_confirmation',
        ];
        
        foreach ($unnecessaryFields as $field) {
            unset($data[$field]);
        }
        
        return $data;
    }

    /**
     * Add response headers for optimization
     */
    public function addOptimizationHeaders(JsonResponse $response): JsonResponse
    {
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'DENY');
        $response->header('X-XSS-Protection', '1; mode=block');
        $response->header('Cache-Control', 'public, max-age=60');
        $response->header('ETag', md5($response->getContent()));
        
        return $response;
    }

    /**
     * Compress response data
     */
    public function compressResponse(JsonResponse $response): JsonResponse
    {
        $content = $response->getContent();
        $compressed = gzencode($content, 9);
        
        if ($compressed !== false) {
            $response->setContent($compressed);
            $response->header('Content-Encoding', 'gzip');
            $response->header('Content-Length', strlen($compressed));
        }
        
        return $response;
    }

    /**
     * Validate API response
     */
    public function validateResponse(JsonResponse $response): bool
    {
        $content = $response->getContent();
        
        // Check if content is valid JSON
        json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        // Check response size
        if (strlen($content) > 1024 * 1024) { // 1MB limit
            return false;
        }
        
        return true;
    }

    /**
     * Get response performance metrics
     */
    public function getResponseMetrics(JsonResponse $response): array
    {
        $content = $response->getContent();
        $data = json_decode($content, true);
        
        return [
            'content_size' => strlen($content),
            'content_size_kb' => round(strlen($content) / 1024, 2),
            'data_count' => is_array($data) ? count($data) : 0,
            'is_compressed' => $response->headers->get('Content-Encoding') === 'gzip',
            'cache_status' => $response->headers->get('X-From-Cache') === 'true',
        ];
    }
}

