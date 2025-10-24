<template>
    <div
        class="relative inline-block"
        @mouseenter="showTooltip"
        @mouseleave="hideTooltip"
        @focus="showTooltip"
        @blur="hideTooltip"
    >
        <!-- Trigger Element -->
        <slot name="trigger" :show="isVisible" />

        <!-- Tooltip -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 scale-95 translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-1"
        >
            <div
                v-if="isVisible"
                :class="tooltipClasses"
                :style="tooltipStyle"
                role="tooltip"
                :aria-describedby="tooltipId"
            >
                <!-- Arrow -->
                <div :class="arrowClasses"></div>

                <!-- Content -->
                <div class="relative z-10">
                    <div v-if="title" class="font-semibold text-white mb-1">
                        {{ title }}
                    </div>
                    <div class="text-sm text-white/90">
                        <slot>{{ content }}</slot>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";

const props = defineProps({
    content: {
        type: String,
        default: "",
    },
    title: {
        type: String,
        default: "",
    },
    position: {
        type: String,
        default: "top",
        validator: (value) =>
            ["top", "bottom", "left", "right"].includes(value),
    },
    variant: {
        type: String,
        default: "dark",
        validator: (value) =>
            [
                "dark",
                "light",
                "primary",
                "success",
                "warning",
                "error",
            ].includes(value),
    },
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg"].includes(value),
    },
    delay: {
        type: Number,
        default: 300,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["show", "hide"]);

const isVisible = ref(false);
const tooltipId = `tooltip-${Math.random().toString(36).substr(2, 9)}`;
let showTimeout = null;
let hideTimeout = null;

const tooltipClasses = computed(() => {
    const baseClass = "absolute z-50 px-3 py-2 rounded-lg shadow-lg max-w-xs";
    const sizeClasses = {
        sm: "text-xs",
        md: "text-sm",
        lg: "text-base",
    };
    const variantClasses = {
        dark: "bg-gray-900 text-white",
        light: "bg-white text-gray-900 border border-gray-200",
        primary: "bg-primary-600 text-white",
        success: "bg-green-600 text-white",
        warning: "bg-yellow-600 text-white",
        error: "bg-red-600 text-white",
    };
    const positionClasses = {
        top: "bottom-full left-1/2 transform -translate-x-1/2 mb-2",
        bottom: "top-full left-1/2 transform -translate-x-1/2 mt-2",
        left: "right-full top-1/2 transform -translate-y-1/2 mr-2",
        right: "left-full top-1/2 transform -translate-y-1/2 ml-2",
    };

    return `${baseClass} ${sizeClasses[props.size]} ${
        variantClasses[props.variant]
    } ${positionClasses[props.position]}`;
});

const arrowClasses = computed(() => {
    const baseClass = "absolute w-2 h-2 transform rotate-45";
    const variantClasses = {
        dark: "bg-gray-900",
        light: "bg-white border border-gray-200",
        primary: "bg-primary-600",
        success: "bg-green-600",
        warning: "bg-yellow-600",
        error: "bg-red-600",
    };
    const positionClasses = {
        top: "top-full left-1/2 transform -translate-x-1/2 -mt-1",
        bottom: "bottom-full left-1/2 transform -translate-x-1/2 -mb-1",
        left: "left-full top-1/2 transform -translate-y-1/2 -ml-1",
        right: "right-full top-1/2 transform -translate-y-1/2 -mr-1",
    };

    return `${baseClass} ${variantClasses[props.variant]} ${
        positionClasses[props.position]
    }`;
});

const tooltipStyle = computed(() => {
    return {
        "--tw-translate-x":
            props.position === "left" || props.position === "right"
                ? "0"
                : "-50%",
        "--tw-translate-y":
            props.position === "top" || props.position === "bottom"
                ? "0"
                : "-50%",
    };
});

const showTooltip = () => {
    if (props.disabled) return;

    clearTimeout(hideTimeout);
    showTimeout = setTimeout(() => {
        isVisible.value = true;
        emit("show");
    }, props.delay);
};

const hideTooltip = () => {
    clearTimeout(showTimeout);
    hideTimeout = setTimeout(() => {
        isVisible.value = false;
        emit("hide");
    }, 100);
};

onMounted(() => {
    // Handle escape key
    const handleEscape = (e) => {
        if (e.key === "Escape" && isVisible.value) {
            isVisible.value = false;
        }
    };

    document.addEventListener("keydown", handleEscape);

    onUnmounted(() => {
        document.removeEventListener("keydown", handleEscape);
    });
});
</script>
