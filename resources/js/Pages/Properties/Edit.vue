<template>
    <ModernDashboardLayout>
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Edit Property
                    </h1>
                    <p class="text-gray-600 mt-1">
                        {{ property.title }}
                    </p>
                </div>
                <div class="flex space-x-3">
                    <Link
                        :href="route('broker.properties.show', property.slug)"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition-all duration-200 flex items-center space-x-2"
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
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            ></path>
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            ></path>
                        </svg>
                        <span>View Property</span>
                    </Link>
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
                        class="w-6 h-6 mr-2 text-indigo-600"
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
                    <div class="md:col-span-2">
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
                            placeholder="e.g., Beach Front Lot in Panglao"
                            required
                            autofocus
                        />
                        <div
                            v-if="errors.title"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ errors.title }}
                        </div>
                    </div>

                    <!-- Property Types (Multi-Select) -->
                    <div class="md:col-span-2">
                        <PropertyTypeMultiSelect
                            v-model="form.types"
                            :available-types="types"
                            label="Property Type(s)"
                            placeholder="Select one or more property types"
                            :error="errors.types || errors.type"
                            help-text="Select all applicable types. Mixed-use properties (e.g., commercial + residential) can have multiple types."
                            required
                        />

                        <!-- Custom Type Input (shown if "other" is selected) -->
                        <transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 transform -translate-y-2"
                            enter-to-class="opacity-100 transform translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 transform translate-y-0"
                            leave-to-class="opacity-0 transform -translate-y-2"
                        >
                            <div
                                v-show="
                                    Array.isArray(form.types) &&
                                    form.types.includes('other')
                                "
                                class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg"
                            >
                                <label
                                    for="custom_type_text"
                                    class="block text-sm font-semibold text-gray-900 mb-2"
                                >
                                    ✏️ Specify Property Type *
                                </label>
                                <input
                                    id="custom_type_text"
                                    v-model="form.custom_type_text"
                                    type="text"
                                    class="w-full border-2 border-blue-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                    :class="{
                                        'border-red-500 ring-red-500':
                                            errors.custom_type_text,
                                    }"
                                    placeholder="e.g., Resort Land, Heritage Site, Eco Farm, Memorial Lot"
                                    :required="form.types.includes('other')"
                                />
                                <p
                                    class="text-xs text-blue-700 mt-2 font-medium"
                                >
                                    💡 This type will be automatically available
                                    for all users to filter
                                </p>
                                <div
                                    v-if="errors.custom_type_text"
                                    class="text-red-600 text-sm mt-2 font-medium"
                                >
                                    {{ errors.custom_type_text }}
                                </div>
                            </div>
                        </transition>
                    </div>

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
                            placeholder="Describe the property features, location advantages, and unique selling points..."
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
                    </svg>
                    Location
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
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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

            <!-- GPS Coordinates / GIS Mapping -->
            <div
                class="bg-white rounded-lg shadow-sm p-6 mb-6 border border-gray-200"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3
                        class="text-xl font-semibold text-gray-900 flex items-center"
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
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            />
                        </svg>
                        GIS Mapping (Optional)
                    </h3>
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input
                            v-model="enableGISMapping"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-5 w-5"
                        />
                        <span class="text-sm font-semibold text-gray-700">
                            🗺️ Enable Location Mapping
                        </span>
                    </label>
                </div>

                <div v-if="enableGISMapping" class="space-y-6">
                    <!-- Method Selection Buttons -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button
                            type="button"
                            @click="
                                showAddressSearch = !showAddressSearch;
                                showInteractiveMap = false;
                                showManualCoords = false;
                            "
                            class="flex items-center justify-center space-x-2 px-4 py-3 rounded-lg border-2 transition-all"
                            :class="
                                showAddressSearch
                                    ? 'border-blue-600 bg-blue-50 text-blue-700 font-semibold'
                                    : 'border-gray-300 hover:border-blue-400 bg-white text-gray-700'
                            "
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
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                            <span>🔍 Search Address</span>
                        </button>

                        <button
                            type="button"
                            @click="
                                showInteractiveMap = !showInteractiveMap;
                                showAddressSearch = false;
                                showManualCoords = false;
                            "
                            class="flex items-center justify-center space-x-2 px-4 py-3 rounded-lg border-2 transition-all"
                            :class="
                                showInteractiveMap
                                    ? 'border-green-600 bg-green-50 text-green-700 font-semibold'
                                    : 'border-gray-300 hover:border-green-400 bg-white text-gray-700'
                            "
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
                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
                                />
                            </svg>
                            <span>🗺️ Interactive Map</span>
                        </button>

                        <button
                            type="button"
                            @click="
                                showManualCoords = !showManualCoords;
                                showAddressSearch = false;
                                showInteractiveMap = false;
                            "
                            class="flex items-center justify-center space-x-2 px-4 py-3 rounded-lg border-2 transition-all"
                            :class="
                                showManualCoords
                                    ? 'border-purple-600 bg-purple-50 text-purple-700 font-semibold'
                                    : 'border-gray-300 hover:border-purple-400 bg-white text-gray-700'
                            "
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
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                            <span>✏️ Manual Entry</span>
                        </button>
                    </div>

                    <!-- Address Search Section -->
                    <div
                        v-if="showAddressSearch"
                        class="bg-blue-50 border-2 border-blue-200 rounded-lg p-6"
                    >
                        <h4
                            class="font-semibold text-gray-900 mb-4 flex items-center"
                        >
                            <svg
                                class="w-5 h-5 mr-2 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                            Search for Address or Location
                        </h4>

                        <div class="flex space-x-2 mb-4">
                            <input
                                v-model="addressSearch"
                                type="text"
                                placeholder="e.g., Alona Beach, Tagbilaran City, Chocolate Hills..."
                                class="flex-1 border-2 border-blue-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @keyup.enter="searchAddress"
                            />
                            <button
                                type="button"
                                @click="searchAddress"
                                :disabled="searchingAddress"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors disabled:opacity-50"
                            >
                                <span v-if="!searchingAddress">Search</span>
                                <span v-else>Searching...</span>
                            </button>
                        </div>

                        <!-- Search Results -->
                        <div
                            v-if="searchResults.length > 0"
                            class="bg-white rounded-lg border-2 border-blue-200 divide-y"
                        >
                            <div
                                v-for="result in searchResults"
                                :key="result.id"
                                @click="selectSearchResult(result)"
                                class="p-4 hover:bg-blue-50 cursor-pointer transition-colors"
                            >
                                <div class="font-semibold text-gray-900">
                                    {{ result.name }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    {{ result.description }}
                                </div>
                            </div>
                        </div>

                        <p class="text-sm text-blue-700 mt-3">
                            💡 Search for municipalities, barangays, or
                            landmarks in Bohol
                        </p>
                    </div>

                    <!-- Interactive Map Section -->
                    <div
                        v-if="showInteractiveMap"
                        class="bg-green-50 border-2 border-green-200 rounded-lg p-6"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <h4
                                class="font-semibold text-gray-900 flex items-center"
                            >
                                <svg
                                    class="w-5 h-5 mr-2 text-green-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
                                    />
                                </svg>
                                Click on the Map to Set Location
                            </h4>

                            <!-- Map Controls -->
                            <div class="flex space-x-2">
                                <button
                                    type="button"
                                    @click="zoomIn"
                                    class="bg-white border border-gray-300 p-2 rounded hover:bg-gray-100"
                                    title="Zoom In"
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
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                        />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    @click="zoomOut"
                                    class="bg-white border border-gray-300 p-2 rounded hover:bg-gray-100"
                                    title="Zoom Out"
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
                                            d="M20 12H4"
                                        />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    @click="resetMapView"
                                    class="bg-white border border-gray-300 px-3 py-2 rounded hover:bg-gray-100 text-sm"
                                    title="Reset View"
                                >
                                    Reset
                                </button>
                            </div>
                        </div>

                        <!-- Leaflet Map Container -->
                        <div
                            ref="mapContainer"
                            class="w-full h-96 rounded-lg border-2 border-green-300 bg-gray-100"
                        ></div>

                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                <span
                                    v-if="
                                        form.coordinates_lat &&
                                        form.coordinates_lng
                                    "
                                    class="font-semibold text-green-700"
                                >
                                    ✓ Location Set:
                                    {{
                                        formatCoordinate(form.coordinates_lat)
                                    }},
                                    {{ formatCoordinate(form.coordinates_lng) }}
                                </span>
                                <span v-else class="text-gray-500">
                                    Click on the map to set coordinates
                                </span>
                            </div>

                            <button
                                v-if="
                                    form.coordinates_lat && form.coordinates_lng
                                "
                                type="button"
                                @click="clearLocation"
                                class="text-red-600 hover:text-red-800 font-semibold text-sm"
                            >
                                Clear Location
                            </button>
                        </div>
                    </div>

                    <!-- Manual Coordinates Entry -->
                    <div
                        v-if="showManualCoords"
                        class="bg-purple-50 border-2 border-purple-200 rounded-lg p-6"
                    >
                        <h4
                            class="font-semibold text-gray-900 mb-4 flex items-center"
                        >
                            <svg
                                class="w-5 h-5 mr-2 text-purple-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                            Enter GPS Coordinates Manually
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                >
                                    Latitude
                                </label>
                                <input
                                    v-model.number="form.coordinates_lat"
                                    type="number"
                                    step="0.000001"
                                    placeholder="e.g., 9.634"
                                    class="w-full border-2 border-purple-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                >
                                    Longitude
                                </label>
                                <input
                                    v-model.number="form.coordinates_lng"
                                    type="number"
                                    step="0.000001"
                                    placeholder="e.g., 123.853"
                                    class="w-full border-2 border-purple-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                />
                            </div>
                        </div>

                        <div class="flex space-x-3">
                            <button
                                type="button"
                                @click="getCurrentLocation"
                                :disabled="gettingLocation"
                                class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-4 py-3 rounded-lg transition-colors disabled:opacity-50 flex items-center justify-center space-x-2"
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
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                    />
                                </svg>
                                <span v-if="!gettingLocation"
                                    >📍 Use My Current Location</span
                                >
                                <span v-else>Getting location...</span>
                            </button>

                            <button
                                v-if="
                                    form.coordinates_lat && form.coordinates_lng
                                "
                                type="button"
                                @click="clearCoordinates"
                                class="bg-red-100 hover:bg-red-200 text-red-700 font-semibold px-4 py-3 rounded-lg transition-colors"
                            >
                                Clear
                            </button>
                        </div>

                        <p class="text-sm text-purple-700 mt-3">
                            💡 Enter exact GPS coordinates if you have them, or
                            use your device's location
                        </p>
                    </div>

                    <!-- Current Coordinates Display -->
                    <div
                        v-if="form.coordinates_lat && form.coordinates_lng"
                        class="bg-gradient-to-r from-green-50 to-blue-50 border-2 border-green-300 rounded-lg p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-gray-900 mb-1">
                                    ✓ Location Set Successfully
                                </div>
                                <div class="text-sm text-gray-700">
                                    <span class="font-medium"
                                        >Coordinates:</span
                                    >
                                    {{
                                        formatCoordinate(form.coordinates_lat)
                                    }},
                                    {{ formatCoordinate(form.coordinates_lng) }}
                                </div>
                                <div
                                    v-if="selectedLocationName"
                                    class="text-sm text-gray-600 mt-1"
                                >
                                    <span class="font-medium">Location:</span>
                                    {{ selectedLocationName }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 text-gray-500">
                    <svg
                        class="w-16 h-16 mx-auto mb-4 text-gray-300"
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
                    <p class="text-base font-medium">
                        GIS mapping is currently disabled
                    </p>
                    <p class="text-sm mt-2">
                        Enable it above to add precise location coordinates to
                        your property listing
                    </p>
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
                v-if="canFeatureProperty"
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

            <!-- Property Images -->
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

                    <!-- Current Images (if any) -->
                    <div
                        v-if="property.images && property.images.length > 0"
                        class="mt-4"
                    >
                        <p class="text-sm font-semibold text-gray-700 mb-2">
                            Current Images:
                        </p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div
                                v-for="(image, index) in property.images"
                                :key="`current-${index}`"
                                class="relative"
                                :class="{
                                    'opacity-50':
                                        form.remove_images.includes(index),
                                }"
                            >
                                <img
                                    :src="getImageUrl(image)"
                                    :alt="`Property Image ${index + 1}`"
                                    class="w-full h-24 object-cover rounded-lg border"
                                    :class="{
                                        grayscale:
                                            form.remove_images.includes(index),
                                    }"
                                />
                                <span
                                    v-if="
                                        index === 0 &&
                                        !form.remove_images.includes(index)
                                    "
                                    class="absolute top-1 left-1 bg-blue-500 text-white text-xs px-2 py-1 rounded"
                                >
                                    Main
                                </span>
                                <button
                                    v-if="!form.remove_images.includes(index)"
                                    type="button"
                                    @click="removeImage(index)"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                                    title="Remove this image"
                                >
                                    ×
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    @click="
                                        form.remove_images =
                                            form.remove_images.filter(
                                                (i) => i !== index
                                            )
                                    "
                                    class="absolute top-1 right-1 bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-green-600"
                                    title="Keep this image"
                                >
                                    ↺
                                </button>
                                <div
                                    v-if="form.remove_images.includes(index)"
                                    class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-lg"
                                >
                                    <span
                                        class="text-white font-semibold text-xs"
                                        >Will be removed</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Images Preview -->
                    <div v-if="imagePreview.length > 0" class="mt-4">
                        <p class="text-sm font-semibold text-gray-700 mb-2">
                            New Images to Upload:
                        </p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div
                                v-for="(preview, index) in imagePreview"
                                :key="`new-${index}`"
                                class="relative"
                            >
                                <img
                                    :src="preview"
                                    :alt="`Preview ${index + 1}`"
                                    class="w-full h-24 object-cover rounded-lg border-2 border-green-500"
                                />
                                <span
                                    class="absolute top-1 left-1 bg-green-500 text-white text-xs px-2 py-1 rounded"
                                >
                                    NEW
                                </span>
                                <button
                                    type="button"
                                    @click="removeNewImage(index)"
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

                <VirtualTourUploader
                    v-model="form.has_virtual_tour"
                    :existing-images="property.virtual_tour_images || []"
                    @images-changed="handleVirtualTourImagesChanged"
                    @existing-removed="removeVirtualTourImage"
                />
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
                    :disabled="processing"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
                >
                    <svg
                        v-if="!processing"
                        class="w-5 h-5"
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
                    <svg
                        v-else
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
                    <span v-if="!processing">Update Property</span>
                    <span v-else>Updating...</span>
                </button>
            </div>
        </form>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from "vue";
import { useForm, usePage, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import PropertyTypeMultiSelect from "@/Components/PropertyTypeMultiSelect.vue";
import VirtualTourUploader from "@/Components/VirtualTourUploader.vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const props = defineProps({
    property: Object,
    user: Object,
    municipalities: Array,
    types: Array,
});

// --- SYNCHRONIZED LOGIC FROM CreateSimple.vue ---
const page = usePage();
const errors = computed(() => page.props.errors);

// Property types for dropdown
const propertyTypes = computed(() => props.types || []);

const processing = ref(false);
const imagePreview = ref([]);
const virtualTourPreview = ref([]);
const nearbyLandmarksText = ref("");
const enableGISMapping = ref(
    !!(props.property.coordinates_lat && props.property.coordinates_lng)
);

// GIS/Location handling - synchronized with CreateSimple.vue
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
const showAdvancedOptions = ref(false);
const coordinates = ref({
    lat: props.property.coordinates_lat,
    lng: props.property.coordinates_lng,
});

// Can feature property - check if user has permission
const canFeatureProperty = computed(() => {
    return (
        props.user &&
        (props.user.role === "admin" || props.user.role === "super_admin")
    );
});

// Safely parse types from property data
const parsePropertyTypes = () => {
    if (!props.property.types && !props.property.type) {
        return [];
    }

    // If types is already an array
    if (Array.isArray(props.property.types)) {
        return props.property.types.filter((t) => t && typeof t === "string");
    }

    // If types is a string (might be JSON)
    if (typeof props.property.types === "string") {
        try {
            const parsed = JSON.parse(props.property.types);
            if (Array.isArray(parsed)) {
                return parsed.filter((t) => t && typeof t === "string");
            }
            return [props.property.types];
        } catch (e) {
            return [props.property.types];
        }
    }

    // Fallback to old single type field
    if (props.property.type && typeof props.property.type === "string") {
        return [props.property.type];
    }

    return [];
};

const form = useForm({
    title: props.property.title,
    types: parsePropertyTypes(),
    type: props.property.type, // Deprecated, kept for backward compatibility
    custom_type_text: props.property.custom_type_text || "",
    description: props.property.description,
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
    nearby_landmarks: Array.isArray(props.property.nearby_landmarks)
        ? props.property.nearby_landmarks
        : props.property.nearby_landmarks
        ? [props.property.nearby_landmarks]
        : [],
    new_images: [], // New images to upload
    remove_images: [], // Existing images to remove (indices)
    has_virtual_tour: props.property.has_virtual_tour || false,
    new_virtual_tour_images: [],
    remove_virtual_tour_images: [], // Track removed virtual tour images
    coordinates_lat: props.property.coordinates_lat,
    coordinates_lng: props.property.coordinates_lng,
    status: props.property.status,
    road_access: props.property.road_access,
    electricity_available: props.property.electricity_available,
    water_source: props.property.water_source,
    internet_available: props.property.internet_available,
    is_featured: props.property.is_featured,
    gis_data: props.property.gis_data || "",
    tour_hotspots: props.property.tour_hotspots || "",
    _method: "PUT", // Method spoofing for file uploads
});

// Watch for changes in area or price per sqm to auto-calculate total
watch([() => form.lot_area_sqm, () => form.price_per_sqm], () => {
    calculateTotalPrice();
});

const calculateTotalPrice = () => {
    const area = Number(form.lot_area_sqm);
    const price = Number(form.price_per_sqm);
    if (!isNaN(area) && !isNaN(price) && area > 0 && price >= 0) {
        form.total_price = area * price;
    }
};

// When user edits total price directly, back-compute price per sqm
const calculatePricePerSqm = () => {
    const area = Number(form.lot_area_sqm) || 0;
    const total = Number(form.total_price) || 0;
    if (area > 0 && total >= 0) {
        form.price_per_sqm = total / area;
    }
};

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

    // Don't clear existing previews, just add new ones
    files.forEach((file) => {
        if (file.size > 2 * 1024 * 1024) {
            alert(`${file.name} is too large. Maximum file size is 2MB.`);
            return;
        }
        form.new_images.push(file);
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const handleDocumentUpload = (event) => {
    const files = Array.from(event.target.files);
    form.new_documents = files;
};

const handleVirtualTourImagesChanged = (images) => {
    // images are File objects for new uploads
    form.new_virtual_tour_images = images;
};

const removeVirtualTourImage = (index) => {
    // Remove by passing the actual path string as backend expects
    const existing = Array.isArray(props.property.virtual_tour_images)
        ? props.property.virtual_tour_images
        : [];
    const toRemove = existing[index];
    if (toRemove && !form.remove_virtual_tour_images.includes(toRemove)) {
        form.remove_virtual_tour_images.push(toRemove);
        console.log("Marked virtual tour image for removal:", toRemove);
    }
};

const onLocationSelected = (location) => {
    form.coordinates_lat = location.lat;
    form.coordinates_lng = location.lng;
    selectedLocationName.value = location.name || "";
};

const removeImage = (index) => {
    // Mark existing image for removal
    if (!form.remove_images.includes(index)) {
        form.remove_images.push(index);
    }
    console.log("Marked image for removal:", index);
    console.log("Images to remove:", form.remove_images);
};

const removeNewImage = (index) => {
    // Remove from new image uploads
    imagePreview.value.splice(index, 1);
    form.new_images.splice(index, 1);
};

const handleVirtualTourUpload = (event) => {
    const files = Array.from(event.target.files);
    if (files.length > 20) {
        alert("You can upload a maximum of 20 panoramic images.");
        return;
    }
    virtualTourPreview.value = [];
    form.new_virtual_tour_images = [];
    files.forEach((file, index) => {
        if (file.size > 5 * 1024 * 1024) {
            alert(`${file.name} is too large. Maximum file size is 5MB.`);
            return;
        }
        const fileName = file.name.toLowerCase();
        if (
            !fileName.endsWith(".jpg") &&
            !fileName.endsWith(".jpeg") &&
            !fileName.endsWith(".png")
        ) {
            alert(`${file.name} must be a JPG, JPEG, or PNG file.`);
            return;
        }
        form.new_virtual_tour_images.push(file);
        const reader = new FileReader();
        reader.onload = (e) => {
            virtualTourPreview.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

// GIS Mapping logic (Leaflet)
const initializeLeafletMap = async () => {
    if (!mapContainer.value || mapInitialized.value) return;
    try {
        leafletMap.value = L.map(mapContainer.value, {
            center: [9.634, 123.853],
            zoom: mapZoomLevel.value,
            zoomControl: false,
        });
        // Base street layer
        const streetLayer = L.tileLayer(
            "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            {
                attribution:
                    '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
            }
        ).addTo(leafletMap.value);

        // Satellite layer (Esri World Imagery)
        const satelliteLayer = L.tileLayer(
            "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
            {
                attribution:
                    '© <a href="https://www.esri.com/">Esri</a> © <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19,
            }
        );

        // Layer control
        L.control
            .layers({ "Street Map": streetLayer, Satellite: satelliteLayer })
            .addTo(leafletMap.value);
        leafletMap.value.on("click", (e) => {
            const { lat, lng } = e.latlng;
            form.coordinates_lat = parseFloat(lat.toFixed(6));
            form.coordinates_lng = parseFloat(lng.toFixed(6));
            addLeafletMarker(lat, lng);
        });
        mapInitialized.value = true;

        const boholBounds = L.latLngBounds([9.45, 123.5], [10.25, 124.7]);
        if (form.coordinates_lat && form.coordinates_lng) {
            addLeafletMarker(form.coordinates_lat, form.coordinates_lng);
            leafletMap.value.setView(
                [form.coordinates_lat, form.coordinates_lng],
                13 // show more surrounding context
            );
        } else {
            leafletMap.value.fitBounds(boholBounds, { padding: [30, 30] });
        }
    } catch (error) {
        alert("Unable to load map. Please check your internet connection.");
    }
};

const addLeafletMarker = (lat, lng) => {
    if (!leafletMap.value) return;
    if (currentMarker.value) {
        leafletMap.value.removeLayer(currentMarker.value);
    }
    const redIcon = L.icon({
        iconUrl:
            "data:image/svg+xml;base64," +
            btoa(`
            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='#ef4444' width='32' height='32'>
                <circle cx='12' cy='12' r='10'/>
            </svg>
        `),
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32],
    });
    currentMarker.value = L.marker([lat, lng], {
        icon: redIcon,
        draggable: true,
    }).addTo(leafletMap.value);
    currentMarker.value
        .bindPopup(
            `<strong>${
                selectedLocationName.value || "Property Location"
            }</strong><br><small>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(
                6
            )}</small>`
        )
        .openPopup();
    currentMarker.value.on("dragend", (e) => {
        const newPos = e.target.getLatLng();
        form.coordinates_lat = parseFloat(newPos.lat.toFixed(6));
        form.coordinates_lng = parseFloat(newPos.lng.toFixed(6));

        reverseGeocode(newPos.lat, newPos.lng);
    });
};

// Comprehensive location search and GIS functions - synced with CreateSimple.vue
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

            // Update map if open
            if (showInteractiveMap.value && leafletMap.value) {
                centerMapOnLocation(
                    position.coords.latitude,
                    position.coords.longitude
                );
            }

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
                    errorMessage += "User denied the request for Geolocation.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage += "Location information is unavailable.";
                    break;
                case error.TIMEOUT:
                    errorMessage +=
                        "The request to get user location timed out.";
                    break;
                default:
                    errorMessage += "An unknown error occurred.";
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
        // 1) Online geocoding attempt for broader coverage
        try {
            const q = addressSearch.value.trim();
            const regionHint = "Bohol Philippines";
            const url = `https://nominatim.openstreetmap.org/search?format=json&limit=8&q=${encodeURIComponent(
                q + " " + regionHint
            )}`;
            const response = await fetch(url, {
                headers: { "User-Agent": "GeoCasaBohol/1.0" },
            });
            if (response.ok) {
                const data = await response.json();
                const mapped = data.map((r) => ({
                    id: `osm-${r.place_id}`,
                    name:
                        (r.display_name || "")
                            .split(",")
                            .slice(0, 2)
                            .join(", ")
                            .trim() || r.display_name,
                    description: r.display_name,
                    lat: parseFloat(r.lat),
                    lng: parseFloat(r.lon),
                }));
                if (mapped.length > 0) {
                    searchResults.value = mapped.slice(0, 8);
                    return; // success via Nominatim; skip local fallback
                }
            }
        } catch (e) {
            // Ignore and fallback
        }

        // 2) Fallback local dataset
        const bohoLocations = [
            {
                id: 1,
                name: "Tagbilaran City",
                description: "Capital city and commercial center",
                lat: 9.634,
                lng: 123.853,
                keywords: ["tagbilaran", "city", "capital"],
            },
            {
                id: 2,
                name: "Cogon",
                description: "Tagbilaran - Barangay Cogon",
                lat: 9.6345,
                lng: 123.8525,
                keywords: ["cogon", "barangay", "tagbilaran"],
            },
            {
                id: 3,
                name: "Dao",
                description: "Tagbilaran - Barangay Dao",
                lat: 9.645,
                lng: 123.852,
                keywords: ["dao", "barangay", "tagbilaran"],
            },
            {
                id: 4,
                name: "Bool",
                description: "Tagbilaran - Barangay Bool",
                lat: 9.638,
                lng: 123.855,
                keywords: ["bool", "barangay", "tagbilaran"],
            },
            {
                id: 9,
                name: "Panglao",
                description: "Island municipality",
                lat: 9.55,
                lng: 123.7833,
                keywords: ["panglao", "municipality", "island"],
            },
            {
                id: 10,
                name: "Alona Beach",
                description: "Panglao - Main beach area",
                lat: 9.5547,
                lng: 123.7598,
                keywords: ["alona", "beach", "panglao"],
            },
            {
                id: 22,
                name: "Baclayon",
                description: "Historic municipality",
                lat: 9.6157,
                lng: 123.9054,
                keywords: ["baclayon", "municipality"],
            },
            {
                id: 28,
                name: "Loboc",
                description: "Municipality - River cruise",
                lat: 9.6389,
                lng: 124.0301,
                keywords: ["loboc", "municipality", "river"],
            },
            {
                id: 34,
                name: "Carmen",
                description: "Municipality - Chocolate Hills",
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
                keywords: ["chocolate", "hills", "carmen"],
            },
            {
                id: 120,
                name: "Dimiao",
                description: "Municipality in central Bohol",
                lat: 9.65,
                lng: 124.0167,
                keywords: ["dimiao", "municipality"],
            },
        ];

        const searchTerm = addressSearch.value.toLowerCase();
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
            searchResults.value = matches.slice(0, 8);
            if (matches.length === 1) {
                setTimeout(() => selectSearchResult(matches[0]), 500);
            }
        } else {
            alert(
                "No locations found matching your search. Try searching for a municipality or barangay name."
            );
        }
    } catch (error) {
        alert("Error searching for location. Please try again.");
    } finally {
        searchingAddress.value = false;
    }
};

const selectSearchResult = (result) => {
    form.coordinates_lat = result.lat;
    form.coordinates_lng = result.lng;
    selectedLocationName.value = result.name;
    addressSearch.value = result.name;
    searchResults.value = [];

    if (!showInteractiveMap.value) {
        showInteractiveMap.value = true;
        setTimeout(() => {
            if (leafletMap.value) {
                centerMapOnLocation(result.lat, result.lng);
            } else {
                setTimeout(
                    () => centerMapOnLocation(result.lat, result.lng),
                    300
                );
            }
        }, 200);
    } else {
        centerMapOnLocation(result.lat, result.lng);
    }
};

const reverseGeocode = async (lat, lng) => {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`,
            { headers: { "User-Agent": "GeoCasaBohol/1.0" } }
        );

        if (response.ok) {
            const data = await response.json();
            if (data.display_name) {
                selectedLocationName.value = data.display_name;
                addressSearch.value = data.display_name;

                if (currentMarker.value) {
                    currentMarker.value
                        .setPopupContent(
                            `<strong>${
                                data.display_name
                            }</strong><br><small>Lat: ${lat.toFixed(
                                6
                            )}, Lng: ${lng.toFixed(6)}</small>`
                        )
                        .openPopup();
                }
            }
        }
    } catch (error) {
        console.log("Geocoding failed, using fallback");
        fallbackReverseGeocode(lat, lng);
    }
};

const fallbackReverseGeocode = (lat, lng) => {
    const majorLocations = [
        { name: "Near Alona Beach", lat: 9.5547, lng: 123.7598 },
        { name: "Near Tagbilaran City", lat: 9.634, lng: 123.853 },
        { name: "Near Chocolate Hills", lat: 9.9169, lng: 124.1695 },
    ];

    let closestLocation = null;
    let minDistance = Infinity;

    majorLocations.forEach((location) => {
        const distance = Math.sqrt(
            Math.pow(lat - location.lat, 2) + Math.pow(lng - location.lng, 2)
        );
        if (distance < minDistance) {
            minDistance = distance;
            closestLocation = location;
        }
    });

    if (closestLocation && minDistance < 0.15) {
        selectedLocationName.value = closestLocation.name;
        addressSearch.value = closestLocation.name;
    } else {
        selectedLocationName.value = "Bohol Location";
        addressSearch.value = "Custom Location in Bohol";
    }
};

const centerMapOnLocation = (lat, lng) => {
    if (leafletMap.value) {
        leafletMap.value.setView([lat, lng], 15);
        addLeafletMarker(lat, lng);
    }
};

const zoomToCurrentLocation = () => {
    getCurrentLocation();
    if (form.coordinates_lat && form.coordinates_lng) {
        centerMapOnLocation(form.coordinates_lat, form.coordinates_lng);
    }
};

const resetMapView = () => {
    // Mirror CreateSimple.vue: province overview
    if (leafletMap.value) {
        leafletMap.value.setView([9.634, 123.853], 10);
        mapZoomLevel.value = 10;
    }
};

const zoomIn = () => {
    if (leafletMap.value) {
        const currentZoom = leafletMap.value.getZoom();
        leafletMap.value.setZoom(Math.min(currentZoom + 1, 20));
    }
};

const zoomOut = () => {
    if (leafletMap.value) {
        const currentZoom = leafletMap.value.getZoom();
        leafletMap.value.setZoom(Math.max(currentZoom - 1, 8));
    }
};

const clearLocation = () => {
    form.coordinates_lat = null;
    form.coordinates_lng = null;
    selectedLocationName.value = "";
    addressSearch.value = "";
    searchResults.value = [];

    if (currentMarker.value && leafletMap.value) {
        leafletMap.value.removeLayer(currentMarker.value);
        currentMarker.value = null;
    }

    if (showInteractiveMap.value) {
        resetMapView();
    }
};

const clearCoordinates = () => {
    clearLocation();
};

const formatCoordinate = (coord) => {
    return coord ? parseFloat(coord).toFixed(6) : "Not set";
};

watch(showInteractiveMap, async (newValue) => {
    if (newValue && !mapInitialized.value) {
        await nextTick();
        setTimeout(() => {
            initializeLeafletMap();
        }, 100);
    }
});

onMounted(() => {
    // Scroll to top when page loads
    window.scrollTo(0, 0);

    // Initialize nearby landmarks text from array or string
    if (props.property.nearby_landmarks) {
        if (Array.isArray(props.property.nearby_landmarks)) {
            nearbyLandmarksText.value =
                props.property.nearby_landmarks.join(", ");
        } else if (typeof props.property.nearby_landmarks === "string") {
            nearbyLandmarksText.value = props.property.nearby_landmarks;
        }
    }

    // Debug: Log property data
    console.log("Property data:", props.property);
    console.log("Property types (raw):", props.property.types);
    console.log("Property type (raw):", props.property.type);
    console.log("Parsed types:", parsePropertyTypes());
    console.log("Form data:", form);
    console.log("Form types:", form.types);
    console.log("Available types from props:", props.types);
});

onUnmounted(() => {
    if (leafletMap.value) {
        leafletMap.value.remove();
    }
});

// --- END SYNCHRONIZATION ---

// Helper function to get image URL
const getImageUrl = (image) => {
    if (!image) return "";

    // If it's already a full URL, return it
    if (image.startsWith("http://") || image.startsWith("https://")) {
        return image;
    }

    // If it starts with /storage, return as is
    if (image.startsWith("/storage/")) {
        return image;
    }

    // Otherwise, prepend /storage/
    return `/storage/${image}`;
};

const submit = () => {
    console.log("Submit function called");

    // Ensure at least one type is selected
    if (!form.types || form.types.length === 0) {
        console.error("At least one property type must be selected");
        alert("Please select at least one property type");
        return;
    }

    // Validate custom type if "other" is selected
    if (form.types.includes("other") && !form.custom_type_text) {
        console.error("Please specify the custom property type");
        alert("Please specify the custom property type");
        return;
    }

    // Set the first type as the legacy 'type' field for backward compatibility
    form.type = form.types[0];

    console.log("Form data:", form.data());
    console.log("Images to remove:", form.remove_images);
    console.log("New images to upload:", form.new_images);

    processing.value = true;

    // Use POST with _method spoofing for file uploads (Laravel requirement)
    form.post(route("broker.properties.update", props.property.slug), {
        onSuccess: () => {
            console.log("Property updated successfully!");
            processing.value = false;
        },
        onError: (errors) => {
            console.log("Validation errors:", errors);
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>
