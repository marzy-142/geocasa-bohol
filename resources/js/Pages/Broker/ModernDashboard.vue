<script setup>
import ModernDashboardLayout from '@/Layouts/ModernDashboardLayout.vue';
import DashboardCard from '@/Components/DashboardCard.vue';
import ModernTable from '@/Components/ModernTable.vue';
import ModernButton from '@/Components/ModernButton.vue';
import { Head } from '@inertiajs/vue3';
import { 
    BuildingOfficeIcon, 
    UserGroupIcon, 
    DocumentTextIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    EyeIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';

// Mock data - replace with real props
const stats = {
    totalProperties: 12,
    activeInquiries: 8,
    totalClients: 24,
    completedTransactions: 15,
    totalCommission: 125000,
    monthlyGrowth: 12.5
};

const recentInquiries = [
    {
        id: 1,
        property: "Sunset Valley Plot",
        client: "John Doe",
        date: "2024-01-15",
        status: "pending",
        amount: "₱2,500,000"
    },
    {
        id: 2,
        property: "Beachfront Land",
        client: "Jane Smith",
        date: "2024-01-14",
        status: "responded",
        amount: "₱5,200,000"
    },
    {
        id: 3,
        property: "Mountain View Lot",
        client: "Mike Johnson",
        date: "2024-01-13",
        status: "pending",
        amount: "₱1,800,000"
    }
];

const tableColumns = [
    { key: 'property', label: 'Property', sortable: true },
    { key: 'client', label: 'Client', sortable: true },
    { key: 'amount', label: 'Amount', sortable: true },
    { key: 'date', label: 'Date', sortable: true },
    { key: 'status', label: 'Status', sortable: false },
    { key: 'actions', label: 'Actions', sortable: false }
];

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        responded: 'bg-green-100 text-green-800',
        closed: 'bg-gray-100 text-gray-800'
    };
    return colors[status] || colors.pending;
};
</script>

<template>
    <Head title="Broker Dashboard" />
    
    <ModernDashboardLayout>
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600 mt-1">Welcome back! Here's what's happening with your properties.</p>
                </div>
                <ModernButton 
                    variant="primary" 
                    :icon="PlusIcon"
                    @click="$inertia.visit('/broker/properties/create')"
                >
                    Add Property
                </ModernButton>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <DashboardCard
                title="Total Properties"
                :value="stats.totalProperties"
                subtitle="Active listings"
                :icon="BuildingOfficeIcon"
                color="blue"
                :trend="{ direction: 'up', value: '+2', label: 'this month' }"
            />
            
            <DashboardCard
                title="Active Inquiries"
                :value="stats.activeInquiries"
                subtitle="Pending responses"
                :icon="DocumentTextIcon"
                color="orange"
                :trend="{ direction: 'up', value: '+5', label: 'this week' }"
            />
            
            <DashboardCard
                title="Total Clients"
                :value="stats.totalClients"
                subtitle="Registered clients"
                :icon="UserGroupIcon"
                color="green"
                :trend="{ direction: 'up', value: '+3', label: 'this month' }"
            />
            
            <DashboardCard
                title="Commission Earned"
                :value="`₱${stats.totalCommission.toLocaleString()}`"
                subtitle="Total earnings"
                :icon="CurrencyDollarIcon"
                color="purple"
                :trend="{ direction: 'up', value: `+${stats.monthlyGrowth}%`, label: 'vs last month' }"
            />
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                        <BuildingOfficeIcon class="w-6 h-6 text-white" />
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-blue-900">Manage Properties</h3>
                        <p class="text-sm text-blue-700">Add, edit, or view your listings</p>
                    </div>
                    <ModernButton 
                        variant="outline" 
                        size="sm"
                        @click="$inertia.visit('/broker/properties')"
                    >
                        View All
                    </ModernButton>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-600 rounded-xl flex items-center justify-center">
                        <UserGroupIcon class="w-6 h-6 text-white" />
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-green-900">Client Management</h3>
                        <p class="text-sm text-green-700">Connect with your clients</p>
                    </div>
                    <ModernButton 
                        variant="outline" 
                        size="sm"
                        @click="$inertia.visit('/broker/clients')"
                    >
                        View All
                    </ModernButton>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center">
                        <ChartBarIcon class="w-6 h-6 text-white" />
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-purple-900">Performance</h3>
                        <p class="text-sm text-purple-700">Track your success metrics</p>
                    </div>
                    <ModernButton 
                        variant="outline" 
                        size="sm"
                        @click="$inertia.visit('/leaderboard')"
                    >
                        View Stats
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Recent Inquiries -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Recent Inquiries</h2>
                    <p class="text-sm text-gray-600">Latest property inquiries from potential clients</p>
                </div>
                <ModernButton 
                    variant="secondary" 
                    size="sm"
                    @click="$inertia.visit('/inquiries')"
                >
                    View All Inquiries
                </ModernButton>
            </div>

            <ModernTable
                :columns="tableColumns"
                :data="recentInquiries"
            >
                <template #cell-property="{ value }">
                    <div class="font-medium text-gray-900">{{ value }}</div>
                </template>

                <template #cell-client="{ value }">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-gray-600">
                                {{ value.charAt(0) }}
                            </span>
                        </div>
                        <span class="text-sm text-gray-900">{{ value }}</span>
                    </div>
                </template>

                <template #cell-amount="{ value }">
                    <span class="font-semibold text-gray-900">{{ value }}</span>
                </template>

                <template #cell-date="{ value }">
                    <span class="text-sm text-gray-600">{{ value }}</span>
                </template>

                <template #cell-status="{ value }">
                    <span 
                        :class="[
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                            getStatusColor(value)
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
    </ModernDashboardLayout>
</template>