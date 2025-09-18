<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import DashboardCard from "@/Components/DashboardCard.vue";
import ModernButton from "@/Components/ModernButton.vue";
import ReminderWidget from "@/Components/ReminderWidget.vue";
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
    ChartBarIcon,
    UserIcon,
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
    reminders: {
        type: Object,
        default: () => ({}),
    },
    recentActivity: {
        type: Array,
        default: () => [],
    },
    performanceIndicators: {
        type: Object,
        default: () => ({}),
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
        warning: "bg-orange-50 text-orange-700",
        error: "bg-red-50 text-red-700",
    };
    return colors[status] || colors.error;
};

const getHealthIndicatorColor = (status) => {
    const colors = {
        healthy: "bg-green-500",
        warning: "bg-orange-500",
        error: "bg-red-500",
    };
    return colors[status] || colors.error;
};
</script>

<template>
    <Head title="Admin Dashboard - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Main Dashboard Container -->
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Dashboard Header -->
            <div
                class="bg-white rounded-2xl border border-slate-200/60 p-8 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-5">
                        <div
                            class="w-14 h-14 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl flex items-center justify-center border border-blue-200/50"
                        >
                            <UserGroupIcon class="w-7 h-7 text-blue-600" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900 mb-2">
                                Admin Dashboard
                            </h1>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-2 h-2 bg-green-500 rounded-full animate-pulse"
                                ></div>
                                <span class="text-slate-600 text-sm font-medium"
                                    >System Online</span
                                >
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('admin.reports.dashboard')"
                            class="text-slate-600 hover:text-blue-600 hover:bg-blue-50 px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 flex items-center gap-2 border border-transparent hover:border-blue-200"
                        >
                            <EyeIcon class="w-4 h-4" />
                            Analytics
                        </Link>
                        <Link
                            :href="route('admin.activity.index')"
                            class="bg-blue-600 text-white hover:bg-blue-700 px-5 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md"
                        >
                            <ClockIcon class="w-4 h-4" />
                            Activity
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    class="bg-white rounded-2xl border border-slate-200/60 p-6 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300 hover:border-blue-300/60 group"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl flex items-center justify-center border border-blue-200/50 group-hover:scale-110 transition-transform duration-300"
                        >
                            <UserGroupIcon class="w-6 h-6 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-slate-900 mb-1">
                                {{ props.stats.totalBrokers }}
                            </p>
                            <p class="text-sm font-medium text-slate-600">
                                Total Brokers
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-2xl border border-slate-200/60 p-6 hover:shadow-lg hover:shadow-orange-500/10 transition-all duration-300 hover:border-orange-300/60 group"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl flex items-center justify-center border border-orange-200/50 group-hover:scale-110 transition-transform duration-300"
                        >
                            <ClockIcon class="w-6 h-6 text-orange-600" />
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-slate-900 mb-1">
                                {{ props.stats.pendingApprovals }}
                            </p>
                            <p class="text-sm font-medium text-slate-600">
                                Pending Approvals
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-2xl border border-slate-200/60 p-6 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300 hover:border-blue-300/60 group"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl flex items-center justify-center border border-blue-200/50 group-hover:scale-110 transition-transform duration-300"
                        >
                            <BuildingOfficeIcon class="w-6 h-6 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-slate-900 mb-1">
                                {{ props.stats.totalProperties }}
                            </p>
                            <p class="text-sm font-medium text-slate-600">
                                Total Properties
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-2xl border border-slate-200/60 p-6 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300 hover:border-blue-300/60 group"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl flex items-center justify-center border border-blue-200/50 group-hover:scale-110 transition-transform duration-300"
                        >
                            <CreditCardIcon class="w-6 h-6 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-slate-900 mb-1">
                                {{ props.stats.totalTransactions }}
                            </p>
                            <p class="text-sm font-medium text-slate-600">
                                Total Transactions
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reminders Widget -->
            <div
                v-if="
                    props.reminders &&
                    props.reminders.summary &&
                    props.reminders.summary.total_reminders > 0
                "
            >
                <div
                    class="bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm"
                >
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg flex items-center justify-center border border-orange-200/50"
                        >
                            <ClockIcon class="w-5 h-5 text-orange-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                Reminders
                            </h2>
                            <p class="text-slate-600 text-xs">
                                {{ props.reminders.summary.total_reminders }}
                                pending tasks
                            </p>
                        </div>
                    </div>
                    <ReminderWidget :show-details="true" :max-items="4" />
                </div>
            </div>

            <!-- Client-Broker Assignment Widgets -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Quick Assignment Widget -->
                <div
                    class="bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm hover:shadow-lg transition-all duration-300"
                >
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg flex items-center justify-center border border-blue-200/50"
                            >
                                <UserGroupIcon class="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">
                                    Seller Assignments
                                </h2>
                                <p class="text-xs text-slate-600">
                                    Manage broker assignments
                                </p>
                            </div>
                        </div>
                        <Link
                            :href="route('seller-requests.index')"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-medium text-sm transition-all duration-200"
                        >
                            View All
                        </Link>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div
                            class="text-center p-4 bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl border border-slate-200/60"
                        >
                            <div class="text-2xl font-bold text-red-600 mb-1">
                                {{ props.unassignedSellerRequests || 0 }}
                            </div>
                            <div class="text-xs text-slate-700 font-medium">
                                Unassigned
                            </div>
                        </div>
                        <div
                            class="text-center p-4 bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl border border-slate-200/60"
                        >
                            <div class="text-2xl font-bold text-green-600 mb-1">
                                {{ props.assignedSellerRequests || 0 }}
                            </div>
                            <div class="text-xs text-slate-700 font-medium">
                                Assigned
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Link
                            :href="route('seller-requests.index')"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded-lg text-sm font-medium transition-all duration-200 text-center"
                        >
                            Manage Assignments
                        </Link>
                        <Link
                            :href="
                                route('seller-requests.index') +
                                '?assignment_status=unassigned'
                            "
                            class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-2 px-3 rounded-lg text-sm font-medium transition-all duration-200 text-center"
                        >
                            Unassigned
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Broker Performance Widget -->
            <div
                class="bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm hover:shadow-lg transition-all duration-300"
            >
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-green-50 to-green-100 rounded-lg flex items-center justify-center border border-green-200/50"
                        >
                            <ChartBarIcon class="w-5 h-5 text-green-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                Broker Analytics
                            </h2>
                            <p class="text-xs text-slate-600">
                                Performance overview
                            </p>
                        </div>
                    </div>
                    <Link
                        :href="route('admin.reports.brokers')"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 shadow-sm hover:shadow-md"
                    >
                        View Analytics
                    </Link>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div
                        class="text-center p-6 bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl border border-slate-200/60"
                    >
                        <div class="text-3xl font-bold text-blue-600 mb-2">
                            {{
                                Math.round(
                                    ((props.stats.assignedClients || 0) /
                                        (props.stats.totalBrokers || 1)) *
                                        10
                                ) / 10
                            }}
                        </div>
                        <div class="text-sm text-slate-700 font-medium">
                            Avg Clients/Broker
                        </div>
                    </div>
                    <div
                        class="text-center p-6 bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl border border-slate-200/60"
                    >
                        <div class="text-3xl font-bold text-blue-600 mb-2">
                            {{ props.stats.activeBrokers || 0 }}
                        </div>
                        <div class="text-sm text-slate-700 font-medium">
                            Active Brokers
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <Link
                        :href="route('admin.assignment-recommendations')"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-xl text-sm font-medium transition-all duration-200 text-center shadow-sm hover:shadow-md"
                    >
                        Recommendations
                    </Link>
                    <Link
                        :href="route('admin.reports.brokers')"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-xl text-sm font-medium transition-all duration-200 text-center shadow-sm hover:shadow-md"
                    >
                        Performance
                    </Link>
                </div>
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Broker Performance -->
            <div
                class="bg-white rounded-2xl border border-slate-200/60 p-8 shadow-sm hover:shadow-lg transition-all duration-300"
            >
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-green-50 to-green-100 rounded-xl flex items-center justify-center border border-green-200/50"
                        >
                            <ChartBarIcon class="w-6 h-6 text-green-600" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900 mb-1">
                                Top Broker Performance
                            </h2>
                            <p class="text-sm font-medium text-slate-600">
                                Leading broker metrics
                            </p>
                        </div>
                    </div>
                    <Link
                        :href="route('leaderboard.index')"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-xl font-medium text-sm transition-all duration-200 shadow-sm hover:shadow-md"
                    >
                        View Metrics
                    </Link>
                </div>

                <div v-if="topBroker" class="space-y-6">
                    <div
                        class="flex items-center gap-4 p-6 bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl border border-slate-200/60"
                    >
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-200 rounded-2xl flex items-center justify-center text-green-700 font-bold text-xl border border-green-200/50"
                        >
                            {{ topBroker.name.charAt(0) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="font-bold text-slate-900 text-lg">
                                    {{ topBroker.name }}
                                </h3>
                                <span
                                    class="text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded-full font-medium border border-green-200/50"
                                >
                                    Top Performer
                                </span>
                            </div>
                            <p class="text-sm text-slate-600 font-medium">
                                {{ topBroker.email }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div
                            class="text-center p-6 bg-white rounded-2xl border border-slate-200/60 shadow-sm"
                        >
                            <div class="text-2xl font-bold text-slate-900 mb-2">
                                {{ topBroker.total_sales }}
                            </div>
                            <div class="text-sm text-slate-600 font-medium">
                                Total Sales
                            </div>
                        </div>
                        <div
                            class="text-center p-6 bg-white rounded-2xl border border-slate-200/60 shadow-sm"
                        >
                            <div class="text-2xl font-bold text-slate-900 mb-2">
                                ₱{{ formatNumber(topBroker.total_sales_value) }}
                            </div>
                            <div class="text-sm text-slate-600 font-medium">
                                Revenue Generated
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-8">
                    <div class="text-slate-400 mb-2">
                        <ChartBarIcon class="w-8 h-8 mx-auto" />
                    </div>
                    <p class="text-sm text-slate-500">
                        No performance data available
                    </p>
                </div>
            </div>

            <!-- Broker Authorization Queue -->
            <div
                class="bg-white rounded-2xl border border-slate-200/60 p-8 shadow-sm hover:shadow-lg transition-all duration-300"
            >
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl flex items-center justify-center border border-orange-200/50"
                        >
                            <UserIcon class="w-6 h-6 text-orange-600" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900 mb-1">
                                Broker Authorization Queue
                            </h2>
                            <p class="text-sm font-medium text-slate-600">
                                Pending broker applications
                            </p>
                        </div>
                    </div>
                    <span
                        class="px-4 py-2 bg-orange-100 text-orange-700 rounded-xl text-sm font-medium border border-orange-200/50"
                    >
                        {{ pendingBrokers.length }} Pending
                    </span>
                </div>
                <div v-if="pendingBrokers.length > 0" class="space-y-3">
                    <div
                        v-for="broker in pendingBrokers"
                        :key="broker.id"
                        class="p-4 border border-slate-200/60 rounded-xl hover:border-slate-300 transition-all duration-200 bg-gradient-to-br from-slate-50 to-slate-100 hover:shadow-sm"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-orange-100 to-orange-200 rounded-lg flex items-center justify-center text-orange-700 font-bold text-sm border border-orange-200/50"
                                >
                                    {{ broker.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-slate-900">
                                            {{ broker.name }}
                                        </h3>
                                        <span
                                            class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-md font-medium"
                                        >
                                            {{ broker.applied }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-600">
                                        {{ broker.email }}
                                    </p>
                                </div>
                            </div>
                            <Link
                                :href="route('admin.brokers.show', broker.id)"
                                class="px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-sm font-medium transition-all duration-200"
                            >
                                Review
                            </Link>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <ClockIcon class="w-8 h-8 text-slate-400 mx-auto mb-2" />
                    <p class="text-slate-500">No pending applications</p>
                </div>
            </div>

            <!-- System Health Monitor -->
            <div
                class="bg-white rounded-2xl border border-slate-200/60 p-8 shadow-sm hover:shadow-lg transition-all duration-300"
            >
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl flex items-center justify-center border border-blue-200/50"
                        >
                            <CheckCircleIcon class="w-6 h-6 text-blue-600" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">
                            System Health Monitor
                        </h3>
                    </div>
                    <div
                        class="flex items-center gap-3 bg-blue-50 rounded-xl px-4 py-2 border border-blue-200/50"
                    >
                        <div
                            class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"
                        ></div>
                        <span class="text-blue-700 text-sm font-medium"
                            >System Online</span
                        >
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div
                        class="flex items-center gap-3 p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200/50"
                    >
                        <div
                            :class="[
                                'w-3 h-3 rounded-full',
                                getHealthIndicatorColor(
                                    systemHealth.database.status
                                ),
                            ]"
                        ></div>
                        <span class="text-sm font-medium text-blue-700"
                            >Database</span
                        >
                    </div>
                    <div
                        class="flex items-center gap-3 p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200/50"
                    >
                        <div
                            :class="[
                                'w-3 h-3 rounded-full',
                                getHealthIndicatorColor(
                                    systemHealth.storage.status
                                ),
                            ]"
                        ></div>
                        <span class="text-sm font-medium text-blue-700"
                            >Storage</span
                        >
                    </div>
                    <div
                        class="flex items-center gap-3 p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200/50"
                    >
                        <div
                            :class="[
                                'w-3 h-3 rounded-full',
                                getHealthIndicatorColor(
                                    systemHealth.cache.status
                                ),
                            ]"
                        ></div>
                        <span class="text-sm font-medium text-blue-700"
                            >Cache</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Administrative Overview -->
        <div
            class="bg-white rounded-xl border border-slate-200/60 p-6 shadow-sm hover:shadow-lg transition-all duration-300"
        >
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg flex items-center justify-center border border-blue-200/50"
                    >
                        <BuildingOfficeIcon class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            Administrative Overview
                        </h2>
                        <p class="text-xs text-slate-600">
                            Platform management
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-3 bg-green-50 rounded-xl px-4 py-2 border border-green-200/50"
                >
                    <div
                        class="w-3 h-3 bg-green-500 rounded-full animate-pulse"
                    ></div>
                    <span class="text-green-700 font-medium text-sm">
                        System Active
                    </span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div
                    class="text-center p-4 bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl border border-slate-200/60 hover:shadow-md transition-all duration-200"
                >
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center mx-auto mb-3 border border-blue-200/50"
                    >
                        <BuildingOfficeIcon class="w-6 h-6 text-blue-600" />
                    </div>
                    <h3 class="font-bold text-slate-900 mb-2 text-base">
                        Total Properties
                    </h3>
                    <p class="text-2xl font-bold text-slate-900 mb-1">
                        {{ props.stats.totalProperties }}
                    </p>
                    <p class="text-slate-600 text-xs">Listed properties</p>
                </div>
                <div
                    class="text-center p-4 bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl border border-slate-200/60 hover:shadow-md transition-all duration-200"
                >
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center mx-auto mb-3 border border-blue-200/50"
                    >
                        <UserGroupIcon class="w-6 h-6 text-blue-600" />
                    </div>
                    <h3 class="font-bold text-slate-900 mb-2 text-base">
                        Active Brokers
                    </h3>
                    <p class="text-2xl font-bold text-slate-900 mb-1">
                        {{ props.stats.totalBrokers }}
                    </p>
                    <p class="text-slate-600 text-xs">Verified agents</p>
                </div>
                <div
                    class="text-center p-4 bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl border border-slate-200/60 hover:shadow-md transition-all duration-200"
                >
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center mx-auto mb-3 border border-blue-200/50"
                    >
                        <CreditCardIcon class="w-6 h-6 text-blue-600" />
                    </div>
                    <h3 class="font-bold text-slate-900 mb-2 text-base">
                        Total Transactions
                    </h3>
                    <p class="text-2xl font-bold text-slate-900 mb-1">
                        {{ props.stats.totalTransactions }}
                    </p>
                    <p class="text-slate-600 text-xs">Completed deals</p>
                </div>
            </div>
            <div
                class="flex items-center justify-between pt-4 border-t border-slate-200"
            >
                <div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">
                        Platform Management
                    </h3>
                    <p class="text-slate-600 mb-4">
                        Comprehensive oversight of properties and broker
                        network.
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="route('admin.properties.index')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
                    >
                        <EyeIcon class="w-4 h-4" />
                        View Properties
                    </Link>
                </div>
            </div>
        </div>
        <!-- Recent Activity Feed -->
        <div class="mb-8">
            <div
                class="bg-gradient-to-br from-white to-slate-50 rounded-xl border border-slate-200/60 shadow-lg overflow-hidden"
            >
                <div
                    class="p-6 border-b border-slate-200/60 bg-gradient-to-r from-blue-50 to-blue-100"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center border border-blue-200/50"
                            >
                                <ChartBarIcon class="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">
                                    Recent Activity Feed
                                </h3>
                                <p class="text-xs text-slate-600">
                                    Latest system activity
                                </p>
                            </div>
                        </div>
                        <Link
                            :href="route('admin.activity.index')"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-medium text-sm transition-all duration-200"
                        >
                            View All →
                        </Link>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    <div
                        v-for="activity in recentActivity"
                        :key="activity.id"
                        class="p-6 hover:bg-gradient-to-r hover:from-slate-50 hover:to-blue-50 transition-all duration-200 group"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                :class="[
                                    'w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold shadow-sm border',
                                    activity.color === 'green'
                                        ? 'bg-gradient-to-br from-green-500 to-green-600 border-green-300'
                                        : activity.color === 'orange'
                                        ? 'bg-gradient-to-br from-orange-500 to-orange-600 border-orange-300'
                                        : 'bg-gradient-to-br from-blue-500 to-blue-600 border-blue-300',
                                ]"
                            >
                                {{ activity.icon }}
                            </div>
                            <div class="flex-1">
                                <div
                                    class="flex items-center justify-between mb-2"
                                >
                                    <h4
                                        class="font-bold text-slate-900 text-lg group-hover:text-blue-700 transition-colors"
                                    >
                                        {{ activity.title }}
                                    </h4>
                                    <span
                                        class="text-xs text-slate-500 font-medium bg-slate-100 px-3 py-1 rounded-full"
                                        >{{ activity.created_at }}</span
                                    >
                                </div>
                                <p
                                    class="text-sm text-slate-600 mb-3 leading-relaxed"
                                >
                                    {{ activity.description }}
                                </p>
                                <div
                                    class="flex items-center gap-6 text-xs text-slate-500"
                                >
                                    <span
                                        class="flex items-center gap-2 bg-slate-100 px-3 py-1.5 rounded-full"
                                    >
                                        <UserIcon class="w-3 h-3" />
                                        <span class="font-medium">{{
                                            activity.user
                                        }}</span>
                                    </span>
                                    <span
                                        v-if="activity.amount"
                                        class="flex items-center gap-2 bg-green-100 text-green-700 px-3 py-1.5 rounded-full"
                                    >
                                        <CreditCardIcon class="w-3 h-3" />
                                        <span class="font-bold"
                                            >₱{{
                                                formatNumber(activity.amount)
                                            }}</span
                                        >
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Indicators -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Monthly Targets -->
            <div
                class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200/60 p-6 shadow-lg"
            >
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center border border-green-300/50"
                    >
                        <CheckCircleIcon class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            Monthly Targets
                        </h3>
                        <p class="text-xs text-green-600">Progress overview</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-slate-700"
                                >Transactions</span
                            >
                            <span class="text-sm font-bold text-green-600">
                                {{
                                    performanceIndicators.monthly_targets
                                        ?.transactions?.current || 0
                                }}
                                /
                                {{
                                    performanceIndicators.monthly_targets
                                        ?.transactions?.target || 0
                                }}
                            </span>
                        </div>
                        <div class="w-full bg-green-100 rounded-full h-3">
                            <div
                                class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-300"
                                :style="{
                                    width:
                                        (performanceIndicators.monthly_targets
                                            ?.transactions?.percentage || 0) +
                                        '%',
                                }"
                            ></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-slate-700"
                                >Revenue</span
                            >
                            <span class="text-sm font-bold text-green-600">
                                ₱{{
                                    formatNumber(
                                        performanceIndicators.monthly_targets
                                            ?.revenue?.current || 0
                                    )
                                }}
                            </span>
                        </div>
                        <div class="w-full bg-green-100 rounded-full h-3">
                            <div
                                class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-300"
                                :style="{
                                    width:
                                        (performanceIndicators.monthly_targets
                                            ?.revenue?.percentage || 0) + '%',
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conversion Rates -->
            <div
                class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200/60 p-6 shadow-lg"
            >
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center border border-blue-300/50"
                    >
                        <ChartBarIcon class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            Conversion Rates
                        </h3>
                        <p class="text-xs text-blue-600">Performance metrics</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div
                        class="text-center p-4 bg-gradient-to-br from-white to-blue-50 rounded-xl border border-blue-200/60 shadow-sm hover:shadow-md transition-all duration-200"
                    >
                        <div class="text-2xl font-bold text-blue-600 mb-1">
                            {{
                                Math.round(
                                    performanceIndicators.conversion_rates
                                        ?.inquiry_to_sale || 0
                                )
                            }}%
                        </div>
                        <div class="text-xs text-slate-600 font-bold">
                            Inquiry to Sale
                        </div>
                    </div>
                    <div
                        class="text-center p-4 bg-gradient-to-br from-white to-blue-50 rounded-xl border border-blue-200/60 shadow-sm hover:shadow-md transition-all duration-200"
                    >
                        <div class="text-2xl font-bold text-blue-600 mb-1">
                            {{
                                Math.round(
                                    performanceIndicators.conversion_rates
                                        ?.property_sale_rate || 0
                                )
                            }}%
                        </div>
                        <div class="text-xs text-slate-600 font-bold">
                            Property Sale Rate
                        </div>
                    </div>
                    <div
                        class="text-center p-4 bg-gradient-to-br from-white to-purple-50 rounded-xl border border-purple-200/60 shadow-sm hover:shadow-md transition-all duration-200"
                    >
                        <div class="text-2xl font-bold text-purple-600 mb-1">
                            {{
                                Math.round(
                                    performanceIndicators.conversion_rates
                                        ?.broker_approval_rate || 0
                                )
                            }}%
                        </div>
                        <div class="text-xs text-slate-600 font-bold">
                            Broker Approval
                        </div>
                    </div>
                    <div
                        class="text-center p-4 bg-gradient-to-br from-white to-green-50 rounded-xl border border-green-200/60 shadow-sm hover:shadow-md transition-all duration-200"
                    >
                        <div class="text-2xl font-bold text-green-600 mb-1">
                            {{
                                performanceIndicators.system_performance
                                    ?.uptime || "N/A"
                            }}
                        </div>
                        <div class="text-xs text-slate-600 font-bold">
                            System Uptime
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Panel -->
        <div
            class="bg-gradient-to-br from-white to-slate-50 rounded-xl border border-slate-200/60 p-6 shadow-lg mb-6"
        >
            <div class="flex items-center gap-3 mb-6">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center border border-blue-200/50"
                >
                    <PlusIcon class="w-5 h-5 text-blue-600" />
                </div>
                <h3 class="text-lg font-bold text-slate-900">Quick Actions</h3>
            </div>

            <div class="space-y-8">
                <!-- Management Section -->
                <div>
                    <h4
                        class="text-sm font-bold text-slate-600 mb-4 uppercase tracking-wider"
                    >
                        Management
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link
                            :href="route('admin.users.index')"
                            class="flex items-center gap-4 p-4 bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl hover:from-blue-50 hover:to-blue-100 hover:border-blue-200 border border-slate-200/60 transition-all duration-200 group shadow-sm hover:shadow-md"
                        >
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 group-hover:from-blue-200 group-hover:to-blue-300 rounded-2xl flex items-center justify-center border border-blue-200/50"
                            >
                                <UserGroupIcon class="w-5 h-5 text-blue-600" />
                            </div>
                            <span
                                class="text-sm font-bold text-slate-700 group-hover:text-blue-700"
                                >Users</span
                            >
                        </Link>
                        <Link
                            :href="route('admin.properties.index')"
                            class="flex items-center gap-4 p-4 bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl hover:from-blue-50 hover:to-blue-100 hover:border-blue-200 border border-slate-200/60 transition-all duration-200 group shadow-sm hover:shadow-md"
                        >
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 group-hover:from-blue-200 group-hover:to-blue-300 rounded-2xl flex items-center justify-center border border-blue-200/50"
                            >
                                <BuildingOfficeIcon
                                    class="w-5 h-5 text-blue-600"
                                />
                            </div>
                            <span
                                class="text-sm font-bold text-slate-700 group-hover:text-blue-700"
                                >Properties</span
                            >
                        </Link>
                        <Link
                            :href="route('admin.transactions.index')"
                            class="flex items-center gap-4 p-4 bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl hover:from-blue-50 hover:to-blue-100 hover:border-blue-200 border border-slate-200/60 transition-all duration-200 group shadow-sm hover:shadow-md"
                        >
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 group-hover:from-blue-200 group-hover:to-blue-300 rounded-2xl flex items-center justify-center border border-blue-200/50"
                            >
                                <CreditCardIcon class="w-5 h-5 text-blue-600" />
                            </div>
                            <span
                                class="text-sm font-bold text-slate-700 group-hover:text-blue-700"
                                >Transactions</span
                            >
                        </Link>
                        <Link
                            :href="route('admin.brokers.index')"
                            class="flex items-center gap-4 p-4 bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl hover:from-blue-50 hover:to-blue-100 hover:border-blue-200 border border-slate-200/60 transition-all duration-200 group shadow-sm hover:shadow-md"
                        >
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-blue-100 to-blue-200 group-hover:from-blue-200 group-hover:to-blue-300 rounded-2xl flex items-center justify-center border border-blue-200/50"
                            >
                                <UserGroupIcon class="w-5 h-5 text-blue-600" />
                            </div>
                            <span
                                class="text-sm font-bold text-slate-700 group-hover:text-blue-700"
                                >Brokers</span
                            >
                        </Link>
                    </div>
                </div>

                <!-- Analytics Section -->
                <div>
                    <h4 class="text-sm font-medium text-slate-500 mb-3">
                        Analytics & Monitoring
                    </h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <Link
                            :href="route('admin.reports.dashboard')"
                            class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg hover:bg-blue-50 hover:border-blue-200 border border-transparent transition-all group"
                        >
                            <div
                                class="w-8 h-8 bg-blue-100 group-hover:bg-blue-200 rounded-lg flex items-center justify-center"
                            >
                                <EyeIcon class="w-4 h-4 text-blue-600" />
                            </div>
                            <span class="text-sm font-medium text-slate-700"
                                >Reports</span
                            >
                        </Link>
                        <Link
                            :href="route('admin.activity.index')"
                            class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg hover:bg-blue-50 hover:border-blue-200 border border-transparent transition-all group"
                        >
                            <div
                                class="w-8 h-8 bg-blue-100 group-hover:bg-blue-200 rounded-lg flex items-center justify-center"
                            >
                                <ClockIcon class="w-4 h-4 text-blue-600" />
                            </div>
                            <span class="text-sm font-medium text-slate-700"
                                >Activity</span
                            >
                        </Link>
                        <Link
                            :href="route('admin.compliance.index')"
                            class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg hover:bg-orange-50 hover:border-orange-200 border border-transparent transition-all group"
                        >
                            <div
                                class="w-8 h-8 bg-orange-100 group-hover:bg-orange-200 rounded-lg flex items-center justify-center"
                            >
                                <CheckCircleIcon
                                    class="w-4 h-4 text-orange-600"
                                />
                            </div>
                            <span class="text-sm font-medium text-slate-700"
                                >Compliance</span
                            >
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
