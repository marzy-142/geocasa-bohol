<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, computed } from "vue";
import NotificationService from "@/Services/NotificationService";
import LoadingSkeleton from "@/Components/LoadingSkeleton.vue";
import EmptyState from "@/Components/EmptyState.vue";
import LazyImage from "@/Components/LazyImage.vue";
import {
    BuildingOfficeIcon,
    HeartIcon,
    ChatBubbleLeftRightIcon,
    EyeIcon,
    MapPinIcon,
    StarIcon,
    ClockIcon,
    UserGroupIcon,
    CalendarIcon,
    BellIcon,
    MagnifyingGlassIcon,
    PlusIcon,
    ArrowRightIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    HomeIcon,
    Cog6ToothIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            savedProperties: 0,
            activeInquiries: 0,
            viewedProperties: 0,
            favoriteAreas: 0,
            totalBudget: 0,
            scheduledMeetings: 0,
        }),
    },
    recentInquiries: {
        type: Array,
        default: () => [],
    },
    recommendedProperties: {
        type: Array,
        default: () => [],
    },
    broker: {
        type: Object,
        default: null,
    },
    recentActivity: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
let echoChannel = null;

// Loading states
const isLoading = ref(false);
const isInitialLoad = ref(true);

// Reactive stats that can be updated in real-time
const realtimeStats = ref({
    savedProperties: props.stats.savedProperties,
    activeInquiries: props.stats.activeInquiries,
    viewedProperties: props.stats.viewedProperties,
    favoriteAreas: props.stats.favoriteAreas,
    totalBudget: props.stats.totalBudget,
    scheduledMeetings: props.stats.scheduledMeetings,
});

// Simulate initial loading
onMounted(() => {
    setTimeout(() => {
        isInitialLoad.value = false;
    }, 1000);
});

// Format currency
const formatCurrency = (value) => {
    if (!value) return "₱0";
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

// Format date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

// Get status color
const getStatusColor = (status) => {
    const colors = {
        pending: "bg-yellow-100 text-yellow-800",
        active: "bg-green-100 text-green-800",
        completed: "bg-blue-100 text-blue-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

// Get status label
const getStatusLabel = (status) => {
    const labels = {
        pending: "Pending",
        active: "Active",
        completed: "Completed",
        cancelled: "Cancelled",
    };
    return labels[status] || status;
};
</script>

<template>
    <Head title="Dashboard - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-neutral-900">Dashboard</h1>
            <p class="text-neutral-600 mt-1">
                Welcome back, {{ page.props.auth.user.name }}
            </p>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <Link
                :href="route('public.properties')"
                class="bg-white border border-neutral-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 group"
            >
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center mr-3"
                    >
                        <MagnifyingGlassIcon class="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <h3 class="font-medium text-neutral-900">
                            Search Properties
                        </h3>
                        <p class="text-sm text-neutral-500">
                            Find your dream home
                        </p>
                    </div>
                </div>
            </Link>

            <Link
                :href="route('client.inquiries.create')"
                class="bg-white border border-neutral-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 group"
            >
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center mr-3"
                    >
                        <PlusIcon class="w-5 h-5 text-green-600" />
                    </div>
                    <div>
                        <h3 class="font-medium text-neutral-900">
                            New Inquiry
                        </h3>
                        <p class="text-sm text-neutral-500">
                            Ask about a property
                        </p>
                    </div>
                </div>
            </Link>

            <Link
                :href="route('client.properties.saved')"
                class="bg-white border border-neutral-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 group"
            >
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center mr-3"
                    >
                        <HeartIcon class="w-5 h-5 text-red-600" />
                    </div>
                    <div>
                        <h3 class="font-medium text-neutral-900">
                            Saved Properties
                        </h3>
                        <p class="text-sm text-neutral-500">
                            View your favorites
                        </p>
                    </div>
                </div>
            </Link>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <template v-if="isInitialLoad">
                <LoadingSkeleton v-for="n in 6" :key="n" type="stats-card" />
            </template>

            <template v-else>
                <!-- Inquiries Card -->
                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-neutral-600 mb-1"
                            >
                                Active Inquiries
                            </p>
                            <p
                                class="text-2xl font-semibold text-neutral-900 mb-1"
                            >
                                {{ realtimeStats.activeInquiries }}
                            </p>
                            <p class="text-sm text-neutral-500">
                                Pending responses
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center"
                        >
                            <ChatBubbleLeftRightIcon
                                class="w-5 h-5 text-blue-600"
                            />
                        </div>
                    </div>
                    <Link
                        :href="route('client.inquiries.index')"
                        class="inline-flex items-center text-blue-600 hover:text-blue-700 text-sm font-medium mt-4"
                    >
                        View all inquiries
                        <ArrowRightIcon class="w-4 h-4 ml-1" />
                    </Link>
                </div>

                <!-- Budget Card -->
                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-neutral-600 mb-1"
                            >
                                Total Budget
                            </p>
                            <p
                                class="text-2xl font-semibold text-neutral-900 mb-1"
                            >
                                {{ formatCurrency(realtimeStats.totalBudget) }}
                            </p>
                            <p class="text-sm text-neutral-500">
                                Available for investment
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center"
                        >
                            <StarIcon class="w-5 h-5 text-green-600" />
                        </div>
                    </div>
                    <Link
                        :href="route('profile.edit')"
                        class="inline-flex items-center text-green-600 hover:text-green-700 text-sm font-medium mt-4"
                    >
                        Update budget
                        <ArrowRightIcon class="w-4 h-4 ml-1" />
                    </Link>
                </div>

                <!-- Saved Properties Card -->
                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-neutral-600 mb-1"
                            >
                                Saved Properties
                            </p>
                            <p
                                class="text-2xl font-semibold text-neutral-900 mb-1"
                            >
                                {{ realtimeStats.savedProperties }}
                            </p>
                            <p class="text-sm text-neutral-500">
                                Your favorites
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center"
                        >
                            <HeartIcon class="w-5 h-5 text-red-600" />
                        </div>
                    </div>
                    <Link
                        :href="route('client.properties.saved')"
                        class="inline-flex items-center text-red-600 hover:text-red-700 text-sm font-medium mt-4"
                    >
                        View saved properties
                        <ArrowRightIcon class="w-4 h-4 ml-1" />
                    </Link>
                </div>

                <!-- Properties Viewed Card -->
                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-neutral-600 mb-1"
                            >
                                Properties Viewed
                            </p>
                            <p
                                class="text-2xl font-semibold text-neutral-900 mb-1"
                            >
                                {{ realtimeStats.viewedProperties }}
                            </p>
                            <p class="text-sm text-neutral-500">
                                Recently browsed
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center"
                        >
                            <EyeIcon class="w-5 h-5 text-purple-600" />
                        </div>
                    </div>
                    <Link
                        :href="route('public.properties')"
                        class="inline-flex items-center text-purple-600 hover:text-purple-700 text-sm font-medium mt-4"
                    >
                        Continue browsing
                        <ArrowRightIcon class="w-4 h-4 ml-1" />
                    </Link>
                </div>

                <!-- Favorite Areas Card -->
                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-neutral-600 mb-1"
                            >
                                Favorite Areas
                            </p>
                            <p
                                class="text-2xl font-semibold text-neutral-900 mb-1"
                            >
                                {{ realtimeStats.favoriteAreas }}
                            </p>
                            <p class="text-sm text-neutral-500">
                                Saved locations
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center"
                        >
                            <MapPinIcon class="w-5 h-5 text-orange-600" />
                        </div>
                    </div>
                    <Link
                        :href="route('public.properties')"
                        class="inline-flex items-center text-orange-600 hover:text-orange-700 text-sm font-medium mt-4"
                    >
                        Explore areas
                        <ArrowRightIcon class="w-4 h-4 ml-1" />
                    </Link>
                </div>

                <!-- Meetings Card -->
                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-medium text-neutral-600 mb-1"
                            >
                                Scheduled Meetings
                            </p>
                            <p
                                class="text-2xl font-semibold text-neutral-900 mb-1"
                            >
                                {{ realtimeStats.scheduledMeetings }}
                            </p>
                            <p class="text-sm text-neutral-500">
                                With your broker
                            </p>
                        </div>
                        <div
                            class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center"
                        >
                            <CalendarIcon class="w-5 h-5 text-indigo-600" />
                        </div>
                    </div>
                    <Link
                        :href="route('client.broker')"
                        class="inline-flex items-center text-indigo-600 hover:text-indigo-700 text-sm font-medium mt-4"
                    >
                        View meetings
                        <ArrowRightIcon class="w-4 h-4 ml-1" />
                    </Link>
                </div>
            </template>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white border border-neutral-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-neutral-900">
                    Recent Activity
                </h2>
                <Link
                    :href="route('client.inquiries.index')"
                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                >
                    View all
                </Link>
            </div>

            <div v-if="recentActivity.length > 0" class="space-y-4">
                <div
                    v-for="activity in recentActivity"
                    :key="activity.id"
                    class="flex items-center justify-between py-3 border-b border-neutral-100 last:border-b-0"
                >
                    <div class="flex items-center">
                        <div
                            class="w-8 h-8 bg-neutral-100 rounded-full flex items-center justify-center mr-3"
                        >
                            <component
                                :is="activity.icon"
                                class="w-4 h-4 text-neutral-600"
                            />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900">
                                {{ activity.title }}
                            </p>
                            <p class="text-xs text-neutral-500">
                                {{ activity.date }}
                            </p>
                        </div>
                    </div>
                    <div v-if="activity.status" class="flex items-center">
                        <span
                            :class="getStatusColor(activity.status)"
                            class="px-2 py-1 rounded-full text-xs font-medium"
                        >
                            {{ getStatusLabel(activity.status) }}
                        </span>
                    </div>
                </div>
            </div>

            <EmptyState
                v-else
                title="No recent activity"
                description="Your activity will appear here as you browse properties and create inquiries."
                :icon="ClockIcon"
                variant="neutral"
                :actions="[
                    {
                        text: 'Start Browsing',
                        href: route('public.properties'),
                        icon: MagnifyingGlassIcon,
                        variant: 'primary',
                    },
                    {
                        text: 'Create Inquiry',
                        href: route('client.inquiries.create'),
                        icon: PlusIcon,
                        variant: 'outline',
                    },
                ]"
            />
        </div>
    </ModernDashboardLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
