<template>
    <ModernDashboardLayout>
        <Head title="Broker Management - Admin" />

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
                                Broker Management
                            </h1>
                            <p class="text-slate-600 text-sm">
                                Manage approved brokers and their performance
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('admin.broker-approvals.index')"
                            class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                        >
                            Pending Approvals
                        </Link>
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
                                Total Brokers
                            </p>
                            <p class="text-2xl font-bold text-slate-900">
                                {{ stats?.totalBrokers || 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                        >
                            <UserGroupIcon class="w-6 h-6 text-blue-600" />
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
                            <p class="text-2xl font-bold text-green-600">
                                {{ stats?.activeBrokers || 0 }}
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
                                Suspended
                            </p>
                            <p class="text-2xl font-bold text-red-600">
                                {{ stats?.suspendedBrokers || 0 }}
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
                                Total Properties
                            </p>
                            <p class="text-2xl font-bold text-blue-600">
                                {{ stats?.totalProperties || 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                        >
                            <BuildingOfficeIcon class="w-6 h-6 text-blue-600" />
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
                            placeholder="Search by name, email..."
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
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 mb-2"
                            >Verification</label
                        >
                        <select
                            v-model="filters.verification_status"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="">All</option>
                            <option value="verified">Verified</option>
                            <option value="unverified">Unverified</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 mb-2"
                            >Sort By</label
                        >
                        <select
                            v-model="filters.sort"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="name">Name</option>
                            <option value="created_at">
                                Registration Date
                            </option>
                            <option value="properties_count">
                                Properties Count
                            </option>
                            <option value="transactions_count">
                                Transactions Count
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
                            @click="showBulkActionsModal = true"
                            :disabled="selectedBrokers.length === 0"
                            class="bg-blue-600 hover:bg-blue-700 disabled:bg-slate-400 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                        >
                            Bulk Actions ({{ selectedBrokers.length }})
                        </button>
                    </div>
                </div>
            </div>

            <!-- Brokers Table -->
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
                                    Broker
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                                >
                                    Contact
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                                >
                                    Properties
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                                >
                                    Transactions
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider"
                                >
                                    Registered
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
                                v-for="broker in brokers.data"
                                :key="broker.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        type="checkbox"
                                        :checked="
                                            selectedBrokers.includes(broker.id)
                                        "
                                        @change="toggleSelection(broker.id)"
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 bg-slate-200 rounded-full flex items-center justify-center"
                                        >
                                            <span
                                                class="text-sm font-medium text-slate-600"
                                            >
                                                {{
                                                    broker.name
                                                        .charAt(0)
                                                        .toUpperCase()
                                                }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-medium text-slate-900"
                                            >
                                                {{ broker.name }}
                                            </div>
                                            <div class="text-sm text-slate-500">
                                                PRC:
                                                {{ broker.prc_id || "N/A" }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm text-slate-900">
                                            {{ broker.email }}
                                        </div>
                                        <div class="text-sm text-slate-500">
                                            {{ broker.phone || "No phone" }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <span
                                            :class="getStatusBadgeClass(broker)"
                                        >
                                            {{ getStatusText(broker) }}
                                        </span>
                                        <span
                                            v-if="broker.prc_verified"
                                            class="text-xs text-green-600"
                                        >
                                            ✓ Verified
                                        </span>
                                        <span
                                            v-else
                                            class="text-xs text-orange-600"
                                        >
                                            ⚠ Unverified
                                        </span>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"
                                >
                                    {{ broker.properties_count || 0 }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"
                                >
                                    {{ broker.transactions_count || 0 }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-slate-500"
                                >
                                    {{ formatDate(broker.created_at) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                >
                                    <div class="flex gap-2">
                                        <Link
                                            :href="
                                                route(
                                                    'admin.brokers.show',
                                                    broker.id
                                                )
                                            "
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            View
                                        </Link>
                                        <Link
                                            :href="
                                                route(
                                                    'admin.brokers.edit',
                                                    broker.id
                                                )
                                            "
                                            class="text-green-600 hover:text-green-900"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="openStatusModal(broker)"
                                            class="text-orange-600 hover:text-orange-900"
                                        >
                                            Status
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="brokers.links"
                    class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                v-if="brokers.prev_page_url"
                                :href="brokers.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="brokers.next_page_url"
                                :href="brokers.next_page_url"
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
                                    Showing {{ brokers.from }} to
                                    {{ brokers.to }} of
                                    {{ brokers.total }} results
                                </p>
                            </div>
                            <div>
                                <nav
                                    class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                >
                                    <Link
                                        v-for="link in brokers.links"
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

        <!-- Status Modal -->
        <div
            v-if="showStatusModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">
                    Update Broker Status
                </h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2"
                        >Status</label
                    >
                    <select
                        v-model="statusForm.status"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="approve">Approve</option>
                        <option value="suspend">Suspend</option>
                        <option value="activate">Activate</option>
                        <option value="deactivate">Deactivate</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2"
                        >Notes</label
                    >
                    <textarea
                        v-model="statusForm.admin_notes"
                        rows="3"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Optional admin notes..."
                    ></textarea>
                </div>

                <div v-if="statusForm.status === 'suspend'" class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2"
                        >Reason</label
                    >
                    <input
                        v-model="statusForm.reason"
                        type="text"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Suspension reason..."
                    />
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="showStatusModal = false"
                        class="px-4 py-2 text-slate-600 hover:text-slate-900 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        @click="updateBrokerStatus"
                        :disabled="statusForm.processing"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-slate-400 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                    >
                        {{ statusForm.processing ? "Updating..." : "Update" }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Actions Modal -->
        <div
            v-if="showBulkActionsModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">
                    Bulk Actions
                </h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2"
                        >Action</label
                    >
                    <select
                        v-model="bulkActionsForm.action"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">Select action...</option>
                        <option value="approve">Approve</option>
                        <option value="suspend">Suspend</option>
                        <option value="activate">Activate</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="send_message">Send Message</option>
                    </select>
                </div>

                <div class="mb-4 p-3 bg-slate-50 rounded-lg">
                    <p class="text-sm text-slate-600">
                        This will apply the action to
                        {{ selectedBrokers.length }} broker(s).
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="showBulkActionsModal = false"
                        class="px-4 py-2 text-slate-600 hover:text-slate-900 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        @click="executeBulkActions"
                        :disabled="!bulkActionsForm.action"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-slate-400 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                    >
                        Execute
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
    CheckCircleIcon,
    ExclamationTriangleIcon,
    BuildingOfficeIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    brokers: Object,
    stats: Object,
    filters: Object,
});

// Reactive data
const selectedBrokers = ref([]);
const showStatusModal = ref(false);
const showBulkActionsModal = ref(false);
const selectedBroker = ref(null);

const filters = reactive({ ...props.filters });

// Forms
const statusForm = useForm({
    status: "approve",
    admin_notes: "",
    reason: "",
});

const bulkActionsForm = useForm({
    action: "",
    broker_ids: [],
});

// Computed
const allSelected = computed(() => {
    return (
        props.brokers.data.length > 0 &&
        selectedBrokers.value.length === props.brokers.data.length
    );
});

// Methods
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getStatusText = (broker) => {
    if (broker.suspended_at) return "Suspended";
    if (broker.is_approved) return "Active";
    return "Inactive";
};

const getStatusBadgeClass = (broker) => {
    if (broker.suspended_at) {
        return "inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800";
    }
    if (broker.is_approved) {
        return "inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800";
    }
    return "inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800";
};

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedBrokers.value = [];
    } else {
        selectedBrokers.value = props.brokers.data.map((broker) => broker.id);
    }
};

const toggleSelection = (brokerId) => {
    const index = selectedBrokers.value.indexOf(brokerId);
    if (index > -1) {
        selectedBrokers.value.splice(index, 1);
    } else {
        selectedBrokers.value.push(brokerId);
    }
};

const applyFilters = () => {
    router.get(route("admin.brokers.index"), filters, {
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
    router.reload({ only: ["brokers", "stats"] });
};

const openStatusModal = (broker) => {
    selectedBroker.value = broker;
    statusForm.reset();
    statusForm.status = broker.is_approved ? "approve" : "activate";
    showStatusModal.value = true;
};

const updateBrokerStatus = () => {
    statusForm.post(
        route("admin.brokers.update-status", selectedBroker.value.id),
        {
            onSuccess: () => {
                showStatusModal.value = false;
                selectedBroker.value = null;
            },
        }
    );
};

const executeBulkActions = () => {
    bulkActionsForm.broker_ids = selectedBrokers.value;
    bulkActionsForm.post(route("admin.brokers.bulk-actions"), {
        onSuccess: () => {
            showBulkActionsModal.value = false;
            selectedBrokers.value = [];
            bulkActionsForm.reset();
        },
    });
};
</script>
