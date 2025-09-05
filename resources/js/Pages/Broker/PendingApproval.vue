<script setup>
import { Head } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import {
    ClockIcon,
    CheckCircleIcon,
    DocumentCheckIcon,
    ShieldCheckIcon,
    ArrowRightOnRectangleIcon,
    EnvelopeIcon,
} from "@heroicons/vue/24/outline";

const handleLogout = () => {
    router.post("/logout");
};

const verificationSteps = [
    {
        title: "Application Submitted",
        description: "Your broker registration has been received",
        status: "completed",
        icon: CheckCircleIcon,
    },
    {
        title: "Document Review",
        description: "Our team is verifying your credentials",
        status: "current",
        icon: DocumentCheckIcon,
    },
    {
        title: "Account Activation",
        description: "Full dashboard access will be granted",
        status: "pending",
        icon: ShieldCheckIcon,
    },
];
</script>

<template>
    <Head title="Pending Approval - GeoCasa Bohol" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-gray-100">
        <!-- Header Section -->
        <div class="bg-white border-b border-gray-100">
            <div class="max-w-4xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
                <div class="text-center">
                    <div
                        class="w-20 h-20 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg"
                    >
                        <ClockIcon class="w-10 h-10 text-white" />
                    </div>
                    <h1
                        class="text-4xl font-light text-gray-900 mb-3 tracking-tight"
                    >
                        Application Under Review
                    </h1>
                    <p
                        class="text-xl text-gray-500 font-light max-w-2xl mx-auto"
                    >
                        Your broker application is being carefully reviewed by
                        our verification team
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Status Overview -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Current Status Card -->
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                    >
                        <div class="px-8 py-6 border-b border-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-3 h-3 bg-amber-400 rounded-full animate-pulse"
                                    ></div>
                                    <span
                                        class="text-sm font-semibold text-amber-700 uppercase tracking-wider"
                                        >In Review</span
                                    >
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">
                                        Estimated Time
                                    </div>
                                    <div
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        1-2 Business Days
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-8 py-6">
                            <h2 class="text-2xl font-light text-gray-900 mb-2">
                                Verification in Progress
                            </h2>
                            <p class="text-gray-600 leading-relaxed">
                                Our team is conducting a thorough review of your
                                credentials and documentation to ensure
                                compliance with industry standards.
                            </p>
                        </div>
                    </div>

                    <!-- Verification Process -->
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                    >
                        <div class="px-8 py-6 border-b border-gray-50">
                            <h3 class="text-xl font-light text-gray-900">
                                Verification Process
                            </h3>
                        </div>
                        <div class="px-8 py-6">
                            <div class="space-y-8">
                                <div
                                    v-for="(step, index) in verificationSteps"
                                    :key="step.title"
                                    class="flex items-start group"
                                >
                                    <div
                                        class="flex flex-col items-center mr-6"
                                    >
                                        <div
                                            :class="[
                                                'w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300',
                                                step.status === 'completed'
                                                    ? 'bg-emerald-100 text-emerald-600 shadow-sm'
                                                    : step.status === 'current'
                                                    ? 'bg-amber-100 text-amber-600 shadow-sm ring-4 ring-amber-50'
                                                    : 'bg-gray-50 text-gray-400',
                                            ]"
                                        >
                                            <component
                                                :is="step.icon"
                                                class="w-6 h-6"
                                            />
                                        </div>
                                        <div
                                            v-if="
                                                index <
                                                verificationSteps.length - 1
                                            "
                                            :class="[
                                                'w-0.5 h-12 mt-4 transition-colors duration-300',
                                                step.status === 'completed'
                                                    ? 'bg-emerald-200'
                                                    : 'bg-gray-200',
                                            ]"
                                        ></div>
                                    </div>
                                    <div class="flex-1 pt-2">
                                        <h4
                                            :class="[
                                                'text-lg font-medium mb-2 transition-colors duration-300',
                                                step.status === 'pending'
                                                    ? 'text-gray-400'
                                                    : 'text-gray-900',
                                            ]"
                                        >
                                            {{ step.title }}
                                        </h4>
                                        <p
                                            :class="[
                                                'text-sm leading-relaxed transition-colors duration-300',
                                                step.status === 'pending'
                                                    ? 'text-gray-400'
                                                    : 'text-gray-600',
                                            ]"
                                        >
                                            {{ step.description }}
                                        </p>
                                        <div class="mt-3">
                                            <span
                                                v-if="
                                                    step.status === 'completed'
                                                "
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700"
                                            >
                                                ✓ Complete
                                            </span>
                                            <span
                                                v-else-if="
                                                    step.status === 'current'
                                                "
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700"
                                            >
                                                ⏳ In Progress
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500"
                                            >
                                                ⏸ Pending
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Review Checklist -->
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                    >
                        <div class="px-6 py-5 border-b border-gray-50">
                            <h3 class="text-lg font-medium text-gray-900">
                                Review Checklist
                            </h3>
                        </div>
                        <div class="px-6 py-5">
                            <div class="space-y-4">
                                <div class="flex items-start space-x-3">
                                    <div
                                        class="w-2 h-2 bg-blue-400 rounded-full mt-2 flex-shrink-0"
                                    ></div>
                                    <span
                                        class="text-sm text-gray-700 leading-relaxed"
                                        >Professional license verification</span
                                    >
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div
                                        class="w-2 h-2 bg-blue-400 rounded-full mt-2 flex-shrink-0"
                                    ></div>
                                    <span
                                        class="text-sm text-gray-700 leading-relaxed"
                                        >Identity document validation</span
                                    >
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div
                                        class="w-2 h-2 bg-blue-400 rounded-full mt-2 flex-shrink-0"
                                    ></div>
                                    <span
                                        class="text-sm text-gray-700 leading-relaxed"
                                        >Background compliance check</span
                                    >
                                </div>
                                <div class="flex items-start space-x-3">
                                    <div
                                        class="w-2 h-2 bg-blue-400 rounded-full mt-2 flex-shrink-0"
                                    ></div>
                                    <span
                                        class="text-sm text-gray-700 leading-relaxed"
                                        >Business registration details</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Support Card -->
                    <div
                        class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-100 overflow-hidden"
                    >
                        <div class="px-6 py-5">
                            <div class="flex items-center space-x-3 mb-4">
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center"
                                >
                                    <EnvelopeIcon
                                        class="w-5 h-5 text-blue-600"
                                    />
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">
                                    Need Assistance?
                                </h3>
                            </div>
                            <p
                                class="text-sm text-gray-600 mb-4 leading-relaxed"
                            >
                                Our support team is here to help with any
                                questions about your application.
                            </p>
                            <a
                                href="mailto:support@geocasa.com"
                                class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200"
                            >
                                support@geocasa.com
                                <svg
                                    class="w-4 h-4 ml-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                    />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Sign Out -->
                    <div class="text-center">
                        <button
                            @click="handleLogout"
                            type="button"
                            class="inline-flex items-center px-6 py-3 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-sm"
                        >
                            <ArrowRightOnRectangleIcon class="w-4 h-4 mr-2" />
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
