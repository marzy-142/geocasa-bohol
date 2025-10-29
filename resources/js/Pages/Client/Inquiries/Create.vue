<template>
    <Head title="Create Inquiry - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Header Section -->
        <div
            class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-white mb-6"
        >
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Create New Inquiry</h1>
                    <p class="text-blue-100">
                        Submit an inquiry about a property you're interested in
                    </p>
                </div>
                <Link
                    :href="route('client.inquiries.index')"
                    class="inline-flex items-center text-blue-100 hover:text-white text-sm font-medium transition-colors"
                >
                    <svg
                        class="w-4 h-4 mr-2"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        ></path>
                    </svg>
                    Back to My Inquiries
                </Link>
            </div>
        </div>

        <!-- Selected Property Preview -->
        <div
            v-if="showPropertyDetails"
            class="bg-white rounded-lg shadow-sm border border-blue-200 p-6 mb-6"
        >
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div
                        class="w-24 h-24 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center text-white"
                    >
                        <svg
                            class="w-12 h-12"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                            ></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                        {{ showPropertyDetails.title }}
                    </h3>
                    <p class="text-sm text-gray-600 mb-2">
                        {{ showPropertyDetails.type }} in
                        {{ showPropertyDetails.municipality }}
                    </p>
                    <p class="text-xl font-bold text-blue-600">
                        {{ formatPrice(showPropertyDetails.total_price) }}
                    </p>
                    <div
                        v-if="showPropertyDetails.broker"
                        class="mt-3 pt-3 border-t border-gray-200"
                    >
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">Broker:</span>
                            {{ showPropertyDetails.broker.name }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inquiry Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form @submit.prevent="submitInquiry">
                    <div class="space-y-6">
                        <!-- Property Selection -->
                        <div>
                            <label
                                for="property_id"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Select Property *
                            </label>
                            <select
                                id="property_id"
                                v-model="form.property_id"
                                :disabled="!!selectedProperty"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                                required
                            >
                                <option value="">Choose a property...</option>
                                <option
                                    v-for="property in properties"
                                    :key="property.id"
                                    :value="property.id"
                                >
                                    {{ property.title }} -
                                    {{ property.type }} in
                                    {{ property.municipality }} ({{
                                        formatPrice(property.total_price)
                                    }})
                                </option>
                            </select>
                            <p
                                v-if="selectedProperty"
                                class="mt-1 text-sm text-blue-600"
                            >
                                Property pre-selected. To inquire about a
                                different property, go back and select another
                                one.
                            </p>
                            <div
                                v-if="form.errors.property_id"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.property_id }}
                            </div>
                        </div>

                        <!-- Message -->
                        <div>
                            <label
                                for="message"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Your Message <span class="text-red-500">*</span>
                                <span
                                    class="text-gray-500 font-normal text-xs ml-1"
                                    >Be specific to get a faster response</span
                                >
                            </label>
                            <textarea
                                id="message"
                                v-model="form.message"
                                rows="4"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Please describe what you'd like to know about this property..."
                                required
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ form.message.length }}/1000 characters
                            </p>
                            <div
                                v-if="form.errors.message"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.message }}
                            </div>
                        </div>

                        <!-- Budget (Optional) -->
                        <div>
                            <label
                                for="budget_range"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Your Budget Range (Optional)
                                <span
                                    class="text-gray-500 font-normal text-xs ml-1"
                                    >Helps broker show suitable options</span
                                >
                            </label>
                            <input
                                id="budget_range"
                                v-model="form.budget_range"
                                type="text"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., ₱5,000,000 - ₱7,000,000"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Optional: Share your budget to receive tailored
                                recommendations
                            </p>
                        </div>

                        <!-- What Happens Next -->
                        <div
                            class="bg-blue-50 border border-blue-200 rounded-lg p-4"
                        >
                            <div class="flex items-start">
                                <svg
                                    class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                                <div>
                                    <h4
                                        class="font-semibold text-blue-900 text-sm mb-1"
                                    >
                                        What happens after you submit?
                                    </h4>
                                    <ul
                                        class="text-sm text-blue-800 space-y-1 list-disc list-inside"
                                    >
                                        <li>
                                            Your inquiry will be sent to the
                                            assigned broker
                                        </li>
                                        <li>
                                            You will receive a confirmation
                                            email
                                        </li>
                                        <li>
                                            Expect a response within 24–48 hours
                                        </li>
                                        <li>
                                            Track your inquiry in "My Inquiries"
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3">
                            <Link
                                :href="route('client.inquiries.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                            >
                                <span v-if="form.processing"
                                    >Submitting...</span
                                >
                                <span v-else>Submit Inquiry</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { computed } from "vue";

const props = defineProps({
    client: Object,
    properties: Array,
    selectedProperty: Object,
});

const form = useForm({
    property_id: props.selectedProperty?.id || "",
    message: props.selectedProperty
        ? `I am interested in ${props.selectedProperty.title}. Please provide more information.`
        : "",
    budget_range: "",
});

// Show property details if pre-selected
const showPropertyDetails = computed(() => {
    if (props.selectedProperty) {
        return props.selectedProperty;
    }
    if (form.property_id) {
        return props.properties.find((p) => p.id === form.property_id);
    }
    return null;
});

const submitInquiry = () => {
    form.post(route("client.inquiries.store"), {
        onSuccess: () => {
            // Form will redirect to inquiry show page
        },
    });
};

const formatPrice = (price) => {
    if (!price) return "₱0";
    return "₱" + new Intl.NumberFormat("en-PH").format(price);
};
</script>
