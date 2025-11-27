<template>
    <ModernDashboardLayout title="Inquiry Management">
        <!-- Enhanced Header Section -->
        <div
            class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg mb-6"
        >
            <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
            >
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">
                        Inquiry Management
                    </h1>
                    <p class="text-blue-100 text-sm md:text-base">
                        Manage and respond to client inquiries efficiently
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Unified Search & Filter -->
            <UnifiedSearchFilter
                title="Search & Filter Inquiries"
                :search="search"
                search-placeholder="Search by name, email, or message content..."
                :filters="filterObject"
                :result-count="inquiries.total"
                :primary-filters="primaryFilters"
                :secondary-filters="secondaryFilters"
                @search-change="handleSearchChange"
                @filter-change="handleFilterChange"
                @clear-filters="clearAllFilters"
            />

            <!-- Inquiries List -->

            <!-- Enhanced Inquiries Grid -->
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6"
                >
                    <h2 class="text-lg font-semibold text-gray-900">
                        Inquiries ({{ inquiries.total }})
                    </h2>
                    <div class="flex items-center gap-2"></div>
                </div>

                <div v-if="inquiries.data.length > 0" class="space-y-4">
                    <div
                        v-for="inquiry in inquiries.data"
                        :key="inquiry.id"
                        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 p-6 border border-gray-100"
                    >
                        <!-- Header: Client name, date, status badge -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1 min-w-0">
                                <h3
                                    class="font-semibold text-gray-900 text-base truncate mb-1"
                                >
                                    {{ inquiry.name }}
                                </h3>
                                <p
                                    class="text-xs text-gray-500 flex items-center gap-2"
                                >
                                    <span>{{
                                        formatDate(inquiry.created_at)
                                    }}</span>
                                    <span
                                        v-if="isOverdue(inquiry)"
                                        class="inline-flex items-center px-2 py-0.5 rounded bg-red-50 text-red-700 border border-red-200"
                                        >Overdue</span
                                    >
                                </p>
                            </div>
                            <span
                                :class="getStatusBadgeClass(inquiry.status)"
                                class="ml-3 px-2.5 py-1 text-xs font-medium rounded-md whitespace-nowrap flex-shrink-0"
                            >
                                {{
                                    inquiry.status.charAt(0).toUpperCase() +
                                    inquiry.status.slice(1)
                                }}
                            </span>
                        </div>

                        <!-- Middle: Property name and message preview -->
                        <div class="mb-5 space-y-3">
                            <div v-if="inquiry.property">
                                <p
                                    class="font-medium text-sm text-gray-900 truncate"
                                >
                                    {{ inquiry.property.title }}
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ inquiry.property.municipality }},
                                    {{ inquiry.property.province }}
                                </p>
                            </div>
                            <p
                                v-if="inquiry.message"
                                class="text-sm text-gray-600 line-clamp-2 leading-relaxed"
                            >
                                {{ inquiry.message }}
                            </p>
                        </div>

                        <!-- Bottom: Action buttons with single accent color -->
                        <!-- Bottom: Action buttons with single accent color -->
                        <div class="flex gap-2 pt-4 border-t border-gray-100">
                            <button
                                v-if="primaryActionLabel(inquiry)"
                                @click="handlePrimaryAction(inquiry)"
                                class="flex-1 py-2 px-3 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                            >
                                {{ primaryActionLabel(inquiry) }}
                            </button>
                            <Link
                                :href="route('inquiries.show', inquiry.id)"
                                class="flex-1 text-center py-2 px-3 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                View
                            </Link>
                            <button
                                v-if="isAdmin"
                                @click="deleteInquiry(inquiry)"
                                class="py-2 px-3 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                                title="Delete Inquiry (Admin Only)"
                            >
                                Delete
                            </button>
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
                        Try adjusting your filters or wait for new inquiries to
                        come in. Inquiries will appear here when clients submit
                        them through property pages.
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="inquiries.data.length > 0" class="mt-6">
                    <Pagination :links="inquiries.links" />
                </div>
            </div>
        </div>

        <!-- Quick Response Modal -->
        <div
            v-if="showQuickResponseModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showQuickResponseModal = false"
        >
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-2">Quick Response</h3>
                <p v-if="selectedInquiry" class="text-sm text-gray-600 mb-4">
                    To {{ selectedInquiry.name }}
                    <span v-if="selectedInquiry.property"
                        >regarding {{ selectedInquiry.property.title }}</span
                    >
                </p>
                <textarea
                    v-model="quickResponseText"
                    rows="4"
                    class="w-full border border-gray-300 rounded-lg p-3 mb-4 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Type your response..."
                ></textarea>
                <div class="flex justify-end gap-2">
                    <button
                        @click="showQuickResponseModal = false"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        @click="sendQuickResponse"
                        :disabled="!quickResponseText.trim()"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition-colors"
                    >
                        Send Response
                    </button>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import UnifiedSearchFilter from "@/Components/UnifiedSearchFilter.vue";
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
const selectedProperty = ref(props.filters.property_id || "");
const dateFrom = ref(props.filters.date_from || "");
const dateTo = ref(props.filters.date_to || "");

// Filter object for UnifiedSearchFilter
const filterObject = computed(() => ({
    status: selectedStatus.value,
    property_id: selectedProperty.value,
    date_from: dateFrom.value,
    date_to: dateTo.value,
}));

// Filter configurations for UnifiedSearchFilter
const primaryFilters = computed(() => [
    {
        key: "status",
        label: "Status",
        type: "select",
        span: 6,
        options: [
            { value: "new", label: "New" },
            { value: "contacted", label: "Contacted" },
            { value: "scheduled", label: "Scheduled" },
            { value: "completed", label: "Completed" },
            { value: "closed", label: "Closed" },
        ],
    },
]);

const secondaryFilters = computed(() => [
    {
        key: "property_id",
        label: "Property",
        type: "select",
        options: props.properties.map((property) => ({
            value: property.id,
            label: property.title,
        })),
    },
    {
        key: "date_from",
        label: "Date From",
        type: "date",
        placeholder: "Start date",
    },
    {
        key: "date_to",
        label: "Date To",
        type: "date",
        placeholder: "End date",
    },
]);

// Event handlers for UnifiedSearchFilter
const handleSearchChange = (value) => {
    search.value = value;
    applyFilters();
};

const handleFilterChange = (key, value) => {
    switch (key) {
        case "status":
            selectedStatus.value = value;
            break;
        case "property_id":
            selectedProperty.value = value;
            break;
        case "date_from":
            dateFrom.value = value;
            break;
        case "date_to":
            dateTo.value = value;
            break;
    }
    applyFilters();
};

const clearAllFilters = () => {
    search.value = "";
    selectedStatus.value = "";
    selectedProperty.value = "";
    dateFrom.value = "";
    dateTo.value = "";
    applyFilters();
};

// Determine if current user is admin to conditionally show admin actions
const page = usePage();
const isAdmin = computed(() => page.props?.auth?.user?.role === "admin");

// New reactive variables for enhanced features
const selectedPriority = ref(props.filters.priority || "");
const viewMode = ref("list");
const sortBy = ref("created_at");
const showQuickResponseModal = ref(false);
const quickResponseText = ref("");
const selectedInquiry = ref(null);

// Real-time functionality
const notifications = ref([]);
const isConnected = ref(false);

// Decide primary action per inquiry status
const primaryActionLabel = (inquiry) => {
    switch (inquiry.status) {
        case "new":
            return "Respond";
        case "contacted":
        case "scheduled":
            return "Chat";
        default:
            return null;
    }
};

const handlePrimaryAction = (inquiry) => {
    switch (inquiry.status) {
        case "new":
            return openQuickResponse(inquiry);
        case "contacted":
        case "scheduled":
            // Start or open the conversation for this inquiry and redirect to messages
            return startChat(inquiry);
        default:
            return;
    }
};

const applyFilters = () => {
    router.get(
        route("inquiries.index"),
        {
            search: search.value,
            status: selectedStatus.value,
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
    if (
        confirm(
            `Are you sure you want to delete this inquiry from ${inquiry.name}?\n\nThis action cannot be undone and will permanently remove all inquiry data.`
        )
    ) {
        router.delete(route("inquiries.destroy", inquiry.id));
    }
};

// Enhanced helper functions
const getPriorityLevel = (inquiry) => {
    // Logic to determine priority based on inquiry age
    const daysSinceCreated = Math.floor(
        (new Date() - new Date(inquiry.created_at)) / (1000 * 60 * 60 * 24)
    );
    if (daysSinceCreated > 3) return "HIGH";
    if (daysSinceCreated > 1) return "MED";
    return "LOW";
};

const getPriorityBadgeClass = (inquiry) => {
    const priority = getPriorityLevel(inquiry);
    const classes = {
        HIGH: "bg-red-100 text-red-800",
        MED: "bg-yellow-100 text-yellow-800",
        LOW: "bg-green-100 text-green-800",
    };
    return classes[priority];
};

const getPriorityBorderClass = (inquiry) => {
    const priority = getPriorityLevel(inquiry);
    const classes = {
        HIGH: "border-l-4 border-l-red-500",
        MED: "border-l-4 border-l-yellow-500",
        LOW: "border-l-4 border-l-green-500",
    };
    return classes[priority];
};

const isUrgent = (inquiry) => {
    const daysSinceCreated = Math.floor(
        (new Date() - new Date(inquiry.created_at)) / (1000 * 60 * 60 * 24)
    );
    return daysSinceCreated > 2 && inquiry.status === "new";
};

const isOverdue = (inquiry) => {
    const daysSinceCreated = Math.floor(
        (new Date() - new Date(inquiry.created_at)) / (1000 * 60 * 60 * 24)
    );
    return daysSinceCreated > 1 && inquiry.status === "new";
};

const getTimeAgo = (dateString) => {
    const now = new Date();
    const date = new Date(dateString);
    const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));

    if (diffInHours < 1) return "Just now";
    if (diffInHours < 24) return `${diffInHours}h ago`;
    const diffInDays = Math.floor(diffInHours / 24);
    return `${diffInDays}d ago`;
};

// Enhanced action functions
const toggleView = () => {
    viewMode.value = viewMode.value === "grid" ? "list" : "grid";
};

const openQuickResponse = (inquiry) => {
    selectedInquiry.value = inquiry;
    quickResponseText.value = "";
    showQuickResponseModal.value = true;
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffHours / 24);

    if (diffHours < 1) return "Just now";
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays === 1) return "Yesterday";
    if (diffDays < 7) return `${diffDays} days ago`;

    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: date.getFullYear() !== now.getFullYear() ? "numeric" : undefined,
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        new: "bg-blue-50 text-blue-700",
        scheduled: "bg-purple-50 text-purple-700",
        in_progress: "bg-amber-50 text-amber-700",
        responded: "bg-green-50 text-green-700",
        closed: "bg-gray-100 text-gray-600",
    };
    return classes[status] || classes.new;
};

const formatTime = (timestamp) => {
    const date = new Date(timestamp);
    return date.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
    });
};

const createTransaction = (inquiry) => {
    // Navigate to transaction creation page with pre-populated inquiry data
    router.visit(route("transactions.create"), {
        method: "get",
        data: {
            inquiry_id: inquiry.id,
            client_id: inquiry.client_id,
            property_id: inquiry.property_id,
            client_name: inquiry.client?.name,
            client_email: inquiry.client?.email,
            client_phone: inquiry.client?.phone,
            property_title: inquiry.property?.title,
            property_address: inquiry.property?.address,
            inquiry_message: inquiry.message,
        },
    });
};

// Helper function to check if property has assigned client
const hasAssignedClient = (property) => {
    if (!property) return false;

    const unavailableStatuses = [
        "reserved",
        "under_negotiation",
        "sold",
        "pending",
    ];
    return unavailableStatuses.includes(property.status);
};

const sendQuickResponse = () => {
    router.post(
        route("inquiries.respond", selectedInquiry.value.id),
        {
            broker_response: quickResponseText.value,
            status: "contacted",
        },
        {
            onSuccess: () => {
                showQuickResponseModal.value = false;
                quickResponseText.value = "";
                selectedInquiry.value = null;
            },
        }
    );
};

const scheduleViewing = (inquiry) => {
    router.visit(route("inquiries.edit", inquiry.id));
};

// Start or open a conversation for an inquiry, then redirect to its messages page
const startChat = async (inquiry) => {
    try {
        await router.post(
            route("conversations.create-inquiry", inquiry.id),
            {},
            {
                preserveScroll: true,
            }
        );
        // The server redirects to conversations.show if it exists/gets created.
        // Inertia will follow the redirect automatically.
    } catch (e) {
        console.error(
            "Failed to start conversation for inquiry",
            inquiry.id,
            e
        );
    }
};

const togglePriority = (inquiry) => {
    // This would require backend support to store priority
    console.log("Toggle priority for inquiry:", inquiry.id);
};

// Real-time connection setup
onMounted(() => {
    // Initialize Echo for real-time updates
    if (window.Echo) {
        const channelsJoined = [];

        const attachHandlers = (channel) => {
            channel
                .listen(".inquiry.new", (e) => {
                    notifications.value.unshift({
                        id: Date.now(),
                        type: "new_inquiry",
                        message: `New inquiry from ${e.inquiry.name}`,
                        inquiry: e.inquiry,
                        timestamp: new Date(),
                    });

                    // Show browser notification if permission granted
                    if (Notification.permission === "granted") {
                        new Notification("New Inquiry Received", {
                            body: `${e.inquiry.name} inquired about ${e.inquiry.property.title}`,
                            icon: "/favicon.ico",
                        });
                    }

                    // Auto-refresh the page data
                    router.reload({ only: ["inquiries"] });
                })
                .listen(".inquiry.status.updated", (e) => {
                    notifications.value.unshift({
                        id: Date.now(),
                        type: "status_update",
                        message: `Inquiry #${e.inquiry_id} status changed to ${e.new_status}`,
                        inquiry: e.inquiry,
                        timestamp: new Date(),
                    });

                    // Update the inquiry in the current list if it exists
                    const inquiryIndex = props.inquiries.data.findIndex(
                        (inq) => inq.id === e.inquiry_id
                    );
                    if (inquiryIndex !== -1) {
                        props.inquiries.data[inquiryIndex].status =
                            e.new_status;
                    }
                });
        };

        // Global inquiries channel
        const inquiriesChannel = window.Echo.private("inquiries");
        attachHandlers(inquiriesChannel);
        channelsJoined.push("inquiries");

        // Also join broker-specific channel if available
        try {
            const userId = usePage()?.props?.auth?.user?.id;
            if (userId) {
                const brokerChannelName = `broker.${userId}`;
                const brokerChannel = window.Echo.private(brokerChannelName);
                attachHandlers(brokerChannel);
                channelsJoined.push(brokerChannelName);
            }
        } catch (err) {
            console.warn("Broker channel join skipped:", err);
        }

        // Connection status listeners
        window.Echo.connector.pusher.connection.bind("connected", () => {
            isConnected.value = true;
        });

        window.Echo.connector.pusher.connection.bind("disconnected", () => {
            isConnected.value = false;
        });
    }

    // Request notification permission
    if ("Notification" in window && Notification.permission === "default") {
        Notification.requestPermission();
    }
});

onUnmounted(() => {
    // Clean up Echo listeners
    if (window.Echo) {
        try {
            // Leave all joined channels
            const userId = usePage()?.props?.auth?.user?.id;
            window.Echo.leaveChannel("inquiries");
            if (userId) {
                window.Echo.leaveChannel(`broker.${userId}`);
            }
        } catch (err) {
            // no-op
        }
    }
});

// New notification management functions
const dismissNotification = (notificationId) => {
    const index = notifications.value.findIndex((n) => n.id === notificationId);
    if (index !== -1) {
        notifications.value.splice(index, 1);
    }
};

const clearAllNotifications = () => {
    notifications.value = [];
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
