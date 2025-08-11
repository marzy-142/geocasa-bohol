<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Link } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    sellerRequests: Object,
    brokers: Array,
    filters: Object,
    canManage: Boolean,
    canCreate: Boolean,
});

const searchForm = useForm({
    search: props.filters.search || "",
    status: props.filters.status || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
    property_type: props.filters.property_type || "",
});

const search = () => {
    searchForm.get(route("seller-requests.index"), {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchForm.reset();
    searchForm.get(route("seller-requests.index"));
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(price);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getStatusColor = (status) => {
    const colors = {
        pending: "bg-yellow-100 text-yellow-800",
        under_review: "bg-blue-100 text-blue-800",
        approved: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
        listed: "bg-emerald-100 text-emerald-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const deleteRequest = (request) => {
    if (confirm("Are you sure you want to delete this seller request?")) {
        useForm({}).delete(route("seller-requests.destroy", request.id));
    }
};
</script>

<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header Section -->
            <div
                class="bg-gradient-to-r from-orange-600 to-red-600 rounded-lg p-6 text-white"
            >
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold">
                            Seller Request Management
                        </h1>
                        <p class="text-orange-100 mt-2">
                            Manage property seller requests in GeoCasa Bohol ({{
                                sellerRequests.total || 0
                            }})
                        </p>
                    </div>
                    <Link
                        v-if="canCreate"
                        :href="route('seller-requests.create')"
                        class="bg-white text-orange-600 hover:bg-orange-50 font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-lg"
                    >
                        <span class="flex items-center">
                            <svg
                                class="w-5 h-5 mr-2"
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
                            New Seller Request
                        </span>
                    </Link>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Search & Filter Seller Requests
                </h3>

                <!-- Primary Filters -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4"
                >
                    <input
                        v-model="searchForm.search"
                        type="text"
                        placeholder="Search seller requests..."
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        @input="search"
                    />
                    <select
                        v-model="searchForm.status"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        @change="search"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="under_review">Under Review</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select
                        v-model="searchForm.property_type"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        @change="search"
                    >
                        <option value="">All Property Types</option>
                        <option value="residential">Residential</option>
                        <option value="commercial">Commercial</option>
                        <option value="agricultural">Agricultural</option>
                        <option value="beachfront">Beachfront</option>
                    </select>
                    <input
                        v-model="searchForm.date_from"
                        type="date"
                        placeholder="Date From"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        @change="search"
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

            <!-- Seller Requests Grid -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Seller Requests ({{ sellerRequests.total || 0 }})
                    </h3>
                </div>

                <div
                    v-if="sellerRequests.data.length > 0"
                    class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6"
                >
                    <div
                        v-for="request in sellerRequests.data"
                        :key="request.id"
                        class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow"
                    >
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ request.seller_name }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        {{ request.seller_email }}
                                    </p>
                                    <span
                                        :class="getStatusColor(request.status)"
                                        class="inline-block px-2 py-1 text-xs font-medium rounded-full mt-1 capitalize"
                                    >
                                        {{ request.status.replace("_", " ") }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <p
                                        class="text-lg font-bold text-orange-600"
                                    >
                                        {{ formatPrice(request.asking_price) }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ request.property_area }}
                                        {{ request.area_unit }}
                                    </p>
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
                                        request.property_title
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
                                    <span>{{ request.property_location }}</span>
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
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                        ></path>
                                    </svg>
                                    <span class="capitalize">{{
                                        request.property_type
                                    }}</span>
                                </div>
                            </div>

                            <!-- Assignment Info -->
                            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                <div class="text-sm">
                                    <div
                                        v-if="request.assigned_broker"
                                        class="text-gray-900"
                                    >
                                        <span class="font-medium"
                                            >Assigned to:</span
                                        >
                                        {{ request.assigned_broker.name }}
                                    </div>
                                    <div v-else class="text-gray-500">
                                        <span class="font-medium">Status:</span>
                                        Unassigned
                                    </div>
                                    <div
                                        v-if="request.reviewed_by"
                                        class="text-gray-500 mt-1"
                                    >
                                        <span class="font-medium"
                                            >Reviewed by:</span
                                        >
                                        {{ request.reviewed_by.name }}
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline Info -->
                            <div class="text-xs text-gray-500 mb-4">
                                <p>
                                    <span class="font-medium">Created:</span>
                                    {{ formatDate(request.created_at) }}
                                </p>
                                <p v-if="request.reviewed_at">
                                    <span class="font-medium">Reviewed:</span>
                                    {{ formatDate(request.reviewed_at) }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div
                                v-if="canManage"
                                class="flex justify-between items-center"
                            >
                                <Link
                                    :href="
                                        route(
                                            'seller-requests.show',
                                            request.id
                                        )
                                    "
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    View Details
                                </Link>
                                <div class="flex space-x-2">
                                    <Link
                                        :href="
                                            route(
                                                'seller-requests.edit',
                                                request.id
                                            )
                                        "
                                        class="text-gray-600 hover:text-gray-800 text-sm"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        v-if="
                                            $page.props.auth.user.role ===
                                            'admin'
                                        "
                                        @click="deleteRequest(request)"
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
                    <div class="text-gray-400 text-6xl mb-4">📋</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        No seller requests found
                    </h3>
                    <p class="text-gray-500">
                        Try adjusting your search filters or create a new seller
                        request.
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="sellerRequests.data.length > 0" class="mt-6">
                    <Pagination :links="sellerRequests.links" />
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
