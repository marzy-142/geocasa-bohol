<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
        <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        User Management
                    </h1>
                    <p class="text-gray-600">
                        Manage all users, roles, and permissions
                    </p>
                </div>
                <div class="flex space-x-3">
                    <button
                        @click="exportUsers"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
                        Export Users
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
                                        Total Users
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                {{ stats.total }}
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
                                        Brokers
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.brokers }}
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
                                <UserIcon class="h-6 w-6 text-purple-400" />
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
                                        {{ stats.clients }}
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
                                <ExclamationTriangleIcon
                                    class="h-6 w-6 text-red-400"
                        />
                    </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Suspended
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.suspended }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow rounded-lg p-6">
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Role
                        </label>
                        <select
                            v-model="filters.role"
                            @change="applyFilters"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                        >
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="broker">Broker</option>
                            <option value="client">Client</option>
                        </select>
                </div>

                <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Status
                        </label>
                    <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>

                <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Approval Status
                        </label>
                    <select
                            v-model="filters.approval_status"
                            @change="applyFilters"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                        >
                            <option value="">All Approval</option>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                    </select>
                </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Search
                        </label>
                        <input
                            v-model="filters.search"
                            @input="debounceSearch"
                            type="text"
                            placeholder="Search users..."
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        />
            </div>
        </div>

                <div class="mt-4 flex justify-between">
                    <button
                        @click="clearFilters"
                        class="text-sm text-gray-500 hover:text-gray-700"
                    >
                        Clear Filters
                    </button>
                    <div class="flex space-x-2">
                        <button
            v-if="selectedUsers.length > 0"
                            @click="showBulkActions = true"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >
                            <UsersIcon class="w-4 h-4 mr-2" />
                            Bulk Actions ({{ selectedUsers.length }})
                        </button>
                </div>
            </div>
        </div>

        <!-- Users Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Users
                        </h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">
                                {{ users.total }} total users
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="
                                                selectedUsers.length ===
                                                    users.data.length &&
                                                users.data.length > 0
                                            "
                                            @change="toggleAllUsers"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                        />
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        User
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Role
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Properties
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Joined
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                <input
                    type="checkbox"
                                            :value="user.id"
                                            v-model="selectedUsers"
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10"
                                            >
                                                <div
                                                    class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center"
                                                >
                                                    <UserIcon
                                                        class="h-5 w-5 text-gray-500"
                                                    />
                                                </div>
                    </div>
                                            <div class="ml-4">
                                                <div
                                                    class="text-sm font-medium text-gray-900"
                                                >
                                                    {{ user.name }}
                        </div>
                                                <div
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{ user.email }}
                        </div>
                    </div>
                </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                <span
                                            :class="
                                                getRoleBadgeClass(user.role)
                                            "
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ formatRole(user.role) }}
                </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                <span
                                            :class="
                                                getStatusBadgeClass(user.status)
                                            "
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ formatStatus(user.status) }}
                </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                    >
                                        {{ user.properties_count || 0 }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                    >
                                        <div class="flex space-x-2">
                                            <Link
                                                :href="
                                                    route(
                                                        'admin.users.show',
                                                        user.id
                                                    )
                                                "
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                View
                                            </Link>
                                            <button
                                                v-if="user.status === 'active'"
                                                @click="suspendUser(user)"
                                                class="text-red-600 hover:text-red-900"
                    >
                        Suspend
                                            </button>
                                            <button
                                                v-else
                                                @click="reactivateUser(user)"
                                                class="text-green-600 hover:text-green-900"
                    >
                        Reactivate
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                </div>

        <!-- Pagination -->
        <div v-if="users.links" class="mt-6">
                        <nav class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <Link
                                    v-if="users.prev_page_url"
                                    :href="users.prev_page_url"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="users.next_page_url"
                                    :href="users.next_page_url"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Next
                                </Link>
                            </div>
                            <div
                                class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing
                                        <span class="font-medium">{{
                                            users.from
                                        }}</span>
                                        to
                                        <span class="font-medium">{{
                                            users.to
                                        }}</span>
                                        of
                                        <span class="font-medium">{{
                                            users.total
                                        }}</span>
                                        results
                                    </p>
                </div>
                                <div>
                                    <nav
                                        class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                    >
                                        <template
                                            v-for="(link, index) in users.links"
                                            :key="index"
                                        >
                                            <Link
                                                v-if="link.url"
                            :href="link.url"
                            :class="[
                                link.active
                                                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                ]"
                                            >
                                                <span
                                                    v-html="link.label"
                                                ></span>
                                            </Link>
                                            <span
                                                v-else
                                                :class="[
                                                    'relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700',
                                                ]"
                                            >
                                                <span
                            v-html="link.label"
                                                ></span>
                                            </span>
                    </template>
                                    </nav>
                                </div>
                            </div>
                        </nav>
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

        <!-- Bulk Actions Modal -->
        <div
            v-if="showBulkActions"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        >
            <div
                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
            >
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Bulk Actions
                    </h3>
                    <form @submit.prevent="confirmBulkAction">
                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Action
                            </label>
                            <select
                                v-model="bulkForm.action"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                required
                            >
                                <option value="">Select Action</option>
                                <option value="suspend">Suspend Users</option>
                                <option value="reactivate">
                                    Reactivate Users
                                </option>
                                <option value="delete">Delete Users</option>
                            </select>
                        </div>
                        <div v-if="bulkForm.action === 'suspend'" class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Reason for Suspension
                    </label>
                    <textarea
                        v-model="bulkForm.reason"
                        rows="3"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        placeholder="Enter reason for suspension..."
                                required
                    ></textarea>
                </div>
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">
                                This action will affect
                                {{ selectedUsers.length }} users.
                            </p>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button
                                type="button"
                                @click="showBulkActions = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200"
                    >
                        Cancel
                            </button>
                            <button
                                type="submit"
                                :class="[
                                    'px-4 py-2 text-sm font-medium text-white border border-transparent rounded-md',
                                    bulkForm.action === 'delete'
                                        ? 'bg-red-600 hover:bg-red-700'
                                        : 'bg-blue-600 hover:bg-blue-700',
                                ]"
                            >
                                {{ formatBulkAction(bulkForm.action) }}
                            </button>
                        </div>
                    </form>
                </div>
                </div>
            </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    UsersIcon,
    UserGroupIcon,
    UserIcon,
    ExclamationTriangleIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    users: Object,
    filters: Object,
    stats: Object,
});

// Reactive data
const selectedUsers = ref([]);
const showSuspendModal = ref(false);
const showBulkActions = ref(false);
const searchTimeout = ref(null);
const currentUser = ref(null);

const filters = reactive({
    role: props.filters.role || "",
    status: props.filters.status || "",
    approval_status: props.filters.approval_status || "",
    search: props.filters.search || "",
});

const suspendForm = reactive({
    reason: "",
    duration: "",
});

const bulkForm = reactive({
    action: "",
    reason: "",
});

// Methods
const applyFilters = () => {
    router.get(route("admin.users.index"), filters, {
        preserveState: true,
        replace: true,
    });
};

const debounceSearch = () => {
    clearTimeout(searchTimeout.value);
    searchTimeout.value = setTimeout(() => {
        applyFilters();
    }, 500);
};

const clearFilters = () => {
    Object.keys(filters).forEach((key) => {
        filters[key] = "";
    });
    applyFilters();
};

const toggleAllUsers = (event) => {
    if (event.target.checked) {
        selectedUsers.value = props.users.data.map((user) => user.id);
    } else {
        selectedUsers.value = [];
    }
};

const suspendUser = (user) => {
    currentUser.value = user;
    showSuspendModal.value = true;
};

const confirmSuspend = () => {
    router.post(
        route("admin.users.suspend", currentUser.value.id),
        suspendForm,
        {
            onSuccess: () => {
                showSuspendModal.value = false;
                suspendForm.reason = "";
                suspendForm.duration = "";
                currentUser.value = null;
            },
        }
    );
};

const reactivateUser = (user) => {
    if (confirm("Are you sure you want to reactivate this user?")) {
        router.post(
            route("admin.users.reactivate", user.id),
            {},
            {
                onSuccess: () => {
                    // Success handled by Inertia
                },
            }
        );
    }
};

const confirmBulkAction = () => {
    const formData = {
        action: bulkForm.action,
        user_ids: selectedUsers.value,
        reason: bulkForm.reason,
    };

    router.post(route("admin.users.bulk-actions"), formData, {
        onSuccess: () => {
            showBulkActions.value = false;
            selectedUsers.value = [];
            bulkForm.action = "";
            bulkForm.reason = "";
        },
    });
};

const exportUsers = () => {
    const params = new URLSearchParams(filters);
    window.open(route("admin.users.export") + "?" + params.toString());
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

const formatRole = (role) => {
    return role.charAt(0).toUpperCase() + role.slice(1);
};

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const formatBulkAction = (action) => {
    const actions = {
        suspend: "Suspend Users",
        reactivate: "Reactivate Users",
        delete: "Delete Users",
    };
    return actions[action] || action;
};
</script>
