<script setup>
import { ref, onMounted, computed } from "vue";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";

const props = defineProps({
    sellerRequest: {
        type: Object,
        default: () => null,
    },
});

const showDetails = ref(false);

// Check if we have valid seller request data
const hasValidData = computed(() => {
    return props.sellerRequest && props.sellerRequest.id;
});

const formatPrice = (price) => {
    if (!price) return "Not specified";
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(price);
};

const formatDate = (date) => {
    if (!date) return "Not available";
    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Auto-show details after a short delay for better UX
onMounted(() => {
    setTimeout(() => {
        showDetails.value = true;
    }, 1000);
});
</script>

<template>
    <ModernDashboardLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Header -->
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6"
                >
                    <div
                        class="p-6 sm:px-20 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-green-200"
                    >
                        <div class="text-center">
                            <!-- Success Icon -->
                            <div
                                class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4"
                            >
                                <svg
                                    class="h-8 w-8 text-green-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5 13l4 4L19 7"
                                    ></path>
                                </svg>
                            </div>

                            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                                Request Submitted Successfully!
                            </h1>
                            <p class="text-lg text-gray-600 mb-4">
                                Thank you for choosing GeoCasa Bohol to list
                                your property.
                            </p>

                            <!-- Request ID -->
                            <div
                                v-if="hasValidData"
                                class="inline-flex items-center px-4 py-2 bg-green-100 rounded-full"
                            >
                                <span
                                    class="text-sm font-medium text-green-800"
                                >
                                    Request ID: #{{ sellerRequest.id }}
                                </span>
                            </div>
                            <div
                                v-else
                                class="inline-flex items-center px-4 py-2 bg-blue-100 rounded-full"
                            >
                                <span class="text-sm font-medium text-blue-800">
                                    Your request has been submitted and is being
                                    processed
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- What Happens Next -->
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6"
                >
                    <div class="p-6 sm:px-20">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            What Happens Next?
                        </h2>

                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100"
                                    >
                                        <span
                                            class="text-sm font-medium text-blue-600"
                                            >1</span
                                        >
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        Review Process
                                    </h3>
                                    <p class="text-gray-600">
                                        Our team will review your property
                                        details and documentation within 24-48
                                        hours.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100"
                                    >
                                        <span
                                            class="text-sm font-medium text-blue-600"
                                            >2</span
                                        >
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        Broker Assignment
                                    </h3>
                                    <p class="text-gray-600">
                                        We'll assign an experienced broker to
                                        handle your property listing and
                                        marketing.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-100"
                                    >
                                        <span
                                            class="text-sm font-medium text-blue-600"
                                            >3</span
                                        >
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        Property Listing
                                    </h3>
                                    <p class="text-gray-600">
                                        Once approved, your property will be
                                        listed on our platform and marketing
                                        channels.
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div
                                        class="flex items-center justify-center h-8 w-8 rounded-full bg-green-100"
                                    >
                                        <span
                                            class="text-sm font-medium text-green-600"
                                            >4</span
                                        >
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        Contact & Updates
                                    </h3>
                                    <p class="text-gray-600">
                                        We'll contact you via email or phone
                                        with updates and potential buyer
                                        inquiries.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Request Details (only show if we have data) -->
                <div
                    v-if="hasValidData"
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6"
                >
                    <div class="p-6 sm:px-20">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-semibold text-gray-900">
                                Your Request Details
                            </h2>
                            <button
                                @click="showDetails = !showDetails"
                                class="text-blue-600 hover:text-blue-800 font-medium"
                            >
                                {{
                                    showDetails
                                        ? "Hide Details"
                                        : "Show Details"
                                }}
                            </button>
                        </div>

                        <div v-show="showDetails" class="space-y-6">
                            <!-- Seller Information -->
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-3"
                                >
                                    Seller Information
                                </h3>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"
                                >
                                    <div>
                                        <span class="font-medium text-gray-700"
                                            >Name:</span
                                        >
                                        <span class="ml-2 text-gray-900">{{
                                            sellerRequest.seller_name
                                        }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700"
                                            >Email:</span
                                        >
                                        <span class="ml-2 text-gray-900">{{
                                            sellerRequest.seller_email
                                        }}</span>
                                    </div>
                                    <div v-if="sellerRequest.seller_phone">
                                        <span class="font-medium text-gray-700"
                                            >Phone:</span
                                        >
                                        <span class="ml-2 text-gray-900">{{
                                            sellerRequest.seller_phone
                                        }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700"
                                            >Submitted:</span
                                        >
                                        <span class="ml-2 text-gray-900">{{
                                            formatDate(sellerRequest.created_at)
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Information -->
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-3"
                                >
                                    Property Information
                                </h3>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"
                                >
                                    <div class="md:col-span-2">
                                        <span class="font-medium text-gray-700"
                                            >Title:</span
                                        >
                                        <span class="ml-2 text-gray-900">{{
                                            sellerRequest.property_title
                                        }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700"
                                            >Type:</span
                                        >
                                        <span
                                            class="ml-2 text-gray-900 capitalize"
                                            >{{
                                                sellerRequest.property_type
                                            }}</span
                                        >
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700"
                                            >Asking Price:</span
                                        >
                                        <span class="ml-2 text-gray-900">{{
                                            formatPrice(
                                                sellerRequest.asking_price
                                            )
                                        }}</span>
                                    </div>
                                    <div v-if="sellerRequest.property_area">
                                        <span class="font-medium text-gray-700"
                                            >Area:</span
                                        >
                                        <span class="ml-2 text-gray-900"
                                            >{{ sellerRequest.property_area }}
                                            {{ sellerRequest.area_unit }}</span
                                        >
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700"
                                            >Location:</span
                                        >
                                        <span class="ml-2 text-gray-900">{{
                                            sellerRequest.property_location
                                        }}</span>
                                    </div>
                                    <div class="md:col-span-2">
                                        <span class="font-medium text-gray-700"
                                            >Address:</span
                                        >
                                        <span class="ml-2 text-gray-900">
                                            {{
                                                [
                                                    sellerRequest.property_address,
                                                    sellerRequest.city,
                                                    sellerRequest.state,
                                                ]
                                                    .filter(Boolean)
                                                    .join(", ")
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Property Description -->
                                <div class="mt-4">
                                    <span class="font-medium text-gray-700"
                                        >Description:</span
                                    >
                                    <p
                                        class="mt-1 text-gray-900 whitespace-pre-wrap"
                                    >
                                        {{ sellerRequest.property_description }}
                                    </p>
                                </div>

                                <!-- Features -->
                                <div
                                    v-if="
                                        sellerRequest.features &&
                                        sellerRequest.features.length > 0
                                    "
                                    class="mt-4"
                                >
                                    <span class="font-medium text-gray-700"
                                        >Features:</span
                                    >
                                    <div class="mt-1 flex flex-wrap gap-2">
                                        <span
                                            v-for="feature in sellerRequest.features"
                                            :key="feature"
                                            class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"
                                        >
                                            {{ feature }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6"
                >
                    <div class="p-6 sm:px-20">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            Need Help?
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-6 w-6 text-blue-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        ></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        Email Support
                                    </h3>
                                    <p class="text-gray-600">
                                        support@geocasabohol.com
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        We typically respond within 24 hours
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-6 w-6 text-blue-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                        ></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        Phone Support
                                    </h3>
                                    <p class="text-gray-600">+63 38 123 4567</p>
                                    <p class="text-sm text-gray-500">
                                        Monday - Friday, 8:00 AM - 6:00 PM
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20">
                        <div
                            class="flex flex-col sm:flex-row gap-4 justify-center"
                        >
                            <a
                                href="/"
                                class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
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
                                Back to Home
                            </a>

                            <a
                                href="/properties"
                                class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m4 0v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    ></path>
                                </svg>
                                Browse Properties
                            </a>

                            <a
                                href="/seller-requests/create"
                                class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                                    ></path>
                                </svg>
                                Submit Another Property
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Important Notice -->
                <div
                    class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4"
                >
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-5 w-5 text-yellow-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                Important Notice
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>
                                    Please keep your contact information up to
                                    date. We will reach out to you via the email
                                    address and phone number you provided.
                                    <span v-if="hasValidData"
                                        >If you need to update your contact
                                        details, please email us at
                                        support@geocasabohol.com with your
                                        request ID #{{
                                            sellerRequest.id
                                        }}.</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<style scoped>
/* Add any custom animations or styles here */
.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
