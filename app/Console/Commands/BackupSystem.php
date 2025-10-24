<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;
use Exception;

class BackupSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run 
                            {--database : Backup database only}
                            {--files : Backup files only}
                            {--cleanup : Run cleanup after backup}
                            {--list : List existing backups}
                            {--status : Show backup status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create system backups for database and files';

    protected BackupService $backupService;

    /**
     * Create a new command instance.
     */
    public function __construct(BackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            if ($this->option('list')) {
                return $this->listBackups();
            }

            if ($this->option('status')) {
                return $this->showStatus();
            }

            $this->info('Starting backup process...');
            $this->newLine();

            if ($this->option('database')) {
                return $this->backupDatabase();
            }

            if ($this->option('files')) {
                return $this->backupFiles();
            }

            // Full backup by default
            return $this->createFullBackup();

        } catch (Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Create a full backup
     */
    protected function createFullBackup(): int
    {
        $this->info('Creating full backup (database + files)...');
        
        $progressBar = $this->output->createProgressBar(3);
        $progressBar->start();

        try {
            $result = $this->backupService->createFullBackup();
            $progressBar->advance();

            $this->newLine(2);
            $this->info('✅ Full backup completed successfully!');
            $this->displayBackupResults($result);

            if ($this->option('cleanup')) {
                $this->info('Running cleanup...');
                $deleted = $this->backupService->cleanupOldBackups();
                $this->info("🗑️  Cleaned up {$deleted} old backups");
            }

            $progressBar->finish();
            return Command::SUCCESS;

        } catch (Exception $e) {
            $progressBar->finish();
            $this->newLine(2);
            $this->error('❌ Backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Backup database only
     */
    protected function backupDatabase(): int
    {
        $this->info('Creating database backup...');
        
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $result = $this->backupService->backupDatabase("backups/{$timestamp}");

            if ($result['status'] === 'success') {
                $this->info('✅ Database backup completed successfully!');
                $this->table(
                    ['Property', 'Value'],
                    [
                        ['Filename', $result['filename']],
                        ['Size', $this->formatBytes($result['size'])],
                        ['Duration', $result['duration_ms'] . 'ms'],
                        ['Path', $result['path']],
                    ]
                );
                return Command::SUCCESS;
            } else {
                $this->error('❌ Database backup failed: ' . $result['error']);
                return Command::FAILURE;
            }

        } catch (Exception $e) {
            $this->error('❌ Database backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Backup files only
     */
    protected function backupFiles(): int
    {
        $this->info('Creating files backup...');
        
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $results = $this->backupService->backupFiles("backups/{$timestamp}");

            $this->info('✅ Files backup completed!');
            
            $tableData = [];
            foreach ($results as $source => $result) {
                $tableData[] = [
                    $source,
                    $result['status'],
                    $result['duration_ms'] ?? 'N/A' . 'ms',
                    $result['error'] ?? $result['reason'] ?? 'Success'
                ];
            }

            $this->table(
                ['Source', 'Status', 'Duration', 'Notes'],
                $tableData
            );

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('❌ Files backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * List existing backups
     */
    protected function listBackups(): int
    {
        $this->info('📋 Existing Backups:');
        $this->newLine();

        $backups = $this->backupService->listBackups();

        if (empty($backups)) {
            $this->warn('No backups found.');
            return Command::SUCCESS;
        }

        $tableData = [];
        foreach ($backups as $backup) {
            $tableData[] = [
                $backup['timestamp'],
                $backup['files'],
                $this->formatBytes($backup['size']),
                $backup['created_at']->diffForHumans(),
            ];
        }

        $this->table(
            ['Timestamp', 'Files', 'Size', 'Age'],
            $tableData
        );

        return Command::SUCCESS;
    }

    /**
     * Show backup status
     */
    protected function showStatus(): int
    {
        $this->info('📊 Backup System Status:');
        $this->newLine();

        $status = $this->backupService->getBackupStatus();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Backups', $status['total_backups']],
                ['Total Size', $this->formatBytes($status['total_size'])],
                ['Latest Backup', $status['latest_backup']['timestamp'] ?? 'None'],
                ['Latest Backup Age', $status['latest_backup'] ? $status['latest_backup']['created_at']->diffForHumans() : 'N/A'],
                ['Cleanup Needed', $status['next_cleanup'] ? 'Yes' : 'No'],
                ['Backup Files', $status['disk_usage']['backup_files'] ?? 'Unknown'],
            ]
        );

        if ($status['latest_backup']) {
            $this->newLine();
            $this->info('Latest Backup Details:');
            $latest = $status['latest_backup'];
            $this->table(
                ['Property', 'Value'],
                [
                    ['Timestamp', $latest['timestamp']],
                    ['Files Count', $latest['files']],
                    ['Size', $this->formatBytes($latest['size'])],
                    ['Created', $latest['created_at']->format('Y-m-d H:i:s')],
                    ['Age', $latest['created_at']->diffForHumans()],
                ]
            );
        }

        return Command::SUCCESS;
    }

    /**
     * Display backup results in a formatted table
     */
    protected function displayBackupResults(array $result): void
    {
        $this->newLine();
        $this->info('📋 Backup Summary:');
        
        $this->table(
            ['Component', 'Status', 'Details'],
            [
                [
                    'Database',
                    $result['database']['status'] === 'success' ? '✅ Success' : '❌ Failed',
                    $result['database']['status'] === 'success' 
                        ? $this->formatBytes($result['database']['size']) . ' (' . $result['database']['duration_ms'] . 'ms)'
                        : $result['database']['error']
                ],
                [
                    'Files',
                    collect($result['files'])->every(fn($f) => $f['status'] === 'success') ? '✅ Success' : '⚠️  Partial',
                    collect($result['files'])->where('status', 'success')->count() . '/' . count($result['files']) . ' items'
                ],
            ]
        );

        $this->newLine();
        $this->info('📁 Backup Location: ' . $result['path']);
        $this->info('🕒 Timestamp: ' . $result['timestamp']);
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}