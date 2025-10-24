<template>
    <component :is="component" v-bind="componentProps" :class="buttonClasses">
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
            :class="iconSizeClasses"
        />

        <slot />

        <!-- Right icon -->
        <component
            v-if="icon && iconPosition === 'right' && !loading"
            :is="icon"
            :class="iconSizeClasses"
        />
    </component>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    // Visual variants
    variant: {
        type: String,
        default: "primary",
        validator: (value) =>
            [
                "primary",
                "secondary",
                "accent",
                "warning",
                "danger",
                "ghost",
                "outline",
                "success",
            ].includes(value),
    },

    // Size variants
    size: {
        type: String,
        default: "md",
        validator: (value) => ["xs", "sm", "md", "lg", "xl"].includes(value),
    },

    // State props
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },

    // Icon props
    icon: [Object, Function],
    iconPosition: {
        type: String,
        default: "left",
        validator: (value) => ["left", "right"].includes(value),
    },

    // Navigation props
    href: {
        type: String,
        default: null,
    },
    as: {
        type: String,
        default: null,
    },
    type: {
        type: String,
        default: "button",
    },

    // Styling props
    rounded: {
        type: String,
        default: "lg",
        validator: (value) =>
            ["none", "sm", "md", "lg", "xl", "full"].includes(value),
    },

    // Width props
    fullWidth: {
        type: Boolean,
        default: false,
    },
});

const buttonClasses = computed(() => {
    const variants = {
        primary: [
            "bg-primary-600 text-white",
            "hover:bg-primary-700 focus:ring-primary-500",
            "shadow-card hover:shadow-card-hover",
        ],
        secondary: [
            "bg-white text-neutral-700 border border-neutral-300",
            "hover:bg-neutral-50 hover:border-neutral-400 focus:ring-neutral-500",
            "shadow-card hover:shadow-card-hover",
        ],
        accent: [
            "bg-accent-600 text-white",
            "hover:bg-accent-700 focus:ring-accent-500",
            "shadow-card hover:shadow-card-hover",
        ],
        warning: [
            "bg-warning-600 text-white",
            "hover:bg-warning-700 focus:ring-warning-500",
            "shadow-card hover:shadow-card-hover",
        ],
        danger: [
            "bg-error-600 text-white",
            "hover:bg-error-700 focus:ring-error-500",
            "shadow-card hover:shadow-card-hover",
        ],
        success: [
            "bg-success-600 text-white",
            "hover:bg-success-700 focus:ring-success-500",
            "shadow-card hover:shadow-card-hover",
        ],
        ghost: [
            "bg-transparent text-neutral-600",
            "hover:bg-neutral-100 focus:ring-neutral-500",
        ],
        outline: [
            "border border-primary-300 bg-white text-primary-600",
            "hover:bg-primary-50 focus:ring-primary-500",
            "shadow-card hover:shadow-card-hover",
        ],
    };

    const sizes = {
        xs: "px-2 py-1 text-xs gap-1",
        sm: "px-3 py-1.5 text-sm gap-1.5",
        md: "px-4 py-2 text-sm gap-2",
        lg: "px-6 py-3 text-base gap-2",
        xl: "px-8 py-4 text-lg gap-3",
    };

    const roundedClasses = {
        none: "rounded-none",
        sm: "rounded-sm",
        md: "rounded-md",
        lg: "rounded-lg",
        xl: "rounded-xl",
        full: "rounded-full",
    };

    const baseClasses = [
        "inline-flex items-center justify-center font-semibold",
        "transition-all duration-200 ease-in-out",
        "focus:outline-none focus:ring-2 focus:ring-offset-2",
        "disabled:opacity-50 disabled:cursor-not-allowed",
        props.fullWidth ? "w-full" : "",
        ...variants[props.variant],
        sizes[props.size],
        roundedClasses[props.rounded],
        props.loading || props.disabled
            ? "cursor-not-allowed"
            : "cursor-pointer",
    ];

    return baseClasses;
});

const iconSizeClasses = computed(() => {
    const sizes = {
        xs: "w-3 h-3",
        sm: "w-4 h-4",
        md: "w-4 h-4",
        lg: "w-5 h-5",
        xl: "w-6 h-6",
    };
    return sizes[props.size];
});

const component = computed(() => {
    if (props.as) return props.as;
    if (props.href) return Link;
    return "button";
});

const componentProps = computed(() => {
    const baseProps = {};

    if (props.href) {
        return {
            ...baseProps,
            href: props.href,
        };
    }

    if (props.as === "button" || component.value === "button") {
        return {
            ...baseProps,
            type: props.type,
            disabled: props.loading || props.disabled,
        };
    }

    return baseProps;
});
</script>
