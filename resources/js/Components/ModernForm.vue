<script setup>
const props = defineProps({
    title: String,
    subtitle: String,
    loading: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <!-- Form Header -->
        <div v-if="title || subtitle" class="px-6 py-5 border-b border-gray-50">
            <h3 v-if="title" class="text-lg font-semibold text-gray-900">{{ title }}</h3>
            <p v-if="subtitle" class="text-sm text-gray-600 mt-1">{{ subtitle }}</p>
        </div>
        
        <!-- Form Content -->
        <div class="p-6">
            <slot />
        </div>
        
        <!-- Form Actions -->
        <div v-if="$slots.actions" class="px-6 py-4 bg-gray-50/50 border-t border-gray-50">
            <slot name="actions" />
        </div>
    </div>
</template>