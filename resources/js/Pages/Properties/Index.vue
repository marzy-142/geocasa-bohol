<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header Section -->
            <div
                class="bg-gradient-to-r from-blue-600 to-teal-600 rounded-md p-4 text-white"
            >
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">Property Management</h1>
                        <p class="text-blue-100 mt-1 text-sm">
                            Manage land properties in GeoCasa Bohol
                        </p>
                    </div>
                    <Link
                        v-if="canCreateProperty"
                        :href="route('broker.properties.create')"
                        class="bg-white text-blue-600 hover:bg-blue-50 font-semibold py-2 px-4 rounded-md transition-colors duration-200 shadow-sm"
                    >
                        <span class="flex items-center">
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
                                    d="M12 4v16m8-8H4"
                                ></path>
                            </svg>
                            Add Property
                        </span>
                    </Link>
                </div>
            </div>

            <!-- Unified Search & Filter -->
            <UnifiedSearchFilter
                title="Search & Filter Properties"
                :search="filters.search"
                search-placeholder="Search properties by title, location, or description..."
                :filters="filters"
                :result-count="properties.total"
                :primary-filters="primaryFilters"
                :secondary-filters="secondaryFilters"
                @search-change="handleSearchChange"
                @filter-change="handleFilterChange"
                @clear-filters="clearAllFilters"
            />

            <!-- Properties Grid -->
            <div class="bg-white rounded-md shadow-sm p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        Properties ({{ properties.total || 0 }})
                    </h3>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                >
                    <div
                        v-for="property in safeProperties"
                        :key="property.id || property.slug || property.title"
                        class="bg-white rounded-md shadow-sm overflow-hidden hover:shadow-md transition-all duration-200 border border-gray-200 hover:border-blue-300"
                    >
                        <div class="relative">
                            <img
                                v-if="property.main_image"
                                :src="property.main_image"
                                :alt="property.title || 'Property Image'"
                                class="w-full h-40 object-cover"
                            />
                            <div
                                v-else
                                class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-400 text-xs"
                            >
                                No Image
                            </div>
                            <div
                                v-if="property.is_featured"
                                class="absolute top-2 left-2 bg-yellow-500 text-white px-2 py-0.5 rounded-full text-xs font-bold shadow-sm"
                            >
                                ⭐ Featured
                            </div>
                            <div
                                class="absolute top-2 right-2 px-2.5 py-1 rounded-lg text-xs font-semibold shadow-md border"
                                :class="getStatusColor(property.status)"
                            >
                                {{ getStatusIcon(property.status) }}
                                {{ formatStatus(property.status) }}
                            </div>
                            <div
                                v-if="property.type === 'beachfront'"
                                class="absolute bottom-2 left-2 bg-blue-500 text-white px-2 py-0.5 rounded-full text-xs shadow-sm"
                            >
                                🏖️ Beachfront
                            </div>
                        </div>
                        <div class="p-3">
                            <h3
                                class="text-base font-semibold text-gray-900 mb-1.5 line-clamp-2"
                            >
                                {{ property.title || "Untitled Property" }}
                            </h3>
                            <p
                                class="text-gray-600 text-xs mb-2 flex items-center"
                            >
                                <svg
                                    class="w-3 h-3 mr-1"
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
                                {{ property.municipality }}, Bohol
                            </p>
                            <div class="mb-3">
                                <p class="text-lg font-bold text-green-600">
                                    {{ property.formatted_total_price }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ property.formatted_price_per_sqm }}/sqm
                                </p>
                            </div>
                            <div
                                class="flex justify-between items-center text-xs text-gray-500 mb-2"
                            >
                                <span class="flex items-center">
                                    <svg
                                        class="w-3 h-3 mr-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"
                                        ></path>
                                    </svg>
                                    {{ property.formatted_area }}
                                </span>
                            </div>

                            <!-- Property Type Badges -->
                            <div class="mb-2">
                                <PropertyTypeBadges
                                    :types="property.formatted_types"
                                    :custom-type-text="
                                        property.custom_type_text
                                    "
                                    :max-display="2"
                                />
                            </div>

                            <!-- Utilities Icons -->
                            <div class="flex flex-wrap gap-1 mb-2">
                                <span
                                    v-if="property.road_access"
                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-green-100 text-green-800"
                                >
                                    🛣️ Road
                                </span>
                                <span
                                    v-if="property.electricity_available"
                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-yellow-100 text-yellow-800"
                                >
                                    ⚡ Power
                                </span>
                                <span
                                    v-if="property.water_source"
                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-blue-100 text-blue-800"
                                >
                                    💧 Water
                                </span>
                                <span
                                    v-if="property.internet_available"
                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-purple-100 text-purple-800"
                                >
                                    📶 Net
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <Link
                                    v-if="property.slug"
                                    :href="
                                        route(
                                            'broker.properties.show',
                                            property.slug
                                        )
                                    "
                                    class="text-blue-600 hover:text-blue-800 font-medium text-xs transition-colors duration-200"
                                >
                                    View Details →
                                </Link>
                                <span v-else class="text-gray-400 text-xs"
                                    >No details</span
                                >
                                <div
                                    v-if="canEditProperty(property)"
                                    class="flex space-x-2"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'broker.properties.edit',
                                                property.slug
                                            )
                                        "
                                        class="text-green-600 hover:text-green-800 text-xs font-medium transition-colors duration-200"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteProperty(property)"
                                        class="text-red-600 hover:text-red-800 text-xs font-medium transition-colors duration-200"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Results -->
                <div
                    v-if="safeProperties.length === 0"
                    class="text-center py-12"
                >
                    <div class="text-gray-400 text-4xl mb-3">🏞️</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        No properties found
                    </h3>
                    <p class="text-gray-500 mb-4 text-sm">
                        Try adjusting your search filters or add a new property.
                    </p>
                    <Link
                        v-if="canCreateProperty"
                        :href="route('broker.properties.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors duration-200 text-sm"
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
                                d="M12 4v16m8-8H4"
                            ></path>
                        </svg>
                        Add Your First Property
                    </Link>
                </div>

                <!-- Pagination -->
                <div
                    v-if="properties.links && safeProperties.length > 0"
                    class="mt-6 border-t border-gray-200 pt-4"
                >
                    <Pagination
                        :links="properties.links"
                        :from="properties.from"
                        :to="properties.to"
                        :total="properties.total"
                    />
                </div>
            </div>

            <!-- Bohol Inspiration Section -->
            <div
                class="bg-gradient-to-r from-green-400 to-blue-500 rounded-md p-4 text-white"
            >
                <div class="text-center">
                    <h3 class="text-lg font-bold mb-1">
                        🏝️ Discover Bohol's Beauty
                    </h3>
                    <p class="text-green-100 text-sm">
                        From pristine beaches to rolling hills, find your
                        perfect piece of paradise in Bohol
                    </p>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import UnifiedSearchFilter from "@/Components/UnifiedSearchFilter.vue";
import Pagination from "@/Components/Pagination.vue";
import PropertyTypeBadges from "@/Components/PropertyTypeBadges.vue";
// Lightweight debounce to avoid extra dependency
function debounce(fn, wait = 300) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), wait);
    };
}

const props = defineProps({
    properties: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    types: { type: Array, default: () => [] },
    statuses: { type: Array, default: () => [] },
    municipalities: { type: Array, default: () => [] },
    brokers: { type: Array, default: () => [] },
    isAdminView: { type: Boolean, default: false },
});

const page = usePage();

// Defensive computed to avoid nulls from backend or during reactive updates
const safeProperties = computed(() => {
    const list =
        props.properties && Array.isArray(props.properties.data)
            ? props.properties.data
            : [];
    const filtered = list.filter((p) => p && (p.title || p.slug || p.id));
    if (filtered.length !== list.length) {
        console.warn("[Properties/Index] Filtered out null/incomplete items", {
            original: list.length,
            kept: filtered.length,
        });
    }
    return filtered;
});

// Initialize filters with proper defaults (type is single-select)
const filters = ref({
    search: props.filters.search ?? "",
    // Normalize to single string even if server sent an array
    type: normalizeTypeToString(props.filters.type ?? props.filters.types),
    municipality: props.filters.municipality ?? "",
    status: props.filters.status ?? "",
    broker_id: props.filters.broker_id ?? "",
    min_price: props.filters.min_price ?? "",
    max_price: props.filters.max_price ?? "",
    min_area: props.filters.min_area ?? "",
    max_area: props.filters.max_area ?? "",
    utilities: props.filters.utilities ?? false,
    featured: props.filters.featured ?? false,
});

// Helper: normalize type(s) input to a single string (first value wins)
function normalizeTypeToString(input) {
    if (!input) return "";
    if (Array.isArray(input)) {
        return input.length > 0 ? String(input[0]) : "";
    }
    if (typeof input === "string") return input;
    return "";
}

// Watch for prop changes and sync local filters (important for page navigation)
watch(
    () => props.filters,
    (newFilters) => {
        filters.value = {
            search: newFilters.search ?? "",
            type: normalizeTypeToString(newFilters.type ?? newFilters.types),
            municipality: newFilters.municipality ?? "",
            status: newFilters.status ?? "",
            broker_id: newFilters.broker_id ?? "",
            min_price: newFilters.min_price ?? "",
            max_price: newFilters.max_price ?? "",
            min_area: newFilters.min_area ?? "",
            max_area: newFilters.max_area ?? "",
            utilities: newFilters.utilities ?? false,
            featured: newFilters.featured ?? false,
        };
    },
    { deep: true }
);

// Filter configurations for UnifiedSearchFilter
const primaryFilters = computed(() => {
    const list = [
        {
            key: "type",
            label: "Property Type",
            type: "select",
            span: 2,
            placeholder: "All Property Types",
            options: props.types, // Already formatted from backend
        },
        {
            key: "municipality",
            label: "Municipality",
            type: "select",
            span: 2,
            options: props.municipalities.map((municipality) => ({
                value: municipality,
                label: municipality,
            })),
        },
        {
            key: "status",
            label: "Status",
            type: "select",
            span: 2,
            options: props.statuses.map((status) => ({
                value: status,
                label: formatStatus(status),
            })),
        },
    ];

    // Add broker filter for admin view only
    if (props.isAdminView && props.brokers && props.brokers.length > 0) {
        list.push({
            key: "broker_id",
            label: "Broker",
            type: "select",
            span: 2,
            placeholder: "All Brokers",
            options: props.brokers.map((broker) => ({
                value: broker.value,
                label: `${broker.label} (${broker.property_count || 0}) - ${
                    broker.property_types || "No types"
                }`,
            })),
        });
    }

    return list;
});

const secondaryFilters = computed(() => [
    {
        key: "min_price",
        label: "Min Price",
        type: "number",
        placeholder: "Minimum price (₱)",
    },
    {
        key: "max_price",
        label: "Max Price",
        type: "number",
        placeholder: "Maximum price (₱)",
    },
    {
        key: "min_area",
        label: "Min Area",
        type: "number",
        placeholder: "Minimum area (sqm)",
    },
    {
        key: "max_area",
        label: "Max Area",
        type: "number",
        placeholder: "Maximum area (sqm)",
    },
]);

// Event handlers for UnifiedSearchFilter
const handleSearchChange = (value) => {
    filters.value.search = value;
    filterProperties();
};

const handleFilterChange = (key, value) => {
    filters.value[key] = key === "type" ? normalizeTypeToString(value) : value;
    filterProperties();
};

const clearAllFilters = () => {
    filters.value = {
        search: "",
        type: "",
        municipality: "",
        status: "",
        broker_id: "",
        min_price: "",
        max_price: "",
        min_area: "",
        max_area: "",
        utilities: false,
        featured: false,
    };
    filterProperties();
};
const canCreateProperty = computed(() => {
    const user = page.props.auth.user;
    // Only brokers can create properties, not admins
    return user && user.role === "broker" && user.is_approved;
});

const canEditProperty = (property) => {
    const user = page.props.auth.user;
    return (
        user.role === "admin" ||
        (user.role === "broker" &&
            user.is_approved &&
            property.broker_id === user.id)
    );
};

const getStatusColor = (status) => {
    const colors = {
        available: "bg-green-100 text-green-800 border-green-200",
        reserved: "bg-amber-100 text-amber-800 border-amber-200",
        sold: "bg-gray-100 text-gray-800 border-gray-300",
        under_negotiation: "bg-blue-100 text-blue-800 border-blue-200",
        off_market: "bg-gray-100 text-gray-600 border-gray-200",
    };
    return colors[status] || "bg-gray-100 text-gray-600 border-gray-200";
};

const getStatusIcon = (status) => {
    const icons = {
        available: "✓",
        reserved: "🔒",
        sold: "✓",
        under_negotiation: "💼",
        off_market: "—",
    };
    return icons[status] || "•";
};

const formatType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatStatus = (status) => {
    const labels = {
        available: "Available",
        reserved: "Reserved",
        sold: "Sold",
        under_negotiation: "Under Transaction",
        off_market: "Off Market",
    };
    return (
        labels[status] ||
        status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase())
    );
};

const filterProperties = debounce(() => {
    // Clean up empty values to avoid sending unnecessary parameters
    const cleanFilters = Object.fromEntries(
        Object.entries(filters.value).filter(([key, value]) => {
            // Include arrays only if they have values
            if (Array.isArray(value)) {
                return value.length > 0;
            }
            // Only include booleans if true
            if (typeof value === "boolean") {
                return value === true;
            }
            // Exclude empty strings and null/undefined
            return value !== "" && value !== null && value !== undefined;
        })
    );

    // Always send types[] to the backend for consistent handling (custom and standard)
    if (typeof cleanFilters.type === "string" && cleanFilters.type !== "") {
        cleanFilters.types = [cleanFilters.type];
        delete cleanFilters.type;
    }

    console.log("Filtering properties with:", cleanFilters);

    // Determine correct route based on user role
    const routeName = props.isAdminView
        ? "admin.properties.index"
        : "broker.properties.index";

    router.get(route(routeName), cleanFilters, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            // Ensure filters stay in sync after successful request
            console.log("Filters applied successfully:", cleanFilters);
        },
    });
}, 300);
const deleteProperty = (property) => {
    if (confirm("Are you sure you want to delete this land property?")) {
        router.delete(route("broker.properties.destroy", property.slug));
    }
};
</script>
