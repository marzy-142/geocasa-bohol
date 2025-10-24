<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Broker Reports
                    </h1>
                    <p class="text-gray-600">
                        Performance analytics and broker insights
                    </p>
                </div>
                <div class="flex space-x-3">
                    <button
                        @click="exportReport"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                        Export Report
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

            <!-- Broker Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <UsersIcon class="h-6 w-6 text-blue-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Total Brokers
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.total_brokers }}
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
                                <CheckCircleIcon
                                    class="h-6 w-6 text-green-400"
                                />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Active Brokers
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.active_brokers }}
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
                                <ClockIcon class="h-6 w-6 text-yellow-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Pending Approval
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.pending_brokers }}
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
                                        Avg Response Rate
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.avg_response_rate }}%
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Registration Trends -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Registration Trends
                    </h3>
                    <div class="h-64 flex items-center justify-center">
                        <div class="text-center">
                            <ChartBarIcon
                                class="h-12 w-12 text-gray-400 mx-auto mb-2"
                            />
                            <p class="text-gray-500">
                                Registration chart would go here
                            </p>
                            <p class="text-sm text-gray-400">
                                Integration with Chart.js or similar library
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Performance Distribution -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Performance Distribution
                    </h3>
                    <div class="h-64 flex items-center justify-center">
                        <div class="text-center">
                            <ChartPieIcon
                                class="h-12 w-12 text-gray-400 mx-auto mb-2"
                            />
                            <p class="text-gray-500">
                                Performance chart would go here
                            </p>
                            <p class="text-sm text-gray-400">
                                Integration with Chart.js or similar library
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performing Brokers -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        Top Performing Brokers
                    </h3>
                    <div class="flex space-x-2">
                        <select
                            v-model="performanceFilter"
                            @change="filterBrokers"
                            class="text-sm border-gray-300 rounded-md"
                        >
                            <option value="all">All Time</option>
                            <option value="month">This Month</option>
                            <option value="week">This Week</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Rank
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Broker
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Properties
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Inquiries
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Response Rate
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="(broker, index) in topBrokers"
                                :key="broker.id"
                                class="hover:bg-gray-50"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                >
                                    <div class="flex items-center">
                                        <div
                                            :class="
                                                getRankBadgeClass(index + 1)
                                            "
                                            class="h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold"
                                        >
                                            {{ index + 1 }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div
                                                class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center"
                                            >
                                                <UserIcon
                                                    class="h-5 w-5 text-gray-500"
                                                />
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ broker.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ broker.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{ broker.properties_count }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{ broker.inquiries_count }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{ calculateResponseRate(broker) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getStatusBadgeClass(broker)"
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ getStatusLabel(broker) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Broker Activities -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Recent Broker Activities
                </h3>
                <div class="flow-root">
                    <ul class="-mb-8">
                        <li
                            v-for="(activity, index) in recentActivities"
                            :key="index"
                            class="relative pb-8"
                        >
                            <div
                                v-if="index !== recentActivities.length - 1"
                                class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                            ></div>
                            <div class="relative flex space-x-3">
                                <div>
                                    <span
                                        :class="
                                            getActivityIconClass(activity.type)
                                        "
                                        class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                    >
                                        <component
                                            :is="getActivityIcon(activity.type)"
                                            class="h-4 w-4"
                                        />
                                    </span>
                                </div>
                                <div
                                    class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4"
                                >
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            {{ activity.description }}
                                        </p>
                                    </div>
                                    <div
                                        class="text-right text-sm whitespace-nowrap text-gray-500"
                                    >
                                        <time>
                                            {{
                                                formatDateTime(
                                                    activity.timestamp
                                                )
                                            }}
                                        </time>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    UsersIcon,
    CheckCircleIcon,
    ClockIcon,
    ChartBarIcon,
    ChartPieIcon,
    UserIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
    UserPlusIcon,
    CheckIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    stats: Object,
    chartData: Object,
    topBrokers: Array,
    recentActivities: Array,
});

// Reactive data
const performanceFilter = ref("all");

// Methods
const exportReport = () => {
    alert("Export functionality would be implemented here");
};

const refreshData = () => {
    router.reload();
};

const filterBrokers = () => {
    // Implementation for filtering brokers by time period
    console.log("Filtering brokers by:", performanceFilter.value);
};

const getRankBadgeClass = (rank) => {
    if (rank === 1) return "bg-yellow-100 text-yellow-800";
    if (rank === 2) return "bg-gray-100 text-gray-800";
    if (rank === 3) return "bg-orange-100 text-orange-800";
    return "bg-blue-100 text-blue-800";
};

const getStatusBadgeClass = (broker) => {
    if (broker.is_approved && broker.application_status === "approved") {
        return "bg-green-100 text-green-800";
    }
    if (broker.application_status === "pending") {
        return "bg-yellow-100 text-yellow-800";
    }
    return "bg-red-100 text-red-800";
};

const getStatusLabel = (broker) => {
    if (broker.is_approved && broker.application_status === "approved") {
        return "Active";
    }
    if (broker.application_status === "pending") {
        return "Pending";
    }
    return "Inactive";
};

const calculateResponseRate = (broker) => {
    // Mock calculation - in real app, this would be calculated from actual data
    return Math.floor(Math.random() * 40) + 60; // 60-100%
};

const getActivityIconClass = (type) => {
    const classes = {
        broker_registration: "bg-blue-100 text-blue-600",
        broker_approval: "bg-green-100 text-green-600",
    };
    return classes[type] || "bg-gray-100 text-gray-600";
};

const getActivityIcon = (type) => {
    const icons = {
        broker_registration: UserPlusIcon,
        broker_approval: CheckIcon,
    };
    return icons[type] || ClockIcon;
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
</script>
