<template>
    <ModernDashboardLayout>
        <Head title="Broker Reports" />
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Broker Reports</h1>
                            <p class="mt-2 text-gray-600">Detailed analytics and performance metrics for brokers</p>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <Link :href="route('admin.reports.dashboard')" class="text-indigo-600 hover:text-indigo-900">
                                ← Back to Dashboard
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <UsersIcon class="h-6 w-6 text-blue-400" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total Brokers</dt>
                                        <dd class="text-2xl font-semibold text-gray-900">{{ stats.total_brokers }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <CheckCircleIcon class="h-6 w-6 text-green-400" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Active Brokers</dt>
                                        <dd class="text-2xl font-semibold text-gray-900">{{ stats.active_brokers }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ClockIcon class="h-6 w-6 text-yellow-400" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Pending Approval</dt>
                                        <dd class="text-2xl font-semibold text-gray-900">{{ stats.pending_brokers }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <ChartBarIcon class="h-6 w-6 text-purple-400" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Avg Response Rate</dt>
                                        <dd class="text-2xl font-semibold text-gray-900">{{ stats.avg_response_rate }}%</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Registration Trends -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Registration Trends</h3>
                        </div>
                        <div class="p-6">
                            <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                                <div class="text-center">
                                    <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Registration Chart</h3>
                                    <p class="mt-1 text-sm text-gray-500">Chart visualization would be implemented here</p>
                                    <div class="mt-4 space-y-2 text-xs text-gray-600">
                                        <div v-for="(count, date) in chartData.registrations" :key="date" class="flex justify-between">
                                            <span>{{ formatDate(date) }}:</span>
                                            <span class="font-medium">{{ count }} registrations</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Distribution -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Performance Distribution</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">High Performers (10+ inquiries)</span>
                                    <span class="text-sm text-gray-900">{{ stats.high_performers }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full" :style="{ width: (stats.high_performers / stats.total_brokers * 100) + '%' }"></div>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">Medium Performers (5-9 inquiries)</span>
                                    <span class="text-sm text-gray-900">{{ stats.medium_performers }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full" :style="{ width: (stats.medium_performers / stats.total_brokers * 100) + '%' }"></div>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">Low Performers (0-4 inquiries)</span>
                                    <span class="text-sm text-gray-900">{{ stats.low_performers }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-500 h-2 rounded-full" :style="{ width: (stats.low_performers / stats.total_brokers * 100) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performers Table -->
                <div class="bg-white shadow rounded-lg mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Top Performing Brokers</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Broker</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Properties Listed</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inquiries Received</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response Rate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="broker in topPerformers" :key="broker.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-indigo-800">{{ broker.name.charAt(0) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ broker.name }}</div>
                                                <div class="text-sm text-gray-500">{{ broker.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ broker.properties_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ broker.inquiries_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm text-gray-900">{{ broker.response_rate }}%</div>
                                            <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                                <div class="bg-green-600 h-2 rounded-full" :style="{ width: broker.response_rate + '%' }"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusClass(broker.status)">
                                            {{ broker.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(broker.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recent Broker Activities</h3>
                    </div>
                    <div class="p-6">
                        <div v-if="recentActivities.length === 0" class="text-center py-8">
                            <ClockIcon class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No recent activities</h3>
                            <p class="mt-1 text-sm text-gray-500">Broker activities will appear here as they occur.</p>
                        </div>
                        <div v-else class="flow-root">
                            <ul class="-mb-8">
                                <li v-for="(activity, index) in recentActivities" :key="index">
                                    <div class="relative pb-8">
                                        <span v-if="index !== recentActivities.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white" :class="getActivityIconClass(activity.type)">
                                                    <component :is="getActivityIcon(activity.type)" class="h-4 w-4 text-white" />
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">{{ activity.description }}</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time :datetime="activity.created_at">{{ formatDate(activity.created_at) }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import ModernDashboardLayout from '@/Layouts/ModernDashboardLayout.vue'
import {
    UsersIcon,
    CheckCircleIcon,
    ClockIcon,
    ChartBarIcon,
    UserIcon,
    UserPlusIcon,
    CheckIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    stats: Object,
    chartData: Object,
    topPerformers: Array,
    recentActivities: Array
})

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    })
}

const getStatusClass = (status) => {
    const classes = {
        active: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        suspended: 'bg-red-100 text-red-800',
        inactive: 'bg-gray-100 text-gray-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getActivityIcon = (type) => {
    const icons = {
        registration: UserPlusIcon,
        approval: CheckIcon,
        login: UserIcon
    }
    return icons[type] || ClockIcon
}

const getActivityIconClass = (type) => {
    const classes = {
        registration: 'bg-blue-500',
        approval: 'bg-green-500',
        login: 'bg-indigo-500'
    }
    return classes[type] || 'bg-gray-500'
}
</script>