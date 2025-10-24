<script setup>
import { computed } from "vue";

const props = defineProps({
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg", "xl"].includes(value),
    },
    variant: {
        type: String,
        default: "primary",
        validator: (value) => ["primary", "accent", "neutral"].includes(value),
    },
    text: {
        type: String,
        default: null,
    },
    overlay: {
        type: Boolean,
        default: false,
    },
});

const sizeClasses = computed(() => {
    const sizes = {
        sm: "w-4 h-4",
        md: "w-8 h-8",
        lg: "w-12 h-12",
        xl: "w-16 h-16",
    };
    return sizes[props.size] || sizes.md;
});

const variantClasses = computed(() => {
    const variants = {
        primary: "text-primary-600",
        accent: "text-accent-600",
        neutral: "text-neutral-600",
    };
    return variants[props.variant] || variants.primary;
});

const textSizeClasses = computed(() => {
    const sizes = {
        sm: "text-xs",
        md: "text-sm",
        lg: "text-base",
        xl: "text-lg",
    };
    return sizes[props.size] || sizes.md;
});
</script>

<template>
    <div
        :class="[
            'flex flex-col items-center justify-center gap-3',
            overlay ? 'fixed inset-0 bg-white/80 backdrop-blur-sm z-50' : '',
        ]"
    >
        <!-- Spinner -->
        <div
            :class="[
                'animate-spin rounded-full border-2 border-transparent',
                sizeClasses,
                variantClasses,
                'border-t-current',
            ]"
        ></div>

        <!-- Loading Text -->
        <p
            v-if="text"
            :class="[
                'font-medium text-neutral-600 animate-pulse-subtle',
                textSizeClasses,
            ]"
        >
            {{ text }}
        </p>

        <!-- Default Loading Text -->
        <p
            v-else-if="overlay"
            :class="[
                'font-medium text-neutral-600 animate-pulse-subtle',
                textSizeClasses,
            ]"
        >
            Loading...
        </p>
    </div>
</template>
