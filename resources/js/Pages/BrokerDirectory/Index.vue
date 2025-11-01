<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import {
    MapPinIcon,
    BriefcaseIcon,
    BuildingOfficeIcon,
    PhoneIcon,
    EnvelopeIcon,
    CheckBadgeIcon,
    TrophyIcon,
    FireIcon,
    ArrowRightIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    topPerformers: Array,
    brokers: Object,
    auth: Object,
});

// Get medal color based on rank
const getMedalColor = (rank) => {
    switch (rank) {
        case 1:
            return "from-yellow-400 to-yellow-600"; // Gold
        case 2:
            return "from-gray-300 to-gray-500"; // Silver
        case 3:
            return "from-orange-400 to-orange-600"; // Bronze
        default:
            return "from-slate-400 to-slate-600";
    }
};
</script>

<template>
    <Head title="Find a Broker - GeoCasa Bohol" />

    <div class="min-h-screen bg-gradient-to-br from-neutral-50 to-neutral-100">
        <PublicNavigation :auth="auth" current-route="brokers.index" />

        <!-- Hero Section -->
        <section class="relative py-16 lg:py-20 overflow-hidden">
            <div
                class="absolute inset-0 bg-gradient-to-r from-primary-600/10 to-accent-600/10"
            ></div>
            <div
                class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center"
            >
                <div class="max-w-4xl mx-auto mb-12">
                    <h1
                        class="text-3xl md:text-5xl lg:text-6xl font-bold text-neutral-900 mb-6"
                    >
                        Find Your
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-accent-600"
                        >
                            Perfect Broker
                        </span>
                        in Bohol
                    </h1>
                    <p
                        class="text-lg md:text-xl text-neutral-600 max-w-3xl mx-auto"
                    >
                        Connect with verified, licensed real estate
                        professionals
                    </p>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Stats Bar -->
            <div class="mb-12 flex items-center justify-center">
                <div
                    class="inline-flex items-center gap-3 px-6 py-3 bg-white rounded-full border border-neutral-200 shadow-sm"
                >
                    <div
                        class="w-2 h-2 bg-green-500 rounded-full animate-pulse"
                    ></div>
                    <span class="text-neutral-700 font-medium"
                        >{{ brokers.data.length }} Active Brokers</span
                    >
                </div>
            </div>

            <!-- Top Broker Spotlight -->
            <div v-if="topPerformers && topPerformers.length > 0" class="mb-16">
                <div class="max-w-4xl mx-auto">
                    <!-- Premium Header with Shimmer Effect -->
                    <div class="text-center mb-8">
                        <div
                            class="inline-flex items-center gap-2 px-4 py-2 mb-4 bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200 rounded-full"
                        >
                            <TrophyIcon
                                class="w-5 h-5 text-amber-600 animate-pulse"
                            />
                            <span
                                class="text-sm font-semibold text-amber-900 uppercase tracking-wider"
                                >Elite Performance</span
                            >
                        </div>
                        <h2
                            class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-amber-600 via-yellow-500 to-amber-600 bg-clip-text text-transparent mb-2"
                        >
                            Top Performing Broker
                        </h2>
                        <p class="text-neutral-600 text-lg">
                            Recognizing excellence and consistent results
                        </p>
                    </div>

                    <!-- Premium Card with Enhanced Styling -->
                    <div class="relative group">
                        <!-- Animated Background Glow -->
                        <div
                            class="absolute -inset-1 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-400 rounded-2xl blur opacity-25 group-hover:opacity-40 transition duration-1000 animate-pulse"
                        ></div>

                        <!-- Main Card Container -->
                        <div class="relative">
                            <!-- Golden Border Effect -->
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-amber-200 via-yellow-300 to-amber-200 rounded-2xl p-[2px]"
                            >
                                <div
                                    class="h-full w-full bg-white rounded-2xl"
                                ></div>
                            </div>

                            <!-- Card Content -->
                            <div
                                class="relative bg-gradient-to-br from-white via-amber-50/30 to-white rounded-2xl shadow-2xl hover:shadow-amber-200/50 transition-all duration-500 overflow-hidden"
                            >
                                <!-- Premium Badge Ribbon -->
                                <div
                                    class="absolute top-0 right-0 w-32 h-32 overflow-hidden"
                                >
                                    <div
                                        class="absolute top-6 -right-10 rotate-45 bg-gradient-to-r from-amber-500 to-yellow-600 text-white text-xs font-bold py-1 px-12 shadow-lg"
                                    >
                                        #1 BROKER
                                    </div>
                                </div>

                                <!-- Decorative Elements -->
                                <div
                                    class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-400"
                                ></div>

                                <div class="p-8 md:p-10">
                                    <div
                                        class="flex flex-col md:flex-row items-center gap-8"
                                    >
                                        <!-- Premium Avatar Section -->
                                        <div class="relative flex-shrink-0">
                                            <!-- Rotating Golden Ring -->
                                            <div
                                                class="absolute -inset-4 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-400 rounded-full opacity-20 blur-xl animate-spin-slow"
                                            ></div>

                                            <!-- Avatar Container -->
                                            <div class="relative">
                                                <div
                                                    class="absolute -inset-2 bg-gradient-to-r from-amber-400 to-yellow-500 rounded-full animate-pulse"
                                                ></div>
                                                <div
                                                    class="relative bg-white p-2 rounded-full"
                                                >
                                                    <UserAvatar
                                                        v-if="topPerformers[0]"
                                                        :user="topPerformers[0]"
                                                        size="2xl"
                                                        bg-color="primary"
                                                        class="w-32 h-32 border-4 border-white shadow-2xl"
                                                    />
                                                </div>
                                            </div>

                                            <!-- Premium Trophy Badge -->
                                            <div
                                                class="absolute -bottom-2 -right-2 bg-gradient-to-br from-amber-500 via-yellow-500 to-amber-600 text-white rounded-full p-3 shadow-xl ring-4 ring-white"
                                            >
                                                <TrophyIcon class="w-6 h-6" />
                                            </div>

                                            <!-- Sparkle Effects -->
                                            <div
                                                class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full animate-ping"
                                            ></div>
                                            <div
                                                class="absolute -bottom-1 -left-1 w-2 h-2 bg-amber-400 rounded-full animate-pulse"
                                            ></div>
                                        </div>

                                        <!-- Premium Content Section -->
                                        <div
                                            class="flex-1 text-center md:text-left"
                                        >
                                            <!-- Name with Premium Styling -->
                                            <div class="mb-3">
                                                <h3
                                                    class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-amber-900 via-amber-700 to-amber-900 bg-clip-text text-transparent mb-1"
                                                >
                                                    {{ topPerformers[0].name }}
                                                </h3>
                                                <p
                                                    v-if="
                                                        topPerformers[0]
                                                            .brokerage_firm_name
                                                    "
                                                    class="text-lg text-neutral-600 font-medium"
                                                >
                                                    {{
                                                        topPerformers[0]
                                                            .brokerage_firm_name
                                                    }}
                                                </p>
                                            </div>

                                            <!-- Premium Badges -->
                                            <div
                                                class="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-6"
                                            >
                                                <div
                                                    v-if="topPerformers[0].city"
                                                    class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200 rounded-full text-sm font-medium text-amber-900"
                                                >
                                                    <MapPinIcon
                                                        class="w-4 h-4 text-amber-600"
                                                    />
                                                    <span>{{
                                                        topPerformers[0].city
                                                    }}</span>
                                                </div>
                                                <div
                                                    class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-full text-sm font-medium text-green-900"
                                                >
                                                    <CheckBadgeIcon
                                                        class="w-4 h-4 text-green-600"
                                                    />
                                                    <span
                                                        >Verified
                                                        Professional</span
                                                    >
                                                </div>
                                                <div
                                                    class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-amber-100 to-yellow-100 border border-amber-300 rounded-full text-sm font-bold text-amber-900 shadow-sm"
                                                >
                                                    <FireIcon
                                                        class="w-4 h-4 text-amber-600"
                                                    />
                                                    <span>Top Performer</span>
                                                </div>
                                            </div>

                                            <!-- Premium Stats & CTA -->
                                            <div
                                                class="flex flex-col sm:flex-row items-center gap-4"
                                            >
                                                <!-- Premium Stats Box -->
                                                <div
                                                    class="relative group/stats"
                                                >
                                                    <div
                                                        class="absolute -inset-0.5 bg-gradient-to-r from-amber-400 to-yellow-500 rounded-xl opacity-50 group-hover/stats:opacity-75 transition blur"
                                                    ></div>
                                                    <div
                                                        class="relative px-6 py-4 bg-gradient-to-br from-amber-50 via-yellow-50 to-amber-50 border-2 border-amber-300 rounded-xl shadow-lg"
                                                    >
                                                        <div
                                                            class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-amber-600 to-yellow-600 bg-clip-text text-transparent"
                                                        >
                                                            {{
                                                                topPerformers[0]
                                                                    .finalized_transactions_count
                                                            }}
                                                        </div>
                                                        <div
                                                            class="text-xs text-amber-900 font-bold uppercase tracking-wider mt-1"
                                                        >
                                                            Completed Sales
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Premium CTA Button -->
                                                <Link
                                                    :href="
                                                        route(
                                                            'brokers.show',
                                                            topPerformers[0].id
                                                        )
                                                    "
                                                    class="group/btn relative inline-flex items-center gap-2 px-6 py-3 overflow-hidden rounded-xl font-semibold text-white transition-all duration-300 hover:scale-105 active:scale-95"
                                                >
                                                    <!-- Button Background -->
                                                    <div
                                                        class="absolute inset-0 bg-gradient-to-r from-amber-500 via-yellow-500 to-amber-500 transition-all duration-300"
                                                    ></div>
                                                    <div
                                                        class="absolute inset-0 bg-gradient-to-r from-amber-600 via-yellow-600 to-amber-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"
                                                    ></div>

                                                    <!-- Button Content -->
                                                    <span
                                                        class="relative flex items-center gap-2"
                                                    >
                                                        View Premium Profile
                                                        <ArrowRightIcon
                                                            class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform"
                                                        />
                                                    </span>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Header -->
            <div class="text-center mb-10">
                <h2
                    class="text-2xl md:text-3xl font-bold text-neutral-900 mb-2"
                >
                    All Brokers
                </h2>
                <p class="text-neutral-600">
                    Browse our complete directory of verified professionals
                </p>
            </div>

            <!-- Broker Grid -->
            <div
                v-if="brokers.data.length > 0"
                class="grid grid-cols-1 lg:grid-cols-2 gap-6"
            >
                <div
                    v-for="broker in brokers.data"
                    :key="broker.id"
                    class="group relative bg-white border border-neutral-200 rounded-xl p-6 hover:shadow-lg hover:border-neutral-300 transition-all duration-300"
                >
                    <div class="flex flex-col lg:flex-row gap-8">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="relative">
                                <UserAvatar
                                    v-if="broker"
                                    :user="broker"
                                    size="lg"
                                    bg-color="primary"
                                    class="w-20 h-20 border-2 border-white shadow-md"
                                />
                                <!-- Verified badge -->
                                <div
                                    class="absolute -bottom-1 -right-1 bg-green-500 rounded-full p-1 shadow-md"
                                >
                                    <CheckBadgeIcon
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Broker Information -->
                        <div class="flex-1 min-w-0">
                            <!-- Header -->
                            <div class="mb-4">
                                <h3
                                    class="text-xl font-bold text-neutral-900 mb-1"
                                >
                                    {{ broker.name }}
                                </h3>
                                <div
                                    class="flex flex-wrap items-center gap-3 text-sm"
                                >
                                    <span
                                        v-if="broker.prc_license_number"
                                        class="text-neutral-500 font-mono text-xs"
                                    >
                                        PRC {{ broker.prc_license_number }}
                                    </span>
                                    <span
                                        v-if="broker.brokerage_firm_name"
                                        class="flex items-center text-neutral-600"
                                    >
                                        <BuildingOfficeIcon
                                            class="w-4 h-4 mr-1 text-neutral-400"
                                        />
                                        {{ broker.brokerage_firm_name }}
                                    </span>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="space-y-3 mb-4">
                                <!-- Location -->
                                <div
                                    v-if="broker.city"
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <MapPinIcon
                                        class="w-4 h-4 text-primary-600"
                                    />
                                    <span class="text-neutral-700"
                                        >{{ broker.city }},
                                        {{ broker.province }}</span
                                    >
                                </div>

                                <!-- Experience -->
                                <div
                                    v-if="broker.years_experience"
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <BriefcaseIcon
                                        class="w-4 h-4 text-accent-600"
                                    />
                                    <span class="text-neutral-700"
                                        >{{ broker.years_experience }} Years
                                        Experience</span
                                    >
                                </div>

                                <!-- Phone -->
                                <div
                                    v-if="
                                        broker.show_phone &&
                                        broker.office_contact_number
                                    "
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <PhoneIcon
                                        class="w-4 h-4 text-primary-600"
                                    />
                                    <a
                                        :href="`tel:${broker.office_contact_number}`"
                                        class="text-neutral-700 hover:text-primary-600 transition-colors"
                                    >
                                        {{ broker.office_contact_number }}
                                    </a>
                                </div>

                                <!-- Email -->
                                <div
                                    v-if="broker.show_email && broker.email"
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <EnvelopeIcon
                                        class="w-4 h-4 text-accent-600"
                                    />
                                    <a
                                        :href="`mailto:${broker.email}`"
                                        class="text-neutral-700 hover:text-accent-600 transition-colors truncate"
                                    >
                                        {{ broker.email }}
                                    </a>
                                </div>
                            </div>

                            <!-- Specializations -->
                            <div
                                v-if="
                                    broker.specializations &&
                                    broker.specializations.length > 0
                                "
                                class="pt-3 border-t border-neutral-100"
                            >
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="spec in broker.specializations"
                                        :key="spec"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-700 bg-primary-50 rounded-md"
                                    >
                                        {{ spec }}
                                    </span>
                                </div>
                            </div>

                            <!-- View Profile Button -->
                            <div class="mt-4 pt-4 border-t border-neutral-100">
                                <Link
                                    :href="route('brokers.show', broker.id)"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-600 to-accent-600 text-white text-sm font-semibold rounded-lg hover:from-primary-700 hover:to-accent-700 transition-all duration-200 shadow-sm hover:shadow-md group"
                                >
                                    <span>View Full Profile</span>
                                    <ArrowRightIcon
                                        class="w-4 h-4 transition-transform group-hover:translate-x-1"
                                    />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Brokers Message -->
            <div v-else class="text-center py-20">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-2xl mb-4"
                >
                    <BuildingOfficeIcon class="w-8 h-8 text-slate-400" />
                </div>
                <p class="text-slate-600 text-lg">
                    No verified brokers available at this time.
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Premium Animations for Top Performer Card */
@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes shimmer {
    0% {
        background-position: -1000px 0;
    }
    100% {
        background-position: 1000px 0;
    }
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

.animate-spin-slow {
    animation: spin-slow 20s linear infinite;
}

.animate-shimmer {
    animation: shimmer 3s linear infinite;
    background-size: 1000px 100%;
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

/* Gradient text animation */
@keyframes gradient-shift {
    0%,
    100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

.bg-gradient-animate {
    background-size: 200% 200%;
    animation: gradient-shift 3s ease infinite;
}
</style>
