<template>
    <ModernDashboardLayout>
        <Head title="Broker Dashboard - GeoCasa Bohol" />

        <!-- Broker Header -->
        <div class="card card-elevated p-8 mb-8">
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <div class="flex items-center gap-4 mb-3">
                        <div
                            class="w-16 h-16 bg-accent-50 rounded-xl flex items-center justify-center border border-accent-200"
                        >
                            <BuildingOfficeIcon
                                class="w-8 h-8 text-accent-600"
                            />
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-neutral-900">
                                Broker Dashboard
                            </h1>
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-3 h-3 bg-accent-500 rounded-full animate-pulse-subtle"
                                ></span>
                                <span
                                    class="text-neutral-600 text-base font-medium"
                                    >Broker Active & Available</span
                                >
                            </div>
                        </div>
                    </div>
                    <p class="text-neutral-600 text-lg">
                        Manage your property portfolio and client relationships
                        with professional tools
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('transactions.create')"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2"
                        >
                            <PlusIcon class="w-4 h-4" />
                            New Transaction
                        </Link>
                        <Link
                            :href="route('broker.properties.create')"
                            class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2"
                        >
                            <PlusIcon class="w-4 h-4" />
                            Add Property
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid using real data -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <DashboardCard
                title="Total Properties"
                :value="stats?.totalProperties || 0"
                subtitle="Your listings"
                :icon="BuildingOfficeIcon"
                color="blue"
                :trend="
                    stats?.monthlyStats
                        ? getTrend(
                              stats.monthlyStats.current.properties,
                              stats.monthlyStats.previous.properties,
                              'this month'
                          )
                        : null
                "
            />

            <DashboardCard
                title="Active Inquiries"
                :value="stats?.activeInquiries || 0"
                subtitle="Pending responses"
                :icon="DocumentTextIcon"
                color="orange"
                :trend="
                    stats?.monthlyStats
                        ? getTrend(
                              stats.monthlyStats.current.inquiries,
                              stats.monthlyStats.previous.inquiries,
                              'this month'
                          )
                        : null
                "
            />

            <DashboardCard
                title="Total Clients"
                :value="stats?.totalClients || 0"
                subtitle="Your clients"
                :icon="UserGroupIcon"
                color="green"
            />

            <DashboardCard
                title="Commission Earned"
                :value="formatCurrency(stats?.totalCommission || 0)"
                subtitle="This month"
                :icon="CurrencyDollarIcon"
                color="purple"
                :trend="
                    stats?.monthlyStats
                        ? getTrend(
                              stats.monthlyStats.current.commission,
                              stats.monthlyStats.previous.commission,
                              'this month'
                          )
                        : null
                "
            />
        </div>

        <!-- Quick Actions -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                Quick Actions
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="card card-hover p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div
                            class="w-12 h-12 bg-primary-50 rounded-lg flex items-center justify-center"
                        >
                            <BuildingOfficeIcon
                                class="w-6 h-6 text-primary-600"
                            />
                        </div>
                        <h3 class="font-semibold text-neutral-900">
                            Manage Properties
                        </h3>
                    </div>
                    <p class="text-neutral-600 text-sm mb-4">
                        Add, edit, or view your property listings
                    </p>
                    <button
                        class="btn-primary-sm w-full"
                        @click="
                            $inertia.visit(route('broker.properties.index'))
                        "
                    >
                        View Properties
                    </button>
                </div>

                <div class="card card-hover p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div
                            class="w-12 h-12 bg-accent-50 rounded-lg flex items-center justify-center"
                        >
                            <UserGroupIcon class="w-6 h-6 text-accent-600" />
                        </div>
                        <h3 class="font-semibold text-neutral-900">
                            Client Management
                        </h3>
                    </div>
                    <p class="text-neutral-600 text-sm mb-4">
                        Track and manage your client relationships
                    </p>
                    <button
                        class="btn-primary-sm w-full"
                        @click="$inertia.visit(route('clients.index'))"
                    >
                        View Clients
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Inquiries -->
            <StandardCard variant="base" size="md">
                <template #header>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Recent Inquiries
                        </h3>
                        <Link
                            :href="route('inquiries.index')"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                        >
                            View All
                        </Link>
                    </div>
                </template>
                <div v-if="recentInquiries?.length > 0" class="space-y-4">
                    <div
                        v-for="inquiry in recentInquiries.slice(0, 5)"
                        :key="inquiry.id"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                    >
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">
                                {{
                                    inquiry.property?.title ||
                                    "Property Inquiry"
                                }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ inquiry.client?.name || "Anonymous" }} •
                                {{ formatDate(inquiry.created_at) }}
                            </p>
                        </div>
                        <StandardBadge :status="inquiry.status">
                            {{ formatStatus(inquiry.status) }}
                        </StandardBadge>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <DocumentTextIcon
                        class="w-12 h-12 text-gray-400 mx-auto mb-2"
                    />
                    <p class="text-gray-500">No recent inquiries</p>
                </div>
            </StandardCard>

            <!-- Recent Transactions -->
            <StandardCard variant="base" size="md">
                <template #header>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Recent Transactions
                        </h3>
                        <Link
                            :href="route('transactions.index')"
                            class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                        >
                            View All
                        </Link>
                    </div>
                </template>
                <div v-if="recentTransactions?.length > 0" class="space-y-4">
                    <div
                        v-for="transaction in recentTransactions.slice(0, 5)"
                        :key="transaction.id"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                    >
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">
                                {{
                                    transaction.property?.title ||
                                    "Property Sale"
                                }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ formatDate(transaction.created_at) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-green-600">
                                {{
                                    formatCurrency(
                                        transaction.final_price ||
                                            transaction.offered_price
                                    )
                                }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ formatStatus(transaction.status) }}
                            </p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <CurrencyDollarIcon
                        class="w-12 h-12 text-gray-400 mx-auto mb-2"
                    />
                    <p class="text-gray-500">No recent transactions</p>
                </div>
            </StandardCard>
        </div>

        <!-- Reminders Widget -->
        <div v-if="reminders" class="mt-8">
            <ReminderWidget :reminders="reminders" />
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref, onMounted, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import DashboardCard from "@/Components/DashboardCard.vue";
import StandardButton from "@/Components/StandardButton.vue";
import StandardCard from "@/Components/StandardCard.vue";
import StandardBadge from "@/Components/StandardBadge.vue";
import ReminderWidget from "@/Components/ReminderWidget.vue";
import { dataLoadingService } from "@/Services/DataLoadingService";
import { performanceService } from "@/Services/PerformanceService";
import { loadingStateService } from "@/Services/LoadingStateService";
import {
    BuildingOfficeIcon,
    UserGroupIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    PlusIcon,
} from "@heroicons/vue/24/outline";

// Accept real props from backend
const props = defineProps({
    stats: Object,
    recentInquiries: Array,
    recentTransactions: Array,
    reminders: Object,
});

const page = usePage();

// Performance monitoring
const performanceMeasurement = ref(null);
const loadingStates = ref({});

// Initialize performance monitoring
onMounted(() => {
    performanceMeasurement.value = performanceService.startMeasurement(
        "broker_dashboard",
        "render"
    );

    // Monitor component performance
    performanceService.monitorComponent("broker_dashboard", () => {
        // Component render logic
    });
});

// Watch for performance issues
watch(
    () => performanceService.getSlowOperations(),
    (slowOps) => {
        if (slowOps.length > 0) {
            console.warn("Slow operations detected:", slowOps);
        }
    },
    { deep: true }
);

// Calculate trend data from monthly stats
const getTrend = (current, previous, label) => {
    if (!previous || previous === 0) return null;
    const change = current - previous;
    const percentage = ((change / previous) * 100).toFixed(1);
    return {
        direction: change >= 0 ? "up" : "down",
        value: `${change >= 0 ? "+" : ""}${change}`,
        label: label,
        percentage: `${percentage}%`,
    };
};

// Utility functions
const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const formatStatus = (status) => {
    return status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};
</script>
