<script setup>
import { Head, useForm, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernTable from "@/Components/ModernTable.vue";
import ModernButton from "@/Components/ModernButton.vue";
import ModernInput from "@/Components/ModernInput.vue";
import Modal from "@/Components/Modal.vue";
import { ref, computed } from "vue";
import {
    UserIcon,
    MagnifyingGlassIcon,
    ChartBarIcon,
    EyeIcon,
    PencilIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    ChatBubbleLeftRightIcon,
    DocumentCheckIcon,
    UserPlusIcon,
    FunnelIcon,
    ArrowPathIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    brokers: Object,
    filters: Object,
    stats: Object,
});

// Search and filter state
const searchForm = useForm({
    search: props.filters.search || "",
    status: props.filters.status || "",
    performance: props.filters.performance || "",
    verification: props.filters.verification || "",
    sort_by: props.filters.sort_by || "name",
    sort_direction: props.filters.sort_direction || "asc",
});

// Status management forms
const statusForm = useForm({
    status: "",
    reason: "",
    admin_notes: "",
});

const communicationForm = useForm({
    subject: "",
    message: "",
    broker_ids: [],
    send_email: true,
    send_notification: true,
});

// Modal states
const showStatusModal = ref(false);
const showCommunicationModal = ref(false);
const selectedBroker = ref(null);
const selectedBrokers = ref([]);
const bulkAction = ref("");

// Table columns
const columns = [
    { key: "select", label: "", sortable: false },
    { key: "name", label: "Broker", sortable: true },
    { key: "license_number", label: "License", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "verification", label: "Verification", sortable: false },
    { key: "performance", label: "Performance", sortable: true },
    { key: "clients_count", label: "Clients", sortable: true },
    { key: "properties_count", label: "Properties", sortable: true },
    { key: "last_active", label: "Last Active", sortable: true },
    { key: "actions", label: "Actions", sortable: false },
];

// Computed properties
const allSelected = computed({
    get: () => selectedBrokers.value.length === props.brokers.data.length,
    set: (value) => {
        selectedBrokers.value = value
            ? props.brokers.data.map((b) => b.id)
            : [];
    },
});

// Methods
const search = () => {
    searchForm.get(route("admin.brokers.index"), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchForm.reset();
    search();
};

const openStatusModal = (broker, newStatus) => {
    selectedBroker.value = broker;
    statusForm.status = newStatus;
    statusForm.reset("reason", "admin_notes");
    showStatusModal.value = true;
};

const updateBrokerStatus = () => {
    statusForm.patch(
        route("admin.brokers.update-status", selectedBroker.value.id),
        {
            onSuccess: () => {
                showStatusModal.value = false;
                selectedBroker.value = null;
            },
        }
    );
};

const openCommunicationModal = (brokerIds = []) => {
    communicationForm.broker_ids = brokerIds.length
        ? brokerIds
        : selectedBrokers.value;
    communicationForm.reset("subject", "message");
    showCommunicationModal.value = true;
};

const sendCommunication = () => {
    communicationForm.post(route("admin.brokers.communicate"), {
        onSuccess: () => {
            showCommunicationModal.value = false;
            selectedBrokers.value = [];
        },
    });
};

const getStatusBadge = (status) => {
    const badges = {
        active: "bg-green-100 text-green-800",
        inactive: "bg-gray-100 text-gray-800",
        suspended: "bg-red-100 text-red-800",
        pending: "bg-yellow-100 text-yellow-800",
        under_review: "bg-blue-100 text-blue-800",
    };
    return badges[status] || "bg-gray-100 text-gray-800";
};

const getVerificationStatus = (broker) => {
    // Only check PRC verification since business permit is being removed
    const verified = broker.prc_verified;

    if (verified)
        return {
            status: "verified",
            class: "bg-green-100 text-green-800",
            text: "Verified",
        };
    return {
        status: "unverified",
        class: "bg-red-100 text-red-800",
        text: "Unverified",
    };
};

const getPerformanceRating = (broker) => {
    const score = broker.performance_score || 0;
    if (score >= 80)
        return {
            rating: "excellent",
            class: "text-green-600",
            text: "Excellent",
        };
    if (score >= 60)
        return { rating: "good", class: "text-blue-600", text: "Good" };
    if (score >= 40)
        return { rating: "average", class: "text-yellow-600", text: "Average" };
    return { rating: "poor", class: "text-red-600", text: "Poor" };
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(value || 0);
};
</script>

<template>
    <ModernDashboardLayout>
        <Head title="Broker Management - Admin" />

        <!-- Header -->
        <div class="mb-8">
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                        Broker Management
                    </h1>
                    <p class="text-neutral-600">
                        Complete broker lifecycle management and performance
                        monitoring
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <ModernButton
                        variant="outline"
                        :href="route('admin.brokers.index')"
                    >
                        <ArrowPathIcon class="w-5 h-5" />
                        Refresh
                    </ModernButton>
                    <ModernButton
                        variant="primary"
                        @click="openCommunicationModal()"
                        :disabled="selectedBrokers.length === 0"
                    >
                        <ChatBubbleLeftRightIcon class="w-5 h-5" />
                        Send Message ({{ selectedBrokers.length }})
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Total Brokers
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ stats.total_brokers }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center"
                    >
                        <UserIcon class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Active Brokers
                        </p>
                        <p class="text-2xl font-bold text-green-600">
                            {{ stats.active_brokers }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center"
                    >
                        <CheckCircleIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Pending Approval
                        </p>
                        <p class="text-2xl font-bold text-yellow-600">
                            {{ stats.pending_brokers }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center"
                    >
                        <ExclamationTriangleIcon
                            class="w-6 h-6 text-yellow-600"
                        />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Avg Performance
                        </p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ stats.avg_performance }}%
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center"
                    >
                        <ChartBarIcon class="w-6 h-6 text-purple-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <div class="relative">
                        <MagnifyingGlassIcon
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                        />
                        <ModernInput
                            v-model="searchForm.search"
                            type="text"
                            placeholder="Search brokers by name, email, or license..."
                            class="pl-10"
                            @keyup.enter="search"
                        />
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <select
                        v-model="searchForm.status"
                        @change="search"
                        class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                        <option value="pending">Pending</option>
                        <option value="under_review">Under Review</option>
                    </select>
                </div>

                <!-- Verification Filter -->
                <div>
                    <select
                        v-model="searchForm.verification"
                        @change="search"
                        class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">All Verification</option>
                        <option value="verified">Verified</option>
                        <option value="partial">Partially Verified</option>
                        <option value="unverified">Unverified</option>
                    </select>
                </div>

                <!-- Performance Filter -->
                <div>
                    <select
                        v-model="searchForm.performance"
                        @change="search"
                        class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">All Performance</option>
                        <option value="excellent">Excellent (80%+)</option>
                        <option value="good">Good (60-79%)</option>
                        <option value="average">Average (40-59%)</option>
                        <option value="poor">Poor (Less than 40%)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center gap-2">
                    <FunnelIcon class="w-5 h-5 text-gray-400" />
                    <span class="text-sm text-gray-600"
                        >{{ brokers.total }} brokers found</span
                    >
                </div>
                <ModernButton variant="ghost" size="sm" @click="clearFilters">
                    Clear Filters
                </ModernButton>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div
            v-if="selectedBrokers.length > 0"
            class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <CheckCircleIcon class="w-5 h-5 text-blue-600" />
                    <span class="text-sm font-medium text-blue-900">
                        {{ selectedBrokers.length }} broker(s) selected
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <ModernButton
                        variant="outline"
                        size="sm"
                        @click="openCommunicationModal()"
                    >
                        <ChatBubbleLeftRightIcon class="w-4 h-4" />
                        Send Message
                    </ModernButton>
                    <ModernButton variant="outline" size="sm">
                        Export Selected
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Brokers Table -->
        <ModernTable :columns="columns" :data="brokers.data">
            <!-- Select Column -->
            <template #cell-select="{ item }">
                <input
                    type="checkbox"
                    :value="item.id"
                    v-model="selectedBrokers"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
            </template>

            <!-- Broker Column -->
            <template #cell-name="{ item }">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center"
                    >
                        <UserIcon class="w-5 h-5 text-primary-600" />
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">
                            {{ item.name }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ item.email }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ item.phone }}
                        </div>
                    </div>
                </div>
            </template>

            <!-- License Column -->
            <template #cell-license_number="{ item }">
                <div class="text-sm">
                    <div class="font-medium text-gray-900">
                        {{ item.license_number }}
                    </div>
                </div>
            </template>

            <!-- Status Column -->
            <template #cell-status="{ item }">
                <span
                    :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        getStatusBadge(item.application_status),
                    ]"
                >
                    {{
                        item.application_status?.replace("_", " ").toUpperCase()
                    }}
                </span>
            </template>

            <!-- Verification Column -->
            <template #cell-verification="{ item }">
                <div class="flex items-center gap-2">
                    <span
                        :class="[
                            'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                            getVerificationStatus(item).class,
                        ]"
                    >
                        {{ getVerificationStatus(item).text }}
                    </span>
                </div>
            </template>

            <!-- Performance Column -->
            <template #cell-performance="{ item }">
                <div class="text-sm">
                    <div
                        :class="[
                            'font-medium',
                            getPerformanceRating(item).class,
                        ]"
                    >
                        {{ getPerformanceRating(item).text }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ item.performance_score || 0 }}% score
                    </div>
                </div>
            </template>

            <!-- Last Active Column -->
            <template #cell-last_active="{ item }">
                <div class="text-sm text-gray-900">
                    {{ formatDate(item.last_login_at || item.updated_at) }}
                </div>
            </template>

            <!-- Actions Column -->
            <template #cell-actions="{ item }">
                <div class="flex items-center gap-2">
                    <ModernButton
                        variant="ghost"
                        size="sm"
                        :href="route('admin.brokers.show', item.id)"
                    >
                        <EyeIcon class="w-4 h-4" />
                    </ModernButton>
                    <ModernButton
                        variant="ghost"
                        size="sm"
                        :href="route('admin.brokers.edit', item.id)"
                    >
                        <PencilIcon class="w-4 h-4" />
                    </ModernButton>

                    <!-- Status Actions -->
                    <div class="relative">
                        <select
                            @change="openStatusModal(item, $event.target.value)"
                            class="text-xs border-gray-200 rounded-md focus:ring-primary-500 focus:border-primary-500"
                        >
                            <option value="">Status</option>
                            <option
                                v-if="item.application_status !== 'active'"
                                value="active"
                            >
                                Activate
                            </option>
                            <option
                                v-if="item.application_status !== 'inactive'"
                                value="inactive"
                            >
                                Deactivate
                            </option>
                            <option
                                v-if="item.application_status !== 'suspended'"
                                value="suspended"
                            >
                                Suspend
                            </option>
                        </select>
                    </div>
                </div>
            </template>
        </ModernTable>

        <!-- Pagination -->
        <div v-if="brokers.links" class="mt-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing {{ brokers.from }} to {{ brokers.to }} of
                    {{ brokers.total }} results
                </div>
                <div class="flex items-center gap-2">
                    <template v-for="link in brokers.links" :key="link.label">
                        <component
                            :is="link.url ? 'Link' : 'span'"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 text-sm rounded-lg transition-colors',
                                link.active
                                    ? 'bg-primary-500 text-white'
                                    : link.url
                                    ? 'text-gray-700 hover:bg-gray-100'
                                    : 'text-gray-400 cursor-not-allowed',
                            ]"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Status Update Modal -->
        <Modal :show="showStatusModal" @close="showStatusModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Update Broker Status
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Change status for
                    <strong>{{ selectedBroker?.name }}</strong> to
                    <strong>{{
                        statusForm.status?.replace("_", " ").toUpperCase()
                    }}</strong>
                </p>

                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Reason for Status Change
                        </label>
                        <select
                            v-model="statusForm.reason"
                            class="w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">Select a reason</option>
                            <option value="performance_issues">
                                Performance Issues
                            </option>
                            <option value="policy_violation">
                                Policy Violation
                            </option>
                            <option value="client_complaints">
                                Client Complaints
                            </option>
                            <option value="document_issues">
                                Document Issues
                            </option>
                            <option value="administrative">
                                Administrative
                            </option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Admin Notes
                        </label>
                        <textarea
                            v-model="statusForm.admin_notes"
                            rows="3"
                            class="w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Add any additional notes about this status change..."
                        ></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <ModernButton
                        variant="outline"
                        @click="showStatusModal = false"
                    >
                        Cancel
                    </ModernButton>
                    <ModernButton
                        variant="primary"
                        @click="updateBrokerStatus"
                        :disabled="statusForm.processing"
                    >
                        Update Status
                    </ModernButton>
                </div>
            </div>
        </Modal>

        <!-- Communication Modal -->
        <Modal
            :show="showCommunicationModal"
            @close="showCommunicationModal = false"
        >
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Send Message to Brokers
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Sending to
                    {{ communicationForm.broker_ids.length }} broker(s)
                </p>

                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Subject
                        </label>
                        <ModernInput
                            v-model="communicationForm.subject"
                            type="text"
                            placeholder="Enter message subject"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Message
                        </label>
                        <textarea
                            v-model="communicationForm.message"
                            rows="5"
                            class="w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Enter your message..."
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-4">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="communicationForm.send_email"
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            />
                            <span class="ml-2 text-sm text-gray-700"
                                >Send Email</span
                            >
                        </label>
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="communicationForm.send_notification"
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            />
                            <span class="ml-2 text-sm text-gray-700"
                                >Send In-App Notification</span
                            >
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <ModernButton
                        variant="outline"
                        @click="showCommunicationModal = false"
                    >
                        Cancel
                    </ModernButton>
                    <ModernButton
                        variant="primary"
                        @click="sendCommunication"
                        :disabled="communicationForm.processing"
                    >
                        Send Message
                    </ModernButton>
                </div>
            </div>
        </Modal>
    </ModernDashboardLayout>
</template>
