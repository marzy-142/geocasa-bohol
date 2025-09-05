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
        <!-- Broker Header with distinct blue styling -->
        <div class="mb-8 bg-gradient-to-r from-blue-600 to-teal-600 rounded-3xl p-8 text-white shadow-xl">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                            <span class="text-2xl">🏢</span>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold mb-1">
                                Broker Control Panel
                            </h1>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                                <span class="text-white/90 text-sm font-medium">Broker Active</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-white/90 text-lg">
                        Manage your property portfolio and client relationships with ease.
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <ModernButton variant="outline" :icon="EyeIcon" class="border-white/30 text-white hover:bg-white/10">
                        My Analytics
                    </ModernButton>
                    <ModernButton variant="primary" :icon="PlusIcon" class="bg-white text-blue-600 hover:bg-blue-50">
                        List Property
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Broker Stats Grid with distinct blue styling -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Broker-specific stat cards with blue accent -->
            <div class="bg-white rounded-3xl border-2 border-blue-100 p-6 hover:border-blue-200 transition-all duration-200 shadow-sm hover:shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center">
                        <BuildingOfficeIcon class="w-6 h-6 text-blue-600" />
                    </div>
                    <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">MY LISTINGS</span>
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-bold text-neutral-900">{{ mockStats.properties }}</h3>
                    <p class="text-sm font-medium text-neutral-600">Active Properties</p>
                    <p class="text-xs text-neutral-500">Your portfolio</p>
                </div>
            </div>
            
            <div class="bg-white rounded-3xl border-2 border-teal-100 p-6 hover:border-teal-200 transition-all duration-200 shadow-sm hover:shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-teal-100 rounded-2xl flex items-center justify-center">
                        <ChatBubbleLeftRightIcon class="w-6 h-6 text-teal-600" />
                    </div>
                    <span class="text-xs font-medium text-teal-600 bg-teal-50 px-2 py-1 rounded-full">INQUIRIES</span>
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-bold text-neutral-900">{{ mockStats.inquiries }}</h3>
                    <p class="text-sm font-medium text-neutral-600">New Inquiries</p>
                    <p class="text-xs text-neutral-500">This week</p>
                </div>
            </div>
            
            <div class="bg-white rounded-3xl border-2 border-cyan-100 p-6 hover:border-cyan-200 transition-all duration-200 shadow-sm hover:shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-cyan-100 rounded-2xl flex items-center justify-center">
                        <UserGroupIcon class="w-6 h-6 text-cyan-600" />
                    </div>
                    <span class="text-xs font-medium text-cyan-600 bg-cyan-50 px-2 py-1 rounded-full">CLIENTS</span>
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-bold text-neutral-900">{{ mockStats.clients }}</h3>
                    <p class="text-sm font-medium text-neutral-600">Active Clients</p>
                    <p class="text-xs text-neutral-500">Your network</p>
                </div>
            </div>
            
            <div class="bg-white rounded-3xl border-2 border-emerald-100 p-6 hover:border-emerald-200 transition-all duration-200 shadow-sm hover:shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center">
                        <CurrencyDollarIcon class="w-6 h-6 text-emerald-600" />
                    </div>
                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">DEALS</span>
                </div>
                <div class="space-y-1">
                    <h3 class="text-2xl font-bold text-neutral-900">{{ mockStats.deals }}</h3>
                    <p class="text-sm font-medium text-neutral-600">Closed Deals</p>
                    <p class="text-xs text-neutral-500">This month</p>
                </div>
            </div>
        </div>

        <!-- Broker Management Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Client Inquiries Hub -->
            <div class="bg-white rounded-3xl border-2 border-blue-100 p-8 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-2xl flex items-center justify-center">
                            <span class="text-lg">💬</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-neutral-900">Client Inquiries Hub</h2>
                            <p class="text-sm text-neutral-600">Your active client communications</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-sm font-medium">
                        {{ recentInquiries.length }} active
                    </span>
                </div>
                <div class="space-y-4">
                    <div
                        v-for="inquiry in recentInquiries"
                        :key="inquiry.id"
                        class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-teal-50 rounded-2xl hover:from-blue-100 hover:to-teal-100 transition-all duration-200 border border-blue-100"
                    >
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 font-medium text-sm shadow-soft border-2 border-blue-200">
                                {{ inquiry.client.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-medium text-neutral-900">
                                        {{ inquiry.property }}
                                    </h3>
                                    <span class="text-xs bg-teal-100 text-teal-700 px-2 py-1 rounded-full font-medium">NEW</span>
                                </div>
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
                            <button class="block mt-2 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Broker Action Center -->
            <div class="bg-white rounded-3xl border-2 border-teal-100 p-8 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-teal-100 rounded-2xl flex items-center justify-center">
                        <span class="text-lg">⚡</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-neutral-900">Broker Tools</h2>
                        <p class="text-sm text-neutral-600">Quick access to your tools</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-2xl p-4 hover:from-blue-100 hover:to-blue-200 transition-all duration-200 border border-blue-200 cursor-pointer">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center mb-2 border-2 border-blue-200">
                                <BuildingOfficeIcon class="w-6 h-6 text-blue-600" />
                            </div>
                            <span class="text-sm font-medium text-neutral-900">📝 List Property</span>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-teal-50 to-teal-100 rounded-2xl p-4 hover:from-teal-100 hover:to-teal-200 transition-all duration-200 border border-teal-200 cursor-pointer">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-12 h-12 bg-teal-100 rounded-2xl flex items-center justify-center mb-2 border-2 border-teal-200">
                                <UserGroupIcon class="w-6 h-6 text-teal-600" />
                            </div>
                            <span class="text-sm font-medium text-neutral-900">👥 Client Network</span>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-cyan-50 to-cyan-100 rounded-2xl p-4 hover:from-cyan-100 hover:to-cyan-200 transition-all duration-200 border border-cyan-200 cursor-pointer">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-12 h-12 bg-cyan-100 rounded-2xl flex items-center justify-center mb-2 border-2 border-cyan-200">
                                <ChatBubbleLeftRightIcon class="w-6 h-6 text-cyan-600" />
                            </div>
                            <span class="text-sm font-medium text-neutral-900">💬 View Inquiries</span>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-2xl p-4 hover:from-emerald-100 hover:to-emerald-200 transition-all duration-200 border border-emerald-200 cursor-pointer">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center mb-2 border-2 border-emerald-200">
                                <CheckCircleIcon class="w-6 h-6 text-emerald-600" />
                            </div>
                            <span class="text-sm font-medium text-neutral-900">💰 Transactions</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GeoCasa Bohol Broker Performance Center -->
        <div class="bg-gradient-to-br from-blue-50 via-teal-50 to-cyan-50 rounded-3xl border-2 border-blue-200 p-8 shadow-lg">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-teal-500 rounded-3xl flex items-center justify-center shadow-lg">
                        <span class="text-2xl text-white">🏆</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-neutral-900 mb-2">
                            GeoCasa Bohol Broker Hub
                        </h2>
                        <p class="text-neutral-700 font-medium">
                            Your performance dashboard and market leadership center
                        </p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-medium text-sm shadow-lg border-2 border-blue-300"
                    >
                        📊 Performance Reports
                    </button>
                    <button
                        class="px-6 py-3 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-xl hover:from-teal-600 hover:to-teal-700 transition-all duration-200 font-medium text-sm shadow-lg border-2 border-teal-300"
                    >
                        📈 Market Insights
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-6 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl border-2 border-blue-300 shadow-md">
                    <div class="flex items-center justify-center mb-3">
                        <span class="text-2xl mr-2">💰</span>
                        <div class="text-3xl font-bold text-blue-800">
                            ₱2.5M
                        </div>
                    </div>
                    <div class="text-blue-700 font-semibold text-sm">
                        Monthly Sales Revenue
                    </div>
                    <div class="text-xs text-blue-600 mt-1">+15% from last month</div>
                </div>
                <div class="text-center p-6 bg-gradient-to-br from-teal-100 to-teal-200 rounded-2xl border-2 border-teal-300 shadow-md">
                    <div class="flex items-center justify-center mb-3">
                        <span class="text-2xl mr-2">🏠</span>
                        <div class="text-3xl font-bold text-teal-800">8</div>
                    </div>
                    <div class="text-teal-700 font-semibold text-sm">
                        Properties Closed
                    </div>
                    <div class="text-xs text-teal-600 mt-1">2 pending this week</div>
                </div>
                <div class="text-center p-6 bg-gradient-to-br from-cyan-100 to-cyan-200 rounded-2xl border-2 border-cyan-300 shadow-md">
                    <div class="flex items-center justify-center mb-3">
                        <span class="text-2xl mr-2">🥉</span>
                        <div class="text-3xl font-bold text-cyan-800">
                            #3
                        </div>
                    </div>
                    <div class="text-cyan-700 font-semibold text-sm">
                        Broker Leaderboard
                    </div>
                    <div class="text-xs text-cyan-600 mt-1">Top performer this quarter</div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
