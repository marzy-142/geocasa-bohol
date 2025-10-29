<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class BrokerDirectoryController extends Controller
{
    /**
     * Display broker directory with top performers (merged page)
     */
    public function index(Request $request)
    {
        // Get top performing brokers (by finalized transactions)
        $topPerformers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->where('prc_verified', true)
            ->whereNull('suspended_at')
            ->withCount(['transactions as completed_sales' => function($query) {
                $query->where('status', 'finalized');
            }])
            ->having('completed_sales', '>', 0)
            ->orderBy('completed_sales', 'desc')
            ->limit(10)
            ->get()
            ->map(function($broker, $index) {
                return [
                    'rank' => $index + 1,
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'avatar' => $broker->avatar,
                    'avatar_url' => $broker->avatar ? asset('storage/' . $broker->avatar) . '?v=' . time() : null,
                    'brokerage_firm_name' => $broker->brokerage_firm_name,
                    'city' => $broker->city,
                    'finalized_transactions_count' => $broker->completed_sales,
                    'last_sale_date' => $broker->last_sale_date,
                ];
            });

        // Get all verified brokers for directory, sorted alphabetically
        $brokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->where('application_status', 'approved')
            ->where('prc_verified', true)
            ->where(function($query) {
                $query->where('show_in_directory', true)
                      ->orWhereNull('show_in_directory'); // Include NULL as true (default behavior)
            })
            ->whereNull('suspended_at')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function($broker) {
                return [
                    'id' => $broker->id,
                    'name' => $broker->name,
                    'avatar' => $broker->avatar,
                    'avatar_url' => $broker->avatar ? asset('storage/' . $broker->avatar) . '?v=' . time() : null,
                    'brokerage_firm_name' => $broker->brokerage_firm_name,
                    'city' => $broker->city,
                    'province' => $broker->province,
                    'years_experience' => $broker->years_experience,
                    'prc_license_number' => $broker->prc_license_number,
                    'specializations' => $broker->specializations,
                    'show_email' => $broker->show_email_in_directory,
                    'show_phone' => $broker->show_phone_in_directory,
                    'email' => $broker->show_email_in_directory ? $broker->email : null,
                    'office_contact_number' => $broker->show_phone_in_directory ? $broker->office_contact_number : null,
                ];
            });

        return Inertia::render('BrokerDirectory/Index', [
            'topPerformers' => $topPerformers,
            'brokers' => [
                'data' => $brokers,
            ],
        ]);
    }

    /**
     * Display individual broker profile
     */
    public function show($id)
    {
        $broker = User::where('role', 'broker')
            ->where('is_approved', true)
            ->where('show_in_directory', true)
            ->whereNull('suspended_at')
            ->with(['properties' => function($q) {
                $q->where('status', 'available')->latest()->limit(6);
            }])
            ->withCount(['properties as active_listings' => function($q) {
                $q->where('status', 'available');
            }])
            ->withCount(['properties as total_listings'])
            ->withCount(['properties as sold_properties' => function($q) {
                $q->where('status', 'sold');
            }])
            ->findOrFail($id);

        return Inertia::render('BrokerDirectory/Show', [
            'broker' => [
                'id' => $broker->id,
                'name' => $broker->name,
                'avatar' => $broker->avatar,
                'avatar_url' => $broker->avatar ? asset('storage/' . $broker->avatar) . '?v=' . time() : null,
                'brokerage_firm_name' => $broker->brokerage_firm_name,
                'city' => $broker->city,
                'province' => $broker->province,
                'years_experience' => $broker->years_experience,
                'prc_license_number' => $broker->prc_license_number,
                'bio' => $broker->bio,
                'specializations' => $broker->specializations,
                'service_areas' => $broker->service_areas,
                'profile_image' => $broker->profile_image,
                'availability_status' => $broker->availability_status,
                'active_listings' => $broker->active_listings,
                'total_listings' => $broker->total_listings,
                'sold_properties' => $broker->sold_properties,
                'show_email' => $broker->show_email_in_directory,
                'show_phone' => $broker->show_phone_in_directory,
                'accept_inquiries' => $broker->accept_directory_inquiries,
                'email' => $broker->show_email_in_directory ? $broker->email : null,
                'office_contact_number' => $broker->show_phone_in_directory ? $broker->office_contact_number : null,
                'website' => $broker->website,
                'facebook' => $broker->facebook,
                'linkedin' => $broker->linkedin,
                'properties' => $broker->properties->map(function($property) {
                    return [
                        'id' => $property->id,
                        'title' => $property->title,
                        'price' => $property->price,
                        'location' => $property->location,
                        'property_type' => $property->property_type,
                        'bedrooms' => $property->bedrooms,
                        'bathrooms' => $property->bathrooms,
                        'lot_area' => $property->lot_area,
                        'images' => $property->images,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Send inquiry to broker
     */
    public function sendInquiry(Request $request, $id)
    {
        $broker = User::where('role', 'broker')
            ->where('accept_directory_inquiries', true)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:1000',
            'inquiry_type' => 'required|in:general,buying,selling,consultation',
        ]);

        // Send email to broker
        \Mail::to($broker->email)->send(
            new \App\Mail\BrokerInquiryMail($broker, $validated)
        );

        // Log inquiry
        \Log::info('Broker directory inquiry sent', [
            'broker_id' => $broker->id,
            'inquirer_email' => $validated['email'],
        ]);

        return back()->with('success', 'Your inquiry has been sent to ' . $broker->name);
    }
}
