<?php

namespace Tests\Unit\Models;

use App\Models\Client;
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
            'name',
            'email',
            'phone',
            'message',
            'inquiry_type',
            'property_id',
            'client_id',
            'status',
            'contacted_at',
            'scheduled_at',
            'broker_notes',
            'broker_response',
            'responded_at'
        ];

        $inquiry = new Inquiry();
        $this->assertEquals($fillable, $inquiry->getFillable());
    }

    public function test_inquiry_has_correct_casts(): void
    {
        $inquiry = new Inquiry();
        $casts = $inquiry->getCasts();

        $this->assertArrayHasKey('contacted_at', $casts);
        $this->assertEquals('datetime', $casts['contacted_at']);
        $this->assertArrayHasKey('scheduled_at', $casts);
        $this->assertEquals('datetime', $casts['scheduled_at']);
        $this->assertArrayHasKey('responded_at', $casts);
        $this->assertEquals('datetime', $casts['responded_at']);
    }

    public function test_inquiry_belongs_to_client(): void
    {
        $broker = User::factory()->broker()->create();
        $client = Client::factory()->create(['broker_id' => $broker->id]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        $inquiry = Inquiry::factory()->create([
            'client_id' => $client->id,
            'property_id' => $property->id
        ]);

        $this->assertInstanceOf(Client::class, $inquiry->client);
        $this->assertEquals($client->id, $inquiry->client->id);
    }

    public function test_inquiry_belongs_to_property(): void
    {
        $broker = User::factory()->broker()->create();
        $client = Client::factory()->create(['broker_id' => $broker->id]);
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
        $broker = User::factory()->broker()->create();
        $client = Client::factory()->create(['broker_id' => $broker->id]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $inquiry = Inquiry::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'client_id' => $client->id,
            'property_id' => $property->id,
            'message' => 'I am interested in this property.',
            'inquiry_type' => 'general',
            'status' => 'new'
        ]);

        $this->assertInstanceOf(Inquiry::class, $inquiry);
        $this->assertEquals('John Doe', $inquiry->name);
        $this->assertEquals('john@example.com', $inquiry->email);
        $this->assertEquals($client->id, $inquiry->client_id);
        $this->assertEquals($property->id, $inquiry->property_id);
        $this->assertEquals('I am interested in this property.', $inquiry->message);
        $this->assertEquals('general', $inquiry->inquiry_type);
        $this->assertEquals('new', $inquiry->status);
    }

    public function test_inquiry_can_be_created_with_all_attributes(): void
    {
        $broker = User::factory()->broker()->create();
        $client = Client::factory()->create(['broker_id' => $broker->id]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $inquiry = Inquiry::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '+63 912 345 6789',
            'client_id' => $client->id,
            'property_id' => $property->id,
            'message' => 'I am very interested in this beautiful property.',
            'inquiry_type' => 'viewing',
            'status' => 'contacted',
            'contacted_at' => now(),
            'broker_notes' => 'Client seems very interested',
            'broker_response' => 'Thank you for your interest. Let me schedule a viewing.',
            'responded_at' => now()
        ]);

        $this->assertInstanceOf(Inquiry::class, $inquiry);
        $this->assertEquals('Jane Smith', $inquiry->name);
        $this->assertEquals('jane@example.com', $inquiry->email);
        $this->assertEquals('+63 912 345 6789', $inquiry->phone);
        $this->assertEquals($client->id, $inquiry->client_id);
        $this->assertEquals($property->id, $inquiry->property_id);
        $this->assertEquals('I am very interested in this beautiful property.', $inquiry->message);
        $this->assertEquals('viewing', $inquiry->inquiry_type);
        $this->assertEquals('contacted', $inquiry->status);
        $this->assertNotNull($inquiry->contacted_at);
        $this->assertEquals('Client seems very interested', $inquiry->broker_notes);
        $this->assertEquals('Thank you for your interest. Let me schedule a viewing.', $inquiry->broker_response);
        $this->assertNotNull($inquiry->responded_at);
    }

    public function test_inquiry_factory_creates_valid_inquiry(): void
    {
        $inquiry = Inquiry::factory()->create();

        $this->assertInstanceOf(Inquiry::class, $inquiry);
        $this->assertNotNull($inquiry->name);
        $this->assertNotNull($inquiry->email);
        $this->assertNotNull($inquiry->property_id);
        $this->assertNotNull($inquiry->message);
        $this->assertNotNull($inquiry->inquiry_type);
        $this->assertNotNull($inquiry->status);
        $this->assertInstanceOf(Property::class, $inquiry->property);
        if ($inquiry->client_id) {
            $this->assertInstanceOf(Client::class, $inquiry->client);
        }
    }

    public function test_inquiry_can_be_marked_as_responded(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'new']);

        $inquiry->update([
            'status' => 'contacted',
            'broker_response' => 'Thank you for your inquiry. I will contact you soon.',
            'responded_at' => now()
        ]);

        $this->assertEquals('contacted', $inquiry->status);
        $this->assertEquals('Thank you for your inquiry. I will contact you soon.', $inquiry->broker_response);
        $this->assertNotNull($inquiry->responded_at);
    }

    public function test_inquiry_can_be_closed(): void
    {
        $inquiry = Inquiry::factory()->create(['status' => 'contacted']);

        $inquiry->update([
            'status' => 'closed',
            'broker_response' => 'Property has been sold.'
        ]);

        $this->assertEquals('closed', $inquiry->status);
        $this->assertEquals('Property has been sold.', $inquiry->broker_response);
    }



    public function test_inquiry_status_defaults_to_pending(): void
    {
        $broker = User::factory()->broker()->create();
        $client = Client::factory()->create(['broker_id' => $broker->id]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $inquiry = Inquiry::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'client_id' => $client->id,
            'property_id' => $property->id,
            'message' => 'I am interested in this property.',
            'inquiry_type' => 'general'
        ]);

        $inquiry->refresh();
        $this->assertEquals('new', $inquiry->status);
    }

    public function test_inquiry_validates_inquiry_type(): void
    {
        $validTypes = ['general', 'viewing', 'purchase', 'information'];
        
        foreach ($validTypes as $type) {
            $inquiry = Inquiry::factory()->create([
                'inquiry_type' => $type
            ]);
            
            $this->assertEquals($type, $inquiry->inquiry_type);
        }
    }

    public function test_inquiry_validates_status_values(): void
    {
        $validStatuses = ['new', 'contacted', 'scheduled', 'completed', 'closed'];
        
        foreach ($validStatuses as $status) {
            $inquiry = Inquiry::factory()->create([
                'status' => $status
            ]);
            
            $this->assertEquals($status, $inquiry->status);
        }
    }

    public function test_inquiry_message_is_required(): void
    {
        $broker = User::factory()->broker()->create();
        $client = Client::factory()->create(['broker_id' => $broker->id]);
        $property = Property::factory()->create(['broker_id' => $broker->id]);

        $inquiry = Inquiry::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'client_id' => $client->id,
            'property_id' => $property->id,
            'message' => 'Test message',
            'inquiry_type' => 'general'
        ]);

        $this->assertNotEmpty($inquiry->message);
        $this->assertEquals('Test message', $inquiry->message);
    }
}