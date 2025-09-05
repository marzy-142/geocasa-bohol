<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import ModernInput from "@/Components/ModernInput.vue";
import ModernTextarea from "@/Components/ModernTextarea.vue";
import ModernSelect from "@/Components/ModernSelect.vue";
import { ref, computed } from "vue";
import {
    UserIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    PauseCircleIcon,
    PlayCircleIcon,
    ClockIcon,
    ShieldCheckIcon,
    ShieldExclamationIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    broker: Object,
    statusHistory: Array,
});

// Form for status change
const statusForm = useForm({
    status: props.broker.application_status,
    reason: "",
    admin_notes: "",
    effective_date: new Date().toISOString().split("T")[0],
    notify_broker: true,
});

// Status options
const statusOptions = [
    {
        value: "active",
        label: "Active",
        description: "Broker can access all features and list properties",
    },
    {
        value: "inactive",
        label: "Inactive",
        description: "Broker account is temporarily disabled",
    },
    {
        value: "suspended",
        label: "Suspended",
        description: "Broker account is suspended due to violations",
    },
    {
        value: "pending",
        label: "Pending Review",
        description: "Broker application is under review",
    },
    {
        value: "under_review",
        label: "Under Investigation",
        description: "Broker account is being investigated",
    },
    {
        value: "terminated",
        label: "Terminated",
        description: "Broker account is permanently terminated",
    },
];

// Reason templates based on status
const reasonTemplates = {
    active: [
        "Application approved after review",
        "Suspension lifted - issues resolved",
        "Account reactivated upon request",
        "Verification completed successfully",
    ],
    inactive: [
        "Broker requested account deactivation",
        "Temporary suspension for maintenance",
        "Account inactive due to inactivity",
        "Pending document updates",
    ],
    suspended: [
        "Violation of terms and conditions",
        "Fraudulent activity detected",
        "Multiple client complaints",
        "Failure to maintain license requirements",
        "Unprofessional conduct reported",
    ],
    pending: [
        "Initial application submitted",
        "Additional documentation required",
        "Awaiting license verification",
        "Background check in progress",
    ],
    under_review: [
        "Investigating client complaints",
        "Reviewing transaction irregularities",
        "Verifying license authenticity",
        "Compliance audit in progress",
    ],
    terminated: [
        "Severe violation of platform rules",
        "Fraudulent activities confirmed",
        "License revoked by regulatory body",
        "Repeated policy violations",
    ],
};

// Computed properties
const currentStatusInfo = computed(() => {
    const statusMap = {
        active: {
            icon: CheckCircleIcon,
            class: "text-green-600 bg-green-100",
            label: "Active",
            description: "Account is fully operational",
        },
        inactive: {
            icon: PauseCircleIcon,
            class: "text-gray-600 bg-gray-100",
            label: "Inactive",
            description: "Account is temporarily disabled",
        },
        suspended: {
            icon: XCircleIcon,
            class: "text-red-600 bg-red-100",
            label: "Suspended",
            description: "Account is suspended",
        },
        pending: {
            icon: ClockIcon,
            class: "text-yellow-600 bg-yellow-100",
            label: "Pending Review",
            description: "Application under review",
        },
        under_review: {
            icon: ExclamationTriangleIcon,
            class: "text-orange-600 bg-orange-100",
            label: "Under Investigation",
            description: "Account being investigated",
        },
        terminated: {
            icon: ShieldExclamationIcon,
            class: "text-red-800 bg-red-200",
            label: "Terminated",
            description: "Account permanently terminated",
        },
    };
    return statusMap[props.broker.application_status] || statusMap.pending;
});

const selectedStatusInfo = computed(() => {
    return statusOptions.find((option) => option.value === statusForm.status);
});

const availableReasons = computed(() => {
    return reasonTemplates[statusForm.status] || [];
});

const isStatusChanged = computed(() => {
    return statusForm.status !== props.broker.application_status;
});

// Methods
const updateStatus = () => {
    statusForm.post(route("admin.brokers.update-status", props.broker.id), {
        onSuccess: () => {
            // Reset form or show success message
            statusForm.reset("reason", "admin_notes");
        },
        onError: (errors) => {
            console.error("Status update failed:", errors);
        },
    });
};

const selectReasonTemplate = (reason) => {
    statusForm.reason = reason;
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

const getStatusBadge = (status) => {
    const badges = {
        active: "bg-green-100 text-green-800",
        inactive: "bg-gray-100 text-gray-800",
        suspended: "bg-red-100 text-red-800",
        pending: "bg-yellow-100 text-yellow-800",
        under_review: "bg-orange-100 text-orange-800",
        terminated: "bg-red-200 text-red-900",
    };
    return badges[status] || "bg-gray-100 text-gray-800";
};

const getStatusIcon = (status) => {
    const icons = {
        active: CheckCircleIcon,
        inactive: PauseCircleIcon,
        suspended: XCircleIcon,
        pending: ClockIcon,
        under_review: ExclamationTriangleIcon,
        terminated: ShieldExclamationIcon,
    };
    return icons[status] || ClockIcon;
};
</script>

<template>
    <ModernDashboardLayout>
        <Head :title="`${broker.name} - Status Control`" />

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

            <div class="flex items-start gap-6">
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
                        {{ broker.name }} - Status Control
                    </h1>
                    <div class="flex items-center gap-4 mb-2">
                        <span class="text-sm text-gray-600">{{
                            broker.email
                        }}</span>
                        <span class="text-sm text-gray-600"
                            >ID: {{ broker.id }}</span
                        >
                    </div>
                    <div class="flex items-center gap-2">
                        <component
                            :is="currentStatusInfo.icon"
                            class="w-5 h-5"
                            :class="currentStatusInfo.class.split(' ')[0]"
                        />
                        <span
                            :class="[
                                'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                currentStatusInfo.class.split(' ')[1] +
                                    ' ' +
                                    currentStatusInfo.class.split(' ')[0],
                            ]"
                        >
                            {{ currentStatusInfo.label }}
                        </span>
                        <span class="text-sm text-gray-600">{{
                            currentStatusInfo.description
                        }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Status Change Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">
                        Change Broker Status
                    </h2>

                    <form @submit.prevent="updateStatus" class="space-y-6">
                        <!-- Status Selection -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                New Status
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div
                                    v-for="option in statusOptions"
                                    :key="option.value"
                                    class="relative"
                                >
                                    <input
                                        :id="`status-${option.value}`"
                                        v-model="statusForm.status"
                                        :value="option.value"
                                        type="radio"
                                        class="sr-only"
                                    />
                                    <label
                                        :for="`status-${option.value}`"
                                        :class="[
                                            'block p-4 border-2 rounded-xl cursor-pointer transition-all',
                                            statusForm.status === option.value
                                                ? 'border-primary-500 bg-primary-50'
                                                : 'border-gray-200 hover:border-gray-300',
                                        ]"
                                    >
                                        <div class="flex items-center gap-3">
                                            <component
                                                :is="
                                                    getStatusIcon(option.value)
                                                "
                                                :class="[
                                                    'w-5 h-5',
                                                    statusForm.status ===
                                                    option.value
                                                        ? 'text-primary-600'
                                                        : 'text-gray-400',
                                                ]"
                                            />
                                            <div>
                                                <div
                                                    class="font-medium text-gray-900"
                                                >
                                                    {{ option.label }}
                                                </div>
                                                <div
                                                    class="text-sm text-gray-600"
                                                >
                                                    {{ option.description }}
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div
                                v-if="statusForm.errors.status"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ statusForm.errors.status }}
                            </div>
                        </div>

                        <!-- Reason Selection -->
                        <div v-if="isStatusChanged">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Reason for Status Change
                            </label>

                            <!-- Quick Reason Templates -->
                            <div v-if="availableReasons.length" class="mb-3">
                                <p class="text-sm text-gray-600 mb-2">
                                    Quick reasons:
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="reason in availableReasons"
                                        :key="reason"
                                        type="button"
                                        @click="selectReasonTemplate(reason)"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-sm bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors"
                                    >
                                        {{ reason }}
                                    </button>
                                </div>
                            </div>

                            <ModernTextarea
                                v-model="statusForm.reason"
                                placeholder="Enter the reason for this status change..."
                                rows="3"
                                :error="statusForm.errors.reason"
                                required
                            />
                        </div>

                        <!-- Admin Notes -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Admin Notes (Optional)
                            </label>
                            <ModernTextarea
                                v-model="statusForm.admin_notes"
                                placeholder="Add any additional notes for internal reference..."
                                rows="3"
                                :error="statusForm.errors.admin_notes"
                            />
                        </div>

                        <!-- Effective Date -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Effective Date
                            </label>
                            <ModernInput
                                v-model="statusForm.effective_date"
                                type="date"
                                :error="statusForm.errors.effective_date"
                                required
                            />
                        </div>

                        <!-- Notification Option -->
                        <div class="flex items-center gap-3">
                            <input
                                id="notify-broker"
                                v-model="statusForm.notify_broker"
                                type="checkbox"
                                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                            />
                            <label
                                for="notify-broker"
                                class="text-sm text-gray-700"
                            >
                                Send notification email to broker about this
                                status change
                            </label>
                        </div>

                        <!-- Action Buttons -->
                        <div
                            class="flex items-center gap-4 pt-4 border-t border-gray-100"
                        >
                            <ModernButton
                                type="submit"
                                variant="primary"
                                :disabled="
                                    statusForm.processing || !isStatusChanged
                                "
                                :loading="statusForm.processing"
                            >
                                <ShieldCheckIcon class="w-5 h-5" />
                                Update Status
                            </ModernButton>

                            <ModernButton
                                type="button"
                                variant="outline"
                                :href="route('admin.brokers.show', broker.id)"
                            >
                                Cancel
                            </ModernButton>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Status History & Info -->
            <div class="space-y-6">
                <!-- Current Status Info -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Current Status
                    </h3>

                    <div class="flex items-center gap-3 mb-4">
                        <component
                            :is="currentStatusInfo.icon"
                            :class="[
                                'w-8 h-8',
                                currentStatusInfo.class.split(' ')[0],
                            ]"
                        />
                        <div>
                            <div class="font-medium text-gray-900">
                                {{ currentStatusInfo.label }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ currentStatusInfo.description }}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status Since:</span>
                            <span class="font-medium text-gray-900">
                                {{
                                    formatDateTime(
                                        broker.status_updated_at ||
                                            broker.updated_at
                                    )
                                }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Last Updated By:</span>
                            <span class="font-medium text-gray-900">
                                {{ broker.status_updated_by || "System" }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Status History -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Status History
                    </h3>

                    <div v-if="statusHistory?.length" class="space-y-4">
                        <div
                            v-for="(history, index) in statusHistory"
                            :key="history.id"
                            class="relative"
                        >
                            <!-- Timeline line -->
                            <div
                                v-if="index < statusHistory.length - 1"
                                class="absolute left-4 top-8 w-0.5 h-full bg-gray-200"
                            ></div>

                            <div class="flex items-start gap-3">
                                <div
                                    :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 border-2 bg-white',
                                        getStatusBadge(history.status).includes(
                                            'green'
                                        )
                                            ? 'border-green-200'
                                            : getStatusBadge(
                                                  history.status
                                              ).includes('red')
                                            ? 'border-red-200'
                                            : getStatusBadge(
                                                  history.status
                                              ).includes('yellow')
                                            ? 'border-yellow-200'
                                            : 'border-gray-200',
                                    ]"
                                >
                                    <component
                                        :is="getStatusIcon(history.status)"
                                        class="w-4 h-4"
                                        :class="
                                            getStatusBadge(
                                                history.status
                                            ).includes('green')
                                                ? 'text-green-600'
                                                : getStatusBadge(
                                                      history.status
                                                  ).includes('red')
                                                ? 'text-red-600'
                                                : getStatusBadge(
                                                      history.status
                                                  ).includes('yellow')
                                                ? 'text-yellow-600'
                                                : 'text-gray-600'
                                        "
                                    />
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                                getStatusBadge(history.status),
                                            ]"
                                        >
                                            {{
                                                history.status
                                                    ?.replace("_", " ")
                                                    .toUpperCase()
                                            }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{
                                                formatDateTime(
                                                    history.created_at
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <p
                                        v-if="history.reason"
                                        class="text-sm text-gray-900 mb-1"
                                    >
                                        {{ history.reason }}
                                    </p>

                                    <p
                                        v-if="history.admin_notes"
                                        class="text-sm text-gray-600 bg-gray-50 p-2 rounded-lg mb-1"
                                    >
                                        {{ history.admin_notes }}
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        Updated by:
                                        {{ history.updated_by || "System" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-4">
                        <ClockIcon class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                        <p class="text-sm text-gray-500">
                            No status history available
                        </p>
                    </div>
                </div>

                <!-- Status Guidelines -->
                <div class="bg-blue-50 rounded-2xl border border-blue-100 p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">
                        Status Guidelines
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-2">
                            <CheckCircleIcon
                                class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0"
                            />
                            <div>
                                <span class="font-medium text-blue-900"
                                    >Active:</span
                                >
                                <span class="text-blue-800">
                                    Full access to all platform features</span
                                >
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <PauseCircleIcon
                                class="w-4 h-4 text-gray-600 mt-0.5 flex-shrink-0"
                            />
                            <div>
                                <span class="font-medium text-blue-900"
                                    >Inactive:</span
                                >
                                <span class="text-blue-800">
                                    Temporary suspension, can be
                                    reactivated</span
                                >
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <XCircleIcon
                                class="w-4 h-4 text-red-600 mt-0.5 flex-shrink-0"
                            />
                            <div>
                                <span class="font-medium text-blue-900"
                                    >Suspended:</span
                                >
                                <span class="text-blue-800">
                                    Account blocked due to violations</span
                                >
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <ClockIcon
                                class="w-4 h-4 text-yellow-600 mt-0.5 flex-shrink-0"
                            />
                            <div>
                                <span class="font-medium text-blue-900"
                                    >Pending:</span
                                >
                                <span class="text-blue-800">
                                    Application under review</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
