<template>
    <div
        v-if="showMessage"
        class="fixed top-4 right-4 z-50 max-w-sm w-full"
        :class="messageClasses"
    >
        <div class="flex items-start space-x-3 p-4 rounded-lg shadow-lg">
            <div class="flex-shrink-0">
                <CheckCircleIcon
                    v-if="type === 'success'"
                    class="h-5 w-5"
                    aria-hidden="true"
                />
                <ExclamationTriangleIcon
                    v-else-if="type === 'warning'"
                    class="h-5 w-5"
                    aria-hidden="true"
                />
                <XCircleIcon
                    v-else-if="type === 'error'"
                    class="h-5 w-5"
                    aria-hidden="true"
                />
                <InformationCircleIcon
                    v-else
                    class="h-5 w-5"
                    aria-hidden="true"
                />
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium">{{ message }}</p>
            </div>
            <div class="flex-shrink-0">
                <button
                    @click="closeMessage"
                    class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2"
                    :class="closeButtonClasses"
                >
                    <span class="sr-only">Close</span>
                    <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import {
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XCircleIcon,
    InformationCircleIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";

const page = usePage();
const showMessage = ref(false);

// Get flash message from page props
const flashMessage = computed(() => {
    return (
        page.props.flash?.success ||
        page.props.flash?.error ||
        page.props.flash?.warning ||
        page.props.flash?.info
    );
});

const message = computed(() => {
    return flashMessage.value;
});

const type = computed(() => {
    if (page.props.flash?.success) return "success";
    if (page.props.flash?.error) return "error";
    if (page.props.flash?.warning) return "warning";
    if (page.props.flash?.info) return "info";
    return "info";
});

const messageClasses = computed(() => {
    const baseClasses = "transition-all duration-300 ease-in-out transform";

    switch (type.value) {
        case "success":
            return `${baseClasses} bg-green-50 border border-green-200 text-green-800`;
        case "error":
            return `${baseClasses} bg-red-50 border border-red-200 text-red-800`;
        case "warning":
            return `${baseClasses} bg-yellow-50 border border-yellow-200 text-yellow-800`;
        case "info":
        default:
            return `${baseClasses} bg-blue-50 border border-blue-200 text-blue-800`;
    }
});

const closeButtonClasses = computed(() => {
    switch (type.value) {
        case "success":
            return "text-green-400 hover:text-green-500 focus:ring-green-500";
        case "error":
            return "text-red-400 hover:text-red-500 focus:ring-red-500";
        case "warning":
            return "text-yellow-400 hover:text-yellow-500 focus:ring-yellow-500";
        case "info":
        default:
            return "text-blue-400 hover:text-blue-500 focus:ring-blue-500";
    }
});

const closeMessage = () => {
    showMessage.value = false;
};

onMounted(() => {
    // Show message if there's a flash message
    if (message.value) {
        showMessage.value = true;

        // Auto-hide after 5 seconds
        setTimeout(() => {
            showMessage.value = false;
        }, 5000);
    }
});

// Watch for new flash messages
import { watch } from "vue";
watch(
    () => page.props.flash,
    (newFlash) => {
        if (
            newFlash?.success ||
            newFlash?.error ||
            newFlash?.warning ||
            newFlash?.info
        ) {
            showMessage.value = true;

            // Auto-hide after 5 seconds
            setTimeout(() => {
                showMessage.value = false;
            }, 5000);
        }
    },
    { deep: true }
);
</script>
