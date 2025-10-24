<template>
    <div class="space-y-1">
        <label
            v-if="label"
            :for="inputId"
            class="block text-sm font-medium text-gray-700"
        >
            {{ label }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </label>

        <div class="relative">
            <div
                v-if="icon && iconPosition === 'left'"
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
            >
                <component :is="icon" class="h-5 w-5 text-gray-400" />
            </div>

            <input
                :id="inputId"
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :class="inputClasses"
                @input="$emit('update:modelValue', $event.target.value)"
                @blur="$emit('blur', $event)"
                @focus="$emit('focus', $event)"
            />

            <div
                v-if="icon && iconPosition === 'right'"
                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
            >
                <component :is="icon" class="h-5 w-5 text-gray-400" />
            </div>
        </div>

        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
        <p v-else-if="help" class="text-sm text-gray-500">{{ help }}</p>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { getVariantClasses } from "@/DesignSystem";

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: "",
    },
    type: {
        type: String,
        default: "text",
    },
    label: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: null,
    },
    error: {
        type: String,
        default: null,
    },
    help: {
        type: String,
        default: null,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg"].includes(value),
    },
    icon: [Object, Function],
    iconPosition: {
        type: String,
        default: "left",
        validator: (value) => ["left", "right"].includes(value),
    },
});

const emit = defineEmits(["update:modelValue", "blur", "focus"]);

const inputId = computed(
    () => `input-${Math.random().toString(36).substr(2, 9)}`
);

const inputClasses = computed(() => {
    const baseClasses = getVariantClasses(
        "input",
        props.error ? "error" : "base"
    );
    const sizeClasses = {
        sm: "px-2 py-1 text-sm",
        md: "px-3 py-2 text-sm",
        lg: "px-4 py-3 text-base",
    };

    const iconPadding = props.icon
        ? props.iconPosition === "left"
            ? "pl-10"
            : "pr-10"
        : "";

    return `${baseClasses} ${sizeClasses[props.size]} ${iconPadding}`;
});
</script>

