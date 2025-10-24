<template>
    <div :class="containerClass">
        <div class="text-center py-12 px-4">
            <!-- Icon -->
            <div class="mb-6">
                <component
                    :is="icon"
                    :class="iconClass"
                    class="w-16 h-16 mx-auto"
                />
            </div>

            <!-- Title -->
            <h3 class="text-xl font-semibold text-neutral-900 mb-2">
                {{ title }}
            </h3>

            <!-- Description -->
            <p class="text-neutral-600 mb-6 max-w-md mx-auto">
                {{ description }}
            </p>

            <!-- Actions -->
            <div class="flex items-center justify-center gap-3">
                <button
                    v-if="showRetry"
                    @click="$emit('retry')"
                    class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center gap-2"
                >
                    <ArrowPathIcon class="w-5 h-5" />
                    {{ retryText }}
                </button>

                <Link
                    v-if="homeLink"
                    :href="route('client.dashboard')"
                    class="bg-neutral-100 text-neutral-700 px-6 py-3 rounded-lg font-medium hover:bg-neutral-200 transition-colors"
                >
                    Go to Dashboard
                </Link>

                <slot name="actions"></slot>
            </div>

            <!-- Additional Info -->
            <div v-if="$slots.info" class="mt-6 text-sm text-neutral-500">
                <slot name="info"></slot>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    ExclamationTriangleIcon,
    WifiIcon,
    ServerIcon,
    DocumentMagnifyingGlassIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    type: {
        type: String,
        default: 'error',
        validator: (value) => ['error', 'network', '404', '500', 'empty'].includes(value),
    },
    title: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
    showRetry: {
        type: Boolean,
        default: true,
    },
    retryText: {
        type: String,
        default: 'Try Again',
    },
    homeLink: {
        type: Boolean,
        default: false,
    },
    bordered: {
        type: Boolean,
        default: true,
    },
});

defineEmits(['retry']);

const icon = computed(() => {
    const icons = {
        error: ExclamationTriangleIcon,
        network: WifiIcon,
        '404': DocumentMagnifyingGlassIcon,
        '500': ServerIcon,
        empty: ExclamationTriangleIcon,
    };
    return icons[props.type] || ExclamationTriangleIcon;
});

const iconClass = computed(() => {
    const classes = {
        error: 'text-red-500',
        network: 'text-orange-500',
        '404': 'text-blue-500',
        '500': 'text-red-500',
        empty: 'text-neutral-400',
    };
    return classes[props.type] || 'text-neutral-400';
});

const containerClass = computed(() => {
    return [
        'bg-white rounded-lg',
        props.bordered ? 'border border-neutral-200' : '',
    ].filter(Boolean).join(' ');
});

// Default messages
const defaultMessages = {
    error: {
        title: 'Something went wrong',
        description: 'An unexpected error occurred. Please try again.',
    },
    network: {
        title: 'Connection Error',
        description: 'Unable to connect to the server. Please check your internet connection and try again.',
    },
    '404': {
        title: 'Page Not Found',
        description: 'The page you\'re looking for doesn\'t exist or has been moved.',
    },
    '500': {
        title: 'Server Error',
        description: 'Our servers are experiencing issues. Please try again in a few moments.',
    },
    empty: {
        title: 'No Data',
        description: 'There\'s nothing here yet.',
    },
};

// Use default messages if not provided
const title = computed(() => props.title || defaultMessages[props.type]?.title || defaultMessages.error.title);
const description = computed(() => props.description || defaultMessages[props.type]?.description || defaultMessages.error.description);
</script>
