<template>
    <ModernDashboardLayout>
        <div class="max-w-5xl mx-auto p-4">
            <!-- Title -->
            <div class="bg-white p-6 rounded mb-4">
                <h1 class="text-3xl font-bold mb-2">{{ property.title }}</h1>
                <p class="text-gray-600 mb-3">{{ property.full_address }}</p>
                <div class="flex gap-3 items-center">
                    <div class="text-2xl font-bold text-blue-600">
                        {{ property.formatted_total_price }}
                    </div>
                    <span
                        :class="getStatusColor(property.status)"
                        class="px-3 py-1 rounded text-sm text-white"
                    >
                        {{ formatStatus(property.status) }}
                    </span>
                </div>
            </div>

            <!-- Photos -->
            <div
                v-if="property.images && property.images.length > 0"
                class="bg-white p-6 rounded mb-4"
            >
                <h2 class="font-semibold mb-3">Photos</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <div
                        v-for="(image, index) in property.images"
                        :key="index"
                        class="relative"
                    >
                        <img
                            :src="getImageUrl(image)"
                            :alt="`Photo ${index + 1}`"
                            class="w-full h-40 object-cover rounded"
                            @error="handleImageError($event, image)"
                        />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Description -->
                    <div class="bg-white p-6 rounded">
                        <h2 class="font-semibold mb-2">Description</h2>
                        <p class="text-gray-700">{{ property.description }}</p>
                    </div>

                    <!-- Details -->
                    <div class="bg-white p-6 rounded">
                        <h2 class="font-semibold mb-3">Details</h2>
                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="text-gray-500">Area:</span>
                                <strong>{{ property.formatted_area }}</strong>
                            </div>
                            <div>
                                <span class="text-gray-500">Location:</span>
                                <strong
                                    >{{ property.municipality }},
                                    {{ property.barangay }}</strong
                                >
                            </div>
                            <div v-if="property.title_type">
                                <span class="text-gray-500">Title:</span>
                                <strong>{{
                                    formatTitleType(property.title_type)
                                }}</strong>
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
                        class="bg-white p-6 rounded"
                    >
                        <h2 class="font-semibold mb-3">Location</h2>
                        <div
                            ref="mapContainer"
                            class="w-full h-48 bg-gray-200 rounded mb-3"
                        ></div>
                        <a
                            :href="`https://www.google.com/maps/dir/?api=1&destination=${property.coordinates_lat},${property.coordinates_lng}`"
                            target="_blank"
                            class="inline-block px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700"
                        >
                            Get Directions
                        </a>
                    </div>
                </div>

                <!-- Sidebar -->
                <div>
                    <div class="bg-white p-6 rounded sticky top-4">
                        <h3 class="font-semibold mb-3">Broker</h3>
                        <div class="mb-4">
                            <div class="font-medium">
                                {{ property.broker?.name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                Licensed Broker
                            </div>
                        </div>

                        <div class="space-y-2">
                            <button
                                class="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                            >
                                Send Inquiry
                            </button>
                            <button
                                class="w-full py-2 border rounded hover:bg-gray-50"
                            >
                                Call
                            </button>
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
                                class="block w-full py-2 bg-yellow-500 text-white text-center rounded hover:bg-yellow-600"
                            >
                                Edit
                            </Link>
                            <button
                                @click="deleteProperty"
                                class="w-full py-2 bg-red-500 text-white rounded hover:bg-red-600"
                            >
                                Delete
                            </button>
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
</script>
