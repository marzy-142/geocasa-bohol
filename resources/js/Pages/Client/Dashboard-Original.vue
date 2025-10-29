<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, computed } from "vue";
import NotificationService from "@/Services/NotificationService";
import LoadingSkeleton from "@/Components/LoadingSkeleton.vue";
import EmptyState from "@/Components/EmptyState.vue";
import EnhancedTooltip from "@/Components/EnhancedTooltip.vue";
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
    SparklesIcon,
    TrophyIcon,
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

// Real-time inquiries list
const realtimeInquiries = ref([...props.recentInquiries]);

// Simulate initial loading
onMounted(() => {
    setTimeout(() => {
        isInitialLoad.value = false;
    }, 1000);
});

// Setup real-time updates for client
const setupRealtimeUpdates = () => {
    const user = page.props.auth.user;

    // Listen to client-specific channel for transaction updates
    echoChannel = window.Echo.private(`App.Models.User.${user.id}`)
        .listen("TransactionCreated", (e) => {
            if (
                e.transaction.client_id === user.id ||
                e.transaction.client?.user_id === user.id
            ) {
                NotificationService.success(
                    `Your transaction has been created: ${e.transaction.property?.title}`
                );
                // Update stats if needed
            }
        })
        .listen("TransactionStatusUpdated", (e) => {
            if (
                e.transaction.client_id === user.id ||
                e.transaction.client?.user_id === user.id
            ) {
                NotificationService.info(
                    `Transaction status updated: ${e.transaction.status}`
                );
                if (e.transaction.status === "finalized") {
                    NotificationService.success(
                        "Congratulations! Your transaction has been finalized!"
                    );
                }
            }
        })
        .listen("InquiryStatusUpdated", (e) => {
            if (
                e.inquiry.client_id === user.id ||
                e.inquiry.user_id === user.id
            ) {
                NotificationService.info(
                    `Your inquiry status updated: ${e.inquiry.status}`
                );
                // Update inquiry in the list
                const inquiryIndex = realtimeInquiries.value.findIndex(
                    (inq) => inq.id === e.inquiry.id
                );
                if (inquiryIndex !== -1) {
                    realtimeInquiries.value[inquiryIndex] = {
                        ...realtimeInquiries.value[inquiryIndex],
                        ...e.inquiry,
                    };
                }
            }
        });
};

const cleanupRealtimeUpdates = () => {
    if (echoChannel) {
        echoChannel
            .stopListening("TransactionCreated")
            .stopListening("TransactionStatusUpdated")
            .stopListening("InquiryStatusUpdated");
        window.Echo.leaveChannel(`App.Models.User.${page.props.auth.user.id}`);
    }
};

onMounted(() => {
    setupRealtimeUpdates();
});

onUnmounted(() => {
    cleanupRealtimeUpdates();
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    })
        .format(value)
        .replace("PHP", "")
        .trim();
};

const getImageUrl = (image) => {
    if (!image) {
        return "/images/placeholder-property.jpg";
    }

    if (typeof image === "string") {
        if (image.startsWith("http")) {
            return image;
        }
        return `/storage/${image}`;
    }

    return "/images/placeholder-property.jpg";
};

const getStatusColor = (status) => {
    const colors = {
        new: "bg-blue-100 text-blue-800",
        contacted: "bg-yellow-100 text-yellow-800",
        scheduled: "bg-purple-100 text-purple-800",
        completed: "bg-green-100 text-green-800",
        closed: "bg-gray-100 text-gray-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const getStatusLabel = (status) => {
    const labels = {
        new: "New",
        contacted: "Contacted",
        scheduled: "Scheduled",
        completed: "Completed",
        closed: "Closed",
    };
    return labels[status] || status;
};

const getActivityIcon = (type) => {
    const icons = {
        inquiry: ChatBubbleLeftRightIcon,
        transaction: CheckCircleIcon,
        meeting: CalendarIcon,
        property: BuildingOfficeIcon,
        notification: BellIcon,
    };
    return icons[type] || InformationCircleIcon;
};

const getActivityColor = (type) => {
    const colors = {
        inquiry: "text-blue-600",
        transaction: "text-green-600",
        meeting: "text-purple-600",
        property: "text-orange-600",
        notification: "text-yellow-600",
    };
    return colors[type] || "text-gray-600";
};
</script>

<template>
    <Head title="My Dashboard - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Welcome Header -->
        <div
            class="bg-white border border-neutral-200 rounded-lg p-6 mb-6 shadow-sm"
        >
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <h1 class="text-2xl font-semibold mb-2 text-neutral-900">
                        Welcome back, {{ page.props.auth.user.name }}
                    </h1>
                    <p class="text-neutral-600 text-base">
                        Manage your property search and inquiries
                    </p>
                    <p class="text-neutral-500 text-sm mt-1">
                        Last login: {{ new Date().toLocaleDateString() }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('public.properties')"
                        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md font-medium transition-colors flex items-center gap-2"
                    >
                        <MagnifyingGlassIcon class="w-4 h-4" />
                        Search Properties
                    </Link>
                    <Link
                        :href="route('client.inquiries.create')"
                        class="bg-white border border-neutral-300 text-neutral-700 hover:bg-neutral-50 px-4 py-2 rounded-md font-medium transition-colors flex items-center gap-2"
                    >
                        <PlusIcon class="w-4 h-4" />
                        New Inquiry
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Loading State -->
            <template v-if="isInitialLoad">
                <LoadingSkeleton v-for="n in 6" :key="n" type="stats-card" />
            </template>

            <!-- Cards when loaded -->
            <template v-else>
                <!-- Inquiries Card -->
                <div
                    class="bg-white rounded-lg p-6 shadow-sm border border-neutral-200 hover:shadow-md transition-shadow duration-200"
                >
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
                <div
                    class="bg-white rounded-lg p-6 shadow-sm border border-neutral-200 hover:shadow-md transition-shadow duration-200"
                >
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
                <div
                    class="bg-white rounded-lg p-6 shadow-sm border border-neutral-200 hover:shadow-md transition-shadow duration-200"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-sm font-medium text-neutral-600">
                                    Saved Properties
                                </p>
                                <EnhancedTooltip
                                    content="Properties you've saved for later viewing"
                                >
                                    <template #trigger>
                                        <InformationCircleIcon
                                            class="w-4 h-4 text-neutral-400 hover:text-neutral-600 cursor-help"
                                        />
                                    </template>
                                </EnhancedTooltip>
                            </div>
                            <p class="text-3xl font-bold text-neutral-900 mb-1">
                                {{ realtimeStats.savedProperties }}
                            </p>
                            <p class="text-sm text-neutral-500">
                                Your favorites
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-red-100 to-red-200 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200"
                        >
                            <HeartIcon class="w-6 h-6 text-red-600" />
                        </div>
                    </div>
                    <Link
                        :href="route('public.properties')"
                        class="inline-flex items-center text-red-600 hover:text-red-700 text-sm font-medium mt-4 group/link"
                    >
                        View saved properties
                        <ArrowRightIcon
                            class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform"
                        />
                    </Link>
                </div>

                <!-- Properties Viewed Card -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-soft-lg border border-neutral-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-sm font-medium text-neutral-600">
                                    Properties Viewed
                                </p>
                                <EnhancedTooltip
                                    content="Number of properties you've viewed this month"
                                >
                                    <template #trigger>
                                        <InformationCircleIcon
                                            class="w-4 h-4 text-neutral-400 hover:text-neutral-600 cursor-help"
                                        />
                                    </template>
                                </EnhancedTooltip>
                            </div>
                            <p class="text-3xl font-bold text-neutral-900 mb-1">
                                {{ realtimeStats.viewedProperties }}
                            </p>
                            <p class="text-sm text-neutral-500">This month</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200"
                        >
                            <EyeIcon class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                    <Link
                        :href="route('public.properties')"
                        class="inline-flex items-center text-purple-600 hover:text-purple-700 text-sm font-medium mt-4 group/link"
                    >
                        Continue browsing
                        <ArrowRightIcon
                            class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform"
                        />
                    </Link>
                </div>

                <!-- Scheduled Meetings Card -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-soft-lg border border-neutral-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-sm font-medium text-neutral-600">
                                    Scheduled Meetings
                                </p>
                                <EnhancedTooltip
                                    content="Meetings scheduled with your broker this week"
                                >
                                    <template #trigger>
                                        <InformationCircleIcon
                                            class="w-4 h-4 text-neutral-400 hover:text-neutral-600 cursor-help"
                                        />
                                    </template>
                                </EnhancedTooltip>
                            </div>
                            <p class="text-3xl font-bold text-neutral-900 mb-1">
                                {{ realtimeStats.scheduledMeetings }}
                            </p>
                            <p class="text-sm text-neutral-500">This week</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-orange-100 to-orange-200 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200"
                        >
                            <CalendarIcon class="w-6 h-6 text-orange-600" />
                        </div>
                    </div>
                    <Link
                        :href="route('client.broker')"
                        class="inline-flex items-center text-orange-600 hover:text-orange-700 text-sm font-medium mt-4 group/link"
                    >
                        View schedule
                        <ArrowRightIcon
                            class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform"
                        />
                    </Link>
                </div>

                <!-- Favorite Areas Card -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-soft-lg border border-neutral-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-sm font-medium text-neutral-600">
                                    Favorite Areas
                                </p>
                                <EnhancedTooltip
                                    content="Locations you've marked as favorites"
                                >
                                    <template #trigger>
                                        <InformationCircleIcon
                                            class="w-4 h-4 text-neutral-400 hover:text-neutral-600 cursor-help"
                                        />
                                    </template>
                                </EnhancedTooltip>
                            </div>
                            <p class="text-3xl font-bold text-neutral-900 mb-1">
                                {{ realtimeStats.favoriteAreas }}
                            </p>
                            <p class="text-sm text-neutral-500">
                                Saved locations
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-teal-100 to-teal-200 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-200"
                        >
                            <MapPinIcon class="w-6 h-6 text-teal-600" />
                        </div>
                    </div>
                    <Link
                        :href="route('profile.edit')"
                        class="inline-flex items-center text-teal-600 hover:text-teal-700 text-sm font-medium mt-4 group/link"
                    >
                        Manage areas
                        <ArrowRightIcon
                            class="w-4 h-4 ml-1 group-hover/link:translate-x-1 transition-transform"
                        />
                    </Link>
                </div>
            </template>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Recommended Properties -->
            <div class="lg:col-span-2">
                <div
                    class="bg-white rounded-2xl shadow-soft-lg border border-neutral-100 p-6"
                >
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-primary-500 to-accent-500 rounded-2xl flex items-center justify-center"
                            >
                                <SparklesIcon class="w-5 h-5 text-white" />
                            </div>
                            <h2 class="text-xl font-bold text-neutral-900">
                                🔥 Recommended for You
                            </h2>
                        </div>
                        <Link
                            :href="route('client.properties')"
                            class="text-primary-600 hover:text-primary-700 text-sm font-medium flex items-center gap-1 group/link"
                        >
                            View all
                            <ArrowRightIcon
                                class="w-4 h-4 group-hover/link:translate-x-1 transition-transform"
                            />
                        </Link>
                    </div>

                    <!-- Loading State for Properties -->
                    <div
                        v-if="isInitialLoad"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                    >
                        <LoadingSkeleton
                            v-for="n in 4"
                            :key="n"
                            type="property-card"
                        />
                    </div>

                    <div
                        v-if="recommendedProperties.length > 0"
                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                    >
                        <div
                            v-for="property in recommendedProperties.slice(
                                0,
                                4
                            )"
                            :key="property.id"
                            class="group cursor-pointer"
                        >
                            <div
                                class="bg-neutral-50 rounded-xl p-4 hover:bg-neutral-100 transition-colors duration-200"
                            >
                                <div
                                    class="h-32 mb-3 overflow-hidden rounded-lg"
                                >
                                    <LazyImage
                                        :src="getImageUrl(property.main_image)"
                                        :alt="property.title"
                                        :lazy="true"
                                        aspect-ratio="16/9"
                                        object-fit="cover"
                                        rounded="lg"
                                        :hover="true"
                                        :badge="
                                            property.is_featured
                                                ? 'Featured'
                                                : null
                                        "
                                        badge-variant="primary"
                                        class="w-full h-full"
                                    >
                                        <template #overlay>
                                            <div
                                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center"
                                            >
                                                <div
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                                >
                                                    <EyeIcon
                                                        class="w-6 h-6 text-white"
                                                    />
                                                </div>
                                            </div>
                                        </template>
                                    </LazyImage>
                                </div>
                                <h3
                                    class="font-semibold text-neutral-900 mb-1 line-clamp-1"
                                >
                                    {{ property.title }}
                                </h3>
                                <p
                                    class="text-sm text-neutral-600 mb-2 flex items-center gap-1"
                                >
                                    <MapPinIcon class="w-4 h-4" />
                                    {{ property.municipality }}
                                </p>
                                <div class="flex items-center justify-between">
                                    <p
                                        class="text-lg font-bold text-primary-600"
                                    >
                                        {{
                                            formatCurrency(property.total_price)
                                        }}
                                    </p>
                                    <div class="flex items-center gap-1">
                                        <StarIcon
                                            class="w-4 h-4 text-yellow-500 fill-current"
                                        />
                                        <span class="text-sm text-neutral-600"
                                            >4.8</span
                                        >
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 mt-3">
                                    <Link
                                        :href="
                                            route(
                                                'public.properties.show',
                                                property.slug
                                            )
                                        "
                                        class="flex-1 bg-primary-600 hover:bg-primary-700 text-white text-center py-2 px-3 rounded-lg text-sm font-medium transition-colors"
                                    >
                                        View Details
                                    </Link>
                                    <button
                                        class="p-2 text-neutral-400 hover:text-red-500 transition-colors"
                                    >
                                        <HeartIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <EmptyState
                        v-else
                        title="No recommendations yet"
                        description="Start browsing properties to get personalized recommendations based on your preferences and budget."
                        :icon="HomeIcon"
                        variant="primary"
                        :actions="[
                            {
                                text: 'Browse Properties',
                                href: route('public.properties'),
                                icon: MagnifyingGlassIcon,
                                variant: 'primary',
                            },
                            {
                                text: 'Update Preferences',
                                href: route('profile.edit'),
                                icon: Cog6ToothIcon,
                                variant: 'outline',
                            },
                        ]"
                    />
                </div>
            </div>

            <!-- My Broker -->
            <div class="lg:col-span-1">
                <div
                    class="bg-white rounded-2xl shadow-soft-lg border border-neutral-100 p-6"
                >
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center"
                        >
                            <UserGroupIcon class="w-5 h-5 text-white" />
                        </div>
                        <h2 class="text-xl font-bold text-neutral-900">
                            👨‍💼 My Broker
                        </h2>
                    </div>

                    <div v-if="broker" class="text-center">
                        <div
                            class="w-20 h-20 bg-gradient-to-br from-primary-500 to-accent-500 rounded-full flex items-center justify-center mx-auto mb-4"
                        >
                            <span class="text-2xl font-bold text-white">{{
                                broker.name.charAt(0)
                            }}</span>
                        </div>
                        <h3 class="font-bold text-neutral-900 mb-1">
                            {{ broker.name }}
                        </h3>
                        <p class="text-sm text-neutral-600 mb-2">
                            🏆 Top Rated Broker
                        </p>
                        <div
                            class="flex items-center justify-center gap-1 mb-3"
                        >
                            <StarIcon
                                class="w-4 h-4 text-yellow-500 fill-current"
                            />
                            <span class="text-sm font-medium text-neutral-700"
                                >4.9 (47 reviews)</span
                            >
                        </div>
                        <p class="text-sm text-neutral-500 mb-4">
                            Specializes in beachfront properties
                        </p>
                        <div class="space-y-2">
                            <Link
                                :href="route('client.broker')"
                                class="w-full bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors block text-center"
                            >
                                💬 Message Broker
                            </Link>
                            <Link
                                :href="route('client.broker')"
                                class="w-full bg-neutral-100 hover:bg-neutral-200 text-neutral-700 py-2 px-4 rounded-lg text-sm font-medium transition-colors block text-center"
                            >
                                📅 Schedule Meeting
                            </Link>
                        </div>
                    </div>

                    <EmptyState
                        v-else
                        title="No broker assigned yet"
                        description="Create an inquiry about a property to get connected with a qualified broker who will assist you with your property search."
                        :icon="UserGroupIcon"
                        variant="neutral"
                        size="md"
                        :actions="[
                            {
                                text: 'Create Inquiry',
                                href: route('client.inquiries.create'),
                                icon: PlusIcon,
                                variant: 'primary',
                            },
                        ]"
                    />
                </div>
            </div>
        </div>

        <!-- Browse All Properties Section -->
        <div
            class="bg-gradient-to-r from-primary-500 to-accent-500 text-white rounded-2xl p-8 shadow-soft-lg"
        >
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <h2 class="text-3xl font-bold mb-3 text-white">
                        🏘️ Explore All Properties
                    </h2>
                    <p class="text-primary-100 text-lg mb-2">
                        Discover amazing properties across beautiful Bohol
                    </p>
                    <p class="text-primary-200 text-sm">
                        Advanced search, filters, and personalized
                        recommendations
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <Link
                        :href="route('client.properties')"
                        class="bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white px-8 py-4 rounded-2xl font-semibold transition-all duration-200 hover:scale-105 flex items-center justify-center gap-2"
                    >
                        <BuildingOfficeIcon class="w-5 h-5" />
                        Browse Properties
                    </Link>
                    <Link
                        :href="route('client.properties') + '?saved=true'"
                        class="bg-white text-primary-600 hover:bg-primary-50 px-8 py-4 rounded-2xl font-semibold transition-all duration-200 hover:scale-105 flex items-center justify-center gap-2"
                    >
                        <HeartIcon class="w-5 h-5" />
                        Saved Properties
                    </Link>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div
            class="bg-white rounded-2xl shadow-soft-lg border border-neutral-100 p-6"
        >
            <div class="flex items-center gap-3 mb-6">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center"
                >
                    <ClockIcon class="w-5 h-5 text-white" />
                </div>
                <h2 class="text-xl font-bold text-neutral-900">
                    📊 Recent Activity
                </h2>
            </div>

            <div v-if="recentActivity.length > 0" class="space-y-4">
                <div
                    v-for="activity in recentActivity.slice(0, 5)"
                    :key="activity.id"
                    class="flex items-start gap-4 p-4 bg-neutral-50 rounded-xl hover:bg-neutral-100 transition-colors"
                >
                    <div
                        class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm"
                    >
                        <component
                            :is="getActivityIcon(activity.type)"
                            :class="getActivityColor(activity.type)"
                            class="w-4 h-4"
                        />
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-neutral-900">
                            {{ activity.title }}
                        </p>
                        <p class="text-sm text-neutral-600">
                            {{ activity.description }}
                        </p>
                        <p class="text-xs text-neutral-500 mt-1">
                            {{ activity.date }}
                        </p>
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
                description="Your activity will appear here as you browse properties, create inquiries, and interact with your broker."
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

.shadow-soft-lg {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>
