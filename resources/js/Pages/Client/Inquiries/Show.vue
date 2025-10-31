<template>
    <Head :title="`Inquiry - ${inquiry.property?.title || 'Property'}`" />

    <ModernDashboardLayout>
        <!-- Completed/Closed Banner (Client) -->
        <div
            v-if="['completed', 'closed'].includes(inquiry.status)"
            class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-6 mb-6 shadow-sm"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="bg-green-100 rounded-full p-2">
                            <svg
                                class="w-6 h-6 text-green-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-green-900 font-bold text-lg">
                                This inquiry is
                                {{
                                    inquiry.status === "completed"
                                        ? "Completed"
                                        : "Closed"
                                }}
                                <span
                                    v-if="inquiry.completion_outcome"
                                    class="ml-2 px-3 py-1 rounded-full text-xs bg-green-100 text-green-800 uppercase font-semibold"
                                    >{{ inquiry.completion_outcome }}</span
                                >
                            </p>
                            <p class="text-green-700 text-sm mt-1">
                                {{
                                    inquiry.status === "completed"
                                        ? "Thank you! Your inquiry has been successfully completed."
                                        : "This inquiry is no longer active."
                                }}
                            </p>
                        </div>
                    </div>
                    <p
                        v-if="inquiry.completion_reason"
                        class="text-green-800 text-sm mt-3 ml-14 bg-white/50 rounded-lg p-3"
                    >
                        <strong>Note:</strong> {{ inquiry.completion_reason }}
                    </p>
                </div>
                <div class="flex flex-col gap-2">
                    <Link
                        v-if="inquiry.conversation"
                        :href="
                            route('conversations.show', inquiry.conversation.id)
                        "
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 shadow-sm transition-all hover:shadow-md"
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
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                            ></path>
                        </svg>
                        View Messages
                    </Link>
                    <Link
                        v-if="inquiry.transaction"
                        :href="
                            route('transactions.show', inquiry.transaction.id)
                        "
                        class="bg-white hover:bg-gray-50 text-green-700 border-2 border-green-200 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all"
                        >View Transaction</Link
                    >
                </div>
            </div>
        </div>

        <!-- Status Help Banner - Show contextual help based on status -->
        <div
            v-if="!['completed', 'closed'].includes(inquiry.status)"
            class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg"
        >
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg
                        class="h-5 w-5 text-blue-400 mt-0.5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd"
                        ></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm text-blue-800 font-medium">
                        {{ getStatusHelp(inquiry.status).title }}
                    </p>
                    <p class="text-sm text-blue-700 mt-1">
                        {{ getStatusHelp(inquiry.status).description }}
                    </p>
                </div>
            </div>
        </div>
        <!-- Enhanced Header Section -->
        <div
            class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl p-8 text-white mb-6 shadow-lg"
        >
            <div class="flex justify-between items-start gap-6">
                <div class="flex-1">
                    <Link
                        :href="route('client.inquiries.index')"
                        class="inline-flex items-center text-blue-100 hover:text-white text-sm font-medium mb-4 transition-colors group"
                    >
                        <svg
                            class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            ></path>
                        </svg>
                        Back to My Inquiries
                    </Link>
                    <h1 class="text-4xl font-bold mb-2">
                        Inquiry #{{ inquiry.id }}
                    </h1>
                    <p class="text-blue-100 text-lg">
                        {{ inquiry.property?.title || "Property Inquiry" }}
                    </p>
                    <div class="flex items-center gap-2 mt-3">
                        <svg
                            class="w-4 h-4 text-blue-200"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            ></path>
                        </svg>
                        <span class="text-blue-100 text-sm"
                            >Submitted
                            {{ formatDate(inquiry.created_at) }}</span
                        >
                    </div>
                </div>
                <div class="flex flex-col items-end gap-3">
                    <span
                        class="inline-flex items-center px-5 py-2.5 rounded-full text-sm font-bold bg-white shadow-md"
                        :class="{
                            'text-yellow-700': inquiry.status === 'new',
                            'text-blue-700': inquiry.status === 'contacted',
                            'text-purple-700': inquiry.status === 'scheduled',
                            'text-green-700': inquiry.status === 'completed',
                            'text-gray-700': inquiry.status === 'closed',
                        }"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <circle cx="10" cy="10" r="4"></circle>
                        </svg>
                        {{ getStatusLabel(inquiry.status) }}
                    </span>
                    <!-- Primary CTA - Message Broker -->
                    <Link
                        v-if="
                            inquiry.conversation &&
                            !['completed', 'closed'].includes(inquiry.status)
                        "
                        :href="
                            route('conversations.show', inquiry.conversation.id)
                        "
                        class="bg-white hover:bg-blue-50 text-blue-700 px-6 py-3 rounded-lg text-sm font-bold flex items-center gap-2 shadow-md transition-all hover:shadow-lg transform hover:-translate-y-0.5"
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
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                            ></path>
                        </svg>
                        Message Broker
                    </Link>
                </div>
            </div>
        </div>

        <div class="py-6">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Property Information -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl">
                    <div
                        class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-100 rounded-full p-2">
                                    <svg
                                        class="w-5 h-5 text-blue-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                        ></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">
                                    Property Information
                                </h3>
                            </div>
                            <span class="text-sm text-gray-500 font-medium">
                                Property ID: #{{ inquiry.property?.id }}
                            </span>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Property Images Gallery -->
                        <div v-if="propertyImages.length > 0" class="mb-8">
                            <div class="relative rounded-xl overflow-hidden bg-gray-100">
                                <!-- Main Image -->
                                <div class="aspect-video w-full">
                                    <img 
                                        :src="currentImage.image_path" 
                                        :alt="inquiry.property.title"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                
                                <!-- Image Counter -->
                                <div class="absolute top-4 right-4 bg-black/70 text-white px-3 py-1.5 rounded-full text-sm font-medium">
                                    {{ currentImageIndex + 1 }} / {{ propertyImages.length }}
                                </div>
                                
                                <!-- Navigation Arrows (if more than 1 image) -->
                                <div v-if="propertyImages.length > 1" class="absolute inset-0 flex items-center justify-between p-4">
                                    <button 
                                        @click="previousImage"
                                        class="bg-white/90 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition-all hover:scale-110"
                                        aria-label="Previous image"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </button>
                                    <button 
                                        @click="nextImage"
                                        class="bg-white/90 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg transition-all hover:scale-110"
                                        aria-label="Next image"
                                    >
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Thumbnail Gallery -->
                            <div v-if="propertyImages.length > 1" class="mt-4 grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2">
                                <button
                                    v-for="(image, index) in propertyImages"
                                    :key="image.id"
                                    @click="currentImageIndex = index"
                                    class="aspect-square rounded-lg overflow-hidden border-2 transition-all"
                                    :class="currentImageIndex === index ? 'border-blue-600 ring-2 ring-blue-200' : 'border-gray-200 hover:border-blue-400'"
                                >
                                    <img 
                                        :src="image.image_path" 
                                        :alt="`Property image ${index + 1}`"
                                        class="w-full h-full object-cover"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- No Images Placeholder -->
                        <div v-else class="mb-8 aspect-video bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center">
                            <div class="text-center text-gray-400">
                                <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm font-medium">No images available</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="space-y-5">
                                <div>
                                    <h4
                                        class="font-bold text-gray-900 text-2xl mb-4"
                                    >
                                        {{
                                            inquiry.property?.title ||
                                            "Property Title"
                                        }}
                                    </h4>
                                    <div class="space-y-3">
                                        <div
                                            class="flex items-start text-gray-700 group hover:text-blue-600 transition-colors"
                                        >
                                            <div
                                                class="bg-gray-100 group-hover:bg-blue-50 rounded-lg p-2 mr-3 transition-colors"
                                            >
                                                <svg
                                                    class="w-5 h-5 text-gray-500 group-hover:text-blue-500"
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
                                            </div>
                                            <div>
                                                <p
                                                    class="text-xs text-gray-500 font-medium"
                                                >
                                                    Location
                                                </p>
                                                <p class="font-medium">
                                                    {{
                                                        inquiry.property
                                                            ?.location ||
                                                        "Location not specified"
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-start text-gray-700 group hover:text-blue-600 transition-colors"
                                        >
                                            <div
                                                class="bg-gray-100 group-hover:bg-blue-50 rounded-lg p-2 mr-3 transition-colors"
                                            >
                                                <svg
                                                    class="w-5 h-5 text-gray-500 group-hover:text-blue-500"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                                    ></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-xs text-gray-500 font-medium"
                                                >
                                                    Property Type
                                                </p>
                                                <p class="font-medium">
                                                    {{
                                                        inquiry.property
                                                            ?.type ||
                                                        "Not specified"
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-start text-gray-700 group hover:text-blue-600 transition-colors"
                                        >
                                            <div
                                                class="bg-gray-100 group-hover:bg-blue-50 rounded-lg p-2 mr-3 transition-colors"
                                            >
                                                <svg
                                                    class="w-5 h-5 text-gray-500 group-hover:text-blue-500"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                    ></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p
                                                    class="text-xs text-gray-500 font-medium"
                                                >
                                                    Price
                                                </p>
                                                <p
                                                    class="font-bold text-xl text-blue-600"
                                                >
                                                    <span
                                                        v-if="
                                                            inquiry.property
                                                                ?.price
                                                        "
                                                        >₱{{
                                                            formatPrice(
                                                                inquiry.property
                                                                    .price
                                                            )
                                                        }}</span
                                                    >
                                                    <span
                                                        v-else
                                                        class="text-gray-500 text-base"
                                                        >Contact for price</span
                                                    >
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="inquiry.property?.user"
                                class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border-2 border-blue-100 shadow-sm"
                            >
                                <h4
                                    class="font-bold text-gray-900 mb-4 flex items-center"
                                >
                                    <div
                                        class="bg-blue-100 rounded-full p-2 mr-2"
                                    >
                                        <svg
                                            class="w-5 h-5 text-blue-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            ></path>
                                        </svg>
                                    </div>
                                    Your Broker
                                </h4>
                                <div class="space-y-3">
                                    <p class="font-bold text-gray-900 text-lg">
                                        {{ inquiry.property.user.name }}
                                    </p>
                                    <div class="space-y-2">
                                        <a
                                            :href="`mailto:${inquiry.property.user.email}`"
                                            class="text-gray-700 flex items-center hover:text-blue-600 transition-colors group"
                                        >
                                            <div
                                                class="bg-white group-hover:bg-blue-50 rounded-lg p-2 mr-2 transition-colors"
                                            >
                                                <svg
                                                    class="w-4 h-4 text-gray-500 group-hover:text-blue-500"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                                    ></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm">{{
                                                inquiry.property.user.email
                                            }}</span>
                                        </a>
                                        <a
                                            v-if="inquiry.property.user.phone"
                                            :href="`tel:${inquiry.property.user.phone}`"
                                            class="text-gray-700 flex items-center hover:text-blue-600 transition-colors group"
                                        >
                                            <div
                                                class="bg-white group-hover:bg-blue-50 rounded-lg p-2 mr-2 transition-colors"
                                            >
                                                <svg
                                                    class="w-4 h-4 text-gray-500 group-hover:text-blue-500"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                                    ></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm">{{
                                                inquiry.property.user.phone
                                            }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-6 pt-6 border-t border-gray-200"
                            v-if="inquiry.property"
                        >
                            <Link
                                :href="
                                    route(
                                        'client.properties.show',
                                        inquiry.property.id
                                    )
                                "
                                class="inline-flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
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
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                    ></path>
                                </svg>
                                View Full Property Details
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Inquiry Details -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                Your Inquiry
                            </h3>
                            <div
                                class="flex items-center text-sm text-gray-500"
                            >
                                <svg
                                    class="w-4 h-4 mr-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.959 8.959 0 01-4.906-1.436L3 21l2.436-5.094A8.959 8.959 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"
                                    ></path>
                                </svg>
                                Inquiry #{{ inquiry.id }}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2">
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <h4
                                        class="font-semibold text-gray-900 mb-3 flex items-center"
                                    >
                                        <svg
                                            class="w-5 h-5 mr-2 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.959 8.959 0 01-4.906-1.436L3 21l2.436-5.094A8.959 8.959 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"
                                            ></path>
                                        </svg>
                                        Your Message
                                    </h4>
                                    <div
                                        class="bg-white rounded-lg p-4 border border-gray-200"
                                    >
                                        <p
                                            class="text-gray-900 leading-relaxed whitespace-pre-wrap"
                                        >
                                            {{ inquiry.message }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <h4
                                        class="font-semibold text-gray-900 mb-4 flex items-center"
                                    >
                                        <svg
                                            class="w-5 h-5 mr-2 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                        Status
                                    </h4>
                                    <span
                                        class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800 border border-yellow-200':
                                                inquiry.status === 'new',
                                            'bg-blue-100 text-blue-800 border border-blue-200':
                                                inquiry.status === 'contacted',
                                            'bg-purple-100 text-purple-800 border border-purple-200':
                                                inquiry.status === 'scheduled',
                                            'bg-green-100 text-green-800 border border-green-200':
                                                inquiry.status === 'completed',
                                            'bg-gray-100 text-gray-800 border border-gray-200':
                                                inquiry.status === 'closed',
                                        }"
                                    >
                                        <svg
                                            class="w-4 h-4 mr-2"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <circle
                                                cx="10"
                                                cy="10"
                                                r="3"
                                            ></circle>
                                        </svg>
                                        {{
                                            inquiry.status
                                                .charAt(0)
                                                .toUpperCase() +
                                            inquiry.status.slice(1)
                                        }}
                                    </span>
                                </div>

                                <div class="bg-gray-50 rounded-lg p-6">
                                    <h4
                                        class="font-semibold text-gray-900 mb-4 flex items-center"
                                    >
                                        <svg
                                            class="w-5 h-5 mr-2 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                        Timeline
                                    </h4>
                                    <div class="text-gray-600">
                                        <p class="text-sm">Submitted on</p>
                                        <p class="font-medium text-gray-900">
                                            {{ formatDate(inquiry.created_at) }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="inquiry.budget_range"
                                    class="bg-gray-50 rounded-lg p-6"
                                >
                                    <h4
                                        class="font-semibold text-gray-900 mb-4 flex items-center"
                                    >
                                        <svg
                                            class="w-5 h-5 mr-2 text-gray-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                        Budget Range
                                    </h4>
                                    <p class="text-gray-900">
                                        {{ inquiry.budget_range }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Broker Response -->
                <div
                    v-if="inquiry.response"
                    class="bg-white overflow-hidden shadow-xl sm:rounded-xl"
                >
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div
                                class="bg-blue-100 rounded-full p-3 flex-shrink-0"
                            >
                                <svg
                                    class="w-6 h-6 text-blue-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    ></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div
                                    class="flex items-center justify-between mb-3"
                                >
                                    <h3 class="text-xl font-bold text-gray-900">
                                        Broker's Response
                                    </h3>
                                    <span class="text-sm text-gray-500">
                                        <svg
                                            class="w-4 h-4 inline mr-1"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                        {{ formatDate(inquiry.updated_at) }}
                                    </span>
                                </div>
                                <div
                                    class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-5 border-l-4 border-blue-500"
                                >
                                    <p
                                        class="text-gray-900 leading-relaxed whitespace-pre-wrap text-base"
                                    >
                                        {{ inquiry.response }}
                                    </p>
                                </div>
                                <div
                                    class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg"
                                >
                                    <p
                                        class="text-sm text-green-800 flex items-start gap-2"
                                    >
                                        <svg
                                            class="w-5 h-5 flex-shrink-0 mt-0.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                        <span
                                            ><strong>Good news!</strong> The
                                            broker has responded to your
                                            inquiry. You can continue the
                                            conversation by messaging them
                                            directly.</span
                                        >
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Response Yet - Encourage Patience -->
                <div
                    v-else
                    class="bg-white overflow-hidden shadow-xl sm:rounded-xl"
                >
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div
                                class="bg-yellow-100 rounded-full p-3 flex-shrink-0"
                            >
                                <svg
                                    class="w-6 h-6 text-yellow-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    ></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3
                                    class="text-xl font-bold text-gray-900 mb-2"
                                >
                                    Waiting for Broker Response
                                </h3>
                                <p class="text-gray-600 mb-4">
                                    The broker has received your inquiry and
                                    will respond soon. Most inquiries receive a
                                    response within 24-48 hours.
                                </p>
                                <div
                                    class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg"
                                >
                                    <p class="text-sm text-blue-800">
                                        <strong>What happens next?</strong> The
                                        broker will review your inquiry and send
                                        you a personalized response. You'll be
                                        notified when they reply, and you can
                                        continue the conversation through
                                        messaging.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client Response Form -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl">
                    <div class="p-6">
                        <div class="flex items-start gap-4 mb-6">
                            <div
                                class="bg-indigo-100 rounded-full p-3 flex-shrink-0"
                            >
                                <svg
                                    class="w-6 h-6 text-indigo-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    ></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3
                                    class="text-xl font-bold text-gray-900 mb-2"
                                >
                                    Add More Details
                                </h3>
                                <p class="text-gray-600 text-sm">
                                    Have more information to share? Update your
                                    inquiry with additional details or questions
                                    to help the broker assist you better.
                                </p>
                            </div>
                        </div>
                        <form @submit.prevent="updateInquiry" class="space-y-5">
                            <div
                                class="bg-gray-50 rounded-lg p-5 border-2 border-dashed border-gray-300"
                            >
                                <label
                                    for="client_response"
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                >
                                    Additional Message or Questions
                                </label>
                                <p class="text-xs text-gray-500 mb-3">
                                    Share any new requirements, questions, or
                                    changes to your preferences
                                </p>
                                <textarea
                                    id="client_response"
                                    v-model="form.client_response"
                                    rows="4"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base"
                                    placeholder="Example: I'm also interested in knowing about nearby schools and shopping centers..."
                                ></textarea>
                            </div>
                            <div
                                class="bg-gray-50 rounded-lg p-5 border-2 border-dashed border-gray-300"
                            >
                                <label
                                    for="budget_range"
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                >
                                    Budget Range
                                </label>
                                <p class="text-xs text-gray-500 mb-3">
                                    Update your budget if it has changed
                                </p>
                                <input
                                    id="budget_range"
                                    v-model="form.budget_range"
                                    type="text"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base"
                                    placeholder="e.g., ₱5,000,000 - ₱7,000,000"
                                />
                            </div>
                            <div
                                class="flex items-center justify-between pt-4 border-t border-gray-200"
                            >
                                <p class="text-sm text-gray-500">
                                    <svg
                                        class="w-4 h-4 inline mr-1"
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
                                    The broker will be notified of your updates
                                </p>
                                <PrimaryButton
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-3 text-base font-semibold"
                                >
                                    <svg
                                        v-if="!form.processing"
                                        class="w-5 h-5 mr-2 inline"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                                        ></path>
                                    </svg>
                                    <span v-if="form.processing"
                                        >Updating...</span
                                    >
                                    <span v-else>Update Inquiry</span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref, computed } from "vue";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    inquiry: Object,
    client: Object,
});

const form = useForm({
    client_response: props.inquiry.client_response || "",
    budget_range: props.inquiry.budget_range || "",
});

// Image gallery state
const currentImageIndex = ref(0);

// Computed property to get formatted images array
const propertyImages = computed(() => {
    const images = props.inquiry?.property?.images;
    if (!images || !Array.isArray(images) || images.length === 0) {
        return [];
    }
    
    // Transform images array to consistent format
    return images.map((img, index) => {
        // If image is a string, it's the path
        if (typeof img === 'string') {
            return {
                id: index,
                image_path: img.startsWith('/') ? img : `/storage/${img}`
            };
        }
        // If image is an object, extract the path from common keys
        if (typeof img === 'object') {
            const path = img.url || img.path || img.src || img.image || img.filename || '';
            return {
                id: img.id || index,
                image_path: path.startsWith('/') ? path : `/storage/${path}`
            };
        }
        return null;
    }).filter(img => img !== null && img.image_path);
});

// Computed property for current image
const currentImage = computed(() => {
    if (propertyImages.value.length > 0) {
        return propertyImages.value[currentImageIndex.value];
    }
    return null;
});

// Image navigation methods
const nextImage = () => {
    if (propertyImages.value.length > 0) {
        currentImageIndex.value = (currentImageIndex.value + 1) % propertyImages.value.length;
    }
};

const previousImage = () => {
    if (propertyImages.value.length > 0) {
        currentImageIndex.value = currentImageIndex.value === 0 
            ? propertyImages.value.length - 1 
            : currentImageIndex.value - 1;
    }
};

const updateInquiry = () => {
    form.put(route("client.inquiries.update", props.inquiry.id), {
        onSuccess: () => {
            // Form will be reset automatically on success
        },
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatPrice = (price) => {
    if (!price) return null;
    return new Intl.NumberFormat("en-PH").format(price);
};

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        responded: "bg-blue-100 text-blue-800",
        closed: "bg-gray-100 text-gray-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getStatusLabel = (status) => {
    const labels = {
        new: "New Inquiry",
        contacted: "Broker Contacted",
        scheduled: "Visit Scheduled",
        completed: "Completed",
        closed: "Closed",
        pending: "Pending",
        responded: "Responded",
    };
    return labels[status] || "Unknown";
};

const getStatusHelp = (status) => {
    const help = {
        new: {
            title: "Your inquiry is being reviewed",
            description:
                "The broker has received your inquiry and will review it shortly. You should receive a response within 24-48 hours.",
        },
        contacted: {
            title: "The broker has contacted you",
            description:
                "Check your messages or email for the broker's response. You can continue the conversation through the messaging system.",
        },
        scheduled: {
            title: "Property viewing scheduled",
            description:
                "You have a scheduled property viewing. Check your messages for details about the date, time, and meeting point.",
        },
        pending: {
            title: "Waiting for response",
            description:
                "Your inquiry has been submitted and is pending review by the broker.",
        },
        responded: {
            title: "Broker has responded",
            description:
                "The broker has sent you a response. Review their message and feel free to ask more questions.",
        },
    };
    return (
        help[status] || {
            title: "Inquiry in progress",
            description:
                "Your inquiry is being processed. Stay tuned for updates.",
        }
    );
};

// Real-time: refresh when broker updates this inquiry
onMounted(() => {
    if (window.Echo && props.client?.id) {
        window.Echo.private(`client.${props.client.id}`).listen(
            ".inquiry.status.updated",
            (e) => {
                if (e.inquiry_id === props.inquiry.id) {
                    router.reload({ only: ["inquiry"] });
                }
            }
        );
    }
});

onUnmounted(() => {
    if (window.Echo && props.client?.id) {
        window.Echo.leaveChannel(`client.${props.client.id}`);
    }
});
</script>
