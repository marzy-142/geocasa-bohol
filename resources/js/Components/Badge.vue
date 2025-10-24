<template>
    <span :class="badgeClasses">
        <component
            v-if="icon && iconPosition === 'left'"
            :is="icon"
            :class="iconClasses"
        />
        <slot />
        <component
            v-if="icon && iconPosition === 'right'"
            :is="icon"
            :class="iconClasses"
        />
    </span>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    // Visual variants
    variant: {
        type: String,
        default: "default",
        validator: (value) =>
            [
                "default",
                "primary",
                "secondary",
                "success",
                "warning",
                "error",
                "info",
                "accent",
            ].includes(value),
    },

    // Size variants
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg"].includes(value),
    },

    // Style variants
    style: {
        type: String,
        default: "solid",
        validator: (value) => ["solid", "outline", "soft"].includes(value),
    },

    // Icon props
    icon: [Object, Function],
    iconPosition: {
        type: String,
        default: "left",
        validator: (value) => ["left", "right"].includes(value),
    },

    // Interactive
    clickable: {
        type: Boolean,
        default: false,
    },

    // Shape
    rounded: {
        type: String,
        default: "full",
        validator: (value) =>
            ["none", "sm", "md", "lg", "full"].includes(value),
    },
});

const badgeClasses = computed(() => {
    const variants = {
        default: {
            solid: "bg-neutral-100 text-neutral-800",
            outline:
                "border border-neutral-300 text-neutral-700 bg-transparent",
            soft: "bg-neutral-50 text-neutral-700",
        },
        primary: {
            solid: "bg-primary-600 text-white",
            outline:
                "border border-primary-300 text-primary-700 bg-transparent",
            soft: "bg-primary-50 text-primary-700",
        },
        secondary: {
            solid: "bg-neutral-600 text-white",
            outline:
                "border border-neutral-300 text-neutral-700 bg-transparent",
            soft: "bg-neutral-50 text-neutral-700",
        },
        success: {
            solid: "bg-success-600 text-white",
            outline:
                "border border-success-300 text-success-700 bg-transparent",
            soft: "bg-success-50 text-success-700",
        },
        warning: {
            solid: "bg-warning-600 text-white",
            outline:
                "border border-warning-300 text-warning-700 bg-transparent",
            soft: "bg-warning-50 text-warning-700",
        },
        error: {
            solid: "bg-error-600 text-white",
            outline: "border border-error-300 text-error-700 bg-transparent",
            soft: "bg-error-50 text-error-700",
        },
        info: {
            solid: "bg-info-600 text-white",
            outline: "border border-info-300 text-info-700 bg-transparent",
            soft: "bg-info-50 text-info-700",
        },
        accent: {
            solid: "bg-accent-600 text-white",
            outline: "border border-accent-300 text-accent-700 bg-transparent",
            soft: "bg-accent-50 text-accent-700",
        },
    };

    const sizes = {
        sm: "px-2 py-0.5 text-xs gap-1",
        md: "px-2.5 py-1 text-sm gap-1.5",
        lg: "px-3 py-1.5 text-base gap-2",
    };

    const roundedClasses = {
        none: "rounded-none",
        sm: "rounded-sm",
        md: "rounded-md",
        lg: "rounded-lg",
        full: "rounded-full",
    };

    const interactiveClasses = props.clickable
        ? ["cursor-pointer hover:opacity-80 transition-opacity"]
        : [];

    return [
        "inline-flex items-center font-medium",
        variants[props.variant][props.style],
        sizes[props.size],
        roundedClasses[props.rounded],
        ...interactiveClasses,
    ];
});

const iconClasses = computed(() => {
    const sizes = {
        sm: "w-3 h-3",
        md: "w-4 h-4",
        lg: "w-5 h-5",
    };
    return sizes[props.size];
});
</script>
