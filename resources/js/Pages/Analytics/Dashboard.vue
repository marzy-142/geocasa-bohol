<template>
    <ModernDashboardLayout>
        <Head title="Analytics Dashboard" />
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Analytics Dashboard
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Performance insights and analytics for
                        {{ getUserRoleTitle() }}
                    </p>
                </div>

                <!-- Period Selector -->
                <div class="mb-8">
                    <div class="flex items-center space-x-4">
                        <label
                            for="period"
                            class="text-sm font-medium text-gray-700"
                            >Period:</label
                        >
                        <select
                            id="period"
                            v-model="selectedPeriod"
                            @change="updatePeriod"
                            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="7">Last 7 days</option>
                            <option value="30">Last 30 days</option>
                            <option value="90">Last 90 days</option>
                            <option value="365">Last year</option>
                        </select>
                    </div>
                </div>

                <!-- Error Message -->
                <div
                    v-if="error"
                    class="mb-6 bg-red-50 border border-red-200 rounded-md p-4"
                >
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-5 w-5 text-red-400"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-800">{{ error }}</p>
                        </div>
                    </div>
                </div>

                <!-- Admin Analytics -->
                <div v-if="user.role === 'admin'" class="space-y-8">
                    <!-- System Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-blue-600"
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
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt
                                                class="text-sm font-medium text-gray-500 truncate"
                                            >
                                                Total Transactions
                                            </dt>
                                            <dd
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    analytics.overall_metrics
                                                        ?.total_transactions ||
                                                    0
                                                }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-green-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt
                                                class="text-sm font-medium text-gray-500 truncate"
                                            >
                                                Total Clients
                                            </dt>
                                            <dd
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    analytics.overall_metrics
                                                        ?.total_clients || 0
                                                }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-purple-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0H8m8 0v2a2 2 0 01-2 2H10a2 2 0 01-2-2V6m8 0H8"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt
                                                class="text-sm font-medium text-gray-500 truncate"
                                            >
                                                Active Brokers
                                            </dt>
                                            <dd
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    analytics.broker_analytics
                                                        ?.active_brokers || 0
                                                }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-yellow-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt
                                                class="text-sm font-medium text-gray-500 truncate"
                                            >
                                                Completion Rate
                                            </dt>
                                            <dd
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    analytics
                                                        .transaction_analytics
                                                        ?.completion_rate || 0
                                                }}%
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Trends (hidden until time-series available) -->
                    <div
                        v-if="hasSystemTrendSeries"
                        class="bg-white shadow rounded-lg"
                    >
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Performance Trends
                            </h3>
                        </div>
                        <div class="px-6 py-6">
                            <AnalyticsChart
                                type="line"
                                :data="systemTrendsChartData"
                                :options="systemTrendsChartOptions"
                            />
                        </div>
                    </div>

                    <!-- Pipeline Distribution (by status) -->
                    <div
                        v-if="analytics?.pipeline?.by_status"
                        class="bg-white shadow rounded-lg"
                    >
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Transaction Pipeline
                            </h3>
                        </div>
                        <div class="px-6 py-4">
                            <div
                                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4"
                            >
                                <div
                                    v-for="(count, status) in analytics.pipeline
                                        .by_status"
                                    :key="status"
                                    class="bg-gray-50 rounded-lg p-4"
                                >
                                    <div
                                        class="text-xs uppercase tracking-wide text-gray-500 mb-1"
                                    >
                                        {{ formatStatus(status) }}
                                    </div>
                                    <div
                                        class="text-xl font-semibold text-gray-900"
                                    >
                                        {{ count }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Broker Analytics -->
                <div v-else-if="user.role === 'broker'" class="space-y-8">
                    <!-- Broker Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-blue-600"
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
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt
                                                class="text-sm font-medium text-gray-500 truncate"
                                            >
                                                Total Transactions
                                            </dt>
                                            <dd
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    analytics
                                                        .transaction_metrics
                                                        ?.total_transactions ||
                                                    0
                                                }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-green-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt
                                                class="text-sm font-medium text-gray-500 truncate"
                                            >
                                                Success Rate
                                            </dt>
                                            <dd
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    analytics
                                                        .transaction_metrics
                                                        ?.success_rate || 0
                                                }}%
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-purple-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt
                                                class="text-sm font-medium text-gray-500 truncate"
                                            >
                                                Active Clients
                                            </dt>
                                            <dd
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    analytics
                                                        .client_management_metrics
                                                        ?.active_clients || 0
                                                }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-yellow-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt
                                                class="text-sm font-medium text-gray-500 truncate"
                                            >
                                                Total Sales Value
                                            </dt>
                                            <dd
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                ₱{{
                                                    formatNumber(
                                                        analytics
                                                            .transaction_metrics
                                                            ?.total_sales_value ||
                                                            0
                                                    )
                                                }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Efficiency Metrics -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Efficiency Metrics
                            </h3>
                        </div>
                        <div class="px-6 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        Transactions per Week
                                    </h4>
                                    <p
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        {{
                                            analytics.efficiency_metrics
                                                ?.transactions_per_week || 0
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        Average Deal Size
                                    </h4>
                                    <p
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        ₱{{
                                            formatNumber(
                                                analytics.efficiency_metrics
                                                    ?.average_deal_size || 0
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        Conversion Rate
                                    </h4>
                                    <p
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        {{
                                            analytics.efficiency_metrics
                                                ?.conversion_rate || 0
                                        }}%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client Analytics -->
                <div v-else class="space-y-8">
                    <!-- Client Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-blue-600"
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
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt
                                                class="text-sm font-medium text-gray-500 truncate"
                                            >
                                                Total Transactions
                                            </dt>
                                            <dd
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    analytics
                                                        .transaction_metrics
                                                        ?.total_transactions ||
                                                    0
                                                }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-green-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt
                                                class="text-sm font-medium text-gray-500 truncate"
                                            >
                                                Active Transactions
                                            </dt>
                                            <dd
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    analytics
                                                        .transaction_metrics
                                                        ?.active_transactions ||
                                                    0
                                                }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-purple-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt
                                                class="text-sm font-medium text-gray-500 truncate"
                                            >
                                                Engagement Level
                                            </dt>
                                            <dd
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    analytics.engagement_metrics
                                                        ?.engagement_level ||
                                                    "N/A"
                                                }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg
                                            class="h-6 w-6 text-yellow-600"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt
                                                class="text-sm font-medium text-gray-500 truncate"
                                            >
                                                Satisfaction Score
                                            </dt>
                                            <dd
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    analytics
                                                        .satisfaction_metrics
                                                        ?.satisfaction_score ||
                                                    "N/A"
                                                }}%
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Engagement Metrics -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">
                                Engagement Metrics
                            </h3>
                        </div>
                        <div class="px-6 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        Total Interactions
                                    </h4>
                                    <p
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        {{
                                            analytics.engagement_metrics
                                                ?.total_interactions || 0
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        Average Response Time
                                    </h4>
                                    <p
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        {{
                                            analytics.engagement_metrics
                                                ?.average_response_time_hours ||
                                            0
                                        }}h
                                    </p>
                                </div>
                                <div>
                                    <h4
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        Active Days
                                    </h4>
                                    <p
                                        class="mt-1 text-2xl font-semibold text-gray-900"
                                    >
                                        {{
                                            analytics.engagement_metrics
                                                ?.active_days || 0
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommendations -->
                <div
                    v-if="
                        analytics.recommendations &&
                        analytics.recommendations.length > 0
                    "
                    class="bg-white shadow rounded-lg"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Recommendations
                        </h3>
                    </div>
                    <div class="px-6 py-4">
                        <ul class="space-y-2">
                            <li
                                v-for="recommendation in analytics.recommendations"
                                :key="recommendation"
                                class="flex items-start"
                            >
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-5 w-5 text-blue-400"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-700">
                                        {{ recommendation }}
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { Head, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import AnalyticsChart from "@/Components/AnalyticsChart.vue";

const props = defineProps({
    user: Object,
    analytics: Object,
    period: Number,
    error: String,
});

const selectedPeriod = ref(props.period || 30);

const updatePeriod = () => {
    router.get(
        route("analytics.dashboard"),
        { days: selectedPeriod.value },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const getUserRoleTitle = () => {
    return props.user.role === "admin"
        ? "System"
        : props.user.role === "broker"
        ? "Your Broker Performance"
        : "Your Transaction Activity";
};

const formatNumber = (number) => {
    return new Intl.NumberFormat("en-PH").format(number);
};

onMounted(() => {
    console.log("Analytics Dashboard mounted");
});

// Show admin system trends only if backend provides a real time-series structure
const hasSystemTrendSeries = computed(() => {
    const trends = props.analytics?.performance_trends;
    // Expect an array series like [{label, data:[]}, ...] or similar
    return Array.isArray(trends?.series) && trends.series.length > 0;
});

// Build chart config from backend series
const systemTrendsChartData = computed(() => {
    const trends = props.analytics?.performance_trends || {};
    const labels = trends.labels || [];
    const series = trends.series || [];
    const palette = [
        "rgb(59, 130, 246)",
        "rgb(16, 185, 129)",
        "rgb(245, 158, 11)",
        "rgb(99, 102, 241)",
    ];
    const datasets = series.map((s, idx) => ({
        label: s.label,
        data: s.data || [],
        borderColor: palette[idx % palette.length],
        backgroundColor: palette[idx % palette.length]
            .replace("rgb", "rgba")
            .replace(")", ", 0.15)"),
        tension: 0.35,
        fill: s.type === "line",
        type: s.type || "line",
        yAxisID: s.label.includes("Sales Value") ? "y1" : "y",
    }));
    return { labels, datasets };
});

const systemTrendsChartOptions = {
    responsive: true,
    interaction: { intersect: false, mode: "index" },
    stacked: false,
    scales: {
        y: { beginAtZero: true, title: { display: true, text: "Count" } },
        y1: {
            beginAtZero: true,
            position: "right",
            grid: { drawOnChartArea: false },
            title: { display: true, text: "Sales (PHP)" },
        },
    },
    plugins: { legend: { position: "top" } },
};

const formatStatus = (status) => {
    return (status || "")
        .replace(/_/g, " ")
        .replace(/\b\w/g, (l) => l.toUpperCase());
};
</script>
