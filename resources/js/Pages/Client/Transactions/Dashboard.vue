<template>
    <Head title="My Transactions - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-neutral-900">My Transactions</h1>
            <p class="text-neutral-600 mt-1">
                Track and manage your property transactions in real-time
            </p>
        </div>

        <!-- Error State -->
        <ErrorState
            v-if="error"
            type="error"
            @retry="retryLoad"
        />

        <!-- Loading State -->
        <div v-else-if="isLoading" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <LoadingSkeleton v-for="n in 4" :key="n" type="stats-card" />
            </div>
            <LoadingSkeleton type="card" class="h-96" />
        </div>

        <!-- Content -->
        <template v-else>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-600 mb-1">
                                Active Transactions
                            </p>
                            <p class="text-3xl font-bold text-blue-600">
                                {{ activeTransactions.length }}
                            </p>
                            <p class="text-sm text-neutral-500">In progress</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center">
                            <DocumentTextIcon class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-600 mb-1">
                                Pending Actions
                            </p>
                            <p class="text-3xl font-bold text-yellow-600">
                                {{ requiredActions.length }}
                            </p>
                            <p class="text-sm text-neutral-500">Needs attention</p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-2xl flex items-center justify-center">
                            <ClockIcon class="w-6 h-6 text-yellow-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-600 mb-1">
                                Completed
                            </p>
                            <p class="text-3xl font-bold text-green-600">
                                {{ completedTransactions }}
                            </p>
                            <p class="text-sm text-neutral-500">Finalized</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center">
                            <CheckCircleIcon class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-600 mb-1">
                                Engagement Score
                            </p>
                            <p class="text-3xl font-bold text-purple-600">
                                {{ averageEngagementScore }}/100
                            </p>
                            <p class="text-sm text-neutral-500">Activity level</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center">
                            <BoltIcon class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                </div>
            </div>

                <!-- Required Actions Alert -->
                <div v-if="requiredActions.length > 0" class="mb-8">
                    <div
                        class="bg-yellow-50 border border-yellow-200 rounded-lg p-4"
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
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Action Required
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>
                                        You have
                                        {{ requiredActions.length }} pending
                                        action(s) that require your attention.
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <div class="-mx-2 -my-1.5 flex">
                                        <button
                                            @click="
                                                showRequiredActions =
                                                    !showRequiredActions
                                            "
                                            class="bg-yellow-50 px-2 py-1.5 rounded-md text-sm font-medium text-yellow-800 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-yellow-50 focus:ring-yellow-600"
                                        >
                                            {{
                                                showRequiredActions
                                                    ? "Hide"
                                                    : "View"
                                            }}
                                            Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Required Actions List -->
                    <div v-if="showRequiredActions" class="mt-4 space-y-3">
                        <div
                            v-for="action in requiredActions"
                            :key="action.id"
                            class="bg-white border border-yellow-200 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ action.title }}
                                    </h4>
                                    <p class="text-sm text-gray-600">
                                        {{ action.description }}
                                    </p>
                                    <p class="text-xs text-yellow-600 mt-1">
                                        Deadline:
                                        {{ formatDate(action.deadline) }}
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <Link
                                        :href="
                                            route(
                                                'client.transactions.show',
                                                action.transaction_id
                                            )
                                        "
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        View Transaction
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Transactions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">
                            Active Transactions
                        </h2>
                    </div>

                    <div
                        v-if="activeTransactions.length === 0"
                        class="px-6 py-12 text-center"
                    >
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            No active transactions
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            You don't have any active transactions at the
                            moment.
                        </p>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div
                            v-for="transaction in activeTransactions"
                            :key="transaction.id"
                            class="px-6 py-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h3
                                            class="text-lg font-medium text-gray-900"
                                        >
                                            {{ transaction.property.title }}
                                        </h3>
                                        <span
                                            :class="
                                                getStatusBadgeClass(
                                                    transaction.status
                                                )
                                            "
                                            class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            {{
                                                formatStatus(transaction.status)
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600"
                                    >
                                        <div>
                                            <span class="font-medium"
                                                >Location:</span
                                            >
                                            {{ transaction.property.address }},
                                            {{
                                                transaction.property
                                                    .municipality
                                            }}
                                        </div>
                                        <div>
                                            <span class="font-medium"
                                                >Offered Price:</span
                                            >
                                            ₱{{
                                                formatPrice(
                                                    transaction.offered_price
                                                )
                                            }}
                                        </div>
                                        <div>
                                            <span class="font-medium"
                                                >Broker:</span
                                            >
                                            {{ transaction.broker.name }}
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="mt-3">
                                        <div
                                            class="flex items-center justify-between text-sm"
                                        >
                                            <span class="text-gray-600"
                                                >Progress</span
                                            >
                                            <span
                                                class="font-medium text-gray-900"
                                                >{{
                                                    getProgressPercentage(
                                                        transaction.status
                                                    )
                                                }}%</span
                                            >
                                        </div>
                                        <div
                                            class="mt-1 bg-gray-200 rounded-full h-2"
                                        >
                                            <div
                                                :style="{
                                                    width:
                                                        getProgressPercentage(
                                                            transaction.status
                                                        ) + '%',
                                                }"
                                                class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- Client Engagement Score -->
                                    <div
                                        v-if="
                                            transaction.client_engagement_score
                                        "
                                        class="mt-2 flex items-center"
                                    >
                                        <span class="text-sm text-gray-600"
                                            >Engagement Score:</span
                                        >
                                        <div class="ml-2 flex items-center">
                                            <div class="flex space-x-1">
                                                <div
                                                    v-for="i in 5"
                                                    :key="i"
                                                    :class="
                                                        i <=
                                                        transaction.client_engagement_score /
                                                            20
                                                            ? 'text-yellow-400'
                                                            : 'text-gray-300'
                                                    "
                                                    class="w-4 h-4"
                                                >
                                                    <svg
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                            <span
                                                class="ml-2 text-sm font-medium text-gray-900"
                                                >{{
                                                    transaction.client_engagement_score
                                                }}/100</span
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="ml-6 flex flex-col space-y-2">
                                    <Link
                                        :href="
                                            route(
                                                'client.transactions.show',
                                                transaction.id
                                            )
                                        "
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        View Details
                                    </Link>

                                    <div
                                        v-if="
                                            transaction.requires_client_action
                                        "
                                        class="text-center"
                                    >
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                                        >
                                            Action Required
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Milestones -->
                <div
                    v-if="milestones.length > 0"
                    class="mt-8 bg-white shadow rounded-lg"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">
                            Upcoming Milestones
                        </h2>
                    </div>

                    <div class="px-6 py-4">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li
                                    v-for="(milestone, index) in milestones"
                                    :key="milestone.id"
                                >
                                    <div class="relative pb-8">
                                        <span
                                            v-if="
                                                index !== milestones.length - 1
                                            "
                                            class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                            aria-hidden="true"
                                        ></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span
                                                    :class="
                                                        milestone.status ===
                                                        'completed'
                                                            ? 'bg-green-500'
                                                            : milestone.status ===
                                                              'current'
                                                            ? 'bg-blue-500'
                                                            : 'bg-gray-400'
                                                    "
                                                    class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                                >
                                                    <svg
                                                        v-if="
                                                            milestone.status ===
                                                            'completed'
                                                        "
                                                        class="h-5 w-5 text-white"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                    <svg
                                                        v-else
                                                        class="h-5 w-5 text-white"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div
                                                class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4"
                                            >
                                                <div>
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        {{
                                                            milestone.description
                                                        }}
                                                    </p>
                                                </div>
                                                <div
                                                    class="text-right text-sm whitespace-nowrap text-gray-500"
                                                >
                                                    <time>{{
                                                        formatDate(
                                                            milestone.due_date
                                                        )
                                                    }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
        </template>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, Head } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import LoadingSkeleton from "@/Components/LoadingSkeleton.vue";
import EmptyState from "@/Components/EmptyState.vue";
import ErrorState from "@/Components/ErrorState.vue";
import { useFormatters } from "@/Composables/useFormatters";
import {
    DocumentTextIcon,
    ClockIcon,
    CheckCircleIcon,
    BoltIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    activeTransactions: Array,
    milestones: Array,
    requiredActions: Array,
});

const showRequiredActions = ref(false);
const isLoading = ref(false);
const error = ref(null);

const { formatRelativeTime } = useFormatters();

const retryLoad = () => {
    error.value = null;
    window.location.reload();
};

const completedTransactions = computed(() => {
    // This would be calculated from all transactions, not just active ones
    return 0; // Placeholder
});

const averageEngagementScore = computed(() => {
    if (props.activeTransactions.length === 0) return 0;
    const total = props.activeTransactions.reduce(
        (sum, transaction) => sum + (transaction.client_engagement_score || 0),
        0
    );
    return Math.round(total / props.activeTransactions.length);
});

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH").format(price);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatStatus = (status) => {
    return status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const getStatusBadgeClass = (status) => {
    const statusClasses = {
        inquiry: "bg-blue-100 text-blue-800",
        initial_contact: "bg-yellow-100 text-yellow-800",
        property_viewing: "bg-purple-100 text-purple-800",
        offer_made: "bg-green-100 text-green-800",
        negotiation: "bg-orange-100 text-orange-800",
        offer_accepted: "bg-green-100 text-green-800",
        contract_signed: "bg-blue-100 text-blue-800",
        due_diligence: "bg-indigo-100 text-indigo-800",
        financing: "bg-purple-100 text-purple-800",
        closing_preparation: "bg-yellow-100 text-yellow-800",
        finalized: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
        client_approval_pending: "bg-yellow-100 text-yellow-800",
        client_review_required: "bg-blue-100 text-blue-800",
        client_rejected: "bg-red-100 text-red-800",
        client_approved: "bg-green-100 text-green-800",
    };
    return statusClasses[status] || "bg-gray-100 text-gray-800";
};

const getProgressPercentage = (status) => {
    const progressMap = {
        inquiry: 10,
        initial_contact: 20,
        property_viewing: 30,
        offer_made: 40,
        negotiation: 50,
        offer_accepted: 60,
        contract_signed: 70,
        due_diligence: 80,
        financing: 85,
        closing_preparation: 90,
        finalized: 100,
        cancelled: 0,
        client_approval_pending: 35,
        client_review_required: 45,
        client_rejected: 0,
        client_approved: 55,
    };
    return progressMap[status] || 0;
};

onMounted(() => {
    // Set up real-time updates if needed
    console.log("Client Transaction Dashboard mounted");
});
</script>
