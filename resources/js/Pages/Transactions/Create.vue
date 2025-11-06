<script setup>
import { ref, computed, watch } from "vue";
import { useForm, usePage, Link } from "@inertiajs/vue3";
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
    InformationCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    properties: Array,
    clients: Array,
    inquiries: Array,
    brokers: Array,
    selectedInquiry: Object,
});

const page = usePage();

// Determine if we're converting from an inquiry
const isFromInquiry = computed(() => !!props.selectedInquiry);

// Smart form initialization
const form = useForm({
    property_id: props.selectedInquiry?.property_id || "",
    client_id: props.selectedInquiry?.client_id || "",
    inquiry_id: props.selectedInquiry?.id || "",
    broker_id: page.props.auth.user.id, // Always use current user
    offered_price: props.selectedInquiry?.property?.total_price || "",
    inquiry_date:
        props.selectedInquiry?.created_at?.split("T")[0] ||
        new Date().toISOString().split("T")[0],
    broker_notes: "",
    status: "offer_made",
});

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
                    <div class="p-8">
                        <!-- Information Banner -->
                        <div
                            class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6"
                        >
                            <div class="flex items-start gap-3">
                                <InformationCircleIcon
                                    class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0"
                                />
                                <div class="text-sm text-blue-900">
                                    <p v-if="isFromInquiry" class="font-medium">
                                        All details have been automatically
                                        filled from the inquiry.
                                    </p>
                                    <p v-else class="font-medium">
                                        Please provide the transaction details
                                        below.
                                    </p>
                                    <p class="mt-1 text-blue-700">
                                        Review the information and adjust the
                                        offer price if needed.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Only show selectors if NOT from inquiry -->
                            <div v-if="!isFromInquiry" class="space-y-6">
                                <!-- Property Selection -->
                                <div>
                                    <label
                                        for="property_id"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Property
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.property_id"
                                        id="property_id"
                                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    >
                                        <option value="">
                                            Select a property
                                        </option>
                                        <option
                                            v-for="property in properties"
                                            :key="property.id"
                                            :value="property.id"
                                        >
                                            {{ property.title }} -
                                            {{
                                                formatPrice(
                                                    property.total_price ??
                                                        property.price
                                                )
                                            }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.property_id"
                                        class="text-red-600 text-sm mt-1"
                                    >
                                        {{ form.errors.property_id }}
                                    </div>
                                </div>

                                <!-- Client Selection -->
                                <div>
                                    <label
                                        for="client_id"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Client
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.client_id"
                                        id="client_id"
                                        class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required
                                    >
                                        <option value="">
                                            Select a client
                                        </option>
                                        <option
                                            v-for="client in clients"
                                            :key="client.id"
                                            :value="client.id"
                                        >
                                            {{ client.name }} -
                                            {{ client.email }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.client_id"
                                        class="text-red-600 text-sm mt-1"
                                    >
                                        {{ form.errors.client_id }}
                                    </div>
                                </div>
                            </div>

                            <!-- Offered Price (Always shown, editable) -->
                            <div>
                                <label
                                    for="offered_price"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    {{
                                        isFromInquiry
                                            ? "Initial Offer Price"
                                            : "Offered Price"
                                    }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-lg shadow-sm">
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
                                        step="1"
                                        min="0"
                                        class="pl-8 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="0"
                                        required
                                    />
                                </div>
                                <p
                                    v-if="isFromInquiry && selectedProperty"
                                    class="mt-1.5 text-xs text-gray-500"
                                >
                                    Listed price:
                                    {{
                                        formatPrice(
                                            selectedProperty.total_price
                                        )
                                    }}
                                </p>
                                <div
                                    v-if="form.errors.offered_price"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.offered_price }}
                                </div>
                            </div>

                            <!-- Broker Notes (Optional) -->
                            <div>
                                <label
                                    for="broker_notes"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Initial Notes
                                    <span class="text-gray-400 text-xs"
                                        >(Optional)</span
                                    >
                                </label>
                                <textarea
                                    v-model="form.broker_notes"
                                    id="broker_notes"
                                    rows="4"
                                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Add any initial notes about this transaction, negotiation points, or special considerations..."
                                ></textarea>
                                <p class="mt-1.5 text-xs text-gray-500">
                                    These notes are private and only visible to
                                    you and admins.
                                </p>
                                <div
                                    v-if="form.errors.broker_notes"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.broker_notes }}
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div
                                class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200"
                            >
                                <Link
                                    :href="route('transactions.index')"
                                    class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed rounded-lg transition-colors flex items-center gap-2"
                                >
                                    <CheckCircleIcon
                                        v-if="!form.processing"
                                        class="w-5 h-5"
                                    />
                                    <svg
                                        v-else
                                        class="w-5 h-5 animate-spin"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    <span>{{
                                        form.processing
                                            ? "Creating Transaction..."
                                            : "Create Transaction"
                                    }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
