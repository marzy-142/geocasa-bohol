<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Navigation -->
        <PublicNavigation :auth="$page.props.auth" />

        <!-- Enhanced Breadcrumb -->
        <div
            class="bg-white/80 backdrop-blur-sm border-b border-gray-200/50 sticky top-0 z-40"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <nav
                    class="flex items-center justify-between"
                    aria-label="Breadcrumb"
                >
                    <ol class="flex items-center space-x-2 text-sm">
                        <li>
                            <Link
                                :href="route('home')"
                                class="text-gray-500 hover:text-blue-600 transition-colors"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"
                                    />
                                </svg>
                            </Link>
                        </li>
                        <li><span class="text-gray-400">/</span></li>
                        <li>
                            <Link
                                :href="route('public.properties')"
                                class="text-gray-500 hover:text-blue-600 transition-colors"
                            >
                                Properties
                            </Link>
                        </li>
                        <li><span class="text-gray-400">/</span></li>
                        <li class="text-gray-900 font-medium truncate max-w-xs">
                            {{ property.title }}
                        </li>
                    </ol>

                    <!-- Back Button -->
                    <Link
                        :href="route('public.properties')"
                        class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all"
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
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                        Back to Properties
                    </Link>
                </nav>
            </div>
        </div>

        <!-- Property Details -->
        <div
            id="main-content"
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8"
        >
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Availability Status Banner -->
                    <div
                        v-if="
                            property.status !== 'available' ||
                            isUnderTransaction
                        "
                        class="rounded-xl border shadow-sm p-4"
                        :class="{
                            'bg-amber-50 border-amber-200 text-amber-800':
                                property.status === 'reserved' ||
                                property.status === 'under_negotiation' ||
                                isUnderTransaction,
                            'bg-rose-50 border-rose-200 text-rose-800':
                                property.status === 'sold',
                        }"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-xl">🔔</span>
                            <div>
                                <div class="font-semibold">
                                    <span v-if="property.status === 'reserved'"
                                        >This property is currently
                                        reserved.</span
                                    >
                                    <span
                                        v-else-if="
                                            property.status ===
                                                'under_negotiation' ||
                                            isUnderTransaction
                                        "
                                        >This property is currently under
                                        transaction.</span
                                    >
                                    <span v-else-if="property.status === 'sold'"
                                        >This property has been sold.</span
                                    >
                                    <span v-else
                                        >This property is not currently
                                        available.</span
                                    >
                                </div>
                                <div class="text-sm opacity-90">
                                    <span v-if="property.status === 'sold'"
                                        >You can still browse similar available
                                        properties below.</span
                                    >
                                    <span v-else
                                        >New inquiries are paused while this
                                        status is active.</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Media Viewer Tabs (Gallery + Panorama) -->
                    <div
                        v-if="property.has_virtual_tour && hasVirtualTourData"
                        class="bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden"
                    >
                        <!-- Tab Navigation -->
                        <div class="border-b border-gray-200">
                            <nav class="flex -mb-px" aria-label="Media tabs">
                                <button
                                    @click="activeTab = 'gallery'"
                                    :class="[
                                        activeTab === 'gallery'
                                            ? 'border-blue-500 text-blue-600 bg-blue-50'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                        'flex-1 py-4 px-6 border-b-2 font-medium text-sm transition-all duration-200',
                                    ]"
                                >
                                    <div
                                        class="flex items-center justify-center gap-2"
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
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                        <span>Photo Gallery</span>
                                        <span
                                            class="ml-1 text-xs bg-gray-200 px-2 py-0.5 rounded-full"
                                            >{{ safeImages.length }}</span
                                        >
                                    </div>
                                </button>
                                <button
                                    @click="activeTab = 'panorama'"
                                    :class="[
                                        activeTab === 'panorama'
                                            ? 'border-purple-500 text-purple-600 bg-purple-50'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                        'flex-1 py-4 px-6 border-b-2 font-medium text-sm transition-all duration-200',
                                    ]"
                                >
                                    <div
                                        class="flex items-center justify-center gap-2"
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
                                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                                            />
                                        </svg>
                                        <span>Panoramic View</span>
                                        <span
                                            class="ml-1 px-2 py-0.5 bg-gradient-to-r from-purple-500 to-blue-500 text-white text-xs rounded-full"
                                            >NEW</span
                                        >
                                    </div>
                                </button>
                            </nav>
                        </div>

                        <!-- Gallery Tab Content -->
                        <div v-show="activeTab === 'gallery'">
                            <!-- Main Image Display -->
                            <div
                                class="relative aspect-video lg:aspect-[4/3] group"
                            >
                                <img
                                    :src="currentImage"
                                    :alt="property.title"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    :class="{
                                        'blur-sm': imageLoading,
                                        'grayscale opacity-80':
                                            property.status === 'sold',
                                    }"
                                    @load="imageLoading = false"
                                    @error="handleImageError"
                                />

                                <!-- Sold Badge Overlay -->
                                <div
                                    v-if="property.status === 'sold'"
                                    class="absolute top-4 left-4 z-10"
                                >
                                    <span
                                        class="px-3 py-1 rounded-full bg-red-600 text-white text-sm font-semibold shadow"
                                        >Sold</span
                                    >
                                </div>

                                <!-- Loading Overlay -->
                                <div
                                    v-if="imageLoading"
                                    class="absolute inset-0 flex items-center justify-center bg-gray-100"
                                >
                                    <div
                                        class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"
                                    ></div>
                                </div>

                                <!-- Navigation Arrows -->
                                <div
                                    v-if="
                                        property.images &&
                                        property.images.length > 1
                                    "
                                    class="absolute inset-y-0 left-0 right-0 flex items-center justify-between px-4 opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <button
                                        @click="previousImage"
                                        class="p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                                        aria-label="Previous image"
                                    >
                                        <svg
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 19l-7-7 7-7"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="nextImage"
                                        class="p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                                        aria-label="Next image"
                                    >
                                        <svg
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Image Counter -->
                                <div
                                    v-if="
                                        property.images &&
                                        property.images.length > 1
                                    "
                                    class="absolute bottom-4 right-4 bg-black/50 text-white px-3 py-1 rounded-full text-sm"
                                >
                                    {{ currentImageIndex + 1 }} /
                                    {{ property.images.length }}
                                </div>

                                <!-- Fullscreen Button -->
                                <button
                                    @click="openImageModal"
                                    class="absolute top-4 right-4 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                                    aria-label="View fullscreen gallery"
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
                                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Image Thumbnails -->
                            <div
                                v-if="
                                    property.images &&
                                    property.images.length > 1
                                "
                                class="p-4 lg:p-6"
                            >
                                <div class="flex gap-2 overflow-x-auto pb-2">
                                    <button
                                        v-for="(image, index) in safeImages"
                                        :key="index"
                                        @click="selectImage(index)"
                                        class="flex-shrink-0 relative group"
                                        :aria-label="`View image ${
                                            index + 1
                                        } of ${safeImages.length}`"
                                        :aria-pressed="
                                            currentImageIndex === index
                                        "
                                    >
                                        <div
                                            class="w-16 h-16 lg:w-20 lg:h-20 rounded-xl overflow-hidden border-2 transition-all duration-200"
                                            :class="
                                                currentImageIndex === index
                                                    ? 'border-blue-500 shadow-lg'
                                                    : 'border-gray-200 hover:border-gray-300'
                                            "
                                        >
                                            <img
                                                :src="getImageUrl(image)"
                                                :alt="`Image ${index + 1}`"
                                                class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-110"
                                                loading="lazy"
                                            />
                                        </div>
                                        <div
                                            v-if="currentImageIndex === index"
                                            class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-blue-500 rounded-full"
                                        ></div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Panorama Tab Content -->
                        <div v-if="activeTab === 'panorama'">
                            <div
                                class="bg-gradient-to-br from-purple-50 to-blue-50 p-4 lg:p-6 border-b border-gray-200"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl flex items-center justify-center"
                                        >
                                            <svg
                                                class="w-6 h-6 text-white"
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
                                        </div>
                                        <div>
                                            <h3
                                                class="text-lg font-bold text-gray-900"
                                            >
                                                Panoramic View
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                Drag to explore • Scroll to zoom
                                            </p>
                                        </div>
                                    </div>
                                    <div
                                        class="bg-white px-3 py-1.5 rounded-full text-sm font-semibold text-purple-700 shadow-sm"
                                    >
                                        🌟 Interactive
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 lg:p-6 bg-gray-50">
                                <VirtualTourViewer360
                                    v-if="virtualTourImages.length > 0"
                                    :imageUrl="virtualTourImages[0].url"
                                    :key="activeTab"
                                />
                                <div
                                    v-else
                                    class="text-center py-12 text-gray-500"
                                >
                                    <p>No panorama images available</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fallback: Gallery Only (No Panoramic View) -->
                    <div
                        v-else
                        class="bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden"
                    >
                        <!-- Main Image Display -->
                        <div
                            class="relative aspect-video lg:aspect-[4/3] group"
                        >
                            <img
                                :src="currentImage"
                                :alt="property.title"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                :class="{
                                    'blur-sm': imageLoading,
                                    'grayscale opacity-80':
                                        property.status === 'sold',
                                }"
                                @load="imageLoading = false"
                                @error="handleImageError"
                            />

                            <!-- Sold Badge Overlay -->
                            <div
                                v-if="property.status === 'sold'"
                                class="absolute top-4 left-4 z-10"
                            >
                                <span
                                    class="px-3 py-1 rounded-full bg-red-600 text-white text-sm font-semibold shadow"
                                    >Sold</span
                                >
                            </div>

                            <!-- Loading Overlay -->
                            <div
                                v-if="imageLoading"
                                class="absolute inset-0 flex items-center justify-center bg-gray-100"
                            >
                                <div
                                    class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"
                                ></div>
                            </div>

                            <!-- Image Navigation Arrows -->
                            <div
                                v-if="
                                    property.images &&
                                    property.images.length > 1
                                "
                                class="absolute inset-y-0 left-0 right-0 flex items-center justify-between p-4 opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                                <button
                                    @click="previousImage"
                                    class="p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                                    aria-label="Previous image"
                                >
                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 19l-7-7 7-7"
                                        />
                                    </svg>
                                </button>
                                <button
                                    @click="nextImage"
                                    class="p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                                    aria-label="Next image"
                                >
                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Image Counter -->
                            <div
                                v-if="
                                    property.images &&
                                    property.images.length > 1
                                "
                                class="absolute bottom-4 right-4 bg-black/50 text-white px-3 py-1 rounded-full text-sm"
                            >
                                {{ currentImageIndex + 1 }} /
                                {{ property.images.length }}
                            </div>

                            <!-- Fullscreen Button -->
                            <button
                                @click="openImageModal"
                                class="absolute top-4 right-4 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                                aria-label="View fullscreen gallery"
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
                                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Enhanced Image Thumbnails -->
                        <div
                            v-if="property.images && property.images.length > 1"
                            class="p-4 lg:p-6"
                        >
                            <div class="flex gap-2 overflow-x-auto pb-2">
                                <button
                                    v-for="(image, index) in safeImages"
                                    :key="index"
                                    @click="selectImage(index)"
                                    class="flex-shrink-0 relative group"
                                    :aria-label="`View image ${index + 1} of ${
                                        safeImages.length
                                    }`"
                                    :aria-pressed="currentImageIndex === index"
                                >
                                    <div
                                        class="w-16 h-16 lg:w-20 lg:h-20 rounded-xl overflow-hidden border-2 transition-all duration-200"
                                        :class="
                                            currentImageIndex === index
                                                ? 'border-blue-500 shadow-lg'
                                                : 'border-gray-200 hover:border-gray-300'
                                        "
                                    >
                                        <img
                                            :src="getImageUrl(image)"
                                            :alt="`Image ${index + 1}`"
                                            class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-110"
                                            loading="lazy"
                                        />
                                    </div>
                                    <div
                                        v-if="currentImageIndex === index"
                                        class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-blue-500 rounded-full"
                                    ></div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Information -->
                    <div
                        class="bg-white rounded-xl border border-neutral-200 shadow-sm p-6 mb-6"
                    >
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
                                    {{ formatAddress(property) }}
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
                                v-if="property.status === 'sold'"
                                class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium"
                            >
                                Sold
                            </span>
                            <span
                                v-else
                                class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium"
                            >
                                Available
                            </span>
                            <!-- Panoramic View Badge -->
                            <span
                                v-if="property.has_virtual_tour"
                                class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium flex items-center space-x-1"
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
                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                                    />
                                </svg>
                                <span>Panoramic View</span>
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

                        <!-- GIS Mapping Section -->
                        <div
                            v-if="
                                property.coordinates_lat &&
                                property.coordinates_lng
                            "
                            class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6"
                        >
                            <div class="p-4 lg:p-6">
                                <h3
                                    class="text-lg font-semibold text-gray-900 mb-3 flex items-center"
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
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        ></path>
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                        ></path>
                                    </svg>
                                    Property Location
                                </h3>

                                <!-- Interactive Map -->
                                <div class="mb-4">
                                    <div
                                        ref="publicMapContainer"
                                        class="w-full h-80 rounded-lg border border-gray-200 overflow-hidden shadow-inner"
                                    ></div>
                                </div>

                                <!-- Location Information & Actions -->
                                <div class="space-y-4">
                                    <!-- Location Description -->
                                    <div
                                        class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                                            >
                                                <svg
                                                    class="w-4 h-4 text-blue-600"
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
                                            </div>
                                            <div>
                                                <h4
                                                    class="font-semibold text-blue-900 mb-1"
                                                >
                                                    Easy Location Guide
                                                </h4>
                                                <p
                                                    class="text-sm text-blue-800 leading-relaxed"
                                                >
                                                    This property is located in
                                                    <strong>{{
                                                        property.municipality ||
                                                        "Bohol"
                                                    }}</strong
                                                    >. Use the map below or
                                                    click the buttons to get
                                                    directions from your current
                                                    location. Google Maps will
                                                    guide you step-by-step to
                                                    reach this property.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-3"
                                    >
                                        <a
                                            :href="`https://www.google.com/maps/dir/?api=1&destination=${property.coordinates_lat},${property.coordinates_lng}`"
                                            target="_blank"
                                            class="inline-flex items-center justify-center px-4 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium"
                                        >
                                            <svg
                                                class="w-5 h-5 mr-2"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7"
                                                ></path>
                                            </svg>
                                            Get Directions
                                        </a>
                                        <a
                                            :href="`https://www.google.com/maps?q=${property.coordinates_lat},${property.coordinates_lng}`"
                                            target="_blank"
                                            class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                                        >
                                            <svg
                                                class="w-5 h-5 mr-2"
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
                                            View on Map
                                        </a>
                                    </div>

                                    <!-- Helpful Tips -->
                                    <div
                                        class="p-3 bg-green-50 rounded-lg border border-green-200"
                                    >
                                        <div class="flex items-start gap-2">
                                            <svg
                                                class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
                                                ></path>
                                            </svg>
                                            <div class="text-sm text-green-800">
                                                <strong>Tip:</strong> Click "Get
                                                Directions" to open Google Maps
                                                with turn-by-turn directions
                                                from your current location. The
                                                app will guide you step-by-step
                                                to reach this property.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Legacy Map Link -->
                        <div v-else-if="property.google_maps_link" class="mb-6">
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
                    <!-- Primary CTA Card -->
                    <div
                        class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 mb-6 text-white"
                    >
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center"
                            >
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">Interested?</h3>
                                <p class="text-blue-100 text-sm">
                                    Get more information
                                </p>
                            </div>
                        </div>
                        <p class="text-blue-50 text-sm mb-4">
                            Contact our licensed broker for property details,
                            viewing schedules, and pricing options.
                        </p>
                        <a
                            v-if="
                                property.status === 'available' &&
                                !isUnderTransaction
                            "
                            href="#inquiry-form"
                            class="block w-full bg-white text-blue-600 text-center font-semibold py-3 px-4 rounded-lg hover:bg-blue-50 transition-colors shadow-md"
                        >
                            Send Inquiry Now
                        </a>
                        <div
                            v-else
                            class="block w-full bg-gray-100 text-gray-700 text-center font-semibold py-3 px-4 rounded-lg"
                        >
                            <span v-if="property.status === 'sold'"
                                >This property has been sold</span
                            >
                            <span v-else-if="property.status === 'reserved'"
                                >This property is currently reserved</span
                            >
                            <span
                                v-else-if="
                                    property.status === 'under_negotiation' ||
                                    isUnderTransaction
                                "
                                >This property is currently under
                                transaction</span
                            >
                            <span v-else
                                >This property is not available for
                                inquiries</span
                            >
                        </div>
                        <div
                            class="flex items-center gap-2 mt-4 text-blue-100 text-xs"
                        >
                            <svg
                                class="w-4 h-4"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span>Verified listing • Fast response</span>
                        </div>
                    </div>

                    <!-- Broker Information -->
                    <div
                        class="bg-white rounded-xl border border-neutral-200 shadow-sm p-6 mb-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Listed by
                        </h3>
                        <div class="flex items-center mb-4">
                            <UserAvatar
                                v-if="property.broker"
                                :user="property.broker"
                                size="lg"
                                bg-color="blue"
                            />
                            <div
                                v-else
                                class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-lg"
                            >
                                G
                            </div>
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">
                                    {{
                                        property.broker?.name || "GeoCasa Bohol"
                                    }}
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
                                {{
                                    property.broker?.email ||
                                    "info@geocasabohol.com"
                                }}
                            </div>
                        </div>
                    </div>

                    <!-- Inquiry Form -->
                    <div
                        v-if="
                            property.status === 'available' &&
                            !isUnderTransaction
                        "
                        id="inquiry-form"
                        class="bg-white rounded-xl border border-neutral-200 shadow-sm p-6 scroll-mt-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Interested in this property?
                        </h3>

                        <!-- Inquiry Success Modal -->
                        <div
                            v-if="showAuthPrompt"
                            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
                        >
                            <div
                                class="bg-white rounded-xl border border-neutral-200 shadow-sm p-8 max-w-md w-full relative"
                            >
                                <button
                                    @click="closeAuthPrompt"
                                    class="absolute top-2 right-2 text-gray-400 hover:text-gray-700"
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
                                            d="M6 18L18 6M6 6l12 12"
                                        ></path>
                                    </svg>
                                </button>
                                <h3
                                    class="text-xl font-semibold mb-2 text-center text-green-700"
                                >
                                    Inquiry Sent!
                                </h3>
                                <p class="mb-4 text-center text-gray-700">
                                    Your inquiry has been sent successfully. To
                                    track your inquiry and get updates, please
                                    log in or register.
                                </p>
                                <div class="flex flex-col gap-3">
                                    <button
                                        @click="redirectToLogin"
                                        class="w-full py-2 px-4 bg-blue-600 text-white rounded hover:bg-blue-700 font-semibold"
                                    >
                                        Login
                                    </button>
                                    <button
                                        @click="redirectToRegister"
                                        class="w-full py-2 px-4 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 font-semibold"
                                    >
                                        Register
                                    </button>
                                </div>
                            </div>
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
                    <div
                        v-else
                        class="bg-white rounded-xl border border-neutral-200 shadow-sm p-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            {{
                                property.status === "sold"
                                    ? "Property Sold"
                                    : property.status === "reserved"
                                    ? "Property Reserved"
                                    : isUnderTransaction
                                    ? "Under Transaction"
                                    : "Not Available"
                            }}
                        </h3>
                        <p class="text-gray-700 mb-4">
                            <span v-if="property.status === 'sold'"
                                >This property has been sold. Inquiries are
                                disabled for sold properties.</span
                            >
                            <span v-else-if="property.status === 'reserved'"
                                >This property is temporarily reserved and not
                                accepting new inquiries.</span
                            >
                            <span
                                v-else-if="
                                    property.status === 'under_negotiation' ||
                                    isUnderTransaction
                                "
                                >This property is currently in an active
                                transaction and not accepting new
                                inquiries.</span
                            >
                            <span v-else
                                >This property is not currently accepting
                                inquiries.</span
                            >
                        </p>
                        <Link
                            :href="route('public.properties')"
                            class="inline-block bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700"
                        >
                            Browse Other Properties
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authentication Required Notification Modal -->
        <div
            v-if="showAuthNotification"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click="closeAuthNotification"
        >
            <div
                class="bg-white rounded-xl border border-neutral-200 shadow-sm max-w-md w-full mx-4 p-6"
                @click.stop
            >
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg
                                class="w-8 h-8 text-blue-600"
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
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-gray-900">
                            Authentication Required
                        </h3>
                    </div>
                    <button
                        @click="closeAuthNotification"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            ></path>
                        </svg>
                    </button>
                </div>

                <div class="mb-6">
                    <p class="text-gray-600 mb-4">
                        To submit an inquiry for this property, you need to be
                        logged in to your account. This helps us connect you
                        directly with the property broker and manage your
                        inquiries effectively.
                    </p>
                    <div
                        class="bg-blue-50 border border-blue-200 rounded-lg p-4"
                    >
                        <div class="flex items-start">
                            <svg
                                class="w-5 h-5 text-blue-600 mt-0.5 mr-2"
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
                            <div class="text-sm text-blue-800">
                                <strong
                                    >Benefits of creating an account:</strong
                                >
                                <ul
                                    class="mt-1 list-disc list-inside space-y-1"
                                >
                                    <li>Track all your property inquiries</li>
                                    <li>Save favorite properties</li>
                                    <li>
                                        Get personalized property
                                        recommendations
                                    </li>
                                    <li>Direct communication with brokers</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button
                        @click="redirectToLogin"
                        class="flex-1 bg-blue-600 text-white text-center py-3 px-4 rounded-lg hover:bg-blue-700 font-medium transition-colors"
                    >
                        Login to Your Account
                    </button>
                    <button
                        @click="redirectToRegister"
                        class="flex-1 bg-white text-blue-600 border-2 border-blue-600 text-center py-3 px-4 rounded-lg hover:bg-blue-50 font-medium transition-colors"
                    >
                        Create New Account
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <button
                        @click="closeAuthNotification"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors"
                    >
                        Maybe later
                    </button>
                </div>
            </div>
        </div>

        <!-- Enhanced Image Modal -->
        <div
            v-if="showImageModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/90"
            @click="closeImageModal"
        >
            <div class="relative max-w-7xl max-h-full p-4" @click.stop>
                <img
                    :src="currentImage"
                    :alt="property.title"
                    class="max-w-full max-h-full object-contain rounded-lg"
                />
                <button
                    @click="closeImageModal"
                    class="absolute top-2 right-2 p-2 text-white hover:bg-white/20 rounded-full transition-colors"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <!-- Modal Navigation -->
                <div
                    v-if="property.images && property.images.length > 1"
                    class="absolute inset-y-0 left-0 right-0 flex items-center justify-between p-4"
                >
                    <button
                        @click="previousImage"
                        class="p-3 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </button>
                    <button
                        @click="nextImage"
                        class="p-3 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Sticky CTA Bar -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-full opacity-0"
        >
            <div
                v-if="showMobileCTA && property.status !== 'sold'"
                class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 shadow-2xl"
            >
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div
                                class="text-sm font-semibold text-gray-900 truncate"
                            >
                                {{ property.title }}
                            </div>
                            <div class="text-lg font-bold text-blue-600">
                                {{ property.formatted_total_price }}
                            </div>
                        </div>
                        <a
                            href="#inquiry-form"
                            class="flex-shrink-0 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all active:scale-95"
                            @click="showMobileCTA = false"
                        >
                            Inquire Now
                        </a>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Footer -->
        <PublicFooter />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import PublicFooter from "@/Components/PublicFooter.vue";
import VirtualTourViewer from "@/Components/VirtualTourViewer.vue";
import VirtualTourViewer360 from "@/Components/VirtualTourViewer360.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

// Fix for default markers in Leaflet
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl:
        "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png",
    iconUrl:
        "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png",
    shadowUrl:
        "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png",
});

const props = defineProps({
    property: Object,
    errors: Object,
});
// Determine if property is under transaction (backend-provided flag preferred)
const isUnderTransaction = computed(() => {
    const p = props.property || {};
    if (typeof p.is_under_transaction !== "undefined") {
        return Boolean(p.is_under_transaction);
    }
    const count = Number(p.active_transactions_count || 0);
    return p.status === "under_negotiation" || count > 0;
});

// Get page props for authentication state
const page = usePage();
const isAuthenticated = computed(() => page.props.auth?.user);

// Authentication notification state
const showAuthNotification = ref(false);

// Initialize the inquiry form
const inquiryForm = useForm({
    name: "",
    email: "",
    phone: "",
    message: `I'm interested in learning more about this property. Please provide additional details.`,
});

// Cache for last submitted inquiry data
let lastInquiryData = null;

// Add processing state for the inquiry form
const processing = computed(() => inquiryForm.processing);

// Enhanced image gallery state
const currentImageIndex = ref(0);
const imageLoading = ref(false);
const showImageModal = ref(false);
const activeTab = ref("gallery"); // Tab state for gallery vs panorama

// Mobile sticky CTA state
const showMobileCTA = ref(false);
const lastScrollY = ref(0);
const scrollThreshold = 300; // Show CTA after scrolling 300px

// Safe images computed property to handle arrays
const safeImages = computed(() => {
    if (!props.property.images || !Array.isArray(props.property.images)) {
        return [];
    }

    return props.property.images
        .map((image) => {
            // If image is an array, take the first valid string
            if (Array.isArray(image)) {
                const firstValid = image.find(
                    (img) => img && typeof img === "string"
                );
                return firstValid || "";
            }
            return image || "";
        })
        .filter((image) => image.trim() !== "");
});

// Computed properties
// Prioritize the selected gallery image; fall back to main_image when gallery is empty
const currentImage = computed(() => {
    if (safeImages.value && safeImages.value.length > 0) {
        const imageAtIndex =
            safeImages.value[currentImageIndex.value] ?? safeImages.value[0];
        return getImageUrl(imageAtIndex);
    }
    // If no gallery images, use main_image (may be placeholder or valid URL)
    return getImageUrl(props.property.main_image);
});

// Panoramic View computed properties (robust against strings/objects)
const parsedVirtualTourArray = computed(() => {
    const value = props.property?.virtual_tour_images;
    if (!value) return [];

    let images = value;

    // If it's a JSON string, try to parse
    if (typeof images === "string") {
        try {
            const parsed = JSON.parse(images);
            images = parsed;
        } catch (e) {
            // Not JSON, treat as single image string
            return [images];
        }
    }

    // If it's an object (not array), convert to array of values
    if (!Array.isArray(images) && typeof images === "object") {
        images = Object.values(images);
    }

    // If it's now an array, flatten any nesting and normalize to strings
    if (Array.isArray(images)) {
        const flat = images.flat(2);
        // Items can be strings or objects with various keys
        return flat
            .map((item) => {
                if (!item) return null;
                if (typeof item === "string") return item;
                if (typeof item === "object") {
                    return (
                        item.url ||
                        item.path ||
                        item.src ||
                        item.image ||
                        item.filename ||
                        null
                    );
                }
                return null;
            })
            .filter((s) => typeof s === "string" && s.trim() !== "");
    }

    // Fallback: if it's a single string at this point
    return typeof images === "string" ? [images] : [];
});

const hasVirtualTourData = computed(
    () => parsedVirtualTourArray.value.length > 0
);

const virtualTourImages = computed(() => {
    const images = parsedVirtualTourArray.value;
    if (!Array.isArray(images) || images.length === 0) return [];

    return images.map((image, index) => ({
        url: getImageUrl(image, true), // Pass true for panoramic images
        thumbnail: getImageUrl(image, true),
        title: `View ${index + 1}`,
        description: `Panoramic view of ${props.property.title}`,
    }));
});

const virtualTourHotspots = computed(() => {
    if (!props.property.tour_hotspots) return [];

    try {
        const hotspots =
            typeof props.property.tour_hotspots === "string"
                ? JSON.parse(props.property.tour_hotspots)
                : props.property.tour_hotspots;

        return Array.isArray(hotspots) ? hotspots : [];
    } catch (error) {
        console.warn("Failed to parse tour hotspots:", error);
        return [];
    }
});

// Auth prompt modal state (must be top-level so template can access)
const showAuthPrompt = ref(false);
const closeAuthPrompt = () => {
    showAuthPrompt.value = false;
};

const submitInquiry = () => {
    inquiryForm.post(route("public.inquiries.store", props.property.slug), {
        preserveScroll: true,
        onSuccess: (response) => {
            // Cache the inquiry data before resetting
            lastInquiryData = {
                name: inquiryForm.name,
                email: inquiryForm.email,
                phone: inquiryForm.phone,
                message: inquiryForm.message,
                property_id: props.property.id,
                property_title: props.property.title,
            };

            inquiryForm.reset("name", "email", "phone");
            inquiryForm.message = `I'm interested in ${props.property.title}. Please provide more information about this property.`;

            // Check if user is authenticated
            if (isAuthenticated.value) {
                // For authenticated users, show success toast
                showSuccessToast(
                    "Your inquiry has been sent successfully! The broker will contact you soon."
                );
            } else {
                // For non-authenticated users, show the auth prompt modal
                setTimeout(() => {
                    showAuthPrompt.value = true;
                }, 0);
            }
        },
        onError: (errors) => {
            // Show error toast
            const firstError = Object.values(errors)[0];
            showErrorToast(
                firstError || "Failed to send inquiry. Please try again."
            );
        },
    });
};

// Success toast notification
const showSuccessToast = (message) => {
    const toast = document.createElement("div");
    toast.className =
        "fixed top-4 right-4 z-50 max-w-sm p-4 rounded-lg shadow-lg transform transition-all duration-300 bg-green-500 text-white";
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <div class="flex-1 text-sm font-medium">${message}</div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
};

// Error toast notification
const showErrorToast = (message) => {
    const toast = document.createElement("div");
    toast.className =
        "fixed top-4 right-4 z-50 max-w-sm p-4 rounded-lg shadow-lg transform transition-all duration-300 bg-red-500 text-white";
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <div class="flex-1 text-sm font-medium">${message}</div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
};

// Store inquiry data and redirect to login
const redirectToLogin = () => {
    // Use last inquiry data if available, else fall back to form
    const inquiryData = lastInquiryData || {
        name: inquiryForm.name,
        email: inquiryForm.email,
        phone: inquiryForm.phone,
        message: inquiryForm.message,
        property_id: props.property.id,
        property_title: props.property.title,
    };
    inquiryForm
        .transform((data) => inquiryData)
        .post(route("public.store-inquiry-session"), {
            onSuccess: () => {
                window.location.href = route("login");
            },
            onError: () => {
                window.location.href = route("login");
            },
        });
};

// Store inquiry data and redirect to register
const redirectToRegister = () => {
    // Use last inquiry data if available, else fall back to form
    const inquiryData = lastInquiryData || {
        name: inquiryForm.name,
        email: inquiryForm.email,
        phone: inquiryForm.phone,
        message: inquiryForm.message,
        property_id: props.property.id,
        property_title: props.property.title,
    };
    inquiryForm
        .transform((data) => inquiryData)
        .post(route("public.store-inquiry-session"), {
            onSuccess: () => {
                window.location.href = route("register");
            },
            onError: () => {
                window.location.href = route("register");
            },
        });
};

// Close authentication notification
const closeAuthNotification = () => {
    showAuthNotification.value = false;
};

// Enhanced getImageUrl function with proper path detection
const getImageUrl = (image, isVirtualTour = false) => {
    // Handle null, undefined, or empty values
    if (!image) {
        return "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
    }

    // Handle arrays - flatten and find first valid string
    if (Array.isArray(image)) {
        const flatArray = image.flat(2); // Flatten up to 2 levels deep
        const firstValidImage = flatArray.find(
            (img) => img && typeof img === "string" && img.trim() !== ""
        );

        if (!firstValidImage) {
            return "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
        }

        return getImageUrl(firstValidImage, isVirtualTour); // Recursive call with string
    }

    // Ensure we have a string
    if (typeof image !== "string") {
        return "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
    }

    // Clean the image string
    let cleanImage = image.trim();

    if (!cleanImage) {
        return "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
    }

    // If it's already a data URL or blob URL, return as-is
    if (cleanImage.startsWith("data:") || cleanImage.startsWith("blob:")) {
        return cleanImage;
    }

    // If already a full URL, return as-is
    if (cleanImage.startsWith("http://") || cleanImage.startsWith("https://")) {
        return cleanImage;
    }

    // If already starts with /storage/, return as-is
    if (cleanImage.startsWith("/storage/")) {
        return cleanImage;
    }

    // For any other case, prepend /storage/ (legacy support)
    cleanImage = cleanImage.replace(/^\/+/, "");
    return `/storage/${cleanImage}`;
};

const formatPropertyType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

// Format and deduplicate address tokens to avoid repeats and empty commas
const formatAddress = (property) => {
    const buildTokensFromFields = () => {
        const country = property.country || "Philippines";
        return [
            property.barangay && String(property.barangay).trim(),
            property.municipality && String(property.municipality).trim(),
            property.province && String(property.province).trim(),
            country && String(country).trim(),
        ].filter(Boolean);
    };

    let tokens = [];
    if (property.full_address && typeof property.full_address === "string") {
        tokens = property.full_address
            .split(",")
            .map((s) => s.trim())
            .filter((s) => s && s !== "-");
        // If parsing results in too few tokens, fall back to fields
        if (tokens.length < 2) {
            tokens = buildTokensFromFields();
        }
    } else {
        tokens = buildTokensFromFields();
    }

    const seen = new Set();
    const result = [];
    for (const t of tokens) {
        const key = t.toLowerCase();
        if (!seen.has(key)) {
            seen.add(key);
            result.push(t);
        }
    }
    return result.join(", ");
};

// Enhanced image gallery methods
const selectImage = (index) => {
    if (index >= 0 && index < safeImages.value.length) {
        imageLoading.value = true;
        currentImageIndex.value = index;
    }
};

const nextImage = () => {
    if (safeImages.value && safeImages.value.length > 1) {
        const nextIndex =
            (currentImageIndex.value + 1) % safeImages.value.length;
        selectImage(nextIndex);
    }
};

const previousImage = () => {
    if (safeImages.value && safeImages.value.length > 1) {
        const prevIndex =
            currentImageIndex.value === 0
                ? safeImages.value.length - 1
                : currentImageIndex.value - 1;
        selectImage(prevIndex);
    }
};

const openImageModal = () => {
    showImageModal.value = true;
    document.body.style.overflow = "hidden";
};

const closeImageModal = () => {
    showImageModal.value = false;
    document.body.style.overflow = "auto";
};

const handleImageError = () => {
    imageLoading.value = false;
    console.warn("Failed to load image:", currentImage.value);
};

// Keyboard navigation
const handleKeydown = (event) => {
    // Don't interfere with form inputs
    if (
        event.target.tagName === "INPUT" ||
        event.target.tagName === "TEXTAREA"
    ) {
        return;
    }

    if (showImageModal.value) {
        switch (event.key) {
            case "Escape":
                closeImageModal();
                event.preventDefault();
                break;
            case "ArrowLeft":
                previousImage();
                event.preventDefault();
                break;
            case "ArrowRight":
                nextImage();
                event.preventDefault();
                break;
        }
    } else {
        // Gallery navigation even when modal is closed
        switch (event.key) {
            case "ArrowLeft":
                if (safeImages.value.length > 1) {
                    previousImage();
                    event.preventDefault();
                }
                break;
            case "ArrowRight":
                if (safeImages.value.length > 1) {
                    nextImage();
                    event.preventDefault();
                }
                break;
        }
    }
};

// Map reference
const publicMapContainer = ref(null);
const publicMap = ref(null);

// Initialize public map
const initPublicMap = () => {
    if (
        props.property.coordinates_lat &&
        props.property.coordinates_lng &&
        publicMapContainer.value
    ) {
        const lat = parseFloat(props.property.coordinates_lat);
        const lng = parseFloat(props.property.coordinates_lng);

        publicMap.value = L.map(publicMapContainer.value).setView(
            [lat, lng],
            15
        );

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors",
        }).addTo(publicMap.value);

        L.marker([lat, lng])
            .addTo(publicMap.value)
            .bindPopup(
                `<b>${props.property.title}</b><br>${formatAddress(
                    props.property
                )}`
            )
            .openPopup();
    }
};

// Mobile CTA scroll handler
const handleMobileCTAScroll = () => {
    const currentScrollY = window.scrollY;

    // Show CTA after scrolling past threshold
    if (currentScrollY > scrollThreshold) {
        // Hide when scrolling down, show when scrolling up
        if (currentScrollY > lastScrollY.value) {
            showMobileCTA.value = false;
        } else {
            showMobileCTA.value = true;
        }
    } else {
        showMobileCTA.value = false;
    }

    lastScrollY.value = currentScrollY;
};

// Lifecycle hooks
onMounted(() => {
    document.addEventListener("keydown", handleKeydown);
    window.addEventListener("scroll", handleMobileCTAScroll, { passive: true });

    // Initialize map after component is mounted
    initPublicMap();
});

onUnmounted(() => {
    document.removeEventListener("keydown", handleKeydown);
    window.removeEventListener("scroll", handleMobileCTAScroll);
    document.body.style.overflow = "auto";

    // Clean up map instance
    if (publicMap.value) {
        publicMap.value.remove();
    }
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
