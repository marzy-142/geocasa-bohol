<template>
    <ModernDashboardLayout>
        <Head title="Transaction Details" />
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <nav class="flex" aria-label="Breadcrumb">
                                <ol class="flex items-center space-x-4">
                                    <li>
                                        <Link
                                            :href="
                                                route(
                                                    'client.transactions.dashboard'
                                                )
                                            "
                                            class="text-gray-400 hover:text-gray-500"
                                        >
                                            <svg
                                                class="flex-shrink-0 h-5 w-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"
                                                />
                                            </svg>
                                            <span class="sr-only">Home</span>
                                        </Link>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <svg
                                                class="flex-shrink-0 h-5 w-5 text-gray-300"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <Link
                                                :href="
                                                    route(
                                                        'client.transactions.dashboard'
                                                    )
                                                "
                                                class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                                            >
                                                Transactions
                                            </Link>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <svg
                                                class="flex-shrink-0 h-5 w-5 text-gray-300"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                            <span
                                                class="ml-4 text-sm font-medium text-gray-500"
                                            >
                                                {{ transaction.property.title }}
                                            </span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                            <h1 class="mt-2 text-3xl font-bold text-gray-900">
                                {{ transaction.property.title }}
                            </h1>
                            <p class="mt-2 text-gray-600">
                                Transaction #{{
                                    transaction.transaction_number
                                }}
                            </p>
                        </div>

                        <div class="flex items-center space-x-3">
                            <span
                                :class="getStatusBadgeClass(transaction.status)"
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                            >
                                {{ formatStatus(transaction.status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Transaction Progress -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-medium text-gray-900">
                                    Transaction Progress
                                </h2>
                            </div>
                            <div class="px-6 py-4">
                                <div
                                    class="flex items-center justify-between text-sm mb-2"
                                >
                                    <span class="text-gray-600"
                                        >Overall Progress</span
                                    >
                                    <span class="font-medium text-gray-900"
                                        >{{
                                            getProgressPercentage(
                                                transaction.status
                                            )
                                        }}%</span
                                    >
                                </div>
                                <div class="bg-gray-200 rounded-full h-3 mb-6">
                                    <div
                                        :style="{
                                            width:
                                                getProgressPercentage(
                                                    transaction.status
                                                ) + '%',
                                        }"
                                        class="bg-blue-600 h-3 rounded-full transition-all duration-500"
                                    ></div>
                                </div>

                                <!-- Progress Steps -->
                                <div class="space-y-4">
                                    <div
                                        v-for="(
                                            step, index
                                        ) in transactionSteps"
                                        :key="step.key"
                                        class="flex items-center"
                                    >
                                        <div class="flex-shrink-0">
                                            <div
                                                :class="
                                                    getStepStatusClass(
                                                        step.key,
                                                        index
                                                    )
                                                "
                                                class="w-8 h-8 rounded-full flex items-center justify-center"
                                            >
                                                <svg
                                                    v-if="
                                                        getStepStatus(
                                                            step.key,
                                                            index
                                                        ) === 'completed'
                                                    "
                                                    class="w-5 h-5 text-white"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                <span
                                                    v-else
                                                    class="text-sm font-medium"
                                                    >{{ index + 1 }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="ml-4 flex-1">
                                            <p
                                                :class="
                                                    getStepTextClass(
                                                        step.key,
                                                        index
                                                    )
                                                "
                                                class="text-sm font-medium"
                                            >
                                                {{ step.label }}
                                            </p>
                                            <p
                                                v-if="step.description"
                                                :class="
                                                    getStepTextClass(
                                                        step.key,
                                                        index
                                                    )
                                                "
                                                class="text-sm"
                                            >
                                                {{ step.description }}
                                            </p>
                                        </div>
                                        <div
                                            v-if="
                                                getStepStatus(
                                                    step.key,
                                                    index
                                                ) === 'completed'
                                            "
                                            class="flex-shrink-0 text-sm text-gray-500"
                                        >
                                            ✓ Complete
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Details -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-medium text-gray-900">
                                    Property Details
                                </h2>
                            </div>
                            <div class="px-6 py-4">
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <div>
                                        <h3
                                            class="text-lg font-semibold text-gray-900"
                                        >
                                            {{ transaction.property.title }}
                                        </h3>
                                        <p class="text-gray-600">
                                            {{ transaction.property.address }},
                                            {{
                                                transaction.property
                                                    .municipality
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-500 mt-2">
                                            {{
                                                transaction.property.description
                                            }}
                                        </p>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600"
                                                >Type:</span
                                            >
                                            <span class="font-medium">{{
                                                formatPropertyType(
                                                    transaction.property.type
                                                )
                                            }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600"
                                                >Listed Price:</span
                                            >
                                            <span class="font-medium"
                                                >₱{{
                                                    formatPrice(
                                                        transaction.property
                                                            .total_price
                                                    )
                                                }}</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600"
                                                >Your Offer:</span
                                            >
                                            <span
                                                class="font-medium text-blue-600"
                                                >₱{{
                                                    formatPrice(
                                                        transaction.offered_price
                                                    )
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Approvals -->
                        <div
                            v-if="pendingApprovals.length > 0"
                            class="bg-white shadow rounded-lg"
                        >
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-medium text-gray-900">
                                    Pending Approvals
                                </h2>
                            </div>
                            <div class="px-6 py-4">
                                <div class="space-y-4">
                                    <div
                                        v-for="approval in pendingApprovals"
                                        :key="approval.id"
                                        class="border border-yellow-200 rounded-lg p-4 bg-yellow-50"
                                    >
                                        <div
                                            class="flex items-start justify-between"
                                        >
                                            <div class="flex-1">
                                                <h3
                                                    class="text-sm font-medium text-yellow-800"
                                                >
                                                    {{
                                                        formatApprovalType(
                                                            approval.type
                                                        )
                                                    }}
                                                </h3>
                                                <p
                                                    class="mt-1 text-sm text-yellow-700"
                                                >
                                                    {{
                                                        approval.notes ||
                                                        "Please review and provide your approval."
                                                    }}
                                                </p>

                                                <!-- Approval Details -->
                                                <div
                                                    v-if="approval.data"
                                                    class="mt-3 space-y-2"
                                                >
                                                    <div
                                                        v-for="(
                                                            value, key
                                                        ) in approval.data"
                                                        :key="key"
                                                        class="text-sm"
                                                    >
                                                        <span
                                                            class="font-medium text-yellow-800"
                                                            >{{
                                                                formatFieldName(
                                                                    key
                                                                )
                                                            }}:</span
                                                        >
                                                        <span
                                                            class="ml-2 text-yellow-700"
                                                        >
                                                            {{
                                                                typeof value ===
                                                                "number"
                                                                    ? "₱" +
                                                                      formatPrice(
                                                                          value
                                                                      )
                                                                    : value
                                                            }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div
                                                    class="mt-3 flex items-center text-sm text-yellow-600"
                                                >
                                                    <svg
                                                        class="flex-shrink-0 mr-1.5 h-4 w-4"
                                                        fill="currentColor"
                                                        viewBox="0 0 20 20"
                                                    >
                                                        <path
                                                            fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                            clip-rule="evenodd"
                                                        />
                                                    </svg>
                                                    Deadline:
                                                    {{
                                                        formatDate(
                                                            approval.deadline
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Approval Actions -->
                                        <div class="mt-4 flex space-x-3">
                                            <button
                                                @click="
                                                    approveAction(approval.id)
                                                "
                                                :disabled="processingApproval"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                                            >
                                                <svg
                                                    v-if="!processingApproval"
                                                    class="w-4 h-4 mr-2"
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
                                                    class="animate-spin w-4 h-4 mr-2"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <circle
                                                        class="opacity-25"
                                                        cx="12"
                                                        cy="12"
                                                        r="10"
                                                        stroke="currentColor"
                                                        stroke-width="4"
                                                    ></circle>
                                                    <path
                                                        class="opacity-75"
                                                        fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                    ></path>
                                                </svg>
                                                {{
                                                    processingApproval
                                                        ? "Processing..."
                                                        : "Approve"
                                                }}
                                            </button>

                                            <button
                                                @click="
                                                    rejectAction(approval.id)
                                                "
                                                :disabled="processingApproval"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                            >
                                                <svg
                                                    v-if="!processingApproval"
                                                    class="w-4 h-4 mr-2"
                                                    fill="currentColor"
                                                    viewBox="0 0 20 20"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                                Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Timeline -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-medium text-gray-900">
                                    Transaction Timeline
                                </h2>
                            </div>
                            <div class="px-6 py-4">
                                <div class="flow-root">
                                    <ul class="-mb-8">
                                        <li
                                            v-for="(
                                                event, index
                                            ) in transactionTimeline"
                                            :key="event.id"
                                        >
                                            <div class="relative pb-8">
                                                <span
                                                    v-if="
                                                        index !==
                                                        transactionTimeline.length -
                                                            1
                                                    "
                                                    class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                                    aria-hidden="true"
                                                ></span>
                                                <div
                                                    class="relative flex space-x-3"
                                                >
                                                    <div>
                                                        <span
                                                            :class="
                                                                getTimelineIconClass(
                                                                    event.type
                                                                )
                                                            "
                                                            class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                                        >
                                                            <svg
                                                                class="h-5 w-5 text-white"
                                                                fill="currentColor"
                                                                viewBox="0 0 20 20"
                                                            >
                                                                <path
                                                                    v-if="
                                                                        event.type ===
                                                                        'created'
                                                                    "
                                                                    fill-rule="evenodd"
                                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                                    clip-rule="evenodd"
                                                                />
                                                                <path
                                                                    v-else-if="
                                                                        event.type ===
                                                                        'status_change'
                                                                    "
                                                                    fill-rule="evenodd"
                                                                    d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"
                                                                    clip-rule="evenodd"
                                                                />
                                                                <path
                                                                    v-else
                                                                    fill-rule="evenodd"
                                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
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
                                                                    event.description
                                                                }}
                                                            </p>
                                                        </div>
                                                        <div
                                                            class="text-right text-sm whitespace-nowrap text-gray-500"
                                                        >
                                                            <time>{{
                                                                formatDate(
                                                                    event.created_at
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
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-8">
                        <!-- Broker Information -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-medium text-gray-900">
                                    Your Broker
                                </h2>
                            </div>
                            <div class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <UserAvatar
                                        v-if="transaction.broker"
                                        :user="transaction.broker"
                                        size="md"
                                        bg-color="blue"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="text-sm font-medium text-gray-900 truncate"
                                        >
                                            {{ transaction.broker.name }}
                                        </p>
                                        <p
                                            class="text-sm text-gray-500 truncate"
                                        >
                                            {{ transaction.broker.email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4 flex space-x-3">
                                    <button
                                        class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
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
                                                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                            />
                                        </svg>
                                        Message
                                    </button>
                                    <button
                                        class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
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
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                            />
                                        </svg>
                                        Call
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Engagement Metrics -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-medium text-gray-900">
                                    Your Engagement
                                </h2>
                            </div>
                            <div class="px-6 py-4 space-y-4">
                                <div>
                                    <div
                                        class="flex items-center justify-between text-sm"
                                    >
                                        <span class="text-gray-600"
                                            >Engagement Score</span
                                        >
                                        <span class="font-medium text-gray-900"
                                            >{{
                                                transaction.client_engagement_score ||
                                                0
                                            }}/100</span
                                        >
                                    </div>
                                    <div
                                        class="mt-1 bg-gray-200 rounded-full h-2"
                                    >
                                        <div
                                            :style="{
                                                width:
                                                    (transaction.client_engagement_score ||
                                                        0) + '%',
                                            }"
                                            class="bg-blue-600 h-2 rounded-full"
                                        ></div>
                                    </div>
                                </div>

                                <div
                                    v-if="transaction.client_last_viewed"
                                    class="text-sm"
                                >
                                    <span class="text-gray-600"
                                        >Last Viewed:</span
                                    >
                                    <span class="ml-2 font-medium">{{
                                        formatDate(
                                            transaction.client_last_viewed
                                        )
                                    }}</span>
                                </div>

                                <div
                                    v-if="
                                        transaction.client_satisfaction &&
                                        transaction.client_satisfaction !==
                                            'pending'
                                    "
                                    class="text-sm"
                                >
                                    <span class="text-gray-600"
                                        >Satisfaction:</span
                                    >
                                    <span class="ml-2 font-medium capitalize">{{
                                        transaction.client_satisfaction
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Client Notes -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-medium text-gray-900">
                                    Your Notes
                                </h2>
                            </div>
                            <div class="px-6 py-4">
                                <textarea
                                    v-model="clientNotes"
                                    @blur="saveClientNotes"
                                    placeholder="Add your personal notes about this transaction..."
                                    class="w-full h-32 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                ></textarea>
                                <p class="mt-2 text-xs text-gray-500">
                                    These notes are private and only visible to
                                    you.
                                </p>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-medium text-gray-900">
                                    Quick Actions
                                </h2>
                            </div>
                            <div class="px-6 py-4 space-y-3">
                                <button
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
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
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                        />
                                    </svg>
                                    Upload Documents
                                </button>

                                <button
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
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
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                    Schedule Meeting
                                </button>

                                <button
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
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
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                        />
                                    </svg>
                                    Provide Feedback
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const props = defineProps({
    transaction: Object,
});

const processingApproval = ref(false);
const clientNotes = ref(props.transaction.client_notes || "");

const pendingApprovals = computed(() => {
    return (props.transaction.client_approvals || []).filter(
        (approval) => approval.status === "pending"
    );
});

const transactionSteps = [
    {
        key: "inquiry",
        label: "Initial Inquiry",
        description: "You expressed interest in this property",
    },
    {
        key: "initial_contact",
        label: "First Contact",
        description: "Your broker made initial contact",
    },
    {
        key: "property_viewing",
        label: "Property Viewing",
        description: "Property viewing scheduled or completed",
    },
    {
        key: "offer_made",
        label: "Offer Submitted",
        description: "Your offer has been submitted",
    },
    {
        key: "negotiation",
        label: "Negotiation",
        description: "Price and terms negotiation",
    },
    {
        key: "offer_accepted",
        label: "Offer Accepted",
        description: "Your offer has been accepted",
    },
    {
        key: "contract_signed",
        label: "Contract Signed",
        description: "Purchase agreement executed",
    },
    {
        key: "due_diligence",
        label: "Due Diligence",
        description: "Property inspection and verification",
    },
    {
        key: "financing",
        label: "Financing",
        description: "Loan processing and approval",
    },
    {
        key: "closing_preparation",
        label: "Closing Prep",
        description: "Final documents and closing preparation",
    },
    {
        key: "finalized",
        label: "Transaction Complete",
        description: "Property ownership transferred",
    },
];

const transactionTimeline = computed(() => {
    const timeline = [
        {
            id: "created",
            type: "created",
            description: "Transaction created",
            created_at: props.transaction.created_at,
        },
        {
            id: "inquiry_date",
            type: "status_change",
            description: "Initial inquiry made",
            created_at: props.transaction.inquiry_date,
        },
        {
            id: "first_contact",
            type: "status_change",
            description: "First contact with broker",
            created_at: props.transaction.first_contact_date,
        },
    ];

    if (props.transaction.offer_date) {
        timeline.push({
            id: "offer_date",
            type: "status_change",
            description: "Offer submitted",
            created_at: props.transaction.offer_date,
        });
    }

    return timeline
        .filter((event) => event.created_at)
        .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
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

const formatPropertyType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatApprovalType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatFieldName = (field) => {
    return field.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const getInitials = (name) => {
    return name
        .split(" ")
        .map((n) => n[0])
        .join("")
        .toUpperCase();
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

const getStepStatus = (stepKey, index) => {
    const currentStepIndex = transactionSteps.findIndex(
        (step) => step.key === props.transaction.status
    );
    if (index < currentStepIndex) return "completed";
    if (index === currentStepIndex) return "current";
    return "upcoming";
};

const getStepStatusClass = (stepKey, index) => {
    const status = getStepStatus(stepKey, index);
    if (status === "completed") return "bg-green-500 text-white";
    if (status === "current") return "bg-blue-500 text-white";
    return "bg-gray-400 text-white";
};

const getStepTextClass = (stepKey, index) => {
    const status = getStepStatus(stepKey, index);
    if (status === "completed") return "text-gray-900";
    if (status === "current") return "text-blue-600";
    return "text-gray-500";
};

const getTimelineIconClass = (type) => {
    const iconClasses = {
        created: "bg-green-500",
        status_change: "bg-blue-500",
        default: "bg-gray-500",
    };
    return iconClasses[type] || iconClasses.default;
};

const approveAction = async (approvalId) => {
    processingApproval.value = true;
    try {
        // This would make an API call to process the approval
        await router.post(
            route("client.transactions.approve", props.transaction.id),
            {
                approval_id: approvalId,
                approved: true,
            }
        );
    } catch (error) {
        console.error("Error processing approval:", error);
    } finally {
        processingApproval.value = false;
    }
};

const rejectAction = async (approvalId) => {
    processingApproval.value = true;
    try {
        // This would make an API call to process the rejection
        await router.post(
            route("client.transactions.approve", props.transaction.id),
            {
                approval_id: approvalId,
                approved: false,
            }
        );
    } catch (error) {
        console.error("Error processing rejection:", error);
    } finally {
        processingApproval.value = false;
    }
};

const saveClientNotes = async () => {
    try {
        await router.patch(
            route("client.transactions.update", props.transaction.id),
            {
                client_notes: clientNotes.value,
            }
        );
    } catch (error) {
        console.error("Error saving notes:", error);
    }
};

onMounted(() => {
    console.log("Transaction details view mounted");
});
</script>
