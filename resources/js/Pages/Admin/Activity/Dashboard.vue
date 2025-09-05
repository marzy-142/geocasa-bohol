<template>
    <AdminLayout>
        <div class="space-y-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                        🔍 Admin Activity Audit
                    </h1>
                    <p class="text-neutral-600">
                        Monitor and track all administrative activities across
                        the system
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <ModernButton
                        @click="exportActivities"
                        variant="outline"
                        :loading="exporting"
                    >
                        📊 Export CSV
                    </ModernButton>
                    <ModernButton
                        @click="refreshData"
                        variant="outline"
                        :loading="refreshing"
                    >
                        🔄 Refresh
                    </ModernButton>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-3xl border border-neutral-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-neutral-600 mb-1">
                                Today's Activities
                            </p>
                            <p class="text-2xl font-bold text-blue-600">
                                {{ stats.today.total_activities || 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center"
                        >
                            <ClockIcon class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-neutral-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-neutral-600 mb-1">
                                This Week
                            </p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ stats.week.total_activities || 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center"
                        >
                            <CalendarIcon class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-neutral-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-neutral-600 mb-1">
                                This Month
                            </p>
                            <p class="text-2xl font-bold text-purple-600">
                                {{ stats.month.total_activities || 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center"
                        >
                            <ChartBarIcon class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-neutral-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-neutral-600 mb-1">
                                Active Admins
                            </p>
                            <p class="text-2xl font-bold text-orange-600">
                                {{ stats.today.unique_admins || 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center"
                        >
                            <UsersIcon class="w-6 h-6 text-orange-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-3xl border border-neutral-100 p-6">
                <h3 class="text-lg font-semibold text-neutral-900 mb-4">
                    🔍 Filters
                </h3>
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <!-- Search -->
                    <div>
                        <label
                            class="block text-sm font-medium text-neutral-700 mb-2"
                        >
                            Search
                        </label>
                        <input
                            v-model="localFilters.search"
                            type="text"
                            placeholder="Search activities..."
                            class="w-full px-4 py-2 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @input="debouncedSearch"
                        />
                    </div>

                    <!-- Admin Filter -->
                    <div>
                        <label
                            class="block text-sm font-medium text-neutral-700 mb-2"
                        >
                            Admin
                        </label>
                        <select
                            v-model="localFilters.admin_id"
                            class="w-full px-4 py-2 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
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

                    <!-- Action Filter -->
                    <div>
                        <label
                            class="block text-sm font-medium text-neutral-700 mb-2"
                        >
                            Action
                        </label>
                        <select
                            v-model="localFilters.action"
                            class="w-full px-4 py-2 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="">All Actions</option>
                            <option
                                v-for="(label, action) in availableActions"
                                :key="action"
                                :value="action"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Target Type Filter -->
                    <div>
                        <label
                            class="block text-sm font-medium text-neutral-700 mb-2"
                        >
                            Target Type
                        </label>
                        <select
                            v-model="localFilters.target_type"
                            class="w-full px-4 py-2 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="">All Types</option>
                            <option
                                v-for="(label, type) in availableTargetTypes"
                                :key="type"
                                :value="type"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-neutral-700 mb-2"
                        >
                            From Date
                        </label>
                        <input
                            v-model="localFilters.date_from"
                            type="date"
                            class="w-full px-4 py-2 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-neutral-700 mb-2"
                        >
                            To Date
                        </label>
                        <input
                            v-model="localFilters.date_to"
                            type="date"
                            class="w-full px-4 py-2 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @change="applyFilters"
                        />
                    </div>
                </div>

                <!-- Clear Filters -->
                <div class="mt-4">
                    <ModernButton
                        @click="clearFilters"
                        variant="outline"
                        size="sm"
                    >
                        Clear Filters
                    </ModernButton>
                </div>
            </div>

            <!-- Activities Table -->
            <div
                class="bg-white rounded-3xl border border-neutral-100 overflow-hidden"
            >
                <div class="p-6 border-b border-neutral-100">
                    <h3 class="text-lg font-semibold text-neutral-900">
                        📋 Activity Log
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-neutral-50">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-sm font-medium text-neutral-600"
                                >
                                    Date & Time
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-sm font-medium text-neutral-600"
                                >
                                    Admin
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-sm font-medium text-neutral-600"
                                >
                                    Action
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-sm font-medium text-neutral-600"
                                >
                                    Target
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-sm font-medium text-neutral-600"
                                >
                                    Details
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-sm font-medium text-neutral-600"
                                >
                                    IP Address
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            <tr
                                v-for="activity in activities.data"
                                :key="activity.id"
                                class="hover:bg-neutral-50 transition-colors"
                            >
                                <td class="px-6 py-4 text-sm text-neutral-900">
                                    {{ formatDateTime(activity.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center"
                                        >
                                            <UserIcon
                                                class="w-4 h-4 text-blue-600"
                                            />
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-medium text-neutral-900"
                                            >
                                                {{
                                                    activity.admin?.name ||
                                                    "Unknown"
                                                }}
                                            </p>
                                            <p class="text-xs text-neutral-500">
                                                {{
                                                    activity.admin?.email ||
                                                    "N/A"
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="[
                                            'px-3 py-1 rounded-full text-xs font-medium',
                                            activity.action_badge,
                                        ]"
                                    >
                                        {{ getActionLabel(activity.action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-neutral-600">
                                    <div v-if="activity.target_type">
                                        <p class="font-medium">
                                            {{
                                                getTargetTypeLabel(
                                                    activity.target_type
                                                )
                                            }}
                                        </p>
                                        <p class="text-xs text-neutral-500">
                                            ID: {{ activity.target_id }}
                                        </p>
                                    </div>
                                    <span v-else class="text-neutral-400"
                                        >System</span
                                    >
                                </td>
                                <td class="px-6 py-4 text-sm text-neutral-600">
                                    <div
                                        v-if="activity.formatted_details"
                                        class="max-w-xs"
                                    >
                                        <p
                                            class="truncate"
                                            :title="activity.formatted_details"
                                        >
                                            {{ activity.formatted_details }}
                                        </p>
                                    </div>
                                    <span v-else class="text-neutral-400"
                                        >No details</span
                                    >
                                </td>
                                <td class="px-6 py-4 text-sm text-neutral-500">
                                    {{ activity.ip_address || "N/A" }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-neutral-100">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-neutral-600">
                            Showing {{ activities.from || 0 }} to
                            {{ activities.to || 0 }} of
                            {{ activities.total || 0 }} results
                        </p>
                        <div class="flex items-center gap-2">
                            <ModernButton
                                v-for="link in activities.links"
                                :key="link.label"
                                @click="changePage(link.url)"
                                :disabled="!link.url"
                                :variant="link.active ? 'primary' : 'outline'"
                                size="sm"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import {
    ClockIcon,
    CalendarIcon,
    ChartBarIcon,
    UsersIcon,
    UserIcon,
} from "@heroicons/vue/24/outline";
import { debounce } from "lodash";

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
const localFilters = reactive({ ...props.filters });
const refreshing = ref(false);
const exporting = ref(false);

// Methods
const applyFilters = () => {
    router.get(route("admin.activity.index"), localFilters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const debouncedSearch = debounce(() => {
    applyFilters();
}, 500);

const clearFilters = () => {
    Object.keys(localFilters).forEach((key) => {
        localFilters[key] = "";
    });
    applyFilters();
};

const refreshData = async () => {
    refreshing.value = true;
    try {
        await router.reload({ only: ["activities", "stats"] });
    } finally {
        refreshing.value = false;
    }
};

const exportActivities = async () => {
    exporting.value = true;
    try {
        const params = new URLSearchParams(localFilters).toString();
        window.open(`/admin/activity/export?${params}`, "_blank");
    } finally {
        exporting.value = false;
    }
};

const changePage = (url) => {
    if (!url) return;
    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const formatDateTime = (dateTime) => {
    return new Date(dateTime).toLocaleString();
};

const getActionLabel = (action) => {
    return props.availableActions[action] || action;
};

const getTargetTypeLabel = (targetType) => {
    return props.availableTargetTypes[targetType] || targetType;
};
</script>
