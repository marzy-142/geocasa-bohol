<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
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
const showAssignModal = ref(false);

// Computed properties
const statusColor = computed(() => {
    const colors = {
        pending: "bg-yellow-100 text-yellow-800 border-yellow-200",
        under_review: "bg-blue-100 text-blue-800 border-blue-200",
        assigned: "bg-indigo-100 text-indigo-800 border-indigo-200",
        approved: "bg-green-100 text-green-800 border-green-200",
        rejected: "bg-red-100 text-red-800 border-red-200",
        listed: "bg-emerald-100 text-emerald-800 border-emerald-200",
    };
    return (
        colors[props.sellerRequest.status] ||
        "bg-gray-100 text-gray-800 border-gray-200"
    );
});

const page = usePage();
const userRole = computed(() => page.props.auth.user.role);

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

const formatArea = (area) => {
    if (!area || area === 0) return 'Area not specified';
    return `${area} sqm`;
};

const formatLocation = (request) => {
    const parts = [];
    
    if (request.barangay) parts.push(request.barangay);
    if (request.municipality) parts.push(request.municipality);
    if (request.city) parts.push(request.city);
    if (request.province) parts.push(request.province);
    
    if (parts.length === 0) {
        return request.address || 'Location not specified';
    }
    
    return parts.join(', ');
};

const formatPropertyAddress = (request) => {
    const parts = [];
    
    if (request.barangay) parts.push(request.barangay);
    if (request.municipality) parts.push(request.municipality);
    if (request.city) parts.push(request.city);
    if (request.province) parts.push(request.province);
    
    return parts.length > 0 ? parts.join(', ') : 'Address not specified';
};

// Actions
const approveRequest = () => {
    approveForm.post(
        route("seller-requests.update-status", props.sellerRequest.id),
        {
            onStart: () => {
                console.log(
                    "Approving seller request",
                    props.sellerRequest.id,
                    { payload: approveForm }
                );
            },
            onSuccess: () => {
                showApproveModal.value = false;
                approveForm.reset();
                console.log("Approve success");
            },
            onError: (errors) => {
                console.error("Approve errors", errors);
                try {
                    alert("Approval failed: " + JSON.stringify(errors));
                } catch (e) {
                    /* ignore */
                }
            },
            onFinish: () => {
                approveForm.processing = false;
                console.log("Approve finished");
            },
        }
    );
};

const rejectRequest = () => {
    rejectForm.post(
        route("seller-requests.update-status", props.sellerRequest.id),
        {
            onStart: () => {
                console.log(
                    "Rejecting seller request",
                    props.sellerRequest.id,
                    { payload: rejectForm }
                );
            },
            onSuccess: () => {
                showRejectModal.value = false;
                rejectForm.reset();
                console.log("Reject success");
            },
            onError: (errors) => {
                console.error("Reject errors", errors);
                try {
                    alert("Rejection failed: " + JSON.stringify(errors));
                } catch (e) {
                    /* ignore */
                }
            },
            onFinish: () => {
                rejectForm.processing = false;
                console.log("Reject finished");
            },
        }
    );
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
            <div class="bg-gradient-to-br from-white via-neutral-50 to-neutral-100 rounded-3xl border border-neutral-200 shadow-xl shadow-neutral-200/20 p-8 backdrop-blur-sm">
                <!-- Back Button -->
                <div class="mb-8">
                    <Link
                        :href="route('seller-requests.index')"
                        class="inline-flex items-center gap-3 text-neutral-600 hover:text-coconut-600 transition-all duration-300 group"
                    >
                        <div class="p-2 rounded-xl bg-white shadow-sm group-hover:shadow-md group-hover:scale-105 transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </div>
                        <span class="font-medium">Back to Seller Requests</span>
                    </Link>
                </div>
                
                <div
                    class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8"
                >
                    <div class="flex items-start gap-6">
                        <div
                            class="w-20 h-20 bg-gradient-to-br from-coconut-500 via-amber-500 to-orange-500 rounded-3xl flex items-center justify-center shadow-2xl shadow-orange-500/30 relative overflow-hidden group"
                        >
                            <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
                            <BuildingOfficeIcon class="w-10 h-10 text-white drop-shadow-lg relative z-10" />
                        </div>
                        <div>
                            <h1
                                class="text-4xl font-bold text-neutral-900 mb-3 leading-tight"
                            >
                                {{ sellerRequest.property_title }}
                            </h1>
                            <div
                                class="flex items-center gap-6 text-neutral-600"
                            >
                                <div class="flex items-center gap-3 bg-white/80 backdrop-blur-sm px-4 py-2 rounded-xl shadow-sm">
                                    <MapPinIcon class="w-5 h-5 text-coconut-600" />
                                    <span class="font-medium">{{ formatLocation(sellerRequest) }}</span>
                                </div>
                                <div
                                    :class="[
                                        'px-4 py-2 rounded-xl text-sm font-semibold border-2 shadow-sm backdrop-blur-sm',
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
                    <div class="flex flex-wrap gap-4">
                        <Link :href="route('seller-requests.index')">
                            <ModernButton variant="outline" size="md" class="shadow-lg hover:shadow-xl transition-all duration-300"
                                >← Back to List</ModernButton
                            >
                        </Link>
                        <!-- Broker actions: Only show if assigned to this broker and status is pending/under_review/assigned -->
                        <template
                            v-if="
                                userRole === 'broker' &&
                                sellerRequest.assigned_broker_id ===
                                    page.props.auth.user.id &&
                                (sellerRequest.status === 'pending' ||
                                    sellerRequest.status === 'under_review' ||
                                    sellerRequest.status === 'assigned')
                            "
                        >
                            <ModernButton
                                @click="showApproveModal = true"
                                variant="primary"
                                size="md"
                                class="flex items-center gap-3 shadow-lg hover:shadow-xl transition-all duration-300"
                            >
                                <CheckCircleIcon class="w-5 h-5" />Approve
                            </ModernButton>
                            <ModernButton
                                @click="showRejectModal = true"
                                variant="danger"
                                size="md"
                                class="flex items-center gap-3 shadow-lg hover:shadow-xl transition-all duration-300"
                            >
                                <XCircleIcon class="w-5 h-5" />Reject
                            </ModernButton>
                        </template>
                        <!-- Admin actions: assign, edit, delete (if needed, keep as before) -->
                        <template v-if="userRole.value === 'admin'">
                            <ModernButton
                                v-if="
                                    sellerRequest.status === 'pending' ||
                                    sellerRequest.status === 'under_review'
                                "
                                @click="showApproveModal = true"
                                variant="primary"
                                size="sm"
                                class="flex items-center gap-2"
                                disabled
                            >
                                <CheckCircleIcon class="w-4 h-4" />Approve
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
                                disabled
                            >
                                <XCircleIcon class="w-4 h-4" />Reject
                            </ModernButton>
                            <ModernButton
                                @click="showAssignModal = true"
                                variant="primary"
                                size="sm"
                                class="flex items-center gap-2"
                            >
                                <UserIcon class="w-4 h-4" />Assign Broker
                            </ModernButton>
                            <Link
                                :href="
                                    route(
                                        'seller-requests.edit',
                                        sellerRequest.id
                                    )
                                "
                                ><ModernButton
                                    variant="outline"
                                    size="sm"
                                    class="flex items-center gap-2"
                                    ><PencilIcon
                                        class="w-4 h-4"
                                    />Edit</ModernButton
                                ></Link
                            >
                            <ModernButton
                                @click="showDeleteModal = true"
                                variant="danger"
                                size="sm"
                                class="flex items-center gap-2"
                                ><TrashIcon
                                    class="w-4 h-4"
                                />Delete</ModernButton
                            >
                        </template>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="xl:col-span-2 space-y-8">
                    <!-- Property Details -->
                    <div
                        class="bg-gradient-to-br from-white via-neutral-50 to-blue-50/30 rounded-3xl border border-neutral-200 shadow-xl shadow-neutral-200/20 p-10 backdrop-blur-sm"
                    >
                        <div class="flex items-center gap-4 mb-8">
                            <div class="p-3 bg-gradient-to-br from-coconut-500 to-amber-500 rounded-2xl shadow-lg">
                                <BuildingOfficeIcon class="w-8 h-8 text-white" />
                            </div>
                            <h2 class="text-3xl font-bold text-neutral-900">
                                Property Details
                            </h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100">
                                    <label
                                        class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                        >Asking Price</label
                                    >
                                    <p
                                        class="text-3xl font-bold text-coconut-600 mt-2"
                                    >
                                        {{
                                            formatPrice(
                                                sellerRequest.asking_price
                                            )
                                        }}
                                    </p>
                                </div>

                                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100">
                                    <label
                                        class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                        >Lot Area</label
                                    >
                                    <p
                                        class="text-2xl font-bold text-neutral-900 mt-2"
                                    >
                                        {{ formatArea(sellerRequest.lot_area) }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100">
                                    <label
                                        class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                        >Location</label
                                    >
                                    <p
                                        class="text-2xl font-bold text-neutral-900 mt-2"
                                    >
                                        {{ formatLocation(sellerRequest) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100">
                            <label class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                >Description</label
                            >
                            <p class="text-neutral-700 mt-4 leading-relaxed text-lg">
                                {{ sellerRequest.property_description }}
                            </p>
                        </div>

                        <!-- Features -->
                        <div
                            v-if="
                                sellerRequest.features &&
                                sellerRequest.features.length
                            "
                            class="mt-8 bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100"
                        >
                            <label class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                >Features</label
                            >
                            <div class="flex flex-wrap gap-3 mt-4">
                                <span
                                    v-for="feature in sellerRequest.features"
                                    :key="feature"
                                    class="px-4 py-2 bg-gradient-to-r from-coconut-100 to-amber-100 text-coconut-800 rounded-full text-sm font-semibold shadow-sm border border-coconut-200"
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
                        class="bg-gradient-to-br from-white via-neutral-50 to-purple-50/30 rounded-3xl border border-neutral-200 shadow-xl shadow-neutral-200/20 p-10 backdrop-blur-sm"
                    >
                        <div class="flex items-center gap-4 mb-8">
                            <div class="p-3 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl shadow-lg">
                                <EyeIcon class="w-8 h-8 text-white" />
                            </div>
                            <h2 class="text-3xl font-bold text-neutral-900">
                                Property Images
                            </h2>
                        </div>

                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                        >
                            <div
                                v-for="(
                                    image, index
                                ) in sellerRequest.uploaded_images"
                                :key="index"
                                class="aspect-square bg-neutral-100 rounded-2xl overflow-hidden group cursor-pointer shadow-lg hover:shadow-2xl transition-all duration-500 border border-neutral-200"
                            >
                                <img
                                    :src="
                                        image.startsWith('http') ||
                                        image.startsWith('/')
                                            ? image
                                            : '/storage/' + image
                                    "
                                    :alt="`Property image ${index + 1}`"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Seller Information -->
                    <div
                        class="bg-gradient-to-br from-white via-neutral-50 to-green-50/30 rounded-3xl border border-neutral-200 shadow-xl shadow-neutral-200/20 p-8 backdrop-blur-sm"
                    >
                        <div class="flex items-center gap-4 mb-8">
                            <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl shadow-lg">
                                <UserIcon class="w-8 h-8 text-white" />
                            </div>
                            <h2 class="text-2xl font-bold text-neutral-900">
                                Seller Information
                            </h2>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100">
                                <label
                                    class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                    >Name</label
                                >
                                <p
                                    class="text-xl font-bold text-neutral-900 mt-2"
                                >
                                    {{ sellerRequest.name }}
                                </p>
                            </div>

                            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100">
                                <label
                                    class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                    >Email</label
                                >
                                <p
                                    class="text-neutral-700 flex items-center gap-3 mt-2"
                                >
                                    <div class="p-2 bg-green-100 rounded-lg">
                                        <EnvelopeIcon class="w-5 h-5 text-green-600" />
                                    </div>
                                    <span class="font-medium">{{ sellerRequest.email }}</span>
                                </p>
                            </div>

                            <div v-if="sellerRequest.phone" class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100">
                                <label
                                    class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                    >Phone</label
                                >
                                <p
                                    class="text-neutral-700 flex items-center gap-3 mt-2"
                                >
                                    <div class="p-2 bg-blue-100 rounded-lg">
                                        <PhoneIcon class="w-5 h-5 text-blue-600" />
                                    </div>
                                    <span class="font-medium">{{ sellerRequest.phone }}</span>
                                </p>
                            </div>

                            <div v-if="sellerRequest.address" class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100">
                                <label
                                    class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                    >Address</label
                                >
                                <p class="text-neutral-700 mt-2 font-medium leading-relaxed">
                                    {{ sellerRequest.address }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Request Timeline -->
                    <div
                        class="bg-gradient-to-br from-white via-neutral-50 to-indigo-50/30 rounded-3xl border border-neutral-200 shadow-xl shadow-neutral-200/20 p-8 backdrop-blur-sm"
                    >
                        <div class="flex items-center gap-4 mb-8">
                            <div class="p-3 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl shadow-lg">
                                <CalendarIcon class="w-8 h-8 text-white" />
                            </div>
                            <h2 class="text-2xl font-bold text-neutral-900">
                                Request Timeline
                            </h2>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100">
                                <label
                                    class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                    >Submitted</label
                                >
                                <p class="text-neutral-700 mt-2 font-medium">
                                    {{ formatDate(sellerRequest.created_at) }}
                                </p>
                            </div>

                            <div v-if="sellerRequest.reviewed_at" class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100">
                                <label
                                    class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                    >Reviewed</label
                                >
                                <p class="text-neutral-700 mt-2 font-medium">
                                    {{ formatDate(sellerRequest.reviewed_at) }}
                                </p>
                            </div>

                            <div v-if="sellerRequest.listed_at" class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100">
                                <label
                                    class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                    >Listed</label
                                >
                                <p class="text-neutral-700 mt-2 font-medium">
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
                        class="bg-gradient-to-br from-white via-neutral-50 to-amber-50/30 rounded-3xl border border-neutral-200 shadow-xl shadow-neutral-200/20 p-8 backdrop-blur-sm"
                    >
                        <div class="flex items-center gap-4 mb-8">
                            <div class="p-3 bg-gradient-to-br from-amber-500 to-orange-500 rounded-2xl shadow-lg">
                                <PencilIcon class="w-8 h-8 text-white" />
                            </div>
                            <h2 class="text-2xl font-bold text-neutral-900">
                                Admin Notes
                            </h2>
                        </div>

                        <div class="space-y-6">
                            <div v-if="sellerRequest.admin_notes" class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-neutral-100">
                                <label
                                    class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                    >Notes</label
                                >
                                <div
                                    class="text-neutral-700 mt-4 p-6 bg-gradient-to-br from-neutral-50 to-blue-50 rounded-xl border border-neutral-200 leading-relaxed"
                                >
                                    {{ sellerRequest.admin_notes }}
                                </div>
                            </div>

                            <div v-if="sellerRequest.rejection_reason" class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-red-200">
                                <label class="text-sm font-semibold text-red-600 uppercase tracking-wider"
                                    >Rejection Reason</label
                                >
                                <div
                                    class="text-red-700 mt-4 p-6 bg-gradient-to-br from-red-50 to-orange-50 rounded-xl border border-red-200 leading-relaxed"
                                >
                                    {{ sellerRequest.rejection_reason }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <Modal :show="showApproveModal" @close="showApproveModal = false">
            <div class="p-8 bg-gradient-to-br from-white to-green-50">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl shadow-lg">
                        <CheckCircleIcon class="w-8 h-8 text-white" />
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900">
                        Approve Seller Request
                    </h3>
                </div>

                <form @submit.prevent="approveRequest" class="space-y-6">
                    <div v-if="userRole === 'admin'" class="bg-white rounded-2xl p-6 shadow-lg border border-neutral-100">
                        <label
                            class="block text-sm font-semibold text-neutral-700 mb-3 uppercase tracking-wider"
                        >
                            Assign Broker
                        </label>
                        <select
                            v-model="approveForm.assigned_broker_id"
                            class="w-full px-4 py-3 border-2 border-neutral-200 rounded-xl focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 bg-white shadow-sm"
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

                    <div v-if="userRole === 'admin'" class="bg-white rounded-2xl p-6 shadow-lg border border-neutral-100">
                        <label
                            class="block text-sm font-semibold text-neutral-700 mb-3 uppercase tracking-wider"
                        >
                            Admin Notes (Optional)
                        </label>
                        <textarea
                            v-model="approveForm.admin_notes"
                            rows="4"
                            class="w-full px-4 py-3 border-2 border-neutral-200 rounded-xl focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 bg-white shadow-sm resize-none"
                            placeholder="Add any notes for the broker..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-4 pt-6 border-t border-neutral-200">
                        <ModernButton
                            type="button"
                            @click="showApproveModal = false"
                            variant="outline"
                            size="lg"
                            class="shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            Cancel
                        </ModernButton>
                        <ModernButton
                            type="submit"
                            variant="primary"
                            size="lg"
                            :disabled="approveForm.processing"
                            class="shadow-lg hover:shadow-xl transition-all duration-300"
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
            <div class="p-8 bg-gradient-to-br from-white to-red-50">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-gradient-to-br from-red-500 to-pink-500 rounded-2xl shadow-lg">
                        <XCircleIcon class="w-8 h-8 text-white" />
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900">
                        Reject Seller Request
                    </h3>
                </div>

                <form @submit.prevent="rejectRequest" class="space-y-6">
                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-red-100">
                        <label
                            class="block text-sm font-semibold text-neutral-700 mb-3 uppercase tracking-wider"
                        >
                            Rejection Reason *
                        </label>
                        <textarea
                            v-model="rejectForm.rejection_reason"
                            rows="4"
                            class="w-full px-4 py-3 border-2 border-red-200 rounded-xl focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 bg-white shadow-sm resize-none"
                            placeholder="Please provide a clear reason for rejection..."
                            required
                        ></textarea>
                    </div>

                    <div v-if="userRole === 'admin'" class="bg-white rounded-2xl p-6 shadow-lg border border-neutral-100">
                        <label
                            class="block text-sm font-semibold text-neutral-700 mb-3 uppercase tracking-wider"
                        >
                            Admin Notes (Optional)
                        </label>
                        <textarea
                            v-model="rejectForm.admin_notes"
                            rows="3"
                            class="w-full px-4 py-3 border-2 border-neutral-200 rounded-xl focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 bg-white shadow-sm resize-none"
                            placeholder="Additional internal notes..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-4 pt-6 border-t border-neutral-200">
                        <ModernButton
                            type="button"
                            @click="showRejectModal = false"
                            variant="outline"
                            size="lg"
                            class="shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            Cancel
                        </ModernButton>
                        <ModernButton
                            type="submit"
                            variant="danger"
                            size="lg"
                            :disabled="rejectForm.processing"
                            class="shadow-lg hover:shadow-xl transition-all duration-300"
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
            <div class="p-8 bg-gradient-to-br from-white to-red-50">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-gradient-to-br from-red-500 to-pink-500 rounded-2xl shadow-lg">
                        <TrashIcon class="w-8 h-8 text-white" />
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900">
                        Delete Seller Request
                    </h3>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-lg border border-red-100 mb-6">
                    <p class="text-neutral-600 text-lg leading-relaxed">
                        Are you sure you want to delete this seller request? This
                        action cannot be undone and all associated data will be permanently removed.
                    </p>
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t border-neutral-200">
                    <ModernButton
                        @click="showDeleteModal = false"
                        variant="outline"
                        size="lg"
                        class="shadow-lg hover:shadow-xl transition-all duration-300"
                    >
                        Cancel
                    </ModernButton>
                    <ModernButton
                        @click="deleteRequest"
                        variant="danger"
                        size="lg"
                        :disabled="deleteForm.processing"
                        class="shadow-lg hover:shadow-xl transition-all duration-300"
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
