<script setup>
import { computed, ref, watch } from "vue";
import { useForm, usePage, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import NotificationService from "@/Services/NotificationService";
import {
    ArrowLeftIcon,
    PencilIcon,
    CheckCircleIcon,
    XCircleIcon,
    CurrencyDollarIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
    CalendarIcon,
    DocumentTextIcon,
    InformationCircleIcon,
    ExclamationTriangleIcon,
    TagIcon,
    ChartBarIcon,
    ArrowPathIcon,
    EyeIcon,
    PlusIcon,
    TrashIcon,
    CheckIcon,
    ClockIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    transaction: Object,
    properties: Array,
    clients: Array,
    inquiries: Array,
    brokers: Array,
});

const page = usePage();

const form = useForm({
    property_id: props.transaction.property_id,
    client_id: props.transaction.client_id,
    inquiry_id: props.transaction.inquiry_id,
    broker_id: props.transaction.broker_id,
    offered_price: props.transaction.offered_price,
    final_price: props.transaction.final_price || "",
    contract_date: props.transaction.contract_date || "",
    closing_date: props.transaction.closing_date || "",
    notes: props.transaction.notes || "",
    status: props.transaction.status,
});

const submit = () => {
    form.patch(route("transactions.update", props.transaction.id), {
        onSuccess: (response) => {
            // Broadcast transaction update to relevant channels
            const updatedTransaction =
                response.props.transaction || props.transaction;

            // Notify client about transaction update
            if (updatedTransaction.client_id) {
                window.Echo.private(
                    `user.${updatedTransaction.client_id}`
                ).whisper("transaction-updated", {
                    transaction: updatedTransaction,
                    broker: page.props.auth.user,
                    message: `Transaction updated for ${updatedTransaction.property?.title}`,
                });
            }

            // Broadcast to transaction-specific channel
            window.Echo.private(`transaction.${updatedTransaction.id}`).whisper(
                "transaction-updated",
                {
                    transaction: updatedTransaction,
                    updatedBy: page.props.auth.user,
                    timestamp: new Date().toISOString(),
                }
            );

            // Show success notification
            NotificationService.success("Transaction updated successfully");
        },
    });
};

const selectedProperty = computed(() => {
    return props.properties?.find((p) => p.id == form.property_id);
});

const formatPrice = (price) => {
    if (!price || isNaN(price)) return "₱0.00";
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(price);
};

const formatDateForInput = (date) => {
    if (!date) return "";
    return new Date(date).toISOString().split("T")[0];
};

// Enhanced form validation and UX
const showValidationErrors = ref(false);
const isDirty = ref(false);

// Watch for form changes
watch(
    form,
    () => {
        isDirty.value = true;
    },
    { deep: true }
);

const hasErrors = computed(() => {
    return Object.keys(form.errors).length > 0;
});

const canSave = computed(() => {
    return (
        form.property_id &&
        form.client_id &&
        form.broker_id &&
        form.offered_price &&
        !form.processing
    );
});

const selectedClient = computed(() => {
    return props.clients?.find((c) => c.id == form.client_id);
});

const selectedBroker = computed(() => {
    return props.brokers?.find((b) => b.id == form.broker_id);
});

const selectedInquiry = computed(() => {
    return props.inquiries?.find((i) => i.id == form.inquiry_id);
});

const getStatusColor = (status) => {
    const colors = {
        pending: "bg-yellow-100 text-yellow-800 border-yellow-200",
        negotiating: "bg-blue-100 text-blue-800 border-blue-200",
        under_review: "bg-purple-100 text-purple-800 border-purple-200",
        accepted: "bg-green-100 text-green-800 border-green-200",
        rejected: "bg-red-100 text-red-800 border-red-200",
        cancelled: "bg-gray-100 text-gray-800 border-gray-200",
        completed: "bg-emerald-100 text-emerald-800 border-emerald-200",
    };
    return colors[status] || "bg-gray-100 text-gray-800 border-gray-200";
};

const getStatusIcon = (status) => {
    const icons = {
        pending: ClockIcon,
        negotiating: ArrowPathIcon,
        under_review: EyeIcon,
        accepted: CheckCircleIcon,
        rejected: XCircleIcon,
        cancelled: XCircleIcon,
        completed: CheckCircleIcon,
    };
    return icons[status] || InformationCircleIcon;
};
</script>

<template>
    <ModernDashboardLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Enhanced Header with Breadcrumb -->
                <div class="mb-8">
                    <nav
                        class="flex items-center space-x-2 text-sm text-gray-500 mb-4"
                    >
                        <Link
                            :href="route('transactions.index')"
                            class="hover:text-gray-700 flex items-center"
                        >
                            <ArrowLeftIcon class="w-4 h-4 mr-1" />
                            Transactions
                        </Link>
                        <span>/</span>
                        <Link
                            :href="route('transactions.show', transaction.id)"
                            class="hover:text-gray-700"
                        >
                            Transaction #{{ transaction.transaction_number }}
                        </Link>
                        <span>/</span>
                        <span class="text-gray-900 font-medium">Edit</span>
                    </nav>

                    <!-- Main Header Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    >
                        <div
                            class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-8"
                        >
                            <div
                                class="flex flex-col lg:flex-row lg:items-center lg:justify-between"
                            >
                                <div class="flex-1">
                                    <div
                                        class="flex items-center space-x-4 mb-4"
                                    >
                                        <div
                                            class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm"
                                        >
                                            <PencilIcon
                                                class="w-8 h-8 text-white"
                                            />
                                        </div>
                                        <div>
                                            <h1
                                                class="text-3xl font-bold text-white"
                                            >
                                                Edit Transaction
                                            </h1>
                                            <p class="text-blue-100 text-lg">
                                                Transaction #{{
                                                    transaction.transaction_number
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Current Status -->
                                    <div class="flex items-center space-x-3">
                                        <component
                                            :is="getStatusIcon(form.status)"
                                            class="w-5 h-5 text-white"
                                        />
                                        <span
                                            :class="getStatusColor(form.status)"
                                            class="inline-flex px-3 py-1 text-sm font-semibold rounded-full border"
                                        >
                                            {{ form.status.replace("_", " ") }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div
                                    class="mt-6 lg:mt-0 lg:ml-8 flex flex-wrap gap-3"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'transactions.show',
                                                transaction.id
                                            )
                                        "
                                        class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg font-medium transition-colors backdrop-blur-sm"
                                    >
                                        <EyeIcon class="w-4 h-4 mr-2" />
                                        View Details
                                    </Link>
                                    <button
                                        @click="submit"
                                        :disabled="!canSave"
                                        class="inline-flex items-center px-4 py-2 bg-green-500/90 hover:bg-green-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors"
                                    >
                                        <CheckIcon class="w-4 h-4 mr-2" />
                                        {{
                                            form.processing
                                                ? "Saving..."
                                                : "Save Changes"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    <!-- Main Form -->
                    <div class="xl:col-span-2">
                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- Step 1: Property & Client Selection -->
                            <div
                                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                            >
                                <div
                                    class="px-6 py-4 bg-gray-50 border-b border-gray-200"
                                >
                                    <h3
                                        class="text-lg font-semibold text-gray-900 flex items-center"
                                    >
                                        <BuildingOfficeIcon
                                            class="w-5 h-5 mr-2 text-blue-600"
                                        />
                                        Property & Client Information
                                    </h3>
                                </div>
                                <div class="p-6 space-y-6">
                                    <!-- Property Selection -->
                                    <div>
                                        <label
                                            for="property_id"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Property *
                                        </label>
                                        <select
                                            v-model="form.property_id"
                                            id="property_id"
                                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
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
                                            class="text-red-600 text-sm mt-1 flex items-center"
                                        >
                                            <ExclamationTriangleIcon
                                                class="w-4 h-4 mr-1"
                                            />
                                            {{ form.errors.property_id }}
                                        </div>
                                    </div>

                                    <!-- Property Details Preview -->
                                    <div
                                        v-if="selectedProperty"
                                        class="bg-blue-50 rounded-lg p-4 border border-blue-200"
                                    >
                                        <h4
                                            class="font-semibold text-blue-900 mb-3 flex items-center"
                                        >
                                            <BuildingOfficeIcon
                                                class="w-4 h-4 mr-2"
                                            />
                                            Selected Property
                                        </h4>
                                        <div
                                            class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"
                                        >
                                            <div>
                                                <span
                                                    class="font-medium text-blue-700"
                                                    >Type:</span
                                                >
                                                <p class="text-blue-900">
                                                    {{ selectedProperty.type }}
                                                </p>
                                            </div>
                                            <div>
                                                <span
                                                    class="font-medium text-blue-700"
                                                    >Location:</span
                                                >
                                                <p class="text-blue-900">
                                                    {{
                                                        selectedProperty.address &&
                                                        selectedProperty.municipality
                                                            ? `${selectedProperty.address}, ${selectedProperty.municipality}`
                                                            : selectedProperty.address ||
                                                              selectedProperty.municipality ||
                                                              "N/A"
                                                    }}
                                                </p>
                                            </div>
                                            <div>
                                                <span
                                                    class="font-medium text-blue-700"
                                                    >Listed Price:</span
                                                >
                                                <p
                                                    class="text-blue-900 font-semibold"
                                                >
                                                    {{
                                                        formatPrice(
                                                            selectedProperty.total_price
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                            <div>
                                                <span
                                                    class="font-medium text-blue-700"
                                                    >Status:</span
                                                >
                                                <p class="text-blue-900">
                                                    {{
                                                        selectedProperty.status
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Client Selection -->
                                    <div>
                                        <label
                                            for="client_id"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Client *
                                        </label>
                                        <select
                                            v-model="form.client_id"
                                            id="client_id"
                                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
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
                                            class="text-red-600 text-sm mt-1 flex items-center"
                                        >
                                            <ExclamationTriangleIcon
                                                class="w-4 h-4 mr-1"
                                            />
                                            {{ form.errors.client_id }}
                                        </div>
                                    </div>

                                    <!-- Client Details Preview -->
                                    <div
                                        v-if="selectedClient"
                                        class="bg-green-50 rounded-lg p-4 border border-green-200"
                                    >
                                        <h4
                                            class="font-semibold text-green-900 mb-3 flex items-center"
                                        >
                                            <UserGroupIcon
                                                class="w-4 h-4 mr-2"
                                            />
                                            Selected Client
                                        </h4>
                                        <div
                                            class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"
                                        >
                                            <div>
                                                <span
                                                    class="font-medium text-green-700"
                                                    >Name:</span
                                                >
                                                <p class="text-green-900">
                                                    {{ selectedClient.name }}
                                                </p>
                                            </div>
                                            <div>
                                                <span
                                                    class="font-medium text-green-700"
                                                    >Email:</span
                                                >
                                                <p class="text-green-900">
                                                    {{ selectedClient.email }}
                                                </p>
                                            </div>
                                            <div v-if="selectedClient.phone">
                                                <span
                                                    class="font-medium text-green-700"
                                                    >Phone:</span
                                                >
                                                <p class="text-green-900">
                                                    {{ selectedClient.phone }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Financial Information -->
                            <div
                                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                            >
                                <div
                                    class="px-6 py-4 bg-gray-50 border-b border-gray-200"
                                >
                                    <h3
                                        class="text-lg font-semibold text-gray-900 flex items-center"
                                    >
                                        <CurrencyDollarIcon
                                            class="w-5 h-5 mr-2 text-green-600"
                                        />
                                        Financial Information
                                    </h3>
                                </div>
                                <div class="p-6 space-y-6">
                                    <!-- Price Information -->
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                    >
                                        <!-- Offered Price -->
                                        <div>
                                            <label
                                                for="offered_price"
                                                class="block text-sm font-medium text-gray-700 mb-2"
                                            >
                                                Offered Price *
                                            </label>
                                            <div class="relative">
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
                                                    step="1"
                                                    min="0"
                                                    class="pl-7 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                    placeholder="0.00"
                                                    required
                                                />
                                            </div>
                                            <div
                                                v-if="form.errors.offered_price"
                                                class="text-red-600 text-sm mt-1 flex items-center"
                                            >
                                                <ExclamationTriangleIcon
                                                    class="w-4 h-4 mr-1"
                                                />
                                                {{ form.errors.offered_price }}
                                            </div>
                                        </div>

                                        <!-- Final Price -->
                                        <div>
                                            <label
                                                for="final_price"
                                                class="block text-sm font-medium text-gray-700 mb-2"
                                            >
                                                Final Price (if agreed)
                                            </label>
                                            <div class="relative">
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
                                                    step="1"
                                                    min="0"
                                                    class="pl-7 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                    placeholder="0.00"
                                                />
                                            </div>
                                            <div
                                                v-if="form.errors.final_price"
                                                class="text-red-600 text-sm mt-1 flex items-center"
                                            >
                                                <ExclamationTriangleIcon
                                                    class="w-4 h-4 mr-1"
                                                />
                                                {{ form.errors.final_price }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Form Status Card -->
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                        >
                            <div
                                class="px-6 py-4 bg-gray-50 border-b border-gray-200"
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-900 flex items-center"
                                >
                                    <InformationCircleIcon
                                        class="w-5 h-5 mr-2 text-gray-600"
                                    />
                                    Form Status
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-sm font-medium text-gray-700"
                                            >Form Status</span
                                        >
                                        <span
                                            :class="
                                                hasErrors
                                                    ? 'text-red-600'
                                                    : 'text-green-600'
                                            "
                                            class="text-sm font-semibold"
                                        >
                                            {{
                                                hasErrors
                                                    ? "Has Errors"
                                                    : "Valid"
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-sm font-medium text-gray-700"
                                            >Can Save</span
                                        >
                                        <span
                                            :class="
                                                canSave
                                                    ? 'text-green-600'
                                                    : 'text-gray-400'
                                            "
                                            class="text-sm font-semibold"
                                        >
                                            {{ canSave ? "Yes" : "No" }}
                                        </span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-sm font-medium text-gray-700"
                                            >Changes Made</span
                                        >
                                        <span
                                            :class="
                                                isDirty
                                                    ? 'text-blue-600'
                                                    : 'text-gray-400'
                                            "
                                            class="text-sm font-semibold"
                                        >
                                            {{ isDirty ? "Yes" : "No" }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
