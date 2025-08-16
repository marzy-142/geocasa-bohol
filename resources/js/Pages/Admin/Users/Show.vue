<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import Modal from "@/Components/Modal.vue";
import { ref } from "vue";
import {
    UserIcon,
    EnvelopeIcon,
    CalendarIcon,
    ShieldCheckIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowLeftIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    user: Object,
    activityLogs: {
        type: Array,
        default: () => []
    },
    stats: Object,
});

// Suspension modal
const showSuspensionModal = ref(false);
const suspensionForm = useForm({
    reason: "",
    notes: "",
});

// Reactivation modal
const showReactivationModal = ref(false);
const reactivationForm = useForm({
    notes: "",
});

const suspendUser = () => {
    suspensionForm.post(route("admin.users.suspend", props.user.id), {
        onSuccess: () => {
            showSuspensionModal.value = false;
        },
    });
};

const reactivateUser = () => {
    reactivationForm.post(route("admin.users.reactivate", props.user.id), {
        onSuccess: () => {
            showReactivationModal.value = false;
        },
    });
};

const getStatusBadge = (status) => {
    if (!status) return "bg-gray-100 text-gray-800";
    const badges = {
        active: "bg-green-100 text-green-800",
        suspended: "bg-red-100 text-red-800",
        pending: "bg-yellow-100 text-yellow-800",
    };
    return badges[status] || "bg-gray-100 text-gray-800";
};

const getRoleBadge = (role) => {
    if (!role) return "bg-gray-100 text-gray-800";
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
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <Head :title="`${user.name} - User Details`" />

    <ModernDashboardLayout>
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <ModernButton
                    variant="ghost"
                    :href="route('admin.users.index')"
                    :icon="ArrowLeftIcon"
                >
                    Back to Users
                </ModernButton>
            </div>

            <div
                class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6"
            >
                <div class="flex items-start gap-6">
                    <!-- User Avatar -->
                    <div
                        class="w-20 h-20 bg-primary-100 rounded-2xl flex items-center justify-center"
                    >
                        <UserIcon class="w-10 h-10 text-primary-600" />
                    </div>

                    <!-- User Info -->
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                            {{ user.name }}
                        </h1>
                        <div class="flex items-center gap-4 mb-3">
                            <span
                                :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                    getStatusBadge(user.status),
                                ]"
                            >
                                {{
                                    user.status.charAt(0).toUpperCase() +
                                    user.status.slice(1)
                                }}
                            </span>
                            <span
                                :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                    getRoleBadge(user.role),
                                ]"
                            >
                                {{
                                    user.role.charAt(0).toUpperCase() +
                                    user.role.slice(1)
                                }}
                            </span>
                        </div>
                        <div
                            class="flex items-center gap-6 text-sm text-gray-600"
                        >
                            <div class="flex items-center gap-2">
                                <EnvelopeIcon class="w-4 h-4" />
                                {{ user.email }}
                            </div>
                            <div class="flex items-center gap-2">
                                <CalendarIcon class="w-4 h-4" />
                                Joined {{ formatDate(user.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <ModernButton
                        v-if="user.status === 'active'"
                        variant="danger"
                        @click="showSuspensionModal = true"
                        :icon="XCircleIcon"
                    >
                        Suspend User
                    </ModernButton>
                    <ModernButton
                        v-else-if="user.status === 'suspended'"
                        variant="accent"
                        @click="showReactivationModal = true"
                        :icon="CheckCircleIcon"
                    >
                        Reactivate User
                    </ModernButton>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- User Details -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        User Details
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Full Name</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ user.name }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Email Address</label
                            >
                            <div
                                class="text-sm text-gray-900 flex items-center gap-2"
                            >
                                {{ user.email }}
                                <CheckCircleIcon
                                    v-if="user.email_verified_at"
                                    class="w-4 h-4 text-green-500"
                                />
                                <XCircleIcon
                                    v-else
                                    class="w-4 h-4 text-red-500"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Role</label
                            >
                            <div class="text-sm text-gray-900">
                                {{
                                    user.role.charAt(0).toUpperCase() +
                                    user.role.slice(1)
                                }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Status</label
                            >
                            <div class="text-sm text-gray-900">
                                {{
                                    user.status.charAt(0).toUpperCase() +
                                    user.status.slice(1)
                                }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Member Since</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ formatDate(user.created_at) }}
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Last Updated</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ formatDate(user.updated_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity Log -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        Recent Activity
                    </h2>

                    <div v-if="activityLogs && Array.isArray(activityLogs) && activityLogs.length > 0" class="space-y-4">
                        <div
                            v-for="log in activityLogs"
                            :key="log.id"
                            class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl"
                        >
                            <div
                                class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0"
                            >
                                <ShieldCheckIcon class="w-4 h-4 text-blue-600" />
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-gray-900">
                                        {{ log.action }}
                                    </h4>
                                    <span class="text-sm text-gray-500">
                                        {{ formatDate(log.created_at) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ log.description }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    by {{ log.admin_name || 'System' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <ShieldCheckIcon class="w-8 h-8 text-gray-400" />
                        </div>
                        <p class="text-gray-500">No activity logs available</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Stats -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        User Statistics
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"
                                >Properties Listed</span
                            >
                            <span class="text-sm font-medium text-gray-900">{{
                                stats.properties_count || 0
                            }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"
                                >Inquiries Sent</span
                            >
                            <span class="text-sm font-medium text-gray-900">{{
                                stats.inquiries_count || 0
                            }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"
                                >Transactions</span
                            >
                            <span class="text-sm font-medium text-gray-900">{{
                                stats.transactions_count || 0
                            }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"
                                >Last Login</span
                            >
                            <span class="text-sm font-medium text-gray-900">
                                {{
                                    user.last_login_at
                                        ? formatDate(user.last_login_at)
                                        : "Never"
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Suspension Info -->
                <div
                    v-if="user.status === 'suspended'"
                    class="bg-red-50 border border-red-200 rounded-2xl p-6"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <ExclamationTriangleIcon class="w-5 h-5 text-red-600" />
                        <h3 class="text-lg font-semibold text-red-900">
                            Suspended Account
                        </h3>
                    </div>

                    <div class="space-y-2">
                        <div>
                            <span class="text-sm font-medium text-red-700"
                                >Suspended At:</span
                            >
                            <span class="text-sm text-red-600 ml-2">{{
                                formatDate(user.suspended_at)
                            }}</span>
                        </div>

                        <div v-if="user.suspension_reason">
                            <span class="text-sm font-medium text-red-700"
                                >Reason:</span
                            >
                            <div class="text-sm text-red-600 mt-1">
                                {{ user.suspension_reason }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suspension Modal -->
        <!-- Around line 287-288 (existing modal opening) -->
        <Modal :show="showSuspensionModal" @close="showSuspensionModal = false">
            <div class="p-6">
                <!-- Previous modal header content (lines 290-297) -->

                <!-- Replace from this point (existing problematic select/textarea) -->
                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Reason for suspension *
                        </label>
                        <select
                            id="suspension_reason"
                            v-model="suspensionForm.reason"
                            class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                            required
                        >
                            <option value="" disabled>Select a reason</option>
                            <option value="violation">
                                Violation of Terms
                            </option>
                            <option value="inactivity">Inactivity</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Additional Notes
                        </label>
                        <textarea
                            id="suspension_notes"
                            v-model="suspensionForm.notes"
                            rows="3"
                            class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Enter additional details..."
                        ></textarea>
                    </div>
                </div>
                <!-- End replacement section -->

                <!-- Keep rest of modal (buttons section starting around line 310) -->
                <div class="flex items-center justify-end gap-3 mt-6">
                    <ModernButton
                        variant="ghost"
                        @click="showSuspensionModal = false"
                    >
                        Cancel
                    </ModernButton>
                    <ModernButton
                        variant="danger"
                        @click="suspendUser"
                        :loading="suspensionForm.processing"
                        :disabled="!suspensionForm.reason"
                    >
                        Suspend User
                    </ModernButton>
                </div>
            </div>
        </Modal>

        <!-- Reactivation Modal -->
        <Modal
            :show="showReactivationModal"
            @close="showReactivationModal = false"
        >
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <CheckCircleIcon class="w-6 h-6 text-green-600" />
                    <h3 class="text-lg font-medium text-gray-900">
                        Reactivate User Account
                    </h3>
                </div>

                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to reactivate
                    <strong>{{ user.name }}</strong
                    >? This will restore their account access.
                </p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reactivation Notes
                    </label>
                    <textarea
                        v-model="reactivationForm.notes"
                        rows="3"
                        class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                        placeholder="Enter reason for reactivation..."
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <ModernButton
                        variant="ghost"
                        @click="showReactivationModal = false"
                    >
                        Cancel
                    </ModernButton>
                    <ModernButton
                        variant="accent"
                        @click="reactivateUser"
                        :loading="reactivationForm.processing"
                    >
                        Reactivate User
                    </ModernButton>
                </div>
            </div>
        </Modal>
    </ModernDashboardLayout>
</template>
