<script setup>
import { ref, computed, onMounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import UnifiedSearchFilter from "@/Components/UnifiedSearchFilter.vue";
import Pagination from "@/Components/Pagination.vue";
import Button from "@/Components/Button.vue";
import Card from "@/Components/Card.vue";
import Badge from "@/Components/Badge.vue";
import {
    ChartBarIcon,
    CurrencyDollarIcon,
    ClockIcon,
    UserGroupIcon,
    UserIcon,
    BuildingOfficeIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    ArrowPathIcon,
    CalendarIcon,
    TagIcon,
    CheckCircleIcon,
    XCircleIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    transactions: Object,
    filters: Object,
    properties: Array,
    statuses: Object,
    stats: Object,
});

const search = ref("");
const selectedStatus = ref("");
const selectedProperty = ref("");
const dateFrom = ref("");
const dateTo = ref("");
const sortBy = ref("");
const sortOrder = ref("");

// Filter object for UnifiedSearchFilter
const filterObject = computed(() => ({
    status: selectedStatus.value,
    property_id: selectedProperty.value,
    date_from: dateFrom.value,
    date_to: dateTo.value,
    sort_by: sortBy.value,
    sort_order: sortOrder.value,
}));

// Filter configurations for UnifiedSearchFilter
const primaryFilters = computed(() => [
    {
        key: "status",
        label: "Status",
        type: "select",
        span: 3,
        options: Object.entries(props.statuses).map(([value, label]) => ({
            value,
            label,
        })),
    },
    {
        key: "sort_by",
        label: "Sort By",
        type: "select",
        span: 3,
        options: [
            { value: "created_at", label: "Date Created" },
            { value: "offered_price", label: "Price" },
            { value: "status", label: "Status" },
            { value: "updated_at", label: "Last Updated" },
        ],
    },
]);

const secondaryFilters = computed(() => [
    {
        key: "property_id",
        label: "Property",
        type: "select",
        options: props.properties.map((property) => ({
            value: property.id,
            label: property.title,
        })),
    },
    {
        key: "date_from",
        label: "Date From",
        type: "date",
        placeholder: "Start date",
    },
    {
        key: "date_to",
        label: "Date To",
        type: "date",
        placeholder: "End date",
    },
    {
        key: "sort_order",
        label: "Sort Order",
        type: "select",
        options: [
            { value: "desc", label: "Newest First" },
            { value: "asc", label: "Oldest First" },
        ],
    },
]);

// Event handlers for UnifiedSearchFilter
const handleSearchChange = (value) => {
    search.value = value;
    applyFilters();
};

const handleFilterChange = (key, value) => {
    switch (key) {
        case "status":
            selectedStatus.value = value;
            break;
        case "property_id":
            selectedProperty.value = value;
            break;
        case "date_from":
            dateFrom.value = value;
            break;
        case "date_to":
            dateTo.value = value;
            break;
        case "sort_by":
            sortBy.value = value || "";
            break;
        case "sort_order":
            sortOrder.value = value || "";
            break;
    }
    applyFilters();
};

const clearAllFilters = () => {
    search.value = "";
    selectedStatus.value = "";
    selectedProperty.value = "";
    dateFrom.value = "";
    dateTo.value = "";
    sortBy.value = "";
    sortOrder.value = "";
    applyFilters();
};

// Determine if current user is admin to conditionally hide edit/delete actions
const page = usePage();
const isAdmin = computed(() => page.props?.auth?.user?.role === "admin");

// Computed properties for better UX
const filteredTransactions = computed(() => {
    return props.transactions?.data || [];
});

const totalValue = computed(() => {
    return filteredTransactions.value.reduce((sum, transaction) => {
        return sum + (parseFloat(transaction.offered_price) || 0);
    }, 0);
});

const activeTransactions = computed(() => {
    return filteredTransactions.value.filter(
        (t) => !["finalized", "cancelled"].includes(t.status)
    ).length;
});

const applyFilters = () => {
    router.get(
        route("transactions.index"),
        {
            search: search.value,
            status: selectedStatus.value,
            property_id: selectedProperty.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
            sort_by: sortBy.value,
            sort_order: sortOrder.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const getStatusColor = (status) => {
    const colors = {
        inquiry: "bg-gray-100 text-gray-800 border-gray-200",
        initial_contact: "bg-blue-100 text-blue-800 border-blue-200",
        property_viewing: "bg-purple-100 text-purple-800 border-purple-200",
        offer_made: "bg-yellow-100 text-yellow-800 border-yellow-200",
        negotiation: "bg-orange-100 text-orange-800 border-orange-200",
        offer_accepted: "bg-green-100 text-green-800 border-green-200",
        contract_signed: "bg-indigo-100 text-indigo-800 border-indigo-200",
        due_diligence: "bg-pink-100 text-pink-800 border-pink-200",
        financing: "bg-cyan-100 text-cyan-800 border-cyan-200",
        closing_preparation: "bg-teal-100 text-teal-800 border-teal-200",
        finalized: "bg-emerald-100 text-emerald-800 border-emerald-200",
        cancelled: "bg-red-100 text-red-800 border-red-200",
    };
    return colors[status] || "bg-gray-100 text-gray-800 border-gray-200";
};

const getStatusIcon = (status) => {
    const icons = {
        inquiry: ExclamationTriangleIcon,
        initial_contact: ClockIcon,
        property_viewing: EyeIcon,
        offer_made: TagIcon,
        negotiation: ArrowPathIcon,
        offer_accepted: CheckCircleIcon,
        contract_signed: CheckCircleIcon,
        due_diligence: ClockIcon,
        financing: CurrencyDollarIcon,
        closing_preparation: ClockIcon,
        finalized: CheckCircleIcon,
        cancelled: XCircleIcon,
    };
    return icons[status] || ClockIcon;
};

const getStatusBadgeVariant = (status) => {
    const variants = {
        inquiry: "default",
        initial_contact: "info",
        property_viewing: "primary",
        offer_made: "warning",
        negotiation: "warning",
        offer_accepted: "success",
        contract_signed: "success",
        due_diligence: "info",
        financing: "accent",
        closing_preparation: "info",
        finalized: "success",
        cancelled: "error",
    };
    return variants[status] || "default";
};

const formatCurrency = (amount) => {
    if (!amount || amount === 0) return "₱0.00";
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getDaysInProgress = (transaction) => {
    const startDate = new Date(transaction.inquiry_date);
    const endDate = transaction.finalized_date
        ? new Date(transaction.finalized_date)
        : new Date();
    const diffTime = Math.abs(endDate - startDate);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};

// Enhanced computed properties for better UX
const isOverdue = (transaction) => {
    const days = getDaysInProgress(transaction);
    return (
        days > 30 &&
        !["finalized", "cancelled", "rejected"].includes(transaction.status)
    );
};

const getPriorityColor = (transaction) => {
    if (isOverdue(transaction)) return "text-red-600 bg-red-50 border-red-200";
    if (transaction.status === "finalized")
        return "text-green-600 bg-green-50 border-green-200";
    if (
        transaction.status === "negotiation" ||
        transaction.status === "offer_made"
    )
        return "text-yellow-600 bg-yellow-50 border-yellow-200";
    if (
        transaction.status === "offer_accepted" ||
        transaction.status === "contract_signed"
    )
        return "text-blue-600 bg-blue-50 border-blue-200";
    return "text-gray-600 bg-gray-50 border-gray-200";
};

const getTransactionProgress = (transaction) => {
    const statusOrder = {
        inquiry: 1,
        initial_contact: 2,
        property_viewing: 3,
        offer_made: 4,
        negotiation: 5,
        offer_accepted: 6,
        contract_signed: 7,
        due_diligence: 8,
        financing: 9,
        closing_preparation: 10,
        finalized: 11,
        rejected: 0,
        cancelled: 0,
    };
    return statusOrder[transaction.status] || 0;
};

const getProgressPercentage = (transaction) => {
    return (getTransactionProgress(transaction) / 11) * 100;
};

const deleteTransaction = (transaction) => {
    if (confirm("Are you sure you want to delete this transaction?")) {
        router.delete(route("transactions.destroy", transaction.id));
    }
};

const updateStatus = (transactionId, newStatus) => {
    router.patch(route("transactions.update", transactionId), {
        status: newStatus,
    });
};

onMounted(() => {
    // Initialize any additional setup if needed
});
</script>

<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Enhanced Header Section -->
            <div
                class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-8 text-white shadow-lg"
            >
                <div
                    class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
                >
                    <div>
                        <div class="flex items-center gap-4 mb-3">
                            <div
                                class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"
                            >
                                <ChartBarIcon class="w-8 h-8 text-white" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold">
                                    Transaction Management
                                </h1>
                                <p class="text-blue-100 text-lg">
                                    Manage your property transactions
                                    efficiently
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unified Search & Filter -->
            <UnifiedSearchFilter
                title="Search & Filter Transactions"
                :search="search"
                search-placeholder="Search by transaction number, property, client, or broker..."
                :filters="filterObject"
                :result-count="transactions.total"
                :primary-filters="primaryFilters"
                :secondary-filters="secondaryFilters"
                @search-change="handleSearchChange"
                @filter-change="handleFilterChange"
                @clear-filters="clearAllFilters"
            />

            <!-- Quick Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    class="bg-white rounded-xl p-6 shadow-sm border border-gray-100"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Total Transactions
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ transactions?.total || 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                        >
                            <ChartBarIcon class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl p-6 shadow-sm border border-gray-100"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Active Transactions
                            </p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ activeTransactions }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center"
                        >
                            <ClockIcon class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl p-6 shadow-sm border border-gray-100"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Total Value
                            </p>
                            <p class="text-2xl font-bold text-indigo-600">
                                {{ formatCurrency(totalValue) }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center"
                        >
                            <CurrencyDollarIcon
                                class="w-6 h-6 text-indigo-600"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl p-6 shadow-sm border border-gray-100"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Properties
                            </p>
                            <p class="text-2xl font-bold text-purple-600">
                                {{ properties?.length || 0 }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center"
                        >
                            <BuildingOfficeIcon
                                class="w-6 h-6 text-purple-600"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Transactions Display -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
            >
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6"
                >
                    <h3
                        class="text-lg font-semibold text-gray-900 flex items-center gap-2"
                    >
                        <ChartBarIcon class="w-5 h-5 text-gray-500" />
                        Transactions ({{ transactions?.total || 0 }})
                    </h3>
                    <div class="mt-2 sm:mt-0 text-sm text-gray-500">
                        Showing {{ filteredTransactions.length }} of
                        {{ transactions?.total || 0 }} transactions
                    </div>
                </div>

                <div
                    v-if="filteredTransactions.length > 0"
                    class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6"
                >
                    <Card
                        v-for="transaction in filteredTransactions"
                        :key="transaction.id"
                        variant="default"
                        hoverable
                        :class="getPriorityColor(transaction)"
                    >
                        <!-- Header with Status and Priority -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ transaction.transaction_number }}
                                    </h3>
                                    <!-- Priority Badge -->
                                    <Badge
                                        v-if="isOverdue(transaction)"
                                        variant="error"
                                        size="sm"
                                        :icon="ExclamationTriangleIcon"
                                    >
                                        OVERDUE
                                    </Badge>
                                </div>

                                <div class="flex items-center gap-2">
                                    <component
                                        :is="getStatusIcon(transaction.status)"
                                        class="w-4 h-4 text-gray-500"
                                    />
                                    <Badge
                                        :variant="
                                            getStatusBadgeVariant(
                                                transaction.status
                                            )
                                        "
                                        size="sm"
                                    >
                                        {{ statuses[transaction.status] }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="text-xl font-bold text-green-600">
                                    {{
                                        formatCurrency(
                                            transaction.offered_price
                                        )
                                    }}
                                </p>
                                <p
                                    v-if="transaction.final_price"
                                    class="text-sm text-gray-500"
                                >
                                    Final:
                                    {{
                                        formatCurrency(transaction.final_price)
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="mb-4">
                            <div
                                class="flex items-center justify-between text-sm text-gray-500 mb-1"
                            >
                                <span>Progress</span>
                                <span
                                    >{{
                                        Math.round(
                                            getProgressPercentage(transaction)
                                        )
                                    }}%</span
                                >
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div
                                    class="bg-blue-600 h-2 rounded-full transition-all duration-500 ease-out"
                                    :style="{
                                        width:
                                            getProgressPercentage(transaction) +
                                            '%',
                                    }"
                                ></div>
                            </div>
                        </div>

                        <!-- Property & Client Info -->
                        <div class="space-y-3 mb-4">
                            <div
                                class="flex items-center text-sm text-gray-600"
                            >
                                <BuildingOfficeIcon
                                    class="w-4 h-4 mr-3 text-gray-400"
                                />
                                <div class="flex-1">
                                    <span class="font-medium">{{
                                        transaction.property.title
                                    }}</span>
                                    <p class="text-xs text-gray-500">
                                        {{ transaction.property.municipality }}
                                    </p>
                                </div>
                            </div>
                            <div
                                class="flex items-center text-sm text-gray-600"
                            >
                                <UserGroupIcon
                                    class="w-4 h-4 mr-3 text-gray-400"
                                />
                                <div class="flex-1">
                                    <span class="font-medium">{{
                                        transaction.client.name
                                    }}</span>
                                    <p class="text-xs text-gray-500">
                                        {{ transaction.client.email }}
                                    </p>
                                </div>
                            </div>
                            <div
                                class="flex items-center text-sm text-gray-600"
                            >
                                <UserIcon class="w-4 h-4 mr-3 text-gray-400" />
                                <span>{{ transaction.broker.name }}</span>
                            </div>
                        </div>

                        <!-- Timeline & Progress -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <div class="flex items-center gap-2">
                                    <CalendarIcon
                                        class="w-4 h-4 text-gray-500"
                                    />
                                    <span class="text-gray-600">Started:</span>
                                    <span class="font-medium">{{
                                        formatDate(transaction.inquiry_date)
                                    }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <ClockIcon class="w-4 h-4 text-gray-500" />
                                    <span class="text-gray-600"
                                        >{{
                                            getDaysInProgress(transaction)
                                        }}
                                        days</span
                                    >
                                </div>
                            </div>
                            <div
                                v-if="transaction.finalized_date"
                                class="mt-2 flex items-center gap-2 text-sm"
                            >
                                <CheckCircleIcon
                                    class="w-4 h-4 text-green-500"
                                />
                                <span class="text-gray-600">Finalized:</span>
                                <span class="font-medium text-green-600">{{
                                    formatDate(transaction.finalized_date)
                                }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div
                            class="flex justify-between items-center pt-4 border-t border-gray-100"
                        >
                            <Button
                                as="a"
                                :href="
                                    route('transactions.show', transaction.id)
                                "
                                variant="ghost"
                                size="sm"
                                :icon="EyeIcon"
                            >
                                View Details
                            </Button>
                            <div class="flex items-center gap-3">
                                <Button
                                    v-if="!isAdmin"
                                    as="a"
                                    :href="
                                        route(
                                            'transactions.edit',
                                            transaction.id
                                        )
                                    "
                                    variant="ghost"
                                    size="sm"
                                    :icon="PencilIcon"
                                >
                                    Edit
                                </Button>
                                <Button
                                    v-if="!isAdmin"
                                    @click="deleteTransaction(transaction)"
                                    variant="ghost"
                                    size="sm"
                                    :icon="TrashIcon"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    Delete
                                </Button>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Enhanced Empty State -->
                <div v-else class="text-center py-16">
                    <div
                        class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6"
                    >
                        <ChartBarIcon class="w-12 h-12 text-gray-400" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        No transactions found
                    </h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        {{
                            search ||
                            selectedStatus ||
                            selectedProperty ||
                            dateFrom ||
                            dateTo
                                ? "Try adjusting your search filters to find transactions."
                                : "No transactions found. Transactions will appear here when client inquiries are converted."
                        }}
                    </p>
                </div>

                <!-- Enhanced Pagination -->
                <div v-if="filteredTransactions.length > 0" class="mt-8">
                    <Pagination :links="transactions.links" />
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
