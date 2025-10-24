<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    placeholder: String,
    error: String,
    required: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    rows: {
        type: Number,
        default: 4
    },
    helper: String,
    size: {
        type: String,
        default: 'md'
    }
});

const emit = defineEmits(['update:modelValue']);

const textareaClasses = computed(() => {
    const sizes = {
        sm: 'px-4 py-2.5 text-sm',
        md: 'px-4 py-3.5 text-sm',
        lg: 'px-6 py-4 text-base'
    };
    
    return [
        'block w-full border-0 rounded-2xl transition-all duration-200 ease-in-out',
        'placeholder-neutral-500 focus:ring-2 focus:ring-offset-0 resize-y',
        'shadow-soft focus:shadow-soft-md',
        props.error 
            ? 'bg-red-50 focus:ring-red-500 focus:bg-white text-red-900' 
            : 'bg-neutral-50 focus:ring-primary-500 focus:bg-white text-neutral-900',
        props.disabled ? 'opacity-50 cursor-not-allowed bg-neutral-100' : '',
        sizes[props.size]
    ];
});
</script>

<template>
    <div class="space-y-3">
        <label v-if="label" class="block text-sm font-semibold text-neutral-700">
            {{ label }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </label>
        
        <textarea
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :rows="rows"
            :class="textareaClasses"
            @input="emit('update:modelValue', $event.target.value)"
        ></textarea>
        
        <p v-if="error" class="text-sm text-red-600 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ error }}
        </p>
        
        <p v-else-if="helper" class="text-sm text-neutral-500">{{ helper }}</p>
    </div>
</template>