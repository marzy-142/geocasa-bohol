<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import { ref, computed } from "vue";
import {
    UserIcon,
    ArrowLeftIcon,
    DocumentCheckIcon,
    DocumentTextIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    XCircleIcon,
    EyeIcon,
    ArrowDownTrayIcon,
    ClockIcon,
    ShieldCheckIcon,
    IdentificationIcon,
    BuildingOfficeIcon,
    CameraIcon,
    CalendarIcon,
    MapPinIcon,
    PhoneIcon,
    EnvelopeIcon,
    GlobeAltIcon,
    AcademicCapIcon,
    BriefcaseIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    broker: Object,
    documents: Object,
    verificationHistory: Array,
    verificationRequirements: Object,
});

// Document verification form
const verificationForm = useForm({
    document_type: "",
    status: "",
    notes: "",
    verified_at: null,
    expiry_date: null,
    verification_details: {},
});

// Modal states
const showVerificationModal = ref(false);
const selectedDocument = ref(null);
const showDocumentViewer = ref(false);
const selectedDocumentUrl = ref("");

// Document types configuration
const documentTypes = {
    prc_license: {
        name: "PRC License",
        icon: IdentificationIcon,
        color: "blue",
        required: true,
        fields: [
            "license_number",
            "issue_date",
            "expiry_date",
            "issuing_office",
        ],
    },
    business_permit: {
        name: "Business Permit",
        icon: BuildingOfficeIcon,
        color: "green",
        required: true,
        fields: [
            "permit_number",
            "business_name",
            "issue_date",
            "expiry_date",
            "issuing_lgu",
        ],
    },
    valid_id: {
        name: "Valid ID",
        icon: IdentificationIcon,
        color: "purple",
        required: true,
        fields: ["id_type", "id_number", "issue_date", "expiry_date"],
    },
    tax_identification: {
        name: "TIN Certificate",
        icon: DocumentTextIcon,
        color: "yellow",
        required: false,
        fields: ["tin_number", "issue_date"],
    },
    educational_certificate: {
        name: "Educational Certificate",
        icon: AcademicCapIcon,
        color: "indigo",
        required: false,
        fields: ["institution", "degree", "graduation_date"],
    },
    work_experience: {
        name: "Work Experience Certificate",
        icon: BriefcaseIcon,
        color: "gray",
        required: false,
        fields: ["company", "position", "start_date", "end_date"],
    },
};

// Computed properties
const verificationStatus = computed(() => {
    const requiredDocs = Object.entries(documentTypes)
        .filter(([key, config]) => config.required)
        .map(([key]) => key);

    const verifiedRequired = requiredDocs.filter(
        (docType) => props.documents[docType]?.status === "verified"
    ).length;

    const totalRequired = requiredDocs.length;
    const percentage =
        totalRequired > 0 ? (verifiedRequired / totalRequired) * 100 : 0;

    return {
        verified: verifiedRequired,
        total: totalRequired,
        percentage: Math.round(percentage),
        isComplete: percentage === 100,
        status:
            percentage === 100
                ? "complete"
                : percentage >= 50
                ? "partial"
                : "incomplete",
    };
});

const getDocumentStatus = (document) => {
    if (!document)
        return {
            status: "missing",
            class: "bg-gray-100 text-gray-800",
            text: "Not Submitted",
        };

    const statusConfig = {
        pending: {
            class: "bg-yellow-100 text-yellow-800",
            text: "Pending Review",
        },
        verified: { class: "bg-green-100 text-green-800", text: "Verified" },
        rejected: { class: "bg-red-100 text-red-800", text: "Rejected" },
        expired: { class: "bg-orange-100 text-orange-800", text: "Expired" },
        missing: { class: "bg-gray-100 text-gray-800", text: "Not Submitted" },
    };

    return statusConfig[document.status] || statusConfig.missing;
};

const isDocumentExpired = (document) => {
    if (!document?.expiry_date) return false;
    return new Date(document.expiry_date) < new Date();
};

const getExpiryWarning = (document) => {
    if (!document?.expiry_date) return null;

    const expiryDate = new Date(document.expiry_date);
    const today = new Date();
    const daysUntilExpiry = Math.ceil(
        (expiryDate - today) / (1000 * 60 * 60 * 24)
    );

    if (daysUntilExpiry < 0)
        return { type: "expired", message: "Expired", class: "text-red-600" };
    if (daysUntilExpiry <= 30)
        return {
            type: "warning",
            message: `Expires in ${daysUntilExpiry} days`,
            class: "text-orange-600",
        };
    if (daysUntilExpiry <= 90)
        return {
            type: "notice",
            message: `Expires in ${daysUntilExpiry} days`,
            class: "text-yellow-600",
        };

    return null;
};

// Methods
const openVerificationModal = (documentType, document = null) => {
    selectedDocument.value = { type: documentType, data: document };
    verificationForm.reset();

    if (document) {
        verificationForm.document_type = documentType;
        verificationForm.status = document.status;
        verificationForm.notes = document.notes || "";
        verificationForm.verified_at = document.verified_at;
        verificationForm.expiry_date = document.expiry_date;
        verificationForm.verification_details =
            document.verification_details || {};
    } else {
        verificationForm.document_type = documentType;
    }

    showVerificationModal.value = true;
};

const closeVerificationModal = () => {
    showVerificationModal.value = false;
    selectedDocument.value = null;
    verificationForm.reset();
};

const submitVerification = () => {
    verificationForm.post(
        route("admin.brokers.documents.verify", {
            broker: props.broker.id,
            document: selectedDocument.value.type,
        }),
        {
            onSuccess: () => {
                closeVerificationModal();
            },
        }
    );
};

const viewDocument = (documentUrl) => {
    selectedDocumentUrl.value = documentUrl;
    showDocumentViewer.value = true;
};

const downloadDocument = (documentUrl, filename) => {
    const link = document.createElement("a");
    link.href = documentUrl;
    link.download = filename;
    link.click();
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

const getStatusIcon = (status) => {
    const icons = {
        verified: CheckCircleIcon,
        rejected: XCircleIcon,
        pending: ClockIcon,
        expired: ExclamationTriangleIcon,
        missing: DocumentTextIcon,
    };
    return icons[status] || DocumentTextIcon;
};

const requestDocumentResubmission = (documentType) => {
    // Implementation for requesting document resubmission
    console.log("Requesting resubmission for:", documentType);
};

const bulkVerifyDocuments = () => {
    // Implementation for bulk verification
    console.log("Bulk verifying documents");
};
</script>

<template>
    <ModernDashboardLayout>
        <Head :title="`${broker.name} - Document Verification`" />

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <ModernButton
                    variant="ghost"
                    :href="route('admin.brokers.show', broker.id)"
                >
                    <ArrowLeftIcon class="w-5 h-5" />
                    Back to Profile
                </ModernButton>
            </div>

            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div class="flex items-center gap-6">
                    <!-- Profile Picture -->
                    <div
                        class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0"
                    >
                        <img
                            v-if="broker.profile_picture_url"
                            :src="broker.profile_picture_url"
                            :alt="broker.name"
                            class="w-full h-full object-cover"
                        />
                        <UserIcon v-else class="w-8 h-8 text-gray-400" />
                    </div>

                    <!-- Basic Info -->
                    <div>
                        <h1 class="text-2xl font-bold text-neutral-900 mb-2">
                            {{ broker.name }} - Document Verification
                        </h1>
                        <div
                            class="flex items-center gap-4 text-sm text-gray-600"
                        >
                            <span>{{ broker.email }}</span>
                            <span>{{ broker.phone }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <ModernButton
                        variant="outline"
                        @click="bulkVerifyDocuments"
                        :disabled="verificationStatus.isComplete"
                    >
                        <ShieldCheckIcon class="w-5 h-5" />
                        Bulk Verify
                    </ModernButton>
                    <ModernButton
                        variant="primary"
                        :href="
                            route('admin.brokers.documents.report', broker.id)
                        "
                    >
                        <DocumentTextIcon class="w-5 h-5" />
                        Generate Report
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Verification Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Overall Status -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Verification Status
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ verificationStatus.percentage }}%
                        </p>
                        <p
                            :class="[
                                'text-sm mt-1',
                                verificationStatus.status === 'complete'
                                    ? 'text-green-600'
                                    : verificationStatus.status === 'partial'
                                    ? 'text-yellow-600'
                                    : 'text-red-600',
                            ]"
                        >
                            {{ verificationStatus.verified }} of
                            {{ verificationStatus.total }} verified
                        </p>
                    </div>
                    <div
                        :class="[
                            'w-12 h-12 rounded-xl flex items-center justify-center',
                            verificationStatus.status === 'complete'
                                ? 'bg-green-100'
                                : verificationStatus.status === 'partial'
                                ? 'bg-yellow-100'
                                : 'bg-red-100',
                        ]"
                    >
                        <component
                            :is="
                                verificationStatus.status === 'complete'
                                    ? CheckCircleIcon
                                    : verificationStatus.status === 'partial'
                                    ? ClockIcon
                                    : ExclamationTriangleIcon
                            "
                            :class="[
                                'w-6 h-6',
                                verificationStatus.status === 'complete'
                                    ? 'text-green-600'
                                    : verificationStatus.status === 'partial'
                                    ? 'text-yellow-600'
                                    : 'text-red-600',
                            ]"
                        />
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                        :class="[
                            'h-2 rounded-full transition-all duration-300',
                            verificationStatus.status === 'complete'
                                ? 'bg-green-600'
                                : verificationStatus.status === 'partial'
                                ? 'bg-yellow-600'
                                : 'bg-red-600',
                        ]"
                        :style="{ width: `${verificationStatus.percentage}%` }"
                    ></div>
                </div>
            </div>

            <!-- Pending Reviews -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Pending Reviews
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{
                                Object.values(documents).filter(
                                    (doc) => doc?.status === "pending"
                                ).length
                            }}
                        </p>
                        <p class="text-sm text-yellow-600 mt-1">
                            Requires attention
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center"
                    >
                        <ClockIcon class="w-6 h-6 text-yellow-600" />
                    </div>
                </div>
            </div>

            <!-- Expiring Soon -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Expiring Soon
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{
                                Object.values(documents).filter((doc) => {
                                    const warning = getExpiryWarning(doc);
                                    return (
                                        warning &&
                                        (warning.type === "warning" ||
                                            warning.type === "expired")
                                    );
                                }).length
                            }}
                        </p>
                        <p class="text-sm text-orange-600 mt-1">
                            Within 30 days
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center"
                    >
                        <ExclamationTriangleIcon
                            class="w-6 h-6 text-orange-600"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Documents List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">
                            Document Verification
                        </h2>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600"
                                >{{
                                    Object.keys(documentTypes).length
                                }}
                                document types</span
                            >
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(config, docType) in documentTypes"
                            :key="docType"
                            class="border border-gray-100 rounded-xl p-6 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-4 flex-1">
                                    <!-- Document Icon -->
                                    <div
                                        :class="[
                                            'w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0',
                                            `bg-${config.color}-100`,
                                        ]"
                                    >
                                        <component
                                            :is="config.icon"
                                            :class="[
                                                'w-6 h-6',
                                                `text-${config.color}-600`,
                                            ]"
                                        />
                                    </div>

                                    <!-- Document Info -->
                                    <div class="flex-1">
                                        <div
                                            class="flex items-center gap-3 mb-2"
                                        >
                                            <h3
                                                class="font-semibold text-gray-900"
                                            >
                                                {{ config.name }}
                                            </h3>
                                            <span
                                                v-if="config.required"
                                                class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-md"
                                            >
                                                Required
                                            </span>
                                            <span
                                                :class="[
                                                    'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                                    getDocumentStatus(
                                                        documents[docType]
                                                    ).class,
                                                ]"
                                            >
                                                {{
                                                    getDocumentStatus(
                                                        documents[docType]
                                                    ).text
                                                }}
                                            </span>
                                        </div>

                                        <!-- Document Details -->
                                        <div
                                            v-if="documents[docType]"
                                            class="space-y-2"
                                        >
                                            <div
                                                class="grid grid-cols-2 gap-4 text-sm"
                                            >
                                                <div
                                                    v-if="
                                                        documents[docType]
                                                            .file_name
                                                    "
                                                >
                                                    <span class="text-gray-600"
                                                        >File:</span
                                                    >
                                                    <span
                                                        class="font-medium text-gray-900 ml-2"
                                                        >{{
                                                            documents[docType]
                                                                .file_name
                                                        }}</span
                                                    >
                                                </div>
                                                <div
                                                    v-if="
                                                        documents[docType]
                                                            .uploaded_at
                                                    "
                                                >
                                                    <span class="text-gray-600"
                                                        >Uploaded:</span
                                                    >
                                                    <span
                                                        class="font-medium text-gray-900 ml-2"
                                                        >{{
                                                            formatDate(
                                                                documents[
                                                                    docType
                                                                ].uploaded_at
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                                <div
                                                    v-if="
                                                        documents[docType]
                                                            .verified_at
                                                    "
                                                >
                                                    <span class="text-gray-600"
                                                        >Verified:</span
                                                    >
                                                    <span
                                                        class="font-medium text-gray-900 ml-2"
                                                        >{{
                                                            formatDate(
                                                                documents[
                                                                    docType
                                                                ].verified_at
                                                            )
                                                        }}</span
                                                    >
                                                </div>
                                                <div
                                                    v-if="
                                                        documents[docType]
                                                            .expiry_date
                                                    "
                                                >
                                                    <span class="text-gray-600"
                                                        >Expires:</span
                                                    >
                                                    <span
                                                        class="font-medium text-gray-900 ml-2"
                                                        >{{
                                                            formatDate(
                                                                documents[
                                                                    docType
                                                                ].expiry_date
                                                            )
                                                        }}</span
                                                    >
                                                    <span
                                                        v-if="
                                                            getExpiryWarning(
                                                                documents[
                                                                    docType
                                                                ]
                                                            )
                                                        "
                                                        :class="[
                                                            'ml-2 text-xs',
                                                            getExpiryWarning(
                                                                documents[
                                                                    docType
                                                                ]
                                                            ).class,
                                                        ]"
                                                    >
                                                        ({{
                                                            getExpiryWarning(
                                                                documents[
                                                                    docType
                                                                ]
                                                            ).message
                                                        }})
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Verification Details -->
                                            <div
                                                v-if="
                                                    documents[docType]
                                                        .verification_details
                                                "
                                                class="mt-3 p-3 bg-gray-50 rounded-lg"
                                            >
                                                <h4
                                                    class="text-sm font-medium text-gray-900 mb-2"
                                                >
                                                    Verification Details
                                                </h4>
                                                <div
                                                    class="grid grid-cols-2 gap-2 text-xs"
                                                >
                                                    <div
                                                        v-for="(
                                                            value, key
                                                        ) in documents[docType]
                                                            .verification_details"
                                                        :key="key"
                                                        class="flex justify-between"
                                                    >
                                                        <span
                                                            class="text-gray-600 capitalize"
                                                            >{{
                                                                key.replace(
                                                                    "_",
                                                                    " "
                                                                )
                                                            }}:</span
                                                        >
                                                        <span
                                                            class="font-medium text-gray-900"
                                                            >{{ value }}</span
                                                        >
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Admin Notes -->
                                            <div
                                                v-if="documents[docType].notes"
                                                class="mt-3 p-3 bg-blue-50 rounded-lg"
                                            >
                                                <h4
                                                    class="text-sm font-medium text-blue-900 mb-1"
                                                >
                                                    Admin Notes
                                                </h4>
                                                <p
                                                    class="text-sm text-blue-800"
                                                >
                                                    {{
                                                        documents[docType].notes
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Missing Document Message -->
                                        <div
                                            v-else
                                            class="text-sm text-gray-500"
                                        >
                                            No document submitted yet
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-2 ml-4">
                                    <ModernButton
                                        v-if="documents[docType]?.file_url"
                                        variant="ghost"
                                        size="sm"
                                        @click="
                                            viewDocument(
                                                documents[docType].file_url
                                            )
                                        "
                                    >
                                        <EyeIcon class="w-4 h-4" />
                                    </ModernButton>

                                    <ModernButton
                                        v-if="documents[docType]?.file_url"
                                        variant="ghost"
                                        size="sm"
                                        @click="
                                            downloadDocument(
                                                documents[docType].file_url,
                                                documents[docType].file_name
                                            )
                                        "
                                    >
                                        <ArrowDownTrayIcon class="w-4 h-4" />
                                    </ModernButton>

                                    <ModernButton
                                        variant="outline"
                                        size="sm"
                                        @click="
                                            openVerificationModal(
                                                docType,
                                                documents[docType]
                                            )
                                        "
                                    >
                                        {{
                                            documents[docType]
                                                ? "Update"
                                                : "Verify"
                                        }}
                                    </ModernButton>

                                    <ModernButton
                                        v-if="
                                            documents[docType] &&
                                            documents[docType].status ===
                                                'rejected'
                                        "
                                        variant="ghost"
                                        size="sm"
                                        @click="
                                            requestDocumentResubmission(docType)
                                        "
                                    >
                                        Request Resubmission
                                    </ModernButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Verification History -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Recent Activity
                    </h3>

                    <div v-if="verificationHistory?.length" class="space-y-4">
                        <div
                            v-for="activity in verificationHistory.slice(0, 5)"
                            :key="activity.id"
                            class="flex items-start gap-3"
                        >
                            <div
                                :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
                                    activity.action === 'verified'
                                        ? 'bg-green-100'
                                        : activity.action === 'rejected'
                                        ? 'bg-red-100'
                                        : activity.action === 'uploaded'
                                        ? 'bg-blue-100'
                                        : 'bg-gray-100',
                                ]"
                            >
                                <component
                                    :is="getStatusIcon(activity.action)"
                                    :class="[
                                        'w-4 h-4',
                                        activity.action === 'verified'
                                            ? 'text-green-600'
                                            : activity.action === 'rejected'
                                            ? 'text-red-600'
                                            : activity.action === 'uploaded'
                                            ? 'text-blue-600'
                                            : 'text-gray-600',
                                    ]"
                                />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ activity.document_name }}
                                    {{ activity.action }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    {{ formatDateTime(activity.created_at) }}
                                </p>
                                <p
                                    v-if="activity.admin_name"
                                    class="text-xs text-gray-500"
                                >
                                    by {{ activity.admin_name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-4">
                        <ClockIcon class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                        <p class="text-sm text-gray-500">No activity yet</p>
                    </div>
                </div>

                <!-- Verification Requirements -->
                <div class="bg-blue-50 rounded-2xl border border-blue-100 p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">
                        Verification Requirements
                    </h3>

                    <div class="space-y-3">
                        <div class="flex items-start gap-2">
                            <CheckCircleIcon
                                class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0"
                            />
                            <div>
                                <p class="text-sm font-medium text-blue-900">
                                    PRC License
                                </p>
                                <p class="text-xs text-blue-700">
                                    Valid real estate broker license
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <CheckCircleIcon
                                class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0"
                            />
                            <div>
                                <p class="text-sm font-medium text-blue-900">
                                    Business Permit
                                </p>
                                <p class="text-xs text-blue-700">
                                    Current business registration
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <CheckCircleIcon
                                class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0"
                            />
                            <div>
                                <p class="text-sm font-medium text-blue-900">
                                    Valid ID
                                </p>
                                <p class="text-xs text-blue-700">
                                    Government-issued identification
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-2">
                            <DocumentTextIcon
                                class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0"
                            />
                            <div>
                                <p class="text-sm font-medium text-blue-900">
                                    Supporting Documents
                                </p>
                                <p class="text-xs text-blue-700">
                                    TIN, certificates, experience proof
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Quick Actions
                    </h3>

                    <div class="space-y-3">
                        <ModernButton
                            variant="outline"
                            class="w-full justify-start"
                            :href="
                                route(
                                    'admin.brokers.documents.request',
                                    broker.id
                                )
                            "
                        >
                            <DocumentTextIcon class="w-4 h-4" />
                            Request Missing Documents
                        </ModernButton>

                        <ModernButton
                            variant="outline"
                            class="w-full justify-start"
                            :href="
                                route(
                                    'admin.brokers.documents.history',
                                    broker.id
                                )
                            "
                        >
                            <ClockIcon class="w-4 h-4" />
                            View Full History
                        </ModernButton>

                        <ModernButton
                            variant="outline"
                            class="w-full justify-start"
                            @click="() => {}"
                        >
                            <EnvelopeIcon class="w-4 h-4" />
                            Send Reminder
                        </ModernButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Modal -->
        <div
            v-if="showVerificationModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            @click="closeVerificationModal"
        >
            <div
                class="bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Verify {{ documentTypes[selectedDocument?.type]?.name }}
                    </h2>
                    <button
                        @click="closeVerificationModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <XCircleIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="submitVerification" class="space-y-6">
                    <!-- Status Selection -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Verification Status
                        </label>
                        <select
                            v-model="verificationForm.status"
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            required
                        >
                            <option value="">Select status...</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                            <option value="pending">Pending Review</option>
                        </select>
                    </div>

                    <!-- Expiry Date -->
                    <div v-if="verificationForm.status === 'verified'">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Expiry Date (if applicable)
                        </label>
                        <input
                            v-model="verificationForm.expiry_date"
                            type="date"
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>

                    <!-- Verification Details -->
                    <div
                        v-if="
                            verificationForm.status === 'verified' &&
                            documentTypes[selectedDocument?.type]?.fields
                        "
                    >
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Verification Details
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <div
                                v-for="field in documentTypes[
                                    selectedDocument?.type
                                ]?.fields"
                                :key="field"
                            >
                                <label
                                    class="block text-xs font-medium text-gray-600 mb-1"
                                >
                                    {{
                                        field
                                            .replace("_", " ")
                                            .replace(/\b\w/g, (l) =>
                                                l.toUpperCase()
                                            )
                                    }}
                                </label>
                                <input
                                    v-model="
                                        verificationForm.verification_details[
                                            field
                                        ]
                                    "
                                    type="text"
                                    class="w-full rounded-md border-gray-300 text-sm focus:border-primary-500 focus:ring-primary-500"
                                    :placeholder="`Enter ${field.replace(
                                        '_',
                                        ' '
                                    )}`"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Admin Notes
                        </label>
                        <textarea
                            v-model="verificationForm.notes"
                            rows="4"
                            class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            :placeholder="
                                verificationForm.status === 'rejected'
                                    ? 'Please provide reason for rejection...'
                                    : 'Optional notes about this verification...'
                            "
                        ></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div
                        class="flex items-center justify-end gap-3 pt-4 border-t"
                    >
                        <ModernButton
                            type="button"
                            variant="ghost"
                            @click="closeVerificationModal"
                        >
                            Cancel
                        </ModernButton>
                        <ModernButton
                            type="submit"
                            variant="primary"
                            :disabled="verificationForm.processing"
                        >
                            {{
                                verificationForm.processing
                                    ? "Saving..."
                                    : "Save Verification"
                            }}
                        </ModernButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Document Viewer Modal -->
        <div
            v-if="showDocumentViewer"
            class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50"
            @click="showDocumentViewer = false"
        >
            <div
                class="bg-white rounded-2xl p-4 w-full max-w-4xl max-h-[90vh] overflow-hidden"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Document Viewer
                    </h3>
                    <button
                        @click="showDocumentViewer = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <XCircleIcon class="w-6 h-6" />
                    </button>
                </div>
                <div class="h-full">
                    <iframe
                        :src="selectedDocumentUrl"
                        class="w-full h-96 border border-gray-300 rounded-lg"
                    ></iframe>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
