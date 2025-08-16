<script setup>
import { Head, useForm, router, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernTable from "@/Components/ModernTable.vue";
import ModernButton from "@/Components/ModernButton.vue";
import ModernInput from "@/Components/ModernInput.vue";
import Modal from "@/Components/Modal.vue";
import { ref, computed } from "vue";
import {
    UserIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    EllipsisVerticalIcon,
    CheckCircleIcon,
    XCircleIcon,
    TrashIcon,
    UserPlusIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    users: Object,
    filters: Object,
    stats: Object,
});

// Search and filter state
const searchForm = useForm({
    search: props.filters.search || "",
    status: props.filters.status || "",
    role: props.filters.role || "",
    sort: props.filters.sort || "created_at",
    direction: props.filters.direction || "desc",
});

// Bulk actions
const selectedUsers = ref([]);
const showBulkModal = ref(false);
const bulkAction = ref("");
const bulkForm = useForm({
    user_ids: [],
    action: "",
    reason: "",
});

// Table columns
const columns = [
    { key: "select", label: "", sortable: false },
    { key: "name", label: "User", sortable: true },
    { key: "email", label: "Email", sortable: true },
    { key: "role", label: "Role", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "created_at", label: "Joined", sortable: true },
    { key: "actions", label: "Actions", sortable: false },
];

// Methods
const search = () => {
    searchForm.get(route("admin.users.index"), {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleSort = (sort) => {
    searchForm.sort = sort.field;
    searchForm.direction = sort.direction;
    search();
};

const toggleUserSelection = (userId) => {
    const index = selectedUsers.value.indexOf(userId);
    if (index > -1) {
        selectedUsers.value.splice(index, 1);
    } else {
        selectedUsers.value.push(userId);
    }
};

const toggleAllUsers = () => {
    if (selectedUsers.value.length === props.users.data.length) {
        selectedUsers.value = [];
    } else {
        selectedUsers.value = props.users.data.map((user) => user.id);
    }
};

const openBulkModal = (action) => {
    if (selectedUsers.value.length === 0) return;
    bulkAction.value = action;
    showBulkModal.value = true;
    bulkForm.reset();
    bulkForm.user_ids = selectedUsers.value;
    bulkForm.action = action;
};

const executeBulkAction = () => {
    bulkForm.post(route("admin.users.bulk-action"), {
        onSuccess: () => {
            showBulkModal.value = false;
            selectedUsers.value = [];
        },
    });
};

const suspendUser = (userId) => {
    router.post(route("admin.users.suspend", userId));
};

const reactivateUser = (userId) => {
    router.post(route("admin.users.reactivate", userId));
};

const getStatusBadge = (status) => {
    const badges = {
        active: "bg-green-100 text-green-800",
        suspended: "bg-red-100 text-red-800",
        pending: "bg-yellow-100 text-yellow-800",
    };
    return badges[status] || "bg-gray-100 text-gray-800";
};

const getRoleBadge = (role) => {
    const badges = {
        admin: "bg-purple-100 text-purple-800",
        broker: "bg-blue-100 text-blue-800",
        user: "bg-gray-100 text-gray-800",
    };
    return badges[role] || "bg-gray-100 text-gray-800";
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const allSelected = computed(() => {
    return (
        selectedUsers.value.length === props.users.data.length &&
        props.users.data.length > 0
    );
});

const someSelected = computed(() => {
    return (
        selectedUsers.value.length > 0 &&
        selectedUsers.value.length < props.users.data.length
    );
});
</script>

<template>
    <Head title="User Management - Admin" />

    <ModernDashboardLayout>
        <!-- Header -->
        <div class="mb-8">
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                        User Management
                    </h1>
                    <p class="text-neutral-600">
                        Manage user accounts, permissions, and activity
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-3 gap-4">
                        <div
                            class="bg-white rounded-xl border border-gray-100 px-4 py-3"
                        >
                            <div class="text-sm text-gray-500">Total Users</div>
                            <div class="text-xl font-bold text-blue-600">
                                {{ stats.total }}
                            </div>
                        </div>
                        <div
                            class="bg-white rounded-xl border border-gray-100 px-4 py-3"
                        >
                            <div class="text-sm text-gray-500">Active</div>
                            <div class="text-xl font-bold text-green-600">
                                {{ stats.active }}
                            </div>
                        </div>
                        <div
                            class="bg-white rounded-xl border border-gray-100 px-4 py-3"
                        >
                            <div class="text-sm text-gray-500">Suspended</div>
                            <div class="text-xl font-bold text-red-600">
                                {{ stats.suspended }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <div class="relative">
                        <MagnifyingGlassIcon
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                        />
                        <ModernInput
                            v-model="searchForm.search"
                            type="text"
                            placeholder="Search users by name or email..."
                            class="pl-10"
                            @keyup.enter="search"
                        />
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <select
                        v-model="searchForm.status"
                        @change="search"
                        class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <!-- Role Filter -->
                <div>
                    <select
                        v-model="searchForm.role"
                        @change="search"
                        class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="broker">Broker</option>
                        <option value="user">User</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div
            v-if="selectedUsers.length > 0"
            class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <CheckCircleIcon class="w-5 h-5 text-blue-600" />
                    <span class="text-sm font-medium text-blue-900">
                        {{ selectedUsers.length }} user(s) selected
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <ModernButton
                        variant="secondary"
                        size="sm"
                        @click="openBulkModal('suspend')"
                    >
                        Suspend Selected
                    </ModernButton>
                    <ModernButton
                        variant="accent"
                        size="sm"
                        @click="openBulkModal('reactivate')"
                    >
                        Reactivate Selected
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <ModernTable
            :columns="columns"
            :data="users.data"
            :current-sort="{
                field: searchForm.sort,
                direction: searchForm.direction,
            }"
            @sort="handleSort"
        >
            <!-- Select Column -->
            <template #cell-select="{ item }">
                <input
                    type="checkbox"
                    :checked="selectedUsers.includes(item.id)"
                    @change="toggleUserSelection(item.id)"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
            </template>

            <!-- User Column -->
            <template #cell-name="{ item }">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center"
                    >
                        <UserIcon class="w-5 h-5 text-primary-600" />
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">
                            {{ item.name }}
                        </div>
                        <div class="text-sm text-gray-500">
                            ID: {{ item.id }}
                        </div>
                    </div>
                </div>
            </template>

            <!-- Email Column -->
            <template #cell-email="{ item }">
                <div class="text-sm text-gray-900">{{ item.email }}</div>
                <div
                    v-if="item.email_verified_at"
                    class="text-xs text-green-600 flex items-center gap-1"
                >
                    <CheckCircleIcon class="w-3 h-3" />
                    Verified
                </div>
                <div v-else class="text-xs text-red-600">Not verified</div>
            </template>

            <!-- Role Column -->
            <template #cell-role="{ item }">
                <span
                    :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        getRoleBadge(item.role),
                    ]"
                >
                    {{
                        item.role
                            ? item.role.charAt(0).toUpperCase() +
                              item.role.slice(1)
                            : "N/A"
                    }}
                </span>
            </template>

            <!-- Status Column -->
            <template #cell-status="{ item }">
                <span
                    :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        getStatusBadge(item.status),
                    ]"
                >
                    {{
                        item.status
                            ? item.status.charAt(0).toUpperCase() +
                              item.status.slice(1)
                            : "N/A"
                    }}
                </span>
            </template>

            <!-- Created At Column -->
            <template #cell-created_at="{ item }">
                <div class="text-sm text-gray-900">
                    {{ formatDate(item.created_at) }}
                </div>
            </template>

            <!-- Actions Column -->
            <template #cell-actions="{ item }">
                <div class="flex items-center gap-2">
                    <ModernButton
                        v-if="item.status === 'active'"
                        variant="outline"
                        size="sm"
                        @click="suspendUser(item.id)"
                    >
                        Suspend
                    </ModernButton>
                    <ModernButton
                        v-else-if="item.status === 'suspended'"
                        variant="accent"
                        size="sm"
                        @click="reactivateUser(item.id)"
                    >
                        Reactivate
                    </ModernButton>
                    <ModernButton
                        variant="ghost"
                        size="sm"
                        :href="route('admin.users.show', item.id)"
                    >
                        View
                    </ModernButton>
                </div>
            </template>
        </ModernTable>

        <!-- Pagination -->
        <div v-if="users.links" class="mt-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing {{ users.from }} to {{ users.to }} of
                    {{ users.total }} results
                </div>
                <div class="flex items-center gap-2">
                    <template v-for="link in users.links" :key="link.label">
                        <component
                            :is="link.url ? 'Link' : 'span'"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 text-sm rounded-lg transition-colors',
                                link.active
                                    ? 'bg-primary-500 text-white'
                                    : link.url
                                    ? 'text-gray-700 hover:bg-gray-100'
                                    : 'text-gray-400 cursor-not-allowed',
                            ]"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Bulk Action Modal -->
        <Modal :show="showBulkModal" @close="showBulkModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{
                        bulkAction === "suspend"
                            ? "Suspend Users"
                            : "Reactivate Users"
                    }}
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to {{ bulkAction }}
                    {{ selectedUsers.length }} user(s)?
                </p>

                <div v-if="bulkAction === 'suspend'" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for suspension
                    </label>
                    <textarea
                        v-model="bulkForm.reason"
                        rows="3"
                        class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="Enter reason for suspension..."
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <ModernButton
                        variant="ghost"
                        @click="showBulkModal = false"
                    >
                        Cancel
                    </ModernButton>
                    <ModernButton
                        :variant="
                            bulkAction === 'suspend' ? 'danger' : 'accent'
                        "
                        @click="executeBulkAction"
                        :loading="bulkForm.processing"
                    >
                        {{
                            bulkAction === "suspend"
                                ? "Suspend Users"
                                : "Reactivate Users"
                        }}
                    </ModernButton>
                </div>
            </div>
        </Modal>
    </ModernDashboardLayout>
</template>
