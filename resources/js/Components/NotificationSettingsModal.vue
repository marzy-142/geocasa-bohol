<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                @click="$emit('close')"
            ></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                            Notification Settings
                        </h3>
                        <button
                            @click="$emit('close')"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <XMarkIcon class="w-6 h-6" />
                        </button>
                    </div>

                    <form @submit.prevent="saveSettings">
                        <!-- Email Notifications -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">
                                Email Notifications
                            </h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input
                                        v-model="settings.email_new_inquiry"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-3 text-sm text-gray-700">
                                        New inquiries
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        v-model="settings.email_transaction_updates"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-3 text-sm text-gray-700">
                                        Transaction status updates
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        v-model="settings.email_messages"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-3 text-sm text-gray-700">
                                        New messages
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        v-model="settings.email_seller_requests"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-3 text-sm text-gray-700">
                                        New seller requests
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- SMS Notifications -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">
                                SMS Notifications
                            </h4>
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Phone Number
                                </label>
                                <input
                                    v-model="settings.phone_number"
                                    type="tel"
                                    placeholder="+63 9XX XXX XXXX"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                <p class="text-xs text-gray-500 mt-1">
                                    Required for SMS notifications
                                </p>
                            </div>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input
                                        v-model="settings.sms_urgent_inquiries"
                                        type="checkbox"
                                        :disabled="!settings.phone_number"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:opacity-50"
                                    >
                                    <span class="ml-3 text-sm text-gray-700">
                                        Urgent inquiries only
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input
                                        v-model="settings.sms_transaction_milestones"
                                        type="checkbox"
                                        :disabled="!settings.phone_number"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 disabled:opacity-50"
                                    >
                                    <span class="ml-3 text-sm text-gray-700">
                                        Transaction milestones
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Browser Notifications -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">
                                Browser Notifications
                            </h4>
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input
                                        v-model="settings.browser_notifications"
                                        type="checkbox"
                                        @change="handleBrowserNotificationChange"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                    <span class="ml-3 text-sm text-gray-700">
                                        Enable browser notifications
                                    </span>
                                </label>
                                <p v-if="browserNotificationStatus" class="text-xs text-gray-500 ml-6">
                                    {{ browserNotificationStatus }}
                                </p>
                            </div>
                        </div>

                        <!-- Notification Frequency -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">
                                Email Frequency
                            </h4>
                            <select
                                v-model="settings.email_frequency"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                                <option value="immediate">Immediate</option>
                                <option value="hourly">Hourly digest</option>
                                <option value="daily">Daily digest</option>
                                <option value="weekly">Weekly digest</option>
                            </select>
                        </div>

                        <!-- Quiet Hours -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">
                                Quiet Hours
                            </h4>
                            <p class="text-xs text-gray-500 mb-3">
                                No notifications will be sent during these hours
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        From
                                    </label>
                                    <input
                                        v-model="settings.quiet_hours_start"
                                        type="time"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        To
                                    </label>
                                    <input
                                        v-model="settings.quiet_hours_end"
                                        type="time"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3">
                            <button
                                type="button"
                                @click="$emit('close')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="saving"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                {{ saving ? 'Saving...' : 'Save Settings' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['close'])

const saving = ref(false)
const browserNotificationStatus = ref('')

const settings = reactive({
    // Email notifications
    email_new_inquiry: true,
    email_transaction_updates: true,
    email_messages: true,
    email_seller_requests: true,
    email_frequency: 'immediate',
    
    // SMS notifications
    phone_number: '',
    sms_urgent_inquiries: false,
    sms_transaction_milestones: false,
    
    // Browser notifications
    browser_notifications: false,
    
    // Quiet hours
    quiet_hours_start: '22:00',
    quiet_hours_end: '08:00'
})

const loadSettings = async () => {
    try {
        const response = await fetch(route('notifications.settings'))
        const data = await response.json()
        Object.assign(settings, data)
    } catch (error) {
        console.error('Failed to load notification settings:', error)
    }
}

const saveSettings = () => {
    saving.value = true
    router.post(route('notifications.settings.update'), settings, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close')
        },
        onFinish: () => {
            saving.value = false
        }
    })
}

const handleBrowserNotificationChange = () => {
    if (settings.browser_notifications) {
        if ('Notification' in window) {
            if (Notification.permission === 'default') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        browserNotificationStatus.value = 'Browser notifications enabled'
                    } else {
                        settings.browser_notifications = false
                        browserNotificationStatus.value = 'Permission denied. Please enable in browser settings.'
                    }
                })
            } else if (Notification.permission === 'granted') {
                browserNotificationStatus.value = 'Browser notifications enabled'
            } else {
                settings.browser_notifications = false
                browserNotificationStatus.value = 'Permission denied. Please enable in browser settings.'
            }
        } else {
            settings.browser_notifications = false
            browserNotificationStatus.value = 'Browser notifications not supported'
        }
    } else {
        browserNotificationStatus.value = ''
    }
}

onMounted(() => {
    if (props.show) {
        loadSettings()
    }
    
    // Check browser notification permission status
    if ('Notification' in window) {
        if (Notification.permission === 'granted') {
            browserNotificationStatus.value = 'Browser notifications enabled'
            settings.browser_notifications = true
        } else if (Notification.permission === 'denied') {
            browserNotificationStatus.value = 'Permission denied. Please enable in browser settings.'
        }
    }
})
</script>