<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import UserAvatar from "@/Components/UserAvatar.vue";
import {
    UserGroupIcon,
    ClockIcon,
    BuildingOfficeIcon,
    CreditCardIcon,
    CheckCircleIcon,
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
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200">
                <div class="px-6 py-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center space-x-3">
                                <h1
                                    class="text-2xl font-semibold text-gray-900"
                                >
                                    Admin Command Center
                                </h1>
                                <div class="flex items-center space-x-2">
                                    <div
                                        class="w-2 h-2 bg-green-500 rounded-full animate-pulse"
                                    ></div>
                                    <span
                                        class="text-xs text-green-600 font-medium"
                                        >System Online</span
                                    >
                                </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                System overview and management
                            </p>
                        </div>
                        <div
                            class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3"
                        >
                            <Link
                                :href="route('admin.reports.dashboard')"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <ChartBarIcon class="w-4 h-4 mr-2" />
                                <span class="hidden sm:inline">Analytics</span>
                            </Link>
                            <Link
                                :href="route('admin.activity.index')"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <ClockIcon class="w-4 h-4 mr-2" />
                                <span class="hidden sm:inline"
                                    >Activity Log</span
                                >
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Metrics -->
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6"
            >
                <div
                    class="bg-white border border-gray-200 rounded-lg shadow-sm"
                >
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"
                                >
                                    <UserGroupIcon
                                        class="w-5 h-5 text-blue-600"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">
                                    Total Brokers
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ props.stats.totalBrokers }}
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
                                    <ClockIcon
                                        class="w-5 h-5 text-orange-600"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">
                                    Pending Approvals
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ props.stats.pendingApprovals }}
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
                                    <BuildingOfficeIcon
                                        class="w-5 h-5 text-green-600"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">
                                    Total Properties
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ props.stats.totalProperties }}
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
                                    <CreditCardIcon
                                        class="w-5 h-5 text-purple-600"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">
                                    Total Transactions
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ props.stats.totalTransactions }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Priority Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Pending Approvals -->
                <div
                    class="bg-white border border-gray-200 rounded-lg shadow-sm"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">
                                    Pending Approvals
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Broker applications awaiting review
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800"
                            >
                                {{ props.stats.pendingApprovals }} pending
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div v-if="pendingBrokers.length > 0" class="space-y-4">
                            <div
                                v-for="broker in pendingBrokers.slice(0, 3)"
                                :key="broker.id"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                            >
                                <div class="flex items-center space-x-3">
                                    <UserAvatar
                                        v-if="broker"
                                        :user="broker"
                                        size="sm"
                                        bg-color="orange"
                                    />
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ broker.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Applied {{ broker.applied }}
                                        </p>
                                    </div>
                                </div>
                                <Link
                                    :href="
                                        route('admin.brokers.show', broker.id)
                                    "
                                    class="text-blue-600 hover:text-blue-500 text-sm font-medium"
                                >
                                    Review
                                </Link>
                            </div>
                            <div
                                v-if="pendingBrokers.length > 3"
                                class="text-center"
                            >
                                <Link
                                    :href="route('admin.brokers.index')"
                                    class="text-sm text-blue-600 hover:text-blue-500 font-medium"
                                >
                                    View all {{ pendingBrokers.length }} pending
                                    applications
                                </Link>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <CheckCircleIcon
                                class="w-8 h-8 text-green-500 mx-auto mb-2"
                            />
                            <p class="text-sm text-gray-500">
                                No pending approvals
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Top Performer -->
                <div
                    class="bg-white border border-gray-200 rounded-lg shadow-sm"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">
                                    Top Performer
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Leading broker this month
                                </p>
                            </div>
                            <Link
                                :href="route('leaderboard.index')"
                                class="text-sm text-blue-600 hover:text-blue-500 font-medium"
                            >
                                View Leaderboard
                            </Link>
                        </div>
                    </div>
                    <div class="p-6">
                        <div v-if="topBroker" class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <UserAvatar
                                    v-if="topBroker"
                                    :user="topBroker"
                                    size="md"
                                    bg-color="green"
                                />
                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ topBroker.name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ topBroker.email }}
                                    </p>
                                </div>
                            </div>
                            <div
                                class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200"
                            >
                                <div class="text-center">
                                    <p
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ topBroker.total_sales }}
                                    </p>
                                    <p class="text-xs text-gray-500">Sales</p>
                                </div>
                                <div class="text-center">
                                    <p
                                        class="text-lg font-semibold text-green-600"
                                    >
                                        ₱{{
                                            formatNumber(
                                                topBroker.total_commission
                                            )
                                        }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Sales Value
                                    </p>
                                </div>
                                <div class="text-center">
                                    <p
                                        class="text-lg font-semibold text-blue-600"
                                    >
                                        ₱{{
                                            formatNumber(
                                                topBroker.total_sales_value
                                            )
                                        }}
                                    </p>
                                    <p class="text-xs text-gray-500">Revenue</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <ChartBarIcon
                                class="w-8 h-8 text-gray-400 mx-auto mb-2"
                            />
                            <p class="text-sm text-gray-500">
                                No performance data available
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Performance Metrics
                    </h3>
                    <p class="text-sm text-gray-500">
                        Key performance indicators and trends
                    </p>
                </div>
                <div class="p-6">
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6"
                    >
                        <!-- Conversion Rate -->
                        <div class="text-center">
                            <div
                                class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3"
                            >
                                <ChartBarIcon class="w-8 h-8 text-blue-600" />
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mb-1">
                                {{ props.stats.conversionRate }}%
                            </p>
                            <p class="text-sm text-gray-500">Conversion Rate</p>
                            <p class="text-xs text-gray-400 mt-1">
                                Inquiries to Transactions
                            </p>
                        </div>

                        <!-- Active Brokers -->
                        <div class="text-center">
                            <div
                                class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3"
                            >
                                <UserGroupIcon class="w-8 h-8 text-green-600" />
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mb-1">
                                {{ props.stats.activeBrokers }}
                            </p>
                            <p class="text-sm text-gray-500">Active Brokers</p>
                            <p class="text-xs text-gray-400 mt-1">
                                With Listed Properties
                            </p>
                        </div>

                        <!-- Total Inquiries -->
                        <div class="text-center">
                            <div
                                class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3"
                            >
                                <ClockIcon class="w-8 h-8 text-purple-600" />
                            </div>
                            <p class="text-2xl font-bold text-gray-900 mb-1">
                                {{ props.stats.totalInquiries }}
                            </p>
                            <p class="text-sm text-gray-500">Total Inquiries</p>
                            <p class="text-xs text-gray-400 mt-1">All Time</p>
                        </div>
                    </div>

                    <!-- Growth Trends -->
                    <div
                        v-if="props.stats.monthlyGrowth"
                        class="mt-6 pt-6 border-t border-gray-200"
                    >
                        <h4 class="text-sm font-medium text-gray-900 mb-4">
                            Monthly Growth
                        </h4>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <p class="text-lg font-semibold text-gray-900">
                                    {{
                                        props.stats.monthlyGrowth.brokers > 0
                                            ? "+"
                                            : ""
                                    }}{{ props.stats.monthlyGrowth.brokers }}%
                                </p>
                                <p class="text-xs text-gray-500">Brokers</p>
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-semibold text-gray-900">
                                    {{
                                        props.stats.monthlyGrowth.properties > 0
                                            ? "+"
                                            : ""
                                    }}{{
                                        props.stats.monthlyGrowth.properties
                                    }}%
                                </p>
                                <p class="text-xs text-gray-500">Properties</p>
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-semibold text-gray-900">
                                    {{
                                        props.stats.monthlyGrowth.transactions >
                                        0
                                            ? "+"
                                            : ""
                                    }}{{
                                        props.stats.monthlyGrowth.transactions
                                    }}%
                                </p>
                                <p class="text-xs text-gray-500">
                                    Transactions
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Quick Actions
                    </h3>
                    <p class="text-sm text-gray-500">
                        Common administrative tasks
                    </p>
                </div>
                <div class="p-6">
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4"
                    >
                        <Link
                            :href="route('admin.users.index')"
                            class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center"
                            >
                                <UserGroupIcon class="w-4 h-4 text-blue-600" />
                            </div>
                            <span class="text-sm font-medium text-gray-700">
                                Users
                            </span>
                        </Link>
                        <Link
                            :href="route('admin.properties.index')"
                            class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center"
                            >
                                <BuildingOfficeIcon
                                    class="w-4 h-4 text-green-600"
                                />
                            </div>
                            <span class="text-sm font-medium text-gray-700">
                                Properties
                            </span>
                        </Link>
                        <Link
                            :href="route('admin.transactions.index')"
                            class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center"
                            >
                                <CreditCardIcon
                                    class="w-4 h-4 text-purple-600"
                                />
                            </div>
                            <span class="text-sm font-medium text-gray-700">
                                Transactions
                            </span>
                        </Link>
                        <Link
                            :href="route('admin.brokers.index')"
                            class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors"
                        >
                            <div
                                class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center"
                            >
                                <UserGroupIcon
                                    class="w-4 h-4 text-orange-600"
                                />
                            </div>
                            <span class="text-sm font-medium text-gray-700">
                                Brokers
                            </span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
