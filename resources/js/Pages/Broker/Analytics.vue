<script setup>
import { Head } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import DashboardCard from "@/Components/DashboardCard.vue";
import ModernTable from "@/Components/ModernTable.vue";
import ModernButton from "@/Components/ModernButton.vue";
import {
    ChartBarIcon,
    ArrowTrendingUpIcon,
    CurrencyDollarIcon,
    EyeIcon,
    DocumentArrowDownIcon,
    CalendarIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    analytics: Object,
    monthlyStats: Array,
    propertyPerformance: Array,
    recentActivity: Array,
});

// Time period filter
const selectedPeriod = ref("6months");
const periods = [
    { value: "1month", label: "1 Month" },
    { value: "3months", label: "3 Months" },
    { value: "6months", label: "6 Months" },
    { value: "1year", label: "1 Year" },
];

// Calculate performance metrics
const performanceMetrics = computed(() => {
    const analytics = props.analytics || {};
    return [
        {
            title: "Conversion Rate",
            value: `${analytics.conversionRate || 0}%`,
            subtitle: "Inquiries to Sales",
            icon: ArrowTrendingUpIcon,
            trend: {
                direction: "up",
                value: "+2.3%",
                label: "vs last month",
            },
            color: "primary",
        },
        {
            title: "Avg. Commission",
            value: `₱${(analytics.averageCommission || 0).toLocaleString()}`,
            subtitle: "Per Transaction",
            icon: CurrencyDollarIcon,
            trend: {
                direction: "up",
                value: "+₱15K",
                label: "vs last month",
            },
            color: "accent",
        },
        {
            title: "Total Inquiries",
            value: analytics.totalInquiries || 0,
            subtitle: "This Period",
            icon: EyeIcon,
            trend: {
                direction: "up",
                value: "+12",
                label: "vs last month",
            },
            color: "coconut",
        },
        {
            title: "Active Properties",
            value: analytics.activeProperties || 0,
            subtitle: "Currently Listed",
            icon: BuildingOfficeIcon,
            color: "neutral",
        },
    ];
});

// Monthly chart data
const chartData = computed(() => {
    const months = props.monthlyStats || [];
    return months.map((month) => ({
        month: month.month,
        inquiries: month.inquiries || 0,
        transactions: month.transactions || 0,
        commission: month.commission || 0,
    }));
});

// Property performance table columns
const propertyColumns = [
    { key: "title", label: "Property", sortable: true },
    { key: "inquiries", label: "Inquiries", sortable: true },
    { key: "views", label: "Views", sortable: true },
    { key: "conversion_rate", label: "Conversion", sortable: true },
    { key: "commission", label: "Commission", sortable: true },
    { key: "status", label: "Status", sortable: false },
];

// Activity table columns
const activityColumns = [
    { key: "type", label: "Activity", sortable: false },
    { key: "property", label: "Property", sortable: true },
    { key: "client", label: "Client", sortable: true },
    { key: "amount", label: "Amount", sortable: true },
    { key: "date", label: "Date", sortable: true },
];

// Export functions
const exportToPDF = () => {
    // Implementation for PDF export
    console.log("Exporting to PDF...");
};

const exportToExcel = () => {
    // Implementation for Excel export
    console.log("Exporting to Excel...");
};
</script>

<template>
    <Head title="Analytics" />

    <ModernDashboardLayout>
        <div class="space-y-8">
            <!-- Header Section -->
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                        📊 Analytics Dashboard
                    </h1>
                    <p class="text-neutral-600">
                        Track your performance and optimize your property
                        listings
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Time Period Filter -->
                    <select
                        v-model="selectedPeriod"
                        class="px-4 py-2 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                        <option
                            v-for="period in periods"
                            :key="period.value"
                            :value="period.value"
                        >
                            {{ period.label }}
                        </option>
                    </select>

                    <!-- Export Buttons -->
                    <div class="flex gap-2">
                        <ModernButton
                            @click="exportToPDF"
                            variant="outline"
                            size="sm"
                            class="flex items-center gap-2"
                        >
                            <DocumentArrowDownIcon class="w-4 h-4" />
                            PDF
                        </ModernButton>
                        <ModernButton
                            @click="exportToExcel"
                            variant="outline"
                            size="sm"
                            class="flex items-center gap-2"
                        >
                            <DocumentArrowDownIcon class="w-4 h-4" />
                            Excel
                        </ModernButton>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <DashboardCard
                    v-for="metric in performanceMetrics"
                    :key="metric.title"
                    :title="metric.title"
                    :value="metric.value"
                    :subtitle="metric.subtitle"
                    :icon="metric.icon"
                    :trend="metric.trend"
                    :color="metric.color"
                    :interactive="true"
                />
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                <!-- Monthly Performance Chart -->
                <div class="bg-white rounded-3xl border border-neutral-100 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-neutral-900">
                            📈 Monthly Performance
                        </h3>
                        <div class="flex items-center gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-3 h-3 bg-primary-500 rounded-full"
                                ></div>
                                <span class="text-neutral-600">Inquiries</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-3 h-3 bg-accent-500 rounded-full"
                                ></div>
                                <span class="text-neutral-600"
                                    >Transactions</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- CSS-based Bar Chart -->
                    <div class="space-y-4">
                        <div
                            v-for="(data, index) in chartData"
                            :key="index"
                            class="space-y-2"
                        >
                            <div
                                class="flex justify-between text-sm text-neutral-600"
                            >
                                <span>{{ data.month }}</span>
                                <span
                                    >{{ data.inquiries }} inquiries,
                                    {{ data.transactions }} sales</span
                                >
                            </div>
                            <div class="flex gap-1 h-8">
                                <!-- Inquiries Bar -->
                                <div
                                    class="flex-1 bg-neutral-100 rounded-lg overflow-hidden"
                                >
                                    <div
                                        class="h-full bg-gradient-to-r from-primary-400 to-primary-500 rounded-lg transition-all duration-500"
                                        :style="{
                                            width: `${Math.min(
                                                (data.inquiries / 50) * 100,
                                                100
                                            )}%`,
                                        }"
                                    ></div>
                                </div>
                                <!-- Transactions Bar -->
                                <div
                                    class="flex-1 bg-neutral-100 rounded-lg overflow-hidden"
                                >
                                    <div
                                        class="h-full bg-gradient-to-r from-accent-400 to-accent-500 rounded-lg transition-all duration-500"
                                        :style="{
                                            width: `${Math.min(
                                                (data.transactions / 10) * 100,
                                                100
                                            )}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commission Trends -->
                <div class="bg-white rounded-3xl border border-neutral-100 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-neutral-900">
                            💰 Commission Trends
                        </h3>
                    </div>

                    <!-- Commission Chart -->
                    <div class="space-y-4">
                        <div
                            v-for="(data, index) in chartData"
                            :key="index"
                            class="space-y-2"
                        >
                            <div
                                class="flex justify-between text-sm text-neutral-600"
                            >
                                <span>{{ data.month }}</span>
                                <span class="font-semibold text-coconut-600"
                                    >₱{{
                                        data.commission.toLocaleString()
                                    }}</span
                                >
                            </div>
                            <div
                                class="h-3 bg-neutral-100 rounded-full overflow-hidden"
                            >
                                <div
                                    class="h-full bg-gradient-to-r from-coconut-400 to-coconut-500 rounded-full transition-all duration-500"
                                    :style="{
                                        width: `${Math.min(
                                            (data.commission / 100000) * 100,
                                            100
                                        )}%`,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Performance Table -->
            <div class="bg-white rounded-3xl border border-neutral-100 p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-neutral-900">
                        🏠 Property Performance
                    </h3>
                    <ModernButton variant="outline" size="sm">
                        View All Properties
                    </ModernButton>
                </div>

                <ModernTable
                    :columns="propertyColumns"
                    :data="propertyPerformance || []"
                    :loading="false"
                    class="mt-4"
                >
                    <template #conversion_rate="{ item }">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-12 h-2 bg-neutral-100 rounded-full overflow-hidden"
                            >
                                <div
                                    class="h-full bg-gradient-to-r from-primary-400 to-primary-500 rounded-full"
                                    :style="{
                                        width: `${item.conversion_rate || 0}%`,
                                    }"
                                ></div>
                            </div>
                            <span class="text-sm font-medium"
                                >{{ item.conversion_rate || 0 }}%</span
                            >
                        </div>
                    </template>

                    <template #commission="{ item }">
                        <span class="font-semibold text-coconut-600">
                            ₱{{ (item.commission || 0).toLocaleString() }}
                        </span>
                    </template>

                    <template #status="{ item }">
                        <span
                            :class="[
                                'px-3 py-1 rounded-full text-xs font-medium',
                                item.status === 'active'
                                    ? 'bg-green-100 text-green-700'
                                    : item.status === 'sold'
                                    ? 'bg-blue-100 text-blue-700'
                                    : 'bg-neutral-100 text-neutral-700',
                            ]"
                        >
                            {{ item.status || "Active" }}
                        </span>
                    </template>
                </ModernTable>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-3xl border border-neutral-100 p-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-neutral-900">
                        🔄 Recent Activity
                    </h3>
                    <ModernButton variant="outline" size="sm">
                        View All Activity
                    </ModernButton>
                </div>

                <ModernTable
                    :columns="activityColumns"
                    :data="recentActivity || []"
                    :loading="false"
                    class="mt-4"
                >
                    <template #type="{ item }">
                        <div class="flex items-center gap-3">
                            <div
                                :class="[
                                    'w-8 h-8 rounded-lg flex items-center justify-center',
                                    item.type === 'inquiry'
                                        ? 'bg-blue-100 text-blue-600'
                                        : item.type === 'transaction'
                                        ? 'bg-green-100 text-green-600'
                                        : item.type === 'viewing'
                                        ? 'bg-purple-100 text-purple-600'
                                        : 'bg-neutral-100 text-neutral-600',
                                ]"
                            >
                                <EyeIcon
                                    v-if="item.type === 'inquiry'"
                                    class="w-4 h-4"
                                />
                                <CurrencyDollarIcon
                                    v-else-if="item.type === 'transaction'"
                                    class="w-4 h-4"
                                />
                                <BuildingOfficeIcon v-else class="w-4 h-4" />
                            </div>
                            <span class="capitalize font-medium">{{
                                item.type || "Activity"
                            }}</span>
                        </div>
                    </template>

                    <template #amount="{ item }">
                        <span
                            v-if="item.amount"
                            class="font-semibold text-coconut-600"
                        >
                            ₱{{ item.amount.toLocaleString() }}
                        </span>
                        <span v-else class="text-neutral-400">-</span>
                    </template>
                </ModernTable>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<style scoped>
/* Custom animations for charts */
@keyframes slideIn {
    from {
        width: 0;
    }
    to {
        width: var(--target-width);
    }
}

.chart-bar {
    animation: slideIn 1s ease-out;
}
</style>
