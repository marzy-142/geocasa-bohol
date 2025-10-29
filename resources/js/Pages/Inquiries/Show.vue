<template>
    <ModernDashboardLayout>
        <!-- Clean Header with Back Button -->
        <div class="mb-6">
            <Link
                :href="route('inquiries.index')"
                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
            >
                <svg
                    class="w-4 h-4 mr-1"
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
                Back to Inquiries
            </Link>

            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        {{ inquiry.name }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Inquiry from {{ formatDate(inquiry.created_at) }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        :class="getStatusBadgeClass(inquiry.status)"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg"
                    >
                        {{
                            inquiry.status.charAt(0).toUpperCase() +
                            inquiry.status.slice(1)
                        }}
                    </span>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto space-y-5">
            <!-- Quick Actions Bar -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-4"
            >
                <div class="flex flex-wrap gap-3">
                    <button
                        v-if="can.respond && !showResponseForm"
                        @click="showResponseForm = true"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                    >
                        Reply to Inquiry
                    </button>
                    <Link
                        v-if="!inquiry.transaction"
                        :href="
                            route('transactions.create', {
                                inquiry_id: inquiry.id,
                            })
                        "
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        Start Transaction
                    </Link>
                    <button
                        v-if="can.delete"
                        @click="deleteInquiry"
                        class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors ml-auto"
                    >
                        Delete Inquiry
                    </button>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Left Column: Inquiry Message & Details -->
                <div class="lg:col-span-2 space-y-5">
                    <!-- Message Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                    >
                        <h3 class="text-base font-semibold text-gray-900 mb-4">
                            Message
                        </h3>
                        <p
                            class="text-gray-700 leading-relaxed whitespace-pre-wrap"
                        >
                            {{ inquiry.message }}
                        </p>
                    </div>

                    <!-- Property Information -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                    >
                        <h3 class="text-base font-semibold text-gray-900 mb-4">
                            Property Details
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ inquiry.property.title }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ inquiry.property.type }} •
                                    {{ inquiry.property.municipality }},
                                    {{ inquiry.property.province }}
                                </p>
                            </div>
                            <div
                                v-if="inquiry.property.total_price"
                                class="flex items-baseline"
                            >
                                <span
                                    class="text-2xl font-semibold text-gray-900"
                                >
                                    ₱{{
                                        Number(
                                            inquiry.property.total_price
                                        ).toLocaleString()
                                    }}
                                </span>
                            </div>
                            <Link
                                v-if="inquiry.property?.slug"
                                :href="
                                    route(
                                        'broker.properties.show',
                                        inquiry.property.slug
                                    )
                                "
                                class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium"
                            >
                                View property
                                <svg
                                    class="w-4 h-4 ml-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    ></path>
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Broker Response (if exists) -->
                    <div
                        v-if="inquiry.broker_response || inquiry.broker_notes"
                        class="bg-blue-50 rounded-xl border border-blue-100 p-6"
                    >
                        <h3 class="text-base font-semibold text-gray-900 mb-4">
                            Your Response
                        </h3>
                        <div v-if="inquiry.broker_response" class="mb-4">
                            <p class="text-gray-700 leading-relaxed">
                                {{ inquiry.broker_response }}
                            </p>
                        </div>
                        <div
                            v-if="inquiry.broker_notes"
                            class="pt-4 border-t border-blue-200"
                        >
                            <p class="text-xs font-medium text-gray-500 mb-1">
                                Internal Notes
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ inquiry.broker_notes }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Contact & Status Info -->
                <div class="space-y-5">
                    <!-- Contact Information -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                    >
                        <h3 class="text-base font-semibold text-gray-900 mb-4">
                            Contact
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <svg
                                    class="w-5 h-5 text-gray-400 mr-3 mt-0.5 flex-shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    ></path>
                                </svg>
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ inquiry.name }}
                                    </p>
                                    <p
                                        v-if="inquiry.client"
                                        class="text-xs text-gray-500 mt-0.5"
                                    >
                                        Client
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <svg
                                    class="w-5 h-5 text-gray-400 mr-3 mt-0.5 flex-shrink-0"
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
                                <a
                                    :href="'mailto:' + inquiry.email"
                                    class="text-sm text-blue-600 hover:text-blue-700"
                                >
                                    {{ inquiry.email }}
                                </a>
                            </div>
                            <div v-if="inquiry.phone" class="flex items-start">
                                <svg
                                    class="w-5 h-5 text-gray-400 mr-3 mt-0.5 flex-shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C7.82 21 3 16.18 3 10V5z"
                                    ></path>
                                </svg>
                                <a
                                    :href="'tel:' + inquiry.phone"
                                    class="text-sm text-blue-600 hover:text-blue-700"
                                >
                                    {{ inquiry.phone }}
                                </a>
                            </div>
                        </div>
                        <Link
                            v-if="inquiry.client"
                            :href="route('clients.show', inquiry.client.id)"
                            class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 font-medium mt-4"
                        >
                            View client profile
                            <svg
                                class="w-4 h-4 ml-1"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                ></path>
                            </svg>
                        </Link>
                    </div>

                    <!-- Status Timeline -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                    >
                        <h3 class="text-base font-semibold text-gray-900 mb-4">
                            Timeline
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <div
                                    class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 mr-3 flex-shrink-0"
                                ></div>
                                <div>
                                    <p class="text-sm text-gray-900">
                                        Inquiry received
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ formatDate(inquiry.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="inquiry.contacted_at"
                                class="flex items-start"
                            >
                                <div
                                    class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 mr-3 flex-shrink-0"
                                ></div>
                                <div>
                                    <p class="text-sm text-gray-900">
                                        Contacted
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ formatDate(inquiry.contacted_at) }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="inquiry.responded_at"
                                class="flex items-start"
                            >
                                <div
                                    class="w-2 h-2 rounded-full bg-green-500 mt-1.5 mr-3 flex-shrink-0"
                                ></div>
                                <div>
                                    <p class="text-sm text-gray-900">
                                        Responded
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ formatDate(inquiry.responded_at) }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="inquiry.scheduled_at"
                                class="flex items-start"
                            >
                                <div
                                    class="w-2 h-2 rounded-full bg-purple-500 mt-1.5 mr-3 flex-shrink-0"
                                ></div>
                                <div>
                                    <p class="text-sm text-gray-900">
                                        Scheduled
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ formatDate(inquiry.scheduled_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inquiry Type Badge -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                    >
                        <h3 class="text-base font-semibold text-gray-900 mb-3">
                            Type
                        </h3>
                        <span
                            :class="getTypeBadgeClass(inquiry.inquiry_type)"
                            class="inline-flex px-3 py-1.5 text-sm font-medium rounded-lg"
                        >
                            {{
                                inquiry.inquiry_type.charAt(0).toUpperCase() +
                                inquiry.inquiry_type.slice(1)
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Response Form -->
            <div
                v-if="showResponseForm"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
            >
                <h3 class="text-base font-semibold text-gray-900 mb-5">
                    Send Response
                </h3>

                <form @submit.prevent="submitResponse" class="space-y-5">
                    <!-- Quick Templates -->
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-500 mb-2"
                            >Quick templates</label
                        >
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="text-xs px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg transition-colors"
                                @click="
                                    responseForm.broker_response =
                                        'Thanks for your inquiry! I\'ll reach out shortly to discuss details.'
                                "
                            >
                                Thanks + will reach out
                            </button>
                            <button
                                type="button"
                                class="text-xs px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg transition-colors"
                                @click="
                                    responseForm.broker_response =
                                        'Let\'s schedule a property viewing. Please share your availability.';
                                    responseForm.status = 'scheduled';
                                "
                            >
                                Schedule viewing
                            </button>
                            <button
                                type="button"
                                class="text-xs px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg transition-colors"
                                @click="
                                    responseForm.broker_response =
                                        'I\'ve reviewed your inquiry and will prepare recommendations within 24 hours.'
                                "
                            >
                                Preparing recommendations
                            </button>
                        </div>
                    </div>

                    <!-- Response Message -->
                    <div>
                        <label
                            for="broker_response"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Response Message <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="broker_response"
                            v-model="responseForm.broker_response"
                            rows="4"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{
                                'border-red-500':
                                    responseForm.errors.broker_response,
                            }"
                            placeholder="Type your response..."
                            required
                        ></textarea>
                        <p
                            v-if="responseForm.errors.broker_response"
                            class="mt-1.5 text-sm text-red-600"
                        >
                            {{ responseForm.errors.broker_response }}
                        </p>
                        <p
                            class="mt-1.5 text-xs text-blue-600 flex items-center"
                        >
                            <svg
                                class="w-4 h-4 mr-1"
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
                            This message will be sent to {{ inquiry.email }}
                        </p>
                    </div>

                    <!-- Status Selection -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Update Status <span class="text-red-500">*</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                @click="responseForm.status = 'contacted'"
                                :class="[
                                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                    responseForm.status === 'contacted'
                                        ? 'bg-amber-100 text-amber-800 border-2 border-amber-300'
                                        : 'bg-gray-50 text-gray-700 border-2 border-transparent hover:bg-gray-100',
                                ]"
                            >
                                Contacted
                            </button>
                            <button
                                type="button"
                                @click="responseForm.status = 'scheduled'"
                                :class="[
                                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                    responseForm.status === 'scheduled'
                                        ? 'bg-purple-100 text-purple-800 border-2 border-purple-300'
                                        : 'bg-gray-50 text-gray-700 border-2 border-transparent hover:bg-gray-100',
                                ]"
                            >
                                Scheduled
                            </button>
                            <button
                                type="button"
                                @click="responseForm.status = 'completed'"
                                :class="[
                                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                    responseForm.status === 'completed'
                                        ? 'bg-green-100 text-green-800 border-2 border-green-300'
                                        : 'bg-gray-50 text-gray-700 border-2 border-transparent hover:bg-gray-100',
                                ]"
                            >
                                Completed
                            </button>
                            <button
                                type="button"
                                @click="responseForm.status = 'closed'"
                                :class="[
                                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                    responseForm.status === 'closed'
                                        ? 'bg-gray-200 text-gray-800 border-2 border-gray-400'
                                        : 'bg-gray-50 text-gray-700 border-2 border-transparent hover:bg-gray-100',
                                ]"
                            >
                                Closed
                            </button>
                        </div>
                    </div>

                    <!-- Completion Details (for Completed/Closed) -->
                    <div
                        v-if="
                            ['completed', 'closed'].includes(
                                responseForm.status
                            )
                        "
                        class="space-y-4 p-4 bg-gray-50 rounded-lg"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    for="completion_outcome"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Outcome
                                    <span
                                        v-if="
                                            responseForm.status === 'completed'
                                        "
                                        class="text-red-500"
                                        >*</span
                                    >
                                </label>
                                <select
                                    id="completion_outcome"
                                    v-model="responseForm.completion_outcome"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :required="
                                        responseForm.status === 'completed'
                                    "
                                >
                                    <option value="">Select outcome</option>
                                    <option value="won">Won</option>
                                    <option value="lost">Lost</option>
                                    <option value="no_response">
                                        No response
                                    </option>
                                    <option value="other">Other</option>
                                </select>
                                <p
                                    v-if="
                                        responseForm.errors.completion_outcome
                                    "
                                    class="mt-1.5 text-sm text-red-600"
                                >
                                    {{ responseForm.errors.completion_outcome }}
                                </p>
                            </div>
                            <div>
                                <label
                                    for="completion_reason"
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Reason (optional)
                                </label>
                                <input
                                    id="completion_reason"
                                    type="text"
                                    maxlength="255"
                                    v-model="responseForm.completion_reason"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :class="{
                                        'border-red-500':
                                            responseForm.errors
                                                .completion_reason,
                                    }"
                                    placeholder="e.g., client chose another property"
                                />
                                <p
                                    v-if="responseForm.errors.completion_reason"
                                    class="mt-1.5 text-sm text-red-600"
                                >
                                    {{ responseForm.errors.completion_reason }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <label
                                for="completion_notes"
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Completion Notes (optional)
                            </label>
                            <textarea
                                id="completion_notes"
                                rows="3"
                                v-model="responseForm.completion_notes"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{
                                    'border-red-500':
                                        responseForm.errors.completion_notes,
                                }"
                                placeholder="Additional details for reporting or follow-up"
                            ></textarea>
                            <p
                                v-if="responseForm.errors.completion_notes"
                                class="mt-1.5 text-sm text-red-600"
                            >
                                {{ responseForm.errors.completion_notes }}
                            </p>
                        </div>
                    </div>

                    <!-- Internal Notes -->
                    <div>
                        <label
                            for="broker_notes"
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Internal Notes (optional)
                        </label>
                        <textarea
                            id="broker_notes"
                            v-model="responseForm.broker_notes"
                            rows="3"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Private notes for your reference..."
                        ></textarea>
                        <p class="mt-1.5 text-xs text-gray-500">
                            These notes are only visible to you
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div
                        class="flex justify-end gap-3 pt-4 border-t border-gray-200"
                    >
                        <button
                            type="button"
                            @click="showResponseForm = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="responseForm.processing"
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 rounded-lg transition-colors"
                        >
                            {{
                                responseForm.processing
                                    ? "Sending..."
                                    : "Send Response"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    inquiry: Object,
    can: Object,
});

const showResponseForm = ref(true);

const responseForm = useForm({
    broker_response: "",
    status: "contacted",
    scheduled_at: "",
    broker_notes: "",
    // Completion fields
    completion_outcome: "",
    completion_reason: "",
    completion_notes: "",
});

const submitResponse = () => {
    responseForm.post(route("inquiries.respond", props.inquiry.id), {
        onSuccess: () => {
            showResponseForm.value = false;
            responseForm.reset();
        },
    });
};

// Soft guard + accept flow
const createTransaction = () => {
    // If already converted, redirect to transaction
    if (props.inquiry.transaction) {
        router.visit(route("transactions.show", props.inquiry.transaction.id));
        return;
    }

    // If inquiry is NEW and there is no broker response, ask for confirmation
    if (props.inquiry.status === "new" && !props.inquiry.broker_response) {
        const proceed = confirm(
            "You haven't added a response yet. Proceed to create a transaction anyway?"
        );
        if (!proceed) {
            // Open quick response form instead
            showResponseForm.value = true;
            return;
        }
    }

    // Post to accept endpoint which auto-normalizes status/timestamps and creates the transaction
    router.post(route("inquiries.accept", props.inquiry.id));
};

const deleteInquiry = () => {
    if (confirm("Are you sure you want to delete this inquiry?")) {
        router.delete(route("inquiries.destroy", props.inquiry.id));
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
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

const getTransactionStatusBadgeClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        active: "bg-blue-100 text-blue-800",
        completed: "bg-green-100 text-green-800",
        cancelled: "bg-red-100 text-red-800",
        on_hold: "bg-orange-100 text-orange-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};
</script>
