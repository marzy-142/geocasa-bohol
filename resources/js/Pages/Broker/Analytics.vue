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

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <CurrencyDollarIcon
                                class="h-6 w-6 text-yellow-600"
                            />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Avg Commission
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                ₱{{
                                    formatNumber(totalStats.averageCommission)
                                }}
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
                                <span class="text-gray-600">Sales</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-3 h-3 bg-purple-500 rounded-full"
                                ></div>
                                <span class="text-gray-600">Commission</span>
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
                                        Total Sales
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-green-900"
                                    >
                                        {{ getTotalSales() }}
                                    </p>
                                    <p class="text-xs text-green-600">
                                        Completed transactions
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
                                        Total Commission
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-purple-900"
                                    >
                                        ₱{{
                                            formatNumber(getTotalCommission())
                                        }}
                                    </p>
                                    <p class="text-xs text-purple-600">
                                        Earned from sales
                                    </p>
                                </div>
                                <CurrencyDollarIcon
                                    class="w-8 h-8 text-purple-600"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Chart with Enhanced Design -->
                <div class="p-6">
                    <div class="h-80 relative">
                        <canvas
                            ref="monthlyChart"
                            class="w-full h-full"
                        ></canvas>

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
                                        Sales
                                    </th>
                                    <th
                                        class="text-right py-2 font-medium text-gray-600"
                                    >
                                        Commission
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
                                    <td class="py-2 text-right text-purple-600">
                                        ₱{{ formatNumber(month.commission) }}
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
import { ref, onMounted } from "vue";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
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

const monthlyChart = ref(null);
let chartInstance = null;

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

const getTotalCommission = () => {
    if (!props.monthlyData) return 0;
    return props.monthlyData.reduce((sum, month) => sum + month.commission, 0);
};

const getBestMonth = () => {
    if (!props.monthlyData || props.monthlyData.length === 0) return "N/A";

    const bestMonth = props.monthlyData.reduce((best, current) => {
        const bestScore =
            best.inquiries + best.transactions + best.commission / 10000;
        const currentScore =
            current.inquiries +
            current.transactions +
            current.commission / 10000;
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
    if (props.monthlyData && props.monthlyData.length > 0) {
        createMonthlyChart();
    }
});

const createMonthlyChart = () => {
    if (!monthlyChart.value) return;

    // Simple chart implementation without external libraries
    const ctx = monthlyChart.value.getContext("2d");
    const data = props.monthlyData;

    // Clear canvas
    ctx.clearRect(0, 0, monthlyChart.value.width, monthlyChart.value.height);

    // Set canvas size
    monthlyChart.value.width = monthlyChart.value.offsetWidth;
    monthlyChart.value.height = 200;

    const width = monthlyChart.value.width;
    const height = monthlyChart.value.height;
    const padding = 40;

    // Find max values for scaling
    const maxInquiries = Math.max(...data.map((d) => d.inquiries));
    const maxTransactions = Math.max(...data.map((d) => d.transactions));
    const maxCommission = Math.max(...data.map((d) => d.commission));
    const maxValue = Math.max(maxInquiries, maxTransactions, maxCommission);

    // Draw axes
    ctx.strokeStyle = "#e5e7eb";
    ctx.lineWidth = 1;

    // X-axis
    ctx.beginPath();
    ctx.moveTo(padding, height - padding);
    ctx.lineTo(width - padding, height - padding);
    ctx.stroke();

    // Y-axis
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, height - padding);
    ctx.stroke();

    // Draw data points and lines with enhanced visualization
    const stepX = (width - 2 * padding) / (data.length - 1);

    // Inquiries line (blue) with area fill
    ctx.strokeStyle = "#3b82f6";
    ctx.fillStyle = "rgba(59, 130, 246, 0.1)";
    ctx.lineWidth = 3;
    ctx.beginPath();
    ctx.moveTo(padding, height - padding);

    data.forEach((point, index) => {
        const x = padding + index * stepX;
        const y =
            height -
            padding -
            (point.inquiries / maxValue) * (height - 2 * padding);
        ctx.lineTo(x, y);
    });

    ctx.lineTo(width - padding, height - padding);
    ctx.closePath();
    ctx.fill();
    ctx.stroke();

    // Transactions line (green) with area fill
    ctx.strokeStyle = "#10b981";
    ctx.fillStyle = "rgba(16, 185, 129, 0.1)";
    ctx.lineWidth = 3;
    ctx.beginPath();
    ctx.moveTo(padding, height - padding);

    data.forEach((point, index) => {
        const x = padding + index * stepX;
        const y =
            height -
            padding -
            (point.transactions / maxValue) * (height - 2 * padding);
        ctx.lineTo(x, y);
    });

    ctx.lineTo(width - padding, height - padding);
    ctx.closePath();
    ctx.fill();
    ctx.stroke();

    // Draw data points
    data.forEach((point, index) => {
        const x = padding + index * stepX;

        // Inquiries point
        const inquiryY =
            height -
            padding -
            (point.inquiries / maxValue) * (height - 2 * padding);
        ctx.fillStyle = "#3b82f6";
        ctx.beginPath();
        ctx.arc(x, inquiryY, 4, 0, 2 * Math.PI);
        ctx.fill();

        // Transactions point
        const transactionY =
            height -
            padding -
            (point.transactions / maxValue) * (height - 2 * padding);
        ctx.fillStyle = "#10b981";
        ctx.beginPath();
        ctx.arc(x, transactionY, 4, 0, 2 * Math.PI);
        ctx.fill();
    });

    // Draw month labels with better styling
    ctx.fillStyle = "#374151";
    ctx.font = "bold 11px Inter, sans-serif";
    ctx.textAlign = "center";
    ctx.textBaseline = "top";

    data.forEach((point, index) => {
        const x = padding + index * stepX;
        ctx.fillText(point.month.substring(0, 3), x, height - padding + 8);
    });

    // Draw Y-axis labels
    ctx.fillStyle = "#6b7280";
    ctx.font = "10px Inter, sans-serif";
    ctx.textAlign = "right";
    ctx.textBaseline = "middle";

    for (let i = 0; i <= 5; i++) {
        const value = (maxValue / 5) * i;
        const y = height - padding - (i / 5) * (height - 2 * padding);
        ctx.fillText(Math.round(value).toString(), padding - 8, y);
    }
};
</script>
