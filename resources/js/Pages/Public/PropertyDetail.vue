<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Navigation -->
        <PublicNavigation :auth="$page.props.auth" />

        <!-- Enhanced Breadcrumb -->
        <div class="bg-white/80 backdrop-blur-sm border-b border-gray-200/50 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <nav class="flex items-center justify-between" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm">
                        <li>
                            <Link :href="route('home')" class="text-gray-500 hover:text-blue-600 transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                </svg>
                            </Link>
                        </li>
                        <li><span class="text-gray-400">/</span></li>
                        <li>
                            <Link :href="route('public.properties')" class="text-gray-500 hover:text-blue-600 transition-colors">
                                Properties
                            </Link>
                        </li>
                        <li><span class="text-gray-400">/</span></li>
                        <li class="text-gray-900 font-medium truncate max-w-xs">
                            {{ property.title }}
                        </li>
                    </ol>
                    
                    <!-- Back Button -->
                    <button @click="$router.go(-1)" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back
                    </button>
                </nav>
            </div>
        </div>

        <!-- Property Details -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Enhanced Property Images Gallery -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <!-- Main Image Display -->
                        <div class="relative aspect-video lg:aspect-[4/3] group">
                            <img
                                :src="currentImage"
                                :alt="property.title"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                :class="{ 'blur-sm': imageLoading }"
                                @load="imageLoading = false"
                                @error="handleImageError"
                            />
                            
                            <!-- Loading Overlay -->
                            <div v-if="imageLoading" class="absolute inset-0 flex items-center justify-center bg-gray-100">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            </div>
                            
                            <!-- Image Navigation Arrows -->
                            <div v-if="property.images && property.images.length > 1" class="absolute inset-y-0 left-0 right-0 flex items-center justify-between p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="previousImage" class="p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                                <button @click="nextImage" class="p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Image Counter -->
                            <div v-if="property.images && property.images.length > 1" class="absolute bottom-4 right-4 bg-black/50 text-white px-3 py-1 rounded-full text-sm">
                                {{ currentImageIndex + 1 }} / {{ property.images.length }}
                            </div>
                            
                            <!-- Fullscreen Button -->
                            <button @click="openImageModal" class="absolute top-4 right-4 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Enhanced Image Thumbnails -->
                        <div v-if="property.images && property.images.length > 1" class="p-4 lg:p-6">
                            <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
                                <button
                                    v-for="(image, index) in property.images"
                                    :key="index"
                                    @click="selectImage(index)"
                                    class="flex-shrink-0 relative group"
                                >
                                    <div class="w-16 h-16 lg:w-20 lg:h-20 rounded-xl overflow-hidden border-2 transition-all duration-200"
                                         :class="currentImageIndex === index ? 'border-blue-500 shadow-lg' : 'border-gray-200 hover:border-gray-300'">
                                        <img
                                            :src="getImageUrl(image)"
                                            :alt="`Image ${index + 1}`"
                                            class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-110"
                                            loading="lazy"
                                        />
                                    </div>
                                    <div v-if="currentImageIndex === index" class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-blue-500 rounded-full"></div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Virtual Tour Section -->
                    <div v-if="property.has_virtual_tour && hasVirtualTourData" class="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div class="p-4 lg:p-6 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">Virtual Tour</h3>
                                        <p class="text-gray-600">Explore this property in 360°</p>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-purple-100 to-blue-100 text-purple-800 px-4 py-2 rounded-full text-sm font-semibold">
                                    🌟 Interactive Experience
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4 lg:p-6">
                            <VirtualTourViewer
                                :tour-images="virtualTourImages"
                                :hotspots="virtualTourHotspots"
                                :auto-rotate="false"
                                :rotation-speed="0.3"
                                class="rounded-xl overflow-hidden"
                            />
                        </div>
                    </div>

                    <!-- Property Information -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h1
                                    class="text-3xl font-bold text-gray-900 mb-2"
                                >
                                    {{ property.title }}
                                </h1>
                                <p class="text-gray-600 flex items-center">
                                    <svg
                                        class="w-5 h-5 mr-2"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    {{ property.full_address }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-blue-600">
                                    {{ property.formatted_total_price }}
                                </div>
                                <div class="text-gray-600">
                                    {{ property.formatted_price_per_sqm }}/sqm
                                </div>
                            </div>
                        </div>

                        <!-- Property Type & Status -->
                        <div class="flex space-x-4 mb-6">
                            <span
                                class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium"
                            >
                                {{ formatPropertyType(property.type) }}
                            </span>
                            <span
                                class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium"
                            >
                                Available
                            </span>
                            <!-- Virtual Tour Badge -->
                            <span
                                v-if="property.has_virtual_tour"
                                class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium flex items-center space-x-1"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span>Virtual Tour</span>
                            </span>
                        </div>

                        <!-- Key Details -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    {{ property.formatted_area }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    Total Area
                                </div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    {{ property.title_type || "N/A" }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    Title Type
                                </div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    {{
                                        property.zoning_classification || "N/A"
                                    }}
                                </div>
                                <div class="text-sm text-gray-600">Zoning</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    {{ property.municipality }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    Municipality
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-3"
                            >
                                Description
                            </h3>
                            <p class="text-gray-700 leading-relaxed">
                                {{ property.description }}
                            </p>
                        </div>

                        <!-- Features & Utilities -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h3
                                    class="text-lg font-semibold text-gray-900 mb-3"
                                >
                                    Utilities & Access
                                </h3>
                                <ul class="space-y-2">
                                    <li class="flex items-center">
                                        <svg
                                            class="w-5 h-5 mr-3"
                                            :class="
                                                property.electricity_available
                                                    ? 'text-green-500'
                                                    : 'text-gray-400'
                                            "
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707z"
                                            />
                                        </svg>
                                        <span
                                            :class="
                                                property.electricity_available
                                                    ? 'text-gray-900'
                                                    : 'text-gray-500'
                                            "
                                        >
                                            Electricity
                                            {{
                                                property.electricity_available
                                                    ? "Available"
                                                    : "Not Available"
                                            }}
                                        </span>
                                    </li>
                                    <li class="flex items-center">
                                        <svg
                                            class="w-5 h-5 mr-3"
                                            :class="
                                                property.water_source
                                                    ? 'text-blue-500'
                                                    : 'text-gray-400'
                                            "
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span
                                            :class="
                                                property.water_source
                                                    ? 'text-gray-900'
                                                    : 'text-gray-500'
                                            "
                                        >
                                            Water Source
                                            {{
                                                property.water_source
                                                    ? "Available"
                                                    : "Not Available"
                                            }}
                                        </span>
                                    </li>
                                    <li class="flex items-center">
                                        <svg
                                            class="w-5 h-5 mr-3"
                                            :class="
                                                property.road_access
                                                    ? 'text-green-500'
                                                    : 'text-gray-400'
                                            "
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span
                                            :class="
                                                property.road_access
                                                    ? 'text-gray-900'
                                                    : 'text-gray-500'
                                            "
                                        >
                                            Road Access
                                            {{
                                                property.road_access
                                                    ? "Available"
                                                    : "Limited"
                                            }}
                                        </span>
                                    </li>
                                    <li class="flex items-center">
                                        <svg
                                            class="w-5 h-5 mr-3"
                                            :class="
                                                property.internet_available
                                                    ? 'text-purple-500'
                                                    : 'text-gray-400'
                                            "
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span
                                            :class="
                                                property.internet_available
                                                    ? 'text-gray-900'
                                                    : 'text-gray-500'
                                            "
                                        >
                                            Internet
                                            {{
                                                property.internet_available
                                                    ? "Available"
                                                    : "Not Available"
                                            }}
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <div
                                v-if="
                                    property.nearby_landmarks &&
                                    property.nearby_landmarks.length > 0
                                "
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-900 mb-3"
                                >
                                    Nearby Landmarks
                                </h3>
                                <ul class="space-y-1">
                                    <li
                                        v-for="landmark in property.nearby_landmarks"
                                        :key="landmark"
                                        class="text-gray-700"
                                    >
                                        • {{ landmark }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Map -->
                        <div v-if="property.google_maps_link" class="mb-6">
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-3"
                            >
                                Location
                            </h3>
                            <a
                                :href="property.google_maps_link"
                                target="_blank"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                View on Google Maps
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Broker Information -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Listed by
                        </h3>
                        <div class="flex items-center mb-4">
                            <div
                                class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-lg"
                            >
                                {{ property.broker?.name?.charAt(0) || 'G' }}
                            </div>
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">
                                    {{ property.broker?.name || 'GeoCasa Bohol' }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    Licensed Broker
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex items-center">
                                <svg
                                    class="w-4 h-4 mr-2"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"
                                    />
                                    <path
                                        d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"
                                    />
                                </svg>
                                {{ property.broker?.email || 'info@geocasabohol.com' }}
                            </div>
                        </div>
                    </div>

                    <!-- Inquiry Form -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Interested in this property?
                        </h3>

                        <div
                            v-if="$page.props.flash.success"
                            class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded"
                        >
                            {{ $page.props.flash.success }}
                        </div>

                        <form @submit.prevent="submitInquiry" class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Full Name *</label
                                >
                                <input
                                    v-model="inquiryForm.name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Your full name"
                                />
                                <div
                                    v-if="errors.name"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ errors.name }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Email Address *</label
                                >
                                <input
                                    v-model="inquiryForm.email"
                                    type="email"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="your.email@example.com"
                                />
                                <div
                                    v-if="errors.email"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ errors.email }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Phone Number</label
                                >
                                <input
                                    v-model="inquiryForm.phone"
                                    type="tel"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="+63 123 456 7890"
                                />
                                <div
                                    v-if="errors.phone"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ errors.phone }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Message *</label
                                >
                                <textarea
                                    v-model="inquiryForm.message"
                                    required
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :placeholder="`I'm interested in ${property.title}. Please provide more information.`"
                                ></textarea>
                                <div
                                    v-if="errors.message"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ errors.message }}
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="processing"
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50"
                            >
                                <span v-if="processing">Sending...</span>
                                <span v-else>Send Inquiry</span>
                            </button>
                        </form>

                        <div class="mt-4 text-xs text-gray-500">
                            By submitting this form, you agree to be contacted
                            by the broker regarding this property.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Image Modal -->
        <div v-if="showImageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90" @click="closeImageModal">
            <div class="relative max-w-7xl max-h-full p-4" @click.stop>
                <img :src="currentImage" :alt="property.title" class="max-w-full max-h-full object-contain rounded-lg" />
                <button @click="closeImageModal" class="absolute top-2 right-2 p-2 text-white hover:bg-white/20 rounded-full transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                
                <!-- Modal Navigation -->
                <div v-if="property.images && property.images.length > 1" class="absolute inset-y-0 left-0 right-0 flex items-center justify-between p-4">
                    <button @click="previousImage" class="p-3 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button @click="nextImage" class="p-3 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <PublicFooter />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import PublicFooter from "@/Components/PublicFooter.vue";
import VirtualTourViewer from "@/Components/VirtualTourViewer.vue";

const props = defineProps({
    property: Object,
    errors: Object,
});

// Initialize the inquiry form
const inquiryForm = useForm({
    name: '',
    email: '',
    phone: '',
    message: `I'm interested in learning more about this property. Please provide additional details.`
});

// Enhanced image gallery state
const currentImageIndex = ref(0);
const imageLoading = ref(false);
const showImageModal = ref(false);

// Computed properties
const currentImage = computed(() => {
    if (props.property.images && props.property.images.length > 0) {
        return getImageUrl(props.property.images[currentImageIndex.value]);
    }
    return props.property.main_image;
});

// Virtual Tour computed properties
const hasVirtualTourData = computed(() => {
    return props.property.virtual_tour_images && 
           props.property.virtual_tour_images.length > 0;
});

const virtualTourImages = computed(() => {
    if (!props.property.virtual_tour_images) return [];
    
    return props.property.virtual_tour_images.map((image, index) => ({
        url: getImageUrl(image),
        thumbnail: getImageUrl(image), // You can add separate thumbnails later
        title: `View ${index + 1}`,
        description: `360° view of ${props.property.title}`
    }));
});

const virtualTourHotspots = computed(() => {
    if (!props.property.tour_hotspots) return [];
    
    try {
        const hotspots = typeof props.property.tour_hotspots === 'string' 
            ? JSON.parse(props.property.tour_hotspots) 
            : props.property.tour_hotspots;
            
        return Array.isArray(hotspots) ? hotspots : [];
    } catch (error) {
        console.warn('Failed to parse tour hotspots:', error);
        return [];
    }
});

const submitInquiry = () => {
    inquiryForm.post(route("public.inquiries.store", props.property.slug), {
        preserveScroll: true,
        onSuccess: () => {
            inquiryForm.reset("name", "email", "phone");
            inquiryForm.message = `I'm interested in ${props.property.title}. Please provide more information about this property.`;
        },
    });
};

const getImageUrl = (image) => {
    return image.startsWith("http") ? image : `/storage/${image}`;
};

const formatPropertyType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

// Enhanced image gallery methods
const selectImage = (index) => {
    if (index >= 0 && index < props.property.images.length) {
        imageLoading.value = true;
        currentImageIndex.value = index;
    }
};

const nextImage = () => {
    if (props.property.images && props.property.images.length > 1) {
        const nextIndex = (currentImageIndex.value + 1) % props.property.images.length;
        selectImage(nextIndex);
    }
};

const previousImage = () => {
    if (props.property.images && props.property.images.length > 1) {
        const prevIndex = currentImageIndex.value === 0 
            ? props.property.images.length - 1 
            : currentImageIndex.value - 1;
        selectImage(prevIndex);
    }
};

const openImageModal = () => {
    showImageModal.value = true;
    document.body.style.overflow = 'hidden';
};

const closeImageModal = () => {
    showImageModal.value = false;
    document.body.style.overflow = 'auto';
};

const handleImageError = () => {
    imageLoading.value = false;
    console.warn('Failed to load image:', currentImage.value);
};

// Keyboard navigation
const handleKeydown = (event) => {
    if (showImageModal.value) {
        switch (event.key) {
            case 'Escape':
                closeImageModal();
                break;
            case 'ArrowLeft':
                previousImage();
                break;
            case 'ArrowRight':
                nextImage();
                break;
        }
    }
};

// Lifecycle hooks
onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = 'auto';
});
</script>

<style scoped>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.aspect-video {
    aspect-ratio: 16 / 9;
}

.aspect-\[4\/3\] {
    aspect-ratio: 4 / 3;
}

@media (max-width: 768px) {
    .aspect-video {
        aspect-ratio: 4 / 3;
    }
}
</style>
