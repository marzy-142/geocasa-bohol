<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ModernInput from "@/Components/ModernInput.vue";
import ModernButton from "@/Components/ModernButton.vue";
import FileUpload from "@/Components/FileUpload.vue";
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
} from "@heroicons/vue/24/outline";

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const clientValidationErrors = ref({});

const form = useForm({
    name: "",
    email: "",
    role: "client", // Changed from "public" to "client"
    prc_id: "",
    prc_id_file: null,
    business_permit: "",
    business_permit_file: null,
    additional_documents: [],
    password: "",
    password_confirmation: "",
});

// Client-side validation rules
const validateForm = () => {
    const errors = {};
    
    // Basic field validation
    if (!form.name.trim()) {
        errors.name = 'Full name is required';
    }
    
    if (!form.email.trim()) {
        errors.email = 'Email address is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = 'Please enter a valid email address';
    }
    
    if (!form.password) {
        errors.password = 'Password is required';
    } else if (form.password.length < 8) {
        errors.password = 'Password must be at least 8 characters long';
    }
    
    if (form.password !== form.password_confirmation) {
        errors.password_confirmation = 'Password confirmation does not match';
    }
    
    // Broker-specific validation
    if (form.role === 'broker') {
        if (!form.prc_id.trim()) {
            errors.prc_id = 'PRC License Number is required for brokers';
        }
        
        if (!form.prc_id_file) {
            errors.prc_id_file = 'PRC License Document is required for brokers';
        }
    }
    
    clientValidationErrors.value = errors;
    return Object.keys(errors).length === 0;
};

// Handle file validation errors
const handleFileValidationError = (field, error) => {
    clientValidationErrors.value = {
        ...clientValidationErrors.value,
        [field]: error
    };
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
    return !form.processing && Object.keys(clientValidationErrors.value).length === 0;
});

const submit = () => {
    // Clear previous client validation errors
    clientValidationErrors.value = {};
    
    // Validate form before submission
    if (!validateForm()) {
        return;
    }
    
    form.post(route("register"), {
        onFinish: () => {
            form.reset("password", "password_confirmation");
        },
        onError: () => {
            // Server validation errors will be handled by Inertia
        }
    });
};
</script>

<template>
    <Head title="Register" />

    <div class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-blue-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <Link :href="route('home')" class="inline-block">
                    <div class="flex items-center justify-center gap-2 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-r from-teal-600 to-blue-600 rounded-xl flex items-center justify-center">
                            <span class="text-white font-bold text-lg">G</span>
                        </div>
                        <span class="text-2xl font-bold bg-gradient-to-r from-teal-600 to-blue-600 bg-clip-text text-transparent">
                            GeoCasa Bohol
                        </span>
                    </div>
                </Link>
                <h2 class="text-3xl font-bold text-gray-900">Create Your Account</h2>
                <p class="mt-2 text-gray-600">
                    Join GeoCasa Bohol and discover your dream property in paradise
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
                                    <UserGroupIcon class="w-5 h-5 text-teal-600" />
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
                                    <BriefcaseIcon class="w-5 h-5 text-teal-600" />
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            Licensed Real Estate Broker
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            List and sell properties (requires verification)
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <p v-if="getFieldError('role')" class="text-sm text-red-600">
                        {{ getFieldError('role') }}
                    </p>
                </div>

                <!-- Broker Credentials Section -->
                <div
                    v-if="form.role === 'broker'"
                    class="space-y-4 p-4 bg-gray-50 rounded-lg border border-gray-200"
                >
                    <div class="flex items-center gap-2">
                        <BriefcaseIcon class="w-5 h-5 text-teal-600" />
                        <h3 class="text-lg font-medium text-gray-900">
                            Professional Credentials
                        </h3>
                    </div>

                    <!-- PRC ID -->
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

                    <!-- PRC ID File Upload -->
                    <div>
                        <FileUpload
                            v-model="form.prc_id_file"
                            label="PRC License Document"
                            accept=".pdf,.jpg,.jpeg,.png"
                            :error="getFieldError('prc_id_file')"
                            @validation-error="(error) => handleFileValidationError('prc_id_file', error)"
                            @update:modelValue="clearValidationError('prc_id_file')"
                            :max-size="5 * 1024 * 1024"
                            required
                        />
                    </div>

                    <!-- Business Permit -->
                    <div>
                        <ModernInput
                            v-model="form.business_permit"
                            type="text"
                            label="Business Permit Number (Optional)"
                            placeholder="Enter your business permit number"
                            :error="getFieldError('business_permit')"
                            @input="clearValidationError('business_permit')"
                        />
                    </div>

                    <!-- Business Permit File Upload -->
                    <div>
                        <FileUpload
                            v-model="form.business_permit_file"
                            label="Business Permit Document (Optional)"
                            accept=".pdf,.jpg,.jpeg,.png"
                            :error="getFieldError('business_permit_file')"
                            @validation-error="(error) => handleFileValidationError('business_permit_file', error)"
                            @update:modelValue="clearValidationError('business_permit_file')"
                            :max-size="5 * 1024 * 1024"
                        />
                    </div>

                    <!-- Additional Documents -->
                    <div>
                        <FileUpload
                            v-model="form.additional_documents"
                            label="Additional Documents (Optional)"
                            accept=".pdf,.jpg,.jpeg,.png"
                            :error="getFieldError('additional_documents')"
                            @validation-error="(error) => handleFileValidationError('additional_documents', error)"
                            @update:modelValue="clearValidationError('additional_documents')"
                            :max-size="5 * 1024 * 1024"
                            multiple
                        />
                    </div>

                    <!-- Info box -->
                    <div class="bg-teal-50 border border-teal-200 rounded-lg p-3">
                        <div class="flex items-start gap-2">
                            <CheckCircleIcon class="w-5 h-5 text-teal-600 mt-0.5 flex-shrink-0" />
                            <p class="text-sm text-teal-800">
                                <strong>Note:</strong> Your broker application will be reviewed by our admin team. You'll receive an email notification once your credentials are verified.
                            </p>
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
                        @click="showPasswordConfirmation = !showPasswordConfirmation"
                        class="absolute right-4 top-11 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                    >
                        <EyeIcon v-if="!showPasswordConfirmation" class="w-5 h-5" />
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
                <div v-if="Object.keys(clientValidationErrors).length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start gap-2">
                        <ExclamationTriangleIcon class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
                        <div>
                            <h4 class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</h4>
                            <ul class="text-sm text-red-700 space-y-1">
                                <li v-for="(error, field) in clientValidationErrors" :key="field">
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
</template>
