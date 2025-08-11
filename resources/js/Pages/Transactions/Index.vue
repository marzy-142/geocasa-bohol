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
    statuses: Object,
    canCreate: Boolean,
});

const search = ref(props.filters.search || "");
const selectedStatus = ref(props.filters.status || "");
const selectedProperty = ref(props.filters.property_id || "");
const dateFrom = ref(props.filters.date_from || "");
const dateTo = ref(props.filters.date_to || "");

const applyFilters = () => {
    router.get(
        route("transactions.index"),
        {
            search: search.value,
            status: selectedStatus.value,
            property_id: selectedProperty.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
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
    dateFrom.value = "";
    dateTo.value = "";
    applyFilters();
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

const deleteTransaction = (transaction) => {
    if (confirm("Are you sure you want to delete this transaction?")) {
        router.delete(route("transactions.destroy", transaction.id));
    }
};
</script>

<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header Section -->
            <div
                class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg p-6 text-white"
            >
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold">
                            Transaction Management
                        </h1>
                        <p class="text-green-100 mt-2">
                            Manage property transactions in GeoCasa Bohol
                        </p>
                    </div>
                    <Link
                        v-if="canCreate"
                        :href="route('transactions.create')"
                        class="bg-white text-green-600 hover:bg-green-50 font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-lg"
                    >
                        <span class="flex items-center">
                            <svg
                                class="w-5 h-5 mr-2"
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

            <!-- Filters Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Search & Filter Transactions
                </h3>

                <!-- Primary Filters -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4"
                >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search transactions..."
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        @input="applyFilters"
                    />
                    <select
                        v-model="selectedStatus"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        @change="applyFilters"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <select
                        v-model="selectedProperty"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        @change="applyFilters"
                    >
                        <option value="">All Properties</option>
                        <option
                            v-for="property in properties"
                            :key="property.id"
                            :value="property.id"
                        >
                            {{ property.title }} - {{ property.municipality }}
                        </option>
                    </select>
                    <input
                        v-model="dateFrom"
                        type="date"
                        placeholder="Date From"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        @change="applyFilters"
                    />
                </div>

                <!-- Clear Filters -->
                <div class="mt-4">
                    <button
                        @click="clearFilters"
                        class="text-sm text-gray-600 hover:text-gray-800 font-medium"
                    >
                        Clear all filters
                    </button>
                </div>
            </div>

            <!-- Transactions Grid -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Transactions ({{ transactions.total || 0 }})
                    </h3>
                </div>

                <div
                    v-if="transactions.data.length > 0"
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
                                        {{
                                            transaction.formatted_offered_price
                                        }}
                                    </p>
                                    <p
                                        v-if="transaction.final_price"
                                        class="text-sm text-gray-500"
                                    >
                                        Final:
                                        {{ transaction.formatted_final_price }}
                                    </p>
                                </div>
                            </div>

                            <!-- Property & Client Info -->
                            <div class="space-y-2 mb-4">
                                <div
                                    class="flex items-center text-sm text-gray-600"
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
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                        ></path>
                                    </svg>
                                    <span class="font-medium">{{
                                        transaction.property.title
                                    }}</span>
                                </div>
                                <div
                                    class="flex items-center text-sm text-gray-600"
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
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        ></path>
                                    </svg>
                                    <span>{{ transaction.client.name }}</span>
                                </div>
                                <div
                                    class="flex items-center text-sm text-gray-600"
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
                                            d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a2 2 0 012 2v1H6V9a2 2 0 012-2h3z"
                                        ></path>
                                    </svg>
                                    <span>{{ transaction.broker.name }}</span>
                                </div>
                            </div>

                            <!-- Timeline Info -->
                            <div class="text-xs text-gray-500 mb-4">
                                <p>
                                    Started:
                                    {{
                                        new Date(
                                            transaction.inquiry_date
                                        ).toLocaleDateString()
                                    }}
                                </p>
                                <p v-if="transaction.finalized_date">
                                    Finalized:
                                    {{
                                        new Date(
                                            transaction.finalized_date
                                        ).toLocaleDateString()
                                    }}
                                </p>
                                <p>
                                    {{ transaction.days_in_progress }} days in
                                    progress
                                </p>
                            </div>

                            <!-- Commission Info -->
                            <div
                                v-if="transaction.commission_amount"
                                class="bg-gray-50 rounded-lg p-3 mb-4"
                            >
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600"
                                        >Commission ({{
                                            (
                                                transaction.commission_rate *
                                                100
                                            ).toFixed(2)
                                        }}%)</span
                                    >
                                    <span class="font-medium text-gray-900">{{
                                        transaction.formatted_commission
                                    }}</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-between items-center">
                                <Link
                                    :href="
                                        route(
                                            'transactions.show',
                                            transaction.id
                                        )
                                    "
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    View Details
                                </Link>
                                <div class="flex space-x-2">
                                    <Link
                                        :href="
                                            route(
                                                'transactions.edit',
                                                transaction.id
                                            )
                                        "
                                        class="text-gray-600 hover:text-gray-800 text-sm"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteTransaction(transaction)"
                                        class="text-red-600 hover:text-red-800 text-sm"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
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
    </ModernDashboardLayout>
</template>
