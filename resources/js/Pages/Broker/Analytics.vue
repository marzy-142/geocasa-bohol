<template>
    <ModernDashboardLayout>
        <div class="p-6">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Analytics Dashboard
                </h1>
                <p class="mt-2 text-gray-600">
                    Track your performance and property analytics
                </p>
            </div>

            <!-- Total Stats Cards -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
            >
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <ChartBarIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Total Inquiries
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ totalStats.totalInquiries }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <ArrowTrendingUpIcon
                                class="h-6 w-6 text-green-600"
                            />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Conversion Rate
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ totalStats.conversionRate }}%
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Commission / monetary summaries removed for privacy -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <ChartBarIcon class="h-6 w-6 text-yellow-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Avg Transactions / Month
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ Math.round(getTotalSales() / 12) || 0 }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <TrophyIcon class="h-6 w-6 text-purple-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Top Property
                            </p>
                            <p class="text-sm font-bold text-gray-900 truncate">
                                {{
                                    totalStats.topPerformingProperty?.title ||
                                    "None"
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Performance Overview -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">
                                Monthly Performance Trends
                            </h2>
                            <p class="text-sm text-gray-600">
                                Track your business growth over the last 12
                                months
                            </p>
                        </div>
                        <div class="flex items-center gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-3 h-3 bg-blue-500 rounded-full"
                                ></div>
                                <span class="text-gray-600">Inquiries</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-3 h-3 bg-green-500 rounded-full"
                                ></div>
                                <span class="text-gray-600"
                                    >Completed Deals</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Cards -->
                <div class="p-6 border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-sm font-medium text-blue-700"
                                    >
                                        Total Inquiries
                                    </p>
                                    <p class="text-2xl font-bold text-blue-900">
                                        {{ getTotalInquiries() }}
                                    </p>
                                    <p class="text-xs text-blue-600">
                                        Last 12 months
                                    </p>
                                </div>
                                <ChartBarIcon class="w-8 h-8 text-blue-600" />
                            </div>
                        </div>

                        <div
                            class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-sm font-medium text-green-700"
                                    >
                                        Completed Deals
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-green-900"
                                    >
                                        {{ getTotalSales() }}
                                    </p>
                                    <p class="text-xs text-green-600">
                                        Total finalized transactions
                                    </p>
                                </div>
                                <TrophyIcon class="w-8 h-8 text-green-600" />
                            </div>
                        </div>

                        <div
                            class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-sm font-medium text-purple-700"
                                    >
                                        Properties Added
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-purple-900"
                                    >
                                        {{ getTotalPropertiesAdded() }}
                                    </p>
                                    <p class="text-xs text-purple-600">
                                        Last 12 months
                                    </p>
                                </div>
                                <ChartBarIcon class="w-8 h-8 text-purple-600" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Chart with Enhanced Design (Chart.js) -->
                <div class="p-6">
                    <div class="h-80 relative">
                        <AnalyticsChart
                            v-if="chartData.labels.length"
                            type="line"
                            :data="chartData"
                            :options="chartOptions"
                            :height="320"
                        />

                        <!-- Chart Overlay Info -->
                        <div
                            class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-lg p-3 shadow-sm"
                        >
                            <div class="text-xs text-gray-600 mb-1">
                                Best Month
                            </div>
                            <div class="font-semibold text-gray-900">
                                {{ getBestMonth() }}
                            </div>
                        </div>

                        <div
                            class="absolute bottom-4 left-4 bg-white/90 backdrop-blur-sm rounded-lg p-3 shadow-sm"
                        >
                            <div class="text-xs text-gray-600 mb-1">
                                Growth Trend
                            </div>
                            <div class="flex items-center gap-1">
                                <ArrowTrendingUpIcon
                                    :class="getTrendIcon()"
                                    class="w-4 h-4"
                                />
                                <span
                                    :class="getTrendColor()"
                                    class="font-semibold"
                                    >{{ getGrowthTrend() }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Data Table -->
                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th
                                        class="text-left py-2 font-medium text-gray-600"
                                    >
                                        Month
                                    </th>
                                    <th
                                        class="text-right py-2 font-medium text-gray-600"
                                    >
                                        Inquiries
                                    </th>
                                    <th
                                        class="text-right py-2 font-medium text-gray-600"
                                    >
                                        Completed Deals
                                    </th>
                                    <th
                                        class="text-right py-2 font-medium text-gray-600"
                                    >
                                        Conversion
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(month, index) in monthlyData"
                                    :key="index"
                                    class="border-b border-gray-100 hover:bg-gray-50"
                                >
                                    <td class="py-2 font-medium text-gray-900">
                                        {{ month.month }}
                                    </td>
                                    <td class="py-2 text-right text-blue-600">
                                        {{ month.inquiries }}
                                    </td>
                                    <td class="py-2 text-right text-green-600">
                                        {{ month.transactions }}
                                    </td>
                                    <td class="py-2 text-right">
                                        <span
                                            :class="
                                                getConversionRateColor(
                                                    month.inquiries,
                                                    month.transactions
                                                )
                                            "
                                            class="font-medium"
                                        >
                                            {{
                                                getConversionRate(
                                                    month.inquiries,
                                                    month.transactions
                                                )
                                            }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Property Performance Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Property Performance
                    </h2>
                    <p class="text-sm text-gray-600">
                        Top performing properties by inquiries and conversions
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Property
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Price
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Inquiries
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Transactions
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Conversion Rate
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="property in propertyStats"
                                :key="property.id"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ property.title }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        ₱{{ formatNumber(property.price) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
                                    >
                                        {{ property.inquiries_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"
                                    >
                                        {{ property.transactions_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800"
                                    >
                                        {{ property.conversion_rate }}%
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import AnalyticsChart from "@/Components/AnalyticsChart.vue";
import {
    ChartBarIcon,
    ArrowTrendingUpIcon,
    CurrencyDollarIcon,
    TrophyIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    monthlyData: Array,
    propertyStats: Array,
    totalStats: Object,
});

// Chart.js data and options (computed from monthlyData)
const chartData = computed(() => {
    const labels = (props.monthlyData || []).map((m) => m.month);
    const inquiries = (props.monthlyData || []).map((m) => m.inquiries || 0);
    const transactions = (props.monthlyData || []).map(
        (m) => m.transactions || 0
    );

    // Use scriptable backgroundColor to build gradient with Chart.js ctx
    const blueFill = (ctx) => {
        const { chart } = ctx;
        const { ctx: c, chartArea } = chart || {};
        if (!chartArea) return "rgba(59,130,246,0.15)"; // fallback before first layout
        const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        g.addColorStop(0, "rgba(59,130,246,0.35)");
        g.addColorStop(1, "rgba(59,130,246,0.05)");
        return g;
    };
    const greenFill = (ctx) => {
        const { chart } = ctx;
        const { ctx: c, chartArea } = chart || {};
        if (!chartArea) return "rgba(16,185,129,0.15)";
        const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        g.addColorStop(0, "rgba(16,185,129,0.35)");
        g.addColorStop(1, "rgba(16,185,129,0.05)");
        return g;
    };

    return {
        labels,
        datasets: [
            {
                label: "Inquiries",
                data: inquiries,
                borderColor: "#3b82f6",
                backgroundColor: blueFill,
                pointBackgroundColor: "#3b82f6",
                pointBorderWidth: 0,
                pointRadius: 3,
                pointHoverRadius: 5,
                tension: 0.35,
                fill: true,
            },
            {
                label: "Completed Deals",
                data: transactions,
                borderColor: "#10b981",
                backgroundColor: greenFill,
                pointBackgroundColor: "#10b981",
                pointBorderWidth: 0,
                pointRadius: 3,
                pointHoverRadius: 5,
                tension: 0.35,
                fill: true,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: "index", intersect: false },
    plugins: {
        legend: {
            display: true,
            position: "top",
            labels: { color: "#374151", boxWidth: 12, usePointStyle: true },
        },
        tooltip: {
            backgroundColor: "rgba(17,24,39,0.9)",
            borderWidth: 0,
            titleColor: "#fff",
            bodyColor: "#e5e7eb",
            callbacks: {
                label: (ctx) => `${ctx.dataset.label}: ${ctx.formattedValue}`,
            },
        },
    },
    scales: {
        x: {
            grid: { color: "rgba(107,114,128,0.08)", drawBorder: false },
            ticks: { color: "#6b7280" },
        },
        y: {
            beginAtZero: true,
            grid: { color: "rgba(107,114,128,0.08)", drawBorder: false },
            ticks: { color: "#6b7280", precision: 0 },
        },
    },
};

const formatNumber = (number) => {
    if (!number) return "0";
    return new Intl.NumberFormat("en-PH").format(Math.round(number));
};

// Enhanced Monthly Performance Calculations
const getTotalInquiries = () => {
    if (!props.monthlyData) return 0;
    return props.monthlyData.reduce((sum, month) => sum + month.inquiries, 0);
};

const getTotalSales = () => {
    if (!props.monthlyData) return 0;
    return props.monthlyData.reduce(
        (sum, month) => sum + month.transactions,
        0
    );
};

// Sum of properties added over the period
const getTotalPropertiesAdded = () => {
    if (!props.monthlyData) return 0;
    return props.monthlyData.reduce(
        (sum, month) => sum + (month.properties_added || 0),
        0
    );
};

const getBestMonth = () => {
    if (!props.monthlyData || props.monthlyData.length === 0) return "N/A";

    const bestMonth = props.monthlyData.reduce((best, current) => {
        const bestScore = best.inquiries + best.transactions;
        const currentScore = current.inquiries + current.transactions;
        return currentScore > bestScore ? current : best;
    });

    return bestMonth.month;
};

const getGrowthTrend = () => {
    if (!props.monthlyData || props.monthlyData.length < 2) return "N/A";

    const recent = props.monthlyData.slice(-3);
    const older = props.monthlyData.slice(-6, -3);

    const recentAvg =
        recent.reduce((sum, month) => sum + month.transactions, 0) /
        recent.length;
    const olderAvg =
        older.length > 0
            ? older.reduce((sum, month) => sum + month.transactions, 0) /
              older.length
            : 0;

    if (olderAvg === 0) return "New";

    const growth = ((recentAvg - olderAvg) / olderAvg) * 100;
    return `${growth > 0 ? "+" : ""}${Math.round(growth)}%`;
};

const getTrendIcon = () => {
    const trend = getGrowthTrend();
    if (trend.includes("+")) return "text-green-600";
    if (trend.includes("-")) return "text-red-600";
    return "text-gray-600";
};

const getTrendColor = () => {
    const trend = getGrowthTrend();
    if (trend.includes("+")) return "text-green-600";
    if (trend.includes("-")) return "text-red-600";
    return "text-gray-600";
};

const getConversionRate = (inquiries, transactions) => {
    if (!inquiries || inquiries === 0) return 0;
    return Math.round((transactions / inquiries) * 100);
};

const getConversionRateColor = (inquiries, transactions) => {
    const rate = getConversionRate(inquiries, transactions);
    if (rate >= 20) return "text-green-600";
    if (rate >= 10) return "text-yellow-600";
    return "text-red-600";
};

onMounted(() => {
    // Nothing needed; AnalyticsChart renders from computed props
});
</script>
