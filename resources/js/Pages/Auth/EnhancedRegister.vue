<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed, onMounted, nextTick, watch } from "vue";
import ModernInput from "@/Components/ModernInput.vue";
import ModernButton from "@/Components/ModernButton.vue";
import EnhancedFileUpload from "@/Components/EnhancedFileUpload.vue";
import RegistrationProgress from "@/Components/RegistrationProgress.vue";
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
    ArrowRightIcon,
    ArrowLeftIcon,
    DocumentCheckIcon,
    PencilIcon,
    DocumentIcon,
    ShieldCheckIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    inquiryData: {
        type: Object,
        default: null,
    },
});

// Step management
const currentStep = ref(1);
const totalSteps = computed(() => (form.role === "client" ? 2 : 4));

const steps = computed(() => {
    if (form.role === "client") {
        return [
            {
                id: 1,
                title: "Basic Information",
                description: "Personal details",
            },
            { id: 2, title: "Review & Submit", description: "Final review" },
        ];
    } else {
        return [
            {
                id: 1,
                title: "Basic Information",
                description: "Personal details",
            },
            {
                id: 2,
                title: "Professional Details",
                description: "License & experience",
            },
            { id: 3, title: "Documents", description: "Upload credentials" },
            { id: 4, title: "Review & Submit", description: "Final review" },
        ];
    }
});

// Form state
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const clientValidationErrors = ref({});
const showTermsModal = ref(false);
const showPrivacyModal = ref(false);

// Enhanced validation state
const fieldTouched = ref({});
const realTimeValidation = ref({});
const passwordStrength = ref(0);
const passwordStrengthLabel = ref("");
const estimatedTimeRemaining = ref("");

// Form data
const form = useForm({
    name: "",
    email: "",
    role: "client",
    birthdate: "",
    phone: "",
    prc_id: "",
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

// Debug form password changes (commented out after fixing issue)
// watch(
//     () => form.password,
//     (newValue, oldValue) => {
//         console.log("🔍 Form password changed:", {
//             oldValue: oldValue,
//             newValue: newValue,
//             type: typeof newValue,
//             length: newValue ? newValue.length : 0,
//         });
//     }
// );

// Toast notification system
const showToast = (message, type = "success") => {
    const toast = document.createElement("div");
    toast.className = `fixed top-4 right-4 z-50 max-w-sm p-4 rounded-lg shadow-lg transform transition-all duration-300 ${
        type === "success"
            ? "bg-green-500 text-white"
            : type === "error"
            ? "bg-red-500 text-white"
            : "bg-blue-500 text-white"
    }`;
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <div class="flex-1 text-sm">${message}</div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 6000);
};

// Auto-populate form with inquiry data
onMounted(() => {
    // Completely reset form validation state on load
    form.clearErrors();
    clientValidationErrors.value = {};
    fieldTouched.value = {};

    // Reset password strength indicators
    passwordStrength.value = 0;
    passwordStrengthLabel.value = "";

    if (props.inquiryData) {
        form.name = props.inquiryData.name || "";
        form.email = props.inquiryData.email || "";
        form.phone = props.inquiryData.phone || "";

        showToast(
            `We've pre-filled your details from your inquiry about "${props.inquiryData.property_title}". You can modify them if needed.`,
            "success"
        );
    }

    // Calculate estimated time remaining
    updateEstimatedTime();
});

// Watch for role changes and update form flow
watch(
    () => form.role,
    (newRole, oldRole) => {
        if (newRole !== oldRole) {
            // Update estimated time
            updateEstimatedTime();

            // Clear any existing validation errors AND touched state for role-specific fields
            const roleSpecificFields = [
                "prc_id",
                "phone",
                "prc_license_expiration",
                "city",
                "province",
                "address",
                "years_experience",
                "information_certified",
                "prc_verification_consent",
                "prc_id_file",
                "business_permit_file",
                "additional_documents",
                "brokerage_firm_name",
                "office_address",
                "office_contact_number",
                "postal_code",
            ];
            roleSpecificFields.forEach((field) => {
                // Clear client validation errors
                if (clientValidationErrors.value[field]) {
                    const { [field]: removed, ...rest } =
                        clientValidationErrors.value;
                    clientValidationErrors.value = rest;
                }
                // Reset touched state so errors don't show until user interacts
                fieldTouched.value[field] = false;
            });

            // If switching to client, go to step 2 (Review & Submit for clients)
            if (newRole === "client" && currentStep.value > 1) {
                currentStep.value = 2;
                showToast(
                    "Switched to Regular User. You can now review and submit your registration.",
                    "info"
                );
            }
            // If switching to broker and we're on step 2 (client's review step), go to step 2 (broker's professional details)
            else if (
                newRole === "broker" &&
                currentStep.value === 2 &&
                oldRole === "client"
            ) {
                currentStep.value = 2;
                showToast(
                    "Switched to Broker. Please complete the professional information.",
                    "info"
                );
            }
        }
    }
);

// Enhanced validation functions
const markFieldTouched = (fieldName) => {
    fieldTouched.value[fieldName] = true;
};

const validateField = (fieldName, value) => {
    const errors = {};

    switch (fieldName) {
        case "name":
            if (!value || (typeof value === "string" && !value.trim())) {
                errors.name = "Full name is required";
            } else if (
                value &&
                typeof value === "string" &&
                value.trim().length < 2
            ) {
                errors.name = "Name must be at least 2 characters";
            }
            break;

        case "email":
            if (!value || (typeof value === "string" && !value.trim())) {
                errors.email = "Email address is required";
            } else if (
                value &&
                typeof value === "string" &&
                !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
            ) {
                errors.email = "Please enter a valid email address";
            }
            break;

        case "birthdate":
            if (form.role === "broker") {
                if (!value) {
                    errors.birthdate = "Birthdate is required for brokers";
                } else {
                    const inputDate = new Date(value);
                    const today = new Date();
                    const eighteenYearsAgo = new Date(
                        today.getFullYear() - 18,
                        today.getMonth(),
                        today.getDate()
                    );
                    if (isNaN(inputDate.getTime())) {
                        errors.birthdate = "Please enter a valid date";
                    } else if (inputDate > today) {
                        errors.birthdate = "Birthdate must be in the past";
                    } else if (inputDate > eighteenYearsAgo) {
                        errors.birthdate = "You must be at least 18 years old";
                    }
                }
            }
            break;

        case "password":
            // Don't validate if password is empty or too short to be meaningful
            if (!value || value.length < 1) {
                errors.password = "Password is required";
                break;
            }

            // Calculate strength
            const strength = calculatePasswordStrength(value);
            passwordStrength.value = strength.score;
            passwordStrengthLabel.value = strength.label;

            // SIMPLE APPROACH: Only set error if password is actually weak
            // Clear any existing password error first
            delete errors.password;

            // Check each requirement individually and only set ONE error at a time
            if (value.length < 12) {
                errors.password = "Password needs: At least 12 characters";
            } else if (!/[a-z]/.test(value)) {
                errors.password = "Password needs: Lowercase letters";
            } else if (!/[A-Z]/.test(value)) {
                errors.password = "Password needs: Uppercase letters";
            } else if (!/\d/.test(value)) {
                errors.password = "Password needs: Numbers";
            } else if (!/[@$!%*?&_\-+=\[\]{}|\\:";'<>.,\/~`]/.test(value)) {
                errors.password =
                    "Password needs: Special characters (@$!%*?&_ etc.)";
            }
            // If all requirements are met, no error is set (errors.password remains undefined)
            break;

        case "password_confirmation":
            if (!value) {
                errors.password_confirmation =
                    "Password confirmation is required";
            } else if (form.password && value !== form.password) {
                // Only add this error if we're not showing the inline message
                // The inline message will handle the "don't match" case when both fields have content
                if (!form.password || form.password.length === 0) {
                    errors.password_confirmation = "Passwords do not match";
                }
            }
            break;

        case "prc_id":
            if (
                form.role === "broker" &&
                (!value || (typeof value === "string" && !value.trim()))
            ) {
                errors.prc_id = "PRC License Number is required for brokers";
            } else if (
                value &&
                typeof value === "string" &&
                !/^PRC-\d{6}$/.test(value.trim().toUpperCase())
            ) {
                errors.prc_id = "PRC License format should be PRC-123456";
            }
            break;

        case "phone":
            if (
                form.role === "broker" &&
                (!value || (typeof value === "string" && !value.trim()))
            ) {
                errors.phone = "Phone number is required for brokers";
            } else if (
                value &&
                typeof value === "string" &&
                !/^\+?[\d\s\-\(\)]{10,}$/.test(value)
            ) {
                errors.phone = "Please enter a valid phone number";
            }
            break;
    }

    return errors;
};

const calculatePasswordStrength = (password) => {
    let score = 0;
    let feedback = [];

    if (!password) return { score: 0, label: "", feedback: [] };

    // Length check
    if (password.length >= 12) score += 1;
    else feedback.push("At least 12 characters");

    // Lowercase check
    if (/[a-z]/.test(password)) score += 1;
    else feedback.push("Lowercase letters");

    // Uppercase check
    if (/[A-Z]/.test(password)) score += 1;
    else feedback.push("Uppercase letters");

    // Number check
    if (/\d/.test(password)) score += 1;
    else feedback.push("Numbers");

    // Special character check - include underscore and other common special chars
    if (/[@$!%*?&_\-+=\[\]{}|\\:";'<>.,\/~`]/.test(password)) score += 1;
    else feedback.push("Special characters (@$!%*?&_ etc.)");

    // Complexity bonus
    if (password.length >= 16 && score >= 4) score += 1;

    const labels = [
        "Very Weak",
        "Weak",
        "Fair",
        "Good",
        "Strong",
        "Very Strong",
    ];
    const colors = [
        "text-red-500",
        "text-orange-500",
        "text-yellow-500",
        "text-blue-500",
        "text-green-500",
        "text-green-600",
    ];

    const finalScore = Math.min(score, 5);

    // If password meets all basic requirements (score >= 4), clear feedback
    // This prevents showing "needs" messages for strong passwords
    if (score >= 4) {
        feedback = []; // Directly clear the feedback array
    }

    const result = {
        score: finalScore,
        label: labels[finalScore],
        color: colors[finalScore],
        feedback: feedback, // Use the modified feedback array directly
    };

    return result;
};

const updateEstimatedTime = () => {
    if (form.role === "client") {
        const remainingSteps = totalSteps.value - currentStep.value;
        estimatedTimeRemaining.value =
            remainingSteps === 0 ? "Almost done!" : "1-2 minutes";
    } else {
        const remainingSteps = totalSteps.value - currentStep.value;
        estimatedTimeRemaining.value =
            remainingSteps === 0
                ? "Almost done!"
                : `${remainingSteps * 3}-${remainingSteps * 5} minutes`;
    }
};

// Flag to prevent multiple simultaneous validations
let isValidationInProgress = false;

// Real-time field validation
const handleFieldInput = (fieldName, value) => {
    markFieldTouched(fieldName);

    // Clear existing client-side error
    if (clientValidationErrors.value[fieldName]) {
        const { [fieldName]: removed, ...rest } = clientValidationErrors.value;
        clientValidationErrors.value = rest;
    }

    // For password field, be extra aggressive about clearing errors
    if (fieldName === "password") {
        const { password: removed, ...rest } = clientValidationErrors.value;
        clientValidationErrors.value = rest;
    }

    // Clear server-side errors when user starts typing (this prevents stale server errors)
    if (form.errors && form.errors[fieldName]) {
        form.clearErrors(fieldName);
        // Force clear from the errors object as a backup
        delete form.errors[fieldName];
    }

    // Also clear all server errors if any exist (more aggressive approach)
    if (form.errors && Object.keys(form.errors).length > 0) {
        form.clearErrors();
    }

    // Reset password strength indicators if password field is cleared
    if (fieldName === "password" && (!value || value.length === 0)) {
        passwordStrength.value = 0;
        passwordStrengthLabel.value = "";
    }

    // Validate field if it's been touched and not already validating
    if (fieldTouched.value[fieldName] && !isValidationInProgress) {
        isValidationInProgress = true;
        const errors = validateField(fieldName, value);

        // For password field, always clear existing error first, then add new ones if any
        if (fieldName === "password") {
            const { password: removed, ...rest } = clientValidationErrors.value;
            clientValidationErrors.value = rest;

            // Only add errors if there are any
            if (Object.keys(errors).length > 0) {
                clientValidationErrors.value = {
                    ...clientValidationErrors.value,
                    ...errors,
                };
            }
        } else {
            clientValidationErrors.value = {
                ...clientValidationErrors.value,
                ...errors,
            };
        }

        isValidationInProgress = false;
    }

    // Update estimated time when role changes
    if (fieldName === "role") {
        updateEstimatedTime();
    }
};

// Step validation - only validate fields that have been touched
const validateStep = (stepNumber, forceValidation = false) => {
    let errors = {};

    // Don't validate if no fields have been touched yet (prevents premature validation)
    const hasTouchedFields = Object.keys(fieldTouched.value).some(
        (key) => fieldTouched.value[key]
    );
    if (!hasTouchedFields && !forceValidation) {
        return true; // Allow proceeding if no validation has been triggered yet
    }

    switch (stepNumber) {
        case 1:
            // If force validation, mark all basic fields as touched
            if (forceValidation) {
                [
                    "name",
                    "email",
                    "password",
                    "password_confirmation",
                    ...(form.role === "broker" ? ["birthdate"] : []),
                ].forEach((field) => {
                    fieldTouched.value[field] = true;
                });
            }

            // Only validate fields that have been touched
            if (fieldTouched.value.name) {
                errors = { ...errors, ...validateField("name", form.name) };
            }
            if (fieldTouched.value.email) {
                errors = { ...errors, ...validateField("email", form.email) };
            }
            if (form.role === "broker" && fieldTouched.value.birthdate) {
                errors = {
                    ...errors,
                    ...validateField("birthdate", form.birthdate),
                };
            }
            if (fieldTouched.value.password) {
                errors = {
                    ...errors,
                    ...validateField("password", form.password),
                };
            }
            if (fieldTouched.value.password_confirmation) {
                errors = {
                    ...errors,
                    ...validateField(
                        "password_confirmation",
                        form.password_confirmation
                    ),
                };
            }
            break;

        case 2:
            if (form.role === "broker") {
                // If force validation, mark all broker fields as touched
                if (forceValidation) {
                    [
                        "prc_id",
                        "prc_license_expiration",
                        "city",
                        "province",
                        "address",
                        "phone",
                        "information_certified",
                        "prc_verification_consent",
                    ].forEach((field) => {
                        fieldTouched.value[field] = true;
                    });
                }

                // Only validate broker fields that have been touched
                if (fieldTouched.value.prc_id) {
                    errors = {
                        ...errors,
                        ...validateField("prc_id", form.prc_id),
                    };
                }
                if (fieldTouched.value.phone) {
                    errors = {
                        ...errors,
                        ...validateField("phone", form.phone),
                    };
                }
                if (
                    fieldTouched.value.prc_license_expiration &&
                    !form.prc_license_expiration
                ) {
                    errors.prc_license_expiration =
                        "PRC License expiration date is required";
                }
                if (
                    fieldTouched.value.city &&
                    (!form.city ||
                        (typeof form.city === "string" && !form.city.trim()))
                ) {
                    errors.city = "City is required for brokers";
                }
                if (
                    fieldTouched.value.province &&
                    (!form.province ||
                        (typeof form.province === "string" &&
                            !form.province.trim()))
                ) {
                    errors.province = "Province is required for brokers";
                }
                if (
                    fieldTouched.value.address &&
                    (!form.address ||
                        (typeof form.address === "string" &&
                            !form.address.trim()))
                ) {
                    errors.address = "Complete address is required for brokers";
                }
                if (
                    fieldTouched.value.information_certified &&
                    !form.information_certified
                ) {
                    errors.information_certified =
                        "You must certify that your information is correct";
                }
                if (
                    fieldTouched.value.prc_verification_consent &&
                    !form.prc_verification_consent
                ) {
                    errors.prc_verification_consent =
                        "You must consent to PRC verification";
                }
            }
            break;

        case 3:
            // Document validation (optional for now)
            break;

        case 2:
            if (form.role === "client") {
                // For clients, step 2 is Review & Submit - validate terms
                if (!form.terms_accepted) {
                    errors.terms_accepted =
                        "You must accept the Terms of Service";
                }
                if (!form.privacy_policy_accepted) {
                    errors.privacy_policy_accepted =
                        "You must accept the Privacy Policy";
                }
            } else if (form.role === "broker") {
                // For brokers, step 2 is Professional Details - handled above
            }
            break;

        case 4:
            if (form.role === "broker") {
                if (!form.terms_accepted) {
                    errors.terms_accepted =
                        "You must accept the Terms of Service";
                }
                if (!form.privacy_policy_accepted) {
                    errors.privacy_policy_accepted =
                        "You must accept the Privacy Policy";
                }
            }
            break;
    }

    clientValidationErrors.value = {
        ...clientValidationErrors.value,
        ...errors,
    };
    return Object.keys(errors).length === 0;
};

// Navigation functions
const nextStep = () => {
    if (validateStep(currentStep.value, true)) {
        // Force validation when user clicks Next
        if (currentStep.value < totalSteps.value) {
            currentStep.value++;
            updateEstimatedTime();
            nextTick(() => {
                window.scrollTo({ top: 0, behavior: "smooth" });
            });
        }
    } else {
        const errorCount = Object.keys(clientValidationErrors.value).length;
        showToast(
            `Please fix ${errorCount} error${
                errorCount > 1 ? "s" : ""
            } before continuing.`,
            "error"
        );
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
        updateEstimatedTime();
        nextTick(() => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }
};

const goToStep = (step) => {
    if (step >= 1 && step <= totalSteps.value) {
        currentStep.value = step;
        updateEstimatedTime();
        nextTick(() => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }
};

// Clear validation error for a specific field
const clearValidationError = (field) => {
    if (clientValidationErrors.value[field]) {
        const { [field]: removed, ...rest } = clientValidationErrors.value;
        clientValidationErrors.value = rest;
    }
};

// Combined errors (server + client) - ONLY SHOW AFTER INTERACTION
const getFieldError = (field) => {
    // CRITICAL: Only show ANY errors if the field has been touched/interacted with
    if (!fieldTouched.value[field]) {
        return null; // No errors until user interacts with the field
    }

    // SPECIAL CASE: For password field, always return null if password is strong
    if (field === "password") {
        const password = form.password;
        if (
            password &&
            password.length >= 12 &&
            /[a-z]/.test(password) &&
            /[A-Z]/.test(password) &&
            /\d/.test(password)
        ) {
            return null; // No error for strong passwords
        }
    }

    // Only show client validation errors if field has been touched
    const clientError = clientValidationErrors.value[field];

    // Show server errors only if field has been touched
    const serverError = form.errors[field];

    // Special handling for password confirmation to prevent duplicate messages
    if (field === "password_confirmation") {
        // If both password fields have content and we're showing inline validation,
        // don't show the individual field error
        if (
            form.password &&
            form.password_confirmation &&
            form.password !== form.password_confirmation
        ) {
            return null; // Let the inline message handle it
        }
    }

    const finalError = serverError || clientError;

    return finalError;
};

// Form submission
const submit = () => {
    if (validateStep(currentStep.value, true)) {
        // Force validation when user submits
        form.post(route("register"), {
            onError: (errors) => {
                Object.keys(errors).forEach((field) => {
                    if (field.includes("file") && errors[field]) {
                        showToast(
                            `File Upload Error: ${errors[field]}`,
                            "error"
                        );
                    }
                });
            },
            onSuccess: (page) => {
                // Force a full page reload to preserve session and query params
                window.location = route("verification.notice", {
                    registered: 1,
                });
            },
        });
    }
};

// File upload error handling
const handleFileValidationError = (field, error) => {
    clientValidationErrors.value = {
        ...clientValidationErrors.value,
        [field]: error,
    };
    showToast(`Upload failed: ${error}`, "error");
};

// Computed properties
const canProceed = computed(() => {
    // Allow proceeding if form is not processing
    // Only validate if user has started interacting with the form
    const hasTouchedFields = Object.keys(fieldTouched.value).some(
        (key) => fieldTouched.value[key]
    );

    // If no fields have been touched yet, allow proceeding (user-friendly approach)
    if (!hasTouchedFields) {
        return !form.processing;
    }

    // If fields have been touched, then validate
    const isValid = validateStep(currentStep.value);
    const canProceedValue = !form.processing && isValid;

    return canProceedValue;
});

const isLastStep = computed(() => currentStep.value === totalSteps.value);
const isFirstStep = computed(() => currentStep.value === 1);

// Step-specific content
const getStepTitle = () => {
    return (
        steps.value.find((step) => step.id === currentStep.value)?.title || ""
    );
};

const getStepDescription = () => {
    return (
        steps.value.find((step) => step.id === currentStep.value)
            ?.description || ""
    );
};
</script>

<template>
    <Head title="Register - GeoCasa Bohol" />

    <div
        class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-blue-50"
    >
        <!-- Header -->
        <div class="bg-white border-b border-gray-100">
            <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
                <div class="text-center">
                    <Link :href="route('home')" class="inline-block">
                        <GeoCasaLogo
                            size="medium"
                            variant="default"
                            :show-text="true"
                            layout="horizontal"
                        />
                    </Link>
                    <h1 class="mt-4 text-3xl font-bold text-gray-900">
                        {{ getStepTitle() }}
                    </h1>
                    <p class="mt-2 text-gray-600">
                        {{ getStepDescription() }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="bg-white border-b border-gray-100">
            <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
                <RegistrationProgress
                    :current-step="currentStep"
                    :total-steps="totalSteps"
                    :steps="steps"
                />

                <!-- Estimated Time Remaining -->
                <div class="mt-4 text-center">
                    <div
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-50 text-blue-700"
                    >
                        <svg
                            class="w-4 h-4 mr-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        Estimated time remaining: {{ estimatedTimeRemaining }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 py-8 pb-16 sm:px-6 lg:px-8">
            <div class="flex justify-center">
                <div class="w-full max-w-2xl">
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Step 1: Basic Information -->
                        <div v-show="currentStep === 1" class="space-y-6">
                            <div
                                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10"
                            >
                                <h2
                                    class="text-xl font-semibold text-gray-900 mb-8"
                                >
                                    Personal Information
                                </h2>

                                <!-- Name -->
                                <div class="mb-8">
                                    <ModernInput
                                        v-model="form.name"
                                        :icon="UserIcon"
                                        type="text"
                                        label="Full Name"
                                        placeholder="Enter your full name"
                                        :error="getFieldError('name')"
                                        @input="
                                            (value) =>
                                                handleFieldInput('name', value)
                                        "
                                        required
                                    />
                                </div>

                                <!-- Email -->
                                <div class="mb-8">
                                    <ModernInput
                                        v-model="form.email"
                                        :icon="EnvelopeIcon"
                                        type="email"
                                        label="Email Address"
                                        placeholder="Enter your email address"
                                        :error="getFieldError('email')"
                                        @input="
                                            (value) =>
                                                handleFieldInput('email', value)
                                        "
                                        required
                                    />
                                </div>

                                <!-- Birthdate (Broker only) -->
                                <div v-if="form.role === 'broker'" class="mb-8">
                                    <ModernInput
                                        v-model="form.birthdate"
                                        type="date"
                                        label="Birthdate"
                                        :error="getFieldError('birthdate')"
                                        @input="
                                            (value) =>
                                                handleFieldInput(
                                                    'birthdate',
                                                    value
                                                )
                                        "
                                        required
                                    />
                                </div>

                                <!-- Role Selection -->
                                <div class="mb-8">
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-4"
                                    >
                                        Account Type
                                    </label>
                                    <div class="space-y-4">
                                        <!-- Regular User Option -->
                                        <label class="relative cursor-pointer">
                                            <input
                                                v-model="form.role"
                                                type="radio"
                                                value="client"
                                                class="sr-only"
                                                @change="
                                                    (e) =>
                                                        handleFieldInput(
                                                            'role',
                                                            e.target.value
                                                        )
                                                "
                                            />
                                            <div
                                                :class="[
                                                    'p-4 border-2 rounded-lg transition-all duration-200',
                                                    form.role === 'client'
                                                        ? 'border-teal-500 bg-teal-50'
                                                        : 'border-gray-200 hover:border-gray-300',
                                                ]"
                                            >
                                                <div
                                                    class="flex items-center gap-3"
                                                >
                                                    <div
                                                        :class="[
                                                            'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                                                            form.role ===
                                                            'client'
                                                                ? 'border-teal-500 bg-teal-500'
                                                                : 'border-gray-300',
                                                        ]"
                                                    >
                                                        <div
                                                            v-if="
                                                                form.role ===
                                                                'client'
                                                            "
                                                            class="w-2 h-2 bg-white rounded-full"
                                                        ></div>
                                                    </div>
                                                    <UserGroupIcon
                                                        class="w-5 h-5 text-teal-600"
                                                    />
                                                    <div>
                                                        <div
                                                            class="font-medium text-gray-900"
                                                        >
                                                            Regular User
                                                        </div>
                                                        <div
                                                            class="text-sm text-gray-600"
                                                        >
                                                            Browse and inquire
                                                            about properties
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
                                                @change="
                                                    (e) =>
                                                        handleFieldInput(
                                                            'role',
                                                            e.target.value
                                                        )
                                                "
                                            />
                                            <div
                                                :class="[
                                                    'p-4 border-2 rounded-lg transition-all duration-200',
                                                    form.role === 'broker'
                                                        ? 'border-teal-500 bg-teal-50'
                                                        : 'border-gray-200 hover:border-gray-300',
                                                ]"
                                            >
                                                <div
                                                    class="flex items-center gap-3"
                                                >
                                                    <div
                                                        :class="[
                                                            'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                                                            form.role ===
                                                            'broker'
                                                                ? 'border-teal-500 bg-teal-500'
                                                                : 'border-gray-300',
                                                        ]"
                                                    >
                                                        <div
                                                            v-if="
                                                                form.role ===
                                                                'broker'
                                                            "
                                                            class="w-2 h-2 bg-white rounded-full"
                                                        ></div>
                                                    </div>
                                                    <BriefcaseIcon
                                                        class="w-5 h-5 text-teal-600"
                                                    />
                                                    <div>
                                                        <div
                                                            class="font-medium text-gray-900"
                                                        >
                                                            Licensed Real Estate
                                                            Broker
                                                        </div>
                                                        <div
                                                            class="text-sm text-gray-600"
                                                        >
                                                            List and sell
                                                            properties (requires
                                                            verification)
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Password -->
                                <div class="mb-8">
                                    <div class="relative">
                                        <ModernInput
                                            v-model="form.password"
                                            :icon="LockClosedIcon"
                                            :type="
                                                showPassword
                                                    ? 'text'
                                                    : 'password'
                                            "
                                            label="Password"
                                            placeholder="Create a strong password"
                                            :error="getFieldError('password')"
                                            @input="
                                                (value) =>
                                                    handleFieldInput(
                                                        'password',
                                                        value
                                                    )
                                            "
                                            required
                                        />
                                        <button
                                            type="button"
                                            @click="
                                                showPassword = !showPassword
                                            "
                                            class="absolute right-4 top-11 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                                        >
                                            <EyeIcon
                                                v-if="!showPassword"
                                                class="w-5 h-5"
                                            />
                                            <EyeSlashIcon
                                                v-else
                                                class="w-5 h-5"
                                            />
                                        </button>
                                    </div>

                                    <!-- Password Strength Indicator -->
                                    <div
                                        v-if="
                                            form.password &&
                                            fieldTouched.password
                                        "
                                        class="mt-3"
                                    >
                                        <div
                                            class="flex items-center justify-between mb-2"
                                        >
                                            <span
                                                class="text-sm font-medium text-gray-700"
                                                >Password Strength</span
                                            >
                                            <span
                                                :class="
                                                    calculatePasswordStrength(
                                                        form.password
                                                    ).color
                                                "
                                                class="text-sm font-medium"
                                            >
                                                {{
                                                    calculatePasswordStrength(
                                                        form.password
                                                    ).label
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            class="w-full bg-gray-200 rounded-full h-2"
                                        >
                                            <div
                                                :class="[
                                                    'h-2 rounded-full transition-all duration-300',
                                                    calculatePasswordStrength(
                                                        form.password
                                                    ).score === 0
                                                        ? 'bg-red-500 w-0'
                                                        : calculatePasswordStrength(
                                                              form.password
                                                          ).score === 1
                                                        ? 'bg-red-500 w-1/5'
                                                        : calculatePasswordStrength(
                                                              form.password
                                                          ).score === 2
                                                        ? 'bg-orange-500 w-2/5'
                                                        : calculatePasswordStrength(
                                                              form.password
                                                          ).score === 3
                                                        ? 'bg-yellow-500 w-3/5'
                                                        : calculatePasswordStrength(
                                                              form.password
                                                          ).score === 4
                                                        ? 'bg-blue-500 w-4/5'
                                                        : 'bg-green-500 w-full',
                                                ]"
                                            ></div>
                                        </div>

                                        <!-- Password Requirements -->
                                        <div
                                            v-if="
                                                calculatePasswordStrength(
                                                    form.password
                                                ).feedback.length > 0
                                            "
                                            class="mt-2"
                                        >
                                            <p
                                                class="text-xs text-gray-600 mb-1"
                                            >
                                                Still needed:
                                            </p>
                                            <ul
                                                class="text-xs text-gray-500 space-y-1"
                                            >
                                                <li
                                                    v-for="requirement in calculatePasswordStrength(
                                                        form.password
                                                    ).feedback"
                                                    :key="requirement"
                                                    class="flex items-center"
                                                >
                                                    <svg
                                                        class="w-3 h-3 text-red-400 mr-1 flex-shrink-0"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                    {{ requirement }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Default help text when password is empty -->
                                    <div v-else class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Must be at least 12 characters with
                                            uppercase, lowercase, number, and
                                            special character (@$!%*?&_ etc.)
                                        </p>
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="mb-8">
                                    <div class="relative">
                                        <ModernInput
                                            v-model="form.password_confirmation"
                                            :icon="LockClosedIcon"
                                            :type="
                                                showPasswordConfirmation
                                                    ? 'text'
                                                    : 'password'
                                            "
                                            label="Confirm Password"
                                            placeholder="Confirm your password"
                                            :error="
                                                getFieldError(
                                                    'password_confirmation'
                                                )
                                            "
                                            @input="
                                                (value) =>
                                                    handleFieldInput(
                                                        'password_confirmation',
                                                        value
                                                    )
                                            "
                                            required
                                        />
                                        <button
                                            type="button"
                                            @click="
                                                showPasswordConfirmation =
                                                    !showPasswordConfirmation
                                            "
                                            class="absolute right-4 top-11 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                                        >
                                            <EyeIcon
                                                v-if="!showPasswordConfirmation"
                                                class="w-5 h-5"
                                            />
                                            <EyeSlashIcon
                                                v-else
                                                class="w-5 h-5"
                                            />
                                        </button>
                                    </div>

                                    <!-- Password Match Indicator -->
                                    <div
                                        v-if="
                                            form.password_confirmation &&
                                            fieldTouched.password_confirmation
                                        "
                                        class="mt-2"
                                    >
                                        <div
                                            v-if="
                                                form.password &&
                                                form.password_confirmation &&
                                                form.password ===
                                                    form.password_confirmation &&
                                                !clientValidationErrors.password_confirmation
                                            "
                                            class="flex items-center text-green-600"
                                        >
                                            <svg
                                                class="w-4 h-4 mr-1"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span class="text-sm"
                                                >Passwords match</span
                                            >
                                        </div>
                                        <div
                                            v-else-if="
                                                form.password &&
                                                form.password_confirmation &&
                                                form.password !==
                                                    form.password_confirmation
                                            "
                                            class="flex items-center text-red-600"
                                        >
                                            <svg
                                                class="w-4 h-4 mr-1"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span class="text-sm"
                                                >Passwords don't match</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Professional Details (Broker only) -->
                        <div
                            v-show="currentStep === 2 && form.role === 'broker'"
                            class="space-y-6"
                        >
                            <div
                                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8"
                            >
                                <h2
                                    class="text-xl font-semibold text-gray-900 mb-6"
                                >
                                    Professional Information
                                </h2>

                                <!-- PRC License Number -->
                                <div class="mb-6">
                                    <ModernInput
                                        v-model="form.prc_id"
                                        type="text"
                                        label="PRC License Number"
                                        placeholder="Enter your PRC license number (e.g., PRC-123456)"
                                        :error="getFieldError('prc_id')"
                                        @input="
                                            (value) =>
                                                handleFieldInput(
                                                    'prc_id',
                                                    value
                                                )
                                        "
                                        required
                                    />
                                    <p class="mt-2 text-sm text-gray-500">
                                        Format: PRC- followed by 6 digits
                                    </p>
                                </div>

                                <!-- PRC License Expiration -->
                                <div class="mb-6">
                                    <ModernInput
                                        v-model="form.prc_license_expiration"
                                        type="date"
                                        label="PRC License Expiration Date"
                                        :error="
                                            getFieldError(
                                                'prc_license_expiration'
                                            )
                                        "
                                        @input="
                                            (value) =>
                                                handleFieldInput(
                                                    'prc_license_expiration',
                                                    value
                                                )
                                        "
                                        required
                                    />
                                </div>

                                <!-- Phone Number -->
                                <div class="mb-6">
                                    <ModernInput
                                        v-model="form.phone"
                                        type="tel"
                                        label="Mobile Number"
                                        placeholder="Enter your mobile number"
                                        :error="getFieldError('phone')"
                                        @input="
                                            (value) =>
                                                handleFieldInput('phone', value)
                                        "
                                        required
                                    />
                                </div>

                                <!-- Location Information -->
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6"
                                >
                                    <div>
                                        <ModernInput
                                            v-model="form.city"
                                            type="text"
                                            label="City"
                                            placeholder="Enter your city"
                                            :error="getFieldError('city')"
                                            @input="
                                                (value) =>
                                                    handleFieldInput(
                                                        'city',
                                                        value
                                                    )
                                            "
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
                                            @input="
                                                (value) =>
                                                    handleFieldInput(
                                                        'province',
                                                        value
                                                    )
                                            "
                                            required
                                        />
                                    </div>
                                </div>

                                <!-- Complete Address -->
                                <div class="mb-6">
                                    <ModernInput
                                        v-model="form.address"
                                        type="text"
                                        label="Complete Address"
                                        placeholder="Enter your complete address"
                                        :error="getFieldError('address')"
                                        @input="
                                            (value) =>
                                                handleFieldInput(
                                                    'address',
                                                    value
                                                )
                                        "
                                        required
                                    />
                                </div>

                                <!-- Years of Experience -->
                                <div class="mb-6">
                                    <ModernInput
                                        v-model="form.years_experience"
                                        type="number"
                                        label="Years of Experience (Optional)"
                                        placeholder="Enter your years of experience"
                                        :error="
                                            getFieldError('years_experience')
                                        "
                                        @input="
                                            (value) =>
                                                handleFieldInput(
                                                    'years_experience',
                                                    value
                                                )
                                        "
                                        min="0"
                                        max="50"
                                    />
                                </div>

                                <!-- Certifications -->
                                <div class="space-y-4">
                                    <label
                                        class="flex items-start gap-3 cursor-pointer"
                                    >
                                        <input
                                            v-model="form.information_certified"
                                            type="checkbox"
                                            class="mt-1 w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                            @change="
                                                (e) =>
                                                    handleFieldInput(
                                                        'information_certified',
                                                        e.target.checked
                                                    )
                                            "
                                        />
                                        <span class="text-sm text-gray-700">
                                            I hereby certify that the
                                            information provided is true and
                                            correct.
                                        </span>
                                    </label>
                                    <p
                                        v-if="
                                            getFieldError(
                                                'information_certified'
                                            )
                                        "
                                        class="text-sm text-red-600 ml-7"
                                    >
                                        {{
                                            getFieldError(
                                                "information_certified"
                                            )
                                        }}
                                    </p>

                                    <label
                                        class="flex items-start gap-3 cursor-pointer"
                                    >
                                        <input
                                            v-model="
                                                form.prc_verification_consent
                                            "
                                            type="checkbox"
                                            class="mt-1 w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                            @change="
                                                (e) =>
                                                    handleFieldInput(
                                                        'prc_verification_consent',
                                                        e.target.checked
                                                    )
                                            "
                                        />
                                        <span class="text-sm text-gray-700">
                                            I consent to the verification of my
                                            PRC license through official PRC
                                            channels.
                                        </span>
                                    </label>
                                    <p
                                        v-if="
                                            getFieldError(
                                                'prc_verification_consent'
                                            )
                                        "
                                        class="text-sm text-red-600 ml-7"
                                    >
                                        {{
                                            getFieldError(
                                                "prc_verification_consent"
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Documents (Broker only) -->
                        <div
                            v-show="currentStep === 3 && form.role === 'broker'"
                            class="space-y-6"
                        >
                            <div
                                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8"
                            >
                                <h2
                                    class="text-xl font-semibold text-gray-900 mb-6"
                                >
                                    Document Upload
                                </h2>

                                <!-- PRC ID File -->
                                <div class="mb-6">
                                    <EnhancedFileUpload
                                        v-model="form.prc_id_file"
                                        label="PRC ID Document (Optional but Recommended)"
                                        accept=".pdf,.jpg,.jpeg,.png"
                                        :max-files="2"
                                        :max-size="10 * 1024 * 1024"
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
                                        helper="Upload both front and back of your PRC ID for faster verification"
                                    />
                                </div>

                                <!-- Additional Documents -->
                                <div class="mb-6">
                                    <EnhancedFileUpload
                                        v-model="form.additional_documents"
                                        label="Additional Documents (Optional)"
                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                        :max-files="5"
                                        :max-size="5 * 1024 * 1024"
                                        :error="
                                            getFieldError(
                                                'additional_documents'
                                            )
                                        "
                                        @validation-error="
                                            (error) =>
                                                handleFileValidationError(
                                                    'additional_documents',
                                                    error
                                                )
                                        "
                                        @update:modelValue="
                                            clearValidationError(
                                                'additional_documents'
                                            )
                                        "
                                        helper="Any additional credentials, certifications, or supporting documents"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Review & Submit (Brokers) / Step 2: Review & Submit (Clients) -->
                        <div
                            v-show="
                                (form.role === 'broker' && currentStep === 4) ||
                                (form.role === 'client' && currentStep === 2)
                            "
                            class="space-y-8"
                        >
                            <!-- Enhanced Review Section with Better UX -->
                            <div
                                class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"
                            >
                                <!-- Header with Completion Status -->
                                <div
                                    class="bg-gradient-to-r from-teal-50 to-blue-50 px-8 py-6 border-b border-gray-100"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            <h2
                                                class="text-2xl font-semibold text-gray-900 mb-2"
                                            >
                                                Review Your Information
                                            </h2>
                                            <p class="text-gray-600">
                                                Please review all details before
                                                submitting your application
                                            </p>
                                        </div>
                                        <div
                                            class="flex items-center space-x-2 bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium"
                                        >
                                            <CheckCircleIcon class="w-4 h-4" />
                                            <span>Ready to Submit</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Content with Improved Layout -->
                                <div class="p-8">
                                    <!-- Summary for Clients -->
                                    <div
                                        v-if="form.role === 'client'"
                                        class="space-y-4"
                                    >
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h3
                                                class="font-medium text-gray-900 mb-3"
                                            >
                                                Personal Information
                                            </h3>
                                            <div class="space-y-2 text-sm">
                                                <p>
                                                    <span class="font-medium"
                                                        >Name:</span
                                                    >
                                                    {{ form.name }}
                                                </p>
                                                <p>
                                                    <span class="font-medium"
                                                        >Email:</span
                                                    >
                                                    {{ form.email }}
                                                </p>
                                                <p>
                                                    <span class="font-medium"
                                                        >Account Type:</span
                                                    >
                                                    Regular User
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Summary for Brokers -->
                                    <div v-else class="space-y-4">
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h3
                                                class="font-medium text-gray-900 mb-3"
                                            >
                                                Personal Information
                                            </h3>
                                            <div class="space-y-2 text-sm">
                                                <p>
                                                    <span class="font-medium"
                                                        >Name:</span
                                                    >
                                                    {{ form.name }}
                                                </p>
                                                <p>
                                                    <span class="font-medium"
                                                        >Email:</span
                                                    >
                                                    {{ form.email }}
                                                </p>
                                                <p>
                                                    <span class="font-medium"
                                                        >Birthdate:</span
                                                    >
                                                    {{ form.birthdate }}
                                                </p>
                                                <p>
                                                    <span class="font-medium"
                                                        >Phone:</span
                                                    >
                                                    {{ form.phone }}
                                                </p>
                                                <p>
                                                    <span class="font-medium"
                                                        >Account Type:</span
                                                    >
                                                    Licensed Real Estate Broker
                                                </p>
                                            </div>
                                        </div>

                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h3
                                                class="font-medium text-gray-900 mb-3"
                                            >
                                                Professional Information
                                            </h3>
                                            <div class="space-y-2 text-sm">
                                                <p>
                                                    <span class="font-medium"
                                                        >PRC License:</span
                                                    >
                                                    {{ form.prc_id }}
                                                </p>
                                                <p>
                                                    <span class="font-medium"
                                                        >Expiration:</span
                                                    >
                                                    {{
                                                        form.prc_license_expiration
                                                    }}
                                                </p>
                                                <p>
                                                    <span class="font-medium"
                                                        >Location:</span
                                                    >
                                                    {{ form.city }},
                                                    {{ form.province }}
                                                </p>
                                                <p>
                                                    <span class="font-medium"
                                                        >Address:</span
                                                    >
                                                    {{ form.address }}
                                                </p>
                                                <p v-if="form.years_experience">
                                                    <span class="font-medium"
                                                        >Experience:</span
                                                    >
                                                    {{ form.years_experience }}
                                                    years
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Terms and Agreements -->
                                        <div class="space-y-4">
                                            <label
                                                class="flex items-start gap-3 cursor-pointer"
                                            >
                                                <input
                                                    v-model="
                                                        form.terms_accepted
                                                    "
                                                    type="checkbox"
                                                    class="mt-1 w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                                    @change="
                                                        clearValidationError(
                                                            'terms_accepted'
                                                        )
                                                    "
                                                />
                                                <span
                                                    class="text-sm text-gray-700"
                                                >
                                                    I agree to the
                                                    <button
                                                        type="button"
                                                        @click="
                                                            showTermsModal = true
                                                        "
                                                        class="text-teal-600 hover:underline cursor-pointer"
                                                    >
                                                        Terms of Service
                                                    </button>
                                                </span>
                                            </label>
                                            <p
                                                v-if="
                                                    getFieldError(
                                                        'terms_accepted'
                                                    )
                                                "
                                                class="text-sm text-red-600 ml-7"
                                            >
                                                {{
                                                    getFieldError(
                                                        "terms_accepted"
                                                    )
                                                }}
                                            </p>

                                            <label
                                                class="flex items-start gap-3 cursor-pointer"
                                            >
                                                <input
                                                    v-model="
                                                        form.privacy_policy_accepted
                                                    "
                                                    type="checkbox"
                                                    class="mt-1 w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                                    @change="
                                                        clearValidationError(
                                                            'privacy_policy_accepted'
                                                        )
                                                    "
                                                />
                                                <span
                                                    class="text-sm text-gray-700"
                                                >
                                                    I agree to the
                                                    <button
                                                        type="button"
                                                        @click="
                                                            showPrivacyModal = true
                                                        "
                                                        class="text-teal-600 hover:underline cursor-pointer"
                                                    >
                                                        Privacy Policy
                                                    </button>
                                                </span>
                                            </label>
                                            <p
                                                v-if="
                                                    getFieldError(
                                                        'privacy_policy_accepted'
                                                    )
                                                "
                                                class="text-sm text-red-600 ml-7"
                                            >
                                                {{
                                                    getFieldError(
                                                        "privacy_policy_accepted"
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Navigation Section -->
                        <div class="space-y-6 pt-8">
                            <!-- Process Information Card (Brokers Only) -->
                            <div
                                v-if="form.role === 'broker' && isLastStep"
                                class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6"
                            >
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center"
                                        >
                                            <svg
                                                class="w-5 h-5 text-blue-600"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                                ></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h3
                                            class="text-lg font-semibold text-blue-900 mb-2"
                                        >
                                            What Happens Next?
                                        </h3>
                                        <div
                                            class="space-y-2 text-sm text-blue-700"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <div
                                                    class="w-2 h-2 bg-blue-400 rounded-full"
                                                ></div>
                                                <span
                                                    >Email verification link
                                                    will be sent to
                                                    {{ form.email }}</span
                                                >
                                            </div>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <div
                                                    class="w-2 h-2 bg-blue-400 rounded-full"
                                                ></div>
                                                <span
                                                    >Admin team reviews your
                                                    credentials (1-3 business
                                                    days)</span
                                                >
                                            </div>
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <div
                                                    class="w-2 h-2 bg-blue-400 rounded-full"
                                                ></div>
                                                <span
                                                    >You'll receive notification
                                                    about approval status</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation Buttons with Enhanced UX -->
                            <div
                                class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-200"
                            >
                                <!-- Previous Button -->
                                <div class="flex-shrink-0">
                                    <ModernButton
                                        v-if="!isFirstStep"
                                        type="button"
                                        variant="secondary"
                                        @click="prevStep"
                                        class="flex items-center gap-2 px-6 py-3"
                                    >
                                        <ArrowLeftIcon class="w-4 h-4" />
                                        Previous
                                    </ModernButton>
                                    <div v-else class="w-24"></div>
                                </div>

                                <!-- Submit/Next Button -->
                                <div class="flex-shrink-0">
                                    <ModernButton
                                        v-if="!isLastStep"
                                        type="button"
                                        variant="primary"
                                        @click="nextStep"
                                        :disabled="!canProceed"
                                        class="flex items-center gap-2 px-8 py-3 text-lg font-semibold"
                                    >
                                        Next Step
                                        <ArrowRightIcon class="w-5 h-5" />
                                    </ModernButton>

                                    <ModernButton
                                        v-else
                                        type="submit"
                                        variant="primary"
                                        :loading="form.processing"
                                        :disabled="!canProceed"
                                        class="flex items-center gap-3 px-8 py-4 text-lg font-semibold bg-gradient-to-r from-teal-600 to-blue-600 hover:from-teal-700 hover:to-blue-700 shadow-lg hover:shadow-xl transition-all duration-200"
                                    >
                                        <DocumentCheckIcon class="w-5 h-5" />
                                        <span v-if="form.role === 'client'"
                                            >Complete Registration</span
                                        >
                                        <span v-else
                                            >Submit Broker Application</span
                                        >
                                    </ModernButton>
                                </div>
                            </div>

                            <!-- Progress Indicator -->
                            <div class="flex justify-center">
                                <div
                                    class="flex items-center space-x-2 text-sm text-gray-500"
                                >
                                    <span
                                        >Step {{ currentStep }} of
                                        {{ totalSteps }}</span
                                    >
                                    <div
                                        class="w-2 h-2 bg-gray-300 rounded-full"
                                    ></div>
                                    <span
                                        >{{
                                            Math.round(
                                                (currentStep / totalSteps) * 100
                                            )
                                        }}% Complete</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Validation Summary - Only show if fields have been touched -->
                    </form>
                </div>
            </div>

            <!-- Login Link -->
            <div class="text-center mt-8">
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
            <div class="text-center mt-4">
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
