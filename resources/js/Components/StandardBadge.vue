<template>
    <span :class="badgeClasses">
        <component v-if="icon" :is="icon" class="w-3 h-3 mr-1" />
        <slot />
    </span>
</template>

<script setup>
import { computed } from "vue";
import { getStatusBadgeClass } from "@/DesignSystem";

const props = defineProps({
    status: {
        type: String,
        default: "neutral",
        validator: (value) =>
            [
                "success",
                "warning",
                "error",
                "info",
                "neutral",
                "active",
                "pending",
                "completed",
                "cancelled",
                "new",
                "contacted",
                "scheduled",
                "closed",
                "available",
                "reserved",
                "sold",
                "under_negotiation",
                "off_market",
            ].includes(value),
    },
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg"].includes(value),
    },
    icon: [Object, Function],
});

const badgeClasses = computed(() => {
    const baseClasses = "inline-flex items-center rounded-full font-medium";
    const statusClasses = getStatusBadgeClass(props.status);

    const sizeClasses = {
        sm: "px-2 py-0.5 text-xs",
        md: "px-2.5 py-0.5 text-xs",
        lg: "px-3 py-1 text-sm",
    };

    return `${baseClasses} ${statusClasses} ${sizeClasses[props.size]}`;
});
</script>
