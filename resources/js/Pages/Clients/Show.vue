<template>
    <ModernDashboardLayout>
        <!-- Header Section with Gradient Background -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">{{ client.name }}</h1>
                        <p class="text-blue-100 mt-2">
                            Client Details & Activity
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <button
                            v-if="canEdit"
                            @click="editClient"
                            class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 backdrop-blur-sm"
                        >
                            Edit Client
                        </button>
                        <button
                            v-if="canDelete"
                            @click="deleteClient"
                            class="bg-red-500/80 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Client Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gray-50"
                        >
                            <h3 class="text-lg font-semibold text-gray-900">
                                Basic Information
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Full Name</label
                                    >
                                    <p class="text-gray-900 font-medium">
                                        {{ client.name }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Email</label
                                    >
                                    <p class="text-gray-900">
                                        {{ client.email }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Phone</label
                                    >
                                    <p class="text-gray-900">
                                        {{ client.phone || "Not provided" }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Status</label
                                    >
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="
                                            client.status === 'active'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800'
                                        "
                                    >
                                        {{ client.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Property Preferences Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gray-50"
                        >
                            <h3 class="text-lg font-semibold text-gray-900">
                                Property Preferences
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Budget Range</label
                                    >
                                    <p class="text-gray-900">
                                        {{ formatCurrency(client.budget_min) }}
                                        -
                                        {{ formatCurrency(client.budget_max) }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Preferred Location</label
                                    >
                                    <p class="text-gray-900">
                                        {{
                                            client.preferred_location ||
                                            "Not specified"
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Property Type</label
                                    >
                                    <p class="text-gray-900">
                                        {{ client.property_type || "Any" }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Bedrooms</label
                                    >
                                    <p class="text-gray-900">
                                        {{ client.bedrooms || "Any" }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Timeline Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gray-50"
                        >
                            <h3 class="text-lg font-semibold text-gray-900">
                                Recent Activity
                            </h3>
                        </div>
                        <div class="p-6">
                            <div
                                v-if="activities && activities.length === 0"
                                class="text-center py-12"
                            >
                                <div
                                    class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center"
                                >
                                    <svg
                                        class="w-8 h-8 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        ></path>
                                    </svg>
                                </div>
                                <h4
                                    class="text-lg font-medium text-gray-900 mb-2"
                                >
                                    No Recent Activity
                                </h4>
                                <p class="text-gray-500">
                                    This client hasn't had any recent activity.
                                </p>
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="activity in activities"
                                    :key="activity.id"
                                    class="flex items-start space-x-3 p-4 bg-gray-50 rounded-lg"
                                >
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center"
                                        >
                                            <svg
                                                class="w-4 h-4 text-blue-600"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"
                                                ></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ activity.type }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ activity.description }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            {{
                                                formatDate(activity.created_at)
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Stats & Actions -->
                <div class="space-y-6">
                    <!-- Quick Stats Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gray-50"
                        >
                            <h3 class="text-lg font-semibold text-gray-900">
                                Quick Stats
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600"
                                    >Total Inquiries</span
                                >
                                <span
                                    class="text-lg font-semibold text-gray-900"
                                    >{{ client.inquiries_count || 0 }}</span
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600"
                                    >Properties Viewed</span
                                >
                                <span
                                    class="text-lg font-semibold text-gray-900"
                                    >{{ client.properties_viewed || 0 }}</span
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600"
                                    >Transactions</span
                                >
                                <span
                                    class="text-lg font-semibold text-gray-900"
                                    >{{ client.transactions_count || 0 }}</span
                                >
                            </div>
                            <div class="pt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600"
                                        >Client Since</span
                                    >
                                    <span
                                        class="text-sm font-medium text-gray-900"
                                        >{{
                                            formatDate(client.created_at)
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assigned Broker Card -->
                    <div
                        v-if="client.broker"
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gray-50"
                        >
                            <h3 class="text-lg font-semibold text-gray-900">
                                Assigned Broker
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center"
                                >
                                    <span
                                        class="text-blue-600 font-medium text-lg"
                                        >{{
                                            client.broker.name.charAt(0)
                                        }}</span
                                    >
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ client.broker.name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ client.broker.email }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gray-50"
                        >
                            <h3 class="text-lg font-semibold text-gray-900">
                                Quick Actions
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <button
                                @click="sendEmail"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                            >
                                Send Email
                            </button>
                            <button
                                @click="scheduleCall"
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                            >
                                Schedule Call
                            </button>
                            <button
                                @click="addNote"
                                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                            >
                                Add Note
                            </button>
                        </div>
                    </div>

                    <!-- Recent Inquiries Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 bg-gray-50"
                        >
                            <h3 class="text-lg font-semibold text-gray-900">
                                Recent Inquiries
                            </h3>
                        </div>
                        <div class="p-6">
                            <div
                                v-if="
                                    client.recent_inquiries &&
                                    client.recent_inquiries.length === 0
                                "
                                class="text-center py-8"
                            >
                                <div
                                    class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center"
                                >
                                    <svg
                                        class="w-6 h-6 text-gray-400"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        ></path>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-500">
                                    No recent inquiries
                                </p>
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="inquiry in client.recent_inquiries"
                                    :key="inquiry.id"
                                    class="p-3 bg-gray-50 rounded-lg"
                                >
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{
                                            inquiry.property?.title ||
                                            "Property Inquiry"
                                        }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ formatDate(inquiry.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";

const props = defineProps({
    client: {
        type: Object,
        required: true,
    },
    activities: {
        type: Array,
        default: () => [],
    },
});

const { props: pageProps } = usePage();

const canEdit = computed(() => {
    const user = pageProps.auth.user;
    return (
        user.role === "admin" ||
        (user.role === "broker" && user.id === props.client.broker_id)
    );
});

const canDelete = computed(() => {
    const user = pageProps.auth.user;
    return user.role === "admin";
});

const formatCurrency = (amount) => {
    if (!amount) return "Not specified";
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const editClient = () => {
    router.visit(route("clients.edit", props.client.id));
};

const deleteClient = () => {
    if (confirm("Are you sure you want to delete this client?")) {
        router.delete(route("clients.destroy", props.client.id));
    }
};

const sendEmail = () => {
    // Implement email functionality
    console.log("Send email to client");
};

const scheduleCall = () => {
    // Implement call scheduling functionality
    console.log("Schedule call with client");
};

const addNote = () => {
    // Implement note adding functionality
    console.log("Add note for client");
};
</script>
