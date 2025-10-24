<template>
    <div v-if="hasError" :class="containerClass">
        <div class="text-center">
            <!-- Error Icon -->
            <div class="flex justify-center mb-4">
                <div :class="iconClass">
                    <ExclamationTriangleIcon class="w-8 h-8" />
                </div>
            </div>

            <!-- Error Message -->
            <h3 :class="titleClass">
                {{ title }}
            </h3>
            <p :class="descriptionClass">
                {{ description }}
            </p>

            <!-- Error Details (Development Only) -->
            <details v-if="showDetails && error" class="mt-4 text-left">
                <summary
                    class="cursor-pointer text-sm text-neutral-500 hover:text-neutral-700"
                >
                    Technical Details
                </summary>
                <div
                    class="mt-2 p-3 bg-neutral-100 rounded-lg text-xs font-mono text-neutral-600 overflow-auto"
                >
                    <pre>{{ errorDetails }}</pre>
                </div>
            </details>

            <!-- Actions -->
            <div class="mt-6 space-y-3">
                <button
                    @click="retry"
                    :class="retryButtonClass"
                    :disabled="isRetrying"
                >
                    <ArrowPathIcon
                        v-if="isRetrying"
                        class="w-4 h-4 mr-2 animate-spin"
                    />
                    <ArrowPathIcon v-else class="w-4 h-4 mr-2" />
                    {{ isRetrying ? "Retrying..." : "Try Again" }}
                </button>

                <button
                    v-if="showReport"
                    @click="reportError"
                    :class="reportButtonClass"
                >
                    <BugAntIcon class="w-4 h-4 mr-2" />
                    Report Issue
                </button>

                <Link v-if="homeHref" :href="homeHref" :class="homeButtonClass">
                    <HomeIcon class="w-4 h-4 mr-2" />
                    Go Home
                </Link>
            </div>
        </div>
    </div>

    <!-- Fallback Content -->
    <div v-else>
        <slot />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onErrorCaptured } from "vue";
import { Link } from "@inertiajs/vue3";
import {
    ExclamationTriangleIcon,
    ArrowPathIcon,
    BugAntIcon,
    HomeIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    title: {
        type: String,
        default: "Something went wrong",
    },
    description: {
        type: String,
        default:
            "An unexpected error occurred. Please try again or contact support if the problem persists.",
    },
    variant: {
        type: String,
        default: "error",
        validator: (value) => ["error", "warning", "info"].includes(value),
    },
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg"].includes(value),
    },
    showDetails: {
        type: Boolean,
        default: false,
    },
    showReport: {
        type: Boolean,
        default: true,
    },
    homeHref: {
        type: String,
        default: "/",
    },
    onRetry: {
        type: Function,
        default: null,
    },
    onReport: {
        type: Function,
        default: null,
    },
});

const emit = defineEmits(["retry", "report", "error"]);

const hasError = ref(false);
const error = ref(null);
const isRetrying = ref(false);

const containerClass = computed(() => {
    const baseClass = "p-8";
    const sizeClasses = {
        sm: "p-4",
        md: "p-8",
        lg: "p-12",
    };
    return `${baseClass} ${sizeClasses[props.size]}`;
});

const iconClass = computed(() => {
    const baseClass = "w-16 h-16 rounded-full flex items-center justify-center";
    const variantClasses = {
        error: "bg-red-100 text-red-600",
        warning: "bg-yellow-100 text-yellow-600",
        info: "bg-blue-100 text-blue-600",
    };
    return `${baseClass} ${variantClasses[props.variant]}`;
});

const titleClass = computed(() => {
    const sizeClasses = {
        sm: "text-lg",
        md: "text-xl",
        lg: "text-2xl",
    };
    return `${sizeClasses[props.size]} font-bold text-neutral-900 mb-2`;
});

const descriptionClass = computed(() => {
    const sizeClasses = {
        sm: "text-sm",
        md: "text-base",
        lg: "text-lg",
    };
    return `${sizeClasses[props.size]} text-neutral-600`;
});

const retryButtonClass = computed(() => {
    const baseClass =
        "inline-flex items-center px-4 py-2 rounded-lg font-medium transition-colors";
    const variantClasses = {
        error: "bg-red-600 hover:bg-red-700 text-white",
        warning: "bg-yellow-600 hover:bg-yellow-700 text-white",
        info: "bg-blue-600 hover:bg-blue-700 text-white",
    };
    const disabledClass = isRetrying.value
        ? "opacity-50 cursor-not-allowed"
        : "";
    return `${baseClass} ${variantClasses[props.variant]} ${disabledClass}`;
});

const reportButtonClass = computed(() => {
    return "inline-flex items-center px-4 py-2 rounded-lg font-medium bg-neutral-100 hover:bg-neutral-200 text-neutral-700 transition-colors";
});

const homeButtonClass = computed(() => {
    return "inline-flex items-center px-4 py-2 rounded-lg font-medium bg-neutral-600 hover:bg-neutral-700 text-white transition-colors";
});

const errorDetails = computed(() => {
    if (!error.value) return "";
    return JSON.stringify(
        {
            message: error.value.message,
            stack: error.value.stack,
            timestamp: new Date().toISOString(),
        },
        null,
        2
    );
});

const retry = async () => {
    isRetrying.value = true;
    hasError.value = false;
    error.value = null;

    try {
        if (props.onRetry) {
            await props.onRetry();
        }
        emit("retry");
    } catch (err) {
        hasError.value = true;
        error.value = err;
        emit("error", err);
    } finally {
        isRetrying.value = false;
    }
};

const reportError = () => {
    if (props.onReport) {
        props.onReport(error.value);
    }
    emit("report", error.value);
};

// Capture errors from child components
onErrorCaptured((err, instance, info) => {
    hasError.value = true;
    error.value = err;
    emit("error", err);
    return false; // Prevent the error from propagating
});

// Global error handler
onMounted(() => {
    const handleError = (event) => {
        hasError.value = true;
        error.value = event.error;
        emit("error", event.error);
    };

    window.addEventListener("error", handleError);
    window.addEventListener("unhandledrejection", handleError);

    return () => {
        window.removeEventListener("error", handleError);
        window.removeEventListener("unhandledrejection", handleError);
    };
});
</script>
