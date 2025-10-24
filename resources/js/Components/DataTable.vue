<script setup>
import { ref, computed } from "vue";
import StatusBadge from "./StatusBadge.vue";

const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
    data: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    emptyMessage: {
        type: String,
        default: "No data available",
    },
    striped: {
        type: Boolean,
        default: true,
    },
    hoverable: {
        type: Boolean,
        default: true,
    },
    sortable: {
        type: Boolean,
        default: true,
    },
    pagination: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["sort", "row-click"]);

const sortColumn = ref(null);
const sortDirection = ref("asc");

const handleSort = (column) => {
    if (!props.sortable || !column.sortable) return;

    if (sortColumn.value === column.key) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortColumn.value = column.key;
        sortDirection.value = "asc";
    }

    emit("sort", {
        column: column.key,
        direction: sortDirection.value,
    });
};

const getSortIcon = (column) => {
    if (!props.sortable || !column.sortable) return null;
    if (sortColumn.value !== column.key)
        return "M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4";

    return sortDirection.value === "asc"
        ? "M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"
        : "M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4";
};

const handleRowClick = (row, index) => {
    if (props.hoverable) {
        emit("row-click", { row, index });
    }
};

const getCellValue = (row, column) => {
    const keys = column.key.split(".");
    let value = row;

    for (const key of keys) {
        value = value?.[key];
        if (value === undefined || value === null) break;
    }

    return value;
};

const formatCellValue = (value, column) => {
    if (value === null || value === undefined) return "-";

    if (column.format === "currency") {
        return new Intl.NumberFormat("en-PH", {
            style: "currency",
            currency: "PHP",
        }).format(value);
    }

    if (column.format === "date") {
        return new Date(value).toLocaleDateString("en-PH");
    }

    if (column.format === "datetime") {
        return new Date(value).toLocaleString("en-PH");
    }

    if (column.format === "number") {
        return new Intl.NumberFormat("en-PH").format(value);
    }

    if (column.format === "status") {
        return value;
    }

    return value;
};
</script>

<template>
    <div class="card overflow-hidden">
        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <!-- Table Header -->
                <thead class="bg-neutral-50 border-b border-neutral-200">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            :class="[
                                'px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider',
                                sortable && column.sortable
                                    ? 'cursor-pointer hover:bg-neutral-100'
                                    : '',
                                column.width ? `w-${column.width}` : '',
                            ]"
                            @click="handleSort(column)"
                        >
                            <div class="flex items-center gap-2">
                                <span>{{ column.label }}</span>

                                <!-- Sort Icon -->
                                <svg
                                    v-if="sortable && column.sortable"
                                    :class="[
                                        'w-4 h-4 transition-transform duration-200',
                                        sortColumn === column.key
                                            ? 'text-primary-600'
                                            : 'text-neutral-400',
                                    ]"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        :d="getSortIcon(column)"
                                    />
                                </svg>
                            </div>
                        </th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody :class="[striped ? 'divide-y divide-neutral-200' : '']">
                    <!-- Loading State -->
                    <tr v-if="loading">
                        <td
                            :colspan="columns.length"
                            class="px-6 py-12 text-center"
                        >
                            <div class="flex flex-col items-center gap-3">
                                <div
                                    class="w-8 h-8 animate-spin rounded-full border-2 border-primary-600 border-t-transparent"
                                ></div>
                                <p class="text-sm text-neutral-600">
                                    Loading data...
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-else-if="data.length === 0">
                        <td
                            :colspan="columns.length"
                            class="px-6 py-12 text-center"
                        >
                            <div class="flex flex-col items-center gap-3">
                                <div
                                    class="w-12 h-12 bg-neutral-100 rounded-lg flex items-center justify-center"
                                >
                                    <svg
                                        class="w-6 h-6 text-neutral-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                        />
                                    </svg>
                                </div>
                                <p class="text-sm text-neutral-600">
                                    {{ emptyMessage }}
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Data Rows -->
                    <tr
                        v-for="(row, index) in data"
                        :key="index"
                        :class="[
                            'transition-colors duration-150',
                            hoverable
                                ? 'hover:bg-neutral-50 cursor-pointer'
                                : '',
                            striped && index % 2 === 1 ? 'bg-neutral-25' : '',
                        ]"
                        @click="handleRowClick(row, index)"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="px-6 py-4 whitespace-nowrap text-sm"
                            :class="[
                                column.align === 'center' ? 'text-center' : '',
                                column.align === 'right'
                                    ? 'text-right'
                                    : 'text-left',
                            ]"
                        >
                            <!-- Custom Cell Content -->
                            <slot
                                v-if="$slots[`cell-${column.key}`]"
                                :name="`cell-${column.key}`"
                                :value="getCellValue(row, column)"
                                :row="row"
                                :column="column"
                                :index="index"
                            >
                                {{
                                    formatCellValue(
                                        getCellValue(row, column),
                                        column
                                    )
                                }}
                            </slot>

                            <!-- Status Badge -->
                            <StatusBadge
                                v-else-if="column.format === 'status'"
                                :status="getCellValue(row, column)"
                                size="sm"
                                :show-icon="true"
                            />

                            <!-- Default Cell Content -->
                            <span
                                v-else
                                :class="[
                                    column.format === 'currency'
                                        ? 'font-mono font-semibold'
                                        : '',
                                    column.format === 'number'
                                        ? 'font-mono'
                                        : '',
                                ]"
                            >
                                {{
                                    formatCellValue(
                                        getCellValue(row, column),
                                        column
                                    )
                                }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div
            v-if="pagination"
            class="px-6 py-4 border-t border-neutral-200 bg-neutral-50"
        >
            <div class="flex items-center justify-between">
                <div class="text-sm text-neutral-600">
                    Showing {{ pagination.from }} to {{ pagination.to }} of
                    {{ pagination.total }} results
                </div>

                <div class="flex items-center gap-2">
                    <!-- Pagination Controls -->
                    <button
                        v-if="pagination.prev_page_url"
                        @click="
                            $emit('page-change', pagination.current_page - 1)
                        "
                        class="px-3 py-2 text-sm font-medium text-neutral-600 bg-white border border-neutral-300 rounded-lg hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200"
                    >
                        Previous
                    </button>

                    <button
                        v-if="pagination.next_page_url"
                        @click="
                            $emit('page-change', pagination.current_page + 1)
                        "
                        class="px-3 py-2 text-sm font-medium text-neutral-600 bg-white border border-neutral-300 rounded-lg hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors duration-200"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
