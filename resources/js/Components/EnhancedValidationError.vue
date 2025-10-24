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
            v-if="error || suggestions.length > 0"
            class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg shadow-sm"
        >
            <!-- Error Message -->
            <div v-if="error" class="flex items-start gap-2 mb-2">
                <ExclamationCircleIcon
                    class="w-4 h-4 flex-shrink-0 mt-0.5 text-red-500"
                />
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-800">{{ error }}</p>
                </div>
            </div>

            <!-- Correction Suggestions -->
            <div v-if="suggestions.length > 0" class="space-y-2">
                <div class="flex items-center gap-2">
                    <LightBulbIcon class="w-4 h-4 text-amber-500" />
                    <span
                        class="text-xs font-medium text-amber-700 uppercase tracking-wide"
                        >Suggestions</span
                    >
                </div>

                <div class="space-y-1">
                    <button
                        v-for="(suggestion, index) in suggestions"
                        :key="index"
                        @click="applySuggestion(suggestion)"
                        class="w-full text-left p-2 bg-white border border-green-200 rounded-md hover:bg-green-50 hover:border-green-300 transition-colors duration-150 group"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <CheckCircleIcon
                                    class="w-3 h-3 text-green-500 opacity-0 group-hover:opacity-100 transition-opacity"
                                />
                                <span
                                    class="text-sm text-green-800 font-medium"
                                    >{{ suggestion.label }}</span
                                >
                            </div>
                            <ArrowRightIcon
                                class="w-3 h-3 text-green-500 opacity-0 group-hover:opacity-100 transition-opacity"
                            />
                        </div>
                        <p
                            v-if="suggestion.description"
                            class="text-xs text-green-600 mt-1 ml-5"
                        >
                            {{ suggestion.description }}
                        </p>
                    </button>
                </div>
            </div>

            <!-- Format Examples -->
            <div
                v-if="formatExamples.length > 0"
                class="mt-3 pt-2 border-t border-red-200"
            >
                <div class="flex items-center gap-2 mb-2">
                    <InformationCircleIcon class="w-4 h-4 text-blue-500" />
                    <span
                        class="text-xs font-medium text-blue-700 uppercase tracking-wide"
                        >Expected Format</span
                    >
                </div>
                <div class="space-y-1">
                    <div
                        v-for="(example, index) in formatExamples"
                        :key="index"
                        class="flex items-center gap-2 text-xs"
                    >
                        <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                        <code
                            class="px-2 py-1 bg-blue-100 text-blue-800 rounded font-mono"
                            >{{ example.format }}</code
                        >
                        <span class="text-blue-600">{{
                            example.description
                        }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div
                v-if="quickActions.length > 0"
                class="mt-3 pt-2 border-t border-red-200"
            >
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="(action, index) in quickActions"
                        :key="index"
                        @click="executeQuickAction(action)"
                        class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-purple-700 bg-purple-100 border border-purple-200 rounded-md hover:bg-purple-200 transition-colors duration-150"
                    >
                        <component :is="action.icon" class="w-3 h-3" />
                        {{ action.label }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { computed } from "vue";
import {
    ExclamationCircleIcon,
    LightBulbIcon,
    CheckCircleIcon,
    ArrowRightIcon,
    InformationCircleIcon,
    SparklesIcon,
    ClipboardDocumentIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    error: {
        type: String,
        default: null,
    },
    fieldType: {
        type: String,
        default: "text",
    },
    fieldValue: {
        type: [String, Number],
        default: "",
    },
    fieldName: {
        type: String,
        default: "",
    },
    customSuggestions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["apply-suggestion", "quick-action"]);

// Generate smart suggestions based on error type and field
const suggestions = computed(() => {
    if (!props.error) return props.customSuggestions;

    const errorLower = props.error.toLowerCase();
    const value = String(props.fieldValue || "").trim();
    const suggestions = [...props.customSuggestions];

    // Email validation suggestions
    if (props.fieldType === "email" || errorLower.includes("email")) {
        if (value && !value.includes("@")) {
            suggestions.push({
                value: value + "@gmail.com",
                label: `Add "@gmail.com" → ${value}@gmail.com`,
                description: "Complete the email address",
            });
        }
        if (value && value.includes("@") && !value.includes(".")) {
            const parts = value.split("@");
            suggestions.push({
                value: parts[0] + "@" + parts[1] + ".com",
                label: `Add ".com" → ${parts[0]}@${parts[1]}.com`,
                description: "Complete the domain",
            });
        }
        if (value && value.includes(" ")) {
            suggestions.push({
                value: value.replace(/\s+/g, ""),
                label: `Remove spaces → ${value.replace(/\s+/g, "")}`,
                description: "Email addresses cannot contain spaces",
            });
        }
    }

    // Phone number suggestions
    if (props.fieldType === "tel" || errorLower.includes("phone")) {
        if (value && !/^\+/.test(value) && value.length >= 10) {
            suggestions.push({
                value: "+63" + value.replace(/\D/g, ""),
                label: `Add country code → +63${value.replace(/\D/g, "")}`,
                description: "Philippine phone number format",
            });
        }
        if (value && /[^\d\+\-\s\(\)]/.test(value)) {
            const cleaned = value.replace(/[^\d\+]/g, "");
            suggestions.push({
                value: cleaned,
                label: `Remove invalid characters → ${cleaned}`,
                description: "Keep only numbers and +",
            });
        }
    }

    // Required field suggestions
    if (errorLower.includes("required") && !value) {
        const fieldSuggestions = getFieldSuggestions(props.fieldName);
        suggestions.push(...fieldSuggestions);
    }

    // Number validation suggestions
    if (props.fieldType === "number" || errorLower.includes("number")) {
        if (value && !/^\d+(\.\d+)?$/.test(value)) {
            const numericValue = value.replace(/[^\d.]/g, "");
            if (numericValue) {
                suggestions.push({
                    value: numericValue,
                    label: `Extract numbers → ${numericValue}`,
                    description: "Remove non-numeric characters",
                });
            }
        }
    }

    return suggestions.slice(0, 3); // Limit to 3 suggestions
});

// Format examples based on field type
const formatExamples = computed(() => {
    const examples = [];

    if (
        props.fieldType === "email" ||
        props.error?.toLowerCase().includes("email")
    ) {
        examples.push(
            {
                format: "user@example.com",
                description: "Standard email format",
            },
            {
                format: "name.surname@domain.co.uk",
                description: "With subdomain",
            }
        );
    }

    if (
        props.fieldType === "tel" ||
        props.error?.toLowerCase().includes("phone")
    ) {
        examples.push(
            { format: "+639123456789", description: "Philippine mobile" },
            { format: "+1234567890", description: "International format" }
        );
    }

    if (
        props.fieldType === "number" ||
        props.error?.toLowerCase().includes("price")
    ) {
        examples.push(
            { format: "1500000", description: "Whole number" },
            { format: "1500000.50", description: "With decimals" }
        );
    }

    return examples;
});

// Quick action buttons
const quickActions = computed(() => {
    const actions = [];
    const value = String(props.fieldValue || "");

    if (value && value !== value.trim()) {
        actions.push({
            label: "Trim spaces",
            icon: SparklesIcon,
            action: "trim",
            value: value.trim(),
        });
    }

    if (props.fieldType === "email" && value) {
        actions.push({
            label: "Lowercase",
            icon: SparklesIcon,
            action: "lowercase",
            value: value.toLowerCase(),
        });
    }

    if (value) {
        actions.push({
            label: "Clear field",
            icon: ClipboardDocumentIcon,
            action: "clear",
            value: "",
        });
    }

    return actions;
});

// Helper function to get field-specific suggestions
const getFieldSuggestions = (fieldName) => {
    const suggestions = [];

    switch (fieldName) {
        case "name":
            suggestions.push({
                value: "John Doe",
                label: "Example: John Doe",
                description: "First and last name",
            });
            break;
        case "property_title":
            suggestions.push({
                value: "Beautiful 3BR House in Tagbilaran",
                label: "Example: Beautiful 3BR House in Tagbilaran",
                description: "Descriptive property title",
            });
            break;
        case "city":
            suggestions.push(
                {
                    value: "Tagbilaran City",
                    label: "Example: Tagbilaran City",
                    description: "Major city in Bohol",
                },
                {
                    value: "Panglao",
                    label: "Example: Panglao",
                    description: "Popular tourist destination",
                }
            );
            break;
    }

    return suggestions;
};

// Event handlers
const applySuggestion = (suggestion) => {
    emit("apply-suggestion", {
        value: suggestion.value,
        suggestion: suggestion,
    });
};

const executeQuickAction = (action) => {
    emit("quick-action", {
        action: action.action,
        value: action.value,
    });
};
</script>

<style scoped>
/* Custom animations for better UX */
.group:hover .opacity-0 {
    opacity: 1;
}
</style>
