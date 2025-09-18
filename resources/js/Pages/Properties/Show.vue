<template>
    <ModernDashboardLayout>
        <!-- Header Section -->
        <div
            class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg mb-6"
        >
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold mb-2">
                        {{ property.title }}
                    </h1>
                    <p class="text-blue-100 mb-4">
                        📍 {{ property.full_address }}
                    </p>
                    <div class="flex items-center space-x-4">
                        <span
                            class="px-3 py-1 rounded-full text-sm font-medium text-white"
                            :class="getStatusColor(property.status)"
                        >
                            {{ formatStatus(property.status) }}
                        </span>
                        <span
                            class="px-3 py-1 bg-white/20 text-white rounded-full text-sm font-medium"
                        >
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
                    <div class="text-3xl font-bold mb-1">
                        {{ property.formatted_total_price }}
                    </div>
                    <div class="text-blue-100">
                        {{ property.formatted_price_per_sqm }}/sqm
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Gallery -->
        <div
            v-if="property.images && property.images.length > 0"
            class="bg-white rounded-lg shadow-sm p-6 mb-6"
        >
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                Property Images
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <img
                    v-for="(image, index) in property.images"
                    :key="index"
                    :src="property.images[0]"
                    :alt="property.title"
                    class="w-full h-48 object-cover"
                />
            </div>
        </div>

        <!-- Property Details -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">
                        Property Details
                    </h3>
                    <div class="space-y-3">
                        <div
                            class="flex justify-between py-2 border-b border-gray-100"
                        >
                            <span class="text-gray-600">Lot Area:</span>
                            <span class="font-medium">{{
                                property.formatted_area
                            }}</span>
                        </div>
                        <div
                            class="flex justify-between py-2 border-b border-gray-100"
                        >
                            <span class="text-gray-600">Municipality:</span>
                            <span class="font-medium">{{
                                property.municipality
                            }}</span>
                        </div>
                        <div
                            class="flex justify-between py-2 border-b border-gray-100"
                        >
                            <span class="text-gray-600">Barangay:</span>
                            <span class="font-medium">{{
                                property.barangay
                            }}</span>
                        </div>
                        <div
                            v-if="property.title_type"
                            class="flex justify-between py-2 border-b border-gray-100"
                        >
                            <span class="text-gray-600">Title Type:</span>
                            <span class="font-medium">{{
                                formatTitleType(property.title_type)
                            }}</span>
                        </div>
                        <div
                            v-if="property.title_number"
                            class="flex justify-between py-2 border-b border-gray-100"
                        >
                            <span class="text-gray-600">Title Number:</span>
                            <span class="font-medium">{{
                                property.title_number
                            }}</span>
                        </div>
                        <div
                            v-if="property.zoning_classification"
                            class="flex justify-between py-2 border-b border-gray-100"
                        >
                            <span class="text-gray-600">Zoning:</span>
                            <span class="font-medium">{{
                                property.zoning_classification
                            }}</span>
                        </div>
                    </div>

                    <!-- Utilities & Access -->
                    <h4 class="text-lg font-semibold text-gray-900 mt-6 mb-3">
                        Utilities & Access
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div
                            class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg"
                        >
                            <span
                                :class="
                                    property.road_access
                                        ? 'text-green-500'
                                        : 'text-red-500'
                                "
                            >
                                {{ property.road_access ? "✅" : "❌" }}
                            </span>
                            <span class="text-sm font-medium">Road Access</span>
                        </div>
                        <div
                            class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg"
                        >
                            <span
                                :class="
                                    property.electricity_available
                                        ? 'text-green-500'
                                        : 'text-red-500'
                                "
                            >
                                {{
                                    property.electricity_available ? "✅" : "❌"
                                }}
                            </span>
                            <span class="text-sm font-medium">Electricity</span>
                        </div>
                        <div
                            class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg"
                        >
                            <span
                                :class="
                                    property.water_source
                                        ? 'text-green-500'
                                        : 'text-red-500'
                                "
                            >
                                {{ property.water_source ? "✅" : "❌" }}
                            </span>
                            <span class="text-sm font-medium"
                                >Water Source</span
                            >
                        </div>
                        <div
                            class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg"
                        >
                            <span
                                :class="
                                    property.internet_available
                                        ? 'text-green-500'
                                        : 'text-red-500'
                                "
                            >
                                {{ property.internet_available ? "✅" : "❌" }}
                            </span>
                            <span class="text-sm font-medium">Internet</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">
                        Description
                    </h3>
                    <p
                        class="text-gray-700 leading-relaxed mb-6 p-4 bg-gray-50 rounded-lg"
                    >
                        {{ property.description }}
                    </p>

                    <!-- Nearby Landmarks -->
                    <div
                        v-if="
                            property.nearby_landmarks &&
                            property.nearby_landmarks.length > 0
                        "
                    >
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">
                            Nearby Landmarks
                        </h4>
                        <ul
                            class="list-disc list-inside space-y-1 text-gray-700 p-4 bg-gray-50 rounded-lg"
                        >
                            <li
                                v-for="landmark in property.nearby_landmarks"
                                :key="landmark"
                            >
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
                    <div
                        class="mt-8 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg border border-blue-100"
                    >
                        <h4 class="text-lg font-semibold text-gray-900 mb-2">
                            Listed by
                        </h4>
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-lg"
                            >
                                {{ property.broker.name.charAt(0) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ property.broker.name }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Licensed Broker
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-wrap gap-4">
                <button
                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm"
                >
                    📞 Contact Broker
                </button>
                <button
                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm"
                >
                    💬 Send Inquiry
                </button>
                <button
                    class="border border-gray-300 hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors shadow-sm"
                >
                    ❤️ Save Property
                </button>
            </div>

            <!-- Admin/Broker Actions -->
            <div
                v-if="canEditProperty"
                class="mt-6 pt-6 border-t border-gray-200"
            >
                <div class="flex flex-wrap gap-4">
                    <Link
                        :href="route('broker.properties.edit', property.slug)"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm"
                    >
                        ✏️ Edit Property
                    </Link>
                    <button
                        @click="toggleFeatured"
                        :class="
                            property.is_featured
                                ? 'bg-gray-500 hover:bg-gray-600'
                                : 'bg-yellow-500 hover:bg-yellow-600'
                        "
                        class="text-white px-4 py-2 rounded-lg font-medium transition-colors shadow-sm"
                    >
                        {{
                            property.is_featured
                                ? "⭐ Remove Featured"
                                : "⭐ Make Featured"
                        }}
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
        <div
            class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 border border-green-100"
        >
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    🏝️ Discover Bohol's Beauty
                </h3>
                <p class="text-gray-600 text-sm">
                    Experience the tropical paradise of Bohol with its pristine
                    beaches, chocolate hills, and rich cultural heritage. Your
                    dream property awaits in this island paradise.
                </p>
            </div>
        </div>
        <!-- Enhanced Image Gallery -->
        <div class="bg-white rounded-xl shadow-soft overflow-hidden mb-8">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-neutral-900 mb-6">
                    Property Images
                </h2>
                <div
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
                >
                    <div
                        v-for="(image, index) in property.images"
                        :key="index"
                        class="relative group cursor-pointer rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300"
                        @click="openImageModal(image)"
                    >
                        <img
                            :src="asset('storage/' + image)"
                            :alt="`Property image ${index + 1}`"
                            class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        />
                        <div
                            class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center"
                        >
                            <svg
                                class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300"
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- GIS Mapping Section -->
        <div
            v-if="property.coordinates_lat && property.coordinates_lng"
            class="bg-white rounded-xl shadow-soft overflow-hidden mb-8"
        >
            <div class="p-6">
                <h2
                    class="text-2xl font-bold text-neutral-900 mb-6 flex items-center"
                >
                    <svg
                        class="w-6 h-6 mr-3 text-primary-600"
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
                </h2>

                <!-- Interactive Map -->
                <div class="mb-6">
                    <div
                        ref="mapContainer"
                        class="w-full h-96 rounded-lg border border-gray-200 overflow-hidden"
                    ></div>
                </div>

                <!-- Coordinate Information -->
                <div
                    class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg"
                >
                    <div class="text-center">
                        <div class="text-sm text-gray-600 mb-1">Latitude</div>
                        <div class="font-semibold text-gray-900">
                            {{
                                parseFloat(property.coordinates_lat).toFixed(6)
                            }}
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-sm text-gray-600 mb-1">Longitude</div>
                        <div class="font-semibold text-gray-900">
                            {{
                                parseFloat(property.coordinates_lng).toFixed(6)
                            }}
                        </div>
                    </div>
                    <div class="text-center">
                        <a
                            :href="`https://www.google.com/maps?q=${property.coordinates_lat},${property.coordinates_lng}`"
                            target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                        >
                            <svg
                                class="w-4 h-4 mr-2"
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
                            View in Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
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
    canEdit: Boolean,
});

// Map functionality
const mapContainer = ref(null);
const map = ref(null);

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
    window.open(image, "_blank");
};

// Initialize map when component mounts
const initMap = () => {
    if (
        props.property.coordinates_lat &&
        props.property.coordinates_lng &&
        mapContainer.value
    ) {
        const lat = parseFloat(props.property.coordinates_lat);
        const lng = parseFloat(props.property.coordinates_lng);

        map.value = L.map(mapContainer.value).setView([lat, lng], 15);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors",
        }).addTo(map.value);

        L.marker([lat, lng])
            .addTo(map.value)
            .bindPopup(
                `<b>${props.property.title}</b><br>${props.property.full_address}`
            )
            .openPopup();
    }
};

onMounted(() => {
    initMap();
});

onUnmounted(() => {
    if (map.value) {
        map.value.remove();
    }
});
</script>
