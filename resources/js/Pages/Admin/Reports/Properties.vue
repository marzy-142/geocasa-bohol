<template>
    <ModernDashboardLayout>
        <Head title="Property Reports" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                Property Reports
                            </h1>
                            <p class="mt-2 text-gray-600">
                                Detailed analytics and performance metrics for
                                properties
                            </p>
                        </div>

                        <div class="flex items-center space-x-4">
                            <Link
                                :href="route('admin.reports.dashboard')"
                                class="text-indigo-600 hover:text-indigo-900"
                            >
                                ← Back to Dashboard
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
                >
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
                                            class="text-2xl font-semibold text-gray-900"
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
                                            Active Listings
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
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
                                        class="h-6 w-6 text-yellow-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Avg. Price
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            ₱{{ formatPrice(stats.avg_price) }}
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
                                    <EyeIcon class="h-6 w-6 text-purple-400" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Total Views
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.total_views }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Property Type Distribution -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Property Type Distribution
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div
                                    v-for="(
                                        count, type
                                    ) in stats.property_types"
                                    :key="type"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center">
                                        <div
                                            class="w-4 h-4 rounded-full mr-3"
                                            :class="getTypeColor(type)"
                                        ></div>
                                        <span
                                            class="text-sm font-medium text-gray-700 capitalize"
                                            >{{ type }}</span
                                        >
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-900">{{
                                            count
                                        }}</span>
                                        <div
                                            class="w-20 bg-gray-200 rounded-full h-2"
                                        >
                                            <div
                                                class="h-2 rounded-full"
                                                :class="getTypeColor(type)"
                                                :style="{
                                                    width:
                                                        (count /
                                                            stats.total_properties) *
                                                            100 +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Range Distribution -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Price Range Distribution
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div
                                    v-for="(range, index) in stats.price_ranges"
                                    :key="index"
                                    class="flex items-center justify-between"
                                >
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                        >{{ range.label }}</span
                                    >
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-900">{{
                                            range.count
                                        }}</span>
                                        <div
                                            class="w-20 bg-gray-200 rounded-full h-2"
                                        >
                                            <div
                                                class="bg-indigo-600 h-2 rounded-full"
                                                :style="{
                                                    width:
                                                        (range.count /
                                                            stats.total_properties) *
                                                            100 +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Listing Trends -->
                <div class="bg-white shadow rounded-lg mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Listing Trends
                        </h3>
                    </div>
                    <div class="p-6">
                        <div
                            class="h-64 flex items-center justify-center bg-gray-50 rounded-lg"
                        >
                            <div class="text-center">
                                <ChartBarIcon
                                    class="mx-auto h-12 w-12 text-gray-400"
                                />
                                <h3
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    Listing Trends Chart
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Chart visualization would be implemented
                                    here
                                </p>
                                <div
                                    class="mt-4 space-y-2 text-xs text-gray-600"
                                >
                                    <div
                                        v-for="(
                                            count, date
                                        ) in chartData.listings"
                                        :key="date"
                                        class="flex justify-between"
                                    >
                                        <span>{{ formatDate(date) }}:</span>
                                        <span class="font-medium"
                                            >{{ count }} new listings</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performing Properties -->
                <div class="bg-white shadow rounded-lg mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Most Inquired Properties
                        </h3>
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
                                        Type
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
                                        Views
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Broker
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Listed
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="property in topPerformers"
                                    :key="property.id"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10"
                                            >
                                                <div
                                                    class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center"
                                                >
                                                    <HomeIcon
                                                        class="h-5 w-5 text-gray-500"
                                                    />
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div
                                                    class="text-sm font-medium text-gray-900"
                                                >
                                                    {{ property.title }}
                                                </div>
                                                <div
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{ property.location }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="getTypeClass(property.type)"
                                        >
                                            {{ property.type }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        ₱{{ formatPrice(property.price) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        {{ property.inquiries_count }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        {{ property.views_count || 0 }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ property.broker?.name || "N/A" }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ formatDate(property.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Recent Property Activities
                        </h3>
                    </div>
                    <div class="p-6">
                        <div
                            v-if="recentActivities.length === 0"
                            class="text-center py-8"
                        >
                            <ClockIcon
                                class="mx-auto h-12 w-12 text-gray-400"
                            />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                No recent activities
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Property activities will appear here as they
                                occur.
                            </p>
                        </div>
                        <div v-else class="flow-root">
                            <ul class="-mb-8">
                                <li
                                    v-for="(
                                        activity, index
                                    ) in recentActivities"
                                    :key="index"
                                >
                                    <div class="relative pb-8">
                                        <span
                                            v-if="
                                                index !==
                                                recentActivities.length - 1
                                            "
                                            class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                            aria-hidden="true"
                                        ></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span
                                                    class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                                    :class="
                                                        getActivityIconClass(
                                                            activity.type
                                                        )
                                                    "
                                                >
                                                    <component
                                                        :is="
                                                            getActivityIcon(
                                                                activity.type
                                                            )
                                                        "
                                                        class="h-4 w-4 text-white"
                                                    />
                                                </span>
                                            </div>
                                            <div
                                                class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4"
                                            >
                                                <div>
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        {{
                                                            activity.description
                                                        }}
                                                    </p>
                                                </div>
                                                <div
                                                    class="text-right text-sm whitespace-nowrap text-gray-500"
                                                >
                                                    <time
                                                        :datetime="
                                                            activity.created_at
                                                        "
                                                        >{{
                                                            formatDate(
                                                                activity.created_at
                                                            )
                                                        }}</time
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    HomeIcon,
    CheckCircleIcon,
    CurrencyDollarIcon,
    EyeIcon,
    ChartBarIcon,
    ClockIcon,
    PlusIcon,
    PencilIcon,
    ChatBubbleLeftIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    stats: Object,
    chartData: Object,
    topPerformers: Array,
    recentActivities: Array,
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH").format(price);
};

const getTypeColor = (type) => {
    const colors = {
        house: "bg-blue-500",
        condo: "bg-green-500",
        lot: "bg-yellow-500",
        commercial: "bg-purple-500",
        apartment: "bg-red-500",
    };
    return colors[type] || "bg-gray-500";
};

const getTypeClass = (type) => {
    const classes = {
        house: "bg-blue-100 text-blue-800",
        condo: "bg-green-100 text-green-800",
        lot: "bg-yellow-100 text-yellow-800",
        commercial: "bg-purple-100 text-purple-800",
        apartment: "bg-red-100 text-red-800",
    };
    return classes[type] || "bg-gray-100 text-gray-800";
};

const getActivityIcon = (type) => {
    const icons = {
        listing: PlusIcon,
        update: PencilIcon,
        inquiry: ChatBubbleLeftIcon,
    };
    return icons[type] || ClockIcon;
};

const getActivityIconClass = (type) => {
    const classes = {
        listing: "bg-green-500",
        update: "bg-blue-500",
        inquiry: "bg-purple-500",
    };
    return classes[type] || "bg-gray-500";
};
</script>
