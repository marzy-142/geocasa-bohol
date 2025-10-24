<template>
    <div :class="cardClasses">
        <div v-if="$slots.header" class="px-6 py-4 border-b border-neutral-200">
            <slot name="header" />
        </div>

        <div :class="bodyClasses">
            <slot />
        </div>

        <div v-if="$slots.footer" class="px-6 py-4 border-t border-neutral-200">
            <slot name="footer" />
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { getVariantClasses } from "@/DesignSystem";

const props = defineProps({
    variant: {
        type: String,
        default: "base",
        validator: (value) => ["base", "elevated", "flat"].includes(value),
    },
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg"].includes(value),
    },
    padding: {
        type: String,
        default: null,
    },
    hover: {
        type: Boolean,
        default: false,
    },
});

const cardClasses = computed(() => {
    const baseClasses =
        "bg-white rounded-xl border border-neutral-200 shadow-sm";
    const hoverClasses = props.hover
        ? "hover:shadow-md transition-shadow duration-200"
        : "";

    return `${baseClasses} ${hoverClasses}`;
});

const bodyClasses = computed(() => {
    if (props.padding) {
        return `p-${props.padding}`;
    }

    const sizePadding = {
        sm: "p-4",
        md: "p-6",
        lg: "p-8",
    };

    return sizePadding[props.size];
});
</script>
