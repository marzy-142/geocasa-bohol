<template>
    <ModernDashboardLayout>
        <!-- Clean Header -->
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

            <div
                class="flex flex-col sm:flex-row sm:items-start justify-between gap-4"
            >
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        {{ inquiry.name }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ formatDate(inquiry.created_at) }}
                    </p>
                </div>
                <!-- Show transaction status if exists, otherwise show inquiry status -->
                <div class="flex flex-col items-end gap-2">
                    <span
                        v-if="inquiry.transaction"
                        :class="
                            getTransactionStatusBadgeClass(
                                inquiry.transaction.status
                            )
                        "
                        class="px-3 py-1.5 text-xs font-medium rounded-lg"
                    >
                        {{
                            formatTransactionStatus(inquiry.transaction.status)
                        }}
                    </span>
                    <span
                        v-else
                        :class="getStatusBadgeClass(inquiry.status)"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg"
                    >
                        {{
                            inquiry.status.charAt(0).toUpperCase() +
                            inquiry.status.slice(1)
                        }}
                    </span>
                    <span
                        v-if="inquiry.transaction"
                        class="text-xs text-gray-500"
                    >
                        {{ inquiry.transaction.transaction_number }}
                    </span>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto space-y-5">
            <!-- 🆕 Auto-Created Transaction Banner -->
            <div
                v-if="inquiry.transaction"
                class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-xl shadow-sm p-6 text-white"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex-shrink-0 w-12 h-12 bg-white/20 rounded-full flex items-center justify-center"
                    >
                        <svg
                            class="w-7 h-7"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3
                            class="text-lg font-semibold mb-1 flex items-center gap-2"
                        >
                            🎉 Transaction Created Automatically!
                        </h3>
                        <p class="text-green-100 text-sm mb-3">
                            Transaction #{{
                                inquiry.transaction.transaction_number
                            }}
                            has been created and is ready for the offer stage.
                            All inquiry data has been transferred automatically.
                        </p>
                        <Link
                            :href="
                                route(
                                    'transactions.show',
                                    inquiry.transaction.id
                                )
                            "
                            class="inline-flex items-center px-4 py-2 bg-white text-green-600 font-medium rounded-lg hover:bg-green-50 transition-colors text-sm"
                        >
                            <svg
                                class="w-4 h-4 mr-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"
                                ></path>
                            </svg>
                            View Transaction Details
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Primary Action: Conversation -->
            <div
                v-if="inquiry.conversation"
                class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-sm p-6 text-white"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h3
                            class="text-lg font-semibold mb-1 flex items-center gap-2"
                        >
                            💬 Message {{ inquiry.name }}
                        </h3>
                        <p class="text-blue-100 text-sm">
                            Have a conversation about this property inquiry
                        </p>
                    </div>
                    <Link
                        :href="
                            route('conversations.show', inquiry.conversation.id)
                        "
                        class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-colors flex items-center gap-2 shadow-lg"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                            ></path>
                        </svg>
                        Start Conversation
                    </Link>
                </div>
            </div>

            <!-- Warning if property unavailable -->
            <div
                v-if="propertyHasAssignedClient"
                class="bg-amber-50 border border-amber-200 rounded-lg p-4"
            >
                <div class="flex items-start gap-3">
                    <svg
                        class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        ></path>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-amber-800">
                            Property Not Available
                        </p>
                        <p class="text-sm text-amber-700 mt-1">
                            {{ propertyUnavailableReason }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- 🆕 Deal Progress & Timeline Components -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <DealProgressCard
                    :inquiry="inquiry"
                    :transaction="inquiry.transaction"
                />
                <UnifiedTimeline
                    :inquiry="inquiry"
                    :transaction="inquiry.transaction"
                />
            </div>

            <!-- Main Content - Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Left: Message & Property -->
                <div class="lg:col-span-2 space-y-5">
                    <!-- Inquiry Message -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-semibold text-gray-900">
                                Inquiry Message
                            </h3>
                            <span
                                :class="getTypeBadgeClass(inquiry.inquiry_type)"
                                class="px-2.5 py-1 text-xs font-medium rounded-lg"
                            >
                                {{
                                    inquiry.inquiry_type
                                        .charAt(0)
                                        .toUpperCase() +
                                    inquiry.inquiry_type.slice(1)
                                }}
                            </span>
                        </div>
                        <p
                            class="text-gray-700 leading-relaxed whitespace-pre-wrap"
                        >
                            {{ inquiry.message }}
                        </p>
                    </div>

                    <!-- Property Info -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                    >
                        <h3 class="text-base font-semibold text-gray-900 mb-4">
                            Property
                        </h3>
                        <div class="space-y-3">
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
                                View full property details
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

                    <!-- Your Response (if exists) -->
                    <div
                        v-if="inquiry.broker_response"
                        class="bg-blue-50 rounded-xl border border-blue-100 p-6"
                    >
                        <h3 class="text-base font-semibold text-gray-900 mb-3">
                            Your Response
                        </h3>
                        <p class="text-gray-700 leading-relaxed">
                            {{ inquiry.broker_response }}
                        </p>
                        <div
                            v-if="inquiry.broker_notes"
                            class="mt-4 pt-4 border-t border-blue-200"
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

                <!-- Right: Contact & Actions -->
                <div class="space-y-5">
                    <!-- Contact Info -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                    >
                        <h3 class="text-base font-semibold text-gray-900 mb-4">
                            Contact
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <svg
                                    class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5"
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
                            <div class="flex items-start gap-3">
                                <svg
                                    class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5"
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
                                    class="text-sm text-blue-600 hover:text-blue-700 break-all"
                                >
                                    {{ inquiry.email }}
                                </a>
                            </div>
                            <div
                                v-if="inquiry.phone"
                                class="flex items-start gap-3"
                            >
                                <svg
                                    class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5"
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
                                    >{{ inquiry.phone }}</a
                                >
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

                    <!-- Quick Actions -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                    >
                        <h3 class="text-base font-semibold text-gray-900 mb-4">
                            Quick Actions
                        </h3>
                        <div class="space-y-3">
                            <!-- Status Update Dropdown -->
                            <div v-if="can.respond && !inquiry.transaction">
                                <label
                                    class="block text-xs font-medium text-gray-600 mb-1.5"
                                >
                                    Update Status
                                </label>
                                <select
                                    v-model="quickStatusForm.status"
                                    @change="updateQuickStatus"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                >
                                    <option
                                        value="new"
                                        :selected="inquiry.status === 'new'"
                                    >
                                        📨 New
                                    </option>
                                    <option
                                        value="contacted"
                                        :selected="
                                            inquiry.status === 'contacted'
                                        "
                                    >
                                        📞 Contacted
                                    </option>
                                    <option
                                        value="scheduled"
                                        :selected="
                                            inquiry.status === 'scheduled'
                                        "
                                    >
                                        📅 Viewing Scheduled
                                    </option>
                                    <option
                                        value="completed"
                                        :selected="
                                            inquiry.status === 'completed'
                                        "
                                    >
                                        ✅ Completed
                                    </option>
                                    <option
                                        value="closed"
                                        :selected="inquiry.status === 'closed'"
                                    >
                                        🚫 Closed
                                    </option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    💡 Use chat to communicate, update status as
                                    you progress
                                </p>
                            </div>

                            <!-- Mark as Won Button -->
                            <button
                                v-if="
                                    can.respond &&
                                    inquiry.status !== 'completed' &&
                                    !inquiry.transaction
                                "
                                @click="showWonDialog = true"
                                class="w-full px-4 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors flex items-center justify-center gap-2"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Mark as Won
                            </button>

                            <Link
                                v-if="
                                    !inquiry.transaction &&
                                    !propertyHasAssignedClient
                                "
                                :href="
                                    route('transactions.create', {
                                        inquiry_id: inquiry.id,
                                    })
                                "
                                class="block w-full px-4 py-2.5 text-sm font-medium text-center text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                Start Transaction
                            </Link>
                            <button
                                v-if="can.delete"
                                @click="deleteInquiry"
                                class="w-full px-4 py-2.5 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                            >
                                Delete Inquiry
                            </button>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                    >
                        <h3 class="text-base font-semibold text-gray-900 mb-4">
                            Timeline
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 flex-shrink-0"
                                ></div>
                                <div>
                                    <p class="text-sm text-gray-900">
                                        Received
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ formatDate(inquiry.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="inquiry.contacted_at"
                                class="flex items-start gap-3"
                            >
                                <div
                                    class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 flex-shrink-0"
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
                                class="flex items-start gap-3"
                            >
                                <div
                                    class="w-2 h-2 rounded-full bg-green-500 mt-1.5 flex-shrink-0"
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
                                class="flex items-start gap-3"
                            >
                                <div
                                    class="w-2 h-2 rounded-full bg-purple-500 mt-1.5 flex-shrink-0"
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

        <!-- Mark as Won Dialog -->
        <div
            v-if="showWonDialog"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="showWonDialog = false"
        >
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center"
                    >
                        <svg
                            class="w-6 h-6 text-green-600"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Mark Inquiry as Won?
                        </h3>
                        <p class="text-sm text-gray-600">
                            This will create a transaction
                        </p>
                    </div>
                </div>

                <div
                    class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6"
                >
                    <p class="text-sm text-blue-900">
                        <strong>What happens next:</strong>
                    </p>
                    <ul class="mt-2 space-y-1 text-sm text-blue-800">
                        <li>✓ Transaction will be created automatically</li>
                        <li>✓ All inquiry data will be transferred</li>
                        <li>✓ Progress moves to Offer stage (57%)</li>
                        <li>✓ You can manage the deal in Transactions</li>
                    </ul>
                </div>

                <div class="flex gap-3">
                    <button
                        type="button"
                        @click="showWonDialog = false"
                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="markAsWon"
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors"
                    >
                        Yes, Mark as Won
                    </button>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import UnifiedTimeline from "@/Components/UnifiedTimeline.vue";
import DealProgressCard from "@/Components/DealProgressCard.vue";
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    inquiry: Object,
    can: Object,
});

const showResponseForm = ref(false);
const showWonDialog = ref(false);

// Quick status update form (no message required)
const quickStatusForm = useForm({
    status: props.inquiry.status,
});

const updateQuickStatus = () => {
    if (quickStatusForm.status === props.inquiry.status) return;

    quickStatusForm.put(route("inquiries.update-status", props.inquiry.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Status updated successfully
        },
    });
};

// Mark as Won function
const markAsWon = () => {
    router.put(
        route("inquiries.mark-as-won", props.inquiry.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showWonDialog.value = false;
            },
        }
    );
};

// Computed property to check if property has an assigned client
const propertyHasAssignedClient = computed(() => {
    if (!props.inquiry.property) return false;

    const unavailableStatuses = [
        "reserved",
        "under_negotiation",
        "sold",
        "pending",
    ];
    return unavailableStatuses.includes(props.inquiry.property.status);
});

// Computed property to get the reason why property is unavailable
const propertyUnavailableReason = computed(() => {
    if (!propertyHasAssignedClient.value) return "";

    const status = props.inquiry.property.status;
    const messages = {
        reserved: "This property is reserved and cannot accept new inquiries.",
        under_negotiation:
            "This property is under negotiation with a client and cannot accept new inquiries.",
        sold: "This property has been sold and cannot accept new inquiries.",
        pending:
            "This property has an accepted offer and cannot accept new inquiries.",
    };

    return (
        messages[status] || "This property is not available for new inquiries."
    );
});

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
        offer_made: "bg-blue-100 text-blue-800",
        negotiation: "bg-purple-100 text-purple-800",
        accepted: "bg-green-100 text-green-800",
        contract_signed: "bg-indigo-100 text-indigo-800",
        finalized: "bg-emerald-100 text-emerald-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const formatTransactionStatus = (status) => {
    const labels = {
        pending: "Pending",
        offer_made: "Offer Made",
        negotiation: "In Negotiation",
        accepted: "Offer Accepted",
        contract_signed: "Contract Signed",
        finalized: "Deal Finalized",
        cancelled: "Cancelled",
    };
    return labels[status] || status.charAt(0).toUpperCase() + status.slice(1);
};
</script>
