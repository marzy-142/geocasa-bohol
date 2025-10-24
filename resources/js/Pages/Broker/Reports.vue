<template>
    <ModernDashboardLayout>
        <div class="p-6">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Reports Dashboard
                </h1>
                <p class="mt-2 text-gray-600">
                    Download and view detailed reports of your performance
                </p>
            </div>

            <!-- Summary Cards -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
            >
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <BuildingOfficeIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Total Properties
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ reportData.summary.total_properties }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <DocumentTextIcon class="h-6 w-6 text-green-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Total Inquiries
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ reportData.summary.total_inquiries }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <ChartBarIcon class="h-6 w-6 text-purple-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Total Transactions
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ reportData.summary.total_transactions }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <CurrencyDollarIcon
                                class="h-6 w-6 text-yellow-600"
                            />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Total Commission
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{
                                    formatPrice(
                                        reportData.summary.total_commission
                                    )
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Recent Transactions
                    </h2>
                    <p class="text-sm text-gray-600">
                        Your latest 20 transactions
                    </p>
                </div>
                <div class="overflow-x-auto">
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
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="transaction in reportData.recent_transactions"
                                :key="transaction.id"
                            >
                                <td
                                    class="px-6 integrated py-4 whitespace-nowrap"
                                >
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        #{{ transaction.transaction_number }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{
                                            transaction.property?.title || "N/A"
                                        }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ transaction.client?.name || "N/A" }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="
                                            getStatusColor(transaction.status)
                                        "
                                    >
                                        {{ formatStatus(transaction.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ formatDate(transaction.created_at) }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Property Performance -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Property Performance
                    </h2>
                    <p class="text-sm text-gray-600">
                        Properties ranked by inquiry count
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Property
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Price
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Inquiries
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Listed Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="property in reportData.property_performance"
                                :key="property.id"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ property.title }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ formatPrice(property.total_price) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
                                    >
                                        {{ property.inquiries_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="
                                            getPropertyStatusColor(
                                                property.status
                                            )
                                        "
                                    >
                                        {{ formatStatus(property.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ formatDate(property.created_at) }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    BuildingOfficeIcon,
    DocumentTextIcon,
    ChartBarIcon,
    CurrencyDollarIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    reportData: Object,
});

const formatNumber = (number) => {
    if (!number) return "0";
    return new Intl.NumberFormat("en-PH").format(Math.round(number));
};

const formatPrice = (price) => {
    if (!price || price === 0) return "₱0.00";
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(price);
};

const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const formatStatus = (status) => {
    if (!status) return "N/A";
    return status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
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
        closing_preparation: "bg-violet-100 text-violet-800",
        finalized: "bg-emerald-100 text-emerald-800",
        cancelled: "bg-red-100 text-red-800",
        completed: "bg-emerald-100 text-emerald-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const getPropertyStatusColor = (status) => {
    const colors = {
        available: "bg-green-100 text-green-800",
        pending: "bg-yellow-100 text-yellow-800",
        sold: "bg-blue-100 text-blue-800",
        inactive: "bg-gray-100 text-gray-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};
</script>
