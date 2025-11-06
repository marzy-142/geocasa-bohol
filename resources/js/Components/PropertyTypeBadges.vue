<template>
    <div class="flex flex-wrap gap-1.5">
        <span
            v-for="type in displayTypes"
            :key="type.value"
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors"
            :class="getBadgeClass(type.value)"
            :title="type.label"
        >
            {{ type.label }}
        </span>
        <span
            v-if="hasMoreTypes"
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
            :title="remainingTypesText"
        >
            +{{ remainingCount }} more
        </span>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    types: {
        type: Array,
        default: () => [],
    },
    customTypeText: {
        type: String,
        default: null,
    },
    maxDisplay: {
        type: Number,
        default: 3,
    },
});

// Normalize types to ensure they're objects with value and label
const normalizedTypes = computed(() => {
    if (!Array.isArray(props.types)) return [];

    return props.types
        .map((type) => {
            // Handle null/undefined
            if (!type) return { value: "", label: "" };

            // If already an object with value and label
            if (
                typeof type === "object" &&
                type !== null &&
                type.value &&
                type.label
            ) {
                return {
                    value: String(type.value),
                    label: String(type.label),
                };
            }

            // If it's a string, convert to object
            if (typeof type === "string") {
                // If type is "other" and we have custom type text, use that instead
                if (type === "other" && props.customTypeText) {
                    return {
                        value: "other",
                        label: props.customTypeText,
                    };
                }

                const label = type
                    .replace(/_/g, " ")
                    .replace(/\b\w/g, (l) => l.toUpperCase());
                return { value: type, label };
            }

            // Fallback - convert to string
            const strValue = String(type);
            const label = strValue
                .replace(/_/g, " ")
                .replace(/\b\w/g, (l) => l.toUpperCase());
            return { value: strValue, label };
        })
        .filter((t) => t.value); // Remove empty values
});

const displayTypes = computed(() => {
    return normalizedTypes.value.slice(0, props.maxDisplay);
});

const hasMoreTypes = computed(() => {
    return normalizedTypes.value.length > props.maxDisplay;
});

const remainingCount = computed(() => {
    return normalizedTypes.value.length - props.maxDisplay;
});

const remainingTypesText = computed(() => {
    const remaining = normalizedTypes.value.slice(props.maxDisplay);
    return remaining.map((t) => t.label).join(", ");
});

const getBadgeClass = (typeValue) => {
    const colorMap = {
        // Residential types - Blue
        residential_lot: "bg-blue-100 text-blue-800 border border-blue-200",
        subdivision_lot: "bg-blue-100 text-blue-800 border border-blue-200",

        // Commercial types - Purple
        commercial_lot:
            "bg-purple-100 text-purple-800 border border-purple-200",

        // Industrial types - Gray
        industrial_lot: "bg-gray-100 text-gray-800 border border-gray-200",

        // Agricultural types - Green
        agricultural_land:
            "bg-green-100 text-green-800 border border-green-200",
        rice_field: "bg-green-100 text-green-800 border border-green-200",
        coconut_plantation:
            "bg-green-100 text-green-800 border border-green-200",

        // Special/Scenic types - Teal/Cyan
        beachfront: "bg-cyan-100 text-cyan-800 border border-cyan-200",
        mountain_view: "bg-teal-100 text-teal-800 border border-teal-200",

        // Other - Orange
        other: "bg-orange-100 text-orange-800 border border-orange-200",
    };

    return (
        colorMap[typeValue] ||
        "bg-gray-100 text-gray-800 border border-gray-200"
    );
};
</script>
