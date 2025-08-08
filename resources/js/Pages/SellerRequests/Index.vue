<script setup>
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    sellerRequests: Object,
    brokers: Array,
    filters: Object,
    canManage: Boolean,
});

const searchForm = useForm({
    search: props.filters.search || "",
    status: props.filters.status || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
    property_type: props.filters.property_type || "",
});

const search = () => {
    searchForm.get(route("seller-requests.index"), {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchForm.reset();
    searchForm.get(route("seller-requests.index"));
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(price);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getStatusColor = (status) => {
    const colors = {
        pending: "bg-yellow-100 text-yellow-800",
        under_review: "bg-blue-100 text-blue-800",
        approved: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
        listed: "bg-emerald-100 text-emerald-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const deleteRequest = (request) => {
    if (confirm("Are you sure you want to delete this seller request?")) {
        useForm({}).delete(route("seller-requests.destroy", request.id));
    }
};
</script>

<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6"
                >
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2
                                    class="text-2xl font-semibold text-gray-900"
                                >
                                    Seller Requests
                                </h2>
                                <p class="mt-2 text-sm text-gray-600">
                                    Manage property listing requests from
                                    sellers
                                </p>
                            </div>
                            <div class="text-sm text-gray-500">
                                Total: {{ sellerRequests.total }} requests
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6"
                >
                    <div class="p-6">
                        <form
                            @submit.prevent="search"
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4"
                        >
                            <!-- Search -->
                            <div>
                                <label
                                    for="search"
                                    class="block text-sm font-medium text-gray-700"
                                    >Search</label
                                >
                                <input
                                    v-model="searchForm.search"
                                    type="text"
                                    id="search"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Name, email, property..."
                                />
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label
                                    for="status"
                                    class="block text-sm font-medium text-gray-700"
                                    >Status</label
                                >
                                <select
                                    v-model="searchForm.status"
                                    id="status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="under_review">
                                        Under Review
                                    </option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="listed">Listed</option>
                                </select>
                            </div>

                            <!-- Property Type Filter -->
                            <div>
                                <label
                                    for="property_type"
                                    class="block text-sm font-medium text-gray-700"
                                    >Property Type</label
                                >
                                <select
                                    v-model="searchForm.property_type"
                                    id="property_type"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All Types</option>
                                    <option value="residential">
                                        Residential
                                    </option>
                                    <option value="commercial">
                                        Commercial
                                    </option>
                                    <option value="agricultural">
                                        Agricultural
                                    </option>
                                    <option value="industrial">
                                        Industrial
                                    </option>
                                    <option value="recreational">
                                        Recreational
                                    </option>
                                </select>
                            </div>

                            <!-- Date From -->
                            <div>
                                <label
                                    for="date_from"
                                    class="block text-sm font-medium text-gray-700"
                                    >From Date</label
                                >
                                <input
                                    v-model="searchForm.date_from"
                                    type="date"
                                    id="date_from"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>

                            <!-- Date To -->
                            <div>
                                <label
                                    for="date_to"
                                    class="block text-sm font-medium text-gray-700"
                                    >To Date</label
                                >
                                <input
                                    v-model="searchForm.date_to"
                                    type="date"
                                    id="date_to"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>

                            <!-- Filter Buttons -->
                            <div class="lg:col-span-5 flex items-end space-x-2">
                                <button
                                    type="submit"
                                    :disabled="searchForm.processing"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    <span v-if="searchForm.processing"
                                        >Searching...</span
                                    >
                                    <span v-else>Search</span>
                                </button>
                                <button
                                    type="button"
                                    @click="clearFilters"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                                >
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Seller & Property
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Details
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Assignment
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Date
                                    </th>
                                    <th
                                        v-if="canManage"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="request in sellerRequests.data"
                                    :key="request.id"
                                    class="hover:bg-gray-50"
                                >
                                    <!-- Seller & Property -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ request.seller_name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ request.seller_email }}
                                            </div>
                                            <div
                                                class="text-sm font-medium text-gray-700 mt-1"
                                            >
                                                {{ request.property_title }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ request.property_location }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Details -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm text-gray-900">
                                                {{
                                                    formatPrice(
                                                        request.asking_price
                                                    )
                                                }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ request.property_area }}
                                                {{ request.area_unit }}
                                            </div>
                                            <div
                                                class="text-sm text-gray-500 capitalize"
                                            >
                                                {{ request.property_type }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="
                                                getStatusColor(request.status)
                                            "
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full capitalize"
                                        >
                                            {{
                                                request.status.replace("_", " ")
                                            }}
                                        </span>
                                    </td>

                                    <!-- Assignment -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            v-if="request.assigned_broker"
                                            class="text-sm text-gray-900"
                                        >
                                            {{ request.assigned_broker.name }}
                                        </div>
                                        <div
                                            v-else
                                            class="text-sm text-gray-500"
                                        >
                                            Unassigned
                                        </div>
                                        <div
                                            v-if="request.reviewed_by"
                                            class="text-sm text-gray-500"
                                        >
                                            Reviewed by
                                            {{ request.reviewed_by.name }}
                                        </div>
                                    </td>

                                    <!-- Date -->
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    >
                                        <div>
                                            {{ formatDate(request.created_at) }}
                                        </div>
                                        <div
                                            v-if="request.reviewed_at"
                                            class="text-xs"
                                        >
                                            Reviewed:
                                            {{
                                                formatDate(request.reviewed_at)
                                            }}
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td
                                        v-if="canManage"
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2"
                                    >
                                        <a
                                            :href="
                                                route(
                                                    'seller-requests.show',
                                                    request.id
                                                )
                                            "
                                            class="text-indigo-600 hover:text-indigo-900"
                                            >View</a
                                        >
                                        <a
                                            :href="
                                                route(
                                                    'seller-requests.edit',
                                                    request.id
                                                )
                                            "
                                            class="text-blue-600 hover:text-blue-900"
                                            >Edit</a
                                        >
                                        <button
                                            v-if="
                                                $page.props.auth.user.role ===
                                                'admin'
                                            "
                                            @click="deleteRequest(request)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="sellerRequests.links"
                        class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <a
                                    v-if="sellerRequests.prev_page_url"
                                    :href="sellerRequests.prev_page_url"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Previous
                                </a>
                                <a
                                    v-if="sellerRequests.next_page_url"
                                    :href="sellerRequests.next_page_url"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Next
                                </a>
                            </div>
                            <div
                                class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Showing {{ sellerRequests.from }} to
                                        {{ sellerRequests.to }} of
                                        {{ sellerRequests.total }} results
                                    </p>
                                </div>
                                <div>
                                    <nav
                                        class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                    >
                                        <template
                                            v-for="link in sellerRequests.links"
                                            :key="link.label"
                                        >
                                            <a
                                                v-if="link.url"
                                                :href="link.url"
                                                :class="[
                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                    link.active
                                                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                ]"
                                                v-html="link.label"
                                            >
                                            </a>
                                            <span
                                                v-else
                                                :class="[
                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                    'bg-white border-gray-300 text-gray-300 cursor-default',
                                                ]"
                                                v-html="link.label"
                                            >
                                            </span>
                                        </template>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="sellerRequests.data.length === 0"
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <div class="p-6 text-center">
                        <div class="text-gray-500">
                            <svg
                                class="mx-auto h-12 w-12 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                No seller requests found
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                No requests match your current filters.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
