<template>
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">
                    Pending Approvals
                </h3>
                <span
                    v-if="pendingApprovals.length > 0"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                >
                    {{ pendingApprovals.length }} pending
                </span>
            </div>
        </div>

        <div v-if="pendingApprovals.length === 0" class="px-6 py-8 text-center">
            <svg
                class="mx-auto h-12 w-12 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">
                No pending approvals
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                All approvals have been processed.
            </p>
        </div>

        <div v-else class="divide-y divide-gray-200">
            <div
                v-for="approval in pendingApprovals"
                :key="approval.id"
                class="px-6 py-6"
            >
                <!-- Approval Header -->
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4 class="text-lg font-medium text-gray-900">
                            {{ formatApprovalType(approval.type) }}
                        </h4>
                        <p class="mt-1 text-sm text-gray-600">
                            {{
                                approval.notes ||
                                "Please review and provide your approval."
                            }}
                        </p>

                        <!-- Deadline Warning -->
                        <div
                            v-if="isApproachingDeadline(approval.deadline)"
                            class="mt-2 flex items-center text-sm text-yellow-600"
                        >
                            <svg
                                class="flex-shrink-0 mr-1.5 h-4 w-4"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span class="font-medium"
                                >Deadline:
                                {{ formatDate(approval.deadline) }}</span
                            >
                            <span class="ml-2"
                                >({{
                                    getTimeRemaining(approval.deadline)
                                }})</span
                            >
                        </div>

                        <div
                            v-else
                            class="mt-2 flex items-center text-sm text-gray-500"
                        >
                            <svg
                                class="flex-shrink-0 mr-1.5 h-4 w-4"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            Deadline: {{ formatDate(approval.deadline) }}
                        </div>
                    </div>
                </div>

                <!-- Approval Details -->
                <div
                    v-if="
                        approval.data && Object.keys(approval.data).length > 0
                    "
                    class="mt-4 bg-gray-50 rounded-lg p-4"
                >
                    <h5 class="text-sm font-medium text-gray-900 mb-3">
                        Approval Details
                    </h5>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="(value, key) in approval.data" :key="key">
                            <dt class="text-sm font-medium text-gray-600">
                                {{ formatFieldName(key) }}
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ formatFieldValue(key, value) }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Approval Actions -->
                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <button
                        @click="processApproval(approval.id, true)"
                        :disabled="processingApproval"
                        class="flex-1 inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg
                            v-if="!processingApproval"
                            class="w-5 h-5 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <svg
                            v-else
                            class="animate-spin w-5 h-5 mr-2"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        {{ processingApproval ? "Processing..." : "Approve" }}
                    </button>

                    <button
                        @click="processApproval(approval.id, false)"
                        :disabled="processingApproval"
                        class="flex-1 inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg
                            v-if="!processingApproval"
                            class="w-5 h-5 mr-2"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        Reject
                    </button>
                </div>

                <!-- Additional Notes -->
                <div class="mt-4">
                    <label
                        for="approval-notes"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Additional Notes (Optional)
                    </label>
                    <textarea
                        v-model="approvalNotes[approval.id]"
                        :id="`approval-notes-${approval.id}`"
                        rows="3"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                        placeholder="Add any comments or concerns..."
                    ></textarea>
                </div>
            </div>
        </div>

        <!-- Approval History -->
        <div
            v-if="completedApprovals.length > 0"
            class="border-t border-gray-200"
        >
            <div class="px-6 py-4">
                <h4 class="text-sm font-medium text-gray-900 mb-4">
                    Recent Approvals
                </h4>
                <div class="space-y-3">
                    <div
                        v-for="approval in completedApprovals.slice(0, 3)"
                        :key="approval.id"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                    >
                        <div class="flex items-center">
                            <div
                                :class="
                                    approval.status === 'approved'
                                        ? 'bg-green-100'
                                        : 'bg-red-100'
                                "
                                class="w-8 h-8 rounded-full flex items-center justify-center"
                            >
                                <svg
                                    v-if="approval.status === 'approved'"
                                    class="w-4 h-4 text-green-600"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <svg
                                    v-else
                                    class="w-4 h-4 text-red-600"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ formatApprovalType(approval.type) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{
                                        approval.status === "approved"
                                            ? "Approved"
                                            : "Rejected"
                                    }}
                                    • {{ formatDate(approval.responded_at) }}
                                </p>
                            </div>
                        </div>
                        <span
                            :class="
                                approval.status === 'approved'
                                    ? 'bg-green-100 text-green-800'
                                    : 'bg-red-100 text-red-800'
                            "
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        >
                            {{
                                approval.status === "approved"
                                    ? "Approved"
                                    : "Rejected"
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, reactive } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    approvals: {
        type: Array,
        default: () => [],
    },
    transactionId: {
        type: [String, Number],
        required: true,
    },
});

const emit = defineEmits(["approval-processed"]);

const processingApproval = ref(false);
const approvalNotes = reactive({});

const pendingApprovals = computed(() => {
    return props.approvals.filter((approval) => approval.status === "pending");
});

const completedApprovals = computed(() => {
    return props.approvals
        .filter((approval) => approval.status !== "pending")
        .sort((a, b) => new Date(b.responded_at) - new Date(a.responded_at));
});

const formatApprovalType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatFieldName = (field) => {
    return field.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatFieldValue = (field, value) => {
    if (
        (typeof value === "number" && field.includes("price")) ||
        field.includes("amount")
    ) {
        return "₱" + new Intl.NumberFormat("en-PH").format(value);
    }
    if (typeof value === "boolean") {
        return value ? "Yes" : "No";
    }
    if (typeof value === "object" && value !== null) {
        return JSON.stringify(value, null, 2);
    }
    return value;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const isApproachingDeadline = (deadline) => {
    const deadlineDate = new Date(deadline);
    const now = new Date();
    const hoursUntilDeadline = (deadlineDate - now) / (1000 * 60 * 60);
    return hoursUntilDeadline <= 24 && hoursUntilDeadline > 0;
};

const getTimeRemaining = (deadline) => {
    const deadlineDate = new Date(deadline);
    const now = new Date();
    const diffInHours = Math.floor((deadlineDate - now) / (1000 * 60 * 60));

    if (diffInHours <= 0) return "Overdue";
    if (diffInHours < 24) return `${diffInHours} hours`;

    const diffInDays = Math.floor(diffInHours / 24);
    return `${diffInDays} days`;
};

const processApproval = async (approvalId, approved) => {
    processingApproval.value = true;

    try {
        await router.post(
            route("client.transactions.approve", props.transactionId),
            {
                approval_id: approvalId,
                approved: approved,
                notes: approvalNotes[approvalId] || null,
            },
            {
                onSuccess: () => {
                    emit("approval-processed", { approvalId, approved });
                    // Clear notes for this approval
                    delete approvalNotes[approvalId];
                },
                onError: (errors) => {
                    console.error("Error processing approval:", errors);
                },
            }
        );
    } catch (error) {
        console.error("Error processing approval:", error);
    } finally {
        processingApproval.value = false;
    }
};
</script>
