<template>
    <ModernDashboardLayout>
        <Head title="Compliance Reports" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                Compliance Reports
                            </h1>
                            <p class="mt-2 text-gray-600">
                                Detailed analytics and metrics for compliance
                                monitoring
                            </p>
                        </div>

                        <div class="flex items-center space-x-4">
                            <Link
                                :href="route('admin.reports.dashboard')"
                                class="text-indigo-600 hover:text-indigo-900"
                            >
                                ← Back to Dashboard
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
                >
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
                                            Total Reports
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.total_reports }}
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
                                    <ClockIcon
                                        class="h-6 w-6 text-yellow-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Under Review
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.under_review }}
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
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.resolved }}
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
                                    <ChartBarIcon
                                        class="h-6 w-6 text-purple-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            Avg. Resolution Time
                                        </dt>
                                        <dd
                                            class="text-2xl font-semibold text-gray-900"
                                        >
                                            {{ stats.avg_resolution_time }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Report Type Distribution -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Report Type Distribution
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div
                                    v-for="(count, type) in stats.report_types"
                                    :key="type"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center">
                                        <div
                                            class="w-4 h-4 rounded-full mr-3"
                                            :class="getTypeColor(type)"
                                        ></div>
                                        <span
                                            class="text-sm font-medium text-gray-700 capitalize"
                                            >{{ formatReportType(type) }}</span
                                        >
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-900">{{
                                            count
                                        }}</span>
                                        <div
                                            class="w-20 bg-gray-200 rounded-full h-2"
                                        >
                                            <div
                                                class="h-2 rounded-full"
                                                :class="getTypeColor(type)"
                                                :style="{
                                                    width:
                                                        (count /
                                                            stats.total_reports) *
                                                            100 +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Severity Distribution -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Severity Distribution
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div
                                    v-for="(
                                        count, severity
                                    ) in stats.severity_distribution"
                                    :key="severity"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center">
                                        <div
                                            class="w-4 h-4 rounded-full mr-3"
                                            :class="getSeverityColor(severity)"
                                        ></div>
                                        <span
                                            class="text-sm font-medium text-gray-700 capitalize"
                                            >{{ severity }}</span
                                        >
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-900">{{
                                            count
                                        }}</span>
                                        <div
                                            class="w-20 bg-gray-200 rounded-full h-2"
                                        >
                                            <div
                                                class="h-2 rounded-full"
                                                :class="
                                                    getSeverityColor(severity)
                                                "
                                                :style="{
                                                    width:
                                                        (count /
                                                            stats.total_reports) *
                                                            100 +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Investigation Statistics -->
                <div class="bg-white shadow rounded-lg mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Investigation Statistics
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-blue-600">
                                    {{
                                        investigationStats.total_investigations
                                    }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    Total Investigations
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-green-600">
                                    {{ investigationStats.evidence_collected }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    Evidence Collected
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-purple-600">
                                    {{
                                        investigationStats.interviews_conducted
                                    }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    Interviews Conducted
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compliance Trends -->
                <div class="bg-white shadow rounded-lg mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Compliance Report Trends
                        </h3>
                    </div>
                    <div class="p-6">
                        <div
                            class="h-64 flex items-center justify-center bg-gray-50 rounded-lg"
                        >
                            <div class="text-center">
                                <ChartBarIcon
                                    class="mx-auto h-12 w-12 text-gray-400"
                                />
                                <h3
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    Compliance Trends Chart
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Chart visualization would be implemented
                                    here
                                </p>
                                <div
                                    class="mt-4 space-y-2 text-xs text-gray-600"
                                >
                                    <div
                                        v-for="(
                                            count, date
                                        ) in chartData.reports"
                                        :key="date"
                                        class="flex justify-between"
                                    >
                                        <span>{{ formatDate(date) }}:</span>
                                        <span class="font-medium"
                                            >{{ count }} reports</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Compliance Reports -->
                <div class="bg-white shadow rounded-lg mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Recent Compliance Reports
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Report ID
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Type
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Severity
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Reported Item
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Assigned To
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Reported
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="report in recentReports"
                                    :key="report.id"
                                >
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                    >
                                        #{{ report.id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="getTypeClass(report.type)"
                                        >
                                            {{ formatReportType(report.type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="
                                                getSeverityClass(
                                                    report.severity
                                                )
                                            "
                                        >
                                            {{ report.severity }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="
                                                getStatusClass(report.status)
                                            "
                                        >
                                            {{ report.status }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ report.reported_item_type }} #{{
                                            report.reported_item_id
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{
                                            report.assigned_admin?.name ||
                                            "Unassigned"
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ formatDate(report.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Investigation Activities -->
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
                                    :key="index"
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
                                        <div class="relative flex space-x-3">
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
                                                        class="h-4 w-4 text-white"
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
                                                        {{
                                                            activity.description
                                                        }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-gray-400"
                                                    >
                                                        Report #{{
                                                            activity.compliance_report_id
                                                        }}
                                                        -
                                                        {{
                                                            activity
                                                                .investigator
                                                                ?.name
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
    </ModernDashboardLayout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    ExclamationTriangleIcon,
    ClockIcon,
    CheckCircleIcon,
    ChartBarIcon,
    PlayIcon,
    DocumentIcon,
    UserIcon,
    ArrowUpIcon,
    CheckIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    stats: Object,
    investigationStats: Object,
    chartData: Object,
    recentReports: Array,
    recentActivities: Array,
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

const formatReportType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const getTypeColor = (type) => {
    const colors = {
        inappropriate_content: "bg-red-500",
        spam: "bg-orange-500",
        fraud: "bg-red-600",
        harassment: "bg-purple-500",
        fake_listing: "bg-yellow-500",
        other: "bg-gray-500",
    };
    return colors[type] || "bg-gray-500";
};

const getTypeClass = (type) => {
    const classes = {
        inappropriate_content: "bg-red-100 text-red-800",
        spam: "bg-orange-100 text-orange-800",
        fraud: "bg-red-100 text-red-800",
        harassment: "bg-purple-100 text-purple-800",
        fake_listing: "bg-yellow-100 text-yellow-800",
        other: "bg-gray-100 text-gray-800",
    };
    return classes[type] || "bg-gray-100 text-gray-800";
};

const getSeverityColor = (severity) => {
    const colors = {
        low: "bg-green-500",
        medium: "bg-yellow-500",
        high: "bg-orange-500",
        critical: "bg-red-600",
    };
    return colors[severity] || "bg-gray-500";
};

const getSeverityClass = (severity) => {
    const classes = {
        low: "bg-green-100 text-green-800",
        medium: "bg-yellow-100 text-yellow-800",
        high: "bg-orange-100 text-orange-800",
        critical: "bg-red-100 text-red-800",
    };
    return classes[severity] || "bg-gray-100 text-gray-800";
};

const getStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        under_review: "bg-blue-100 text-blue-800",
        resolved: "bg-green-100 text-green-800",
        dismissed: "bg-gray-100 text-gray-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getActivityIcon = (actionType) => {
    const icons = {
        start_investigation: PlayIcon,
        collect_evidence: DocumentIcon,
        conduct_interview: UserIcon,
        escalate: ArrowUpIcon,
        resolve: CheckIcon,
    };
    return icons[actionType] || ClockIcon;
};

const getActivityIconClass = (actionType) => {
    const classes = {
        start_investigation: "bg-blue-500",
        collect_evidence: "bg-purple-500",
        conduct_interview: "bg-green-500",
        escalate: "bg-orange-500",
        resolve: "bg-green-600",
    };
    return classes[actionType] || "bg-gray-500";
};
</script>
