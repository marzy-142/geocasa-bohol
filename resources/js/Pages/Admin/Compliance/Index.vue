<template>
    <ModernDashboardLayout>
        <Head title="Compliance Management" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Compliance Management
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Monitor and manage compliance reports for properties,
                        inquiries, and user activities.
                    </p>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ExclamationTriangleIcon
                                        class="h-6 w-6 text-gray-400"
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
                                            {{ stats.total }}
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
                                            Pending
                                        </dt>
                                        <dd
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            {{ stats.pending }}
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
                                    <EyeIcon class="h-6 w-6 text-blue-400" />
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
                                    <ExclamationCircleIcon
                                        class="h-6 w-6 text-red-400"
                                    />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt
                                            class="text-sm font-medium text-gray-500 truncate"
                                        >
                                            High Priority
                                        </dt>
                                        <dd
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            {{ stats.high_priority }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white shadow rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Filters
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Status</label
                                >
                                <select
                                    v-model="filters.status"
                                    @change="applyFilters"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All Statuses</option>
                                    <option
                                        v-for="(label, value) in statuses"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Report Type</label
                                >
                                <select
                                    v-model="filters.report_type"
                                    @change="applyFilters"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All Types</option>
                                    <option
                                        v-for="(label, value) in reportTypes"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Severity</label
                                >
                                <select
                                    v-model="filters.severity"
                                    @change="applyFilters"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All Severities</option>
                                    <option
                                        v-for="(label, value) in severities"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Search</label
                                >
                                <input
                                    v-model="filters.search"
                                    @input="debounceSearch"
                                    type="text"
                                    placeholder="Search reports..."
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div
                    v-if="selectedReports.length > 0"
                    class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <CheckCircleIcon
                                class="h-5 w-5 text-blue-400 mr-2"
                            />
                            <span class="text-sm font-medium text-blue-800"
                                >{{ selectedReports.length }} reports
                                selected</span
                            >
                        </div>
                        <div class="flex space-x-2">
                            <button
                                @click="showBulkAssignModal = true"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Assign
                            </button>
                            <button
                                @click="bulkUpdateStatus('under_review')"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                            >
                                Mark Under Review
                            </button>
                            <button
                                @click="bulkUpdateStatus('dismissed')"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                            >
                                Dismiss
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Reports Table -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Compliance Reports
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        <input
                                            type="checkbox"
                                            @change="toggleSelectAll"
                                            :checked="allSelected"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Report
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
                                        Reported
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="report in reports.data"
                                    :key="report.id"
                                    class="hover:bg-gray-50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input
                                            type="checkbox"
                                            v-model="selectedReports"
                                            :value="report.id"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ report.description }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{
                                                report.reportable_type
                                                    .split("\\")
                                                    .pop()
                                            }}
                                            #{{ report.reportable_id }}
                                        </div>
                                        <div
                                            v-if="report.reporter"
                                            class="text-xs text-gray-400 mt-1"
                                        >
                                            Reported by:
                                            {{ report.reporter.name }}
                                        </div>
                                        <div
                                            v-else
                                            class="text-xs text-gray-400 mt-1"
                                        >
                                            System-generated
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="
                                                getReportTypeBadgeClass(
                                                    report.report_type
                                                )
                                            "
                                        >
                                            {{
                                                reportTypes[
                                                    report.report_type
                                                ] || report.report_type
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="
                                                getSeverityBadgeClass(
                                                    report.severity
                                                )
                                            "
                                        >
                                            {{
                                                severities[report.severity] ||
                                                report.severity
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="
                                                getStatusBadgeClass(
                                                    report.status
                                                )
                                            "
                                        >
                                            {{
                                                statuses[report.status] ||
                                                report.status
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        {{ formatDate(report.reported_at) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'admin.compliance.show',
                                                    report.id
                                                )
                                            "
                                            class="text-indigo-600 hover:text-indigo-900 mr-3"
                                        >
                                            View
                                        </Link>
                                        <button
                                            v-if="report.status === 'pending'"
                                            @click="
                                                updateReportStatus(
                                                    report,
                                                    'under_review'
                                                )
                                            "
                                            class="text-yellow-600 hover:text-yellow-900 mr-3"
                                        >
                                            Review
                                        </button>
                                        <button
                                            v-if="
                                                [
                                                    'pending',
                                                    'under_review',
                                                ].includes(report.status)
                                            "
                                            @click="
                                                updateReportStatus(
                                                    report,
                                                    'resolved'
                                                )
                                            "
                                            class="text-green-600 hover:text-green-900 mr-3"
                                        >
                                            Resolve
                                        </button>
                                        <button
                                            v-if="
                                                [
                                                    'pending',
                                                    'under_review',
                                                ].includes(report.status)
                                            "
                                            @click="
                                                updateReportStatus(
                                                    report,
                                                    'dismissed'
                                                )
                                            "
                                            class="text-gray-600 hover:text-gray-900"
                                        >
                                            Dismiss
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="reports.links"
                        class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <Link
                                    v-if="reports.prev_page_url"
                                    :href="reports.prev_page_url"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Previous
                                </Link>
                                <Link
                                    v-if="reports.next_page_url"
                                    :href="reports.next_page_url"
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
                                        Showing {{ reports.from }} to
                                        {{ reports.to }} of
                                        {{ reports.total }} results
                                    </p>
                                </div>
                                <div>
                                    <nav
                                        class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                    >
                                        <Link
                                            v-for="link in reports.links"
                                            :key="link.label"
                                            :href="link.url"
                                            v-html="link.label"
                                            :class="[
                                                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                link.active
                                                    ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                            ]"
                                        ></Link>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Assign Modal -->
        <TransitionRoot as="template" :show="showBulkAssignModal">
            <Dialog
                as="div"
                class="relative z-10"
                @close="showBulkAssignModal = false"
            >
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div
                        class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                    >
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                            >
                                <form @submit.prevent="bulkAssign">
                                    <div>
                                        <div
                                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100"
                                        >
                                            <UserGroupIcon
                                                class="h-6 w-6 text-blue-600"
                                            />
                                        </div>
                                        <div class="mt-3 text-center sm:mt-5">
                                            <DialogTitle
                                                as="h3"
                                                class="text-base font-semibold leading-6 text-gray-900"
                                            >
                                                Assign Reports
                                            </DialogTitle>
                                            <div class="mt-2">
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    Assign
                                                    {{
                                                        selectedReports.length
                                                    }}
                                                    selected reports to an admin
                                                    for review.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5">
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                            >Assign to Admin</label
                                        >
                                        <select
                                            v-model="bulkAssignForm.assigned_to"
                                            required
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">
                                                Select Admin
                                            </option>
                                            <!-- You would populate this with admin users -->
                                            <option value="1">
                                                Admin User 1
                                            </option>
                                            <option value="2">
                                                Admin User 2
                                            </option>
                                        </select>
                                    </div>
                                    <div
                                        class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3"
                                    >
                                        <button
                                            type="submit"
                                            :disabled="
                                                bulkAssignForm.processing
                                            "
                                            class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2 disabled:opacity-50"
                                        >
                                            <span
                                                v-if="bulkAssignForm.processing"
                                                >Assigning...</span
                                            >
                                            <span v-else>Assign Reports</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="showBulkAssignModal = false"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    ExclamationTriangleIcon,
    ClockIcon,
    EyeIcon,
    ExclamationCircleIcon,
    CheckCircleIcon,
    UserGroupIcon,
} from "@heroicons/vue/24/outline";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";

const props = defineProps({
    reports: Object,
    stats: Object,
    filters: Object,
    reportTypes: Object,
    severities: Object,
    statuses: Object,
});

// Reactive data
const selectedReports = ref([]);
const showBulkAssignModal = ref(false);
const filters = ref({
    status: props.filters.status || "",
    report_type: props.filters.report_type || "",
    severity: props.filters.severity || "",
    search: props.filters.search || "",
});

// Forms
const bulkAssignForm = useForm({
    report_ids: [],
    action: "assign",
    assigned_to: "",
});

// Computed
const allSelected = computed(() => {
    return (
        props.reports.data.length > 0 &&
        selectedReports.value.length === props.reports.data.length
    );
});

// Methods
const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedReports.value = [];
    } else {
        selectedReports.value = props.reports.data.map((report) => report.id);
    }
};

const applyFilters = () => {
    router.get(route("admin.compliance.index"), filters.value, {
        preserveState: true,
        replace: true,
    });
};

const debounceSearch = (() => {
    let timeout;
    return () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            applyFilters();
        }, 500);
    };
})();

const updateReportStatus = (report, status) => {
    router.patch(
        route("admin.compliance.update-status", report.id),
        {
            status: status,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Remove from selected if it was selected
                const index = selectedReports.value.indexOf(report.id);
                if (index > -1) {
                    selectedReports.value.splice(index, 1);
                }
            },
        }
    );
};

const bulkUpdateStatus = (status) => {
    if (selectedReports.value.length === 0) return;

    router.post(
        route("admin.compliance.bulk-update"),
        {
            report_ids: selectedReports.value,
            action: "update_status",
            status: status,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                selectedReports.value = [];
            },
        }
    );
};

const bulkAssign = () => {
    bulkAssignForm.report_ids = selectedReports.value;
    bulkAssignForm.post(route("admin.compliance.bulk-update"), {
        preserveScroll: true,
        onSuccess: () => {
            selectedReports.value = [];
            showBulkAssignModal.value = false;
            bulkAssignForm.reset();
        },
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        under_review: "bg-blue-100 text-blue-800",
        resolved: "bg-green-100 text-green-800",
        dismissed: "bg-gray-100 text-gray-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
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

const getReportTypeBadgeClass = (type) => {
    const classes = {
        suspicious_content: "bg-red-100 text-red-800",
        price_anomaly: "bg-orange-100 text-orange-800",
        spam_pattern: "bg-yellow-100 text-yellow-800",
        duplicate_listing: "bg-blue-100 text-blue-800",
        contact_bypass: "bg-purple-100 text-purple-800",
        incomplete_listing: "bg-gray-100 text-gray-800",
        excessive_activity: "bg-red-100 text-red-800",
        incomplete_profile: "bg-yellow-100 text-yellow-800",
    };
    return classes[type] || "bg-gray-100 text-gray-800";
};
</script>
