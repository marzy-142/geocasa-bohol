<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    variant: {
        type: String,
        default: "primary",
    },
    size: {
        type: String,
        default: "md",
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    icon: [Object, Function],
    iconPosition: {
        type: String,
        default: "left",
    },
    rounded: {
        type: String,
        default: "xl",
    },
    href: {
        type: String,
        default: null,
    },
    as: {
        type: String,
        default: null,
    },
});

const buttonClasses = computed(() => {
    const variants = {
        primary:
            "bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 shadow-card hover:shadow-card-hover",
        secondary:
            "bg-white text-neutral-700 hover:bg-neutral-50 focus:ring-neutral-500 border border-neutral-300 hover:border-neutral-400 shadow-card hover:shadow-card-hover",
        accent: "bg-accent-600 text-white hover:bg-accent-700 focus:ring-accent-500 shadow-card hover:shadow-card-hover",
        warning:
            "bg-warning-600 text-white hover:bg-warning-700 focus:ring-warning-500 shadow-card hover:shadow-card-hover",
        ghost: "bg-transparent text-neutral-600 hover:bg-neutral-100 focus:ring-neutral-500",
        outline:
            "border border-primary-300 bg-white text-primary-600 hover:bg-primary-50 focus:ring-primary-500 shadow-card hover:shadow-card-hover",
        danger: "bg-error-600 text-white hover:bg-error-700 focus:ring-error-500 shadow-card hover:shadow-card-hover",
    };

    const sizes = {
        sm: "px-3 py-2 text-sm",
        md: "px-4 py-2 text-sm",
        lg: "px-6 py-3 text-base",
        xl: "px-8 py-4 text-lg",
    };

    const roundedClasses = {
        none: "rounded-none",
        sm: "rounded-md",
        md: "rounded-lg",
        lg: "rounded-xl",
        xl: "rounded-xl",
        full: "rounded-full",
    };

    return [
        "inline-flex items-center justify-center gap-2 font-medium transition-colors duration-200",
        "focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed",
        variants[props.variant],
        sizes[props.size],
        roundedClasses[props.rounded],
        props.loading || props.disabled
            ? "cursor-not-allowed"
            : "cursor-pointer",
    ];
});

const component = computed(() => {
    if (props.as) return props.as;
    if (props.href) return Link;
    return "button";
});

const componentProps = computed(() => {
    const baseProps = {
        class: buttonClasses.value,
    };

    if (props.href) {
        return {
            ...baseProps,
            href: props.href,
        };
    }

    return {
        ...baseProps,
        disabled: props.loading || props.disabled,
    };
});
</script>

<template>
    <component :is="component" v-bind="componentProps">
        <!-- Loading spinner -->
        <svg
            v-if="loading"
            class="w-4 h-4 animate-spin"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="2"
            ></circle>
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>

        <!-- Left icon -->
        <component
            v-else-if="icon && iconPosition === 'left'"
            :is="icon"
            class="w-4 h-4"
        />

        <slot />

        <!-- Right icon -->
        <component
            v-if="icon && iconPosition === 'right' && !loading"
            :is="icon"
            class="w-4 h-4"
        />
    </component>
</template>
