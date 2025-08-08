<script setup>
import { ref, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    transactions: Object,
    filters: Object,
    properties: Array,
    statuses: Object,
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
    <AppLayout>
        <Head title="Transactions" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                Transactions
                            </h1>
                            <p class="mt-2 text-gray-600">
                                Manage property transactions and deals
                            </p>
                        </div>
                        <Link
                            :href="route('transactions.create')"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                        >
                            New Transaction
                        </Link>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4"
                    >
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Search</label
                            >
                            <input
                                v-model="search"
                                @keyup.enter="applyFilters"
                                type="text"
                                placeholder="Transaction number, property, client..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Status</label
                            >
                            <select
                                v-model="selectedStatus"
                                @change="applyFilters"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Property</label
                            >
                            <select
                                v-model="selectedProperty"
                                @change="applyFilters"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">All Properties</option>
                                <option
                                    v-for="property in properties"
                                    :key="property.id"
                                    :value="property.id"
                                >
                                    {{ property.title }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Date From</label
                            >
                            <input
                                v-model="dateFrom"
                                @change="applyFilters"
                                type="date"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Date To</label
                            >
                            <input
                                v-model="dateTo"
                                @change="applyFilters"
                                type="date"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end mt-4 space-x-2">
                        <button
                            @click="clearFilters"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors"
                        >
                            Clear Filters
                        </button>
                        <button
                            @click="applyFilters"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>

                <!-- Transactions Grid -->
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
                <div
                    v-else
                    class="bg-white rounded-lg shadow-md p-12 text-center"
                >
                    <svg
                        class="w-16 h-16 mx-auto text-gray-400 mb-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        ></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        No transactions found
                    </h3>
                    <p class="text-gray-600 mb-4">
                        Get started by creating your first transaction.
                    </p>
                    <Link
                        :href="route('transactions.create')"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                    >
                        Create Transaction
                    </Link>
                </div>

                <!-- Pagination -->
                <div
                    v-if="transactions.links && transactions.links.length > 3"
                    class="mt-6"
                >
                    <nav class="flex justify-center">
                        <div class="flex space-x-1">
                            <Link
                                v-for="link in transactions.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-2 text-sm rounded-lg transition-colors',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : link.url
                                        ? 'text-gray-700 hover:bg-gray-100'
                                        : 'text-gray-400 cursor-not-allowed',
                                ]"
                                >{{ link.label }}</Link
                            >
                            v-for="link in transactions.links" :key="link.label"
                            :href="link.url" >{{ link.label }}
                            :class="[ 'px-3 py-2 text-sm rounded-lg
                            transition-colors', link.active ? 'bg-blue-600
                            text-white' : link.url ? 'text-gray-700
                            hover:bg-gray-100' : 'text-gray-400
                            cursor-not-allowed' ]" />
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
