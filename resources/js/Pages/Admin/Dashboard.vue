<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import DashboardCard from "@/Components/DashboardCard.vue";
import ModernButton from "@/Components/ModernButton.vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    UserGroupIcon,
    ClockIcon,
    BuildingOfficeIcon,
    CreditCardIcon,
    CheckCircleIcon,
    XCircleIcon,
    EyeIcon,
    PlusIcon,
} from "@heroicons/vue/24/outline";

// Accept real props from controller
const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    topBroker: {
        type: Object,
        default: null,
    },
    pendingBrokers: {
        type: Array,
        required: true,
    },
    systemHealth: {
        type: Object,
        required: true,
    },
});

// Add the formatNumber function
const formatNumber = (number) => {
    return new Intl.NumberFormat("en-PH").format(number || 0);
};

// Convert stats to dashboard cards format
const statsCards = [
    {
        title: "Total Brokers",
        value: props.stats.totalBrokers.toString(),
        subtitle: "Active brokers",
        icon: UserGroupIcon,
        color: "primary",
        trend: { direction: "up", value: "+3", label: "new this month" },
    },
    {
        title: "Pending Approvals",
        value: props.stats.pendingApprovals.toString(),
        subtitle: "Awaiting review",
        icon: ClockIcon,
        color: "accent",
        trend: { direction: "down", value: "-2", label: "from last week" },
    },
    {
        title: "Total Properties",
        value: props.stats.totalProperties.toString(),
        subtitle: "Listed properties",
        icon: BuildingOfficeIcon,
        color: "coconut",
        trend: { direction: "up", value: "+12", label: "this month" },
    },
    {
        title: "Total Transactions",
        value: props.stats.totalTransactions.toString(),
        subtitle: "Completed deals",
        icon: CreditCardIcon,
        color: "neutral",
        trend: { direction: "up", value: "+7", label: "this month" },
    },
];

const getHealthStatusColor = (status) => {
    const colors = {
        healthy: "bg-green-50 text-green-700",
        warning: "bg-yellow-50 text-yellow-700",
        error: "bg-red-50 text-red-700",
    };
    return colors[status] || colors.error;
};

const getHealthIndicatorColor = (status) => {
    const colors = {
        healthy: "bg-green-500",
        warning: "bg-yellow-500",
        error: "bg-red-500",
    };
    return colors[status] || colors.error;
};
</script>

<template>
    <Head title="Admin Dashboard - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Welcome Section -->
        <div class="mb-8">
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                        Admin Dashboard
                    </h1>
                    <p class="text-neutral-600 text-lg">
                        System overview and management for GeoCasa Bohol
                        platform.
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <ModernButton variant="outline" :icon="EyeIcon">
                        View Reports
                    </ModernButton>
                    <Link
                        :href="route('admin.brokers.index')"
                        class="btn-primary"
                    >
                        <PlusIcon class="w-5 h-5 mr-2" />
                        Manage Brokers
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="dashboard-grid mb-12">
            <DashboardCard
                v-for="stat in statsCards"
                :key="stat.title"
                :title="stat.title"
                :value="stat.value"
                :subtitle="stat.subtitle"
                :icon="stat.icon"
                :color="stat.color"
                :trend="stat.trend"
                :interactive="true"
            />
        </div>

        <!-- Pending Broker Approvals -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Top Performing Broker Widget -->
            <div class="card p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-neutral-900">
                        Top Performing Broker
                    </h2>
                    <Link
                        :href="route('leaderboard.index')"
                        class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                    >
                        View Full Leaderboard →
                    </Link>
                </div>
                
                <div v-if="topBroker" class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-accent-400 rounded-xl flex items-center justify-center text-white font-semibold">
                            {{ topBroker.name.charAt(0) }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-neutral-900">{{ topBroker.name }}</h3>
                            <p class="text-sm text-neutral-600">{{ topBroker.email }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-neutral-100">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-neutral-900">{{ topBroker.total_sales }}</div>
                            <div class="text-xs text-neutral-500 uppercase tracking-wide">Total Sales</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-neutral-900">₱{{ formatNumber(topBroker.total_sales_value) }}</div>
                            <div class="text-xs text-neutral-500 uppercase tracking-wide">Sales Value</div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-8">
                    <div class="text-neutral-400 mb-2">📊</div>
                    <p class="text-sm text-neutral-500">No sales data available</p>
                </div>
            </div>
            
            <div class="card p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-neutral-900">
                        Pending Broker Approvals
                    </h2>
                    <span
                        class="px-3 py-1 bg-accent-100 text-accent-700 rounded-full text-sm font-medium"
                    >
                        {{ pendingBrokers.length }} pending
                    </span>
                </div>
                <div v-if="pendingBrokers.length > 0" class="space-y-4">
                    <div
                        v-for="broker in pendingBrokers"
                        :key="broker.id"
                        class="flex items-center justify-between p-4 bg-neutral-50 rounded-2xl hover:bg-neutral-100 transition-colors"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-primary-400 to-accent-400 rounded-2xl flex items-center justify-center text-white font-medium text-sm shadow-soft"
                            >
                                {{ broker.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="font-medium text-neutral-900">
                                    {{ broker.name }}
                                </h3>
                                <p class="text-sm text-neutral-600">
                                    {{ broker.email }}
                                </p>
                                <p class="text-xs text-neutral-500">
                                    Applied: {{ broker.applied }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link
                                :href="route('admin.brokers.show', broker.id)"
                                class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-xl transition-colors"
                            >
                                <EyeIcon class="w-5 h-5" />
                            </Link>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <ClockIcon
                        class="w-12 h-12 text-neutral-300 mx-auto mb-4"
                    />
                    <p class="text-neutral-500">No pending approvals</p>
                </div>
            </div>

            <!-- System Health -->
            <div class="card p-8">
                <h2 class="text-xl font-bold text-neutral-900 mb-6">
                    System Health
                </h2>
                <div class="space-y-4">
                    <div
                        :class="[
                            'flex items-center justify-between p-4 rounded-2xl',
                            getHealthStatusColor(systemHealth.database.status),
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                :class="[
                                    'w-3 h-3 rounded-full',
                                    getHealthIndicatorColor(
                                        systemHealth.database.status
                                    ),
                                ]"
                            ></div>
                            <span class="font-medium text-neutral-900"
                                >Database</span
                            >
                        </div>
                        <span class="text-sm font-medium">{{
                            systemHealth.database.message
                        }}</span>
                    </div>
                    <div
                        :class="[
                            'flex items-center justify-between p-4 rounded-2xl',
                            getHealthStatusColor(systemHealth.storage.status),
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                :class="[
                                    'w-3 h-3 rounded-full',
                                    getHealthIndicatorColor(
                                        systemHealth.storage.status
                                    ),
                                ]"
                            ></div>
                            <span class="font-medium text-neutral-900"
                                >File Storage</span
                            >
                        </div>
                        <span class="text-sm font-medium">{{
                            systemHealth.storage.message
                        }}</span>
                    </div>
                    <div
                        :class="[
                            'flex items-center justify-between p-4 rounded-2xl',
                            getHealthStatusColor(systemHealth.cache.status),
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                :class="[
                                    'w-3 h-3 rounded-full',
                                    getHealthIndicatorColor(
                                        systemHealth.cache.status
                                    ),
                                ]"
                            ></div>
                            <span class="font-medium text-neutral-900"
                                >Cache System</span
                            >
                        </div>
                        <span class="text-sm font-medium">{{
                            systemHealth.cache.message
                        }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bohol Admin Section -->
        <div class="card p-8 tropical-gradient text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">
                        Manage Bohol Real Estate
                    </h2>
                    <p class="text-white/90 mb-4">
                        Oversee the beautiful properties and trusted brokers
                        across Bohol's stunning landscapes.
                    </p>
                    <Link :href="route('properties.index')" class="btn-coconut">
                        View All Properties
                    </Link>
                </div>
                <div class="hidden lg:block">
                    <div
                        class="w-32 h-32 bg-white/10 rounded-3xl backdrop-blur-sm flex items-center justify-center"
                    >
                        <span class="text-4xl">🏛️</span>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
