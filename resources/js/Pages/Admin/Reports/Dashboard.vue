<template>
    <ModernDashboardLayout>
        <Head title="Reports Dashboard" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                Reports Dashboard
                            </h1>
                            <p class="mt-2 text-gray-600">
                                Comprehensive analytics and reporting for your
                                platform
                            </p>
                        </div>

                        <!-- Date Range Filter -->
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700"
                                    >From:</label
                                >
                                <input
                                    type="date"
                                    v-model="filters.start_date"
                                    @change="updateDateRange"
                                    class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                />
                            </div>
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700"
                                    >To:</label
                                >
                                <input
                                    type="date"
                                    v-model="filters.end_date"
                                    @change="updateDateRange"
                                    class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overview Statistics -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
                >
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <UsersIcon class="h-6 w-6 text-blue-400" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Total Brokers
                                        </dt>
                                        <dd class="flex items-baseline">
                                            <div
                                                class="text-2xl font-semibold text-gray-900"
                                            >
                                                {{ stats.total_brokers }}
                                            </div>
                                            <div
                                                class="ml-2 flex items-baseline text-sm font-semibold text-green-600"
                                            >
                                                {{ stats.active_brokers }}
                                                active
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <Link
                                    :href="route('admin.reports.brokers')"
                                    class="font-medium text-indigo-700 hover:text-indigo-900"
                                >
                                    View broker reports
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <HomeIcon class="h-6 w-6 text-green-400" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Total Properties
                                        </dt>
                                        <dd class="flex items-baseline">
                                            <div
                                                class="text-2xl font-semibold text-gray-900"
                                            >
                                                {{ stats.total_properties }}
                                            </div>
                                            <div
                                                class="ml-2 flex items-baseline text-sm font-semibold text-green-600"
                                            >
                                                {{ stats.active_properties }}
                                                active
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <Link
                                    :href="route('admin.reports.properties')"
                                    class="font-medium text-indigo-700 hover:text-indigo-900"
                                >
                                    View property reports
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ChatBubbleLeftRightIcon
                                        class="h-6 w-6 text-purple-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Inquiries
                                        </dt>
                                        <dd class="flex items-baseline">
                                            <div
                                                class="text-2xl font-semibold text-gray-900"
                                            >
                                                {{ stats.total_inquiries }}
                                            </div>
                                            <div
                                                class="ml-2 flex items-baseline text-sm font-semibold text-yellow-600"
                                            >
                                                {{ stats.pending_inquiries }}
                                                pending
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <Link
                                    :href="route('admin.reports.inquiries')"
                                    class="font-medium text-indigo-700 hover:text-indigo-900"
                                >
                                    View inquiry reports
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ExclamationTriangleIcon
                                        class="h-6 w-6 text-red-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Compliance Reports
                                        </dt>
                                        <dd class="flex items-baseline">
                                            <div
                                                class="text-2xl font-semibold text-gray-900"
                                            >
                                                {{ stats.compliance_reports }}
                                            </div>
                                            <div
                                                class="ml-2 flex items-baseline text-sm font-semibold text-red-600"
                                            >
                                                in period
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <Link
                                    :href="route('admin.reports.compliance')"
                                    class="font-medium text-indigo-700 hover:text-indigo-900"
                                >
                                    View compliance reports
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Activity Chart -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Activity Trends
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg"
                            >
                                <div class="text-center">
                                    <ChartBarIcon
                                        class="mx-auto h-12 w-12 text-gray-400"
                                    />
                                    <h3
                                        class="mt-2 text-sm font-medium text-gray-900"
                                    >
                                        Activity Chart
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Chart visualization would be implemented
                                        here
                                    </p>
                                    <div
                                        class="mt-4 space-y-2 text-xs text-gray-600"
                                    >
                                        <div class="flex justify-between">
                                            <span>Broker Registrations:</span>
                                            <span class="font-medium"
                                                >{{ (chartData?.registrations || []).length }}
                                                days with activity</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Property Listings:</span>
                                            <span class="font-medium"
                                                >{{ (chartData?.listings || []).length }}
                                                days with activity</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Inquiries:</span>
                                            <span class="font-medium"
                                                >{{
                                                    chartData.inquiries.length
                                                }}
                                                days with activity</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Recent Activities
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
                                <h3
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    No recent activities
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Activities will appear here as they occur.
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
                                            <div
                                                class="relative flex space-x-3"
                                            >
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

                <!-- Top Performers -->
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Top Brokers -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Top Performing Brokers
                            </h3>
                        </div>
                        <div class="p-6">
                            <div
                                v-if="(topPerformers?.brokers || []).length === 0"
                                class="text-center py-8"
                            >
                                <UsersIcon
                                    class="mx-auto h-12 w-12 text-gray-400"
                                />
                                <h3
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    No broker data
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Broker performance data will appear here.
                                </p>
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="(
                                        broker, index
                                    ) in topPerformers.brokers"
                                    :key="broker.id"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center"
                                            >
                                                <span
                                                    class="text-sm font-medium text-indigo-800"
                                                    >{{ index + 1 }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ broker.name }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ broker.email }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ broker.inquiries_count }}
                                            inquiries
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ broker.properties_count }}
                                            properties
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Properties -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Most Inquired Properties
                            </h3>
                        </div>
                        <div class="p-6">
                            <div
                                v-if="(topPerformers?.properties || []).length === 0"
                                class="text-center py-8"
                            >
                                <HomeIcon
                                    class="mx-auto h-12 w-12 text-gray-400"
                                />
                                <h3
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    No property data
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Property performance data will appear here.
                                </p>
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="(
                                        property, index
                                    ) in topPerformers.properties"
                                    :key="property.id"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center"
                                            >
                                                <span
                                                    class="text-sm font-medium text-green-800"
                                                    >{{ index + 1 }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ property.title }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ property.location }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ property.inquiries_count }}
                                            inquiries
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            ₱{{ formatPrice(property.price) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, reactive } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    UsersIcon,
    HomeIcon,
    ChatBubbleLeftRightIcon,
    ExclamationTriangleIcon,
    ChartBarIcon,
    ClockIcon,
    UserIcon,
    BuildingOfficeIcon,
    ChatBubbleOvalLeftIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    stats: Object,
    chartData: {
        type: Object,
        default: () => ({
            registrations: [],
            listings: [],
            inquiries: [],
        }),
    },
    recentActivities: Array,
    topPerformers: Object,
    dateRange: {
        type: Object,
        default: () => ({
            start: new Date().toISOString().split("T")[0],
            end: new Date().toISOString().split("T")[0],
        }),
    },
});

const filters = reactive({
    start_date:
        props.dateRange?.start || new Date().toISOString().split("T")[0],
    end_date: props.dateRange?.end || new Date().toISOString().split("T")[0],
});

const updateDateRange = () => {
    router.get(route("admin.reports.dashboard"), filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH").format(price);
};

const getActivityIcon = (type) => {
    const icons = {
        broker_registration: UserIcon,
        property_listing: BuildingOfficeIcon,
        inquiry: ChatBubbleOvalLeftIcon,
    };
    return icons[type] || ClockIcon;
};

const getActivityIconClass = (type) => {
    const classes = {
        broker_registration: "bg-blue-500",
        property_listing: "bg-green-500",
        inquiry: "bg-purple-500",
    };
    return classes[type] || "bg-gray-500";
};
</script>
