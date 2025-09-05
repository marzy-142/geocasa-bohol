<template>
    <ModernDashboardLayout>
        <Head title="Investigation Dashboard" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Investigation Dashboard
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Monitor and manage ongoing compliance investigations
                    </p>
                </div>

                <!-- Statistics Cards -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
                >
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ClipboardDocumentListIcon
                                        class="h-6 w-6 text-gray-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Total Actions
                                        </dt>
                                        <dd
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            {{ stats.total_actions }}
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
                                    <DocumentMagnifyingGlassIcon
                                        class="h-6 w-6 text-blue-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Evidence Collected
                                        </dt>
                                        <dd
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            {{ stats.evidence_collected }}
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
                                    <ChatBubbleLeftRightIcon
                                        class="h-6 w-6 text-purple-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Interviews
                                        </dt>
                                        <dd
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            {{ stats.interviews_conducted }}
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
                                    <CheckCircleIcon
                                        class="h-6 w-6 text-green-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Resolved
                                        </dt>
                                        <dd
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            {{ stats.cases_resolved }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Active Investigations -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Active Investigations
                            </h3>
                        </div>
                        <div class="p-6">
                            <div
                                v-if="activeInvestigations.data.length === 0"
                                class="text-center py-8"
                            >
                                <ExclamationTriangleIcon
                                    class="mx-auto h-12 w-12 text-gray-400"
                                />
                                <h3
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    No active investigations
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    All compliance reports are up to date.
                                </p>
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="investigation in activeInvestigations.data"
                                    :key="investigation.id"
                                    class="border border-gray-200 rounded-lg p-4"
                                >
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <div class="flex-1">
                                            <div
                                                class="flex items-center space-x-2"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'admin.compliance.show',
                                                            investigation.id
                                                        )
                                                    "
                                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                                                >
                                                    Report #{{
                                                        investigation.id
                                                    }}
                                                </Link>
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                    :class="
                                                        getSeverityBadgeClass(
                                                            investigation.severity
                                                        )
                                                    "
                                                >
                                                    {{ investigation.severity }}
                                                </span>
                                            </div>
                                            <p
                                                class="mt-1 text-sm text-gray-600"
                                            >
                                                {{ investigation.description }}
                                            </p>
                                            <div
                                                class="mt-2 flex items-center text-xs text-gray-500"
                                            >
                                                <span
                                                    >{{
                                                        investigation.reportable_type
                                                            .split("\\")
                                                            .pop()
                                                    }}
                                                    #{{
                                                        investigation.reportable_id
                                                    }}</span
                                                >
                                                <span class="mx-2">•</span>
                                                <span>{{
                                                    formatDate(
                                                        investigation.reviewed_at
                                                    )
                                                }}</span>
                                                <span
                                                    v-if="
                                                        investigation.assigned_admin
                                                    "
                                                    class="mx-2"
                                                    >•</span
                                                >
                                                <span
                                                    v-if="
                                                        investigation.assigned_admin
                                                    "
                                                    >{{
                                                        investigation
                                                            .assigned_admin.name
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pagination -->
                            <div
                                v-if="activeInvestigations.last_page > 1"
                                class="mt-6"
                            >
                                <nav class="flex items-center justify-between">
                                    <div
                                        class="flex-1 flex justify-between sm:hidden"
                                    >
                                        <Link
                                            v-if="
                                                activeInvestigations.prev_page_url
                                            "
                                            :href="
                                                activeInvestigations.prev_page_url
                                            "
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            Previous
                                        </Link>
                                        <Link
                                            v-if="
                                                activeInvestigations.next_page_url
                                            "
                                            :href="
                                                activeInvestigations.next_page_url
                                            "
                                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            Next
                                        </Link>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Recent Investigation Activities
                            </h3>
                        </div>
                        <div class="p-6">
                            <div
                                v-if="recentActivities.length === 0"
                                class="text-center py-8"
                            >
                                <ClockIcon
                                    class="mx-auto h-12 w-12 text-gray-400"
                                />
                                <h3
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    No recent activities
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Investigation activities will appear here.
                                </p>
                            </div>
                            <div v-else class="flow-root">
                                <ul class="-mb-8">
                                    <li
                                        v-for="(
                                            activity, index
                                        ) in recentActivities"
                                        :key="activity.id"
                                    >
                                        <div class="relative pb-8">
                                            <span
                                                v-if="
                                                    index !==
                                                    recentActivities.length - 1
                                                "
                                                class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                                aria-hidden="true"
                                            ></span>
                                            <div
                                                class="relative flex space-x-3"
                                            >
                                                <div>
                                                    <span
                                                        class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                                        :class="
                                                            getActivityIconClass(
                                                                activity.action_type
                                                            )
                                                        "
                                                    >
                                                        <component
                                                            :is="
                                                                getActivityIcon(
                                                                    activity.action_type
                                                                )
                                                            "
                                                            class="h-4 w-4"
                                                        />
                                                    </span>
                                                </div>
                                                <div
                                                    class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4"
                                                >
                                                    <div>
                                                        <p
                                                            class="text-sm text-gray-500"
                                                        >
                                                            <span
                                                                class="font-medium text-gray-900"
                                                                >{{
                                                                    activity
                                                                        .investigator
                                                                        .name
                                                                }}</span
                                                            >
                                                            {{
                                                                getActivityDescription(
                                                                    activity.action_type
                                                                )
                                                            }}
                                                            <Link
                                                                :href="
                                                                    route(
                                                                        'admin.compliance.show',
                                                                        activity
                                                                            .compliance_report
                                                                            .id
                                                                    )
                                                                "
                                                                class="font-medium text-indigo-600 hover:text-indigo-500"
                                                            >
                                                                Report #{{
                                                                    activity
                                                                        .compliance_report
                                                                        .id
                                                                }}
                                                            </Link>
                                                        </p>
                                                        <p
                                                            class="mt-1 text-sm text-gray-600"
                                                        >
                                                            {{
                                                                activity.description
                                                            }}
                                                        </p>
                                                    </div>
                                                    <div
                                                        class="text-right text-sm whitespace-nowrap text-gray-500"
                                                    >
                                                        <time
                                                            :datetime="
                                                                activity.action_taken_at
                                                            "
                                                            >{{
                                                                formatDate(
                                                                    activity.action_taken_at
                                                                )
                                                            }}</time
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    ClipboardDocumentListIcon,
    DocumentMagnifyingGlassIcon,
    ChatBubbleLeftRightIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    EyeIcon,
    DocumentIcon,
    UserIcon,
    PencilIcon,
    ArrowTrendingUpIcon,
    CheckIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    stats: Object,
    recentActivities: Array,
    activeInvestigations: Object,
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getSeverityBadgeClass = (severity) => {
    const classes = {
        low: "bg-green-100 text-green-800",
        medium: "bg-yellow-100 text-yellow-800",
        high: "bg-orange-100 text-orange-800",
        critical: "bg-red-100 text-red-800",
    };
    return classes[severity] || "bg-gray-100 text-gray-800";
};

const getActivityIconClass = (actionType) => {
    const classes = {
        evidence_collected: "bg-blue-500",
        interview_conducted: "bg-purple-500",
        status_changed: "bg-yellow-500",
        note_added: "bg-gray-500",
        escalated: "bg-red-500",
        resolved: "bg-green-500",
    };
    return classes[actionType] || "bg-gray-500";
};

const getActivityIcon = (actionType) => {
    const icons = {
        evidence_collected: DocumentIcon,
        interview_conducted: UserIcon,
        status_changed: EyeIcon,
        note_added: PencilIcon,
        escalated: ArrowTrendingUpIcon,
        resolved: CheckIcon,
    };
    return icons[actionType] || DocumentIcon;
};

const getActivityDescription = (actionType) => {
    const descriptions = {
        evidence_collected: "collected evidence for",
        interview_conducted: "conducted interview for",
        status_changed: "changed status of",
        note_added: "added note to",
        escalated: "escalated",
        resolved: "resolved",
    };
    return descriptions[actionType] || "updated";
};
</script>
