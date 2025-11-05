<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, reactive, watch, computed } from "vue";
import LoadingSkeleton from "@/Components/LoadingSkeleton.vue";
import EmptyState from "@/Components/EmptyState.vue";
import EnhancedTooltip from "@/Components/EnhancedTooltip.vue";
import PropertyMapView from "@/Components/PropertyMapView.vue";
import { useToast } from "@/Composables/useToast";
import {
    MagnifyingGlassIcon,
    MapPinIcon,
    BuildingOfficeIcon,
    StarIcon,
    HeartIcon,
    EyeIcon,
    FunnelIcon,
    AdjustmentsHorizontalIcon,
    XMarkIcon,
    PlusIcon,
    ArrowRightIcon,
    CheckCircleIcon,
    ClockIcon,
    CurrencyDollarIcon,
    HomeIcon,
    MapIcon,
    ListBulletIcon,
    Squares2X2Icon,
    SparklesIcon,
    TrophyIcon,
    HomeModernIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    properties: Object,
    types: Array,
    municipalities: Array,
    filters: Object,
    savedProperties: {
        type: Array,
        default: () => [],
    },
    isSavedView: {
        type: Boolean,
        default: false,
    },
});

// Reactive form state
const form = reactive({
    search: props.filters.search || "",
    type: props.filters.type || "",
    municipality: props.filters.municipality || "",
    min_price: props.filters.min_price || "",
    max_price: props.filters.max_price || "",
    min_area: props.filters.min_area || "",
    max_area: props.filters.max_area || "",
    utilities: props.filters.utilities || false,
    featured: props.filters.featured || false,
    virtual_tour: props.filters.virtual_tour || false,
});

// Toast notifications
const toast = useToast();

// UI state
const showFilters = ref(false);
const viewMode = ref("grid"); // 'grid' or 'list'
const sortBy = ref("relevance");
const showMap = ref(false);
const selectedProperties = ref([]);
const isLoading = ref(false);
const isInitialLoad = ref(true);
const isSavingSearch = ref(false);

// Computed properties
const filteredProperties = computed(() => {
    return props.properties.data || [];
});

const savedPropertyIds = computed(() => {
    return props.savedProperties.map((p) => p.id);
});

const hasActiveFilters = computed(() => {
    return (
        form.search ||
        form.type ||
        form.municipality ||
        form.min_price ||
        form.max_price ||
        form.min_area ||
        form.max_area ||
        form.utilities ||
        form.featured ||
        form.virtual_tour
    );
});

// Methods
const saveSearch = () => {
    if (!hasActiveFilters.value) {
        toast.warning(
            "No Filters",
            "Please apply some filters before saving your search."
        );
        return;
    }

    isSavingSearch.value = true;

    router.post(
        route("client.searches.store"),
        {
            name: generateSearchName(),
            filters: { ...form },
            notify_on_new: true,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(
                    "Search Saved",
                    "You'll receive email alerts when new matching properties are listed."
                );
                isSavingSearch.value = false;
            },
            onError: () => {
                toast.error(
                    "Failed to Save",
                    "Unable to save search. Please try again."
                );
                isSavingSearch.value = false;
            },
        }
    );
};

const generateSearchName = () => {
    const parts = [];

    if (form.type) {
        parts.push(form.type.replace(/_/g, " "));
    }
    if (form.municipality) {
        parts.push(`in ${form.municipality}`);
    }
    if (form.min_price || form.max_price) {
        if (form.min_price && form.max_price) {
            parts.push(
                `₱${formatNumber(form.min_price)}-₱${formatNumber(
                    form.max_price
                )}`
            );
        } else if (form.min_price) {
            parts.push(`above ₱${formatNumber(form.min_price)}`);
        } else {
            parts.push(`under ₱${formatNumber(form.max_price)}`);
        }
    }

    return parts.length > 0 ? parts.join(" ") : "My Property Search";
};

const formatNumber = (value) => {
    return new Intl.NumberFormat("en-US").format(value);
};

const handlePropertyClick = (property) => {
    router.visit(route("client.properties.show", property.id));
};

// Methods
const search = () => {
    const payload = { ...form };
    isLoading.value = true;

    // Remove empty filters
    Object.keys(payload).forEach((key) => {
        const value = payload[key];
        if (value === "" || value === null || value === false) {
            delete payload[key];
        }
    });

    router.get(route("client.properties"), payload, {
        preserveScroll: true,
        replace: true,
        preserveState: true,
        onFinish: () => {
            isLoading.value = false;
            isInitialLoad.value = false;
        },
    });
};

const clearFilters = () => {
    Object.keys(form).forEach((key) => {
        if (typeof form[key] === "boolean") {
            form[key] = false;
        } else {
            form[key] = "";
        }
    });
    search();
};

const toggleSavedProperty = (propertyId, propertySlug) => {
    if (savedPropertyIds.value.includes(propertyId)) {
        // Remove from saved
        router.post(
            route("client.properties.unsave", propertySlug),
            {},
            {
                preserveScroll: true,
                preserveState: true,
            }
        );
    } else {
        // Add to saved
        router.post(
            route("client.properties.save", propertySlug),
            {},
            {
                preserveScroll: true,
                preserveState: true,
            }
        );
    }
};

const togglePropertySelection = (propertyId) => {
    const index = selectedProperties.value.indexOf(propertyId);
    if (index > -1) {
        selectedProperties.value.splice(index, 1);
    } else {
        selectedProperties.value.push(propertyId);
    }
};

const compareProperties = () => {
    if (selectedProperties.value.length < 2) return;
    // Navigate to comparison page
    router.get(
        route("client.properties.compare", {
            ids: selectedProperties.value.join(","),
        })
    );
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const getImageUrl = (image) => {
    if (!image) {
        return "/images/placeholder-property.jpg";
    }

    if (typeof image === "string") {
        if (image.startsWith("http")) {
            return image;
        }
        // Check if the path already starts with /storage/ to avoid double prefix
        if (image.startsWith("/storage/")) {
            return image;
        }
        // Check if it's a data URL (SVG placeholder)
        if (image.startsWith("data:")) {
            return image;
        }
        return `/storage/${image}`;
    }

    return "/images/placeholder-property.jpg";
};

const getPropertyTypeIcon = (type) => {
    const icons = {
        house: HomeIcon,
        land: MapIcon,
        condo: BuildingOfficeIcon,
        villa: HomeIcon,
    };
    return icons[type] || BuildingOfficeIcon;
};

// Status badge helpers
const getStatusBadgeClass = (status) => {
    switch ((status || "").toLowerCase()) {
        case "sold":
            return "bg-red-100 text-red-700 border-red-200";
        case "under_contract":
        case "under-contract":
            return "bg-yellow-100 text-yellow-700 border-yellow-200";
        case "active":
        default:
            return "bg-green-100 text-green-700 border-green-200";
    }
};

const isUnavailable = (status) => {
    const s = (status || "").toLowerCase();
    return s === "sold" || s === "under_contract" || s === "finalized";
};

// Debounced search
let searchTimeout = null;
const debouncedSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        search();
    }, 500); // Wait 500ms after user stops typing
};

// Auto-search when filters change (debounced)
watch(
    form,
    () => {
        debouncedSearch();
    },
    { deep: true }
);
</script>

<template>
    <Head title="Property Search - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Header Section -->
        <div class="bg-white border border-neutral-200 rounded-lg p-6 mb-6">
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <h1 class="text-2xl font-semibold mb-2 text-neutral-900">
                        {{
                            isSavedView
                                ? "Your Saved Properties"
                                : "Property Search"
                        }}
                    </h1>
                    <p class="text-neutral-600 text-base">
                        {{
                            isSavedView
                                ? "Properties you've saved for later viewing"
                                : "Find your perfect property in Bohol"
                        }}
                    </p>
                    <p class="text-neutral-500 text-sm mt-1">
                        {{
                            isSavedView
                                ? `${properties.length || 0} saved properties`
                                : `${
                                      properties.total || 0
                                  } properties available`
                        }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        v-if="hasActiveFilters"
                        @click="saveSearch"
                        class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-md font-medium transition-colors flex items-center gap-2"
                        :disabled="isSavingSearch"
                    >
                        <StarIcon class="w-4 h-4" />
                        {{ isSavingSearch ? "Saving..." : "Save Search" }}
                    </button>
                    <button
                        @click="showFilters = !showFilters"
                        class="bg-white border border-neutral-300 text-neutral-700 hover:bg-neutral-50 px-4 py-2 rounded-md font-medium transition-colors flex items-center gap-2"
                    >
                        <FunnelIcon class="w-4 h-4" />
                        {{ showFilters ? "Hide" : "Show" }} Filters
                    </button>
                    <button
                        @click="showMap = !showMap"
                        class="bg-white border border-neutral-300 text-neutral-700 hover:bg-neutral-50 px-4 py-2 rounded-md font-medium transition-colors flex items-center gap-2"
                    >
                        <MapIcon class="w-4 h-4" />
                        {{ showMap ? "List" : "Map" }} View
                    </button>
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div
            v-if="showFilters"
            class="bg-white border border-neutral-200 rounded-lg p-6 mb-6"
        >
            <div class="flex items-center justify-between mb-6">
                <h2
                    class="text-xl font-bold text-neutral-900 flex items-center gap-2"
                >
                    <AdjustmentsHorizontalIcon class="w-5 h-5" />
                    Advanced Search Filters
                </h2>
                <button
                    @click="clearFilters"
                    class="text-neutral-500 hover:text-neutral-700 text-sm font-medium flex items-center gap-1"
                >
                    <XMarkIcon class="w-4 h-4" />
                    Clear All
                </button>
            </div>

            <form
                @submit.prevent="search"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"
            >
                <!-- Search Input -->
                <div class="lg:col-span-2">
                    <label
                        class="block text-sm font-semibold text-neutral-700 mb-2"
                        >Search Properties</label
                    >
                    <div class="relative">
                        <MagnifyingGlassIcon
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400"
                        />
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Search by title, location, or description..."
                            class="w-full pl-10 pr-4 py-3 border border-neutral-200 rounded-2xl focus:border-primary-500 focus:ring-primary-500 focus:outline-none"
                        />
                    </div>
                </div>

                <!-- Property Type -->
                <div>
                    <label
                        class="block text-sm font-semibold text-neutral-700 mb-2"
                        >Property Type</label
                    >
                    <select
                        v-model="form.type"
                        class="w-full px-4 py-3 border border-neutral-200 rounded-2xl focus:border-primary-500 focus:ring-primary-500 focus:outline-none"
                    >
                        <option value="">All Types</option>
                        <option v-for="type in types" :key="type" :value="type">
                            {{
                                type
                                    .replace(/_/g, " ")
                                    .replace(/\b\w/g, (l) => l.toUpperCase())
                            }}
                        </option>
                    </select>
                </div>

                <!-- Location -->
                <div>
                    <label
                        class="block text-sm font-semibold text-neutral-700 mb-2"
                        >Location</label
                    >
                    <select
                        v-model="form.municipality"
                        class="w-full px-4 py-3 border border-neutral-200 rounded-2xl focus:border-primary-500 focus:ring-primary-500 focus:outline-none"
                    >
                        <option value="">All Locations</option>
                        <option
                            v-for="municipality in municipalities"
                            :key="municipality"
                            :value="municipality"
                        >
                            {{ municipality }}
                        </option>
                    </select>
                </div>

                <!-- Price Range -->
                <div>
                    <label
                        class="block text-sm font-semibold text-neutral-700 mb-2"
                        >Min Price</label
                    >
                    <div class="relative">
                        <CurrencyDollarIcon
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400"
                        />
                        <input
                            v-model="form.min_price"
                            type="number"
                            placeholder="0"
                            class="w-full pl-10 pr-4 py-3 border border-neutral-200 rounded-2xl focus:border-primary-500 focus:ring-primary-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label
                        class="block text-sm font-semibold text-neutral-700 mb-2"
                        >Max Price</label
                    >
                    <div class="relative">
                        <CurrencyDollarIcon
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400"
                        />
                        <input
                            v-model="form.max_price"
                            type="number"
                            placeholder="No limit"
                            class="w-full pl-10 pr-4 py-3 border border-neutral-200 rounded-2xl focus:border-primary-500 focus:ring-primary-500 focus:outline-none"
                        />
                    </div>
                </div>

                <!-- Area Range -->
                <div>
                    <label
                        class="block text-sm font-semibold text-neutral-700 mb-2"
                        >Min Area (sqm)</label
                    >
                    <input
                        v-model="form.min_area"
                        type="number"
                        step="1"
                        placeholder="0"
                        class="w-full px-4 py-3 border border-neutral-200 rounded-2xl focus:border-primary-500 focus:ring-primary-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label
                        class="block text-sm font-semibold text-neutral-700 mb-2"
                        >Max Area (sqm)</label
                    >
                    <input
                        v-model="form.max_area"
                        type="number"
                        step="1"
                        placeholder="No limit"
                        class="w-full px-4 py-3 border border-neutral-200 rounded-2xl focus:border-primary-500 focus:ring-primary-500 focus:outline-none"
                    />
                </div>

                <!-- Special Features -->
                <div class="lg:col-span-4">
                    <label
                        class="block text-sm font-semibold text-neutral-700 mb-3"
                        >Special Features</label
                    >
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.utilities"
                                type="checkbox"
                                class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500"
                            />
                            <span class="text-sm text-neutral-700"
                                >Utilities Available</span
                            >
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.featured"
                                type="checkbox"
                                class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500"
                            />
                            <span class="text-sm text-neutral-700"
                                >Featured Properties</span
                            >
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.virtual_tour"
                                type="checkbox"
                                class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500"
                            />
                            <span class="text-sm text-neutral-700"
                                >Virtual Tour Available</span
                            >
                        </label>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Header -->
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6"
        >
            <div class="flex items-center gap-4">
                <h2 class="text-2xl font-bold text-neutral-900">
                    {{ filteredProperties.length }} Properties Found
                </h2>
                <div
                    v-if="selectedProperties.length > 0"
                    class="flex items-center gap-2"
                >
                    <span class="text-sm text-neutral-600"
                        >{{ selectedProperties.length }} selected</span
                    >
                    <button
                        @click="compareProperties"
                        :disabled="selectedProperties.length < 2"
                        class="bg-primary-600 hover:bg-primary-700 disabled:bg-neutral-300 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        Compare Properties
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <!-- Sort Options -->
                <select
                    v-model="sortBy"
                    class="px-4 py-2 border border-neutral-200 rounded-lg focus:border-primary-500 focus:ring-primary-500 focus:outline-none"
                >
                    <option value="relevance">Sort by Relevance</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="area_large">Area: Largest First</option>
                    <option value="area_small">Area: Smallest First</option>
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                </select>

                <!-- View Mode Toggle -->
                <div class="flex items-center bg-neutral-100 rounded-lg p-1">
                    <button
                        @click="viewMode = 'grid'"
                        :class="
                            viewMode === 'grid'
                                ? 'bg-white shadow-sm'
                                : 'text-neutral-500'
                        "
                        class="p-2 rounded-md transition-all"
                    >
                        <Squares2X2Icon class="w-5 h-5" />
                    </button>
                    <button
                        @click="viewMode = 'list'"
                        :class="
                            viewMode === 'list'
                                ? 'bg-white shadow-sm'
                                : 'text-neutral-500'
                        "
                        class="p-2 rounded-md transition-all"
                    >
                        <ListBulletIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Map View -->
        <div
            v-if="showMap && !isLoading"
            class="bg-white border border-neutral-200 rounded-lg p-4 mb-6"
        >
            <div class="h-[600px]">
                <PropertyMapView
                    :properties="filteredProperties"
                    @property-click="handlePropertyClick"
                />
            </div>
        </div>

        <!-- Loading State -->
        <div
            v-if="isLoading && !showMap"
            :class="
                viewMode === 'grid'
                    ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6'
                    : 'space-y-4'
            "
        >
            <LoadingSkeleton v-for="n in 6" :key="n" type="property-card" />
        </div>

        <!-- Properties Grid/List -->
        <div
            v-else-if="filteredProperties.length > 0 && !showMap"
            :class="
                viewMode === 'grid'
                    ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6'
                    : 'space-y-4'
            "
        >
            <div
                v-for="property in filteredProperties"
                :key="property.id"
                :class="
                    viewMode === 'grid'
                        ? 'group cursor-pointer'
                        : 'flex gap-6 p-6 bg-white border border-neutral-200 rounded-lg hover:shadow-md transition-shadow duration-200'
                "
            >
                <!-- Grid View -->
                <div
                    v-if="viewMode === 'grid'"
                    class="bg-white border border-neutral-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200"
                >
                    <!-- Property Image -->
                    <div class="relative h-56 overflow-hidden">
                        <img
                            :src="getImageUrl(property.main_image)"
                            :alt="property.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        />
                        <div
                            class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center pointer-events-none"
                        >
                            <div
                                class="opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                            >
                                <EyeIcon class="w-8 h-8 text-white" />
                            </div>
                        </div>
                        <!-- Availability Badge -->
                        <div class="absolute bottom-4 left-4 z-10">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-semibold border"
                                :class="getStatusBadgeClass(property.status)"
                            >
                                {{
                                    (property.status || "active").replace(
                                        "_",
                                        " "
                                    )
                                }}
                            </span>
                        </div>
                        <!-- Top badges -->
                        <div class="absolute top-3 left-3 z-10">
                            <span
                                v-if="property.has_virtual_tour"
                                class="bg-blue-600 text-white px-2.5 py-1 rounded-md text-xs font-medium"
                            >
                                Panorama
                            </span>
                        </div>

                        <!-- Action buttons - simplified -->
                        <div
                            class="absolute top-3 right-3 z-20 flex flex-col gap-2"
                        >
                            <button
                                @click.stop="
                                    toggleSavedProperty(
                                        property.id,
                                        property.slug
                                    )
                                "
                                :class="
                                    savedPropertyIds.includes(property.id)
                                        ? 'bg-red-500 text-white hover:bg-red-600'
                                        : 'bg-white/90 text-gray-700 hover:bg-white'
                                "
                                class="p-2 rounded-lg backdrop-blur-sm transition-colors duration-150 shadow-sm"
                                :title="
                                    savedPropertyIds.includes(property.id)
                                        ? 'Remove from saved'
                                        : 'Save property'
                                "
                            >
                                <HeartIcon
                                    :class="
                                        savedPropertyIds.includes(property.id)
                                            ? 'fill-current'
                                            : ''
                                    "
                                    class="w-5 h-5"
                                />
                            </button>
                            <button
                                @click.stop="
                                    togglePropertySelection(property.id)
                                "
                                :class="
                                    selectedProperties.includes(property.id)
                                        ? 'bg-blue-600 text-white hover:bg-blue-700'
                                        : 'bg-white/90 text-gray-700 hover:bg-white'
                                "
                                class="p-2 rounded-lg backdrop-blur-sm transition-colors duration-150 shadow-sm"
                                :title="
                                    selectedProperties.includes(property.id)
                                        ? 'Remove from comparison'
                                        : 'Add to comparison'
                                "
                            >
                                <CheckCircleIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Property Details -->
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-3">
                            <h3
                                class="text-lg font-bold text-neutral-900 line-clamp-1 group-hover:text-primary-600 transition-colors"
                            >
                                {{ property.title }}
                            </h3>
                            <component
                                :is="getPropertyTypeIcon(property.type)"
                                class="w-5 h-5 text-neutral-400 flex-shrink-0 ml-2"
                            />
                        </div>

                        <div class="flex items-center text-neutral-600 mb-3">
                            <MapPinIcon class="w-4 h-4 mr-1" />
                            <span class="text-sm">{{
                                property.municipality
                            }}</span>
                        </div>

                        <div class="mb-4">
                            <p class="text-2xl font-bold text-blue-600">
                                {{ formatCurrency(property.total_price) }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ property.lot_area_sqm?.toLocaleString() }}
                                sqm
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Link
                                :href="
                                    route('client.properties.show', property.id)
                                "
                                :class="[
                                    'flex-1 text-center py-2 px-4 rounded-lg text-sm font-medium transition-colors',
                                    isUnavailable(property.status)
                                        ? 'bg-gray-300 text-white cursor-not-allowed'
                                        : 'bg-blue-600 hover:bg-blue-700 text-white',
                                ]"
                                :aria-disabled="isUnavailable(property.status)"
                                :tabindex="
                                    isUnavailable(property.status) ? -1 : 0
                                "
                            >
                                View Details
                            </Link>
                            <Link
                                :href="
                                    route('client.inquiries.create', {
                                        property_id: property.id,
                                    })
                                "
                                :class="[
                                    'py-2 px-4 rounded-lg text-sm font-medium transition-colors',
                                    isUnavailable(property.status)
                                        ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                        : 'bg-gray-100 hover:bg-gray-200 text-gray-700',
                                ]"
                                :aria-disabled="isUnavailable(property.status)"
                                :tabindex="
                                    isUnavailable(property.status) ? -1 : 0
                                "
                            >
                                Inquire
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div v-else class="flex-1">
                    <div class="flex items-center gap-4">
                        <img
                            :src="getImageUrl(property.main_image)"
                            :alt="property.title"
                            class="w-32 h-24 object-cover rounded-lg flex-shrink-0"
                        />
                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-lg font-bold text-neutral-900">
                                    {{ property.title }}
                                </h3>
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="
                                            toggleSavedProperty(
                                                property.id,
                                                property.slug
                                            )
                                        "
                                        :class="
                                            savedPropertyIds.includes(
                                                property.id
                                            )
                                                ? 'text-red-500'
                                                : 'text-neutral-400 hover:text-red-500'
                                        "
                                        class="p-1 rounded transition-colors"
                                    >
                                        <HeartIcon
                                            :class="
                                                savedPropertyIds.includes(
                                                    property.id
                                                )
                                                    ? 'fill-current'
                                                    : ''
                                            "
                                            class="w-5 h-5"
                                        />
                                    </button>
                                    <button
                                        @click="
                                            togglePropertySelection(property.id)
                                        "
                                        :class="
                                            selectedProperties.includes(
                                                property.id
                                            )
                                                ? 'bg-primary-600 text-white'
                                                : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200'
                                        "
                                        class="p-1 rounded transition-all"
                                    >
                                        <CheckCircleIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                            <div
                                class="flex items-center text-neutral-600 mb-2"
                            >
                                <MapPinIcon class="w-4 h-4 mr-1" />
                                <span class="text-sm">{{
                                    property.municipality
                                }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xl font-bold text-blue-600">
                                        {{
                                            formatCurrency(property.total_price)
                                        }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{
                                            property.lot_area_sqm?.toLocaleString()
                                        }}
                                        sqm
                                    </p>
                                    <div class="mt-2">
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-semibold border"
                                            :class="
                                                getStatusBadgeClass(
                                                    property.status
                                                )
                                            "
                                        >
                                            {{
                                                (
                                                    property.status || "active"
                                                ).replace("_", " ")
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="
                                            route(
                                                'client.properties.show',
                                                property.id
                                            )
                                        "
                                        :class="[
                                            'py-2 px-4 rounded-lg text-sm font-medium transition-colors',
                                            isUnavailable(property.status)
                                                ? 'bg-gray-300 text-white cursor-not-allowed'
                                                : 'bg-blue-600 hover:bg-blue-700 text-white',
                                        ]"
                                        :aria-disabled="
                                            isUnavailable(property.status)
                                        "
                                        :tabindex="
                                            isUnavailable(property.status)
                                                ? -1
                                                : 0
                                        "
                                    >
                                        View Details
                                    </Link>
                                    <Link
                                        :href="
                                            route('client.inquiries.create', {
                                                property_id: property.id,
                                            })
                                        "
                                        :class="[
                                            'py-2 px-4 rounded-lg text-sm font-medium transition-colors',
                                            isUnavailable(property.status)
                                                ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                                : 'bg-gray-100 hover:bg-gray-200 text-gray-700',
                                        ]"
                                        :aria-disabled="
                                            isUnavailable(property.status)
                                        "
                                        :tabindex="
                                            isUnavailable(property.status)
                                                ? -1
                                                : 0
                                        "
                                    >
                                        Inquire
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <EmptyState
            v-else
            title="No Properties Found"
            description="Try adjusting your search criteria or browse all available properties"
            :icon="BuildingOfficeIcon"
            :actions="[
                {
                    text: 'Clear All Filters',
                    onClick: clearFilters,
                    variant: 'secondary',
                },
                {
                    text: 'Browse All Properties',
                    href: route('public.properties'),
                    variant: 'primary',
                },
            ]"
        />

        <!-- Pagination -->
        <div
            v-if="properties.links && properties.links.length > 3"
            class="mt-12"
        >
            <nav class="flex items-center justify-center">
                <div class="flex items-center space-x-2">
                    <Link
                        v-for="link in properties.links"
                        :key="link.label"
                        :href="link.url"
                        :class="[
                            'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                            link.active
                                ? 'bg-primary-600 text-white'
                                : 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100',
                        ]"
                    >
                        <span v-html="link.label"></span>
                    </Link>
                </div>
            </nav>
        </div>

        <!-- Floating Comparison Bar -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="translate-y-full opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-full opacity-0"
        >
            <div
                v-if="selectedProperties.length > 0"
                class="fixed bottom-0 left-0 right-0 bg-white border-t-2 border-blue-500 shadow-2xl z-40"
            >
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <CheckCircleIcon
                                    class="w-6 h-6 text-blue-600"
                                />
                                <span class="font-semibold text-neutral-900">
                                    {{ selectedProperties.length }}
                                    {{
                                        selectedProperties.length === 1
                                            ? "property"
                                            : "properties"
                                    }}
                                    selected
                                </span>
                            </div>
                            <button
                                @click="selectedProperties = []"
                                class="text-sm text-neutral-600 hover:text-neutral-900 flex items-center gap-1"
                            >
                                <XMarkIcon class="w-4 h-4" />
                                Clear
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <button
                                v-if="selectedProperties.length >= 2"
                                @click="compareProperties"
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2"
                            >
                                <SparklesIcon class="w-5 h-5" />
                                Compare Properties
                            </button>
                            <p v-else class="text-sm text-neutral-500">
                                Select at least 2 properties to compare
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </ModernDashboardLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-clamp: 1; /* Standard property for compatibility */
}
.shadow-soft-lg {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.shadow-soft-xl {
    box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>
