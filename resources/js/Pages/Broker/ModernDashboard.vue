<script setup>
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import DashboardCard from "@/Components/DashboardCard.vue";
import ModernTable from "@/Components/ModernTable.vue";
import ModernButton from "@/Components/ModernButton.vue";
import ReminderWidget from "@/Components/ReminderWidget.vue";
import {
    BuildingOfficeIcon,
    UserGroupIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    PlusIcon,
    EyeIcon,
} from "@heroicons/vue/24/outline";

// Accept real props from backend
const props = defineProps({
    stats: Object,
    recentInquiries: Array,
    reminders: Object,
});

const page = usePage();

const canCreateProperty = computed(() => {
    const user = page.props.auth.user;
    return user.role === "broker" && user.is_approved;
});

// Calculate trend data from monthly stats
const getTrend = (current, previous, label) => {
    if (!previous || previous === 0) return null;
    const change = current - previous;
    const percentage = ((change / previous) * 100).toFixed(1);
    return {
        direction: change >= 0 ? "up" : "down",
        value: `${change >= 0 ? "+" : ""}${change}`,
        label: label,
        percentage: `${percentage}%`,
    };
};

const tableColumns = [
    { key: "property", label: "Property", sortable: true },
    { key: "client", label: "Client", sortable: true },
    { key: "amount", label: "Amount", sortable: true },
    { key: "date", label: "Date", sortable: true },
    { key: "status", label: "Status", sortable: false },
    { key: "actions", label: "Actions", sortable: false },
];

const getStatusColor = (status) => {
    const colors = {
        pending: "bg-yellow-100 text-yellow-800",
        responded: "bg-green-100 text-green-800",
        closed: "bg-gray-100 text-gray-800",
    };
    return colors[status] || colors.pending;
};
</script>

<template>
    <Head title="Broker Dashboard" />

    <ModernDashboardLayout>
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600">
                Welcome back! Here's what's happening with your properties.
            </p>
        </div>

        <!-- Stats Grid using real data -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <DashboardCard
                title="Total Properties"
                :value="props.stats?.totalProperties || 0"
                subtitle="Your listings"
                :icon="BuildingOfficeIcon"
                color="blue"
                :trend="
                    getTrend(
                        props.stats?.totalProperties || 0,
                        props.stats?.activeProperties || 0,
                        'active'
                    )
                "
            />

            <DashboardCard
                title="Active Inquiries"
                :value="props.stats?.activeInquiries || 0"
                subtitle="Pending responses"
                :icon="DocumentTextIcon"
                color="orange"
                :trend="
                    props.stats?.monthlyStats
                        ? getTrend(
                              props.stats.monthlyStats.currentMonth.inquiries,
                              props.stats.monthlyStats.lastMonth.inquiries,
                              'this month'
                          )
                        : null
                "
            />

            <DashboardCard
                title="Total Clients"
                :value="props.stats?.totalClients || 0"
                subtitle="Your clients"
                :icon="UserGroupIcon"
                color="green"
            />

            <DashboardCard
                title="Commission Earned"
                :value="`₱${(
                    props.stats?.totalCommission || 0
                ).toLocaleString()}`"
                subtitle="Total earnings"
                :icon="CurrencyDollarIcon"
                color="purple"
                :trend="
                    props.stats?.monthlyStats
                        ? getTrend(
                              props.stats.monthlyStats.currentMonth
                                  .transactions,
                              props.stats.monthlyStats.lastMonth.transactions,
                              'transactions'
                          )
                        : null
                "
            />
        </div>

        <!-- Reminders Widget -->
        <div v-if="props.reminders && props.reminders.summary.total_reminders > 0" class="mb-8">
            <ReminderWidget :show-details="true" :max-items="3" />
        </div>

        <!-- Quick Actions -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                Quick Actions
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white"
                >
                    <h3 class="font-semibold mb-2">Manage Properties</h3>
                    <p class="text-blue-100 text-sm mb-4">
                        Add, edit, or view your property listings
                    </p>
                    <ModernButton
                        variant="outline"
                        class="border-white text-white hover:bg-white hover:text-blue-600"
                        @click="
                            $inertia.visit(route('broker.properties.index'))
                        "
                    >
                        View Properties
                    </ModernButton>
                </div>

                <div
                    class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white"
                >
                    <h3 class="font-semibold mb-2">Client Management</h3>
                    <p class="text-green-100 text-sm mb-4">
                        Track and manage your client relationships
                    </p>
                    <ModernButton
                        variant="outline"
                        class="border-white text-white hover:bg-white hover:text-green-600"
                        @click="$inertia.visit(route('broker.clients.index'))"
                    >
                        View Clients
                    </ModernButton>
                </div>

                <div
                    class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white"
                >
                    <h3 class="font-semibold mb-2">Performance Analytics</h3>
                    <p class="text-purple-100 text-sm mb-4">
                        View detailed reports and insights
                    </p>
                    <ModernButton
                        variant="outline"
                        class="border-white text-white hover:bg-white hover:text-purple-600"
                        @click="$inertia.visit(route('broker.analytics'))"
                    >
                        View Analytics
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Recent Inquiries -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    Recent Inquiries
                </h2>
                <p class="text-sm text-gray-600">
                    Latest inquiries on your properties
                </p>
            </div>

            <div
                v-if="props.recentInquiries && props.recentInquiries.length > 0"
            >
                <ModernTable
                    :columns="tableColumns"
                    :data="props.recentInquiries"
                >
                    <template #cell-property="{ value }">
                        <div class="font-medium text-gray-900">{{ value }}</div>
                    </template>

                    <template #cell-client="{ value }">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center"
                            >
                                <span class="text-xs font-medium text-gray-600">
                                    {{ value.charAt(0) }}
                                </span>
                            </div>
                            <span class="text-sm text-gray-900">{{
                                value
                            }}</span>
                        </div>
                    </template>

                    <template #cell-amount="{ value }">
                        <span class="font-semibold text-gray-900">{{
                            value
                        }}</span>
                    </template>

                    <template #cell-date="{ value }">
                        <span class="text-sm text-gray-600">{{ value }}</span>
                    </template>

                    <template #cell-status="{ value }">
                        <span
                            :class="[
                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                                getStatusColor(value),
                            ]"
                        >
                            {{ value }}
                        </span>
                    </template>

                    <template #cell-actions="{ item }">
                        <div class="flex items-center gap-2">
                            <ModernButton
                                variant="outline"
                                size="sm"
                                :icon="EyeIcon"
                                @click="$inertia.visit(`/inquiries/${item.id}`)"
                            >
                                View
                            </ModernButton>
                        </div>
                    </template>
                </ModernTable>
            </div>

            <div v-else class="px-6 py-8 text-center">
                <DocumentTextIcon
                    class="w-12 h-12 text-gray-400 mx-auto mb-4"
                />
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    No inquiries yet
                </h3>
                <p class="text-gray-600">
                    When clients inquire about your properties, they'll appear
                    here.
                </p>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
