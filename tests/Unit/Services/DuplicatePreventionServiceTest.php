<?php

namespace Tests\Unit\Services;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Client;
use App\Services\DuplicatePreventionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DuplicatePreventionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DuplicatePreventionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DuplicatePreventionService();
    }

    /** @test */
    public function it_detects_exact_duplicate_inquiries()
    {
        $property = Property::factory()->create();
        
        // Create original inquiry
        $originalInquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'email' => 'test@example.com',
            'message' => 'I am interested in this property',
            'created_at' => now()->subMinutes(30)
        ]);

        // Create duplicate data
        $duplicateData = [
            'property_id' => $property->id,
            'email' => 'test@example.com',
            'message' => 'I am interested in this property',
            'phone' => null,
            'name' => 'John Doe'
        ];

        $result = $this->service->checkForDuplicates($duplicateData);

        $this->assertTrue($result['is_duplicate']);
        $this->assertEquals('exact_match', $result['duplicate_type']);
        $this->assertEquals($originalInquiry->id, $result['original_inquiry_id']);
        $this->assertEquals('reject', $result['action']);
    }

    /** @test */
    public function it_detects_similar_content_duplicates()
    {
        $property = Property::factory()->create();
        
        // Create original inquiry
        Inquiry::factory()->create([
            'property_id' => $property->id,
            'email' => 'test@example.com',
            'message' => 'I am very interested in this beautiful property',
            'created_at' => now()->subMinutes(30)
        ]);

        // Create similar content
        $duplicateData = [
            'property_id' => $property->id,
            'email' => 'test@example.com',
            'message' => 'I am really interested in this nice property',
            'phone' => null,
            'name' => 'John Doe'
        ];

        $result = $this->service->checkForDuplicates($duplicateData);

        $this->assertTrue($result['is_duplicate']);
        $this->assertEquals('similar_content', $result['duplicate_type']);
        $this->assertEquals('flag', $result['action']);
    }

    /** @test */
    public function it_detects_client_frequency_violations()
    {
        $property1 = Property::factory()->create();
        $property2 = Property::factory()->create();
        
        // Create multiple inquiries from same client within time window
        Inquiry::factory()->count(3)->create([
            'property_id' => $property1->id,
            'email' => 'frequent@example.com',
            'created_at' => now()->subMinutes(30)
        ]);

        // New inquiry from same client
        $duplicateData = [
            'property_id' => $property2->id,
            'email' => 'frequent@example.com',
            'message' => 'Another inquiry from same client',
            'phone' => null,
            'name' => 'Frequent User'
        ];

        $result = $this->service->checkForDuplicates($duplicateData);

        $this->assertTrue($result['is_duplicate']);
        $this->assertEquals('client_frequency', $result['duplicate_type']);
        $this->assertEquals('flag', $result['action']);
    }

    /** @test */
    public function it_detects_ip_frequency_violations()
    {
        $property1 = Property::factory()->create();
        $property2 = Property::factory()->create();
        
        // Create multiple inquiries from same IP within time window
        Inquiry::factory()->count(6)->create([
            'property_id' => $property1->id,
            'ip_address' => '192.168.1.100',
            'created_at' => now()->subMinutes(30)
        ]);

        // New inquiry from same IP
        $duplicateData = [
            'property_id' => $property2->id,
            'email' => 'different@example.com',
            'message' => 'Inquiry from same IP',
            'ip_address' => '192.168.1.100',
            'phone' => null,
            'name' => 'Different User'
        ];

        $result = $this->service->checkForDuplicates($duplicateData);

        $this->assertTrue($result['is_duplicate']);
        $this->assertEquals('ip_frequency', $result['duplicate_type']);
        $this->assertEquals('reject', $result['action']);
    }

    /** @test */
    public function it_detects_property_spam()
    {
        $property = Property::factory()->create();
        
        // Create multiple inquiries for same property within time window
        Inquiry::factory()->count(11)->create([
            'property_id' => $property->id,
            'created_at' => now()->subMinutes(30)
        ]);

        // New inquiry for same property
        $duplicateData = [
            'property_id' => $property->id,
            'email' => 'newuser@example.com',
            'message' => 'Inquiry for popular property',
            'phone' => null,
            'name' => 'New User'
        ];

        $result = $this->service->checkForDuplicates($duplicateData);

        $this->assertTrue($result['is_duplicate']);
        $this->assertEquals('property_spam', $result['duplicate_type']);
        $this->assertEquals('flag', $result['action']);
    }

    /** @test */
    public function it_allows_non_duplicate_inquiries()
    {
        $property = Property::factory()->create();
        
        // Create original inquiry
        Inquiry::factory()->create([
            'property_id' => $property->id,
            'email' => 'original@example.com',
            'message' => 'Original inquiry message',
            'created_at' => now()->subHours(2)
        ]);

        // Create different inquiry
        $newData = [
            'property_id' => $property->id,
            'email' => 'different@example.com',
            'message' => 'Completely different inquiry message',
            'phone' => '+1234567890',
            'name' => 'Different User'
        ];

        $result = $this->service->checkForDuplicates($newData);

        $this->assertFalse($result['is_duplicate']);
        $this->assertEquals('allow', $result['action']);
    }

    /** @test */
    public function it_ignores_old_inquiries_outside_time_window()
    {
        $property = Property::factory()->create();
        
        // Create old inquiry outside time window
        Inquiry::factory()->create([
            'property_id' => $property->id,
            'email' => 'test@example.com',
            'message' => 'I am interested in this property',
            'created_at' => now()->subDays(2)
        ]);

        // Create new inquiry with same content
        $newData = [
            'property_id' => $property->id,
            'email' => 'test@example.com',
            'message' => 'I am interested in this property',
            'phone' => null,
            'name' => 'John Doe'
        ];

        $result = $this->service->checkForDuplicates($newData);

        $this->assertFalse($result['is_duplicate']);
        $this->assertEquals('allow', $result['action']);
    }

    /** @test */
    public function it_calculates_content_similarity_correctly()
    {
        $text1 = 'I am very interested in this beautiful property for sale';
        $text2 = 'I am really interested in this nice property for purchase';
        
        $similarity = $this->service->calculateContentSimilarity($text1, $text2);
        
        $this->assertGreaterThan(0.7, $similarity);
        $this->assertLessThan(1.0, $similarity);
    }

    /** @test */
    public function it_logs_duplicate_attempts()
    {
        $property = Property::factory()->create();
        
        // Create original inquiry
        $originalInquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'email' => 'test@example.com',
            'message' => 'Original message',
            'created_at' => now()->subMinutes(30)
        ]);

        // Attempt duplicate
        $duplicateData = [
            'property_id' => $property->id,
            'email' => 'test@example.com',
            'message' => 'Original message',
            'ip_address' => '192.168.1.100',
            'phone' => null,
            'name' => 'John Doe'
        ];

        $this->service->logDuplicateAttempt($duplicateData, [
            'is_duplicate' => true,
            'duplicate_type' => 'exact_match',
            'original_inquiry_id' => $originalInquiry->id,
            'action' => 'reject'
        ]);

        // Check that log was created
        $this->assertDatabaseHas('duplicate_logs', [
            'email' => 'test@example.com',
            'property_id' => $property->id,
            'duplicate_type' => 'exact_match',
            'action_taken' => 'reject',
            'original_inquiry_id' => $originalInquiry->id
        ]);
    }

    /** @test */
    public function it_generates_duplicate_statistics()
    {
        $property = Property::factory()->create();
        
        // Create some duplicate logs
        \DB::table('duplicate_logs')->insert([
            [
                'email' => 'test1@example.com',
                'property_id' => $property->id,
                'duplicate_type' => 'exact_match',
                'action_taken' => 'reject',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1)
            ],
            [
                'email' => 'test2@example.com',
                'property_id' => $property->id,
                'duplicate_type' => 'similar_content',
                'action_taken' => 'flag',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2)
            ],
            [
                'email' => 'test3@example.com',
                'property_id' => $property->id,
                'duplicate_type' => 'client_frequency',
                'action_taken' => 'flag',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3)
            ]
        ]);

        $stats = $this->service->getDuplicateStatistics(7);

        $this->assertEquals(3, $stats['total_duplicates']);
        $this->assertEquals(1, $stats['by_type']['exact_match']);
        $this->assertEquals(1, $stats['by_type']['similar_content']);
        $this->assertEquals(1, $stats['by_type']['client_frequency']);
        $this->assertEquals(1, $stats['by_action']['reject']);
        $this->assertEquals(2, $stats['by_action']['flag']);
    }

    /** @test */
    public function it_handles_missing_email_gracefully()
    {
        $property = Property::factory()->create();
        
        $duplicateData = [
            'property_id' => $property->id,
            'email' => null,
            'message' => 'Inquiry without email',
            'phone' => '+1234567890',
            'name' => 'Anonymous User'
        ];

        $result = $this->service->checkForDuplicates($duplicateData);

        $this->assertFalse($result['is_duplicate']);
        $this->assertEquals('allow', $result['action']);
    }

    /** @test */
    public function it_handles_missing_message_gracefully()
    {
        $property = Property::factory()->create();
        
        $duplicateData = [
            'property_id' => $property->id,
            'email' => 'test@example.com',
            'message' => null,
            'phone' => '+1234567890',
            'name' => 'Test User'
        ];

        $result = $this->service->checkForDuplicates($duplicateData);

        $this->assertFalse($result['is_duplicate']);
        $this->assertEquals('allow', $result['action']);
    }

    /** @test */
    public function it_respects_custom_time_windows()
    {
        $property = Property::factory()->create();
        
        // Create inquiry just outside custom time window
        Inquiry::factory()->create([
            'property_id' => $property->id,
            'email' => 'test@example.com',
            'message' => 'Test message',
            'created_at' => now()->subMinutes(31)
        ]);

        $duplicateData = [
            'property_id' => $property->id,
            'email' => 'test@example.com',
            'message' => 'Test message',
            'phone' => null,
            'name' => 'Test User'
        ];

        // Use custom 30-minute window
        $result = $this->service->checkForDuplicates($duplicateData, 30);

        $this->assertFalse($result['is_duplicate']);
        $this->assertEquals('allow', $result['action']);
    }
}