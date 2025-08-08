<template>
    <ModernDashboardLayout>
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold mb-2">{{ property.title }}</h1>
                    <p class="text-blue-100 mb-4">📍 {{ property.full_address }}</p>
                    <div class="flex items-center space-x-4">
                        <span
                            class="px-3 py-1 rounded-full text-sm font-medium text-white"
                            :class="getStatusColor(property.status)"
                        >
                            {{ formatStatus(property.status) }}
                        </span>
                        <span class="px-3 py-1 bg-white/20 text-white rounded-full text-sm font-medium">
                            {{ formatType(property.type) }}
                        </span>
                        <span
                            v-if="property.is_featured"
                            class="px-3 py-1 bg-yellow-400 text-yellow-900 rounded-full text-sm font-medium"
                        >
                            ⭐ Featured
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold mb-1">{{ property.formatted_total_price }}</div>
                    <div class="text-blue-100">{{ property.formatted_price_per_sqm }}/sqm</div>
                </div>
            </div>
        </div>

        <!-- Image Gallery -->
        <div v-if="property.images && property.images.length > 0" class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Property Images</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <img
                    v-for="(image, index) in property.images"
                    :key="index"
                    :src="asset('storage/' + image)"
                    :alt="`${property.title} - Image ${index + 1}`"
                    class="w-full h-64 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity shadow-sm"
                    @click="openImageModal(image)"
                />
            </div>
        </div>

        <!-- Property Details -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Property Details</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Lot Area:</span>
                            <span class="font-medium">{{ property.formatted_area }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Municipality:</span>
                            <span class="font-medium">{{ property.municipality }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Barangay:</span>
                            <span class="font-medium">{{ property.barangay }}</span>
                        </div>
                        <div v-if="property.title_type" class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Title Type:</span>
                            <span class="font-medium">{{ formatTitleType(property.title_type) }}</span>
                        </div>
                        <div v-if="property.title_number" class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Title Number:</span>
                            <span class="font-medium">{{ property.title_number }}</span>
                        </div>
                        <div v-if="property.zoning_classification" class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Zoning:</span>
                            <span class="font-medium">{{ property.zoning_classification }}</span>
                        </div>
                    </div>

                    <!-- Utilities & Access -->
                    <h4 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Utilities & Access</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg">
                            <span :class="property.road_access ? 'text-green-500' : 'text-red-500'">
                                {{ property.road_access ? "✅" : "❌" }}
                            </span>
                            <span class="text-sm font-medium">Road Access</span>
                        </div>
                        <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg">
                            <span :class="property.electricity_available ? 'text-green-500' : 'text-red-500'">
                                {{ property.electricity_available ? "✅" : "❌" }}
                            </span>
                            <span class="text-sm font-medium">Electricity</span>
                        </div>
                        <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg">
                            <span :class="property.water_source ? 'text-green-500' : 'text-red-500'">
                                {{ property.water_source ? "✅" : "❌" }}
                            </span>
                            <span class="text-sm font-medium">Water Source</span>
                        </div>
                        <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg">
                            <span :class="property.internet_available ? 'text-green-500' : 'text-red-500'">
                                {{ property.internet_available ? "✅" : "❌" }}
                            </span>
                            <span class="text-sm font-medium">Internet</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Description</h3>
                    <p class="text-gray-700 leading-relaxed mb-6 p-4 bg-gray-50 rounded-lg">
                        {{ property.description }}
                    </p>

                    <!-- Nearby Landmarks -->
                    <div v-if="property.nearby_landmarks && property.nearby_landmarks.length > 0">
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Nearby Landmarks</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-700 p-4 bg-gray-50 rounded-lg">
                            <li v-for="landmark in property.nearby_landmarks" :key="landmark">
                                {{ landmark }}
                            </li>
                        </ul>
                    </div>

                    <!-- Google Maps Link -->
                    <div v-if="property.google_maps_link" class="mt-6">
                        <a
                            :href="property.google_maps_link"
                            target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors shadow-sm"
                        >
                            🗺️ View on Google Maps
                        </a>
                    </div>

                    <!-- Broker Information -->
                    <div class="mt-8 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-100">
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">Listed by</h4>
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                {{ property.broker.name.charAt(0) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ property.broker.name }}</p>
                                <p class="text-sm text-gray-600">Licensed Broker</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-wrap gap-4">
                <button class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                    📞 Contact Broker
                </button>
                <button class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                    💬 Send Inquiry
                </button>
                <button class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                    ❤️ Save Property
                </button>
            </div>

            <!-- Admin/Broker Actions -->
            <div v-if="canEditProperty" class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex flex-wrap gap-4">
                    <Link
                        :href="route('properties.edit', property.slug)"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm"
                    >
                        ✏️ Edit Property
                    </Link>
                    <button
                        @click="toggleFeatured"
                        :class="property.is_featured ? 'bg-gray-500 hover:bg-gray-600' : 'bg-yellow-500 hover:bg-yellow-600'"
                        class="text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm"
                    >
                        {{ property.is_featured ? "⭐ Remove Featured" : "⭐ Make Featured" }}
                    </button>
                    <button
                        @click="deleteProperty"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm"
                    >
                        🗑️ Delete Property
                    </button>
                </div>
            </div>
        </div>

        <!-- Bohol Inspiration Section -->
        <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 border border-green-100">
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">🏝️ Discover Bohol's Beauty</h3>
                <p class="text-gray-600 text-sm">
                    Experience the tropical paradise of Bohol with its pristine beaches, chocolate hills, and rich cultural heritage.
                    Your dream property awaits in this island paradise.
                </p>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";

const props = defineProps({
    property: Object,
});

const page = usePage();

const canEditProperty = computed(() => {
    const user = page.props.auth.user;
    return (
        user.role === "admin" ||
        (user.role === "broker" &&
            user.is_approved &&
            props.property.broker_id === user.id)
    );
});

const getStatusColor = (status) => {
    const colors = {
        available: "bg-green-500",
        reserved: "bg-yellow-500",
        sold: "bg-red-500",
        under_negotiation: "bg-blue-500",
        off_market: "bg-gray-500",
    };
    return colors[status] || "bg-gray-500";
};

const formatType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatStatus = (status) => {
    return status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatTitleType = (titleType) => {
    const types = {
        titled: "Titled",
        tax_declared: "Tax Declared",
        mother_title: "Mother Title",
        cct: "CCT",
    };
    return types[titleType] || titleType;
};

const asset = (path) => {
    return `/${path}`;
};

const toggleFeatured = () => {
    router.post(route("properties.toggle-featured", props.property.slug));
};

const deleteProperty = () => {
    if (confirm("Are you sure you want to delete this property?")) {
        router.delete(route("properties.destroy", props.property.slug));
    }
};

const openImageModal = (image) => {
    // Implement image modal functionality
    window.open(asset("storage/" + image), "_blank");
};
</script>
