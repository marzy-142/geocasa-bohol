<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import { ref, computed } from "vue";
import {
    UserIcon,
    ArrowLeftIcon,
    DocumentCheckIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
    EyeIcon,
    DocumentTextIcon,
    CalendarIcon,
    FunnelIcon,
    MagnifyingGlassIcon,
    ArrowDownTrayIcon,
    ChatBubbleLeftRightIcon,
    PencilIcon,
    TrashIcon,
    UserPlusIcon,
    BuildingOfficeIcon,
    IdentificationIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    AcademicCapIcon,
    BriefcaseIcon,
    StarIcon,
    XMarkIcon,
    InformationCircleIcon,
    ExclamationCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    applications: Array,
    stats: Object,
    filters: Object,
});

// Forms
const approveForm = useForm({
    admin_notes: "",
    welcome_message: "",
    initial_commission_rate: 5.0,
    probation_period: 90,
    assigned_mentor: null,
});

const rejectForm = useForm({
    reason: "",
    admin_notes: "",
    feedback: "",
    reapplication_allowed: true,
    reapplication_date: null,
});

const reviewForm = useForm({
    status: "under_review",
    reviewer_notes: "",
    required_documents: [],
    follow_up_date: null,
});

// Modal states
const showApprovalModal = ref(false);
const showRejectionModal = ref(false);
const showReviewModal = ref(false);
const showApplicationDetails = ref(false);
const selectedApplication = ref(null);
const showBulkActions = ref(false);
const selectedApplications = ref([]);

// Filter states
const searchQuery = ref(props.filters?.search || "");
const selectedStatus = ref(props.filters?.status || "");
const selectedPriority = ref(props.filters?.priority || "");
const selectedReviewer = ref(props.filters?.reviewer || "");
const dateRange = ref({
    start: props.filters?.date_start || "",
    end: props.filters?.date_end || "",
});
const sortBy = ref(props.filters?.sort || "created_at");
const sortOrder = ref(props.filters?.order || "desc");

// Application statuses
const applicationStatuses = {
    pending: {
        name: "Pending Review",
        color: "yellow",
        icon: ClockIcon,
        class: "bg-yellow-100 text-yellow-800",
        description: "Awaiting initial review",
    },
    under_review: {
        name: "Under Review",
        color: "blue",
        icon: DocumentCheckIcon,
        class: "bg-blue-100 text-blue-800",
        description: "Currently being reviewed",
    },
    documents_required: {
        name: "Documents Required",
        color: "orange",
        icon: ExclamationTriangleIcon,
        class: "bg-orange-100 text-orange-800",
        description: "Additional documents needed",
    },
    approved: {
        name: "Approved",
        color: "green",
        icon: CheckCircleIcon,
        class: "bg-green-100 text-green-800",
        description: "Application approved",
    },
    rejected: {
        name: "Rejected",
        color: "red",
        icon: XCircleIcon,
        class: "bg-red-100 text-red-800",
        description: "Application rejected",
    },
    on_hold: {
        name: "On Hold",
        color: "gray",
        icon: ExclamationCircleIcon,
        class: "bg-gray-100 text-gray-800",
        description: "Application temporarily on hold",
    },
};

// Priority levels
const priorityLevels = {
    low: { name: "Low", color: "gray", class: "bg-gray-100 text-gray-800" },
    normal: {
        name: "Normal",
        color: "blue",
        class: "bg-blue-100 text-blue-800",
    },
    high: {
        name: "High",
        color: "yellow",
        class: "bg-yellow-100 text-yellow-800",
    },
    urgent: { name: "Urgent", color: "red", class: "bg-red-100 text-red-800" },
};

// Computed properties
const filteredApplications = computed(() => {
    let filtered = props.applications || [];

    if (searchQuery.value) {
        filtered = filtered.filter(
            (app) =>
                app.name
                    .toLowerCase()
                    .includes(searchQuery.value.toLowerCase()) ||
                app.email
                    .toLowerCase()
                    .includes(searchQuery.value.toLowerCase()) ||
                app.prc_id
                    ?.toLowerCase()
                    .includes(searchQuery.value.toLowerCase())
        );
    }

    if (selectedStatus.value) {
        filtered = filtered.filter(
            (app) => app.application_status === selectedStatus.value
        );
    }

    if (selectedPriority.value) {
        filtered = filtered.filter(
            (app) => app.priority === selectedPriority.value
        );
    }

    if (selectedReviewer.value) {
        filtered = filtered.filter(
            (app) => app.assigned_reviewer === selectedReviewer.value
        );
    }

    if (dateRange.value.start) {
        filtered = filtered.filter(
            (app) => new Date(app.created_at) >= new Date(dateRange.value.start)
        );
    }

    if (dateRange.value.end) {
        filtered = filtered.filter(
            (app) => new Date(app.created_at) <= new Date(dateRange.value.end)
        );
    }

    // Sort applications
    filtered.sort((a, b) => {
        let aValue = a[sortBy.value];
        let bValue = b[sortBy.value];

        if (sortBy.value === "created_at" || sortBy.value === "updated_at") {
            aValue = new Date(aValue);
            bValue = new Date(bValue);
        }

        if (sortOrder.value === "asc") {
            return aValue > bValue ? 1 : -1;
        } else {
            return aValue < bValue ? 1 : -1;
        }
    });

    return filtered;
});

const canApprove = computed(() => {
    return (
        selectedApplication.value &&
        selectedApplication.value.application_status !== "approved" &&
        selectedApplication.value.application_status !== "rejected"
    );
});

const canReject = computed(() => {
    return (
        selectedApplication.value &&
        selectedApplication.value.application_status !== "approved" &&
        selectedApplication.value.application_status !== "rejected"
    );
});

// Methods
const openApprovalModal = (application) => {
    selectedApplication.value = application;
    approveForm.reset();
    approveForm.initial_commission_rate = 5.0;
    approveForm.probation_period = 90;
    showApprovalModal.value = true;
};

const closeApprovalModal = () => {
    showApprovalModal.value = false;
    selectedApplication.value = null;
    approveForm.reset();
};

const openRejectionModal = (application) => {
    selectedApplication.value = application;
    rejectForm.reset();
    rejectForm.reapplication_allowed = true;
    showRejectionModal.value = true;
};

const closeRejectionModal = () => {
    showRejectionModal.value = false;
    selectedApplication.value = null;
    rejectForm.reset();
};

const openReviewModal = (application) => {
    selectedApplication.value = application;
    reviewForm.reset();
    reviewForm.status = application.application_status;
    showReviewModal.value = true;
};

const closeReviewModal = () => {
    showReviewModal.value = false;
    selectedApplication.value = null;
    reviewForm.reset();
};

const viewApplicationDetails = (application) => {
    selectedApplication.value = application;
    showApplicationDetails.value = true;
};

const closeApplicationDetails = () => {
    showApplicationDetails.value = false;
    selectedApplication.value = null;
};

const approveApplication = () => {
    approveForm.post(
        route(
            "admin.brokers.applications.approve",
            selectedApplication.value.id
        ),
        {
            onSuccess: () => {
                closeApprovalModal();
            },
        }
    );
};

const rejectApplication = () => {
    rejectForm.post(
        route(
            "admin.brokers.applications.reject",
            selectedApplication.value.id
        ),
        {
            onSuccess: () => {
                closeRejectionModal();
            },
        }
    );
};

const updateReviewStatus = () => {
    reviewForm.post(
        route(
            "admin.brokers.applications.review",
            selectedApplication.value.id
        ),
        {
            onSuccess: () => {
                closeReviewModal();
            },
        }
    );
};

const toggleApplicationSelection = (applicationId) => {
    const index = selectedApplications.value.indexOf(applicationId);
    if (index > -1) {
        selectedApplications.value.splice(index, 1);
    } else {
        selectedApplications.value.push(applicationId);
    }
};

const selectAllApplications = () => {
    selectedApplications.value = filteredApplications.value.map(
        (app) => app.id
    );
};

const clearAllSelections = () => {
    selectedApplications.value = [];
};

const bulkApprove = () => {
    if (selectedApplications.value.length === 0) return;

    if (
        confirm(
            `Are you sure you want to approve ${selectedApplications.value.length} applications?`
        )
    ) {
        // Implementation for bulk approve
        console.log("Bulk approving:", selectedApplications.value);
        selectedApplications.value = [];
    }
};

const bulkReject = () => {
    if (selectedApplications.value.length === 0) return;

    if (
        confirm(
            `Are you sure you want to reject ${selectedApplications.value.length} applications?`
        )
    ) {
        // Implementation for bulk reject
        console.log("Bulk rejecting:", selectedApplications.value);
        selectedApplications.value = [];
    }
};

const assignReviewer = (applicationId, reviewerId) => {
    // Implementation for assigning reviewer
    console.log(
        "Assigning reviewer:",
        reviewerId,
        "to application:",
        applicationId
    );
};

const setPriority = (applicationId, priority) => {
    // Implementation for setting priority
    console.log(
        "Setting priority:",
        priority,
        "for application:",
        applicationId
    );
};

const downloadDocument = (documentPath) => {
    window.open(`/storage/${documentPath}`, "_blank");
};

const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const formatDateTime = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getApplicationAge = (createdAt) => {
    const now = new Date();
    const created = new Date(createdAt);
    const diffTime = Math.abs(now - created);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 1) return "1 day ago";
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
    return `${Math.floor(diffDays / 30)} months ago`;
};

const getCompletionPercentage = (application) => {
    let completed = 0;
    let total = 6; // Total required fields

    if (application.name) completed++;
    if (application.email) completed++;
    if (application.phone) completed++;
    if (application.prc_id) completed++;
    if (application.business_permit) completed++;
    if (application.additional_documents) completed++;

    return Math.round((completed / total) * 100);
};

const clearFilters = () => {
    searchQuery.value = "";
    selectedStatus.value = "";
    selectedPriority.value = "";
    selectedReviewer.value = "";
    dateRange.value = { start: "", end: "" };
};

const applyFilters = () => {
    // Implementation for applying filters
    console.log("Applying filters");
};
</script>

<template>
    <ModernDashboardLayout>
        <Head title="Broker Registration" />

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <ModernButton
                    variant="ghost"
                    :href="route('admin.brokers.index')"
                >
                    <ArrowLeftIcon class="w-5 h-5" />
                    Back to Brokers
                </ModernButton>
            </div>

            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900 mb-2">
                        Broker Registration & Applications
                    </h1>
                    <p class="text-gray-600">
                        Review, approve, and manage broker applications with
                        enhanced workflow
                    </p>
                </div>

                <!-- Quick Actions -->
                <div class="flex items-center gap-3">
                    <ModernButton
                        variant="outline"
                        @click="selectAllApplications"
                        v-if="filteredApplications.length > 0"
                    >
                        Select All
                    </ModernButton>
                    <ModernButton
                        variant="primary"
                        :href="route('admin.brokers.applications.export')"
                    >
                        <ArrowDownTrayIcon class="w-5 h-5" />
                        Export Data
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Total Applications
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ stats?.total || 0 }}
                        </p>
                        <p class="text-sm text-blue-600 mt-1">
                            This month: {{ stats?.monthly || 0 }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center"
                    >
                        <UserPlusIcon class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Pending Review
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ stats?.pending || 0 }}
                        </p>
                        <p class="text-sm text-yellow-600 mt-1">
                            Avg. wait: {{ stats?.avg_wait_days || 0 }} days
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center"
                    >
                        <ClockIcon class="w-6 h-6 text-yellow-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Under Review
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ stats?.under_review || 0 }}
                        </p>
                        <p class="text-sm text-blue-600 mt-1">In progress</p>
                    </div>
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center"
                    >
                        <DocumentCheckIcon class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Approved
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ stats?.approved || 0 }}
                        </p>
                        <p class="text-sm text-green-600 mt-1">
                            {{ stats?.approval_rate || 0 }}% rate
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center"
                    >
                        <CheckCircleIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Rejected
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ stats?.rejected || 0 }}
                        </p>
                        <p class="text-sm text-red-600 mt-1">
                            {{ stats?.rejection_rate || 0 }}% rate
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center"
                    >
                        <XCircleIcon class="w-6 h-6 text-red-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">
                    Filters & Search
                </h2>
                <div class="flex items-center gap-2">
                    <ModernButton
                        variant="ghost"
                        size="sm"
                        @click="clearFilters"
                    >
                        Clear All
                    </ModernButton>
                    <ModernButton
                        variant="outline"
                        size="sm"
                        @click="applyFilters"
                    >
                        <FunnelIcon class="w-4 h-4" />
                        Apply Filters
                    </ModernButton>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Search</label
                    >
                    <div class="relative">
                        <MagnifyingGlassIcon
                            class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Name, email, PRC ID..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        />
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Status</label
                    >
                    <select
                        v-model="selectedStatus"
                        class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">All Status</option>
                        <option
                            v-for="(config, status) in applicationStatuses"
                            :key="status"
                            :value="status"
                        >
                            {{ config.name }}
                        </option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Priority</label
                    >
                    <select
                        v-model="selectedPriority"
                        class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">All Priorities</option>
                        <option
                            v-for="(config, priority) in priorityLevels"
                            :key="priority"
                            :value="priority"
                        >
                            {{ config.name }}
                        </option>
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Sort By</label
                    >
                    <select
                        v-model="sortBy"
                        class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="created_at">Application Date</option>
                        <option value="updated_at">Last Updated</option>
                        <option value="name">Name</option>
                        <option value="priority">Priority</option>
                    </select>
                </div>

                <!-- Sort Order -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Order</label
                    >
                    <select
                        v-model="sortOrder"
                        class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="desc">Newest First</option>
                        <option value="asc">Oldest First</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Date Range</label
                    >
                    <div class="flex gap-1">
                        <input
                            v-model="dateRange.start"
                            type="date"
                            class="flex-1 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 text-sm"
                        />
                        <input
                            v-model="dateRange.end"
                            type="date"
                            class="flex-1 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 text-sm"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div
            v-if="selectedApplications.length > 0"
            class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <InformationCircleIcon class="w-5 h-5 text-blue-600" />
                    <span class="text-sm font-medium text-blue-900">
                        {{ selectedApplications.length }} applications selected
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <ModernButton
                        variant="outline"
                        size="sm"
                        @click="bulkApprove"
                    >
                        <CheckCircleIcon class="w-4 h-4" />
                        Bulk Approve
                    </ModernButton>
                    <ModernButton
                        variant="outline"
                        size="sm"
                        @click="bulkReject"
                    >
                        <XCircleIcon class="w-4 h-4" />
                        Bulk Reject
                    </ModernButton>
                    <ModernButton
                        variant="ghost"
                        size="sm"
                        @click="clearAllSelections"
                    >
                        Clear Selection
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Applications List -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    Applications ({{ filteredApplications.length }})
                </h2>
            </div>

            <div v-if="filteredApplications.length" class="space-y-4">
                <div
                    v-for="application in filteredApplications"
                    :key="application.id"
                    class="border border-gray-100 rounded-xl p-6 hover:bg-gray-50 transition-colors"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4 flex-1">
                            <!-- Selection Checkbox -->
                            <input
                                type="checkbox"
                                :checked="
                                    selectedApplications.includes(
                                        application.id
                                    )
                                "
                                @change="
                                    toggleApplicationSelection(application.id)
                                "
                                class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            />

                            <!-- Applicant Avatar -->
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0"
                            >
                                <span class="text-lg font-semibold text-white">
                                    {{
                                        application.name.charAt(0).toUpperCase()
                                    }}
                                </span>
                            </div>

                            <!-- Application Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="font-semibold text-gray-900">
                                        {{ application.name }}
                                    </h3>
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                            applicationStatuses[
                                                application.application_status
                                            ]?.class,
                                        ]"
                                    >
                                        {{
                                            applicationStatuses[
                                                application.application_status
                                            ]?.name
                                        }}
                                    </span>
                                    <span
                                        v-if="application.priority"
                                        :class="[
                                            'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                            priorityLevels[application.priority]
                                                ?.class,
                                        ]"
                                    >
                                        {{
                                            priorityLevels[application.priority]
                                                ?.name
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3"
                                >
                                    <div
                                        class="flex items-center gap-2 text-sm text-gray-600"
                                    >
                                        <EnvelopeIcon class="w-4 h-4" />
                                        <span>{{ application.email }}</span>
                                    </div>
                                    <div
                                        class="flex items-center gap-2 text-sm text-gray-600"
                                    >
                                        <PhoneIcon class="w-4 h-4" />
                                        <span>{{
                                            application.phone || "N/A"
                                        }}</span>
                                    </div>
                                    <div
                                        class="flex items-center gap-2 text-sm text-gray-600"
                                    >
                                        <IdentificationIcon class="w-4 h-4" />
                                        <span
                                            >PRC:
                                            {{
                                                application.prc_id || "N/A"
                                            }}</span
                                        >
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div class="mb-3">
                                    <div
                                        class="flex items-center justify-between text-sm mb-1"
                                    >
                                        <span class="text-gray-600"
                                            >Application Completion</span
                                        >
                                        <span class="font-medium text-gray-900"
                                            >{{
                                                getCompletionPercentage(
                                                    application
                                                )
                                            }}%</span
                                        >
                                    </div>
                                    <div
                                        class="w-full bg-gray-200 rounded-full h-2"
                                    >
                                        <div
                                            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                            :style="{
                                                width:
                                                    getCompletionPercentage(
                                                        application
                                                    ) + '%',
                                            }"
                                        ></div>
                                    </div>
                                </div>

                                <div
                                    class="flex items-center gap-6 text-sm text-gray-500"
                                >
                                    <div class="flex items-center gap-1">
                                        <CalendarIcon class="w-4 h-4" />
                                        <span
                                            >Applied
                                            {{
                                                getApplicationAge(
                                                    application.created_at
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <DocumentTextIcon class="w-4 h-4" />
                                        <span
                                            >{{
                                                application.documents_count || 0
                                            }}
                                            documents</span
                                        >
                                    </div>
                                    <div
                                        v-if="application.assigned_reviewer"
                                        class="flex items-center gap-1"
                                    >
                                        <UserIcon class="w-4 h-4" />
                                        <span
                                            >Reviewer:
                                            {{
                                                application.assigned_reviewer
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 ml-4">
                            <ModernButton
                                variant="ghost"
                                size="sm"
                                @click="viewApplicationDetails(application)"
                            >
                                <EyeIcon class="w-4 h-4" />
                            </ModernButton>

                            <ModernButton
                                variant="ghost"
                                size="sm"
                                @click="openReviewModal(application)"
                            >
                                <PencilIcon class="w-4 h-4" />
                            </ModernButton>

                            <ModernButton
                                v-if="canReject"
                                variant="outline"
                                size="sm"
                                @click="openRejectionModal(application)"
                            >
                                <XCircleIcon class="w-4 h-4" />
                                Reject
                            </ModernButton>

                            <ModernButton
                                v-if="canApprove"
                                variant="primary"
                                size="sm"
                                @click="openApprovalModal(application)"
                            >
                                <CheckCircleIcon class="w-4 h-4" />
                                Approve
                            </ModernButton>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12">
                <UserPlusIcon class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                <p class="text-gray-500">No applications found</p>
                <p class="text-sm text-gray-400 mt-1">
                    Try adjusting your filters or search criteria
                </p>
            </div>
        </div>

        <!-- Approval Modal -->
        <div
            v-if="showApprovalModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            @click="closeApprovalModal"
        >
            <div
                class="bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Approve Application
                    </h2>
                    <button
                        @click="closeApprovalModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="mb-6">
                    <div
                        class="flex items-center gap-4 p-4 bg-green-50 rounded-lg"
                    >
                        <div
                            class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center"
                        >
                            <CheckCircleIcon class="w-6 h-6 text-green-600" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-green-900">
                                {{ selectedApplication?.name }}
                            </h3>
                            <p class="text-sm text-green-700">
                                {{ selectedApplication?.email }}
                            </p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="approveApplication" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Initial Commission Rate (%)
                            </label>
                            <input
                                v-model="approveForm.initial_commission_rate"
                                type="number"
                                step="0.1"
                                min="0"
                                max="100"
                                class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                required
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Probation Period (days)
                            </label>
                            <input
                                v-model="approveForm.probation_period"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                required
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Welcome Message
                        </label>
                        <textarea
                            v-model="approveForm.welcome_message"
                            rows="3"
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Welcome message to be sent to the new broker..."
                        ></textarea>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Admin Notes
                        </label>
                        <textarea
                            v-model="approveForm.admin_notes"
                            rows="3"
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Internal notes about this approval..."
                        ></textarea>
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 pt-4 border-t"
                    >
                        <ModernButton
                            type="button"
                            variant="ghost"
                            @click="closeApprovalModal"
                        >
                            Cancel
                        </ModernButton>
                        <ModernButton
                            type="submit"
                            variant="primary"
                            :disabled="approveForm.processing"
                        >
                            {{
                                approveForm.processing
                                    ? "Approving..."
                                    : "Approve Application"
                            }}
                        </ModernButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rejection Modal -->
        <div
            v-if="showRejectionModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            @click="closeRejectionModal"
        >
            <div
                class="bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Reject Application
                    </h2>
                    <button
                        @click="closeRejectionModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="mb-6">
                    <div
                        class="flex items-center gap-4 p-4 bg-red-50 rounded-lg"
                    >
                        <div
                            class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center"
                        >
                            <XCircleIcon class="w-6 h-6 text-red-600" />
                        </div>
                        <div>
                            <h3 class="font-semibold text-red-900">
                                {{ selectedApplication?.name }}
                            </h3>
                            <p class="text-sm text-red-700">
                                {{ selectedApplication?.email }}
                            </p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="rejectApplication" class="space-y-6">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Rejection Reason <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="rejectForm.reason"
                            rows="4"
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Provide a clear reason for rejection..."
                            required
                        ></textarea>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Feedback for Applicant
                        </label>
                        <textarea
                            v-model="rejectForm.feedback"
                            rows="3"
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Constructive feedback to help them improve..."
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center">
                            <input
                                v-model="rejectForm.reapplication_allowed"
                                type="checkbox"
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            />
                            <label class="ml-2 text-sm text-gray-700">
                                Allow reapplication
                            </label>
                        </div>

                        <div v-if="rejectForm.reapplication_allowed">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Earliest Reapplication Date
                            </label>
                            <input
                                v-model="rejectForm.reapplication_date"
                                type="date"
                                class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Admin Notes
                        </label>
                        <textarea
                            v-model="rejectForm.admin_notes"
                            rows="2"
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Internal notes about this rejection..."
                        ></textarea>
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 pt-4 border-t"
                    >
                        <ModernButton
                            type="button"
                            variant="ghost"
                            @click="closeRejectionModal"
                        >
                            Cancel
                        </ModernButton>
                        <ModernButton
                            type="submit"
                            variant="danger"
                            :disabled="
                                rejectForm.processing || !rejectForm.reason
                            "
                        >
                            {{
                                rejectForm.processing
                                    ? "Rejecting..."
                                    : "Reject Application"
                            }}
                        </ModernButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Application Details Modal -->
        <div
            v-if="showApplicationDetails && selectedApplication"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            @click="closeApplicationDetails"
        >
            <div
                class="bg-white rounded-2xl p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Application Details
                    </h2>
                    <button
                        @click="closeApplicationDetails"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="space-y-6">
                    <!-- Applicant Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-4"
                            >
                                Personal Information
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label
                                        class="text-sm font-medium text-gray-600"
                                        >Full Name</label
                                    >
                                    <p class="text-gray-900">
                                        {{ selectedApplication.name }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="text-sm font-medium text-gray-600"
                                        >Email</label
                                    >
                                    <p class="text-gray-900">
                                        {{ selectedApplication.email }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="text-sm font-medium text-gray-600"
                                        >Phone</label
                                    >
                                    <p class="text-gray-900">
                                        {{ selectedApplication.phone || "N/A" }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="text-sm font-medium text-gray-600"
                                        >Address</label
                                    >
                                    <p class="text-gray-900">
                                        {{
                                            selectedApplication.address || "N/A"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-4"
                            >
                                Professional Information
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label
                                        class="text-sm font-medium text-gray-600"
                                        >PRC License ID</label
                                    >
                                    <p class="text-gray-900">
                                        {{
                                            selectedApplication.prc_id || "N/A"
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="text-sm font-medium text-gray-600"
                                        >Business Permit</label
                                    >
                                    <p class="text-gray-900">
                                        {{
                                            selectedApplication.business_permit ||
                                            "N/A"
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="text-sm font-medium text-gray-600"
                                        >Years of Experience</label
                                    >
                                    <p class="text-gray-900">
                                        {{
                                            selectedApplication.years_experience ||
                                            "N/A"
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="text-sm font-medium text-gray-600"
                                        >Specialization</label
                                    >
                                    <p class="text-gray-900">
                                        {{
                                            selectedApplication.specialization ||
                                            "N/A"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Submitted Documents
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div
                                v-if="selectedApplication.prc_id_file"
                                class="border border-gray-200 rounded-lg p-4"
                            >
                                <div
                                    class="flex items-center justify-between mb-2"
                                >
                                    <span
                                        class="text-sm font-medium text-gray-900"
                                        >PRC License</span
                                    >
                                    <CheckCircleIcon
                                        class="w-5 h-5 text-green-500"
                                    />
                                </div>
                                <ModernButton
                                    variant="outline"
                                    size="sm"
                                    @click="
                                        downloadDocument(
                                            selectedApplication.prc_id_file
                                        )
                                    "
                                >
                                    <ArrowDownTrayIcon class="w-4 h-4" />
                                    Download
                                </ModernButton>
                            </div>

                            <div
                                v-if="selectedApplication.business_permit_file && selectedApplication.prc_id_file"
                                class="border border-gray-200 rounded-lg p-4"
                            >
                                <div
                                    class="flex items-center justify-between mb-2"
                                >
                                    <span
                                        class="text-sm font-medium text-gray-900"
                                        >Business Permit</span
                                    >
                                    <CheckCircleIcon
                                        class="w-5 h-5 text-green-500"
                                    />
                                </div>
                                <ModernButton
                                    variant="outline"
                                    size="sm"
                                    @click="
                                        downloadDocument(
                                            selectedApplication.business_permit_file
                                        )
                                    "
                                >
                                    <ArrowDownTrayIcon class="w-4 h-4" />
                                    Download
                                </ModernButton>
                            </div>
                        </div>
                    </div>

                    <!-- Application Timeline -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Application Timeline
                        </h3>
                        <div class="space-y-3">
                            <div
                                class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg"
                            >
                                <div
                                    class="w-2 h-2 bg-blue-500 rounded-full"
                                ></div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Application Submitted
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        {{
                                            formatDateTime(
                                                selectedApplication.created_at
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="selectedApplication.reviewed_at"
                                class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg"
                            >
                                <div
                                    class="w-2 h-2 bg-yellow-500 rounded-full"
                                ></div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Review Started
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        {{
                                            formatDateTime(
                                                selectedApplication.reviewed_at
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="selectedApplication.approved_at"
                                class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg"
                            >
                                <div
                                    class="w-2 h-2 bg-green-500 rounded-full"
                                ></div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Application Approved
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        {{
                                            formatDateTime(
                                                selectedApplication.approved_at
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
