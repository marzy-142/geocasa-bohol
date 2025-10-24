<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import ModernInput from "@/Components/ModernInput.vue";
import ModernButton from "@/Components/ModernButton.vue";
import GeoCasaLogo from "@/Components/GeoCasaLogo.vue";
import TermsOfServiceModal from "@/Components/TermsOfServiceModal.vue";
import PrivacyPolicyModal from "@/Components/PrivacyPolicyModal.vue";
import {
    UserIcon,
    EnvelopeIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
    UserGroupIcon,
    ArrowRightIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    inquiryData: {
        type: Object,
        default: null,
    },
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const showTermsModal = ref(false);
const showPrivacyModal = ref(false);
const passwordStrength = ref(0);
const passwordStrengthLabel = ref("");
const fieldTouched = ref({});
const clientValidationErrors = ref({});

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "client", // Fixed role
    terms_accepted: false,
    privacy_policy_accepted: false,
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
        showToast(
            `We've pre-filled your details from your inquiry about "${props.inquiryData.property_title}". You can modify them if needed.`,
            "success"
        );
    }
});

// Simple toast notification system
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

// Password strength calculation
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

    // Only show "Very Strong" if all requirements are met AND no feedback items
    const finalScore =
        feedback.length > 0 ? Math.min(score, 4) : Math.min(score, 5);

    const result = {
        score: finalScore,
        label: labels[finalScore],
        color: colors[finalScore],
        feedback,
    };

    return result;
};

// Real-time field validation
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

        case "password":
            // Don't validate if password is empty or too short to be meaningful
            if (!value || value.length < 1) {
                errors.password = "Password is required";
                break;
            }

            const strength = calculatePasswordStrength(value);
            passwordStrength.value = strength.score;
            passwordStrengthLabel.value = strength.label;

            // Debug logging (commented out after fixing issue)
            // console.log("Password validation debug:", {
            //     value: value,
            //     length: value.length,
            //     strength: strength,
            //     score: strength.score,
            //     feedback: strength.feedback,
            // });

            // Simplified validation - only show error if feedback exists
            if (strength.feedback.length > 0) {
                errors.password = `Password needs: ${strength.feedback.join(
                    ", "
                )}`;
            }
            break;

        case "password_confirmation":
            if (!value) {
                errors.password_confirmation =
                    "Password confirmation is required";
            } else if (form.password && value !== form.password) {
                errors.password_confirmation = "Passwords do not match";
            }
            break;
    }

    return errors;
};

const markFieldTouched = (fieldName) => {
    fieldTouched.value[fieldName] = true;
};

const handleFieldInput = (fieldName, value) => {
    markFieldTouched(fieldName);

    // Clear existing client-side error
    if (clientValidationErrors.value[fieldName]) {
        const { [fieldName]: removed, ...rest } = clientValidationErrors.value;
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

    // Only validate if field has been touched and has meaningful content
    if (fieldTouched.value[fieldName]) {
        const errors = validateField(fieldName, value);
        clientValidationErrors.value = {
            ...clientValidationErrors.value,
            ...errors,
        };
    }
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
    // Only show client validation errors if field has been touched
    const clientError = fieldTouched.value[field]
        ? clientValidationErrors.value[field]
        : null;

    // Don't show server errors if field hasn't been touched yet
    const serverError = fieldTouched.value[field] ? form.errors[field] : null;

    const finalError = serverError || clientError;

    // Enhanced debug logging for password field (commented out after fixing issue)
    // if (field === "password") {
    //     console.log(`🔍 Password field error check:`, {
    //         field,
    //         fieldTouched: fieldTouched.value[field],
    //         clientError,
    //         serverError,
    //         finalError,
    //         formPassword: form.password,
    //         clientValidationErrors: clientValidationErrors.value,
    //         formErrors: form.errors,
    //     });
    // }

    // Debug logging (commented out after fixing issue)
    // if (finalError) {
    //     console.log(`Field ${field} error:`, {
    //         fieldTouched: fieldTouched.value[field],
    //         clientError,
    //         serverError,
    //         finalError,
    //     });
    // }

    return finalError;
};

// Form validation
const validateForm = () => {
    let errors = {};

    // Mark all fields as touched for form submission validation
    ["name", "email", "password", "password_confirmation"].forEach((field) => {
        fieldTouched.value[field] = true;
    });

    errors = { ...errors, ...validateField("name", form.name) };
    errors = { ...errors, ...validateField("email", form.email) };
    errors = { ...errors, ...validateField("password", form.password) };
    errors = {
        ...errors,
        ...validateField("password_confirmation", form.password_confirmation),
    };

    // Terms validation
    if (!form.terms_accepted) {
        errors.terms_accepted = "You must accept the Terms of Service";
    }
    if (!form.privacy_policy_accepted) {
        errors.privacy_policy_accepted = "You must accept the Privacy Policy";
    }

    clientValidationErrors.value = errors;
    return Object.keys(errors).length === 0;
};

// Form submission
const submit = () => {
    if (validateForm()) {
        form.post(route("register"), {
            onError: (errors) => {
                Object.keys(errors).forEach((field) => {
                    if (errors[field]) {
                        showToast(`Error: ${errors[field]}`, "error");
                    }
                });
            },
            onSuccess: () => {
                showToast(
                    "Registration submitted successfully! Please check your email.",
                    "success"
                );
            },
        });
    } else {
        const errorCount = Object.keys(clientValidationErrors.value).length;
        showToast(
            `Please fix ${errorCount} error${
                errorCount > 1 ? "s" : ""
            } before submitting.`,
            "error"
        );
    }
};

// Computed properties
const canSubmit = computed(() => {
    return (
        !form.processing &&
        form.name &&
        form.email &&
        form.password &&
        form.password_confirmation &&
        form.terms_accepted &&
        form.privacy_policy_accepted &&
        Object.keys(clientValidationErrors.value).length === 0
    );
});
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
                        Create Your Account
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Join GeoCasa Bohol to browse and inquire about
                        properties
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto px-4 py-8 pb-16 sm:px-6 lg:px-8">
            <div class="flex justify-center">
                <div class="w-full max-w-2xl">
                    <div
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10"
                    >
                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- Name -->
                            <div>
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
                            <div>
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

                            <!-- Account Type Info -->
                            <div
                                class="bg-teal-50 border border-teal-200 rounded-lg p-4"
                            >
                                <div class="flex items-center gap-3">
                                    <UserGroupIcon
                                        class="w-5 h-5 text-teal-600"
                                    />
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            Regular User Account
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            Browse and inquire about properties
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Password -->
                            <div>
                                <div class="relative">
                                    <ModernInput
                                        v-model="form.password"
                                        :icon="LockClosedIcon"
                                        :type="
                                            showPassword ? 'text' : 'password'
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
                                        @click="showPassword = !showPassword"
                                        class="absolute right-4 top-11 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                                    >
                                        <EyeIcon
                                            v-if="!showPassword"
                                            class="w-5 h-5"
                                        />
                                        <EyeSlashIcon v-else class="w-5 h-5" />
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
                                        >
                                            Password Strength
                                        </span>
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
                                        <p class="text-xs text-gray-600 mb-1">
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
                            <div>
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
                                        <EyeSlashIcon v-else class="w-5 h-5" />
                                    </button>
                                </div>

                                <!-- Password Match Indicator - Only show success when passwords actually match -->
                                <div
                                    v-if="
                                        form.password_confirmation &&
                                        fieldTouched.password_confirmation &&
                                        form.password &&
                                        form.password ===
                                            form.password_confirmation &&
                                        !getFieldError(
                                            'password_confirmation'
                                        ) &&
                                        !getFieldError('password')
                                    "
                                    class="mt-2"
                                >
                                    <div
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
                                </div>
                            </div>

                            <!-- Terms and Privacy -->
                            <div class="space-y-4">
                                <label
                                    class="flex items-start gap-3 cursor-pointer"
                                >
                                    <input
                                        v-model="form.terms_accepted"
                                        type="checkbox"
                                        class="mt-1 w-4 h-4 text-teal-600 border-gray-300 rounded focus:ring-teal-500"
                                        @change="
                                            clearValidationError(
                                                'terms_accepted'
                                            )
                                        "
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

                                <label
                                    class="flex items-start gap-3 cursor-pointer"
                                >
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
                                    v-if="
                                        getFieldError('privacy_policy_accepted')
                                    "
                                    class="text-sm text-red-600 ml-7"
                                >
                                    {{
                                        getFieldError("privacy_policy_accepted")
                                    }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-4">
                                <ModernButton
                                    type="submit"
                                    variant="primary"
                                    :loading="form.processing"
                                    :disabled="!canSubmit"
                                    class="w-full flex items-center justify-center gap-2"
                                >
                                    <UserGroupIcon class="w-5 h-5" />
                                    Create Account
                                    <ArrowRightIcon class="w-4 h-4" />
                                </ModernButton>
                            </div>

                            <!-- Validation Summary -->
                            <div
                                v-if="
                                    Object.keys(clientValidationErrors).length >
                                    0
                                "
                                class="bg-red-50 border border-red-200 rounded-lg p-4"
                            >
                                <h4
                                    class="text-sm font-medium text-red-800 mb-2"
                                >
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
                        </form>
                    </div>
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
