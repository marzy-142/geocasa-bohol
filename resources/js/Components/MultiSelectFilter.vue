<template>
    <div class="relative" ref="dropdownRef">
        <button
            type="button"
            @click="toggleDropdown"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-left bg-white hover:bg-gray-50 flex items-center justify-between"
            :class="{ 'ring-2 ring-blue-500': isOpen }"
        >
            <span
                :class="{
                    'text-gray-500': selectedValues.length === 0,
                    'text-gray-900': selectedValues.length > 0,
                }"
            >
                <span v-if="selectedValues.length === 0">
                    {{ placeholder }}
                </span>
                <span v-else class="flex items-center gap-1">
                    <span class="font-medium">{{ selectedValues.length }}</span>
                    selected
                </span>
            </span>
            <svg
                class="w-4 h-4 text-gray-400 transition-transform"
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

        <!-- Selected badges (shown below button) -->
        <div
            v-if="selectedValues.length > 0 && !isOpen"
            class="flex flex-wrap gap-1 mt-1.5"
        >
            <span
                v-for="value in selectedValues.slice(0, 3)"
                :key="value"
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
            >
                {{ getOptionLabel(value) }}
            </span>
            <span
                v-if="selectedValues.length > 3"
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600"
            >
                +{{ selectedValues.length - 3 }}
            </span>
        </div>

        <!-- Dropdown menu -->
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
                class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto"
            >
                <!-- Search within options (if many options) -->
                <div
                    v-if="options.length > 8"
                    class="sticky top-0 bg-white border-b border-gray-200 p-2"
                >
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        @click.stop
                    />
                </div>

                <div class="py-1">
                    <!-- Select All / Clear All -->
                    <div
                        class="px-3 py-2 border-b border-gray-100 flex justify-between items-center"
                    >
                        <button
                            type="button"
                            @click="selectAll"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                        >
                            Select All
                        </button>
                        <button
                            type="button"
                            @click="clearAll"
                            class="text-xs text-gray-600 hover:text-gray-800"
                        >
                            Clear
                        </button>
                    </div>

                    <!-- Options list -->
                    <label
                        v-for="option in filteredOptions"
                        :key="option.value"
                        class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer transition-colors"
                    >
                        <input
                            type="checkbox"
                            :value="option.value"
                            :checked="isSelected(option.value)"
                            @change="toggleOption(option.value)"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <span class="ml-2 text-sm text-gray-700">
                            {{ option.label }}
                        </span>
                        <span
                            v-if="option.count"
                            class="ml-auto text-xs text-gray-500"
                        >
                            ({{ option.count }})
                        </span>
                    </label>

                    <div
                        v-if="filteredOptions.length === 0"
                        class="px-3 py-2 text-sm text-gray-500 text-center"
                    >
                        No options found
                    </div>
                </div>

                <!-- Apply button -->
                <div
                    class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-3 py-2"
                >
                    <button
                        type="button"
                        @click="applySelection"
                        class="w-full px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700 transition-colors"
                    >
                        Apply
                        <span v-if="selectedValues.length > 0">
                            ({{ selectedValues.length }})
                        </span>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
    modelValue: {
        type: [Array, String],
        default: () => [],
    },
    options: {
        type: Array,
        required: true,
    },
    placeholder: {
        type: String,
        default: "Select options",
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

const dropdownRef = ref(null);
const isOpen = ref(false);
const searchQuery = ref("");

// Convert modelValue to array
const selectedValues = ref(
    Array.isArray(props.modelValue)
        ? [...props.modelValue]
        : props.modelValue
        ? [props.modelValue]
        : []
);

// Watch for external changes
watch(
    () => props.modelValue,
    (newValue) => {
        selectedValues.value = Array.isArray(newValue)
            ? [...newValue]
            : newValue
            ? [newValue]
            : [];
    }
);

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;

    const query = searchQuery.value.toLowerCase();
    return props.options.filter((option) =>
        option.label.toLowerCase().includes(query)
    );
});

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (!isOpen.value) {
        searchQuery.value = "";
    }
};

const isSelected = (value) => {
    return selectedValues.value.includes(value);
};

const toggleOption = (value) => {
    if (isSelected(value)) {
        selectedValues.value = selectedValues.value.filter((v) => v !== value);
    } else {
        selectedValues.value.push(value);
    }
    emit("update:modelValue", selectedValues.value);
    emit("change", selectedValues.value);
};

const selectAll = () => {
    selectedValues.value = props.options.map((opt) => opt.value);
    emit("update:modelValue", selectedValues.value);
    emit("change", selectedValues.value);
};

const clearAll = () => {
    selectedValues.value = [];
    emit("update:modelValue", selectedValues.value);
    emit("change", selectedValues.value);
};

const applySelection = () => {
    emit("update:modelValue", selectedValues.value);
    emit("change", selectedValues.value);
    isOpen.value = false;
    searchQuery.value = "";
};

const getOptionLabel = (value) => {
    const option = props.options.find((opt) => opt.value === value);
    return option ? option.label : value;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        if (isOpen.value) {
            applySelection(); // Auto-apply on click outside
        }
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>
