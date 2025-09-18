<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Client;
use App\Services\BrokerAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class BrokerAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BrokerAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BrokerAssignmentService();
    }

    /** @test */
    public function it_assigns_broker_to_inquiry_successfully()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        $property = Property::factory()->create(['status' => 'active']);
        $inquiry = Inquiry::factory()->create(['property_id' => $property->id]);

        $assignedBroker = $this->service->assignBrokerToInquiry($inquiry);

        $this->assertNotNull($assignedBroker);
        $this->assertEquals($broker->id, $assignedBroker->id);
        $this->assertEquals($broker->id, $property->fresh()->broker_id);
    }

    /** @test */
    public function it_returns_null_when_no_brokers_available()
    {
        $property = Property::factory()->create(['status' => 'active']);
        $inquiry = Inquiry::factory()->create(['property_id' => $property->id]);

        $assignedBroker = $this->service->assignBrokerToInquiry($inquiry);

        $this->assertNull($assignedBroker);
    }

    /** @test */
    public function it_selects_broker_with_lowest_workload()
    {
        // Create two brokers
        $heavyBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        $lightBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        // Give heavy broker more workload
        $heavyProperty = Property::factory()->create(['broker_id' => $heavyBroker->id]);
        Inquiry::factory()->count(5)->create([
            'property_id' => $heavyProperty->id,
            'status' => 'new'
        ]);

        Client::factory()->count(3)->create([
            'broker_id' => $heavyBroker->id,
            'status' => 'active'
        ]);

        $property = Property::factory()->create(['status' => 'active']);
        $inquiry = Inquiry::factory()->create(['property_id' => $property->id]);

        $assignedBroker = $this->service->assignBrokerToInquiry($inquiry);

        $this->assertEquals($lightBroker->id, $assignedBroker->id);
    }

    /** @test */
    public function it_calculates_workload_score_correctly()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        // Create workload
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        Inquiry::factory()->count(3)->create([
            'property_id' => $property->id,
            'status' => 'new'
        ]);

        Client::factory()->count(2)->create([
            'broker_id' => $broker->id,
            'status' => 'active'
        ]);

        $workload = $this->service->getBrokerWorkload($broker);

        $this->assertEquals(3, $workload['active_inquiries']);
        $this->assertEquals(2, $workload['active_clients']);
        $this->assertEquals(5, $workload['total_workload']);
    }

    /** @test */
    public function it_considers_broker_performance_in_scoring()
    {
        $highPerformanceBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        $lowPerformanceBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        // Create performance data for high performer
        $highProperty = Property::factory()->create(['broker_id' => $highPerformanceBroker->id]);
        Inquiry::factory()->count(5)->create([
            'property_id' => $highProperty->id,
            'status' => 'completed',
            'created_at' => now()->subDays(15)
        ]);

        // Create performance data for low performer
        $lowProperty = Property::factory()->create(['broker_id' => $lowPerformanceBroker->id]);
        Inquiry::factory()->count(5)->create([
            'property_id' => $lowProperty->id,
            'status' => 'new',
            'created_at' => now()->subDays(15)
        ]);

        $property = Property::factory()->create(['status' => 'active']);
        $inquiry = Inquiry::factory()->create(['property_id' => $property->id]);

        $assignedBroker = $this->service->assignBrokerToInquiry($inquiry);

        // Should prefer high performance broker despite equal workload
        $this->assertEquals($highPerformanceBroker->id, $assignedBroker->id);
    }

    /** @test */
    public function it_considers_location_preferences()
    {
        $localBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true,
            'preferred_locations' => json_encode(['Tagbilaran', 'Panglao'])
        ]);

        $remoteBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true,
            'preferred_locations' => json_encode(['Cebu', 'Manila'])
        ]);

        $property = Property::factory()->create([
            'status' => 'active',
            'location' => 'Tagbilaran City, Bohol'
        ]);

        $inquiry = Inquiry::factory()->create(['property_id' => $property->id]);

        $assignedBroker = $this->service->assignBrokerToInquiry($inquiry);

        // Should prefer broker with matching location preference
        $this->assertEquals($localBroker->id, $assignedBroker->id);
    }

    /** @test */
    public function it_considers_broker_availability()
    {
        $activeBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true,
            'last_seen_at' => now()->subMinutes(30)
        ]);

        $inactiveBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true,
            'last_seen_at' => now()->subDays(5)
        ]);

        $property = Property::factory()->create(['status' => 'active']);
        $inquiry = Inquiry::factory()->create(['property_id' => $property->id]);

        $assignedBroker = $this->service->assignBrokerToInquiry($inquiry);

        // Should prefer recently active broker
        $this->assertEquals($activeBroker->id, $assignedBroker->id);
    }

    /** @test */
    public function it_skips_inactive_brokers()
    {
        $inactiveBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => false
        ]);

        $activeBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        $property = Property::factory()->create(['status' => 'active']);
        $inquiry = Inquiry::factory()->create(['property_id' => $property->id]);

        $assignedBroker = $this->service->assignBrokerToInquiry($inquiry);

        $this->assertEquals($activeBroker->id, $assignedBroker->id);
    }

    /** @test */
    public function it_skips_unapproved_brokers()
    {
        $unapprovedBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'pending',
            'is_active' => true
        ]);

        $approvedBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        $property = Property::factory()->create(['status' => 'active']);
        $inquiry = Inquiry::factory()->create(['property_id' => $property->id]);

        $assignedBroker = $this->service->assignBrokerToInquiry($inquiry);

        $this->assertEquals($approvedBroker->id, $assignedBroker->id);
    }

    /** @test */
    public function it_reassigns_inquiries_when_broker_unavailable()
    {
        $unavailableBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        $availableBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        // Create inquiries assigned to unavailable broker
        $property1 = Property::factory()->create(['broker_id' => $unavailableBroker->id]);
        $property2 = Property::factory()->create(['broker_id' => $unavailableBroker->id]);

        Inquiry::factory()->create([
            'property_id' => $property1->id,
            'status' => 'new'
        ]);

        Inquiry::factory()->create([
            'property_id' => $property2->id,
            'status' => 'contacted'
        ]);

        $reassignedCount = $this->service->reassignBrokerInquiries($unavailableBroker);

        $this->assertEquals(2, $reassignedCount);
        $this->assertEquals($availableBroker->id, $property1->fresh()->broker_id);
        $this->assertEquals($availableBroker->id, $property2->fresh()->broker_id);
    }

    /** @test */
    public function it_does_not_reassign_completed_inquiries()
    {
        $unavailableBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        $availableBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        // Create completed inquiry
        $property = Property::factory()->create(['broker_id' => $unavailableBroker->id]);
        Inquiry::factory()->create([
            'property_id' => $property->id,
            'status' => 'completed'
        ]);

        $reassignedCount = $this->service->reassignBrokerInquiries($unavailableBroker);

        $this->assertEquals(0, $reassignedCount);
        $this->assertEquals($unavailableBroker->id, $property->fresh()->broker_id);
    }

    /** @test */
    public function it_handles_no_available_brokers_for_reassignment()
    {
        $unavailableBroker = User::factory()->create([
            'role' => 'broker',
            'status' => 'approved',
            'is_active' => true
        ]);

        // Create inquiry but no other available brokers
        $property = Property::factory()->create(['broker_id' => $unavailableBroker->id]);
        Inquiry::factory()->create([
            'property_id' => $property->id,
            'status' => 'new'
        ]);

        $reassignedCount = $this->service->reassignBrokerInquiries($unavailableBroker);

        $this->assertEquals(0, $reassignedCount);
        $this->assertEquals($unavailableBroker->id, $property->fresh()->broker_id);
    }
}