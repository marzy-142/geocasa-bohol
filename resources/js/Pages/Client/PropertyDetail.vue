<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useToast } from "@/Composables/useToast";
import VirtualTourViewer from "@/Components/VirtualTourViewer.vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import {
    HeartIcon,
    MapPinIcon,
    HomeIcon,
    CurrencyDollarIcon,
    CalendarIcon,
    UserIcon,
    PhoneIcon,
    EnvelopeIcon,
    BuildingOfficeIcon,
    ArrowLeftIcon,
    ShareIcon,
    PrinterIcon,
    EyeIcon,
    CheckCircleIcon,
    ClockIcon,
    ChatBubbleLeftRightIcon,
    CubeIcon,
    SparklesIcon,
} from "@heroicons/vue/24/outline";
import { HeartIcon as HeartIconSolid } from "@heroicons/vue/24/solid";

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
    isSaved: Boolean,
    hasInquired: Boolean,
    clientInquiry: Object,
    similarProperties: Array,
    client: Object,
});

const toast = useToast();

// Image gallery state
const currentImageIndex = ref(0);
const imageLoading = ref(false);
const showImageModal = ref(false);

// Map state
const propertyMap = ref(null);
const mapContainer = ref(null);

// Safe images computed property
const safeImages = computed(() => {
    if (!props.property.images || !Array.isArray(props.property.images)) {
        return [];
    }

    return props.property.images
        .map((image) => {
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

const currentImage = computed(() => {
    if (
        props.property.main_image &&
        !props.property.main_image.includes("data:image/svg+xml")
    ) {
        return getImageUrl(props.property.main_image);
    }
    if (safeImages.value && safeImages.value.length > 0) {
        const imageAtIndex = safeImages.value[currentImageIndex.value];
        return getImageUrl(imageAtIndex);
    }
    return getImageUrl(props.property.main_image);
});

// Virtual Tour computed properties
const hasVirtualTourData = computed(() => {
    return (
        props.property.virtual_tour_images &&
        props.property.virtual_tour_images.length > 0
    );
});

const virtualTourImages = computed(() => {
    if (!props.property.virtual_tour_images) return [];
    let images = props.property.virtual_tour_images;
    if (
        Array.isArray(images) &&
        images.length > 0 &&
        Array.isArray(images[0])
    ) {
        images = images.flat();
    }
    return images.map((img) => getImageUrl(img)).filter((url) => url);
});

// Status badge helpers
const getStatusBadgeClass = (status) => {
    switch ((status || "").toLowerCase()) {
        case "sold":
            return "bg-red-100 text-red-700 border-red-200";
        case "under_contract":
        case "under-contract":
            return "bg-yellow-100 text-yellow-700 border-yellow-200";
        case "available":
        default:
            return "bg-green-100 text-green-700 border-green-200";
    }
};

const isUnavailable = (status) => {
    const s = (status || "").toLowerCase();
    return s === "sold" || s === "under_contract" || s === "finalized";
};

// Helper functions
const getImageUrl = (image) => {
    if (!image) return "/images/placeholder-property.jpg";
    if (typeof image === "string") {
        if (image.startsWith("http")) return image;
        if (image.startsWith("/storage/")) return image;
        if (image.startsWith("data:")) return image;
        return `/storage/${image}`;
    }
    return "/images/placeholder-property.jpg";
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
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

// Image navigation
const previousImage = () => {
    if (safeImages.value.length === 0) return;
    currentImageIndex.value =
        currentImageIndex.value === 0
            ? safeImages.value.length - 1
            : currentImageIndex.value - 1;
    imageLoading.value = true;
};

const nextImage = () => {
    if (safeImages.value.length === 0) return;
    currentImageIndex.value =
        (currentImageIndex.value + 1) % safeImages.value.length;
    imageLoading.value = true;
};

const selectImage = (index) => {
    currentImageIndex.value = index;
    imageLoading.value = true;
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
};

// Keyboard navigation
const handleKeydown = (e) => {
    if (!showImageModal.value) return;
    if (e.key === "ArrowLeft") previousImage();
    if (e.key === "ArrowRight") nextImage();
    if (e.key === "Escape") closeImageModal();
};

// Actions
const toggleSaved = () => {
    const route = props.isSaved
        ? "client.properties.unsave"
        : "client.properties.save";

    router.post(
        window.route(route, props.property.slug || props.property.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    props.isSaved ? "Removed from Saved" : "Saved!",
                    props.isSaved
                        ? "Property removed from your saved list"
                        : "Property added to your saved list"
                );
            },
        }
    );
};

const createInquiry = () => {
    router.visit(
        route("client.inquiries.create", { property_id: props.property.id })
    );
};

const shareProperty = () => {
    if (navigator.share) {
        navigator
            .share({
                title: props.property.title,
                text: `Check out this property: ${props.property.title}`,
                url: window.location.href,
            })
            .catch(() => {
                copyToClipboard();
            });
    } else {
        copyToClipboard();
    }
};

const copyToClipboard = () => {
    navigator.clipboard.writeText(window.location.href);
    toast.success("Link Copied", "Property link copied to clipboard");
};

const printProperty = () => {
    window.print();
};

// Map initialization
const initMap = () => {
    if (
        !mapContainer.value ||
        !props.property.latitude ||
        !props.property.longitude
    )
        return;

    const lat = parseFloat(props.property.latitude);
    const lng = parseFloat(props.property.longitude);

    if (isNaN(lat) || isNaN(lng)) return;

    propertyMap.value = L.map(mapContainer.value).setView([lat, lng], 15);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors",
    }).addTo(propertyMap.value);

    L.marker([lat, lng])
        .addTo(propertyMap.value)
        .bindPopup(
            `<b>${props.property.title}</b><br>${props.property.municipality}`
        )
        .openPopup();
};

// Lifecycle hooks
onMounted(() => {
    document.addEventListener("keydown", handleKeydown);
    setTimeout(initMap, 100);
});

onUnmounted(() => {
    document.removeEventListener("keydown", handleKeydown);
    document.body.style.overflow = "auto";
    if (propertyMap.value) {
        propertyMap.value.remove();
    }
});
</script>

<template>
    <Head :title="`${property.title} - GeoCasa Bohol`" />

    <ModernDashboardLayout>
        <!-- Header with breadcrumb and actions -->
        <div class="bg-white border border-neutral-200 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <nav class="flex items-center space-x-2 text-sm">
                    <Link
                        :href="route('client.dashboard')"
                        class="text-neutral-500 hover:text-blue-600 transition-colors"
                    >
                        Dashboard
                    </Link>
                    <span class="text-neutral-400">/</span>
                    <Link
                        :href="route('client.properties')"
                        class="text-neutral-500 hover:text-blue-600 transition-colors"
                    >
                        Properties
                    </Link>
                    <span class="text-neutral-400">/</span>
                    <span class="text-neutral-900 font-medium">{{
                        property.title
                    }}</span>
                </nav>

                <div class="flex items-center gap-3">
                    <button
                        @click="shareProperty"
                        class="p-2 text-neutral-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                        title="Share property"
                    >
                        <ShareIcon class="w-5 h-5" />
                    </button>
                    <button
                        @click="printProperty"
                        class="p-2 text-neutral-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                        title="Print property details"
                    >
                        <PrinterIcon class="w-5 h-5" />
                    </button>
                    <button
                        @click="toggleSaved"
                        :class="[
                            'p-2 rounded-lg transition-colors',
                            isSaved
                                ? 'text-red-600 bg-red-50 hover:bg-red-100'
                                : 'text-neutral-600 hover:text-red-600 hover:bg-red-50',
                        ]"
                        :title="isSaved ? 'Remove from saved' : 'Save property'"
                    >
                        <HeartIconSolid v-if="isSaved" class="w-5 h-5" />
                        <HeartIcon v-else class="w-5 h-5" />
                    </button>
                    <Link
                        :href="route('client.properties')"
                        class="flex items-center gap-2 px-4 py-2 text-neutral-700 bg-neutral-100 hover:bg-neutral-200 rounded-lg transition-colors"
                    >
                        <ArrowLeftIcon class="w-4 h-4" />
                        Back to Properties
                    </Link>
                </div>
            </div>

            <!-- Status badges -->
            <div class="flex items-center gap-3 flex-wrap">
                <span
                    class="px-3 py-1.5 rounded-full text-sm font-semibold border"
                    :class="getStatusBadgeClass(property.status)"
                >
                    {{ (property.status || "available").replace("_", " ") }}
                </span>
                <span
                    v-if="property.is_featured"
                    class="px-3 py-1.5 rounded-full text-sm font-semibold bg-amber-100 text-amber-700 border border-amber-200 flex items-center gap-1"
                >
                    <SparklesIcon class="w-4 h-4" />
                    Featured
                </span>
                <span
                    v-if="hasInquired"
                    class="px-3 py-1.5 rounded-full text-sm font-semibold bg-blue-100 text-blue-700 border border-blue-200 flex items-center gap-1"
                >
                    <CheckCircleIcon class="w-4 h-4" />
                    You've inquired about this property
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Image Gallery -->
                <div
                    class="bg-white rounded-lg border border-neutral-200 shadow-sm overflow-hidden"
                >
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
                        <div
                            v-if="imageLoading"
                            class="absolute inset-0 flex items-center justify-center bg-gray-100"
                        >
                            <div
                                class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"
                            ></div>
                        </div>

                        <!-- Image Navigation -->
                        <div
                            v-if="safeImages.length > 1"
                            class="absolute inset-y-0 left-0 right-0 flex items-center justify-between p-4 opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                            <button
                                @click="previousImage"
                                class="p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
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
                                        d="M15 19l-7-7 7-7"
                                    />
                                </svg>
                            </button>
                            <button
                                @click="nextImage"
                                class="p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
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
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </button>
                        </div>

                        <!-- Image Counter -->
                        <div
                            v-if="safeImages.length > 1"
                            class="absolute bottom-4 right-4 bg-black/50 text-white px-3 py-1 rounded-full text-sm"
                        >
                            {{ currentImageIndex + 1 }} /
                            {{ safeImages.length }}
                        </div>

                        <!-- Fullscreen Button -->
                        <button
                            v-if="safeImages.length > 0"
                            @click="openImageModal"
                            class="absolute top-4 right-4 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
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

                    <!-- Thumbnails -->
                    <div v-if="safeImages.length > 1" class="p-4 lg:p-6">
                        <div class="flex gap-2 overflow-x-auto pb-2">
                            <button
                                v-for="(image, index) in safeImages"
                                :key="index"
                                @click="selectImage(index)"
                                :class="[
                                    'flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all',
                                    currentImageIndex === index
                                        ? 'border-blue-600 ring-2 ring-blue-200'
                                        : 'border-neutral-200 hover:border-blue-400',
                                ]"
                            >
                                <img
                                    :src="getImageUrl(image)"
                                    :alt="`Property image ${index + 1}`"
                                    class="w-full h-full object-cover"
                                />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Property Details -->
                <div class="bg-white rounded-lg border border-neutral-200 p-6">
                    <h1 class="text-3xl font-bold text-neutral-900 mb-4">
                        {{ property.title }}
                    </h1>

                    <div class="flex items-center text-neutral-600 mb-6">
                        <MapPinIcon class="w-5 h-5 mr-2" />
                        <span>{{ formatAddress(property) }}</span>
                    </div>

                    <div class="mb-6">
                        <div class="text-4xl font-bold text-blue-600 mb-2">
                            {{ formatCurrency(property.total_price) }}
                        </div>
                        <div class="text-lg text-neutral-600">
                            {{ formatCurrency(property.price_per_sqm) }}/sqm
                        </div>
                    </div>

                    <!-- Key Features Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div
                            class="bg-neutral-50 rounded-lg p-4 border border-neutral-200"
                        >
                            <div class="text-sm text-neutral-600 mb-1">
                                Property Type
                            </div>
                            <div class="font-semibold text-neutral-900">
                                {{
                                    property.type
                                        ?.replace(/_/g, " ")
                                        .replace(/\b\w/g, (l) =>
                                            l.toUpperCase()
                                        )
                                }}
                            </div>
                        </div>
                        <div
                            class="bg-neutral-50 rounded-lg p-4 border border-neutral-200"
                        >
                            <div class="text-sm text-neutral-600 mb-1">
                                Lot Area
                            </div>
                            <div class="font-semibold text-neutral-900">
                                {{ property.lot_area_sqm?.toLocaleString() }}
                                sqm
                            </div>
                        </div>
                        <div
                            class="bg-neutral-50 rounded-lg p-4 border border-neutral-200"
                        >
                            <div class="text-sm text-neutral-600 mb-1">
                                Location
                            </div>
                            <div class="font-semibold text-neutral-900">
                                {{ property.municipality }}
                            </div>
                        </div>
                        <div
                            class="bg-neutral-50 rounded-lg p-4 border border-neutral-200"
                        >
                            <div class="text-sm text-neutral-600 mb-1">
                                Listed
                            </div>
                            <div class="font-semibold text-neutral-900">
                                {{ formatDate(property.created_at) }}
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <h2 class="text-xl font-bold text-neutral-900 mb-3">
                            Description
                        </h2>
                        <div
                            class="text-neutral-700 leading-relaxed whitespace-pre-line"
                        >
                            {{ property.description }}
                        </div>
                    </div>
                </div>

                <!-- Utilities & Features -->
                <div class="bg-white rounded-lg border border-neutral-200 p-6">
                    <h2 class="text-xl font-bold text-neutral-900 mb-4">
                        Utilities & Features
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center gap-3">
                            <div
                                :class="[
                                    'w-10 h-10 rounded-lg flex items-center justify-center',
                                    property.electricity_available
                                        ? 'bg-green-100 text-green-600'
                                        : 'bg-neutral-100 text-neutral-400',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-neutral-900">
                                    Electricity
                                </div>
                                <div class="text-sm text-neutral-600">
                                    {{
                                        property.electricity_available
                                            ? "Available"
                                            : "Not Available"
                                    }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                :class="[
                                    'w-10 h-10 rounded-lg flex items-center justify-center',
                                    property.water_source
                                        ? 'bg-blue-100 text-blue-600'
                                        : 'bg-neutral-100 text-neutral-400',
                                ]"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.5 2a.5.5 0 000 1h2a.5.5 0 000-1h-2zm4 0a.5.5 0 000 1h2a.5.5 0 000-1h-2zm4 0a.5.5 0 000 1h2a.5.5 0 000-1h-2zM10 9.5a.5.5 0 01.5-.5h2a.5.5 0 01.5.5v5a.5.5 0 01-.5.5h-2a.5.5 0 01-.5-.5v-5zm-3 0a.5.5 0 01.5-.5h2a.5.5 0 01.5.5v5a.5.5 0 01-.5.5h-2a.5.5 0 01-.5-.5v-5zm6 0a.5.5 0 01.5-.5h2a.5.5 0 01.5.5v5a.5.5 0 01-.5.5h-2a.5.5 0 01-.5-.5v-5z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-neutral-900">
                                    Water Source
                                </div>
                                <div class="text-sm text-neutral-600">
                                    {{
                                        property.water_source
                                            ? "Available"
                                            : "Not Available"
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Virtual Tour -->
                <div
                    v-if="hasVirtualTourData"
                    class="bg-white rounded-lg border border-neutral-200 p-6"
                >
                    <h2 class="text-xl font-bold text-neutral-900 mb-4">
                        Virtual Tour
                    </h2>
                    <VirtualTourViewer
                        :images="virtualTourImages"
                        :hotspots="property.tour_hotspots"
                    />
                </div>

                <!-- Map -->
                <div
                    v-if="property.latitude && property.longitude"
                    class="bg-white rounded-lg border border-neutral-200 p-6"
                >
                    <h2 class="text-xl font-bold text-neutral-900 mb-4">
                        Location
                    </h2>
                    <div
                        ref="mapContainer"
                        class="h-96 rounded-lg overflow-hidden"
                    ></div>
                </div>

                <!-- Similar Properties -->
                <div
                    v-if="similarProperties && similarProperties.length > 0"
                    class="bg-white rounded-lg border border-neutral-200 p-6"
                >
                    <h2 class="text-xl font-bold text-neutral-900 mb-4">
                        Similar Properties
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Link
                            v-for="similar in similarProperties"
                            :key="similar.id"
                            :href="route('client.properties.show', similar.id)"
                            class="group border border-neutral-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow"
                        >
                            <div class="aspect-video overflow-hidden">
                                <img
                                    :src="getImageUrl(similar.main_image)"
                                    :alt="similar.title"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                            </div>
                            <div class="p-4">
                                <h3
                                    class="font-semibold text-neutral-900 mb-2 line-clamp-1"
                                >
                                    {{ similar.title }}
                                </h3>
                                <div class="text-blue-600 font-bold mb-1">
                                    {{ formatCurrency(similar.total_price) }}
                                </div>
                                <div class="text-sm text-neutral-600">
                                    {{ similar.municipality }}
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions Card -->
                <div
                    class="bg-white rounded-lg border border-neutral-200 p-6 sticky top-6"
                >
                    <h2 class="text-lg font-bold text-neutral-900 mb-4">
                        Quick Actions
                    </h2>

                    <!-- Inquiry Status -->
                    <div
                        v-if="hasInquired && clientInquiry"
                        class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <CheckCircleIcon class="w-5 h-5 text-blue-600" />
                            <span class="font-semibold text-blue-900"
                                >Inquiry Sent</span
                            >
                        </div>
                        <p class="text-sm text-blue-700 mb-2">
                            You sent an inquiry on
                            {{ formatDate(clientInquiry.created_at) }}
                        </p>
                        <Link
                            :href="route('client.dashboard')"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        >
                            View in Dashboard →
                        </Link>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button
                            v-if="!hasInquired"
                            @click="createInquiry"
                            :disabled="isUnavailable(property.status)"
                            :class="[
                                'w-full py-3 px-4 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2',
                                isUnavailable(property.status)
                                    ? 'bg-neutral-200 text-neutral-500 cursor-not-allowed'
                                    : 'bg-blue-600 text-white hover:bg-blue-700',
                            ]"
                        >
                            <ChatBubbleLeftRightIcon class="w-5 h-5" />
                            Send Inquiry
                        </button>

                        <button
                            @click="toggleSaved"
                            :class="[
                                'w-full py-3 px-4 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2',
                                isSaved
                                    ? 'bg-red-50 text-red-600 border-2 border-red-200 hover:bg-red-100'
                                    : 'bg-neutral-50 text-neutral-700 border-2 border-neutral-200 hover:bg-neutral-100',
                            ]"
                        >
                            <HeartIconSolid v-if="isSaved" class="w-5 h-5" />
                            <HeartIcon v-else class="w-5 h-5" />
                            {{ isSaved ? "Saved" : "Save Property" }}
                        </button>

                        <button
                            @click="shareProperty"
                            class="w-full py-3 px-4 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2 bg-neutral-50 text-neutral-700 border-2 border-neutral-200 hover:bg-neutral-100"
                        >
                            <ShareIcon class="w-5 h-5" />
                            Share Property
                        </button>
                    </div>
                </div>

                <!-- Broker Information -->
                <div
                    v-if="property.broker"
                    class="bg-white rounded-lg border border-neutral-200 p-6"
                >
                    <h2 class="text-lg font-bold text-neutral-900 mb-4">
                        Listed By
                    </h2>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center"
                            >
                                <UserIcon class="w-6 h-6 text-blue-600" />
                            </div>
                            <div>
                                <div class="font-semibold text-neutral-900">
                                    {{ property.broker.name }}
                                </div>
                                <div class="text-sm text-neutral-600">
                                    Real Estate Broker
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="property.broker.email"
                            class="flex items-center gap-2 text-sm text-neutral-600"
                        >
                            <EnvelopeIcon class="w-4 h-4" />
                            <a
                                :href="`mailto:${property.broker.email}`"
                                class="hover:text-blue-600"
                            >
                                {{ property.broker.email }}
                            </a>
                        </div>

                        <div
                            v-if="property.broker.phone"
                            class="flex items-center gap-2 text-sm text-neutral-600"
                        >
                            <PhoneIcon class="w-4 h-4" />
                            <a
                                :href="`tel:${property.broker.phone}`"
                                class="hover:text-blue-600"
                            >
                                {{ property.broker.phone }}
                            </a>
                        </div>

                        <div
                            v-if="property.broker.office_address"
                            class="flex items-start gap-2 text-sm text-neutral-600"
                        >
                            <BuildingOfficeIcon class="w-4 h-4 mt-0.5" />
                            <span>{{ property.broker.office_address }}</span>
                        </div>
                    </div>
                </div>

                <!-- Property ID -->
                <div
                    class="bg-neutral-50 rounded-lg border border-neutral-200 p-4"
                >
                    <div class="text-xs text-neutral-500 mb-1">Property ID</div>
                    <div class="font-mono text-sm text-neutral-900">
                        {{ property.id }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Modal -->
        <Teleport to="body">
            <div
                v-if="showImageModal"
                class="fixed inset-0 z-50 bg-black/95 flex items-center justify-center"
                @click="closeImageModal"
            >
                <button
                    @click="closeImageModal"
                    class="absolute top-4 right-4 p-2 text-white hover:bg-white/20 rounded-full transition-colors z-10"
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

                <div
                    class="relative w-full h-full flex items-center justify-center p-4"
                    @click.stop
                >
                    <img
                        :src="currentImage"
                        :alt="property.title"
                        class="max-w-full max-h-full object-contain"
                    />

                    <!-- Navigation in modal -->
                    <div
                        v-if="safeImages.length > 1"
                        class="absolute inset-x-0 flex items-center justify-between px-4"
                    >
                        <button
                            @click.stop="previousImage"
                            class="p-3 rounded-full bg-white/20 text-white hover:bg-white/30 transition-colors"
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
                            @click.stop="nextImage"
                            class="p-3 rounded-full bg-white/20 text-white hover:bg-white/30 transition-colors"
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
        </Teleport>
    </ModernDashboardLayout>
</template>

<style scoped>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
