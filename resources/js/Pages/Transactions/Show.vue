<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";

const props = defineProps({
    transaction: Object,
});

const showStatusModal = ref(false);
const statusForm = useForm({
    status: props.transaction.status,
    notes: "",
});

const updateStatus = () => {
    statusForm.patch(
        route("transactions.update-status", props.transaction.id),
        {
            onSuccess: () => {
                showStatusModal.value = false;
                statusForm.reset("notes");
            },
        }
    );
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(price);
};

const formatDate = (date) => {
    return date
        ? new Date(date).toLocaleDateString("en-PH", {
              year: "numeric",
              month: "long",
              day: "numeric",
          })
        : "Not set";
};

const getStatusColor = (status) => {
    const colors = {
        pending: "bg-yellow-100 text-yellow-800",
        negotiating: "bg-blue-100 text-blue-800",
        under_review: "bg-purple-100 text-purple-800",
        accepted: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
        cancelled: "bg-gray-100 text-gray-800",
        completed: "bg-emerald-100 text-emerald-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const canUpdateStatus = () => {
    return ["pending", "negotiating", "under_review", "accepted"].includes(
        props.transaction.status
    );
};
</script>

<template>
    <ModernDashboardLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6"
                >
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2
                                    class="text-2xl font-semibold text-gray-900"
                                >
                                    Transaction Details
                                </h2>
                                <p class="mt-2 text-sm text-gray-600">
                                    Transaction #{{
                                        transaction.transaction_number
                                    }}
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <a
                                    :href="
                                        route(
                                            'transactions.edit',
                                            transaction.id
                                        )
                                    "
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Edit
                                </a>
                                <button
                                    @click="showStatusModal = true"
                                    v-if="canUpdateStatus()"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Update Status
                                </button>
                                <a
                                    :href="route('transactions.index')"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Transaction Info -->
                    <div class="lg:col-span-2">
                        <div
                            class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Transaction Information
                                </h3>

                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <!-- Status -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Status</label
                                        >
                                        <span
                                            :class="
                                                getStatusColor(
                                                    transaction.status
                                                )
                                            "
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full capitalize mt-1"
                                        >
                                            {{
                                                transaction.status.replace(
                                                    "_",
                                                    " "
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <!-- Offered Price -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Offered Price</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatPrice(
                                                    transaction.offered_price
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <!-- Final Price -->
                                    <div v-if="transaction.final_price">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Final Price</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatPrice(
                                                    transaction.final_price
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <!-- Commission Rate -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Commission Rate</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ transaction.commission_rate }}%
                                        </p>
                                    </div>

                                    <!-- Commission Amount -->
                                    <div v-if="transaction.commission_amount">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Commission Amount</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatPrice(
                                                    transaction.commission_amount
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <!-- Created Date -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Created</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatDate(
                                                    transaction.created_at
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <!-- Contract Date -->
                                    <div v-if="transaction.contract_date">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Contract Date</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatDate(
                                                    transaction.contract_date
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <!-- Closing Date -->
                                    <div v-if="transaction.closing_date">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Closing Date</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatDate(
                                                    transaction.closing_date
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div v-if="transaction.notes" class="mt-6">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Notes</label
                                    >
                                    <p
                                        class="mt-1 text-sm text-gray-900 whitespace-pre-wrap"
                                    >
                                        {{ transaction.notes }}
                                    </p>
                                </div>

                                <!-- Documents -->
                                <div
                                    v-if="
                                        transaction.documents &&
                                        transaction.documents.length > 0
                                    "
                                    class="mt-6"
                                >
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Documents</label
                                    >
                                    <ul class="mt-1 space-y-1">
                                        <li
                                            v-for="doc in transaction.documents"
                                            :key="doc"
                                            class="text-sm text-blue-600 hover:text-blue-800"
                                        >
                                            <a :href="doc" target="_blank">{{
                                                doc.split("/").pop()
                                            }}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Related Information -->
                    <div class="space-y-6">
                        <!-- Property Information -->
                        <div
                            class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Property
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Title</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ transaction.property.title }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Type</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ transaction.property.type }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Location</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ transaction.property.location }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Listed Price</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{
                                                formatPrice(
                                                    transaction.property.price
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <a
                                        :href="
                                            route(
                                                'properties.show',
                                                transaction.property.id
                                            )
                                        "
                                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
                                    >
                                        View Property Details →
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Client Information -->
                        <div
                            class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Client
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Name</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ transaction.client.name }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Email</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ transaction.client.email }}
                                        </p>
                                    </div>
                                    <div v-if="transaction.client.phone">
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Phone</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ transaction.client.phone }}
                                        </p>
                                    </div>
                                    <a
                                        :href="
                                            route(
                                                'clients.show',
                                                transaction.client.id
                                            )
                                        "
                                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
                                    >
                                        View Client Details →
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Broker Information -->
                        <div
                            class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Assigned Broker
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Name</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ transaction.broker.name }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Email</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ transaction.broker.email }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Related Inquiry -->
                        <div
                            v-if="transaction.inquiry"
                            class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Related Inquiry
                                </h3>
                                <div class="space-y-3">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Type</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{
                                                transaction.inquiry.inquiry_type
                                            }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Status</label
                                        >
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ transaction.inquiry.status }}
                                        </p>
                                    </div>
                                    <a
                                        :href="
                                            route(
                                                'inquiries.show',
                                                transaction.inquiry.id
                                            )
                                        "
                                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
                                    >
                                        View Inquiry Details →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Update Modal -->
        <div
            v-if="showStatusModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        >
            <div
                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
            >
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Update Transaction Status
                    </h3>

                    <form @submit.prevent="updateStatus" class="space-y-4">
                        <div>
                            <label
                                for="status"
                                class="block text-sm font-medium text-gray-700"
                                >New Status</label
                            >
                            <select
                                v-model="statusForm.status"
                                id="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            >
                                <option value="pending">Pending</option>
                                <option value="negotiating">Negotiating</option>
                                <option value="under_review">
                                    Under Review
                                </option>
                                <option value="accepted">Accepted</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div>
                            <label
                                for="status_notes"
                                class="block text-sm font-medium text-gray-700"
                                >Status Update Notes</label
                            >
                            <textarea
                                v-model="statusForm.notes"
                                id="status_notes"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Add notes about this status change..."
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <button
                                type="button"
                                @click="showStatusModal = false"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="statusForm.processing"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                            >
                                <span v-if="statusForm.processing"
                                    >Updating...</span
                                >
                                <span v-else>Update Status</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
