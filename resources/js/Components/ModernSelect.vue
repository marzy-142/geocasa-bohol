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
    options: {
        type: Array,
        default: () => []
    },
    helper: String,
    size: {
        type: String,
        default: 'md'
    }
});

const emit = defineEmits(['update:modelValue']);

const selectClasses = computed(() => {
    const sizes = {
        sm: 'px-4 py-2.5 text-sm',
        md: 'px-4 py-3.5 text-sm',
        lg: 'px-6 py-4 text-base'
    };
    
    return [
        'block w-full border-0 rounded-2xl transition-all duration-200 ease-in-out',
        'focus:ring-2 focus:ring-offset-0 appearance-none',
        'shadow-soft focus:shadow-soft-md',
        'bg-no-repeat bg-right bg-[length:16px_16px] pr-10',
        'bg-[url("data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'m6 8 4 4 4-4\'/%3e%3c/svg%3e")]',
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
        
        <select
            :value="modelValue"
            :disabled="disabled"
            :class="selectClasses"
            @change="emit('update:modelValue', $event.target.value)"
        >
            <option v-if="placeholder" value="" disabled>
                {{ placeholder }}
            </option>
            <option 
                v-for="option in options" 
                :key="option.value || option"
                :value="option.value || option"
            >
                {{ option.label || option }}
            </option>
        </select>
        
        <p v-if="error" class="text-sm text-red-600 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ error }}
        </p>
        
        <p v-else-if="helper" class="text-sm text-neutral-500">{{ helper }}</p>
    </div>
</template>