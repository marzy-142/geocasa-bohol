<script setup>
import { Head } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import {
    XCircleIcon,
    ArrowRightOnRectangleIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

defineProps({
    rejection_reason: String,
    reviewed_at: String,
});

const handleLogout = () => {
    router.post("/logout");
};

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
</script>

<template>
    <Head title="Application Rejected - GeoCasa Bohol" />

    <div class="min-h-screen bg-gray-50 py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <div
                    class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-6"
                >
                    <XCircleIcon class="w-8 h-8 text-white" />
                </div>
                <h1 class="text-3xl font-semibold text-gray-900 mb-3">
                    Application Rejected
                </h1>
                <p class="text-gray-600 text-lg">
                    Unfortunately, your broker application was not approved
                </p>
            </div>

            <!-- Status Card -->
            <div
                class="bg-white rounded-lg shadow-sm border border-red-200 p-8 mb-8"
            >
                <div class="flex items-center mb-6">
                    <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                    <span
                        class="text-sm font-medium text-red-700 uppercase tracking-wide"
                        >Rejected</span
                    >
                </div>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">
                    Application Status
                </h2>
                <p class="text-gray-600">
                    Reviewed on: {{ formatDate(reviewed_at) }}
                </p>
            </div>

            <!-- Rejection Reason -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-8 mb-8">
                <div class="flex items-start">
                    <ExclamationTriangleIcon
                        class="w-6 h-6 text-red-600 mr-3 mt-0.5 flex-shrink-0"
                    />
                    <div>
                        <h3 class="text-lg font-semibold text-red-900 mb-3">
                            Reason for Rejection
                        </h3>
                        <p class="text-red-800 leading-relaxed">
                            {{ rejection_reason }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8"
            >
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    What You Can Do
                </h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div
                            class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-3 mt-2"
                        ></div>
                        <div>
                            <p class="text-gray-700 font-medium">
                                Review the rejection reason
                            </p>
                            <p class="text-gray-600 text-sm">
                                Address the specific issues mentioned above
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div
                            class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-3 mt-2"
                        ></div>
                        <div>
                            <p class="text-gray-700 font-medium">
                                Gather required documents
                            </p>
                            <p class="text-gray-600 text-sm">
                                Ensure all credentials and permits are valid and
                                up-to-date
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div
                            class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-3 mt-2"
                        ></div>
                        <div>
                            <p class="text-gray-700 font-medium">
                                Contact support for clarification
                            </p>
                            <p class="text-gray-600 text-sm">
                                Our team can provide additional guidance if
                                needed
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div
                            class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-3 mt-2"
                        ></div>
                        <div>
                            <p class="text-gray-700 font-medium">
                                Submit a new application
                            </p>
                            <p class="text-gray-600 text-sm">
                                You may reapply once you've addressed the issues
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8"
            >
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                    Need Help?
                </h3>
                <p class="text-gray-600 mb-4">
                    Contact our support team for assistance with your
                    application or to discuss the rejection.
                </p>
                <a
                    href="mailto:support@geocasa.com"
                    class="text-blue-600 hover:text-blue-700 font-medium text-sm"
                >
                    support@geocasa.com
                </a>
            </div>

            <!-- Sign Out -->
            <div class="text-center">
                <button
                    @click="handleLogout"
                    type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                    <ArrowRightOnRectangleIcon class="w-4 h-4 mr-2" />
                    Sign Out
                </button>
            </div>
        </div>
    </div>
</template>
