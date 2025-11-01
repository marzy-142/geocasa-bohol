<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Link } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    transactions: Object,
    filters: Object,
    properties: Array,
    brokers: Array,
    statuses: Object,
    canCreate: Boolean,
    financialStats: Object,
    performanceMetrics: Object,
});

const search = ref(props.filters.search || "");
const selectedStatus = ref(props.filters.status || "");
const selectedProperty = ref(props.filters.property_id || "");
const selectedBroker = ref(props.filters.broker_id || "");
const dateFrom = ref(props.filters.date_from || "");
const dateTo = ref(props.filters.date_to || "");
const minAmount = ref(props.filters.min_amount || "");
const maxAmount = ref(props.filters.max_amount || "");
const sortBy = ref(props.filters.sort_by || "created_at");

// View states
const viewMode = ref("grid");
const showAdvancedFilters = ref(false);
const showFinancialModal = ref(false);
const selectedTransaction = ref(null);

const applyFilters = () => {
    router.get(
        route("admin.transactions.index"),
        {
            search: search.value,
            status: selectedStatus.value,
            property_id: selectedProperty.value,
            broker_id: selectedBroker.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
            min_amount: minAmount.value,
            max_amount: maxAmount.value,
            sort_by: sortBy.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const clearFilters = () => {
    search.value = "";
    selectedStatus.value = "";
    selectedProperty.value = "";
    selectedBroker.value = "";
    dateFrom.value = "";
    dateTo.value = "";
    minAmount.value = "";
    maxAmount.value = "";
    applyFilters();
};

const toggleAdvancedFilters = () => {
    showAdvancedFilters.value = !showAdvancedFilters.value;
};

const getStatusColor = (status) => {
    const colors = {
        inquiry: "bg-gray-100 text-gray-800",
        initial_contact: "bg-blue-100 text-blue-800",
        property_viewing: "bg-purple-100 text-purple-800",
        offer_made: "bg-yellow-100 text-yellow-800",
        negotiation: "bg-orange-100 text-orange-800",
        offer_accepted: "bg-green-100 text-green-800",
        contract_signed: "bg-indigo-100 text-indigo-800",
        due_diligence: "bg-pink-100 text-pink-800",
        financing: "bg-cyan-100 text-cyan-800",
        closing_preparation: "bg-teal-100 text-teal-800",
        finalized: "bg-emerald-100 text-emerald-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const getProgressPercentage = (status) => {
    const progressMap = {
        inquiry: 10,
        initial_contact: 20,
        property_viewing: 30,
        offer_made: 40,
        negotiation: 50,
        offer_accepted: 60,
        contract_signed: 70,
        due_diligence: 80,
        financing: 85,
        closing_preparation: 90,
        finalized: 100,
        cancelled: 0,
    };
    return progressMap[status] || 0;
};

// Admin oversight note modal state
const showOversightNoteModal = ref(false);
const oversightNoteForm = ref({
    transaction_id: null,
    oversight_note: "",
    flag_for_review: false,
});

const addOversightNote = (transaction) => {
    oversightNoteForm.value = {
        transaction_id: transaction.id,
        oversight_note: "",
        flag_for_review: false,
    };
    selectedTransaction.value = transaction;
    showOversightNoteModal.value = true;
};

const submitOversightNote = () => {
    router.post(
        route(
            "admin.transactions.add-oversight-note",
            oversightNoteForm.value.transaction_id
        ),
        {
            oversight_note: oversightNoteForm.value.oversight_note,
            flag_for_review: oversightNoteForm.value.flag_for_review,
        },
        {
            onSuccess: () => {
                showOversightNoteModal.value = false;
                oversightNoteForm.value = {
                    transaction_id: null,
                    oversight_note: "",
                    flag_for_review: false,
                };
            },
        }
    );
};

const viewFinancialDetails = (transaction) => {
    selectedTransaction.value = transaction;
    showFinancialModal.value = true;
};

const exportReport = () => {
    window.open(route("admin.transactions.export", props.filters), "_blank");
};

const formatCurrency = (amount) => {
    return (
        "₱" +
        Number(amount).toLocaleString("en-PH", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })
    );
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getDaysInProgress = (transaction) => {
    const start = new Date(transaction.inquiry_date || transaction.created_at);
    const end = transaction.finalized_date
        ? new Date(transaction.finalized_date)
        : new Date();
    const days = Math.floor((end - start) / (1000 * 60 * 60 * 24));
    return days;
};
</script>

<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Enhanced Admin Header with Financial Stats -->
            <div
                class="bg-gradient-to-r from-emerald-600 via-green-600 to-teal-600 text-white p-8 rounded-xl mb-6 shadow-2xl"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold mb-2">
                            Transaction Oversight & Financial Analytics
                        </h1>
                        <p class="text-emerald-100 text-lg">
                            System-wide transaction monitoring, sales tracking,
                            and performance analytics
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Financial Summary -->
                        <div
                            class="bg-white/20 backdrop-blur-sm rounded-xl p-4"
                        >
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="text-center">
                                    <div class="text-2xl font-bold">
                                        {{
                                            financialStats?.total_transactions ||
                                            0
                                        }}
                                    </div>
                                    <div class="text-emerald-100">Total</div>
                                </div>
                                <div class="text-center">
                                    <div
                                        class="text-2xl font-bold text-yellow-300"
                                    >
                                        {{ financialStats?.active || 0 }}
                                    </div>
                                    <div class="text-emerald-100">Active</div>
                                </div>
                                <div class="text-center">
                                    <div
                                        class="text-2xl font-bold text-green-300"
                                    >
                                        {{
                                            formatCurrency(
                                                financialStats?.total_value || 0
                                            )
                                        }}
                                    </div>
                                    <div class="text-emerald-100">
                                        Total Value
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div
                                        class="text-2xl font-bold text-blue-300"
                                    >
                                        {{
                                            formatCurrency(
                                                financialStats?.total_commission ||
                                                    0
                                            )
                                        }}
                                    </div>
                                    <div class="text-emerald-100">
                                        Sales Value
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Quick Actions -->
                        <div class="flex flex-col space-y-2">
                            <button
                                @click="exportReport"
                                class="bg-white/20 hover:bg-white/30 backdrop-blur-sm px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center"
                            >
                                <svg
                                    class="w-4 h-4 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    ></path>
                                </svg>
                                Export Report
                            </button>
                            <Link
                                v-if="canCreate"
                                :href="route('admin.transactions.create')"
                                class="bg-white text-green-600 hover:bg-green-50 font-semibold px-4 py-2 rounded-lg transition-colors duration-200 shadow-lg text-center text-sm"
                            >
                                <span class="flex items-center justify-center">
                                    <svg
                                        class="w-4 h-4 mr-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 4v16m8-8H4"
                                        ></path>
                                    </svg>
                                    New Transaction
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics Dashboard -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6"
            >
                <div
                    class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-emerald-500"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Avg Deal Time
                            </p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                {{ performanceMetrics?.avg_deal_time || "N/A" }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{
                                    performanceMetrics?.deal_time_trend ||
                                    "No trend data"
                                }}
                            </p>
                        </div>
                        <div class="bg-emerald-100 rounded-full p-3">
                            <svg
                                class="w-8 h-8 text-emerald-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Success Rate
                            </p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                {{ performanceMetrics?.success_rate || "0" }}%
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{
                                    performanceMetrics?.success_trend ||
                                    "No trend data"
                                }}
                            </p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3">
                            <svg
                                class="w-8 h-8 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Avg Sales Value
                            </p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                {{
                                    formatCurrency(
                                        performanceMetrics?.avg_commission || 0
                                    )
                                }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Per transaction
                            </p>
                        </div>
                        <div class="bg-purple-100 rounded-full p-3">
                            <svg
                                class="w-8 h-8 text-purple-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-orange-500"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">
                                Pending Review
                            </p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                {{ performanceMetrics?.pending_review || 0 }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Requires attention
                            </p>
                        </div>
                        <div class="bg-orange-100 rounded-full p-3">
                            <svg
                                class="w-8 h-8 text-orange-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                ></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Filters Section -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        Advanced Filters & Search
                    </h2>
                    <button
                        @click="toggleAdvancedFilters"
                        class="text-sm text-emerald-600 hover:text-emerald-800 font-medium"
                    >
                        {{ showAdvancedFilters ? "Hide" : "Show" }} Advanced
                        Filters
                    </button>
                </div>

                <!-- Primary Filters -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4"
                >
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Search</label
                        >
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search transactions..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            @input="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Status</label
                        >
                        <select
                            v-model="selectedStatus"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            @change="applyFilters"
                        >
                            <option value="">All Statuses</option>
                            <option
                                v-for="(label, value) in statuses"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Broker</label
                        >
                        <select
                            v-model="selectedBroker"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            @change="applyFilters"
                        >
                            <option value="">All Brokers</option>
                            <option
                                v-for="broker in brokers"
                                :key="broker.id"
                                :value="broker.id"
                            >
                                {{ broker.name }} ({{
                                    broker.transaction_count || 0
                                }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Property</label
                        >
                        <select
                            v-model="selectedProperty"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            @change="applyFilters"
                        >
                            <option value="">All Properties</option>
                            <option
                                v-for="property in properties"
                                :key="property.id"
                                :value="property.id"
                            >
                                {{ property.title }} -
                                {{ property.municipality }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Date From</label
                        >
                        <input
                            v-model="dateFrom"
                            type="date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            @change="applyFilters"
                        />
                    </div>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div
                    v-if="showAdvancedFilters"
                    class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-4 border-t border-gray-200"
                >
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Date To</label
                        >
                        <input
                            v-model="dateTo"
                            type="date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Min Amount</label
                        >
                        <input
                            v-model="minAmount"
                            type="number"
                            placeholder="₱0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Max Amount</label
                        >
                        <input
                            v-model="maxAmount"
                            type="number"
                            placeholder="₱999,999,999"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Sort By</label
                        >
                        <select
                            v-model="sortBy"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            @change="applyFilters"
                        >
                            <option value="created_at">Date Created</option>
                            <option value="amount">Transaction Amount</option>
                            <option value="commission">Sales Value</option>
                            <option value="status">Status</option>
                            <option value="broker">Broker</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6">
                    <button
                        @click="clearFilters"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium transition-colors"
                    >
                        Clear all filters
                    </button>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm text-gray-600">View:</span>
                        <button
                            @click="viewMode = 'grid'"
                            :class="
                                viewMode === 'grid'
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-gray-200 text-gray-700'
                            "
                            class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
                        >
                            Grid
                        </button>
                        <button
                            @click="viewMode = 'table'"
                            :class="
                                viewMode === 'table'
                                    ? 'bg-emerald-600 text-white'
                                    : 'bg-gray-200 text-gray-700'
                            "
                            class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
                        >
                            Table
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transactions Display -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-900">
                        All Transactions ({{ transactions.total || 0 }})
                    </h2>
                </div>

                <!-- Grid View -->
                <div
                    v-if="viewMode === 'grid' && transactions.data.length > 0"
                    class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6"
                >
                    <div
                        v-for="transaction in transactions.data"
                        :key="transaction.id"
                        class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow"
                    >
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ transaction.transaction_number }}
                                    </h3>
                                    <span
                                        :class="
                                            getStatusColor(transaction.status)
                                        "
                                        class="inline-block px-2 py-1 text-xs font-medium rounded-full mt-1"
                                    >
                                        {{ statuses[transaction.status] }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-green-600">
                                        {{ transaction.formatted_total_price }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ transaction.formatted_created_at }}
                                    </p>
                                </div>
                            </div>

                            <!-- Property Info -->
                            <div class="mb-4">
                                <h4 class="font-medium text-gray-900">
                                    {{ transaction.property?.title }}
                                </h4>
                                <p class="text-sm text-gray-500">
                                    {{ transaction.property?.address }},
                                    {{ transaction.property?.municipality }}
                                </p>
                            </div>

                            <!-- Client Info -->
                            <div class="mb-4">
                                <div class="flex items-center space-x-2">
                                    <div
                                        class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center"
                                    >
                                        <span
                                            class="text-xs font-medium text-blue-600"
                                        >
                                            {{
                                                transaction.client?.name?.charAt(
                                                    0
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ transaction.client?.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ transaction.client?.email }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Broker Info -->
                            <div class="mb-4">
                                <div class="flex items-center space-x-2">
                                    <div
                                        class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center"
                                    >
                                        <span
                                            class="text-xs font-medium text-green-600"
                                        >
                                            {{
                                                transaction.broker?.name?.charAt(
                                                    0
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ transaction.broker?.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Broker
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <div
                                    class="flex justify-between text-xs text-gray-600 mb-1"
                                >
                                    <span>Progress</span>
                                    <span
                                        >{{
                                            getProgressPercentage(
                                                transaction.status
                                            )
                                        }}%</span
                                    >
                                </div>
                                <div
                                    class="w-full bg-gray-200 rounded-full h-2"
                                >
                                    <div
                                        :class="
                                            transaction.status === 'cancelled'
                                                ? 'bg-red-500'
                                                : 'bg-emerald-500'
                                        "
                                        class="h-2 rounded-full transition-all duration-300"
                                        :style="{
                                            width:
                                                getProgressPercentage(
                                                    transaction.status
                                                ) + '%',
                                        }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Admin Actions -->
                            <div
                                class="flex flex-wrap gap-2 pt-4 border-t border-gray-200"
                            >
                                <Link
                                    :href="
                                        route(
                                            'admin.transactions.show',
                                            transaction.id
                                        )
                                    "
                                    class="flex-1 bg-emerald-600 text-white text-center py-2 px-3 rounded-lg text-xs font-medium hover:bg-emerald-700 transition-colors"
                                >
                                    View Details
                                </Link>
                                <button
                                    @click="viewFinancialDetails(transaction)"
                                    class="flex-1 bg-purple-600 text-white py-2 px-3 rounded-lg text-xs font-medium hover:bg-purple-700 transition-colors"
                                >
                                    Financials
                                </button>
                                <button
                                    @click="addOversightNote(transaction)"
                                    class="flex-1 bg-amber-600 text-white py-2 px-3 rounded-lg text-xs font-medium hover:bg-amber-700 transition-colors"
                                    title="Add administrative oversight note"
                                >
                                    Add Note
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table View -->
                <div
                    v-if="viewMode === 'table' && transactions.data.length > 0"
                    class="overflow-x-auto"
                >
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Transaction
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Property
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Client
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Broker
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Amount
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Sales Value
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Days
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="transaction in transactions.data"
                                :key="transaction.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ transaction.transaction_number }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ formatDate(transaction.created_at) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ transaction.property?.title }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ transaction.property?.municipality }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ transaction.client?.name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ transaction.broker?.name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-semibold text-emerald-600"
                                    >
                                        {{
                                            formatCurrency(
                                                transaction.offered_price ||
                                                    transaction.final_price ||
                                                    0
                                            )
                                        }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-purple-600"
                                    >
                                        {{
                                            formatCurrency(
                                                transaction.final_price ||
                                                    transaction.offered_price ||
                                                    0
                                            )
                                        }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="
                                            getStatusColor(transaction.status)
                                        "
                                        class="px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ statuses[transaction.status] }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ getDaysInProgress(transaction) }}d
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'admin.transactions.show',
                                                transaction.id
                                            )
                                        "
                                        class="text-emerald-600 hover:text-emerald-900 mr-3"
                                    >
                                        View
                                    </Link>
                                    <button
                                        @click="
                                            viewFinancialDetails(transaction)
                                        "
                                        class="text-purple-600 hover:text-purple-900"
                                    >
                                        Details
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">💼</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        No transactions found
                    </h3>
                    <p class="text-gray-500">
                        Try adjusting your search filters or create a new
                        transaction.
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="transactions.data.length > 0" class="mt-6">
                    <Pagination :links="transactions.links" />
                </div>
            </div>
        </div>

        <!-- Financial Details Modal -->
        <div
            v-if="showFinancialModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div
                class="bg-white rounded-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto"
            >
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">
                        Financial Details
                    </h3>
                    <button
                        @click="showFinancialModal = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            ></path>
                        </svg>
                    </button>
                </div>

                <div v-if="selectedTransaction" class="space-y-6">
                    <!-- Transaction Overview -->
                    <div
                        class="bg-gradient-to-r from-emerald-50 to-green-50 rounded-lg p-6"
                    >
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">
                                    Transaction Number
                                </p>
                                <p class="text-lg font-bold text-gray-900">
                                    {{ selectedTransaction.transaction_number }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status</p>
                                <span
                                    :class="
                                        getStatusColor(
                                            selectedTransaction.status
                                        )
                                    "
                                    class="inline-block px-3 py-1 text-sm font-semibold rounded-full mt-1"
                                >
                                    {{ statuses[selectedTransaction.status] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Property & Parties -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-2">Property</p>
                            <p class="font-semibold text-gray-900">
                                {{ selectedTransaction.property?.title }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ selectedTransaction.property?.municipality }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-2">Client</p>
                            <p class="font-semibold text-gray-900">
                                {{ selectedTransaction.client?.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ selectedTransaction.client?.email }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-2">Broker</p>
                            <p class="font-semibold text-gray-900">
                                {{ selectedTransaction.broker?.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ selectedTransaction.broker?.email }}
                            </p>
                        </div>
                    </div>

                    <!-- Financial Breakdown -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">
                            Financial Breakdown
                        </h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600"
                                    >Property Listed Price</span
                                >
                                <span class="font-semibold text-gray-900">{{
                                    formatCurrency(
                                        selectedTransaction.property
                                            ?.total_price || 0
                                    )
                                }}</span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600">Offered Price</span>
                                <span class="font-semibold text-emerald-600">{{
                                    formatCurrency(
                                        selectedTransaction.offered_price || 0
                                    )
                                }}</span>
                            </div>
                            <div
                                v-if="selectedTransaction.final_price"
                                class="flex justify-between items-center py-2"
                            >
                                <span class="text-gray-600"
                                    >Final Negotiated Price</span
                                >
                                <span class="font-bold text-emerald-700">{{
                                    formatCurrency(
                                        selectedTransaction.final_price
                                    )
                                }}</span>
                            </div>

                            <div class="bg-emerald-50 rounded-lg p-4 mt-4">
                                <div class="flex justify-between items-center">
                                    <span
                                        class="text-lg font-bold text-gray-900"
                                        >Total Transaction Value</span
                                    >
                                    <span
                                        class="text-2xl font-bold text-emerald-600"
                                    >
                                        {{
                                            formatCurrency(
                                                selectedTransaction.final_price ||
                                                    selectedTransaction.offered_price ||
                                                    0
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-bold text-gray-900 mb-4">
                            Transaction Timeline
                        </h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Inquiry Date</span>
                                <span class="font-medium text-gray-900">{{
                                    formatDate(
                                        selectedTransaction.inquiry_date ||
                                            selectedTransaction.created_at
                                    )
                                }}</span>
                            </div>
                            <div
                                v-if="selectedTransaction.offer_date"
                                class="flex justify-between"
                            >
                                <span class="text-gray-600">Offer Made</span>
                                <span class="font-medium text-gray-900">{{
                                    formatDate(selectedTransaction.offer_date)
                                }}</span>
                            </div>
                            <div
                                v-if="selectedTransaction.contract_date"
                                class="flex justify-between"
                            >
                                <span class="text-gray-600"
                                    >Contract Signed</span
                                >
                                <span class="font-medium text-gray-900">{{
                                    formatDate(
                                        selectedTransaction.contract_date
                                    )
                                }}</span>
                            </div>
                            <div
                                v-if="selectedTransaction.finalized_date"
                                class="flex justify-between"
                            >
                                <span class="text-gray-600">Finalized</span>
                                <span class="font-medium text-gray-900">{{
                                    formatDate(
                                        selectedTransaction.finalized_date
                                    )
                                }}</span>
                            </div>
                            <div
                                class="flex justify-between pt-2 border-t border-gray-200"
                            >
                                <span class="text-gray-600 font-semibold"
                                    >Total Duration</span
                                >
                                <span class="font-bold text-emerald-600"
                                    >{{
                                        getDaysInProgress(selectedTransaction)
                                    }}
                                    days</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex justify-end space-x-3 pt-4 border-t border-gray-200"
                    >
                        <button
                            @click="showFinancialModal = false"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium"
                        >
                            Close
                        </button>
                        <Link
                            :href="
                                route(
                                    'admin.transactions.show',
                                    selectedTransaction.id
                                )
                            "
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium"
                        >
                            View Full Details
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Oversight Note Modal -->
        <div
            v-if="showOversightNoteModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl p-6 w-full max-w-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900">
                        Add Administrative Oversight Note
                    </h3>
                    <button
                        @click="showOversightNoteModal = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            ></path>
                        </svg>
                    </button>
                </div>

                <div v-if="selectedTransaction" class="space-y-4">
                    <!-- Info Alert -->
                    <div
                        class="bg-amber-50 border border-amber-200 rounded-lg p-4"
                    >
                        <div class="flex gap-3">
                            <svg
                                class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <div class="text-sm text-amber-800">
                                <p class="font-medium mb-1">
                                    Administrative Oversight
                                </p>
                                <p>
                                    This note is for monitoring purposes only.
                                    You cannot modify transaction data. Notes
                                    will be visible to the broker.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Reference -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-600 mb-1">Transaction</p>
                        <p class="font-semibold text-gray-900">
                            {{ selectedTransaction.transaction_number }}
                        </p>
                        <p class="text-sm text-gray-600 mt-2">
                            {{ selectedTransaction.property?.title }}
                        </p>
                    </div>

                    <!-- Oversight Note Textarea -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Oversight Note <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="oversightNoteForm.oversight_note"
                            rows="5"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                            placeholder="Enter your administrative oversight note here..."
                            required
                        ></textarea>
                    </div>

                    <!-- Flag for Review Checkbox -->
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="flag_for_review"
                            v-model="oversightNoteForm.flag_for_review"
                            class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500"
                        />
                        <label
                            for="flag_for_review"
                            class="text-sm text-gray-700"
                        >
                            Flag this transaction for administrative review
                        </label>
                    </div>

                    <!-- Action Buttons -->
                    <div
                        class="flex justify-end space-x-3 pt-4 border-t border-gray-200"
                    >
                        <button
                            @click="showOversightNoteModal = false"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitOversightNote"
                            :disabled="!oversightNoteForm.oversight_note"
                            class="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Add Note
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
