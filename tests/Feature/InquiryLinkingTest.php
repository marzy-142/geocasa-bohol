<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Inquiry;
use App\Models\Client;
use App\Models\Property;
use App\Services\InquiryLinkingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InquiryLinkingTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        // RefreshDatabase trait handles database setup automatically
    }

    public function test_existing_inquiries_are_linked_to_new_user_by_email()
    {
        // Create a property for the inquiry
        $property = Property::factory()->create();
        
        // Create an inquiry without a user_id (guest inquiry)
        $inquiry = Inquiry::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123-456-7890',
            'message' => 'I am interested in this property',
            'property_id' => $property->id,
            'inquiry_type' => 'general',
            'status' => 'new'
        ]);

        $this->assertNull($inquiry->user_id);

        // Create a new user with the same email
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'role' => 'client'
        ]);

        // Use the inquiry linking service
        $inquiryLinkingService = new InquiryLinkingService();
        $result = $inquiryLinkingService->linkExistingInquiriesToUser($user);

        // Assert the inquiry was linked
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['linked_inquiries']);
        $this->assertEquals(0, $result['linked_clients']);

        // Refresh the inquiry and check it's linked to the user
        $inquiry->refresh();
        $this->assertEquals($user->id, $inquiry->user_id);
    }

    public function test_existing_clients_are_linked_to_new_user_by_email()
    {
        // Create a broker for the client
        $broker = User::factory()->create(['role' => 'broker']);
        
        // Create a client without a user_id
        $client = Client::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '987-654-3210',
            'broker_id' => $broker->id,
            'status' => 'active'
        ]);

        $this->assertNull($client->user_id);

        // Create a new user with the same email
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'name' => 'Jane Smith',
            'role' => 'client'
        ]);

        // Use the inquiry linking service
        $inquiryLinkingService = new InquiryLinkingService();
        $result = $inquiryLinkingService->linkExistingInquiriesToUser($user);

        // Assert the client was linked
        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['linked_inquiries']);
        $this->assertEquals(1, $result['linked_clients']);

        // Refresh the client and check it's linked to the user
        $client->refresh();
        $this->assertEquals($user->id, $client->user_id);
    }

    public function test_client_inquiries_are_also_linked_when_client_is_linked()
    {
        // Create necessary models
        $broker = User::factory()->create(['role' => 'broker']);
        $property = Property::factory()->create();
        
        // Create a client without a user_id
        $client = Client::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'phone' => '555-123-4567',
            'broker_id' => $broker->id,
            'status' => 'active'
        ]);

        // Create an inquiry linked to this client
        $inquiry = Inquiry::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'phone' => '555-123-4567',
            'message' => 'I want to schedule a viewing',
            'property_id' => $property->id,
            'client_id' => $client->id,
            'inquiry_type' => 'viewing',
            'status' => 'new'
        ]);

        $this->assertNull($client->user_id);
        $this->assertNull($inquiry->user_id);

        // Create a new user with the same email
        $user = User::factory()->create([
            'email' => 'bob@example.com',
            'name' => 'Bob Johnson',
            'role' => 'client'
        ]);

        // Use the inquiry linking service
        $inquiryLinkingService = new InquiryLinkingService();
        $result = $inquiryLinkingService->linkExistingInquiriesToUser($user);

        // Assert both client and inquiry were linked
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['linked_inquiries']);
        $this->assertEquals(1, $result['linked_clients']);

        // Refresh and check both are linked to the user
        $client->refresh();
        $inquiry->refresh();
        $this->assertEquals($user->id, $client->user_id);
        $this->assertEquals($user->id, $inquiry->user_id);
    }

    public function test_no_linking_occurs_for_different_email()
    {
        // Create a property for the inquiry
        $property = Property::factory()->create();
        
        // Create an inquiry with different email
        $inquiry = Inquiry::create([
            'name' => 'Alice Brown',
            'email' => 'alice@example.com',
            'phone' => '111-222-3333',
            'message' => 'I am interested in this property',
            'property_id' => $property->id,
            'inquiry_type' => 'general',
            'status' => 'new'
        ]);

        // Create a new user with different email
        $user = User::factory()->create([
            'email' => 'different@example.com',
            'name' => 'Different User',
            'role' => 'client'
        ]);

        // Use the inquiry linking service
        $inquiryLinkingService = new InquiryLinkingService();
        $result = $inquiryLinkingService->linkExistingInquiriesToUser($user);

        // Assert no linking occurred
        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['linked_inquiries']);
        $this->assertEquals(0, $result['linked_clients']);

        // Refresh the inquiry and check it's still not linked
        $inquiry->refresh();
        $this->assertNull($inquiry->user_id);
    }

    public function test_check_existing_data_for_email()
    {
        // Create a property for the inquiry
        $property = Property::factory()->create();
        
        // Create an inquiry without user_id
        Inquiry::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '123-456-7890',
            'message' => 'Test inquiry',
            'property_id' => $property->id,
            'inquiry_type' => 'general',
            'status' => 'new'
        ]);

        $inquiryLinkingService = new InquiryLinkingService();
        $result = $inquiryLinkingService->checkExistingDataForEmail('test@example.com');

        $this->assertTrue($result['has_existing_data']);
        $this->assertEquals(1, $result['inquiries_count']);
        $this->assertEquals(0, $result['clients_count']);

        // Test with non-existing email
        $result = $inquiryLinkingService->checkExistingDataForEmail('nonexistent@example.com');
        $this->assertFalse($result['has_existing_data']);
        $this->assertEquals(0, $result['inquiries_count']);
        $this->assertEquals(0, $result['clients_count']);
    }
}