<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Admin Activity Audit
                    </h1>
                    <p class="text-gray-600">
                        Monitor and track all administrative activities
                    </p>
                </div>
                <div class="flex space-x-3">
                    <button
                        @click="exportActivities"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                        Export CSV
                    </button>
                    <button
                        @click="refreshData"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <ArrowPathIcon class="w-4 h-4 mr-2" />
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <ClockIcon class="h-6 w-6 text-blue-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Today's Activities
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.today?.total_activities || 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <CalendarIcon class="h-6 w-6 text-green-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        This Week
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.week?.total_activities || 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <ChartBarIcon class="h-6 w-6 text-purple-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        This Month
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.month?.total_activities || 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <UsersIcon class="h-6 w-6 text-orange-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Active Admins
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.today?.unique_admins || 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow rounded-lg p-6">
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Admin</label
                        >
                        <select
                            v-model="filters.admin_id"
                            @change="applyFilters"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                        >
                            <option value="">All Admins</option>
                            <option
                                v-for="admin in adminUsers"
                                :key="admin.id"
                                :value="admin.id"
                            >
                                {{ admin.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Action</label
                        >
                        <select
                            v-model="filters.action"
                            @change="applyFilters"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                        >
                            <option value="">All Actions</option>
                            <option
                                v-for="(label, value) in availableActions"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Target Type</label
                        >
                        <select
                            v-model="filters.target_type"
                            @change="applyFilters"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                        >
                            <option value="">All Types</option>
                            <option
                                v-for="(label, value) in availableTargetTypes"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Search</label
                        >
                        <input
                            v-model="filters.search"
                            @input="debounceSearch"
                            type="text"
                            placeholder="Search activities..."
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        />
                    </div>
                </div>

                <div class="mt-4 flex justify-between">
                    <button
                        @click="clearFilters"
                        class="text-sm text-gray-500 hover:text-gray-700"
                    >
                        Clear Filters
                    </button>
                    <div class="flex space-x-2">
                        <button
                            @click="setDateRange('today')"
                            :class="[
                                'px-3 py-1 text-xs font-medium rounded-full',
                                dateRange === 'today'
                                    ? 'bg-blue-100 text-blue-800'
                                    : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                            ]"
                        >
                            Today
                        </button>
                        <button
                            @click="setDateRange('week')"
                            :class="[
                                'px-3 py-1 text-xs font-medium rounded-full',
                                dateRange === 'week'
                                    ? 'bg-blue-100 text-blue-800'
                                    : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                            ]"
                        >
                            This Week
                        </button>
                        <button
                            @click="setDateRange('month')"
                            :class="[
                                'px-3 py-1 text-xs font-medium rounded-full',
                                dateRange === 'month'
                                    ? 'bg-blue-100 text-blue-800'
                                    : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                            ]"
                        >
                            This Month
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activities Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Activity Log
                        </h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">
                                {{ activities.total }} total activities
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Time
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Admin
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Action
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Target
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Details
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        IP Address
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="activity in activities.data"
                                    :key="activity.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{
                                            formatDateTime(activity.created_at)
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div
                                                    class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center"
                                                >
                                                    <UserIcon
                                                        class="h-4 w-4 text-gray-500"
                                                    />
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div
                                                    class="text-sm font-medium text-gray-900"
                                                >
                                                    {{
                                                        activity.admin?.name ||
                                                        "Unknown"
                                                    }}
                                                </div>
                                                <div
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{
                                                        activity.admin?.email ||
                                                        ""
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="
                                                getActionBadgeClass(
                                                    activity.action
                                                )
                                            "
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{
                                                getActionLabel(activity.action)
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        <div v-if="activity.target_type">
                                            <div class="text-sm font-medium">
                                                {{
                                                    getTargetTypeLabel(
                                                        activity.target_type
                                                    )
                                                }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: {{ activity.target_id }}
                                            </div>
                                        </div>
                                        <span v-else class="text-gray-400"
                                            >N/A</span
                                        >
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-500 max-w-xs"
                                    >
                                        <div
                                            v-if="activity.formatted_details"
                                            class="truncate"
                                            :title="activity.formatted_details"
                                        >
                                            {{ activity.formatted_details }}
                                        </div>
                                        <span v-else class="text-gray-400"
                                            >No details</span
                                        >
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ activity.ip_address || "N/A" }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="activities.links" class="mt-6">
                        <nav class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <Link
                                    v-if="activities.prev_page_url"
                                    :href="activities.prev_page_url"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="activities.next_page_url"
                                    :href="activities.next_page_url"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Next
                                </Link>
                            </div>
                            <div
                                class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing
                                        <span class="font-medium">{{
                                            activities.from
                                        }}</span>
                                        to
                                        <span class="font-medium">{{
                                            activities.to
                                        }}</span>
                                        of
                                        <span class="font-medium">{{
                                            activities.total
                                        }}</span>
                                        results
                                    </p>
                                </div>
                                <div>
                                    <nav
                                        class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                    >
                                        <template
                                            v-for="(
                                                link, index
                                            ) in activities.links"
                                            :key="index"
                                        >
                                            <Link
                                                v-if="link.url"
                                                :href="link.url"
                                                :class="[
                                                    link.active
                                                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                ]"
                                            >
                                                <span
                                                    v-html="link.label"
                                                ></span>
                                            </Link>
                                            <span
                                                v-else
                                                :class="[
                                                    'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700',
                                                ]"
                                            >
                                                <span
                                                    v-html="link.label"
                                                ></span>
                                            </span>
                                        </template>
                                    </nav>
                                </div>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    ClockIcon,
    CalendarIcon,
    ChartBarIcon,
    UsersIcon,
    UserIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    activities: Object,
    stats: Object,
    filters: Object,
    availableActions: Object,
    availableTargetTypes: Object,
    adminUsers: Array,
});

// Reactive data
const dateRange = ref("today");
const searchTimeout = ref(null);

const filters = reactive({
    admin_id: props.filters.admin_id || "",
    action: props.filters.action || "",
    target_type: props.filters.target_type || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
    search: props.filters.search || "",
});

// Methods
const applyFilters = () => {
    router.get(route("admin.activity.index"), filters, {
        preserveState: true,
        replace: true,
    });
};

const debounceSearch = () => {
    clearTimeout(searchTimeout.value);
    searchTimeout.value = setTimeout(() => {
        applyFilters();
    }, 500);
};

const clearFilters = () => {
    Object.keys(filters).forEach((key) => {
        filters[key] = "";
    });
    applyFilters();
};

const setDateRange = (range) => {
    dateRange.value = range;
    const today = new Date();

    switch (range) {
        case "today":
            filters.date_from = today.toISOString().split("T")[0];
            filters.date_to = today.toISOString().split("T")[0];
            break;
        case "week":
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay());
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6);
            filters.date_from = startOfWeek.toISOString().split("T")[0];
            filters.date_to = endOfWeek.toISOString().split("T")[0];
            break;
        case "month":
            const startOfMonth = new Date(
                today.getFullYear(),
                today.getMonth(),
                1
            );
            const endOfMonth = new Date(
                today.getFullYear(),
                today.getMonth() + 1,
                0
            );
            filters.date_from = startOfMonth.toISOString().split("T")[0];
            filters.date_to = endOfMonth.toISOString().split("T")[0];
            break;
    }

    applyFilters();
};

const exportActivities = () => {
    const params = new URLSearchParams(filters);
    window.open(route("admin.activity.export") + "?" + params.toString());
};

const refreshData = () => {
    router.reload();
};

const getActionBadgeClass = (action) => {
    const classes = {
        user_created: "bg-green-100 text-green-800",
        user_updated: "bg-blue-100 text-blue-800",
        user_suspended: "bg-red-100 text-red-800",
        user_reactivated: "bg-green-100 text-green-800",
        user_approved: "bg-green-100 text-green-800",
        user_rejected: "bg-red-100 text-red-800",
        property_created: "bg-green-100 text-green-800",
        property_updated: "bg-blue-100 text-blue-800",
        property_deleted: "bg-red-100 text-red-800",
        transaction_created: "bg-green-100 text-green-800",
        transaction_updated: "bg-blue-100 text-blue-800",
        seller_request_approved: "bg-green-100 text-green-800",
        seller_request_rejected: "bg-red-100 text-red-800",
        bulk_user_action: "bg-purple-100 text-purple-800",
        system_settings_updated: "bg-yellow-100 text-yellow-800",
        compliance_report_created: "bg-orange-100 text-orange-800",
        compliance_report_updated: "bg-blue-100 text-blue-800",
    };
    return classes[action] || "bg-gray-100 text-gray-800";
};

const getActionLabel = (action) => {
    return props.availableActions[action] || action;
};

const getTargetTypeLabel = (targetType) => {
    return props.availableTargetTypes[targetType] || targetType;
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Initialize date range on mount
onMounted(() => {
    if (!filters.date_from && !filters.date_to) {
        setDateRange("today");
    }
});
</script>
