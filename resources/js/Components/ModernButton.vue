<script setup>
import { computed } from "vue";
import { Link } from '@inertiajs/vue3';

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
            "bg-primary-500 text-white hover:bg-primary-600 focus:ring-primary-500 shadow-soft hover:shadow-soft-md",
        secondary:
            "bg-primary-50 text-primary-700 hover:bg-primary-100 focus:ring-primary-500 border border-primary-200 hover:border-primary-300",
        accent: "bg-accent-500 text-white hover:bg-accent-600 focus:ring-accent-500 shadow-soft hover:shadow-soft-md",
        coconut:
            "bg-coconut-400 text-coconut-900 hover:bg-coconut-500 focus:ring-coconut-500 shadow-soft hover:shadow-soft-md",
        ghost: "bg-transparent text-neutral-700 hover:bg-neutral-50 focus:ring-neutral-500 hover:shadow-soft",
        outline:
            "border border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50 focus:ring-primary-500 shadow-soft hover:shadow-soft-md",
        danger: "bg-red-500 text-white hover:bg-red-600 focus:ring-red-500 shadow-soft hover:shadow-soft-md",
    };

    const sizes = {
        sm: "px-4 py-2.5 text-sm",
        md: "px-6 py-3 text-sm",
        lg: "px-8 py-4 text-base",
        xl: "px-10 py-5 text-lg",
    };

    const roundedClasses = {
        none: "rounded-none",
        sm: "rounded-lg",
        md: "rounded-xl",
        lg: "rounded-2xl",
        xl: "rounded-2xl",
        full: "rounded-full",
    };

    return [
        "inline-flex items-center justify-center gap-3 font-medium transition-all duration-200 ease-in-out",
        "focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed",
        "transform hover:-translate-y-0.5 active:translate-y-0",
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
    return 'button';
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
                stroke-width="4"
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
