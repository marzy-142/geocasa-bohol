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
                        Quick property listing for brokers
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
            novalidate
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
                    <!-- Property Title -->
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
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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

                    <!-- Property Type -->
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
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{
                                'border-red-500 ring-red-500': errors.type,
                            }"
                            required
                        >
                            <option value="">Select Property Type</option>
                            <option
                                v-for="type in types"
                                :key="type.value"
                                :value="type.value"
                            >
                                {{ type.label }}
                            </option>
                        </select>
                        <div
                            v-if="errors.type"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.type }}
                        </div>
                    </div>

                    <!-- Other Type Specification (only if "other" is selected) -->
                    <div v-if="form.type === 'other'">
                        <label
                            for="type_other"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Specify Property Type *
                        </label>
                        <input
                            id="type_other"
                            v-model="form.type_other"
                            type="text"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.type_other,
                            }"
                            placeholder="e.g., Resort, Hotel, etc."
                            :required="form.type === 'other'"
                        />
                        <div
                            v-if="errors.type_other"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.type_other }}
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
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
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.description,
                            }"
                            placeholder="Describe the property features, location benefits, and other important details..."
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
            </div>

            <!-- Location -->
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
                    Location
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Municipality -->
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
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                                {{
                                    municipality.charAt(0).toUpperCase() +
                                    municipality.slice(1).replace("_", " ")
                                }}
                            </option>
                        </select>
                        <div
                            v-if="errors.municipality"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.municipality }}
                        </div>
                    </div>

                    <!-- Barangay -->
                    <div>
                        <label
                            for="barangay"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Barangay
                        </label>
                        <input
                            id="barangay"
                            v-model="form.barangay"
                            type="text"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., Poblacion"
                        />
                    </div>

                    <!-- Zoning Classification -->
                    <div>
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
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., Residential, Commercial, Agricultural"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Optional: Specify the zoning classification
                        </p>
                    </div>

                    <!-- Nearby Landmarks -->
                    <div class="md:col-span-2">
                        <label
                            for="nearby_landmarks"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Nearby Landmarks / Address Details
                        </label>
                        <input
                            id="nearby_landmarks"
                            v-model="nearbyLandmarksText"
                            type="text"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., Near Alona Beach, 5 mins to mall, Beside church"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Optional: Enter landmarks or address details
                            separated by commas
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
                        class="w-6 h-6 mr-2 text-yellow-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                        ></path>
                    </svg>
                    Pricing & Area
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Lot Area -->
                    <div>
                        <label
                            for="lot_area_sqm"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Lot Area (sqm) *
                        </label>
                        <input
                            id="lot_area_sqm"
                            v-model.number="form.lot_area_sqm"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.lot_area_sqm,
                            }"
                            placeholder="1000"
                            required
                            @input="handleLotAreaChange"
                        />
                        <div
                            v-if="errors.lot_area_sqm"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.lot_area_sqm }}
                        </div>
                    </div>

                    <!-- Price per sqm -->
                    <div>
                        <label
                            for="price_per_sqm"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Price per sqm (₱) *
                        </label>
                        <input
                            id="price_per_sqm"
                            v-model.number="form.price_per_sqm"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.price_per_sqm,
                            }"
                            placeholder="5000"
                            required
                            @input="calculateTotalPrice"
                        />
                        <div
                            v-if="errors.price_per_sqm"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.price_per_sqm }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Auto-calculated from total price ÷ lot area, or
                            enter manually
                        </div>
                    </div>

                    <!-- Total Price -->
                    <div>
                        <label
                            for="total_price"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Total Price (₱) *
                        </label>
                        <input
                            id="total_price"
                            v-model.number="form.total_price"
                            type="number"
                            min="0"
                            step="0.01"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            :class="{
                                'border-red-500 ring-red-500':
                                    errors.total_price,
                            }"
                            placeholder="5000000"
                            required
                            @input="calculatePricePerSqm"
                        />
                        <div
                            v-if="errors.total_price"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.total_price }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Auto-calculated from lot area × price per sqm, or
                            enter manually
                        </div>
                    </div>
                </div>
            </div>

            <!-- Title Type -->
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
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        ></path>
                    </svg>
                    Title Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Title Type -->
                    <div>
                        <label
                            for="title_type"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Title Type *
                        </label>
                        <select
                            id="title_type"
                            v-model="form.title_type"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                            <option value="cct">
                                CCT (Certificate of Confirmation of Title)
                            </option>
                        </select>
                        <div
                            v-if="errors.title_type"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.title_type }}
                        </div>
                    </div>

                    <!-- Title Number -->
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
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., TCT-12345"
                        />
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
            >
                <h3
                    class="text-xl font-semibold text-gray-900 mb-6 flex items-center"
                >
                    <svg
                        class="w-6 h-6 mr-2 text-indigo-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        ></path>
                    </svg>
                    Property Images
                </h3>

                <div>
                    <label
                        for="images"
                        class="block text-sm font-medium text-gray-700 mb-2"
                    >
                        Upload Images (Max 10 images, 2MB each)
                    </label>
                    <input
                        id="images"
                        type="file"
                        multiple
                        accept="image/*"
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @change="handleImageUpload"
                    />
                    <p class="text-sm text-gray-500 mt-2">
                        Supported formats: JPEG, PNG, JPG, GIF. First image will
                        be used as the main photo.
                    </p>

                    <!-- Image Preview -->
                    <div v-if="imagePreview.length > 0" class="mt-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div
                                v-for="(preview, index) in imagePreview"
                                :key="index"
                                class="relative"
                            >
                                <img
                                    :src="preview"
                                    :alt="`Preview ${index + 1}`"
                                    class="w-full h-24 object-cover rounded-lg border"
                                />
                                <span
                                    v-if="index === 0"
                                    class="absolute top-1 left-1 bg-blue-500 text-white text-xs px-2 py-1 rounded"
                                >
                                    Main
                                </span>
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
                </div>
            </div>

            <!-- Panoramic View (Optional) -->
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
                    Panoramic View (Optional)
                </h3>

                <div class="mb-4">
                    <label class="flex items-center space-x-3">
                        <input
                            v-model="form.has_virtual_tour"
                            type="checkbox"
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                        />
                        <span class="text-sm font-medium text-gray-700">
                            🌟 Enable Panoramic View for this property
                        </span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-6">
                        Panoramic views help potential buyers explore your
                        property with wide-angle imagery
                    </p>
                </div>

                <div v-if="form.has_virtual_tour" class="space-y-4">
                    <div>
                        <label
                            for="virtual_tour_images"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Upload Panoramic Images (JPG/PNG, max 5MB each)
                        </label>
                        <input
                            id="virtual_tour_images"
                            type="file"
                            multiple
                            accept=".jpg,.jpeg,.png"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            @change="handleVirtualTourUpload"
                        />
                        <p class="text-xs text-gray-500 mt-2">
                            Upload wide-angle panoramic images. Recommended:
                            Equirectangular format, minimum 2048x1024 resolution
                        </p>
                    </div>

                    <!-- Virtual Tour Preview -->
                    <div v-if="virtualTourPreview.length > 0" class="mt-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="(preview, index) in virtualTourPreview"
                                :key="index"
                                class="relative"
                            >
                                <img
                                    :src="preview"
                                    :alt="`Panoramic Preview ${index + 1}`"
                                    class="w-full h-32 object-cover rounded-lg border"
                                />
                                <span
                                    class="absolute top-1 left-1 bg-purple-500 text-white text-xs px-2 py-1 rounded"
                                >
                                    360°
                                </span>
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
                </div>
            </div>

            <!-- GIS Mapping (Optional) -->
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
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"
                        />
                    </svg>
                    Property Location on Map (Optional)
                </h3>

                <div class="mb-6">
                    <label class="flex items-center space-x-3">
                        <input
                            v-model="enableGISMapping"
                            type="checkbox"
                            class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                        />
                        <span class="text-sm font-medium text-gray-700">
                            🗺️ Pin exact location on map for better property
                            visibility
                        </span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-6">
                        Help buyers find your property easier with precise map
                        location
                    </p>
                </div>

                <div v-if="enableGISMapping" class="space-y-6">
                    <!-- Location Input Methods -->
                    <div
                        class="bg-green-50 border border-green-200 rounded-lg p-4"
                    >
                        <h4 class="text-sm font-medium text-green-800 mb-3">
                            Choose how to set location:
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Current Location Button -->
                            <button
                                type="button"
                                @click="getCurrentLocation"
                                :disabled="gettingLocation"
                                class="flex items-center justify-center space-x-2 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-200"
                            >
                                <svg
                                    v-if="gettingLocation"
                                    class="animate-spin w-4 h-4"
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
                                <svg
                                    v-else
                                    class="w-4 h-4"
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
                                <span>{{
                                    gettingLocation
                                        ? "Getting..."
                                        : "Use My Location"
                                }}</span>
                            </button>

                            <!-- Search Address Button -->
                            <button
                                type="button"
                                @click="showAddressSearch = !showAddressSearch"
                                class="flex items-center justify-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-200"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    ></path>
                                </svg>
                                <span>Search Address</span>
                            </button>

                            <!-- Click on Map Button -->
                            <button
                                type="button"
                                @click="
                                    showInteractiveMap = !showInteractiveMap
                                "
                                class="flex items-center justify-center space-x-2 bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg text-sm font-medium transition-colors duration-200"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"
                                    ></path>
                                </svg>
                                <span>Click on Map</span>
                            </button>
                        </div>
                    </div>

                    <!-- Address Search -->
                    <div
                        v-if="showAddressSearch"
                        class="bg-blue-50 border border-blue-200 rounded-lg p-4"
                    >
                        <label
                            for="address_search"
                            class="block text-sm font-medium text-blue-800 mb-2"
                        >
                            Search for your property address:
                        </label>
                        <div class="flex space-x-2">
                            <input
                                id="address_search"
                                v-model="addressSearch"
                                type="text"
                                class="flex-1 border border-blue-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="e.g., Sagbayan, Poblacion, Alona Beach, Tawala, Dao Terminal"
                                @keyup.enter="searchAddress"
                            />
                            <button
                                type="button"
                                @click="searchAddress"
                                :disabled="!addressSearch || searchingAddress"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200"
                            >
                                {{
                                    searchingAddress ? "Searching..." : "Search"
                                }}
                            </button>
                        </div>
                        <p class="text-xs text-blue-600 mt-2">
                            Try: Any Bohol municipality (Carmen, Panglao,
                            Loboc), tourist attractions (Chocolate Hills, Alona
                            Beach), or specific areas
                        </p>

                        <!-- Search Results -->
                        <div
                            v-if="searchResults.length > 0"
                            class="mt-3 space-y-2"
                        >
                            <p
                                class="text-xs font-medium text-blue-700 flex items-center"
                            >
                                <svg
                                    class="w-4 h-4 mr-1 text-red-500"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"
                                    />
                                </svg>
                                Found {{ searchResults.length }} location{{
                                    searchResults.length > 1 ? "s" : ""
                                }}
                                - Click to pin on map:
                            </p>
                            <div class="space-y-1">
                                <button
                                    v-for="result in searchResults"
                                    :key="result.id"
                                    type="button"
                                    @click="selectSearchResult(result)"
                                    class="w-full text-left px-3 py-2 bg-white border border-blue-200 rounded hover:bg-blue-50 hover:border-blue-300 text-sm transition-all duration-200 group"
                                >
                                    <div class="flex items-start space-x-2">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg
                                                class="w-4 h-4 text-red-500 group-hover:text-red-600"
                                                fill="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"
                                                />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div
                                                class="font-medium text-blue-900 group-hover:text-blue-800"
                                            >
                                                {{ result.name }}
                                            </div>
                                            <div
                                                class="text-xs text-blue-600 group-hover:text-blue-700"
                                            >
                                                {{ result.description }}
                                            </div>
                                            <div
                                                class="text-xs text-gray-500 mt-1"
                                            >
                                                📍 {{ result.lat.toFixed(4) }},
                                                {{ result.lng.toFixed(4) }}
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg
                                                class="w-4 h-4 text-gray-400 group-hover:text-blue-500"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                                                ></path>
                                            </svg>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Map -->
                    <div
                        v-if="showInteractiveMap"
                        class="bg-purple-50 border border-purple-200 rounded-lg p-4"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-purple-800">
                                Click on the map to set property location:
                            </h4>
                            <div class="flex space-x-2">
                                <button
                                    type="button"
                                    @click="zoomToCurrentLocation"
                                    class="text-xs bg-purple-600 hover:bg-purple-700 text-white px-2 py-1 rounded"
                                >
                                    📍 Find Me
                                </button>
                                <button
                                    type="button"
                                    @click="resetMapView"
                                    class="text-xs bg-gray-600 hover:bg-gray-700 text-white px-2 py-1 rounded"
                                >
                                    🏝️ Bohol
                                </button>
                            </div>
                        </div>

                        <!-- Map Container -->
                        <div
                            ref="mapContainer"
                            id="property-map"
                            class="bg-gray-200 rounded-lg h-64 relative border-2 border-dashed border-purple-300 hover:border-purple-400 transition-colors duration-200 overflow-hidden"
                        >
                            <!-- Loading indicator for map -->
                            <div
                                v-if="!mapInitialized"
                                class="absolute inset-0 flex items-center justify-center text-gray-600 z-10 bg-white"
                            >
                                <div class="text-center">
                                    <svg
                                        class="w-12 h-12 mx-auto mb-2 animate-spin"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                        ></path>
                                    </svg>
                                    <p class="text-sm font-medium">
                                        Loading OpenStreetMap...
                                    </p>
                                    <p class="text-xs mt-1">
                                        Free & unlimited mapping
                                    </p>
                                </div>
                            </div>

                            <!-- Map Status Overlay -->
                            <div
                                v-if="
                                    form.coordinates_lat && form.coordinates_lng
                                "
                                class="absolute top-2 left-2 bg-white bg-opacity-95 rounded-lg px-3 py-2 shadow-lg text-sm z-10 border-2 border-green-300"
                            >
                                <div class="flex items-center space-x-2">
                                    <div
                                        class="w-3 h-3 bg-red-500 rounded-full animate-pulse"
                                    ></div>
                                    <span
                                        class="font-semibold text-green-800"
                                        >{{
                                            selectedLocationName ||
                                            "Property Location"
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="text-green-700 mt-1 text-xs font-mono"
                                >
                                    {{ form.coordinates_lat?.toFixed(6) }},
                                    {{ form.coordinates_lng?.toFixed(6) }}
                                </div>
                                <div class="text-green-600 mt-1 text-xs">
                                    📍 Location pinned successfully
                                </div>
                            </div>

                            <!-- Custom Map Controls -->
                            <div
                                class="absolute top-2 right-2 flex flex-col space-y-1 z-10"
                            >
                                <button
                                    type="button"
                                    @click="zoomIn"
                                    class="w-8 h-8 bg-white hover:bg-gray-100 border border-gray-300 rounded flex items-center justify-center text-gray-700 shadow-sm transition-colors"
                                    title="Zoom In"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                        ></path>
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    @click="zoomOut"
                                    class="w-8 h-8 bg-white hover:bg-gray-100 border border-gray-300 rounded flex items-center justify-center text-gray-700 shadow-sm transition-colors"
                                    title="Zoom Out"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M18 12H6"
                                        ></path>
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    @click="resetMapView"
                                    class="w-8 h-8 bg-white hover:bg-gray-100 border border-gray-300 rounded flex items-center justify-center text-gray-700 shadow-sm transition-colors"
                                    title="Reset View"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                        ></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Free mapping badge -->
                            <div
                                class="absolute bottom-2 right-2 bg-green-100 border border-green-300 rounded px-2 py-1 text-xs z-10"
                            >
                                <span class="font-medium text-green-800"
                                    >🌍 Free Mapping</span
                                >
                            </div>
                        </div>

                        <!-- Map Instructions -->
                        <div
                            class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs"
                        >
                            <div class="flex items-start space-x-2">
                                <div
                                    class="w-4 h-4 bg-purple-100 rounded flex items-center justify-center mt-0.5"
                                >
                                    <span class="text-purple-600">1</span>
                                </div>
                                <div>
                                    <p class="font-medium text-purple-800">
                                        Search first (optional)
                                    </p>
                                    <p class="text-purple-600">
                                        Use address search to find general area
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-2">
                                <div
                                    class="w-4 h-4 bg-purple-100 rounded flex items-center justify-center mt-0.5"
                                >
                                    <span class="text-purple-600">2</span>
                                </div>
                                <div>
                                    <p class="font-medium text-purple-800">
                                        Click to pin location
                                    </p>
                                    <p class="text-purple-600">
                                        Click exactly where your property is
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Coordinates (Advanced) -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <button
                            type="button"
                            @click="showManualCoords = !showManualCoords"
                            class="flex items-center justify-between w-full text-left"
                        >
                            <span class="text-sm font-medium text-gray-700">
                                Advanced: Enter coordinates manually
                            </span>
                            <svg
                                class="w-4 h-4 transform transition-transform duration-200"
                                :class="{ 'rotate-180': showManualCoords }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                ></path>
                            </svg>
                        </button>

                        <div
                            v-if="showManualCoords"
                            class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            <div>
                                <label
                                    for="coordinates_lat"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Latitude
                                </label>
                                <input
                                    id="coordinates_lat"
                                    v-model.number="form.coordinates_lat"
                                    type="number"
                                    step="any"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="e.g., 9.6340"
                                />
                            </div>
                            <div>
                                <label
                                    for="coordinates_lng"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Longitude
                                </label>
                                <input
                                    id="coordinates_lng"
                                    v-model.number="form.coordinates_lng"
                                    type="number"
                                    step="any"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="e.g., 123.8530"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Current Location Display -->
                    <div
                        v-if="form.coordinates_lat && form.coordinates_lng"
                        class="bg-green-50 border-2 border-green-300 rounded-lg p-4 shadow-sm"
                    >
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center"
                                >
                                    <svg
                                        class="w-5 h-5 text-green-600"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <svg
                                        class="w-5 h-5 text-green-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 13l4 4L19 7"
                                        ></path>
                                    </svg>
                                    <span
                                        class="text-sm font-semibold text-green-800"
                                        >📍 Location Pinned Successfully!</span
                                    >
                                </div>

                                <div v-if="selectedLocationName" class="mb-2">
                                    <p
                                        class="text-sm font-medium text-green-800"
                                    >
                                        {{ selectedLocationName }}
                                    </p>
                                </div>

                                <div
                                    class="bg-white bg-opacity-60 rounded px-3 py-2 mb-2"
                                >
                                    <p class="text-sm text-green-700 font-mono">
                                        📍 Coordinates:
                                        {{ form.coordinates_lat?.toFixed(6) }},
                                        {{ form.coordinates_lng?.toFixed(6) }}
                                    </p>
                                </div>

                                <p class="text-xs text-green-600 mb-3">
                                    ✅ This location will help buyers find your
                                    property on maps and search results
                                </p>

                                <div class="flex items-center space-x-3">
                                    <button
                                        type="button"
                                        @click="clearLocation"
                                        class="text-xs text-red-600 hover:text-red-800 hover:bg-red-50 px-2 py-1 rounded underline transition-colors"
                                    >
                                        🗑️ Clear location
                                    </button>
                                    <button
                                        v-if="!showInteractiveMap"
                                        type="button"
                                        @click="showInteractiveMap = true"
                                        class="text-xs text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-2 py-1 rounded underline transition-colors"
                                    >
                                        🗺️ View on map
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Features/Amenities -->
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
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    Property Features & Amenities
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Road Access -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                id="road_access"
                                v-model="form.road_access"
                                type="checkbox"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                        </div>
                        <div class="ml-3">
                            <label
                                for="road_access"
                                class="font-medium text-gray-700 cursor-pointer"
                            >
                                Road Access
                            </label>
                            <p class="text-sm text-gray-500">
                                Property has accessible road connection
                            </p>
                        </div>
                    </div>

                    <!-- Electricity Available -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                id="electricity_available"
                                v-model="form.electricity_available"
                                type="checkbox"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                        </div>
                        <div class="ml-3">
                            <label
                                for="electricity_available"
                                class="font-medium text-gray-700 cursor-pointer"
                            >
                                Electricity Available
                            </label>
                            <p class="text-sm text-gray-500">
                                Electric power is available on-site
                            </p>
                        </div>
                    </div>

                    <!-- Water Source -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                id="water_source"
                                v-model="form.water_source"
                                type="checkbox"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                        </div>
                        <div class="ml-3">
                            <label
                                for="water_source"
                                class="font-medium text-gray-700 cursor-pointer"
                            >
                                Water Source
                            </label>
                            <p class="text-sm text-gray-500">
                                Water supply or source available
                            </p>
                        </div>
                    </div>

                    <!-- Internet Available -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                id="internet_available"
                                v-model="form.internet_available"
                                type="checkbox"
                                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                        </div>
                        <div class="ml-3">
                            <label
                                for="internet_available"
                                class="font-medium text-gray-700 cursor-pointer"
                            >
                                Internet Available
                            </label>
                            <p class="text-sm text-gray-500">
                                Internet connectivity is available
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Featured Property (Admin Only) -->
            <div
                v-if="pageProps.auth?.user?.role === 'admin'"
                class="bg-gradient-to-r from-yellow-50 to-amber-50 rounded-lg shadow-sm border-2 border-yellow-300 p-6"
            >
                <h3
                    class="text-xl font-semibold text-gray-900 mb-4 flex items-center"
                >
                    <svg
                        class="w-6 h-6 mr-2 text-yellow-600"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                        />
                    </svg>
                    Featured Listing
                    <span
                        class="ml-2 text-xs bg-yellow-600 text-white px-2 py-1 rounded-full"
                    >
                        Admin Only
                    </span>
                </h3>

                <div
                    class="flex items-start bg-white rounded-lg p-4 border border-yellow-200"
                >
                    <div class="flex items-center h-5">
                        <input
                            id="is_featured"
                            v-model="form.is_featured"
                            type="checkbox"
                            class="w-5 h-5 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
                        />
                    </div>
                    <div class="ml-3">
                        <label
                            for="is_featured"
                            class="font-semibold text-gray-900 cursor-pointer flex items-center"
                        >
                            ⭐ Mark as Featured Property
                        </label>
                        <p class="text-sm text-gray-600 mt-1">
                            Featured properties are displayed prominently on the
                            homepage and in search results. They get priority
                            placement and increased visibility to potential
                            buyers.
                        </p>
                        <p class="text-xs text-yellow-700 mt-2 font-medium">
                            💡 Note: Brokers can feature their own properties
                            after creation (up to 5 at a time)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <Link
                    :href="route('broker.properties.index')"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-3 px-6 rounded-lg transition-all duration-200"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    @click="handleSubmitClick"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
                >
                    <svg
                        v-if="form.processing"
                        class="animate-spin h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
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
                        form.processing
                            ? "Creating Property..."
                            : "Create Property"
                    }}</span>
                </button>
            </div>
        </form>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from "vue";
import { useForm, usePage, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

// Props
const props = defineProps({
    municipalities: Array,
    types: Array,
});

// Page data
const { props: pageProps } = usePage();
const errors = computed(() => pageProps.errors);

// Form data
const form = useForm({
    title: "",
    type: "",
    type_other: "",
    description: "",
    municipality: "",
    barangay: "",
    address: "",
    lot_area_sqm: null,
    price_per_sqm: null,
    total_price: null,
    title_type: "",
    title_number: "",
    zoning_classification: "",
    nearby_landmarks: [],
    images: [],
    has_virtual_tour: false,
    virtual_tour_images: [],
    coordinates_lat: null,
    coordinates_lng: null,
    status: "available",
    // Property features/amenities
    road_access: false,
    electricity_available: false,
    water_source: false,
    internet_available: false,
    is_featured: false,
});

// Image handling
const imagePreview = ref([]);
const virtualTourPreview = ref([]);
const processing = ref(false);
const nearbyLandmarksText = ref("");
const enableGISMapping = ref(false);

// GIS/Location handling
const showAddressSearch = ref(false);
const showInteractiveMap = ref(false);
const showManualCoords = ref(false);
const addressSearch = ref("");
const searchingAddress = ref(false);
const gettingLocation = ref(false);
const searchResults = ref([]);
const selectedLocationName = ref("");
const mapInitialized = ref(false);
const mapZoomLevel = ref(10);
const leafletMap = ref(null);
const mapContainer = ref(null);
const currentMarker = ref(null);

// Leaflet Map initialization
const initializeLeafletMap = async () => {
    if (!mapContainer.value || mapInitialized.value) return;

    try {
        // Create Leaflet map
        leafletMap.value = L.map(mapContainer.value, {
            center: [9.634, 123.853], // Tagbilaran center
            zoom: mapZoomLevel.value,
            zoomControl: false, // We'll add custom controls
        });

        // Add OpenStreetMap tiles
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution:
                '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(leafletMap.value);

        // Add satellite layer option (using Esri satellite)
        const satelliteLayer = L.tileLayer(
            "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
            {
                attribution:
                    '© <a href="https://www.esri.com/">Esri</a> © <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19,
            }
        );

        // Layer control
        const baseLayers = {
            "Street Map": L.tileLayer(
                "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
                {
                    attribution: "© OpenStreetMap contributors",
                }
            ),
            Satellite: satelliteLayer,
        };

        L.control.layers(baseLayers).addTo(leafletMap.value);

        // Add click handler
        leafletMap.value.on("click", (e) => {
            const { lat, lng } = e.latlng;

            // Update form coordinates
            form.coordinates_lat = parseFloat(lat.toFixed(6));
            form.coordinates_lng = parseFloat(lng.toFixed(6));

            // Add/update marker
            addLeafletMarker(lat, lng);

            // Try to get place name
            reverseGeocode(lat, lng);
        });

        mapInitialized.value = true;

        // If coordinates already exist, show them on map
        if (form.coordinates_lat && form.coordinates_lng) {
            addLeafletMarker(form.coordinates_lat, form.coordinates_lng);
            centerMapOnLocation(form.coordinates_lat, form.coordinates_lng);
        }
    } catch (error) {
        console.error("Error initializing Leaflet map:", error);
        alert("Unable to load map. Please check your internet connection.");
    }
};

// Add or update marker on Leaflet map
const addLeafletMarker = (lat, lng) => {
    if (!leafletMap.value) return;

    // Remove existing marker
    if (currentMarker.value) {
        leafletMap.value.removeLayer(currentMarker.value);
    }

    // Create custom red marker icon
    const redIcon = L.icon({
        iconUrl:
            "data:image/svg+xml;base64," +
            btoa(`
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ef4444" width="32" height="32">
                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
            </svg>
        `),
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32],
    });

    // Create new marker
    currentMarker.value = L.marker([lat, lng], {
        icon: redIcon,
        draggable: true,
    }).addTo(leafletMap.value);

    // Add popup with location info
    const popupContent = `
        <div class="text-center">
            <strong>${
                selectedLocationName.value || "Property Location"
            }</strong><br>
            <small>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}</small><br>
            <small class="text-gray-500">Drag marker to adjust</small>
        </div>
    `;
    currentMarker.value.bindPopup(popupContent).openPopup();

    // Handle marker drag
    currentMarker.value.on("dragend", (e) => {
        const newPos = e.target.getLatLng();
        form.coordinates_lat = parseFloat(newPos.lat.toFixed(6));
        form.coordinates_lng = parseFloat(newPos.lng.toFixed(6));

        reverseGeocode(newPos.lat, newPos.lng);
    });
};

// Watch for map visibility changes
watch(showInteractiveMap, async (newValue) => {
    if (newValue && !mapInitialized.value) {
        await nextTick();
        setTimeout(() => {
            initializeLeafletMap();
        }, 100);
    }
});

// Cleanup on component unmount
onUnmounted(() => {
    if (leafletMap.value) {
        leafletMap.value.remove();
    }
});

// Watch for map visibility changes
watch(showInteractiveMap, async (newValue) => {
    if (newValue && !mapInitialized.value) {
        await nextTick();
        setTimeout(() => {
            initializeLeafletMap();
        }, 100);
    }
});

// Cleanup on component unmount
onUnmounted(() => {
    if (leafletMap.value) {
        leafletMap.value.remove();
    }
});

// Watch for changes in area or price per sqm to auto-calculate total
watch([() => form.lot_area_sqm, () => form.price_per_sqm], () => {
    calculateTotalPrice();
});

// Watch for nearby landmarks text changes - convert comma-separated to array
watch(nearbyLandmarksText, (newValue) => {
    if (newValue) {
        form.nearby_landmarks = newValue
            .split(",")
            .map((landmark) => landmark.trim())
            .filter((landmark) => landmark.length > 0);
    } else {
        form.nearby_landmarks = [];
    }
});

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files);

    // Limit to 10 images
    if (files.length > 10) {
        alert("You can upload a maximum of 10 images.");
        return;
    }

    // Clear previous images
    imagePreview.value = [];
    form.images = [];

    files.forEach((file) => {
        // File size validation (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert(`${file.name} is too large. Maximum file size is 2MB.`);
            return;
        }

        // Add to form
        form.images.push(file);

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index) => {
    imagePreview.value.splice(index, 1);
    form.images.splice(index, 1);
};

const handleVirtualTourUpload = (event) => {
    const files = Array.from(event.target.files);

    console.log("Virtual tour upload started, files:", files);

    // Limit to 20 images
    if (files.length > 20) {
        alert("You can upload a maximum of 20 panoramic images.");
        return;
    }

    // Clear previous images
    virtualTourPreview.value = [];
    form.virtual_tour_images = [];

    files.forEach((file, index) => {
        console.log(
            `Processing file ${index}:`,
            file.name,
            file.type,
            file.size
        );

        // File size validation (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert(`${file.name} is too large. Maximum file size is 5MB.`);
            return;
        }

        // File type validation - check file extension instead of mime type
        const fileName = file.name.toLowerCase();
        if (
            !fileName.endsWith(".jpg") &&
            !fileName.endsWith(".jpeg") &&
            !fileName.endsWith(".png")
        ) {
            alert(`${file.name} must be a JPG, JPEG, or PNG file.`);
            return;
        }

        console.log(`File ${file.name} passed validation, adding to form`);

        // Add to form
        form.virtual_tour_images.push(file);

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            virtualTourPreview.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });

    console.log("Final virtual_tour_images array:", form.virtual_tour_images);
};

const removeVirtualTourImage = (index) => {
    virtualTourPreview.value.splice(index, 1);
    form.virtual_tour_images.splice(index, 1);
};

const getCurrentLocation = () => {
    if (!navigator.geolocation) {
        alert("Geolocation is not supported by this browser.");
        return;
    }

    gettingLocation.value = true;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.coordinates_lat = position.coords.latitude;
            form.coordinates_lng = position.coords.longitude;
            gettingLocation.value = false;

            // Provide feedback to user
            alert(
                `Location set successfully!\nLatitude: ${position.coords.latitude.toFixed(
                    6
                )}\nLongitude: ${position.coords.longitude.toFixed(6)}`
            );
        },
        (error) => {
            gettingLocation.value = false;
            let errorMessage = "Error getting location: ";
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage += "Location access denied by user.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage += "Location information unavailable.";
                    break;
                case error.TIMEOUT:
                    errorMessage += "Location request timed out.";
                    break;
                default:
                    errorMessage += "An unknown error occurred.";
                    break;
            }
            alert(errorMessage);
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0,
        }
    );
};

const searchAddress = async () => {
    if (!addressSearch.value.trim()) {
        alert("Please enter an address to search.");
        return;
    }

    searchingAddress.value = true;
    searchResults.value = [];

    try {
        // Comprehensive Bohol locations database including municipalities, barangays, sitios, and areas
        const bohoLocations = [
            // TAGBILARAN CITY - Barangays and Areas
            {
                id: 1,
                name: "Tagbilaran City",
                description: "Capital city and commercial center",
                lat: 9.634,
                lng: 123.853,
                keywords: ["tagbilaran", "city", "capital", "commercial"],
            },
            {
                id: 2,
                name: "Cogon",
                description: "Tagbilaran - Barangay Cogon, residential area",
                lat: 9.6345,
                lng: 123.8525,
                keywords: ["cogon", "barangay", "tagbilaran", "residential"],
            },
            {
                id: 3,
                name: "Dao",
                description: "Tagbilaran - Barangay Dao, terminal area",
                lat: 9.645,
                lng: 123.852,
                keywords: ["dao", "barangay", "tagbilaran", "terminal"],
            },
            {
                id: 4,
                name: "Bool",
                description: "Tagbilaran - Barangay Bool, commercial district",
                lat: 9.638,
                lng: 123.855,
                keywords: ["bool", "barangay", "tagbilaran", "commercial"],
            },
            {
                id: 5,
                name: "Poblacion",
                description: "Tagbilaran - Barangay Poblacion, city center",
                lat: 9.634,
                lng: 123.853,
                keywords: ["poblacion", "barangay", "tagbilaran", "center"],
            },
            {
                id: 6,
                name: "Manga",
                description: "Tagbilaran - Barangay Manga, residential",
                lat: 9.632,
                lng: 123.8515,
                keywords: ["manga", "barangay", "tagbilaran"],
            },
            {
                id: 7,
                name: "Taloto",
                description: "Tagbilaran - Barangay Taloto, suburban area",
                lat: 9.6365,
                lng: 123.8545,
                keywords: ["taloto", "barangay", "tagbilaran"],
            },
            {
                id: 8,
                name: "Ubujan",
                description: "Tagbilaran - Barangay Ubujan, port area",
                lat: 9.631,
                lng: 123.858,
                keywords: ["ubujan", "barangay", "tagbilaran", "port"],
            },

            // PANGLAO - Barangays and Tourist Areas
            {
                id: 9,
                name: "Panglao",
                description: "Island municipality - Tourist destination",
                lat: 9.55,
                lng: 123.7833,
                keywords: ["panglao", "municipality", "island", "tourist"],
            },
            {
                id: 10,
                name: "Alona Beach",
                description: "Panglao - Barangay Tawala, main beach area",
                lat: 9.5547,
                lng: 123.7598,
                keywords: ["alona", "beach", "panglao", "tawala", "tourist"],
            },
            {
                id: 11,
                name: "Dumaluan Beach",
                description: "Panglao - Barangay Doljo, white sand beach",
                lat: 9.538,
                lng: 123.7825,
                keywords: [
                    "dumaluan",
                    "beach",
                    "panglao",
                    "doljo",
                    "white sand",
                ],
            },
            {
                id: 12,
                name: "Tawala",
                description: "Panglao - Barangay Tawala, Alona Beach area",
                lat: 9.5547,
                lng: 123.7598,
                keywords: ["tawala", "barangay", "panglao", "alona"],
            },
            {
                id: 13,
                name: "Doljo",
                description: "Panglao - Barangay Doljo, Dumaluan area",
                lat: 9.538,
                lng: 123.7825,
                keywords: ["doljo", "barangay", "panglao", "dumaluan"],
            },
            {
                id: 14,
                name: "Libaong",
                description: "Panglao - Barangay Libaong, residential",
                lat: 9.56,
                lng: 123.79,
                keywords: ["libaong", "barangay", "panglao"],
            },
            {
                id: 15,
                name: "Poblacion Panglao",
                description: "Panglao - Town center",
                lat: 9.565,
                lng: 123.795,
                keywords: ["poblacion", "barangay", "panglao", "center"],
            },
            {
                id: 16,
                name: "Bohol-Panglao International Airport",
                description: "Panglao - Main airport",
                lat: 9.5651,
                lng: 123.8534,
                keywords: ["airport", "panglao", "international", "bpia"],
            },

            // DAUIS - Barangays and Areas
            {
                id: 17,
                name: "Dauis",
                description: "Municipality in Panglao Island",
                lat: 9.6241,
                lng: 123.8449,
                keywords: ["dauis", "municipality", "panglao"],
            },
            {
                id: 18,
                name: "Hinagdanan Cave",
                description: "Dauis - Barangay Bingag, famous cave",
                lat: 9.6122,
                lng: 123.8234,
                keywords: [
                    "hinagdanan",
                    "cave",
                    "dauis",
                    "bingag",
                    "attraction",
                ],
            },
            {
                id: 19,
                name: "Bingag",
                description: "Dauis - Barangay Bingag, cave area",
                lat: 9.6122,
                lng: 123.8234,
                keywords: ["bingag", "barangay", "dauis", "cave"],
            },
            {
                id: 20,
                name: "Biking",
                description: "Dauis - Barangay Biking",
                lat: 9.62,
                lng: 123.84,
                keywords: ["biking", "barangay", "dauis"],
            },
            {
                id: 21,
                name: "Maribago",
                description: "Dauis - Barangay Maribago",
                lat: 9.628,
                lng: 123.838,
                keywords: ["maribago", "barangay", "dauis"],
            },

            // BACLAYON - Historic Town and Barangays
            {
                id: 22,
                name: "Baclayon",
                description: "Historic municipality with heritage church",
                lat: 9.6157,
                lng: 123.9054,
                keywords: ["baclayon", "municipality", "historic"],
            },
            {
                id: 23,
                name: "Baclayon Church",
                description: "Baclayon - Historic heritage church",
                lat: 9.6157,
                lng: 123.9054,
                keywords: ["baclayon", "church", "historic", "heritage"],
            },
            {
                id: 24,
                name: "Poblacion Baclayon",
                description: "Baclayon - Town center",
                lat: 9.6157,
                lng: 123.9054,
                keywords: ["poblacion", "baclayon", "center"],
            },
            {
                id: 25,
                name: "Tanday",
                description: "Baclayon - Barangay Tanday",
                lat: 9.61,
                lng: 123.91,
                keywords: ["tanday", "barangay", "baclayon"],
            },
            {
                id: 26,
                name: "Landayao",
                description: "Baclayon - Barangay Landayao",
                lat: 9.618,
                lng: 123.9,
                keywords: ["landayao", "barangay", "baclayon"],
            },
            {
                id: 27,
                name: "Pamilacan Island",
                description: "Baclayon - Island barangay, dolphin watching",
                lat: 9.5167,
                lng: 123.8833,
                keywords: [
                    "pamilacan",
                    "island",
                    "barangay",
                    "baclayon",
                    "dolphin",
                ],
            },

            // LOBOC - River Town and Barangays
            {
                id: 28,
                name: "Loboc",
                description: "Municipality - Famous river cruise",
                lat: 9.6389,
                lng: 124.0301,
                keywords: ["loboc", "municipality", "river"],
            },
            {
                id: 29,
                name: "Loboc River",
                description: "Loboc - Famous river cruise destination",
                lat: 9.6389,
                lng: 124.0301,
                keywords: ["loboc", "river", "cruise"],
            },
            {
                id: 30,
                name: "Bamboo Hanging Bridge",
                description: "Loboc - Tourist attraction",
                lat: 9.64,
                lng: 124.032,
                keywords: ["bamboo", "hanging bridge", "loboc"],
            },
            {
                id: 31,
                name: "Poblacion Loboc",
                description: "Loboc - Town center",
                lat: 9.6389,
                lng: 124.0301,
                keywords: ["poblacion", "loboc", "center"],
            },
            {
                id: 32,
                name: "Canayaon",
                description: "Loboc - Barangay Canayaon",
                lat: 9.635,
                lng: 124.025,
                keywords: ["canayaon", "barangay", "loboc"],
            },
            {
                id: 33,
                name: "Napo",
                description: "Loboc - Barangay Napo",
                lat: 9.645,
                lng: 124.04,
                keywords: ["napo", "barangay", "loboc"],
            },

            // CARMEN - Chocolate Hills Area and Barangays
            {
                id: 34,
                name: "Carmen",
                description: "Municipality - Chocolate Hills location",
                lat: 9.9169,
                lng: 124.1695,
                keywords: ["carmen", "municipality", "chocolate hills"],
            },
            {
                id: 35,
                name: "Chocolate Hills",
                description: "Carmen - Famous geological formation",
                lat: 9.9169,
                lng: 124.1695,
                keywords: ["chocolate", "hills", "carmen", "tourist"],
            },
            {
                id: 36,
                name: "Chocolate Hills Complex",
                description: "Carmen - Main viewing area",
                lat: 9.9169,
                lng: 124.1695,
                keywords: ["chocolate hills", "complex", "carmen", "viewing"],
            },
            {
                id: 37,
                name: "Poblacion Carmen",
                description: "Carmen - Town center",
                lat: 9.91,
                lng: 124.16,
                keywords: ["poblacion", "carmen", "center"],
            },
            {
                id: 38,
                name: "Katipunan",
                description: "Carmen - Barangay Katipunan",
                lat: 9.92,
                lng: 124.17,
                keywords: ["katipunan", "barangay", "carmen"],
            },
            {
                id: 39,
                name: "Guadalupe",
                description: "Carmen - Barangay Guadalupe",
                lat: 9.915,
                lng: 124.165,
                keywords: ["guadalupe", "barangay", "carmen"],
            },

            // SAGBAYAN - Municipality and Barangays
            {
                id: 40,
                name: "Sagbayan",
                description: "Municipality in central Bohol",
                lat: 9.8833,
                lng: 124.1,
                keywords: ["sagbayan", "municipality"],
            },
            {
                id: 41,
                name: "Poblacion Sagbayan",
                description: "Sagbayan - Town center",
                lat: 9.8833,
                lng: 124.1,
                keywords: ["poblacion", "sagbayan", "center"],
            },
            {
                id: 42,
                name: "Cabog",
                description: "Sagbayan - Barangay Cabog",
                lat: 9.88,
                lng: 124.095,
                keywords: ["cabog", "barangay", "sagbayan"],
            },
            {
                id: 43,
                name: "Canmano",
                description: "Sagbayan - Barangay Canmano",
                lat: 9.885,
                lng: 124.105,
                keywords: ["canmano", "barangay", "sagbayan"],
            },
            {
                id: 44,
                name: "Manaba",
                description: "Sagbayan - Barangay Manaba",
                lat: 9.888,
                lng: 124.102,
                keywords: ["manaba", "barangay", "sagbayan"],
            },

            // CORELLA - Tarsier Sanctuary Area
            {
                id: 45,
                name: "Corella",
                description: "Municipality in western Bohol",
                lat: 9.6833,
                lng: 123.9167,
                keywords: ["corella", "municipality"],
            },
            {
                id: 46,
                name: "Tarsier Sanctuary",
                description: "Corella - Tarsier conservation area",
                lat: 9.6833,
                lng: 123.9167,
                keywords: ["tarsier", "sanctuary", "corella", "conservation"],
            },
            {
                id: 47,
                name: "Poblacion Corella",
                description: "Corella - Town center",
                lat: 9.6833,
                lng: 123.9167,
                keywords: ["poblacion", "corella", "center"],
            },
            {
                id: 48,
                name: "Canapnapan",
                description: "Corella - Barangay Canapnapan",
                lat: 9.68,
                lng: 123.92,
                keywords: ["canapnapan", "barangay", "corella"],
            },

            // BILAR - Man-made Forest Area
            {
                id: 49,
                name: "Bilar",
                description: "Municipality - Man-made Forest area",
                lat: 9.7667,
                lng: 124.0833,
                keywords: ["bilar", "municipality", "forest"],
            },
            {
                id: 50,
                name: "Mahogany Forest",
                description: "Bilar - Man-made forest",
                lat: 9.7667,
                lng: 124.0833,
                keywords: ["mahogany", "forest", "bilar", "man-made"],
            },
            {
                id: 51,
                name: "Poblacion Bilar",
                description: "Bilar - Town center",
                lat: 9.765,
                lng: 124.08,
                keywords: ["poblacion", "bilar", "center"],
            },
            {
                id: 52,
                name: "Campagao",
                description: "Bilar - Barangay Campagao",
                lat: 9.77,
                lng: 124.085,
                keywords: ["campagao", "barangay", "bilar"],
            },
            {
                id: 53,
                name: "Yanaya",
                description: "Bilar - Barangay Yanaya",
                lat: 9.763,
                lng: 124.078,
                keywords: ["yanaya", "barangay", "bilar"],
            },

            // ANTEQUERA - Waterfall Area
            {
                id: 54,
                name: "Antequera",
                description: "Municipality in central Bohol",
                lat: 9.8167,
                lng: 124.15,
                keywords: ["antequera", "municipality"],
            },
            {
                id: 55,
                name: "Mag-Aso Falls",
                description: "Antequera - Natural waterfall",
                lat: 9.82,
                lng: 124.15,
                keywords: ["mag-aso", "falls", "antequera", "waterfall"],
            },
            {
                id: 56,
                name: "Poblacion Antequera",
                description: "Antequera - Town center",
                lat: 9.8167,
                lng: 124.15,
                keywords: ["poblacion", "antequera", "center"],
            },
            {
                id: 57,
                name: "Cawayan",
                description: "Antequera - Barangay Cawayan",
                lat: 9.815,
                lng: 124.145,
                keywords: ["cawayan", "barangay", "antequera"],
            },
            {
                id: 58,
                name: "Villa Aurora",
                description: "Antequera - Barangay Villa Aurora",
                lat: 9.82,
                lng: 124.155,
                keywords: ["villa aurora", "barangay", "antequera"],
            },

            // ANDA - Eastern Beach Municipality
            {
                id: 59,
                name: "Anda",
                description: "Municipality in eastern Bohol - beaches",
                lat: 9.7333,
                lng: 124.6333,
                keywords: ["anda", "municipality", "beach", "east"],
            },
            {
                id: 60,
                name: "Poblacion Anda",
                description: "Anda - Town center",
                lat: 9.7333,
                lng: 124.6333,
                keywords: ["poblacion", "anda", "center"],
            },
            {
                id: 61,
                name: "Virgen",
                description: "Anda - Barangay Virgen, beach area",
                lat: 9.73,
                lng: 124.63,
                keywords: ["virgen", "barangay", "anda", "beach"],
            },
            {
                id: 62,
                name: "Badiang",
                description: "Anda - Barangay Badiang",
                lat: 9.735,
                lng: 124.635,
                keywords: ["badiang", "barangay", "anda"],
            },
            {
                id: 63,
                name: "Tanongon",
                description: "Anda - Barangay Tanongon",
                lat: 9.738,
                lng: 124.638,
                keywords: ["tanongon", "barangay", "anda"],
            },

            // JAGNA - Southeastern Municipality
            {
                id: 64,
                name: "Jagna",
                description: "Municipality in southeastern Bohol",
                lat: 9.65,
                lng: 124.3667,
                keywords: ["jagna", "municipality"],
            },
            {
                id: 65,
                name: "Poblacion Jagna",
                description: "Jagna - Town center",
                lat: 9.65,
                lng: 124.3667,
                keywords: ["poblacion", "jagna", "center"],
            },
            {
                id: 66,
                name: "Bunga",
                description: "Jagna - Barangay Bunga",
                lat: 9.645,
                lng: 124.37,
                keywords: ["bunga", "barangay", "jagna"],
            },
            {
                id: 67,
                name: "Cantagay",
                description: "Jagna - Barangay Cantagay",
                lat: 9.655,
                lng: 124.365,
                keywords: ["cantagay", "barangay", "jagna"],
            },

            // CANDIJAY - Eastern Municipality with Falls
            {
                id: 68,
                name: "Candijay",
                description: "Municipality in eastern Bohol",
                lat: 9.8167,
                lng: 124.5167,
                keywords: ["candijay", "municipality"],
            },
            {
                id: 69,
                name: "Can-umantad Falls",
                description: "Candijay - Waterfall attraction",
                lat: 9.82,
                lng: 124.52,
                keywords: ["can-umantad", "falls", "candijay", "waterfall"],
            },
            {
                id: 70,
                name: "Poblacion Candijay",
                description: "Candijay - Town center",
                lat: 9.8167,
                lng: 124.5167,
                keywords: ["poblacion", "candijay", "center"],
            },

            // TUBIGON - Port Town and Barangays
            {
                id: 71,
                name: "Tubigon",
                description: "Municipality in western Bohol - Port town",
                lat: 9.95,
                lng: 123.8167,
                keywords: ["tubigon", "municipality", "port"],
            },
            {
                id: 72,
                name: "Poblacion Tubigon",
                description: "Tubigon - Town center and port",
                lat: 9.95,
                lng: 123.8167,
                keywords: ["poblacion", "tubigon", "center", "port"],
            },
            {
                id: 73,
                name: "Cahayag",
                description: "Tubigon - Barangay Cahayag",
                lat: 9.945,
                lng: 123.82,
                keywords: ["cahayag", "barangay", "tubigon"],
            },
            {
                id: 74,
                name: "Gemar",
                description: "Tubigon - Barangay Gemar",
                lat: 9.955,
                lng: 123.815,
                keywords: ["gemar", "barangay", "tubigon"],
            },

            // TALIBON - Northern Municipality
            {
                id: 75,
                name: "Talibon",
                description: "Municipality in northern Bohol",
                lat: 10.05,
                lng: 124.2667,
                keywords: ["talibon", "municipality"],
            },
            {
                id: 76,
                name: "Poblacion Talibon",
                description: "Talibon - Town center",
                lat: 10.05,
                lng: 124.2667,
                keywords: ["poblacion", "talibon", "center"],
            },
            {
                id: 77,
                name: "San Carlos",
                description: "Talibon - Barangay San Carlos",
                lat: 10.045,
                lng: 124.27,
                keywords: ["san carlos", "barangay", "talibon"],
            },
            {
                id: 78,
                name: "Kinan",
                description: "Talibon - Barangay Kinan",
                lat: 10.055,
                lng: 124.265,
                keywords: ["kinan", "barangay", "talibon"],
            },

            // UBAY - Northeastern Municipality
            {
                id: 79,
                name: "Ubay",
                description: "Municipality in northeastern Bohol",
                lat: 10.05,
                lng: 124.4833,
                keywords: ["ubay", "municipality"],
            },
            {
                id: 80,
                name: "Poblacion Ubay",
                description: "Ubay - Town center",
                lat: 10.05,
                lng: 124.4833,
                keywords: ["poblacion", "ubay", "center"],
            },
            {
                id: 81,
                name: "Fatima",
                description: "Ubay - Barangay Fatima",
                lat: 10.045,
                lng: 124.48,
                keywords: ["fatima", "barangay", "ubay"],
            },
            {
                id: 82,
                name: "Carlos P. Garcia",
                description: "Ubay - Barangay Carlos P. Garcia",
                lat: 10.055,
                lng: 124.485,
                keywords: ["carlos garcia", "barangay", "ubay"],
            },

            // LOON - Western Municipality
            {
                id: 83,
                name: "Loon",
                description: "Municipality in western Bohol",
                lat: 9.7833,
                lng: 123.8,
                keywords: ["loon", "municipality"],
            },
            {
                id: 84,
                name: "Poblacion Loon",
                description: "Loon - Town center",
                lat: 9.7833,
                lng: 123.8,
                keywords: ["poblacion", "loon", "center"],
            },
            {
                id: 85,
                name: "Napo Loon",
                description: "Loon - Barangay Napo",
                lat: 9.78,
                lng: 123.795,
                keywords: ["napo", "barangay", "loon"],
            },
            {
                id: 86,
                name: "Tangnan",
                description: "Loon - Barangay Tangnan",
                lat: 9.785,
                lng: 123.805,
                keywords: ["tangnan", "barangay", "loon"],
            },

            // CALAPE - Western Municipality
            {
                id: 87,
                name: "Calape",
                description: "Municipality in western Bohol",
                lat: 9.85,
                lng: 123.8833,
                keywords: ["calape", "municipality"],
            },
            {
                id: 88,
                name: "Poblacion Calape",
                description: "Calape - Town center",
                lat: 9.85,
                lng: 123.8833,
                keywords: ["poblacion", "calape", "center"],
            },
            {
                id: 89,
                name: "Cabacnitan",
                description: "Calape - Barangay Cabacnitan",
                lat: 9.845,
                lng: 123.88,
                keywords: ["cabacnitan", "barangay", "calape"],
            },
            {
                id: 90,
                name: "Sohoton",
                description: "Calape - Barangay Sohoton",
                lat: 9.855,
                lng: 123.885,
                keywords: ["sohoton", "barangay", "calape"],
            },

            // MARIBOJOC - Western Municipality
            {
                id: 91,
                name: "Maribojoc",
                description: "Municipality in western Bohol",
                lat: 9.7333,
                lng: 123.8167,
                keywords: ["maribojoc", "municipality"],
            },
            {
                id: 92,
                name: "Poblacion Maribojoc",
                description: "Maribojoc - Town center",
                lat: 9.7333,
                lng: 123.8167,
                keywords: ["poblacion", "maribojoc", "center"],
            },
            {
                id: 93,
                name: "Busao",
                description: "Maribojoc - Barangay Busao",
                lat: 9.73,
                lng: 123.82,
                keywords: ["busao", "barangay", "maribojoc"],
            },
            {
                id: 94,
                name: "Oy",
                description: "Maribojoc - Barangay Oy",
                lat: 9.735,
                lng: 123.815,
                keywords: ["oy", "barangay", "maribojoc"],
            },

            // ALBURQUERQUE - Western Municipality
            {
                id: 95,
                name: "Alburquerque",
                description: "Municipality in western Bohol",
                lat: 9.6167,
                lng: 123.9167,
                keywords: ["alburquerque", "municipality"],
            },
            {
                id: 96,
                name: "Poblacion Alburquerque",
                description: "Alburquerque - Town center",
                lat: 9.6167,
                lng: 123.9167,
                keywords: ["poblacion", "alburquerque", "center"],
            },
            {
                id: 97,
                name: "Cabog",
                description: "Alburquerque - Barangay Cabog",
                lat: 9.615,
                lng: 123.92,
                keywords: ["cabog", "barangay", "alburquerque"],
            },
            {
                id: 98,
                name: "Katipunan Alburquerque",
                description: "Alburquerque - Barangay Katipunan",
                lat: 9.62,
                lng: 123.915,
                keywords: ["katipunan", "barangay", "alburquerque"],
            },

            // LOAY - Western Municipality
            {
                id: 99,
                name: "Loay",
                description: "Municipality in western Bohol",
                lat: 9.5833,
                lng: 123.95,
                keywords: ["loay", "municipality"],
            },
            {
                id: 100,
                name: "Poblacion Loay",
                description: "Loay - Town center",
                lat: 9.5833,
                lng: 123.95,
                keywords: ["poblacion", "loay", "center"],
            },
            {
                id: 101,
                name: "Canangca-an",
                description: "Loay - Barangay Canangca-an",
                lat: 9.58,
                lng: 123.945,
                keywords: ["canangca-an", "barangay", "loay"],
            },
            {
                id: 102,
                name: "Guinacot",
                description: "Loay - Barangay Guinacot",
                lat: 9.585,
                lng: 123.955,
                keywords: ["guinacot", "barangay", "loay"],
            },

            // Additional Major Areas and Sitios
            {
                id: 103,
                name: "Virgin Island",
                description: "Panglao - Small island destination",
                lat: 9.53,
                lng: 123.74,
                keywords: ["virgin island", "panglao", "island"],
            },
            {
                id: 104,
                name: "Balicasag Island",
                description: "Panglao - Diving and snorkeling spot",
                lat: 9.5167,
                lng: 123.6833,
                keywords: ["balicasag", "island", "diving", "snorkeling"],
            },
            {
                id: 105,
                name: "Blood Compact Monument",
                description: "Tagbilaran - Historical site",
                lat: 9.6298,
                lng: 123.8602,
                keywords: [
                    "blood compact",
                    "monument",
                    "historical",
                    "tagbilaran",
                ],
            },
            {
                id: 106,
                name: "Bohol Beach Club",
                description: "Panglao - Resort area",
                lat: 9.5565,
                lng: 123.7625,
                keywords: ["bohol beach club", "resort", "panglao"],
            },
            {
                id: 107,
                name: "Dao Terminal",
                description: "Tagbilaran - Main bus terminal",
                lat: 9.645,
                lng: 123.852,
                keywords: ["dao", "terminal", "bus", "tagbilaran"],
            },
            {
                id: 108,
                name: "ICM Mall",
                description: "Tagbilaran - Shopping center",
                lat: 9.638,
                lng: 123.855,
                keywords: ["icm", "mall", "shopping", "tagbilaran"],
            },
            {
                id: 109,
                name: "Bohol Quality Mall",
                description: "Tagbilaran - Shopping mall",
                lat: 9.635,
                lng: 123.848,
                keywords: ["bohol quality", "mall", "shopping", "tagbilaran"],
            },

            // Additional municipalities for comprehensive coverage
            {
                id: 110,
                name: "Alicia",
                description: "Municipality in central Bohol",
                lat: 9.9167,
                lng: 124.4167,
                keywords: ["alicia", "municipality"],
            },
            {
                id: 111,
                name: "Balilihan",
                description: "Municipality in central Bohol",
                lat: 9.75,
                lng: 124.1167,
                keywords: ["balilihan", "municipality"],
            },
            {
                id: 112,
                name: "Batuan",
                description: "Municipality in central Bohol",
                lat: 9.7833,
                lng: 124.1667,
                keywords: ["batuan", "municipality"],
            },
            {
                id: 113,
                name: "Bien Unido",
                description: "Municipality in northern Bohol",
                lat: 10.1167,
                lng: 124.3667,
                keywords: ["bien unido", "municipality", "north"],
            },
            {
                id: 114,
                name: "Buenavista",
                description: "Municipality in northern Bohol",
                lat: 10.0833,
                lng: 124.3,
                keywords: ["buenavista", "municipality", "north"],
            },
            {
                id: 115,
                name: "Catigbian",
                description: "Municipality in central Bohol",
                lat: 9.8333,
                lng: 124.2167,
                keywords: ["catigbian", "municipality"],
            },
            {
                id: 116,
                name: "Clarin",
                description: "Municipality in western Bohol",
                lat: 9.9667,
                lng: 123.95,
                keywords: ["clarin", "municipality"],
            },
            {
                id: 117,
                name: "Cortes",
                description: "Municipality in western Bohol",
                lat: 9.6167,
                lng: 123.8833,
                keywords: ["cortes", "municipality"],
            },
            {
                id: 118,
                name: "Dagohoy",
                description: "Municipality in central Bohol",
                lat: 9.8833,
                lng: 124.3167,
                keywords: ["dagohoy", "municipality"],
            },
            {
                id: 119,
                name: "Danao",
                description: "Municipality in northeastern Bohol",
                lat: 9.9833,
                lng: 124.3833,
                keywords: ["danao", "municipality"],
            },
            {
                id: 120,
                name: "Dimiao",
                description: "Municipality in central Bohol",
                lat: 9.65,
                lng: 124.0167,
                keywords: ["dimiao", "municipality"],
            },
            {
                id: 121,
                name: "Duero",
                description: "Municipality in southeastern Bohol",
                lat: 9.6167,
                lng: 124.4167,
                keywords: ["duero", "municipality"],
            },
            {
                id: 122,
                name: "Garcia Hernandez",
                description: "Municipality in central Bohol",
                lat: 9.6667,
                lng: 124.1833,
                keywords: ["garcia hernandez", "municipality"],
            },
            {
                id: 123,
                name: "Getafe",
                description: "Municipality in northeastern Bohol",
                lat: 10.15,
                lng: 124.15,
                keywords: ["getafe", "municipality"],
            },
            {
                id: 124,
                name: "Guindulman",
                description: "Municipality in southeastern Bohol",
                lat: 9.7667,
                lng: 124.4833,
                keywords: ["guindulman", "municipality"],
            },
            {
                id: 125,
                name: "Inabanga",
                description: "Municipality in northern Bohol",
                lat: 9.9833,
                lng: 124.0833,
                keywords: ["inabanga", "municipality"],
            },
            {
                id: 126,
                name: "Jetafe",
                description: "Municipality in northern Bohol",
                lat: 10.1667,
                lng: 124.1833,
                keywords: ["jetafe", "municipality"],
            },
            {
                id: 127,
                name: "Lila",
                description: "Municipality in central Bohol",
                lat: 9.6333,
                lng: 124.0833,
                keywords: ["lila", "municipality"],
            },
            {
                id: 128,
                name: "Mabini",
                description: "Municipality in southeastern Bohol",
                lat: 9.85,
                lng: 124.5833,
                keywords: ["mabini", "municipality"],
            },
            {
                id: 129,
                name: "Pilar",
                description: "Municipality in southeastern Bohol",
                lat: 9.8333,
                lng: 124.3333,
                keywords: ["pilar", "municipality"],
            },
            {
                id: 130,
                name: "President Carlos P. Garcia",
                description: "Island municipality",
                lat: 9.85,
                lng: 124.7833,
                keywords: [
                    "president garcia",
                    "pcpg",
                    "municipality",
                    "island",
                ],
            },
            {
                id: 131,
                name: "San Isidro",
                description: "Municipality in northern Bohol",
                lat: 10.0167,
                lng: 124.2167,
                keywords: ["san isidro", "municipality"],
            },
            {
                id: 132,
                name: "San Miguel",
                description: "Municipality in central Bohol",
                lat: 9.8167,
                lng: 124.25,
                keywords: ["san miguel", "municipality"],
            },
            {
                id: 133,
                name: "Sevilla",
                description: "Municipality in central Bohol",
                lat: 9.65,
                lng: 124.15,
                keywords: ["sevilla", "municipality"],
            },
            {
                id: 134,
                name: "Sierra Bullones",
                description: "Municipality in central Bohol",
                lat: 9.7833,
                lng: 124.2333,
                keywords: ["sierra bullones", "municipality"],
            },
            {
                id: 135,
                name: "Sikatuna",
                description: "Municipality in central Bohol",
                lat: 9.7167,
                lng: 124.05,
                keywords: ["sikatuna", "municipality"],
            },
            {
                id: 136,
                name: "Trinidad",
                description: "Municipality in northern Bohol",
                lat: 10.0167,
                lng: 124.0333,
                keywords: ["trinidad", "municipality"],
            },
            {
                id: 137,
                name: "Valencia",
                description: "Municipality in southeastern Bohol",
                lat: 9.6167,
                lng: 124.2167,
                keywords: ["valencia", "municipality"],
            },
        ];

        const searchTerm = addressSearch.value.toLowerCase();

        // Enhanced search algorithm - more flexible matching
        const matches = bohoLocations.filter((location) => {
            const nameMatch =
                location.name.toLowerCase().includes(searchTerm) ||
                searchTerm.includes(location.name.toLowerCase());
            const keywordMatch = location.keywords.some(
                (keyword) =>
                    keyword.includes(searchTerm) || searchTerm.includes(keyword)
            );
            const descriptionMatch = location.description
                .toLowerCase()
                .includes(searchTerm);

            return nameMatch || keywordMatch || descriptionMatch;
        });

        if (matches.length > 0) {
            // Sort by relevance (exact name matches first, then partial matches)
            const sortedMatches = matches.sort((a, b) => {
                const aExactMatch = a.name.toLowerCase() === searchTerm;
                const bExactMatch = b.name.toLowerCase() === searchTerm;
                const aStartsMatch = a.name
                    .toLowerCase()
                    .startsWith(searchTerm);
                const bStartsMatch = b.name
                    .toLowerCase()
                    .startsWith(searchTerm);

                if (aExactMatch && !bExactMatch) return -1;
                if (!aExactMatch && bExactMatch) return 1;
                if (aStartsMatch && !bStartsMatch) return -1;
                if (!aStartsMatch && bStartsMatch) return 1;
                return 0;
            });

            searchResults.value = sortedMatches.slice(0, 8); // Show max 8 results

            // If only one result, auto-select it after a short delay
            if (sortedMatches.length === 1) {
                setTimeout(() => selectSearchResult(sortedMatches[0]), 800);
            }
        } else {
            alert(
                `No locations found for "${addressSearch.value}".\n\nSearchable locations include:\n• All 47 Bohol municipalities (e.g., Carmen, Panglao, Sagbayan)\n• Barangays and areas (e.g., Poblacion, Cogon, Dao, Tawala)\n• Tourist attractions (e.g., Chocolate Hills, Alona Beach)\n• Local landmarks (e.g., Tarsier Sanctuary, Hinagdanan Cave)\n• Infrastructure (e.g., airports, malls, terminals)\n\nTry a more specific location name or partial match.`
            );
        }
    } catch (error) {
        alert("Error searching for location. Please try again.");
    } finally {
        searchingAddress.value = false;
    }
};

const selectSearchResult = (result) => {
    // Set coordinates
    form.coordinates_lat = result.lat;
    form.coordinates_lng = result.lng;
    selectedLocationName.value = result.name;

    // Update address search field
    addressSearch.value = result.name;

    // Clear search results
    searchResults.value = [];

    // Auto-open map if not already open and add marker
    if (!showInteractiveMap.value) {
        showInteractiveMap.value = true;
        // Wait for map to initialize, then add marker
        setTimeout(() => {
            if (leafletMap.value) {
                centerMapOnLocation(result.lat, result.lng);
            } else {
                // Map not ready yet, wait a bit more
                setTimeout(() => {
                    centerMapOnLocation(result.lat, result.lng);
                }, 500);
            }
        }, 200);
    } else {
        // Map already open, immediately center and add marker
        centerMapOnLocation(result.lat, result.lng);
    }

    // Show success feedback
    console.log(
        `📍 Location found: ${result.name} (${result.lat}, ${result.lng})`
    );

    // Optional: Show a temporary success message
    const successMessage = document.createElement("div");
    successMessage.className =
        "fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-pulse";
    successMessage.textContent = `📍 Location set: ${result.name}`;
    document.body.appendChild(successMessage);

    setTimeout(() => {
        if (successMessage.parentNode) {
            successMessage.parentNode.removeChild(successMessage);
        }
    }, 3000);
};

const reverseGeocode = async (lat, lng) => {
    try {
        // Use OpenStreetMap's Nominatim service (free)
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
            {
                headers: {
                    "User-Agent": "GeoCasa Bohol Property Listing System",
                },
            }
        );

        if (response.ok) {
            const data = await response.json();
            let locationName = "Custom Location";

            if (data.display_name) {
                // Extract meaningful parts of the address
                const address = data.address || {};

                if (address.village || address.town || address.city) {
                    locationName = `Near ${
                        address.village || address.town || address.city
                    }`;
                } else if (address.suburb || address.neighbourhood) {
                    locationName = `Near ${
                        address.suburb || address.neighbourhood
                    }`;
                } else if (data.display_name.includes("Bohol")) {
                    // Extract first meaningful part of display name
                    const parts = data.display_name.split(",");
                    locationName = `Near ${parts[0].trim()}`;
                } else {
                    locationName = "Bohol Location";
                }
            }

            selectedLocationName.value = locationName;
            addressSearch.value = locationName;

            // Update marker popup if it exists
            if (currentMarker.value) {
                const popupContent = `
                    <div class="text-center">
                        <strong>${locationName}</strong><br>
                        <small>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(
                    6
                )}</small><br>
                        <small class="text-gray-500">Drag marker to adjust</small>
                    </div>
                `;
                currentMarker.value.setPopupContent(popupContent);
            }
        } else {
            throw new Error("Geocoding request failed");
        }
    } catch (error) {
        console.log("Online geocoding failed, using fallback method");
        fallbackReverseGeocode(lat, lng);
    }
};

const fallbackReverseGeocode = (lat, lng) => {
    // Enhanced reverse geocoding based on proximity to major Bohol locations
    const majorBohoLocations = [
        { name: "Near Alona Beach", lat: 9.5547, lng: 123.7598 },
        { name: "Near Tagbilaran City", lat: 9.634, lng: 123.853 },
        { name: "Near Panglao Airport", lat: 9.5651, lng: 123.8534 },
        { name: "Near Baclayon", lat: 9.6157, lng: 123.9054 },
        { name: "Near Loboc", lat: 9.6389, lng: 124.0301 },
        { name: "Near Chocolate Hills", lat: 9.9169, lng: 124.1695 },
        { name: "Near Carmen", lat: 9.9169, lng: 124.1695 },
        { name: "Near Dauis", lat: 9.6241, lng: 123.8449 },
        { name: "Near Corella", lat: 9.6833, lng: 123.9167 },
        { name: "Near Antequera", lat: 9.8167, lng: 124.15 },
        { name: "Near Balilihan", lat: 9.75, lng: 124.1167 },
        { name: "Near Jagna", lat: 9.65, lng: 124.3667 },
        { name: "Near Ubay", lat: 10.05, lng: 124.4833 },
        { name: "Near Talibon", lat: 10.05, lng: 124.2667 },
        { name: "Near Tubigon", lat: 9.95, lng: 123.8167 },
        { name: "Near Loon", lat: 9.7833, lng: 123.8 },
        { name: "Near Calape", lat: 9.85, lng: 123.8833 },
        { name: "Near Clarin", lat: 9.9667, lng: 123.95 },
        { name: "Near Inabanga", lat: 9.9833, lng: 124.0833 },
        { name: "Near Sagbayan", lat: 9.8833, lng: 124.1 },
        { name: "Near Anda", lat: 9.7333, lng: 124.6333 },
        { name: "Near Guindulman", lat: 9.7667, lng: 124.4833 },
        { name: "Near Candijay", lat: 9.8167, lng: 124.5167 },
        { name: "Near Mabini", lat: 9.85, lng: 124.5833 },
    ];

    let closestLocation = null;
    let minDistance = Infinity;

    majorBohoLocations.forEach((location) => {
        const distance = Math.sqrt(
            Math.pow(lat - location.lat, 2) + Math.pow(lng - location.lng, 2)
        );
        if (distance < minDistance) {
            minDistance = distance;
            closestLocation = location;
        }
    });

    if (closestLocation && minDistance < 0.15) {
        // Within ~15km
        selectedLocationName.value = closestLocation.name;
        addressSearch.value = closestLocation.name;
    } else {
        selectedLocationName.value = "Bohol Location";
        addressSearch.value = "Custom Location in Bohol";
    }
};

const centerMapOnLocation = (lat, lng) => {
    if (leafletMap.value) {
        leafletMap.value.setView([lat, lng], 15); // Zoom in for better detail
        addLeafletMarker(lat, lng);
    }
    mapInitialized.value = true;
};

const zoomToCurrentLocation = () => {
    getCurrentLocation();
    if (form.coordinates_lat && form.coordinates_lng) {
        centerMapOnLocation(form.coordinates_lat, form.coordinates_lng);
    }
};

const resetMapView = () => {
    // Reset to Bohol overview
    if (leafletMap.value) {
        leafletMap.value.setView([9.634, 123.853], 10); // Tagbilaran center
        mapZoomLevel.value = 10;
    }
};

const zoomIn = () => {
    if (leafletMap.value) {
        const currentZoom = leafletMap.value.getZoom();
        const newZoom = Math.min(currentZoom + 1, 20);
        leafletMap.value.setZoom(newZoom);
        mapZoomLevel.value = newZoom;
    }
};

const zoomOut = () => {
    if (leafletMap.value) {
        const currentZoom = leafletMap.value.getZoom();
        const newZoom = Math.max(currentZoom - 1, 8);
        leafletMap.value.setZoom(newZoom);
        mapZoomLevel.value = newZoom;
    }
};

const clearLocation = () => {
    form.coordinates_lat = null;
    form.coordinates_lng = null;
    selectedLocationName.value = "";
    addressSearch.value = "";
    searchResults.value = [];

    // Reset map view if open
    if (showInteractiveMap.value) {
        resetMapView();
    }

    console.log("Location cleared");
};

const clearCoordinates = () => {
    clearLocation(); // Use the same functionality
};

const formatCoordinate = (coord) => {
    return coord ? parseFloat(coord).toFixed(6) : "Not set";
};

// Price calculation - simple auto-calculation when area or price per sqm changes
const calculateTotalPrice = () => {
    if (form.lot_area_sqm && form.price_per_sqm) {
        form.total_price = form.lot_area_sqm * form.price_per_sqm;
    }
};

// Form submission
const submit = () => {
    console.log("Submit function called");

    // Clean up type_other - only send if type is 'other'
    if (form.type !== "other") {
        form.type_other = null;
    }

    console.log("Form data:", form.data());

    processing.value = true;

    form.post(route("broker.properties.store"), {
        onSuccess: () => {
            console.log("Success!");
            processing.value = false;
        },
        onError: (errors) => {
            console.log("Errors:", errors);
            processing.value = false;
        },
    });
};
</script>
