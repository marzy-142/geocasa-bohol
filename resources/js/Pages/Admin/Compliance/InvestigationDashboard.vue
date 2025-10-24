<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Investigation Dashboard
                    </h1>
                    <p class="text-gray-600">
                        Monitor active investigations and investigation
                        activities
                    </p>
                </div>
                <Link
                    :href="route('compliance.index')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <ArrowLeftIcon class="w-4 h-4 mr-2" />
                    Back to Compliance
                </Link>
            </div>

            <!-- Investigation Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <MagnifyingGlassIcon
                                    class="h-6 w-6 text-blue-400"
                                />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Active Investigations
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.active_investigations || 0 }}
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
                                        Resolved This Month
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.resolved_this_month || 0 }}
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
                                <ClockIcon class="h-6 w-6 text-yellow-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Avg. Resolution Time
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.avg_resolution_time || "N/A" }}
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
                                        Overdue Investigations
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.overdue_investigations || 0 }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Active Investigations -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3
                            class="text-lg leading-6 font-medium text-gray-900 mb-4"
                        >
                            Active Investigations
                        </h3>

                        <div
                            v-if="activeInvestigations.data.length === 0"
                            class="text-center py-8"
                        >
                            <MagnifyingGlassIcon
                                class="mx-auto h-12 w-12 text-gray-400"
                            />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                No active investigations
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                All compliance reports are currently resolved or
                                pending review.
                            </p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="investigation in activeInvestigations.data"
                                :key="investigation.id"
                                class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <h4
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{
                                                    investigation.reportable
                                                        ?.title ||
                                                    investigation.reportable
                                                        ?.name ||
                                                    "Unknown Item"
                                                }}
                                            </h4>
                                            <span
                                                :class="
                                                    getSeverityBadgeClass(
                                                        investigation.severity
                                                    )
                                                "
                                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            >
                                                {{ investigation.severity }}
                                            </span>
                                        </div>
                                        <p
                                            class="mt-1 text-sm text-gray-500 line-clamp-2"
                                        >
                                            {{ investigation.description }}
                                        </p>
                                        <div
                                            class="mt-2 flex items-center space-x-4 text-xs text-gray-500"
                                        >
                                            <span
                                                >Reported:
                                                {{
                                                    formatDate(
                                                        investigation.reported_at
                                                    )
                                                }}</span
                                            >
                                            <span
                                                v-if="
                                                    investigation.assigned_admin
                                                "
                                                >Assigned:
                                                {{
                                                    investigation.assigned_admin
                                                        .name
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <Link
                                            :href="
                                                route(
                                                    'compliance.show',
                                                    investigation.id
                                                )
                                            "
                                            class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                                        >
                                            View Details
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination for Active Investigations -->
                        <div v-if="activeInvestigations.links" class="mt-4">
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
                                <div
                                    class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"
                                >
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Showing
                                            {{ activeInvestigations.from }} to
                                            {{ activeInvestigations.to }} of
                                            {{
                                                activeInvestigations.total
                                            }}
                                            results
                                        </p>
                                    </div>
                                    <div>
                                        <nav
                                            class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                        >
                                            <template
                                                v-for="(
                                                    link, index
                                                ) in activeInvestigations.links"
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

                <!-- Recent Investigation Activities -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3
                            class="text-lg leading-6 font-medium text-gray-900 mb-4"
                        >
                            Recent Investigation Activities
                        </h3>

                        <div
                            v-if="recentActivities.length === 0"
                            class="text-center py-8"
                        >
                            <ClockIcon
                                class="mx-auto h-12 w-12 text-gray-400"
                            />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                No recent activities
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Investigation activities will appear here as
                                they occur.
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
                                        <div
                                            v-if="
                                                index !==
                                                recentActivities.length - 1
                                            "
                                            class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                        ></div>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span
                                                    :class="
                                                        getActivityIconClass(
                                                            activity.action_type
                                                        )
                                                    "
                                                    class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                                >
                                                    <component
                                                        :is="
                                                            getActivityIcon(
                                                                activity.action_type
                                                            )
                                                        "
                                                        class="h-5 w-5"
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
                                                                    ?.name ||
                                                                "System"
                                                            }}</span
                                                        >
                                                        {{
                                                            getActivityDescription(
                                                                activity.action_type
                                                            )
                                                        }}
                                                        <span
                                                            class="font-medium text-gray-900"
                                                            >{{
                                                                activity
                                                                    .compliance_report
                                                                    ?.reportable
                                                                    ?.title ||
                                                                "Unknown Item"
                                                            }}</span
                                                        >
                                                    </p>
                                                    <p
                                                        v-if="
                                                            activity.description
                                                        "
                                                        class="mt-1 text-sm text-gray-500"
                                                    >
                                                        {{
                                                            activity.description
                                                        }}
                                                    </p>
                                                </div>
                                                <div
                                                    class="text-right text-sm whitespace-nowrap text-gray-500"
                                                >
                                                    {{
                                                        formatDate(
                                                            activity.action_taken_at
                                                        )
                                                    }}
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

            <!-- Investigation Workflow Actions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3
                        class="text-lg leading-6 font-medium text-gray-900 mb-4"
                    >
                        Investigation Workflow
                    </h3>
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                    >
                        <div class="text-center">
                            <div
                                class="mx-auto h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center"
                            >
                                <MagnifyingGlassIcon
                                    class="h-6 w-6 text-blue-600"
                                />
                            </div>
                            <h4 class="mt-2 text-sm font-medium text-gray-900">
                                Start Investigation
                            </h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Begin formal investigation process
                            </p>
                        </div>

                        <div class="text-center">
                            <div
                                class="mx-auto h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center"
                            >
                                <DocumentTextIcon
                                    class="h-6 w-6 text-green-600"
                                />
                            </div>
                            <h4 class="mt-2 text-sm font-medium text-gray-900">
                                Collect Evidence
                            </h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Gather and document evidence
                            </p>
                        </div>

                        <div class="text-center">
                            <div
                                class="mx-auto h-12 w-12 bg-yellow-100 rounded-lg flex items-center justify-center"
                            >
                                <UserGroupIcon
                                    class="h-6 w-6 text-yellow-600"
                                />
                            </div>
                            <h4 class="mt-2 text-sm font-medium text-gray-900">
                                Conduct Interviews
                            </h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Interview relevant parties
                            </p>
                        </div>

                        <div class="text-center">
                            <div
                                class="mx-auto h-12 w-12 bg-red-100 rounded-lg flex items-center justify-center"
                            >
                                <CheckCircleIcon class="h-6 w-6 text-red-600" />
                            </div>
                            <h4 class="mt-2 text-sm font-medium text-gray-900">
                                Resolve Investigation
                            </h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Complete and close investigation
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    MagnifyingGlassIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    DocumentTextIcon,
    UserGroupIcon,
    ArrowLeftIcon,
    PlusIcon,
    EyeIcon,
    ChatBubbleLeftRightIcon,
    ExclamationCircleIcon,
    ArrowUpIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    stats: Object,
    recentActivities: Array,
    activeInvestigations: Object,
});

// Methods
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
        investigation_started: "bg-blue-100 text-blue-600",
        evidence_collected: "bg-green-100 text-green-600",
        interview_conducted: "bg-yellow-100 text-yellow-600",
        note_added: "bg-gray-100 text-gray-600",
        investigation_escalated: "bg-red-100 text-red-600",
        investigation_resolved: "bg-green-100 text-green-600",
    };
    return classes[actionType] || "bg-gray-100 text-gray-600";
};

const getActivityIcon = (actionType) => {
    const icons = {
        investigation_started: MagnifyingGlassIcon,
        evidence_collected: DocumentTextIcon,
        interview_conducted: ChatBubbleLeftRightIcon,
        note_added: PlusIcon,
        investigation_escalated: ArrowUpIcon,
        investigation_resolved: CheckCircleIcon,
    };
    return icons[actionType] || EyeIcon;
};

const getActivityDescription = (actionType) => {
    const descriptions = {
        investigation_started: "started investigation for",
        evidence_collected: "collected evidence for",
        interview_conducted: "conducted interview for",
        note_added: "added note to",
        investigation_escalated: "escalated investigation for",
        investigation_resolved: "resolved investigation for",
    };
    return descriptions[actionType] || "performed action on";
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>
