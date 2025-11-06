<template>
    <ModernDashboardLayout title="Admin Inquiry Oversight">
        <!-- Enhanced Admin Header with System-Wide Stats -->
        <div
            class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white p-8 rounded-lg mb-6 shadow-xl"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">
                        Inquiry Oversight & Analytics
                    </h1>
                    <p class="text-indigo-100 text-lg">
                        System-wide inquiry monitoring, broker performance, and
                        response analytics
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Advanced Search & Filter Section -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        Advanced Filters & Search
                    </h2>
                    <button
                        @click="toggleAdvancedFilters"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                    >
                        {{ showAdvancedFilters ? "Hide" : "Show" }} Advanced
                        Filters
                    </button>
                </div>

                <!-- Primary Filters -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4"
                >
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Search</label
                        >
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search inquiries..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            @input="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Status</label
                        >
                        <select
                            v-model="selectedStatus"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            @change="applyFilters"
                        >
                            <option value="">All Statuses</option>
                            <option value="new">New</option>
                            <option value="contacted">Contacted</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="completed">Completed</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Broker</label
                        >
                        <select
                            v-model="selectedBroker"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            @change="applyFilters"
                        >
                            <option value="">All Brokers</option>
                            <option
                                v-for="broker in brokers || []"
                                :key="broker.id"
                                :value="broker.id"
                            >
                                {{ broker.name }} ({{
                                    broker.inquiry_count || 0
                                }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Property</label
                        >
                        <select
                            v-model="selectedProperty"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            @change="applyFilters"
                        >
                            <option value="">All Properties</option>
                            <option
                                v-for="property in properties || []"
                                :key="property.id"
                                :value="property.id"
                            >
                                {{ property.title }} -
                                {{ property.municipality }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Priority</label
                        >
                        <select
                            v-model="selectedPriority"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            @change="applyFilters"
                        >
                            <option value="">All Priorities</option>
                            <option value="high">High Priority</option>
                            <option value="medium">Medium Priority</option>
                            <option value="low">Low Priority</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div
                    v-if="showAdvancedFilters"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-4 border-t border-gray-200"
                >
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Inquiry Type</label
                        >
                        <select
                            v-model="selectedType"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            @change="applyFilters"
                        >
                            <option value="">All Types</option>
                            <option value="general">General</option>
                            <option value="viewing">Viewing</option>
                            <option value="purchase">Purchase</option>
                            <option value="information">Information</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Date From</label
                        >
                        <input
                            v-model="dateFrom"
                            type="date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Date To</label
                        >
                        <input
                            v-model="dateTo"
                            type="date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Response Time</label
                        >
                        <select
                            v-model="selectedResponseTime"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            @change="applyFilters"
                        >
                            <option value="">Any Time</option>
                            <option value="under_1h">Under 1 hour</option>
                            <option value="under_24h">Under 24 hours</option>
                            <option value="over_24h">Over 24 hours</option>
                            <option value="over_48h">
                                Over 48 hours (Overdue)
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6">
                    <button
                        @click="clearFilters"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium transition-colors"
                    >
                        Clear all filters
                    </button>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm text-gray-600">View:</span>
                        <button
                            @click="viewMode = 'grid'"
                            :class="
                                viewMode === 'grid'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-200 text-gray-700'
                            "
                            class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
                        >
                            Grid
                        </button>
                        <button
                            @click="viewMode = 'list'"
                            :class="
                                viewMode === 'list'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-200 text-gray-700'
                            "
                            class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
                        >
                            List
                        </button>
                        <button
                            @click="viewMode = 'table'"
                            :class="
                                viewMode === 'table'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-200 text-gray-700'
                            "
                            class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
                        >
                            Table
                        </button>
                    </div>
                </div>
            </div>

            <!-- Inquiries Display -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        All Inquiries ({{ inquiries.total }})
                    </h2>
                    <div class="flex items-center space-x-2">
                        <select
                            v-model="sortBy"
                            @change="applyFilters"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="created_at">Sort by Date</option>
                            <option value="priority">Sort by Priority</option>
                            <option value="status">Sort by Status</option>
                            <option value="broker">Sort by Broker</option>
                            <option value="response_time">
                                Sort by Response Time
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Grid View -->
                <div
                    v-if="viewMode === 'grid' && inquiries?.data?.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <div
                        v-for="inquiry in inquiries?.data || []"
                        :key="inquiry.id"
                        :class="[
                            'bg-white border-2 rounded-xl p-6 hover:shadow-lg transition-all duration-200',
                            getInquiryBorderClass(inquiry),
                        ]"
                    >
                        <!-- Header with Status and Priority -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-2">
                                <h3 class="font-bold text-gray-900">
                                    {{ inquiry.name }}
                                </h3>
                                <span
                                    v-if="isOverdue(inquiry)"
                                    class="flex items-center text-red-500"
                                    title="Overdue"
                                >
                                    <svg
                                        class="w-5 h-5"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"
                                        ></path>
                                    </svg>
                                </span>
                            </div>
                            <div class="flex flex-col items-end space-y-1">
                                <span
                                    :class="getStatusColor(inquiry.status)"
                                    class="px-3 py-1 text-xs font-bold rounded-full"
                                >
                                    {{ inquiry.status.toUpperCase() }}
                                </span>
                            </div>
                        </div>

                        <!-- Broker Assignment -->
                        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div
                                        class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center"
                                    >
                                        <span
                                            class="text-xs font-bold text-indigo-600"
                                        >
                                            {{
                                                inquiry.broker?.name?.charAt(
                                                    0
                                                ) || "?"
                                            }}
                                        </span>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{
                                                inquiry.broker?.name ||
                                                "Unassigned"
                                            }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Assigned Broker
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact & Property Info -->
                        <div class="space-y-2 mb-4 text-sm">
                            <div class="flex items-center text-gray-600">
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
                                        d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                    ></path>
                                </svg>
                                {{ inquiry.email }}
                            </div>
                            <div
                                v-if="inquiry.property"
                                class="flex items-center text-gray-600"
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
                                {{ inquiry.property.title }}
                            </div>
                        </div>

                        <!-- Response Time -->
                        <div class="mb-4 p-2 bg-blue-50 rounded text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"
                                    >Response Time:</span
                                >
                                <span
                                    :class="getResponseTimeClass(inquiry)"
                                    class="font-bold"
                                >
                                    {{ getResponseTime(inquiry) }}
                                </span>
                            </div>
                        </div>

                        <!-- Admin Actions -->
                        <div
                            class="flex flex-wrap gap-2 pt-4 border-t border-gray-200"
                        >
                            <Link
                                :href="
                                    route('admin.inquiries.show', inquiry.id)
                                "
                                class="w-full bg-indigo-600 text-white text-center py-2 px-3 rounded-lg text-xs font-medium hover:bg-indigo-700 transition-colors"
                            >
                                View Details
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div
                    v-if="viewMode === 'table' && inquiries?.data?.length > 0"
                    class="overflow-x-auto"
                >
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Inquiry
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Broker
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Property
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Response Time
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Date
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
                                v-for="inquiry in inquiries?.data || []"
                                :key="inquiry.id"
                                :class="isOverdue(inquiry) ? 'bg-red-50' : ''"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ inquiry.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ inquiry.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{
                                            inquiry.broker?.name || "Unassigned"
                                        }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ inquiry.property?.title || "N/A" }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getStatusColor(inquiry.status)"
                                        class="px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ inquiry.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getResponseTimeClass(inquiry)"
                                        class="text-sm font-medium"
                                    >
                                        {{ getResponseTime(inquiry) }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ formatDate(inquiry.created_at) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'admin.inquiries.show',
                                                inquiry.id
                                            )
                                        "
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="!inquiries?.data?.length" class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">📊</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        No inquiries found
                    </h3>
                    <p class="text-gray-500">
                        Try adjusting your filters to see more results.
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="inquiries?.data?.length > 0" class="mt-6">
                    <Pagination :links="inquiries?.links || []" />
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
    brokers: Array,
    filters: {
        type: Object,
        default: () => ({}),
    },
    systemStats: {
        type: Object,
        default: () => ({}),
    },
    metrics: {
        type: Object,
        default: () => ({}),
    },
});

// Filter states
const search = ref(props.filters?.search || "");
const selectedStatus = ref(props.filters?.status || "");
const selectedBroker = ref(props.filters?.broker_id || "");
const selectedProperty = ref(props.filters?.property_id || "");
const selectedPriority = ref(props.filters?.priority || "");
const selectedType = ref(props.filters?.inquiry_type || "");
const dateFrom = ref(props.filters?.date_from || "");
const dateTo = ref(props.filters?.date_to || "");
const selectedResponseTime = ref(props.filters?.response_time || "");
const sortBy = ref(props.filters?.sort_by || "created_at");

// View states
const viewMode = ref("grid");
const showAdvancedFilters = ref(false);
const showAnalytics = ref(false);

const applyFilters = () => {
    router.get(
        route("admin.inquiries.index"),
        {
            search: search.value,
            status: selectedStatus.value,
            broker_id: selectedBroker.value,
            property_id: selectedProperty.value,
            priority: selectedPriority.value,
            inquiry_type: selectedType.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
            response_time: selectedResponseTime.value,
            sort_by: sortBy.value,
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
    selectedBroker.value = "";
    selectedProperty.value = "";
    selectedPriority.value = "";
    selectedType.value = "";
    dateFrom.value = "";
    dateTo.value = "";
    selectedResponseTime.value = "";
    applyFilters();
};

const toggleAdvancedFilters = () => {
    showAdvancedFilters.value = !showAdvancedFilters.value;
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

const getPriorityLevel = (inquiry) => {
    const daysSinceCreated = Math.floor(
        (new Date() - new Date(inquiry.created_at)) / (1000 * 60 * 60 * 24)
    );
    if (inquiry.inquiry_type === "purchase" || daysSinceCreated > 3)
        return "HIGH";
    if (inquiry.inquiry_type === "viewing" || daysSinceCreated > 1)
        return "MED";
    return "LOW";
};

const getPriorityBadgeClass = (inquiry) => {
    const priority = getPriorityLevel(inquiry);
    const classes = {
        HIGH: "bg-red-100 text-red-800",
        MED: "bg-yellow-100 text-yellow-800",
        LOW: "bg-green-100 text-green-800",
    };
    return classes[priority];
};

const getInquiryBorderClass = (inquiry) => {
    if (isOverdue(inquiry)) return "border-red-500";
    const priority = getPriorityLevel(inquiry);
    const classes = {
        HIGH: "border-red-300",
        MED: "border-yellow-300",
        LOW: "border-green-300",
    };
    return classes[priority];
};

const isOverdue = (inquiry) => {
    const daysSinceCreated = Math.floor(
        (new Date() - new Date(inquiry.created_at)) / (1000 * 60 * 60 * 24)
    );
    return daysSinceCreated > 2 && inquiry.status === "new";
};

const getResponseTime = (inquiry) => {
    if (!inquiry.responded_at) {
        const hoursSinceCreated = Math.floor(
            (new Date() - new Date(inquiry.created_at)) / (1000 * 60 * 60)
        );
        if (hoursSinceCreated < 1) return "Just now";
        if (hoursSinceCreated < 24) return `${hoursSinceCreated}h pending`;
        return `${Math.floor(hoursSinceCreated / 24)}d pending`;
    }
    const responseTime =
        new Date(inquiry.responded_at) - new Date(inquiry.created_at);
    const hours = Math.floor(responseTime / (1000 * 60 * 60));
    if (hours < 1) return "< 1h";
    if (hours < 24) return `${hours}h`;
    return `${Math.floor(hours / 24)}d`;
};

const getResponseTimeClass = (inquiry) => {
    if (!inquiry.responded_at) {
        const hoursSinceCreated = Math.floor(
            (new Date() - new Date(inquiry.created_at)) / (1000 * 60 * 60)
        );
        if (hoursSinceCreated > 48) return "text-red-600";
        if (hoursSinceCreated > 24) return "text-orange-600";
        return "text-yellow-600";
    }
    const responseTime =
        new Date(inquiry.responded_at) - new Date(inquiry.created_at);
    const hours = Math.floor(responseTime / (1000 * 60 * 60));
    if (hours < 1) return "text-green-600";
    if (hours < 24) return "text-blue-600";
    return "text-gray-600";
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>
