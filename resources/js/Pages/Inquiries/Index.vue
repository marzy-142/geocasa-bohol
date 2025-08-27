<template>
    <ModernDashboardLayout title="Inquiry Management">
        <!-- Enhanced Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Inquiry Management</h1>
                    <p class="text-blue-100">Manage and respond to client inquiries efficiently</p>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Real-time Status Indicators -->
                    <div class="bg-white/20 rounded-lg p-3">
                        <div class="flex items-center space-x-4 text-sm">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-red-400 rounded-full mr-2 animate-pulse"></div>
                                <span>{{ newInquiriesCount }} New</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></div>
                                <span>{{ pendingInquiriesCount }} Pending</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                                <span>{{ completedTodayCount }} Completed Today</span>
                            </div>
                        </div>
                    </div>
                    <!-- Quick Actions -->
                    <div class="flex space-x-2">
                        <button @click="markAllAsRead" class="bg-white/20 hover:bg-white/30 px-3 py-2 rounded-lg text-sm transition-colors">
                            Mark All Read
                        </button>
                        <button @click="exportInquiries" class="bg-white/20 hover:bg-white/30 px-3 py-2 rounded-lg text-sm transition-colors">
                            Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Enhanced Search & Filter Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Search & Filter Inquiries</h2>
                
                <!-- Primary Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name, email, or message..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @input="applyFilters"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            v-model="selectedStatus"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">All Statuses</option>
                            <option value="new">New</option>
                            <option value="contacted">Contacted</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="completed">Completed</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                        <select
                            v-model="selectedPriority"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">All Priorities</option>
                            <option value="high">High Priority</option>
                            <option value="medium">Medium Priority</option>
                            <option value="low">Low Priority</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select
                            v-model="selectedType"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">All Types</option>
                            <option value="general">General</option>
                            <option value="viewing">Viewing</option>
                            <option value="purchase">Purchase</option>
                            <option value="information">Information</option>
                        </select>
                    </div>
                </div>

                <!-- Secondary Filters -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Property</label>
                        <select
                            v-model="selectedProperty"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @change="applyFilters"
                        >
                            <option value="">All Properties</option>
                            <option v-for="property in properties" :key="property.id" :value="property.id">
                                {{ property.title }} - {{ property.municipality }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input
                            v-model="dateFrom"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input
                            v-model="dateTo"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            @change="applyFilters"
                        />
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <button
                        @click="clearFilters"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors"
                    >
                        Clear all filters
                    </button>
                </div>
            </div>

            <!-- Enhanced Inquiries Grid -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Inquiries ({{ inquiries.total }})
                    </h2>
                    <div class="flex items-center space-x-2">
                        <button @click="toggleView" class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            {{ viewMode === 'grid' ? 'List View' : 'Grid View' }}
                        </button>
                        <select v-model="sortBy" @change="applyFilters" class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                            <option value="created_at">Sort by Date</option>
                            <option value="priority">Sort by Priority</option>
                            <option value="status">Sort by Status</option>
                        </select>
                    </div>
                </div>

                <div v-if="inquiries.data.length > 0" :class="viewMode === 'grid' ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6' : 'space-y-4'">
                    <div
                        v-for="inquiry in inquiries.data"
                        :key="inquiry.id"
                        :class="[
                            'bg-white border rounded-lg p-6 hover:shadow-md transition-shadow',
                            inquiry.status === 'new' ? 'border-l-4 border-l-blue-500' : '',
                            getPriorityBorderClass(inquiry)
                        ]"
                    >
                        <!-- Enhanced Header with Priority and Status -->
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-2">
                                <h3 class="font-semibold text-gray-900">{{ inquiry.name }}</h3>
                                <!-- Priority Indicator -->
                                <span :class="getPriorityBadgeClass(inquiry)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ getPriorityLevel(inquiry) }}
                                </span>
                                <!-- Urgency Indicator -->
                                <div v-if="isUrgent(inquiry)" class="flex items-center text-red-500">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-1">
                                <span :class="getStatusColor(inquiry.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ inquiry.status.charAt(0).toUpperCase() + inquiry.status.slice(1) }}
                                </span>
                                <span :class="getTypeColor(inquiry.inquiry_type)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ inquiry.inquiry_type.charAt(0).toUpperCase() + inquiry.inquiry_type.slice(1) }}
                                </span>
                            </div>
                        </div>

                        <!-- Connection Status -->
                        <div class="flex items-center space-x-1">
                            <div :class="[
                                'w-2 h-2 rounded-full',
                                isConnected ? 'bg-green-400 animate-pulse' : 'bg-red-400'
                            ]"></div>
                            <span class="text-xs text-white/80">
                                {{ isConnected ? 'Live' : 'Offline' }}
                            </span>
                        </div>
                        
                        <!-- Notification Bell -->
                        <div class="relative">
                            <button @click="showNotifications = !showNotifications" class="relative p-2 text-white/80 hover:text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span v-if="notifications.length > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    {{ notifications.length > 9 ? '9+' : notifications.length }}
                                </span>
                            </button>
                            
                            <!-- Notifications Dropdown -->
                            <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-50">
                                <div class="p-4 border-b">
                                    <div class="flex justify-between items-center">
                                        <h3 class="font-semibold text-gray-900">Notifications</h3>
                                        <button @click="clearAllNotifications" class="text-sm text-blue-600 hover:text-blue-800">
                                            Clear All
                                        </button>
                                    </div>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <div v-if="notifications.length === 0" class="p-4 text-center text-gray-500">
                                        No new notifications
                                    </div>
                                    <div v-for="notification in notifications" :key="notification.id" class="p-4 border-b hover:bg-gray-50">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">{{ notification.message }}</p>
                                                <p class="text-xs text-gray-500 mt-1">{{ formatTime(notification.timestamp) }}</p>
                                            </div>
                                            <button @click="dismissNotification(notification.id)" class="text-gray-400 hover:text-gray-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">📧</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        No inquiries found
                    </h3>
                    <p class="text-gray-500">
Try adjusting your filters or wait for new inquiries to come in. Inquiries will appear here when clients submit them through property pages.
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="inquiries.data.length > 0" class="mt-6">
                    <Pagination :links="inquiries.links" />
                </div>
            </div>
        </div>

        <!-- Quick Response Modal -->
        <div v-if="showQuickResponseModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Quick Response</h3>
                <textarea
                    v-model="quickResponseText"
                    rows="4"
                    class="w-full border border-gray-300 rounded-lg p-3 mb-4"
                    placeholder="Type your response..."
                ></textarea>
                <div class="flex justify-end space-x-2">
                    <button @click="showQuickResponseModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Cancel
                    </button>
                    <button @click="sendQuickResponse" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Send
                    </button>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Link } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    inquiries: Object,
    properties: Array,
    filters: Object,
    can: Object,
});

// Existing reactive variables
const search = ref(props.filters.search || "");
const selectedStatus = ref(props.filters.status || "");
const selectedType = ref(props.filters.inquiry_type || "");
const selectedProperty = ref(props.filters.property_id || "");
const dateFrom = ref(props.filters.date_from || "");
const dateTo = ref(props.filters.date_to || "");

// New reactive variables for enhanced features
const selectedPriority = ref(props.filters.priority || "");
const viewMode = ref('grid');
const sortBy = ref('created_at');
const showQuickResponseModal = ref(false);
const quickResponseText = ref('');
const selectedInquiry = ref(null);

// Real-time functionality
const notifications = ref([]);
const isConnected = ref(false);

// Computed properties for dashboard indicators
const newInquiriesCount = computed(() => {
    return props.inquiries.data.filter(inquiry => inquiry.status === 'new').length;
});

const pendingInquiriesCount = computed(() => {
    return props.inquiries.data.filter(inquiry => ['contacted', 'scheduled'].includes(inquiry.status)).length;
});

const completedTodayCount = computed(() => {
    const today = new Date().toDateString();
    return props.inquiries.data.filter(inquiry => 
        inquiry.status === 'completed' && 
        new Date(inquiry.responded_at).toDateString() === today
    ).length;
});

const applyFilters = () => {
    router.get(
        route("inquiries.index"),
        {
            search: search.value,
            status: selectedStatus.value,
            inquiry_type: selectedType.value,
            property_id: selectedProperty.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const clearFilters = () => {
    search.value = "";
    selectedStatus.value = "";
    selectedType.value = "";
    selectedProperty.value = "";
    dateFrom.value = "";
    dateTo.value = "";
    applyFilters();
};

const getStatusColor = (status) => {
    const colors = {
        new: "bg-blue-100 text-blue-800",
        contacted: "bg-yellow-100 text-yellow-800",
        scheduled: "bg-purple-100 text-purple-800",
        completed: "bg-green-100 text-green-800",
        closed: "bg-gray-100 text-gray-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const getTypeColor = (type) => {
    const colors = {
        general: "bg-gray-100 text-gray-800",
        viewing: "bg-blue-100 text-blue-800",
        purchase: "bg-green-100 text-green-800",
        information: "bg-purple-100 text-purple-800",
    };
    return colors[type] || "bg-gray-100 text-gray-800";
};

const deleteInquiry = (inquiry) => {
    if (confirm("Are you sure you want to delete this inquiry?")) {
        router.delete(route("inquiries.destroy", inquiry.id));
    }
};

// Enhanced helper functions
const getPriorityLevel = (inquiry) => {
    // Logic to determine priority based on inquiry age, type, etc.
    const daysSinceCreated = Math.floor((new Date() - new Date(inquiry.created_at)) / (1000 * 60 * 60 * 24));
    if (inquiry.inquiry_type === 'purchase' || daysSinceCreated > 3) return 'HIGH';
    if (inquiry.inquiry_type === 'viewing' || daysSinceCreated > 1) return 'MED';
    return 'LOW';
};

const getPriorityBadgeClass = (inquiry) => {
    const priority = getPriorityLevel(inquiry);
    const classes = {
        'HIGH': 'bg-red-100 text-red-800',
        'MED': 'bg-yellow-100 text-yellow-800',
        'LOW': 'bg-green-100 text-green-800'
    };
    return classes[priority];
};

const getPriorityBorderClass = (inquiry) => {
    const priority = getPriorityLevel(inquiry);
    const classes = {
        'HIGH': 'border-l-4 border-l-red-500',
        'MED': 'border-l-4 border-l-yellow-500',
        'LOW': 'border-l-4 border-l-green-500'
    };
    return classes[priority];
};

const isUrgent = (inquiry) => {
    const daysSinceCreated = Math.floor((new Date() - new Date(inquiry.created_at)) / (1000 * 60 * 60 * 24));
    return daysSinceCreated > 2 && inquiry.status === 'new';
};

const isOverdue = (inquiry) => {
    const daysSinceCreated = Math.floor((new Date() - new Date(inquiry.created_at)) / (1000 * 60 * 60 * 24));
    return daysSinceCreated > 1 && inquiry.status === 'new';
};

const getTimeAgo = (dateString) => {
    const now = new Date();
    const date = new Date(dateString);
    const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));
    
    if (diffInHours < 1) return 'Just now';
    if (diffInHours < 24) return `${diffInHours}h ago`;
    const diffInDays = Math.floor(diffInHours / 24);
    return `${diffInDays}d ago`;
};

// Enhanced action functions
const toggleView = () => {
    viewMode.value = viewMode.value === 'grid' ? 'list' : 'grid';
};

const quickRespond = (inquiry) => {
    selectedInquiry.value = inquiry;
    showQuickResponseModal.value = true;
};

const sendQuickResponse = () => {
    router.post(route('inquiries.respond', selectedInquiry.value.id), {
        broker_response: quickResponseText.value,
        status: 'contacted'
    }, {
        onSuccess: () => {
            showQuickResponseModal.value = false;
            quickResponseText.value = '';
            selectedInquiry.value = null;
        }
    });
};

const scheduleViewing = (inquiry) => {
    router.visit(route('inquiries.edit', inquiry.id));
};

const togglePriority = (inquiry) => {
    // This would require backend support to store priority
    console.log('Toggle priority for inquiry:', inquiry.id);
};

const markAllAsRead = () => {
    // Implementation for marking all as read
    console.log('Mark all as read');
};

const exportInquiries = () => {
    // Implementation for exporting inquiries
    window.open(route('inquiries.export', props.filters));
};

// Real-time connection setup
onMounted(() => {
    // Initialize Echo for real-time updates
    if (window.Echo) {
        // Listen for new inquiries
        window.Echo.private('inquiries')
            .listen('.inquiry.new', (e) => {
                notifications.value.unshift({
                    id: Date.now(),
                    type: 'new_inquiry',
                    message: `New inquiry from ${e.inquiry.name}`,
                    inquiry: e.inquiry,
                    timestamp: new Date()
                });
                
                // Show browser notification if permission granted
                if (Notification.permission === 'granted') {
                    new Notification('New Inquiry Received', {
                        body: `${e.inquiry.name} inquired about ${e.inquiry.property.title}`,
                        icon: '/favicon.ico'
                    });
                }
                
                // Auto-refresh the page data
                router.reload({ only: ['inquiries'] });
            })
            .listen('.inquiry.status.updated', (e) => {
                notifications.value.unshift({
                    id: Date.now(),
                    type: 'status_update',
                    message: `Inquiry #${e.inquiry_id} status changed to ${e.new_status}`,
                    inquiry: e.inquiry,
                    timestamp: new Date()
                });
                
                // Update the inquiry in the current list if it exists
                const inquiryIndex = props.inquiries.data.findIndex(inq => inq.id === e.inquiry_id);
                if (inquiryIndex !== -1) {
                    props.inquiries.data[inquiryIndex].status = e.new_status;
                }
            });
            
        // Connection status listeners
        window.Echo.connector.pusher.connection.bind('connected', () => {
            isConnected.value = true;
        });
        
        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            isConnected.value = false;
        });
    }
    
    // Request notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
});

onUnmounted(() => {
    // Clean up Echo listeners
    if (window.Echo) {
        window.Echo.leaveChannel('inquiries');
    }
});

// New notification management functions
const dismissNotification = (notificationId) => {
    const index = notifications.value.findIndex(n => n.id === notificationId);
    if (index !== -1) {
        notifications.value.splice(index, 1);
    }
};

const clearAllNotifications = () => {
    notifications.value = [];
};
</script>
