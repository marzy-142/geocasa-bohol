<template>
    <select
        :id="id"
        ref="input"
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
        v-bind="$attrs"
    >
        <slot />
    </select>
</template>

<script setup>
import { onMounted, ref } from "vue";

defineProps({
    id: {
        type: String,
        default: () => `select-${Math.random().toString(36).substr(2, 9)}`,
    },
    modelValue: {
        type: [String, Number],
        default: "",
    },
});

defineEmits(["update:modelValue"]);

const input = ref(null);

defineExpose({ focus: () => input.value.focus() });

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});
</script>
