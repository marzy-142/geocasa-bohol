<template>
    <ModernDashboardLayout>
        <Head title="Inquiry Reports" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                Inquiry Reports
                            </h1>
                            <p class="mt-2 text-gray-600">
                                Detailed analytics and response metrics for
                                inquiries
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
                                    <ChatBubbleLeftRightIcon
                                        class="h-6 w-6 text-blue-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Total Inquiries
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.total_inquiries }}
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
                                    <ClockIcon
                                        class="h-6 w-6 text-yellow-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Pending
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.pending_inquiries }}
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
                                            Responded
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.responded_inquiries }}
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
                                    <ChartBarIcon
                                        class="h-6 w-6 text-purple-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Response Rate
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.response_rate }}%
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Inquiry Status Distribution -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Status Distribution
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div
                                    v-for="(
                                        count, status
                                    ) in stats.status_distribution"
                                    :key="status"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center">
                                        <div
                                            class="w-4 h-4 rounded-full mr-3"
                                            :class="getStatusColor(status)"
                                        ></div>
                                        <span
                                            class="text-sm font-medium text-gray-700 capitalize"
                                            >{{ status }}</span
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
                                                :class="getStatusColor(status)"
                                                :style="{
                                                    width:
                                                        (count /
                                                            stats.total_inquiries) *
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

                    <!-- Response Time Analysis -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Response Time Analysis
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                        >< 1 hour</span
                                    >
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-900">{{
                                            stats.response_times.under_1h
                                        }}</span>
                                        <div
                                            class="w-20 bg-gray-200 rounded-full h-2"
                                        >
                                            <div
                                                class="bg-green-600 h-2 rounded-full"
                                                :style="{
                                                    width:
                                                        (stats.response_times
                                                            .under_1h /
                                                            stats.responded_inquiries) *
                                                            100 +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                        >1-24 hours</span
                                    >
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-900">{{
                                            stats.response_times.under_24h
                                        }}</span>
                                        <div
                                            class="w-20 bg-gray-200 rounded-full h-2"
                                        >
                                            <div
                                                class="bg-yellow-500 h-2 rounded-full"
                                                :style="{
                                                    width:
                                                        (stats.response_times
                                                            .under_24h /
                                                            stats.responded_inquiries) *
                                                            100 +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                        >1-7 days</span
                                    >
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-900">{{
                                            stats.response_times.under_7d
                                        }}</span>
                                        <div
                                            class="w-20 bg-gray-200 rounded-full h-2"
                                        >
                                            <div
                                                class="bg-orange-500 h-2 rounded-full"
                                                :style="{
                                                    width:
                                                        (stats.response_times
                                                            .under_7d /
                                                            stats.responded_inquiries) *
                                                            100 +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                        >> 7 days</span
                                    >
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-900">{{
                                            stats.response_times.over_7d
                                        }}</span>
                                        <div
                                            class="w-20 bg-gray-200 rounded-full h-2"
                                        >
                                            <div
                                                class="bg-red-500 h-2 rounded-full"
                                                :style="{
                                                    width:
                                                        (stats.response_times
                                                            .over_7d /
                                                            stats.responded_inquiries) *
                                                            100 +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500"
                                        >Average Response Time:</span
                                    >
                                    <span class="font-medium text-gray-900">{{
                                        stats.avg_response_time
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inquiry Trends -->
                <div class="bg-white shadow rounded-lg mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Inquiry Trends
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
                                    Inquiry Trends Chart
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
                                        ) in chartData.inquiries"
                                        :key="date"
                                        class="flex justify-between"
                                    >
                                        <span>{{ formatDate(date) }}:</span>
                                        <span class="font-medium"
                                            >{{ count }} inquiries</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Inquired Properties -->
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
                                        Broker
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Total Inquiries
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Pending
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Responded
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Response Rate
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="property in topInquiredProperties"
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
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ property.broker?.name || "N/A" }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        {{ property.total_inquiries }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600"
                                    >
                                        {{ property.pending_inquiries }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-green-600"
                                    >
                                        {{ property.responded_inquiries }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm text-gray-900">
                                                {{ property.response_rate }}%
                                            </div>
                                            <div
                                                class="ml-2 w-16 bg-gray-200 rounded-full h-2"
                                            >
                                                <div
                                                    class="bg-green-600 h-2 rounded-full"
                                                    :style="{
                                                        width:
                                                            property.response_rate +
                                                            '%',
                                                    }"
                                                ></div>
                                            </div>
                                        </div>
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
                            Recent Inquiry Activities
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
                                Inquiry activities will appear here as they
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
    ChatBubbleLeftRightIcon,
    ClockIcon,
    CheckCircleIcon,
    ChartBarIcon,
    HomeIcon,
    PlusIcon,
    ChatBubbleLeftIcon,
    CheckIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    stats: Object,
    chartData: Object,
    topInquiredProperties: Array,
    recentActivities: Array,
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const getStatusColor = (status) => {
    const colors = {
        pending: "bg-yellow-500",
        responded: "bg-green-500",
        closed: "bg-gray-500",
        cancelled: "bg-red-500",
    };
    return colors[status] || "bg-gray-500";
};

const getActivityIcon = (type) => {
    const icons = {
        inquiry: PlusIcon,
        response: ChatBubbleLeftIcon,
        closed: CheckIcon,
    };
    return icons[type] || ClockIcon;
};

const getActivityIconClass = (type) => {
    const classes = {
        inquiry: "bg-blue-500",
        response: "bg-green-500",
        closed: "bg-gray-500",
    };
    return classes[type] || "bg-gray-500";
};
</script>
