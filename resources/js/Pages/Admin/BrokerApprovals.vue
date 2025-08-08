<script setup>
import { Head, useForm, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { ref } from "vue";

defineProps({
    pendingBrokers: Array,
});

const approveForm = useForm({
    admin_notes: "",
});

const rejectForm = useForm({
    rejection_reason: "",
    admin_notes: "",
});

const selectedBroker = ref(null);
const showApprovalModal = ref(false);
const showRejectionModal = ref(false);

const openApprovalModal = (broker) => {
    selectedBroker.value = broker;
    showApprovalModal.value = true;
    approveForm.reset();
};

const openRejectionModal = (broker) => {
    selectedBroker.value = broker;
    showRejectionModal.value = true;
    rejectForm.reset();
};

const approveBroker = () => {
    approveForm.post(route("admin.brokers.approve", selectedBroker.value.id), {
        onSuccess: () => {
            showApprovalModal.value = false;
            selectedBroker.value = null;
        },
    });
};

const rejectBroker = () => {
    rejectForm.delete(route("admin.brokers.reject", selectedBroker.value.id), {
        onSuccess: () => {
            showRejectionModal.value = false;
            selectedBroker.value = null;
        },
    });
};

const getStatusBadge = (status) => {
    const badges = {
        pending: "bg-yellow-100 text-yellow-800",
        under_review: "bg-blue-100 text-blue-800",
        approved: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
    };
    return badges[status] || "bg-gray-100 text-gray-800";
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>

<template>
    <ModernDashboardLayout>
        <Head title="Broker Approvals" />

        <div class="px-4 py-6 sm:px-0">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            Broker Application Review
                        </h1>
                        <p class="text-gray-600 mt-2">
                            Review and verify broker credentials and
                            professional qualifications
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div
                            class="bg-white rounded-lg shadow-sm border px-4 py-2"
                        >
                            <div class="text-sm text-gray-500">
                                Pending Applications
                            </div>
                            <div class="text-2xl font-bold text-blue-600">
                                {{ pendingBrokers.length }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="pendingBrokers.length === 0"
                class="bg-white rounded-xl shadow-sm border p-12 text-center"
            >
                <div class="mx-auto h-16 w-16 text-gray-400 mb-6">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    All Applications Processed
                </h3>
                <p class="text-gray-600">
                    No pending broker applications require review at this time.
                </p>
            </div>

            <!-- Applications List -->
            <div v-else class="space-y-6">
                <div
                    v-for="broker in pendingBrokers"
                    :key="broker.id"
                    class="bg-white rounded-xl shadow-sm border hover:shadow-md transition-all duration-200"
                >
                    <div class="p-6">
                        <!-- Header Row -->
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="h-12 w-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center"
                                >
                                    <span
                                        class="text-lg font-semibold text-white"
                                    >
                                        {{
                                            broker.name.charAt(0).toUpperCase()
                                        }}
                                    </span>
                                </div>
                                <div>
                                    <h3
                                        class="text-xl font-semibold text-gray-900"
                                    >
                                        {{ broker.name }}
                                    </h3>
                                    <p class="text-gray-600">
                                        {{ broker.email }}
                                    </p>
                                    <div class="flex items-center mt-1">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                getStatusBadge(
                                                    broker.application_status
                                                ),
                                            ]"
                                        >
                                            {{
                                                broker.application_status
                                                    .replace("_", " ")
                                                    .toUpperCase()
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">Applied</div>
                                <div class="font-medium text-gray-900">
                                    {{
                                        formatDate(
                                            broker.submitted_at ||
                                                broker.created_at
                                        )
                                    }}
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information Grid -->
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6"
                        >
                            <!-- PRC License -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div
                                    class="flex items-center justify-between mb-2"
                                >
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        PRC License
                                    </h4>
                                    <svg
                                        v-if="broker.prc_id"
                                        class="h-4 w-4 text-green-500"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="h-4 w-4 text-red-500"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="text-sm text-gray-600">
                                    {{ broker.prc_id || "Not provided" }}
                                </div>
                                <div v-if="broker.prc_id_file" class="mt-2">
                                    <a
                                        :href="`/storage/${broker.prc_id_file}`"
                                        target="_blank"
                                        class="text-xs text-blue-600 hover:text-blue-800 flex items-center"
                                    >
                                        <svg
                                            class="h-3 w-3 mr-1"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        View Document
                                    </a>
                                </div>
                            </div>

                            <!-- Business Permit -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div
                                    class="flex items-center justify-between mb-2"
                                >
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Business Permit
                                    </h4>
                                    <svg
                                        v-if="broker.business_permit"
                                        class="h-4 w-4 text-green-500"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="h-4 w-4 text-red-500"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="text-sm text-gray-600">
                                    {{
                                        broker.business_permit || "Not provided"
                                    }}
                                </div>
                                <div
                                    v-if="broker.business_permit_file"
                                    class="mt-2"
                                >
                                    <a
                                        :href="`/storage/${broker.business_permit_file}`"
                                        target="_blank"
                                        class="text-xs text-blue-600 hover:text-blue-800 flex items-center"
                                    >
                                        <svg
                                            class="h-3 w-3 mr-1"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        View Document
                                    </a>
                                </div>
                            </div>

                            <!-- Additional Documents -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div
                                    class="flex items-center justify-between mb-2"
                                >
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Additional Docs
                                    </h4>
                                    <span
                                        class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full"
                                    >
                                        {{
                                            broker.additional_documents
                                                ? JSON.parse(
                                                      broker.additional_documents
                                                  ).length
                                                : 0
                                        }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-600">
                                    Supporting documents
                                </div>
                                <div
                                    v-if="broker.additional_documents"
                                    class="mt-2"
                                >
                                    <div
                                        v-for="(doc, index) in JSON.parse(
                                            broker.additional_documents
                                        )"
                                        :key="index"
                                        class="mb-1"
                                    >
                                        <a
                                            :href="`/storage/${doc}`"
                                            target="_blank"
                                            class="text-xs text-blue-600 hover:text-blue-800 flex items-center"
                                        >
                                            <svg
                                                class="h-3 w-3 mr-1"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            Document {{ index + 1 }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Application Status -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4
                                    class="text-sm font-medium text-gray-900 mb-2"
                                >
                                    Review Status
                                </h4>
                                <div class="space-y-2">
                                    <div
                                        class="flex items-center justify-between text-xs"
                                    >
                                        <span class="text-gray-600"
                                            >Submitted</span
                                        >
                                        <span class="text-gray-900">{{
                                            formatDate(
                                                broker.submitted_at ||
                                                    broker.created_at
                                            )
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="broker.reviewed_at"
                                        class="flex items-center justify-between text-xs"
                                    >
                                        <span class="text-gray-600"
                                            >Last Review</span
                                        >
                                        <span class="text-gray-900">{{
                                            formatDate(broker.reviewed_at)
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div
                            class="flex items-center justify-between pt-4 border-t border-gray-200"
                        >
                            <div class="flex items-center space-x-4">
                                <Link
                                    :href="
                                        route('admin.brokers.show', broker.id)
                                    "
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                                >
                                    View Full Application →
                                </Link>
                            </div>
                            <div class="flex space-x-3">
                                <button
                                    @click="openRejectionModal(broker)"
                                    class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                                >
                                    <svg
                                        class="w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                    Reject
                                </button>
                                <button
                                    @click="openApprovalModal(broker)"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                                >
                                    <svg
                                        class="w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                    Approve
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Modal -->
        <div
            v-if="showApprovalModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        >
            <div
                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white"
            >
                <div class="mt-3">
                    <div
                        class="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full mb-4"
                    >
                        <svg
                            class="w-6 h-6 text-green-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>
                    <h3
                        class="text-lg font-medium text-gray-900 text-center mb-4"
                    >
                        Approve Broker Application
                    </h3>
                    <p class="text-sm text-gray-600 text-center mb-6">
                        Are you sure you want to approve
                        <strong>{{ selectedBroker?.name }}</strong> as a
                        licensed broker?
                    </p>

                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Admin Notes (Optional)
                        </label>
                        <textarea
                            v-model="approveForm.admin_notes"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Add any notes about this approval..."
                        ></textarea>
                    </div>

                    <div class="flex space-x-3">
                        <button
                            @click="showApprovalModal = false"
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            Cancel
                        </button>
                        <button
                            @click="approveBroker"
                            :disabled="approveForm.processing"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50"
                        >
                            {{
                                approveForm.processing
                                    ? "Approving..."
                                    : "Approve"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejection Modal -->
        <div
            v-if="showRejectionModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        >
            <div
                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white"
            >
                <div class="mt-3">
                    <div
                        class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4"
                    >
                        <svg
                            class="w-6 h-6 text-red-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </div>
                    <h3
                        class="text-lg font-medium text-gray-900 text-center mb-4"
                    >
                        Reject Broker Application
                    </h3>
                    <p class="text-sm text-gray-600 text-center mb-6">
                        Please provide a reason for rejecting
                        <strong>{{ selectedBroker?.name }}</strong
                        >'s application.
                    </p>

                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Rejection Reason <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="rejectForm.rejection_reason"
                            rows="3"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Explain why this application is being rejected..."
                        ></textarea>
                    </div>

                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Admin Notes (Optional)
                        </label>
                        <textarea
                            v-model="rejectForm.admin_notes"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Additional internal notes..."
                        ></textarea>
                    </div>

                    <div class="flex space-x-3">
                        <button
                            @click="showRejectionModal = false"
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            Cancel
                        </button>
                        <button
                            @click="rejectBroker"
                            :disabled="
                                rejectForm.processing ||
                                !rejectForm.rejection_reason
                            "
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50"
                        >
                            {{
                                rejectForm.processing
                                    ? "Rejecting..."
                                    : "Reject"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
