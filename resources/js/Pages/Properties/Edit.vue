<template>
    <ModernDashboardLayout>
        <!-- Header Section -->
        <div
            class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg mb-6"
        >
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold">Edit Property</h2>
                    <p class="text-blue-100">
                        GeoCasa Bohol - {{ property.title }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Link
                        :href="route('properties.show', property.slug)"
                        class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors"
                    >
                        View Property
                    </Link>
                    <Link
                        :href="route('properties.index')"
                        class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors"
                    >
                        Back to Properties
                    </Link>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" enctype="multipart/form-data">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Basic Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="title" value="Property Title" />
                        <TextInput
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autofocus
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="type" value="Property Type" />
                        <select
                            id="type"
                            v-model="form.type"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="">Select Type</option>
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="agricultural">Agricultural</option>
                            <option value="industrial">Industrial</option>
                            <option value="beachfront">Beachfront</option>
                            <option value="mountain_view">Mountain View</option>
                            <option value="rice_field">Rice Field</option>
                            <option value="coconut_plantation">
                                Coconut Plantation
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.type" />
                    </div>

                    <div class="md:col-span-2">
                        <InputLabel for="description" value="Description" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        ></textarea>
                        <InputError
                            class="mt-2"
                            :message="form.errors.description"
                        />
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Location Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="municipality" value="Municipality" />
                        <select
                            id="municipality"
                            v-model="form.municipality"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="">Select Municipality</option>
                            <option
                                v-for="municipality in municipalities"
                                :key="municipality"
                                :value="municipality"
                            >
                                {{ municipality }}
                            </option>
                        </select>
                        <InputError
                            class="mt-2"
                            :message="form.errors.municipality"
                        />
                    </div>

                    <div>
                        <InputLabel for="barangay" value="Barangay" />
                        <TextInput
                            id="barangay"
                            v-model="form.barangay"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.barangay"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <InputLabel for="address" value="Complete Address" />
                        <TextInput
                            id="address"
                            v-model="form.address"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.address"
                        />
                    </div>
                </div>
            </div>

            <!-- Pricing & Area -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Pricing & Area
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <InputLabel for="lot_area_sqm" value="Lot Area (sqm)" />
                        <TextInput
                            id="lot_area_sqm"
                            v-model="form.lot_area_sqm"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                            required
                            @input="calculateTotalPrice"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.lot_area_sqm"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="price_per_sqm"
                            value="Price per sqm (₱)"
                        />
                        <TextInput
                            id="price_per_sqm"
                            v-model="form.price_per_sqm"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                            required
                            @input="calculateTotalPrice"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.price_per_sqm"
                        />
                    </div>

                    <div>
                        <InputLabel for="total_price" value="Total Price (₱)" />
                        <TextInput
                            id="total_price"
                            v-model="form.total_price"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full bg-gray-100"
                            readonly
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.total_price"
                        />
                    </div>
                </div>
            </div>

            <!-- Legal Documents -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Legal Documents
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="title_type" value="Title Type" />
                        <select
                            id="title_type"
                            v-model="form.title_type"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Select Title Type</option>
                            <option value="titled">Titled</option>
                            <option value="tax_declared">Tax Declared</option>
                            <option value="mother_title">Mother Title</option>
                            <option value="cct">CCT</option>
                        </select>
                        <InputError
                            class="mt-2"
                            :message="form.errors.title_type"
                        />
                    </div>

                    <div>
                        <InputLabel for="title_number" value="Title Number" />
                        <TextInput
                            id="title_number"
                            v-model="form.title_number"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.title_number"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="tax_declaration_number"
                            value="Tax Declaration Number"
                        />
                        <TextInput
                            id="tax_declaration_number"
                            v-model="form.tax_declaration_number"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.tax_declaration_number"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="zoning_classification"
                            value="Zoning Classification"
                        />
                        <TextInput
                            id="zoning_classification"
                            v-model="form.zoning_classification"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.zoning_classification"
                        />
                    </div>
                </div>
            </div>

            <!-- GPS Coordinates -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    GPS Coordinates
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="coordinates_lat" value="Latitude" />
                        <TextInput
                            id="coordinates_lat"
                            v-model="form.coordinates_lat"
                            type="number"
                            step="0.000001"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.coordinates_lat"
                        />
                    </div>

                    <div>
                        <InputLabel for="coordinates_lng" value="Longitude" />
                        <TextInput
                            id="coordinates_lng"
                            v-model="form.coordinates_lng"
                            type="number"
                            step="0.000001"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.coordinates_lng"
                        />
                    </div>
                </div>
            </div>

            <!-- Utilities & Access -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Utilities & Access
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="flex items-center">
                        <Checkbox
                            id="road_access"
                            v-model:checked="form.road_access"
                            name="road_access"
                        />
                        <InputLabel
                            for="road_access"
                            value="Road Access"
                            class="ml-2"
                        />
                    </div>

                    <div class="flex items-center">
                        <Checkbox
                            id="electricity_available"
                            v-model:checked="form.electricity_available"
                            name="electricity_available"
                        />
                        <InputLabel
                            for="electricity_available"
                            value="Electricity"
                            class="ml-2"
                        />
                    </div>

                    <div class="flex items-center">
                        <Checkbox
                            id="water_source"
                            v-model:checked="form.water_source"
                            name="water_source"
                        />
                        <InputLabel
                            for="water_source"
                            value="Water Source"
                            class="ml-2"
                        />
                    </div>

                    <div class="flex items-center">
                        <Checkbox
                            id="internet_available"
                            v-model:checked="form.internet_available"
                            name="internet_available"
                        />
                        <InputLabel
                            for="internet_available"
                            value="Internet"
                            class="ml-2"
                        />
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Additional Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel
                            for="nearby_landmarks"
                            value="Nearby Landmarks (comma separated)"
                        />
                        <textarea
                            id="nearby_landmarks"
                            v-model="nearbyLandmarksText"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            placeholder="School, Hospital, Market, etc."
                        ></textarea>
                        <InputError
                            class="mt-2"
                            :message="form.errors.nearby_landmarks"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="google_maps_link"
                            value="Google Maps Link"
                        />
                        <TextInput
                            id="google_maps_link"
                            v-model="form.google_maps_link"
                            type="url"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.google_maps_link"
                        />
                    </div>

                    <div>
                        <InputLabel for="status" value="Status" />
                        <select
                            id="status"
                            v-model="form.status"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                            <option value="under_negotiation">
                                Under Negotiation
                            </option>
                            <option value="off_market">Off Market</option>
                        </select>
                        <InputError
                            class="mt-2"
                            :message="form.errors.status"
                        />
                    </div>

                    <div class="flex items-center">
                        <Checkbox
                            id="is_featured"
                            v-model:checked="form.is_featured"
                            name="is_featured"
                        />
                        <InputLabel
                            for="is_featured"
                            value="Featured Property"
                            class="ml-2"
                        />
                    </div>
                </div>
            </div>

            <!-- Current Images -->
            <div
                v-if="property.images && property.images.length > 0"
                class="bg-white rounded-lg shadow-sm p-6 mb-6"
            >
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Current Images
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div
                        v-for="(image, index) in property.images"
                        :key="index"
                        class="relative"
                    >
                        <img
                            :src="asset('storage/' + image)"
                            :alt="`Property Image ${index + 1}`"
                            class="w-full h-32 object-cover rounded-lg"
                        />
                        <button
                            type="button"
                            @click="removeImage(index)"
                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>

            <!-- New Images -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Add New Images
                </h3>
                <div>
                    <InputLabel
                        for="images"
                        value="Upload New Images (Multiple files allowed)"
                    />
                    <input
                        id="images"
                        type="file"
                        multiple
                        accept="image/*"
                        @change="handleImageUpload"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.new_images"
                    />
                    <p class="mt-1 text-sm text-gray-500">
                        Select additional images to add to your property.
                        Supported formats: JPG, PNG, GIF
                    </p>
                </div>
            </div>

            <!-- New Documents -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Add New Documents
                </h3>
                <div>
                    <InputLabel
                        for="documents"
                        value="Upload New Documents (PDF files)"
                    />
                    <input
                        id="documents"
                        type="file"
                        multiple
                        accept=".pdf"
                        @change="handleDocumentUpload"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.new_documents"
                    />
                    <p class="mt-1 text-sm text-gray-500">
                        Upload additional legal documents (PDF format only)
                    </p>
                </div>
            </div>

            <!-- Virtual Tour Section -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6"
            >
                <h3
                    class="text-xl font-semibold text-gray-900 mb-6 flex items-center"
                >
                    <svg
                        class="w-6 h-6 mr-2 text-purple-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                        />
                    </svg>
                    Virtual Tour (Optional)
                </h3>

                <div class="mb-6">
                    <label class="flex items-center space-x-3">
                        <input
                            id="has_virtual_tour"
                            v-model="form.has_virtual_tour"
                            type="checkbox"
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                        />
                        <span class="text-sm font-medium text-gray-700">
                            🌟 Enable Virtual Tour for this property
                        </span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-6">
                        Virtual tours help potential buyers explore your
                        property in 360°
                    </p>
                </div>

                <!-- Current Virtual Tour Images -->
                <div
                    v-if="
                        property.virtual_tour_images &&
                        property.virtual_tour_images.length > 0
                    "
                    class="mb-6"
                >
                    <h4 class="text-sm font-medium text-gray-700 mb-2">
                        Current Virtual Tour Images:
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="(
                                image, index
                            ) in property.virtual_tour_images"
                            :key="index"
                            class="relative"
                        >
                            <img
                                :src="asset('storage/' + image)"
                                :alt="`Virtual Tour ${index + 1}`"
                                class="w-full h-32 object-cover rounded border"
                            />
                            <button
                                type="button"
                                @click="removeVirtualTourImage(index)"
                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="form.has_virtual_tour" class="space-y-6">
                    <!-- Virtual Tour Images -->
                    <div>
                        <InputLabel
                            for="virtual_tour_images"
                            value="Add New 360° Images"
                        />
                        <input
                            id="virtual_tour_images"
                            type="file"
                            multiple
                            accept="image/*"
                            class="mt-1 block w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm"
                            :class="{
                                'border-red-500 ring-red-500':
                                    form.errors.virtual_tour_images,
                            }"
                            @change="handleVirtualTourImageUpload"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Upload 360-degree panoramic images. Recommended:
                            Equirectangular format, minimum 2048x1024 resolution
                        </p>
                        <InputError
                            class="mt-2"
                            :message="form.errors.virtual_tour_images"
                        />
                    </div>

                    <!-- Virtual Tour Image Preview -->
                    <div v-if="virtualTourImagePreview.length > 0" class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">
                            New Virtual Tour Images Preview:
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="(
                                    image, index
                                ) in virtualTourImagePreview"
                                :key="index"
                                class="relative"
                            >
                                <img
                                    :src="image"
                                    :alt="`Virtual Tour ${index + 1}`"
                                    class="w-full h-32 object-cover rounded border"
                                />
                                <button
                                    type="button"
                                    @click="removeNewVirtualTourImage(index)"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- GIS Data -->
                    <div>
                        <InputLabel
                            for="gis_data"
                            value="GIS Data (Optional)"
                        />
                        <textarea
                            id="gis_data"
                            v-model="form.gis_data"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm"
                            placeholder="Enter GIS coordinates, elevation data, or other geographic information..."
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Geographic Information System data for enhanced
                            property mapping
                        </p>
                        <InputError
                            class="mt-2"
                            :message="form.errors.gis_data"
                        />
                    </div>

                    <!-- Tour Hotspots -->
                    <div>
                        <InputLabel
                            for="tour_hotspots"
                            value="Tour Hotspots (Optional)"
                        />
                        <textarea
                            id="tour_hotspots"
                            v-model="form.tour_hotspots"
                            rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm"
                            placeholder='[{"x": 50, "y": 30, "title": "Beautiful View", "description": "Stunning ocean view from this angle"}]'
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Add interactive hotspots to highlight key features.
                            Format: JSON array with position and description
                            data
                        </p>
                        <InputError
                            class="mt-2"
                            :message="form.errors.tour_hotspots"
                        />
                    </div>

                    <!-- Virtual Tour Help -->
                    <div
                        class="bg-purple-50 border border-purple-200 rounded-lg p-4"
                    >
                        <div class="flex items-start space-x-3">
                            <div class="w-5 h-5 text-purple-600 mt-0.5">
                                <svg fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p
                                    class="text-sm text-purple-800 font-medium mb-1"
                                >
                                    Virtual Tour Tips:
                                </p>
                                <ul class="text-sm text-purple-700 space-y-1">
                                    <li>
                                        • Use 360-degree cameras or smartphone
                                        apps to capture panoramic images
                                    </li>
                                    <li>
                                        • Ensure good lighting and stable
                                        positioning for best results
                                    </li>
                                    <li>
                                        • Capture multiple viewpoints to give a
                                        comprehensive tour
                                    </li>
                                    <li>
                                        • Hotspots can highlight important
                                        features like views, amenities, or
                                        boundaries
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-center justify-end space-x-4">
                    <Link
                        :href="route('properties.show', property.slug)"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition-colors"
                    >
                        Cancel
                    </Link>
                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Update Property
                    </PrimaryButton>
                </div>
            </div>
        </form>

        <!-- Footer Message -->
        <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                🏝️ Showcase Bohol's Paradise
            </h3>
            <p class="text-gray-600 text-sm">
                Update your property listing to highlight the unique beauty and
                investment potential of Bohol. Help buyers discover their
                perfect piece of paradise.
            </p>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";

const props = defineProps({
    property: Object,
});

const municipalities = [
    "Tagbilaran City",
    "Alburquerque",
    "Alicia",
    "Anda",
    "Antequera",
    "Baclayon",
    "Balilihan",
    "Batuan",
    "Bien Unido",
    "Bilar",
    "Buenavista",
    "Calape",
    "Candijay",
    "Carmen",
    "Catigbian",
    "Clarin",
    "Corella",
    "Cortes",
    "Dagohoy",
    "Danao",
    "Dauis",
    "Dimiao",
    "Duero",
    "Garcia Hernandez",
    "Getafe",
    "Guindulman",
    "Inabanga",
    "Jagna",
    "Jetafe",
    "Lila",
    "Loay",
    "Loboc",
    "Loon",
    "Mabini",
    "Maribojoc",
    "Panglao",
    "Pilar",
    "President Carlos P. Garcia",
    "Sagbayan",
    "San Isidro",
    "San Miguel",
    "Sevilla",
    "Sierra Bullones",
    "Sikatuna",
    "Talibon",
    "Trinidad",
    "Tubigon",
    "Ubay",
    "Valencia",
];

const nearbyLandmarksText = ref("");
const virtualTourImagePreview = ref([]);

const form = useForm({
    title: props.property.title,
    description: props.property.description,
    type: props.property.type,
    municipality: props.property.municipality,
    barangay: props.property.barangay,
    address: props.property.address,
    lot_area_sqm: props.property.lot_area_sqm,
    price_per_sqm: props.property.price_per_sqm,
    total_price: props.property.total_price,
    title_type: props.property.title_type,
    title_number: props.property.title_number,
    tax_declaration_number: props.property.tax_declaration_number,
    zoning_classification: props.property.zoning_classification,
    coordinates_lat: props.property.coordinates_lat,
    coordinates_lng: props.property.coordinates_lng,
    road_access: props.property.road_access,
    electricity_available: props.property.electricity_available,
    water_source: props.property.water_source,
    internet_available: props.property.internet_available,
    nearby_landmarks: props.property.nearby_landmarks || [],
    google_maps_link: props.property.google_maps_link,
    status: props.property.status,
    is_featured: props.property.is_featured,
    new_images: [],
    new_documents: [],
    remove_images: [],
    remove_documents: [],
    // Virtual Tour fields
    has_virtual_tour: props.property.has_virtual_tour || false,
    virtual_tour_images: [],
    gis_data: props.property.gis_data || "",
    tour_hotspots: props.property.tour_hotspots || "",
    remove_virtual_tour_images: [],
});

onMounted(() => {
    if (
        props.property.nearby_landmarks &&
        Array.isArray(props.property.nearby_landmarks)
    ) {
        nearbyLandmarksText.value = props.property.nearby_landmarks.join(", ");
    }
});

const calculateTotalPrice = () => {
    const area = parseFloat(form.lot_area_sqm) || 0;
    const pricePerSqm = parseFloat(form.price_per_sqm) || 0;
    form.total_price = (area * pricePerSqm).toFixed(2);
};

const handleImageUpload = (event) => {
    form.new_images = Array.from(event.target.files);
};

const handleDocumentUpload = (event) => {
    form.new_documents = Array.from(event.target.files);
};

// Virtual Tour Image Upload Handler
const handleVirtualTourImageUpload = (event) => {
    const files = Array.from(event.target.files);
    form.virtual_tour_images = files;

    // Create preview URLs
    virtualTourImagePreview.value = [];
    files.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            virtualTourImagePreview.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index) => {
    if (confirm("Are you sure you want to remove this image?")) {
        form.remove_images.push(props.property.images[index]);
        props.property.images.splice(index, 1);
    }
};

const removeVirtualTourImage = (index) => {
    if (confirm("Are you sure you want to remove this virtual tour image?")) {
        form.remove_virtual_tour_images.push(
            props.property.virtual_tour_images[index]
        );
        props.property.virtual_tour_images.splice(index, 1);
    }
};

const removeNewVirtualTourImage = (index) => {
    const newImages = Array.from(form.virtual_tour_images);
    newImages.splice(index, 1);
    form.virtual_tour_images = newImages;
    virtualTourImagePreview.value.splice(index, 1);
};

const asset = (path) => {
    return `/${path}`;
};

watch(nearbyLandmarksText, (newValue) => {
    form.nearby_landmarks = newValue
        .split(",")
        .map((item) => item.trim())
        .filter((item) => item);
});

const submit = () => {
    form.post(route("properties.update", props.property.slug), {
        _method: "put",
    });
};
</script>
