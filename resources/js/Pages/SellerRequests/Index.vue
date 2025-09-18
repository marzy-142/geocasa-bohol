<script setup>
import { ref, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Link } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";
import { debounce } from "lodash";

const props = defineProps({
    sellerRequests: Object,
    brokers: Array,
    filters: Object,
    canManage: Boolean,
    canCreate: Boolean,
    stats: Object,
});

// Assignment functionality
const selectedSellerRequests = ref([]);
const showAssignModal = ref(false);
const showBulkAssignModal = ref(false);
const selectedSellerRequest = ref(null);

const assignForm = ref({
    broker_id: "",
    processing: false,
});

const bulkAssignForm = ref({
    broker_id: "",
    processing: false,
});

const allSellerRequestsSelected = computed(() => {
    return (
        props.sellerRequests.data.length > 0 &&
        selectedSellerRequests.value.length === props.sellerRequests.data.length
    );
});

const filters = ref({ ...props.filters });

const filterSellerRequests = debounce(() => {
    router.get(route("seller-requests.index"), filters.value, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

const toggleAllSellerRequests = () => {
    if (allSellerRequestsSelected.value) {
        selectedSellerRequests.value = [];
    } else {
        selectedSellerRequests.value = props.sellerRequests.data.map(
            (sellerRequest) => sellerRequest.id
        );
    }
};

const openAssignModal = (sellerRequest) => {
    selectedSellerRequest.value = sellerRequest;
    assignForm.value.broker_id = sellerRequest.assigned_broker?.id || "";
    showAssignModal.value = true;
};

const closeAssignModal = () => {
    showAssignModal.value = false;
    selectedSellerRequest.value = null;
    assignForm.value.broker_id = "";
};

const assignBroker = () => {
    if (!assignForm.value.broker_id) return;

    assignForm.value.processing = true;

    router.post(
        route("seller-requests.assign"),
        {
            seller_request_id: selectedSellerRequest.value.id,
            broker_id: assignForm.value.broker_id,
        },
        {
            onSuccess: () => {
                closeAssignModal();
            },
            onFinish: () => {
                assignForm.value.processing = false;
            },
        }
    );
};

const bulkAssignBroker = () => {
    if (
        !bulkAssignForm.value.broker_id ||
        selectedSellerRequests.value.length === 0
    )
        return;

    bulkAssignForm.value.processing = true;

    router.post(
        route("seller-requests.bulk-assign"),
        {
            seller_request_ids: selectedSellerRequests.value,
            broker_id: bulkAssignForm.value.broker_id,
        },
        {
            onSuccess: () => {
                showBulkAssignModal.value = false;
                selectedSellerRequests.value = [];
                bulkAssignForm.value.broker_id = "";
            },
            onFinish: () => {
                bulkAssignForm.value.processing = false;
            },
        }
    );
};

const getInitials = (name) => {
    return name
        .split(" ")
        .map((word) => word.charAt(0))
        .join("")
        .toUpperCase();
};

const clearFilters = () => {
    filters.value = {};
    router.get(route("seller-requests.index"));
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
                    <div class="flex space-x-3">
                        <button
                            v-if="$page.props.auth.user.role === 'admin'"
                            @click="showBulkAssignModal = true"
                            :disabled="selectedSellerRequests.length === 0"
                            class="bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-lg"
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
                                Bulk Assign ({{
                                    selectedSellerRequests.length
                                }})
                            </span>
                        </button>
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
            </div>

            <!-- Statistics Cards (Admin Only) -->
            <div
                v-if="$page.props.auth.user.role === 'admin' && stats"
                class="grid grid-cols-1 md:grid-cols-4 gap-6"
            >
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
                >
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-lg">
                            <svg
                                class="w-6 h-6 text-red-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Unassigned Sellers
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ stats.unassigned }}
                            </p>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
                >
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <svg
                                class="w-6 h-6 text-green-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Assigned Sellers
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ stats.assigned }}
                            </p>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
                >
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <svg
                                class="w-6 h-6 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                ></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Active Brokers
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ stats.activeBrokers }}
                            </p>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
                >
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <svg
                                class="w-6 h-6 text-purple-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                ></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Avg per Broker
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ stats.avgSellerRequestsPerBroker }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Search & Filter Seller Requests
                </h3>

                <!-- Primary Filters -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-4"
                >
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search seller requests..."
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        @input="filterSellerRequests"
                    />
                    <select
                        v-model="filters.status"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        @change="filterSellerRequests"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="under_review">Under Review</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select
                        v-if="$page.props.auth.user.role === 'admin'"
                        v-model="filters.assignment_status"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        @change="filterSellerRequests"
                    >
                        <option value="">All Assignments</option>
                        <option value="assigned">Assigned</option>
                        <option value="unassigned">Unassigned</option>
                    </select>
                    <select
                        v-if="$page.props.auth.user.role === 'admin'"
                        v-model="filters.broker_id"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        @change="filterSellerRequests"
                    >
                        <option value="">All Brokers</option>
                        <option
                            v-for="broker in brokers"
                            :key="broker.id"
                            :value="broker.id"
                        >
                            {{ broker.name }}
                        </option>
                    </select>
                    <select
                        v-model="filters.property_type"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        @change="filterSellerRequests"
                    >
                        <option value="">All Property Types</option>
                        <option value="residential">Residential</option>
                        <option value="commercial">Commercial</option>
                        <option value="agricultural">Agricultural</option>
                        <option value="beachfront">Beachfront</option>
                    </select>
                    <input
                        v-model="filters.date_from"
                        type="date"
                        placeholder="Date From"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        @change="filterSellerRequests"
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
                    <div class="flex items-center space-x-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Seller Requests ({{ sellerRequests.total || 0 }})
                        </h3>
                        <div
                            v-if="
                                $page.props.auth.user.role === 'admin' &&
                                sellerRequests.data.length > 0
                            "
                            class="flex items-center space-x-2"
                        >
                            <input
                                type="checkbox"
                                :checked="allSellerRequestsSelected"
                                @change="toggleAllSellerRequests"
                                class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                            />
                            <span class="text-sm text-gray-600"
                                >Select All</span
                            >
                        </div>
                    </div>
                    <div
                        v-if="
                            $page.props.auth.user.role === 'admin' &&
                            selectedSellerRequests.length > 0
                        "
                        class="flex items-center space-x-2"
                    >
                        <span class="text-sm text-gray-600"
                            >{{ selectedSellerRequests.length }} selected</span
                        >
                        <button
                            @click="showBulkAssignModal = true"
                            class="bg-blue-600 text-white hover:bg-blue-700 font-medium py-2 px-4 rounded-lg transition-colors duration-200"
                        >
                            Assign to Broker
                        </button>
                    </div>
                </div>

                <div
                    v-if="sellerRequests.data.length > 0"
                    class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6"
                >
                    <div
                        v-for="request in sellerRequests.data"
                        :key="request.id"
                        class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow"
                        :class="{
                            'ring-2 ring-orange-500':
                                selectedSellerRequests.includes(request.id),
                        }"
                    >
                        <div class="p-6">
                            <!-- Selection Checkbox (Admin Only) -->
                            <div
                                v-if="$page.props.auth.user.role === 'admin'"
                                class="flex justify-end mb-2"
                            >
                                <input
                                    type="checkbox"
                                    :value="request.id"
                                    v-model="selectedSellerRequests"
                                    class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                />
                            </div>
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
                                    <button
                                        v-if="
                                            $page.props.auth.user.role ===
                                            'admin'
                                        "
                                        @click="openAssignModal(request)"
                                        class="text-green-600 hover:text-green-800 text-sm font-medium"
                                    >
                                        {{
                                            request.assigned_broker
                                                ? "Reassign"
                                                : "Assign"
                                        }}
                                    </button>
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

        <!-- Assignment Modal -->
        <div
            v-if="showAssignModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        >
            <div
                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
            >
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Assign Broker to
                        {{ selectedSellerRequest?.seller_name }}
                    </h3>
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Select Broker
                        </label>
                        <select
                            v-model="assignForm.broker_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                        >
                            <option value="">Select a broker...</option>
                            <option
                                v-for="broker in brokers"
                                :key="broker.id"
                                :value="broker.id"
                            >
                                {{ broker.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeAssignModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md"
                        >
                            Cancel
                        </button>
                        <button
                            @click="assignBroker"
                            :disabled="
                                !assignForm.broker_id || assignForm.processing
                            "
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-md"
                        >
                            {{
                                assignForm.processing
                                    ? "Assigning..."
                                    : "Assign Broker"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Assignment Modal -->
        <div
            v-if="showBulkAssignModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        >
            <div
                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
            >
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Bulk Assign {{ selectedSellerRequests.length }} Seller
                        Requests
                    </h3>
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Select Broker
                        </label>
                        <select
                            v-model="bulkAssignForm.broker_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                        >
                            <option value="">Select a broker...</option>
                            <option
                                v-for="broker in brokers"
                                :key="broker.id"
                                :value="broker.id"
                            >
                                {{ broker.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="
                                showBulkAssignModal = false;
                                bulkAssignForm.broker_id = '';
                            "
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md"
                        >
                            Cancel
                        </button>
                        <button
                            @click="bulkAssignBroker"
                            :disabled="
                                !bulkAssignForm.broker_id ||
                                bulkAssignForm.processing
                            "
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-md"
                        >
                            {{
                                bulkAssignForm.processing
                                    ? "Assigning..."
                                    : "Assign to All"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
