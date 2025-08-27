<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header Section -->
            <div
                class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg p-6 text-white"
            >
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold">Client Management</h1>
                        <p class="text-purple-100 mt-2">
                            Manage your clients in GeoCasa Bohol
                        </p>
                    </div>
                    <Link
                        v-if="canCreateClient"
                        :href="route('clients.create')"
                        class="bg-white text-purple-600 hover:bg-purple-50 font-semibold py-3 px-6 rounded-lg transition-colors duration-200 shadow-lg"
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
                            Add New Client
                        </span>
                    </Link>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Search & Filter Clients
                </h3>

                <!-- Primary Filters -->
                <div
                    class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-4"
                >
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search clients..."
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                        @input="filterClients"
                    />
                    <select
                        v-model="filters.status"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                        @change="filterClients"
                    >
                        <option value="">All Statuses</option>
                        <option
                            v-for="status in statuses"
                            :key="status"
                            :value="status"
                        >
                            {{ formatStatus(status) }}
                        </option>
                    </select>
                    <select
                        v-model="filters.source"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                        @change="filterClients"
                    >
                        <option value="">All Sources</option>
                        <option
                            v-for="source in sources"
                            :key="source"
                            :value="source"
                        >
                            {{ formatSource(source) }}
                        </option>
                    </select>
                    <select
                        v-model="filters.preferred_location"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                        @change="filterClients"
                    >
                        <option value="">All Locations</option>
                        <option
                            v-for="municipality in municipalities"
                            :key="municipality"
                            :value="municipality"
                        >
                            {{ municipality }}
                        </option>
                    </select>
                    <input
                        v-model="filters.min_budget"
                        type="number"
                        placeholder="Min Budget (₱)"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                        @input="filterClients"
                    />
                    <input
                        v-model="filters.max_budget"
                        type="number"
                        placeholder="Max Budget (₱)"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
                        @input="filterClients"
                    />
                </div>
            </div>

            <!-- Clients Grid -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <div
                        v-for="client in clients.data"
                        :key="client.id"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-purple-200 transition-all duration-200 overflow-hidden"
                    >
                        <div class="p-6">
                            <!-- Client Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 mb-2"
                                    >
                                        {{ client.name }}
                                    </h3>
                                    <div class="space-y-1">
                                        <p
                                            class="text-sm text-gray-600 flex items-center"
                                        >
                                            <svg
                                                class="w-4 h-4 mr-2 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                                                ></path>
                                            </svg>
                                            {{ client.email }}
                                        </p>
                                        <p
                                            class="text-sm text-gray-600 flex items-center"
                                        >
                                            <svg
                                                class="w-4 h-4 mr-2 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                                ></path>
                                            </svg>
                                            {{ client.phone }}
                                        </p>
                                    </div>
                                </div>
                                <div
                                    class="px-3 py-1 rounded-full text-xs font-semibold text-white"
                                    :class="getStatusColor(client.status)"
                                >
                                    {{ formatStatus(client.status) }}
                                </div>
                            </div>

                            <!-- Client Details -->
                            <div class="space-y-3 mb-4">
                                <div
                                    class="flex items-center text-sm text-gray-600"
                                >
                                    <svg
                                        class="w-4 h-4 mr-2 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        ></path>
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                        ></path>
                                    </svg>
                                    {{
                                        client.preferred_location ||
                                        "Any location"
                                    }}
                                </div>
                                <div
                                    class="flex items-center text-sm text-gray-600"
                                >
                                    <svg
                                        class="w-4 h-4 mr-2 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                                        ></path>
                                    </svg>
                                    {{ client.formatted_budget_range }}
                                </div>
                                <div
                                    class="flex items-center text-sm text-gray-600"
                                >
                                    <svg
                                        class="w-4 h-4 mr-2 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"
                                        ></path>
                                    </svg>
                                    {{ client.formatted_preferred_area }}
                                </div>
                            </div>

                            <!-- Preferred Features -->
                            <div v-if="client.preferred_features" class="mb-4">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="feature in client.preferred_features.split(
                                            ','
                                        )"
                                        :key="feature"
                                        class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full font-medium"
                                    >
                                        {{ feature.trim() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Client Stats -->
                            <div
                                class="grid grid-cols-2 gap-4 mb-4 p-3 bg-gray-50 rounded-lg"
                            >
                                <div class="text-center">
                                    <div
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ client.inquiries_count || 0 }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Inquiries
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ client.transactions_count || 0 }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Transactions
                                    </div>
                                </div>
                            </div>

                            <!-- Client Meta -->
                            <div
                                class="flex justify-between text-xs text-gray-500 mb-4"
                            >
                                <span class="flex items-center">
                                    <svg
                                        class="w-3 h-3 mr-1"
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
                                    {{ formatSource(client.source) }}
                                </span>
                                <span class="flex items-center">
                                    <svg
                                        class="w-3 h-3 mr-1"
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
                                    {{ client.broker?.name || "Unassigned" }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div
                                class="flex justify-between items-center pt-4 border-t border-gray-100"
                            >
                                <Link
                                    :href="route('clients.show', client.id)"
                                    class="text-purple-600 hover:text-purple-800 font-medium text-sm transition-colors duration-200"
                                >
                                    View Details →
                                </Link>
                                <div
                                    v-if="canEditClient(client)"
                                    class="flex space-x-3"
                                >
                                    <Link
                                        :href="route('clients.edit', client.id)"
                                        class="text-green-600 hover:text-green-800 text-sm font-medium transition-colors duration-200"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteClient(client)"
                                        class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors duration-200"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Empty State -->
                <div v-if="clients.data.length === 0" class="text-center py-16">
                    <div
                        class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6"
                    >
                        <svg
                            class="w-12 h-12 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                            ></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        No clients found
                    </h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        Try adjusting your search filters or add a new client to
                        get started.
                    </p>
                    <Link
                        v-if="canCreateClient"
                        :href="route('clients.create')"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors duration-200"
                    >
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
                        Add Your First Client
                    </Link>
                </div>

                <!-- Pagination -->
                <div
                    v-if="clients.links && clients.data.length > 0"
                    class="mt-8"
                >
                    <Pagination
                        :links="clients.links"
                        :from="clients.from"
                        :to="clients.to"
                        :total="clients.total"
                    />
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import Pagination from "@/Components/Pagination.vue";
import { debounce } from "lodash";

const props = defineProps({
    clients: Object,
    filters: Object,
    statuses: Array,
    sources: Array,
    municipalities: Array,
});

const page = usePage();
const filters = ref({ ...props.filters });

const canCreateClient = computed(() => {
    const user = page.props.auth.user;
    return ["admin", "broker"].includes(user.role) && user.is_approved;
});

const canEditClient = (client) => {
    const user = page.props.auth.user;
    return (
        user.role === "admin" ||
        (user.role === "broker" &&
            user.is_approved &&
            client.broker_id === user.id)
    );
};

const getStatusColor = (status) => {
    const colors = {
        active: "bg-green-500",
        inactive: "bg-gray-500",
        converted: "bg-blue-500",
    };
    return colors[status] || "bg-gray-500";
};

const formatStatus = (status) => {
    return status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatSource = (source) => {
    return source.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const filterClients = debounce(() => {
    router.get(route("clients.index"), filters.value, {
        preserveState: true,
        replace: true,
    });
}, 300);

const deleteClient = (client) => {
    if (confirm("Are you sure you want to delete this client?")) {
        router.delete(route("clients.destroy", client.id));
    }
};
</script>
