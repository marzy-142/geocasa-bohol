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
                v-if="safeImages.length > 0"
                class="bg-white p-4 md:p-6 rounded-xl shadow-sm mb-6 border border-gray-200"
            >
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">Photos</h2>
                    <span class="text-xs text-gray-500"
                        >{{ mainImageIndex + 1 }} /
                        {{ safeImages.length }}</span
                    >
                </div>
                <div class="space-y-3">
                    <div
                        class="relative w-full aspect-[16/9] bg-gray-100 rounded-lg overflow-hidden group"
                    >
                        <img
                            :src="getImageUrl(safeImages[mainImageIndex])"
                            class="w-full h-full object-cover cursor-zoom-in"
                            :alt="`Photo ${mainImageIndex + 1}`"
                            loading="lazy"
                            decoding="async"
                            @error="
                                handleImageError(
                                    $event,
                                    safeImages[mainImageIndex]
                                )
                            "
                            @click="openViewer(mainImageIndex)"
                        />
                        <!-- Inline nav controls on hover -->
                        <button
                            type="button"
                            class="absolute left-2 top-1/2 -translate-y-1/2 hidden md:flex items-center justify-center w-9 h-9 rounded-full bg-black/40 text-white group-hover:flex focus:outline-none focus:ring-2 focus:ring-white/70"
                            @click.stop="prevViewer"
                            aria-label="Previous photo"
                        >
                            ‹
                        </button>
                        <button
                            type="button"
                            class="absolute right-2 top-1/2 -translate-y-1/2 hidden md:flex items-center justify-center w-9 h-9 rounded-full bg-black/40 text-white group-hover:flex focus:outline-none focus:ring-2 focus:ring-white/70"
                            @click.stop="nextViewer"
                            aria-label="Next photo"
                        >
                            ›
                        </button>
                        <!-- Expand button -->
                        <button
                            type="button"
                            class="absolute top-2 right-2 px-2.5 py-1.5 rounded-md bg-black/40 text-white text-xs backdrop-blur hover:bg-black/50 focus:outline-none focus:ring-2 focus:ring-white/70"
                            @click.stop="openViewer(mainImageIndex)"
                            aria-label="Open lightbox"
                        >
                            Expand
                        </button>
                    </div>
                    <div class="flex gap-2 overflow-x-auto no-scrollbar">
                        <button
                            v-for="(image, index) in safeImages"
                            :key="`thumb-${index}`"
                            @click="mainImageIndex = index"
                            :aria-label="`Show photo ${index + 1}`"
                            class="relative flex-shrink-0 w-20 h-14 md:w-24 md:h-16 rounded-lg overflow-hidden border transition ring-2"
                            :class="
                                index === mainImageIndex
                                    ? 'border-blue-500 ring-blue-200'
                                    : 'border-gray-200 ring-transparent hover:border-gray-300'
                            "
                        >
                            <img
                                :src="getImageUrl(image)"
                                class="w-full h-full object-cover"
                                :alt="`Thumbnail ${index + 1}`"
                                loading="lazy"
                                decoding="async"
                                @error="handleImageError($event, image)"
                                @click.stop="openViewer(index)"
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
                                    {{ formattedTypesString }}
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
        <!-- Lightbox Modal -->
        <div v-if="showViewer" class="viewer-overlay" @click.self="closeViewer">
            <button
                class="absolute top-4 right-4 px-3 py-2 rounded-lg viewer-btn"
                @click="closeViewer"
                aria-label="Close"
            >
                ✕
            </button>
            <button
                class="absolute left-4 px-3 py-2 rounded-full viewer-btn"
                @click="prevViewer"
                aria-label="Previous"
            >
                ‹
            </button>
            <img
                :src="getImageUrl(safeImages[mainImageIndex])"
                class="max-w-[90vw] max-h-[85vh] object-contain rounded-lg shadow-lg"
                :alt="`Image ${mainImageIndex + 1}`"
                loading="eager"
            />
            <button
                class="absolute right-4 px-3 py-2 rounded-full viewer-btn"
                @click="nextViewer"
                aria-label="Next"
            >
                ›
            </button>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from "vue";
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

// Normalize images for robust rendering (supports strings, objects, JSON, CSV)
const extractImageSrc = (item) => {
    if (!item) return null;
    if (typeof item === "string") return item.trim();
    if (typeof item === "object") {
        // Common keys we may receive from backend
        const keys = [
            "url",
            "full_url",
            "src",
            "path",
            "image_path",
            "storage_path",
            "filename",
            "name",
        ];
        for (const k of keys) {
            const v = item[k];
            if (typeof v === "string" && v.trim()) return v.trim();
        }
    }
    return null;
};

const normalizeImages = (imagesInput) => {
    let list = [];

    if (Array.isArray(imagesInput)) {
        list = imagesInput;
    } else if (typeof imagesInput === "string") {
        const s = imagesInput.trim();
        try {
            const parsed = JSON.parse(s);
            if (Array.isArray(parsed)) list = parsed;
        } catch {
            // Fallback: comma-separated
            if (s.includes(",")) list = s.split(",");
            else if (s) list = [s];
        }
    }

    // Map to strings and filter
    const mapped = list
        .map(extractImageSrc)
        .filter((x) => typeof x === "string" && x.length > 0);

    // De-duplicate while preserving order
    const seen = new Set();
    const deduped = [];
    for (const v of mapped) {
        const key = v.toLowerCase();
        if (!seen.has(key)) {
            seen.add(key);
            deduped.push(v);
        }
    }
    return deduped;
};

const safeImages = computed(() => {
    const primary = extractImageSrc(
        props.property?.primary_image || props.property?.main_image
    );
    const imgs = normalizeImages(props.property?.images);
    // Put primary image at front if present and not already first
    const list = primary ? [primary, ...imgs] : imgs.slice();
    // Guard against non-image placeholder strings like 'null'
    return list.filter(
        (s) => typeof s === "string" && s !== "null" && s !== "undefined"
    );
});

const formattedTypesString = computed(() => {
    const ft = props.property?.formatted_types || [];
    if (Array.isArray(ft) && ft.length) {
        return ft
            .map((t) => (typeof t === "string" ? t : t.label || t.value))
            .join(", ");
    }
    if (
        (props.property?.types || []).includes?.("other") ||
        props.property?.type === "other"
    ) {
        return props.property?.custom_type_text || "Other";
    }
    return props.property?.type || "—";
});

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
    // Accept strings or objects
    const src = extractImageSrc(image);
    if (!src) return PLACEHOLDER_DATA_URI;
    const val = src.trim();
    if (val.startsWith("http") || val.startsWith("data:")) return val;
    if (val.startsWith("/storage/")) return val;
    if (val.startsWith("storage/")) return `/${val}`;
    if (val.startsWith("properties/")) return `/storage/${val}`;
    // Common absolute paths without leading slash
    return `/storage/properties/images/${val}`;
};

const PLACEHOLDER_DATA_URI =
    "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='800' height='600'%3E%3Crect width='100%25' height='100%25' fill='%23f3f4f6'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' fill='%239ca3af' font-family='Arial' font-size='20'%3ENo image%3C/text%3E%3C/svg%3E";

const handleImageError = (event, image) => {
    console.warn("Image failed to load:", image, "URL:", event?.target?.src);
    if (event?.target) event.target.src = PLACEHOLDER_DATA_URI;
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

// Removed delete button per request

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
    initMap();
    window.addEventListener("keydown", onKey);
});

onUnmounted(() => {
    if (map.value) {
        map.value.remove();
    }
    window.removeEventListener("keydown", onKey);
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

// Lightweight Lightbox Viewer
const showViewer = ref(false);
const openViewer = (idx = 0) => {
    if (!safeImages.value.length) return;
    mainImageIndex.value = Math.min(
        Math.max(idx, 0),
        safeImages.value.length - 1
    );
    showViewer.value = true;
};
const closeViewer = () => (showViewer.value = false);
const nextViewer = () => {
    if (!safeImages.value.length) return;
    mainImageIndex.value = (mainImageIndex.value + 1) % safeImages.value.length;
};
const prevViewer = () => {
    if (!safeImages.value.length) return;
    mainImageIndex.value =
        (mainImageIndex.value - 1 + safeImages.value.length) %
        safeImages.value.length;
};
const onKey = (e) => {
    if (!showViewer.value) return;
    if (e.key === "Escape") closeViewer();
    else if (e.key === "ArrowRight") nextViewer();
    else if (e.key === "ArrowLeft") prevViewer();
};

// Keep index in bounds and lock body scroll when viewer is open
watch(
    () => safeImages.value.length,
    (len) => {
        if (len === 0) mainImageIndex.value = 0;
        else if (mainImageIndex.value > len - 1) mainImageIndex.value = 0;
    }
);

watch(showViewer, (open) => {
    try {
        const body = document.querySelector("body");
        if (!body) return;
        if (open) body.classList.add("overflow-hidden");
        else body.classList.remove("overflow-hidden");
    } catch {}
});
</script>

<style scoped>
.viewer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 60;
}
.viewer-btn {
    background: rgba(0, 0, 0, 0.6);
    color: white;
}
</style>
