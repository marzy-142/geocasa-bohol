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
                </div>
            </div>

            <!-- Unified Search & Filter -->
            <UnifiedSearchFilter
                title="Search Clients"
                :search="filters.search"
                search-placeholder="Search by name, email, or phone..."
                :filters="filters"
                :result-count="clients.total"
                :primary-filters="primaryFilters"
                :secondary-filters="secondaryFilters"
                @search-change="handleSearchChange"
                @filter-change="handleFilterChange"
                @clear-filters="clearAllFilters"
            />

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
                                        "No location preference"
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
                                    {{ client.formatted_budget }}
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
                                class="flex justify-end text-xs text-gray-500 mb-4"
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
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        ></path>
                                    </svg>
                                    <span
                                        :class="
                                            client.broker
                                                ? 'text-green-600'
                                                : 'text-orange-600'
                                        "
                                    >
                                        {{
                                            client.broker?.name || "Unassigned"
                                        }}
                                    </span>
                                    <button
                                        v-if="isAdmin"
                                        @click="showAssignBrokerModal(client)"
                                        class="ml-2 text-xs text-blue-600 hover:text-blue-800 font-medium"
                                        title="Assign/Reassign Broker"
                                    >
                                        {{
                                            client.broker
                                                ? "Reassign"
                                                : "Assign"
                                        }}
                                    </button>
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
                        Try adjusting your search filters to find clients.
                    </p>
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

        <!-- Assign Broker Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
            @click="closeModal"
        >
            <div
                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
                @click.stop
            >
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ selectedClient?.broker ? "Reassign" : "Assign" }}
                            Broker
                        </h3>
                        <button
                            @click="closeModal"
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

                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">
                            Client:
                            <span class="font-medium">{{
                                selectedClient?.name
                            }}</span>
                        </p>
                        <p
                            v-if="selectedClient?.broker"
                            class="text-sm text-gray-600 mb-4"
                        >
                            Current Broker:
                            <span class="font-medium text-green-600">{{
                                selectedClient.broker.name
                            }}</span>
                        </p>
                    </div>

                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Select Broker
                        </label>
                        <select
                            v-model="selectedBroker"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        >
                            <option value="">Unassign (No Broker)</option>
                            <option
                                v-for="broker in brokers"
                                :key="broker.id"
                                :value="broker.id"
                            >
                                {{ broker.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="assignBroker"
                            :disabled="isAssigning"
                            class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors"
                        >
                            {{
                                isAssigning
                                    ? "Assigning..."
                                    : selectedClient?.broker
                                    ? "Reassign"
                                    : "Assign"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import UnifiedSearchFilter from "@/Components/UnifiedSearchFilter.vue";
import Pagination from "@/Components/Pagination.vue";
import { debounce } from "lodash";

const props = defineProps({
    clients: Object,
    filters: Object,
    statuses: Array,
    municipalities: Array,
    brokers: Array,
    can: Object,
});

const page = usePage();
const filters = ref({ ...props.filters });
const showModal = ref(false);
const selectedClient = ref(null);
const selectedBroker = ref("");
const isAssigning = ref(false);

const isAdmin = computed(() => {
    const user = page.props.auth.user;
    return user.role === "admin";
});

const getStatusColor = (status) => {
    const colors = {
        active: "bg-green-500",
        inactive: "bg-gray-500",
        converted: "bg-blue-500", // Keep same color but now shows as "Purchased"
    };
    return colors[status] || "bg-gray-500";
};

const formatStatus = (status) => {
    const statusLabels = {
        active: "Active",
        inactive: "Inactive",
        converted: "Purchased", // Much clearer than "Converted"
    };

    return (
        statusLabels[status] ||
        status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase())
    );
};

// Filter configurations for UnifiedSearchFilter
const primaryFilters = computed(() => [
    {
        key: "status",
        label: "Status",
        type: "select",
        span: 6,
        options: props.statuses.map((status) => ({
            value: status,
            label: formatStatus(status),
        })),
    },
]);

const secondaryFilters = computed(() => [
    {
        key: "budget_min",
        label: "Min Budget",
        type: "number",
        placeholder: "Minimum budget (₱)",
    },
    {
        key: "budget_max",
        label: "Max Budget",
        type: "number",
        placeholder: "Maximum budget (₱)",
    },
    ...(isAdmin.value
        ? [
              {
                  key: "broker_id",
                  label: "Assigned Broker",
                  type: "select",
                  options: props.brokers.map((broker) => ({
                      value: broker.id,
                      label: broker.name,
                  })),
              },
          ]
        : []),
    {
        key: "preferred_location",
        label: "Preferred Location",
        type: "select",
        options: props.municipalities.map((city) => ({
            value: city,
            label: city,
        })),
    },
]);

// Event handlers for UnifiedSearchFilter
const handleSearchChange = (value) => {
    filters.value.search = value;
    filterClients();
};

const handleFilterChange = (key, value) => {
    filters.value[key] = value;
    filterClients();
};

const clearAllFilters = () => {
    Object.keys(filters.value).forEach((key) => {
        filters.value[key] = "";
    });
    filterClients();
};

const filterClients = debounce(() => {
    router.get(route("clients.index"), filters.value, {
        preserveState: true,
        replace: true,
    });
}, 300);

const showAssignBrokerModal = (client) => {
    selectedClient.value = client;
    selectedBroker.value = client.broker_id || "";
    showModal.value = true;
};

const assignBroker = () => {
    if (!selectedClient.value) return;

    isAssigning.value = true;

    const data = {
        broker_id: selectedBroker.value || null,
    };

    router.patch(
        route("admin.clients.assign-broker", selectedClient.value.id),
        data,
        {
            onSuccess: () => {
                showModal.value = false;
                selectedClient.value = null;
                selectedBroker.value = "";
            },
            onFinish: () => {
                isAssigning.value = false;
            },
        }
    );
};

const closeModal = () => {
    showModal.value = false;
    selectedClient.value = null;
    selectedBroker.value = "";
};
</script>
