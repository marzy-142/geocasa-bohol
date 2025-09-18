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
    return type.charAt(0).toUpperCase() + type.slice(1);
};
</script>

<template>
    <Head title="GeoCasa Bohol - Premium Real Estate in Paradise" />

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
                <div
                    class="absolute inset-0 bg-gradient-to-br from-primary-900/70 via-primary-800/50 to-accent-900/70"
                ></div>
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
                        Discover
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-accent-300 to-coconut-300"
                        >
                            Land
                        </span>
                        Opportunities in Bohol
                    </h1>

                    <p
                        class="text-xl md:text-2xl text-white/90 mb-12 max-w-4xl mx-auto text-balance drop-shadow-md"
                    >
                        Explore available properties across Bohol's diverse
                        locations. From coastal areas to inland developments, we
                        help connect buyers with land opportunities.
                    </p>

                    <!-- CTA Buttons -->
                    <div
                        class="flex flex-col sm:flex-row gap-6 justify-center mb-16"
                    >
                        <Link
                            :href="route('public.properties')"
                            class="bg-white text-primary-600 hover:bg-neutral-50 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 shadow-soft hover:shadow-soft-lg flex items-center justify-center gap-3"
                        >
                            <BuildingOfficeIcon class="w-5 h-5" />
                            Browse Properties
                        </Link>
                        <Link
                            :href="route('seller-requests.create')"
                            class="border-2 border-white text-white hover:bg-white hover:text-primary-600 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 flex items-center justify-center gap-3"
                        >
                            <ArrowRightIcon class="w-5 h-5" />
                            List Your Property
                        </Link>
                    </div>

                    <!-- Trust Indicators -->
                    <div
                        class="flex flex-wrap justify-center items-center gap-8 text-sm text-white/80"
                    >
                        <div class="flex items-center gap-2">
                            <CheckCircleIcon class="w-5 h-5 text-accent-300" />
                            <span>Licensed Brokers</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <CheckCircleIcon class="w-5 h-5 text-accent-300" />
                            <span>Verified Properties</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <CheckCircleIcon class="w-5 h-5 text-accent-300" />
                            <span>Secure Transactions</span>
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
                            class="w-16 h-16 bg-coconut-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-coconut-100 transition-colors"
                        >
                            <StarIcon class="w-8 h-8 text-coconut-600" />
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

        <!-- Top Performing Broker -->
        <section v-if="topBrokers.length > 0" class="py-20 bg-neutral-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2
                        class="text-3xl md:text-4xl font-bold text-neutral-900 mb-4"
                    >
                        Active Broker
                    </h2>
                    <p class="text-xl text-neutral-600">
                        Connect with real estate professionals on our platform
                    </p>
                </div>

                <div class="card max-w-5xl mx-auto overflow-hidden">
                    <div class="px-8 py-6 tropical-gradient">
                        <h3
                            class="text-2xl font-bold text-white flex items-center gap-3"
                        >
                            <div
                                class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center"
                            >
                                <StarIcon class="w-5 h-5 text-white" />
                            </div>
                            #1 Broker of the Month
                        </h3>
                    </div>

                    <div
                        v-if="topBrokers[0]"
                        class="p-8 bg-gradient-to-br from-primary-50/50 to-accent-50/50"
                    >
                        <div
                            class="flex flex-col lg:flex-row items-center lg:items-start gap-8"
                        >
                            <!-- Broker Info -->
                            <div class="flex items-center gap-6 flex-1">
                                <div
                                    class="w-24 h-24 bg-gradient-to-br from-primary-400 to-accent-500 rounded-3xl flex items-center justify-center shadow-soft-lg"
                                >
                                    <span class="text-4xl">🏆</span>
                                </div>

                                <div>
                                    <h4
                                        class="text-3xl font-bold text-neutral-900 mb-2"
                                    >
                                        {{ topBrokers[0].name }}
                                    </h4>
                                    <p class="text-lg text-neutral-600 mb-3">
                                        Licensed Real Estate Broker
                                    </p>
                                    <div
                                        class="flex items-center gap-2 text-primary-600"
                                    >
                                        <StarIcon
                                            class="w-5 h-5 fill-current"
                                        />
                                        <span class="font-semibold"
                                            >Top Performer</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Performance Stats -->
                            <div class="grid grid-cols-2 gap-6 lg:gap-8">
                                <div class="text-center">
                                    <div
                                        class="text-3xl font-bold text-primary-600 mb-1"
                                    >
                                        {{
                                            topBrokers[0].total_properties || 0
                                        }}
                                    </div>
                                    <div class="text-sm text-neutral-600">
                                        Properties Listed
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div
                                        class="text-3xl font-bold text-accent-600 mb-1"
                                    >
                                        {{
                                            topBrokers[0].total_transactions ||
                                            0
                                        }}
                                    </div>
                                    <div class="text-sm text-neutral-600">
                                        Transactions
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                    class="bg-gradient-to-r from-accent-500 to-accent-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-soft"
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
                            class="w-16 h-16 bg-coconut-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <CheckCircleIcon class="w-8 h-8 text-coconut-600" />
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
                            class="w-16 h-16 bg-coconut-50 rounded-2xl flex items-center justify-center mx-auto mb-6"
                        >
                            <ChartBarIcon class="w-8 h-8 text-coconut-600" />
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

        <!-- Call to Action -->
        <section class="py-20 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="card p-12 tropical-gradient text-white">
                    <h2 class="text-3xl md:text-4xl font-bold mb-6">
                        Ready to Find Your Paradise?
                    </h2>
                    <p class="text-xl mb-8 opacity-90">
                        Start your property search journey in Bohol with our
                        platform's assistance.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            :href="route('public.properties')"
                            class="bg-white text-primary-600 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-neutral-50 transition-colors shadow-soft"
                        >
                            Browse Properties
                        </Link>
                        <Link
                            :href="route('register')"
                            class="border-2 border-white text-white px-8 py-4 rounded-xl font-semibold text-lg hover:bg-white hover:text-primary-600 transition-colors"
                        >
                            Join as Broker
                        </Link>
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
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-primary-900/70 via-primary-600/30 to-transparent"
                            ></div>
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
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-accent-900/70 via-accent-600/30 to-transparent"
                            ></div>
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
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-coconut-900/70 via-coconut-600/30 to-transparent"
                            ></div>
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
