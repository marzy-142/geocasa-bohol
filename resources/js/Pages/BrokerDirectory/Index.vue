<script setup>
import { Head } from "@inertiajs/vue3";
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
                <div class="max-w-5xl mx-auto">
                    <!-- Header -->
                    <div class="text-center mb-10">
                        <h2
                            class="text-3xl md:text-4xl font-bold text-neutral-900 mb-3"
                        >
                            Top Performing
                            <span
                                class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-accent-600"
                            >
                                Broker
                            </span>
                        </h2>
                        <p class="text-neutral-600 text-lg">
                            Excellence in Real Estate
                        </p>
                    </div>

                    <!-- Premium Card -->
                    <div
                        class="relative bg-white rounded-xl shadow-lg border border-neutral-200 overflow-hidden hover:shadow-xl transition-shadow duration-300"
                    >
                        <!-- Gradient accent line -->
                        <div
                            class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary-600 to-accent-600"
                        ></div>

                        <div class="p-12">
                            <div
                                class="flex flex-col md:flex-row items-center gap-10"
                            >
                                <!-- Refined Avatar -->
                                <div class="relative flex-shrink-0">
                                    <UserAvatar
                                        v-if="topPerformers[0]"
                                        :user="topPerformers[0]"
                                        size="2xl"
                                        bg-color="primary"
                                        class="w-32 h-32 border-4 border-white shadow-lg"
                                    />
                                    <!-- Trophy badge -->
                                    <div
                                        class="absolute -bottom-2 -right-2 w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center shadow-lg border-4 border-white"
                                    >
                                        <TrophyIcon
                                            class="w-6 h-6 text-white"
                                        />
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 text-center md:text-left">
                                    <div class="mb-6">
                                        <h3
                                            class="text-3xl md:text-4xl font-bold text-neutral-900 mb-2"
                                        >
                                            {{ topPerformers[0].name }}
                                        </h3>
                                        <p
                                            v-if="
                                                topPerformers[0]
                                                    .brokerage_firm_name
                                            "
                                            class="text-lg text-neutral-600"
                                        >
                                            {{
                                                topPerformers[0]
                                                    .brokerage_firm_name
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        class="flex flex-wrap items-center justify-center md:justify-start gap-4 mb-6"
                                    >
                                        <div
                                            v-if="topPerformers[0].city"
                                            class="flex items-center text-neutral-600"
                                        >
                                            <MapPinIcon
                                                class="w-4 h-4 mr-1.5 text-primary-600"
                                            />
                                            <span class="text-sm font-medium">{{
                                                topPerformers[0].city
                                            }}</span>
                                        </div>
                                        <div
                                            class="flex items-center text-neutral-600"
                                        >
                                            <CheckBadgeIcon
                                                class="w-4 h-4 mr-1.5 text-green-600"
                                            />
                                            <span class="text-sm font-medium"
                                                >Verified Professional</span
                                            >
                                        </div>
                                    </div>

                                    <!-- Stats Badge -->
                                    <div
                                        class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-primary-500 to-accent-500 rounded-lg shadow-md"
                                    >
                                        <div
                                            class="text-4xl font-bold text-white"
                                        >
                                            {{
                                                topPerformers[0]
                                                    .finalized_transactions_count
                                            }}
                                        </div>
                                        <div
                                            class="text-sm text-white/90 uppercase tracking-wide font-semibold"
                                        >
                                            Completed Sales
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
