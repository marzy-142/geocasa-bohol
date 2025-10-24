<template>
    <Head :title="`${broker.name} - Broker Details`" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <Link 
                            :href="route('leaderboard.index')"
                            class="text-blue-600 hover:text-blue-800"
                        >
                            ← Back to Leaderboard
                        </Link>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ broker.name }}</h1>
                            <p class="text-gray-600 mt-1">Licensed Real Estate Broker</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Broker Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm p-6 border">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100">
                            <CurrencyDollarIcon class="h-6 w-6 text-green-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Sales</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.total_sales }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100">
                            <BuildingOfficeIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Listings</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.active_listings }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100">
                            <UserGroupIcon class="h-6 w-6 text-purple-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Clients</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.total_clients }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100">
                            <ChartBarIcon class="h-6 w-6 text-yellow-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Success Rate</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ stats.success_rate }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Transactions -->
                <div class="bg-white rounded-xl shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Recent Transactions</h2>
                    </div>
                    <div class="p-6">
                        <div v-if="recentTransactions.length === 0" class="text-center py-8 text-gray-500">
                            No recent transactions
                        </div>
                        <div v-else class="space-y-4">
                            <div 
                                v-for="transaction in recentTransactions" 
                                :key="transaction.id"
                                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
                            >
                                <div>
                                    <p class="font-medium text-gray-900">{{ transaction.property?.title }}</p>
                                    <p class="text-sm text-gray-500">{{ transaction.client?.name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-green-600">₱{{ formatNumber(transaction.final_price) }}</p>
                                    <p class="text-sm text-gray-500">{{ formatDate(transaction.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Properties -->
                <div class="bg-white rounded-xl shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Active Properties</h2>
                    </div>
                    <div class="p-6">
                        <div v-if="activeProperties.length === 0" class="text-center py-8 text-gray-500">
                            No active properties
                        </div>
                        <div v-else class="space-y-4">
                            <div 
                                v-for="property in activeProperties" 
                                :key="property.id"
                                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
                            >
                                <div>
                                    <p class="font-medium text-gray-900">{{ property.title }}</p>
                                    <p class="text-sm text-gray-500">{{ property.municipality }}, Bohol</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-blue-600">₱{{ formatNumber(property.total_price) }}</p>
                                    <p class="text-sm text-gray-500">{{ property.inquiries_count }} inquiries</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { 
    CurrencyDollarIcon, 
    BuildingOfficeIcon, 
    UserGroupIcon, 
    ChartBarIcon 
} from '@heroicons/vue/24/outline'

const props = defineProps({
    broker: Object,
    stats: Object,
    recentTransactions: Array,
    activeProperties: Array,
    performanceTrend: Array,
    period: String
})

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-PH').format(number || 0)
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}
</script>