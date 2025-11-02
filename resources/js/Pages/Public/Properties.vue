<script setup>
import { Link, Head, router } from "@inertiajs/vue3";
import { reactive, watch, ref } from "vue";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import PublicFooter from "@/Components/PublicFooter.vue";
import ModernInput from "@/Components/ModernInput.vue";
import ModernButton from "@/Components/ModernButton.vue";
import Pagination from "@/Components/Pagination.vue";
import LoadingSkeleton from "@/Components/LoadingSkeleton.vue";
import EmptyState from "@/Components/EmptyState.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
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

// Loading state
const isLoading = ref(false);

const form = reactive({
    search: props.filters.search || "",
    type: props.filters.type || "",
    municipality: props.filters.municipality || "",
    min_price: props.filters.min_price || "",
    max_price: props.filters.max_price || "",
    utilities: props.filters.utilities || false,
    featured: props.filters.featured || false,
    virtual_tour: props.filters.virtual_tour || false,
    include_sold: props.filters.include_sold || false,
});

const getImageUrl = (image, isVirtualTour = false) => {
    // Handle null, undefined, or empty values
    if (!image) {
        return "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
    }

    // Handle arrays - flatten and find first valid string
    if (Array.isArray(image)) {
        const flatArray = image.flat(2);
        const firstValidImage = flatArray.find(
            (img) => img && typeof img === "string" && img.trim() !== ""
        );

        if (!firstValidImage) {
            return "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
        }

        return getImageUrl(firstValidImage, isVirtualTour);
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

    // If already a full URL or data URI, return as-is
    if (
        cleanImage.startsWith("http://") ||
        cleanImage.startsWith("https://") ||
        cleanImage.startsWith("data:")
    ) {
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

function search() {
    isLoading.value = true;
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
        onFinish: () => {
            isLoading.value = false;
        },
    });
}

const formatPropertyType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

// Check if property has virtual tour data
const hasVirtualTour = (property) => {
    return (
        property.has_virtual_tour &&
        property.virtual_tour_images &&
        property.virtual_tour_images.length > 0
    );
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

// Determine if a property should be treated as under transaction.
// This is true when status is explicitly 'under_negotiation' OR
// when there is at least one active (non-finalized, non-cancelled) transaction counted by backend.
const isUnderTransaction = (property) => {
    // Prefer backend-computed flag if provided
    if (typeof property.is_under_transaction !== "undefined") {
        return Boolean(property.is_under_transaction);
    }
    // Fallback to active transactions count or explicit status
    const activeCount = Number(property.active_transactions_count || 0);
    return (
        property.status === "under_negotiation" ||
        (activeCount && activeCount > 0)
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
                    <p
                        class="text-lg md:text-xl text-neutral-600 max-w-3xl mx-auto"
                    >
                        Browse through {{ properties.total }} properties across
                        Bohol's diverse locations
                    </p>
                </div>

                <!-- Search & Filters -->
                <div
                    class="bg-white rounded-xl border border-neutral-200 shadow-sm p-6 lg:p-8 max-w-6xl mx-auto"
                >
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
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                            >
                                <select
                                    v-model="form.type"
                                    class="modern-select"
                                    id="property-type-filter"
                                    name="type"
                                >
                                    <option value="">All Property Types</option>
                                    <option
                                        v-for="t in types"
                                        :key="
                                            typeof t === 'string'
                                                ? t
                                                : t.value ?? t.key ?? String(t)
                                        "
                                        :value="
                                            typeof t === 'string'
                                                ? t
                                                : t.value ?? t.key ?? ''
                                        "
                                    >
                                        {{
                                            typeof t === "string"
                                                ? formatPropertyType(t)
                                                : t.label ||
                                                  formatPropertyType(
                                                      t.value ?? t.key ?? ""
                                                  )
                                        }}
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
                            <div
                                class="flex flex-wrap items-center justify-center gap-6 pt-2"
                            >
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
                                    <span
                                        class="text-sm font-medium text-neutral-700"
                                    >
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
                                    <span
                                        class="text-sm font-medium text-neutral-700 flex items-center gap-1"
                                    >
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
                                    <span
                                        class="text-sm font-medium text-neutral-700 flex items-center gap-1"
                                    >
                                        <VideoCameraIcon class="w-4 h-4" />
                                        Virtual Tour
                                    </span>
                                </label>
                                <label
                                    class="flex items-center gap-2 cursor-pointer hover:text-rose-600 transition-colors"
                                >
                                    <input
                                        id="sold-filter"
                                        name="include_sold"
                                        v-model="form.include_sold"
                                        type="checkbox"
                                        class="modern-checkbox"
                                    />
                                    <span
                                        class="text-sm font-medium text-neutral-700 flex items-center gap-1"
                                    >
                                        Show Sold Properties
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
                        {{
                            form.include_sold
                                ? "Sold Properties"
                                : "All Properties"
                        }}
                        <span class="text-neutral-500 text-lg font-normal">
                            ({{ properties.total }} found)
                        </span>
                    </h2>
                </div>

                <!-- Loading State -->
                <div
                    v-if="isLoading"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                >
                    <LoadingSkeleton
                        v-for="n in 6"
                        :key="n"
                        type="property-card"
                    />
                </div>

                <!-- Properties Grid -->
                <div
                    v-else-if="properties.data.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                >
                    <div
                        v-for="property in properties.data"
                        :key="property.id"
                        class="bg-white rounded-xl border border-neutral-200 overflow-hidden group hover:shadow-md transition-shadow duration-200"
                    >
                        <!-- Property Image -->
                        <div class="relative h-56 overflow-hidden">
                            <img
                                :src="getImageUrl(property.main_image)"
                                :alt="property.title"
                                :class="[
                                    'w-full h-full object-cover transition-transform duration-300',
                                    property.status === 'sold'
                                        ? 'grayscale opacity-75'
                                        : 'group-hover:scale-105',
                                ]"
                                loading="lazy"
                            />
                            <div
                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center"
                            >
                                <div
                                    class="opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                >
                                    <EyeIcon class="w-8 h-8 text-white" />
                                </div>
                            </div>
                            <!-- Badges -->
                            <div
                                class="absolute top-4 left-4 flex flex-col gap-2"
                            >
                                <div
                                    v-if="property.is_featured"
                                    class="bg-accent-500 text-white px-3 py-1 rounded-full text-xs font-medium"
                                >
                                    Featured
                                </div>
                                <div
                                    v-if="hasVirtualTour(property)"
                                    class="bg-purple-500 text-white px-3 py-1 rounded-full text-xs font-medium flex items-center gap-1"
                                >
                                    <VideoCameraIcon class="w-3 h-3" />
                                    Virtual Tour
                                </div>
                                <div
                                    class="bg-primary-600 text-white px-3 py-1 rounded-full text-xs font-medium"
                                >
                                    {{ formatPropertyType(property.type) }}
                                </div>
                            </div>
                            <!-- Status Badge -->
                            <div
                                v-if="
                                    property.status !== 'available' ||
                                    isUnderTransaction(property)
                                "
                                class="absolute top-4 right-4"
                            >
                                <span
                                    :class="{
                                        'bg-rose-600 text-white':
                                            property.status === 'sold',
                                        'bg-amber-500 text-white':
                                            property.status === 'reserved',
                                        'bg-blue-500 text-white':
                                            isUnderTransaction(property),
                                        'bg-gray-500 text-white':
                                            property.status === 'off_market',
                                    }"
                                    class="px-3 py-1.5 text-xs font-semibold rounded-lg shadow-md"
                                >
                                    <span v-if="property.status === 'sold'"
                                        >✓ Sold</span
                                    >
                                    <span
                                        v-else-if="
                                            property.status === 'reserved'
                                        "
                                        >🔒 Reserved</span
                                    >
                                    <span
                                        v-else-if="isUnderTransaction(property)"
                                        >💼 Under Transaction</span
                                    >
                                    <span
                                        v-else-if="
                                            property.status === 'off_market'
                                        "
                                        >— Off Market</span
                                    >
                                </span>
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
                                <MapPinIcon
                                    class="w-4 h-4 flex-shrink-0 text-neutral-400"
                                />
                                <span class="text-sm line-clamp-1">{{
                                    formatAddress(property)
                                }}</span>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex justify-between items-center">
                                    <div
                                        class="text-2xl lg:text-3xl font-bold text-primary-600"
                                    >
                                        {{ property.formatted_total_price }}
                                    </div>
                                    <div
                                        class="text-sm text-neutral-500 bg-neutral-100 px-3 py-1 rounded-full font-medium"
                                    >
                                        {{ property.formatted_area }}
                                    </div>
                                </div>

                                <div class="flex justify-between items-center">
                                    <div
                                        class="text-base lg:text-lg font-semibold text-accent-600"
                                    >
                                        {{
                                            property.formatted_price_per_sqm
                                        }}/sqm
                                    </div>
                                    <div
                                        class="text-sm text-neutral-600 font-medium bg-neutral-50 px-2 py-1 rounded"
                                    >
                                        {{ property.municipality }}
                                    </div>
                                </div>
                            </div>

                            <!-- Utilities & Features -->
                            <div class="flex flex-wrap items-center gap-3 mb-6">
                                <div
                                    class="flex items-center gap-1.5 bg-green-50 px-2 py-1 rounded-md"
                                >
                                    <BoltIcon class="w-4 h-4 text-green-600" />
                                    <span
                                        class="text-xs font-medium text-green-700"
                                    >
                                        {{
                                            property.electricity_available
                                                ? "Power"
                                                : "No Power"
                                        }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center gap-1.5 bg-blue-50 px-2 py-1 rounded-md"
                                >
                                    <BeakerIcon class="w-4 h-4 text-blue-600" />
                                    <span
                                        class="text-xs font-medium text-blue-700"
                                    >
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
                                    <VideoCameraIcon
                                        class="w-4 h-4 text-purple-600"
                                    />
                                    <span
                                        class="text-xs font-medium text-purple-700"
                                    >
                                        360° Tour
                                    </span>
                                </div>
                            </div>

                            <!-- Broker Info -->
                            <div
                                class="flex items-center justify-between pt-4 border-t border-neutral-100"
                            >
                                <div class="flex items-center gap-3">
                                    <UserAvatar
                                        v-if="property.broker"
                                        :user="property.broker"
                                        size="sm"
                                        bg-color="primary"
                                    />
                                    <div
                                        v-else
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
                <EmptyState
                    v-else
                    title="No Properties Found"
                    description="We couldn't find any properties matching your criteria. Try adjusting your filters or search terms."
                    :icon="BuildingOfficeIcon"
                    variant="neutral"
                    size="lg"
                    :actions="[
                        {
                            text: 'Clear All Filters',
                            onClick: () => {
                                Object.assign(form, {
                                    search: '',
                                    type: '',
                                    municipality: '',
                                    min_price: '',
                                    max_price: '',
                                    utilities: false,
                                    featured: false,
                                    virtual_tour: false,
                                });
                            },
                            variant: 'primary',
                        },
                        {
                            text: 'Browse All Properties',
                            href: route('public.properties'),
                            variant: 'outline',
                        },
                    ]"
                />

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
