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
        <!-- Admin Dashboard Header -->
        <div
            class="mb-8 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-8 border border-blue-200 shadow-lg"
        >
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg"
                        >
                            <UserGroupIcon class="w-8 h-8 text-white" />
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h1 class="text-3xl font-bold text-slate-900">
                                    Admin Dashboard
                                </h1>
                                <span
                                    class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium border border-green-200"
                                    >Active</span
                                >
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-2 h-2 bg-green-500 rounded-full"
                                    ></span>
                                    <span
                                        class="text-slate-600 text-sm font-medium"
                                        >System Online</span
                                    >
                                </div>
                                <div class="w-px h-4 bg-slate-300"></div>
                                <span class="text-slate-600 text-sm font-medium"
                                    >Administrator Panel</span
                                >
                            </div>
                        </div>
                    </div>
                    <p class="text-slate-700 text-lg">
                        Manage platform operations, users, and system settings for GeoCasa Bohol.
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.reports.dashboard')"
                        class="bg-white hover:bg-blue-50 text-blue-600 px-6 py-3 rounded-xl font-medium transition-all duration-200 flex items-center gap-2 border border-blue-200 shadow-sm hover:shadow-md"
                    >
                        <EyeIcon class="w-5 h-5" />
                        Analytics
                    </Link>
                    <Link
                        :href="route('admin.activity.index')"
                        class="bg-blue-600 text-white hover:bg-blue-700 px-6 py-3 rounded-xl font-medium transition-all duration-200 flex items-center gap-2 shadow-sm hover:shadow-md"
                    >
                        <ClockIcon class="w-5 h-5" />
                        Activity Log
                    </Link>
                </div>
            </div>
        </div>

        <!-- Dashboard Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Brokers Card -->
            <div
                class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition-all duration-200"
            >
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center"
                    >
                        <UserGroupIcon class="w-6 h-6 text-blue-600" />
                    </div>
                    <span
                        class="text-xs font-medium text-blue-700 bg-blue-50 px-2 py-1 rounded-md"
                        >Active</span
                    >
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-bold text-slate-900">
                        {{ props.stats.totalBrokers }}
                    </h3>
                    <p class="text-sm font-medium text-slate-700">
                        Registered Brokers
                    </p>
                    <p class="text-xs text-slate-500">
                        Platform agents
                    </p>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition-all duration-200"
            >
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center"
                    >
                        <ClockIcon class="w-6 h-6 text-orange-600" />
                    </div>
                    <span
                        class="text-xs font-medium text-orange-700 bg-orange-50 px-2 py-1 rounded-md"
                        >Pending</span
                    >
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-bold text-slate-900">
                        {{ props.stats.pendingApprovals }}
                    </h3>
                    <p class="text-sm font-medium text-slate-700">
                        Pending Approvals
                    </p>
                    <p class="text-xs text-slate-500">
                        Awaiting review
                    </p>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition-all duration-200"
            >
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center"
                    >
                        <BuildingOfficeIcon class="w-6 h-6 text-green-600" />
                    </div>
                    <span
                        class="text-xs font-medium text-green-700 bg-green-50 px-2 py-1 rounded-md"
                        >Listed</span
                    >
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-bold text-slate-900">
                        {{ props.stats.totalProperties }}
                    </h3>
                    <p class="text-sm font-medium text-slate-700">
                        Total Properties
                    </p>
                    <p class="text-xs text-slate-500">
                        Platform listings
                    </p>
                </div>
            </div>

            <div
                class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition-all duration-200"
            >
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center"
                    >
                        <CreditCardIcon class="w-6 h-6 text-purple-600" />
                    </div>
                    <span
                        class="text-xs font-medium text-purple-700 bg-purple-50 px-2 py-1 rounded-md"
                        >Completed</span
                    >
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-bold text-slate-900">
                        {{ props.stats.totalTransactions }}
                    </h3>
                    <p class="text-sm font-medium text-slate-700">
                        Transactions
                    </p>
                    <p class="text-xs text-slate-500">
                        Platform activity
                    </p>
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
            class="mb-8"
        >
            <ReminderWidget :show-details="true" :max-items="5" />
        </div>

        <!-- Client-Broker Assignment Widgets -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">
            <!-- Quick Assignment Widget -->
            <div
                class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition-all duration-200"
            >
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center"
                        >
                            <UserGroupIcon class="w-6 h-6 text-blue-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">
                                Seller Assignments
                            </h2>
                            <p class="text-sm text-slate-600">
                                Assign seller requests to brokers
                            </p>
                        </div>
                    </div>
                    <Link
                        :href="route('admin.client-assignments')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-medium text-sm transition-colors"
                    >
                        Manage All
                    </Link>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div
                        class="text-center p-4 bg-slate-50 rounded-xl border border-slate-200"
                    >
                        <div class="text-2xl font-bold text-red-600 mb-1">
                            {{ props.unassignedSellerRequests || 0 }}
                        </div>
                        <div
                            class="text-xs text-slate-600 font-medium"
                        >
                            Unassigned
                        </div>
                    </div>
                    <div
                        class="text-center p-4 bg-slate-50 rounded-xl border border-slate-200"
                    >
                        <div class="text-2xl font-bold text-green-600 mb-1">
                            {{ props.assignedSellerRequests || 0 }}
                        </div>
                        <div
                            class="text-xs text-slate-600 font-medium"
                        >
                            Assigned
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <Link
                        :href="route('admin.client-assignments')"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded-lg text-sm font-medium transition-colors text-center"
                    >
                        Bulk Assign
                    </Link>
                    <Link
                        :href="
                            route('admin.client-assignments') +
                            '?filter=unassigned'
                        "
                        class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-2 px-3 rounded-lg text-sm font-medium transition-colors text-center"
                    >
                        View Unassigned
                    </Link>
                </div>
            </div>

            <!-- Broker Performance Widget -->
            <div
                class="bg-white rounded-2xl border border-slate-200 p-6 hover:shadow-lg transition-all duration-200"
            >
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center"
                        >
                            <ChartBarIcon class="w-6 h-6 text-green-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">
                                Broker Analytics
                            </h2>
                            <p class="text-sm text-slate-600">
                                Monitor broker assignments
                            </p>
                        </div>
                    </div>
                    <Link
                        :href="route('admin.broker-analytics-page')"
                        class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg font-medium text-sm transition-colors"
                    >
                        View Analytics
                    </Link>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div
                        class="text-center p-4 bg-slate-50 rounded-xl border border-slate-200"
                    >
                        <div class="text-2xl font-bold text-blue-600 mb-1">
                            {{
                                Math.round(
                                    ((props.stats.assignedClients || 0) /
                                        (props.stats.totalBrokers || 1)) *
                                        10
                                ) / 10
                            }}
                        </div>
                        <div
                            class="text-xs text-slate-600 font-medium"
                        >
                            Avg Clients/Broker
                        </div>
                    </div>
                    <div
                        class="text-center p-4 bg-slate-50 rounded-xl border border-slate-200"
                    >
                        <div class="text-2xl font-bold text-purple-600 mb-1">
                            {{ props.stats.activeBrokers || 0 }}
                        </div>
                        <div
                            class="text-xs text-slate-600 font-medium"
                        >
                            Active Brokers
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <Link
                        :href="route('admin.assignment-recommendations')"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 px-3 rounded-lg text-sm font-medium transition-colors text-center"
                    >
                        Recommendations
                    </Link>
                    <Link
                        :href="route('admin.broker-analytics-page')"
                        class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-2 px-3 rounded-lg text-sm font-medium transition-colors text-center"
                    >
                        Performance
                    </Link>
                </div>
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">
            <!-- Broker Performance -->
            <div
                class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm"
            >
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center"
                        >
                            <ChartBarIcon class="w-6 h-6 text-yellow-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">
                                Top Broker Performance
                            </h2>
                            <p class="text-sm text-slate-600">
                                Leading broker metrics
                            </p>
                        </div>
                    </div>
                    <Link
                        :href="route('leaderboard.index')"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg font-medium text-sm transition-colors"
                    >
                        View Metrics
                    </Link>
                </div>

                <div v-if="topBroker" class="space-y-4">
                    <div
                        class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl border border-slate-200"
                    >
                        <div
                            class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center text-yellow-700 font-semibold text-lg"
                        >
                            {{ topBroker.name.charAt(0) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-slate-900">
                                    {{ topBroker.name }}
                                </h3>
                                <span
                                    class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-md font-medium"
                                    >Top Performer</span
                                >
                            </div>
                            <p class="text-sm text-slate-600">
                                {{ topBroker.email }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-200"
                    >
                        <div
                            class="text-center p-3 bg-white rounded-lg border border-slate-200"
                        >
                            <div class="text-xl font-bold text-slate-900">
                                {{ topBroker.total_sales }}
                            </div>
                            <div
                                class="text-xs text-slate-600 font-medium"
                            >
                                Total Sales
                            </div>
                        </div>
                        <div
                            class="text-center p-3 bg-white rounded-lg border border-slate-200"
                        >
                            <div class="text-xl font-bold text-slate-900">
                                ₱{{ formatNumber(topBroker.total_sales_value) }}
                            </div>
                            <div
                                class="text-xs text-slate-600 font-medium"
                            >
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
                class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm"
            >
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"
                        >
                            <UserIcon class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">
                                Broker Authorization Queue
                            </h2>
                            <p class="text-sm text-slate-600">
                                Pending broker applications
                            </p>
                        </div>
                    </div>
                    <span
                        class="px-3 py-1 bg-blue-100 text-blue-700 rounded-md text-sm font-medium"
                    >
                        {{ pendingBrokers.length }} Pending
                    </span>
                </div>
                <div v-if="pendingBrokers.length > 0" class="space-y-3">
                    <div
                        v-for="broker in pendingBrokers"
                        :key="broker.id"
                        class="p-4 border border-slate-200 rounded-lg hover:border-slate-300 transition-colors bg-slate-50"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-700 font-semibold"
                                >
                                    {{ broker.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-slate-900">
                                            {{ broker.name }}
                                        </h3>
                                        <span
                                            class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-md font-medium"
                                            >Pending Review</span
                                        >
                                    </div>
                                    <p
                                        class="text-sm text-slate-600"
                                    >
                                        {{ broker.email }}
                                    </p>
                                    <p
                                        class="text-xs text-slate-500"
                                    >
                                        Applied: {{ broker.applied }}
                                    </p>
                                </div>
                            </div>
                            <Link
                                :href="route('admin.brokers.show', broker.id)"
                                class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                            >
                                <EyeIcon class="w-4 h-4" />
                            </Link>
                        </div>
                        <div
                            class="flex gap-2 pt-3 border-t border-slate-200"
                        >
                            <button
                                class="flex-1 bg-green-600 text-white hover:bg-green-700 py-2 px-3 rounded-lg text-sm font-medium transition-colors"
                            >
                                Approve
                            </button>
                            <button
                                class="flex-1 bg-red-600 text-white hover:bg-red-700 py-2 px-3 rounded-lg text-sm font-medium transition-colors"
                            >
                                Deny
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <ClockIcon class="w-8 h-8 text-slate-400 mx-auto mb-2" />
                    <p class="text-slate-500">
                        No pending applications
                    </p>
                </div>
            </div>

            <!-- System Health Monitor -->
            <div
                class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm"
            >
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center"
                        >
                            <CheckCircleIcon class="w-5 h-5 text-green-600" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">
                                System Health Monitor
                            </h2>
                            <p class="text-slate-600 text-sm">
                                Real-time system status monitoring
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex items-center gap-2 bg-green-100 rounded-lg px-3 py-2"
                    >
                        <div
                            class="w-2 h-2 bg-green-500 rounded-full"
                        ></div>
                        <span class="text-green-700 font-medium text-sm">
                            Systems Online
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div
                        :class="[
                            'text-center bg-slate-50 rounded-lg p-4 border transition-colors',
                            getHealthStatusColor(
                                systemHealth.database.status
                            ).includes('green')
                                ? 'border-green-200'
                                : 'border-red-200',
                        ]"
                    >
                        <div class="flex items-center justify-center gap-2 mb-3">
                            <div
                                :class="[
                                    'w-2 h-2 rounded-full',
                                    getHealthIndicatorColor(
                                        systemHealth.database.status
                                    ),
                                ]"
                            ></div>
                            <span class="font-medium text-slate-900"
                                >Database</span
                            >
                        </div>
                        <div
                            class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3"
                        >
                            <CheckCircleIcon class="w-6 h-6 text-green-600" />
                        </div>
                        <p
                            class="text-green-700 text-sm font-medium bg-green-100 px-2 py-1 rounded-md"
                        >
                            {{ systemHealth.database.message }}
                        </p>
                    </div>
                    <div
                        :class="[
                            'text-center bg-slate-50 rounded-lg p-4 border transition-colors',
                            getHealthStatusColor(
                                systemHealth.storage.status
                            ).includes('green')
                                ? 'border-green-200'
                                : 'border-red-200',
                        ]"
                    >
                        <div class="flex items-center justify-center gap-2 mb-3">
                            <div
                                :class="[
                                    'w-2 h-2 rounded-full',
                                    getHealthIndicatorColor(
                                        systemHealth.storage.status
                                    ),
                                ]"
                            ></div>
                            <span class="font-medium text-slate-900"
                                >File Storage</span
                            >
                        </div>
                        <div
                            class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3"
                        >
                            <CheckCircleIcon class="w-6 h-6 text-green-600" />
                        </div>
                        <p
                            class="text-green-700 text-sm font-medium bg-green-100 px-2 py-1 rounded-md"
                        >
                            {{ systemHealth.storage.message }}
                        </p>
                    </div>
                    <div
                        :class="[
                            'text-center bg-slate-50 rounded-lg p-4 border transition-colors',
                            getHealthStatusColor(
                                systemHealth.cache.status
                            ).includes('yellow')
                                ? 'border-yellow-200'
                                : 'border-green-200',
                        ]"
                    >
                        <div class="flex items-center justify-center gap-2 mb-3">
                            <div
                                :class="[
                                    'w-2 h-2 rounded-full',
                                    getHealthIndicatorColor(
                                        systemHealth.cache.status
                                    ),
                                ]"
                            ></div>
                            <span class="font-medium text-slate-900"
                                >Cache System</span
                            >
                        </div>
                        <div
                            class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-3"
                        >
                            <XCircleIcon class="w-6 h-6 text-yellow-600" />
                        </div>
                        <p
                            class="text-yellow-700 text-sm font-medium bg-yellow-100 px-2 py-1 rounded-md"
                        >
                            {{ systemHealth.cache.message }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Administrative Overview -->
        <div
            class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm"
        >
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                    >
                        <BuildingOfficeIcon class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">
                            Administrative Overview
                        </h2>
                        <p class="text-slate-600">
                            Platform management and oversight
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-2 bg-green-50 rounded-lg px-4 py-2 border border-green-200"
                >
                    <div
                        class="w-3 h-3 bg-green-500 rounded-full"
                    ></div>
                    <span class="text-green-700 font-medium text-sm">
                        System Active
                    </span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div
                    class="text-center p-4 bg-slate-50 rounded-lg border border-slate-200"
                >
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3"
                    >
                        <BuildingOfficeIcon class="w-6 h-6 text-blue-600" />
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">
                        Total Properties
                    </h3>
                    <p class="text-2xl font-bold text-slate-900 mb-1">
                        {{ props.stats.totalProperties }}
                    </p>
                    <p
                        class="text-slate-600 text-sm"
                    >
                        Listed properties
                    </p>
                </div>
                <div
                    class="text-center p-4 bg-slate-50 rounded-lg border border-slate-200"
                >
                    <div
                        class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3"
                    >
                        <UserGroupIcon class="w-6 h-6 text-green-600" />
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">
                        Active Brokers
                    </h3>
                    <p class="text-2xl font-bold text-slate-900 mb-1">
                        {{ props.stats.totalBrokers }}
                    </p>
                    <p
                        class="text-slate-600 text-sm"
                    >
                        Verified agents
                    </p>
                </div>
                <div
                    class="text-center p-4 bg-slate-50 rounded-lg border border-slate-200"
                >
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-3"
                    >
                        <CreditCardIcon class="w-6 h-6 text-purple-600" />
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">
                        Total Transactions
                    </h3>
                    <p class="text-2xl font-bold text-slate-900 mb-1">
                        {{ props.stats.totalTransactions }}
                    </p>
                    <p
                        class="text-slate-600 text-sm"
                    >
                        Completed deals
                    </p>
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
                        Comprehensive oversight of properties and broker network.
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="route('properties.index')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
                    >
                        <EyeIcon class="w-4 h-4" />
                        View Properties
                    </Link>
                </div>
            </div>
        </div>
        <!-- Recent Activity Feed -->
        <div class="mb-12">
            <div
                class="bg-white rounded-3xl border border-neutral-100 shadow-lg overflow-hidden"
            >
                <div
                    class="p-6 border-b border-neutral-100 bg-gradient-to-r from-slate-50 to-blue-50"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center"
                            >
                                <ChartBarIcon class="w-5 h-5 text-white" />
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">
                                    Recent Activity Feed
                                </h3>
                                <p class="text-sm text-slate-600">
                                    Latest transactions, registrations, and
                                    system notifications
                                </p>
                            </div>
                        </div>
                        <Link
                            :href="route('admin.activity.index')"
                            class="text-blue-600 hover:text-blue-700 font-medium text-sm"
                        >
                            View All Activity →
                        </Link>
                    </div>
                </div>

                <div class="divide-y divide-neutral-100">
                    <div
                        v-for="activity in recentActivity"
                        :key="activity.id"
                        class="p-6 hover:bg-neutral-50 transition-colors"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                :class="[
                                    'w-10 h-10 rounded-2xl flex items-center justify-center text-white font-bold',
                                    activity.color === 'green'
                                        ? 'bg-green-500'
                                        : activity.color === 'blue'
                                        ? 'bg-blue-500'
                                        : activity.color === 'purple'
                                        ? 'bg-purple-500'
                                        : activity.color === 'indigo'
                                        ? 'bg-indigo-500'
                                        : 'bg-gray-500',
                                ]"
                            >
                                {{ activity.icon }}
                            </div>
                            <div class="flex-1">
                                <div
                                    class="flex items-center justify-between mb-1"
                                >
                                    <h4 class="font-semibold text-slate-900">
                                        {{ activity.title }}
                                    </h4>
                                    <span class="text-xs text-slate-500">{{
                                        activity.created_at
                                    }}</span>
                                </div>
                                <p class="text-sm text-slate-600 mb-2">
                                    {{ activity.description }}
                                </p>
                                <div
                                    class="flex items-center gap-4 text-xs text-slate-500"
                                >
                                    <span class="flex items-center gap-1">
                                        <UserIcon class="w-3 h-3" />
                                        {{ activity.user }}
                                    </span>
                                    <span v-if="activity.amount" class="flex items-center gap-1">
                                        <CreditCardIcon class="w-3 h-3" />
                                        ₱{{ formatNumber(activity.amount) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Indicators -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Monthly Targets -->
            <div
                class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-3xl border-2 border-emerald-200 p-8 shadow-lg"
            >
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="w-12 h-12 bg-emerald-500 rounded-lg flex items-center justify-center"
                    >
                        <CheckCircleIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">
                            Monthly Targets
                        </h3>
                        <p class="text-sm text-emerald-600">
                            Progress towards monthly goals
                        </p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-slate-700"
                                >Transactions</span
                            >
                            <span class="text-sm font-bold text-emerald-600">
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
                        <div class="w-full bg-emerald-100 rounded-full h-3">
                            <div
                                class="bg-gradient-to-r from-emerald-500 to-green-600 h-3 rounded-full transition-all duration-300"
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
                            <span class="text-sm font-bold text-emerald-600">
                                ₱{{
                                    formatNumber(
                                        performanceIndicators.monthly_targets
                                            ?.revenue?.current || 0
                                    )
                                }}
                            </span>
                        </div>
                        <div class="w-full bg-emerald-100 rounded-full h-3">
                            <div
                                class="bg-gradient-to-r from-emerald-500 to-green-600 h-3 rounded-full transition-all duration-300"
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
                class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl border-2 border-blue-200 p-8 shadow-lg"
            >
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center"
                    >
                        <ChartBarIcon class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">
                            Conversion Rates
                        </h3>
                        <p class="text-sm text-blue-600">
                            System performance metrics
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div
                        class="text-center p-4 bg-white rounded-2xl border border-blue-100"
                    >
                        <div class="text-2xl font-bold text-blue-600 mb-1">
                            {{
                                Math.round(
                                    performanceIndicators.conversion_rates
                                        ?.inquiry_to_sale || 0
                                )
                            }}%
                        </div>
                        <div class="text-xs text-slate-600 font-medium">
                            Inquiry to Sale
                        </div>
                    </div>
                    <div
                        class="text-center p-4 bg-white rounded-2xl border border-blue-100"
                    >
                        <div class="text-2xl font-bold text-indigo-600 mb-1">
                            {{
                                Math.round(
                                    performanceIndicators.conversion_rates
                                        ?.property_sale_rate || 0
                                )
                            }}%
                        </div>
                        <div class="text-xs text-slate-600 font-medium">
                            Property Sale Rate
                        </div>
                    </div>
                    <div
                        class="text-center p-4 bg-white rounded-2xl border border-blue-100"
                    >
                        <div class="text-2xl font-bold text-purple-600 mb-1">
                            {{
                                Math.round(
                                    performanceIndicators.conversion_rates
                                        ?.broker_approval_rate || 0
                                )
                            }}%
                        </div>
                        <div class="text-xs text-slate-600 font-medium">
                            Broker Approval
                        </div>
                    </div>
                    <div
                        class="text-center p-4 bg-white rounded-2xl border border-blue-100"
                    >
                        <div class="text-2xl font-bold text-green-600 mb-1">
                            {{
                                performanceIndicators.system_performance
                                    ?.uptime || "N/A"
                            }}
                        </div>
                        <div class="text-xs text-slate-600 font-medium">
                            System Uptime
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Shortcut Access Panel -->
        <div
            class="bg-gradient-to-br from-slate-50 to-neutral-50 rounded-3xl border border-neutral-200 p-8 shadow-lg mb-12"
        >
            <div class="flex items-center gap-4 mb-8">
                <div
                    class="w-12 h-12 bg-slate-600 rounded-lg flex items-center justify-center"
                >
                    <PlusIcon class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-900">
                        Quick Access Panel
                    </h3>
                    <p class="text-slate-600">
                        Direct links to most-used admin functions
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <Link
                    :href="route('admin.users.index')"
                    class="flex flex-col items-center p-4 bg-white rounded-2xl border border-neutral-200 hover:border-blue-300 hover:shadow-md transition-all group"
                >
                    <div
                        class="w-12 h-12 bg-blue-100 group-hover:bg-blue-200 rounded-xl flex items-center justify-center mb-3 transition-colors"
                    >
                        <UserGroupIcon class="w-6 h-6 text-blue-600" />
                    </div>
                    <span class="text-sm font-medium text-slate-700 text-center"
                        >User Management</span
                    >
                </Link>

                <Link
                    :href="route('admin.properties.index')"
                    class="flex flex-col items-center p-4 bg-white rounded-2xl border border-neutral-200 hover:border-green-300 hover:shadow-md transition-all group"
                >
                    <div
                        class="w-12 h-12 bg-green-100 group-hover:bg-green-200 rounded-xl flex items-center justify-center mb-3 transition-colors"
                    >
                        <BuildingOfficeIcon class="w-6 h-6 text-green-600" />
                    </div>
                    <span class="text-sm font-medium text-slate-700 text-center"
                        >Properties</span
                    >
                </Link>

                <Link
                    :href="route('admin.transactions.index')"
                    class="flex flex-col items-center p-4 bg-white rounded-2xl border border-neutral-200 hover:border-purple-300 hover:shadow-md transition-all group"
                >
                    <div
                        class="w-12 h-12 bg-purple-100 group-hover:bg-purple-200 rounded-xl flex items-center justify-center mb-3 transition-colors"
                    >
                        <CreditCardIcon class="w-6 h-6 text-purple-600" />
                    </div>
                    <span class="text-sm font-medium text-slate-700 text-center"
                        >Transactions</span
                    >
                </Link>

                <Link
                    :href="route('admin.reports.dashboard')"
                    class="flex flex-col items-center p-4 bg-white rounded-2xl border border-neutral-200 hover:border-orange-300 hover:shadow-md transition-all group"
                >
                    <div
                        class="w-12 h-12 bg-orange-100 group-hover:bg-orange-200 rounded-xl flex items-center justify-center mb-3 transition-colors"
                    >
                        <EyeIcon class="w-6 h-6 text-orange-600" />
                    </div>
                    <span class="text-sm font-medium text-slate-700 text-center"
                        >Reports</span
                    >
                </Link>

                <Link
                    :href="route('admin.compliance.index')"
                    class="flex flex-col items-center p-4 bg-white rounded-2xl border border-neutral-200 hover:border-red-300 hover:shadow-md transition-all group"
                >
                    <div
                        class="w-12 h-12 bg-red-100 group-hover:bg-red-200 rounded-xl flex items-center justify-center mb-3 transition-colors"
                    >
                        <CheckCircleIcon class="w-6 h-6 text-red-600" />
                    </div>
                    <span class="text-sm font-medium text-slate-700 text-center"
                        >Compliance</span
                    >
                </Link>

                <Link
                    :href="route('admin.activity.index')"
                    class="flex flex-col items-center p-4 bg-white rounded-2xl border border-neutral-200 hover:border-indigo-300 hover:shadow-md transition-all group"
                >
                    <div
                        class="w-12 h-12 bg-indigo-100 group-hover:bg-indigo-200 rounded-xl flex items-center justify-center mb-3 transition-colors"
                    >
                        <ClockIcon class="w-6 h-6 text-indigo-600" />
                    </div>
                    <span class="text-sm font-medium text-slate-700 text-center"
                        >Activity Audit</span
                    >
                </Link>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
