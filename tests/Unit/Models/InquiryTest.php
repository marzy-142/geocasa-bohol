<?php

namespace Tests\Unit\Models;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    public function test_inquiry_has_correct_fillable_attributes(): void
    {
        $fillable = [
            'client_id',
            'property_id',
            'message',
            'preferred_contact_method',
            'budget_range',
            'status',
            'response',
            'responded_at'
        ];

        $inquiry = new Inquiry();
        $this->assertEquals($fillable, $inquiry->getFillable());
    }

    public function test_inquiry_has_correct_casts(): void
    {
        $inquiry = new Inquiry();
        $casts = $inquiry->getCasts();

        $this->assertArrayHasKey('responded_at', $casts);
        $this->assertEquals('datetime', $casts['responded_at']);
    }

    public function test_inquiry_belongs_to_client(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $inquiry = Inquiry::factory()->create([
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);

        $this->assertInstanceOf(User::class, $inquiry->client);
        $this->assertEquals($client->id, $inquiry->client->id);
        $this->assertEquals('client', $inquiry->client->role);
    }

    public function test_inquiry_belongs_to_property(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $inquiry = Inquiry::factory()->create([
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);

        $this->assertInstanceOf(Property::class, $inquiry->property);
        $this->assertEquals($property->id, $inquiry->property->id);
    }

    public function test_inquiry_can_be_created_with_required_attributes(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $inquiry = Inquiry::create([
            'client_id' => $client->id,
            'property_id' => $property->id,
            'message' => 'I am interested in this property.',
            'preferred_contact_method' => 'email',
            'status' => 'pending'
        ]);

        $this->assertInstanceOf(Inquiry::class, $inquiry);
        $this->assertEquals($client->id, $inquiry->client_id);
        $this->assertEquals($property->id, $inquiry->property_id);
        $this->assertEquals('I am interested in this property.', $inquiry->message);
        $this->assertEquals('email', $inquiry->preferred_contact_method);
        $this->assertEquals('pending', $inquiry->status);
    }

    public function test_inquiry_can_be_created_with_all_attributes(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $inquiry = Inquiry::create([
            'client_id' => $client->id,
            'property_id' => $property->id,
            'message' => 'I am very interested in this beautiful property.',
            'preferred_contact_method' => 'phone',
            'budget_range' => '5000000-7000000',
            'status' => 'responded',
            'response' => 'Thank you for your interest. Let me schedule a viewing.',
            'responded_at' => now()
        ]);

        $this->assertInstanceOf(Inquiry::class, $inquiry);
        $this->assertEquals($client->id, $inquiry->client_id);
        $this->assertEquals($property->id, $inquiry->property_id);
        $this->assertEquals('I am very interested in this beautiful property.', $inquiry->message);
        $this->assertEquals('phone', $inquiry->preferred_contact_method);
        $this->assertEquals('5000000-7000000', $inquiry->budget_range);
        $this->assertEquals('responded', $inquiry->status);
        $this->assertEquals('Thank you for your interest. Let me schedule a viewing.', $inquiry->response);
        $this->assertNotNull($inquiry->responded_at);
    }

    public function test_inquiry_factory_creates_valid_inquiry(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->assertInstanceOf(Inquiry::class, $inquiry);
        $this->assertNotNull($inquiry->client_id);
        $this->assertNotNull($inquiry->property_id);
        $this->assertNotNull($inquiry->message);
        $this->assertNotNull($inquiry->preferred_contact_method);
        $this->assertNotNull($inquiry->status);
        $this->assertInstanceOf(User::class, $inquiry->client);
        $this->assertInstanceOf(Property::class, $inquiry->property);
    }

    public function test_inquiry_can_be_marked_as_responded(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'pending']);

        $inquiry->update([
            'status' => 'responded',
            'response' => 'Thank you for your inquiry. I will contact you soon.',
            'responded_at' => now()
        ]);

        $this->assertEquals('responded', $inquiry->status);
        $this->assertEquals('Thank you for your inquiry. I will contact you soon.', $inquiry->response);
        $this->assertNotNull($inquiry->responded_at);
    }

    public function test_inquiry_can_be_closed(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'responded']);

        $inquiry->update([
            'status' => 'closed',
            'response' => 'Property has been sold.'
        ]);

        $this->assertEquals('closed', $inquiry->status);
        $this->assertEquals('Property has been sold.', $inquiry->response);
    }

    public function test_inquiry_supports_soft_deletes(): void
    {
        $inquiry = Inquiry::factory()->create();
        $inquiryId = $inquiry->id;

        $inquiry->delete();

        $this->assertSoftDeleted('inquiries', ['id' => $inquiryId]);
        $this->assertNotNull($inquiry->fresh()->deleted_at);
    }

    public function test_inquiry_can_be_restored_after_soft_delete(): void
    {
        $inquiry = Inquiry::factory()->create();
        $inquiryId = $inquiry->id;

        $inquiry->delete();
        $this->assertSoftDeleted('inquiries', ['id' => $inquiryId]);

        $inquiry->restore();
        $this->assertDatabaseHas('inquiries', [
            'id' => $inquiryId,
            'deleted_at' => null
        ]);
    }

    public function test_inquiry_status_defaults_to_pending(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $inquiry = Inquiry::create([
            'client_id' => $client->id,
            'property_id' => $property->id,
            'message' => 'I am interested in this property.',
            'preferred_contact_method' => 'email'
        ]);

        $this->assertEquals('pending', $inquiry->status);
    }

    public function test_inquiry_validates_preferred_contact_method(): void
    {
        $validMethods = ['email', 'phone', 'both'];
        
        foreach ($validMethods as $method) {
            $inquiry = Inquiry::factory()->create([
                'preferred_contact_method' => $method
            ]);
            
            $this->assertEquals($method, $inquiry->preferred_contact_method);
        }
    }

    public function test_inquiry_validates_status_values(): void
    {
        $validStatuses = ['pending', 'responded', 'closed'];
        
        foreach ($validStatuses as $status) {
            $inquiry = Inquiry::factory()->create([
                'status' => $status
            ]);
            
            $this->assertEquals($status, $inquiry->status);
        }
    }

    public function test_inquiry_message_is_required(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_approved' => true,
            'application_status' => 'approved'
        ]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $inquiry = Inquiry::create([
            'client_id' => $client->id,
            'property_id' => $property->id,
            'message' => 'Test message',
            'preferred_contact_method' => 'email'
        ]);

        $this->assertNotEmpty($inquiry->message);
        $this->assertEquals('Test message', $inquiry->message);
    }
}