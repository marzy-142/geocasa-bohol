<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header Section -->
            <div
                class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-white"
            >
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold">Inquiry Management</h1>
                        <p class="text-blue-100 mt-2">
                            Manage property inquiries from clients in GeoCasa
                            Bohol
                        </p>
                        <p class="text-blue-200 text-sm mt-1">
                            <svg
                                class="w-4 h-4 inline mr-1"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                            Inquiries are submitted by clients through public
                            property pages
                        </p>
                    </div>
                    <!-- Note: No "Add New Inquiry" button since inquiries are created by clients -->
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Search & Filter Inquiries
                </h3>

                <!-- Primary Filters -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4"
                >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search inquiries..."
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @input="applyFilters"
                    />
                    <select
                        v-model="selectedStatus"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @change="applyFilters"
                    >
                        <option value="">All Statuses</option>
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="closed">Closed</option>
                    </select>
                    <select
                        v-model="selectedType"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @change="applyFilters"
                    >
                        <option value="">All Types</option>
                        <option value="general">General</option>
                        <option value="viewing">Viewing</option>
                        <option value="purchase">Purchase</option>
                        <option value="information">Information</option>
                    </select>
                    <select
                        v-model="selectedProperty"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @change="applyFilters"
                    >
                        <option value="">All Properties</option>
                        <option
                            v-for="property in properties"
                            :key="property.id"
                            :value="property.id"
                        >
                            {{ property.title }} - {{ property.municipality }}
                        </option>
                    </select>
                </div>

                <!-- Secondary Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <input
                        v-model="dateFrom"
                        type="date"
                        placeholder="Date From"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @change="applyFilters"
                    />
                    <input
                        v-model="dateTo"
                        type="date"
                        placeholder="Date To"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @change="applyFilters"
                    />
                </div>

                <!-- Clear Filters -->
                <div class="mt-4">
                    <button
                        @click="clearFilters"
                        class="text-sm text-gray-600 hover:text-gray-800 font-medium"
                    >
                        Clear all filters
                    </button>
                </div>
            </div>

            <!-- Inquiries Grid -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Inquiries ({{ inquiries.total || 0 }})
                    </h3>
                </div>

                <div
                    v-if="inquiries.data.length > 0"
                    class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6"
                >
                    <div
                        v-for="inquiry in inquiries.data"
                        :key="inquiry.id"
                        class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow"
                    >
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ inquiry.name }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        {{ inquiry.email }}
                                    </p>
                                    <div class="flex space-x-2 mt-2">
                                        <span
                                            :class="
                                                getStatusColor(inquiry.status)
                                            "
                                            class="inline-block px-2 py-1 text-xs font-medium rounded-full"
                                        >
                                            {{ inquiry.status }}
                                        </span>
                                        <span
                                            :class="
                                                getTypeColor(
                                                    inquiry.inquiry_type
                                                )
                                            "
                                            class="inline-block px-2 py-1 text-xs font-medium rounded-full"
                                        >
                                            {{ inquiry.inquiry_type }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Info -->
                            <div class="space-y-2 mb-4">
                                <div
                                    class="flex items-center text-sm text-gray-600"
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
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                        ></path>
                                    </svg>
                                    <span class="font-medium">{{
                                        inquiry.property.title
                                    }}</span>
                                </div>
                                <div
                                    class="flex items-center text-sm text-gray-600"
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
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        ></path>
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                        ></path>
                                    </svg>
                                    <span>{{
                                        inquiry.property.municipality
                                    }}</span>
                                </div>
                            </div>

                            <!-- Message Preview -->
                            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                <p class="text-sm text-gray-700 line-clamp-3">
                                    {{ inquiry.message }}
                                </p>
                            </div>

                            <!-- Timeline Info -->
                            <div class="text-xs text-gray-500 mb-4">
                                <p>
                                    <span class="font-medium">Submitted:</span>
                                    {{
                                        new Date(
                                            inquiry.created_at
                                        ).toLocaleDateString()
                                    }}
                                </p>
                                <p v-if="inquiry.contacted_at">
                                    <span class="font-medium">Contacted:</span>
                                    {{
                                        new Date(
                                            inquiry.contacted_at
                                        ).toLocaleDateString()
                                    }}
                                </p>
                                <p v-if="inquiry.responded_at">
                                    <span class="font-medium">Responded:</span>
                                    {{
                                        new Date(
                                            inquiry.responded_at
                                        ).toLocaleDateString()
                                    }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-between items-center">
                                <Link
                                    :href="route('inquiries.show', inquiry.id)"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    View Details
                                </Link>
                                <div class="flex space-x-2">
                                    <Link
                                        v-if="can.respond"
                                        :href="
                                            route('inquiries.edit', inquiry.id)
                                        "
                                        class="text-gray-600 hover:text-gray-800 text-sm"
                                    >
                                        Respond
                                    </Link>
                                    <button
                                        v-if="can.delete"
                                        @click="deleteInquiry(inquiry)"
                                        class="text-red-600 hover:text-red-800 text-sm"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">📧</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        No inquiries found
                    </h3>
                    <p class="text-gray-500">
                        Inquiries will appear here when clients submit them
                        through property pages.
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="inquiries.data.length > 0" class="mt-6">
                    <Pagination :links="inquiries.links" />
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Link } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    inquiries: Object,
    properties: Array,
    filters: Object,
    can: Object,
});

const search = ref(props.filters.search || "");
const selectedStatus = ref(props.filters.status || "");
const selectedType = ref(props.filters.inquiry_type || "");
const selectedProperty = ref(props.filters.property_id || "");
const dateFrom = ref(props.filters.date_from || "");
const dateTo = ref(props.filters.date_to || "");

const applyFilters = () => {
    router.get(
        route("inquiries.index"),
        {
            search: search.value,
            status: selectedStatus.value,
            inquiry_type: selectedType.value,
            property_id: selectedProperty.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const clearFilters = () => {
    search.value = "";
    selectedStatus.value = "";
    selectedType.value = "";
    selectedProperty.value = "";
    dateFrom.value = "";
    dateTo.value = "";
    applyFilters();
};

const getStatusColor = (status) => {
    const colors = {
        new: "bg-blue-100 text-blue-800",
        contacted: "bg-yellow-100 text-yellow-800",
        scheduled: "bg-purple-100 text-purple-800",
        completed: "bg-green-100 text-green-800",
        closed: "bg-gray-100 text-gray-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const getTypeColor = (type) => {
    const colors = {
        general: "bg-gray-100 text-gray-800",
        viewing: "bg-blue-100 text-blue-800",
        purchase: "bg-green-100 text-green-800",
        information: "bg-purple-100 text-purple-800",
    };
    return colors[type] || "bg-gray-100 text-gray-800";
};

const deleteInquiry = (inquiry) => {
    if (confirm("Are you sure you want to delete this inquiry?")) {
        router.delete(route("inquiries.destroy", inquiry.id));
    }
};
</script>
