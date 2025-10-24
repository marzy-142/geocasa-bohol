<script setup>
import { computed } from "vue";

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    type: {
        type: String,
        default: "text",
    },
    placeholder: String,
    error: String,
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    icon: [Object, Function], // Accept both Object and Function
    helper: String,
    size: {
        type: String,
        default: "md",
    },
});

const emit = defineEmits(["update:modelValue"]);

const inputClasses = computed(() => {
    const sizes = {
        sm: "px-4 py-2.5 text-sm",
        md: "px-4 py-3.5 text-sm",
        lg: "px-6 py-4 text-base",
    };

    return [
        "block w-full border border-neutral-200 rounded-xl transition-colors duration-200",
        "placeholder-neutral-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500",
        "shadow-sm focus:shadow-md",
        props.error
            ? "bg-red-50 focus:ring-red-500 focus:border-red-500 text-red-900 border-red-200"
            : "bg-white focus:bg-white text-neutral-900",
        props.disabled ? "opacity-50 cursor-not-allowed bg-neutral-100" : "",
        props.icon ? "pl-12" : "",
        sizes[props.size],
    ];
});
</script>

<template>
    <div class="space-y-3">
        <label v-if="label" class="block text-sm font-medium text-neutral-700">
            {{ label }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </label>

        <div class="relative">
            <div
                v-if="icon"
                class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
            >
                <component :is="icon" class="w-5 h-5 text-neutral-400" />
            </div>

            <input
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :class="inputClasses"
                :aria-invalid="error ? 'true' : 'false'"
                :aria-describedby="
                    error
                        ? `${$attrs.id}-error`
                        : helper
                        ? `${$attrs.id}-helper`
                        : null
                "
                @input="emit('update:modelValue', $event.target.value)"
            />
        </div>

        <p
            v-if="error"
            :id="`${$attrs.id}-error`"
            class="text-sm text-red-600 flex items-center gap-2"
        >
            <svg
                class="w-4 h-4 flex-shrink-0"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path
                    fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"
                />
            </svg>
            {{ error }}
        </p>

        <p
            v-else-if="helper"
            :id="`${$attrs.id}-helper`"
            class="text-sm text-neutral-500"
        >
            {{ helper }}
        </p>
    </div>
</template>
