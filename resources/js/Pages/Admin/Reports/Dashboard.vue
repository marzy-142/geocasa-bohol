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
                                @click="toggleCustomization"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <Cog6ToothIcon class="w-4 h-4 mr-2" />
                                Customize
                            </button>
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

            <!-- Dashboard Customization Panel -->
            <div
                v-if="showCustomization"
                class="bg-white border border-gray-200 rounded-lg shadow-sm"
            >
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                Customize Dashboard
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Personalize your analytics experience
                            </p>
                        </div>
                        <button
                            @click="toggleCustomization"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Widget Selection -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">
                                Available Widgets
                            </h4>
                            <div class="space-y-2">
                                <label
                                    v-for="widget in availableWidgets"
                                    :key="widget.id"
                                    class="flex items-center"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="selectedWidgets"
                                        :value="widget.id"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">
                                        {{ widget.name }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Time Period Selection -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">
                                Time Period
                            </h4>
                            <select
                                v-model="selectedTimePeriod"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            >
                                <option value="7d">Last 7 Days</option>
                                <option value="30d">Last 30 Days</option>
                                <option value="90d">Last 90 Days</option>
                                <option value="1y">Last Year</option>
                            </select>
                        </div>

                        <!-- Layout Options -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">
                                Layout Density
                            </h4>
                            <select
                                v-model="layoutDensity"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            >
                                <option value="compact">Compact</option>
                                <option value="normal">Normal</option>
                                <option value="spacious">Spacious</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            @click="resetCustomization"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Reset to Default
                        </button>
                        <button
                            @click="applyCustomization"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Apply Changes
                        </button>
                    </div>
                </div>
            </div>

            <!-- Analytics Navigation -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Analytics Sections
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Choose your analytics focus area
                    </p>
                </div>
                <div class="p-6">
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4"
                    >
                        <Link
                            :href="route('admin.analytics.brokers.index')"
                            class="group p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"
                                    >
                                        <UserGroupIcon
                                            class="w-5 h-5 text-blue-600"
                                        />
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4
                                        class="text-sm font-medium text-gray-900 group-hover:text-blue-900"
                                    >
                                        Broker Analytics
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Performance metrics
                                    </p>
                                </div>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.reports.brokers')"
                            class="group p-4 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition-colors"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center"
                                    >
                                        <ChartBarIcon
                                            class="w-5 h-5 text-green-600"
                                        />
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4
                                        class="text-sm font-medium text-gray-900 group-hover:text-green-900"
                                    >
                                        Broker Reports
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Detailed reports
                                    </p>
                                </div>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.reports.properties')"
                            class="group p-4 border border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition-colors"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center"
                                    >
                                        <HomeIcon
                                            class="w-5 h-5 text-purple-600"
                                        />
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4
                                        class="text-sm font-medium text-gray-900 group-hover:text-purple-900"
                                    >
                                        Property Reports
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Property analytics
                                    </p>
                                </div>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.reports.compliance')"
                            class="group p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center"
                                    >
                                        <ExclamationTriangleIcon
                                            class="w-5 h-5 text-orange-600"
                                        />
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4
                                        class="text-sm font-medium text-gray-900 group-hover:text-orange-900"
                                    >
                                        Compliance
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Audit reports
                                    </p>
                                </div>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.activity.index')"
                            class="group p-4 border border-gray-200 rounded-lg hover:border-gray-400 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center"
                                    >
                                        <ClockIcon
                                            class="w-5 h-5 text-gray-600"
                                        />
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4
                                        class="text-sm font-medium text-gray-900 group-hover:text-gray-900"
                                    >
                                        Activity Audit
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        System logs
                                    </p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Key Metrics Cards -->
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
                                    Total Brokers
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ stats.total_brokers }}
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
                                    <HomeIcon class="w-5 h-5 text-green-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">
                                    Active Properties
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ stats.active_properties }}
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
                                    <ChatBubbleLeftRightIcon
                                        class="w-5 h-5 text-purple-600"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">
                                    Total Inquiries
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ stats.total_inquiries }}
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
                                    <ExclamationTriangleIcon
                                        class="w-5 h-5 text-orange-600"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">
                                    Compliance Reports
                                </p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ stats.total_compliance_reports }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Activity Trends Chart -->
                <div
                    class="bg-white border border-gray-200 rounded-lg shadow-sm"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Activity Trends
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Monthly performance overview
                        </p>
                    </div>
                    <div class="p-6">
                        <div class="h-64">
                            <AnalyticsChart
                                type="line"
                                :data="activityChartData"
                                :options="activityChartOptions"
                            />
                        </div>
                    </div>
                </div>

                <!-- Broker Performance Chart -->
                <div
                    class="bg-white border border-gray-200 rounded-lg shadow-sm"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Broker Performance
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Properties listed by broker
                        </p>
                    </div>
                    <div class="p-6">
                        <div class="h-64">
                            <AnalyticsChart
                                type="bar"
                                :data="brokerPerformanceData"
                                :options="brokerPerformanceOptions"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performers Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Brokers -->
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Top Performing Brokers
                        </h3>
                        <Link
                            :href="route('admin.reports.brokers')"
                            class="text-sm text-blue-600 hover:text-blue-500"
                        >
                            View All
                        </Link>
                    </div>
                    <div class="space-y-3">
                        <div
                            v-for="(broker, index) in topBrokers"
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
                                        >
                                            {{ index + 1 }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ broker.name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ broker.properties_count }} properties
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ broker.inquiries_count }} inquiries
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Most Inquired Properties -->
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Most Inquired Properties
                        </h3>
                        <Link
                            :href="route('admin.reports.properties')"
                            class="text-sm text-blue-600 hover:text-blue-500"
                        >
                            View All
                        </Link>
                    </div>
                    <div class="space-y-3">
                        <div
                            v-for="(property, index) in topProperties"
                            :key="property.id"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div
                                        class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center"
                                    >
                                        <span
                                            class="text-sm font-medium text-green-600"
                                        >
                                            {{ index + 1 }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ property.title }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ property.type }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ property.inquiries_count }} inquiries
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white shadow rounded-lg p-6">
                <details class="group" open>
                    <summary
                        class="flex items-center justify-between cursor-pointer list-none"
                    >
                        <h3 class="text-lg font-medium text-gray-900">
                            Recent Activities
                        </h3>
                        <div class="flex items-center gap-4">
                            <Link
                                :href="route('admin.activity.index')"
                                class="text-sm text-blue-600 hover:text-blue-500"
                            >
                                View All
                            </Link>
                            <svg
                                class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                ></path>
                            </svg>
                        </div>
                    </summary>
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li
                                v-for="(activity, index) in recentActivities"
                                :key="index"
                                class="relative pb-8"
                            >
                                <div
                                    v-if="index !== recentActivities.length - 1"
                                    class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                ></div>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span
                                            :class="
                                                getActivityIconClass(
                                                    activity.type
                                                )
                                            "
                                            class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                        >
                                            <component
                                                :is="
                                                    getActivityIcon(
                                                        activity.type
                                                    )
                                                "
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
                                                {{
                                                    formatDateTime(
                                                        activity.created_at
                                                    )
                                                }}
                                            </time>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </details>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <details class="group">
                    <summary
                        class="flex items-center justify-between cursor-pointer list-none mb-4"
                    >
                        <h3 class="text-lg font-medium text-gray-900">
                            Quick Actions
                        </h3>
                        <svg
                            class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            ></path>
                        </svg>
                    </summary>
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                    >
                        <Link
                            :href="route('admin.reports.brokers')"
                            class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                        >
                            <UsersIcon class="h-6 w-6 text-blue-500 mr-3" />
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    Broker Reports
                                </div>
                                <div class="text-sm text-gray-500">
                                    Performance analytics
                                </div>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.reports.properties')"
                            class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                        >
                            <HomeIcon class="h-6 w-6 text-green-500 mr-3" />
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    Property Reports
                                </div>
                                <div class="text-sm text-gray-500">
                                    Listing analytics
                                </div>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.reports.compliance')"
                            class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                        >
                            <ExclamationTriangleIcon
                                class="h-6 w-6 text-orange-500 mr-3"
                            />
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    Compliance Reports
                                </div>
                                <div class="text-sm text-gray-500">
                                    Investigation analytics
                                </div>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.activity.index')"
                            class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                        >
                            <ClockIcon class="h-6 w-6 text-purple-500 mr-3" />
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    Activity Audit
                                </div>
                                <div class="text-sm text-gray-500">
                                    System activity logs
                                </div>
                            </div>
                        </Link>
                    </div>
                </details>
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
    HomeIcon,
    ChatBubbleLeftRightIcon,
    ExclamationTriangleIcon,
    ChartBarIcon,
    ChartPieIcon,
    ClockIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
    UserPlusIcon,
    HomeIcon as HomeIconSolid,
    ChatBubbleLeftRightIcon as ChatIcon,
    Cog6ToothIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    stats: Object,
    chartData: Object,
    recentActivities: Array,
    topBrokers: Array,
    topProperties: Array,
});

// Methods
const exportReport = () => {
    // Implementation for exporting reports
    alert("Export functionality would be implemented here");
};

const refreshData = () => {
    router.reload();
};

const getActivityIconClass = (type) => {
    const classes = {
        broker_registration: "bg-blue-100 text-blue-600",
        property_listing: "bg-green-100 text-green-600",
        inquiry: "bg-purple-100 text-purple-600",
    };
    return classes[type] || "bg-gray-100 text-gray-600";
};

const getActivityIcon = (type) => {
    const icons = {
        broker_registration: UserPlusIcon,
        property_listing: HomeIconSolid,
        inquiry: ChatIcon,
    };
    return icons[type] || ClockIcon;
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

// Dashboard customization
const showCustomization = ref(false);
const selectedTimePeriod = ref("30d");
const layoutDensity = ref("normal");
const selectedWidgets = ref([
    "key-metrics",
    "charts",
    "activities",
    "quick-actions",
]);

const availableWidgets = [
    { id: "key-metrics", name: "Key Metrics Cards" },
    { id: "charts", name: "Activity Charts" },
    { id: "activities", name: "Recent Activities" },
    { id: "quick-actions", name: "Quick Actions" },
    { id: "top-performers", name: "Top Performers" },
];

const toggleCustomization = () => {
    showCustomization.value = !showCustomization.value;
};

const applyCustomization = () => {
    // Apply customization settings
    console.log("Applying customization:", {
        timePeriod: selectedTimePeriod.value,
        layoutDensity: layoutDensity.value,
        selectedWidgets: selectedWidgets.value,
    });

    // Hide customization panel
    showCustomization.value = false;

    // Here you would typically save preferences to localStorage or send to backend
    localStorage.setItem(
        "dashboard-customization",
        JSON.stringify({
            timePeriod: selectedTimePeriod.value,
            layoutDensity: layoutDensity.value,
            selectedWidgets: selectedWidgets.value,
        })
    );
};

const resetCustomization = () => {
    selectedTimePeriod.value = "30d";
    layoutDensity.value = "normal";
    selectedWidgets.value = [
        "key-metrics",
        "charts",
        "activities",
        "quick-actions",
    ];
};

// Chart data and options
const activityChartData = computed(() => ({
    labels: props.chartData?.labels || [],
    datasets: [
        {
            label: "New Brokers",
            data: props.chartData?.brokers || [],
            borderColor: "rgb(59, 130, 246)",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            tension: 0.4,
            fill: true,
        },
        {
            label: "New Properties",
            data: props.chartData?.properties || [],
            borderColor: "rgb(16, 185, 129)",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            tension: 0.4,
            fill: true,
        },
        {
            label: "New Inquiries",
            data: props.chartData?.inquiries || [],
            borderColor: "rgb(168, 85, 247)",
            backgroundColor: "rgba(168, 85, 247, 0.1)",
            tension: 0.4,
            fill: true,
        },
        {
            label: "Transactions",
            data: props.chartData?.transactions || [],
            borderColor: "rgb(245, 158, 11)",
            backgroundColor: "rgba(245, 158, 11, 0.1)",
            tension: 0.4,
            fill: true,
        },
    ],
}));

const activityChartOptions = {
    plugins: {
        legend: {
            position: "top",
        },
        title: {
            display: false,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
    interaction: {
        intersect: false,
        mode: "index",
    },
};

const brokerPerformanceData = computed(() => {
    // Get top 5 brokers by property count
    const topBrokers = props.topBrokers?.slice(0, 5) || [];

    return {
        labels: topBrokers.map((broker) => broker.name),
        datasets: [
            {
                label: "Properties Listed",
                data: topBrokers.map((broker) => broker.properties_count || 0),
                backgroundColor: [
                    "rgba(59, 130, 246, 0.8)",
                    "rgba(16, 185, 129, 0.8)",
                    "rgba(245, 158, 11, 0.8)",
                    "rgba(168, 85, 247, 0.8)",
                    "rgba(239, 68, 68, 0.8)",
                ],
                borderColor: [
                    "rgb(59, 130, 246)",
                    "rgb(16, 185, 129)",
                    "rgb(245, 158, 11)",
                    "rgb(168, 85, 247)",
                    "rgb(239, 68, 68)",
                ],
                borderWidth: 1,
            },
        ],
    };
});

const brokerPerformanceOptions = {
    plugins: {
        legend: {
            display: false,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
        },
    },
};
</script>
