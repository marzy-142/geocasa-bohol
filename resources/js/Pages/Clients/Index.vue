<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-3xl font-bold text-gray-900">
                                    Client Management
                                </h2>
                                <p class="text-gray-600">
                                    Manage your clients in GeoCasa Bohol
                                </p>
                            </div>
                            <Link
                                v-if="canCreateClient"
                                :href="route('clients.create')"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Add New Client
                            </Link>
                        </div>

                        <!-- Advanced Filters -->
                        <div
                            class="mb-6 grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4"
                        >
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Search clients..."
                                class="border border-gray-300 rounded-md px-3 py-2"
                                @input="filterClients"
                            />
                            <select
                                v-model="filters.status"
                                class="border border-gray-300 rounded-md px-3 py-2"
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
                                class="border border-gray-300 rounded-md px-3 py-2"
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
                                class="border border-gray-300 rounded-md px-3 py-2"
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
                                class="border border-gray-300 rounded-md px-3 py-2"
                                @input="filterClients"
                            />
                            <input
                                v-model="filters.max_budget"
                                type="number"
                                placeholder="Max Budget (₱)"
                                class="border border-gray-300 rounded-md px-3 py-2"
                                @input="filterClients"
                            />
                        </div>

                        <!-- Clients Grid -->
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                        >
                            <div
                                v-for="client in clients.data"
                                :key="client.id"
                                class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow border"
                            >
                                <div class="p-4">
                                    <div
                                        class="flex justify-between items-start mb-3"
                                    >
                                        <div>
                                            <h3
                                                class="text-lg font-semibold text-gray-900"
                                            >
                                                {{ client.name }}
                                            </h3>
                                            <p class="text-gray-600 text-sm">
                                                📧 {{ client.email }}
                                            </p>
                                            <p class="text-gray-600 text-sm">
                                                📱 {{ client.phone }}
                                            </p>
                                        </div>
                                        <div
                                            class="px-2 py-1 rounded text-xs font-bold text-white"
                                            :class="
                                                getStatusColor(client.status)
                                            "
                                        >
                                            {{ formatStatus(client.status) }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <p class="text-sm text-gray-600 mb-1">
                                            📍
                                            {{
                                                client.preferred_location ||
                                                "Any location"
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600 mb-1">
                                            💰
                                            {{ client.formatted_budget_range }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            📏
                                            {{
                                                client.formatted_preferred_area
                                            }}
                                        </p>
                                    </div>

                                    <!-- Preferred Features -->
                                    <div
                                        v-if="client.preferred_features"
                                        class="mb-3"
                                    >
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="feature in client.preferred_features.split(
                                                    ','
                                                )"
                                                :key="feature"
                                                class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"
                                            >
                                                {{ feature.trim() }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Client Info -->
                                    <div
                                        class="flex justify-between text-xs text-gray-500 mb-3"
                                    >
                                        <span
                                            >🏢
                                            {{
                                                formatSource(client.source)
                                            }}</span
                                        >
                                        <span
                                            >👤
                                            {{
                                                client.broker?.name ||
                                                "Unassigned"
                                            }}</span
                                        >
                                    </div>

                                    <!-- Activity Stats -->
                                    <div
                                        class="flex justify-between text-xs text-gray-500 mb-4"
                                    >
                                        <span
                                            >📞
                                            {{ client.inquiries_count || 0 }}
                                            inquiries</span
                                        >
                                        <span
                                            >🏠
                                            {{ client.transactions_count || 0 }}
                                            transactions</span
                                        >
                                    </div>

                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <Link
                                            :href="
                                                route('clients.show', client.id)
                                            "
                                            class="text-blue-500 hover:text-blue-700 font-medium"
                                        >
                                            View Details
                                        </Link>
                                        <div
                                            v-if="canEditClient(client)"
                                            class="flex space-x-2"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'clients.edit',
                                                        client.id
                                                    )
                                                "
                                                class="text-green-500 hover:text-green-700 text-sm"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                @click="deleteClient(client)"
                                                class="text-red-500 hover:text-red-700 text-sm"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No Results -->
                        <div
                            v-if="clients.data.length === 0"
                            class="text-center py-12"
                        >
                            <div class="text-gray-500 text-lg mb-4">👥</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                No clients found
                            </h3>
                            <p class="text-gray-500">
                                Try adjusting your search filters or add a new
                                client.
                            </p>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="clients.links && clients.data.length > 0"
                            class="mt-6"
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
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
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
