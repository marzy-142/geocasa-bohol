<template>
    <div class="property-type-multi-select">
        <label
            v-if="label"
            class="block text-sm font-medium text-gray-700 mb-2"
        >
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <!-- Selected Types Display -->
        <div v-if="selectedTypes.length > 0" class="flex flex-wrap gap-2 mb-3">
            <span
                v-for="type in selectedTypes"
                :key="type"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200"
            >
                {{ formatType(type) }}
                <button
                    type="button"
                    @click="removeType(type)"
                    class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <svg
                        class="w-3 h-3"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </span>
        </div>

        <!-- Dropdown Container -->
        <div class="relative">
            <button
                type="button"
                @click="toggleDropdown"
                class="w-full border border-gray-300 rounded-lg px-4 py-3 text-left focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white hover:bg-gray-50 transition-colors"
                :class="{
                    'border-red-500 ring-red-500': error,
                    'border-gray-300': !error,
                }"
            >
                <span v-if="selectedTypes.length === 0" class="text-gray-500">
                    {{ placeholder }}
                </span>
                <span v-else class="text-gray-700">
                    {{ selectedTypes.length }} type{{
                        selectedTypes.length > 1 ? "s" : ""
                    }}
                    selected
                </span>
                <svg
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 transition-transform"
                    :class="{ 'rotate-180': isOpen }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 9l-7 7-7-7"
                    />
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div
                    v-if="isOpen"
                    class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto"
                >
                    <div class="p-2">
                        <label
                            v-for="type in availableTypes"
                            :key="type.value"
                            class="flex items-center px-3 py-2 hover:bg-gray-50 rounded-md cursor-pointer transition-colors"
                        >
                            <input
                                type="checkbox"
                                :value="type.value"
                                :checked="isSelected(type.value)"
                                @change="toggleType(type.value)"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <span class="ml-3 text-sm text-gray-700">
                                {{ type.label }}
                            </span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div
                        class="border-t border-gray-200 p-2 flex justify-between items-center bg-gray-50"
                    >
                        <button
                            type="button"
                            @click="clearAll"
                            class="text-sm text-gray-600 hover:text-gray-800 px-3 py-1 rounded hover:bg-gray-100"
                        >
                            Clear All
                        </button>
                        <button
                            type="button"
                            @click="closeDropdown"
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium px-3 py-1 rounded hover:bg-blue-50"
                        >
                            Done
                        </button>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="text-red-500 text-sm mt-1">
            {{ error }}
        </div>

        <!-- Help Text -->
        <div v-if="helpText" class="text-gray-500 text-sm mt-1">
            {{ helpText }}
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    availableTypes: {
        type: Array,
        required: true,
    },
    label: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "Select property types",
    },
    error: {
        type: String,
        default: "",
    },
    helpText: {
        type: String,
        default:
            "Select one or more property types. Mixed-use properties can have multiple types.",
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);

// Ensure selectedTypes only contains strings
const normalizeTypes = (types) => {
    if (!Array.isArray(types)) return [];
    return types.map((t) =>
        typeof t === "string" ? t : t?.value || String(t)
    );
};

const selectedTypes = ref(normalizeTypes(props.modelValue));

// Watch for external changes to modelValue
watch(
    () => props.modelValue,
    (newValue) => {
        selectedTypes.value = normalizeTypes(newValue);
    }
);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
    isOpen.value = false;
};

const isSelected = (type) => {
    return selectedTypes.value.includes(type);
};

const toggleType = (type) => {
    if (isSelected(type)) {
        selectedTypes.value = selectedTypes.value.filter((t) => t !== type);
    } else {
        selectedTypes.value.push(type);
    }
    emit("update:modelValue", selectedTypes.value);
};

const removeType = (type) => {
    selectedTypes.value = selectedTypes.value.filter((t) => t !== type);
    emit("update:modelValue", selectedTypes.value);
};

const clearAll = () => {
    selectedTypes.value = [];
    emit("update:modelValue", selectedTypes.value);
};

const formatType = (type) => {
    // Ensure type is a string
    const typeValue =
        typeof type === "string" ? type : type?.value || String(type);
    const typeObj = props.availableTypes.find((t) => t.value === typeValue);
    return typeObj ? typeObj.label : typeValue;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    const dropdown = event.target.closest(".property-type-multi-select");
    if (!dropdown && isOpen.value) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<style scoped>
.property-type-multi-select {
    position: relative;
}
</style>
