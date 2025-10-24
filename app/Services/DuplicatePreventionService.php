<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\Client;
use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DuplicatePreventionService
{
    // Configuration constants
    const DUPLICATE_WINDOW_HOURS = 24;
    const SIMILARITY_THRESHOLD = 0.8;
    const MAX_INQUIRIES_PER_CLIENT_PER_DAY = 5;
    const MAX_INQUIRIES_PER_IP_PER_HOUR = 3;

    /**
     * Check if an inquiry is a duplicate
     */
    public function isDuplicate(array $inquiryData): array
    {
        $duplicateChecks = [
            'exact_match' => $this->checkExactDuplicate($inquiryData),
            'similar_content' => $this->checkSimilarContent($inquiryData),
            'client_frequency' => $this->checkClientFrequency($inquiryData),
            'ip_frequency' => $this->checkIpFrequency($inquiryData),
            'property_spam' => $this->checkPropertySpam($inquiryData)
        ];

        $isDuplicate = collect($duplicateChecks)->contains(function ($check) {
            return $check['is_duplicate'];
        });

        return [
            'is_duplicate' => $isDuplicate,
            'checks' => $duplicateChecks,
            'action' => $this->determineAction($duplicateChecks)
        ];
    }

    /**
     * Check for exact duplicate inquiries
     */
    protected function checkExactDuplicate(array $data): array
    {
        $timeWindow = Carbon::now()->subHours(self::DUPLICATE_WINDOW_HOURS);
        
        $query = Inquiry::where('created_at', '>=', $timeWindow);

        // Check by email if provided
        if (!empty($data['email'])) {
            $query->where('email', $data['email']);
        }

        // Check by phone if provided
        if (!empty($data['phone'])) {
            $query->orWhere('phone', $data['phone']);
        }

        // Check by property and message content
        if (!empty($data['property_id']) && !empty($data['message'])) {
            $query->orWhere(function ($q) use ($data) {
                $q->where('property_id', $data['property_id'])
                  ->where('message', $data['message']);
            });
        }

        $existingInquiry = $query->first();

        return [
            'is_duplicate' => $existingInquiry !== null,
            'reason' => $existingInquiry ? 'Exact match found within ' . self::DUPLICATE_WINDOW_HOURS . ' hours' : null,
            'existing_inquiry_id' => $existingInquiry?->id,
            'severity' => 'high'
        ];
    }

    /**
     * Check for similar content using text similarity
     */
    protected function checkSimilarContent(array $data): array
    {
        if (empty($data['message']) || empty($data['property_id'])) {
            return ['is_duplicate' => false, 'reason' => null, 'severity' => 'low'];
        }

        $timeWindow = Carbon::now()->subHours(self::DUPLICATE_WINDOW_HOURS * 2);
        
        $recentInquiries = Inquiry::where('property_id', $data['property_id'])
            ->where('created_at', '>=', $timeWindow)
            ->get(['id', 'message', 'email', 'phone']);

        foreach ($recentInquiries as $inquiry) {
            $similarity = $this->calculateTextSimilarity($data['message'], $inquiry->message);
            
            if ($similarity >= self::SIMILARITY_THRESHOLD) {
                // Additional check for same contact info
                $sameContact = ($data['email'] === $inquiry->email) || 
                              ($data['phone'] === $inquiry->phone);
                
                if ($sameContact) {
                    return [
                        'is_duplicate' => true,
                        'reason' => "Similar content ({$similarity}% match) from same contact",
                        'existing_inquiry_id' => $inquiry->id,
                        'similarity_score' => $similarity,
                        'severity' => 'medium'
                    ];
                }
            }
        }

        return ['is_duplicate' => false, 'reason' => null, 'severity' => 'low'];
    }

    /**
     * Check client inquiry frequency
     */
    protected function checkClientFrequency(array $data): array
    {
        if (empty($data['email']) && empty($data['phone'])) {
            return ['is_duplicate' => false, 'reason' => null, 'severity' => 'low'];
        }

        $today = Carbon::now()->startOfDay();
        
        $query = Inquiry::where('created_at', '>=', $today);
        
        if (!empty($data['email'])) {
            $query->where('email', $data['email']);
        } elseif (!empty($data['phone'])) {
            $query->where('phone', $data['phone']);
        }

        $todayCount = $query->count();

        $isExceeded = $todayCount >= self::MAX_INQUIRIES_PER_CLIENT_PER_DAY;

        return [
            'is_duplicate' => $isExceeded,
            'reason' => $isExceeded ? "Client exceeded daily limit ({$todayCount}/" . self::MAX_INQUIRIES_PER_CLIENT_PER_DAY . ")" : null,
            'count' => $todayCount,
            'limit' => self::MAX_INQUIRIES_PER_CLIENT_PER_DAY,
            'severity' => 'medium'
        ];
    }

    /**
     * Check IP address frequency
     */
    protected function checkIpFrequency(array $data): array
    {
        if (empty($data['ip_address'])) {
            return ['is_duplicate' => false, 'reason' => null, 'severity' => 'low'];
        }

        $oneHourAgo = Carbon::now()->subHour();
        
        $hourlyCount = Inquiry::where('ip_address', $data['ip_address'])
            ->where('created_at', '>=', $oneHourAgo)
            ->count();

        $isExceeded = $hourlyCount >= self::MAX_INQUIRIES_PER_IP_PER_HOUR;

        return [
            'is_duplicate' => $isExceeded,
            'reason' => $isExceeded ? "IP exceeded hourly limit ({$hourlyCount}/" . self::MAX_INQUIRIES_PER_IP_PER_HOUR . ")" : null,
            'count' => $hourlyCount,
            'limit' => self::MAX_INQUIRIES_PER_IP_PER_HOUR,
            'severity' => 'high'
        ];
    }

    /**
     * Check for property-specific spam patterns
     */
    protected function checkPropertySpam(array $data): array
    {
        if (empty($data['property_id'])) {
            return ['is_duplicate' => false, 'reason' => null, 'severity' => 'low'];
        }

        $oneHourAgo = Carbon::now()->subHour();
        
        // Check for multiple inquiries on same property from different contacts
        $recentInquiries = Inquiry::where('property_id', $data['property_id'])
            ->where('created_at', '>=', $oneHourAgo)
            ->get(['email', 'phone', 'message']);

        // Look for suspicious patterns
        $uniqueContacts = $recentInquiries->unique(function ($inquiry) {
            return $inquiry->email . '|' . $inquiry->phone;
        })->count();

        $totalInquiries = $recentInquiries->count();

        // If more than 5 inquiries from different contacts in 1 hour, flag as suspicious
        if ($totalInquiries > 5 && $uniqueContacts > 3) {
            return [
                'is_duplicate' => true,
                'reason' => "Suspicious activity on property ({$totalInquiries} inquiries from {$uniqueContacts} contacts in 1 hour)",
                'inquiry_count' => $totalInquiries,
                'unique_contacts' => $uniqueContacts,
                'severity' => 'high'
            ];
        }

        return ['is_duplicate' => false, 'reason' => null, 'severity' => 'low'];
    }

    /**
     * Calculate text similarity between two strings
     */
    protected function calculateTextSimilarity(string $text1, string $text2): float
    {
        // Normalize texts
        $text1 = strtolower(trim($text1));
        $text2 = strtolower(trim($text2));

        if ($text1 === $text2) {
            return 1.0;
        }

        // Use Levenshtein distance for similarity calculation
        $maxLength = max(strlen($text1), strlen($text2));
        
        if ($maxLength === 0) {
            return 1.0;
        }

        $distance = levenshtein($text1, $text2);
        return 1 - ($distance / $maxLength);
    }

    /**
     * Determine action based on duplicate checks
     */
    protected function determineAction(array $checks): string
    {
        $highSeverityDuplicate = collect($checks)->first(function ($check) {
            return $check['is_duplicate'] && ($check['severity'] ?? 'low') === 'high';
        });

        if ($highSeverityDuplicate) {
            return 'block';
        }

        $mediumSeverityDuplicate = collect($checks)->first(function ($check) {
            return $check['is_duplicate'] && ($check['severity'] ?? 'low') === 'medium';
        });

        if ($mediumSeverityDuplicate) {
            return 'flag';
        }

        return 'allow';
    }

    /**
     * Log duplicate attempt for monitoring
     */
    public function logDuplicateAttempt(array $inquiryData, array $duplicateResult): void
    {
        Log::warning('Duplicate inquiry attempt detected', [
            'inquiry_data' => [
                'email' => $inquiryData['email'] ?? null,
                'phone' => $inquiryData['phone'] ?? null,
                'property_id' => $inquiryData['property_id'] ?? null,
                'ip_address' => $inquiryData['ip_address'] ?? null,
            ],
            'duplicate_checks' => $duplicateResult['checks'],
            'action' => $duplicateResult['action'],
            'timestamp' => now()
        ]);
    }

    /**
     * Get duplicate statistics for monitoring
     */
    public function getDuplicateStats(int $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days);

        return [
            'total_inquiries' => Inquiry::where('created_at', '>=', $startDate)->count(),
            'flagged_duplicates' => Inquiry::where('created_at', '>=', $startDate)
                ->where('is_flagged', true)->count(),
            'blocked_attempts' => DB::table('duplicate_logs')
                ->where('created_at', '>=', $startDate)
                ->where('action', 'block')->count(),
            'top_duplicate_properties' => $this->getTopDuplicateProperties($startDate),
            'top_duplicate_ips' => $this->getTopDuplicateIps($startDate)
        ];
    }

    /**
     * Get properties with most duplicate attempts
     */
    protected function getTopDuplicateProperties(Carbon $startDate): array
    {
        return Inquiry::where('created_at', '>=', $startDate)
            ->where('is_flagged', true)
            ->select('property_id', DB::raw('COUNT(*) as duplicate_count'))
            ->groupBy('property_id')
            ->orderByDesc('duplicate_count')
            ->limit(10)
            ->with('property:id,title,location')
            ->get()
            ->toArray();
    }

    /**
     * Get IP addresses with most duplicate attempts
     */
    protected function getTopDuplicateIps(Carbon $startDate): array
    {
        return Inquiry::where('created_at', '>=', $startDate)
            ->whereNotNull('ip_address')
            ->select('ip_address', DB::raw('COUNT(*) as inquiry_count'))
            ->groupBy('ip_address')
            ->having('inquiry_count', '>', 5)
            ->orderByDesc('inquiry_count')
            ->limit(10)
            ->get()
            ->toArray();
    }
}