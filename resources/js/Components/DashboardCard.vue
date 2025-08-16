<script setup>
import { computed } from "vue";

const props = defineProps({
    title: String,
    value: [String, Number],
    subtitle: String,
    icon: [Object, Function], // ✅ Accept both Object and Function
    trend: {
        type: Object,
        default: null,
    },
    color: {
        type: String,
        default: "primary",
    },
    loading: {
        type: Boolean,
        default: false,
    },
    interactive: {
        type: Boolean,
        default: false,
    },
});

const colorClasses = computed(() => {
    const colors = {
        primary: {
            bg: "bg-primary-50",
            icon: "text-primary-600",
            trend: "text-primary-600",
            gradient: "from-primary-500 to-primary-600",
        },
        accent: {
            bg: "bg-accent-50",
            icon: "text-accent-600",
            trend: "text-accent-600",
            gradient: "from-accent-500 to-accent-600",
        },
        coconut: {
            bg: "bg-coconut-50",
            icon: "text-coconut-600",
            trend: "text-coconut-600",
            gradient: "from-coconut-400 to-coconut-500",
        },
        neutral: {
            bg: "bg-neutral-50",
            icon: "text-neutral-600",
            trend: "text-neutral-600",
            gradient: "from-neutral-400 to-neutral-500",
        },
    };
    return colors[props.color] || colors.primary;
});
</script>

<template>
    <div
        :class="[
            'bg-white rounded-3xl border border-neutral-100 p-8 transition-all duration-300 ease-in-out',
            'hover:shadow-soft-lg hover:border-neutral-200 hover:-translate-y-1',
            props.interactive ? 'cursor-pointer' : '',
            'animate-scale-in',
        ]"
    >
        <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-4">
                <div
                    :class="[
                        'w-14 h-14 rounded-2xl flex items-center justify-center shadow-soft',
                        colorClasses.bg,
                    ]"
                >
                    <component
                        :is="icon"
                        :class="['w-7 h-7', colorClasses.icon]"
                    />
                </div>
                <div>
                    <h3
                        class="text-sm font-semibold text-neutral-600 uppercase tracking-wide"
                    >
                        {{ title }}
                    </h3>
                    <p v-if="subtitle" class="text-xs text-neutral-500 mt-1">
                        {{ subtitle }}
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div v-if="loading" class="animate-pulse">
                <div class="h-10 bg-neutral-200 rounded-xl w-32"></div>
            </div>
            <div v-else class="text-3xl font-bold text-neutral-900 font-serif">
                {{ value }}
            </div>

            <div v-if="trend" class="flex items-center gap-2">
                <div
                    :class="[
                        'flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-medium',
                        trend.direction === 'up'
                            ? 'bg-accent-50 text-accent-700'
                            : trend.direction === 'down'
                            ? 'bg-red-50 text-red-700'
                            : 'bg-neutral-50 text-neutral-700',
                    ]"
                >
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
                            stroke-width="2"
                            d="M7 17l9.2-9.2M17 17V7H7"
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
                            stroke-width="2"
                            d="M17 7l-9.2 9.2M7 7v10h10"
                        />
                    </svg>
                    <span>{{ trend.value }}</span>
                </div>
                <span class="text-sm text-neutral-500">{{ trend.label }}</span>
            </div>
        </div>
    </div>
</template>
