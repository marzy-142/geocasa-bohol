<script setup>
import { computed } from "vue";
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    value: [String, Number, Boolean, Array],
    rules: {
        type: Array,
        default: () => [],
    },
    field: String,
    showHelp: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["validation-result"]);

const validationResult = computed(() => {
    const errors = [];
    const warnings = [];
    const suggestions = [];

    // Run validation rules
    props.rules.forEach((rule) => {
        const result = rule(props.value);
        if (result) {
            if (result.type === "error") {
                errors.push(result.message);
            } else if (result.type === "warning") {
                warnings.push(result.message);
            } else if (result.type === "suggestion") {
                suggestions.push(result.message);
            }
        }
    });

    // Emit validation result to parent
    emit("validation-result", {
        field: props.field,
        errors,
        warnings,
        suggestions,
        isValid: errors.length === 0,
    });

    return {
        errors,
        warnings,
        suggestions,
        isValid: errors.length === 0,
    };
});

// Common validation rules
const commonRules = {
    required: (value) => {
        if (!value || (typeof value === "string" && !value.trim())) {
            return { type: "error", message: "This field is required" };
        }
        return null;
    },

    email: (value) => {
        if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            return {
                type: "error",
                message: "Please enter a valid email address",
            };
        }
        return null;
    },

    passwordStrength: (value) => {
        if (!value) return null;

        const hasLower = /[a-z]/.test(value);
        const hasUpper = /[A-Z]/.test(value);
        const hasNumber = /\d/.test(value);
        const hasSpecial = /[@$!%*?&]/.test(value);
        const hasLength = value.length >= 12;

        const missing = [];
        if (!hasLower) missing.push("lowercase letter");
        if (!hasUpper) missing.push("uppercase letter");
        if (!hasNumber) missing.push("number");
        if (!hasSpecial) missing.push("special character");
        if (!hasLength) missing.push("12+ characters");

        if (missing.length > 0) {
            return {
                type: "warning",
                message: `Password needs: ${missing.join(", ")}`,
            };
        }
        return null;
    },

    phoneFormat: (value) => {
        if (!value) return null;

        const phoneRegex = /^(\+63|0)[0-9]{10}$/;
        if (!phoneRegex.test(value.replace(/[\s-]/g, ""))) {
            return {
                type: "warning",
                message: "Phone format: +639123456789 or 09123456789",
            };
        }
        return null;
    },

    prcIdFormat: (value) => {
        if (!value) return null;

        // New rule: numeric-only PRC license number
        const numericOnly = /^\d+$/;
        if (!numericOnly.test(String(value).trim())) {
            return {
                type: "warning",
                message: "PRC License number should contain digits only",
            };
        }
        return null;
    },

    minLength: (min) => (value) => {
        if (value && value.length < min) {
            return {
                type: "error",
                message: `Minimum ${min} characters required`,
            };
        }
        return null;
    },

    maxLength: (max) => (value) => {
        if (value && value.length > max) {
            return {
                type: "error",
                message: `Maximum ${max} characters allowed`,
            };
        }
        return null;
    },
};

// Expose common rules
defineExpose({
    commonRules,
});
</script>

<template>
    <div class="space-y-2">
        <!-- Validation Messages -->
        <div v-if="validationResult.errors.length > 0" class="space-y-1">
            <div
                v-for="error in validationResult.errors"
                :key="error"
                class="flex items-center gap-2 text-sm text-red-600"
            >
                <ExclamationTriangleIcon class="w-4 h-4 flex-shrink-0" />
                <span>{{ error }}</span>
            </div>
        </div>

        <!-- Warning Messages -->
        <div v-if="validationResult.warnings.length > 0" class="space-y-1">
            <div
                v-for="warning in validationResult.warnings"
                :key="warning"
                class="flex items-center gap-2 text-sm text-amber-600"
            >
                <ExclamationTriangleIcon class="w-4 h-4 flex-shrink-0" />
                <span>{{ warning }}</span>
            </div>
        </div>

        <!-- Success Messages -->
        <div
            v-if="validationResult.isValid && props.value && showHelp"
            class="flex items-center gap-2 text-sm text-green-600"
        >
            <CheckCircleIcon class="w-4 h-4 flex-shrink-0" />
            <span>Looks good!</span>
        </div>

        <!-- Suggestions -->
        <div v-if="validationResult.suggestions.length > 0" class="space-y-1">
            <div
                v-for="suggestion in validationResult.suggestions"
                :key="suggestion"
                class="flex items-center gap-2 text-sm text-blue-600"
            >
                <InformationCircleIcon class="w-4 h-4 flex-shrink-0" />
                <span>{{ suggestion }}</span>
            </div>
        </div>
    </div>
</template>
