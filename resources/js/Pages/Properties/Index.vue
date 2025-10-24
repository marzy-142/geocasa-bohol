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

            <!-- Filters Section -->
            <div class="bg-white rounded-md shadow-sm p-4">
                <h3 class="text-base font-semibold text-gray-900 mb-3">
                    Search & Filter Properties
                </h3>

                <!-- Primary Filters -->
                <div
                    class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-3"
                >
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search properties..."
                        class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @input="filterProperties"
                    />
                    <select
                        v-model="filters.type"
                        class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @change="filterProperties"
                    >
                        <option value="">All Types</option>
                        <option v-for="type in types" :key="type" :value="type">
                            {{ formatType(type) }}
                        </option>
                    </select>
                    <select
                        v-model="filters.municipality"
                        class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @change="filterProperties"
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
                    <select
                        v-model="filters.status"
                        class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @change="filterProperties"
                    >
                        <option value="">All Statuses</option>
                        <option
                            v-for="status in statuses"
                            :key="status"
                            :value="status"
                        >
                            {{ formatStatus(status) }}
                        </option>
                    </select>
                    <input
                        v-model="filters.min_price"
                        type="number"
                        placeholder="Min Price (₱)"
                        class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @input="filterProperties"
                    />
                    <input
                        v-model="filters.max_price"
                        type="number"
                        placeholder="Max Price (₱)"
                        class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @input="filterProperties"
                    />
                </div>

                <!-- Secondary Filters -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input
                        v-model="filters.min_area"
                        type="number"
                        placeholder="Min Area (sqm)"
                        class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @input="filterProperties"
                    />
                    <input
                        v-model="filters.max_area"
                        type="number"
                        placeholder="Max Area (sqm)"
                        class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @input="filterProperties"
                    />
                    <label
                        class="flex items-center space-x-2 bg-gray-50 rounded-md px-3 py-1.5"
                    >
                        <input
                            v-model="filters.utilities"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            @change="filterProperties"
                        />
                        <span class="text-sm text-gray-700"
                            >With Utilities</span
                        >
                    </label>
                </div>
            </div>

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
                        v-for="property in properties.data"
                        :key="property.id"
                        class="bg-white rounded-md shadow-sm overflow-hidden hover:shadow-md transition-all duration-200 border border-gray-200 hover:border-blue-300"
                    >
                        <div class="relative">
                            <img
                                :src="property.main_image"
                                :alt="property.title"
                                class="w-full h-40 object-cover"
                            />
                            <div
                                v-if="property.is_featured"
                                class="absolute top-2 left-2 bg-yellow-500 text-white px-2 py-0.5 rounded-full text-xs font-bold shadow-sm"
                            >
                                ⭐ Featured
                            </div>
                            <div
                                class="absolute top-2 right-2 px-2 py-0.5 rounded-full text-xs font-bold text-white shadow-sm"
                                :class="getStatusColor(property.status)"
                            >
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
                                {{ property.title }}
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
                                class="flex justify-between text-xs text-gray-500 mb-2"
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
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                        ></path>
                                    </svg>
                                    {{ formatType(property.type) }}
                                </span>
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
                    v-if="properties.data.length === 0"
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
                    v-if="properties.links && properties.data.length > 0"
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
import { ref, computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { debounce } from "lodash";

const props = defineProps({
    properties: Object,
    filters: Object,
    types: Array,
    statuses: Array,
    municipalities: Array,
});

const page = usePage();

// Initialize filters with proper defaults
const filters = ref({
    search: props.filters.search || "",
    type: props.filters.type || "",
    municipality: props.filters.municipality || "",
    status: props.filters.status || "",
    min_price: props.filters.min_price || "",
    max_price: props.filters.max_price || "",
    min_area: props.filters.min_area || "",
    max_area: props.filters.max_area || "",
    utilities: props.filters.utilities || false,
    featured: props.filters.featured || false,
});

const canCreateProperty = computed(() => {
    const user = page.props.auth.user;
    // Only brokers can create properties, not admins
    return user.role === "broker" && user.is_approved;
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

const filterProperties = debounce(() => {
    // Clean up empty values to avoid sending unnecessary parameters
    const cleanFilters = Object.fromEntries(
        Object.entries(filters.value).filter(([key, value]) => {
            // Keep boolean false values, but remove empty strings and null/undefined
            return value !== "" && value !== null && value !== undefined;
        })
    );

    router.get(route("broker.properties.index"), cleanFilters, {
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
