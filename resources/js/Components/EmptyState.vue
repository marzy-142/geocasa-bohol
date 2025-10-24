<template>
    <div :class="containerClass">
        <!-- Illustration -->
        <div class="flex justify-center mb-6">
            <div :class="illustrationClass">
                <component v-if="icon" :is="icon" :class="iconClass" />
                <div v-else class="text-6xl">{{ emoji }}</div>
            </div>
        </div>

        <!-- Content -->
        <div class="text-center max-w-md mx-auto">
            <h3 :class="titleClass">
                {{ title }}
            </h3>
            <p :class="descriptionClass">
                {{ description }}
            </p>

            <!-- Action Buttons -->
            <div v-if="actions && actions.length > 0" class="mt-6 space-y-3">
                <div
                    v-for="(action, index) in actions"
                    :key="index"
                    class="flex justify-center"
                >
                    <component
                        :is="action.component || 'button'"
                        :href="action.href"
                        :to="action.to"
                        :class="getActionClass(action.variant)"
                        @click="action.onClick"
                    >
                        <component
                            v-if="action.icon"
                            :is="action.icon"
                            class="w-4 h-4 mr-2"
                        />
                        {{ action.text }}
                    </component>
                </div>
            </div>

            <!-- Custom Slot Content -->
            <div v-if="$slots.default" class="mt-6">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
    icon: {
        type: [Object, Function],
        default: null,
    },
    emoji: {
        type: String,
        default: "📭",
    },
    variant: {
        type: String,
        default: "neutral",
        validator: (value) =>
            ["neutral", "primary", "success", "warning", "error"].includes(
                value
            ),
    },
    size: {
        type: String,
        default: "lg",
        validator: (value) => ["sm", "md", "lg", "xl"].includes(value),
    },
    actions: {
        type: Array,
        default: () => [],
    },
    className: {
        type: String,
        default: "",
    },
});

const containerClass = computed(() => {
    const baseClass = "py-12 px-4";
    const sizeClasses = {
        sm: "py-8",
        md: "py-10",
        lg: "py-12",
        xl: "py-16",
    };
    return `${baseClass} ${sizeClasses[props.size]} ${props.className}`;
});

const illustrationClass = computed(() => {
    const baseClass = "rounded-3xl flex items-center justify-center";
    const sizeClasses = {
        sm: "w-16 h-16",
        md: "w-20 h-20",
        lg: "w-24 h-24",
        xl: "w-32 h-32",
    };
    const variantClasses = {
        neutral: "bg-neutral-100",
        primary: "bg-primary-100",
        success: "bg-green-100",
        warning: "bg-yellow-100",
        error: "bg-red-100",
    };
    return `${baseClass} ${sizeClasses[props.size]} ${
        variantClasses[props.variant]
    }`;
});

const iconClass = computed(() => {
    const sizeClasses = {
        sm: "w-8 h-8",
        md: "w-10 h-10",
        lg: "w-12 h-12",
        xl: "w-16 h-16",
    };
    const variantClasses = {
        neutral: "text-neutral-400",
        primary: "text-primary-500",
        success: "text-green-500",
        warning: "text-yellow-500",
        error: "text-red-500",
    };
    return `${sizeClasses[props.size]} ${variantClasses[props.variant]}`;
});

const titleClass = computed(() => {
    const sizeClasses = {
        sm: "text-lg",
        md: "text-xl",
        lg: "text-2xl",
        xl: "text-3xl",
    };
    return `${sizeClasses[props.size]} font-bold text-neutral-900 mb-2`;
});

const descriptionClass = computed(() => {
    const sizeClasses = {
        sm: "text-sm",
        md: "text-base",
        lg: "text-lg",
        xl: "text-xl",
    };
    return `${sizeClasses[props.size]} text-neutral-500`;
});

const getActionClass = (variant = "primary") => {
    const baseClass =
        "inline-flex items-center px-6 py-3 rounded-lg font-medium transition-all duration-200 hover:scale-105";
    const variantClasses = {
        primary: "bg-primary-600 hover:bg-primary-700 text-white",
        secondary: "bg-neutral-100 hover:bg-neutral-200 text-neutral-700",
        outline:
            "border border-primary-600 text-primary-600 hover:bg-primary-50",
        ghost: "text-primary-600 hover:bg-primary-50",
    };
    return `${baseClass} ${variantClasses[variant]}`;
};
</script>
