<script setup>
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    transaction: Object,
    properties: Array,
    clients: Array,
    inquiries: Array,
    brokers: Array,
});

const form = useForm({
    property_id: props.transaction.property_id,
    client_id: props.transaction.client_id,
    inquiry_id: props.transaction.inquiry_id,
    broker_id: props.transaction.broker_id,
    offered_price: props.transaction.offered_price,
    final_price: props.transaction.final_price || "",
    commission_rate: props.transaction.commission_rate,
    commission_amount: props.transaction.commission_amount || "",
    contract_date: props.transaction.contract_date || "",
    closing_date: props.transaction.closing_date || "",
    notes: props.transaction.notes || "",
    status: props.transaction.status,
});

const submit = () => {
    form.patch(route("transactions.update", props.transaction.id));
};

const selectedProperty = computed(() => {
    return props.properties.find((p) => p.id == form.property_id);
});

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(price);
};

const calculateCommission = () => {
    const price = form.final_price || form.offered_price;
    if (price && form.commission_rate) {
        form.commission_amount = ((price * form.commission_rate) / 100).toFixed(
            2
        );
    }
};

const formatDateForInput = (date) => {
    if (!date) return "";
    return new Date(date).toISOString().split("T")[0];
};
</script>

<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2
                                    class="text-2xl font-semibold text-gray-900"
                                >
                                    Edit Transaction
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
                                            'transactions.show',
                                            transaction.id
                                        )
                                    "
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    View Details
                                </a>
                                <a
                                    :href="route('transactions.index')"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Back to List
                                </a>
                            </div>
                        </div>
                    </div>

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

                            <!-- Price Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Offered Price -->
                                <div>
                                    <label
                                        for="offered_price"
                                        class="block text-sm font-medium text-gray-700"
                                        >Offered Price</label
                                    >
                                    <div
                                        class="mt-1 relative rounded-md shadow-sm"
                                    >
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <span
                                                class="text-gray-500 sm:text-sm"
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

                                <!-- Final Price -->
                                <div>
                                    <label
                                        for="final_price"
                                        class="block text-sm font-medium text-gray-700"
                                        >Final Price (if agreed)</label
                                    >
                                    <div
                                        class="mt-1 relative rounded-md shadow-sm"
                                    >
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <span
                                                class="text-gray-500 sm:text-sm"
                                                >₱</span
                                            >
                                        </div>
                                        <input
                                            v-model="form.final_price"
                                            type="number"
                                            id="final_price"
                                            step="0.01"
                                            @input="calculateCommission"
                                            class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="0.00"
                                        />
                                    </div>
                                    <div
                                        v-if="form.errors.final_price"
                                        class="text-red-600 text-sm mt-1"
                                    >
                                        {{ form.errors.final_price }}
                                    </div>
                                </div>
                            </div>

                            <!-- Commission Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Commission Rate -->
                                <div>
                                    <label
                                        for="commission_rate"
                                        class="block text-sm font-medium text-gray-700"
                                        >Commission Rate (%)</label
                                    >
                                    <div
                                        class="mt-1 relative rounded-md shadow-sm"
                                    >
                                        <input
                                            v-model="form.commission_rate"
                                            type="number"
                                            id="commission_rate"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            @input="calculateCommission"
                                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="5.00"
                                        />
                                        <div
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
                                        >
                                            <span
                                                class="text-gray-500 sm:text-sm"
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

                                <!-- Commission Amount -->
                                <div>
                                    <label
                                        for="commission_amount"
                                        class="block text-sm font-medium text-gray-700"
                                        >Commission Amount</label
                                    >
                                    <div
                                        class="mt-1 relative rounded-md shadow-sm"
                                    >
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                        >
                                            <span
                                                class="text-gray-500 sm:text-sm"
                                                >₱</span
                                            >
                                        </div>
                                        <input
                                            v-model="form.commission_amount"
                                            type="number"
                                            id="commission_amount"
                                            step="0.01"
                                            class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="0.00"
                                        />
                                    </div>
                                    <div
                                        v-if="form.errors.commission_amount"
                                        class="text-red-600 text-sm mt-1"
                                    >
                                        {{ form.errors.commission_amount }}
                                    </div>
                                </div>
                            </div>

                            <!-- Important Dates -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Contract Date -->
                                <div>
                                    <label
                                        for="contract_date"
                                        class="block text-sm font-medium text-gray-700"
                                        >Contract Date</label
                                    >
                                    <input
                                        v-model="form.contract_date"
                                        type="date"
                                        id="contract_date"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                    <div
                                        v-if="form.errors.contract_date"
                                        class="text-red-600 text-sm mt-1"
                                    >
                                        {{ form.errors.contract_date }}
                                    </div>
                                </div>

                                <!-- Closing Date -->
                                <div>
                                    <label
                                        for="closing_date"
                                        class="block text-sm font-medium text-gray-700"
                                        >Closing Date</label
                                    >
                                    <input
                                        v-model="form.closing_date"
                                        type="date"
                                        id="closing_date"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                    <div
                                        v-if="form.errors.closing_date"
                                        class="text-red-600 text-sm mt-1"
                                    >
                                        {{ form.errors.closing_date }}
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label
                                    for="status"
                                    class="block text-sm font-medium text-gray-700"
                                    >Status</label
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
                                    <option value="accepted">Accepted</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="completed">Completed</option>
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
                                    >Notes</label
                                >
                                <textarea
                                    v-model="form.notes"
                                    id="notes"
                                    rows="4"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Add any notes about this transaction..."
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
                                    :href="
                                        route(
                                            'transactions.show',
                                            transaction.id
                                        )
                                    "
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
                                        >Updating...</span
                                    >
                                    <span v-else>Update Transaction</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
