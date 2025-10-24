<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 transform -translate-y-2 scale-95"
        enter-to-class="opacity-100 transform translate-y-0 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 transform translate-y-0 scale-100"
        leave-to-class="opacity-0 transform -translate-y-2 scale-95"
    >
        <div
            v-if="hasErrors"
            class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg"
            role="alert"
            aria-live="polite"
        >
            <div class="flex items-start gap-3">
                <ExclamationTriangleIcon class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-red-800 mb-2">
                        {{ title }}
                    </h3>
                    <p v-if="description" class="text-sm text-red-700 mb-3">
                        {{ description }}
                    </p>
                    
                    <!-- Error List -->
                    <ul class="space-y-2">
                        <li
                            v-for="(error, field) in processedErrors"
                            :key="field"
                            class="flex items-start gap-2 text-sm"
                        >
                            <span class="w-1.5 h-1.5 bg-red-600 rounded-full mt-2 flex-shrink-0"></span>
                            <div class="flex-1">
                                <button
                                    v-if="enableNavigation"
                                    type="button"
                                    class="text-red-700 hover:text-red-900 underline text-left"
                                    @click="scrollToField(field)"
                                >
                                    <strong>{{ formatFieldName(field) }}:</strong>
                                    {{ Array.isArray(error) ? error[0] : error }}
                                </button>
                                <span v-else class="text-red-700">
                                    <strong>{{ formatFieldName(field) }}:</strong>
                                    {{ Array.isArray(error) ? error[0] : error }}
                                </span>
                            </div>
                        </li>
                    </ul>

                    <!-- Quick Actions -->
                    <div v-if="showQuickActions" class="mt-4 flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 rounded-md transition-colors duration-200"
                            @click="scrollToFirstError"
                        >
                            <ArrowUpIcon class="w-3 h-3" />
                            Go to first error
                        </button>
                        <button
                            v-if="enableAutoFix"
                            type="button"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-md transition-colors duration-200"
                            @click="attemptAutoFix"
                        >
                            <WrenchScrewdriverIcon class="w-3 h-3" />
                            Auto-fix common issues
                        </button>
                    </div>
                </div>

                <!-- Dismiss Button -->
                <button
                    v-if="dismissible"
                    type="button"
                    class="text-red-400 hover:text-red-600 transition-colors duration-200"
                    @click="$emit('dismiss')"
                >
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { computed } from 'vue';
import {
    ExclamationTriangleIcon,
    XMarkIcon,
    ArrowUpIcon,
    WrenchScrewdriverIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({})
    },
    title: {
        type: String,
        default: 'Please fix the following errors:'
    },
    description: {
        type: String,
        default: null
    },
    enableNavigation: {
        type: Boolean,
        default: true
    },
    showQuickActions: {
        type: Boolean,
        default: true
    },
    enableAutoFix: {
        type: Boolean,
        default: false
    },
    dismissible: {
        type: Boolean,
        default: false
    },
    maxErrors: {
        type: Number,
        default: 10
    }
});

const emit = defineEmits(['dismiss', 'navigate-to-field', 'auto-fix']);

// Computed properties
const hasErrors = computed(() => {
    return Object.keys(props.errors).length > 0;
});

const processedErrors = computed(() => {
    const errors = { ...props.errors };
    const errorKeys = Object.keys(errors);
    
    // Limit the number of errors shown
    if (errorKeys.length > props.maxErrors) {
        const limitedErrors = {};
        errorKeys.slice(0, props.maxErrors).forEach(key => {
            limitedErrors[key] = errors[key];
        });
        limitedErrors['...'] = `And ${errorKeys.length - props.maxErrors} more errors`;
        return limitedErrors;
    }
    
    return errors;
});

// Methods
const formatFieldName = (field) => {
    // Convert snake_case and camelCase to Title Case
    return field
        .replace(/[_-]/g, ' ')
        .replace(/([a-z])([A-Z])/g, '$1 $2')
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
};

const scrollToField = (field) => {
    emit('navigate-to-field', field);
    
    // Try to find and focus the field
    const fieldElement = document.querySelector(`[name="${field}"], #${field}, [data-field="${field}"]`);
    if (fieldElement) {
        fieldElement.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
        
        // Focus the field after scrolling
        setTimeout(() => {
            fieldElement.focus();
        }, 300);
    }
};

const scrollToFirstError = () => {
    const firstField = Object.keys(props.errors)[0];
    if (firstField) {
        scrollToField(firstField);
    }
};

const attemptAutoFix = () => {
    emit('auto-fix', props.errors);
};
</script>