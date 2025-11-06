<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Property Reports
                    </h1>
                    <p class="text-gray-600">
                        Property listing analytics and insights
                    </p>
                </div>
            </div>

            <!-- Property Statistics -->
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
                                        Total Properties
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.total_properties }}
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
                                        Active Properties
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.active_properties }}
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
                                        Average Price
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        ₱{{ formatNumber(stats.avg_price) }}
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
                                <EyeIcon class="h-6 w-6 text-orange-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Total Views
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ formatNumber(stats.total_views) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Property Types Distribution -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Property Types Distribution
                    </h3>
                    <div class="h-64 flex items-center justify-center">
                        <div class="text-center">
                            <ChartPieIcon
                                class="h-12 w-12 text-gray-400 mx-auto mb-2"
                            />
                            <p class="text-gray-500">
                                Property types chart would go here
                            </p>
                            <p class="text-sm text-gray-400">
                                Integration with Chart.js or similar library
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Price Range Distribution -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Price Range Distribution
                    </h3>
                    <div class="h-64 flex items-center justify-center">
                        <div class="text-center">
                            <ChartBarIcon
                                class="h-12 w-12 text-gray-400 mx-auto mb-2"
                            />
                            <p class="text-gray-500">
                                Price ranges chart would go here
                            </p>
                            <p class="text-sm text-gray-400">
                                Integration with Chart.js or similar library
                            </p>
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
                    <div class="flex space-x-2">
                        <select
                            v-model="inquiryFilter"
                            @change="filterProperties"
                            class="text-sm border-gray-300 rounded-md"
                        >
                            <option value="all">All Time</option>
                            <option value="month">This Month</option>
                            <option value="week">This Week</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Rank
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Property
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Type
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Location
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
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="(property, index) in topProperties"
                                :key="property.id"
                                class="hover:bg-gray-50"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                >
                                    <div class="flex items-center">
                                        <div
                                            :class="
                                                getRankBadgeClass(index + 1)
                                            "
                                            class="h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold"
                                        >
                                            {{ index + 1 }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <img
                                                v-if="property.main_image"
                                                :src="property.main_image"
                                                :alt="property.title"
                                                class="h-12 w-12 rounded-lg object-cover"
                                            />
                                            <div
                                                v-else
                                                class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center"
                                            >
                                                <HomeIcon
                                                    class="h-6 w-6 text-gray-400"
                                                />
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ property.title }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{
                                                    property.broker?.name ||
                                                    "No broker"
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
                                    >
                                        {{ formatPropertyType(property.type) }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ property.city }}, {{ property.province }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    ₱{{ formatNumber(property.price) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{ property.inquiries_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="
                                            getStatusBadgeClass(property.status)
                                        "
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ formatStatus(property.status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Property Activities -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Recent Property Activities
                </h3>
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
                                            {{
                                                formatDateTime(
                                                    activity.timestamp
                                                )
                                            }}
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
    HomeIcon,
    CheckCircleIcon,
    CurrencyDollarIcon,
    EyeIcon,
    ChartPieIcon,
    ChartBarIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
    HomeIcon as HomeIconSolid,
    PencilIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    stats: Object,
    chartData: Object,
    topProperties: Array,
    recentActivities: Array,
});

// Reactive data
const inquiryFilter = ref("all");

// Methods
const filterProperties = () => {
    console.log("Filtering properties by:", inquiryFilter.value);
};

const getRankBadgeClass = (rank) => {
    if (rank === 1) return "bg-yellow-100 text-yellow-800";
    if (rank === 2) return "bg-gray-100 text-gray-800";
    if (rank === 3) return "bg-orange-100 text-orange-800";
    return "bg-blue-100 text-blue-800";
};

const getStatusBadgeClass = (status) => {
    const classes = {
        active: "bg-green-100 text-green-800",
        pending: "bg-yellow-100 text-yellow-800",
        sold: "bg-blue-100 text-blue-800",
        inactive: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatPropertyType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatNumber = (number) => {
    return new Intl.NumberFormat("en-US").format(number || 0);
};

const getActivityIconClass = (type) => {
    const classes = {
        property_listing: "bg-green-100 text-green-600",
        property_update: "bg-blue-100 text-blue-600",
    };
    return classes[type] || "bg-gray-100 text-gray-600";
};

const getActivityIcon = (type) => {
    const icons = {
        property_listing: HomeIconSolid,
        property_update: PencilIcon,
    };
    return icons[type] || HomeIcon;
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
