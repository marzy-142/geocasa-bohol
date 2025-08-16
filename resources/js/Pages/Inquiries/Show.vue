<template>
    <ModernDashboardLayout>
        <!-- Header Section -->
        <div
            class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-white mb-6"
        >
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Inquiry Details</h1>
                    <p class="text-blue-100 mt-2">
                        View and manage inquiry information
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Link
                        v-if="can.edit"
                        :href="route('inquiries.edit', inquiry.id)"
                        class="bg-white/20 hover:bg-white/30 text-white font-medium py-2 px-4 rounded-lg transition-colors"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="can.respond && !showResponseForm"
                        @click="showResponseForm = true"
                        class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg transition-colors"
                    >
                        Respond
                    </button>
                    <button
                        v-if="can.delete"
                        @click="deleteInquiry"
                        class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-lg transition-colors"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Inquiry Information -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <h3 class="text-lg font-medium text-gray-900">
                                Inquiry Information
                            </h3>
                            <div class="flex space-x-2">
                                <span
                                    :class="getStatusBadgeClass(inquiry.status)"
                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{
                                        inquiry.status.charAt(0).toUpperCase() +
                                        inquiry.status.slice(1)
                                    }}
                                </span>
                                <span
                                    :class="
                                        getTypeBadgeClass(inquiry.inquiry_type)
                                    "
                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{
                                        inquiry.inquiry_type
                                            .charAt(0)
                                            .toUpperCase() +
                                        inquiry.inquiry_type.slice(1)
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">
                                    Contact Details
                                </h4>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <p>
                                        <strong>Name:</strong>
                                        {{ inquiry.name }}
                                    </p>
                                    <p>
                                        <strong>Email:</strong>
                                        {{ inquiry.email }}
                                    </p>
                                    <p v-if="inquiry.phone">
                                        <strong>Phone:</strong>
                                        {{ inquiry.phone }}
                                    </p>
                                    <p>
                                        <strong>Inquiry Date:</strong>
                                        {{ formatDate(inquiry.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">
                                    Status Information
                                </h4>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <p v-if="inquiry.contacted_at">
                                        <strong>Contacted:</strong>
                                        {{ formatDate(inquiry.contacted_at) }}
                                    </p>
                                    <p v-if="inquiry.responded_at">
                                        <strong>Responded:</strong>
                                        {{ formatDate(inquiry.responded_at) }}
                                    </p>
                                    <p v-if="inquiry.scheduled_at">
                                        <strong>Scheduled:</strong>
                                        {{ formatDate(inquiry.scheduled_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h4 class="font-medium text-gray-900 mb-2">
                                Message
                            </h4>
                            <p class="text-gray-600 bg-gray-50 p-4 rounded-lg">
                                {{ inquiry.message }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Property Information -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Property Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">
                                    Property Details
                                </h4>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <p>
                                        <strong>Title:</strong>
                                        {{ inquiry.property.title }}
                                    </p>
                                    <p>
                                        <strong>Type:</strong>
                                        {{ inquiry.property.property_type }}
                                    </p>
                                    <p>
                                        <strong>Price:</strong> ${{
                                            Number(
                                                inquiry.property.price
                                            ).toLocaleString()
                                        }}
                                    </p>
                                    <p>
                                        <strong>Location:</strong>
                                        {{ inquiry.property.municipality }},
                                        {{ inquiry.property.province }}
                                    </p>
                                </div>
                            </div>

                            <div v-if="inquiry.property.broker">
                                <h4 class="font-medium text-gray-900 mb-2">
                                    Broker Information
                                </h4>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <p>
                                        <strong>Name:</strong>
                                        {{ inquiry.property.broker.name }}
                                    </p>
                                    <p>
                                        <strong>Email:</strong>
                                        {{ inquiry.property.broker.email }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <Link
                                :href="
                                    route(
                                        'properties.show',
                                        inquiry.property.id
                                    )
                                "
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                View Full Property Details →
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Client Information -->
                <div
                    v-if="inquiry.client"
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Client Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <p>
                                        <strong>Name:</strong>
                                        {{ inquiry.client.name }}
                                    </p>
                                    <p>
                                        <strong>Email:</strong>
                                        {{ inquiry.client.email }}
                                    </p>
                                    <p v-if="inquiry.client.phone">
                                        <strong>Phone:</strong>
                                        {{ inquiry.client.phone }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <Link
                                :href="route('clients.show', inquiry.client.id)"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                View Full Client Details →
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Broker Response -->
                <div
                    v-if="inquiry.broker_response || inquiry.broker_notes"
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Broker Response
                        </h3>

                        <div v-if="inquiry.broker_response" class="mb-4">
                            <h4 class="font-medium text-gray-900 mb-2">
                                Response
                            </h4>
                            <p class="text-gray-600 bg-gray-50 p-4 rounded-lg">
                                {{ inquiry.broker_response }}
                            </p>
                        </div>

                        <div v-if="inquiry.broker_notes">
                            <h4 class="font-medium text-gray-900 mb-2">
                                Internal Notes
                            </h4>
                            <p
                                class="text-gray-600 bg-yellow-50 p-4 rounded-lg"
                            >
                                {{ inquiry.broker_notes }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Response Form -->
                <div
                    v-if="showResponseForm"
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Send Response
                        </h3>

                        <form
                            @submit.prevent="submitResponse"
                            class="space-y-4"
                        >
                            <div>
                                <label
                                    for="broker_response"
                                    class="block text-sm font-medium text-gray-700"
                                    >Response Message *</label
                                >
                                <textarea
                                    id="broker_response"
                                    v-model="responseForm.broker_response"
                                    rows="4"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    :class="{
                                        'border-red-500':
                                            responseForm.errors.broker_response,
                                    }"
                                    placeholder="Enter your response to the client..."
                                    required
                                ></textarea>
                                <div
                                    v-if="responseForm.errors.broker_response"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ responseForm.errors.broker_response }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        for="status"
                                        class="block text-sm font-medium text-gray-700"
                                        >Status *</label
                                    >
                                    <select
                                        id="status"
                                        v-model="responseForm.status"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{
                                            'border-red-500':
                                                responseForm.errors.status,
                                        }"
                                        required
                                    >
                                        <option value="contacted">
                                            Contacted
                                        </option>
                                        <option value="scheduled">
                                            Scheduled
                                        </option>
                                        <option value="completed">
                                            Completed
                                        </option>
                                        <option value="closed">Closed</option>
                                    </select>
                                    <div
                                        v-if="responseForm.errors.status"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ responseForm.errors.status }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="scheduled_at"
                                        class="block text-sm font-medium text-gray-700"
                                        >Scheduled Date (Optional)</label
                                    >
                                    <input
                                        id="scheduled_at"
                                        v-model="responseForm.scheduled_at"
                                        type="datetime-local"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{
                                            'border-red-500':
                                                responseForm.errors
                                                    .scheduled_at,
                                        }"
                                    />
                                    <div
                                        v-if="responseForm.errors.scheduled_at"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ responseForm.errors.scheduled_at }}
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label
                                    for="broker_notes"
                                    class="block text-sm font-medium text-gray-700"
                                    >Internal Notes (Optional)</label
                                >
                                <textarea
                                    id="broker_notes"
                                    v-model="responseForm.broker_notes"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    :class="{
                                        'border-red-500':
                                            responseForm.errors.broker_notes,
                                    }"
                                    placeholder="Internal notes (not visible to client)..."
                                ></textarea>
                                <div
                                    v-if="responseForm.errors.broker_notes"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ responseForm.errors.broker_notes }}
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end space-x-4"
                            >
                                <button
                                    type="button"
                                    @click="showResponseForm = false"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="responseForm.processing"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    <span v-if="responseForm.processing"
                                        >Sending...</span
                                    >
                                    <span v-else>Send Response</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Transaction Information -->
                <div
                    v-if="inquiry.transaction"
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Related Transaction
                        </h3>
                        <div class="space-y-2 text-sm text-gray-600">
                            <p>
                                <strong>Transaction ID:</strong>
                                {{ inquiry.transaction.id }}
                            </p>
                            <p>
                                <strong>Status:</strong>
                                {{ inquiry.transaction.status }}
                            </p>
                            <p>
                                <strong>Amount:</strong> ${{
                                    Number(
                                        inquiry.transaction.sale_price
                                    ).toLocaleString()
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    inquiry: Object,
    can: Object,
});

const showResponseForm = ref(false);

const responseForm = useForm({
    broker_response: "",
    status: "contacted",
    scheduled_at: "",
    broker_notes: "",
});

const submitResponse = () => {
    responseForm.post(route("inquiries.respond", props.inquiry.id), {
        onSuccess: () => {
            showResponseForm.value = false;
            responseForm.reset();
        },
    });
};

const deleteInquiry = () => {
    if (confirm("Are you sure you want to delete this inquiry?")) {
        router.delete(route("inquiries.destroy", props.inquiry.id));
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        new: "bg-blue-100 text-blue-800",
        contacted: "bg-yellow-100 text-yellow-800",
        scheduled: "bg-purple-100 text-purple-800",
        completed: "bg-green-100 text-green-800",
        closed: "bg-gray-100 text-gray-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getTypeBadgeClass = (type) => {
    const classes = {
        general: "bg-gray-100 text-gray-800",
        viewing: "bg-blue-100 text-blue-800",
        purchase: "bg-green-100 text-green-800",
        information: "bg-yellow-100 text-yellow-800",
    };
    return classes[type] || "bg-gray-100 text-gray-800";
};
</script>
