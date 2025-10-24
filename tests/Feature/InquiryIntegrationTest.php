<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Client;
use App\Enums\InquiryStatus;
use App\Services\InquiryService;
use App\Notifications\NewInquiryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Carbon\Carbon;

class InquiryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected InquiryService $inquiryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inquiryService = app(InquiryService::class);
        
        // Fake notifications and events for testing
        Notification::fake();
        Event::fake();
        Queue::fake();
    }

    /** @test */
    public function it_creates_inquiry_with_complete_workflow()
    {
        // Create test data
        $broker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        $property = Property::factory()->create([
            'status' => 'active',
            'broker_id' => null
        ]);

        $inquiryData = [
            'property_id' => $property->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'message' => 'I am interested in this property',
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 Test Browser'
        ];

        // Create inquiry
        $inquiry = $this->inquiryService->createInquiry($inquiryData);

        // Verify inquiry was created
        $this->assertNotNull($inquiry);
        $this->assertEquals(InquiryStatus::NEW, $inquiry->status);
        $this->assertEquals($inquiryData['name'], $inquiry->name);
        $this->assertEquals($inquiryData['email'], $inquiry->email);
        $this->assertEquals($inquiryData['phone'], $inquiry->phone);
        $this->assertEquals($inquiryData['message'], $inquiry->message);

        // Verify client was created/linked
        $this->assertNotNull($inquiry->client_id);
        $client = Client::find($inquiry->client_id);
        $this->assertEquals($inquiryData['email'], $client->email);
        $this->assertEquals($broker->id, $client->broker_id);

        // Verify broker was assigned to property
        $property->refresh();
        $this->assertEquals($broker->id, $property->broker_id);

        // Verify notifications were sent
        Notification::assertSentTo($broker, NewInquiryNotification::class);
    }

    /** @test */
    public function it_prevents_duplicate_inquiries()
    {
        $property = Property::factory()->create(['status' => 'active']);
        
        $inquiryData = [
            'property_id' => $property->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'message' => 'I am interested in this property',
            'ip_address' => '192.168.1.100'
        ];

        // Create first inquiry
        $firstInquiry = $this->inquiryService->createInquiry($inquiryData);
        $this->assertNotNull($firstInquiry);

        // Attempt to create duplicate
        $duplicateInquiry = $this->inquiryService->createInquiry($inquiryData);
        $this->assertNull($duplicateInquiry);

        // Verify only one inquiry exists
        $this->assertEquals(1, Inquiry::where('email', 'john@example.com')->count());
    }

    /** @test */
    public function it_handles_status_transitions_correctly()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        $inquiry = Inquiry::factory()->create([
            'status' => InquiryStatus::NEW
        ]);

        // Test valid transition: NEW -> CONTACTED
        $result = $this->inquiryService->updateInquiryStatus($inquiry, InquiryStatus::CONTACTED, $broker);
        $this->assertTrue($result);
        $inquiry->refresh();
        $this->assertEquals(InquiryStatus::CONTACTED, $inquiry->status);

        // Test valid transition: CONTACTED -> SCHEDULED
        $result = $this->inquiryService->updateInquiryStatus($inquiry, InquiryStatus::SCHEDULED, $broker);
        $this->assertTrue($result);
        $inquiry->refresh();
        $this->assertEquals(InquiryStatus::SCHEDULED, $inquiry->status);

        // Test valid transition: SCHEDULED -> COMPLETED
        $result = $this->inquiryService->updateInquiryStatus($inquiry, InquiryStatus::COMPLETED, $broker);
        $this->assertTrue($result);
        $inquiry->refresh();
        $this->assertEquals(InquiryStatus::COMPLETED, $inquiry->status);

        // Test invalid transition: COMPLETED -> NEW (should fail)
        $result = $this->inquiryService->updateInquiryStatus($inquiry, InquiryStatus::NEW, $broker);
        $this->assertFalse($result);
        $inquiry->refresh();
        $this->assertEquals(InquiryStatus::COMPLETED, $inquiry->status);
    }

    /** @test */
    public function it_respects_business_hours_validation()
    {
        $property = Property::factory()->create(['status' => 'active']);
        
        // Set time to outside business hours (e.g., 2 AM)
        Carbon::setTestNow(Carbon::now()->setTime(2, 0, 0));

        $inquiryData = [
            'property_id' => $property->id,
            'name' => 'Night Owl',
            'email' => 'night@example.com',
            'phone' => '+1234567890',
            'message' => 'Late night inquiry',
            'ip_address' => '192.168.1.100'
        ];

        $inquiry = $this->inquiryService->createInquiry($inquiryData);
        
        // Should still create inquiry but may be flagged
        $this->assertNotNull($inquiry);
        
        // Reset time
        Carbon::setTestNow();
    }

    /** @test */
    public function it_handles_rate_limiting()
    {
        $property = Property::factory()->create(['status' => 'active']);
        
        $baseData = [
            'property_id' => $property->id,
            'name' => 'Frequent User',
            'phone' => '+1234567890',
            'message' => 'Multiple inquiries',
            'ip_address' => '192.168.1.100'
        ];

        // Create multiple inquiries from same email
        for ($i = 1; $i <= 5; $i++) {
            $inquiryData = array_merge($baseData, [
                'email' => 'frequent@example.com',
                'message' => "Inquiry number {$i}"
            ]);
            
            $inquiry = $this->inquiryService->createInquiry($inquiryData);
            
            if ($i <= 3) {
                // First 3 should succeed
                $this->assertNotNull($inquiry, "Inquiry {$i} should be created");
            } else {
                // 4th and 5th should be blocked by rate limiting
                $this->assertNull($inquiry, "Inquiry {$i} should be blocked");
            }
        }
    }

    /** @test */
    public function it_generates_accurate_statistics()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        $property = Property::factory()->create([
            'status' => 'active',
            'broker_id' => $broker->id
        ]);

        // Create inquiries with different statuses
        $newInquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'status' => InquiryStatus::NEW,
            'created_at' => now()->subDays(1)
        ]);

        $completedInquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'status' => InquiryStatus::COMPLETED,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(1)
        ]);

        $closedInquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'status' => InquiryStatus::CLOSED,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(2)
        ]);

        $stats = $this->inquiryService->getInquiryStatistics(7);

        $this->assertEquals(3, $stats['total_inquiries']);
        $this->assertEquals(1, $stats['new_inquiries']);
        $this->assertEquals(1, $stats['completed_inquiries']);
        $this->assertGreaterThan(0, $stats['conversion_rate']);
        $this->assertIsArray($stats['top_properties']);
        $this->assertIsArray($stats['broker_performance']);
    }

    /** @test */
    public function it_identifies_overdue_inquiries()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        $property = Property::factory()->create([
            'status' => 'active',
            'broker_id' => $broker->id
        ]);

        // Create overdue inquiry (older than 24 hours in NEW status)
        $overdueInquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'status' => InquiryStatus::NEW,
            'created_at' => now()->subHours(25)
        ]);

        // Create recent inquiry (not overdue)
        $recentInquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'status' => InquiryStatus::NEW,
            'created_at' => now()->subHours(12)
        ]);

        $overdueInquiries = $this->inquiryService->getOverdueInquiries();

        $this->assertCount(1, $overdueInquiries);
        $this->assertEquals($overdueInquiry->id, $overdueInquiries->first()->id);
    }

    /** @test */
    public function it_handles_broker_assignment_when_no_brokers_available()
    {
        $property = Property::factory()->create(['status' => 'active']);
        
        $inquiryData = [
            'property_id' => $property->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'message' => 'I am interested in this property',
            'ip_address' => '192.168.1.100'
        ];

        // No brokers available
        $inquiry = $this->inquiryService->createInquiry($inquiryData);

        // Should still create inquiry but without broker assignment
        $this->assertNotNull($inquiry);
        $this->assertEquals(InquiryStatus::NEW, $inquiry->status);
        
        // Property should not have broker assigned
        $property->refresh();
        $this->assertNull($property->broker_id);
    }

    /** @test */
    public function it_handles_invalid_property_gracefully()
    {
        $inquiryData = [
            'property_id' => 99999, // Non-existent property
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'message' => 'I am interested in this property',
            'ip_address' => '192.168.1.100'
        ];

        $inquiry = $this->inquiryService->createInquiry($inquiryData);

        // Should return null for invalid property
        $this->assertNull($inquiry);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $property = Property::factory()->create(['status' => 'active']);
        
        // Missing required fields
        $inquiryData = [
            'property_id' => $property->id,
            // Missing name, email, message
            'phone' => '+1234567890',
            'ip_address' => '192.168.1.100'
        ];

        $inquiry = $this->inquiryService->createInquiry($inquiryData);

        // Should return null for missing required fields
        $this->assertNull($inquiry);
    }

    /** @test */
    public function it_validates_email_format()
    {
        $property = Property::factory()->create(['status' => 'active']);
        
        $inquiryData = [
            'property_id' => $property->id,
            'name' => 'John Doe',
            'email' => 'invalid-email-format', // Invalid email
            'phone' => '+1234567890',
            'message' => 'I am interested in this property',
            'ip_address' => '192.168.1.100'
        ];

        $inquiry = $this->inquiryService->createInquiry($inquiryData);

        // Should return null for invalid email format
        $this->assertNull($inquiry);
    }

    /** @test */
    public function it_validates_message_length()
    {
        $property = Property::factory()->create(['status' => 'active']);
        
        $inquiryData = [
            'property_id' => $property->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'message' => str_repeat('a', 2001), // Too long message
            'ip_address' => '192.168.1.100'
        ];

        $inquiry = $this->inquiryService->createInquiry($inquiryData);

        // Should return null for message too long
        $this->assertNull($inquiry);
    }

    /** @test */
    public function it_links_existing_clients_correctly()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        $property = Property::factory()->create([
            'status' => 'active',
            'broker_id' => $broker->id
        ]);

        // Create existing client
        $existingClient = Client::factory()->create([
            'email' => 'existing@example.com',
            'broker_id' => $broker->id
        ]);

        $inquiryData = [
            'property_id' => $property->id,
            'name' => 'Existing Client',
            'email' => 'existing@example.com',
            'phone' => '+1234567890',
            'message' => 'Another inquiry from existing client',
            'ip_address' => '192.168.1.100'
        ];

        $inquiry = $this->inquiryService->createInquiry($inquiryData);

        // Should link to existing client
        $this->assertNotNull($inquiry);
        $this->assertEquals($existingClient->id, $inquiry->client_id);
        
        // Should not create duplicate client
        $this->assertEquals(1, Client::where('email', 'existing@example.com')->count());
    }
}