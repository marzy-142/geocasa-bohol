<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    BellIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    UserIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    XMarkIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/outline';
import { BellIcon as BellSolidIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    showDetails: {
        type: Boolean,
        default: false,
    },
    maxItems: {
        type: Number,
        default: 5,
    },
});

const emit = defineEmits(['reminder-clicked', 'view-all']);

const reminders = ref({
    pending_seller_requests: [],
    unverified_accounts: [],
    overdue_inquiries: [],
    summary: {
        total_reminders: 0,
        high_priority_count: 0,
        has_urgent_items: false,
    },
});

const loading = ref(true);
const error = ref(null);
const showWidget = ref(true);

// Computed properties
const totalReminders = computed(() => reminders.value.summary.total_reminders);
const hasUrgentItems = computed(() => reminders.value.summary.has_urgent_items);
const highPriorityCount = computed(() => reminders.value.summary.high_priority_count);

const allReminders = computed(() => {
    const all = [
        ...reminders.value.pending_seller_requests,
        ...reminders.value.unverified_accounts,
        ...reminders.value.overdue_inquiries,
    ];
    
    // Sort by priority and age
    return all.sort((a, b) => {
        const priorityOrder = { high: 3, medium: 2, low: 1 };
        if (priorityOrder[a.priority] !== priorityOrder[b.priority]) {
            return priorityOrder[b.priority] - priorityOrder[a.priority];
        }
        return b.days_old - a.days_old;
    }).slice(0, props.maxItems);
});

// Methods
const fetchReminders = async () => {
    try {
        loading.value = true;
        const response = await fetch('/api/reminders');
        const data = await response.json();
        
        if (data.success) {
            reminders.value = data.data;
        } else {
            throw new Error('Failed to fetch reminders');
        }
    } catch (err) {
        error.value = err.message;
        console.error('Error fetching reminders:', err);
    } finally {
        loading.value = false;
    }
};

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

const handleReminderClick = (reminder) => {
    emit('reminder-clicked', reminder);
    
    // Navigate based on reminder type
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

const handleViewAll = () => {
    emit('view-all');
    window.location.href = '/reminders';
};

const dismissWidget = () => {
    showWidget.value = false;
};

const formatTimeAgo = (daysOld) => {
    if (daysOld === 0) return 'Today';
    if (daysOld === 1) return '1 day ago';
    return `${daysOld} days ago`;
};

// Lifecycle
onMounted(() => {
    fetchReminders();
    
    // Refresh reminders every 5 minutes
    const interval = setInterval(fetchReminders, 5 * 60 * 1000);
    
    // Cleanup interval on unmount
    return () => clearInterval(interval);
});
</script>

<template>
    <div v-if="showWidget && (totalReminders > 0 || loading)" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Widget Header -->
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <BellSolidIcon v-if="hasUrgentItems" class="w-5 h-5 text-red-500" />
                        <BellIcon v-else class="w-5 h-5 text-gray-600" />
                        <span v-if="totalReminders > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                            {{ totalReminders > 9 ? '9+' : totalReminders }}
                        </span>
                    </div>
                    <h3 class="font-semibold text-gray-900">Reminders</h3>
                    <span v-if="highPriorityCount > 0" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        {{ highPriorityCount }} urgent
                    </span>
                </div>
                <button @click="dismissWidget" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <XMarkIcon class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="px-4 py-6 text-center">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
            <p class="text-sm text-gray-600 mt-2">Loading reminders...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="px-4 py-6 text-center">
            <ExclamationTriangleIcon class="w-8 h-8 text-red-500 mx-auto mb-2" />
            <p class="text-sm text-red-600">{{ error }}</p>
            <button @click="fetchReminders" class="text-sm text-blue-600 hover:text-blue-800 mt-2">
                Try again
            </button>
        </div>

        <!-- Reminders List -->
        <div v-else-if="allReminders.length > 0" class="divide-y divide-gray-200">
            <div
                v-for="reminder in allReminders"
                :key="`${reminder.type}-${reminder.id}`"
                @click="handleReminderClick(reminder)"
                class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors"
            >
                <div class="flex items-start gap-3">
                    <!-- Type Icon -->
                    <div :class="['p-1.5 rounded-full', getTypeColor(reminder.type)]">
                        <component :is="getTypeIcon(reminder.type)" class="w-4 h-4" />
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ reminder.title }}
                            </p>
                            <div class="flex items-center gap-2 ml-2">
                                <!-- Priority Badge -->
                                <span :class="['inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium', getPriorityColor(reminder.priority)]">
                                    {{ reminder.priority }}
                                </span>
                                <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 truncate mt-1">
                            {{ reminder.description }}
                        </p>
                        
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                            <div class="flex items-center gap-1">
                                <ClockIcon class="w-3 h-3" />
                                {{ formatTimeAgo(reminder.days_old) }}
                            </div>
                            <span class="capitalize">{{ reminder.status.replace('_', ' ') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="px-4 py-6 text-center">
            <BellIcon class="w-8 h-8 text-gray-400 mx-auto mb-2" />
            <p class="text-sm text-gray-600">No reminders at this time</p>
            <p class="text-xs text-gray-500 mt-1">You're all caught up!</p>
        </div>

        <!-- Footer -->
        <div v-if="totalReminders > props.maxItems" class="px-4 py-3 border-t border-gray-200 bg-gray-50">
            <button
                @click="handleViewAll"
                class="w-full text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors"
            >
                View all {{ totalReminders }} reminders
            </button>
        </div>
    </div>
</template>