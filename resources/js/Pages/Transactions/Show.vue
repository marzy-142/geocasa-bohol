<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { useForm, Link, usePage } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import NotificationService from "@/Services/NotificationService";
import {
    ArrowLeftIcon,
    PencilIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    CurrencyDollarIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
    CalendarIcon,
    DocumentTextIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    EyeIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    TagIcon,
    ChartBarIcon,
    ArrowPathIcon,
    PlusIcon,
    TrashIcon,
    ShareIcon,
    PrinterIcon,
    ArrowDownTrayIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    transaction: Object,
    canEdit: {
        type: Boolean,
        default: false,
    },
    userRole: {
        type: String,
        default: "client",
    },
});

const page = usePage();
const currentTransaction = ref(props.transaction);
let echoChannel = null;

const showStatusModal = ref(false);
const showAdminOversightModal = ref(false);

const statusForm = useForm({
    status: currentTransaction.value.status,
    notes: "",
});

const adminOversightForm = useForm({
    oversight_note: "",
    flag_for_review: false,
});

const updateStatus = () => {
    statusForm.post(
        route("transactions.update-status", currentTransaction.value.id),
        {
            onSuccess: () => {
                showStatusModal.value = false;
                statusForm.reset("notes");
                // Update the current transaction status
                currentTransaction.value.status = statusForm.status;
            },
        }
    );
};

const submitAdminOversightNote = () => {
    adminOversightForm.post(
        route(
            "admin.transactions.add-oversight-note",
            currentTransaction.value.id
        ),
        {
            onSuccess: () => {
                showAdminOversightModal.value = false;
                adminOversightForm.reset();
            },
        }
    );
};

const formatPrice = (price) => {
    const n = Number(price);
    if (Number.isNaN(n) || n === null) return "—";
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(n);
};

const formatDate = (date) => {
    return date
        ? new Date(date).toLocaleDateString("en-PH", {
              year: "numeric",
              month: "long",
              day: "numeric",
          })
        : "Not set";
};

const getStatusColor = (status) => {
    const colors = {
        inquiry: "bg-gray-100 text-gray-800",
        initial_contact: "bg-blue-100 text-blue-800",
        property_viewing: "bg-purple-100 text-purple-800",
        offer_made: "bg-yellow-100 text-yellow-800",
        negotiation: "bg-orange-100 text-orange-800",
        offer_accepted: "bg-green-100 text-green-800",
        contract_signed: "bg-indigo-100 text-indigo-800",
        due_diligence: "bg-pink-100 text-pink-800",
        financing: "bg-cyan-100 text-cyan-800",
        closing_preparation: "bg-violet-100 text-violet-800",
        finalized: "bg-emerald-100 text-emerald-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const canUpdateStatus = () => {
    return [
        "inquiry",
        "initial_contact",
        "property_viewing",
        "offer_made",
        "negotiation",
        "offer_accepted",
        "contract_signed",
        "due_diligence",
        "financing",
        "closing_preparation",
    ].includes(currentTransaction.value.status);
};

// Enhanced computed properties for better UX
const transactionProgress = computed(() => {
    const statusOrder = {
        inquiry: 1,
        initial_contact: 2,
        property_viewing: 3,
        offer_made: 4,
        negotiation: 5,
        offer_accepted: 6,
        contract_signed: 7,
        due_diligence: 8,
        financing: 9,
        closing_preparation: 10,
        finalized: 11,
        rejected: 0,
        cancelled: 0,
    };
    return statusOrder[currentTransaction.value.status] || 0;
});

const progressPercentage = computed(() => {
    return (transactionProgress.value / 11) * 100;
});

const isOverdue = computed(() => {
    if (!currentTransaction.value.created_at) return false;
    const created = new Date(currentTransaction.value.created_at);
    const now = new Date();
    const daysDiff = (now - created) / (1000 * 60 * 60 * 24);
    return (
        daysDiff > 30 &&
        !["finalized", "cancelled", "rejected"].includes(
            currentTransaction.value.status
        )
    );
});

const daysInProgress = computed(() => {
    if (!currentTransaction.value.created_at) return 0;
    const created = new Date(currentTransaction.value.created_at);
    const now = new Date();
    return Math.floor((now - created) / (1000 * 60 * 60 * 24));
});

const statusIcon = computed(() => {
    const icons = {
        inquiry: TagIcon,
        initial_contact: PhoneIcon,
        property_viewing: EyeIcon,
        offer_made: CurrencyDollarIcon,
        negotiation: ArrowPathIcon,
        offer_accepted: CheckCircleIcon,
        contract_signed: DocumentTextIcon,
        due_diligence: CalendarIcon,
        financing: CurrencyDollarIcon,
        closing_preparation: ClockIcon,
        finalized: CheckCircleIcon,
        rejected: XCircleIcon,
        cancelled: XCircleIcon,
    };
    return icons[currentTransaction.value.status] || InformationCircleIcon;
});

const statusDescription = computed(() => {
    const descriptions = {
        inquiry: "Initial inquiry received from client",
        initial_contact: "First contact established with client",
        property_viewing: "Property viewing scheduled or completed",
        offer_made: "Initial offer has been made",
        negotiation: "Price and terms are being negotiated",
        offer_accepted: "Offer has been accepted by all parties",
        contract_signed: "Contract has been signed",
        due_diligence: "Due diligence process in progress",
        financing: "Financing is being arranged",
        closing_preparation: "Preparing for closing",
        finalized: "Transaction has been completed successfully",
        rejected: "Transaction has been rejected",
        cancelled: "Transaction has been cancelled",
    };
    return descriptions[currentTransaction.value.status] || "Unknown status";
});

// Real-time transaction updates
const setupRealtimeUpdates = () => {
    const user = page.props.auth.user;

    // Listen to transaction-specific channel
    echoChannel = window.Echo.private(
        `transaction.${currentTransaction.value.id}`
    )
        .listen("TransactionCreated", (e) => {
            console.log("New transaction created:", e.transaction);
            // This shouldn't happen on the show page, but handle it gracefully
        })
        .listen("TransactionStatusUpdated", (e) => {
            console.log("Transaction status updated:", e);

            // Update the current transaction with new data
            currentTransaction.value = {
                ...currentTransaction.value,
                ...e.transaction,
                status: e.newStatus,
            };

            // Update the status form
            statusForm.status = e.newStatus;

            // Show notification
            NotificationService.showTransactionUpdate({
                transaction_id: e.transaction.id,
                property_title:
                    e.transaction.property?.title || "Unknown Property",
                new_status: e.newStatus,
                old_status: e.oldStatus,
            });
        });

    // Also listen to user-specific channels for broader updates
    if (user.role === "client") {
        window.Echo.private(`client.${user.id}`).listen(
            "TransactionStatusUpdated",
            (e) => {
                if (e.transaction.id === currentTransaction.value.id) {
                    currentTransaction.value = {
                        ...currentTransaction.value,
                        ...e.transaction,
                        status: e.newStatus,
                    };
                    statusForm.status = e.newStatus;
                }
            }
        );
    } else if (user.role === "broker") {
        window.Echo.private(`broker.${user.id}`).listen(
            "TransactionStatusUpdated",
            (e) => {
                if (e.transaction.id === currentTransaction.value.id) {
                    currentTransaction.value = {
                        ...currentTransaction.value,
                        ...e.transaction,
                        status: e.newStatus,
                    };
                    statusForm.status = e.newStatus;
                }
            }
        );
    }
};

const cleanupRealtimeUpdates = () => {
    if (echoChannel) {
        window.Echo.leave(`transaction.${currentTransaction.value.id}`);
        echoChannel = null;
    }

    const user = page.props.auth.user;
    if (user.role === "client") {
        window.Echo.leave(`client.${user.id}`);
    } else if (user.role === "broker") {
        window.Echo.leave(`broker.${user.id}`);
    }
};

onMounted(() => {
    setupRealtimeUpdates();
});

onUnmounted(() => {
    cleanupRealtimeUpdates();
});
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
                        <span class="text-gray-900 font-medium"
                            >Transaction Details</span
                        >
                    </nav>

                    <!-- Status Alert -->
                    <div
                        v-if="isOverdue"
                        class="mb-6 bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg"
                    >
                        <div class="flex items-center">
                            <ExclamationTriangleIcon
                                class="w-5 h-5 text-amber-400 mr-3"
                            />
                            <div>
                                <h3 class="text-sm font-medium text-amber-800">
                                    Transaction Overdue
                                </h3>
                                <p class="text-sm text-amber-700">
                                    This transaction has been in progress for
                                    {{ daysInProgress }} days. Consider
                                    following up with the client.
                                </p>
                            </div>
                        </div>
                    </div>

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
                                            <component
                                                :is="statusIcon"
                                                class="w-8 h-8 text-white"
                                            />
                                        </div>
                                        <div>
                                            <h1
                                                class="text-3xl font-bold text-white"
                                            >
                                                Transaction #{{
                                                    currentTransaction.transaction_number
                                                }}
                                            </h1>
                                            <p class="text-blue-100 text-lg">
                                                {{ statusDescription }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="mt-6">
                                        <div
                                            class="flex items-center justify-between text-sm text-blue-100 mb-2"
                                        >
                                            <span>Transaction Progress</span>
                                            <span
                                                >{{
                                                    Math.round(
                                                        progressPercentage
                                                    )
                                                }}%</span
                                            >
                                        </div>
                                        <div
                                            class="w-full bg-white/20 rounded-full h-2"
                                        >
                                            <div
                                                class="bg-white h-2 rounded-full transition-all duration-500 ease-out"
                                                :style="{
                                                    width:
                                                        progressPercentage +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div
                                    class="mt-6 lg:mt-0 lg:ml-8 flex flex-wrap gap-3"
                                >
                                    <!-- Edit Button - Only for brokers -->
                                    <Link
                                        v-if="canEdit"
                                        :href="
                                            route(
                                                'transactions.edit',
                                                currentTransaction.id
                                            )
                                        "
                                        class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg font-medium transition-colors backdrop-blur-sm"
                                    >
                                        <PencilIcon class="w-4 h-4 mr-2" />
                                        Edit
                                    </Link>

                                    <!-- Update Status Button - Only for brokers -->
                                    <button
                                        v-if="canEdit && canUpdateStatus()"
                                        @click="showStatusModal = true"
                                        class="inline-flex items-center px-4 py-2 bg-green-500/90 hover:bg-green-500 text-white rounded-lg font-medium transition-colors"
                                    >
                                        <ArrowPathIcon class="w-4 h-4 mr-2" />
                                        Update Status
                                    </button>

                                    <!-- Admin Oversight Note Button - Only for admins -->
                                    <button
                                        v-if="userRole === 'admin'"
                                        @click="showAdminOversightModal = true"
                                        class="inline-flex items-center px-4 py-2 bg-amber-500/90 hover:bg-amber-500 text-white rounded-lg font-medium transition-colors"
                                    >
                                        <PencilIcon class="w-4 h-4 mr-2" />
                                        Add Oversight Note
                                    </button>

                                    <button
                                        class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg font-medium transition-colors backdrop-blur-sm"
                                    >
                                        <ShareIcon class="w-4 h-4 mr-2" />
                                        Share
                                    </button>
                                    <button
                                        class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg font-medium transition-colors backdrop-blur-sm"
                                    >
                                        <PrinterIcon class="w-4 h-4 mr-2" />
                                        Print
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="space-y-8">
                    <!-- Main Content Row -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left Column - Property & People -->
                        <div class="space-y-6">
                            <!-- Property Information -->
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
                                        Property Details
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <div class="space-y-4">
                                        <div>
                                            <h4
                                                class="font-semibold text-gray-900 text-lg mb-2"
                                            >
                                                {{
                                                    currentTransaction.property
                                                        .title
                                                }}
                                            </h4>
                                            <div
                                                class="flex items-center text-sm text-gray-500 mb-3"
                                            >
                                                <MapPinIcon
                                                    class="w-4 h-4 mr-1"
                                                />
                                                {{
                                                    currentTransaction.property
                                                        .location
                                                }}
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 gap-3">
                                            <div
                                                class="bg-gray-50 rounded-lg p-3"
                                            >
                                                <p
                                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                                >
                                                    Type
                                                </p>
                                                <p
                                                    class="text-sm font-semibold text-gray-900"
                                                >
                                                    {{
                                                        currentTransaction
                                                            .property.type
                                                    }}
                                                </p>
                                            </div>
                                            <div
                                                class="bg-gray-50 rounded-lg p-3"
                                            >
                                                <p
                                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide"
                                                >
                                                    Listed Price
                                                </p>
                                                <p
                                                    class="text-sm font-semibold text-gray-900"
                                                >
                                                    {{
                                                        formatPrice(
                                                            currentTransaction
                                                                .property
                                                                .total_price
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <Link
                                            v-if="
                                                currentTransaction.property &&
                                                currentTransaction.property.slug
                                            "
                                            :href="
                                                route(
                                                    'broker.properties.show',
                                                    currentTransaction.property
                                                        .slug
                                                )
                                            "
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                                        >
                                            <EyeIcon class="w-4 h-4 mr-2" />
                                            View Property
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- People Involved -->
                            <div
                                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                            >
                                <div
                                    class="px-6 py-4 bg-gray-50 border-b border-gray-200"
                                >
                                    <h3
                                        class="text-lg font-semibold text-gray-900 flex items-center"
                                    >
                                        <UserGroupIcon
                                            class="w-5 h-5 mr-2 text-blue-600"
                                        />
                                        People Involved
                                    </h3>
                                </div>
                                <div class="p-6 space-y-6">
                                    <!-- Client -->
                                    <div>
                                        <p
                                            class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3"
                                        >
                                            Client
                                        </p>
                                        <div
                                            class="flex items-center space-x-3 mb-3"
                                        >
                                            <div
                                                class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center"
                                            >
                                                <span
                                                    class="text-sm font-semibold text-green-600"
                                                >
                                                    {{
                                                        currentTransaction.client.name.charAt(
                                                            0
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                            <div>
                                                <h4
                                                    class="font-semibold text-gray-900"
                                                >
                                                    {{
                                                        currentTransaction
                                                            .client.name
                                                    }}
                                                </h4>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{
                                                        currentTransaction
                                                            .client.email
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <Link
                                            :href="
                                                route(
                                                    'clients.show',
                                                    currentTransaction.client.id
                                                )
                                            "
                                            class="inline-flex items-center px-3 py-1 bg-green-100 hover:bg-green-200 text-green-700 text-sm font-medium rounded-lg transition-colors"
                                        >
                                            <EyeIcon class="w-4 h-4 mr-1" />
                                            View Client
                                        </Link>
                                    </div>

                                    <!-- Broker -->
                                    <div class="border-t border-gray-100 pt-6">
                                        <p
                                            class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-3"
                                        >
                                            Assigned Broker
                                        </p>
                                        <div
                                            class="flex items-center space-x-3"
                                        >
                                            <div
                                                class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center"
                                            >
                                                <span
                                                    class="text-sm font-semibold text-purple-600"
                                                >
                                                    {{
                                                        currentTransaction.broker.name.charAt(
                                                            0
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                            <div>
                                                <h4
                                                    class="font-semibold text-gray-900"
                                                >
                                                    {{
                                                        currentTransaction
                                                            .broker.name
                                                    }}
                                                </h4>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{
                                                        currentTransaction
                                                            .broker.email
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Middle Column - Timeline & Notes -->
                        <div class="space-y-6">
                            <!-- Detailed Timeline -->
                            <div
                                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                            >
                                <div
                                    class="px-6 py-4 bg-gray-50 border-b border-gray-200"
                                >
                                    <h3
                                        class="text-lg font-semibold text-gray-900 flex items-center"
                                    >
                                        <ClockIcon
                                            class="w-5 h-5 mr-2 text-indigo-600"
                                        />
                                        Key Dates
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <div class="space-y-4">
                                        <!-- Created -->
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center"
                                                >
                                                    <CalendarIcon
                                                        class="w-4 h-4 text-blue-600"
                                                    />
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p
                                                    class="text-sm font-medium text-gray-900"
                                                >
                                                    Transaction Created
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{
                                                        formatDate(
                                                            currentTransaction.created_at
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Contract Date -->
                                        <div
                                            v-if="
                                                currentTransaction.contract_date
                                            "
                                            class="flex items-start space-x-3"
                                        >
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center"
                                                >
                                                    <DocumentTextIcon
                                                        class="w-4 h-4 text-green-600"
                                                    />
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p
                                                    class="text-sm font-medium text-gray-900"
                                                >
                                                    Contract Signed
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{
                                                        formatDate(
                                                            currentTransaction.contract_date
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Closing Date -->
                                        <div
                                            v-if="
                                                currentTransaction.closing_date
                                            "
                                            class="flex items-start space-x-3"
                                        >
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center"
                                                >
                                                    <CheckCircleIcon
                                                        class="w-4 h-4 text-emerald-600"
                                                    />
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p
                                                    class="text-sm font-medium text-gray-900"
                                                >
                                                    Transaction Closed
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    {{
                                                        formatDate(
                                                            currentTransaction.closing_date
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div
                                v-if="currentTransaction.notes"
                                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                            >
                                <div
                                    class="px-6 py-4 bg-gray-50 border-b border-gray-200"
                                >
                                    <h3
                                        class="text-lg font-semibold text-gray-900 flex items-center"
                                    >
                                        <DocumentTextIcon
                                            class="w-5 h-5 mr-2 text-gray-600"
                                        />
                                        Transaction Notes
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <p
                                        class="text-sm text-gray-700 leading-relaxed"
                                    >
                                        {{ currentTransaction.notes }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Financials, Status, Documents -->
                        <div class="space-y-6">
                            <!-- Financial Summary -->
                            <div
                                class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border border-green-200 p-6"
                            >
                                <div
                                    class="flex items-center justify-between mb-4"
                                >
                                    <h3
                                        class="text-lg font-semibold text-green-900"
                                    >
                                        Financial Summary
                                    </h3>
                                    <CurrencyDollarIcon
                                        class="w-6 h-6 text-green-600"
                                    />
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-green-700">
                                            Offered Price
                                        </p>
                                        <p
                                            class="text-xl font-bold text-green-900"
                                        >
                                            {{
                                                formatPrice(
                                                    currentTransaction.offered_price
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        v-if="currentTransaction.final_price"
                                        class="pt-4 border-t border-green-200"
                                    >
                                        <p class="text-sm text-green-700">
                                            Final Price
                                        </p>
                                        <p
                                            class="text-xl font-bold text-green-900"
                                        >
                                            {{
                                                formatPrice(
                                                    currentTransaction.final_price
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Transaction Status -->
                            <div
                                class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-6"
                            >
                                <div
                                    class="flex items-center justify-between mb-4"
                                >
                                    <h3
                                        class="text-lg font-semibold text-blue-900"
                                    >
                                        Current Status
                                    </h3>
                                    <component
                                        :is="statusIcon"
                                        class="w-6 h-6 text-blue-600"
                                    />
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <span
                                            :class="
                                                getStatusColor(
                                                    currentTransaction.status
                                                )
                                            "
                                            class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                                        >
                                            {{
                                                currentTransaction.status.replace(
                                                    "_",
                                                    " "
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-blue-700">
                                        {{ statusDescription }}
                                    </p>
                                    <div class="pt-2">
                                        <div
                                            class="flex items-center justify-between text-xs text-blue-600 mb-1"
                                        >
                                            <span>Progress</span>
                                            <span
                                                >{{
                                                    Math.round(
                                                        progressPercentage
                                                    )
                                                }}%</span
                                            >
                                        </div>
                                        <div
                                            class="w-full bg-blue-200 rounded-full h-2"
                                        >
                                            <div
                                                class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                                                :style="{
                                                    width:
                                                        progressPercentage +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Documents -->
                            <div
                                v-if="
                                    currentTransaction.documents &&
                                    currentTransaction.documents.length > 0
                                "
                                class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                            >
                                <div
                                    class="px-6 py-4 bg-gray-50 border-b border-gray-200"
                                >
                                    <h3
                                        class="text-lg font-semibold text-gray-900 flex items-center"
                                    >
                                        <ArrowDownTrayIcon
                                            class="w-5 h-5 mr-2 text-gray-600"
                                        />
                                        Documents ({{
                                            currentTransaction.documents.length
                                        }})
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <div class="space-y-3">
                                        <div
                                            v-for="doc in currentTransaction.documents"
                                            :key="doc.id"
                                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
                                        >
                                            <div
                                                class="flex items-center space-x-3"
                                            >
                                                <DocumentTextIcon
                                                    class="w-5 h-5 text-gray-400"
                                                />
                                                <span
                                                    class="text-sm font-medium text-gray-900"
                                                    >{{ doc.name }}</span
                                                >
                                            </div>
                                            <a
                                                :href="doc.url"
                                                target="_blank"
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                            >
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Status Update Modal -->
        <div
            v-if="showStatusModal"
            class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
        >
            <div
                class="relative bg-white rounded-xl shadow-2xl w-full max-w-md"
            >
                <!-- Modal Header -->
                <div
                    class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-t-xl"
                >
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-xl font-semibold text-white flex items-center"
                        >
                            <ArrowPathIcon class="w-5 h-5 mr-2" />
                            Update Transaction Status
                        </h3>
                        <button
                            @click="showStatusModal = false"
                            class="text-white/80 hover:text-white transition-colors"
                        >
                            <XCircleIcon class="w-6 h-6" />
                        </button>
                    </div>
                    <p class="text-blue-100 text-sm mt-1">
                        Update the current status of this transaction
                    </p>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form @submit.prevent="updateStatus" class="space-y-6">
                        <!-- Current Status Display -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Current Status
                                    </p>
                                    <div class="flex items-center mt-1">
                                        <component
                                            :is="statusIcon"
                                            class="w-4 h-4 mr-2 text-gray-600"
                                        />
                                        <span
                                            :class="
                                                getStatusColor(
                                                    currentTransaction.status
                                                )
                                            "
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{
                                                currentTransaction.status.replace(
                                                    "_",
                                                    " "
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <ArrowRightIcon class="w-5 h-5 text-gray-400" />
                            </div>
                        </div>

                        <!-- New Status Selection -->
                        <div>
                            <label
                                for="status"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                New Status
                            </label>
                            <select
                                v-model="statusForm.status"
                                id="status"
                                class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                required
                            >
                                <option value="inquiry">Inquiry</option>
                                <option value="initial_contact">
                                    Initial Contact
                                </option>
                                <option value="property_viewing">
                                    Property Viewing
                                </option>
                                <option value="offer_made">Offer Made</option>
                                <option value="negotiation">Negotiation</option>
                                <option value="offer_accepted">
                                    Offer Accepted
                                </option>
                                <option value="contract_signed">
                                    Contract Signed
                                </option>
                                <option value="due_diligence">
                                    Due Diligence
                                </option>
                                <option value="financing">Financing</option>
                                <option value="closing_preparation">
                                    Closing Preparation
                                </option>
                                <option value="finalized">Finalized</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <!-- Status Notes -->
                        <div>
                            <label
                                for="status_notes"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Status Update Notes
                            </label>
                            <textarea
                                v-model="statusForm.notes"
                                id="status_notes"
                                rows="4"
                                class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                placeholder="Add notes about this status change..."
                            ></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div
                            class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200"
                        >
                            <button
                                type="button"
                                @click="showStatusModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="statusForm.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            >
                                <ArrowPathIcon
                                    v-if="statusForm.processing"
                                    class="w-4 h-4 mr-2 animate-spin"
                                />
                                <span v-if="statusForm.processing"
                                    >Updating...</span
                                >
                                <span v-else>Update Status</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Admin Oversight Note Modal -->
        <div
            v-if="showAdminOversightModal"
            class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
        >
            <div
                class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg"
            >
                <!-- Modal Header -->
                <div
                    class="px-6 py-4 bg-gradient-to-r from-amber-600 to-orange-700 rounded-t-xl"
                >
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-xl font-semibold text-white flex items-center"
                        >
                            <PencilIcon class="w-5 h-5 mr-2" />
                            Add Administrative Oversight Note
                        </h3>
                        <button
                            @click="showAdminOversightModal = false"
                            class="text-white/80 hover:text-white transition-colors"
                        >
                            <XCircleIcon class="w-6 h-6" />
                        </button>
                    </div>
                    <p class="text-amber-100 text-sm mt-1">
                        For monitoring and oversight purposes only
                    </p>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form
                        @submit.prevent="submitAdminOversightNote"
                        class="space-y-6"
                    >
                        <!-- Warning Alert -->
                        <div
                            class="bg-amber-50 border border-amber-200 rounded-lg p-4"
                        >
                            <div class="flex gap-3">
                                <ExclamationTriangleIcon
                                    class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"
                                />
                                <div class="text-sm text-amber-800">
                                    <p class="font-medium mb-1">
                                        Read-Only Access
                                    </p>
                                    <p>
                                        As an administrator, you can only add
                                        oversight notes. You cannot modify
                                        transaction data, status, or any
                                        financial information.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Reference -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600 mb-1">
                                Transaction
                            </p>
                            <p class="font-semibold text-gray-900">
                                {{ currentTransaction.transaction_number }}
                            </p>
                            <p class="text-sm text-gray-600 mt-2">
                                {{ currentTransaction.property?.title }}
                            </p>
                        </div>

                        <!-- Oversight Note -->
                        <div>
                            <label
                                for="oversight_note"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Oversight Note
                                <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="adminOversightForm.oversight_note"
                                id="oversight_note"
                                rows="5"
                                class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm"
                                placeholder="Enter your administrative oversight note here..."
                                required
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                This note will be visible to the broker and
                                added to the transaction record with an admin
                                timestamp.
                            </p>
                        </div>

                        <!-- Flag for Review -->
                        <div class="flex items-start gap-3">
                            <input
                                type="checkbox"
                                id="flag_for_review"
                                v-model="adminOversightForm.flag_for_review"
                                class="mt-1 w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500"
                            />
                            <div class="flex-1">
                                <label
                                    for="flag_for_review"
                                    class="text-sm font-medium text-gray-700 cursor-pointer"
                                >
                                    Flag this transaction for administrative
                                    review
                                </label>
                                <p class="text-xs text-gray-500 mt-1">
                                    The broker will be notified that this
                                    transaction requires administrative
                                    attention.
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div
                            class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200"
                        >
                            <button
                                type="button"
                                @click="showAdminOversightModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="
                                    adminOversightForm.processing ||
                                    !adminOversightForm.oversight_note
                                "
                                class="px-6 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="adminOversightForm.processing"
                                    >Adding Note...</span
                                >
                                <span v-else>Add Oversight Note</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
