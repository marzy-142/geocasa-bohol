<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import PublicFooter from "@/Components/PublicFooter.vue";

import {
    MapPinIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
    ChartBarIcon,
    StarIcon,
    ArrowRightIcon,
    PlayIcon,
    CheckCircleIcon,
    SunIcon,
} from "@heroicons/vue/24/outline";

defineProps({
    auth: Object,
    stats: {
        type: Object,
        default: () => ({
            totalProperties: 247,
            totalBrokers: 15,
            totalClients: 89,
            successRate: 92,
        }),
    },
    featuredProperties: {
        type: Array,
        default: () => [],
    },
    topBrokers: {
        type: Array,
        default: () => [],
    },
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    })
        .format(value)
        .replace("PHP", "")
        .trim();
};

const formatPropertyType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};
</script>

<template>
    <Head title="GeoCasa Bohol - Premium Real Estate in Paradise">
        <meta
            name="description"
            content="Invest in Bohol's growing real estate market with GeoCasa. Verified properties, licensed brokers, and expert guidance for beachfront lots, mountain retreats, and urban developments. Join 500+ successful investors."
        />
        <meta
            name="keywords"
            content="Bohol real estate, property investment, beachfront lots, mountain properties, Tagbilaran real estate, Panglao properties, foreign investment Philippines"
        />
        <meta
            property="og:title"
            content="GeoCasa Bohol - Premium Real Estate Investment Opportunities"
        />
        <meta
            property="og:description"
            content="Discover verified properties across Bohol with expert local brokers. From beachfront lots to mountain retreats, secure your piece of paradise with 92% success rate."
        />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://geocasabohol.com" />
        <meta
            property="og:image"
            content="https://images.unsplash.com/photo-1500382017468-9049fed747ef?ixlib=rb-4.0.3"
        />
        <meta name="twitter:card" content="summary_large_image" />
        <meta
            name="twitter:title"
            content="GeoCasa Bohol - Premium Real Estate Investment"
        />
        <meta
            name="twitter:description"
            content="Join 500+ successful investors in Bohol's growing real estate market. Verified properties, licensed brokers, expert guidance."
        />
    </Head>

    <div class="min-h-screen bg-white">
        <!-- Navigation -->
        <PublicNavigation :auth="auth" />

        <!-- Hero Section -->
        <section
            class="relative min-h-screen flex items-center justify-center overflow-hidden"
        >
            <!-- Hero Background Image -->
            <div class="absolute inset-0">
                <img
                    src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2532&q=80"
                    alt="Prime land development and property lots in Bohol"
                    class="w-full h-full object-cover"
                />
                <div class="absolute inset-0 bg-black/40"></div>
            </div>

            <!-- Background Elements -->
            <div
                class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl"
            ></div>
            <div
                class="absolute bottom-20 right-10 w-96 h-96 bg-white/10 rounded-full blur-3xl"
            ></div>

            <div
                class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 z-10"
            >
                <div class="text-center animate-fade-in">
                    <!-- Badge -->
                    <div
                        class="inline-flex items-center gap-2 bg-white/90 backdrop-blur-sm border border-white/20 rounded-full px-6 py-3 mb-8 shadow-soft"
                    >
                        <div
                            class="w-2 h-2 bg-accent-500 rounded-full animate-pulse"
                        ></div>
                        <span class="text-sm font-medium text-neutral-700"
                            >Your Trusted Real Estate Partner</span
                        >
                    </div>

                    <!-- Main Heading -->
                    <h1
                        class="text-5xl md:text-7xl font-bold text-white mb-8 text-balance drop-shadow-lg"
                    >
                        Invest in
                        <span class="text-accent-400"> Bohol's </span>
                        Growing Real Estate Market
                    </h1>

                    <p
                        class="text-xl md:text-2xl text-white/90 mb-12 max-w-4xl mx-auto text-balance drop-shadow-md"
                    >
                        Join 500+ successful investors who've discovered Bohol's
                        potential. From beachfront lots to mountain retreats,
                        secure your piece of paradise with verified properties
                        and expert local brokers.
                    </p>

                    <!-- CTA Buttons -->
                    <div
                        class="flex flex-col sm:flex-row gap-6 justify-center mb-16"
                    >
                        <Link
                            :href="route('public.properties')"
                            class="bg-accent-600 text-white hover:bg-accent-700 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 shadow-card hover:shadow-card-hover flex items-center justify-center gap-3 transform hover:scale-105"
                        >
                            <BuildingOfficeIcon class="w-5 h-5" />
                            Find My Investment Property
                        </Link>
                        <Link
                            :href="route('seller-requests.create')"
                            class="bg-white/90 backdrop-blur-sm text-primary-600 hover:bg-white px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 shadow-card flex items-center justify-center gap-3 border border-white/20"
                        >
                            <ArrowRightIcon class="w-5 h-5" />
                            Get Free Property Valuation
                        </Link>
                    </div>

                    <!-- Authentication CTA for guests -->
                    <div v-if="!auth.user" class="text-center mb-8">
                        <p class="text-white/80 mb-4">
                            Ready to start your investment journey?
                        </p>
                        <div
                            class="flex flex-col sm:flex-row gap-4 justify-center"
                        >
                            <Link
                                :href="route('register')"
                                class="bg-white/20 backdrop-blur-sm text-white hover:bg-white/30 px-6 py-3 rounded-lg font-medium transition-all duration-300 border border-white/30"
                            >
                                Create Free Account
                            </Link>
                            <Link
                                :href="route('login')"
                                class="text-white hover:text-accent-200 px-6 py-3 font-medium transition-all duration-300"
                            >
                                Already have an account? Sign In
                            </Link>
                        </div>
                    </div>
                    <!-- Welcome back for authenticated users -->
                    <div v-else class="text-center mb-8">
                        <p class="text-white/90 mb-4">
                            Welcome back, {{ auth.user.name }}!
                        </p>
                        <Link
                            :href="route('dashboard')"
                            class="bg-white/20 backdrop-blur-sm text-white hover:bg-white/30 px-6 py-3 rounded-lg font-medium transition-all duration-300 border border-white/30"
                        >
                            Go to Dashboard
                        </Link>
                    </div>

                    <!-- Trust Indicators -->
                    <div
                        class="flex flex-wrap justify-center items-center gap-8 text-sm text-white/90"
                    >
                        <div class="flex items-center gap-2">
                            <CheckCircleIcon class="w-5 h-5 text-accent-400" />
                            <span>15+ Licensed Brokers</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <CheckCircleIcon class="w-5 h-5 text-accent-400" />
                            <span>247+ Verified Properties</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <CheckCircleIcon class="w-5 h-5 text-accent-400" />
                            <span>92% Success Rate</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <CheckCircleIcon class="w-5 h-5 text-accent-400" />
                            <span>Free Consultations</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2
                        class="text-3xl md:text-4xl font-bold text-neutral-900 mb-4"
                    >
                        Platform Overview
                    </h2>
                    <p class="text-xl text-neutral-600">
                        Current activity on our platform
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="text-center group">
                        <div
                            class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-primary-100 transition-colors"
                        >
                            <BuildingOfficeIcon
                                class="w-8 h-8 text-primary-600"
                            />
                        </div>
                        <div
                            class="text-4xl md:text-5xl font-bold text-neutral-900 mb-2"
                        >
                            {{ stats.totalProperties }}+
                        </div>
                        <div class="text-neutral-600 font-medium">
                            Properties Listed
                        </div>
                    </div>

                    <div class="text-center group">
                        <div
                            class="w-16 h-16 bg-accent-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-accent-100 transition-colors"
                        >
                            <UserGroupIcon class="w-8 h-8 text-accent-600" />
                        </div>
                        <div
                            class="text-4xl md:text-5xl font-bold text-neutral-900 mb-2"
                        >
                            {{ stats.totalBrokers }}+
                        </div>
                        <div class="text-neutral-600 font-medium">
                            Registered Brokers
                        </div>
                    </div>

                    <div class="text-center group">
                        <div
                            class="w-16 h-16 bg-warning-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-warning-100 transition-colors"
                        >
                            <StarIcon class="w-8 h-8 text-warning-600" />
                        </div>
                        <div
                            class="text-4xl md:text-5xl font-bold text-neutral-900 mb-2"
                        >
                            {{ stats.totalClients }}+
                        </div>
                        <div class="text-neutral-600 font-medium">
                            Registered Users
                        </div>
                    </div>

                    <div class="text-center group">
                        <div
                            class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-primary-100 transition-colors"
                        >
                            <ChartBarIcon class="w-8 h-8 text-primary-600" />
                        </div>
                        <div
                            class="text-4xl md:text-5xl font-bold text-neutral-900 mb-2"
                        >
                            {{ stats.successRate }}%
                        </div>
                        <div class="text-neutral-600 font-medium">
                            Platform Activity
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Brokers -->
        <section v-if="topBrokers.length > 0" class="py-20 bg-neutral-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2
                        class="text-3xl md:text-4xl font-bold text-neutral-900 mb-4"
                    >
                        Our Expert Brokers
                    </h2>
                    <p class="text-xl text-neutral-600">
                        Connect with licensed real estate professionals who know
                        Bohol inside and out
                    </p>
                </div>

                <!-- Top Broker Highlight - Clean Premium Design -->
                <div class="max-w-5xl mx-auto mb-16">
                    <div
                        v-if="topBrokers[0]"
                        class="bg-white rounded-2xl shadow-xl border border-neutral-100 overflow-hidden"
                    >
                        <!-- Header -->
                        <div
                            class="bg-gradient-to-r from-primary-600 to-primary-700 px-8 py-6"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center"
                                >
                                    <StarIcon class="w-5 h-5 text-white" />
                                </div>
                                <h3 class="text-xl font-bold text-white">
                                    Top Performer of the Month
                                </h3>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-8">
                            <div class="grid lg:grid-cols-2 gap-8 items-center">
                                <!-- Broker Info -->
                                <div>
                                    <div class="flex items-center gap-6 mb-6">
                                        <!-- Avatar -->
                                        <div
                                            class="w-20 h-20 bg-gradient-to-br from-primary-100 to-primary-200 rounded-2xl flex items-center justify-center"
                                        >
                                            <span class="text-3xl">🏆</span>
                                        </div>

                                        <!-- Basic Info -->
                                        <div>
                                            <h4
                                                class="text-3xl font-bold text-neutral-900 mb-1"
                                            >
                                                {{ topBrokers[0].name }}
                                            </h4>
                                            <p class="text-neutral-600 mb-2">
                                                Licensed Real Estate Broker
                                            </p>
                                            <div
                                                class="flex items-center gap-4"
                                            >
                                                <div
                                                    class="flex items-center gap-2 text-primary-600"
                                                >
                                                    <CheckCircleIcon
                                                        class="w-4 h-4"
                                                    />
                                                    <span
                                                        class="text-sm font-medium"
                                                        >Verified</span
                                                    >
                                                </div>
                                                <div
                                                    class="flex items-center gap-2 text-green-600"
                                                >
                                                    <div
                                                        class="w-2 h-2 bg-green-500 rounded-full"
                                                    ></div>
                                                    <span
                                                        class="text-sm font-medium"
                                                        >Online</span
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Specialization -->
                                    <p
                                        class="text-neutral-600 mb-8 leading-relaxed"
                                    >
                                        Specializes in beachfront properties and
                                        investment opportunities. Fluent in
                                        English, Filipino, and local Boholano
                                        dialects.
                                    </p>

                                    <!-- Action Buttons -->
                                    <div class="flex gap-4">
                                        <Link
                                            v-if="auth.user"
                                            :href="route('client.broker')"
                                            class="bg-primary-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-primary-700 transition-colors"
                                        >
                                            View Profile
                                        </Link>
                                        <Link
                                            v-else
                                            :href="route('register')"
                                            class="bg-primary-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-primary-700 transition-colors"
                                        >
                                            Join to Connect
                                        </Link>
                                        <Link
                                            :href="
                                                route('seller-requests.create')
                                            "
                                            class="border border-neutral-300 text-neutral-700 px-6 py-3 rounded-xl font-semibold hover:bg-neutral-50 transition-colors"
                                        >
                                            Contact
                                        </Link>
                                    </div>
                                </div>

                                <!-- Performance Metrics -->
                                <div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <!-- Properties Sold -->
                                        <div
                                            class="text-center p-6 bg-blue-50 rounded-xl"
                                        >
                                            <div
                                                class="text-3xl font-bold text-blue-600 mb-1"
                                            >
                                                {{
                                                    topBrokers[0]
                                                        .total_properties || 0
                                                }}
                                            </div>
                                            <div
                                                class="text-sm font-medium text-blue-800"
                                            >
                                                Properties Sold
                                            </div>
                                        </div>

                                        <!-- Happy Clients -->
                                        <div
                                            class="text-center p-6 bg-green-50 rounded-xl"
                                        >
                                            <div
                                                class="text-3xl font-bold text-green-600 mb-1"
                                            >
                                                {{
                                                    topBrokers[0]
                                                        .total_transactions || 0
                                                }}
                                            </div>
                                            <div
                                                class="text-sm font-medium text-green-800"
                                            >
                                                Happy Clients
                                            </div>
                                        </div>

                                        <!-- Success Rate -->
                                        <div
                                            class="text-center p-6 bg-yellow-50 rounded-xl"
                                        >
                                            <div
                                                class="text-3xl font-bold text-yellow-600 mb-1"
                                            >
                                                98%
                                            </div>
                                            <div
                                                class="text-sm font-medium text-yellow-800"
                                            >
                                                Success Rate
                                            </div>
                                        </div>

                                        <!-- Response Time -->
                                        <div
                                            class="text-center p-6 bg-purple-50 rounded-xl"
                                        >
                                            <div
                                                class="text-3xl font-bold text-purple-600 mb-1"
                                            >
                                                24h
                                            </div>
                                            <div
                                                class="text-sm font-medium text-purple-800"
                                            >
                                                Response Time
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Brokers Grid -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12"
                >
                    <div
                        v-for="broker in topBrokers.slice(1, 4)"
                        :key="broker.id"
                        class="card p-6 text-center hover:shadow-card-hover transition-all duration-300"
                    >
                        <div
                            class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4"
                        >
                            <span class="text-primary-600 font-bold text-xl">{{
                                broker.name.charAt(0)
                            }}</span>
                        </div>
                        <h4 class="text-lg font-semibold text-neutral-900 mb-2">
                            {{ broker.name }}
                        </h4>
                        <p class="text-sm text-neutral-600 mb-4">
                            Licensed Real Estate Broker
                        </p>
                        <div class="flex justify-center mb-4">
                            <StarIcon
                                class="w-4 h-4 text-warning-400 fill-current"
                            />
                            <StarIcon
                                class="w-4 h-4 text-warning-400 fill-current"
                            />
                            <StarIcon
                                class="w-4 h-4 text-warning-400 fill-current"
                            />
                            <StarIcon
                                class="w-4 h-4 text-warning-400 fill-current"
                            />
                            <StarIcon
                                class="w-4 h-4 text-warning-400 fill-current"
                            />
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                            <div>
                                <div class="font-semibold text-primary-600">
                                    {{ broker.total_properties || 0 }}
                                </div>
                                <div class="text-neutral-600">Properties</div>
                            </div>
                            <div>
                                <div class="font-semibold text-accent-600">
                                    {{ broker.total_transactions || 0 }}
                                </div>
                                <div class="text-neutral-600">Sales</div>
                            </div>
                        </div>
                        <Link
                            v-if="auth.user"
                            :href="route('client.broker')"
                            class="btn-outline-sm w-full"
                        >
                            View Profile
                        </Link>
                        <Link
                            v-else
                            :href="route('register')"
                            class="btn-outline-sm w-full"
                        >
                            Join to Connect
                        </Link>
                    </div>
                </div>

                <!-- View All Brokers CTA -->
                <div class="text-center">
                    <Link
                        v-if="auth.user"
                        :href="route('client.broker')"
                        class="btn-outline px-8 py-3"
                    >
                        View All Brokers
                    </Link>
                    <Link
                        v-else
                        :href="route('register')"
                        class="btn-outline px-8 py-3"
                    >
                        Join to Access Brokers
                    </Link>
                </div>
            </div>
        </section>

        <!-- Featured Properties -->
        <section v-if="featuredProperties.length > 0" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2
                        class="text-3xl md:text-4xl font-bold text-neutral-900 mb-4"
                    >
                        Featured Properties
                    </h2>
                    <p class="text-xl text-neutral-600">
                        Selected property listings from across Bohol
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                >
                    <div
                        v-for="property in featuredProperties.slice(0, 6)"
                        :key="property.id"
                        class="card overflow-hidden group hover:shadow-soft-xl transition-all duration-300 hover:-translate-y-1"
                    >
                        <div class="relative h-56 overflow-hidden">
                            <img
                                :src="property.main_image"
                                :alt="property.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div class="absolute top-4 left-4">
                                <div
                                    class="bg-accent-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-card"
                                >
                                    ⭐ Featured
                                </div>
                            </div>
                            <div class="absolute top-4 right-4">
                                <div
                                    class="bg-white/90 backdrop-blur-sm text-neutral-800 px-2 py-1 rounded-lg text-xs font-medium"
                                >
                                    {{ formatPropertyType(property.type) }}
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <h3
                                class="text-xl font-bold text-neutral-900 mb-2 line-clamp-2"
                            >
                                {{ property.title }}
                            </h3>
                            <div
                                class="flex items-center gap-2 text-neutral-600 mb-3"
                            >
                                <MapPinIcon class="w-4 h-4 flex-shrink-0" />
                                <span class="text-sm line-clamp-1">{{
                                    property.full_address
                                }}</span>
                            </div>

                            <!-- Property Features -->
                            <div class="flex items-center gap-4 mb-4">
                                <div class="flex items-center gap-1">
                                    <div
                                        class="w-2 h-2 bg-primary-500 rounded-full"
                                    ></div>
                                    <span class="text-xs text-neutral-600">
                                        {{
                                            property.electricity_available
                                                ? "Power"
                                                : "No Power"
                                        }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <div
                                        class="w-2 h-2 bg-accent-500 rounded-full"
                                    ></div>
                                    <span class="text-xs text-neutral-600">
                                        {{
                                            property.water_source
                                                ? "Water"
                                                : "No Water"
                                        }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <div
                                    class="text-2xl font-bold text-primary-600"
                                >
                                    {{ formatCurrency(property.total_price) }}
                                </div>
                                <Link
                                    v-if="property.slug"
                                    :href="
                                        route(
                                            'public.properties.show',
                                            property.slug
                                        )
                                    "
                                    class="btn-primary-sm"
                                >
                                    View Details
                                </Link>
                                <span
                                    v-else
                                    class="btn-primary-sm opacity-50 cursor-not-allowed"
                                >
                                    View Details
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <Link
                        :href="route('public.properties')"
                        class="btn-outline text-lg px-8 py-4"
                    >
                        View All Properties
                    </Link>
                </div>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="py-20 bg-neutral-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2
                        class="text-3xl md:text-4xl font-bold text-neutral-900 mb-4"
                    >
                        Why Choose GeoCasa Bohol?
                    </h2>
                    <p class="text-xl text-neutral-600">
                        Connecting buyers and sellers in Bohol's real estate
                        market
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8"
                >
                    <div class="card p-8 text-center">
                        <div
                            class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <UserGroupIcon class="w-8 h-8 text-primary-600" />
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 mb-4">
                            Licensed Brokers
                        </h3>
                        <p class="text-neutral-600">
                            Connect with registered real estate professionals
                            familiar with the local Bohol market.
                        </p>
                    </div>

                    <div class="card p-8 text-center">
                        <div
                            class="w-16 h-16 bg-accent-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <CheckCircleIcon class="w-8 h-8 text-accent-600" />
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 mb-4">
                            Verified Properties
                        </h3>
                        <p class="text-neutral-600">
                            Property listings undergo verification processes to
                            help ensure accuracy and legitimacy.
                        </p>
                    </div>

                    <div class="card p-8 text-center">
                        <div
                            class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <BuildingOfficeIcon
                                class="w-8 h-8 text-primary-600"
                            />
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 mb-4">
                            Local Expertise
                        </h3>
                        <p class="text-neutral-600">
                            Deep knowledge of Bohol's municipalities,
                            regulations, and market trends.
                        </p>
                    </div>

                    <div class="card p-8 text-center">
                        <div
                            class="w-16 h-16 bg-accent-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <StarIcon class="w-8 h-8 text-accent-600" />
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 mb-4">
                            Premium Service
                        </h3>
                        <p class="text-neutral-600">
                            Personalized attention and support throughout your
                            real estate journey.
                        </p>
                    </div>

                    <div class="card p-8 text-center">
                        <div
                            class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <ChartBarIcon class="w-8 h-8 text-primary-600" />
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 mb-4">
                            Market Insights
                        </h3>
                        <p class="text-neutral-600">
                            Access to comprehensive market data and investment
                            analysis tools.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bohol Investment Benefits -->
        <section class="py-20 bg-neutral-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2
                        class="text-3xl md:text-4xl font-bold text-neutral-900 mb-4"
                    >
                        Why Invest in Bohol Real Estate?
                    </h2>
                    <p class="text-xl text-neutral-600">
                        Discover the unique advantages that make Bohol a smart
                        investment choice
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                >
                    <!-- Benefit 1 -->
                    <div class="card p-8 text-center">
                        <div
                            class="w-16 h-16 bg-accent-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <ChartBarIcon class="w-8 h-8 text-accent-600" />
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 mb-4">
                            Rapid Tourism Growth
                        </h3>
                        <p class="text-neutral-600 mb-4">
                            Bohol's tourism industry has grown 25% annually,
                            driving property values up and creating rental
                            income opportunities.
                        </p>
                        <div class="text-2xl font-bold text-accent-600">
                            +25% Growth
                        </div>
                    </div>

                    <!-- Benefit 2 -->
                    <div class="card p-8 text-center">
                        <div
                            class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <BuildingOfficeIcon
                                class="w-8 h-8 text-primary-600"
                            />
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 mb-4">
                            Infrastructure Development
                        </h3>
                        <p class="text-neutral-600 mb-4">
                            New airports, roads, and utilities are transforming
                            Bohol into a modern investment destination with
                            excellent connectivity.
                        </p>
                        <div class="text-2xl font-bold text-primary-600">
                            ₱50B+ Investment
                        </div>
                    </div>

                    <!-- Benefit 3 -->
                    <div class="card p-8 text-center">
                        <div
                            class="w-16 h-16 bg-warning-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <StarIcon class="w-8 h-8 text-warning-600" />
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 mb-4">
                            UNESCO Heritage Status
                        </h3>
                        <p class="text-neutral-600 mb-4">
                            Protected natural attractions like Chocolate Hills
                            ensure long-term property value appreciation and
                            sustainable tourism.
                        </p>
                        <div class="text-2xl font-bold text-warning-600">
                            Protected Value
                        </div>
                    </div>

                    <!-- Benefit 4 -->
                    <div class="card p-8 text-center">
                        <div
                            class="w-16 h-16 bg-accent-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <MapPinIcon class="w-8 h-8 text-accent-600" />
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 mb-4">
                            Strategic Location
                        </h3>
                        <p class="text-neutral-600 mb-4">
                            Central Philippines location with easy access to
                            major cities and international destinations via
                            direct flights.
                        </p>
                        <div class="text-2xl font-bold text-accent-600">
                            2-Hour Access
                        </div>
                    </div>

                    <!-- Benefit 5 -->
                    <div class="card p-8 text-center">
                        <div
                            class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <UserGroupIcon class="w-8 h-8 text-primary-600" />
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 mb-4">
                            Foreign Investment Friendly
                        </h3>
                        <p class="text-neutral-600 mb-4">
                            Simplified property ownership laws for foreigners,
                            making Bohol an attractive destination for
                            international investors.
                        </p>
                        <div class="text-2xl font-bold text-primary-600">
                            Easy Ownership
                        </div>
                    </div>

                    <!-- Benefit 6 -->
                    <div class="card p-8 text-center">
                        <div
                            class="w-16 h-16 bg-warning-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <SunIcon class="w-8 h-8 text-warning-600" />
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 mb-4">
                            Year-Round Climate
                        </h3>
                        <p class="text-neutral-600 mb-4">
                            Tropical paradise with consistent weather, perfect
                            for vacation rentals and year-round property
                            enjoyment.
                        </p>
                        <div class="text-2xl font-bold text-warning-600">
                            365 Days
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2
                        class="text-3xl md:text-4xl font-bold text-neutral-900 mb-4"
                    >
                        Frequently Asked Questions
                    </h2>
                    <p class="text-xl text-neutral-600">
                        Everything you need to know about investing in Bohol
                        real estate
                    </p>
                </div>

                <div class="space-y-6">
                    <!-- FAQ 1 -->
                    <div class="card p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 mb-3">
                            Can foreigners buy property in Bohol?
                        </h3>
                        <p class="text-neutral-600">
                            Yes! Foreigners can purchase condominium units and
                            lease land for up to 50 years (renewable). For land
                            ownership, foreigners can buy through corporations
                            with 40% foreign ownership or through a Filipino
                            spouse. Our brokers will guide you through the legal
                            requirements.
                        </p>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="card p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 mb-3">
                            What are the typical property prices in Bohol?
                        </h3>
                        <p class="text-neutral-600">
                            Property prices vary by location. Beachfront lots in
                            Panglao start around ₱15,000-25,000 per sqm, while
                            inland properties range from ₱3,000-8,000 per sqm.
                            Urban lots in Tagbilaran City typically cost
                            ₱8,000-15,000 per sqm. Prices have been appreciating
                            10-15% annually due to tourism growth.
                        </p>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="card p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 mb-3">
                            How long does the property buying process take?
                        </h3>
                        <p class="text-neutral-600">
                            The complete process typically takes 30-45 days for
                            cash purchases and 60-90 days for bank financing.
                            This includes due diligence, legal verification,
                            deed preparation, and transfer registration. Our
                            experienced brokers handle all paperwork to ensure a
                            smooth transaction.
                        </p>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="card p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 mb-3">
                            What documents do I need to buy property?
                        </h3>
                        <p class="text-neutral-600">
                            For Filipinos: Valid ID, TIN, and proof of income.
                            For foreigners: Passport, ACR-I card, proof of
                            income, and bank statements. Our brokers will
                            provide a complete checklist and help you gather all
                            necessary documents to ensure a successful
                            transaction.
                        </p>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="card p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 mb-3">
                            Are there any hidden fees or taxes?
                        </h3>
                        <p class="text-neutral-600">
                            We believe in complete transparency. Standard costs
                            include: Transfer tax (0.5-0.75%), documentary
                            stamps (1.5%), registration fees (0.25%), and broker
                            commission (3-5%). We provide detailed cost
                            breakdowns before any transaction to avoid
                            surprises.
                        </p>
                    </div>

                    <!-- FAQ 6 -->
                    <div class="card p-6">
                        <h3 class="text-lg font-semibold text-neutral-900 mb-3">
                            How do I verify a property's legal status?
                        </h3>
                        <p class="text-neutral-600">
                            All our listed properties undergo verification
                            including: Title verification at the Registry of
                            Deeds, tax clearance checks, survey plan validation,
                            and encumbrance clearance. We provide complete
                            documentation and can arrange property inspections
                            before purchase.
                        </p>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <p class="text-neutral-600 mb-6">Still have questions?</p>
                    <Link
                        :href="route('seller-requests.create')"
                        class="btn-outline px-8 py-3"
                    >
                        Get Free Consultation
                    </Link>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="py-20 bg-neutral-50">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="card card-elevated p-12">
                    <h2
                        class="text-3xl md:text-4xl font-bold mb-6 text-neutral-900"
                    >
                        Start Your Bohol Investment Journey Today
                    </h2>
                    <p class="text-xl mb-8 text-neutral-600">
                        Join hundreds of successful investors who've discovered
                        Bohol's potential. Get expert guidance and access to
                        verified properties.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            :href="route('public.properties')"
                            class="btn-primary px-8 py-4 text-lg"
                        >
                            Browse Investment Properties
                        </Link>
                        <Link
                            :href="route('seller-requests.create')"
                            class="btn-secondary px-8 py-4 text-lg"
                        >
                            Get Free Property Valuation
                        </Link>
                    </div>
                    <div v-if="!auth.user" class="mt-6">
                        <p class="text-sm text-neutral-600 mb-4">
                            Already have an account?
                        </p>
                        <Link
                            :href="route('login')"
                            class="btn-outline px-6 py-3"
                        >
                            Sign In to Dashboard
                        </Link>
                    </div>
                    <div v-else class="mt-6">
                        <Link
                            :href="route('dashboard')"
                            class="btn-primary px-6 py-3"
                        >
                            Go to Dashboard
                        </Link>
                    </div>
                    <div class="mt-8 text-sm text-neutral-500">
                        <p>
                            ✓ Free consultation • ✓ No obligation • ✓ Expert
                            local guidance
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bohol Inspiration -->
        <section class="py-20 bg-neutral-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2
                        class="text-3xl md:text-4xl font-bold text-neutral-900 mb-4"
                    >
                        Discover Bohol's Beauty
                    </h2>
                    <p class="text-xl text-neutral-600">
                        From pristine beaches to rolling hills, find your
                        perfect slice of paradise
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div
                        class="card overflow-hidden group hover:shadow-soft-xl transition-all duration-300 hover:-translate-y-1"
                    >
                        <div class="relative h-48 overflow-hidden">
                            <img
                                src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80"
                                alt="Beautiful coastal property in Bohol"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div class="absolute inset-0 bg-black/20"></div>
                            <div class="absolute bottom-4 left-4 text-white">
                                <SunIcon class="w-8 h-8 mb-2" />
                                <h3 class="text-xl font-bold">
                                    Coastal Properties
                                </h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-neutral-600">
                                Wake up to ocean views and gentle sea breezes in
                                our premium beachfront locations.
                            </p>
                        </div>
                    </div>

                    <div
                        class="card overflow-hidden group hover:shadow-soft-xl transition-all duration-300 hover:-translate-y-1"
                    >
                        <div class="relative h-48 overflow-hidden">
                            <img
                                src="https://images.unsplash.com/photo-1586500036706-41963de24d8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80"
                                alt="Chocolate Hills mountain retreat in Bohol"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div class="absolute inset-0 bg-black/20"></div>
                            <div class="absolute bottom-4 left-4 text-white">
                                <MapPinIcon class="w-8 h-8 mb-2" />
                                <h3 class="text-xl font-bold">
                                    Mountain Retreats
                                </h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-neutral-600">
                                Escape to elevated properties with panoramic
                                views of Bohol's famous Chocolate Hills.
                            </p>
                        </div>
                    </div>

                    <div
                        class="card overflow-hidden group hover:shadow-soft-xl transition-all duration-300 hover:-translate-y-1"
                    >
                        <div class="relative h-48 overflow-hidden">
                            <img
                                src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80"
                                alt="Modern urban development in Bohol"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div class="absolute inset-0 bg-black/20"></div>
                            <div class="absolute bottom-4 left-4 text-white">
                                <BuildingOfficeIcon class="w-8 h-8 mb-2" />
                                <h3 class="text-xl font-bold">
                                    Urban Developments
                                </h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-neutral-600">
                                Modern conveniences meet tropical living in our
                                carefully planned urban communities.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <PublicFooter />
    </div>
</template>
