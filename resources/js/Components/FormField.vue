<template>
    <div class="form-field-container">
        <!-- Label -->
        <label
            v-if="label"
            :for="fieldId"
            class="form-field-label"
            :class="{ 'text-red-700': hasError }"
        >
            {{ label }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </label>

        <!-- Input Field -->
        <div class="form-field-input-wrapper">
            <!-- Text Input -->
            <input
                v-if="
                    type === 'text' ||
                    type === 'email' ||
                    type === 'password' ||
                    type === 'tel' ||
                    type === 'number'
                "
                :id="fieldId"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :required="required"
                :disabled="disabled"
                :class="inputClasses"
                @input="handleInput"
                @blur="handleBlur"
                @focus="handleFocus"
            />

            <!-- Textarea -->
            <textarea
                v-else-if="type === 'textarea'"
                :id="fieldId"
                :value="modelValue"
                :placeholder="placeholder"
                :required="required"
                :disabled="disabled"
                :rows="rows"
                :class="inputClasses"
                @input="handleInput"
                @blur="handleBlur"
                @focus="handleFocus"
            ></textarea>

            <!-- Select -->
            <select
                v-else-if="type === 'select'"
                :id="fieldId"
                :value="modelValue"
                :required="required"
                :disabled="disabled"
                :class="inputClasses"
                @change="handleInput"
                @blur="handleBlur"
                @focus="handleFocus"
            >
                <option value="" disabled>
                    {{ placeholder || "Select an option" }}
                </option>
                <!-- Support for slot content (preferred) -->
                <slot v-if="$slots.default"></slot>
                <!-- Fallback to options prop -->
                <option
                    v-else
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>

            <!-- File Input -->
            <input
                v-else-if="type === 'file'"
                :id="fieldId"
                type="file"
                :accept="accept"
                :required="required"
                :disabled="disabled"
                :multiple="multiple"
                :class="fileInputClasses"
                @change="handleFileInput"
                @blur="handleBlur"
                @focus="handleFocus"
            />

            <!-- Validation Icon -->
            <div v-if="showValidationIcon" class="form-field-validation-icon">
                <CheckCircleIcon
                    v-if="isValid && touched"
                    class="w-5 h-5 text-green-500"
                />
                <ExclamationCircleIcon
                    v-else-if="hasError"
                    class="w-5 h-5 text-red-500"
                />
            </div>
        </div>

        <!-- Help Text -->
        <p v-if="helpText && !hasError" class="form-field-help-text">
            {{ helpText }}
        </p>

        <!-- Enhanced Validation Error with Corrections -->
        <EnhancedValidationError
            :error="error"
            :field-type="type"
            :field-value="modelValue"
            :field-name="fieldId"
            :custom-suggestions="customSuggestions"
            @apply-suggestion="handleSuggestion"
            @quick-action="handleQuickAction"
        />

        <!-- Real-time Validation Feedback -->
        <div
            v-if="realTimeValidation && touched && isValid && modelValue"
            class="form-field-success-feedback"
        >
            <CheckCircleIcon class="w-3 h-3" />
            <span>Looks good!</span>
        </div>

        <!-- Real-time Validation Error Feedback -->
        <div
            v-if="realTimeValidation && touched && !isValid && modelValue && !hasError"
            class="form-field-error-feedback"
        >
            <ExclamationCircleIcon class="w-3 h-3" />
            <span>{{ getValidationErrorMessage() }}</span>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch, nextTick } from "vue";
import {
    CheckCircleIcon,
    ExclamationCircleIcon,
} from "@heroicons/vue/24/outline";
import ValidationError from "./ValidationError.vue";
import EnhancedValidationError from "./EnhancedValidationError.vue";

const props = defineProps({
    modelValue: {
        type: [String, Number, File, Array],
        default: "",
    },
    type: {
        type: String,
        default: "text",
        validator: (value) =>
            [
                "text",
                "email",
                "password",
                "tel",
                "number",
                "textarea",
                "select",
                "file",
            ].includes(value),
    },
    label: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: null,
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
    helpText: {
        type: String,
        default: null,
    },
    rows: {
        type: Number,
        default: 3,
    },
    options: {
        type: Array,
        default: () => [],
    },
    accept: {
        type: String,
        default: null,
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    realTimeValidation: {
        type: Boolean,
        default: true,
    },
    showValidationIcon: {
        type: Boolean,
        default: true,
    },
    validationRules: {
        type: Object,
        default: () => ({}),
    },
    customSuggestions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits([
    "update:modelValue",
    "blur",
    "focus",
    "validate",
    "apply-suggestion",
    "quick-action",
]);

// Generate unique field ID
const fieldId = ref(`field-${Math.random().toString(36).substr(2, 9)}`);

// Track field state
const touched = ref(false);
const focused = ref(false);

// Computed properties
const hasError = computed(() => Boolean(props.error));

const isValid = computed(() => {
    if (!props.modelValue || !touched.value) return false;
    const validation = validateField(props.modelValue);
    return !hasError.value && validation.isValid;
});

const baseInputClasses = computed(() => [
    "block w-full rounded-lg border-0 py-2.5 px-3.5 text-neutral-900 shadow-sm ring-1 ring-inset",
    "placeholder:text-neutral-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6",
    "transition-all duration-200 ease-in-out",
    {
        "ring-neutral-300 focus:ring-primary-600":
            !hasError.value && !isValid.value,
        "ring-green-300 focus:ring-green-600": isValid.value && touched.value,
        "ring-red-300 focus:ring-red-600": hasError.value,
        "bg-neutral-50 text-neutral-500 cursor-not-allowed": props.disabled,
        "pr-10":
            props.showValidationIcon &&
            (hasError.value || (isValid.value && touched.value)),
    },
]);

const inputClasses = computed(() => [
    ...baseInputClasses.value,
    {
        "resize-none": props.type === "textarea",
    },
]);

const fileInputClasses = computed(() => [
    "block w-full text-sm text-neutral-500 file:mr-4 file:py-2 file:px-4",
    "file:rounded-lg file:border-0 file:text-sm file:font-medium",
    "file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100",
    "file:cursor-pointer cursor-pointer",
    "focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2",
    "transition-all duration-200 ease-in-out",
    {
        "file:bg-neutral-50 file:text-neutral-400 cursor-not-allowed":
            props.disabled,
    },
]);

// Validation function
const validateField = (value) => {
    const rules = props.validationRules;
    const errors = [];

    // Required validation
    if (
        props.required &&
        (!value || (typeof value === "string" && !value.trim()))
    ) {
        errors.push(`${props.label || "This field"} is required`);
    }

    // Email validation - Enhanced regex for better validation
    if (props.type === "email" && value) {
        // More comprehensive email validation
        const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
        
        if (!emailRegex.test(value)) {
            errors.push("Please enter a valid email address");
        }
        
        // Additional checks for common invalid patterns
        if (value.includes('..') || value.startsWith('.') || value.endsWith('.')) {
            errors.push("Email address contains invalid characters");
        }
        
        // Check for minimum domain requirements
        const parts = value.split('@');
        if (parts.length !== 2 || parts[0].length === 0 || parts[1].length < 3) {
            errors.push("Please enter a valid email address");
        }
        
        // Check for valid domain structure
        if (parts.length === 2 && !parts[1].includes('.')) {
            errors.push("Please enter a valid email address with a proper domain");
        }
    }

    // Custom validation rules
    if (rules.minLength && value && value.length < rules.minLength) {
        errors.push(`Must be at least ${rules.minLength} characters long`);
    }

    if (rules.maxLength && value && value.length > rules.maxLength) {
        errors.push(`Must not exceed ${rules.maxLength} characters`);
    }

    if (rules.pattern && value && !rules.pattern.test(value)) {
        errors.push(rules.patternMessage || "Invalid format");
    }

    return {
        isValid: errors.length === 0,
        errors,
    };
};

// Event handlers
const getValidationErrorMessage = () => {
    if (!props.modelValue) return '';
    const validation = validateField(props.modelValue);
    return validation.errors.length > 0 ? validation.errors[0] : '';
};

const handleInput = (event) => {
    const value = event.target.value;
    emit("update:modelValue", value);

    if (props.realTimeValidation && touched.value) {
        nextTick(() => {
            const validation = validateField(value);
            emit("validate", {
                field: props.label,
                value,
                isValid: validation.isValid,
                errors: validation.errors,
            });
        });
    }
};

const handleFileInput = (event) => {
    const files = event.target.files;
    const value = props.multiple ? Array.from(files) : files[0];
    emit("update:modelValue", value);

    if (props.realTimeValidation && touched.value) {
        nextTick(() => {
            const validation = validateField(value);
            emit("validate", {
                field: props.label,
                value,
                isValid: validation.isValid,
                errors: validation.errors,
            });
        });
    }
};

const handleBlur = (event) => {
    touched.value = true;
    focused.value = false;
    emit("blur", event);

    if (props.realTimeValidation) {
        const validation = validateField(props.modelValue);
        emit("validate", {
            field: props.label,
            value: props.modelValue,
            isValid: validation.isValid,
            errors: validation.errors,
        });
    }
};

const handleFocus = (event) => {
    focused.value = true;
    emit("focus", event);
};

// Watch for external error changes
watch(
    () => props.error,
    (newError) => {
        if (newError) {
            touched.value = true;
        }
    }
);

// Handle suggestion application
const handleSuggestion = (data) => {
    emit("update:modelValue", data.value);
    emit("apply-suggestion", data);

    // Trigger validation after applying suggestion
    nextTick(() => {
        const validation = validateField(data.value);
        emit("validate", {
            field: props.label,
            value: data.value,
            isValid: validation.isValid,
            errors: validation.errors,
        });
    });
};

// Handle quick actions
const handleQuickAction = (data) => {
    emit("update:modelValue", data.value);
    emit("quick-action", data);

    // Trigger validation after quick action
    nextTick(() => {
        const validation = validateField(data.value);
        emit("validate", {
            field: props.label,
            value: data.value,
            isValid: validation.isValid,
            errors: validation.errors,
        });
    });
};
</script>

<style scoped>
.form-field-container {
    @apply space-y-1;
}

.form-field-label {
    @apply block text-sm font-medium leading-6 text-neutral-900;
}

.form-field-input-wrapper {
    @apply relative;
}

.form-field-validation-icon {
    @apply absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none;
}

.form-field-help-text {
    @apply text-sm text-neutral-600;
}

.form-field-success-feedback {
    @apply flex items-center gap-1 text-sm text-green-600 mt-1;
}

.form-field-error-feedback {
    @apply flex items-center gap-1 text-sm text-red-600 mt-1;
}
</style>
