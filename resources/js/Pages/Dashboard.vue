<script setup>
import ModernDashboardLayout from '@/Layouts/ModernDashboardLayout.vue';
import DashboardCard from '@/Components/DashboardCard.vue';
import ModernButton from '@/Components/ModernButton.vue';
import { Head } from '@inertiajs/vue3';
import { 
    BuildingOfficeIcon, 
    UserGroupIcon, 
    CurrencyDollarIcon,
    ChartBarIcon,
    PlusIcon,
    EyeIcon
} from '@heroicons/vue/24/outline';

// Sample data - replace with actual props
const stats = [
    {
        title: 'Total Properties',
        value: '247',
        subtitle: 'Active listings',
        icon: BuildingOfficeIcon,
        color: 'primary',
        trend: { direction: 'up', value: '+12%', label: 'from last month' }
    },
    {
        title: 'Active Clients',
        value: '89',
        subtitle: 'Engaged buyers',
        icon: UserGroupIcon,
        color: 'accent',
        trend: { direction: 'up', value: '+8%', label: 'from last month' }
    },
    {
        title: 'Total Revenue',
        value: '₱2.4M',
        subtitle: 'This quarter',
        icon: CurrencyDollarIcon,
        color: 'coconut',
        trend: { direction: 'up', value: '+23%', label: 'from last quarter' }
    },
    {
        title: 'Conversion Rate',
        value: '18.2%',
        subtitle: 'Inquiries to sales',
        icon: ChartBarIcon,
        color: 'neutral',
        trend: { direction: 'up', value: '+3.1%', label: 'from last month' }
    }
];
</script>

<template>
    <Head title="Dashboard - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Welcome Section -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                        Welcome back! 👋
                    </h1>
                    <p class="text-neutral-600 text-lg">
                        Here's what's happening with your real estate business in beautiful Bohol.
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <ModernButton variant="outline" :icon="EyeIcon">
                        View Reports
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

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Recent Activity -->
            <div class="card p-8">
                <h2 class="text-xl font-bold text-neutral-900 mb-6">Recent Activity</h2>
                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-4 bg-neutral-50 rounded-2xl">
                        <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                            <BuildingOfficeIcon class="w-5 h-5 text-primary-600" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-neutral-900">New property listed</p>
                            <p class="text-xs text-neutral-500">Beachfront Villa in Panglao • 2 hours ago</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-neutral-50 rounded-2xl">
                        <div class="w-10 h-10 bg-accent-100 rounded-xl flex items-center justify-center">
                            <UserGroupIcon class="w-5 h-5 text-accent-600" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-neutral-900">New client inquiry</p>
                            <p class="text-xs text-neutral-500">Maria Santos interested in condo • 4 hours ago</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 bg-neutral-50 rounded-2xl">
                        <div class="w-10 h-10 bg-coconut-100 rounded-xl flex items-center justify-center">
                            <CurrencyDollarIcon class="w-5 h-5 text-coconut-600" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-neutral-900">Transaction completed</p>
                            <p class="text-xs text-neutral-500">Townhouse in Tagbilaran sold • 1 day ago</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
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
                        <ChartBarIcon class="w-6 h-6 mb-2" />
                        <span class="text-sm">View Analytics</span>
                    </ModernButton>
                    <ModernButton variant="secondary" class="h-20 flex-col">
                        <CurrencyDollarIcon class="w-6 h-6 mb-2" />
                        <span class="text-sm">Transactions</span>
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Bohol Inspiration Section -->
        <div class="card p-8 tropical-gradient text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Discover Bohol's Beauty</h2>
                    <p class="text-white/90 mb-4">
                        From pristine beaches to historic landmarks, help your clients find their perfect piece of paradise.
                    </p>
                    <ModernButton variant="coconut">
                        Explore Featured Properties
                    </ModernButton>
                </div>
                <div class="hidden lg:block">
                    <div class="w-32 h-32 bg-white/10 rounded-3xl backdrop-blur-sm flex items-center justify-center">
                        <span class="text-4xl">🏝️</span>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
