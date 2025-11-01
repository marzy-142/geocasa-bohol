<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200">
                <div class="px-6 py-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">
                                Analytics Hub
                            </h1>
                            <p class="mt-1 text-sm text-gray-500">
                                Business intelligence and performance insights
                            </p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button
                                @click="exportReport"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                                Export
                            </button>
                            <button
                                @click="refreshData"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <ArrowPathIcon class="w-4 h-4 mr-2" />
                                Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Minimal KPI Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    class="bg-white border border-gray-200 rounded-lg shadow-sm"
                >
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"
                                >
                                    <UsersIcon class="w-5 h-5 text-blue-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">
                                    Total Transactions
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{
                                        analytics?.overall_metrics
                                            ?.total_transactions || 0
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white border border-gray-200 rounded-lg shadow-sm"
                >
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center"
                                >
                                    <ChartBarIcon
                                        class="w-5 h-5 text-green-600"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">
                                    Completion Rate
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{
                                        analytics?.transaction_analytics
                                            ?.completion_rate || 0
                                    }}%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white border border-gray-200 rounded-lg shadow-sm"
                >
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center"
                                >
                                    <ChartPieIcon
                                        class="w-5 h-5 text-purple-600"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">
                                    Sales Value
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{
                                        formatCurrency(
                                            analytics?.transaction_analytics
                                                ?.revenue_generated || 0
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white border border-gray-200 rounded-lg shadow-sm"
                >
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center"
                                >
                                    <UsersIcon
                                        class="w-5 h-5 text-orange-600"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">
                                    Active Brokers
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{
                                        analytics?.broker_analytics
                                            ?.active_brokers || 0
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overview Trends Chart (if available) -->
            <div
                v-if="hasSystemTrendSeries"
                class="bg-white border border-gray-200 rounded-lg shadow-sm"
            >
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Performance Trends
                    </h3>
                </div>
                <div class="p-6">
                    <div class="h-64">
                        <AnalyticsChart
                            type="line"
                            :data="systemTrendsChartData"
                            :options="systemTrendsChartOptions"
                        />
                    </div>
                </div>
            </div>

            <!-- Top Performers (compact) -->
            <div class="grid grid-cols-1 gap-6">
                <div
                    class="bg-white border border-gray-200 rounded-lg shadow-sm p-6"
                >
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Top Performing Brokers
                        </h3>
                        <Link
                            :href="route('admin.reports.brokers')"
                            class="text-sm text-blue-600 hover:text-blue-500"
                            >View All</Link
                        >
                    </div>
                    <div class="space-y-3">
                        <div
                            v-for="(broker, index) in (topBrokers || []).slice(
                                0,
                                5
                            )"
                            :key="broker.id"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div
                                        class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center"
                                    >
                                        <span
                                            class="text-sm font-medium text-blue-600"
                                            >{{ index + 1 }}</span
                                        >
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ broker.name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ broker.total_sales || 0 }} finalized
                                        •
                                        {{
                                            formatCurrency(
                                                broker.total_sales_value || 0
                                            )
                                        }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ broker.active_listings || 0 }} active
                                    listings
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pipeline mini-cards -->
            <div
                v-if="analytics?.pipeline?.by_status"
                class="bg-white border border-gray-200 rounded-lg shadow-sm"
            >
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Transaction Pipeline
                    </h3>
                </div>
                <div class="p-6">
                    <div
                        class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4"
                    >
                        <div
                            v-for="(count, status) in analytics.pipeline
                                .by_status"
                            :key="status"
                            class="bg-gray-50 rounded-lg p-4"
                        >
                            <div
                                class="text-xs uppercase tracking-wide text-gray-500 mb-1"
                            >
                                {{ formatStatus(status) }}
                            </div>
                            <div class="text-xl font-semibold text-gray-900">
                                {{ count }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stage Dwell Time (Bottleneck Analysis) -->
            <div
                v-if="hasStageDwellTime"
                class="bg-white border border-gray-200 rounded-lg shadow-sm"
            >
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Stage Dwell Time
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Median days transactions spend in each stage
                    </p>
                </div>
                <div class="p-6">
                    <div class="h-64">
                        <AnalyticsChart
                            type="bar"
                            :data="stageDwellChartData"
                            :options="stageDwellChartOptions"
                        />
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import AnalyticsChart from "@/Components/AnalyticsChart.vue";
import {
    UsersIcon,
    ChartBarIcon,
    ChartPieIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    topBrokers: Array,
    analytics: Object,
});

// Methods
const exportReport = () => {
    // Implementation for exporting reports
    alert("Export functionality would be implemented here");
};

const refreshData = () => {
    router.reload();
};

// Time-series from backend analytics
const hasSystemTrendSeries = computed(() => {
    const trends = props.analytics?.performance_trends;
    return Array.isArray(trends?.series) && trends.series.length > 0;
});

const systemTrendsChartData = computed(() => {
    const trends = props.analytics?.performance_trends || {};
    const labels = trends.labels || [];
    const series = trends.series || [];
    const palette = [
        "rgb(59, 130, 246)",
        "rgb(16, 185, 129)",
        "rgb(245, 158, 11)",
        "rgb(99, 102, 241)",
    ];
    const datasets = series.map((s, idx) => ({
        label: s.label,
        data: s.data || [],
        borderColor: palette[idx % palette.length],
        backgroundColor: palette[idx % palette.length]
            .replace("rgb", "rgba")
            .replace(")", ", 0.15)"),
        tension: 0.35,
        fill: s.type === "line",
        type: s.type || "line",
        yAxisID: s.label?.includes("Sales Value") ? "y1" : "y",
    }));
    return { labels, datasets };
});

const systemTrendsChartOptions = {
    responsive: true,
    interaction: { intersect: false, mode: "index" },
    stacked: false,
    scales: {
        y: { beginAtZero: true, title: { display: true, text: "Count" } },
        y1: {
            beginAtZero: true,
            position: "right",
            grid: { drawOnChartArea: false },
            title: { display: true, text: "Sales (PHP)" },
        },
    },
    plugins: { legend: { position: "top" } },
};

const formatCurrency = (value) => {
    try {
        return new Intl.NumberFormat("en-PH", {
            style: "currency",
            currency: "PHP",
            maximumFractionDigits: 0,
        }).format(value || 0);
    } catch (e) {
        return `₱${(value || 0).toLocaleString()}`;
    }
};

const formatStatus = (status) => {
    return (status || "")
        .replace(/_/g, " ")
        .replace(/\b\w/g, (l) => l.toUpperCase());
};

// Stage dwell time analysis
const hasStageDwellTime = computed(() => {
    const dwellTime = props.analytics?.pipeline?.stage_dwell_time;
    return dwellTime && Object.keys(dwellTime).length > 0;
});

const stageDwellChartData = computed(() => {
    const dwellTime = props.analytics?.pipeline?.stage_dwell_time || {};
    const stages = Object.keys(dwellTime);
    const medianDays = stages.map((stage) => dwellTime[stage].median_days || 0);

    return {
        labels: stages.map(formatStatus),
        datasets: [
            {
                label: "Median Days in Stage",
                data: medianDays,
                backgroundColor: "rgba(59, 130, 246, 0.8)",
                borderColor: "rgb(59, 130, 246)",
                borderWidth: 1,
            },
        ],
    };
});

const stageDwellChartOptions = {
    responsive: true,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                afterLabel: (context) => {
                    const stage = Object.keys(
                        props.analytics?.pipeline?.stage_dwell_time || {}
                    )[context.dataIndex];
                    const data =
                        props.analytics?.pipeline?.stage_dwell_time?.[stage];
                    if (data) {
                        return [
                            `Average: ${data.avg_days} days`,
                            `Transactions: ${data.count}`,
                        ];
                    }
                    return "";
                },
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            title: { display: true, text: "Days" },
        },
    },
};
</script>
