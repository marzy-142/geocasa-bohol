<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Compliance Reports
                    </h1>
                    <p class="text-gray-600">
                        Investigation analytics and compliance insights
                    </p>
                </div>
            </div>

            <!-- Compliance Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <ExclamationTriangleIcon
                                    class="h-6 w-6 text-orange-400"
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
                                        class="text-lg font-medium text-gray-900"
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
                                <ClockIcon class="h-6 w-6 text-yellow-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Under Review
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
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
                                        class="text-lg font-medium text-gray-900"
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
                                <ChartBarIcon class="h-6 w-6 text-purple-400" />
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt
                                        class="text-sm font-medium text-gray-500 truncate"
                                    >
                                        Avg Resolution Time
                                    </dt>
                                    <dd
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        {{ stats.avg_resolution_time }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Report Types Distribution -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Report Types Distribution
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="(count, type) in stats.report_types"
                            :key="type"
                            class="flex items-center justify-between"
                        >
                            <div class="flex items-center">
                                <div
                                    :class="getReportTypeColor(type)"
                                    class="w-3 h-3 rounded-full mr-3"
                                ></div>
                                <span class="text-sm font-medium text-gray-700">
                                    {{ formatReportType(type) }}
                                </span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">
                                {{ count }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Severity Distribution -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Severity Distribution
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="(
                                count, severity
                            ) in stats.severity_distribution"
                            :key="severity"
                            class="flex items-center justify-between"
                        >
                            <div class="flex items-center">
                                <div
                                    :class="getSeverityColor(severity)"
                                    class="w-3 h-3 rounded-full mr-3"
                                ></div>
                                <span class="text-sm font-medium text-gray-700">
                                    {{ formatSeverity(severity) }}
                                </span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">
                                {{ count }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Investigation Statistics -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Investigation Statistics
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">
                            {{ investigationStats.total_investigations }}
                        </div>
                        <div class="text-sm text-gray-500">
                            Total Investigations
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">
                            {{ investigationStats.evidence_collected }}
                        </div>
                        <div class="text-sm text-gray-500">
                            Evidence Collected
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">
                            {{ investigationStats.interviews_conducted }}
                        </div>
                        <div class="text-sm text-gray-500">
                            Interviews Conducted
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Compliance Reports -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        Recent Compliance Reports
                    </h3>
                    <Link
                        :href="route('admin.compliance.index')"
                        class="text-sm text-blue-600 hover:text-blue-500"
                    >
                        View All
                    </Link>
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
                                    Reporter
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Assigned Admin
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Created
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="report in recentReports"
                                :key="report.id"
                                class="hover:bg-gray-50"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                >
                                    #{{ report.id }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{ formatReportType(report.report_type) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="
                                            getSeverityBadgeClass(
                                                report.severity
                                            )
                                        "
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ formatSeverity(report.severity) }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{ report.reporter?.name || "Anonymous" }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{
                                        report.assigned_admin?.name ||
                                        "Unassigned"
                                    }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="
                                            getStatusBadgeClass(report.status)
                                        "
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ formatStatus(report.status) }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ formatDateTime(report.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Investigation Activities -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Recent Investigation Activities
                </h3>
                <div class="flow-root">
                    <ul class="-mb-8">
                        <li
                            v-for="(activity, index) in recentActivities"
                            :key="index"
                            class="relative pb-8"
                        >
                            <div
                                v-if="index !== recentActivities.length - 1"
                                class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                            ></div>
                            <div class="relative flex space-x-3">
                                <div>
                                    <span
                                        :class="
                                            getActivityIconClass(activity.type)
                                        "
                                        class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                    >
                                        <component
                                            :is="getActivityIcon(activity.type)"
                                            class="h-4 w-4"
                                        />
                                    </span>
                                </div>
                                <div
                                    class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4"
                                >
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            {{ activity.description }}
                                        </p>
                                    </div>
                                    <div
                                        class="text-right text-sm whitespace-nowrap text-gray-500"
                                    >
                                        <time>
                                            {{
                                                formatDateTime(
                                                    activity.timestamp
                                                )
                                            }}
                                        </time>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    ExclamationTriangleIcon,
    ClockIcon,
    CheckCircleIcon,
    ChartBarIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
    DocumentMagnifyingGlassIcon,
    UserGroupIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    stats: Object,
    chartData: Object,
    recentReports: Array,
    investigationStats: Object,
    recentActivities: Array,
});

// Methods
const getReportTypeColor = (type) => {
    const colors = {
        inappropriate_content: "bg-red-500",
        spam: "bg-yellow-500",
        fraud: "bg-red-600",
        harassment: "bg-purple-500",
        fake_listing: "bg-orange-500",
        other: "bg-gray-500",
    };
    return colors[type] || "bg-gray-500";
};

const getSeverityColor = (severity) => {
    const colors = {
        low: "bg-green-500",
        medium: "bg-yellow-500",
        high: "bg-orange-500",
        critical: "bg-red-500",
    };
    return colors[severity] || "bg-gray-500";
};

const formatReportType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatSeverity = (severity) => {
    return severity.charAt(0).toUpperCase() + severity.slice(1);
};

const formatStatus = (status) => {
    return status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
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

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        under_review: "bg-blue-100 text-blue-800",
        resolved: "bg-green-100 text-green-800",
        closed: "bg-gray-100 text-gray-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getActivityIconClass = (type) => {
    const classes = {
        evidence_collected: "bg-blue-100 text-blue-600",
        interview_conducted: "bg-green-100 text-green-600",
    };
    return classes[type] || "bg-gray-100 text-gray-600";
};

const getActivityIcon = (type) => {
    const icons = {
        evidence_collected: DocumentMagnifyingGlassIcon,
        interview_conducted: UserGroupIcon,
    };
    return icons[type] || ExclamationTriangleIcon;
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>
