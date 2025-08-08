<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    broker: Object,
    credentials: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getStatusBadge = (status) => {
    const badges = {
        under_review: "bg-yellow-100 text-yellow-800",
        approved: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
        pending: "bg-gray-100 text-gray-800",
    };
    return badges[status] || badges["pending"];
};
</script>

<template>
    <Head title="Broker Application Details" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                Broker Application Details
                            </h1>
                            <p class="mt-2 text-gray-600">
                                Complete application review for
                                {{ broker.name }}
                            </p>
                        </div>
                        <Link
                            :href="route('admin.brokers.index')"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >
                            ← Back to Applications
                        </Link>
                    </div>
                </div>

                <!-- Application Status Card -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-8">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="h-16 w-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center"
                                >
                                    <span class="text-2xl font-bold text-white">
                                        {{
                                            broker.name.charAt(0).toUpperCase()
                                        }}
                                    </span>
                                </div>
                                <div>
                                    <h2
                                        class="text-2xl font-bold text-gray-900"
                                    >
                                        {{ broker.name }}
                                    </h2>
                                    <p class="text-gray-600">
                                        {{ broker.email }}
                                    </p>
                                    <div class="flex items-center mt-2">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                                getStatusBadge(
                                                    broker.application_status
                                                ),
                                            ]"
                                        >
                                            {{
                                                broker.application_status
                                                    ?.replace("_", " ")
                                                    .toUpperCase() || "PENDING"
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Application Timeline -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Application Timeline
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full"
                                    ></div>
                                    <div class="ml-4">
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            Application Submitted
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{
                                                formatDate(
                                                    broker.submitted_at ||
                                                        broker.created_at
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="broker.reviewed_at"
                                    class="flex items-center"
                                >
                                    <div
                                        class="flex-shrink-0 w-2 h-2 bg-yellow-500 rounded-full"
                                    ></div>
                                    <div class="ml-4">
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            Last Reviewed
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{ formatDate(broker.reviewed_at) }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-if="broker.approved_at"
                                    class="flex items-center"
                                >
                                    <div
                                        class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full"
                                    ></div>
                                    <div class="ml-4">
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            Approved
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{ formatDate(broker.approved_at) }}
                                        </p>
                                        <p
                                            v-if="broker.approved_by"
                                            class="text-sm text-gray-500"
                                        >
                                            Approved by:
                                            {{
                                                broker.approved_by?.name ||
                                                "System"
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Personal Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Full Name</label
                                >
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ broker.name }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Email Address</label
                                >
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ broker.email }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Phone Number</label
                                >
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ broker.phone || "Not provided" }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Registration Date</label
                                >
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(broker.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Professional Credentials -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Professional Credentials
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            <!-- PRC ID -->
                            <div class="border rounded-lg p-4">
                                <div
                                    class="flex items-center justify-between mb-3"
                                >
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        PRC License
                                    </h4>
                                    <span
                                        v-if="broker.prc_id"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                    >
                                        Provided
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                    >
                                        Missing
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">
                                    <strong>PRC ID:</strong>
                                    {{ broker.prc_id || "Not provided" }}
                                </p>
                                <div
                                    v-if="credentials.prc_id_file"
                                    class="mt-3"
                                >
                                    <a
                                        :href="credentials.prc_id_file"
                                        target="_blank"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        <svg
                                            class="h-4 w-4 mr-2"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        View PRC Document
                                    </a>
                                </div>
                            </div>

                            <!-- Business Permit -->
                            <div class="border rounded-lg p-4">
                                <div
                                    class="flex items-center justify-between mb-3"
                                >
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Business Permit
                                    </h4>
                                    <span
                                        v-if="broker.business_permit"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                    >
                                        Provided
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                    >
                                        Missing
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">
                                    <strong>Permit Number:</strong>
                                    {{
                                        broker.business_permit || "Not provided"
                                    }}
                                </p>
                                <div
                                    v-if="credentials.business_permit_file"
                                    class="mt-3"
                                >
                                    <a
                                        :href="credentials.business_permit_file"
                                        target="_blank"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        <svg
                                            class="h-4 w-4 mr-2"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        View Business Permit
                                    </a>
                                </div>
                            </div>

                            <!-- Additional Documents -->
                            <div
                                v-if="
                                    credentials.additional_documents &&
                                    credentials.additional_documents.length > 0
                                "
                                class="border rounded-lg p-4"
                            >
                                <h4
                                    class="text-sm font-medium text-gray-900 mb-3"
                                >
                                    Additional Documents
                                </h4>
                                <div class="space-y-2">
                                    <div
                                        v-for="(
                                            doc, index
                                        ) in credentials.additional_documents"
                                        :key="index"
                                    >
                                        <a
                                            :href="doc"
                                            target="_blank"
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            <svg
                                                class="h-4 w-4 mr-2"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            Additional Document {{ index + 1 }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejection Reason (if applicable) -->
                <div
                    v-if="broker.rejection_reason"
                    class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8"
                >
                    <h3 class="text-lg font-medium text-red-900 mb-2">
                        Rejection Reason
                    </h3>
                    <p class="text-sm text-red-700">
                        {{ broker.rejection_reason }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div
                    v-if="broker.application_status === 'under_review'"
                    class="flex justify-end space-x-4"
                >
                    <Link
                        :href="route('admin.brokers.index')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Back to List
                    </Link>
                    <button
                        class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50"
                    >
                        Reject Application
                    </button>
                    <button
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                    >
                        Approve Application
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
