<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-3xl font-bold text-gray-900">
                                    Inquiry Management
                                </h2>
                                <p class="text-gray-600">
                                    Manage property inquiries in GeoCasa Bohol
                                </p>
                            </div>
                            <Link
                                v-if="canRespond"
                                :href="route('inquiries.create')"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium"
                            >
                                Add New Inquiry
                            </Link>
                        </div>

                        <!-- Filters -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                            >
                                <!-- Search -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Search</label
                                    >
                                    <input
                                        v-model="filters.search"
                                        type="text"
                                        placeholder="Name, email, or message..."
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        @input="applyFilters"
                                    />
                                </div>

                                <!-- Status Filter -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Status</label
                                    >
                                    <select
                                        v-model="filters.status"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">All Statuses</option>
                                        <option value="new">New</option>
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
                                </div>

                                <!-- Inquiry Type Filter -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Type</label
                                    >
                                    <select
                                        v-model="filters.inquiry_type"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">All Types</option>
                                        <option value="general">General</option>
                                        <option value="viewing">Viewing</option>
                                        <option value="purchase">
                                            Purchase
                                        </option>
                                        <option value="information">
                                            Information
                                        </option>
                                    </select>
                                </div>

                                <!-- Property Filter -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Property</label
                                    >
                                    <select
                                        v-model="filters.property_id"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        @change="applyFilters"
                                    >
                                        <option value="">All Properties</option>
                                        <option
                                            v-for="property in properties"
                                            :key="property.id"
                                            :value="property.id"
                                        >
                                            {{ property.title }} -
                                            {{ property.municipality }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Date Range Filters -->
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4"
                            >
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Date From</label
                                    >
                                    <input
                                        v-model="filters.date_from"
                                        type="date"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        @change="applyFilters"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Date To</label
                                    >
                                    <input
                                        v-model="filters.date_to"
                                        type="date"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        @change="applyFilters"
                                    />
                                </div>
                            </div>

                            <!-- Clear Filters -->
                            <div class="mt-4">
                                <button
                                    @click="clearFilters"
                                    class="text-sm text-gray-600 hover:text-gray-800"
                                >
                                    Clear all filters
                                </button>
                            </div>
                        </div>

                        <!-- Inquiries Grid -->
                        <div
                            v-if="inquiries.data.length > 0"
                            class="grid grid-cols-1 lg:grid-cols-2 gap-6"
                        >
                            <div
                                v-for="inquiry in inquiries.data"
                                :key="inquiry.id"
                                class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
                            >
                                <!-- Header -->
                                <div
                                    class="flex justify-between items-start mb-4"
                                >
                                    <div>
                                        <h3
                                            class="text-lg font-semibold text-gray-900"
                                        >
                                            {{ inquiry.name }}
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            {{ inquiry.email }}
                                        </p>
                                        <p
                                            v-if="inquiry.phone"
                                            class="text-sm text-gray-600"
                                        >
                                            {{ inquiry.phone }}
                                        </p>
                                    </div>
                                    <div
                                        class="flex flex-col items-end space-y-2"
                                    >
                                        <span
                                            :class="
                                                getStatusBadgeClass(
                                                    inquiry.status
                                                )
                                            "
                                            class="px-2 py-1 text-xs font-medium rounded-full"
                                        >
                                            {{ formatStatus(inquiry.status) }}
                                        </span>
                                        <span
                                            :class="
                                                getTypeBadgeClass(
                                                    inquiry.inquiry_type
                                                )
                                            "
                                            class="px-2 py-1 text-xs font-medium rounded-full"
                                        >
                                            {{
                                                formatInquiryType(
                                                    inquiry.inquiry_type
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Property Info -->
                                <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                                    <h4 class="font-medium text-gray-900">
                                        {{ inquiry.property.title }}
                                    </h4>
                                    <p class="text-sm text-gray-600">
                                        {{ inquiry.property.municipality }},
                                        Bohol
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        ₱{{
                                            formatPrice(inquiry.property.price)
                                        }}
                                    </p>
                                </div>

                                <!-- Message Preview -->
                                <div class="mb-4">
                                    <p
                                        class="text-sm text-gray-700 line-clamp-3"
                                    >
                                        {{ inquiry.message }}
                                    </p>
                                </div>

                                <!-- Client Info -->
                                <div
                                    v-if="inquiry.client"
                                    class="mb-4 text-sm text-gray-600"
                                >
                                    <span class="font-medium">Client:</span>
                                    {{ inquiry.client.name }}
                                </div>

                                <!-- Timestamps -->
                                <div
                                    class="mb-4 text-xs text-gray-500 space-y-1"
                                >
                                    <div>
                                        Created:
                                        {{ formatDate(inquiry.created_at) }}
                                    </div>
                                    <div v-if="inquiry.contacted_at">
                                        Contacted:
                                        {{ formatDate(inquiry.contacted_at) }}
                                    </div>
                                    <div v-if="inquiry.scheduled_at">
                                        Scheduled:
                                        {{ formatDate(inquiry.scheduled_at) }}
                                    </div>
                                    <div v-if="inquiry.responded_at">
                                        Responded:
                                        {{ formatDate(inquiry.responded_at) }}
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div
                                    class="flex justify-between items-center pt-4 border-t border-gray-200"
                                >
                                    <div class="flex space-x-2">
                                        <Link
                                            :href="
                                                route(
                                                    'inquiries.show',
                                                    inquiry.id
                                                )
                                            "
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                        >
                                            View Details
                                        </Link>
                                        <Link
                                            v-if="canRespond"
                                            :href="
                                                route(
                                                    'inquiries.edit',
                                                    inquiry.id
                                                )
                                            "
                                            class="text-green-600 hover:text-green-800 text-sm font-medium"
                                        >
                                            Edit
                                        </Link>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button
                                            v-if="
                                                canRespond &&
                                                inquiry.status === 'new'
                                            "
                                            @click="quickRespond(inquiry)"
                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm"
                                        >
                                            Quick Respond
                                        </button>
                                        <button
                                            v-if="canDelete"
                                            @click="deleteInquiry(inquiry)"
                                            class="text-red-600 hover:text-red-800 text-sm"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <div class="text-gray-400 text-6xl mb-4">📧</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                No inquiries found
                            </h3>
                            <p class="text-gray-500">
                                Try adjusting your search filters or add a new
                                inquiry.
                            </p>
                        </div>

                        <!-- Pagination -->
                        <div v-if="inquiries.data.length > 0" class="mt-6">
                            <Pagination :links="inquiries.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    inquiries: Object,
    properties: Array,
    filters: Object,
    can: Object,
});

const filters = ref({
    search: props.filters.search || "",
    status: props.filters.status || "",
    inquiry_type: props.filters.inquiry_type || "",
    property_id: props.filters.property_id || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
});

const canRespond = computed(() => props.can.respond);
const canDelete = computed(() => props.can.delete);

const applyFilters = () => {
    router.get(route("inquiries.index"), filters.value, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    filters.value = {
        search: "",
        status: "",
        inquiry_type: "",
        property_id: "",
        date_from: "",
        date_to: "",
    };
    applyFilters();
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

const formatStatus = (status) => {
    const statuses = {
        new: "New",
        contacted: "Contacted",
        scheduled: "Scheduled",
        completed: "Completed",
        closed: "Closed",
    };
    return statuses[status] || status;
};

const formatInquiryType = (type) => {
    const types = {
        general: "General",
        viewing: "Viewing",
        purchase: "Purchase",
        information: "Information",
    };
    return types[type] || type;
};

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH").format(price);
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

const quickRespond = (inquiry) => {
    router.visit(route("inquiries.show", inquiry.id));
};

const deleteInquiry = (inquiry) => {
    if (confirm("Are you sure you want to delete this inquiry?")) {
        router.delete(route("inquiries.destroy", inquiry.id));
    }
};
</script>
