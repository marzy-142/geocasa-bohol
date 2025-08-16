<template>
    <Head title="Top Broker - GeoCasa Bohol" />

    <!-- Public Navigation -->
    <PublicNavigation
        :auth="$page.props.auth"
        currentRoute="leaderboard.index"
    />

    <div class="min-h-screen bg-white">
        <!-- Hero Section with Minimal Design -->
        <div class="bg-white border-b border-neutral-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="text-center">
                    <div class="mb-8">
                        <div
                            class="inline-flex items-center justify-center w-20 h-20 bg-neutral-900 rounded-full mb-6"
                        >
                            <svg
                                class="w-10 h-10 text-white"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M12 2L15.09 8.26L22 9L17 14L18.18 21L12 17.77L5.82 21L7 14L2 9L8.91 8.26L12 2Z"
                                />
                            </svg>
                        </div>
                    </div>
                    <h1
                        class="text-6xl font-light text-neutral-900 mb-4 tracking-tight"
                    >
                        Excellence
                    </h1>
                    <p class="text-xl text-neutral-500 mb-12 font-light">
                        Celebrating outstanding performance in Bohol real estate
                    </p>

                    <!-- Period Filter - Minimal Design -->
                    <div
                        class="inline-flex items-center bg-neutral-50 rounded-full p-1 border border-neutral-200"
                    >
                        <select
                            v-model="selectedPeriod"
                            @change="updatePeriod"
                            class="border-0 bg-transparent focus:ring-0 text-sm text-neutral-700 cursor-pointer px-4 py-2 rounded-full"
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

        <!-- Main Content - Redesigned Layout -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <!-- Top Broker Display -->
            <div v-if="topBroker" class="space-y-16">
                <!-- Broker Profile - Centered and Minimal -->
                <div class="text-center">
                    <div class="inline-block">
                        <div
                            class="w-32 h-32 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-8 border-4 border-white shadow-lg"
                        >
                            <span class="text-neutral-600 font-light text-4xl">
                                {{ topBroker.name.charAt(0) }}
                            </span>
                        </div>
                        <h2
                            class="text-4xl font-light text-neutral-900 mb-2 tracking-tight"
                        >
                            {{ topBroker.name }}
                        </h2>
                        <p class="text-neutral-500 text-lg mb-6">
                            {{ topBroker.email }}
                        </p>
                        <div
                            class="inline-flex items-center px-6 py-2 bg-neutral-900 text-white rounded-full text-sm font-medium"
                        >
                            Leading Broker
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics - Horizontal Layout -->
                <div
                    class="grid grid-cols-1 md:grid-cols-3 gap-16 max-w-4xl mx-auto"
                >
                    <!-- Total Sales -->
                    <div class="text-center">
                        <div class="mb-4">
                            <div
                                class="text-5xl font-light text-neutral-900 mb-2"
                            >
                                {{ topBroker.total_sales }}
                            </div>
                            <div
                                class="w-12 h-px bg-neutral-300 mx-auto mb-4"
                            ></div>
                            <div
                                class="text-sm uppercase tracking-widest text-neutral-500 font-medium"
                            >
                                Total Sales
                            </div>
                        </div>
                    </div>

                    <!-- Sales Value -->
                    <div class="text-center">
                        <div class="mb-4">
                            <div
                                class="text-4xl font-light text-neutral-900 mb-2"
                            >
                                ₱{{ formatNumber(topBroker.total_sales_value) }}
                            </div>
                            <div
                                class="w-12 h-px bg-neutral-300 mx-auto mb-4"
                            ></div>
                            <div
                                class="text-sm uppercase tracking-widest text-neutral-500 font-medium"
                            >
                                Sales Value
                            </div>
                        </div>
                    </div>

                    <!-- Commission -->
                    <div class="text-center">
                        <div class="mb-4">
                            <div
                                class="text-4xl font-light text-neutral-900 mb-2"
                            >
                                ₱{{ formatNumber(topBroker.total_commission) }}
                            </div>
                            <div
                                class="w-12 h-px bg-neutral-300 mx-auto mb-4"
                            ></div>
                            <div
                                class="text-sm uppercase tracking-widest text-neutral-500 font-medium"
                            >
                                Commission
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Achievement Statement - Minimal Typography -->
                <div class="text-center max-w-3xl mx-auto">
                    <div class="py-16 border-t border-neutral-100">
                        <blockquote
                            class="text-2xl font-light text-neutral-700 leading-relaxed italic"
                        >
                            "{{ topBroker.name }} has achieved
                            <span class="text-neutral-900 font-normal"
                                >{{ topBroker.total_sales }} successful
                                transactions</span
                            >
                            {{
                                period === "all-time"
                                    ? "of all time"
                                    : `in the ${formatPeriodLabel(
                                          period
                                      ).toLowerCase()}`
                            }}, setting the standard for excellence in Bohol
                            real estate."
                        </blockquote>
                    </div>
                </div>
            </div>

            <!-- No Data State - Minimal -->
            <div v-else class="text-center py-32">
                <div
                    class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-8"
                >
                    <svg
                        class="w-8 h-8 text-neutral-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                        />
                    </svg>
                </div>
                <h3 class="text-2xl font-light text-neutral-900 mb-4">
                    No Sales Data Available
                </h3>
                <p class="text-neutral-500 max-w-md mx-auto">
                    There are no completed sales for the selected period.
                </p>
            </div>
        </div>

        <!-- Call to Action - Minimal and Elegant -->
        <div class="bg-neutral-50 border-t border-neutral-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="text-center">
                    <h3
                        class="text-3xl font-light text-neutral-900 mb-6 tracking-tight"
                    >
                        Work with Excellence
                    </h3>
                    <p
                        class="text-lg text-neutral-600 mb-12 max-w-2xl mx-auto font-light"
                    >
                        Connect with our top-performing brokers to find your
                        perfect property in Bohol.
                    </p>
                    <div
                        class="flex flex-col sm:flex-row gap-6 justify-center items-center"
                    >
                        <Link
                            :href="route('public.properties')"
                            class="inline-flex items-center px-8 py-4 bg-neutral-900 text-white font-medium rounded-full hover:bg-neutral-800 transition-colors"
                        >
                            Browse Properties
                            <svg
                                class="w-4 h-4 ml-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </Link>
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center px-8 py-4 text-neutral-700 font-medium hover:text-neutral-900 transition-colors"
                        >
                            Join as Broker
                            <svg
                                class="w-4 h-4 ml-2"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Public Footer -->
    <PublicFooter />
</template>

<script setup>
import { ref } from "vue";
import { Head, router, Link } from "@inertiajs/vue3";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import PublicFooter from "@/Components/PublicFooter.vue";

const props = defineProps({
    topBroker: Object,
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

const formatPeriodLabel = (period) => {
    const labels = {
        "all-time": "All Time",
        "this-year": "This Year",
        "this-month": "This Month",
        "last-30-days": "Last 30 Days",
        "last-90-days": "Last 90 Days",
    };
    return labels[period] || "All Time";
};
</script>
