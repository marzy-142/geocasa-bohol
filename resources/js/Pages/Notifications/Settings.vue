<template>
    <Head title="Notification Settings" />

    <ModernDashboardLayout>
        <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">
                    Notification Settings
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Manage how and when you receive notifications for different
                    activities.
                </p>
            </div>

            <!-- Settings Form -->
            <div class="bg-white shadow rounded-lg">
                <form
                    @submit.prevent="saveSettings"
                    class="divide-y divide-gray-200"
                >
                    <!-- Email Notifications -->
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">
                                    Email Notifications
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Choose which email notifications you'd like
                                    to receive.
                                </p>
                            </div>
                            <div class="flex items-center">
                                <input
                                    v-model="allEmailEnabled"
                                    @change="toggleAllEmail"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                                <label class="ml-2 text-sm text-gray-700"
                                    >Enable all</label
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                            >
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        New Inquiries
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        When someone submits a new property
                                        inquiry
                                    </p>
                                </div>
                                <input
                                    v-model="form.email_new_inquiries"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                            </div>

                            <div
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                            >
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Transaction Updates
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Status changes in property transactions
                                    </p>
                                </div>
                                <input
                                    v-model="form.email_transaction_updates"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                            </div>

                            <div
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                            >
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        New Messages
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        When you receive new messages from
                                        clients
                                    </p>
                                </div>
                                <input
                                    v-model="form.email_new_messages"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                            </div>

                            <div
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                            >
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Seller Requests
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        New property listing requests from
                                        sellers
                                    </p>
                                </div>
                                <input
                                    v-model="form.email_seller_requests"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                            </div>

                            <div
                                v-if="$page.props.auth.user.role === 'broker'"
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                            >
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Client Assignments
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        When clients are assigned or reassigned
                                        to you
                                    </p>
                                </div>
                                <input
                                    v-model="form.email_broker_assignments"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                            </div>
                        </div>

                        <div class="mt-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Email Frequency</label
                            >
                            <select
                                v-model="form.email_frequency"
                                class="block w-full md:w-1/3 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            >
                                <option value="immediate">Immediate</option>
                                <option value="hourly">Hourly digest</option>
                                <option value="daily">Daily digest</option>
                                <option value="weekly">Weekly digest</option>
                            </select>
                        </div>
                    </div>

                    <!-- SMS Notifications -->
                    <div class="p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                SMS Notifications
                            </h3>
                            <p class="text-sm text-gray-500">
                                Receive text messages for urgent notifications.
                            </p>
                        </div>

                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Phone Number</label
                            >
                            <input
                                v-model="form.phone_number"
                                type="tel"
                                placeholder="+63 9XX XXX XXXX"
                                class="block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            />
                            <p class="text-xs text-gray-500 mt-1">
                                Required for SMS notifications. Include country
                                code.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                            >
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Urgent Inquiries
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        High-priority property inquiries only
                                    </p>
                                </div>
                                <input
                                    v-model="form.sms_urgent_inquiries"
                                    :disabled="!form.phone_number"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded disabled:opacity-50"
                                />
                            </div>

                            <div
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                            >
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Transaction Milestones
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Important transaction status changes
                                    </p>
                                </div>
                                <input
                                    v-model="form.sms_transaction_milestones"
                                    :disabled="!form.phone_number"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded disabled:opacity-50"
                                />
                            </div>

                            <div
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                            >
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Important Messages
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Urgent messages from clients
                                    </p>
                                </div>
                                <input
                                    v-model="form.sms_important_messages"
                                    :disabled="!form.phone_number"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded disabled:opacity-50"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Browser Notifications -->
                    <div class="p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                Browser Notifications
                            </h3>
                            <p class="text-sm text-gray-500">
                                Get real-time notifications in your browser.
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                            >
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Enable Browser Notifications
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Show desktop notifications for real-time
                                        alerts
                                    </p>
                                </div>
                                <input
                                    v-model="form.browser_enabled"
                                    @change="handleBrowserNotificationChange"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                            </div>

                            <div
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                            >
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Sound Notifications
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Play sound when notifications arrive
                                    </p>
                                </div>
                                <input
                                    v-model="form.browser_sound"
                                    :disabled="!form.browser_enabled"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded disabled:opacity-50"
                                />
                            </div>

                            <div
                                v-if="browserNotificationStatus"
                                class="p-3 rounded-md"
                                :class="{
                                    'bg-green-50 text-green-700':
                                        browserNotificationStatus.includes(
                                            'enabled'
                                        ),
                                    'bg-yellow-50 text-yellow-700':
                                        browserNotificationStatus.includes(
                                            'denied'
                                        ),
                                    'bg-red-50 text-red-700':
                                        browserNotificationStatus.includes(
                                            'not supported'
                                        ),
                                }"
                            >
                                <p class="text-sm">
                                    {{ browserNotificationStatus }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quiet Hours -->
                    <div class="p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                Quiet Hours
                            </h3>
                            <p class="text-sm text-gray-500">
                                Set times when you don't want to receive
                                notifications.
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                            >
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Enable Quiet Hours
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        Pause notifications during specified
                                        hours
                                    </p>
                                </div>
                                <input
                                    v-model="form.quiet_hours_enabled"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                            </div>

                            <div
                                v-if="form.quiet_hours_enabled"
                                class="grid grid-cols-1 md:grid-cols-2 gap-4"
                            >
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Start Time</label
                                    >
                                    <input
                                        v-model="form.quiet_hours_start"
                                        type="time"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >End Time</label
                                    >
                                    <input
                                        v-model="form.quiet_hours_end"
                                        type="time"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="px-6 py-4 bg-gray-50 flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save Settings</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({}),
    },
});

const browserNotificationStatus = ref("");

const form = useForm({
    email_new_inquiries: props.settings.email_new_inquiries ?? true,
    email_transaction_updates: props.settings.email_transaction_updates ?? true,
    email_new_messages: props.settings.email_new_messages ?? true,
    email_seller_requests: props.settings.email_seller_requests ?? true,
    email_frequency: props.settings.email_frequency ?? "immediate",
    phone_number: props.settings.phone_number ?? "",
    sms_urgent_inquiries: props.settings.sms_urgent_inquiries ?? false,
    sms_transaction_milestones:
        props.settings.sms_transaction_milestones ?? false,
    sms_important_messages: props.settings.sms_important_messages ?? false,
    browser_enabled: props.settings.browser_enabled ?? true,
    browser_sound: props.settings.browser_sound ?? true,
    quiet_hours_enabled: props.settings.quiet_hours_enabled ?? false,
    quiet_hours_start: props.settings.quiet_hours_start ?? "22:00",
    quiet_hours_end: props.settings.quiet_hours_end ?? "08:00",
});

const allEmailEnabled = computed({
    get() {
        return (
            form.email_new_inquiries &&
            form.email_transaction_updates &&
            form.email_new_messages &&
            form.email_seller_requests
        );
    },
    set(value) {
        // This will be handled by toggleAllEmail method
    },
});

const toggleAllEmail = () => {
    const newValue = !allEmailEnabled.value;
    form.email_new_inquiries = newValue;
    form.email_transaction_updates = newValue;
    form.email_new_messages = newValue;
    form.email_seller_requests = newValue;
};

const handleBrowserNotificationChange = () => {
    if (form.browser_enabled) {
        if ("Notification" in window) {
            if (Notification.permission === "default") {
                Notification.requestPermission().then((permission) => {
                    if (permission === "granted") {
                        browserNotificationStatus.value =
                            "Browser notifications enabled successfully";
                    } else {
                        form.browser_enabled = false;
                        browserNotificationStatus.value =
                            "Permission denied. Please enable notifications in your browser settings.";
                    }
                });
            } else if (Notification.permission === "granted") {
                browserNotificationStatus.value =
                    "Browser notifications are already enabled";
            } else {
                form.browser_enabled = false;
                browserNotificationStatus.value =
                    "Permission denied. Please enable notifications in your browser settings.";
            }
        } else {
            form.browser_enabled = false;
            browserNotificationStatus.value =
                "Browser notifications are not supported in this browser";
        }
    } else {
        browserNotificationStatus.value = "";
    }
};

const saveSettings = () => {
    form.post(route("notifications.settings"), {
        onSuccess: () => {
            // Settings saved successfully
        },
        onError: (errors) => {
            console.error("Failed to save notification settings:", errors);
        },
    });
};

onMounted(() => {
    // Check browser notification permission status
    if ("Notification" in window) {
        if (Notification.permission === "granted") {
            browserNotificationStatus.value =
                "Browser notifications are enabled";
        } else if (Notification.permission === "denied") {
            browserNotificationStatus.value =
                "Browser notifications are blocked. Please enable them in your browser settings.";
        }
    } else {
        browserNotificationStatus.value =
            "Browser notifications are not supported in this browser";
    }
});
</script>
