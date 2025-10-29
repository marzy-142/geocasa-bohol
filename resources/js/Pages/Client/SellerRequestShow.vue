<template>
    <ModernDashboardLayout>
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <Link
                    :href="route('client.seller-requests.index')"
                    class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
                >
                    <ChevronLeftIcon class="w-4 h-4 mr-1" />
                    Back to My Requests
                </Link>
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            {{ sellerRequest.property_title }}
                        </h1>
                        <p class="text-gray-600">
                            Submitted
                            {{ formatDate(sellerRequest.submission_date) }}
                        </p>
                    </div>
                    <span
                        :class="getStatusBadgeClass(sellerRequest.status)"
                        class="px-4 py-2 rounded-full text-sm font-semibold"
                    >
                        {{ sellerRequest.status.replace("_", " ") }}
                    </span>
                </div>
            </div>

            <!-- Main Content -->
            <div class="space-y-6">
                <!-- Property Details Card -->
                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                >
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        Property Details
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-500 mb-1"
                                >Location</label
                            >
                            <p class="text-gray-900">
                                {{ sellerRequest.address }}<br />
                                {{ sellerRequest.barangay }},
                                {{ sellerRequest.municipality }}<br />
                                Bohol
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-500 mb-1"
                                >Lot Area</label
                            >
                            <p class="text-gray-900">
                                {{ sellerRequest.lot_area?.toLocaleString() }}
                                sqm
                            </p>
                        </div>

                        <div v-if="sellerRequest.price_expectation">
                            <label
                                class="block text-sm font-medium text-gray-500 mb-1"
                                >Price Expectation</label
                            >
                            <p class="text-xl font-bold text-blue-600">
                                {{
                                    formatCurrency(
                                        sellerRequest.price_expectation
                                    )
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-500 mb-1"
                                >Property Type</label
                            >
                            <p class="text-gray-900">
                                {{
                                    sellerRequest.property_type?.replace(
                                        "_",
                                        " "
                                    )
                                }}
                            </p>
                        </div>
                    </div>

                    <div v-if="sellerRequest.description" class="mt-6">
                        <label
                            class="block text-sm font-medium text-gray-500 mb-1"
                            >Description</label
                        >
                        <p class="text-gray-900 whitespace-pre-line">
                            {{ sellerRequest.description }}
                        </p>
                    </div>
                </div>

                <!-- Assigned Broker Card -->
                <div
                    v-if="sellerRequest.assigned_broker"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                >
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        Assigned Broker
                    </h2>

                    <div class="flex items-center gap-4">
                        <UserAvatar
                            :user="sellerRequest.assigned_broker"
                            size="lg"
                            bg-color="blue"
                        />
                        <div>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ sellerRequest.assigned_broker.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ sellerRequest.assigned_broker.email }}
                            </p>
                            <p
                                v-if="sellerRequest.assigned_broker.phone"
                                class="text-sm text-gray-600"
                            >
                                {{ sellerRequest.assigned_broker.phone }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <Link
                            :href="route('client.broker')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            <ChatBubbleLeftRightIcon class="w-5 h-5" />
                            Contact Broker
                        </Link>
                    </div>
                </div>

                <!-- Converted Property Card -->
                <div
                    v-if="sellerRequest.property"
                    class="bg-green-50 rounded-lg border border-green-200 p-6"
                >
                    <div class="flex items-start gap-3">
                        <CheckCircleIcon
                            class="w-6 h-6 text-green-600 flex-shrink-0 mt-1"
                        />
                        <div class="flex-1">
                            <h3
                                class="text-lg font-semibold text-green-900 mb-2"
                            >
                                Property Listed Successfully!
                            </h3>
                            <p class="text-green-800 mb-4">
                                Your property has been approved and is now live
                                on our platform.
                            </p>
                            <Link
                                :href="
                                    route(
                                        'client.properties.show',
                                        sellerRequest.property.slug
                                    )
                                "
                                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                            >
                                View Property Listing →
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Card -->
                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                >
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        Contact Information
                    </h2>

                    <div class="space-y-3">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-500 mb-1"
                                >Name</label
                            >
                            <p class="text-gray-900">
                                {{ sellerRequest.contact_name }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-500 mb-1"
                                >Email</label
                            >
                            <p class="text-gray-900">
                                {{ sellerRequest.contact_email }}
                            </p>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-500 mb-1"
                                >Phone</label
                            >
                            <p class="text-gray-900">
                                {{ sellerRequest.contact_phone }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div
                    v-if="
                        sellerRequest.images && sellerRequest.images.length > 0
                    "
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                >
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        Property Photos
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <img
                            v-for="(image, index) in sellerRequest.images"
                            :key="index"
                            :src="`/storage/${image}`"
                            :alt="`Property photo ${index + 1}`"
                            class="w-full h-48 object-cover rounded-lg"
                        />
                    </div>
                </div>

                <!-- Documents -->
                <div
                    v-if="
                        sellerRequest.documents &&
                        sellerRequest.documents.length > 0
                    "
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                >
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">
                        Documents
                    </h2>
                    <div class="space-y-2">
                        <a
                            v-for="(document, index) in sellerRequest.documents"
                            :key="index"
                            :href="`/storage/${document.path}`"
                            target="_blank"
                            class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                        >
                            <DocumentIcon class="w-5 h-5 text-gray-400" />
                            <span class="text-sm text-gray-700">{{
                                document.name
                            }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import {
    ChevronLeftIcon,
    ChatBubbleLeftRightIcon,
    CheckCircleIcon,
    DocumentIcon,
} from "@heroicons/vue/24/outline";

defineProps({
    sellerRequest: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        assigned: "bg-blue-100 text-blue-800",
        under_review: "bg-purple-100 text-purple-800",
        converted: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};
</script>
