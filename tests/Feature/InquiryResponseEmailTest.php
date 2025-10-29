<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use App\Mail\InquiryResponseMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InquiryResponseEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_is_sent_when_broker_responds_to_inquiry()
    {
        Mail::fake();

        // Create a broker and property
        $broker = User::factory()->create(['role' => 'broker']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        
        // Create an inquiry
        $inquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'email' => 'client@example.com',
            'name' => 'John Doe',
            'status' => 'new',
        ]);

        // Act as broker and respond
        $this->actingAs($broker);
        
        $response = $this->post(route('inquiries.respond', $inquiry->id), [
            'broker_response' => 'Thank you for your inquiry. I would be happy to arrange a viewing.',
            'status' => 'contacted',
        ]);

        // Assert email was sent
        Mail::assertSent(InquiryResponseMail::class, function ($mail) use ($inquiry) {
            return $mail->hasTo('client@example.com') &&
                   $mail->inquiry->id === $inquiry->id &&
                   $mail->brokerResponse === 'Thank you for your inquiry. I would be happy to arrange a viewing.';
        });

        $response->assertRedirect(route('inquiries.show', $inquiry));
    }

    public function test_inquiry_response_email_contains_correct_data()
    {
        $broker = User::factory()->create(['role' => 'broker', 'name' => 'Jane Broker']);
        $property = Property::factory()->create([
            'broker_id' => $broker->id,
            'title' => 'Beachfront Villa',
            'municipality' => 'Panglao',
            'province' => 'Bohol',
            'total_price' => 5000000,
        ]);
        
        $inquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'email' => 'buyer@example.com',
            'name' => 'Test Buyer',
            'message' => 'I am interested in this property.',
        ]);

        $mailable = new InquiryResponseMail(
            $inquiry,
            'I would love to show you this property.',
            'Jane Broker'
        );

        $mailable->assertSeeInHtml('Test Buyer');
        $mailable->assertSeeInHtml('Beachfront Villa');
        $mailable->assertSeeInHtml('I would love to show you this property.');
        $mailable->assertSeeInHtml('Jane Broker');
        $mailable->assertSeeInHtml('Panglao, Bohol');
    }

    public function test_email_failure_does_not_prevent_response_from_being_saved()
    {
        Mail::fake();
        Mail::shouldReceive('to')->andThrow(new \Exception('SMTP Error'));

        $broker = User::factory()->create(['role' => 'broker']);
        $property = Property::factory()->create(['broker_id' => $broker->id]);
        
        $inquiry = Inquiry::factory()->create([
            'property_id' => $property->id,
            'status' => 'new',
        ]);

        $this->actingAs($broker);
        
        $response = $this->post(route('inquiries.respond', $inquiry->id), [
            'broker_response' => 'Thank you for your inquiry.',
            'status' => 'contacted',
        ]);

        // Response should still be saved even if email fails
        $this->assertDatabaseHas('inquiries', [
            'id' => $inquiry->id,
            'broker_response' => 'Thank you for your inquiry.',
            'status' => 'contacted',
        ]);
    }
}
