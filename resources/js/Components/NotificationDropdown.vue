<template>
    <div class="relative">
        <!-- Notification Bell -->
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg"
        >
            <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
            </svg>
            <!-- Notification Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
            >
                {{ unreadCount > 99 ? "99+" : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <div
            v-if="showDropdown"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        >
            <!-- Header -->
            <div class="px-4 py-3 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Notifications
                    </h3>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="text-sm text-blue-600 hover:text-blue-800"
                    >
                        Mark all read
                    </button>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="max-h-96 overflow-y-auto">
                <div
                    v-if="notifications.length === 0"
                    class="px-4 py-8 text-center text-gray-500"
                >
                    No notifications yet
                </div>

                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
                    :class="{ 'bg-blue-50': !notification.read_at }"
                    @click="handleNotificationClick(notification)"
                >
                    <div class="flex items-start space-x-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-1">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center"
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
                                    class="w-4 h-4"
                                />
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ notification.data.message }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ formatDate(notification.created_at) }}
                            </p>
                        </div>

                        <!-- Unread indicator -->
                        <div v-if="!notification.read_at" class="flex-shrink-0">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-4 py-3 border-t border-gray-200">
                <Link
                    :href="route('notifications.index')"
                    class="text-sm text-blue-600 hover:text-blue-800"
                >
                    View all notifications
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },
});

const showDropdown = ref(false);

const unreadCount = computed(() => {
    return props.notifications.filter((n) => !n.read_at).length;
});

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const markAllAsRead = () => {
    router.post(
        route("notifications.mark-all-read"),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // Notifications will be updated via props
            },
        }
    );
};

const handleNotificationClick = (notification) => {
    // Mark as read if unread
    if (!notification.read_at) {
        router.post(
            route("notifications.mark-read", notification.id),
            {},
            {
                preserveScroll: true,
            }
        );
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
    };

    const routeFunction = routes[notification.data.type];
    if (routeFunction) {
        router.visit(routeFunction());
    }

    showDropdown.value = false;
};

const getNotificationIcon = (type) => {
    const icons = {
        new_inquiry: "InboxIcon",
        seller_request: "HomeIcon",
        transaction_status: "CurrencyDollarIcon",
        broker_approval: "CheckCircleIcon",
    };
    return icons[type] || "BellIcon";
};

const getNotificationIconClass = (type) => {
    const classes = {
        new_inquiry: "bg-blue-100 text-blue-600",
        seller_request: "bg-green-100 text-green-600",
        transaction_status: "bg-yellow-100 text-yellow-600",
        broker_approval: "bg-purple-100 text-purple-600",
    };
    return classes[type] || "bg-gray-100 text-gray-600";
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = (now - date) / (1000 * 60 * 60);

    if (diffInHours < 1) {
        return "Just now";
    } else if (diffInHours < 24) {
        return `${Math.floor(diffInHours)}h ago`;
    } else if (diffInHours < 168) {
        return `${Math.floor(diffInHours / 24)}d ago`;
    } else {
        return date.toLocaleDateString();
    }
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
    if (!event.target.closest(".relative")) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>
