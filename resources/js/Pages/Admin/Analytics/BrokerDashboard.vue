<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Broker Analytics Dashboard
                    </h1>
                    <p class="text-gray-600">
                        Performance insights and analytics for brokers
                    </p>
                </div>
                <div class="flex space-x-3">
                    <button
                        @click="exportAnalytics"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                        Export Analytics
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

            <!-- Filters -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Time Range
                        </label>
                        <select
                            v-model="filters.time_range"
                            @change="applyFilters"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                        >
                            <option value="7">Last 7 days</option>
                            <option value="30">Last 30 days</option>
                            <option value="90">Last 90 days</option>
                            <option value="365">Last year</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Broker
                        </label>
                        <select
                            v-model="filters.broker_id"
                            @change="applyFilters"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
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

                    <div class="flex items-end">
                        <button
                            @click="clearFilters"
                            class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Overall Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <UserGroupIcon class="h-6 w-6 text-blue-400" />
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
                                        {{ overallStats.total_brokers }}
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
                                <HomeIcon class="h-6 w-6 text-green-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Properties Listed
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ overallStats.total_properties }}
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
                                <CurrencyDollarIcon
                                    class="h-6 w-6 text-purple-400"
                                />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Total Sales Value
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        ₱{{
                                            formatNumber(
                                                overallStats.total_commission
                                            )
                                        }}
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
                                <ChartBarIcon class="h-6 w-6 text-orange-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Conversion Rate
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ overallStats.conversion_rate }}%
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Performance Trends -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Performance Trends
                    </h3>
                    <div class="h-64">
                        <AnalyticsChart
                            type="line"
                            :data="performanceTrendsData"
                            :options="performanceTrendsOptions"
                        />
                    </div>
                </div>

                <!-- Commission Analytics -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Sales Value Analytics
                    </h3>
                    <div class="h-64">
                        <AnalyticsChart
                            type="doughnut"
                            :data="commissionAnalyticsData"
                            :options="commissionAnalyticsOptions"
                        />
                    </div>
                </div>
            </div>

            <!-- Top Performing Brokers -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        Top Performing Brokers
                    </h3>
                    <Link
                        :href="route('admin.reports.brokers')"
                        class="text-sm text-blue-600 hover:text-blue-500"
                    >
                        View Detailed Report
                    </Link>
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
                                    Transactions
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Sales Value
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Performance Score
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
                                    {{ broker.transactions_count }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    ₱{{ formatNumber(broker.commission) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="w-16 bg-gray-200 rounded-full h-2 mr-2"
                                        >
                                            <div
                                                :class="
                                                    getPerformanceColor(
                                                        broker.performance_score
                                                    )
                                                "
                                                class="h-2 rounded-full"
                                                :style="{
                                                    width:
                                                        broker.performance_score +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                        <span class="text-sm text-gray-600">
                                            {{
                                                Math.round(
                                                    broker.performance_score
                                                )
                                            }}
                                        </span>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'admin.analytics.brokers.show',
                                                broker.id
                                            )
                                        "
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        View Details
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Property Analytics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Properties by Type
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="(
                                count, type
                            ) in propertyAnalytics.properties_by_type"
                            :key="type"
                            class="flex items-center justify-between"
                        >
                            <div class="flex items-center">
                                <div
                                    :class="getPropertyTypeColor(type)"
                                    class="w-3 h-3 rounded-full mr-3"
                                ></div>
                                <span class="text-sm font-medium text-gray-700">
                                    {{ formatPropertyType(type) }}
                                </span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">
                                {{ count }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Price Range Distribution
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="(
                                count, range
                            ) in propertyAnalytics.price_range_distribution"
                            :key="range"
                            class="flex items-center justify-between"
                        >
                            <div class="flex items-center">
                                <div
                                    :class="getPriceRangeColor(range)"
                                    class="w-3 h-3 rounded-full mr-3"
                                ></div>
                                <span class="text-sm font-medium text-gray-700">
                                    {{ range }}
                                </span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">
                                {{ count }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Analytics -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Client Analytics
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">
                            {{ clientAnalytics.total_clients }}
                        </div>
                        <div class="text-sm text-gray-500">Total Clients</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">
                            {{ clientAnalytics.new_clients }}
                        </div>
                        <div class="text-sm text-gray-500">New Clients</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">
                            {{ clientAnalytics.client_acquisition_rate }}%
                        </div>
                        <div class="text-sm text-gray-500">
                            Acquisition Rate
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600">
                            {{ clientAnalytics.client_retention_rate }}%
                        </div>
                        <div class="text-sm text-gray-500">Retention Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import AnalyticsChart from "@/Components/AnalyticsChart.vue";
import {
    UserGroupIcon,
    HomeIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    ChartBarSquareIcon,
    ChartPieIcon,
    UserIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    overallStats: Object,
    topBrokers: Array,
    performanceTrends: Array,
    commissionAnalytics: Object,
    propertyAnalytics: Object,
    clientAnalytics: Object,
    brokers: Array,
    filters: Object,
});

// Reactive data
const filters = reactive({
    time_range: props.filters.time_range || "30",
    broker_id: props.filters.broker_id || "",
});

// Methods
const applyFilters = () => {
    router.get(route("admin.analytics.brokers.index"), filters, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    filters.time_range = "30";
    filters.broker_id = "";
    applyFilters();
};

const exportAnalytics = () => {
    const params = new URLSearchParams(filters);
    window.open(
        route("admin.analytics.brokers.export") + "?" + params.toString()
    );
};

const refreshData = () => {
    router.reload();
};

const getRankBadgeClass = (rank) => {
    if (rank === 1) return "bg-yellow-100 text-yellow-800";
    if (rank === 2) return "bg-gray-100 text-gray-800";
    if (rank === 3) return "bg-orange-100 text-orange-800";
    return "bg-blue-100 text-blue-800";
};

const getPerformanceColor = (score) => {
    if (score >= 80) return "bg-green-500";
    if (score >= 60) return "bg-yellow-500";
    if (score >= 40) return "bg-orange-500";
    return "bg-red-500";
};

const getPropertyTypeColor = (type) => {
    const colors = {
        residential_lot: "bg-blue-500",
        agricultural_land: "bg-green-500",
        beachfront: "bg-yellow-500",
        commercial_lot: "bg-purple-500",
        titled_land: "bg-orange-500",
    };
    return colors[type] || "bg-gray-500";
};

const getPriceRangeColor = (range) => {
    const colors = {
        "0-1M": "bg-green-500",
        "1M-5M": "bg-blue-500",
        "5M-10M": "bg-yellow-500",
        "10M+": "bg-red-500",
    };
    return colors[range] || "bg-gray-500";
};

const formatPropertyType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatNumber = (number) => {
    return new Intl.NumberFormat("en-US").format(number || 0);
};

// Chart data and options
const performanceTrendsData = computed(() => ({
    labels: ["Week 1", "Week 2", "Week 3", "Week 4"],
    datasets: [
        {
            label: "Properties Listed",
            data: [2, 3, 1, 4],
            borderColor: "rgb(59, 130, 246)",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            tension: 0.4,
            fill: true,
        },
        {
            label: "Transactions Completed",
            data: [1, 2, 1, 3],
            borderColor: "rgb(16, 185, 129)",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            tension: 0.4,
            fill: true,
        },
        {
            label: "Commission Earned (₱10k)",
            data: [5, 8, 4, 12],
            borderColor: "rgb(168, 85, 247)",
            backgroundColor: "rgba(168, 85, 247, 0.1)",
            tension: 0.4,
            fill: true,
        },
    ],
}));

const performanceTrendsOptions = {
    plugins: {
        legend: {
            position: "top",
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
    interaction: {
        intersect: false,
        mode: "index",
    },
};

const commissionAnalyticsData = computed(() => ({
    labels: ["Maria Santos", "Juan Dela Cruz", "Pedro Reyes"],
    datasets: [
        {
            data: [150000, 108000, 0],
            backgroundColor: [
                "rgba(59, 130, 246, 0.8)",
                "rgba(16, 185, 129, 0.8)",
                "rgba(245, 158, 11, 0.8)",
            ],
            borderColor: [
                "rgb(59, 130, 246)",
                "rgb(16, 185, 129)",
                "rgb(245, 158, 11)",
            ],
            borderWidth: 2,
        },
    ],
}));

const commissionAnalyticsOptions = {
    plugins: {
        legend: {
            position: "bottom",
        },
        tooltip: {
            callbacks: {
                label: function (context) {
                    return context.label + ": ₱" + formatNumber(context.parsed);
                },
            },
        },
    },
};
</script>
