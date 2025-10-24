<template>
    <Transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isVisible"
            :class="notificationClass"
            role="alert"
            :aria-live="type === 'error' ? 'assertive' : 'polite'"
        >
            <div class="flex items-start">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <component :is="iconComponent" :class="iconClass" />
                </div>

                <!-- Content -->
                <div class="ml-3 w-0 flex-1">
                    <p v-if="title" :class="titleClass">
                        {{ title }}
                    </p>
                    <p :class="messageClass">
                        {{ message }}
                    </p>

                    <!-- Actions -->
                    <div
                        v-if="actions && actions.length > 0"
                        class="mt-3 space-x-3"
                    >
                        <button
                            v-for="(action, index) in actions"
                            :key="index"
                            @click="handleAction(action)"
                            :class="getActionClass(action.variant)"
                        >
                            {{ action.text }}
                        </button>
                    </div>
                </div>

                <!-- Close Button -->
                <div class="ml-4 flex-shrink-0 flex">
                    <button
                        @click="dismiss"
                        :class="closeButtonClass"
                        :aria-label="'Dismiss notification'"
                    >
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Progress Bar -->
            <div v-if="showProgress" class="mt-2">
                <div class="w-full bg-white bg-opacity-20 rounded-full h-1">
                    <div
                        :class="progressBarClass"
                        :style="{ width: progressWidth + '%' }"
                    ></div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    XCircleIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        default: "info",
        validator: (value) =>
            ["success", "error", "warning", "info"].includes(value),
    },
    title: {
        type: String,
        default: null,
    },
    message: {
        type: String,
        required: true,
    },
    duration: {
        type: Number,
        default: 5000,
    },
    persistent: {
        type: Boolean,
        default: false,
    },
    actions: {
        type: Array,
        default: () => [],
    },
    position: {
        type: String,
        default: "top-right",
        validator: (value) =>
            [
                "top-right",
                "top-left",
                "bottom-right",
                "bottom-left",
                "top-center",
                "bottom-center",
            ].includes(value),
    },
});

const emit = defineEmits(["dismiss", "action"]);

const isVisible = ref(true);
const progressWidth = ref(100);
const showProgress = ref(!props.persistent && props.duration > 0);

let progressInterval = null;
let dismissTimeout = null;

const iconComponent = computed(() => {
    const icons = {
        success: CheckCircleIcon,
        error: XCircleIcon,
        warning: ExclamationTriangleIcon,
        info: InformationCircleIcon,
    };
    return icons[props.type];
});

const notificationClass = computed(() => {
    const baseClass =
        "max-w-sm w-full shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden";
    const typeClasses = {
        success: "bg-green-50 border border-green-200",
        error: "bg-red-50 border border-red-200",
        warning: "bg-yellow-50 border border-yellow-200",
        info: "bg-blue-50 border border-blue-200",
    };
    return `${baseClass} ${typeClasses[props.type]}`;
});

const iconClass = computed(() => {
    const baseClass = "h-5 w-5";
    const typeClasses = {
        success: "text-green-400",
        error: "text-red-400",
        warning: "text-yellow-400",
        info: "text-blue-400",
    };
    return `${baseClass} ${typeClasses[props.type]}`;
});

const titleClass = computed(() => {
    const baseClass = "text-sm font-medium";
    const typeClasses = {
        success: "text-green-800",
        error: "text-red-800",
        warning: "text-yellow-800",
        info: "text-blue-800",
    };
    return `${baseClass} ${typeClasses[props.type]}`;
});

const messageClass = computed(() => {
    const baseClass = "text-sm";
    const typeClasses = {
        success: "text-green-700",
        error: "text-red-700",
        warning: "text-yellow-700",
        info: "text-blue-700",
    };
    return `${baseClass} ${typeClasses[props.type]}`;
});

const closeButtonClass = computed(() => {
    const baseClass =
        "bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2";
    const typeClasses = {
        success: "focus:ring-green-500",
        error: "focus:ring-red-500",
        warning: "focus:ring-yellow-500",
        info: "focus:ring-blue-500",
    };
    return `${baseClass} ${typeClasses[props.type]}`;
});

const progressBarClass = computed(() => {
    const typeClasses = {
        success: "bg-green-500",
        error: "bg-red-500",
        warning: "bg-yellow-500",
        info: "bg-blue-500",
    };
    return `h-1 rounded-full transition-all duration-100 ${
        typeClasses[props.type]
    }`;
});

const getActionClass = (variant = "primary") => {
    const baseClass =
        "text-sm font-medium underline hover:no-underline focus:outline-none";
    const variantClasses = {
        primary: "text-blue-600 hover:text-blue-500",
        secondary: "text-gray-600 hover:text-gray-500",
        danger: "text-red-600 hover:text-red-500",
    };
    return `${baseClass} ${variantClasses[variant]}`;
};

const handleAction = (action) => {
    if (action.handler) {
        action.handler();
    }
    emit("action", action);
};

const dismiss = () => {
    isVisible.value = false;
    emit("dismiss", props.id);
};

const startProgress = () => {
    if (props.persistent || props.duration <= 0) return;

    const interval = 50; // Update every 50ms
    const totalSteps = props.duration / interval;
    const stepSize = 100 / totalSteps;

    progressInterval = setInterval(() => {
        progressWidth.value -= stepSize;
        if (progressWidth.value <= 0) {
            dismiss();
        }
    }, interval);
};

const stopProgress = () => {
    if (progressInterval) {
        clearInterval(progressInterval);
        progressInterval = null;
    }
};

onMounted(() => {
    if (!props.persistent && props.duration > 0) {
        startProgress();
    }
});

onUnmounted(() => {
    stopProgress();
    if (dismissTimeout) {
        clearTimeout(dismissTimeout);
    }
});
</script>
