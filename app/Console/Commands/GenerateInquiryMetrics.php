<?php

namespace App\Console\Commands;

use App\Models\Inquiry;
use App\Models\User;
use App\Models\Property;
use App\Services\InquiryMonitoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateInquiryMetrics extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'inquiry:generate-metrics 
                            {--date= : Generate metrics for specific date (Y-m-d format)}
                            {--days=1 : Number of days to generate metrics for}
                            {--force : Force regeneration of existing metrics}';

    /**
     * The console command description.
     */
    protected $description = 'Generate daily inquiry metrics and performance reports';

    protected InquiryMonitoringService $monitoringService;

    public function __construct(InquiryMonitoringService $monitoringService)
    {
        parent::__construct();
        $this->monitoringService = $monitoringService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::yesterday();
        $days = (int) $this->option('days');
        $force = $this->option('force');

        $this->info("Generating inquiry metrics for {$days} day(s) starting from {$date->format('Y-m-d')}");

        $progressBar = $this->output->createProgressBar($days);
        $progressBar->start();

        for ($i = 0; $i < $days; $i++) {
            $currentDate = $date->copy()->subDays($i);
            
            try {
                $this->generateDailyMetrics($currentDate, $force);
                $this->generateBrokerMetrics($currentDate, $force);
                $this->generatePropertyMetrics($currentDate, $force);
                
                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("\nFailed to generate metrics for {$currentDate->format('Y-m-d')}: {$e->getMessage()}");
                return Command::FAILURE;
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Inquiry metrics generated successfully!');

        return Command::SUCCESS;
    }

    /**
     * Generate daily summary metrics
     */
    private function generateDailyMetrics(Carbon $date, bool $force): void
    {
        $existingMetric = DB::table('inquiry_metrics')
            ->where('date', $date->format('Y-m-d'))
            ->where('metric_type', 'daily_summary')
            ->first();

        if ($existingMetric && !$force) {
            return;
        }

        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Get inquiry statistics
        $totalInquiries = Inquiry::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
        $newInquiries = Inquiry::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('status', 'new')->count();
        $contactedInquiries = Inquiry::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('status', 'contacted')->count();
        $scheduledInquiries = Inquiry::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('status', 'scheduled')->count();
        $completedInquiries = Inquiry::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('status', 'completed')->count();

        // Get duplicate prevention stats
        $duplicatesPrevented = DB::table('duplicate_logs')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->where('action_taken', 'reject')
            ->count();

        // Calculate response times
        $avgResponseTime = DB::table('inquiry_status_history')
            ->join('inquiries', 'inquiry_status_history.inquiry_id', '=', 'inquiries.id')
            ->whereBetween('inquiries.created_at', [$startOfDay, $endOfDay])
            ->where('inquiry_status_history.new_status', 'contacted')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, inquiries.created_at, inquiry_status_history.changed_at)) as avg_hours')
            ->value('avg_hours');

        // Get hourly breakdown
        $hourlyBreakdown = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourStart = $date->copy()->hour($hour)->minute(0)->second(0);
            $hourEnd = $hourStart->copy()->addHour()->subSecond();
            
            $hourlyBreakdown[$hour] = Inquiry::whereBetween('created_at', [$hourStart, $hourEnd])->count();
        }

        $metrics = [
            'total_inquiries' => $totalInquiries,
            'new_inquiries' => $newInquiries,
            'contacted_inquiries' => $contactedInquiries,
            'scheduled_inquiries' => $scheduledInquiries,
            'completed_inquiries' => $completedInquiries,
            'duplicates_prevented' => $duplicatesPrevented,
            'conversion_rate' => $totalInquiries > 0 ? round(($completedInquiries / $totalInquiries) * 100, 2) : 0,
            'contact_rate' => $totalInquiries > 0 ? round((($contactedInquiries + $scheduledInquiries + $completedInquiries) / $totalInquiries) * 100, 2) : 0,
            'average_response_time_hours' => $avgResponseTime ? round($avgResponseTime, 2) : null,
            'hourly_breakdown' => $hourlyBreakdown,
            'generated_at' => now()->toISOString(),
        ];

        DB::table('inquiry_metrics')->updateOrInsert(
            [
                'date' => $date->format('Y-m-d'),
                'metric_type' => 'daily_summary'
            ],
            [
                'data' => json_encode($metrics),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Generate broker performance metrics
     */
    private function generateBrokerMetrics(Carbon $date, bool $force): void
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $brokers = User::where('role', 'broker')->get();

        foreach ($brokers as $broker) {
            $existingMetric = DB::table('broker_performance_metrics')
                ->where('broker_id', $broker->id)
                ->where('date', $date->format('Y-m-d'))
                ->first();

            if ($existingMetric && !$force) {
                continue;
            }

            $inquiriesAssigned = Inquiry::where('assigned_broker_id', $broker->id)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();

            $inquiriesContacted = Inquiry::where('assigned_broker_id', $broker->id)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereIn('status', ['contacted', 'scheduled', 'completed'])
                ->count();

            $inquiriesScheduled = Inquiry::where('assigned_broker_id', $broker->id)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereIn('status', ['scheduled', 'completed'])
                ->count();

            $inquiriesCompleted = Inquiry::where('assigned_broker_id', $broker->id)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('status', 'completed')
                ->count();

            // Calculate average response time
            $avgResponseTime = DB::table('inquiry_status_history')
                ->join('inquiries', 'inquiry_status_history.inquiry_id', '=', 'inquiries.id')
                ->where('inquiries.assigned_broker_id', $broker->id)
                ->whereBetween('inquiries.created_at', [$startOfDay, $endOfDay])
                ->where('inquiry_status_history.new_status', 'contacted')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, inquiries.created_at, inquiry_status_history.changed_at)) as avg_hours')
                ->value('avg_hours');

            // Calculate conversion rate
            $conversionRate = $inquiriesAssigned > 0 ? round(($inquiriesCompleted / $inquiriesAssigned) * 100, 2) : 0;

            // Calculate workload score (active inquiries)
            $activeClients = Inquiry::where('assigned_broker_id', $broker->id)
                ->whereIn('status', ['new', 'contacted', 'scheduled'])
                ->count();

            DB::table('broker_performance_metrics')->updateOrInsert(
                [
                    'broker_id' => $broker->id,
                    'date' => $date->format('Y-m-d')
                ],
                [
                    'inquiries_assigned' => $inquiriesAssigned,
                    'inquiries_contacted' => $inquiriesContacted,
                    'inquiries_scheduled' => $inquiriesScheduled,
                    'inquiries_completed' => $inquiriesCompleted,
                    'average_response_time_hours' => $avgResponseTime ? round($avgResponseTime, 2) : null,
                    'conversion_rate' => $conversionRate,
                    'active_clients' => $activeClients,
                    'workload_score' => $this->calculateWorkloadScore($activeClients, $inquiriesAssigned),
                    'updated_at' => now()
                ]
            );
        }
    }

    /**
     * Generate property inquiry metrics
     */
    private function generatePropertyMetrics(Carbon $date, bool $force): void
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Get properties that received inquiries on this date
        $propertyIds = Inquiry::whereBetween('created_at', [$startOfDay, $endOfDay])
            ->distinct()
            ->pluck('property_id');

        foreach ($propertyIds as $propertyId) {
            $existingMetric = DB::table('property_inquiry_metrics')
                ->where('property_id', $propertyId)
                ->where('date', $date->format('Y-m-d'))
                ->first();

            if ($existingMetric && !$force) {
                continue;
            }

            $inquiriesReceived = Inquiry::where('property_id', $propertyId)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();

            $inquiriesCompleted = Inquiry::where('property_id', $propertyId)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('status', 'completed')
                ->count();

            $duplicatesPrevented = DB::table('duplicate_logs')
                ->where('property_id', $propertyId)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('action_taken', 'reject')
                ->count();

            // Calculate average response time for this property
            $avgResponseTime = DB::table('inquiry_status_history')
                ->join('inquiries', 'inquiry_status_history.inquiry_id', '=', 'inquiries.id')
                ->where('inquiries.property_id', $propertyId)
                ->whereBetween('inquiries.created_at', [$startOfDay, $endOfDay])
                ->where('inquiry_status_history.new_status', 'contacted')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, inquiries.created_at, inquiry_status_history.changed_at)) as avg_hours')
                ->value('avg_hours');

            $conversionRate = $inquiriesReceived > 0 ? round(($inquiriesCompleted / $inquiriesReceived) * 100, 2) : 0;

            DB::table('property_inquiry_metrics')->updateOrInsert(
                [
                    'property_id' => $propertyId,
                    'date' => $date->format('Y-m-d')
                ],
                [
                    'inquiries_received' => $inquiriesReceived,
                    'inquiries_completed' => $inquiriesCompleted,
                    'duplicates_prevented' => $duplicatesPrevented,
                    'conversion_rate' => $conversionRate,
                    'average_response_time_hours' => $avgResponseTime ? round($avgResponseTime, 2) : null,
                    'updated_at' => now()
                ]
            );
        }
    }

    /**
     * Calculate workload score for a broker
     */
    private function calculateWorkloadScore(int $activeClients, int $totalAssigned): float
    {
        // Simple workload scoring algorithm
        // Higher score means higher workload
        $baseScore = $activeClients * 2; // Active clients are weighted more
        $totalScore = $baseScore + ($totalAssigned * 0.5);
        
        return round($totalScore, 2);
    }
}