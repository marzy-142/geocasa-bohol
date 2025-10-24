<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use App\Models\Client;
use App\Enums\InquiryStatus;
use App\Services\InquiryService;
use App\Services\BrokerAssignmentService;
use App\Services\DuplicatePreventionService;
use App\Notifications\NewInquiryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Carbon\Carbon;

class InquiryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected InquiryService $inquiryService;
    protected User $broker;
    protected Property $property;
    protected array $validInquiryData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inquiryService = app(InquiryService::class);

        // Create test broker
        $this->broker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true,
            'email' => 'broker@test.com'
        ]);

        // Create test property
        $this->property = Property::factory()->create([
            'status' => 'active',
            'broker_id' => $this->broker->id
        ]);

        // Valid inquiry data template
        $this->validInquiryData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'message' => 'I am interested in this property. Please contact me.',
            'property_id' => $this->property->id,
            'inquiry_type' => 'general'
        ];

        Notification::fake();
        Event::fake();
    }

    /** @test */
    public function it_creates_inquiry_successfully_with_valid_data()
    {
        $result = $this->inquiryService->createInquiry($this->validInquiryData);

        $this->assertTrue($result['success']);
        $this->assertInstanceOf(Inquiry::class, $result['inquiry']);
        $this->assertEquals(InquiryStatus::NEW->value, $result['inquiry']->status);
        $this->assertNotNull($result['broker']);
        $this->assertEquals($this->broker->id, $result['broker']->id);

        // Verify inquiry was saved to database
        $this->assertDatabaseHas('inquiries', [
            'email' => 'john@example.com',
            'property_id' => $this->property->id,
            'status' => InquiryStatus::NEW->value
        ]);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $requiredFields = ['name', 'email', 'message', 'property_id'];

        foreach ($requiredFields as $field) {
            $invalidData = $this->validInquiryData;
            unset($invalidData[$field]);

            $result = $this->inquiryService->createInquiry($invalidData);

            $this->assertFalse($result['success']);
            $this->assertEquals('validation', $result['type']);
            $this->assertStringContainsString($field, $result['error']);
        }
    }

    /** @test */
    public function it_validates_email_format()
    {
        $invalidData = $this->validInquiryData;
        $invalidData['email'] = 'invalid-email';

        $result = $this->inquiryService->createInquiry($invalidData);

        $this->assertFalse($result['success']);
        $this->assertEquals('validation', $result['type']);
        $this->assertStringContainsString('valid email', $result['error']);
    }

    /** @test */
    public function it_validates_phone_format()
    {
        $invalidData = $this->validInquiryData;
        $invalidData['phone'] = '123'; // Too short

        $result = $this->inquiryService->createInquiry($invalidData);

        $this->assertFalse($result['success']);
        $this->assertEquals('validation', $result['type']);
        $this->assertStringContainsString('valid phone', $result['error']);
    }

    /** @test */
    public function it_validates_message_length()
    {
        // Test minimum length
        $shortMessageData = $this->validInquiryData;
        $shortMessageData['message'] = 'Short'; // Less than 10 characters

        $result = $this->inquiryService->createInquiry($shortMessageData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('at least 10 characters', $result['error']);

        // Test maximum length
        $longMessageData = $this->validInquiryData;
        $longMessageData['message'] = str_repeat('a', 2001); // More than 2000 characters

        $result = $this->inquiryService->createInquiry($longMessageData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('cannot exceed 2000 characters', $result['error']);
    }

    /** @test */
    public function it_validates_property_exists_and_is_active()
    {
        // Test non-existent property
        $invalidData = $this->validInquiryData;
        $invalidData['property_id'] = 99999;

        $result = $this->inquiryService->createInquiry($invalidData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not available for inquiries', $result['error']);

        // Test inactive property
        $inactiveProperty = Property::factory()->create(['status' => 'inactive']);
        $invalidData['property_id'] = $inactiveProperty->id;

        $result = $this->inquiryService->createInquiry($invalidData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not available for inquiries', $result['error']);
    }

    /** @test */
    public function it_enforces_rate_limiting_by_ip()
    {
        // Create 5 inquiries from same IP (should reach limit)
        for ($i = 0; $i < 5; $i++) {
            $data = $this->validInquiryData;
            $data['email'] = "user{$i}@example.com";
            
            $result = $this->inquiryService->createInquiry($data);
            $this->assertTrue($result['success'], "Inquiry {$i} should succeed");
        }

        // 6th inquiry should fail
        $data = $this->validInquiryData;
        $data['email'] = 'user6@example.com';
        
        $result = $this->inquiryService->createInquiry($data);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Too many inquiries from this location', $result['error']);
    }

    /** @test */
    public function it_enforces_rate_limiting_by_email()
    {
        // Create 3 inquiries from same email (should reach limit)
        for ($i = 0; $i < 3; $i++) {
            $data = $this->validInquiryData;
            $data['property_id'] = Property::factory()->create(['status' => 'active'])->id;
            
            $result = $this->inquiryService->createInquiry($data);
            $this->assertTrue($result['success'], "Inquiry {$i} should succeed");
        }

        // 4th inquiry should fail
        $data = $this->validInquiryData;
        $data['property_id'] = Property::factory()->create(['status' => 'active'])->id;
        
        $result = $this->inquiryService->createInquiry($data);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('daily limit for inquiries', $result['error']);
    }

    /** @test */
    public function it_detects_exact_duplicate_inquiries()
    {
        // Create first inquiry
        $result1 = $this->inquiryService->createInquiry($this->validInquiryData);
        $this->assertTrue($result1['success']);

        // Try to create exact duplicate within 24 hours
        $result2 = $this->inquiryService->createInquiry($this->validInquiryData);
        
        $this->assertFalse($result2['success']);
        $this->assertStringContainsString('Duplicate inquiry detected', $result2['error']);
    }

    /** @test */
    public function it_assigns_broker_automatically()
    {
        $result = $this->inquiryService->createInquiry($this->validInquiryData);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['broker']);
        $this->assertEquals($this->broker->id, $result['broker']->id);
    }

    /** @test */
    public function it_assigns_broker_based_on_workload()
    {
        // Create another broker with less workload
        $lightBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        // Give first broker more workload
        Client::factory()->count(5)->create(['broker_id' => $this->broker->id]);

        $result = $this->inquiryService->createInquiry($this->validInquiryData);

        $this->assertTrue($result['success']);
        // Should assign to broker with less workload
        $this->assertEquals($lightBroker->id, $result['broker']->id);
    }

    /** @test */
    public function it_links_inquiry_to_existing_client()
    {
        // Create existing client
        $existingClient = Client::factory()->create([
            'email' => $this->validInquiryData['email']
        ]);

        $result = $this->inquiryService->createInquiry($this->validInquiryData);

        $this->assertTrue($result['success']);
        
        // Verify inquiry is linked to existing client
        $inquiry = $result['inquiry'];
        $this->assertEquals($existingClient->id, $inquiry->client_id);
    }

    /** @test */
    public function it_sends_notifications_to_relevant_parties()
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $result = $this->inquiryService->createInquiry($this->validInquiryData);

        $this->assertTrue($result['success']);

        // Verify notifications were sent
        Notification::assertSentTo($this->broker, NewInquiryNotification::class);
        Notification::assertSentTo($admin, NewInquiryNotification::class);
    }

    /** @test */
    public function it_updates_inquiry_status_with_validation()
    {
        $inquiry = Inquiry::factory()->create(['status' => InquiryStatus::NEW->value]);

        // Valid status transition
        $result = $this->inquiryService->updateInquiryStatus($inquiry, InquiryStatus::CONTACTED->value);
        $this->assertTrue($result['success']);
        $this->assertEquals(InquiryStatus::CONTACTED->value, $inquiry->fresh()->status);

        // Invalid status transition (skip validation for test)
        $result = $this->inquiryService->updateInquiryStatus($inquiry, InquiryStatus::CLOSED->value);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid status transition', $result['error']);
    }

    /** @test */
    public function it_handles_business_hours_validation()
    {
        // Mock business hours enforcement
        config(['app.enforce_business_hours' => true]);

        // Test weekend (Sunday = 0)
        Carbon::setTestNow(Carbon::create(2024, 1, 7, 10, 0, 0)); // Sunday 10 AM

        $result = $this->inquiryService->createInquiry($this->validInquiryData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('business hours', $result['error']);

        // Test after hours (7 PM)
        Carbon::setTestNow(Carbon::create(2024, 1, 8, 19, 0, 0)); // Monday 7 PM

        $result = $this->inquiryService->createInquiry($this->validInquiryData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('business hours', $result['error']);

        // Test valid business hours (Monday 10 AM)
        Carbon::setTestNow(Carbon::create(2024, 1, 8, 10, 0, 0)); // Monday 10 AM

        $result = $this->inquiryService->createInquiry($this->validInquiryData);

        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_generates_inquiry_statistics()
    {
        // Create test data
        Inquiry::factory()->count(5)->create([
            'status' => InquiryStatus::NEW->value,
            'created_at' => now()->subDays(5)
        ]);

        Inquiry::factory()->count(3)->create([
            'status' => InquiryStatus::COMPLETED->value,
            'created_at' => now()->subDays(10)
        ]);

        $stats = $this->inquiryService->getInquiryStatistics(30);

        $this->assertArrayHasKey('total_inquiries', $stats);
        $this->assertArrayHasKey('by_status', $stats);
        $this->assertArrayHasKey('conversion_rate', $stats);
        $this->assertArrayHasKey('average_response_time', $stats);
        $this->assertArrayHasKey('top_properties', $stats);
        $this->assertArrayHasKey('broker_performance', $stats);

        $this->assertEquals(8, $stats['total_inquiries']);
        $this->assertIsFloat($stats['conversion_rate']);
    }

    /** @test */
    public function it_handles_overdue_inquiries()
    {
        // Create overdue inquiry (older than 24 hours without response)
        $overdueInquiry = Inquiry::factory()->create([
            'status' => InquiryStatus::NEW->value,
            'created_at' => now()->subHours(25)
        ]);

        // Create recent inquiry
        $recentInquiry = Inquiry::factory()->create([
            'status' => InquiryStatus::NEW->value,
            'created_at' => now()->subHours(12)
        ]);

        $overdueInquiries = $this->inquiryService->getOverdueInquiries();

        $this->assertCount(1, $overdueInquiries);
        $this->assertEquals($overdueInquiry->id, $overdueInquiries->first()->id);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset Carbon test time
        parent::tearDown();
    }
}