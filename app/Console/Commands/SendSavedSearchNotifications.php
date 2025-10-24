<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\SavedSearch;
use App\Notifications\NewPropertiesMatchSearch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendSavedSearchNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'searches:notify {--force : Force send notifications even if sent recently}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notifications for saved searches when new matching properties are found';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking saved searches for new property matches...');

        // Get all saved searches with notifications enabled
        $searches = SavedSearch::with('user')
            ->where('notify_on_new', true)
            ->get();

        if ($searches->isEmpty()) {
            $this->info('No saved searches with notifications enabled.');
            return 0;
        }

        $this->info("Found {$searches->count()} saved searches to check.");

        $notificationsSent = 0;

        foreach ($searches as $search) {
            // Skip if notified recently (within 24 hours) unless forced
            if (!$this->option('force') && $search->last_notified_at && $search->last_notified_at->gt(now()->subDay())) {
                $this->line("Skipping '{$search->name}' - notified recently");
                continue;
            }

            // Find properties matching the search filters
            $matchingProperties = $this->findMatchingProperties($search);

            if ($matchingProperties->isEmpty()) {
                $this->line("No new properties for '{$search->name}'");
                continue;
            }

            // Send notification
            try {
                $search->user->notify(new NewPropertiesMatchSearch($search, $matchingProperties));
                
                // Update last notified timestamp
                $search->update(['last_notified_at' => now()]);
                
                $this->info("✓ Sent notification to {$search->user->email} for '{$search->name}' ({$matchingProperties->count()} properties)");
                $notificationsSent++;
            } catch (\Exception $e) {
                $this->error("✗ Failed to send notification for '{$search->name}': {$e->getMessage()}");
            }
        }

        $this->info("---");
        $this->info("Completed! Sent {$notificationsSent} notifications.");

        return 0;
    }

    /**
     * Find properties matching the saved search filters
     */
    protected function findMatchingProperties(SavedSearch $search)
    {
        $filters = $search->filters;
        $query = Property::query()->where('status', 'available');

        // Only get properties created since last notification (or last 7 days if never notified)
        $since = $search->last_notified_at ?? now()->subWeek();
        $query->where('created_at', '>', $since);

        // Apply search filters
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['municipality'])) {
            $query->where('municipality', $filters['municipality']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('total_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('total_price', '<=', $filters['max_price']);
        }

        if (!empty($filters['min_area'])) {
            $query->where('area', '>=', $filters['min_area']);
        }

        if (!empty($filters['max_area'])) {
            $query->where('area', '<=', $filters['max_area']);
        }

        if (!empty($filters['utilities'])) {
            $query->where('utilities_available', true);
        }

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['virtual_tour'])) {
            $query->whereNotNull('virtual_tour_images');
        }

        return $query->latest()->get();
    }
}
