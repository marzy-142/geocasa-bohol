<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class DatabaseQueryOptimizer
{
    /**
     * Optimize common query patterns
     */
    public function optimizeCommonQueries(): void
    {
        $this->optimizePropertyQueries();
        $this->optimizeInquiryQueries();
        $this->optimizeTransactionQueries();
        $this->optimizeUserQueries();
    }

    /**
     * Optimize property-related queries
     */
    private function optimizePropertyQueries(): void
    {
        // Add indexes for common property queries
        $this->addIndexIfNotExists('properties', 'status');
        $this->addIndexIfNotExists('properties', 'type');
        $this->addIndexIfNotExists('properties', 'municipality');
        $this->addIndexIfNotExists('properties', 'broker_id');
        $this->addIndexIfNotExists('properties', ['status', 'type']);
        $this->addIndexIfNotExists('properties', ['municipality', 'status']);
        $this->addIndexIfNotExists('properties', ['broker_id', 'status']);
    }

    /**
     * Optimize inquiry-related queries
     */
    private function optimizeInquiryQueries(): void
    {
        // Add indexes for common inquiry queries
        $this->addIndexIfNotExists('inquiries', 'status');
        $this->addIndexIfNotExists('inquiries', 'inquiry_type');
        $this->addIndexIfNotExists('inquiries', 'assigned_broker_id');
        $this->addIndexIfNotExists('inquiries', 'client_id');
        $this->addIndexIfNotExists('inquiries', 'property_id');
        $this->addIndexIfNotExists('inquiries', ['status', 'created_at']);
        $this->addIndexIfNotExists('inquiries', ['assigned_broker_id', 'status']);
        $this->addIndexIfNotExists('inquiries', 'is_flagged');
    }

    /**
     * Optimize transaction-related queries
     */
    private function optimizeTransactionQueries(): void
    {
        // Add indexes for common transaction queries
        $this->addIndexIfNotExists('transactions', 'status');
        $this->addIndexIfNotExists('transactions', 'broker_id');
        $this->addIndexIfNotExists('transactions', 'client_id');
        $this->addIndexIfNotExists('transactions', 'property_id');
        $this->addIndexIfNotExists('transactions', ['status', 'created_at']);
        $this->addIndexIfNotExists('transactions', ['broker_id', 'status']);
        $this->addIndexIfNotExists('transactions', 'finalized_date');
    }

    /**
     * Optimize user-related queries
     */
    private function optimizeUserQueries(): void
    {
        // Add indexes for common user queries
        $this->addIndexIfNotExists('users', 'role');
        $this->addIndexIfNotExists('users', 'application_status');
        $this->addIndexIfNotExists('users', ['role', 'application_status']);
        $this->addIndexIfNotExists('users', 'is_approved');
    }

    /**
     * Add index if it doesn't exist
     */
    private function addIndexIfNotExists(string $table, $columns): void
    {
        try {
            $indexName = $this->generateIndexName($table, $columns);
            
            if (!$this->indexExists($table, $indexName)) {
                $columnsString = is_array($columns) ? implode(',', $columns) : $columns;
                DB::statement("ALTER TABLE {$table} ADD INDEX {$indexName} ({$columnsString})");
                Log::info("Added index {$indexName} to table {$table}");
            }
        } catch (\Exception $e) {
            Log::warning("Failed to add index to table {$table}: " . $e->getMessage());
        }
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Generate index name
     */
    private function generateIndexName(string $table, $columns): string
    {
        $columnString = is_array($columns) ? implode('_', $columns) : $columns;
        return "idx_{$table}_{$columnString}";
    }

    /**
     * Optimize specific query builder
     */
    public function optimizeQuery(Builder $query): Builder
    {
        // Add common optimizations
        $query->select('*'); // Avoid selecting all columns when possible
        
        // Add eager loading for common relationships
        if (str_contains($query->toSql(), 'properties')) {
            $query->with(['broker:id,name', 'client:id,name']);
        }
        
        if (str_contains($query->toSql(), 'inquiries')) {
            $query->with(['property:id,title', 'client:id,name', 'broker:id,name']);
        }
        
        if (str_contains($query->toSql(), 'transactions')) {
            $query->with(['property:id,title', 'client:id,name', 'broker:id,name']);
        }
        
        return $query;
    }

    /**
     * Get query performance metrics
     */
    public function getQueryMetrics(): array
    {
        $queries = DB::getQueryLog();
        
        $totalQueries = count($queries);
        $totalTime = array_sum(array_column($queries, 'time'));
        $avgTime = $totalQueries > 0 ? $totalTime / $totalQueries : 0;
        
        $slowQueries = array_filter($queries, function($query) {
            return $query['time'] > 100; // Queries taking more than 100ms
        });
        
        return [
            'total_queries' => $totalQueries,
            'total_time' => round($totalTime, 2),
            'average_time' => round($avgTime, 2),
            'slow_queries' => count($slowQueries),
            'slow_query_percentage' => $totalQueries > 0 ? round((count($slowQueries) / $totalQueries) * 100, 2) : 0,
        ];
    }

    /**
     * Analyze slow queries
     */
    public function analyzeSlowQueries(): array
    {
        $queries = DB::getQueryLog();
        
        $slowQueries = array_filter($queries, function($query) {
            return $query['time'] > 100; // Queries taking more than 100ms
        });
        
        $analysis = [];
        foreach ($slowQueries as $query) {
            $analysis[] = [
                'sql' => $query['query'],
                'time' => $query['time'],
                'bindings' => $query['bindings'],
                'suggestions' => $this->getQuerySuggestions($query['query']),
            ];
        }
        
        return $analysis;
    }

    /**
     * Get suggestions for optimizing a query
     */
    private function getQuerySuggestions(string $sql): array
    {
        $suggestions = [];
        
        // Check for common performance issues
        if (str_contains($sql, 'SELECT *')) {
            $suggestions[] = 'Consider selecting only needed columns instead of *';
        }
        
        if (str_contains($sql, 'ORDER BY') && !str_contains($sql, 'LIMIT')) {
            $suggestions[] = 'Consider adding LIMIT clause to ORDER BY queries';
        }
        
        if (str_contains($sql, 'LIKE') && str_contains($sql, '%')) {
            $suggestions[] = 'Consider using full-text search for better performance';
        }
        
        if (str_contains($sql, 'WHERE') && str_contains($sql, 'OR')) {
            $suggestions[] = 'Consider using UNION instead of OR for better performance';
        }
        
        return $suggestions;
    }

    /**
     * Enable query logging
     */
    public function enableQueryLogging(): void
    {
        DB::enableQueryLog();
    }

    /**
     * Disable query logging
     */
    public function disableQueryLogging(): void
    {
        DB::disableQueryLog();
    }

    /**
     * Clear query log
     */
    public function clearQueryLog(): void
    {
        DB::flushQueryLog();
    }
}

