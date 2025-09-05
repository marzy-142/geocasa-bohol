<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class DatabaseMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:monitor {--report : Generate performance report}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor database performance and generate reports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('report')) {
            $this->generatePerformanceReport();
        } else {
            $this->showCurrentStats();
        }
    }

    /**
     * Show current database statistics
     */
    protected function showCurrentStats()
    {
        $this->info('Database Performance Monitor');
        $this->line('================================');

        // Connection info
        $this->info('Connection: ' . config('database.default'));
        $this->info('Host: ' . config('database.connections.' . config('database.default') . '.host'));
        $this->info('Database: ' . config('database.connections.' . config('database.default') . '.database'));
        $this->line('');

        // Show process list
        $this->showProcessList();

        // Show table sizes
        $this->showTableSizes();

        // Show index usage
        $this->showIndexUsage();
    }

    /**
     * Show MySQL process list
     */
    protected function showProcessList()
    {
        try {
            $processes = DB::select('SHOW PROCESSLIST');
            $this->info('Active Connections: ' . count($processes));
            
            $activeQueries = collect($processes)->where('Command', '!=', 'Sleep')->count();
            $this->info('Active Queries: ' . $activeQueries);
            $this->line('');
        } catch (\Exception $e) {
            $this->warn('Could not retrieve process list: ' . $e->getMessage());
        }
    }

    /**
     * Show table sizes
     */
    protected function showTableSizes()
    {
        try {
            $database = config('database.connections.' . config('database.default') . '.database');
            $tables = DB::select("
                SELECT 
                    table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                    table_rows
                FROM information_schema.TABLES 
                WHERE table_schema = ? 
                ORDER BY (data_length + index_length) DESC
                LIMIT 10
            ", [$database]);

            $this->info('Top 10 Largest Tables:');
            $this->table(
                ['Table', 'Size (MB)', 'Rows'],
                collect($tables)->map(function ($table) {
                    return [
                        $table->table_name,
                        $table->size_mb,
                        number_format($table->table_rows)
                    ];
                })->toArray()
            );
            $this->line('');
        } catch (\Exception $e) {
            $this->warn('Could not retrieve table sizes: ' . $e->getMessage());
        }
    }

    /**
     * Show index usage statistics
     */
    protected function showIndexUsage()
    {
        try {
            $database = config('database.connections.' . config('database.default') . '.database');
            $indexes = DB::select("
                SELECT 
                    t.table_name,
                    s.index_name,
                    s.column_name,
                    s.cardinality
                FROM information_schema.STATISTICS s
                JOIN information_schema.TABLES t ON s.table_name = t.table_name
                WHERE s.table_schema = ? 
                AND t.table_schema = ?
                AND s.index_name != 'PRIMARY'
                ORDER BY s.cardinality DESC
                LIMIT 15
            ", [$database, $database]);

            $this->info('Index Statistics (Top 15 by Cardinality):');
            $this->table(
                ['Table', 'Index', 'Column', 'Cardinality'],
                collect($indexes)->map(function ($index) {
                    return [
                        $index->table_name,
                        $index->index_name,
                        $index->column_name,
                        number_format($index->cardinality)
                    ];
                })->toArray()
            );
        } catch (\Exception $e) {
            $this->warn('Could not retrieve index statistics: ' . $e->getMessage());
        }
    }

    /**
     * Generate performance report from log files
     */
    protected function generatePerformanceReport()
    {
        $this->info('Generating Database Performance Report');
        $this->line('=========================================');

        $slowQueryLogPath = storage_path('logs/slow-queries-' . Carbon::now()->format('Y-m-d') . '.log');
        
        if (File::exists($slowQueryLogPath)) {
            $this->analyzeSlowQueries($slowQueryLogPath);
        } else {
            $this->info('No slow query log found for today.');
        }

        $this->line('');
        $this->info('Report generated at: ' . Carbon::now()->format('Y-m-d H:i:s'));
    }

    /**
     * Analyze slow queries from log file
     */
    protected function analyzeSlowQueries($logPath)
    {
        $content = File::get($logPath);
        $lines = explode("\n", $content);
        
        $slowQueries = [];
        foreach ($lines as $line) {
            if (strpos($line, 'Slow Database Query Detected') !== false) {
                $slowQueries[] = $line;
            }
        }

        $this->info('Slow Queries Today: ' . count($slowQueries));
        
        if (count($slowQueries) > 0) {
            $this->warn('Recent slow queries detected. Check ' . $logPath . ' for details.');
            
            // Show first few slow queries as examples
            $this->info('Sample slow queries:');
            foreach (array_slice($slowQueries, 0, 3) as $query) {
                $this->line('- ' . substr($query, 0, 100) . '...');
            }
        }
    }
}