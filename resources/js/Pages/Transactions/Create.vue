<script setup>
import { ref, computed, watch } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import NotificationService from "@/Services/NotificationService";
import {
    BuildingOfficeIcon,
    UserGroupIcon,
    CurrencyDollarIcon,
    CalendarIcon,
    DocumentTextIcon,
    ArrowRightIcon,
    CheckCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    properties: Array,
    clients: Array,
    inquiries: Array,
    brokers: Array,
    selectedInquiry: Object,
});

const page = usePage();

const form = useForm({
    property_id: "",
    client_id: "",
    inquiry_id: "",
    broker_id: "",
    offered_price: "",
    commission_rate: "",
    inquiry_date: "",
    broker_notes: "",
    status: "inquiry",
});

// Auto-populate form if inquiry is selected
if (props.selectedInquiry) {
    form.property_id = props.selectedInquiry.property_id;
    form.client_id = props.selectedInquiry.client_id;
    form.inquiry_id = props.selectedInquiry.id;
    form.inquiry_date = new Date().toISOString().split("T")[0];
    form.commission_rate = 0.05; // Default 5% commission
}

const submit = () => {
    form.post(route("transactions.store"), {
        onSuccess: (response) => {
            // Broadcast transaction creation to relevant channels
            const transaction = response.props.transaction;
            if (transaction) {
                // Notify client about new transaction
                if (transaction.client_id) {
                    window.Echo.private(
                        `user.${transaction.client_id}`
                    ).whisper("transaction-created", {
                        transaction: transaction,
                        broker: page.props.auth.user,
                        message: `New transaction created for ${transaction.property?.title}`,
                    });
                }

                // Show success notification
                NotificationService.success("Transaction created successfully");
            }
        },
    });
};

const selectedProperty = computed(() => {
    return props.properties.find((p) => p.id == form.property_id);
});

const selectedInquiry = computed(() => {
    return props.inquiries.find((i) => i.id == form.inquiry_id);
});

const selectedClient = computed(() => {
    return props.clients.find((c) => c.id == form.client_id);
});

// Watch for inquiry selection to auto-populate related fields
watch(
    () => form.inquiry_id,
    (newInquiryId) => {
        if (newInquiryId && selectedInquiry.value) {
            const inquiry = selectedInquiry.value;
            form.property_id = inquiry.property_id;
            form.client_id = inquiry.client_id;
            form.inquiry_date = inquiry.created_at
                ? new Date(inquiry.created_at).toISOString().split("T")[0]
                : new Date().toISOString().split("T")[0];
        }
    }
);

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>

<template>
    <ModernDashboardLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Enhanced Header -->
                <div
                    class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-8 text-white shadow-lg mb-8"
                >
                    <div
                        class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
                    >
                            <div>
                            <div class="flex items-center gap-4 mb-3">
                                <div
                                    class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"
                                >
                                    <DocumentTextIcon
                                        class="w-8 h-8 text-white"
                                    />
                                </div>
                                <div>
                                    <h1 class="text-3xl font-bold">
                                        Create Transaction
                                    </h1>
                                    <p class="text-blue-100 text-lg">
                                        {{
                                            selectedInquiry
                                                ? "Convert inquiry to transaction"
                                                : "Start a new property transaction"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <Link
                                :href="route('transactions.index')"
                                class="bg-white/20 hover:bg-white/30 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 backdrop-blur-sm flex items-center justify-center gap-2"
                            >
                                <ArrowRightIcon class="w-5 h-5 rotate-180" />
                                Back to Transactions
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Inquiry Details Card (if converting from inquiry) -->
                <div
                    v-if="selectedInquiry"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8"
                >
                    <div class="flex items-center gap-3 mb-4">
                        <CheckCircleIcon class="w-6 h-6 text-green-600" />
                        <h3 class="text-lg font-semibold text-gray-900">
                            Converting Inquiry to Transaction
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Inquiry Info -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4
                                class="font-medium text-gray-900 mb-3 flex items-center gap-2"
                            >
                                <DocumentTextIcon
                                    class="w-5 h-5 text-gray-500"
                                />
                                Inquiry Details
                            </h4>
                            <div class="space-y-2 text-sm">
                                <p>
                                    <strong>Type:</strong>
                                    {{ selectedInquiry.inquiry_type }}
                                </p>
                                <p>
                                    <strong>Date:</strong>
                                    {{ formatDate(selectedInquiry.created_at) }}
                                </p>
                                <p>
                                    <strong>Status:</strong>
                                    <span
                                        class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs"
                                    >
                                        {{ selectedInquiry.status }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Property Info -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4
                                class="font-medium text-gray-900 mb-3 flex items-center gap-2"
                            >
                                <BuildingOfficeIcon
                                    class="w-5 h-5 text-gray-500"
                                />
                                Property
                            </h4>
                            <div class="space-y-2 text-sm">
                                <p>
                                    <strong>{{
                                        selectedProperty?.title
                                    }}</strong>
                                </p>
                                <p class="text-gray-600">
                                    {{ selectedProperty?.address }}
                                </p>
                                <p class="text-gray-600">
                                    {{ selectedProperty?.municipality }}
                                </p>
                                <p class="font-semibold text-green-600">
                                    {{
                                        formatPrice(
                                            selectedProperty?.total_price
                                        )
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Client Info -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4
                                class="font-medium text-gray-900 mb-3 flex items-center gap-2"
                            >
                                <UserGroupIcon class="w-5 h-5 text-gray-500" />
                                Client
                            </h4>
                            <div class="space-y-2 text-sm">
                                <p>
                                    <strong>{{ selectedClient?.name }}</strong>
                                </p>
                                <p class="text-gray-600">
                                    {{ selectedClient?.email }}
                                </p>
                                <p
                                    v-if="selectedClient?.phone"
                                    class="text-gray-600"
                                >
                                    {{ selectedClient.phone }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Inquiry Message -->
                    <div
                        v-if="selectedInquiry.message"
                        class="mt-4 bg-blue-50 rounded-lg p-4"
                    >
                        <h4 class="font-medium text-gray-900 mb-2">
                            Client Message:
                        </h4>
                        <p class="text-sm text-gray-700">
                            {{ selectedInquiry.message }}
                        </p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Property Selection -->
                            <div>
                                <label
                                    for="property_id"
                                    class="block text-sm font-medium text-gray-700"
                                    >Property</label
                                >
                                <select
                                    v-model="form.property_id"
                                    id="property_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    required
                                >
                                    <option value="">Select a property</option>
                                    <option
                                        v-for="property in properties"
                                        :key="property.id"
                                        :value="property.id"
                                    >
                                        {{ property.title }} -
                                        {{ formatPrice(property.price) }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.property_id"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.property_id }}
                                </div>
                            </div>

                            <!-- Property Details (if selected) -->
                            <div
                                v-if="selectedProperty"
                                class="bg-gray-50 p-4 rounded-lg"
                            >
                                <h4 class="font-medium text-gray-900">
                                    Property Details
                                </h4>
                                <div
                                    class="mt-2 grid grid-cols-2 gap-4 text-sm"
                                >
                                    <div>
                                        <span class="font-medium">Type:</span>
                                        {{ selectedProperty.type }}
                                    </div>
                                    <div>
                                        <span class="font-medium"
                                            >Location:</span
                                        >
                                        {{ selectedProperty.location }}
                                    </div>
                                    <div>
                                        <span class="font-medium"
                                            >Listed Price:</span
                                        >
                                        {{
                                            formatPrice(selectedProperty.price)
                                        }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Status:</span>
                                        {{ selectedProperty.status }}
                                    </div>
                                </div>
                            </div>

                            <!-- Client Selection -->
                            <div>
                                <label
                                    for="client_id"
                                    class="block text-sm font-medium text-gray-700"
                                    >Client</label
                                >
                                <select
                                    v-model="form.client_id"
                                    id="client_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    required
                                >
                                    <option value="">Select a client</option>
                                    <option
                                        v-for="client in clients"
                                        :key="client.id"
                                        :value="client.id"
                                    >
                                        {{ client.name }} - {{ client.email }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.client_id"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.client_id }}
                                </div>
                            </div>

                            <!-- Inquiry Selection (Optional) -->
                            <div>
                                <label
                                    for="inquiry_id"
                                    class="block text-sm font-medium text-gray-700"
                                    >Related Inquiry (Optional)</label
                                >
                                <select
                                    v-model="form.inquiry_id"
                                    id="inquiry_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">No related inquiry</option>
                                    <option
                                        v-for="inquiry in inquiries"
                                        :key="inquiry.id"
                                        :value="inquiry.id"
                                    >
                                        {{ inquiry.name }} -
                                        {{ inquiry.inquiry_type }} ({{
                                            inquiry.created_at
                                        }})
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.inquiry_id"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.inquiry_id }}
                                </div>
                            </div>

                            <!-- Broker Assignment -->
                            <div>
                                <label
                                    for="broker_id"
                                    class="block text-sm font-medium text-gray-700"
                                    >Assigned Broker</label
                                >
                                <select
                                    v-model="form.broker_id"
                                    id="broker_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    required
                                >
                                    <option value="">Select a broker</option>
                                    <option
                                        v-for="broker in brokers"
                                        :key="broker.id"
                                        :value="broker.id"
                                    >
                                        {{ broker.name }} - {{ broker.email }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.broker_id"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.broker_id }}
                                </div>
                            </div>

                            <!-- Offered Price -->
                            <div>
                                <label
                                    for="offered_price"
                                    class="block text-sm font-medium text-gray-700"
                                    >Offered Price</label
                                >
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                    >
                                        <span class="text-gray-500 sm:text-sm"
                                            >₱</span
                                        >
                                    </div>
                                    <input
                                        v-model="form.offered_price"
                                        type="number"
                                        id="offered_price"
                                        step="0.01"
                                        class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="0.00"
                                        required
                                    />
                                </div>
                                <div
                                    v-if="form.errors.offered_price"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.offered_price }}
                                </div>
                            </div>

                            <!-- Commission Rate -->
                            <div>
                                <label
                                    for="commission_rate"
                                    class="block text-sm font-medium text-gray-700"
                                    >Commission Rate (%)</label
                                >
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input
                                        v-model="form.commission_rate"
                                        type="number"
                                        id="commission_rate"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="5.00"
                                    />
                                    <div
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
                                    >
                                        <span class="text-gray-500 sm:text-sm"
                                            >%</span
                                        >
                                    </div>
                                </div>
                                <div
                                    v-if="form.errors.commission_rate"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.commission_rate }}
                                </div>
                            </div>

                            <!-- Initial Status -->
                            <div>
                                <label
                                    for="status"
                                    class="block text-sm font-medium text-gray-700"
                                    >Initial Status</label
                                >
                                <select
                                    v-model="form.status"
                                    id="status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="negotiating">
                                        Negotiating
                                    </option>
                                    <option value="under_review">
                                        Under Review
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.status"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.status }}
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label
                                    for="notes"
                                    class="block text-sm font-medium text-gray-700"
                                    >Initial Notes</label
                                >
                                <textarea
                                    v-model="form.notes"
                                    id="notes"
                                    rows="4"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Add any initial notes about this transaction..."
                                ></textarea>
                                <div
                                    v-if="form.errors.notes"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.notes }}
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div
                                class="flex items-center justify-end space-x-4"
                            >
                                <a
                                    :href="route('transactions.index')"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                                >
                                    Cancel
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    <span v-if="form.processing"
                                        >Creating...</span
                                    >
                                    <span v-else>Create Transaction</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
