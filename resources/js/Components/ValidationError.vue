<template>
    <Transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 transform -translate-y-1"
        enter-to-class="opacity-100 transform translate-y-0"
        leave-active-class="transition-all duration-150 ease-in"
        leave-from-class="opacity-100 transform translate-y-0"
        leave-to-class="opacity-0 transform -translate-y-1"
    >
        <div
            v-if="error"
            class="mt-1 flex items-start gap-2 text-sm text-red-600"
        >
            <ExclamationCircleIcon class="w-4 h-4 flex-shrink-0 mt-0.5" />
            <span>{{ error }}</span>
        </div>
    </Transition>
</template>

<script setup>
import { ExclamationCircleIcon } from '@heroicons/vue/24/outline';

defineProps({
    error: {
        type: String,
        default: null
    }
});
</script>