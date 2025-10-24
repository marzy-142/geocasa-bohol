<script setup>
import { computed } from "vue";

const props = defineProps({
    status: {
        type: String,
        required: true,
        validator: (value) =>
            [
                "active",
                "inactive",
                "pending",
                "approved",
                "rejected",
                "completed",
                "cancelled",
                "online",
                "offline",
                "available",
                "busy",
                "success",
                "warning",
                "error",
                "info",
            ].includes(value),
    },
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg"].includes(value),
    },
    showIcon: {
        type: Boolean,
        default: false,
    },
});

const statusConfig = computed(() => {
    const configs = {
        active: {
            bg: "bg-accent-50",
            text: "text-accent-700",
            border: "border-accent-200",
            icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
        },
        inactive: {
            bg: "bg-neutral-50",
            text: "text-neutral-600",
            border: "border-neutral-200",
            icon: "M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z",
        },
        pending: {
            bg: "bg-warning-50",
            text: "text-warning-700",
            border: "border-warning-200",
            icon: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
        },
        approved: {
            bg: "bg-accent-50",
            text: "text-accent-700",
            border: "border-accent-200",
            icon: "M5 13l4 4L19 7",
        },
        rejected: {
            bg: "bg-error-50",
            text: "text-error-700",
            border: "border-error-200",
            icon: "M6 18L18 6M6 6l12 12",
        },
        completed: {
            bg: "bg-accent-50",
            text: "text-accent-700",
            border: "border-accent-200",
            icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
        },
        cancelled: {
            bg: "bg-error-50",
            text: "text-error-700",
            border: "border-error-200",
            icon: "M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2",
        },
        online: {
            bg: "bg-accent-50",
            text: "text-accent-700",
            border: "border-accent-200",
            icon: "M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z",
        },
        offline: {
            bg: "bg-neutral-50",
            text: "text-neutral-600",
            border: "border-neutral-200",
            icon: "M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z",
        },
        available: {
            bg: "bg-accent-50",
            text: "text-accent-700",
            border: "border-accent-200",
            icon: "M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z",
        },
        busy: {
            bg: "bg-warning-50",
            text: "text-warning-700",
            border: "border-warning-200",
            icon: "M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728",
        },
        success: {
            bg: "bg-accent-50",
            text: "text-accent-700",
            border: "border-accent-200",
            icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
        },
        warning: {
            bg: "bg-warning-50",
            text: "text-warning-700",
            border: "border-warning-200",
            icon: "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z",
        },
        error: {
            bg: "bg-error-50",
            text: "text-error-700",
            border: "border-error-200",
            icon: "M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
        },
        info: {
            bg: "bg-primary-50",
            text: "text-primary-700",
            border: "border-primary-200",
            icon: "M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
        },
    };
    return configs[props.status] || configs.inactive;
});

const sizeClasses = computed(() => {
    const sizes = {
        sm: {
            container: "px-2 py-1 text-xs",
            icon: "w-3 h-3",
        },
        md: {
            container: "px-3 py-1.5 text-sm",
            icon: "w-4 h-4",
        },
        lg: {
            container: "px-4 py-2 text-base",
            icon: "w-5 h-5",
        },
    };
    return sizes[props.size] || sizes.md;
});

const displayText = computed(() => {
    // Capitalize first letter of status
    return props.status.charAt(0).toUpperCase() + props.status.slice(1);
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-1.5 rounded-lg border font-medium',
            statusConfig.bg,
            statusConfig.text,
            statusConfig.border,
            sizeClasses.container,
        ]"
    >
        <!-- Status Icon -->
        <svg
            v-if="showIcon"
            :class="['flex-shrink-0', sizeClasses.icon]"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                :d="statusConfig.icon"
            />
        </svg>

        <!-- Status Text -->
        <span>{{ displayText }}</span>
    </span>
</template>
