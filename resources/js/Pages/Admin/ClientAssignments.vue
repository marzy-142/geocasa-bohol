<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header Section -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Seller-Broker Assignments
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Manage broker assignments for sellers who want to
                            list their properties
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <button
                            @click="showBulkAssignModal = true"
                            :disabled="selectedSellerRequests.length === 0"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <svg
                                class="w-4 h-4 inline mr-2"
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
                            Bulk Assign ({{ selectedSellerRequests.length }})
                        </button>
                        <Link
                            :href="route('seller-requests.index')"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                        >
                            View All Seller Requests
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                ></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Avg. Sellers/Broker
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ stats.avgSellersPerBroker }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
            >
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Search Sellers</label
                        >
                        <input
                            v-model="filters.search"
                            @input="filterSellerRequests"
                            type="text"
                            placeholder="Seller name, email, phone, or property..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Assignment Status</label
                        >
                        <select
                            v-model="filters.assignment_status"
                            @change="filterSellerRequests"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">All Sellers</option>
                            <option value="unassigned">Unassigned</option>
                            <option value="assigned">Assigned</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Filter by Broker</label
                        >
                        <select
                            v-model="filters.broker_id"
                            @change="filterSellerRequests"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Request Status</label
                        >
                        <select
                            v-model="filters.status"
                            @change="filterSellerRequests"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="under_review">Under Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Clients Table -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
            >
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Seller Request Assignments
                        </h3>
                        <div class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                :checked="allSellerRequestsSelected"
                                @change="toggleAllSellerRequests"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span class="text-sm text-gray-600"
                                >Select All</span
                            >
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Select
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Seller
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Contact
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Property Details
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Current Broker
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="sellerRequest in sellerRequests.data"
                                :key="sellerRequest.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        type="checkbox"
                                        :value="sellerRequest.id"
                                        v-model="selectedSellerRequests"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div
                                                class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center"
                                            >
                                                <span
                                                    class="text-sm font-medium text-blue-600"
                                                    >{{
                                                        getInitials(
                                                            sellerRequest.seller_name
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ sellerRequest.seller_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: {{ sellerRequest.id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ sellerRequest.seller_email }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{
                                            sellerRequest.seller_phone ||
                                            "No phone"
                                        }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ sellerRequest.property_title }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ sellerRequest.property_type }} - ₱{{
                                            formatPrice(
                                                sellerRequest.asking_price
                                            )
                                        }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        v-if="sellerRequest.assigned_broker"
                                        class="flex items-center"
                                    >
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div
                                                class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center"
                                            >
                                                <span
                                                    class="text-xs font-medium text-green-600"
                                                    >{{
                                                        getInitials(
                                                            sellerRequest
                                                                .assigned_broker
                                                                .name
                                                        )
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{
                                                    sellerRequest
                                                        .assigned_broker.name
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="flex items-center">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                        >
                                            Unassigned
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="
                                            getStatusClass(sellerRequest.status)
                                        "
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    >
                                        {{ formatStatus(sellerRequest.status) }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                >
                                    <div class="flex space-x-2">
                                        <button
                                            @click="
                                                openAssignModal(sellerRequest)
                                            "
                                            class="text-blue-600 hover:text-blue-900 transition-colors"
                                        >
                                            {{
                                                sellerRequest.assigned_broker
                                                    ? "Reassign"
                                                    : "Assign"
                                            }}
                                        </button>
                                        <Link
                                            :href="
                                                route(
                                                    'seller-requests.show',
                                                    sellerRequest.id
                                                )
                                            "
                                            class="text-gray-600 hover:text-gray-900 transition-colors"
                                        >
                                            View
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="
                        sellerRequests.links && sellerRequests.data.length > 0
                    "
                    class="px-6 py-4 border-t border-gray-200"
                >
                    <Pagination
                        :links="sellerRequests.links"
                        :from="sellerRequests.from"
                        :to="sellerRequests.to"
                        :total="sellerRequests.total"
                    />
                </div>
            </div>
        </div>

        <!-- Single Assignment Modal -->
        <div
            v-if="showAssignModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        >
            <div
                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
            >
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{
                            selectedSellerRequest?.assigned_broker
                                ? "Reassign"
                                : "Assign"
                        }}
                        Broker to {{ selectedSellerRequest?.seller_name }}
                    </h3>
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Select Broker</label
                        >
                        <select
                            v-model="assignForm.broker_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Choose a broker...</option>
                            <option
                                v-for="broker in brokers"
                                :key="broker.id"
                                :value="broker.id"
                            >
                                {{ broker.name }} ({{
                                    broker.assigned_seller_requests_count || 0
                                }}
                                sellers)
                            </option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeAssignModal"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="assignBroker"
                            :disabled="
                                !assignForm.broker_id || assignForm.processing
                            "
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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
                        Bulk Assign Broker to
                        {{ selectedSellerRequests.length }} Seller Requests
                    </h3>
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Select Broker</label
                        >
                        <select
                            v-model="bulkAssignForm.broker_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Choose a broker...</option>
                            <option
                                v-for="broker in brokers"
                                :key="broker.id"
                                :value="broker.id"
                            >
                                {{ broker.name }} ({{
                                    broker.assigned_seller_requests_count || 0
                                }}
                                sellers)
                            </option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeBulkAssignModal"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="bulkAssignBroker"
                            :disabled="
                                !bulkAssignForm.broker_id ||
                                bulkAssignForm.processing
                            "
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { debounce } from "lodash";

const props = defineProps({
    sellerRequests: Object,
    brokers: Array,
    stats: Object,
    filters: Object,
});

const filters = ref({ ...props.filters });
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

const filterSellerRequests = debounce(() => {
    router.get(route("admin.client-assignments"), filters.value, {
        preserveState: true,
        replace: true,
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
    assignForm.value.processing = false;
};

const closeBulkAssignModal = () => {
    showBulkAssignModal.value = false;
    selectedSellerRequests.value = [];
    bulkAssignForm.value.broker_id = "";
    bulkAssignForm.value.processing = false;
};

const assignBroker = async () => {
    if (!assignForm.value.broker_id || !selectedSellerRequest.value) return;

    assignForm.value.processing = true;

    try {
        await router.post(
            route("admin.client-assignments.assign"),
            {
                seller_request_id: selectedSellerRequest.value.id,
                broker_id: assignForm.value.broker_id,
            },
            {
                onSuccess: () => {
                    closeAssignModal();
                    // Show success message
                },
                onError: (errors) => {
                    console.error("Assignment failed:", errors);
                },
            }
        );
    } catch (error) {
        console.error("Assignment error:", error);
    } finally {
        assignForm.value.processing = false;
    }
};

const bulkAssignBroker = async () => {
    if (
        !bulkAssignForm.value.broker_id ||
        selectedSellerRequests.value.length === 0
    )
        return;

    bulkAssignForm.value.processing = true;

    try {
        await router.post(
            route("admin.client-assignments.bulk-assign"),
            {
                seller_request_ids: selectedSellerRequests.value,
                broker_id: bulkAssignForm.value.broker_id,
            },
            {
                onSuccess: () => {
                    closeBulkAssignModal();
                    selectedSellerRequests.value = [];
                    // Show success message
                },
                onError: (errors) => {
                    console.error("Bulk assignment failed:", errors);
                },
            }
        );
    } catch (error) {
        console.error("Bulk assignment error:", error);
    } finally {
        bulkAssignForm.value.processing = false;
    }
};

const getInitials = (name) => {
    return name
        .split(" ")
        .map((n) => n[0])
        .join("")
        .toUpperCase();
};

const getStatusClass = (status) => {
    const classes = {
        active: "bg-green-100 text-green-800",
        inactive: "bg-gray-100 text-gray-800",
        converted: "bg-blue-100 text-blue-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const formatStatus = (status) => {
    return status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH").format(price);
};
</script>
