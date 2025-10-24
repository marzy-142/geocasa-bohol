<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('admin.users.index')"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <ArrowLeftIcon class="h-6 w-6" />
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            User Details
                        </h1>
                        <p class="text-gray-600">
                            {{ user.name }} - {{ formatRole(user.role) }}
                        </p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button
                        v-if="user.status === 'active'"
                        @click="suspendUser"
                        class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                        <ExclamationTriangleIcon class="w-4 h-4 mr-2" />
                        Suspend User
                    </button>
                    <button
                        v-else
                        @click="reactivateUser"
                        class="inline-flex items-center px-4 py-2 border border-green-300 rounded-md shadow-sm text-sm font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    >
                        <CheckCircleIcon class="w-4 h-4 mr-2" />
                        Reactivate User
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

            <!-- User Profile Card -->
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
                                {{ user.name }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ user.email }}
                            </p>
                            <div class="mt-2 flex space-x-4">
                                <span
                                    :class="getRoleBadgeClass(user.role)"
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{ formatRole(user.role) }}
                                </span>
                                <span
                                    :class="getStatusBadgeClass(user.status)"
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{ formatStatus(user.status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">
                                Phone Number
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ user.phone || "Not provided" }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">
                                Email Verified
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span
                                    :class="
                                        user.email_verified_at
                                            ? 'text-green-600'
                                            : 'text-red-600'
                                    "
                                    class="inline-flex items-center"
                                >
                                    <CheckCircleIcon
                                        v-if="user.email_verified_at"
                                        class="h-4 w-4 mr-1"
                                    />
                                    <XCircleIcon v-else class="h-4 w-4 mr-1" />
                                    {{
                                        user.email_verified_at
                                            ? "Verified"
                                            : "Not Verified"
                                    }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">
                                Member Since
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ formatDate(user.created_at) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">
                                Last Updated
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ formatDate(user.updated_at) }}
                            </dd>
                        </div>
                        <div v-if="user.suspended_at">
                            <dt class="text-sm font-medium text-gray-500">
                                Suspended Since
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ formatDate(user.suspended_at) }}
                            </dd>
                        </div>
                        <div v-if="user.suspension_reason">
                            <dt class="text-sm font-medium text-gray-500">
                                Suspension Reason
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ user.suspension_reason }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Statistics Cards -->
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
                                        Properties
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.totalProperties }}
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
                                <UserGroupIcon class="h-6 w-6 text-green-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Clients
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.totalClients }}
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
                                        Transactions
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.totalTransactions }}
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
                                        Total Commission
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        ₱{{
                                            formatNumber(stats.totalCommission)
                                        }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Properties Section -->
            <div
                v-if="user.properties && user.properties.length > 0"
                class="bg-white shadow rounded-lg"
            >
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Recent Properties
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
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Created
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="property in user.properties"
                                :key="property.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ property.title }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ property.city }},
                                        {{ property.province }}
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{ formatPropertyType(property.type) }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    ₱{{ formatNumber(property.price) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="
                                            getPropertyStatusBadgeClass(
                                                property.status
                                            )
                                        "
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ formatStatus(property.status) }}
                                    </span>
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

            <!-- Activity Logs -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Activity Logs
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flow-root">
                        <ul class="-mb-8">
                            <li
                                v-for="(log, index) in activityLogs"
                                :key="log.id"
                                class="relative pb-8"
                            >
                                <div
                                    v-if="index !== activityLogs.length - 1"
                                    class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                ></div>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span
                                            :class="
                                                getActivityIconClass(log.action)
                                            "
                                            class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                        >
                                            <component
                                                :is="
                                                    getActivityIcon(log.action)
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
                                                {{ log.description }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                Admin: {{ log.admin_name }}
                                            </p>
                                        </div>
                                        <div
                                            class="text-right text-sm whitespace-nowrap text-gray-500"
                                        >
                                            <time>
                                                {{
                                                    formatDateTime(
                                                        log.created_at
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
        </div>

        <!-- Suspend User Modal -->
        <div
            v-if="showSuspendModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        >
            <div
                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
            >
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Suspend User
                    </h3>
                    <form @submit.prevent="confirmSuspend">
                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Reason for Suspension
                            </label>
                            <textarea
                                v-model="suspendForm.reason"
                                rows="3"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Enter reason for suspension..."
                                required
                            ></textarea>
                        </div>
                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Duration (days) - Leave empty for permanent
                            </label>
                            <input
                                v-model="suspendForm.duration"
                                type="number"
                                min="1"
                                max="365"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="e.g., 7"
                            />
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button
                                type="button"
                                @click="showSuspendModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700"
                            >
                                Suspend User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, reactive } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    ArrowLeftIcon,
    UserIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowPathIcon,
    HomeIcon,
    UserGroupIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    ClockIcon,
    PencilIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    user: Object,
    activityLogs: Array,
    stats: Object,
});

// Reactive data
const showSuspendModal = ref(false);

const suspendForm = reactive({
    reason: "",
    duration: "",
});

// Methods
const suspendUser = () => {
    showSuspendModal.value = true;
};

const confirmSuspend = () => {
    router.post(route("admin.users.suspend", props.user.id), suspendForm, {
        onSuccess: () => {
            showSuspendModal.value = false;
            suspendForm.reason = "";
            suspendForm.duration = "";
        },
    });
};

const reactivateUser = () => {
    if (confirm("Are you sure you want to reactivate this user?")) {
        router.post(
            route("admin.users.reactivate", props.user.id),
            {},
            {
                onSuccess: () => {
                    // Success handled by Inertia
                },
            }
        );
    }
};

const refreshData = () => {
    router.reload();
};

const getRoleBadgeClass = (role) => {
    const classes = {
        admin: "bg-purple-100 text-purple-800",
        broker: "bg-blue-100 text-blue-800",
        client: "bg-green-100 text-green-800",
    };
    return classes[role] || "bg-gray-100 text-gray-800";
};

const getStatusBadgeClass = (status) => {
    const classes = {
        active: "bg-green-100 text-green-800",
        suspended: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getPropertyStatusBadgeClass = (status) => {
    const classes = {
        active: "bg-green-100 text-green-800",
        pending: "bg-yellow-100 text-yellow-800",
        sold: "bg-blue-100 text-blue-800",
        inactive: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const formatRole = (role) => {
    return role.charAt(0).toUpperCase() + role.slice(1);
};

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatPropertyType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
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

const formatNumber = (number) => {
    return new Intl.NumberFormat("en-US").format(number || 0);
};

const getActivityIconClass = (action) => {
    const classes = {
        "Account Created": "bg-green-100 text-green-600",
        "Profile Updated": "bg-blue-100 text-blue-600",
    };
    return classes[action] || "bg-gray-100 text-gray-600";
};

const getActivityIcon = (action) => {
    const icons = {
        "Account Created": CheckCircleIcon,
        "Profile Updated": PencilIcon,
    };
    return icons[action] || ClockIcon;
};
</script>
