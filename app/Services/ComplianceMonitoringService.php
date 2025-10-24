<?php

namespace App\Services;

use App\Models\ComplianceReport;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ComplianceMonitoringService
{
    /**
     * Suspicious keywords that may indicate fraudulent or inappropriate content
     */
    private const SUSPICIOUS_KEYWORDS = [
        'scam', 'fraud', 'fake', 'money laundering', 'illegal', 'stolen',
        'urgent cash', 'quick money', 'guaranteed profit', 'no questions asked',
        'under the table', 'off the books', 'tax free', 'cash only deal',
        'bypass regulations', 'avoid taxes', 'hidden fees', 'secret deal'
    ];

    /**
     * Price anomaly thresholds
     */
    private const PRICE_ANOMALY_THRESHOLD = 0.5; // 50% below market average
    private const HIGH_PRICE_THRESHOLD = 50000000; // 50M PHP

    /**
     * Monitor a property for compliance issues
     */
    public function monitorProperty(Property $property): array
    {
        $issues = [];

        // Check for suspicious content in description
        $suspiciousContent = $this->checkSuspiciousContent($property->description);
        if (!empty($suspiciousContent)) {
            $issues[] = [
                'type' => 'suspicious_content',
                'severity' => 'medium',
                'description' => 'Property description contains suspicious keywords: ' . implode(', ', $suspiciousContent),
                'evidence' => ['keywords' => $suspiciousContent]
            ];
        }

        // Check for price anomalies
        $priceIssue = $this->checkPriceAnomalies($property);
        if ($priceIssue) {
            $issues[] = $priceIssue;
        }

        // Check for incomplete or suspicious property details
        $detailsIssue = $this->checkPropertyDetails($property);
        if ($detailsIssue) {
            $issues[] = $detailsIssue;
        }

        // Check for duplicate listings
        $duplicateIssue = $this->checkDuplicateProperty($property);
        if ($duplicateIssue) {
            $issues[] = $duplicateIssue;
        }

        // Auto-create compliance reports for high severity issues
        foreach ($issues as $issue) {
            if (in_array($issue['severity'], ['high', 'critical'])) {
                $this->createComplianceReport($property, $issue);
            }
        }

        return $issues;
    }

    /**
     * Monitor an inquiry for compliance issues
     */
    public function monitorInquiry(Inquiry $inquiry): array
    {
        $issues = [];

        // Check for suspicious content in message
        $suspiciousContent = $this->checkSuspiciousContent($inquiry->message);
        if (!empty($suspiciousContent)) {
            $issues[] = [
                'type' => 'suspicious_content',
                'severity' => 'medium',
                'description' => 'Inquiry message contains suspicious keywords: ' . implode(', ', $suspiciousContent),
                'evidence' => ['keywords' => $suspiciousContent]
            ];
        }

        // Check for spam patterns
        $spamIssue = $this->checkSpamPatterns($inquiry);
        if ($spamIssue) {
            $issues[] = $spamIssue;
        }

        // Check for contact information in message (potential bypass)
        $contactBypassIssue = $this->checkContactBypass($inquiry->message);
        if ($contactBypassIssue) {
            $issues[] = $contactBypassIssue;
        }

        // Auto-create compliance reports for high severity issues
        foreach ($issues as $issue) {
            if (in_array($issue['severity'], ['high', 'critical'])) {
                $this->createComplianceReport($inquiry, $issue);
            }
        }

        return $issues;
    }

    /**
     * Monitor user activity for suspicious patterns
     */
    public function monitorUserActivity(User $user): array
    {
        $issues = [];

        // Check for excessive property listings in short time
        $excessiveListings = $this->checkExcessiveListings($user);
        if ($excessiveListings) {
            $issues[] = $excessiveListings;
        }

        // Check for rapid inquiry patterns
        $rapidInquiries = $this->checkRapidInquiries($user);
        if ($rapidInquiries) {
            $issues[] = $rapidInquiries;
        }

        // Check for incomplete profile information
        $incompleteProfile = $this->checkIncompleteProfile($user);
        if ($incompleteProfile) {
            $issues[] = $incompleteProfile;
        }

        // Auto-create compliance reports for high severity issues
        foreach ($issues as $issue) {
            if (in_array($issue['severity'], ['high', 'critical'])) {
                $this->createComplianceReport($user, $issue);
            }
        }

        return $issues;
    }

    /**
     * Check for suspicious keywords in content
     */
    private function checkSuspiciousContent(string $content): array
    {
        $foundKeywords = [];
        $lowerContent = strtolower($content);

        foreach (self::SUSPICIOUS_KEYWORDS as $keyword) {
            if (strpos($lowerContent, strtolower($keyword)) !== false) {
                $foundKeywords[] = $keyword;
            }
        }

        return $foundKeywords;
    }

    /**
     * Check for price anomalies in property
     */
    private function checkPriceAnomalies(Property $property): ?array
    {
        if (!$property->price) {
            return null;
        }

        // Check for unrealistically high prices
        if ($property->price > self::HIGH_PRICE_THRESHOLD) {
            return [
                'type' => 'price_anomaly',
                'severity' => 'medium',
                'description' => 'Property price is unusually high: ₱' . number_format($property->price),
                'evidence' => ['price' => $property->price, 'threshold' => self::HIGH_PRICE_THRESHOLD]
            ];
        }

        // Check for unrealistically low prices (potential scam)
        $averagePrice = Property::where('property_type', $property->property_type)
            ->where('city', $property->city)
            ->avg('price');

        if ($averagePrice && $property->price < ($averagePrice * self::PRICE_ANOMALY_THRESHOLD)) {
            return [
                'type' => 'price_anomaly',
                'severity' => 'high',
                'description' => 'Property price is significantly below market average',
                'evidence' => [
                    'property_price' => $property->price,
                    'market_average' => $averagePrice,
                    'percentage_below' => round((1 - ($property->price / $averagePrice)) * 100, 2)
                ]
            ];
        }

        return null;
    }

    /**
     * Check property details for completeness and accuracy
     */
    private function checkPropertyDetails(Property $property): ?array
    {
        $missingFields = [];

        // Check for essential missing information
        if (empty($property->description) || strlen($property->description) < 50) {
            $missingFields[] = 'description';
        }

        if (empty($property->address)) {
            $missingFields[] = 'address';
        }

        if (!$property->images || count($property->images) < 3) {
            $missingFields[] = 'images';
        }

        if (!empty($missingFields)) {
            return [
                'type' => 'incomplete_listing',
                'severity' => 'low',
                'description' => 'Property listing is missing essential information: ' . implode(', ', $missingFields),
                'evidence' => ['missing_fields' => $missingFields]
            ];
        }

        return null;
    }

    /**
     * Check for duplicate property listings
     */
    private function checkDuplicateProperty(Property $property): ?array
    {
        $duplicates = Property::where('id', '!=', $property->id)
            ->where('address', $property->address)
            ->where('property_type', $property->property_type)
            ->whereBetween('price', [$property->price * 0.95, $property->price * 1.05])
            ->count();

        if ($duplicates > 0) {
            return [
                'type' => 'duplicate_listing',
                'severity' => 'medium',
                'description' => "Potential duplicate listing detected ({$duplicates} similar properties found)",
                'evidence' => ['duplicate_count' => $duplicates]
            ];
        }

        return null;
    }

    /**
     * Check for spam patterns in inquiries
     */
    private function checkSpamPatterns(Inquiry $inquiry): ?array
    {
        // Check for very short or generic messages
        if (strlen($inquiry->message) < 10) {
            return [
                'type' => 'spam_pattern',
                'severity' => 'low',
                'description' => 'Inquiry message is too short and may be spam',
                'evidence' => ['message_length' => strlen($inquiry->message)]
            ];
        }

        // Check for repeated inquiries from same user
        $recentInquiries = Inquiry::where('user_id', $inquiry->user_id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentInquiries > 10) {
            return [
                'type' => 'spam_pattern',
                'severity' => 'high',
                'description' => 'User has sent excessive inquiries in short time period',
                'evidence' => ['inquiry_count' => $recentInquiries, 'timeframe' => '1 hour']
            ];
        }

        return null;
    }

    /**
     * Check for contact information bypass attempts
     */
    private function checkContactBypass(string $message): ?array
    {
        $patterns = [
            '/\b\d{11}\b/', // 11-digit phone numbers
            '/\b\d{4}[-\s]\d{3}[-\s]\d{4}\b/', // Formatted phone numbers
            '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', // Email addresses
            '/\b(facebook|fb|messenger|whatsapp|viber|telegram)\b/i', // Social media mentions
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return [
                    'type' => 'contact_bypass',
                    'severity' => 'medium',
                    'description' => 'Inquiry contains contact information, potentially bypassing platform communication',
                    'evidence' => ['pattern_matched' => $pattern]
                ];
            }
        }

        return null;
    }

    /**
     * Check for excessive property listings
     */
    private function checkExcessiveListings(User $user): ?array
    {
        $recentListings = Property::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($recentListings > 20) {
            return [
                'type' => 'excessive_activity',
                'severity' => 'medium',
                'description' => 'User has created excessive property listings in short time period',
                'evidence' => ['listing_count' => $recentListings, 'timeframe' => '24 hours']
            ];
        }

        return null;
    }

    /**
     * Check for rapid inquiry patterns
     */
    private function checkRapidInquiries(User $user): ?array
    {
        $recentInquiries = Inquiry::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentInquiries > 15) {
            return [
                'type' => 'excessive_activity',
                'severity' => 'high',
                'description' => 'User has sent excessive inquiries in short time period',
                'evidence' => ['inquiry_count' => $recentInquiries, 'timeframe' => '1 hour']
            ];
        }

        return null;
    }

    /**
     * Check for incomplete user profile
     */
    private function checkIncompleteProfile(User $user): ?array
    {
        if ($user->role === 'broker') {
            $missingFields = [];

            if (empty($user->prc_id)) {
                $missingFields[] = 'PRC ID';
            }

            if (empty($user->business_permit)) {
                $missingFields[] = 'Business Permit';
            }

            if (!empty($missingFields)) {
                return [
                    'type' => 'incomplete_profile',
                    'severity' => 'medium',
                    'description' => 'Broker profile is missing required verification documents: ' . implode(', ', $missingFields),
                    'evidence' => ['missing_documents' => $missingFields]
                ];
            }
        }

        return null;
    }

    /**
     * Create a compliance report for detected issues
     */
    private function createComplianceReport($reportable, array $issue): ComplianceReport
    {
        return ComplianceReport::create([
            'reportable_type' => get_class($reportable),
            'reportable_id' => $reportable->id,
            'report_type' => $issue['type'],
            'severity' => $issue['severity'],
            'status' => 'pending',
            'description' => $issue['description'],
            'evidence' => $issue['evidence'] ?? [],
            'reported_by' => null, // System-generated
            'reported_at' => now(),
        ]);
    }

    /**
     * Run comprehensive compliance check on all recent content
     */
    public function runComplianceCheck(): array
    {
        $results = [
            'properties_checked' => 0,
            'inquiries_checked' => 0,
            'users_checked' => 0,
            'issues_found' => 0,
            'reports_created' => 0,
        ];

        // Check recent properties
        $recentProperties = Property::where('created_at', '>=', now()->subDay())->get();
        foreach ($recentProperties as $property) {
            $issues = $this->monitorProperty($property);
            $results['properties_checked']++;
            $results['issues_found'] += count($issues);
            $results['reports_created'] += count(array_filter($issues, fn($issue) => in_array($issue['severity'], ['high', 'critical'])));
        }

        // Check recent inquiries
        $recentInquiries = Inquiry::where('created_at', '>=', now()->subDay())->get();
        foreach ($recentInquiries as $inquiry) {
            $issues = $this->monitorInquiry($inquiry);
            $results['inquiries_checked']++;
            $results['issues_found'] += count($issues);
            $results['reports_created'] += count(array_filter($issues, fn($issue) => in_array($issue['severity'], ['high', 'critical'])));
        }

        // Check active users
        $activeUsers = User::where('last_login_at', '>=', now()->subWeek())->get();
        foreach ($activeUsers as $user) {
            $issues = $this->monitorUserActivity($user);
            $results['users_checked']++;
            $results['issues_found'] += count($issues);
            $results['reports_created'] += count(array_filter($issues, fn($issue) => in_array($issue['severity'], ['high', 'critical'])));
        }

        Log::info('Compliance monitoring completed', $results);

        return $results;
    }
}