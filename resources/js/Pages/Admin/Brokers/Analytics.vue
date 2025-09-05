<script setup>
import { Head, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import { ref, computed } from "vue";
import {
    UserIcon,
    ArrowLeftIcon,
    ChartBarIcon,
    TrophyIcon,
    StarIcon,
    CurrencyDollarIcon,
    BuildingOfficeIcon,
    UsersIcon,
    ClockIcon,
    EyeIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    CalendarIcon,
    DocumentTextIcon,
    PhoneIcon,
    ChatBubbleLeftRightIcon,
    HandThumbUpIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    broker: Object,
    analytics: Object,
    performanceMetrics: Object,
    monthlyData: Array,
    clientFeedback: Array,
    topProperties: Array,
    recentTransactions: Array,
    comparisonData: Object,
});

// Time period filter
const selectedPeriod = ref("6months");
const periodOptions = [
    { value: "1month", label: "Last Month" },
    { value: "3months", label: "Last 3 Months" },
    { value: "6months", label: "Last 6 Months" },
    { value: "1year", label: "Last Year" },
    { value: "all", label: "All Time" },
];

// Computed properties
const performanceRating = computed(() => {
    const score = props.performanceMetrics?.overall_score || 0;
    if (score >= 90)
        return {
            rating: "excellent",
            class: "text-green-600",
            icon: "🏆",
            text: "Excellent",
            color: "green",
        };
    if (score >= 80)
        return {
            rating: "very-good",
            class: "text-blue-600",
            icon: "⭐",
            text: "Very Good",
            color: "blue",
        };
    if (score >= 70)
        return {
            rating: "good",
            class: "text-indigo-600",
            icon: "👍",
            text: "Good",
            color: "indigo",
        };
    if (score >= 60)
        return {
            rating: "average",
            class: "text-yellow-600",
            icon: "📊",
            text: "Average",
            color: "yellow",
        };
    return {
        rating: "poor",
        class: "text-red-600",
        icon: "📉",
        text: "Needs Improvement",
        color: "red",
    };
});

const responseTimeRating = computed(() => {
    const avgHours = props.performanceMetrics?.avg_response_time_hours || 0;
    if (avgHours <= 2) return { class: "text-green-600", text: "Excellent" };
    if (avgHours <= 6) return { class: "text-blue-600", text: "Good" };
    if (avgHours <= 24) return { class: "text-yellow-600", text: "Average" };
    return { class: "text-red-600", text: "Slow" };
});

const conversionTrend = computed(() => {
    const current = props.performanceMetrics?.conversion_rate || 0;
    const previous = props.comparisonData?.previous_conversion_rate || 0;
    const change = current - previous;

    return {
        change: Math.abs(change).toFixed(1),
        isPositive: change >= 0,
        icon: change >= 0 ? ArrowTrendingUpIcon : ArrowTrendingDownIcon,
        class: change >= 0 ? "text-green-600" : "text-red-600",
    };
});

// Methods
const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(value || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getPerformanceColor = (value, thresholds) => {
    if (value >= thresholds.excellent) return "text-green-600";
    if (value >= thresholds.good) return "text-blue-600";
    if (value >= thresholds.average) return "text-yellow-600";
    return "text-red-600";
};

const getProgressBarColor = (value, thresholds) => {
    if (value >= thresholds.excellent) return "bg-green-600";
    if (value >= thresholds.good) return "bg-blue-600";
    if (value >= thresholds.average) return "bg-yellow-600";
    return "bg-red-600";
};

const getRatingStars = (rating) => {
    const stars = [];
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;

    for (let i = 0; i < 5; i++) {
        if (i < fullStars) {
            stars.push("full");
        } else if (i === fullStars && hasHalfStar) {
            stars.push("half");
        } else {
            stars.push("empty");
        }
    }
    return stars;
};

const getTransactionStatusBadge = (status) => {
    const badges = {
        completed: "bg-green-100 text-green-800",
        pending: "bg-yellow-100 text-yellow-800",
        cancelled: "bg-red-100 text-red-800",
        in_progress: "bg-blue-100 text-blue-800",
    };
    return badges[status] || "bg-gray-100 text-gray-800";
};
</script>

<template>
    <ModernDashboardLayout>
        <Head :title="`${broker.name} - Performance Analytics`" />

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <ModernButton
                    variant="ghost"
                    :href="route('admin.brokers.show', broker.id)"
                >
                    <ArrowLeftIcon class="w-5 h-5" />
                    Back to Profile
                </ModernButton>
            </div>

            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div class="flex items-center gap-6">
                    <!-- Profile Picture -->
                    <div
                        class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0"
                    >
                        <img
                            v-if="broker.profile_picture_url"
                            :src="broker.profile_picture_url"
                            :alt="broker.name"
                            class="w-full h-full object-cover"
                        />
                        <UserIcon v-else class="w-8 h-8 text-gray-400" />
                    </div>

                    <!-- Basic Info -->
                    <div>
                        <h1 class="text-2xl font-bold text-neutral-900 mb-2">
                            {{ broker.name }} - Performance Analytics
                        </h1>
                        <div
                            class="flex items-center gap-4 text-sm text-gray-600"
                        >
                            <span>{{ broker.email }}</span>
                            <span
                                >Member since
                                {{ formatDate(broker.created_at) }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Period Filter -->
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700"
                        >Period:</label
                    >
                    <select
                        v-model="selectedPeriod"
                        class="rounded-lg border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option
                            v-for="option in periodOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Overall Performance Score -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Performance Score
                        </p>
                        <div class="flex items-center gap-2">
                            <p class="text-3xl font-bold text-gray-900">
                                {{ performanceMetrics?.overall_score || 0 }}%
                            </p>
                            <span class="text-2xl">{{
                                performanceRating.icon
                            }}</span>
                        </div>
                        <p :class="['text-sm mt-1', performanceRating.class]">
                            {{ performanceRating.text }}
                        </p>
                    </div>
                    <div
                        :class="[
                            'w-12 h-12 rounded-xl flex items-center justify-center',
                            `bg-${performanceRating.color}-100`,
                        ]"
                    >
                        <TrophyIcon
                            :class="[
                                'w-6 h-6',
                                `text-${performanceRating.color}-600`,
                            ]"
                        />
                    </div>
                </div>

                <!-- Performance breakdown -->
                <div class="space-y-2">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">Response Rate</span>
                        <span class="font-medium"
                            >{{ performanceMetrics?.response_rate || 0 }}%</span
                        >
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">Client Satisfaction</span>
                        <span class="font-medium"
                            >{{
                                performanceMetrics?.satisfaction_rate || 0
                            }}%</span
                        >
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">Conversion Rate</span>
                        <span class="font-medium"
                            >{{
                                performanceMetrics?.conversion_rate || 0
                            }}%</span
                        >
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Total Commission
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{
                                formatCurrency(analytics?.total_commission || 0)
                            }}
                        </p>
                        <div class="flex items-center gap-1 mt-1">
                            <component
                                :is="conversionTrend.icon"
                                :class="['w-4 h-4', conversionTrend.class]"
                            />
                            <span :class="['text-sm', conversionTrend.class]">
                                {{ conversionTrend.change }}% vs last period
                            </span>
                        </div>
                    </div>
                    <div
                        class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center"
                    >
                        <CurrencyDollarIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
            </div>

            <!-- Active Properties -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Active Properties
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ analytics?.active_properties || 0 }}
                        </p>
                        <p class="text-sm text-blue-600 mt-1">
                            {{ analytics?.total_properties || 0 }} total listed
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center"
                    >
                        <BuildingOfficeIcon class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
            </div>

            <!-- Client Satisfaction -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Client Rating
                        </p>
                        <div class="flex items-center gap-2">
                            <p class="text-2xl font-bold text-gray-900">
                                {{ analytics?.avg_rating || 0 }}
                            </p>
                            <div class="flex items-center">
                                <StarIcon
                                    v-for="(star, index) in getRatingStars(
                                        analytics?.avg_rating || 0
                                    )"
                                    :key="index"
                                    :class="[
                                        'w-4 h-4',
                                        star === 'full'
                                            ? 'text-yellow-400 fill-current'
                                            : star === 'half'
                                            ? 'text-yellow-400'
                                            : 'text-gray-300',
                                    ]"
                                />
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ analytics?.total_reviews || 0 }} reviews
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center"
                    >
                        <HandThumbUpIcon class="w-6 h-6 text-yellow-600" />
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Analytics -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Performance Metrics -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        Performance Metrics
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Response Time -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700"
                                    >Average Response Time</span
                                >
                                <span
                                    :class="[
                                        'text-sm font-medium',
                                        responseTimeRating.class,
                                    ]"
                                >
                                    {{ responseTimeRating.text }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <ClockIcon class="w-5 h-5 text-gray-400" />
                                <span
                                    class="text-lg font-semibold text-gray-900"
                                >
                                    {{
                                        performanceMetrics?.avg_response_time_hours ||
                                        0
                                    }}
                                    hours
                                </span>
                            </div>
                            <div class="mt-2 text-xs text-gray-600">
                                Industry average: 6-12 hours
                            </div>
                        </div>

                        <!-- Conversion Rate -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700"
                                    >Conversion Rate</span
                                >
                                <span
                                    :class="[
                                        'text-sm font-medium',
                                        getPerformanceColor(
                                            performanceMetrics?.conversion_rate ||
                                                0,
                                            {
                                                excellent: 15,
                                                good: 10,
                                                average: 5,
                                            }
                                        ),
                                    ]"
                                >
                                    {{
                                        performanceMetrics?.conversion_rate ||
                                        0
                                    }}%
                                </span>
                            </div>
                            <div
                                class="w-full bg-gray-200 rounded-full h-2 mb-2"
                            >
                                <div
                                    :class="[
                                        'h-2 rounded-full',
                                        getProgressBarColor(
                                            performanceMetrics?.conversion_rate ||
                                                0,
                                            {
                                                excellent: 15,
                                                good: 10,
                                                average: 5,
                                            }
                                        ),
                                    ]"
                                    :style="{
                                        width: `${Math.min(
                                            (performanceMetrics?.conversion_rate ||
                                                0) * 5,
                                            100
                                        )}%`,
                                    }"
                                ></div>
                            </div>
                            <div class="text-xs text-gray-600">
                                {{ analytics?.inquiries_converted || 0 }} of
                                {{ analytics?.total_inquiries || 0 }} inquiries
                            </div>
                        </div>

                        <!-- Client Retention -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700"
                                    >Client Retention</span
                                >
                                <span
                                    :class="[
                                        'text-sm font-medium',
                                        getPerformanceColor(
                                            performanceMetrics?.retention_rate ||
                                                0,
                                            {
                                                excellent: 80,
                                                good: 60,
                                                average: 40,
                                            }
                                        ),
                                    ]"
                                >
                                    {{
                                        performanceMetrics?.retention_rate || 0
                                    }}%
                                </span>
                            </div>
                            <div
                                class="w-full bg-gray-200 rounded-full h-2 mb-2"
                            >
                                <div
                                    :class="[
                                        'h-2 rounded-full',
                                        getProgressBarColor(
                                            performanceMetrics?.retention_rate ||
                                                0,
                                            {
                                                excellent: 80,
                                                good: 60,
                                                average: 40,
                                            }
                                        ),
                                    ]"
                                    :style="{
                                        width: `${
                                            performanceMetrics?.retention_rate ||
                                            0
                                        }%`,
                                    }"
                                ></div>
                            </div>
                            <div class="text-xs text-gray-600">
                                {{ analytics?.repeat_clients || 0 }} repeat
                                clients
                            </div>
                        </div>

                        <!-- Average Deal Size -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700"
                                    >Average Deal Size</span
                                >
                                <span
                                    class="text-sm font-medium text-green-600"
                                >
                                    {{
                                        formatCurrency(
                                            performanceMetrics?.avg_deal_size ||
                                                0
                                        )
                                    }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <CurrencyDollarIcon
                                    class="w-5 h-5 text-gray-400"
                                />
                                <span
                                    class="text-lg font-semibold text-gray-900"
                                >
                                    {{
                                        analytics?.completed_transactions || 0
                                    }}
                                    deals
                                </span>
                            </div>
                            <div class="mt-2 text-xs text-gray-600">
                                Total value:
                                {{
                                    formatCurrency(
                                        analytics?.total_transaction_value || 0
                                    )
                                }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performing Properties -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">
                            Top Performing Properties
                        </h2>
                        <ModernButton
                            variant="outline"
                            size="sm"
                            :href="route('admin.transactions.index', { broker: broker.id })"
                        >
                            View All
                        </ModernButton>
                    </div>

                    <div v-if="topProperties?.length" class="space-y-4">
                        <div
                            v-for="(property, index) in topProperties"
                            :key="property.id"
                            class="flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center"
                                >
                                    <span
                                        class="text-sm font-bold text-blue-600"
                                        >{{ index + 1 }}</span
                                    >
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">
                                        {{ property.title }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        {{ property.location }}
                                    </p>
                                    <div class="flex items-center gap-4 mt-1">
                                        <span
                                            class="text-sm font-medium text-primary-600"
                                        >
                                            {{ formatCurrency(property.price) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ property.views || 0 }} views
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{
                                                property.inquiries || 0
                                            }}
                                            inquiries
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <p
                                        class="text-sm font-medium text-green-600"
                                    >
                                        {{ property.conversion_rate || 0 }}%
                                        conversion
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Listed
                                        {{ formatDate(property.created_at) }}
                                    </p>
                                </div>
                                <ModernButton
                                    variant="ghost"
                                    size="sm"
                                    :href="
                                        route(
                                            'admin.properties.show',
                                            property.id
                                        )
                                    "
                                >
                                    <EyeIcon class="w-4 h-4" />
                                </ModernButton>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <BuildingOfficeIcon
                            class="w-12 h-12 text-gray-400 mx-auto mb-3"
                        />
                        <p class="text-gray-500">
                            No properties data available
                        </p>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">
                            Recent Transactions
                        </h2>
                        <ModernButton
                            variant="outline"
                            size="sm"
                            :href="
                                route('admin.brokers.transactions', broker.id)
                            "
                        >
                            View All Transactions
                        </ModernButton>
                    </div>

                    <div v-if="recentTransactions?.length" class="space-y-4">
                        <div
                            v-for="transaction in recentTransactions"
                            :key="transaction.id"
                            class="flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center"
                                >
                                    <CurrencyDollarIcon
                                        class="w-6 h-6 text-green-600"
                                    />
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">
                                        {{ transaction.property_title }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        {{ transaction.client_name }}
                                    </p>
                                    <div class="flex items-center gap-4 mt-1">
                                        <span
                                            class="text-sm font-medium text-green-600"
                                        >
                                            {{
                                                formatCurrency(
                                                    transaction.amount
                                                )
                                            }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            Commission:
                                            {{
                                                formatCurrency(
                                                    transaction.commission
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                            getTransactionStatusBadge(
                                                transaction.status
                                            ),
                                        ]"
                                    >
                                        {{
                                            transaction.status
                                                ?.replace("_", " ")
                                                .toUpperCase()
                                        }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ formatDate(transaction.created_at) }}
                                    </p>
                                </div>
                                <ModernButton
                                    variant="ghost"
                                    size="sm"
                                    :href="
                                        route(
                                            'admin.transactions.show',
                                            transaction.id
                                        )
                                    "
                                >
                                    <EyeIcon class="w-4 h-4" />
                                </ModernButton>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <CurrencyDollarIcon
                            class="w-12 h-12 text-gray-400 mx-auto mb-3"
                        />
                        <p class="text-gray-500">No recent transactions</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Stats -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Quick Stats
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <UsersIcon class="w-5 h-5 text-blue-600" />
                                <span class="text-sm text-gray-600"
                                    >Total Clients</span
                                >
                            </div>
                            <span class="font-semibold text-gray-900">{{
                                analytics?.total_clients || 0
                            }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <BuildingOfficeIcon
                                    class="w-5 h-5 text-green-600"
                                />
                                <span class="text-sm text-gray-600"
                                    >Properties Sold</span
                                >
                            </div>
                            <span class="font-semibold text-gray-900">{{
                                analytics?.properties_sold || 0
                            }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <ChatBubbleLeftRightIcon
                                    class="w-5 h-5 text-purple-600"
                                />
                                <span class="text-sm text-gray-600"
                                    >Total Inquiries</span
                                >
                            </div>
                            <span class="font-semibold text-gray-900">{{
                                analytics?.total_inquiries || 0
                            }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <PhoneIcon class="w-5 h-5 text-indigo-600" />
                                <span class="text-sm text-gray-600"
                                    >Calls Made</span
                                >
                            </div>
                            <span class="font-semibold text-gray-900">{{
                                analytics?.calls_made || 0
                            }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <CalendarIcon class="w-5 h-5 text-yellow-600" />
                                <span class="text-sm text-gray-600"
                                    >Appointments</span
                                >
                            </div>
                            <span class="font-semibold text-gray-900">{{
                                analytics?.appointments || 0
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Client Feedback -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Recent Client Feedback
                    </h3>

                    <div v-if="clientFeedback?.length" class="space-y-4">
                        <div
                            v-for="feedback in clientFeedback.slice(0, 3)"
                            :key="feedback.id"
                            class="p-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex items-center">
                                    <StarIcon
                                        v-for="(star, index) in getRatingStars(
                                            feedback.rating
                                        )"
                                        :key="index"
                                        :class="[
                                            'w-3 h-3',
                                            star === 'full'
                                                ? 'text-yellow-400 fill-current'
                                                : star === 'half'
                                                ? 'text-yellow-400'
                                                : 'text-gray-300',
                                        ]"
                                    />
                                </div>
                                <span class="text-xs text-gray-600">{{
                                    feedback.client_name
                                }}</span>
                            </div>
                            <p class="text-sm text-gray-700">
                                {{ feedback.comment }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ formatDate(feedback.created_at) }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="text-center py-4">
                        <HandThumbUpIcon
                            class="w-8 h-8 text-gray-400 mx-auto mb-2"
                        />
                        <p class="text-sm text-gray-500">No feedback yet</p>
                    </div>
                </div>

                <!-- Performance Comparison -->
                <div class="bg-blue-50 rounded-2xl border border-blue-100 p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">
                        Performance vs Average
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-blue-800">Response Rate</span>
                                <span class="font-medium text-blue-900">
                                    {{
                                        performanceMetrics?.response_rate || 0
                                    }}%
                                    <span class="text-xs"
                                        >(avg:
                                        {{
                                            comparisonData?.avg_response_rate ||
                                            0
                                        }}%)</span
                                    >
                                </span>
                            </div>
                            <div class="w-full bg-blue-200 rounded-full h-2">
                                <div
                                    class="bg-blue-600 h-2 rounded-full"
                                    :style="{
                                        width: `${
                                            performanceMetrics?.response_rate ||
                                            0
                                        }%`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-blue-800"
                                    >Conversion Rate</span
                                >
                                <span class="font-medium text-blue-900">
                                    {{
                                        performanceMetrics?.conversion_rate ||
                                        0
                                    }}%
                                    <span class="text-xs"
                                        >(avg:
                                        {{
                                            comparisonData?.avg_conversion_rate ||
                                            0
                                        }}%)</span
                                    >
                                </span>
                            </div>
                            <div class="w-full bg-blue-200 rounded-full h-2">
                                <div
                                    class="bg-blue-600 h-2 rounded-full"
                                    :style="{
                                        width: `${Math.min(
                                            (performanceMetrics?.conversion_rate ||
                                                0) * 5,
                                            100
                                        )}%`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-blue-800"
                                    >Client Satisfaction</span
                                >
                                <span class="font-medium text-blue-900">
                                    {{
                                        performanceMetrics?.satisfaction_rate ||
                                        0
                                    }}%
                                    <span class="text-xs"
                                        >(avg:
                                        {{
                                            comparisonData?.avg_satisfaction_rate ||
                                            0
                                        }}%)</span
                                    >
                                </span>
                            </div>
                            <div class="w-full bg-blue-200 rounded-full h-2">
                                <div
                                    class="bg-blue-600 h-2 rounded-full"
                                    :style="{
                                        width: `${
                                            performanceMetrics?.satisfaction_rate ||
                                            0
                                        }%`,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Items -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Recommended Actions
                    </h3>

                    <div class="space-y-3">
                        <div
                            v-if="(performanceMetrics?.response_rate || 0) < 80"
                            class="flex items-start gap-2"
                        >
                            <ExclamationTriangleIcon
                                class="w-4 h-4 text-yellow-600 mt-0.5 flex-shrink-0"
                            />
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Improve Response Time
                                </p>
                                <p class="text-xs text-gray-600">
                                    Current response rate is below average
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="
                                (performanceMetrics?.conversion_rate || 0) < 10
                            "
                            class="flex items-start gap-2"
                        >
                            <ExclamationTriangleIcon
                                class="w-4 h-4 text-yellow-600 mt-0.5 flex-shrink-0"
                            />
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Focus on Conversions
                                </p>
                                <p class="text-xs text-gray-600">
                                    Consider sales training or lead
                                    qualification
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="(analytics?.total_reviews || 0) < 5"
                            class="flex items-start gap-2"
                        >
                            <ExclamationTriangleIcon
                                class="w-4 h-4 text-yellow-600 mt-0.5 flex-shrink-0"
                            />
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Collect More Reviews
                                </p>
                                <p class="text-xs text-gray-600">
                                    Encourage satisfied clients to leave reviews
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="(analytics?.active_properties || 0) < 5"
                            class="flex items-start gap-2"
                        >
                            <ExclamationTriangleIcon
                                class="w-4 h-4 text-yellow-600 mt-0.5 flex-shrink-0"
                            />
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    Increase Property Listings
                                </p>
                                <p class="text-xs text-gray-600">
                                    More listings can lead to more opportunities
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
