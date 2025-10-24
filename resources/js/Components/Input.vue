<template>
    <div class="form-field-container">
        <!-- Label -->
        <label v-if="label" :for="inputId" :class="labelClasses">
            {{ label }}
            <span v-if="required" class="text-error-500 ml-1">*</span>
        </label>

        <!-- Input wrapper -->
        <div :class="inputWrapperClasses">
            <!-- Left icon -->
            <div v-if="leftIcon" :class="leftIconClasses">
                <component :is="leftIcon" class="w-5 h-5 text-neutral-400" />
            </div>

            <!-- Input element -->
            <component
                :is="inputComponent"
                :id="inputId"
                v-model="model"
                :type="inputType"
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :required="required"
                :autocomplete="autocomplete"
                :class="inputClasses"
                v-bind="$attrs"
                @input="handleInput"
                @blur="handleBlur"
                @focus="handleFocus"
            />

            <!-- Right icon or clear button -->
            <div v-if="rightIcon || showClear" :class="rightIconClasses">
                <!-- Clear button -->
                <button
                    v-if="showClear && model"
                    type="button"
                    @click="clearInput"
                    class="text-neutral-400 hover:text-neutral-600 transition-colors"
                >
                    <XMarkIcon class="w-4 h-4" />
                </button>

                <!-- Right icon -->
                <component
                    v-else-if="rightIcon"
                    :is="rightIcon"
                    class="w-5 h-5 text-neutral-400"
                />
            </div>
        </div>

        <!-- Help text -->
        <p v-if="helpText && !hasError" class="form-field-help-text">
            {{ helpText }}
        </p>

        <!-- Error message -->
        <p v-if="hasError" class="text-sm text-error-600 mt-1">
            {{ errorMessage }}
        </p>

        <!-- Success message -->
        <p v-if="hasSuccess" class="text-sm text-success-600 mt-1">
            {{ successMessage }}
        </p>
    </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { XMarkIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    // Input props
    modelValue: [String, Number],
    type: {
        type: String,
        default: "text",
    },
    placeholder: {
        type: String,
        default: "",
    },

    // Label and help
    label: {
        type: String,
        default: null,
    },
    helpText: {
        type: String,
        default: null,
    },

    // State props
    disabled: {
        type: Boolean,
        default: false,
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },

    // Validation
    error: {
        type: [String, Boolean],
        default: null,
    },
    success: {
        type: [String, Boolean],
        default: null,
    },

    // Icons
    leftIcon: [Object, Function],
    rightIcon: [Object, Function],

    // Features
    clearable: {
        type: Boolean,
        default: false,
    },
    autocomplete: {
        type: String,
        default: null,
    },

    // Size
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg"].includes(value),
    },
});

const emit = defineEmits([
    "update:modelValue",
    "blur",
    "focus",
    "input",
    "clear",
]);

// Generate unique ID for accessibility
const inputId = ref(`input-${Math.random().toString(36).substr(2, 9)}`);

// Reactive model
const model = computed({
    get: () => props.modelValue,
    set: (value) => emit("update:modelValue", value),
});

// Computed properties
const inputComponent = computed(() => {
    return props.type === "textarea" ? "textarea" : "input";
});

const inputType = computed(() => {
    if (props.type === "textarea") return null;
    return props.type;
});

const hasError = computed(() => {
    return (
        props.error &&
        (typeof props.error === "string" ? props.error.length > 0 : props.error)
    );
});

const hasSuccess = computed(() => {
    return (
        props.success &&
        (typeof props.success === "string"
            ? props.success.length > 0
            : props.success)
    );
});

const errorMessage = computed(() => {
    return typeof props.error === "string" ? props.error : null;
});

const successMessage = computed(() => {
    return typeof props.success === "string" ? props.success : null;
});

const showClear = computed(() => {
    return props.clearable && model.value && !props.disabled && !props.readonly;
});

// Classes
const labelClasses = computed(() => {
    return [
        "form-field-label",
        props.required
            ? "after:content-['*'] after:text-error-500 after:ml-1"
            : "",
    ];
});

const inputWrapperClasses = computed(() => {
    return [
        "form-field-input-wrapper",
        "relative",
        props.leftIcon || props.rightIcon || showClear.value
            ? "flex items-center"
            : "",
    ];
});

const inputClasses = computed(() => {
    const sizeClasses = {
        sm: "py-2 px-3 text-sm",
        md: "py-3 px-4 text-sm",
        lg: "py-4 px-5 text-base",
    };

    const stateClasses = hasError.value
        ? "error"
        : hasSuccess.value
        ? "success"
        : "";

    const iconPaddingClasses = {
        left: props.leftIcon ? "pl-10" : "",
        right: props.rightIcon || showClear.value ? "pr-10" : "",
    };

    return [
        "form-field-container input,textarea,select",
        sizeClasses[props.size],
        stateClasses,
        iconPaddingClasses.left,
        iconPaddingClasses.right,
    ];
});

const leftIconClasses = computed(() => {
    return [
        "absolute left-3 top-1/2 transform -translate-y-1/2",
        "pointer-events-none z-10",
    ];
});

const rightIconClasses = computed(() => {
    return ["absolute right-3 top-1/2 transform -translate-y-1/2", "z-10"];
});

// Event handlers
const handleInput = (event) => {
    emit("input", event);
};

const handleBlur = (event) => {
    emit("blur", event);
};

const handleFocus = (event) => {
    emit("focus", event);
};

const clearInput = () => {
    model.value = "";
    emit("clear");
};
</script>
