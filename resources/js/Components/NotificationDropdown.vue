<template>
    <div class="relative">
        <!-- Notification Bell -->
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg transition-colors duration-200"
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
            <!-- Enhanced Notification Badge with animation -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium shadow-lg animate-pulse"
                :class="{
                    'animate-bounce': hasNewNotification,
                }"
            >
                {{ unreadCount > 99 ? "99+" : unreadCount }}
            </span>
            <!-- New notification indicator dot -->
            <span
                v-if="hasNewNotification"
                class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-red-400 rounded-full animate-ping"
            ></span>
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
                        <span
                            v-if="unreadCount > 0"
                            class="ml-2 text-sm font-normal text-gray-500"
                        >
                            ({{ unreadCount }} unread)
                        </span>
                    </h3>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="text-sm text-blue-600 hover:text-blue-800 transition-colors"
                    >
                        Mark all read
                    </button>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="max-h-96 overflow-y-auto">
                <div
                    v-if="localNotifications.length === 0"
                    class="px-4 py-8 text-center text-gray-500"
                >
                    <BellIcon class="w-8 h-8 mx-auto mb-2 text-gray-300" />
                    <p>No notifications yet</p>
                </div>

                <div
                    v-for="notification in localNotifications"
                    :key="notification.id"
                    class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors duration-150"
                    :class="{
                        'bg-blue-50 border-l-4 border-l-blue-500':
                            !notification.read_at,
                        'animate-pulse': notification.isNew,
                    }"
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
                                {{ getNotificationMessage(notification) }}
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
                <div class="flex items-center justify-between">
                    <Link
                        :href="route('notifications.index')"
                        class="text-sm text-blue-600 hover:text-blue-800"
                    >
                        View all notifications
                    </Link>
                    <button
                        @click="openSettings"
                        class="text-sm text-gray-600 hover:text-gray-800 flex items-center space-x-1"
                    >
                        <Cog6ToothIcon class="w-4 h-4" />
                        <span>Settings</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Settings Modal -->
    <NotificationSettingsModal
        :show="showSettingsModal"
        @close="closeSettings"
    />
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import NotificationService from "@/Services/NotificationService";
import NotificationSettingsModal from "@/Components/NotificationSettingsModal.vue";
import {
    InboxIcon,
    HomeIcon,
    CurrencyDollarIcon,
    ChatBubbleBottomCenterTextIcon,
    CheckCircleIcon,
    BellIcon,
    Cog6ToothIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },
});

// Get authentication data from Inertia
const page = usePage();
const user = computed(() => page.props.auth?.user);

// Local reactive notifications state (guard against null/invalid shapes)
// Some parents pass `notifications: null` or an object; ensure we always work with an array
const initialNotifications = Array.isArray(props.notifications)
    ? props.notifications
    : [];
const localNotifications = ref([...initialNotifications]);
const hasNewNotification = ref(false);
const newNotificationTimeout = ref(null);

// Watch for prop changes and update local state
watch(
    () => props.notifications,
    (newNotifications) => {
        localNotifications.value = Array.isArray(newNotifications)
            ? [...newNotifications]
            : [];
    },
    { deep: true }
);

// Initialize browser notifications
onMounted(async () => {
    await NotificationService.init();
    setupEchoListeners();
});

const showDropdown = ref(false);
const showSettingsModal = ref(false);

const unreadCount = computed(() => {
    return localNotifications.value.filter((n) => !n.read_at).length;
});

// Setup Echo listeners for real-time notifications
let echoChannel = null;

const setupEchoListeners = () => {
    if (window.Echo && user.value) {
        try {
            echoChannel = window.Echo.private(
                `App.Models.User.${user.value.id}`
            );
            echoChannel.notification((notification) => {
                // Add new notification to local state
                const newNotification = {
                    id: notification.id || Date.now(),
                    type: notification.type,
                    data: notification,
                    created_at: new Date().toISOString(),
                    read_at: null,
                    isNew: true, // Flag for animation
                };

                // Add to beginning of array
                localNotifications.value.unshift(newNotification);

                // Trigger badge animation
                hasNewNotification.value = true;

                // Clear animation after 3 seconds
                if (newNotificationTimeout.value) {
                    clearTimeout(newNotificationTimeout.value);
                }
                newNotificationTimeout.value = setTimeout(() => {
                    hasNewNotification.value = false;
                    // Remove isNew flag from notification
                    const notificationIndex =
                        localNotifications.value.findIndex(
                            (n) => n.id === newNotification.id
                        );
                    if (notificationIndex !== -1) {
                        localNotifications.value[
                            notificationIndex
                        ].isNew = false;
                    }
                }, 3000);

                // Show browser notification if enabled
                showBrowserNotification(notification);

                // Play sound if enabled
                playNotificationSound();
            });
        } catch (error) {
            console.warn("⚠️ Failed to setup Echo listeners:", error);
            console.log(
                "📡 Real-time notifications disabled, using polling fallback"
            );
        }
    } else {
        // Suppress warning for development - real-time notifications are optional
        console.log(
            "📡 Real-time notifications not available, using polling fallback"
        );
    }
};

// Show browser notification
const showBrowserNotification = (notification) => {
    if (Notification.permission === "granted") {
        const title = getNotificationTitle(notification.type);
        const body =
            notification.message ||
            notification.data?.message ||
            "You have a new notification";

        new Notification(title, {
            body,
            icon: "/favicon.ico",
            badge: "/favicon.ico",
            tag: notification.id,
        });
    }
};

// Play notification sound
const playNotificationSound = () => {
    try {
        // TODO: Add notification.mp3 to public/sounds/ directory
        const audio = new Audio("/sounds/notification.mp3");
        audio.volume = 0.3;
        audio.play().catch(() => {
            // Ignore audio play errors (user interaction required or file not found)
        });
    } catch (error) {
        // Ignore audio errors silently
    }
};

// Get notification title based on type
const getNotificationTitle = (type) => {
    const titles = {
        "App\\Notifications\\NewInquiryNotification": "New Property Inquiry",
        "App\\Notifications\\TransactionStatusNotification":
            "Transaction Update",
        "App\\Notifications\\MessageNotification": "New Message",
        "App\\Notifications\\SellerRequestNotification": "New Seller Request",
        "App\\Notifications\\BrokerApprovalNotification": "Broker Approval",
        "App\\Notifications\\BrokerAssignmentNotification": "Client Assignment",
    };
    return titles[type] || "New Notification";
};

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const openSettings = () => {
    showSettingsModal.value = true;
    showDropdown.value = false;
};

const closeSettings = () => {
    showSettingsModal.value = false;
};

const handleNotificationClick = (notification) => {
    // Mark as read if unread
    if (!notification.read_at) {
        // Use axios instead of router.post for proper JSON request
        window.axios
            .post(
                route("notifications.mark-read", notification.id),
                {},
                {
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                    },
                }
            )
            .then((response) => {
                // Update local state
                const index = localNotifications.value.findIndex(
                    (n) => n.id === notification.id
                );
                if (index !== -1) {
                    localNotifications.value[index].read_at =
                        new Date().toISOString();
                }
            })
            .catch((error) => {
                console.error("Failed to mark notification as read:", error);
            });
    }

    // Close dropdown first
    showDropdown.value = false;

    // Get notification type and data
    const notifType = notification.data?.type;
    const notifData = notification.data;

    // Navigate based on notification type
    try {
        let targetRoute = null;

        switch (notifType) {
            case "new_inquiry":
                if (notifData.inquiry_id) {
                    targetRoute = route("inquiries.show", notifData.inquiry_id);
                }
                break;

            case "seller_request":
            case "broker_seller_assignment":
                if (notifData.seller_request_id) {
                    targetRoute = route(
                        "seller-requests.show",
                        notifData.seller_request_id
                    );
                }
                break;

            case "transaction_status":
                if (notifData.transaction_id) {
                    targetRoute = route(
                        "transactions.show",
                        notifData.transaction_id
                    );
                }
                break;

            case "new_message":
                if (notifData.conversation_id) {
                    targetRoute = route(
                        "conversations.show",
                        notifData.conversation_id
                    );
                }
                break;

            case "broker_approval":
                // Redirect to broker dashboard
                targetRoute = route("broker.dashboard");
                break;

            case "broker_assignment":
                if (notifData.client_id) {
                    targetRoute = route("clients.show", notifData.client_id);
                }
                break;

            // Add more cases as needed
            default:
                // For unhandled types, try to redirect to notifications page
                console.log("Unhandled notification type:", notifType);
                targetRoute = route("notifications.index");
                break;
        }

        if (targetRoute) {
            router.visit(targetRoute);
        }
    } catch (error) {
        console.error("Error navigating from notification:", error);
        // Fallback to notifications page
        router.visit(route("notifications.index"));
    }
};

const markAllAsRead = () => {
    // Use axios for proper JSON request
    window.axios
        .post(
            route("notifications.mark-all-read"),
            {},
            {
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            }
        )
        .then((response) => {
            // Update local state
            localNotifications.value.forEach((notification) => {
                notification.read_at = new Date().toISOString();
            });
        })
        .catch((error) => {
            console.error("Failed to mark all notifications as read:", error);
        });
};

const getNotificationIcon = (type) => {
    const icons = {
        new_inquiry: "InboxIcon",
        seller_request: "HomeIcon",
        broker_seller_assignment: "HomeIcon",
        transaction_status: "CurrencyDollarIcon",
        new_message: "ChatBubbleBottomCenterTextIcon",
        broker_approval: "CheckCircleIcon",
        broker_assignment: "InboxIcon",
        new_broker_application: "InboxIcon",
        broker_application_status_change: "InboxIcon",
        failed_prc_verification: "ExclamationTriangleIcon",
        incomplete_broker_applications: "BellIcon",
        daily_broker_summary: "BellIcon",
    };
    return icons[type] || "BellIcon";
};

const getNotificationIconClass = (type) => {
    const classes = {
        new_inquiry: "bg-blue-100 text-blue-600",
        seller_request: "bg-green-100 text-green-600",
        broker_seller_assignment: "bg-green-100 text-green-600",
        transaction_status: "bg-yellow-100 text-yellow-600",
        new_message: "bg-indigo-100 text-indigo-600",
        broker_approval: "bg-purple-100 text-purple-600",
        broker_assignment: "bg-orange-100 text-orange-600",
        new_broker_application: "bg-blue-100 text-blue-600",
        broker_application_status_change: "bg-blue-100 text-blue-600",
        failed_prc_verification: "bg-red-100 text-red-600",
        incomplete_broker_applications: "bg-gray-100 text-gray-600",
        daily_broker_summary: "bg-gray-100 text-gray-600",
    };
    return classes[type] || "bg-gray-100 text-gray-600";
};

// Build a human-readable message if backend didn't include one (legacy rows)
const getNotificationMessage = (notification) => {
    const data = notification.data || {};
    if (data.message) return data.message;
    switch (data.type) {
        case "new_broker_application":
            return `New broker application submitted: ${
                data.applicant_name || "Unknown applicant"
            }`;
        case "broker_application_status_change":
            return `Broker application status changed: ${
                data.old_status || "?"
            } → ${data.new_status || "?"}`;
        case "failed_prc_verification":
            return `PRC verification failed: ${
                data.verification_error || "Unknown error"
            }`;
        case "incomplete_broker_applications":
            return `Incomplete broker applications: ${
                data.incomplete_count ?? "N/A"
            }`;
        case "daily_broker_summary":
            return `Daily summary: ${data.new_applications ?? 0} new, ${
                data.pending_applications ?? 0
            } pending.`;
        default:
            return "New notification";
    }
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
    setupEchoListeners();
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);

    // Clean up Echo listeners
    if (echoChannel) {
        echoChannel.stopListening(".notification");
        window.Echo.leave(`App.Models.User.${user.value?.id}`);
        echoChannel = null;
    }

    // Clean up timeouts
    if (newNotificationTimeout.value) {
        clearTimeout(newNotificationTimeout.value);
    }
});
</script>
