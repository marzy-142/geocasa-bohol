<template>
    <div :class="containerClass">
        <!-- Card Skeleton -->
        <div v-if="type === 'card'" class="animate-pulse">
            <div
                class="bg-white rounded-2xl p-6 shadow-soft-lg border border-neutral-100"
            >
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-neutral-200 rounded-2xl"></div>
                        <div class="space-y-2">
                            <div class="h-4 bg-neutral-200 rounded w-24"></div>
                            <div class="h-3 bg-neutral-200 rounded w-16"></div>
                        </div>
                    </div>
                    <div class="w-12 h-12 bg-neutral-200 rounded-2xl"></div>
                </div>
                <div class="space-y-3">
                    <div class="h-8 bg-neutral-200 rounded w-20"></div>
                    <div class="h-4 bg-neutral-200 rounded w-32"></div>
                    <div class="h-4 bg-neutral-200 rounded w-24"></div>
                </div>
            </div>
        </div>

        <!-- Property Card Skeleton -->
        <div v-else-if="type === 'property-card'" class="animate-pulse">
            <div
                class="bg-white rounded-2xl shadow-soft-lg border border-neutral-100 overflow-hidden group"
            >
                <div class="h-56 bg-neutral-200"></div>
                <div class="p-6">
                    <div class="space-y-3 mb-4">
                        <div class="h-6 bg-neutral-200 rounded w-3/4"></div>
                        <div class="h-4 bg-neutral-200 rounded w-1/2"></div>
                    </div>
                    <div class="space-y-2 mb-4">
                        <div class="h-8 bg-neutral-200 rounded w-24"></div>
                        <div class="h-4 bg-neutral-200 rounded w-32"></div>
                    </div>
                    <div class="flex gap-2 mb-4">
                        <div class="h-6 bg-neutral-200 rounded w-16"></div>
                        <div class="h-6 bg-neutral-200 rounded w-20"></div>
                    </div>
                    <div
                        class="flex items-center justify-between pt-4 border-t border-neutral-100"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 bg-neutral-200 rounded-full"
                            ></div>
                            <div class="space-y-1">
                                <div
                                    class="h-4 bg-neutral-200 rounded w-20"
                                ></div>
                                <div
                                    class="h-3 bg-neutral-200 rounded w-16"
                                ></div>
                            </div>
                        </div>
                        <div class="h-8 bg-neutral-200 rounded w-24"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Row Skeleton -->
        <div v-else-if="type === 'table-row'" class="animate-pulse">
            <div class="flex items-center space-x-4 p-4">
                <div class="w-4 h-4 bg-neutral-200 rounded"></div>
                <div class="flex items-center space-x-3 flex-1">
                    <div class="w-10 h-10 bg-neutral-200 rounded-full"></div>
                    <div class="space-y-2 flex-1">
                        <div class="h-4 bg-neutral-200 rounded w-32"></div>
                        <div class="h-3 bg-neutral-200 rounded w-24"></div>
                    </div>
                </div>
                <div class="space-y-2 w-32">
                    <div class="h-4 bg-neutral-200 rounded w-full"></div>
                    <div class="h-3 bg-neutral-200 rounded w-20"></div>
                </div>
                <div class="h-4 bg-neutral-200 rounded w-16"></div>
                <div class="h-4 bg-neutral-200 rounded w-16"></div>
                <div class="h-4 bg-neutral-200 rounded w-20"></div>
                <div class="space-x-2 flex">
                    <div class="h-6 bg-neutral-200 rounded w-12"></div>
                    <div class="h-6 bg-neutral-200 rounded w-12"></div>
                    <div class="h-6 bg-neutral-200 rounded w-12"></div>
                </div>
            </div>
        </div>

        <!-- Stats Card Skeleton -->
        <div v-else-if="type === 'stats-card'" class="animate-pulse">
            <div
                class="bg-white rounded-2xl p-6 shadow-soft-lg border border-neutral-100"
            >
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <div class="h-4 bg-neutral-200 rounded w-24"></div>
                        <div class="h-8 bg-neutral-200 rounded w-16"></div>
                        <div class="h-3 bg-neutral-200 rounded w-20"></div>
                    </div>
                    <div class="w-12 h-12 bg-neutral-200 rounded-2xl"></div>
                </div>
                <div class="mt-4 h-4 bg-neutral-200 rounded w-20"></div>
            </div>
        </div>

        <!-- List Item Skeleton -->
        <div v-else-if="type === 'list-item'" class="animate-pulse">
            <div class="flex items-start gap-4 p-4 bg-neutral-50 rounded-xl">
                <div class="w-8 h-8 bg-neutral-200 rounded-lg"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-neutral-200 rounded w-3/4"></div>
                    <div class="h-3 bg-neutral-200 rounded w-1/2"></div>
                    <div class="h-3 bg-neutral-200 rounded w-1/4"></div>
                </div>
                <div class="h-6 bg-neutral-200 rounded-full w-16"></div>
            </div>
        </div>

        <!-- Generic Skeleton -->
        <div v-else class="animate-pulse">
            <div :class="skeletonClass"></div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    type: {
        type: String,
        default: "generic",
        validator: (value) =>
            [
                "card",
                "property-card",
                "table-row",
                "stats-card",
                "list-item",
                "generic",
            ].includes(value),
    },
    count: {
        type: Number,
        default: 1,
    },
    className: {
        type: String,
        default: "",
    },
});

const containerClass = computed(() => {
    const baseClass = "space-y-4";
    return props.className ? `${baseClass} ${props.className}` : baseClass;
});

const skeletonClass = computed(() => {
    const baseClass = "h-4 bg-neutral-200 rounded";
    return baseClass;
});
</script>

<style scoped>
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}
</style>
