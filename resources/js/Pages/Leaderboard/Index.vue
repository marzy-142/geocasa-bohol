<template>
    <Head title="Broker Leaderboard" />

    <div
        class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50"
    >
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            🏆 Broker Leaderboard
                        </h1>
                        <p class="text-gray-600 mt-1">
                            Top performing real estate brokers in Bohol
                        </p>
                    </div>

                    <!-- Period Filter -->
                    <div class="flex items-center space-x-4">
                        <select
                            v-model="selectedPeriod"
                            @change="updatePeriod"
                            class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="all-time">All Time</option>
                            <option value="this-year">This Year</option>
                            <option value="this-month">This Month</option>
                            <option value="last-30-days">Last 30 Days</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Platform Stats -->
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
            >
                <div class="bg-white rounded-xl shadow-sm p-6 border">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100">
                            <CurrencyDollarIcon
                                class="h-6 w-6 text-green-600"
                            />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">
                                Total Sales
                            </p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ platformStats.total_sales }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100">
                            <BuildingOfficeIcon class="h-6 w-6 text-blue-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">
                                Properties Sold
                            </p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ platformStats.properties_sold }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100">
                            <UserGroupIcon class="h-6 w-6 text-purple-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">
                                Active Brokers
                            </p>
                            <p class="text-2xl font-semibold text-gray-900">
                                {{ platformStats.active_brokers }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 border">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100">
                            <ChartBarIcon class="h-6 w-6 text-yellow-600" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">
                                Total Commission
                            </p>
                            <p class="text-2xl font-semibold text-gray-900">
                                ₱{{
                                    formatNumber(platformStats.total_commission)
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard -->
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Top Brokers
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Rank
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Broker
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Sales
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Commission
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Success Rate
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Active Listings
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="(broker, index) in brokers"
                                :key="broker.id"
                                :class="
                                    index < 3
                                        ? 'bg-gradient-to-r from-yellow-50 to-yellow-100'
                                        : ''
                                "
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span
                                            v-if="index === 0"
                                            class="text-2xl"
                                            >🥇</span
                                        >
                                        <span
                                            v-else-if="index === 1"
                                            class="text-2xl"
                                            >🥈</span
                                        >
                                        <span
                                            v-else-if="index === 2"
                                            class="text-2xl"
                                            >🥉</span
                                        >
                                        <span
                                            v-else
                                            class="text-lg font-semibold text-gray-600"
                                            >{{ index + 1 }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div
                                                class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center"
                                            >
                                                <span
                                                    class="text-white font-semibold"
                                                    >{{
                                                        broker.name.charAt(0)
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ broker.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ broker.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ broker.total_sales }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        ₱{{
                                            formatNumber(
                                                broker.total_sales_value
                                            )
                                        }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm font-medium text-green-600"
                                    >
                                        ₱{{
                                            formatNumber(
                                                broker.total_commission
                                            )
                                        }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ broker.success_rate }}%
                                        </div>
                                        <div
                                            class="ml-2 w-16 bg-gray-200 rounded-full h-2"
                                        >
                                            <div
                                                class="bg-green-500 h-2 rounded-full"
                                                :style="{
                                                    width:
                                                        broker.success_rate +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{ broker.active_listings }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'leaderboard.broker',
                                                broker.id
                                            )
                                        "
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        View Details
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    CurrencyDollarIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
    ChartBarIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    brokers: Array,
    platformStats: Object,
    period: String,
});

const selectedPeriod = ref(props.period);

const updatePeriod = () => {
    router.get(
        route("leaderboard.index"),
        { period: selectedPeriod.value },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const formatNumber = (number) => {
    return new Intl.NumberFormat("en-PH").format(number || 0);
};
</script>
