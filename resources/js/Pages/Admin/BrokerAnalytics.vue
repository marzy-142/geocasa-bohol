<template>
    <ModernDashboardLayout>
        <Head title="Broker Analytics" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                Broker Performance Analytics
                            </h1>
                            <p class="mt-2 text-gray-600">
                                Comprehensive analytics for broker-client
                                assignments and performance metrics
                            </p>
                        </div>

                        <div class="flex items-center space-x-4">
                            <button
                                @click="refreshData"
                                :disabled="loading"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors disabled:opacity-50"
                            >
                                <ArrowPathIcon
                                    v-if="loading"
                                    class="w-4 h-4 animate-spin"
                                />
                                <ArrowPathIcon v-else class="w-4 h-4" />
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
                >
                    <div
                        class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <UsersIcon class="h-8 w-8 text-blue-600" />
                                </div>
                                <div class="ml-4 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Total Active Brokers
                                        </dt>
                                        <dd
                                            class="text-2xl font-bold text-gray-900"
                                        >
                                            {{ brokerAnalytics.length }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <UserGroupIcon
                                        class="h-8 w-8 text-green-600"
                                    />
                                </div>
                                <div class="ml-4 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Total Clients Assigned
                                        </dt>
                                        <dd
                                            class="text-2xl font-bold text-gray-900"
                                        >
                                            {{ totalClientsAssigned }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ChartBarIcon
                                        class="h-8 w-8 text-purple-600"
                                    />
                                </div>
                                <div class="ml-4 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Avg Conversion Rate
                                        </dt>
                                        <dd
                                            class="text-2xl font-bold text-gray-900"
                                        >
                                            {{ averageConversionRate }}%
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200"
                    >
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <CurrencyDollarIcon
                                        class="h-8 w-8 text-yellow-600"
                                    />
                                </div>
                                <div class="ml-4 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Total Sales Value
                                        </dt>
                                        <dd
                                            class="text-2xl font-bold text-gray-900"
                                        >
                                            {{ totalSalesValue }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Broker Performance Table -->
                <div
                    class="bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Broker Performance Metrics
                        </h3>
                        <p class="text-sm text-gray-600">
                            Detailed performance analytics for each broker
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Broker
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Total Clients
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Active Clients
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Converted Clients
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Conversion Rate
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Completed Transactions
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Total Sales Value
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Performance Score
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="broker in sortedBrokers"
                                    :key="broker.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10"
                                            >
                                                <div
                                                    class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center"
                                                >
                                                    <span
                                                        class="text-sm font-medium text-blue-600"
                                                    >
                                                        {{
                                                            broker.name
                                                                .charAt(0)
                                                                .toUpperCase()
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div
                                                    class="text-sm font-medium text-gray-900"
                                                >
                                                    {{ broker.name }}
                                                </div>
                                                <div
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{ broker.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                        >
                                            {{ broker.total_clients }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                        >
                                            {{ broker.active_clients }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                                        >
                                            {{ broker.converted_clients }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        <div class="flex items-center">
                                            <div
                                                class="flex-1 bg-gray-200 rounded-full h-2 mr-2"
                                            >
                                                <div
                                                    class="h-2 rounded-full transition-all duration-300"
                                                    :class="
                                                        getConversionRateColor(
                                                            broker.conversion_rate
                                                        )
                                                    "
                                                    :style="{
                                                        width:
                                                            Math.min(
                                                                broker.conversion_rate,
                                                                100
                                                            ) + '%',
                                                    }"
                                                ></div>
                                            </div>
                                            <span class="text-sm font-medium"
                                                >{{
                                                    broker.conversion_rate
                                                }}%</span
                                            >
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                                        >
                                            {{ broker.completed_transactions }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                    >
                                        {{ broker.formatted_sales_value }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-1 bg-gray-200 rounded-full h-2 mr-2"
                                            >
                                                <div
                                                    class="h-2 rounded-full transition-all duration-300"
                                                    :class="
                                                        getPerformanceScoreColor(
                                                            getPerformanceScore(
                                                                broker
                                                            )
                                                        )
                                                    "
                                                    :style="{
                                                        width:
                                                            getPerformanceScore(
                                                                broker
                                                            ) + '%',
                                                    }"
                                                ></div>
                                            </div>
                                            <span class="text-sm font-medium"
                                                >{{
                                                    getPerformanceScore(broker)
                                                }}%</span
                                            >
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Performance Insights -->
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Top Performers -->
                    <div
                        class="bg-white shadow-lg rounded-xl border border-gray-200 p-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            🏆 Top Performers
                        </h3>
                        <div class="space-y-4">
                            <div
                                v-for="(broker, index) in topPerformers"
                                :key="broker.id"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                            >
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center mr-3"
                                    >
                                        <span
                                            class="text-sm font-bold text-yellow-600"
                                            >{{ index + 1 }}</span
                                        >
                                    </div>
                                    <div>
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ broker.name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ broker.conversion_rate }}%
                                            conversion rate
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ broker.formatted_sales_value }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{
                                            broker.completed_transactions
                                        }}
                                        transactions
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Workload Distribution -->
                    <div
                        class="bg-white shadow-lg rounded-xl border border-gray-200 p-6"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            📊 Workload Distribution
                        </h3>
                        <div class="space-y-4">
                            <div
                                v-for="category in workloadCategories"
                                :key="category.label"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center">
                                    <div
                                        class="w-4 h-4 rounded-full mr-3"
                                        :class="category.color"
                                    ></div>
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                        >{{ category.label }}</span
                                    >
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-900 mr-2"
                                        >{{ category.count }} brokers</span
                                    >
                                    <span class="text-xs text-gray-500"
                                        >({{ category.percentage }}%)</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    UsersIcon,
    UserGroupIcon,
    ChartBarIcon,
    CurrencyDollarIcon,
    ArrowPathIcon,
} from "@heroicons/vue/24/outline";

const brokerAnalytics = ref([]);
const loading = ref(false);

// Computed properties for summary statistics
const totalClientsAssigned = computed(() => {
    return brokerAnalytics.value.reduce(
        (sum, broker) => sum + broker.total_clients,
        0
    );
});

const averageConversionRate = computed(() => {
    if (brokerAnalytics.value.length === 0) return 0;
    const totalRate = brokerAnalytics.value.reduce(
        (sum, broker) => sum + broker.conversion_rate,
        0
    );
    return Math.round((totalRate / brokerAnalytics.value.length) * 10) / 10;
});

const totalSalesValue = computed(() => {
    const total = brokerAnalytics.value.reduce(
        (sum, broker) => sum + broker.total_sales_value,
        0
    );
    return "₱" + total.toLocaleString();
});

// Sorted brokers by performance score
const sortedBrokers = computed(() => {
    return [...brokerAnalytics.value].sort(
        (a, b) => getPerformanceScore(b) - getPerformanceScore(a)
    );
});

// Top performers (top 5)
const topPerformers = computed(() => {
    return sortedBrokers.value.slice(0, 5);
});

// Workload categories
const workloadCategories = computed(() => {
    const categories = [
        { label: "Light Load (0-5 clients)", color: "bg-green-400", count: 0 },
        {
            label: "Moderate Load (6-15 clients)",
            color: "bg-yellow-400",
            count: 0,
        },
        { label: "Heavy Load (16+ clients)", color: "bg-red-400", count: 0 },
    ];

    brokerAnalytics.value.forEach((broker) => {
        if (broker.total_clients <= 5) categories[0].count++;
        else if (broker.total_clients <= 15) categories[1].count++;
        else categories[2].count++;
    });

    const total = brokerAnalytics.value.length;
    categories.forEach((category) => {
        category.percentage =
            total > 0 ? Math.round((category.count / total) * 100) : 0;
    });

    return categories;
});

// Helper functions
const getPerformanceScore = (broker) => {
    // Calculate performance score based on multiple factors
    const conversionWeight = 0.4;
    const transactionWeight = 0.3;
    const clientWeight = 0.3;

    const conversionScore = Math.min(broker.conversion_rate, 100);
    const transactionScore = Math.min(broker.completed_transactions * 5, 100);
    const clientScore = Math.min(broker.total_clients * 2, 100);

    return Math.round(
        conversionScore * conversionWeight +
            transactionScore * transactionWeight +
            clientScore * clientWeight
    );
};

const getConversionRateColor = (rate) => {
    if (rate >= 80) return "bg-green-500";
    if (rate >= 60) return "bg-yellow-500";
    if (rate >= 40) return "bg-orange-500";
    return "bg-red-500";
};

const getPerformanceScoreColor = (score) => {
    if (score >= 80) return "bg-green-500";
    if (score >= 60) return "bg-blue-500";
    if (score >= 40) return "bg-yellow-500";
    return "bg-red-500";
};

// Data fetching
const fetchBrokerAnalytics = async () => {
    loading.value = true;
    try {
        const response = await fetch(route("admin.broker-analytics"));
        const data = await response.json();
        if (data.success) {
            brokerAnalytics.value = data.data;
        }
    } catch (error) {
        console.error("Error fetching broker analytics:", error);
    } finally {
        loading.value = false;
    }
};

const refreshData = () => {
    fetchBrokerAnalytics();
};

// Initialize
onMounted(() => {
    fetchBrokerAnalytics();
});
</script>
