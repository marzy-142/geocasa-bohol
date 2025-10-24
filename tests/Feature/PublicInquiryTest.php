<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class PublicInquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_inquiry_without_authentication()
    {
        // Create a broker and property
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'available'
        ]);

        // Submit inquiry as guest
        $inquiryData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123-456-7890',
            'message' => 'I am interested in this property'
        ];

        $response = $this->postJson(route('public.inquiries.store', $property->slug), $inquiryData);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Verify inquiry was created
        $this->assertDatabaseHas('inquiries', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123-456-7890',
            'message' => 'I am interested in this property',
            'property_id' => $property->id,
            'user_id' => null // Should be null for guest inquiries
        ]);
    }

    public function test_inquiry_is_linked_when_user_registers_with_same_email()
    {
        // Create a broker and property
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'available'
        ]);

        // Submit inquiry as guest
        $inquiryData = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '987-654-3210',
            'message' => 'I want to schedule a viewing'
        ];

        $this->post(route('public.inquiries.store', $property->slug), $inquiryData);

        // Verify inquiry exists without user_id
        $inquiry = Inquiry::where('email', 'jane@example.com')->first();
        $this->assertNotNull($inquiry);
        $this->assertNull($inquiry->user_id);

        // Register user with same email
        $userData = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'client'
        ];

        $this->post(route('register'), $userData);

        // Verify inquiry is now linked to user
        $inquiry->refresh();
        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($user->id, $inquiry->user_id);
    }

    public function test_inquiry_is_linked_when_existing_user_logs_in()
    {
        // Create existing user
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'role' => 'client'
        ]);

        // Create a broker and property
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'available'
        ]);

        // Submit inquiry as guest with same email
        $inquiryData = [
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'phone' => '555-123-4567',
            'message' => 'I am interested in this property'
        ];

        $this->post(route('public.inquiries.store', $property->slug), $inquiryData);

        // Verify inquiry exists without user_id
        $inquiry = Inquiry::where('email', 'existing@example.com')->first();
        $this->assertNotNull($inquiry);
        $this->assertNull($inquiry->user_id);

        // Login with existing user
        $this->post(route('login'), [
            'email' => 'existing@example.com',
            'password' => 'password'
        ]);

        // Verify inquiry is now linked to user
        $inquiry->refresh();
        $this->assertEquals($user->id, $inquiry->user_id);
    }

    public function test_linked_inquiries_appear_in_client_dashboard()
    {
        // Create user
        $user = User::factory()->create([
            'email' => 'client@example.com',
            'role' => 'client'
        ]);

        // Create a broker and property
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'available'
        ]);

        // Create client record with broker_id
        $client = Client::create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'phone' => '555-999-8888',
            'broker_id' => $broker->id,
            'user_id' => $user->id,
            'status' => 'active'
        ]);

        // Create inquiry linked to user and client
        $inquiry = Inquiry::create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'phone' => '555-999-8888',
            'message' => 'I want to buy this property',
            'property_id' => $property->id,
            'user_id' => $user->id,
            'client_id' => $client->id,
            'inquiry_type' => 'general',
            'status' => 'new'
        ]);

        // Login and access client inquiries
        $response = $this->actingAs($user)->get(route('client.inquiries.index'));

        $response->assertStatus(200);
        
        // Check that the inquiry appears in the response
        $response->assertSee('I want to buy this property');
        $response->assertSee($property->title);
    }
}