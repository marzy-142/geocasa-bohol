<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ModernInput from "@/Components/ModernInput.vue";
import ModernButton from "@/Components/ModernButton.vue";
import FileUpload from "@/Components/FileUpload.vue";
import GeoCasaLogo from "@/Components/GeoCasaLogo.vue";
import TermsOfServiceModal from "@/Components/TermsOfServiceModal.vue";
import PrivacyPolicyModal from "@/Components/PrivacyPolicyModal.vue";
import {
    UserIcon,
    EnvelopeIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
    BriefcaseIcon,
    UserGroupIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    BuildingOfficeIcon,
} from "@heroicons/vue/24/outline";

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const clientValidationErrors = ref({});
const showTermsModal = ref(false);
const showPrivacyModal = ref(false);

const form = useForm({
    name: "",
    email: "",
    role: "client",
    phone: "",
    prc_id: "", // Keep as prc_id to match database
    prc_license_expiration: "",
    years_experience: "",
    brokerage_firm_name: "",
    office_address: "",
    office_contact_number: "",
    postal_code: "",
    information_certified: false,
    prc_verification_consent: false,
    prc_id_file: null,
    additional_documents: [],
    city: "",
    province: "",
    address: "",
    terms_accepted: false,
    privacy_policy_accepted: false,
    password: "",
    password_confirmation: "",
});

// Client-side validation rules
const validateForm = () => {
    const errors = {};

    // Basic field validation
    if (!form.name.trim()) {
        errors.name = "Full name is required";
    }

    if (!form.email.trim()) {
        errors.email = "Email address is required";
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = "Please enter a valid email address";
    }

    if (!form.password) {
        errors.password = "Password is required";
    } else if (form.password.length < 8) {
        errors.password = "Password must be at least 8 characters long";
    }

    if (form.password !== form.password_confirmation) {
        errors.password_confirmation = "Password confirmation does not match";
    }

    // Broker-specific validation
    if (form.role === "broker") {
        if (!form.prc_id.trim()) {
            errors.prc_id = "PRC License Number is required for brokers";
        }

        // Remove this required validation since it's optional
        // if (!form.prc_id_file) {
        //     errors.prc_id_file = "PRC License Document is required for brokers";
        // }

        if (!form.prc_license_expiration) {
            errors.prc_license_expiration =
                "PRC License expiration date is required";
        }

        if (!form.city.trim()) {
            errors.city = "City is required for brokers";
        }

        if (!form.province.trim()) {
            errors.province = "Province is required for brokers";
        }

        if (!form.address.trim()) {
            errors.address = "Complete address is required for brokers";
        }

        if (!form.information_certified) {
            errors.information_certified =
                "You must certify that your information is correct";
        }

        if (!form.prc_verification_consent) {
            errors.prc_verification_consent =
                "You must consent to PRC verification";
        }

        if (!form.terms_accepted) {
            errors.terms_accepted = "You must accept the Terms of Service";
        }

        if (!form.privacy_policy_accepted) {
            errors.privacy_policy_accepted =
                "You must accept the Privacy Policy";
        }
    }

    clientValidationErrors.value = errors;
    return Object.keys(errors).length === 0;
};

// Handle file validation errors
const handleFileValidationError = (field, error) => {
    // Create more detailed error message
    let detailedError = error;

    // Add context-specific information
    if (field === "prc_id_file") {
        detailedError = `PRC ID Upload Error: ${error}`;
    } else if (field === "business_permit_file") {
        detailedError = `Business Permit Upload Error: ${error}`;
    } else if (field.includes("additional_documents")) {
        detailedError = `Additional Document Upload Error: ${error}`;
    }

    clientValidationErrors.value = {
        ...clientValidationErrors.value,
        [field]: detailedError,
    };

    // Show toast notification for immediate feedback
    if (window.toast) {
        window.toast.error(`Upload failed: ${error}`, {
            duration: 5000,
            position: "top-right",
        });
    }
};

// Enhanced form submission with better error handling
const submit = () => {
    // Clear previous errors
    clientValidationErrors.value = {};

    // Validate form before submission
    if (!validateForm()) {
        // Show summary of validation errors
        const errorCount = Object.keys(clientValidationErrors.value).length;
        if (window.toast && errorCount > 0) {
            window.toast.error(
                `Please fix ${errorCount} validation error${
                    errorCount > 1 ? "s" : ""
                } before submitting.`,
                {
                    duration: 4000,
                }
            );
        }
        return;
    }

    form.post(route("register"), {
        onError: (errors) => {
            // Enhanced error handling for file upload errors
            Object.keys(errors).forEach((field) => {
                if (field.includes("file") && errors[field]) {
                    // Show specific file upload error notification
                    if (window.toast) {
                        window.toast.error(
                            `File Upload Error: ${errors[field]}`,
                            {
                                duration: 6000,
                                position: "top-right",
                            }
                        );
                    }
                }
            });
        },
        onSuccess: () => {
            if (window.toast) {
                window.toast.success("Registration submitted successfully!", {
                    duration: 3000,
                });
            }
        },
    });
};

// Clear validation error for a specific field
const clearValidationError = (field) => {
    if (clientValidationErrors.value[field]) {
        const { [field]: removed, ...rest } = clientValidationErrors.value;
        clientValidationErrors.value = rest;
    }
};

// Combined errors (server + client)
const getFieldError = (field) => {
    return form.errors[field] || clientValidationErrors.value[field];
};

// Check if form can be submitted
const canSubmit = computed(() => {
    return (
        !form.processing &&
        Object.keys(clientValidationErrors.value).length === 0
    );
});
</script>

<template>
    <Head title="Register" />

    <div
        class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-blue-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-md w-full space-y-8">
            <!-- Standardized Header -->
            <div class="text-center">
                <Link :href="route('home')" class="inline-block">
                    <GeoCasaLogo
                        size="medium"
                        variant="default"
                        :show-text="true"
                        layout="horizontal"
                    />
                </Link>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">
                    Create Your Account
                </h2>
                <p class="mt-2 text-gray-600">
                    Join GeoCasa Bohol and discover your dream property in
                    paradise
                </p>
            </div>

            <!-- Registration Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Name -->
                <div>
                    <ModernInput
                        v-model="form.name"
                        :icon="UserIcon"
                        type="text"
                        label="Full Name"
                        placeholder="Enter your full name"
                        :error="getFieldError('name')"
                        @input="clearValidationError('name')"
                        required
                    />
                </div>

                <!-- Email -->
                <div>
                    <ModernInput
                        v-model="form.email"
                        :icon="EnvelopeIcon"
                        type="email"
                        label="Email Address"
                        placeholder="Enter your email address"
                        :error="getFieldError('email')"
                        @input="clearValidationError('email')"
                        required
                    />
                </div>

                <!-- Role Selection -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">
                        Account Type
                    </label>
                    <div class="space-y-3">
                        <!-- Regular User Option -->
                        <label class="relative cursor-pointer">
                            <input
                                v-model="form.role"
                                type="radio"
                                value="client"
                                class="sr-only"
                                @change="clearValidationError('role')"
                            />
                            <div
                                :class="[
                                    'p-4 border-2 rounded-lg transition-all duration-200',
                                    form.role === 'client'
                                        ? 'border-teal-500 bg-teal-50'
                                        : 'border-gray-200 hover:border-gray-300',
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        :class="[
                                            'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                                            form.role === 'client'
                                                ? 'border-teal-500 bg-teal-500'
                                                : 'border-gray-300',
                                        ]"
                                    >
                                        <div
                                            v-if="form.role === 'client'"
                                            class="w-2 h-2 bg-white rounded-full"
                                        ></div>
                                    </div>
                                    <UserGroupIcon
                                        class="w-5 h-5 text-teal-600"
                                    />
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            Regular User
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            Browse and inquire about properties
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Broker Option -->
                        <label class="relative cursor-pointer">
                            <input
                                v-model="form.role"
                                type="radio"
                                value="broker"
                                class="sr-only"
                                @change="clearValidationError('role')"
                            />
                            <div
                                :class="[
                                    'p-4 border-2 rounded-lg transition-all duration-200',
                                    form.role === 'broker'
                                        ? 'border-teal-500 bg-teal-50'
                                        : 'border-gray-200 hover:border-gray-300',
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        :class="[
                                            'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                                            form.role === 'broker'
                                                ? 'border-teal-500 bg-teal-500'
                                                : 'border-gray-300',
                                        ]"
                                    >
                                        <div
                                            v-if="form.role === 'broker'"
                                            class="w-2 h-2 bg-white rounded-full"
                                        ></div>
                                    </div>
                                    <BriefcaseIcon
                                        class="w-5 h-5 text-teal-600"
                                    />
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            Licensed Real Estate Broker
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            List and sell properties (requires
                                            verification)
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <p
                        v-if="getFieldError('role')"
                        class="text-sm text-red-600"
                    >
                        {{ getFieldError("role") }}
                    </p>
                </div>

                <!-- Broker Credentials Section -->
                <div
                    v-if="form.role === 'broker'"
                    class="space-y-6 p-6 bg-gray-50 rounded-lg border border-gray-200"
                >
                    <!-- Section 1: Account Information -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <UserIcon class="w-5 h-5 text-teal-600" />
                            <h3 class="text-lg font-medium text-gray-900">
                                1. Account Information
                            </h3>
                        </div>

                        <!-- Mobile Number -->
                        <div>
                            <ModernInput
                                v-model="form.phone"
                                type="tel"
                                label="Mobile Number"
                                placeholder="Enter your mobile number"
                                :error="getFieldError('phone')"
                                @input="clearValidationError('phone')"
                                required
                            />
                        </div>
                    </div>

                    <!-- Section 2: Professional Details -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <BriefcaseIcon class="w-5 h-5 text-teal-600" />
                            <h3 class="text-lg font-medium text-gray-900">
                                2. Professional Details
                            </h3>
                        </div>

                        <!-- PRC License Number -->
                        <div>
                            <ModernInput
                                v-model="form.prc_id"
                                type="text"
                                label="PRC License Number"
                                placeholder="Enter your PRC license number"
                                :error="getFieldError('prc_id')"
                                @input="clearValidationError('prc_id')"
                                required
                            />
                        </div>

                        <!-- PRC License Expiration Date -->
                        <div>
                            <ModernInput
                                v-model="form.prc_license_expiration"
                                type="date"
                                label="Expiration Date of PRC License"
                                :error="getFieldError('prc_license_expiration')"
                                @input="
                                    clearValidationError(
                                        'prc_license_expiration'
                                    )
                                "
                                required
                            />
                        </div>

                        <!-- Upload PRC ID -->
                        <div>
                            <FileUpload
                                v-model="form.prc_id_file"
                                label="Upload PRC ID (front and back, optional but recommended)"
                                accept=".pdf,.jpg,.jpeg,.png"
                                :error="getFieldError('prc_id_file')"
                                @validation-error="
                                    (error) =>
                                        handleFileValidationError(
                                            'prc_id_file',
                                            error
                                        )
                                "
                                @update:modelValue="
                                    clearValidationError('prc_id_file')
                                "
                                :max-size="10 * 1024 * 1024"
                                multiple
                            />
                            <p class="text-xs text-gray-500 mt-1">
                                Upload both front and back of your PRC ID for
                                faster verification
                            </p>
                        </div>

                        <!-- Years of Experience -->
                        <div>
                            <ModernInput
                                v-model="form.years_experience"
                                type="number"
                                label="Years of Experience in Real Estate (optional)"
                                placeholder="Enter your years of experience"
                                :error="getFieldError('years_experience')"
                                @input="
                                    clearValidationError('years_experience')
                                "
                                min="0"
                                max="50"
                            />
                        </div>
                    </div>

                    <!-- Section 3: Business Details (Optional) -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <BuildingOfficeIcon class="w-5 h-5 text-teal-600" />
                            <h3 class="text-lg font-medium text-gray-900">
                                3. Business Details (Optional)
                            </h3>
                            <span class="text-sm text-gray-500">
                                Include only if registering as a company/firm
                            </span>
                        </div>

                        <!-- Brokerage Firm Name -->
                        <div>
                            <ModernInput
                                v-model="form.brokerage_firm_name"
                                type="text"
                                label="Brokerage Firm Name"
                                placeholder="Enter your brokerage firm name"
                                :error="getFieldError('brokerage_firm_name')"
                                @input="
                                    clearValidationError('brokerage_firm_name')
                                "
                            />
                        </div>

                        <!-- Office Address -->
                        <div>
                            <ModernInput
                                v-model="form.office_address"
                                type="textarea"
                                label="Office Address"
                                placeholder="Enter your office address"
                                :error="getFieldError('office_address')"
                                @input="clearValidationError('office_address')"
                                rows="3"
                            />
                        </div>

                        <!-- Office Contact Number -->
                        <div>
                            <ModernInput
                                v-model="form.office_contact_number"
                                type="tel"
                                label="Office Contact Number"
                                placeholder="Enter your office contact number"
                                :error="getFieldError('office_contact_number')"
                                @input="
                                    clearValidationError(
                                        'office_contact_number'
                                    )
                                "
                            />
                        </div>
                    </div>

                    <!-- Address Information (existing) -->
                    <div class="space-y-4">
                        <h4 class="text-md font-medium text-gray-900">
                            Personal Address Information
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <ModernInput
                                    v-model="form.city"
                                    type="text"
                                    label="City"
                                    placeholder="Enter your city"
                                    :error="getFieldError('city')"
                                    @input="clearValidationError('city')"
                                    required
                                />
                            </div>
                            <div>
                                <ModernInput
                                    v-model="form.province"
                                    type="text"
                                    label="Province"
                                    placeholder="Enter your province"
                                    :error="getFieldError('province')"
                                    @input="clearValidationError('province')"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <ModernInput
                                v-model="form.address"
                                type="text"
                                label="Complete Address"
                                placeholder="Enter your complete address"
                                :error="getFieldError('address')"
                                @input="clearValidationError('address')"
                                required
                            />
                        </div>

                        <div>
                            <ModernInput
                                v-model="form.postal_code"
                                type="text"
                                label="Postal Code (Optional)"
                                placeholder="Enter your postal code"
                                :error="getFieldError('postal_code')"
                                @input="clearValidationError('postal_code')"
                            />
                        </div>
                    </div>

                    <!-- Section 4: Agreement -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <CheckCircleIcon class="w-5 h-5 text-teal-600" />
                            <h3 class="text-lg font-medium text-gray-900">
                                4. Agreement
                            </h3>
                        </div>

                        <!-- Information Certification -->
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                v-model="form.information_certified"
                                type="checkbox"
                                class="mt-1 w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                @change="
                                    clearValidationError(
                                        'information_certified'
                                    )
                                "
                            />
                            <span class="text-sm text-gray-700">
                                I hereby certify that the information provided
                                is true and correct.
                            </span>
                        </label>
                        <p
                            v-if="getFieldError('information_certified')"
                            class="text-sm text-red-600 ml-7"
                        >
                            {{ getFieldError("information_certified") }}
                        </p>

                        <!-- PRC Verification Consent -->
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                v-model="form.prc_verification_consent"
                                type="checkbox"
                                class="mt-1 w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                @change="
                                    clearValidationError(
                                        'prc_verification_consent'
                                    )
                                "
                            />
                            <span class="text-sm text-gray-700">
                                I consent to the verification of my PRC license
                                through official PRC channels.
                            </span>
                        </label>
                        <p
                            v-if="getFieldError('prc_verification_consent')"
                            class="text-sm text-red-600 ml-7"
                        >
                            {{ getFieldError("prc_verification_consent") }}
                        </p>

                        <!-- Existing Terms and Privacy Policy -->
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                v-model="form.terms_accepted"
                                type="checkbox"
                                class="mt-1 w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                @change="clearValidationError('terms_accepted')"
                            />
                            <span class="text-sm text-gray-700">
                                I agree to the
                                <button
                                    type="button"
                                    @click="showTermsModal = true"
                                    class="text-teal-600 hover:underline cursor-pointer"
                                >
                                    Terms of Service
                                </button>
                            </span>
                        </label>
                        <p
                            v-if="getFieldError('terms_accepted')"
                            class="text-sm text-red-600 ml-7"
                        >
                            {{ getFieldError("terms_accepted") }}
                        </p>

                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                v-model="form.privacy_policy_accepted"
                                type="checkbox"
                                class="mt-1 w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                @change="
                                    clearValidationError(
                                        'privacy_policy_accepted'
                                    )
                                "
                            />
                            <span class="text-sm text-gray-700">
                                I agree to the
                                <button
                                    type="button"
                                    @click="showPrivacyModal = true"
                                    class="text-teal-600 hover:underline cursor-pointer"
                                >
                                    Privacy Policy
                                </button>
                            </span>
                        </label>
                        <p
                            v-if="getFieldError('privacy_policy_accepted')"
                            class="text-sm text-red-600 ml-7"
                        >
                            {{ getFieldError("privacy_policy_accepted") }}
                        </p>
                    </div>

                    <!-- Info box -->
                    <div
                        class="bg-teal-50 border border-teal-200 rounded-lg p-4"
                    >
                        <div class="flex items-start gap-2">
                            <CheckCircleIcon
                                class="w-5 h-5 text-teal-600 mt-0.5 flex-shrink-0"
                            />
                            <div class="text-sm text-teal-800">
                                <p class="font-medium mb-1">
                                    Application Review Process
                                </p>
                                <p>
                                    Your broker application will be reviewed by
                                    our admin team. You'll receive an email
                                    notification once your credentials are
                                    verified. The review process typically takes
                                    1-3 business days.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="relative">
                    <ModernInput
                        v-model="form.password"
                        :icon="LockClosedIcon"
                        :type="showPassword ? 'text' : 'password'"
                        label="Password"
                        placeholder="Create a password"
                        :error="getFieldError('password')"
                        @input="clearValidationError('password')"
                        required
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-4 top-11 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                    >
                        <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                        <EyeSlashIcon v-else class="w-5 h-5" />
                    </button>
                </div>

                <!-- Confirm Password -->
                <div class="relative">
                    <ModernInput
                        v-model="form.password_confirmation"
                        :icon="LockClosedIcon"
                        :type="showPasswordConfirmation ? 'text' : 'password'"
                        label="Confirm Password"
                        placeholder="Confirm your password"
                        :error="getFieldError('password_confirmation')"
                        @input="clearValidationError('password_confirmation')"
                        required
                    />
                    <button
                        type="button"
                        @click="
                            showPasswordConfirmation = !showPasswordConfirmation
                        "
                        class="absolute right-4 top-11 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                    >
                        <EyeIcon
                            v-if="!showPasswordConfirmation"
                            class="w-5 h-5"
                        />
                        <EyeSlashIcon v-else class="w-5 h-5" />
                    </button>
                </div>

                <!-- Submit Button -->
                <div>
                    <ModernButton
                        type="submit"
                        variant="primary"
                        size="lg"
                        :loading="form.processing"
                        :disabled="!canSubmit"
                        class="w-full"
                    >
                        Create Account
                    </ModernButton>
                </div>

                <!-- Validation Summary -->
                <div
                    v-if="Object.keys(clientValidationErrors).length > 0"
                    class="bg-red-50 border border-red-200 rounded-lg p-4"
                >
                    <div class="flex items-start gap-2">
                        <ExclamationTriangleIcon
                            class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"
                        />
                        <div>
                            <h4 class="text-sm font-medium text-red-800 mb-2">
                                Please fix the following errors:
                            </h4>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li
                                    v-for="(
                                        error, field
                                    ) in clientValidationErrors"
                                    :key="field"
                                >
                                    • {{ error }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Login Link -->
            <div class="text-center">
                <p class="text-gray-600">
                    Already have an account?
                    <Link
                        :href="route('login')"
                        class="text-teal-600 hover:text-teal-700 font-semibold hover:underline"
                    >
                        Sign in here
                    </Link>
                </p>
            </div>

            <!-- Back to Home -->
            <div class="text-center">
                <Link
                    :href="route('home')"
                    class="text-sm text-gray-500 hover:text-gray-700 hover:underline inline-flex items-center gap-1"
                >
                    ← Back to Home
                </Link>
            </div>
        </div>
    </div>

    <!-- Modal Components -->
    <TermsOfServiceModal
        :show="showTermsModal"
        @close="showTermsModal = false"
    />
    <PrivacyPolicyModal
        :show="showPrivacyModal"
        @close="showPrivacyModal = false"
    />
</template>
