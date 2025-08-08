<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with(['broker', 'client'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('municipality', 'like', "%{$search}%")
                      ->orWhere('barangay', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->municipality, function ($query, $municipality) {
                $query->where('municipality', $municipality);
            })
            ->when($request->min_price, function ($query, $minPrice) {
                $query->where('total_price', '>=', $minPrice);
            })
            ->when($request->max_price, function ($query, $maxPrice) {
                $query->where('total_price', '<=', $maxPrice);
            })
            ->when($request->min_area, function ($query, $minArea) {
                $query->where('lot_area_sqm', '>=', $minArea);
            })
            ->when($request->max_area, function ($query, $maxArea) {
                $query->where('lot_area_sqm', '<=', $maxArea);
            })
            ->when($request->utilities, function ($query) {
                $query->where('electricity_available', true)
                      ->where('water_source', true);
            })
            ->when(auth()->user()->role === 'broker', function ($query) {
                $query->where('broker_id', auth()->id());
            });

        $properties = $query->latest()->paginate(12);

        return Inertia::render('Properties/Index', [
            'properties' => $properties,
            'filters' => $request->only(['search', 'type', 'status', 'municipality', 'min_price', 'max_price', 'min_area', 'max_area', 'utilities']),
            'types' => Property::TYPES,
            'statuses' => Property::STATUSES,
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
        ]);
    }

    public function show(Property $property)
    {
        $property->load(['broker', 'client', 'inquiries.client', 'transactions']);
        
        return Inertia::render('Properties/Show', [
            'property' => $property,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Property::class);
        
        $clients = auth()->user()->role === 'broker' 
            ? auth()->user()->clients()->get(['id', 'name', 'email'])
            : collect();

        return Inertia::render('Properties/Create', [
            'clients' => $clients,
            'types' => Property::TYPES,
            'statuses' => Property::STATUSES,
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Property::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:' . implode(',', Property::TYPES),
            'status' => 'required|in:' . implode(',', Property::STATUSES),
            'price_per_sqm' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'address' => 'required|string|max:500',
            'municipality' => 'required|in:' . implode(',', Property::BOHOL_MUNICIPALITIES),
            'barangay' => 'required|string|max:100',
            'lot_area_sqm' => 'required|numeric|min:1',
            'lot_area_hectares' => 'nullable|numeric|min:0',
            'title_type' => 'nullable|in:titled,tax_declared,mother_title,cct',
            'title_number' => 'nullable|string|max:100',
            'tax_declaration_number' => 'nullable|string|max:100',
            'coordinates_lat' => 'nullable|numeric|between:-90,90',
            'coordinates_lng' => 'nullable|numeric|between:-180,180',
            'road_access' => 'boolean',
            'water_source' => 'boolean',
            'electricity_available' => 'boolean',
            'internet_available' => 'boolean',
            'nearby_landmarks' => 'nullable|array',
            'zoning_classification' => 'nullable|string|max:100',
            'client_id' => 'nullable|exists:clients,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'documents' => 'nullable|array|max:5',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $validated['broker_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(6);

        // Calculate hectares if not provided
        if (!$validated['lot_area_hectares']) {
            $validated['lot_area_hectares'] = $validated['lot_area_sqm'] / 10000;
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties/images', 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = $imagePaths;
        }

        // Handle document uploads
        if ($request->hasFile('documents')) {
            $documentPaths = [];
            foreach ($request->file('documents') as $document) {
                $path = $document->store('properties/documents', 'public');
                $documentPaths[] = $path;
            }
            $validated['documents'] = $documentPaths;
        }

        $property = Property::create($validated);

        return redirect()->route('properties.show', $property)
            ->with('success', 'Land property created successfully.');
    }

    public function edit(Property $property)
    {
        $this->authorize('update', $property);
        
        $clients = auth()->user()->role === 'broker' 
            ? auth()->user()->clients()->get(['id', 'name', 'email'])
            : collect();

        return Inertia::render('Properties/Edit', [
            'property' => $property,
            'clients' => $clients,
            'types' => Property::TYPES,
            'statuses' => Property::STATUSES,
            'municipalities' => Property::BOHOL_MUNICIPALITIES,
        ]);
    }

    public function update(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:' . implode(',', Property::TYPES),
            'status' => 'required|in:' . implode(',', Property::STATUSES),
            'price_per_sqm' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'address' => 'required|string|max:500',
            'municipality' => 'required|in:' . implode(',', Property::BOHOL_MUNICIPALITIES),
            'barangay' => 'required|string|max:100',
            'lot_area_sqm' => 'required|numeric|min:1',
            'lot_area_hectares' => 'nullable|numeric|min:0',
            'title_type' => 'nullable|in:titled,tax_declared,mother_title,cct',
            'title_number' => 'nullable|string|max:100',
            'tax_declaration_number' => 'nullable|string|max:100',
            'coordinates_lat' => 'nullable|numeric|between:-90,90',
            'coordinates_lng' => 'nullable|numeric|between:-180,180',
            'road_access' => 'boolean',
            'water_source' => 'boolean',
            'electricity_available' => 'boolean',
            'internet_available' => 'boolean',
            'nearby_landmarks' => 'nullable|array',
            'zoning_classification' => 'nullable|string|max:100',
            'client_id' => 'nullable|exists:clients,id',
            'new_images' => 'nullable|array|max:10',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'new_documents' => 'nullable|array|max:5',
            'new_documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'string',
            'remove_documents' => 'nullable|array',
            'remove_documents.*' => 'string',
        ]);

        // Update slug if title changed
        if ($validated['title'] !== $property->title) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(6);
        }

        // Calculate hectares if not provided
        if (!$validated['lot_area_hectares']) {
            $validated['lot_area_hectares'] = $validated['lot_area_sqm'] / 10000;
        }

        // Handle file removals and additions
        $this->handleFileUpdates($request, $property, $validated);

        $property->update($validated);

        return redirect()->route('properties.show', $property)
            ->with('success', 'Land property updated successfully.');
    }

    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);

        // Delete associated files
        if ($property->images) {
            foreach ($property->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        if ($property->documents) {
            foreach ($property->documents as $document) {
                Storage::disk('public')->delete($document);
            }
        }

        $property->delete();

        return redirect()->route('properties.index')
            ->with('success', 'Land property deleted successfully.');
    }

    public function toggleFeatured(Property $property)
    {
        $this->authorize('update', $property);
        
        $property->update(['is_featured' => !$property->is_featured]);
        
        return back()->with('success', 'Property featured status updated.');
    }

    private function handleFileUpdates(Request $request, Property $property, array &$validated)
    {
        // Handle image removal
        if ($request->has('remove_images')) {
            $currentImages = $property->images ?? [];
            foreach ($request->remove_images as $imageToRemove) {
                if (in_array($imageToRemove, $currentImages)) {
                    Storage::disk('public')->delete($imageToRemove);
                    $currentImages = array_filter($currentImages, fn($img) => $img !== $imageToRemove);
                }
            }
            $validated['images'] = array_values($currentImages);
        }

        // Handle document removal
        if ($request->has('remove_documents')) {
            $currentDocuments = $property->documents ?? [];
            foreach ($request->remove_documents as $documentToRemove) {
                if (in_array($documentToRemove, $currentDocuments)) {
                    Storage::disk('public')->delete($documentToRemove);
                    $currentDocuments = array_filter($currentDocuments, fn($doc) => $doc !== $documentToRemove);
                }
            }
            $validated['documents'] = array_values($currentDocuments);
        }

        // Handle new image uploads
        if ($request->hasFile('new_images')) {
            $currentImages = $validated['images'] ?? $property->images ?? [];
            foreach ($request->file('new_images') as $image) {
                $path = $image->store('properties/images', 'public');
                $currentImages[] = $path;
            }
            $validated['images'] = $currentImages;
        }

        // Handle new document uploads
        if ($request->hasFile('new_documents')) {
            $currentDocuments = $validated['documents'] ?? $property->documents ?? [];
            foreach ($request->file('new_documents') as $document) {
                $path = $document->store('properties/documents', 'public');
                $currentDocuments[] = $path;
            }
            $validated['documents'] = $currentDocuments;
        }
    }
}