<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernTable from "@/Components/ModernTable.vue";
import ModernButton from "@/Components/ModernButton.vue";
import ModernInput from "@/Components/ModernInput.vue";
import { ref, computed } from "vue";
import {
    UserIcon,
    MagnifyingGlassIcon,
    ChartBarIcon,
    EyeIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    approvedBrokers: Object,
    filters: Object,
});

// Search and filter state
const searchForm = useForm({
    search: props.filters.search || "",
    performance: props.filters.performance || "",
});

// Table columns
const columns = [
    { key: "name", label: "Broker", sortable: true },
    { key: "clients_count", label: "Clients", sortable: true },
    { key: "properties_count", label: "Properties", sortable: true },
    { key: "transactions_count", label: "Transactions", sortable: true },
    { key: "total_commission", label: "Commission", sortable: true },
    { key: "actions", label: "Actions", sortable: false },
];

// Methods
const search = () => {
    searchForm.get(route("admin.approved-brokers.index"), {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' })
        .format(value || 0);
};
</script>

<template>
    <ModernDashboardLayout>
        <Head title="Approved Brokers - Admin" />

        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                        Approved Brokers
                    </h1>
                    <p class="text-neutral-600">
                        Manage and monitor performance of approved brokers
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <div class="relative">
                        <MagnifyingGlassIcon
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                        />
                        <ModernInput
                            v-model="searchForm.search"
                            type="text"
                            placeholder="Search brokers by name or email..."
                            class="pl-10"
                            @keyup.enter="search"
                        />
                    </div>
                </div>

                <!-- Performance Filter -->
                <div>
                    <select
                        v-model="searchForm.performance"
                        @change="search"
                        class="w-full rounded-xl border-gray-200 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">All Performance</option>
                        <option value="high">High Performers</option>
                        <option value="medium">Medium Performers</option>
                        <option value="low">Low Performers</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Brokers Table -->
        <ModernTable
            :columns="columns"
            :data="approvedBrokers.data"
        >
            <!-- Broker Column -->
            <template #cell-name="{ item }">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center"
                    >
                        <UserIcon class="w-5 h-5 text-primary-600" />
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">
                            {{ item.name }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ item.email }}
                        </div>
                    </div>
                </div>
            </template>

            <!-- Commission Column -->
            <template #cell-total_commission="{ item }">
                <div class="text-sm text-gray-900">
                    {{ formatCurrency(item.total_commission) }}
                </div>
            </template>

            <!-- Actions Column -->
            <template #cell-actions="{ item }">
                <div class="flex items-center gap-2">
                    <ModernButton
                        variant="ghost"
                        size="sm"
                        :href="route('admin.approved-brokers.show', item.id)"
                    >
                        <EyeIcon class="w-5 h-5" />
                        View Profile
                    </ModernButton>
                </div>
            </template>
        </ModernTable>

        <!-- Pagination -->
        <div v-if="approvedBrokers.links" class="mt-6">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing {{ approvedBrokers.from }} to {{ approvedBrokers.to }} of
                    {{ approvedBrokers.total }} results
                </div>
                <div class="flex items-center gap-2">
                    <template v-for="link in approvedBrokers.links" :key="link.label">
                        <component
                            :is="link.url ? 'Link' : 'span'"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 text-sm rounded-lg transition-colors',
                                link.active
                                    ? 'bg-primary-500 text-white'
                                    : link.url
                                    ? 'text-gray-700 hover:bg-gray-100'
                                    : 'text-gray-400 cursor-not-allowed',
                            ]"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>