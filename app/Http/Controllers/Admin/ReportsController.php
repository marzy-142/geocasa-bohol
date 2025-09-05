<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\ComplianceReport;
use App\Models\InvestigationLog;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index()
    {
        $stats = [
            'total_brokers' => User::where('role', 'broker')->count(),
            'active_brokers' => User::where('role', 'broker')->active()->count(),
            'total_properties' => Property::count(),
            'active_properties' => Property::where('status', 'active')->count(),
            'total_inquiries' => Inquiry::count(),
            'pending_inquiries' => Inquiry::where('status', 'pending')->count(),
            'total_compliance_reports' => ComplianceReport::count(),
            'pending_compliance_reports' => ComplianceReport::where('status', 'pending')->count(),
        ];

        $chartData = $this->getActivityTrends();
        $recentActivities = $this->getRecentActivities();
        $topBrokers = $this->getTopPerformingBrokers();
        $topProperties = $this->getMostInquiredProperties();

        return Inertia::render('Admin/Reports/Dashboard', [
            'stats' => $stats,
            'chartData' => $chartData,
            'recentActivities' => $recentActivities,
            'topBrokers' => $topBrokers,
            'topProperties' => $topProperties,
        ]);
    }
    
    public function brokers()
    {
        $stats = $this->getBrokerStats();
        $chartData = $this->getBrokerChartData();
        $topBrokers = $this->getTopPerformingBrokers();
        $recentActivities = $this->getRecentBrokerActivities();

        return Inertia::render('Admin/Reports/Brokers', [
            'stats' => $stats,
            'chartData' => $chartData,
            'topBrokers' => $topBrokers,
            'recentActivities' => $recentActivities,
        ]);
    }
    
    public function properties()
    {
        $stats = $this->getPropertyStats();
        $chartData = $this->getPropertyChartData();
        $topProperties = $this->getMostInquiredProperties();
        $recentActivities = $this->getRecentPropertyActivities();

        return Inertia::render('Admin/Reports/Properties', [
            'stats' => $stats,
            'chartData' => $chartData,
            'topProperties' => $topProperties,
            'recentActivities' => $recentActivities,
        ]);
    }
    
    public function inquiries()
    {
        $stats = $this->getInquiryStats();
        $chartData = $this->getInquiryChartData();
        $topProperties = $this->getMostInquiredProperties();
        $recentActivities = $this->getRecentInquiryActivities();

        return Inertia::render('Admin/Reports/Inquiries', [
            'stats' => $stats,
            'chartData' => $chartData,
            'topProperties' => $topProperties,
            'recentActivities' => $recentActivities,
        ]);
    }
    
    public function compliance()
    {
        $stats = $this->getComplianceStats();
        $chartData = $this->getComplianceChartData();
        $recentReports = $this->getRecentComplianceReports();
        $investigationStats = $this->getInvestigationStats();
        $recentActivities = $this->getRecentInvestigationActivities();

        return Inertia::render('Admin/Reports/Compliance', [
            'stats' => $stats,
            'chartData' => $chartData,
            'recentReports' => $recentReports,
            'investigationStats' => $investigationStats,
            'recentActivities' => $recentActivities,
        ]);
    }
    
    private function getActivityTrends()
    {
        return [
            'brokers' => [
                '2024-01-01' => 10,
                '2024-01-02' => 12,
                '2024-01-03' => 15,
            ],
            'properties' => [
                '2024-01-01' => 25,
                '2024-01-02' => 30,
                '2024-01-03' => 28,
            ],
            'inquiries' => [
                '2024-01-01' => 45,
                '2024-01-02' => 52,
                '2024-01-03' => 48,
            ],
        ];
    }

    private function getRecentActivities()
    {
        $activities = collect();
        
        // Recent broker registrations
        $recentBrokers = User::where('role', 'broker')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($broker) {
                return [
                    'type' => 'broker_registration',
                    'description' => "New broker registered: {$broker->name}",
                    'created_at' => $broker->created_at,
                    'user' => $broker
                ];
            });
            
        // Recent property listings
        $recentProperties = Property::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($property) {
                return [
                    'type' => 'property_listing',
                    'description' => "New property listed: {$property->title}",
                    'created_at' => $property->created_at,
                    'property' => $property
                ];
            });
            
        // Recent inquiries
        $recentInquiries = Inquiry::with(['user', 'property'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($inquiry) {
                return [
                    'type' => 'inquiry',
                    'description' => "New inquiry from {$inquiry->user->name}",
                    'created_at' => $inquiry->created_at,
                    'inquiry' => $inquiry
                ];
            });
        
        return $activities->merge($recentBrokers)
            ->merge($recentProperties)
            ->merge($recentInquiries)
            ->sortByDesc('created_at')
            ->take(15)
            ->values();
    }

    private function getTopPerformingBrokers()
    {
        return User::where('role', 'broker')
            ->withCount(['properties', 'inquiries'])
            ->orderBy('properties_count', 'desc')
            ->limit(5)
            ->get();
    }

    private function getMostInquiredProperties()
    {
        return Property::withCount('inquiries')
            ->orderBy('inquiries_count', 'desc')
            ->limit(5)
            ->get();
    }

    private function getBrokerStats()
    {
        return [
            'total_brokers' => User::where('role', 'broker')->count(),
            'active_brokers' => User::where('role', 'broker')->where('status', 'active')->count(),
            'pending_brokers' => User::where('role', 'broker')->where('status', 'pending')->count(),
            'avg_response_rate' => 85.5,
        ];
    }

    private function getBrokerChartData()
    {
        return [
            'registrations' => [
                '2024-01-01' => 2,
                '2024-01-02' => 3,
                '2024-01-03' => 1,
            ],
            'performance' => [
                'excellent' => 15,
                'good' => 25,
                'average' => 10,
                'poor' => 5,
            ],
        ];
    }

    private function getRecentBrokerActivities()
    {
        return [
            [
                'type' => 'broker_registration',
                'description' => 'New broker registered: Maria Santos',
                'timestamp' => now()->subHours(1),
            ],
            [
                'type' => 'broker_approval',
                'description' => 'Broker approved: Juan Cruz',
                'timestamp' => now()->subHours(3),
            ],
        ];
    }

    private function getPropertyStats()
    {
        return [
            'total_properties' => Property::count(),
            'active_properties' => Property::where('status', 'active')->count(),
            'avg_price' => Property::where('status', 'active')->avg('price') ?? 0,
            'total_views' => 15420,
        ];
    }

    private function getPropertyChartData()
    {
        return [
            'types' => [
                'house' => 45,
                'condo' => 30,
                'lot' => 25,
            ],
            'price_ranges' => [
                '0-1M' => 20,
                '1M-5M' => 35,
                '5M-10M' => 25,
                '10M+' => 20,
            ],
            'listings' => [
                '2024-01-01' => 5,
                '2024-01-02' => 8,
                '2024-01-03' => 6,
            ],
        ];
    }

    private function getRecentPropertyActivities()
    {
        return [
            [
                'type' => 'property_listing',
                'description' => 'New property listed: 3BR House in Tagbilaran',
                'timestamp' => now()->subHours(2),
            ],
            [
                'type' => 'property_update',
                'description' => 'Property updated: Condo in Panglao',
                'timestamp' => now()->subHours(4),
            ],
        ];
    }

    private function getInquiryStats()
    {
        return [
            'total_inquiries' => Inquiry::count(),
            'pending_inquiries' => Inquiry::where('status', 'pending')->count(),
            'responded_inquiries' => Inquiry::where('status', 'responded')->count(),
            'response_rate' => $this->calculateResponseRate(),
        ];
    }

    private function getInquiryChartData()
    {
        return [
            'status' => [
                'pending' => 25,
                'responded' => 45,
                'closed' => 30,
            ],
            'response_times' => [
                '< 1 hour' => 40,
                '1-6 hours' => 35,
                '6-24 hours' => 20,
                '> 24 hours' => 5,
            ],
            'trends' => [
                '2024-01-01' => 12,
                '2024-01-02' => 18,
                '2024-01-03' => 15,
            ],
        ];
    }

    private function getRecentInquiryActivities()
    {
        return [
            [
                'type' => 'inquiry_received',
                'description' => 'New inquiry for Property #456',
                'timestamp' => now()->subMinutes(30),
            ],
            [
                'type' => 'inquiry_responded',
                'description' => 'Inquiry responded for Property #123',
                'timestamp' => now()->subHours(1),
            ],
        ];
    }

    private function getComplianceStats()
    {
        return [
            'total_reports' => ComplianceReport::count(),
            'under_review' => ComplianceReport::where('status', 'under_review')->count(),
            'resolved' => ComplianceReport::where('status', 'resolved')->count(),
            'avg_resolution_time' => $this->calculateAvgResolutionTime(),
            'report_types' => [
                'inappropriate_content' => 15,
                'spam' => 8,
                'fraud' => 5,
                'harassment' => 3,
                'fake_listing' => 12,
                'other' => 7,
            ],
            'severity_distribution' => [
                'low' => 20,
                'medium' => 18,
                'high' => 10,
                'critical' => 2,
            ],
        ];
    }

    private function getComplianceChartData()
    {
        return [
            'reports' => [
                '2024-01-01' => 3,
                '2024-01-02' => 5,
                '2024-01-03' => 2,
            ],
        ];
    }

    private function getRecentComplianceReports()
    {
        return ComplianceReport::with(['reporter', 'assignedAdmin'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    private function getInvestigationStats()
    {
        return [
            'total_investigations' => 25,
            'evidence_collected' => 18,
            'interviews_conducted' => 12,
        ];
    }

    private function getRecentInvestigationActivities()
    {
        return [
            [
                'type' => 'evidence_collected',
                'description' => 'Evidence collected for Report #123',
                'timestamp' => now()->subHours(2),
            ],
            [
                'type' => 'interview_conducted',
                'description' => 'Interview conducted with broker',
                'timestamp' => now()->subHours(4),
            ],
        ];
    }

    private function calculateResponseRate()
    {
        $totalInquiries = Inquiry::count();
        $respondedInquiries = Inquiry::where('status', 'responded')->count();
        
        return $totalInquiries > 0 ? round(($respondedInquiries / $totalInquiries) * 100, 2) : 0;
    }

    private function calculateAvgResolutionTime()
    {
        $resolvedReports = ComplianceReport::whereNotNull('resolved_at')->get();
        
        if ($resolvedReports->isEmpty()) {
            return '0 days';
        }
        
        $totalHours = $resolvedReports->sum(function ($report) {
            return $report->created_at->diffInHours($report->resolved_at);
        });
        
        $avgHours = $totalHours / $resolvedReports->count();
        $avgDays = round($avgHours / 24, 1);
        
        return $avgDays . ' days';
    }
}