<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InquiryControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_authenticated_user_can_create_inquiry(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'available'
        ]);

        $inquiryData = [
            'property_id' => $property->id,
            'message' => 'I am interested in this property. Can we schedule a viewing?',
            'preferred_contact_method' => 'email',
            'budget_range' => '5000000-7000000'
        ];

        $response = $this->actingAs($client)->post('/inquiries', $inquiryData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'client_id' => $client->id,
            'property_id' => $property->id,
            'message' => 'I am interested in this property. Can we schedule a viewing?',
            'status' => 'pending'
        ]);
    }

    public function test_guest_cannot_create_inquiry(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'available'
        ]);

        $inquiryData = [
            'property_id' => $property->id,
            'message' => 'I am interested in this property.',
            'preferred_contact_method' => 'email'
        ];

        $response = $this->post('/inquiries', $inquiryData);

        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('inquiries', [
            'property_id' => $property->id,
            'message' => 'I am interested in this property.'
        ]);
    }

    public function test_client_can_view_own_inquiries(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        Inquiry::factory()->count(3)->create([
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);

        // Create inquiries for other clients
        Inquiry::factory()->count(2)->create();

        $response = $this->actingAs($client)->get('/client/inquiries');

        $response->assertStatus(200)
                 ->assertViewIs('client.inquiries.index')
                 ->assertViewHas('inquiries');

        $inquiries = $response->viewData('inquiries');
        $this->assertCount(3, $inquiries);
        
        foreach ($inquiries as $inquiry) {
            $this->assertEquals($client->id, $inquiry->client_id);
        }
    }

    public function test_broker_can_view_property_inquiries(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        Inquiry::factory()->count(3)->create([
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);

        $response = $this->actingAs($broker)->get('/broker/inquiries');

        $response->assertStatus(200)
                 ->assertViewIs('broker.inquiries.index')
                 ->assertViewHas('inquiries');

        $inquiries = $response->viewData('inquiries');
        $this->assertCount(3, $inquiries);
        
        foreach ($inquiries as $inquiry) {
            $this->assertEquals($property->id, $inquiry->property_id);
            $this->assertEquals($broker->id, $inquiry->property->broker_id);
        }
    }

    public function test_broker_can_respond_to_inquiry(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $inquiry = Inquiry::factory()->create([
            'client_id' => $client->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        $responseData = [
            'response' => 'Thank you for your interest. I would be happy to schedule a viewing.',
            'status' => 'responded'
        ];

        $response = $this->actingAs($broker)
                         ->put("/broker/inquiries/{$inquiry->id}", $responseData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'id' => $inquiry->id,
            'response' => 'Thank you for your interest. I would be happy to schedule a viewing.',
            'status' => 'responded'
        ]);
    }

    public function test_broker_cannot_respond_to_other_broker_inquiry(): void
    {
        $broker1 = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $broker2 = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker2->id]);
        $inquiry = Inquiry::factory()->create([
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);

        $responseData = [
            'response' => 'Unauthorized response',
            'status' => 'responded'
        ];

        $response = $this->actingAs($broker1)
                         ->put("/broker/inquiries/{$inquiry->id}", $responseData);

        $response->assertStatus(403);
    }

    public function test_inquiry_validation_fails_for_missing_fields(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)->post('/inquiries', []);

        $response->assertSessionHasErrors([
            'property_id',
            'message',
            'preferred_contact_method'
        ]);
    }

    public function test_inquiry_validation_fails_for_invalid_property(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $inquiryData = [
            'property_id' => 999, // Non-existent property
            'message' => 'I am interested in this property.',
            'preferred_contact_method' => 'email'
        ];

        $response = $this->actingAs($client)->post('/inquiries', $inquiryData);

        $response->assertSessionHasErrors(['property_id']);
    }

    public function test_inquiry_validation_fails_for_unavailable_property(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'sold'
        ]);

        $inquiryData = [
            'property_id' => $property->id,
            'message' => 'I am interested in this property.',
            'preferred_contact_method' => 'email'
        ];

        $response = $this->actingAs($client)->post('/inquiries', $inquiryData);

        $response->assertSessionHasErrors(['property_id']);
    }

    public function test_client_cannot_create_duplicate_inquiry_for_same_property(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'status' => 'available'
        ]);

        // Create first inquiry
        Inquiry::factory()->create([
            'client_id' => $client->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        $inquiryData = [
            'property_id' => $property->id,
            'message' => 'Another inquiry for the same property.',
            'preferred_contact_method' => 'email'
        ];

        $response = $this->actingAs($client)->post('/inquiries', $inquiryData);

        $response->assertSessionHasErrors(['property_id']);
    }

    public function test_inquiry_status_can_be_updated(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $client = User::factory()->create(['role' => 'client']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $inquiry = Inquiry::factory()->create([
            'client_id' => $client->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        $updateData = [
            'status' => 'closed',
            'response' => 'Property has been sold to another client.'
        ];

        $response = $this->actingAs($broker)
                         ->put("/broker/inquiries/{$inquiry->id}", $updateData);

        $response->assertRedirect()
                 ->assertSessionHas('success');

        $this->assertDatabaseHas('inquiries', [
            'id' => $inquiry->id,
            'status' => 'closed',
            'response' => 'Property has been sold to another client.'
        ]);
    }

    public function test_inquiry_shows_property_details(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'title' => 'Beautiful Beachfront Property'
        ]);
        $inquiry = Inquiry::factory()->create([
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);

        $response = $this->actingAs($client)->get("/client/inquiries/{$inquiry->id}");

        $response->assertStatus(200)
                 ->assertViewIs('client.inquiries.show')
                 ->assertViewHas('inquiry', $inquiry)
                 ->assertSee('Beautiful Beachfront Property');
    }
}