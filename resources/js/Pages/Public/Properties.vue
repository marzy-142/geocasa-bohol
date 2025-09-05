<script setup>
import { Link, Head, router } from "@inertiajs/vue3";
import { reactive, watch } from "vue";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import PublicFooter from "@/Components/PublicFooter.vue";
import ModernInput from "@/Components/ModernInput.vue";
import ModernButton from "@/Components/ModernButton.vue";
import Pagination from "@/Components/Pagination.vue";
import {
    MagnifyingGlassIcon,
    MapPinIcon,
    BuildingOfficeIcon,
    StarIcon,
    BoltIcon,
    BeakerIcon,
    UserIcon,
    EyeIcon,
    VideoCameraIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    auth: Object,
    properties: Object,
    types: Array,
    municipalities: Array,
    filters: Object,
});

const form = reactive({
    search: props.filters.search || "",
    type: props.filters.type || "",
    municipality: props.filters.municipality || "",
    min_price: props.filters.min_price || "",
    max_price: props.filters.max_price || "",
    utilities: props.filters.utilities || false,
    featured: props.filters.featured || false,
    virtual_tour: props.filters.virtual_tour || false,
});

const getImageUrl = (image, isVirtualTour = false) => {
    // Handle null, undefined, or empty values
    if (!image) {
        return "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzlDQTNBRiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==";
    }

    // Handle arrays - flatten and find first valid string
    if (Array.isArray(image)) {
        console.warn("Array passed to getImageUrl:", image);
        const flatArray = image.flat(2);
        const firstValidImage = flatArray.find(
            (img) => img && typeof img === "string" && img.trim() !== ""
        );

        if (!firstValidImage) {
            console.error("No valid image found in array:", image);
            return "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzlDQTNBRiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==";
        }

        return getImageUrl(firstValidImage, isVirtualTour);
    }

    // Ensure we have a string
    if (typeof image !== "string") {
        console.error("Invalid image type:", typeof image, image);
        return "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzlDQTNBRiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==";
    }

    // Clean the image string
    let cleanImage = image.trim();

    if (!cleanImage) {
        return "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzlDQTNBRiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==";
    }

    // If already a full URL, return as-is
    if (cleanImage.startsWith("http://") || cleanImage.startsWith("https://")) {
        return cleanImage;
    }

    // If already starts with /storage/, return as-is to prevent duplication
    if (cleanImage.startsWith("/storage/")) {
        return cleanImage;
    }

    // Remove any leading slashes to prevent double slashes
    cleanImage = cleanImage.replace(/^\/+/, "");

    // Check for existing path segments to prevent duplication
    if (cleanImage.includes("properties/virtual-tours/")) {
        return `/storage/${cleanImage}`;
    } else if (cleanImage.includes("properties/images/")) {
        return `/storage/${cleanImage}`;
    }

    // Determine the correct path based on context
    if (
        isVirtualTour ||
        cleanImage.includes("virtual") ||
        cleanImage.includes("tour")
    ) {
        return `/storage/properties/virtual-tours/${cleanImage}`;
    } else {
        return `/storage/properties/images/${cleanImage}`;
    }
};

function search() {
    const payload = { ...form };

    // Drop falsey filters except 0 numbers
    Object.keys(payload).forEach((k) => {
        const v = payload[k];
        if (v === "" || v === null || v === false) {
            delete payload[k];
        }
    });

    router.get(route("public.properties"), payload, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
    });
}

const formatPropertyType = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1);
};

// Check if property has virtual tour data
const hasVirtualTour = (property) => {
    return (
        property.has_virtual_tour &&
        property.virtual_tour_images &&
        property.virtual_tour_images.length > 0
    );
};

// Auto-search when filters change
watch(
    form,
    () => {
        search();
    },
    { deep: true }
);
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-neutral-50 to-neutral-100">
        <Head title="Properties - GeoCasa Bohol" />

        <!-- Navigation -->
        <PublicNavigation :auth="auth" />

        <!-- Hero Section -->
        <section class="relative py-16 lg:py-20 overflow-hidden">
            <div
                class="absolute inset-0 bg-gradient-to-r from-primary-600/10 to-accent-600/10"
            ></div>
            <div
                class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center"
            >
                <div class="max-w-4xl mx-auto mb-12">
                    <h1
                        class="text-3xl md:text-5xl lg:text-6xl font-bold text-neutral-900 mb-6"
                    >
                        Discover Your
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-accent-600"
                        >
                            Dream Property
                        </span>
                        in Bohol
                    </h1>
                    <p class="text-lg md:text-xl text-neutral-600 max-w-3xl mx-auto">
                        Browse through {{ properties.total }} available properties
                        across Bohol's diverse locations
                    </p>
                </div>

                <!-- Search & Filters -->
                <div class="bg-white rounded-2xl shadow-soft-xl p-6 lg:p-8 max-w-6xl mx-auto">
                    <form @submit.prevent="search" class="space-y-6">
                        <!-- Main Search -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1">
                                <ModernInput
                                    v-model="form.search"
                                    type="text"
                                    placeholder="Search by title, location, or description..."
                                    class="text-base lg:text-lg"
                                >
                                    <template #icon>
                                        <MagnifyingGlassIcon class="w-5 h-5" />
                                    </template>
                                </ModernInput>
                            </div>
                            <ModernButton
                                type="submit"
                                class="px-6 lg:px-8 py-3 lg:py-4 text-base lg:text-lg whitespace-nowrap"
                            >
                                <MagnifyingGlassIcon class="w-5 h-5" />
                                Search
                            </ModernButton>
                        </div>

                        <!-- Advanced Filters -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <select
                                    v-model="form.type"
                                    class="modern-select"
                                    id="property-type-filter"
                                    name="type"
                                >
                                    <option value="">All Property Types</option>
                                    <option
                                        v-for="type in types"
                                        :key="type"
                                        :value="type"
                                    >
                                        {{ type }}
                                    </option>
                                </select>

                                <select
                                    id="municipality"
                                    name="municipality"
                                    v-model="form.municipality"
                                    class="modern-select"
                                >
                                    <option value="">All Municipalities</option>
                                    <option
                                        v-for="municipality in municipalities"
                                        :key="municipality"
                                        :value="municipality"
                                    >
                                        {{ municipality }}
                                    </option>
                                </select>

                                <div class="flex gap-2">
                                    <ModernInput
                                        v-model="form.min_price"
                                        type="number"
                                        placeholder="Min Price"
                                        class="flex-1"
                                    />
                                    <ModernInput
                                        v-model="form.max_price"
                                        type="number"
                                        placeholder="Max Price"
                                        class="flex-1"
                                    />
                                </div>
                            </div>

                            <!-- Filter Checkboxes -->
                            <div class="flex flex-wrap items-center justify-center gap-6 pt-2">
                                <label
                                    class="flex items-center gap-2 cursor-pointer hover:text-primary-600 transition-colors"
                                >
                                    <input
                                        id="utilities-filter"
                                        name="utilities"
                                        v-model="form.utilities"
                                        type="checkbox"
                                        class="modern-checkbox"
                                    />
                                    <span class="text-sm font-medium text-neutral-700">
                                        With Utilities
                                    </span>
                                </label>
                                <label
                                    class="flex items-center gap-2 cursor-pointer hover:text-accent-600 transition-colors"
                                >
                                    <input
                                        id="featured-filter"
                                        name="featured"
                                        v-model="form.featured"
                                        type="checkbox"
                                        class="modern-checkbox"
                                    />
                                    <span class="text-sm font-medium text-neutral-700 flex items-center gap-1">
                                        <StarIcon class="w-4 h-4" />
                                        Featured Only
                                    </span>
                                </label>
                                <label
                                    class="flex items-center gap-2 cursor-pointer hover:text-purple-600 transition-colors"
                                >
                                    <input
                                        id="virtual-tour-filter"
                                        name="virtual_tour"
                                        v-model="form.virtual_tour"
                                        type="checkbox"
                                        class="modern-checkbox"
                                    />
                                    <span class="text-sm font-medium text-neutral-700 flex items-center gap-1">
                                        <VideoCameraIcon class="w-4 h-4" />
                                        Virtual Tour
                                    </span>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Properties Grid -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold text-neutral-900">
                        Available Properties
                        <span class="text-neutral-500 text-lg font-normal">
                            ({{ properties.total }} found)
                        </span>
                    </h2>
                </div>

                <div
                    v-if="properties.data.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                >
                    <div
                        v-for="property in properties.data"
                        :key="property.id"
                        class="card overflow-hidden group hover:shadow-soft-xl transition-all duration-300 hover:-translate-y-1"
                    >
                        <!-- Property Image -->
                        <div class="relative h-56 overflow-hidden">
                            <img
                                :src="getImageUrl(property.images && property.images.length > 0 ? property.images[0] : null)"
                                :alt="property.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />

                            <!-- Badges -->
                            <div
                                class="absolute top-4 left-4 flex flex-col gap-2"
                            >
                                <div
                                    v-if="property.is_featured"
                                    class="bg-gradient-to-r from-accent-500 to-accent-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-soft"
                                >
                                    ⭐ Featured
                                </div>
                                <div
                                    v-if="hasVirtualTour(property)"
                                    class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-soft flex items-center gap-1"
                                >
                                    <VideoCameraIcon class="w-3 h-3" />
                                    Virtual Tour
                                </div>
                                <div
                                    class="bg-primary-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-soft"
                                >
                                    {{ formatPropertyType(property.type) }}
                                </div>
                            </div>
                        </div>

                        <!-- Property Details -->
                        <div class="p-6">
                            <h3
                                class="text-xl font-bold text-neutral-900 mb-3 line-clamp-2 leading-tight"
                            >
                                {{ property.title }}
                            </h3>

                            <div
                                class="flex items-center gap-2 text-neutral-600 mb-4"
                            >
                                <MapPinIcon class="w-4 h-4 flex-shrink-0 text-neutral-400" />
                                <span class="text-sm line-clamp-1">{{
                                    property.full_address
                                }}</span>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex justify-between items-center">
                                    <div class="text-2xl lg:text-3xl font-bold text-primary-600">
                                        {{ property.formatted_total_price }}
                                    </div>
                                    <div class="text-sm text-neutral-500 bg-neutral-100 px-3 py-1 rounded-full font-medium">
                                        {{ property.formatted_area }}
                                    </div>
                                </div>

                                <div class="flex justify-between items-center">
                                    <div class="text-base lg:text-lg font-semibold text-accent-600">
                                        {{ property.formatted_price_per_sqm }}/sqm
                                    </div>
                                    <div class="text-sm text-neutral-600 font-medium bg-neutral-50 px-2 py-1 rounded">
                                        {{ property.municipality }}
                                    </div>
                                </div>
                            </div>

                            <!-- Utilities & Features -->
                            <div class="flex flex-wrap items-center gap-3 mb-6">
                                <div class="flex items-center gap-1.5 bg-green-50 px-2 py-1 rounded-md">
                                    <BoltIcon class="w-4 h-4 text-green-600" />
                                    <span class="text-xs font-medium text-green-700">
                                        {{
                                            property.electricity_available
                                                ? "Power"
                                                : "No Power"
                                        }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 bg-blue-50 px-2 py-1 rounded-md">
                                    <BeakerIcon class="w-4 h-4 text-blue-600" />
                                    <span class="text-xs font-medium text-blue-700">
                                        {{
                                            property.water_source
                                                ? "Water"
                                                : "No Water"
                                        }}
                                    </span>
                                </div>
                                <div
                                    v-if="hasVirtualTour(property)"
                                    class="flex items-center gap-1.5 bg-purple-50 px-2 py-1 rounded-md"
                                >
                                    <VideoCameraIcon class="w-4 h-4 text-purple-600" />
                                    <span class="text-xs font-medium text-purple-700">
                                        360° Tour
                                    </span>
                                </div>
                            </div>

                            <!-- Broker Info -->
                            <div
                                class="flex items-center justify-between pt-4 border-t border-neutral-100"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 bg-gradient-to-br from-primary-400 to-accent-500 rounded-full flex items-center justify-center"
                                    >
                                        <UserIcon class="w-4 h-4 text-white" />
                                    </div>
                                    <div>
                                        <div
                                            class="text-sm font-semibold text-neutral-900"
                                        >
                                            {{
                                                property.broker?.name ||
                                                "GeoCasa Bohol"
                                            }}
                                        </div>
                                        <div class="text-xs text-neutral-500">
                                            Licensed Broker
                                        </div>
                                    </div>
                                </div>

                                <Link
                                    :href="
                                        route(
                                            'public.properties.show',
                                            property.slug
                                        )
                                    "
                                    class="btn-primary-sm flex items-center gap-2"
                                >
                                    <EyeIcon class="w-4 h-4" />
                                    <span v-if="hasVirtualTour(property)"
                                        >View & Tour</span
                                    >
                                    <span v-else>View Details</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Properties Found -->
                <div v-else class="text-center py-16">
                    <div
                        class="w-24 h-24 bg-neutral-100 rounded-3xl flex items-center justify-center mx-auto mb-6"
                    >
                        <BuildingOfficeIcon
                            class="w-12 h-12 text-neutral-400"
                        />
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900 mb-4">
                        No Properties Found
                    </h3>
                    <p class="text-neutral-600 mb-8 max-w-md mx-auto">
                        We couldn't find any properties matching your criteria.
                        Try adjusting your filters or search terms.
                    </p>
                    <ModernButton
                        @click="
                            form = {
                                search: '',
                                type: '',
                                municipality: '',
                                min_price: '',
                                max_price: '',
                                utilities: false,
                                featured: false,
                                virtual_tour: false,
                            }
                        "
                    >
                        Clear All Filters
                    </ModernButton>
                </div>

                <!-- Pagination -->
                <div v-if="properties.data.length > 0" class="mt-12">
                    <Pagination :links="properties.links" />
                </div>
            </div>
        </section>

        <!-- Footer -->
        <PublicFooter />
    </div>
</template>
