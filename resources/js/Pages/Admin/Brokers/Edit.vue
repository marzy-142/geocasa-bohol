<script setup>
import { Head, useForm, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import ModernInput from "@/Components/ModernInput.vue";
import Modal from "@/Components/Modal.vue";
import { ref, computed } from "vue";
import {
    UserIcon,
    PencilIcon,
    DocumentCheckIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    XCircleIcon,
    ArrowLeftIcon,
    PhotoIcon,
    GlobeAltIcon,
    PhoneIcon,
    EnvelopeIcon,
    BuildingOfficeIcon,
    IdentificationIcon,
    AcademicCapIcon,
    StarIcon,
    ChartBarIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    broker: Object,
    specializations: Array,
    provinces: Array,
    cities: Array,
});

// Form for broker profile
const profileForm = useForm({
    // Basic Information
    name: props.broker.name || "",
    email: props.broker.email || "",
    phone: props.broker.phone || "",
    bio: props.broker.bio || "",
    profile_picture: null,

    // Professional Information
    prc_id: props.broker.prc_id || "",
    company_address: props.broker.company_address || "",
    years_experience: props.broker.years_experience || "",
    specialization: props.broker.specialization || [],

    // Location
    province: props.broker.province || "",
    city: props.broker.city || "",
    address: props.broker.address || "",

    // Online Presence
    website: props.broker.website || "",
    facebook: props.broker.facebook || "",
    instagram: props.broker.instagram || "",
    linkedin: props.broker.linkedin || "",

    // Status and Notes
    application_status: props.broker.application_status || "pending",
    admin_notes: props.broker.admin_notes || "",
});

// Form for document verification
const verificationForm = useForm({
    prc_verified: props.broker.prc_verified || false,
    prc_verification_notes: props.broker.prc_verification_notes || "",
    business_permit_verified: props.broker.business_permit_verified || false,
    business_permit_notes: props.broker.business_permit_notes || "",
    additional_documents_verified:
        props.broker.additional_documents_verified || false,
    additional_documents_notes: props.broker.additional_documents_notes || "",
});

// Form for status change
const statusForm = useForm({
    status: props.broker.application_status || "pending",
    reason: "",
    admin_notes: "",
    notify_broker: true,
});

// Modal states
const showStatusModal = ref(false);
const showVerificationModal = ref(false);
const showDocumentModal = ref(false);
const selectedDocument = ref(null);

// Computed properties
const filteredCities = computed(() => {
    if (!profileForm.province) return [];
    return props.cities.filter(
        (city) => city.province_id === profileForm.province
    );
});

const hasUnsavedChanges = computed(() => {
    return profileForm.isDirty;
});

// Methods
const updateProfile = () => {
    profileForm.patch(route("admin.brokers.update", props.broker.id), {
        onSuccess: () => {
            // Success handled by Inertia
        },
    });
};

const updateVerification = () => {
    verificationForm.patch(
        route("admin.brokers.update-verification", props.broker.id),
        {
            onSuccess: () => {
                showVerificationModal.value = false;
            },
        }
    );
};

const updateStatus = () => {
    statusForm.patch(route("admin.brokers.update-status", props.broker.id), {
        onSuccess: () => {
            showStatusModal.value = false;
            profileForm.application_status = statusForm.status;
        },
    });
};

const openStatusModal = () => {
    statusForm.status = profileForm.application_status;
    statusForm.reset("reason", "admin_notes");
    showStatusModal.value = true;
};

const openVerificationModal = () => {
    showVerificationModal.value = true;
};

const viewDocument = (documentType) => {
    selectedDocument.value = {
        type: documentType,
        url: props.broker[`${documentType}_url`],
        name: documentType.replace("_", " ").toUpperCase(),
    };
    showDocumentModal.value = true;
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        profileForm.profile_picture = file;
    }
};

const getStatusBadge = (status) => {
    const badges = {
        active: "bg-green-100 text-green-800",
        inactive: "bg-gray-100 text-gray-800",
        suspended: "bg-red-100 text-red-800",
        pending: "bg-yellow-100 text-yellow-800",
        under_review: "bg-blue-100 text-blue-800",
    };
    return badges[status] || "bg-gray-100 text-gray-800";
};

const getVerificationIcon = (verified) => {
    return verified ? CheckCircleIcon : XCircleIcon;
};

const getVerificationClass = (verified) => {
    return verified ? "text-green-600" : "text-red-600";
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>

<template>
    <ModernDashboardLayout>
        <Head :title="`Edit Broker - ${broker.name}`" />

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
                    <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                        Edit Broker Profile
                    </h1>
                    <p class="text-neutral-600">
                        Manage {{ broker.name }}'s profile, credentials, and
                        verification status
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span
                        :class="[
                            'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                            getStatusBadge(profileForm.application_status),
                        ]"
                    >
                        {{
                            profileForm.application_status
                                ?.replace("_", " ")
                                .toUpperCase()
                        }}
                    </span>
                    <ModernButton variant="outline" @click="openStatusModal">
                        Change Status
                    </ModernButton>
                    <ModernButton
                        variant="primary"
                        @click="updateProfile"
                        :disabled="profileForm.processing || !hasUnsavedChanges"
                    >
                        Save Changes
                    </ModernButton>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Profile Form -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Basic Information -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2
                        class="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2"
                    >
                        <UserIcon class="w-6 h-6 text-primary-600" />
                        Basic Information
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Profile Picture -->
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Profile Picture
                            </label>
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center overflow-hidden"
                                >
                                    <img
                                        v-if="broker.profile_picture_url"
                                        :src="broker.profile_picture_url"
                                        :alt="broker.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <PhotoIcon
                                        v-else
                                        class="w-8 h-8 text-gray-400"
                                    />
                                </div>
                                <div>
                                    <input
                                        type="file"
                                        @change="handleFileUpload"
                                        accept="image/*"
                                        class="hidden"
                                        ref="fileInput"
                                    />
                                    <ModernButton
                                        variant="outline"
                                        size="sm"
                                        @click="$refs.fileInput.click()"
                                    >
                                        Change Photo
                                    </ModernButton>
                                </div>
                            </div>
                        </div>

                        <!-- Name -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Full Name *
                            </label>
                            <ModernInput
                                v-model="profileForm.name"
                                type="text"
                                required
                                :error="profileForm.errors.name"
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Email Address *
                            </label>
                            <ModernInput
                                v-model="profileForm.email"
                                type="email"
                                required
                                :error="profileForm.errors.email"
                            />
                        </div>

                        <!-- Phone -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Phone Number *
                            </label>
                            <ModernInput
                                v-model="profileForm.phone"
                                type="tel"
                                required
                                :error="profileForm.errors.phone"
                            />
                        </div>

                        <!-- Bio -->
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Professional Bio
                            </label>
                            <textarea
                                v-model="profileForm.bio"
                                rows="4"
                                class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Tell us about your professional background and expertise..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2
                        class="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2"
                    >
                        <IdentificationIcon class="w-6 h-6 text-primary-600" />
                        Professional Information
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- License Number -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                PRC License Number *
                            </label>
                            <ModernInput
                                v-model="profileForm.prc_id"
                                type="text"
                                required
                                :error="profileForm.errors.prc_id"
                            />
                        </div>

                        <!-- Years of Experience -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Years of Experience
                            </label>
                            <select
                                v-model="profileForm.years_experience"
                                class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">Select experience</option>
                                <option value="0-1">0-1 years</option>
                                <option value="2-5">2-5 years</option>
                                <option value="6-10">6-10 years</option>
                                <option value="11-15">11-15 years</option>
                                <option value="16+">16+ years</option>
                            </select>
                        </div>

                        <!-- Company Name -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Company/Agency Name
                            </label>
                            <ModernInput
                                v-model="profileForm.company_name"
                                type="text"
                                :error="profileForm.errors.company_name"
                            />
                        </div>

                        <!-- Specialization -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Specialization
                            </label>
                            <select
                                v-model="profileForm.specialization"
                                multiple
                                class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option
                                    v-for="spec in specializations"
                                    :key="spec"
                                    :value="spec"
                                >
                                    {{ spec }}
                                </option>
                            </select>
                        </div>

                        <!-- Company Address -->
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Company Address
                            </label>
                            <textarea
                                v-model="profileForm.company_address"
                                rows="2"
                                class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Enter company address..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Location Information -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2
                        class="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2"
                    >
                        <BuildingOfficeIcon class="w-6 h-6 text-primary-600" />
                        Location Information
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Province -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Province *
                            </label>
                            <select
                                v-model="profileForm.province"
                                class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                                required
                            >
                                <option value="">Select Province</option>
                                <option
                                    v-for="province in provinces"
                                    :key="province.id"
                                    :value="province.id"
                                >
                                    {{ province.name }}
                                </option>
                            </select>
                        </div>

                        <!-- City -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                City/Municipality *
                            </label>
                            <select
                                v-model="profileForm.city"
                                class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                                required
                                :disabled="!profileForm.province"
                            >
                                <option value="">Select City</option>
                                <option
                                    v-for="city in filteredCities"
                                    :key="city.id"
                                    :value="city.id"
                                >
                                    {{ city.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Address -->
                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Complete Address
                            </label>
                            <textarea
                                v-model="profileForm.address"
                                rows="2"
                                class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                                placeholder="Enter complete address..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Online Presence -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2
                        class="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2"
                    >
                        <GlobeAltIcon class="w-6 h-6 text-primary-600" />
                        Online Presence
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Website -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Website
                            </label>
                            <ModernInput
                                v-model="profileForm.website"
                                type="url"
                                placeholder="https://"
                            />
                        </div>

                        <!-- Facebook -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Facebook
                            </label>
                            <ModernInput
                                v-model="profileForm.facebook"
                                type="url"
                                placeholder="https://facebook.com/"
                            />
                        </div>

                        <!-- Instagram -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Instagram
                            </label>
                            <ModernInput
                                v-model="profileForm.instagram"
                                type="url"
                                placeholder="https://instagram.com/"
                            />
                        </div>

                        <!-- LinkedIn -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                LinkedIn
                            </label>
                            <ModernInput
                                v-model="profileForm.linkedin"
                                type="url"
                                placeholder="https://linkedin.com/in/"
                            />
                        </div>
                    </div>
                </div>

                <!-- Admin Notes -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2
                        class="text-xl font-semibold text-gray-900 mb-6 flex items-center gap-2"
                    >
                        <PencilIcon class="w-6 h-6 text-primary-600" />
                        Admin Notes
                    </h2>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Internal Notes
                        </label>
                        <textarea
                            v-model="profileForm.admin_notes"
                            rows="4"
                            class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Add internal notes about this broker..."
                        ></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            These notes are only visible to administrators
                        </p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Broker Summary -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Broker Summary
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"
                                >Member Since</span
                            >
                            <span class="text-sm font-medium text-gray-900">
                                {{ formatDate(broker.created_at) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"
                                >Last Updated</span
                            >
                            <span class="text-sm font-medium text-gray-900">
                                {{ formatDate(broker.updated_at) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"
                                >Properties</span
                            >
                            <span class="text-sm font-medium text-gray-900">
                                {{ broker.properties_count || 0 }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Clients</span>
                            <span class="text-sm font-medium text-gray-900">
                                {{ broker.clients_count || 0 }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600"
                                >Transactions</span
                            >
                            <span class="text-sm font-medium text-gray-900">
                                {{ broker.transactions_count || 0 }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Document Verification -->
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Document Verification
                        </h3>
                        <ModernButton
                            variant="outline"
                            size="sm"
                            @click="openVerificationModal"
                        >
                            <DocumentCheckIcon class="w-4 h-4" />
                            Verify
                        </ModernButton>
                    </div>

                    <div class="space-y-3">
                        <!-- PRC License -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <component
                                    :is="
                                        getVerificationIcon(broker.prc_verified)
                                    "
                                    :class="[
                                        'w-5 h-5',
                                        getVerificationClass(
                                            broker.prc_verified
                                        ),
                                    ]"
                                />
                                <span class="text-sm text-gray-700"
                                    >PRC License</span
                                >
                            </div>
                            <ModernButton
                                v-if="broker.prc_id_url"
                                variant="ghost"
                                size="sm"
                                @click="viewDocument('prc_id')"
                            >
                                View
                            </ModernButton>
                        </div>

                        <!-- Business Permit -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <component
                                    :is="
                                        getVerificationIcon(
                                            broker.business_permit_verified
                                        )
                                    "
                                    :class="[
                                        'w-5 h-5',
                                        getVerificationClass(
                                            broker.business_permit_verified
                                        ),
                                    ]"
                                />
                                <span class="text-sm text-gray-700"
                                    >Business Permit</span
                                >
                            </div>
                            <ModernButton
                                v-if="broker.business_permit_url"
                                variant="ghost"
                                size="sm"
                                @click="viewDocument('business_permit')"
                            >
                                View
                            </ModernButton>
                        </div>

                        <!-- Additional Documents -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <component
                                    :is="
                                        getVerificationIcon(
                                            broker.additional_documents_verified
                                        )
                                    "
                                    :class="[
                                        'w-5 h-5',
                                        getVerificationClass(
                                            broker.additional_documents_verified
                                        ),
                                    ]"
                                />
                                <span class="text-sm text-gray-700"
                                    >Additional Docs</span
                                >
                            </div>
                            <ModernButton
                                v-if="broker.additional_documents_url"
                                variant="ghost"
                                size="sm"
                                @click="viewDocument('additional_documents')"
                            >
                                View
                            </ModernButton>
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
                            size="sm"
                            class="w-full justify-start"
                            :href="route('admin.brokers.show', broker.id)"
                        >
                            <UserIcon class="w-4 h-4" />
                            View Profile
                        </ModernButton>

                        <ModernButton
                            variant="outline"
                            size="sm"
                            class="w-full justify-start"
                            :href="route('admin.brokers.properties', broker.id)"
                        >
                            <BuildingOfficeIcon class="w-4 h-4" />
                            View Properties
                        </ModernButton>

                        <ModernButton
                            variant="outline"
                            size="sm"
                            :href="
                                route('admin.properties.index', {
                                    broker: broker.id,
                                })
                            "
                        >
                            View All
                        </ModernButton>

                        <ModernButton
                            variant="outline"
                            size="sm"
                            class="w-full justify-start"
                            :href="route('admin.reports.brokers')"
                        >
                            <ChartBarIcon class="w-4 h-4" />
                            View Analytics
                        </ModernButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Change Modal -->
        <Modal :show="showStatusModal" @close="showStatusModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Change Broker Status
                </h3>

                <div class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            New Status
                        </label>
                        <select
                            v-model="statusForm.status"
                            class="w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                            <option value="pending">Pending</option>
                            <option value="under_review">Under Review</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Reason
                        </label>
                        <select
                            v-model="statusForm.reason"
                            class="w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">Select a reason</option>
                            <option value="performance_issues">
                                Performance Issues
                            </option>
                            <option value="policy_violation">
                                Policy Violation
                            </option>
                            <option value="client_complaints">
                                Client Complaints
                            </option>
                            <option value="document_issues">
                                Document Issues
                            </option>
                            <option value="administrative">
                                Administrative
                            </option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Admin Notes
                        </label>
                        <textarea
                            v-model="statusForm.admin_notes"
                            rows="3"
                            class="w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Add notes about this status change..."
                        ></textarea>
                    </div>

                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            v-model="statusForm.notify_broker"
                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                        />
                        <span class="ml-2 text-sm text-gray-700"
                            >Notify broker via email</span
                        >
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <ModernButton
                        variant="outline"
                        @click="showStatusModal = false"
                    >
                        Cancel
                    </ModernButton>
                    <ModernButton
                        variant="primary"
                        @click="updateStatus"
                        :disabled="statusForm.processing"
                    >
                        Update Status
                    </ModernButton>
                </div>
            </div>
        </Modal>

        <!-- Verification Modal -->
        <Modal
            :show="showVerificationModal"
            @close="showVerificationModal = false"
        >
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Document Verification
                </h3>

                <div class="space-y-6">
                    <!-- PRC License Verification -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900">
                                PRC License
                            </h4>
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="verificationForm.prc_verified"
                                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                />
                                <span class="ml-2 text-sm text-gray-700"
                                    >Verified</span
                                >
                            </label>
                        </div>
                        <textarea
                            v-model="verificationForm.prc_verification_notes"
                            rows="2"
                            class="w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Add verification notes..."
                        ></textarea>
                    </div>

                    <!-- Business Permit Verification -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900">
                                Business Permit
                            </h4>
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="
                                        verificationForm.business_permit_verified
                                    "
                                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                />
                                <span class="ml-2 text-sm text-gray-700"
                                    >Verified</span
                                >
                            </label>
                        </div>
                        <textarea
                            v-model="verificationForm.business_permit_notes"
                            rows="2"
                            class="w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Add verification notes..."
                        ></textarea>
                    </div>

                    <!-- Additional Documents Verification -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-medium text-gray-900">
                                Additional Documents
                            </h4>
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="
                                        verificationForm.additional_documents_verified
                                    "
                                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                />
                                <span class="ml-2 text-sm text-gray-700"
                                    >Verified</span
                                >
                            </label>
                        </div>
                        <textarea
                            v-model="
                                verificationForm.additional_documents_notes
                            "
                            rows="2"
                            class="w-full rounded-md border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Add verification notes..."
                        ></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <ModernButton
                        variant="outline"
                        @click="showVerificationModal = false"
                    >
                        Cancel
                    </ModernButton>
                    <ModernButton
                        variant="primary"
                        @click="updateVerification"
                        :disabled="verificationForm.processing"
                    >
                        Update Verification
                    </ModernButton>
                </div>
            </div>
        </Modal>

        <!-- Document Viewer Modal -->
        <Modal :show="showDocumentModal" @close="showDocumentModal = false">
            <div class="p-6" v-if="selectedDocument">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ selectedDocument.name }}
                </h3>

                <div class="bg-gray-100 rounded-lg p-4 text-center">
                    <img
                        v-if="selectedDocument.url"
                        :src="selectedDocument.url"
                        :alt="selectedDocument.name"
                        class="max-w-full max-h-96 mx-auto"
                    />
                    <div v-else class="py-8">
                        <DocumentCheckIcon
                            class="w-12 h-12 text-gray-400 mx-auto mb-2"
                        />
                        <p class="text-gray-500">Document not available</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6">
                    <ModernButton
                        variant="outline"
                        @click="showDocumentModal = false"
                    >
                        Close
                    </ModernButton>
                    <ModernButton
                        v-if="selectedDocument.url"
                        variant="primary"
                        :href="selectedDocument.url"
                        target="_blank"
                    >
                        Download
                    </ModernButton>
                </div>
            </div>
        </Modal>
    </ModernDashboardLayout>
</template>
