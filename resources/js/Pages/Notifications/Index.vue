<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Notifications
                    </h1>
                    <p class="text-gray-600 mt-1">
                        Stay updated with all your important activities
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Filter Dropdown -->
                    <select
                        v-model="selectedFilter"
                        class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="all">All Notifications</option>
                        <option value="unread">Unread Only</option>
                        <option value="new_inquiry">Inquiries</option>
                        <option value="transaction_status">Transactions</option>
                        <option value="seller_request">Seller Requests</option>
                        <option value="broker_approval">
                            Broker Approvals
                        </option>
                    </select>

                    <!-- Mark All Read Button -->
                    <ModernButton
                        v-if="unreadCount > 0"
                        variant="outline"
                        @click="markAllAsRead"
                        :disabled="markingAllRead"
                    >
                        <CheckIcon class="w-4 h-4 mr-2" />
                        Mark All Read
                    </ModernButton>

                    <!-- Settings Button -->
                    <ModernButton
                        variant="outline"
                        @click="showSettings = true"
                    >
                        <Cog6ToothIcon class="w-4 h-4 mr-2" />
                        Settings
                    </ModernButton>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                >
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <BellIcon class="w-6 h-6 text-blue-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Total
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ notifications.total }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                >
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <ExclamationCircleIcon
                                class="w-6 h-6 text-red-600"
                            />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Unread
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ unreadCount }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                >
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <InboxIcon class="w-6 h-6 text-green-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                Today
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ todayCount }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                >
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <ClockIcon class="w-6 h-6 text-yellow-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">
                                This Week
                            </p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ weekCount }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ filteredNotifications.length }} Notifications
                    </h2>
                </div>

                <div class="divide-y divide-gray-200">
                    <div
                        v-if="filteredNotifications.length === 0"
                        class="px-6 py-12 text-center"
                    >
                        <BellSlashIcon
                            class="w-12 h-12 text-gray-400 mx-auto mb-4"
                        />
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            No notifications found
                        </h3>
                        <p class="text-gray-500">
                            {{
                                selectedFilter === "unread"
                                    ? "All caught up! No unread notifications."
                                    : "You'll see notifications here when they arrive."
                            }}
                        </p>
                    </div>

                    <div
                        v-for="notification in filteredNotifications"
                        :key="notification.id"
                        class="px-6 py-4 hover:bg-gray-50 cursor-pointer transition-colors duration-150"
                        :class="{
                            'bg-blue-50 border-l-4 border-l-blue-500':
                                !notification.read_at,
                            'opacity-75': notification.read_at,
                        }"
                        @click="handleNotificationClick(notification)"
                    >
                        <div class="flex items-start space-x-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0 mt-1">
                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center"
                                    :class="
                                        getNotificationIconClass(
                                            notification.data.type
                                        )
                                    "
                                >
                                    <component
                                        :is="
                                            getNotificationIcon(
                                                notification.data.type
                                            )
                                        "
                                        class="w-5 h-5"
                                    />
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p
                                            class="text-sm font-medium text-gray-900 mb-1"
                                        >
                                            {{ notification.data.message }}
                                        </p>
                                        <div
                                            class="flex items-center space-x-4 text-xs text-gray-500"
                                        >
                                            <span class="flex items-center">
                                                <ClockIcon
                                                    class="w-3 h-3 mr-1"
                                                />
                                                {{
                                                    formatDate(
                                                        notification.created_at
                                                    )
                                                }}
                                            </span>
                                            <span
                                                class="px-2 py-1 bg-gray-100 rounded-full"
                                            >
                                                {{
                                                    getNotificationTypeLabel(
                                                        notification.data.type
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div
                                        class="flex items-center space-x-2 ml-4"
                                    >
                                        <button
                                            v-if="!notification.read_at"
                                            @click.stop="
                                                markAsRead(notification.id)
                                            "
                                            class="p-1 text-gray-400 hover:text-blue-600 rounded"
                                            title="Mark as read"
                                        >
                                            <CheckIcon class="w-4 h-4" />
                                        </button>
                                        <button
                                            @click.stop="
                                                deleteNotification(
                                                    notification.id
                                                )
                                            "
                                            class="p-1 text-gray-400 hover:text-red-600 rounded"
                                            title="Delete notification"
                                        >
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="notifications.last_page > 1"
                    class="px-6 py-4 border-t border-gray-200"
                >
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ notifications.from }} to
                            {{ notifications.to }} of
                            {{ notifications.total }} results
                        </div>
                        <div class="flex items-center space-x-2">
                            <Link
                                v-if="notifications.prev_page_url"
                                :href="notifications.prev_page_url"
                                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="notifications.next_page_url"
                                :href="notifications.next_page_url"
                                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Settings Modal -->
        <NotificationSettingsModal
            :show="showSettings"
            @close="showSettings = false"
        />
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import NotificationSettingsModal from "@/Components/NotificationSettingsModal.vue";
import {
    BellIcon,
    BellSlashIcon,
    CheckIcon,
    ClockIcon,
    Cog6ToothIcon,
    ExclamationCircleIcon,
    InboxIcon,
    TrashIcon,
    HomeIcon,
    CurrencyDollarIcon,
    CheckCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    notifications: {
        type: Object,
        required: true,
    },
});

const selectedFilter = ref("all");
const showSettings = ref(false);
const markingAllRead = ref(false);

// Computed properties
const unreadCount = computed(() => {
    return props.notifications.data.filter((n) => !n.read_at).length;
});

const todayCount = computed(() => {
    const today = new Date().toDateString();
    return props.notifications.data.filter((n) => {
        return new Date(n.created_at).toDateString() === today;
    }).length;
});

const weekCount = computed(() => {
    const weekAgo = new Date();
    weekAgo.setDate(weekAgo.getDate() - 7);
    return props.notifications.data.filter((n) => {
        return new Date(n.created_at) >= weekAgo;
    }).length;
});

const filteredNotifications = computed(() => {
    let filtered = props.notifications.data;

    if (selectedFilter.value === "unread") {
        filtered = filtered.filter((n) => !n.read_at);
    } else if (selectedFilter.value !== "all") {
        filtered = filtered.filter((n) => n.data.type === selectedFilter.value);
    }

    return filtered;
});

// Methods
const markAllAsRead = () => {
    markingAllRead.value = true;
    router.post(
        route("notifications.mark-all-read"),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                markingAllRead.value = false;
            },
        }
    );
};

const markAsRead = (notificationId) => {
    router.post(
        route("notifications.mark-read", notificationId),
        {},
        {
            preserveScroll: true,
        }
    );
};

const deleteNotification = (notificationId) => {
    if (confirm("Are you sure you want to delete this notification?")) {
        router.delete(route("notifications.delete", notificationId), {
            preserveScroll: true,
        });
    }
};

const handleNotificationClick = (notification) => {
    // Mark as read if unread
    if (!notification.read_at) {
        markAsRead(notification.id);
    }

    // Navigate based on notification type
    const routes = {
        new_inquiry: () =>
            route("inquiries.show", notification.data.inquiry_id),
        seller_request: () =>
            route("seller-requests.show", notification.data.seller_request_id),
        transaction_status: () =>
            route("transactions.show", notification.data.transaction_id),
        broker_approval: () => route("broker.dashboard"),
        message_received: () =>
            route("conversations.show", notification.data.conversation_id),
    };

    const routeFunction = routes[notification.data.type];
    if (routeFunction) {
        router.visit(routeFunction());
    }
};

const getNotificationIcon = (type) => {
    const icons = {
        new_inquiry: InboxIcon,
        seller_request: HomeIcon,
        transaction_status: CurrencyDollarIcon,
        broker_approval: CheckCircleIcon,
        message_received: BellIcon,
    };
    return icons[type] || BellIcon;
};

const getNotificationIconClass = (type) => {
    const classes = {
        new_inquiry: "bg-blue-100 text-blue-600",
        seller_request: "bg-green-100 text-green-600",
        transaction_status: "bg-yellow-100 text-yellow-600",
        broker_approval: "bg-purple-100 text-purple-600",
        message_received: "bg-indigo-100 text-indigo-600",
    };
    return classes[type] || "bg-gray-100 text-gray-600";
};

const getNotificationTypeLabel = (type) => {
    const labels = {
        new_inquiry: "Inquiry",
        seller_request: "Listing Request",
        transaction_status: "Transaction",
        broker_approval: "Approval",
        message_received: "Message",
    };
    return labels[type] || "Notification";
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = (now - date) / (1000 * 60 * 60);

    if (diffInHours < 1) {
        return "Just now";
    } else if (diffInHours < 24) {
        return `${Math.floor(diffInHours)}h ago`;
    } else if (diffInHours < 48) {
        return "Yesterday";
    } else {
        return date.toLocaleDateString();
    }
};

// Real-time updates
onMounted(() => {
    if (window.Echo) {
        window.Echo.private(`user.${props.auth?.user?.id}`).notification(
            (notification) => {
                // Refresh the page to show new notification
                router.reload({ only: ["notifications"] });
            }
        );
    }
});
</script>
