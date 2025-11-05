<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-gray-900">
                    {{ title || "Search & Filter" }}
                    <span
                        v-if="resultCount !== null"
                        class="text-gray-500 font-normal"
                    >
                        ({{ resultCount }}
                        {{ resultCount === 1 ? "result" : "results" }})
                    </span>
                </h3>
                <div class="flex items-center space-x-2">
                    <button
                        v-if="hasActiveFilters"
                        @click="clearFilters"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors"
                    >
                        Clear filters
                    </button>
                    <button
                        v-if="collapsible"
                        @click="isExpanded = !isExpanded"
                        class="p-1 text-gray-400 hover:text-gray-600 transition-colors"
                        :aria-label="
                            isExpanded ? 'Collapse filters' : 'Expand filters'
                        "
                    >
                        <ChevronUpIcon v-if="isExpanded" class="w-4 h-4" />
                        <ChevronDownIcon v-else class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div v-show="!collapsible || isExpanded" class="p-4">
            <!-- Primary Search Row -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-3">
                <!-- Search Input -->
                <div class="md:col-span-4">
                    <div class="relative">
                        <MagnifyingGlassIcon
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"
                        />
                        <input
                            :value="search"
                            @input="$emit('search-change', $event.target.value)"
                            type="text"
                            :placeholder="searchPlaceholder"
                            class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        />
                        <button
                            v-if="search"
                            @click="$emit('search-change', '')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Primary Filters -->
                <div
                    v-for="filter in primaryFilters"
                    :key="filter.key"
                    :class="getFilterColumnClass(filter)"
                >
                    <select
                        v-if="filter.type === 'select'"
                        :value="getFilterValue(filter.key)"
                        @change="
                            $emit(
                                'filter-change',
                                filter.key,
                                $event.target.value
                            )
                        "
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    >
                        <option value="">
                            {{ filter.placeholder || `All ${filter.label}` }}
                        </option>
                        <option
                            v-for="option in filter.options"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>

                    <input
                        v-else-if="filter.type === 'number'"
                        :value="getFilterValue(filter.key)"
                        @input="
                            $emit(
                                'filter-change',
                                filter.key,
                                $event.target.value
                            )
                        "
                        type="number"
                        :placeholder="filter.placeholder"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    />

                    <input
                        v-else-if="filter.type === 'date'"
                        :value="getFilterValue(filter.key)"
                        @input="
                            $emit(
                                'filter-change',
                                filter.key,
                                $event.target.value
                            )
                        "
                        type="date"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    />
                </div>

                <!-- Expand/More Button -->
                <div v-if="secondaryFilters.length > 0" class="md:col-span-2">
                    <button
                        @click="showSecondaryFilters = !showSecondaryFilters"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors flex items-center justify-center"
                    >
                        <AdjustmentsHorizontalIcon class="w-4 h-4 mr-1" />
                        More
                        <ChevronDownIcon
                            v-if="!showSecondaryFilters"
                            class="w-3 h-3 ml-1"
                        />
                        <ChevronUpIcon v-else class="w-3 h-3 ml-1" />
                    </button>
                </div>
            </div>

            <!-- Secondary Filters (Expandable) -->
            <div
                v-show="showSecondaryFilters && secondaryFilters.length > 0"
                class="grid grid-cols-1 md:grid-cols-4 gap-3 pt-3 border-t border-gray-200"
            >
                <div v-for="filter in secondaryFilters" :key="filter.key">
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        {{ filter.label }}
                    </label>

                    <select
                        v-if="filter.type === 'select'"
                        :value="getFilterValue(filter.key)"
                        @change="
                            $emit(
                                'filter-change',
                                filter.key,
                                $event.target.value
                            )
                        "
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    >
                        <option value="">
                            {{ filter.placeholder || `All ${filter.label}` }}
                        </option>
                        <option
                            v-for="option in filter.options"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>

                    <input
                        v-else
                        :value="getFilterValue(filter.key)"
                        @input="
                            $emit(
                                'filter-change',
                                filter.key,
                                $event.target.value
                            )
                        "
                        :type="filter.type"
                        :placeholder="filter.placeholder"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    />
                </div>
            </div>

            <!-- Active Filter Tags -->
            <div
                v-if="activeFilterTags.length > 0"
                class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-200"
            >
                <span
                    v-for="tag in activeFilterTags"
                    :key="tag.key"
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                >
                    {{ tag.label }}: {{ tag.value }}
                    <button
                        @click="$emit('filter-change', tag.key, '')"
                        class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-blue-200 transition-colors"
                    >
                        <XMarkIcon class="w-3 h-3" />
                    </button>
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import {
    MagnifyingGlassIcon,
    XMarkIcon,
    ChevronUpIcon,
    ChevronDownIcon,
    AdjustmentsHorizontalIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    title: String,
    search: String,
    searchPlaceholder: {
        type: String,
        default: "Search...",
    },
    filters: Object,
    primaryFilters: {
        type: Array,
        default: () => [],
    },
    secondaryFilters: {
        type: Array,
        default: () => [],
    },
    resultCount: {
        type: Number,
        default: null,
    },
    collapsible: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["search-change", "filter-change", "clear-filters"]);

const isExpanded = ref(true);
const showSecondaryFilters = ref(false);

const hasActiveFilters = computed(() => {
    if (props.search) return true;
    return Object.values(props.filters || {}).some(
        (value) => value !== "" && value !== null
    );
});

const activeFilterTags = computed(() => {
    const tags = [];

    if (props.search) {
        tags.push({
            key: "search",
            label: "Search",
            value: props.search,
        });
    }

    const allFilters = [...props.primaryFilters, ...props.secondaryFilters];

    allFilters.forEach((filter) => {
        const value = getFilterValue(filter.key);
        if (value) {
            const option = filter.options?.find((opt) => opt.value === value);
            tags.push({
                key: filter.key,
                label: filter.label,
                value: option?.label || value,
            });
        }
    });

    return tags;
});

const getFilterValue = (key) => {
    return props.filters?.[key] || "";
};

const getFilterColumnClass = (filter) => {
    const span = filter.span || 2;
    return `md:col-span-${span}`;
};

const clearFilters = () => {
    emit("clear-filters");
};
</script>
