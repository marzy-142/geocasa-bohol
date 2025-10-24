<script setup>
import { computed } from 'vue';
import { ChevronUpDownIcon, ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    columns: Array,
    data: Array,
    loading: {
        type: Boolean,
        default: false
    },
    sortable: {
        type: Boolean,
        default: true
    },
    currentSort: {
        type: Object,
        default: () => ({ field: null, direction: null })
    }
});

const emit = defineEmits(['sort']);

const handleSort = (column) => {
    if (!props.sortable || !column.sortable) return;
    
    let direction = 'asc';
    if (props.currentSort.field === column.key && props.currentSort.direction === 'asc') {
        direction = 'desc';
    }
    
    emit('sort', { field: column.key, direction });
};

const getSortIcon = (column) => {
    if (!props.sortable || !column.sortable) return null;
    
    if (props.currentSort.field === column.key) {
        return props.currentSort.direction === 'asc' ? ChevronUpIcon : ChevronDownIcon;
    }
    return ChevronUpDownIcon;
};
</script>

<template>
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider',
                                column.sortable && sortable ? 'cursor-pointer hover:bg-gray-100 transition-colors' : ''
                            ]"
                            @click="handleSort(column)"
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ column.label }}</span>
                                <component
                                    v-if="getSortIcon(column)"
                                    :is="getSortIcon(column)"
                                    class="w-4 h-4 text-gray-400"
                                />
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    <!-- Loading state -->
                    <tr v-if="loading" v-for="i in 5" :key="`loading-${i}`">
                        <td v-for="column in columns" :key="column.key" class="px-6 py-4">
                            <div class="animate-pulse">
                                <div class="h-4 bg-gray-200 rounded w-full"></div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Data rows -->
                    <tr 
                        v-else
                        v-for="(item, index) in data" 
                        :key="index"
                        class="hover:bg-gray-50/50 transition-colors"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="px-6 py-4 whitespace-nowrap"
                        >
                            <slot 
                                :name="`cell-${column.key}`" 
                                :item="item" 
                                :value="item[column.key]"
                                :index="index"
                            >
                                <span class="text-sm text-gray-900">
                                    {{ item[column.key] }}
                                </span>
                            </slot>
                        </td>
                    </tr>
                    
                    <!-- Empty state -->
                    <tr v-if="!loading && data.length === 0">
                        <td :colspan="columns.length" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">No data available</p>
                                    <p class="text-sm text-gray-500">Get started by adding your first item.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>