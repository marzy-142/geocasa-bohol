<template>
    <ModernDashboardLayout>
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Add New Property
                    </h1>
                    <p class="text-gray-600 mt-1">
                        List your land property in GeoCasa Bohol
                    </p>
                </div>
                <Link
                    :href="route('broker.properties.index')"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition-all duration-200 flex items-center space-x-2"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        ></path>
                    </svg>
                    <span>Back to Properties</span>
                </Link>
            </div>
        </div>

        <form
            @submit.prevent="submit"
            enctype="multipart/form-data"
            class="space-y-8"
        >
            <!-- Basic Information -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3
                    class="text-xl font-semibold text-gray-900 mb-6 flex items-center"
                >
                    <svg
                        class="w-6 h-6 mr-2 text-blue-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        ></path>
                    </svg>
                    Basic Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            for="title"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Property Title *
                        </label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            :class="{
                                'border-red-500 ring-red-500': errors.title,
                            }"
                            placeholder="e.g., Prime Beachfront Lot in Panglao"
                            required
                        />
                        <div
                            v-if="errors.title"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.title }}
                        </div>
                    </div>

                    <div>
                        <label
                            for="type"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Property Type *
                        </label>
                        <select
                            id="type"
                            v-model="form.type"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            :class="{
                                'border-red-500 ring-red-500': errors.type,
                            }"
                            required
                        >
                            <option value="">Select Property Type</option>
                            <option
                                v-for="type in propertyTypes"
                                :key="type"
                                :value="type"
                            >
                                {{ formatType(type) }}
                            </option>
                        </select>
                        <div
                            v-if="errors.type"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.type }}
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label
                        for="description"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Description *
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="4"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        :class="{
                            'border-red-500 ring-red-500': errors.description,
                        }"
                        placeholder="Describe the property, its features, and surroundings..."
                        required
                    ></textarea>
                    <div
                        v-if="errors.description"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ errors.description }}
                    </div>
                </div>
            </div>

            <!-- Location Details -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3
                    class="text-xl font-semibold text-gray-900 mb-6 flex items-center"
                >
                    <svg
                        class="w-6 h-6 mr-2 text-green-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                        ></path>
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        ></path>
                    </svg>
                    Location Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            for="municipality"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Municipality *
                        </label>
                        <select
                            id="municipality"
                            v-model="form.municipality"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.municipality,
                            }"
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
                        <div
                            v-if="errors.municipality"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.municipality }}
                        </div>
                    </div>

                    <div>
                        <label
                            for="barangay"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Barangay *
                        </label>
                        <input
                            id="barangay"
                            v-model="form.barangay"
                            type="text"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            :class="{
                                'border-red-500 ring-red-500': errors.barangay,
                            }"
                            placeholder="e.g., Poblacion"
                            required
                        />
                        <div
                            v-if="errors.barangay"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.barangay }}
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label
                        for="address"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Complete Address
                    </label>
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="2"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        :class="{
                            'border-red-500 ring-red-500': errors.address,
                        }"
                        placeholder="Complete address with landmarks..."
                    ></textarea>
                    <div
                        v-if="errors.address"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ errors.address }}
                    </div>
                </div>

                <!-- Interactive Map Location Picker -->
                <div class="mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <label class="flex items-center space-x-3">
                            <input
                                v-model="enableGISMapping"
                                type="checkbox"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="text-sm font-medium text-gray-700">
                                🗺️ Enable GIS Mapping (Optional)
                            </span>
                        </label>
                        <div class="text-xs text-gray-500">
                            Add precise GPS coordinates for better property
                            visibility
                        </div>
                    </div>

                    <div
                        v-if="enableGISMapping"
                        class="border border-gray-200 rounded-lg p-4 bg-gray-50"
                    >
                        <MapLocationPicker
                            v-model="coordinates"
                            label="Property Location (GPS Coordinates)"
                            :error="
                                errors.coordinates_lat || errors.coordinates_lng
                            "
                            @location-selected="onLocationSelected"
                        />
                        <p class="text-xs text-gray-600 mt-2">
                            💡 Tip: Click on the map to set precise coordinates,
                            or search for an address to auto-locate the
                            property.
                        </p>
                    </div>

                    <div v-else class="text-center py-8 text-gray-500">
                        <svg
                            class="w-12 h-12 mx-auto mb-3 text-gray-300"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            ></path>
                        </svg>
                        <p class="text-sm">
                            GIS mapping is disabled. Enable it above to add
                            precise location coordinates.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pricing & Area -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3
                    class="text-xl font-semibold text-gray-900 mb-6 flex items-center"
                >
                    <svg
                        class="w-6 h-6 mr-2 text-green-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                        ></path>
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        ></path>
                    </svg>
                    Pricing & Area
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label
                            for="lot_area_sqm"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Lot Area (sqm) *
                        </label>
                        <input
                            id="lot_area_sqm"
                            v-model="form.lot_area_sqm"
                            type="number"
                            step="0.01"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.lot_area_sqm,
                            }"
                            placeholder="e.g., 1000"
                            required
                            @input="calculateTotalPrice"
                        />
                        <div
                            v-if="errors.lot_area_sqm"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.lot_area_sqm }}
                        </div>
                    </div>

                    <div>
                        <label
                            for="price_per_sqm"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Price per sqm (₱) *
                        </label>
                        <input
                            id="price_per_sqm"
                            v-model="form.price_per_sqm"
                            type="number"
                            step="0.01"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.price_per_sqm,
                            }"
                            placeholder="e.g., 5000"
                            required
                            @input="calculateTotalPrice"
                        />
                        <div
                            v-if="errors.price_per_sqm"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.price_per_sqm }}
                        </div>
                    </div>

                    <div>
                        <label
                            for="total_price"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Total Price (₱) *
                        </label>
                        <input
                            id="total_price"
                            v-model="form.total_price"
                            type="number"
                            step="0.01"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 bg-gray-50"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.total_price,
                            }"
                            placeholder="Auto-calculated"
                            readonly
                        />
                        <div
                            v-if="errors.total_price"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.total_price }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Legal Documents -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3
                    class="text-xl font-semibold text-gray-900 mb-6 flex items-center"
                >
                    <svg
                        class="w-6 h-6 mr-2 text-green-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                        ></path>
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        ></path>
                    </svg>
                    Legal Documents
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            for="title_type"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Title Type
                        </label>
                        <select
                            id="title_type"
                            v-model="form.title_type"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.title_type,
                            }"
                            required
                        >
                            <option value="">Select Title Type</option>
                            <option value="titled">Titled</option>
                            <option value="tax_declared">Tax Declared</option>
                            <option value="mother_title">Mother Title</option>
                            <option value="cct">CCT</option>
                        </select>
                        <div
                            v-if="errors.title_type"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.title_type }}
                        </div>
                    </div>

                    <div>
                        <label
                            for="title_number"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Title Number
                        </label>
                        <input
                            id="title_number"
                            v-model="form.title_number"
                            type="text"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.title_number,
                            }"
                            placeholder="e.g., TCT-12345"
                        />
                        <div
                            v-if="errors.title_number"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.title_number }}
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label
                        for="zoning_classification"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Zoning Classification
                    </label>
                    <input
                        id="zoning_classification"
                        v-model="form.zoning_classification"
                        type="text"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        :class="{
                            'border-red-500 ring-red-500':
                                errors.zoning_classification,
                        }"
                        placeholder="e.g., Residential, Commercial, Agricultural"
                    />
                    <div
                        v-if="errors.zoning_classification"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ errors.zoning_classification }}
                    </div>
                </div>
            </div>

            <!-- Utilities & Access -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3
                    class="text-xl font-semibold text-gray-900 mb-6 flex items-center"
                >
                    <svg
                        class="w-6 h-6 mr-2 text-green-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                        ></path>
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        ></path>
                    </svg>
                    Utilities & Access
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <label class="flex items-center space-x-3">
                        <input
                            v-model="form.road_access"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="text-sm font-medium text-gray-700"
                            >🛣️ Road Access</span
                        >
                    </label>

                    <label class="flex items-center space-x-3">
                        <input
                            v-model="form.electricity_available"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="text-sm font-medium text-gray-700"
                            >⚡ Electricity</span
                        >
                    </label>

                    <label class="flex items-center space-x-3">
                        <input
                            v-model="form.water_source"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="text-sm font-medium text-gray-700"
                            >💧 Water Source</span
                        >
                    </label>

                    <label class="flex items-center space-x-3">
                        <input
                            v-model="form.internet_available"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="text-sm font-medium text-gray-700"
                            >📶 Internet</span
                        >
                    </label>
                </div>
            </div>

            <!-- Additional Details -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3
                    class="text-xl font-semibold text-gray-900 mb-6 flex items-center"
                >
                    <svg
                        class="w-6 h-6 mr-2 text-green-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                        ></path>
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        ></path>
                    </svg>
                    Additional Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            for="nearby_landmarks"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Nearby Landmarks
                        </label>
                        <textarea
                            id="nearby_landmarks"
                            v-model="nearbyLandmarksText"
                            rows="3"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            placeholder="Enter landmarks separated by commas (e.g., Alona Beach, Bohol Bee Farm, Hinagdanan Cave)"
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Separate multiple landmarks with commas
                        </p>
                    </div>

                    <div>
                        <label
                            for="google_maps_link"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Google Maps Link
                        </label>
                        <input
                            id="google_maps_link"
                            v-model="form.google_maps_link"
                            type="url"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.google_maps_link,
                            }"
                            placeholder="https://maps.google.com/..."
                        />
                        <div
                            v-if="errors.google_maps_link"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.google_maps_link }}
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <label
                        for="additional_notes"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Additional Notes
                    </label>
                    <textarea
                        id="additional_notes"
                        v-model="form.additional_notes"
                        rows="3"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        placeholder="Any additional information about the property..."
                    ></textarea>
                </div>
            </div>

            <!-- Virtual Tour Section -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
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

                <div v-if="form.has_virtual_tour" class="space-y-6">
                    <!-- Virtual Tour Images -->
                    <div>
                        <label
                            for="virtual_tour_images"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            360° Images *
                        </label>
                        <input
                            id="virtual_tour_images"
                            type="file"
                            multiple
                            accept="image/*"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.virtual_tour_images,
                            }"
                            @change="handleVirtualTourImageUpload"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Upload 360-degree panoramic images. Recommended:
                            Equirectangular format, minimum 2048x1024 resolution
                        </p>
                        <div
                            v-if="errors.virtual_tour_images"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.virtual_tour_images }}
                        </div>
                    </div>

                    <!-- Virtual Tour Image Preview -->
                    <div v-if="virtualTourImagePreview.length > 0" class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">
                            Virtual Tour Images Preview:
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
                                    @click="removeVirtualTourImage(index)"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                                >
                                    ×
                                </button>
                                <div
                                    class="absolute bottom-2 left-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded"
                                >
                                    360° View {{ index + 1 }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GIS Data -->
                    <div>
                        <label
                            for="gis_data"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            GIS Data (Optional)
                        </label>
                        <textarea
                            id="gis_data"
                            v-model="form.gis_data"
                            rows="3"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            :class="{
                                'border-red-500 ring-red-500': errors.gis_data,
                            }"
                            placeholder="Enter GIS coordinates or geographic data in JSON format..."
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Optional: Add geographic information system data for
                            enhanced mapping
                        </p>
                        <div
                            v-if="errors.gis_data"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.gis_data }}
                        </div>
                    </div>

                    <!-- Tour Hotspots -->
                    <div>
                        <label
                            for="tour_hotspots"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Tour Hotspots (Optional)
                        </label>
                        <textarea
                            id="tour_hotspots"
                            v-model="form.tour_hotspots"
                            rows="4"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.tour_hotspots,
                            }"
                            placeholder="Enter hotspot data in JSON format..."
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Add interactive hotspots to highlight key features.
                            Format: JSON array with position and description
                            data
                        </p>
                        <div
                            v-if="errors.tour_hotspots"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.tour_hotspots }}
                        </div>
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

            <!-- Images -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Property Images
                </h3>
                <div>
                    <label
                        for="images"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Upload Images
                    </label>
                    <input
                        id="images"
                        type="file"
                        multiple
                        accept="image/*"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        :class="{
                            'border-red-500 ring-red-500': errors.images,
                        }"
                        @change="handleImageUpload"
                    />
                    <p class="text-xs text-gray-500 mt-1">
                        You can select multiple images. Supported formats: JPG,
                        PNG, GIF
                    </p>
                    <div v-if="errors.images" class="text-red-500 text-sm mt-1">
                        {{ errors.images }}
                    </div>
                </div>

                <!-- Image Preview -->
                <div v-if="imagePreview.length > 0" class="mt-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">
                        Image Preview:
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div
                            v-for="(image, index) in imagePreview"
                            :key="index"
                            class="relative"
                        >
                            <img
                                :src="image"
                                :alt="`Preview ${index + 1}`"
                                class="w-full h-24 object-cover rounded border"
                            />
                            <button
                                type="button"
                                @click="removeImage(index)"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                            >
                                ×
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Property Status
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            for="status"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Status *
                        </label>
                        <select
                            v-if="isAdmin"
                            id="status"
                            v-model="form.status"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{
                                'border-red-500': errors.status,
                            }"
                            required
                        >
                            <option value="">Select Status</option>
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                            <option value="under_negotiation">
                                Under Negotiation
                            </option>
                            <option value="off_market">Off Market</option>
                        </select>
                        <div
                            v-else
                            class="w-full rounded-md px-3 py-2 border border-gray-200 bg-gray-50"
                        >
                            <div class="text-sm text-gray-500">Status</div>
                            <div class="font-medium text-gray-900">
                                Available
                            </div>
                            <input
                                type="hidden"
                                v-model="form.status"
                                id="property-status"
                                name="status"
                            />
                        </div>
                        <div
                            v-if="errors.status"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.status }}
                        </div>
                    </div>

                    <div v-if="canFeatureProperty">
                        <label class="flex items-center space-x-3 mt-8">
                            <input
                                id="is_featured"
                                v-model="form.is_featured"
                                type="checkbox"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="text-sm font-medium text-gray-700"
                                >⭐ Feature this property</span
                            >
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <div class="flex justify-end space-x-4">
                    <Link
                        :href="route('broker.properties.index')"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-8 rounded-lg transition-all duration-200"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-8 rounded-lg shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
                    >
                        <svg
                            v-if="processing"
                            class="animate-spin w-5 h-5"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        <span>{{
                            processing ? "Creating..." : "Create Property"
                        }}</span>
                    </button>
                </div>
            </div>
        </form>

        <!-- Bohol Inspiration Section -->
        <div
            class="mt-12 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-8 border border-green-200"
        >
            <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">
                    🌴 Share Your Bohol Paradise
                </h3>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Help others discover the beauty of Bohol by listing your
                    property. From beachfront lots to mountain retreats, every
                    property tells a story.
                </p>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useForm, usePage, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import MapLocationPicker from "@/Components/MapLocationPicker.vue";

const page = usePage();

// GIS Mapping toggle
const enableGISMapping = ref(false);

const form = useForm({
    title: "",
    description: "",
    type: "",
    municipality: "",
    barangay: "",
    address: "",
    coordinates_lat: "",
    coordinates_lng: "",
    lot_area_sqm: "",
    price_per_sqm: "",
    total_price: "",
    title_type: "",
    title_number: "",
    zoning_classification: "",
    road_access: false,
    electricity_available: false,
    water_source: false,
    internet_available: false,
    nearby_landmarks: [],
    google_maps_link: "",
    additional_notes: "",
    images: [],
    status: "available",
    is_featured: false,
    // Virtual Tour fields
    has_virtual_tour: false,
    virtual_tour_images: [],
    gis_data: "",
    tour_hotspots: "",
});

const errors = computed(() => page.props.errors || {});
const processing = ref(false);
const imagePreview = ref([]);
const virtualTourImagePreview = ref([]);
const nearbyLandmarksText = ref("");

// Map coordinates for the MapLocationPicker component
const coordinates = ref({
    lat: form.coordinates_lat ? parseFloat(form.coordinates_lat) : null,
    lng: form.coordinates_lng ? parseFloat(form.coordinates_lng) : null,
});

// Property types aligned with backend App\Models\Property::TYPES
const propertyTypes = [
    "residential_lot",
    "agricultural_land",
    "commercial_lot",
    "industrial_lot",
    "beachfront",
    "mountain_view",
    "rice_field",
    "coconut_plantation",
    "subdivision_lot",
    "titled_land",
    "tax_declared",
];

// Bohol municipalities
const municipalities = [
    "Tagbilaran City",
    "Panglao",
    "Dauis",
    "Baclayon",
    "Loboc",
    "Carmen",
    "Loon",
    "Maribojoc",
    "Antequera",
    "Balilihan",
    "Batuan",
    "Bilar",
    "Buenavista",
    "Calape",
    "Candijay",
    "Clarin",
    "Corella",
    "Cortes",
    "Dimiao",
    "Duero",
    "Garcia Hernandez",
    "Getafe",
    "Guindulman",
    "Inabanga",
    "Jagna",
    "Lila",
    "Loay",
    "Mabini",
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
    "Well",
];

const canFeatureProperty = computed(() => {
    const user = page.props.auth.user;
    return user.role === "admin" || user.role === "broker";
});

const isAdmin = computed(() => {
    const user = page.props.auth.user;
    return user && user.role === "admin";
});

const formatType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

// Handle location selection from map
const onLocationSelected = (location) => {
    if (location && location.lat && location.lng) {
        form.coordinates_lat = location.lat.toString();
        form.coordinates_lng = location.lng.toString();
        coordinates.value = location;
    }
};

const calculateTotalPrice = () => {
    const area = parseFloat(form.lot_area_sqm) || 0;
    const pricePerSqm = parseFloat(form.price_per_sqm) || 0;
    form.total_price = area * pricePerSqm;
};

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files);
    form.images = files;

    // Create preview URLs
    imagePreview.value = [];
    files.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index) => {
    const newImages = Array.from(form.images);
    newImages.splice(index, 1);
    form.images = newImages;
    imagePreview.value.splice(index, 1);
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

const removeVirtualTourImage = (index) => {
    const newImages = Array.from(form.virtual_tour_images);
    newImages.splice(index, 1);
    form.virtual_tour_images = newImages;
    virtualTourImagePreview.value.splice(index, 1);
};

// Watch for changes in coordinates from the map component
watch(
    coordinates,
    (newCoords) => {
        if (newCoords && newCoords.lat && newCoords.lng) {
            form.coordinates_lat = newCoords.lat.toString();
            form.coordinates_lng = newCoords.lng.toString();
        }
    },
    { deep: true }
);

// Watch for GIS mapping toggle
watch(enableGISMapping, (enabled) => {
    if (!enabled) {
        // Clear coordinates when GIS mapping is disabled
        form.coordinates_lat = "";
        form.coordinates_lng = "";
        coordinates.value = { lat: null, lng: null };
    }
});

// Watch for changes in nearby landmarks text and convert to array
watch(nearbyLandmarksText, (newValue) => {
    if (newValue) {
        form.nearby_landmarks = newValue
            .split(",")
            .map((landmark) => landmark.trim())
            .filter((landmark) => landmark);
    } else {
        form.nearby_landmarks = [];
    }
});

const submit = () => {
    processing.value = true;

    form.post(route("broker.properties.store"), {
        // Fixed route name
        onSuccess: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};
</script>
