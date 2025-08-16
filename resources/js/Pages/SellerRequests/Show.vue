<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import Modal from "@/Components/Modal.vue";
import { ref } from "vue";
import {
    BuildingOfficeIcon,
    MapPinIcon,
    CurrencyDollarIcon,
    CalendarIcon,
    UserIcon,
    PhoneIcon,
    EnvelopeIcon,
    CheckCircleIcon,
    XCircleIcon,
    PencilIcon,
    TrashIcon,
    EyeIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    sellerRequest: Object,
    brokers: Array,
    canManage: Boolean,
});

// Forms
const approveForm = useForm({
    status: "approved",
    assigned_broker_id: "",
    admin_notes: "",
});

const rejectForm = useForm({
    status: "rejected",
    rejection_reason: "",
    admin_notes: "",
});

const deleteForm = useForm({});

// Modal states
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const showDeleteModal = ref(false);

// Computed properties
const statusColor = computed(() => {
    const colors = {
        pending: "bg-yellow-100 text-yellow-800 border-yellow-200",
        under_review: "bg-blue-100 text-blue-800 border-blue-200",
        approved: "bg-green-100 text-green-800 border-green-200",
        rejected: "bg-red-100 text-red-800 border-red-200",
        listed: "bg-emerald-100 text-emerald-800 border-emerald-200",
    };
    return (
        colors[props.sellerRequest.status] ||
        "bg-gray-100 text-gray-800 border-gray-200"
    );
});

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(price);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Actions
const approveRequest = () => {
    approveForm.patch(route("seller-requests.update", props.sellerRequest.id), {
        onSuccess: () => {
            showApproveModal.value = false;
            approveForm.reset();
        },
    });
};

const rejectRequest = () => {
    rejectForm.patch(route("seller-requests.update", props.sellerRequest.id), {
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
    });
};

const deleteRequest = () => {
    deleteForm.delete(
        route("seller-requests.destroy", props.sellerRequest.id),
        {
            onSuccess: () => {
                showDeleteModal.value = false;
            },
        }
    );
};
</script>

<template>
    <Head :title="`Seller Request - ${sellerRequest.property_title}`" />

    <ModernDashboardLayout>
        <div class="space-y-8">
            <!-- Header Section -->
            <div class="bg-white rounded-3xl border border-neutral-100 p-8">
                <div
                    class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl flex items-center justify-center shadow-soft"
                        >
                            <BuildingOfficeIcon class="w-8 h-8 text-white" />
                        </div>
                        <div>
                            <h1
                                class="text-3xl font-bold text-neutral-900 mb-2"
                            >
                                {{ sellerRequest.property_title }}
                            </h1>
                            <div
                                class="flex items-center gap-4 text-neutral-600"
                            >
                                <div class="flex items-center gap-2">
                                    <MapPinIcon class="w-4 h-4" />
                                    <span>{{
                                        sellerRequest.property_location
                                    }}</span>
                                </div>
                                <div
                                    :class="[
                                        'px-3 py-1 rounded-full text-sm font-medium border',
                                        statusColor,
                                    ]"
                                >
                                    {{
                                        sellerRequest.status
                                            .replace("_", " ")
                                            .toUpperCase()
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <Link :href="route('seller-requests.index')">
                            <ModernButton variant="outline" size="sm">
                                ← Back to List
                            </ModernButton>
                        </Link>

                        <template v-if="canManage">
                            <ModernButton
                                v-if="
                                    sellerRequest.status === 'pending' ||
                                    sellerRequest.status === 'under_review'
                                "
                                @click="showApproveModal = true"
                                variant="primary"
                                size="sm"
                                class="flex items-center gap-2"
                            >
                                <CheckCircleIcon class="w-4 h-4" />
                                Approve
                            </ModernButton>

                            <ModernButton
                                v-if="
                                    sellerRequest.status === 'pending' ||
                                    sellerRequest.status === 'under_review'
                                "
                                @click="showRejectModal = true"
                                variant="danger"
                                size="sm"
                                class="flex items-center gap-2"
                            >
                                <XCircleIcon class="w-4 h-4" />
                                Reject
                            </ModernButton>

                            <Link
                                :href="
                                    route(
                                        'seller-requests.edit',
                                        sellerRequest.id
                                    )
                                "
                            >
                                <ModernButton
                                    variant="outline"
                                    size="sm"
                                    class="flex items-center gap-2"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                    Edit
                                </ModernButton>
                            </Link>

                            <ModernButton
                                @click="showDeleteModal = true"
                                variant="danger"
                                size="sm"
                                class="flex items-center gap-2"
                            >
                                <TrashIcon class="w-4 h-4" />
                                Delete
                            </ModernButton>
                        </template>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="xl:col-span-2 space-y-8">
                    <!-- Property Details -->
                    <div
                        class="bg-white rounded-3xl border border-neutral-100 p-8"
                    >
                        <h2
                            class="text-2xl font-semibold text-neutral-900 mb-6"
                        >
                            🏠 Property Details
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="text-sm font-medium text-neutral-600"
                                        >Property Type</label
                                    >
                                    <p
                                        class="text-lg font-semibold text-neutral-900 capitalize"
                                    >
                                        {{ sellerRequest.property_type }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="text-sm font-medium text-neutral-600"
                                        >Asking Price</label
                                    >
                                    <p
                                        class="text-2xl font-bold text-coconut-600"
                                    >
                                        {{
                                            formatPrice(
                                                sellerRequest.asking_price
                                            )
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="text-sm font-medium text-neutral-600"
                                        >Property Area</label
                                    >
                                    <p
                                        class="text-lg font-semibold text-neutral-900"
                                    >
                                        {{ sellerRequest.property_area }}
                                        {{ sellerRequest.area_unit }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="text-sm font-medium text-neutral-600"
                                        >Location</label
                                    >
                                    <p
                                        class="text-lg font-semibold text-neutral-900"
                                    >
                                        {{ sellerRequest.property_location }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="text-sm font-medium text-neutral-600"
                                        >Address</label
                                    >
                                    <p class="text-lg text-neutral-700">
                                        {{ sellerRequest.property_address }}
                                    </p>
                                    <p class="text-sm text-neutral-600">
                                        {{ sellerRequest.city }},
                                        {{ sellerRequest.state }}
                                        {{ sellerRequest.zip_code }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="text-sm font-medium text-neutral-600"
                                >Description</label
                            >
                            <p class="text-neutral-700 mt-2 leading-relaxed">
                                {{ sellerRequest.property_description }}
                            </p>
                        </div>

                        <!-- Features -->
                        <div
                            v-if="
                                sellerRequest.features &&
                                sellerRequest.features.length
                            "
                            class="mt-6"
                        >
                            <label class="text-sm font-medium text-neutral-600"
                                >Features</label
                            >
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span
                                    v-for="feature in sellerRequest.features"
                                    :key="feature"
                                    class="px-3 py-1 bg-primary-50 text-primary-700 rounded-full text-sm font-medium"
                                >
                                    {{ feature }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Images -->
                    <div
                        v-if="
                            sellerRequest.uploaded_images &&
                            sellerRequest.uploaded_images.length
                        "
                        class="bg-white rounded-3xl border border-neutral-100 p-8"
                    >
                        <h2
                            class="text-2xl font-semibold text-neutral-900 mb-6"
                        >
                            📸 Property Images
                        </h2>

                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                        >
                            <div
                                v-for="(
                                    image, index
                                ) in sellerRequest.uploaded_images"
                                :key="index"
                                class="aspect-square bg-neutral-100 rounded-2xl overflow-hidden group cursor-pointer"
                            >
                                <img
                                    :src="image"
                                    :alt="`Property image ${index + 1}`"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Seller Information -->
                    <div
                        class="bg-white rounded-3xl border border-neutral-100 p-8"
                    >
                        <h2
                            class="text-xl font-semibold text-neutral-900 mb-6 flex items-center gap-2"
                        >
                            <UserIcon class="w-5 h-5" />
                            Seller Information
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-neutral-600"
                                    >Name</label
                                >
                                <p
                                    class="text-lg font-semibold text-neutral-900"
                                >
                                    {{ sellerRequest.seller_name }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="text-sm font-medium text-neutral-600"
                                    >Email</label
                                >
                                <p
                                    class="text-neutral-700 flex items-center gap-2"
                                >
                                    <EnvelopeIcon class="w-4 h-4" />
                                    {{ sellerRequest.seller_email }}
                                </p>
                            </div>

                            <div v-if="sellerRequest.seller_phone">
                                <label
                                    class="text-sm font-medium text-neutral-600"
                                    >Phone</label
                                >
                                <p
                                    class="text-neutral-700 flex items-center gap-2"
                                >
                                    <PhoneIcon class="w-4 h-4" />
                                    {{ sellerRequest.seller_phone }}
                                </p>
                            </div>

                            <div v-if="sellerRequest.seller_address">
                                <label
                                    class="text-sm font-medium text-neutral-600"
                                    >Address</label
                                >
                                <p class="text-neutral-700">
                                    {{ sellerRequest.seller_address }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Request Timeline -->
                    <div
                        class="bg-white rounded-3xl border border-neutral-100 p-8"
                    >
                        <h2
                            class="text-xl font-semibold text-neutral-900 mb-6 flex items-center gap-2"
                        >
                            <CalendarIcon class="w-5 h-5" />
                            Request Timeline
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label
                                    class="text-sm font-medium text-neutral-600"
                                    >Submitted</label
                                >
                                <p class="text-neutral-700">
                                    {{ formatDate(sellerRequest.created_at) }}
                                </p>
                            </div>

                            <div v-if="sellerRequest.reviewed_at">
                                <label
                                    class="text-sm font-medium text-neutral-600"
                                    >Reviewed</label
                                >
                                <p class="text-neutral-700">
                                    {{ formatDate(sellerRequest.reviewed_at) }}
                                </p>
                            </div>

                            <div v-if="sellerRequest.listed_at">
                                <label
                                    class="text-sm font-medium text-neutral-600"
                                    >Listed</label
                                >
                                <p class="text-neutral-700">
                                    {{ formatDate(sellerRequest.listed_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div
                        v-if="
                            sellerRequest.admin_notes ||
                            sellerRequest.rejection_reason
                        "
                        class="bg-white rounded-3xl border border-neutral-100 p-8"
                    >
                        <h2 class="text-xl font-semibold text-neutral-900 mb-6">
                            📝 Admin Notes
                        </h2>

                        <div class="space-y-4">
                            <div v-if="sellerRequest.admin_notes">
                                <label
                                    class="text-sm font-medium text-neutral-600"
                                    >Notes</label
                                >
                                <p
                                    class="text-neutral-700 mt-2 p-4 bg-neutral-50 rounded-xl"
                                >
                                    {{ sellerRequest.admin_notes }}
                                </p>
                            </div>

                            <div v-if="sellerRequest.rejection_reason">
                                <label class="text-sm font-medium text-red-600"
                                    >Rejection Reason</label
                                >
                                <p
                                    class="text-red-700 mt-2 p-4 bg-red-50 rounded-xl border border-red-200"
                                >
                                    {{ sellerRequest.rejection_reason }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <Modal :show="showApproveModal" @close="showApproveModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-neutral-900 mb-4">
                    Approve Seller Request
                </h3>

                <form @submit.prevent="approveRequest" class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-neutral-700 mb-2"
                        >
                            Assign Broker
                        </label>
                        <select
                            v-model="approveForm.assigned_broker_id"
                            class="w-full px-4 py-2 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            required
                        >
                            <option value="">Select a broker...</option>
                            <option
                                v-for="broker in brokers"
                                :key="broker.id"
                                :value="broker.id"
                            >
                                {{ broker.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-neutral-700 mb-2"
                        >
                            Admin Notes (Optional)
                        </label>
                        <textarea
                            v-model="approveForm.admin_notes"
                            rows="3"
                            class="w-full px-4 py-2 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="Add any notes for the broker..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <ModernButton
                            type="button"
                            @click="showApproveModal = false"
                            variant="outline"
                        >
                            Cancel
                        </ModernButton>
                        <ModernButton
                            type="submit"
                            variant="primary"
                            :disabled="approveForm.processing"
                        >
                            {{
                                approveForm.processing
                                    ? "Approving..."
                                    : "Approve Request"
                            }}
                        </ModernButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Reject Modal -->
        <Modal :show="showRejectModal" @close="showRejectModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-neutral-900 mb-4">
                    Reject Seller Request
                </h3>

                <form @submit.prevent="rejectRequest" class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-medium text-neutral-700 mb-2"
                        >
                            Rejection Reason *
                        </label>
                        <textarea
                            v-model="rejectForm.rejection_reason"
                            rows="4"
                            class="w-full px-4 py-2 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="Please provide a clear reason for rejection..."
                            required
                        ></textarea>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-neutral-700 mb-2"
                        >
                            Admin Notes (Optional)
                        </label>
                        <textarea
                            v-model="rejectForm.admin_notes"
                            rows="3"
                            class="w-full px-4 py-2 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="Additional internal notes..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <ModernButton
                            type="button"
                            @click="showRejectModal = false"
                            variant="outline"
                        >
                            Cancel
                        </ModernButton>
                        <ModernButton
                            type="submit"
                            variant="danger"
                            :disabled="rejectForm.processing"
                        >
                            {{
                                rejectForm.processing
                                    ? "Rejecting..."
                                    : "Reject Request"
                            }}
                        </ModernButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-neutral-900 mb-4">
                    Delete Seller Request
                </h3>

                <p class="text-neutral-600 mb-6">
                    Are you sure you want to delete this seller request? This
                    action cannot be undone.
                </p>

                <div class="flex justify-end gap-3">
                    <ModernButton
                        @click="showDeleteModal = false"
                        variant="outline"
                    >
                        Cancel
                    </ModernButton>
                    <ModernButton
                        @click="deleteRequest"
                        variant="danger"
                        :disabled="deleteForm.processing"
                    >
                        {{
                            deleteForm.processing
                                ? "Deleting..."
                                : "Delete Request"
                        }}
                    </ModernButton>
                </div>
            </div>
        </Modal>
    </ModernDashboardLayout>
</template>
