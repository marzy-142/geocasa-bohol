<template>
    <div ref="containerRef" :class="containerClass" :style="containerStyle">
        <!-- Loading Skeleton -->
        <div
            v-if="isLoading"
            :class="skeletonClass"
            class="animate-pulse bg-neutral-200 rounded-lg"
        >
            <div class="w-full h-full flex items-center justify-center">
                <div
                    class="w-8 h-8 border-2 border-neutral-300 border-t-transparent rounded-full animate-spin"
                ></div>
            </div>
        </div>

        <!-- Error State -->
        <div
            v-else-if="hasError"
            :class="imageClass"
            class="bg-neutral-100 flex items-center justify-center"
        >
            <div class="text-center p-4">
                <div class="w-8 h-8 mx-auto mb-2 text-neutral-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                </div>
                <p class="text-xs text-neutral-500">Failed to load</p>
            </div>
        </div>

        <!-- Actual Image -->
        <img
            v-else
            ref="imageRef"
            :src="imageSrc"
            :srcset="srcset"
            :sizes="sizes"
            :alt="alt"
            :class="imageClass"
            :loading="lazy ? 'lazy' : 'eager'"
            :decoding="lazy ? 'async' : 'sync'"
            @load="handleLoad"
            @error="handleError"
            @click="$emit('click', $event)"
        />

        <!-- Overlay Content -->
        <div v-if="$slots.overlay" class="absolute inset-0 z-10">
            <slot name="overlay" />
        </div>

        <!-- Badge -->
        <div v-if="badge" class="absolute top-2 left-2 z-20">
            <span :class="badgeClass">
                {{ badge }}
            </span>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";

const props = defineProps({
    src: {
        type: String,
        required: true,
    },
    alt: {
        type: String,
        default: "",
    },
    lazy: {
        type: Boolean,
        default: true,
    },
    aspectRatio: {
        type: String,
        default: "16/9",
        validator: (value) => /^\d+\/\d+$/.test(value),
    },
    objectFit: {
        type: String,
        default: "cover",
        validator: (value) =>
            ["cover", "contain", "fill", "scale-down", "none"].includes(value),
    },
    rounded: {
        type: String,
        default: "lg",
        validator: (value) =>
            ["none", "sm", "md", "lg", "xl", "2xl", "3xl", "full"].includes(
                value
            ),
    },
    shadow: {
        type: Boolean,
        default: false,
    },
    hover: {
        type: Boolean,
        default: false,
    },
    badge: {
        type: String,
        default: null,
    },
    badgeVariant: {
        type: String,
        default: "primary",
        validator: (value) =>
            [
                "primary",
                "secondary",
                "success",
                "warning",
                "error",
                "info",
            ].includes(value),
    },
    className: {
        type: String,
        default: "",
    },
    srcset: {
        type: String,
        default: "",
    },
    sizes: {
        type: String,
        default: "(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw",
    },
});

const emit = defineEmits(["load", "error", "click"]);

const isLoading = ref(true);
const hasError = ref(false);
const isIntersecting = ref(false);

const containerClass = computed(() => {
    const baseClass = "relative overflow-hidden";
    const roundedClass =
        props.rounded !== "none" ? `rounded-${props.rounded}` : "";
    const shadowClass = props.shadow ? "shadow-lg" : "";
    const hoverClass = props.hover
        ? "hover:scale-105 transition-transform duration-300"
        : "";
    return `${baseClass} ${roundedClass} ${shadowClass} ${hoverClass} ${props.className}`;
});

const containerStyle = computed(() => {
    const [width, height] = props.aspectRatio.split("/");
    const paddingBottom = (height / width) * 100;
    return {
        "padding-bottom": `${paddingBottom}%`,
    };
});

const imageClass = computed(() => {
    const baseClass = "absolute inset-0 w-full h-full";
    const objectFitClass = `object-${props.objectFit}`;
    const roundedClass =
        props.rounded !== "none" ? `rounded-${props.rounded}` : "";
    return `${baseClass} ${objectFitClass} ${roundedClass}`;
});

const imageSrc = computed(() => {
    if (!isIntersecting.value && props.lazy) {
        // Use a simple 1x1 transparent pixel instead of base64 SVG
        return "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
    }
    return props.src;
});

const skeletonClass = computed(() => {
    return `absolute inset-0 w-full h-full`;
});

const badgeClass = computed(() => {
    const baseClass = "px-2 py-1 text-xs font-semibold rounded-full";
    const variantClasses = {
        primary: "bg-primary-500 text-white",
        secondary: "bg-neutral-500 text-white",
        success: "bg-green-500 text-white",
        warning: "bg-yellow-500 text-white",
        error: "bg-red-500 text-white",
        info: "bg-blue-500 text-white",
    };
    return `${baseClass} ${variantClasses[props.badgeVariant]}`;
});

const handleLoad = () => {
    isLoading.value = false;
    hasError.value = false;
    emit("load");
};

const handleError = () => {
    isLoading.value = false;
    hasError.value = true;
    emit("error");
};

// Intersection Observer for lazy loading
let observer = null;
const imageRef = ref(null);
const containerRef = ref(null);

onMounted(() => {
    if (props.lazy) {
        // Observe the container instead of the image
        nextTick(() => {
            const elementToObserve = containerRef.value || imageRef.value;

            if (elementToObserve) {
                observer = new IntersectionObserver(
                    (entries) => {
                        entries.forEach((entry) => {
                            if (entry.isIntersecting) {
                                isIntersecting.value = true;
                                if (observer) {
                                    observer.unobserve(entry.target);
                                }
                            }
                        });
                    },
                    {
                        rootMargin: "100px", // Increased margin for earlier loading
                        threshold: 0.01,
                    }
                );
                observer.observe(elementToObserve);
            } else {
                // Fallback if element is not found
                isIntersecting.value = true;
            }
        });
    } else {
        isIntersecting.value = true;
    }
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
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
