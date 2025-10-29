<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('admin.analytics.brokers.index')"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <ArrowLeftIcon class="h-6 w-6" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Broker Analytics
                        </h1>
                        <p class="text-gray-600">
                            {{ broker.name }} - Performance Details
                        </p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button
                        @click="exportBrokerAnalytics"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                        Export Analytics
                    </button>
                    <button
                        @click="refreshData"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <ArrowPathIcon class="w-4 h-4 mr-2" />
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Broker Profile Card -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-16 w-16">
                            <div
                                class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center"
                            >
                                <UserIcon class="h-8 w-8 text-gray-500" />
                            </div>
                        </div>
                        <div class="ml-6">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ broker.name }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ broker.email }}
                            </p>
                            <div class="mt-2 flex space-x-4">
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
                                >
                                    Broker
                                </span>
                                <span
                                    :class="
                                        broker.suspended_at
                                            ? 'bg-red-100 text-red-800'
                                            : 'bg-green-100 text-green-800'
                                    "
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{
                                        broker.suspended_at
                                            ? "Suspended"
                                            : "Active"
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <HomeIcon class="h-6 w-6 text-blue-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Properties Listed
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{
                                            performanceMetrics.properties_listed
                                        }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <CheckCircleIcon
                                    class="h-6 w-6 text-green-400"
                                />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Properties Sold
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ performanceMetrics.properties_sold }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <CurrencyDollarIcon
                                    class="h-6 w-6 text-purple-400"
                                />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Total Sales Value
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        ₱{{
                                            formatNumber(
                                                performanceMetrics.total_commission
                                            )
                                        }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <ChartBarIcon class="h-6 w-6 text-orange-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Conversion Rate
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{
                                            performanceMetrics.conversion_rate
                                        }}%
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Performance Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <ClockIcon class="h-6 w-6 text-blue-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Avg Response Time
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{
                                            performanceMetrics.avg_response_time
                                        }}h
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <StarIcon class="h-6 w-6 text-yellow-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Client Satisfaction
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{
                                            performanceMetrics.client_satisfaction
                                        }}/5
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <ArrowTrendingUpIcon
                                    class="h-6 w-6 text-green-400"
                                />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Performance Score
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ calculatePerformanceScore() }}/100
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Property Performance -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Property Performance
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600"
                                >Total Properties</span
                            >
                            <span class="text-lg font-semibold">{{
                                propertyAnalytics.total
                            }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600"
                                >Average Price</span
                            >
                            <span class="text-lg font-semibold"
                                >₱{{
                                    formatNumber(propertyAnalytics.avg_price)
                                }}</span
                            >
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600"
                                >Total Value</span
                            >
                            <span class="text-lg font-semibold"
                                >₱{{
                                    formatNumber(propertyAnalytics.total_value)
                                }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Commission Analytics -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Sales Value Analytics
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600"
                                >Total Sales Value</span
                            >
                            <span class="text-lg font-semibold"
                                >₱{{
                                    formatNumber(commissionAnalytics.total)
                                }}</span
                            >
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600"
                                >Avg per Transaction</span
                            >
                            <span class="text-lg font-semibold"
                                >₱{{
                                    formatNumber(
                                        commissionAnalytics.avg_per_transaction
                                    )
                                }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Type Distribution -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Properties by Type
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div
                        v-for="(count, type) in propertyAnalytics.by_type"
                        :key="type"
                        class="text-center p-4 bg-gray-50 rounded-lg"
                    >
                        <div class="text-2xl font-bold text-gray-900">
                            {{ count }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ formatPropertyType(type) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Analytics -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Client Analytics
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">
                            {{ clientAnalytics.total }}
                        </div>
                        <div class="text-sm text-gray-500">Total Clients</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">
                            {{ clientAnalytics.new_clients }}
                        </div>
                        <div class="text-sm text-gray-500">New Clients</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">
                            {{ clientAnalytics.active_clients }}
                        </div>
                        <div class="text-sm text-gray-500">Active Clients</div>
                    </div>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Recent Activity
                </h3>
                <div class="flow-root">
                    <ul class="-mb-8">
                        <li
                            v-for="(activity, index) in activityTimeline"
                            :key="index"
                            class="relative pb-8"
                        >
                            <div
                                v-if="index !== activityTimeline.length - 1"
                                class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                            ></div>
                            <div class="relative flex space-x-3">
                                <div>
                                    <span
                                        :class="
                                            getActivityIconClass(activity.type)
                                        "
                                        class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                    >
                                        <component
                                            :is="getActivityIcon(activity.type)"
                                            class="h-4 w-4"
                                        />
                                    </span>
                                </div>
                                <div
                                    class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4"
                                >
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            {{ activity.description }}
                                        </p>
                                    </div>
                                    <div
                                        class="text-right text-sm whitespace-nowrap text-gray-500"
                                    >
                                        <time>
                                            {{ formatDateTime(activity.date) }}
                                        </time>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    ArrowLeftIcon,
    UserIcon,
    HomeIcon,
    CheckCircleIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    ClockIcon,
    StarIcon,
    ArrowTrendingUpIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
    HomeIcon as HomeIconSolid,
    CurrencyDollarIcon as CurrencyIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    broker: Object,
    performanceMetrics: Object,
    propertyAnalytics: Object,
    clientAnalytics: Object,
    commissionAnalytics: Object,
    activityTimeline: Array,
    filters: Object,
});

// Methods
const exportBrokerAnalytics = () => {
    const params = new URLSearchParams(props.filters);
    window.open(
        route("admin.analytics.brokers.export", props.broker.id) +
            "?" +
            params.toString()
    );
};

const refreshData = () => {
    router.reload();
};

const calculatePerformanceScore = () => {
    const metrics = props.performanceMetrics;
    let score = 0;

    // Properties listed (max 30 points)
    score += Math.min(30, (metrics.properties_listed || 0) * 2);

    // Properties sold (max 25 points)
    score += Math.min(25, (metrics.properties_sold || 0) * 5);

    // Commission earned (max 25 points)
    score += Math.min(25, ((metrics.total_commission || 0) / 10000) * 2);

    // Conversion rate (max 20 points)
    score += Math.min(20, (metrics.conversion_rate || 0) * 1.2);

    return Math.round(score);
};

const getActivityIconClass = (type) => {
    const classes = {
        property_listed: "bg-green-100 text-green-600",
        transaction_completed: "bg-blue-100 text-blue-600",
    };
    return classes[type] || "bg-gray-100 text-gray-600";
};

const getActivityIcon = (type) => {
    const icons = {
        property_listed: HomeIconSolid,
        transaction_completed: CurrencyIcon,
    };
    return icons[type] || HomeIcon;
};

const formatPropertyType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatNumber = (number) => {
    return new Intl.NumberFormat("en-US").format(number || 0);
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>
