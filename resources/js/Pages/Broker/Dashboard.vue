<script setup>
import ModernDashboardLayout from '@/Layouts/ModernDashboardLayout.vue';
import DashboardCard from '@/Components/DashboardCard.vue';
import ModernButton from '@/Components/ModernButton.vue';
import { Head } from '@inertiajs/vue3';
import { 
    BuildingOfficeIcon, 
    ChatBubbleLeftRightIcon,
    UserGroupIcon,
    CheckCircleIcon,
    EyeIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';

// Mock data for demonstration
const stats = [
    {
        title: 'My Properties',
        value: '12',
        subtitle: 'Active listings',
        icon: BuildingOfficeIcon,
        color: 'primary',
        trend: { direction: 'up', value: '+2', label: 'this month' }
    },
    {
        title: 'Active Inquiries',
        value: '8',
        subtitle: 'Pending responses',
        icon: ChatBubbleLeftRightIcon,
        color: 'accent',
        trend: { direction: 'up', value: '+3', label: 'this week' }
    },
    {
        title: 'Total Clients',
        value: '24',
        subtitle: 'Managed clients',
        icon: UserGroupIcon,
        color: 'coconut',
        trend: { direction: 'up', value: '+5', label: 'this month' }
    },
    {
        title: 'Completed Deals',
        value: '15',
        subtitle: 'Successful sales',
        icon: CheckCircleIcon,
        color: 'neutral',
        trend: { direction: 'up', value: '+2', label: 'this month' }
    }
];

const recentInquiries = [
    {
        id: 1,
        property: "Sunset Valley Plot",
        client: "John Doe",
        date: "2024-01-15",
        status: "new"
    },
    {
        id: 2,
        property: "Mountain View Land",
        client: "Jane Smith",
        date: "2024-01-14",
        status: "responded"
    },
    {
        id: 3,
        property: "Riverside Property",
        client: "Mike Johnson",
        date: "2024-01-13",
        status: "pending"
    },
];
</script>

<template>
    <Head title="Broker Dashboard - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Welcome Section -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                        Broker Dashboard 🏡
                    </h1>
                    <p class="text-neutral-600 text-lg">
                        Manage your properties, clients, and transactions in beautiful Bohol.
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <ModernButton variant="outline" :icon="EyeIcon">
                        View Analytics
                    </ModernButton>
                    <ModernButton variant="primary" :icon="PlusIcon">
                        Add Property
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="dashboard-grid mb-12">
            <DashboardCard
                v-for="stat in stats"
                :key="stat.title"
                :title="stat.title"
                :value="stat.value"
                :subtitle="stat.subtitle"
                :icon="stat.icon"
                :color="stat.color"
                :trend="stat.trend"
                :interactive="true"
            />
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Recent Inquiries -->
            <div class="card p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-neutral-900">Recent Inquiries</h2>
                    <span class="px-3 py-1 bg-accent-100 text-accent-700 rounded-full text-sm font-medium">
                        {{ recentInquiries.length }} active
                    </span>
                </div>
                <div class="space-y-4">
                    <div
                        v-for="inquiry in recentInquiries"
                        :key="inquiry.id"
                        class="flex items-center justify-between p-4 bg-neutral-50 rounded-2xl hover:bg-neutral-100 transition-colors"
                    >
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-accent-400 rounded-2xl flex items-center justify-center text-white font-medium text-sm shadow-soft">
                                {{ inquiry.client.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="font-medium text-neutral-900">
                                    {{ inquiry.property }}
                                </h3>
                                <p class="text-sm text-neutral-600">
                                    Client: {{ inquiry.client }}
                                </p>
                                <p class="text-xs text-neutral-500">{{ inquiry.date }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span 
                                :class="[
                                    'px-2 py-1 rounded-full text-xs font-medium',
                                    inquiry.status === 'new' ? 'bg-green-100 text-green-700' :
                                    inquiry.status === 'responded' ? 'bg-blue-100 text-blue-700' :
                                    'bg-yellow-100 text-yellow-700'
                                ]"
                            >
                                {{ inquiry.status }}
                            </span>
                            <button class="block mt-2 text-primary-600 hover:text-primary-800 text-sm font-medium">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card p-8">
                <h2 class="text-xl font-bold text-neutral-900 mb-6">Quick Actions</h2>
                <div class="grid grid-cols-2 gap-4">
                    <ModernButton variant="secondary" class="h-20 flex-col">
                        <BuildingOfficeIcon class="w-6 h-6 mb-2" />
                        <span class="text-sm">Add Property</span>
                    </ModernButton>
                    <ModernButton variant="secondary" class="h-20 flex-col">
                        <UserGroupIcon class="w-6 h-6 mb-2" />
                        <span class="text-sm">New Client</span>
                    </ModernButton>
                    <ModernButton variant="secondary" class="h-20 flex-col">
                        <ChatBubbleLeftRightIcon class="w-6 h-6 mb-2" />
                        <span class="text-sm">View Inquiries</span>
                    </ModernButton>
                    <ModernButton variant="secondary" class="h-20 flex-col">
                        <CheckCircleIcon class="w-6 h-6 mb-2" />
                        <span class="text-sm">Transactions</span>
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Bohol Broker Section -->
        <div class="card p-8 tropical-gradient text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Showcase Bohol's Beauty</h2>
                    <p class="text-white/90 mb-4">
                        Help clients discover their dream properties in Bohol's paradise - from beachfront lots to mountain retreats.
                    </p>
                    <ModernButton variant="coconut">
                        Explore My Listings
                    </ModernButton>
                </div>
                <div class="hidden lg:block">
                    <div class="w-32 h-32 bg-white/10 rounded-3xl backdrop-blur-sm flex items-center justify-center">
                        <span class="text-4xl">🏖️</span>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
