<script setup>
import { computed } from "vue";

const props = defineProps({
    title: String,
    value: [String, Number],
    subtitle: String,
    icon: [Object, Function],
    trend: {
        type: Object,
        default: null,
    },
    color: {
        type: String,
        default: "primary",
        validator: (value) =>
            [
                "primary",
                "accent",
                "warning",
                "error",
                "neutral",
                "property",
            ].includes(value),
    },
    loading: {
        type: Boolean,
        default: false,
    },
    interactive: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: "default",
        validator: (value) => ["small", "default", "large"].includes(value),
    },
});

const colorClasses = computed(() => {
    const colors = {
        primary: {
            bg: "bg-primary-50",
            icon: "text-primary-600",
            trend: "text-primary-600",
            border: "border-primary-200",
        },
        accent: {
            bg: "bg-accent-50",
            icon: "text-accent-600",
            trend: "text-accent-600",
            border: "border-accent-200",
        },
        warning: {
            bg: "bg-warning-50",
            icon: "text-warning-600",
            trend: "text-warning-600",
            border: "border-warning-200",
        },
        error: {
            bg: "bg-error-50",
            icon: "text-error-600",
            trend: "text-error-600",
            border: "border-error-200",
        },
        neutral: {
            bg: "bg-neutral-50",
            icon: "text-neutral-600",
            trend: "text-neutral-600",
            border: "border-neutral-200",
        },
        property: {
            bg: "bg-property-50",
            icon: "text-property-600",
            trend: "text-property-600",
            border: "border-property-200",
        },
    };
    return colors[props.color] || colors.primary;
});

const sizeClasses = computed(() => {
    const sizes = {
        small: {
            padding: "p-4",
            iconSize: "w-8 h-8",
            iconClass: "w-4 h-4",
            titleClass: "text-xs",
            valueClass: "text-lg",
        },
        default: {
            padding: "p-6",
            iconSize: "w-12 h-12",
            iconClass: "w-6 h-6",
            titleClass: "text-sm",
            valueClass: "text-2xl",
        },
        large: {
            padding: "p-8",
            iconSize: "w-16 h-16",
            iconClass: "w-8 h-8",
            titleClass: "text-base",
            valueClass: "text-3xl",
        },
    };
    return sizes[props.size] || sizes.default;
});
</script>

<template>
    <div
        :class="[
            'card',
            sizeClasses.padding,
            props.interactive ? 'card-interactive' : 'card-hover',
        ]"
    >
        <!-- Header with Icon and Title -->
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div
                    :class="[
                        sizeClasses.iconSize,
                        'rounded-lg flex items-center justify-center border',
                        colorClasses.bg,
                        colorClasses.border,
                    ]"
                >
                    <component
                        :is="icon"
                        :class="[sizeClasses.iconClass, colorClasses.icon]"
                    />
                </div>
                <div>
                    <h3
                        :class="[
                            sizeClasses.titleClass,
                            'font-semibold text-neutral-600 uppercase tracking-wide',
                        ]"
                    >
                        {{ title }}
                    </h3>
                    <p v-if="subtitle" class="text-xs text-neutral-500 mt-0.5">
                        {{ subtitle }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Value and Trend -->
        <div class="space-y-3">
            <!-- Loading State -->
            <div v-if="loading" class="space-y-2">
                <div class="h-8 bg-neutral-200 rounded-lg animate-pulse"></div>
                <div
                    class="h-4 bg-neutral-200 rounded w-24 animate-pulse"
                ></div>
            </div>

            <!-- Value Display -->
            <div v-else>
                <div
                    :class="[
                        sizeClasses.valueClass,
                        'font-bold text-neutral-900 tabular-nums',
                    ]"
                >
                    {{ value }}
                </div>

                <!-- Trend Indicator -->
                <div v-if="trend" class="flex items-center gap-2 mt-2">
                    <div
                        :class="[
                            'flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium',
                            trend.direction === 'up'
                                ? 'bg-accent-50 text-accent-700 border border-accent-200'
                                : trend.direction === 'down'
                                ? 'bg-error-50 text-error-700 border border-error-200'
                                : 'bg-neutral-50 text-neutral-700 border border-neutral-200',
                        ]"
                    >
                        <!-- Trend Arrow -->
                        <svg
                            v-if="trend.direction === 'up'"
                            class="w-3 h-3"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M7 14l3-3 3 3"
                            />
                        </svg>
                        <svg
                            v-else-if="trend.direction === 'down'"
                            class="w-3 h-3"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M17 10l-3 3-3-3"
                            />
                        </svg>
                        <svg
                            v-else
                            class="w-3 h-3"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M20 12H4"
                            />
                        </svg>
                        <span>{{ trend.value }}</span>
                    </div>
                    <span v-if="trend.label" class="text-xs text-neutral-500">{{
                        trend.label
                    }}</span>
                </div>
            </div>
        </div>

        <!-- Slot for additional content -->
        <div v-if="$slots.default" class="mt-4">
            <slot />
        </div>
    </div>
</template>
