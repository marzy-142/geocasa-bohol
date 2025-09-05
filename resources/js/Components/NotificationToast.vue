<template>
    <Teleport to="body">
        <div
            v-if="notifications.length > 0"
            class="fixed top-4 right-4 z-50 space-y-2"
        >
            <TransitionGroup name="toast" tag="div" class="space-y-2">
                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="bg-white rounded-lg shadow-lg border border-gray-200 p-4 max-w-sm cursor-pointer transform transition-all duration-300 hover:scale-105"
                    :class="getNotificationClass(notification.type)"
                    @click="handleClick(notification)"
                >
                    <div class="flex items-start space-x-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center"
                                :class="getIconClass(notification.type)"
                            >
                                <component
                                    :is="getIcon(notification.type)"
                                    class="w-4 h-4"
                                />
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">
                                {{ notification.title }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ notification.message }}
                            </p>
                        </div>

                        <!-- Close button -->
                        <button
                            @click.stop="dismiss(notification.id)"
                            class="flex-shrink-0 text-gray-400 hover:text-gray-600"
                        >
                            <XMarkIcon class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Progress bar for auto-dismiss -->
                    <div
                        v-if="notification.autoDismiss"
                        class="mt-3 w-full bg-gray-200 rounded-full h-1"
                    >
                        <div
                            class="bg-blue-500 h-1 rounded-full transition-all duration-100 ease-linear"
                            :style="{ width: `${notification.progress}%` }"
                        ></div>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import {
    InboxIcon,
    HomeIcon,
    CurrencyDollarIcon,
    ChatBubbleBottomCenterTextIcon,
    CheckCircleIcon,
    BellIcon,
    XMarkIcon,
    UserPlusIcon,
} from "@heroicons/vue/24/outline";

const notifications = ref([]);

const getIcon = (type) => {
    const icons = {
        new_inquiry: InboxIcon,
        seller_request: HomeIcon,
        transaction_status: CurrencyDollarIcon,
        new_message: ChatBubbleBottomCenterTextIcon,
        broker_approval: CheckCircleIcon,
        broker_assignment: UserPlusIcon,
        broker_seller_assignment: UserPlusIcon,
    };
    return icons[type] || BellIcon;
};

const getIconClass = (type) => {
    const classes = {
        new_inquiry: "bg-blue-100 text-blue-600",
        seller_request: "bg-green-100 text-green-600",
        transaction_status: "bg-yellow-100 text-yellow-600",
        new_message: "bg-indigo-100 text-indigo-600",
        broker_approval: "bg-purple-100 text-purple-600",
        broker_assignment: "bg-orange-100 text-orange-600",
        broker_seller_assignment: "bg-orange-100 text-orange-600",
    };
    return classes[type] || "bg-gray-100 text-gray-600";
};

const getNotificationClass = (type) => {
    const classes = {
        new_inquiry: "border-l-4 border-blue-500",
        seller_request: "border-l-4 border-green-500",
        transaction_status: "border-l-4 border-yellow-500",
        new_message: "border-l-4 border-indigo-500",
        broker_approval: "border-l-4 border-purple-500",
        broker_assignment: "border-l-4 border-orange-500",
        broker_seller_assignment: "border-l-4 border-orange-500",
    };
    return classes[type] || "border-l-4 border-gray-500";
};

const handleClick = (notification) => {
    const routes = {
        new_inquiry: () =>
            route("inquiries.show", notification.data.inquiry_id),
        seller_request: () =>
            route("seller-requests.show", notification.data.seller_request_id),
        transaction_status: () =>
            route("transactions.show", notification.data.transaction_id),
        new_message: () =>
            route("conversations.show", notification.data.conversation_id),
        broker_approval: () => route("broker.dashboard"),
        broker_assignment: () =>
            route("seller-requests.show", notification.data.seller_request_id),
        broker_seller_assignment: () =>
            route("seller-requests.show", notification.data.seller_request_id),
    };

    const routeFunction = routes[notification.type];
    if (routeFunction) {
        router.visit(routeFunction());
    }

    dismiss(notification.id);
};

const dismiss = (id) => {
    const index = notifications.value.findIndex((n) => n.id === id);
    if (index > -1) {
        notifications.value.splice(index, 1);
    }
};

const addNotification = (notification) => {
    const id = Date.now() + Math.random();
    const newNotification = {
        id,
        ...notification,
        autoDismiss: notification.autoDismiss !== false,
        progress: 100,
    };

    notifications.value.push(newNotification);

    // Auto-dismiss after 5 seconds with progress bar
    if (newNotification.autoDismiss) {
        const duration = 5000;
        const interval = 50;
        const step = (interval / duration) * 100;

        const timer = setInterval(() => {
            newNotification.progress -= step;
            if (newNotification.progress <= 0) {
                clearInterval(timer);
                dismiss(id);
            }
        }, interval);
    }
};

// Get authentication data from Inertia
const page = usePage();
const user = computed(() => page.props.auth?.user);

// Listen for Echo events
const setupEchoListeners = () => {
    if (window.Echo && user.value) {
        // Listen for new inquiries
        window.Echo.private(`App.Models.User.${user.value.id}`).notification(
            (notification) => {
                if (
                    notification.type ===
                    "App\\Notifications\\NewInquiryNotification"
                ) {
                    addNotification({
                        type: "new_inquiry",
                        title: "New Property Inquiry",
                        message: `New inquiry for ${notification.property_title}`,
                        data: notification,
                    });
                }

                if (
                    notification.type ===
                    "App\\Notifications\\TransactionStatusNotification"
                ) {
                    addNotification({
                        type: "transaction_status",
                        title: "Transaction Update",
                        message: `Transaction status updated to ${notification.new_status.replace(
                            "_",
                            " "
                        )}`,
                        data: notification,
                    });
                }

                if (
                    notification.type ===
                    "App\\Notifications\\MessageNotification"
                ) {
                    addNotification({
                        type: "new_message",
                        title: "New Message",
                        message: `New message from ${notification.sender_name}`,
                        data: notification,
                    });
                }

                if (
                    notification.type ===
                    "App\\Notifications\\SellerRequestNotification"
                ) {
                    addNotification({
                        type: "seller_request",
                        title: "New Seller Request",
                        message: `New seller request from ${notification.client_name}`,
                        data: notification,
                    });
                }

                if (
                    notification.type ===
                    "App\\Notifications\\BrokerAssignmentNotification"
                ) {
                    addNotification({
                        type: "broker_assignment",
                        title: "Client Assignment",
                        message: `Client ${notification.client_name} has been ${notification.action} to you`,
                        data: notification,
                    });
                }

                if (
                    notification.type ===
                    "App\\Notifications\\BrokerSellerAssignmentNotification"
                ) {
                    addNotification({
                        type: "broker_seller_assignment",
                        title: "Seller Request Assignment",
                        message: `Seller request "${notification.property_title}" has been ${notification.action} to you`,
                        data: notification,
                    });
                }
            }
        );
    }
};

// Expose method to add notifications programmatically
defineExpose({
    addNotification,
});

onMounted(() => {
    setupEchoListeners();
});
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>
