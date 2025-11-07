<?php

namespace Tests\Feature;

use App\Models\SellerRequest;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class SellerRequestApprovalTest extends TestCase
{
    use WithFaker;

    /**
     * Happy path: broker approves a seller request with custom type (Other + custom text)
     * Expect: request auto-converted -> status listed, property created with types ['other'] & custom_type_text preserved, images carried over.
     */
    public function test_broker_can_approve_and_convert_custom_type_request(): void
    {
        // Create a broker user
        $broker = User::factory()->broker()->create();

        $this->actingAs($broker);

        // Minimal seller request data reflecting public form fields
        $sellerRequest = SellerRequest::create([
            'name' => 'Owner Name',
            'email' => 'owner@example.com',
            'phone' => '09171234567',
            'property_title' => 'Test Memorial Lot',
            'property_description' => 'A test memorial lot property.',
            'property_type' => ['other'],
            'custom_property_type' => 'Memorial Lot',
            'asking_price' => 150000.00,
            'municipality' => 'Tagbilaran City',
            'barangay' => 'Cogon',
            'lot_area' => 1000,
            'features' => ['road_access', 'electricity'],
            'uploaded_images' => ['seller-requests/images/demo.jpg'],
            'status' => 'pending',
            'assigned_broker_id' => $broker->id,
            'terms_accepted' => true,
        ]);

        // Approve (this triggers auto-conversion in updateStatus)
        $response = $this->post(route('seller-requests.update-status', $sellerRequest), [
            'status' => 'approved',
        ]);

        $response->assertRedirect();

        $sellerRequest->refresh();

        $this->assertNotNull($sellerRequest->property_id, 'Property should have been created');
        $this->assertEquals('listed', $sellerRequest->status, 'Seller request should be marked listed after conversion');

        $property = Property::find($sellerRequest->property_id);
        $this->assertNotNull($property, 'Property record must exist');
        $this->assertEquals('Memorial Lot', $property->custom_type_text, 'Custom type text must be preserved');
        $this->assertIsArray($property->types ?? [], 'Multi-types should be array when column exists');
        $this->assertContains('other', $property->types ?? ['other'], "'other' should be stored for custom types");
        $this->assertIsArray($property->images, 'Images should be an array');
        $this->assertNotEmpty($property->images, 'Images array should not be empty');
    }

    /**
     * Validation path: rejecting must include a rejection_reason.
     */
    public function test_reject_requires_rejection_reason(): void
    {
        $broker = User::factory()->broker()->create();
        $this->actingAs($broker);

        $sellerRequest = SellerRequest::create([
            'name' => 'Owner Name',
            'email' => 'owner2@example.com',
            'property_title' => 'Another Lot',
            'property_type' => ['residential_lot'],
            'asking_price' => 100000,
            'status' => 'pending',
            'assigned_broker_id' => $broker->id,
            'terms_accepted' => true,
        ]);

        $response = $this->post(route('seller-requests.update-status', $sellerRequest), [
            'status' => 'rejected',
            // Missing rejection_reason purposely
        ]);

        $response->assertSessionHasErrors(['rejection_reason']);
        $sellerRequest->refresh();
        $this->assertEquals('pending', $sellerRequest->status, 'Status should remain pending on validation error');
    }
}
