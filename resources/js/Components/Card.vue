<template>
    <div :class="cardClasses">
        <!-- Card Header -->
        <div v-if="$slots.header || title || subtitle" :class="headerClasses">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 v-if="title" :class="titleClasses">{{ title }}</h3>
                    <p v-if="subtitle" :class="subtitleClasses">
                        {{ subtitle }}
                    </p>
                    <slot name="header" />
                </div>
                <div
                    v-if="$slots.headerActions"
                    class="flex items-center gap-2"
                >
                    <slot name="headerActions" />
                </div>
            </div>
        </div>

        <!-- Card Body -->
        <div v-if="$slots.default || $slots.body" :class="bodyClasses">
            <slot />
            <slot name="body" />
        </div>

        <!-- Card Footer -->
        <div v-if="$slots.footer" :class="footerClasses">
            <slot name="footer" />
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    // Content props
    title: {
        type: String,
        default: null,
    },
    subtitle: {
        type: String,
        default: null,
    },

    // Visual variants
    variant: {
        type: String,
        default: "default",
        validator: (value) =>
            ["default", "elevated", "flat", "outlined", "gradient"].includes(
                value
            ),
    },

    // Size variants
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg", "xl"].includes(value),
    },

    // Interactive states
    interactive: {
        type: Boolean,
        default: false,
    },
    hoverable: {
        type: Boolean,
        default: false,
    },

    // Padding variants
    padding: {
        type: String,
        default: "default",
        validator: (value) =>
            ["none", "sm", "default", "lg", "xl"].includes(value),
    },

    // Border radius
    rounded: {
        type: String,
        default: "xl",
        validator: (value) =>
            ["none", "sm", "md", "lg", "xl", "2xl"].includes(value),
    },
});

const cardClasses = computed(() => {
    const variants = {
        default: ["bg-white border border-neutral-200", "shadow-card"],
        elevated: [
            "bg-white border border-neutral-300",
            "shadow-professional-md",
        ],
        flat: ["bg-white border border-neutral-200", "shadow-none"],
        outlined: ["bg-transparent border-2 border-neutral-300", "shadow-none"],
        gradient: [
            "bg-gradient-to-br from-white to-neutral-50",
            "border border-neutral-200",
            "shadow-card",
        ],
    };

    const roundedClasses = {
        none: "rounded-none",
        sm: "rounded-sm",
        md: "rounded-md",
        lg: "rounded-lg",
        xl: "rounded-xl",
        "2xl": "rounded-2xl",
    };

    const interactiveClasses = props.interactive
        ? [
              "cursor-pointer",
              "hover:shadow-card-hover hover:border-neutral-300",
              "hover:-translate-y-0.5 transition-all duration-200",
          ]
        : [];

    const hoverableClasses = props.hoverable
        ? [
              "hover:shadow-card-hover hover:border-neutral-300",
              "transition-all duration-200",
          ]
        : [];

    return [
        ...variants[props.variant],
        roundedClasses[props.rounded],
        ...interactiveClasses,
        ...hoverableClasses,
    ];
});

const headerClasses = computed(() => {
    const paddingClasses = {
        none: "",
        sm: "px-4 py-3",
        default: "px-6 py-4",
        lg: "px-8 py-6",
        xl: "px-10 py-8",
    };

    return ["border-b border-neutral-100", paddingClasses[props.padding]];
});

const bodyClasses = computed(() => {
    const paddingClasses = {
        none: "",
        sm: "p-4",
        default: "p-6",
        lg: "p-8",
        xl: "p-10",
    };

    return paddingClasses[props.padding];
});

const footerClasses = computed(() => {
    const paddingClasses = {
        none: "",
        sm: "px-4 py-3",
        default: "px-6 py-4",
        lg: "px-8 py-6",
        xl: "px-10 py-8",
    };

    return [
        "border-t border-neutral-100 bg-neutral-50",
        paddingClasses[props.padding],
    ];
});

const titleClasses = computed(() => {
    const sizeClasses = {
        sm: "text-lg font-semibold text-neutral-900",
        md: "text-xl font-semibold text-neutral-900",
        lg: "text-2xl font-bold text-neutral-900",
        xl: "text-3xl font-bold text-neutral-900",
    };

    return sizeClasses[props.size];
});

const subtitleClasses = computed(() => {
    return "text-sm text-neutral-600 mt-1";
});
</script>
