<template>
    <ModernDashboardLayout>
        <Head title="Client Assignments - Admin" />

        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Header -->
            <div
                class="bg-white rounded-2xl border border-slate-200/60 p-8 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-5">
                        <div
                            class="w-14 h-14 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl flex items-center justify-center border border-blue-200/50"
                        >
                            <UserGroupIcon class="w-7 h-7 text-blue-600" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900 mb-2">
                                Seller Request Assignments
                            </h1>
                            <p class="text-slate-600 text-sm">
                                Manage broker assignments for seller requests
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            @click="refreshData"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                        >
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div
                    class="bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">
                                Total Requests
                            </p>
                            <p class="text-2xl font-bold text-slate-900">
                                {{ stats?.total || 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                        >
                            <ClipboardDocumentListIcon
                                class="w-6 h-6 text-blue-600"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">
                                Unassigned
                            </p>
                            <p class="text-2xl font-bold text-red-600">
                                {{ stats?.unassigned || 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center"
                        >
                            <ExclamationTriangleIcon
                                class="w-6 h-6 text-red-600"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">
                                Assigned
                            </p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ stats?.assigned || 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center"
                        >
                            <CheckCircleIcon class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-600">
                                Active Brokers
                            </p>
                            <p class="text-2xl font-bold text-blue-600">
                                {{ stats?.activeBrokers || 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                        >
                            <UserGroupIcon class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm"
            >
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 mb-2"
                            >Search</label
                        >
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search by name, email, property..."
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @input="debouncedSearch"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 mb-2"
                            >Status</label
                        >
                        <select
                            v-model="filters.status"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="under_review">Under Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="listed">Listed</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 mb-2"
                            >Assignment</label
                        >
                        <select
                            v-model="filters.assignment_status"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="">All</option>
                            <option value="assigned">Assigned</option>
                            <option value="unassigned">Unassigned</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 mb-2"
                            >Broker</label
                        >
                        <select
                            v-model="filters.broker_id"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
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
                </div>

                <div class="flex justify-between items-center mt-4">
                    <button
                        @click="clearFilters"
                        class="text-slate-600 hover:text-slate-900 font-medium"
                    >
                        Clear Filters
                    </button>

                    <div class="flex gap-2">
                        <button
                            @click="showBulkAssignModal = true"
                            :disabled="selectedRequests.length === 0"
                            class="bg-blue-600 hover:bg-blue-700 disabled:bg-slate-400 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                        >
                            Bulk Assign ({{ selectedRequests.length }})
                        </button>
                    </div>
                </div>
            </div>

            <!-- Seller Requests Table -->
            <div
                class="bg-white rounded-xl border border-slate-200/60 shadow-sm overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        :checked="allSelected"
                                        @change="toggleSelectAll"
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                                >
                                    Seller
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                                >
                                    Property
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                                >
                                    Price
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                                >
                                    Assigned Broker
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                                >
                                    Created
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            <tr
                                v-for="request in sellerRequests.data"
                                :key="request.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        type="checkbox"
                                        :checked="
                                            selectedRequests.includes(
                                                request.id
                                            )
                                        "
                                        @change="toggleSelection(request.id)"
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div
                                            class="text-sm font-medium text-slate-900"
                                        >
                                            {{ request.name }}
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            {{ request.email }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div
                                            class="text-sm font-medium text-slate-900"
                                        >
                                            {{ request.property_title }}
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            {{ request.city }},
                                            {{ request.province }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-slate-900"
                                    >
                                        ₱{{
                                            formatNumber(request.asking_price)
                                        }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="
                                            getStatusBadgeClass(request.status)
                                        "
                                    >
                                        {{ formatStatus(request.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="request.assigned_broker">
                                        <div
                                            class="text-sm font-medium text-slate-900"
                                        >
                                            {{ request.assigned_broker.name }}
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            {{ request.assigned_broker.email }}
                                        </div>
                                    </div>
                                    <span
                                        v-else
                                        class="text-sm text-slate-500 italic"
                                        >Unassigned</span
                                    >
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-500"
                                >
                                    {{ formatDate(request.created_at) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                >
                                    <div class="flex gap-2">
                                        <button
                                            @click="viewRequest(request)"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            View
                                        </button>
                                        <button
                                            v-if="!request.assigned_broker_id"
                                            @click="openAssignModal(request)"
                                            class="text-green-600 hover:text-green-900"
                                        >
                                            Assign
                                        </button>
                                        <button
                                            v-else
                                            @click="showReassignModal(request)"
                                            class="text-orange-600 hover:text-orange-900"
                                        >
                                            Reassign
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="sellerRequests.links"
                    class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                v-if="sellerRequests.prev_page_url"
                                :href="sellerRequests.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="sellerRequests.next_page_url"
                                :href="sellerRequests.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div
                            class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"
                        >
                            <div>
                                <p class="text-sm text-slate-700">
                                    Showing {{ sellerRequests.from }} to
                                    {{ sellerRequests.to }} of
                                    {{ sellerRequests.total }} results
                                </p>
                            </div>
                            <div>
                                <nav
                                    class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                >
                                    <Link
                                        v-for="link in sellerRequests.links"
                                        :key="link.label"
                                        :href="link.url"
                                        :class="[
                                            link.url
                                                ? 'bg-white hover:bg-slate-50'
                                                : 'bg-slate-100 cursor-not-allowed',
                                            link.active
                                                ? 'bg-blue-50 text-blue-600'
                                                : 'text-slate-700',
                                            'relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium',
                                        ]"
                                    >
                                        <span v-html="link.label"></span>
                                    </Link>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assign Modal -->
        <div
            v-if="showAssignModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">
                    Assign Broker
                </h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2"
                        >Select Broker</label
                    >
                    <select
                        v-model="assignForm.broker_id"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                            requests)
                        </option>
                    </select>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="showAssignModal = false"
                        class="px-4 py-2 text-slate-600 hover:text-slate-900 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        @click="assignBroker"
                        :disabled="!assignForm.broker_id"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-slate-400 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                    >
                        Assign
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Assign Modal -->
        <div
            v-if="showBulkAssignModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">
                    Bulk Assign Brokers
                </h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2"
                        >Select Broker</label
                    >
                    <select
                        v-model="bulkAssignForm.broker_id"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                            requests)
                        </option>
                    </select>
                </div>

                <div class="mb-4 p-3 bg-slate-50 rounded-lg">
                    <p class="text-sm text-slate-600">
                        This will assign {{ selectedRequests.length }} seller
                        request(s) to the selected broker.
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="showBulkAssignModal = false"
                        class="px-4 py-2 text-slate-600 hover:text-slate-900 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        @click="bulkAssignBrokers"
                        :disabled="!bulkAssignForm.broker_id"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-slate-400 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                    >
                        Assign All
                    </button>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { ref, computed, reactive } from "vue";
import {
    UserGroupIcon,
    ClipboardDocumentListIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    sellerRequests: Object,
    brokers: Array,
    stats: Object,
    filters: Object,
});

// Reactive data
const selectedRequests = ref([]);
const showAssignModal = ref(false);
const showBulkAssignModal = ref(false);
const selectedRequest = ref(null);

const filters = reactive({ ...props.filters });

// Forms
const assignForm = useForm({
    seller_request_id: null,
    broker_id: null,
});

const bulkAssignForm = useForm({
    seller_request_ids: [],
    broker_id: null,
});

// Computed
const allSelected = computed(() => {
    return (
        props.sellerRequests.data.length > 0 &&
        selectedRequests.value.length === props.sellerRequests.data.length
    );
});

// Methods
const formatNumber = (number) => {
    return new Intl.NumberFormat("en-PH").format(number || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const formatStatus = (status) => {
    return status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        under_review: "bg-blue-100 text-blue-800",
        approved: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
        listed: "bg-emerald-100 text-emerald-800",
    };
    return `inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
        classes[status] || "bg-gray-100 text-gray-800"
    }`;
};

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedRequests.value = [];
    } else {
        selectedRequests.value = props.sellerRequests.data.map(
            (request) => request.id
        );
    }
};

const toggleSelection = (requestId) => {
    const index = selectedRequests.value.indexOf(requestId);
    if (index > -1) {
        selectedRequests.value.splice(index, 1);
    } else {
        selectedRequests.value.push(requestId);
    }
};

const applyFilters = () => {
    router.get(route("admin.client-assignments"), filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const debouncedSearch = (() => {
    let timeout;
    return () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            applyFilters();
        }, 500);
    };
})();

const clearFilters = () => {
    Object.keys(filters).forEach((key) => {
        filters[key] = "";
    });
    applyFilters();
};

const refreshData = () => {
    router.reload({ only: ["sellerRequests", "stats"] });
};

const viewRequest = (request) => {
    // Navigate to seller request details
    window.open(route("seller-requests.show", request.id), "_blank");
};

const openAssignModal = (request) => {
    selectedRequest.value = request;
    assignForm.seller_request_id = request.id;
    showAssignModal.value = true;
};

const showReassignModal = (request) => {
    openAssignModal(request);
};

const assignBroker = () => {
    assignForm.post(route("admin.admin.seller-requests.assign"), {
        onSuccess: () => {
            showAssignModal.value = false;
            selectedRequest.value = null;
            assignForm.reset();
        },
    });
};

const bulkAssignBrokers = () => {
    bulkAssignForm.seller_request_ids = selectedRequests.value;
    bulkAssignForm.post(route("admin.admin.seller-requests.bulk-assign"), {
        onSuccess: () => {
            showBulkAssignModal.value = false;
            selectedRequests.value = [];
            bulkAssignForm.reset();
        },
    });
};
</script>
