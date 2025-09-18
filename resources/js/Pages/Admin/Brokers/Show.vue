<script setup>
import { Head, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import ModernTable from "@/Components/ModernTable.vue";
import { ref, computed } from "vue";
import {
    UserIcon,
    PencilIcon,
    ChartBarIcon,
    EyeIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    BuildingOfficeIcon,
    UsersIcon,
    CurrencyDollarIcon,
    DocumentCheckIcon,
    PhoneIcon,
    EnvelopeIcon,
    GlobeAltIcon,
    MapPinIcon,
    CalendarIcon,
    StarIcon,
    TrophyIcon,
    ClockIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    broker: Object,
    properties: Object,
    clients: Object,
    transactions: Object,
    performance: Object,
    recentActivities: Array,
});

// Computed properties
const verificationStatus = computed(() => {
    const verified =
        props.broker.prc_verified && props.broker.business_permit_verified;
    const partial =
        props.broker.prc_verified || props.broker.business_permit_verified;

    if (verified)
        return {
            status: "verified",
            class: "bg-green-100 text-green-800",
            text: "Fully Verified",
        };
    if (partial)
        return {
            status: "partial",
            class: "bg-yellow-100 text-yellow-800",
            text: "Partially Verified",
        };
    return {
        status: "unverified",
        class: "bg-red-100 text-red-800",
        text: "Unverified",
    };
});

const performanceRating = computed(() => {
    const score = props.performance?.overall_score || 0;
    if (score >= 90)
        return {
            rating: "excellent",
            class: "text-green-600",
            icon: "🏆",
            text: "Excellent",
        };
    if (score >= 80)
        return {
            rating: "very-good",
            class: "text-blue-600",
            icon: "⭐",
            text: "Very Good",
        };
    if (score >= 70)
        return {
            rating: "good",
            class: "text-indigo-600",
            icon: "👍",
            text: "Good",
        };
    if (score >= 60)
        return {
            rating: "average",
            class: "text-yellow-600",
            icon: "📊",
            text: "Average",
        };
    return {
        rating: "poor",
        class: "text-red-600",
        icon: "📉",
        text: "Needs Improvement",
    };
});

// Methods
const getStatusBadge = (status) => {
    const badges = {
        active: "bg-green-100 text-green-800",
        inactive: "bg-gray-100 text-gray-800",
        suspended: "bg-red-100 text-red-800",
        pending: "bg-yellow-100 text-yellow-800",
        under_review: "bg-blue-100 text-blue-800",
    };
    return badges[status] || "bg-gray-100 text-gray-800";
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(value || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
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

const getPropertyStatusBadge = (status) => {
    const badges = {
        active: "bg-green-100 text-green-800",
        sold: "bg-blue-100 text-blue-800",
        rented: "bg-purple-100 text-purple-800",
        inactive: "bg-gray-100 text-gray-800",
        pending: "bg-yellow-100 text-yellow-800",
    };
    return badges[status] || "bg-gray-100 text-gray-800";
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

const getActivityIcon = (type) => {
    const icons = {
        property_added: BuildingOfficeIcon,
        client_added: UsersIcon,
        transaction_completed: CurrencyDollarIcon,
        profile_updated: UserIcon,
        document_verified: DocumentCheckIcon,
        status_changed: ExclamationTriangleIcon,
    };
    return icons[type] || ClockIcon;
};

const getActivityColor = (type) => {
    const colors = {
        property_added: "text-green-600",
        client_added: "text-blue-600",
        transaction_completed: "text-purple-600",
        profile_updated: "text-indigo-600",
        document_verified: "text-emerald-600",
        status_changed: "text-yellow-600",
    };
    return colors[type] || "text-gray-600";
};
</script>

<template>
    <ModernDashboardLayout>
        <Head :title="`${broker.name} - Broker Profile`" />

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <ModernButton
                    variant="ghost"
                    :href="route('admin.brokers.index')"
                >
                    <ArrowLeftIcon class="w-5 h-5" />
                    Back to Brokers
                </ModernButton>
            </div>

            <div
                class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6"
            >
                <div class="flex items-start gap-6">
                    <!-- Profile Picture -->
                    <div
                        class="w-24 h-24 bg-gray-100 rounded-2xl flex items-center justify-center overflow-hidden flex-shrink-0"
                    >
                        <img
                            v-if="broker.profile_picture_url"
                            :src="broker.profile_picture_url"
                            :alt="broker.name"
                            class="w-full h-full object-cover"
                        />
                        <UserIcon v-else class="w-12 h-12 text-gray-400" />
                    </div>

                    <!-- Basic Info -->
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                            {{ broker.name }}
                        </h1>
                        <div class="flex items-center gap-4 mb-3">
                            <span
                                :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                    getStatusBadge(broker.application_status),
                                ]"
                            >
                                {{
                                    broker.application_status
                                        ?.replace("_", " ")
                                        .toUpperCase()
                                }}
                            </span>
                            <span
                                :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                    verificationStatus.class,
                                ]"
                            >
                                {{ verificationStatus.text }}
                            </span>
                        </div>
                        <div
                            class="flex items-center gap-6 text-sm text-gray-600"
                        >
                            <div class="flex items-center gap-1">
                                <EnvelopeIcon class="w-4 h-4" />
                                {{ broker.email }}
                            </div>
                            <div class="flex items-center gap-1">
                                <PhoneIcon class="w-4 h-4" />
                                {{ broker.phone }}
                            </div>
                            <div class="flex items-center gap-1">
                                <MapPinIcon class="w-4 h-4" />
                                {{ broker.city_name }},
                                {{ broker.province_name }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <ModernButton
                        variant="outline"
                        :href="route('admin.brokers.edit', broker.id)"
                    >
                        <PencilIcon class="w-5 h-5" />
                        Edit Profile
                    </ModernButton>
                    <ModernButton
                        variant="primary"
                        :href="route('admin.reports.brokers')"
                    >
                        <ChartBarIcon class="w-5 h-5" />
                        View Analytics
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Properties Listed
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ properties.total || 0 }}
                        </p>
                        <p class="text-xs text-green-600 mt-1">
                            {{ properties.active || 0 }} active
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center"
                    >
                        <BuildingOfficeIcon class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Total Clients
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ clients.total || 0 }}
                        </p>
                        <p class="text-xs text-blue-600 mt-1">
                            {{ clients.active || 0 }} active
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center"
                    >
                        <UsersIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Transactions
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ transactions.total || 0 }}
                        </p>
                        <p class="text-xs text-purple-600 mt-1">
                            {{ transactions.completed || 0 }} completed
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center"
                    >
                        <CurrencyDollarIcon class="w-6 h-6 text-purple-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Performance Score
                        </p>
                        <div class="flex items-center gap-2">
                            <p class="text-2xl font-bold text-gray-900">
                                {{ performance?.overall_score || 0 }}%
                            </p>
                            <span class="text-lg">{{
                                performanceRating.icon
                            }}</span>
                        </div>
                        <p :class="['text-xs mt-1', performanceRating.class]">
                            {{ performanceRating.text }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center"
                    >
                        <TrophyIcon class="w-6 h-6 text-yellow-600" />
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Professional Information -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        Professional Information
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                PRC License Number
                            </label>
                            <p class="text-gray-900 font-medium">
                                {{ broker.license_number || "Not provided" }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Years of Experience
                            </label>
                            <p class="text-gray-900 font-medium">
                                {{ broker.years_experience || "Not specified" }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Company/Agency
                            </label>
                            <p class="text-gray-900 font-medium">
                                {{ broker.company_name || "Independent" }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Specialization
                            </label>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="spec in broker.specialization || []"
                                    :key="spec"
                                    class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800"
                                >
                                    {{ spec }}
                                </span>
                                <span
                                    v-if="!broker.specialization?.length"
                                    class="text-gray-500"
                                >
                                    Not specified
                                </span>
                            </div>
                        </div>

                        <div class="md:col-span-2" v-if="broker.bio">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Professional Bio
                            </label>
                            <p class="text-gray-900">{{ broker.bio }}</p>
                        </div>

                        <div
                            class="md:col-span-2"
                            v-if="broker.company_address"
                        >
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Company Address
                            </label>
                            <p class="text-gray-900">
                                {{ broker.company_address }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Recent Properties -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">
                            Recent Properties
                        </h2>
                        <ModernButton
                            variant="outline"
                            size="sm"
                            :href="
                                route('admin.properties.index', {
                                    broker: broker.id,
                                })
                            "
                        >
                            View All
                        </ModernButton>
                    </div>

                    <div v-if="properties.data?.length" class="space-y-4">
                        <div
                            v-for="property in properties.data.slice(0, 5)"
                            :key="property.id"
                            class="flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                                >
                                    <BuildingOfficeIcon
                                        class="w-6 h-6 text-blue-600"
                                    />
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">
                                        {{ property.title }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        {{ property.location }}
                                    </p>
                                    <p
                                        class="text-sm font-medium text-primary-600"
                                    >
                                        {{ formatCurrency(property.price) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    :class="[
                                        'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                        getPropertyStatusBadge(property.status),
                                    ]"
                                >
                                    {{ property.status?.toUpperCase() }}
                                </span>
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
                        <p class="text-gray-500">No properties listed yet</p>
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
                                route('admin.transactions.index', {
                                    broker: broker.id,
                                })
                            "
                        >
                            View All
                        </ModernButton>
                    </div>

                    <div v-if="transactions.data?.length" class="space-y-4">
                        <div
                            v-for="transaction in transactions.data.slice(0, 5)"
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
                                    <p
                                        class="text-sm font-medium text-green-600"
                                    >
                                        {{ formatCurrency(transaction.amount) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
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
                        <p class="text-gray-500">No transactions yet</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Contact Information -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Contact Information
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <EnvelopeIcon class="w-5 h-5 text-gray-400" />
                            <div>
                                <p class="text-sm text-gray-600">Email</p>
                                <p class="font-medium text-gray-900">
                                    {{ broker.email }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <PhoneIcon class="w-5 h-5 text-gray-400" />
                            <div>
                                <p class="text-sm text-gray-600">Phone</p>
                                <p class="font-medium text-gray-900">
                                    {{ broker.phone }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <MapPinIcon class="w-5 h-5 text-gray-400" />
                            <div>
                                <p class="text-sm text-gray-600">Location</p>
                                <p class="font-medium text-gray-900">
                                    {{ broker.city_name }},
                                    {{ broker.province_name }}
                                </p>
                                <p
                                    class="text-sm text-gray-600"
                                    v-if="broker.address"
                                >
                                    {{ broker.address }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="broker.website"
                            class="flex items-center gap-3"
                        >
                            <GlobeAltIcon class="w-5 h-5 text-gray-400" />
                            <div>
                                <p class="text-sm text-gray-600">Website</p>
                                <a
                                    :href="broker.website"
                                    target="_blank"
                                    class="font-medium text-primary-600 hover:text-primary-700"
                                >
                                    {{ broker.website }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Verification Status -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Document Verification
                    </h3>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <component
                                    :is="
                                        broker.prc_verified
                                            ? CheckCircleIcon
                                            : XCircleIcon
                                    "
                                    :class="[
                                        'w-5 h-5',
                                        broker.prc_verified
                                            ? 'text-green-600'
                                            : 'text-red-600',
                                    ]"
                                />
                                <span class="text-sm text-gray-700"
                                    >PRC License</span
                                >
                            </div>
                            <span
                                :class="[
                                    'text-xs px-2 py-1 rounded-full',
                                    broker.prc_verified
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800',
                                ]"
                            >
                                {{
                                    broker.prc_verified
                                        ? "Verified"
                                        : "Unverified"
                                }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <component
                                    :is="
                                        broker.business_permit_verified
                                            ? CheckCircleIcon
                                            : XCircleIcon
                                    "
                                    :class="[
                                        'w-5 h-5',
                                        broker.business_permit_verified
                                            ? 'text-green-600'
                                            : 'text-red-600',
                                    ]"
                                />
                                <span class="text-sm text-gray-700"
                                    >Business Permit</span
                                >
                            </div>
                            <span
                                :class="[
                                    'text-xs px-2 py-1 rounded-full',
                                    broker.business_permit_verified
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800',
                                ]"
                            >
                                {{
                                    broker.business_permit_verified
                                        ? "Verified"
                                        : "Unverified"
                                }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <component
                                    :is="
                                        broker.additional_documents_verified
                                            ? CheckCircleIcon
                                            : XCircleIcon
                                    "
                                    :class="[
                                        'w-5 h-5',
                                        broker.additional_documents_verified
                                            ? 'text-green-600'
                                            : 'text-red-600',
                                    ]"
                                />
                                <span class="text-sm text-gray-700"
                                    >Additional Docs</span
                                >
                            </div>
                            <span
                                :class="[
                                    'text-xs px-2 py-1 rounded-full',
                                    broker.additional_documents_verified
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800',
                                ]"
                            >
                                {{
                                    broker.additional_documents_verified
                                        ? "Verified"
                                        : "Unverified"
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Performance Metrics
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-600"
                                    >Response Rate</span
                                >
                                <span class="text-sm font-medium text-gray-900">
                                    {{ performance?.response_rate || 0 }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div
                                    class="bg-blue-600 h-2 rounded-full"
                                    :style="{
                                        width: `${
                                            performance?.response_rate || 0
                                        }%`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-600"
                                    >Client Satisfaction</span
                                >
                                <span class="text-sm font-medium text-gray-900">
                                    {{ performance?.satisfaction_rate || 0 }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div
                                    class="bg-green-600 h-2 rounded-full"
                                    :style="{
                                        width: `${
                                            performance?.satisfaction_rate || 0
                                        }%`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-600"
                                    >Conversion Rate</span
                                >
                                <span class="text-sm font-medium text-gray-900">
                                    {{ performance?.conversion_rate || 0 }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div
                                    class="bg-purple-600 h-2 rounded-full"
                                    :style="{
                                        width: `${
                                            performance?.conversion_rate || 0
                                        }%`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-100">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600"
                                    >Total Commission</span
                                >
                                <span class="text-sm font-bold text-green-600">
                                    {{
                                        formatCurrency(
                                            performance?.total_commission || 0
                                        )
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Recent Activity
                    </h3>

                    <div v-if="recentActivities?.length" class="space-y-4">
                        <div
                            v-for="activity in recentActivities.slice(0, 5)"
                            :key="activity.id"
                            class="flex items-start gap-3"
                        >
                            <div
                                :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
                                    'bg-gray-100',
                                ]"
                            >
                                <component
                                    :is="getActivityIcon(activity.type)"
                                    :class="[
                                        'w-4 h-4',
                                        getActivityColor(activity.type),
                                    ]"
                                />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">
                                    {{ activity.description }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ formatDateTime(activity.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-4">
                        <ClockIcon class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                        <p class="text-sm text-gray-500">No recent activity</p>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Account Information
                    </h3>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"
                                >Member Since</span
                            >
                            <span class="text-sm font-medium text-gray-900">
                                {{ formatDate(broker.created_at) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"
                                >Last Login</span
                            >
                            <span class="text-sm font-medium text-gray-900">
                                {{
                                    broker.last_login_at
                                        ? formatDateTime(broker.last_login_at)
                                        : "Never"
                                }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"
                                >Profile Updated</span
                            >
                            <span class="text-sm font-medium text-gray-900">
                                {{ formatDate(broker.updated_at) }}
                            </span>
                        </div>

                        <div
                            v-if="broker.admin_notes"
                            class="pt-3 border-t border-gray-100"
                        >
                            <span class="text-sm text-gray-600 block mb-1"
                                >Admin Notes</span
                            >
                            <p
                                class="text-sm text-gray-900 bg-gray-50 p-2 rounded-lg"
                            >
                                {{ broker.admin_notes }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
