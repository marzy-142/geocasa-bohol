<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ModernDashboardLayout from '@/Layouts/ModernDashboardLayout.vue';
import ModernButton from '@/Components/ModernButton.vue';
import ModernTable from '@/Components/ModernTable.vue';
import {
    BellIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    UserIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    FunnelIcon,
    MagnifyingGlassIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    reminders: Object,
    user_role: String,
});

const page = usePage();

// Reactive data
const selectedTab = ref('all');
const searchQuery = ref('');
const priorityFilter = ref('all');
const statusFilter = ref('all');
const loading = ref(false);
const acknowledgedItems = ref(new Set());

// Computed properties
const tabs = computed(() => [
    { key: 'all', label: 'All Reminders', count: totalReminders.value },
    { key: 'seller_requests', label: 'Seller Requests', count: props.reminders.pending_seller_requests?.length || 0 },
    { key: 'unverified_accounts', label: 'Unverified Accounts', count: props.reminders.unverified_accounts?.length || 0 },
    { key: 'overdue_inquiries', label: 'Overdue Inquiries', count: props.reminders.overdue_inquiries?.length || 0 },
]);

const totalReminders = computed(() => {
    return (props.reminders.pending_seller_requests?.length || 0) +
           (props.reminders.unverified_accounts?.length || 0) +
           (props.reminders.overdue_inquiries?.length || 0);
});

const highPriorityCount = computed(() => {
    return props.reminders.summary?.high_priority_count || 0;
});

const allReminders = computed(() => {
    let reminders = [];
    
    if (selectedTab.value === 'all') {
        reminders = [
            ...(props.reminders.pending_seller_requests || []),
            ...(props.reminders.unverified_accounts || []),
            ...(props.reminders.overdue_inquiries || []),
        ];
    } else if (selectedTab.value === 'seller_requests') {
        reminders = props.reminders.pending_seller_requests || [];
    } else if (selectedTab.value === 'unverified_accounts') {
        reminders = props.reminders.unverified_accounts || [];
    } else if (selectedTab.value === 'overdue_inquiries') {
        reminders = props.reminders.overdue_inquiries || [];
    }
    
    // Apply filters
    return reminders.filter(reminder => {
        const matchesSearch = !searchQuery.value || 
            reminder.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            reminder.description.toLowerCase().includes(searchQuery.value.toLowerCase());
            
        const matchesPriority = priorityFilter.value === 'all' || reminder.priority === priorityFilter.value;
        const matchesStatus = statusFilter.value === 'all' || reminder.status === statusFilter.value;
        const notAcknowledged = !acknowledgedItems.value.has(`${reminder.type}-${reminder.id}`);
        
        return matchesSearch && matchesPriority && matchesStatus && notAcknowledged;
    }).sort((a, b) => {
        // Sort by priority first, then by age
        const priorityOrder = { high: 3, medium: 2, low: 1 };
        if (priorityOrder[a.priority] !== priorityOrder[b.priority]) {
            return priorityOrder[b.priority] - priorityOrder[a.priority];
        }
        return b.days_old - a.days_old;
    });
});

const tableColumns = [
    { key: 'type', label: 'Type', sortable: true },
    { key: 'title', label: 'Title', sortable: true },
    { key: 'description', label: 'Description', sortable: false },
    { key: 'priority', label: 'Priority', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'days_old', label: 'Age', sortable: true },
    { key: 'actions', label: 'Actions', sortable: false },
];

// Methods
const getTypeIcon = (type) => {
    const icons = {
        seller_request: BuildingOfficeIcon,
        unverified_account: UserIcon,
        overdue_inquiry: DocumentTextIcon,
    };
    return icons[type] || DocumentTextIcon;
};

const getTypeColor = (type) => {
    const colors = {
        seller_request: 'text-blue-600 bg-blue-100',
        unverified_account: 'text-orange-600 bg-orange-100',
        overdue_inquiry: 'text-red-600 bg-red-100',
    };
    return colors[type] || 'text-gray-600 bg-gray-100';
};

const getPriorityColor = (priority) => {
    const colors = {
        high: 'text-red-600 bg-red-100',
        medium: 'text-yellow-600 bg-yellow-100',
        low: 'text-green-600 bg-green-100',
    };
    return colors[priority] || colors.low;
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'text-yellow-600 bg-yellow-100',
        under_review: 'text-blue-600 bg-blue-100',
        approved: 'text-green-600 bg-green-100',
        rejected: 'text-red-600 bg-red-100',
    };
    return colors[status] || 'text-gray-600 bg-gray-100';
};

const formatTimeAgo = (daysOld) => {
    if (daysOld === 0) return 'Today';
    if (daysOld === 1) return '1 day ago';
    return `${daysOld} days ago`;
};

const handleReminderClick = (reminder) => {
    const routes = {
        seller_request: `/admin/seller-requests/${reminder.id}`,
        unverified_account: `/admin/brokers/${reminder.id}`,
        overdue_inquiry: `/broker/inquiries/${reminder.id}`,
    };
    
    const route = routes[reminder.type];
    if (route) {
        window.location.href = route;
    }
};

const acknowledgeReminder = async (reminder) => {
    try {
        loading.value = true;
        const response = await fetch('/api/reminders/acknowledge', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': page.props.csrf_token,
            },
            body: JSON.stringify({
                type: reminder.type,
                id: reminder.id,
            }),
        });
        
        const data = await response.json();
        if (data.success) {
            acknowledgedItems.value.add(`${reminder.type}-${reminder.id}`);
        }
    } catch (error) {
        console.error('Error acknowledging reminder:', error);
    } finally {
        loading.value = false;
    }
};

const clearFilters = () => {
    searchQuery.value = '';
    priorityFilter.value = 'all';
    statusFilter.value = 'all';
};

// Layout component based on user role
const LayoutComponent = computed(() => {
    return props.user_role === 'admin' ? AdminLayout : ModernDashboardLayout;
});
</script>

<template>
    <Head title="Reminders Dashboard" />

    <component :is="LayoutComponent">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <BellIcon class="w-7 h-7" />
                        Reminders Dashboard
                    </h1>
                    <p class="text-gray-600 mt-1">
                        Manage pending tasks and notifications
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span v-if="highPriorityCount > 0" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        <ExclamationTriangleIcon class="w-4 h-4 mr-1" />
                        {{ highPriorityCount }} urgent
                    </span>
                    <span class="text-sm text-gray-600">
                        {{ totalReminders }} total reminders
                    </span>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Reminders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ totalReminders }}</p>
                    </div>
                    <BellIcon class="w-8 h-8 text-blue-600" />
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Seller Requests</p>
                        <p class="text-2xl font-bold text-gray-900">{{ props.reminders.pending_seller_requests?.length || 0 }}</p>
                    </div>
                    <BuildingOfficeIcon class="w-8 h-8 text-blue-600" />
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Unverified Accounts</p>
                        <p class="text-2xl font-bold text-gray-900">{{ props.reminders.unverified_accounts?.length || 0 }}</p>
                    </div>
                    <UserIcon class="w-8 h-8 text-orange-600" />
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Overdue Inquiries</p>
                        <p class="text-2xl font-bold text-gray-900">{{ props.reminders.overdue_inquiries?.length || 0 }}</p>
                    </div>
                    <DocumentTextIcon class="w-8 h-8 text-red-600" />
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <!-- Search -->
                    <div class="relative flex-1 max-w-md">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search reminders..."
                            class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    
                    <!-- Filters -->
                    <div class="flex items-center gap-4">
                        <select v-model="priorityFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="all">All Priorities</option>
                            <option value="high">High Priority</option>
                            <option value="medium">Medium Priority</option>
                            <option value="low">Low Priority</option>
                        </select>
                        
                        <select v-model="statusFilter" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="all">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="under_review">Under Review</option>
                            <option value="approved">Approved</option>
                        </select>
                        
                        <ModernButton
                            v-if="searchQuery || priorityFilter !== 'all' || statusFilter !== 'all'"
                            variant="outline"
                            @click="clearFilters"
                        >
                            Clear Filters
                        </ModernButton>
                    </div>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="px-6">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="selectedTab = tab.key"
                        :class="[
                            'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                            selectedTab === tab.key
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]"
                    >
                        {{ tab.label }}
                        <span v-if="tab.count > 0" :class="[
                            'ml-2 py-0.5 px-2 rounded-full text-xs',
                            selectedTab === tab.key
                                ? 'bg-blue-100 text-blue-600'
                                : 'bg-gray-100 text-gray-600'
                        ]">
                            {{ tab.count }}
                        </span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Reminders Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div v-if="allReminders.length > 0">
                <ModernTable :columns="tableColumns" :data="allReminders">
                    <template #cell-type="{ value }">
                        <div class="flex items-center gap-2">
                            <div :class="['p-1.5 rounded-full', getTypeColor(value)]">
                                <component :is="getTypeIcon(value)" class="w-4 h-4" />
                            </div>
                            <span class="text-sm font-medium capitalize">{{ value.replace('_', ' ') }}</span>
                        </div>
                    </template>
                    
                    <template #cell-title="{ value }">
                        <span class="font-medium text-gray-900">{{ value }}</span>
                    </template>
                    
                    <template #cell-description="{ value }">
                        <span class="text-sm text-gray-600">{{ value }}</span>
                    </template>
                    
                    <template #cell-priority="{ value }">
                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize', getPriorityColor(value)]">
                            {{ value }}
                        </span>
                    </template>
                    
                    <template #cell-status="{ value }">
                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize', getStatusColor(value)]">
                            {{ value.replace('_', ' ') }}
                        </span>
                    </template>
                    
                    <template #cell-days_old="{ value }">
                        <div class="flex items-center gap-1 text-sm text-gray-600">
                            <ClockIcon class="w-4 h-4" />
                            {{ formatTimeAgo(value) }}
                        </div>
                    </template>
                    
                    <template #cell-actions="{ item }">
                        <div class="flex items-center gap-2">
                            <ModernButton
                                variant="outline"
                                size="sm"
                                @click="handleReminderClick(item)"
                            >
                                View
                            </ModernButton>
                            <ModernButton
                                variant="outline"
                                size="sm"
                                :icon="CheckCircleIcon"
                                @click="acknowledgeReminder(item)"
                                :disabled="loading"
                            >
                                Acknowledge
                            </ModernButton>
                        </div>
                    </template>
                </ModernTable>
            </div>
            
            <!-- Empty State -->
            <div v-else class="px-6 py-12 text-center">
                <BellIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    No reminders found
                </h3>
                <p class="text-gray-600">
                    {{ searchQuery || priorityFilter !== 'all' || statusFilter !== 'all' 
                        ? 'Try adjusting your filters to see more results.' 
                        : 'You\'re all caught up! No pending reminders at this time.' }}
                </p>
            </div>
        </div>
    </component>
</template>