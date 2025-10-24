<template>
    <ModernDashboardLayout>
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        My Listing Requests
                    </h1>
                    <p class="text-gray-600">
                        Track your land listing requests and broker assignments
                    </p>
                </div>
                <Link
                    :href="route('client.seller-requests.create')"
                    class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                >
                    <PlusIcon class="w-5 h-5" />
                    List My Land
                </Link>
            </div>

            <!-- Requests List -->
            <div v-if="sellerRequests.length > 0" class="space-y-4">
                <div
                    v-for="request in sellerRequests"
                    :key="request.id"
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-start justify-between">
                        <!-- Request Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ request.property_type.replace('_', ' ') }}
                                </h3>
                                <span
                                    :class="getStatusBadgeClass(request.status)"
                                    class="px-3 py-1 rounded-full text-xs font-semibold"
                                >
                                    {{ request.status.replace('_', ' ') }}
                                </span>
                            </div>
                            
                            <div class="space-y-2 text-sm text-gray-600">
                                <div class="flex items-center gap-2">
                                    <MapPinIcon class="w-4 h-4 text-gray-400" />
                                    <span>{{ request.address }}, {{ request.municipality }}</span>
                                </div>
                                
                                <div v-if="request.price_expectation" class="flex items-center gap-2">
                                    <CurrencyDollarIcon class="w-4 h-4 text-gray-400" />
                                    <span>Expected Price: {{ formatCurrency(request.price_expectation) }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <CalendarIcon class="w-4 h-4 text-gray-400" />
                                    <span>Submitted: {{ formatDate(request.submission_date) }}</span>
                                </div>
                            </div>

                            <!-- Assigned Broker -->
                            <div v-if="request.assigned_broker" class="mt-4 p-4 bg-blue-50 rounded-lg">
                                <p class="text-sm font-medium text-gray-900 mb-2">Assigned Broker</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-semibold">
                                            {{ request.assigned_broker.name.charAt(0) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ request.assigned_broker.name }}
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            {{ request.assigned_broker.email }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Converted Property -->
                            <div v-if="request.property" class="mt-4 p-4 bg-green-50 rounded-lg">
                                <p class="text-sm font-medium text-gray-900 mb-2">
                                    ✓ Converted to Property Listing
                                </p>
                                <Link
                                    :href="route('client.properties.show', request.property.slug)"
                                    class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                                >
                                    View Property Listing →
                                </Link>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="ml-6">
                            <Link
                                :href="route('client.seller-requests.show', request.id)"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                View Details →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <HomeModernIcon class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    No Listing Requests Yet
                </h3>
                <p class="text-gray-600 mb-6">
                    Own land in Bohol? Submit a listing request and get matched with a professional broker who will help you sell it.
                </p>
                <Link
                    :href="route('client.seller-requests.create')"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <PlusIcon class="w-5 h-5" />
                    List My Land
                </Link>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import ModernDashboardLayout from '@/Layouts/ModernDashboardLayout.vue';
import {
    PlusIcon,
    HomeModernIcon,
    MapPinIcon,
    CurrencyDollarIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    sellerRequests: {
        type: Array,
        default: () => [],
    },
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        assigned: 'bg-blue-100 text-blue-800',
        under_review: 'bg-purple-100 text-purple-800',
        converted: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>
