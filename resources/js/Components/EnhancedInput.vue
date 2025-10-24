<script setup>
import { ref, computed } from "vue";
import ModernInput from "./ModernInput.vue";
import ValidationHelper from "./ValidationHelper.vue";

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    type: {
        type: String,
        default: "text",
    },
    placeholder: String,
    error: String,
    helper: String,
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    icon: [Object, Function],
    size: {
        type: String,
        default: "md",
    },
    validationRules: {
        type: Array,
        default: () => []
    },
    showValidation: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(["update:modelValue", "validation-change"]);

const internalValue = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit("update:modelValue", value);
    }
});

const handleValidationResult = (result) => {
    emit("validation-change", result);
};

// Get validation rules based on field type and requirements
const getValidationRules = () => {
    const rules = [...props.validationRules];
    
    if (props.required) {
        rules.push(ValidationHelper.commonRules.required);
    }
    
    if (props.type === 'email') {
        rules.push(ValidationHelper.commonRules.email);
    }
    
    if (props.type === 'password') {
        rules.push(ValidationHelper.commonRules.passwordStrength);
    }
    
    if (props.type === 'tel') {
        rules.push(ValidationHelper.commonRules.phoneFormat);
    }
    
    return rules;
};
</script>

<template>
    <div class="space-y-3">
        <!-- Enhanced Input -->
        <ModernInput
            v-model="internalValue"
            :type="type"
            :label="label"
            :placeholder="placeholder"
            :error="error"
            :required="required"
            :disabled="disabled"
            :icon="icon"
            :size="size"
        />

        <!-- Validation Helper -->
        <ValidationHelper
            v-if="showValidation"
            :value="internalValue"
            :rules="getValidationRules()"
            :field="label"
            @validation-result="handleValidationResult"
        />

        <!-- Helper Text (only show if no validation messages) -->
        <p v-if="helper && !error && showValidation" class="text-sm text-neutral-500">
            {{ helper }}
        </p>
    </div>
</template>


