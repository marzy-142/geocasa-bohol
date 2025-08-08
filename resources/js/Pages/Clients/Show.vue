<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Header -->
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-3xl font-bold text-gray-900">
                                    Client Details
                                </h2>
                                <p class="text-gray-600">
                                    {{ client.name }} - GeoCasa Bohol Client
                                </p>
                            </div>
                            <div class="flex space-x-3">
                                <Link
                                    :href="route('clients.index')"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    ← Back to Clients
                                </Link>
                                <Link
                                    v-if="canEdit"
                                    :href="route('clients.edit', client.id)"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Edit Client
                                </Link>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Client Information -->
                            <div class="lg:col-span-2">
                                <!-- Basic Information Card -->
                                <div
                                    class="bg-white border rounded-lg p-6 mb-6"
                                >
                                    <div
                                        class="flex items-center justify-between mb-4"
                                    >
                                        <h3
                                            class="text-xl font-semibold text-gray-900"
                                        >
                                            Basic Information
                                        </h3>
                                        <div
                                            class="px-3 py-1 rounded-full text-sm font-medium"
                                            :class="
                                                getStatusColor(client.status)
                                            "
                                        >
                                            {{ formatStatus(client.status) }}
                                        </div>
                                    </div>

                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-500"
                                                >Full Name</label
                                            >
                                            <p class="text-lg text-gray-900">
                                                {{ client.name }}
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-500"
                                                >Email</label
                                            >
                                            <p class="text-lg text-gray-900">
                                                <a
                                                    :href="`mailto:${client.email}`"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    {{ client.email }}
                                                </a>
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-500"
                                                >Phone</label
                                            >
                                            <p class="text-lg text-gray-900">
                                                <a
                                                    :href="`tel:${client.phone}`"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    {{ client.phone }}
                                                </a>
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-500"
                                                >Source</label
                                            >
                                            <p class="text-lg text-gray-900">
                                                {{
                                                    formatSource(client.source)
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <label
                                            class="block text-sm font-medium text-gray-500"
                                            >Address</label
                                        >
                                        <p class="text-lg text-gray-900">
                                            {{ client.address }}<br />
                                            {{ client.city }},
                                            {{ client.state }}
                                            {{ client.zip_code }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Property Preferences Card -->
                                <div
                                    class="bg-white border rounded-lg p-6 mb-6"
                                >
                                    <h3
                                        class="text-xl font-semibold text-gray-900 mb-4"
                                    >
                                        Property Preferences
                                    </h3>

                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-500"
                                                >Budget Range</label
                                            >
                                            <p class="text-lg text-gray-900">
                                                {{ client.formatted_budget }}
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-500"
                                                >Preferred Area</label
                                            >
                                            <p class="text-lg text-gray-900">
                                                {{
                                                    client.formatted_preferred_area
                                                }}
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-500"
                                                >Preferred Location</label
                                            >
                                            <p class="text-lg text-gray-900">
                                                {{
                                                    client.preferred_location ||
                                                    "Not specified"
                                                }}
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-500"
                                                >Property Features</label
                                            >
                                            <div
                                                class="flex flex-wrap gap-2 mt-1"
                                            >
                                                <span
                                                    v-for="feature in client.preferred_features"
                                                    :key="feature"
                                                    class="px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded"
                                                >
                                                    {{ formatFeature(feature) }}
                                                </span>
                                                <span
                                                    v-if="
                                                        !client.preferred_features ||
                                                        client
                                                            .preferred_features
                                                            .length === 0
                                                    "
                                                    class="text-gray-500"
                                                >
                                                    No specific features
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="client.notes" class="mt-4">
                                        <label
                                            class="block text-sm font-medium text-gray-500"
                                            >Notes</label
                                        >
                                        <p
                                            class="text-gray-900 whitespace-pre-wrap"
                                        >
                                            {{ client.notes }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Activity Timeline -->
                                <div class="bg-white border rounded-lg p-6">
                                    <h3
                                        class="text-xl font-semibold text-gray-900 mb-4"
                                    >
                                        Activity Timeline
                                    </h3>

                                    <div class="space-y-4">
                                        <div
                                            v-for="activity in activities"
                                            :key="activity.id"
                                            class="flex items-start space-x-3"
                                        >
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm"
                                                    :class="
                                                        getActivityColor(
                                                            activity.type
                                                        )
                                                    "
                                                >
                                                    {{
                                                        getActivityIcon(
                                                            activity.type
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <p
                                                    class="text-sm text-gray-900"
                                                >
                                                    {{ activity.description }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-500"
                                                >
                                                    {{
                                                        formatDate(
                                                            activity.created_at
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            v-if="activities.length === 0"
                                            class="text-center py-8"
                                        >
                                            <div
                                                class="text-gray-400 text-4xl mb-2"
                                            >
                                                📋
                                            </div>
                                            <p class="text-gray-500">
                                                No activity recorded yet
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="space-y-6">
                                <!-- Quick Stats -->
                                <div class="bg-white border rounded-lg p-6">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 mb-4"
                                    >
                                        Quick Stats
                                    </h3>

                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600"
                                                >Total Inquiries</span
                                            >
                                            <span class="font-semibold">{{
                                                client.inquiries_count || 0
                                            }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600"
                                                >Properties Viewed</span
                                            >
                                            <span class="font-semibold">{{
                                                client.properties_viewed || 0
                                            }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600"
                                                >Transactions</span
                                            >
                                            <span class="font-semibold">{{
                                                client.transactions_count || 0
                                            }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600"
                                                >Client Since</span
                                            >
                                            <span class="font-semibold">{{
                                                formatDate(client.created_at)
                                            }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assigned Broker -->
                                <div class="bg-white border rounded-lg p-6">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 mb-4"
                                    >
                                        Assigned Broker
                                    </h3>

                                    <div
                                        v-if="client.broker"
                                        class="flex items-center space-x-3"
                                    >
                                        <div
                                            class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold"
                                        >
                                            {{
                                                client.broker.name
                                                    .charAt(0)
                                                    .toUpperCase()
                                            }}
                                        </div>
                                        <div>
                                            <p
                                                class="font-semibold text-gray-900"
                                            >
                                                {{ client.broker.name }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                {{ client.broker.email }}
                                            </p>
                                        </div>
                                    </div>

                                    <div v-else class="text-center py-4">
                                        <div
                                            class="text-gray-400 text-2xl mb-2"
                                        >
                                            👤
                                        </div>
                                        <p class="text-gray-500">
                                            No broker assigned
                                        </p>
                                    </div>
                                </div>

                                <!-- Recent Inquiries -->
                                <div class="bg-white border rounded-lg p-6">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 mb-4"
                                    >
                                        Recent Inquiries
                                    </h3>

                                    <div class="space-y-3">
                                        <div
                                            v-for="inquiry in client.recent_inquiries"
                                            :key="inquiry.id"
                                            class="border-l-4 border-blue-500 pl-3"
                                        >
                                            <p
                                                class="font-medium text-gray-900"
                                            >
                                                {{ inquiry.property.title }}
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                {{
                                                    inquiry.property
                                                        .municipality
                                                }}, Bohol
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{
                                                    formatDate(
                                                        inquiry.created_at
                                                    )
                                                }}
                                            </p>
                                        </div>

                                        <div
                                            v-if="
                                                !client.recent_inquiries ||
                                                client.recent_inquiries
                                                    .length === 0
                                            "
                                            class="text-center py-4"
                                        >
                                            <div
                                                class="text-gray-400 text-2xl mb-2"
                                            >
                                                💬
                                            </div>
                                            <p class="text-gray-500">
                                                No inquiries yet
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="bg-white border rounded-lg p-6">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 mb-4"
                                    >
                                        Quick Actions
                                    </h3>

                                    <div class="space-y-2">
                                        <button
                                            @click="sendEmail"
                                            class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm"
                                        >
                                            📧 Send Email
                                        </button>
                                        <button
                                            @click="scheduleCall"
                                            class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm"
                                        >
                                            📞 Schedule Call
                                        </button>
                                        <button
                                            @click="addNote"
                                            class="w-full bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm"
                                        >
                                            📝 Add Note
                                        </button>
                                        <button
                                            v-if="canDelete"
                                            @click="deleteClient"
                                            class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm"
                                        >
                                            🗑️ Delete Client
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    client: Object,
    activities: Array,
});

const page = usePage();

const canEdit = computed(() => {
    const user = page.props.auth.user;
    return (
        user.role === "admin" ||
        (user.role === "broker" &&
            user.is_approved &&
            props.client.broker_id === user.id)
    );
});

const canDelete = computed(() => {
    const user = page.props.auth.user;
    return user.role === "admin";
});

const getStatusColor = (status) => {
    const colors = {
        active: "bg-green-100 text-green-800",
        inactive: "bg-gray-100 text-gray-800",
        converted: "bg-blue-100 text-blue-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const formatStatus = (status) => {
    return status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatSource = (source) => {
    const sources = {
        inquiry: "Property Inquiry",
        manual: "Manual Entry",
        referral: "Referral",
        website: "Website Registration",
    };
    return sources[source] || source;
};

const formatFeature = (feature) => {
    return feature.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const getActivityColor = (type) => {
    const colors = {
        inquiry: "bg-blue-500",
        call: "bg-green-500",
        email: "bg-yellow-500",
        meeting: "bg-purple-500",
        note: "bg-gray-500",
    };
    return colors[type] || "bg-gray-500";
};

const getActivityIcon = (type) => {
    const icons = {
        inquiry: "💬",
        call: "📞",
        email: "📧",
        meeting: "🤝",
        note: "📝",
    };
    return icons[type] || "📋";
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const sendEmail = () => {
    window.location.href = `mailto:${props.client.email}?subject=GeoCasa Bohol - Property Inquiry`;
};

const scheduleCall = () => {
    alert("Call scheduling feature coming soon!");
};

const addNote = () => {
    const note = prompt("Add a note about this client:");
    if (note) {
        router.post(route("clients.add-note", props.client.id), {
            note: note,
        });
    }
};

const deleteClient = () => {
    if (
        confirm(
            "Are you sure you want to delete this client? This action cannot be undone."
        )
    ) {
        router.delete(route("clients.destroy", props.client.id));
    }
};
</script>
