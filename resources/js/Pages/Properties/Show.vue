<template>
    <ModernDashboardLayout>
        <div class="max-w-6xl mx-auto p-4 lg:p-6">
            <!-- Back button -->
            <div class="mb-4">
                <button
                    @click="goBack"
                    class="inline-flex items-center text-gray-600 hover:text-gray-800"
                    aria-label="Go back"
                >
                    <ArrowLeftIcon class="w-5 h-5 mr-1" />
                    <span>Back</span>
                </button>
            </div>
            <!-- Title -->
            <div
                class="bg-white p-6 rounded-xl shadow-sm mb-6 border border-gray-200"
            >
                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-3"
                >
                    <div>
                        <h1
                            class="text-2xl md:text-3xl font-bold text-gray-900 mb-1"
                        >
                            {{ property.title }}
                        </h1>
                        <p class="text-gray-600">{{ property.full_address }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-2xl font-extrabold text-blue-700">
                            {{ property.formatted_total_price }}
                        </div>
                        <span
                            :class="getStatusColor(property.status)"
                            class="px-3 py-1.5 rounded-full text-sm text-white"
                        >
                            {{ formatStatus(property.status) }}
                        </span>
                    </div>
                </div>
                <div
                    v-if="property.is_under_transaction"
                    class="mt-3 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3"
                >
                    This property is part of an active transaction.
                </div>
            </div>

            <!-- Photos -->
            <div
                v-if="property.images && property.images.length > 0"
                class="bg-white p-4 md:p-6 rounded-xl shadow-sm mb-6 border border-gray-200"
            >
                <h2 class="font-semibold text-gray-900 mb-4">Photos</h2>
                <div class="space-y-3">
                    <div
                        class="relative w-full aspect-[16/9] bg-gray-100 rounded-lg overflow-hidden"
                    >
                        <img
                            :src="getImageUrl(property.images[mainImageIndex])"
                            class="w-full h-full object-cover"
                            :alt="`Photo ${mainImageIndex + 1}`"
                            @error="
                                handleImageError(
                                    $event,
                                    property.images[mainImageIndex]
                                )
                            "
                        />
                    </div>
                    <div class="flex gap-2 overflow-x-auto no-scrollbar">
                        <button
                            v-for="(image, index) in property.images"
                            :key="`thumb-${index}`"
                            @click="mainImageIndex = index"
                            :aria-label="`Show photo ${index + 1}`"
                            class="relative flex-shrink-0 w-20 h-14 md:w-24 md:h-16 rounded-lg overflow-hidden border transition ring-2"
                            :class="
                                index === mainImageIndex
                                    ? 'border-blue-500 ring-blue-200'
                                    : 'border-gray-200 ring-transparent'
                            "
                        >
                            <img
                                :src="getImageUrl(image)"
                                class="w-full h-full object-cover"
                                :alt="`Thumbnail ${index + 1}`"
                                @error="handleImageError($event, image)"
                            />
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Description -->
                    <div
                        class="bg-white p-6 rounded-xl shadow-sm border border-gray-200"
                    >
                        <h2 class="font-semibold text-gray-900 mb-2">
                            Description
                        </h2>
                        <p class="text-gray-700 leading-relaxed">
                            {{ property.description }}
                        </p>
                    </div>

                    <!-- Key Facts -->
                    <div
                        class="bg-white p-6 rounded-xl shadow-sm border border-gray-200"
                    >
                        <h2 class="font-semibold text-gray-900 mb-3">
                            Key Facts
                        </h2>
                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm"
                        >
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p
                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                >
                                    Area
                                </p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ property.formatted_area }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p
                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                >
                                    Types
                                </p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{
                                        property.formatted_types?.length
                                            ? property.formatted_types.join(
                                                  ", "
                                              )
                                            : property.type || "—"
                                    }}
                                </p>
                            </div>
                            <div
                                class="bg-gray-50 rounded-lg p-3"
                                v-if="property.formatted_price_per_sqm"
                            >
                                <p
                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                >
                                    Price per sqm
                                </p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ property.formatted_price_per_sqm }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p
                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                >
                                    Status
                                </p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ formatStatus(property.status) }}
                                </p>
                            </div>
                            <div
                                class="bg-gray-50 rounded-lg p-3"
                                v-if="property.title_type"
                            >
                                <p
                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                >
                                    Title
                                </p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ formatTitleType(property.title_type) }}
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p
                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                >
                                    Location
                                </p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ property.municipality }},
                                    {{ property.barangay }}
                                </p>
                            </div>
                        </div>

                        <div v-if="hasUtilities" class="mt-4 pt-4 border-t">
                            <div class="flex gap-2 flex-wrap">
                                <span
                                    v-if="property.road_access"
                                    class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs"
                                    >✓ Road</span
                                >
                                <span
                                    v-if="property.electricity_available"
                                    class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs"
                                    >✓ Electric</span
                                >
                                <span
                                    v-if="property.water_source"
                                    class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs"
                                    >✓ Water</span
                                >
                                <span
                                    v-if="property.internet_available"
                                    class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs"
                                    >✓ Internet</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Map -->
                    <div
                        v-if="
                            property.coordinates_lat && property.coordinates_lng
                        "
                        class="bg-white p-6 rounded-xl shadow-sm border border-gray-200"
                    >
                        <h2 class="font-semibold text-gray-900 mb-3">
                            Location
                        </h2>
                        <div
                            ref="mapContainer"
                            class="w-full h-56 md:h-72 bg-gray-200 rounded-lg mb-3"
                        ></div>
                        <a
                            :href="`https://www.google.com/maps/dir/?api=1&destination=${property.coordinates_lat},${property.coordinates_lng}`"
                            target="_blank"
                            class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700"
                        >
                            Get Directions
                        </a>
                    </div>
                </div>

                <!-- Sidebar -->
                <div>
                    <div
                        class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 sticky top-4"
                    >
                        <h3 class="font-semibold text-gray-900 mb-3">Broker</h3>
                        <div class="mb-4">
                            <div class="font-medium">
                                {{ property.broker?.name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                Licensed Broker
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Link
                                :href="
                                    route(
                                        'public.properties.show',
                                        property.slug
                                    )
                                "
                                class="block w-full py-2 bg-blue-600 text-white rounded-lg text-center hover:bg-blue-700"
                            >
                                Inquire on Public Page
                            </Link>
                            <a
                                v-if="property.google_maps_link"
                                :href="property.google_maps_link"
                                target="_blank"
                                class="block w-full py-2 border rounded-lg text-center hover:bg-gray-50"
                            >
                                View on Google Maps
                            </a>
                        </div>

                        <!-- Admin -->
                        <div
                            v-if="canEditProperty"
                            class="mt-4 pt-4 border-t space-y-2"
                        >
                            <Link
                                :href="
                                    route(
                                        'broker.properties.edit',
                                        property.slug
                                    )
                                "
                                class="block w-full py-2 bg-yellow-500 text-white text-center rounded-lg hover:bg-yellow-600"
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
                                class="w-full py-2 text-white rounded-lg transition-colors"
                            >
                                {{
                                    property.is_featured
                                        ? "⭐ Remove Featured"
                                        : "⭐ Make Featured"
                                }}
                            </button>
                            <button
                                @click="deleteProperty"
                                class="w-full py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                            >
                                🗑️ Delete Property
                            </button>
                            <Link
                                :href="
                                    route(
                                        'public.properties.show',
                                        property.slug
                                    )
                                "
                                class="block w-full py-2 border rounded-lg text-center hover:bg-gray-50"
                            >
                                🔗 Open Public View
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { ArrowLeftIcon } from "@heroicons/vue/24/outline";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

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

const mapContainer = ref(null);
const map = ref(null);
const page = usePage();
const mainImageIndex = ref(0);

const canEditProperty = computed(() => {
    const user = page.props.auth.user;
    return (
        user.role === "admin" ||
        (user.role === "broker" &&
            user.is_approved &&
            props.property.broker_id === user.id)
    );
});

const hasUtilities = computed(() => {
    return (
        props.property.road_access ||
        props.property.electricity_available ||
        props.property.water_source ||
        props.property.internet_available
    );
});

const getImageUrl = (image) => {
    // Debug: log the image value
    console.log("Image path:", image);

    // Try different path formats
    if (image.startsWith("http")) {
        return image;
    } else if (image.startsWith("properties/")) {
        return `/storage/${image}`;
    } else if (image.startsWith("/storage/")) {
        return image;
    } else {
        return `/storage/properties/images/${image}`;
    }
};

const handleImageError = (event, image) => {
    console.error("Image failed to load:", image);
    console.error("Attempted URL:", event.target.src);
    event.target.src = "/images/placeholder.jpg";
};

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

const formatStatus = (status) => {
    return status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatTitleType = (titleType) => {
    const types = {
        titled: "Titled",
        tax_declared: "Tax Declared",
        cct: "CCT",
    };
    return types[titleType] || titleType;
};

const deleteProperty = () => {
    if (confirm("Are you sure you want to delete this property?")) {
        router.delete(route("broker.properties.destroy", props.property.slug));
    }
};

const toggleFeatured = () => {
    router.post(
        route("broker.properties.toggle-featured", props.property.slug)
    );
};

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
    console.log("Property data:", props.property);
    console.log("Images array:", props.property.images);
    initMap();
});

onUnmounted(() => {
    if (map.value) {
        map.value.remove();
    }
});

// Navigation: back with sensible fallback by role
const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
        return;
    }
    const role = page.props?.auth?.user?.role;
    if (role === "broker") {
        router.visit(route("broker.properties.index"));
    } else if (role === "admin") {
        router.visit(route("admin.properties.index"));
    } else {
        router.visit(route("client.properties"));
    }
};
</script>
