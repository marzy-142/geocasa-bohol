<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import PublicFooter from "@/Components/PublicFooter.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import { ref, computed } from "vue";

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
    TrophyIcon,
    CheckBadgeIcon,
    FunnelIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
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

// Featured properties filter state
const selectedPriceRange = ref("all");
const selectedSort = ref("featured");

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

// Filter and sort featured properties
const filteredProperties = computed(() => {
    if (!props.featuredProperties) return [];

    let filtered = [...props.featuredProperties];

    // Apply price range filter
    if (selectedPriceRange.value !== "all") {
        filtered = filtered.filter((prop) => {
            const price = prop.total_price || 0;
            switch (selectedPriceRange.value) {
                case "under-1m":
                    return price < 1000000;
                case "1m-5m":
                    return price >= 1000000 && price < 5000000;
                case "5m-10m":
                    return price >= 5000000 && price < 10000000;
                case "over-10m":
                    return price >= 10000000;
                default:
                    return true;
            }
        });
    }

    // Apply sorting
    if (selectedSort.value === "price-low") {
        filtered.sort((a, b) => (a.total_price || 0) - (b.total_price || 0));
    } else if (selectedSort.value === "price-high") {
        filtered.sort((a, b) => (b.total_price || 0) - (a.total_price || 0));
    } else if (selectedSort.value === "newest") {
        filtered.sort(
            (a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0)
        );
    }

    return filtered.slice(0, 6);
});

// Robust image resolver (shared logic adapted from Properties pages)
const placeholderImg =
    "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=1200&q=60";

const getImageUrl = (image, isVirtualTour = false) => {
    if (!image) return placeholderImg;

    if (Array.isArray(image)) {
        const first = image
            .flat(2)
            .find((img) => img && typeof img === "string" && img.trim() !== "");
        return getImageUrl(first, isVirtualTour);
    }

    if (typeof image !== "string") return placeholderImg;

    let clean = image.trim();
    if (!clean) return placeholderImg;

    // If already a full URL or data URI, return as-is
    if (
        clean.startsWith("http://") ||
        clean.startsWith("https://") ||
        clean.startsWith("data:")
    ) {
        return clean;
    }

    // If already starts with /storage/, return as-is
    if (clean.startsWith("/storage/")) {
        return clean;
    }

    // For any other case, prepend /storage/ (legacy support)
    clean = clean.replace(/^\/+/, "");
    return `/storage/${clean}`;
};
</script>

<template>
    <Head title="GeoCasa Bohol - Premium Real Estate in Paradise">
        <meta
            name="description"
            content="Verified listings, licensed brokers, and a simple path to property ownership in Bohol."
        />
        <meta
            property="og:title"
            content="GeoCasa Bohol - Premium Real Estate"
        />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://geocasabohol.com" />
        <meta
            property="og:image"
            content="https://images.unsplash.com/photo-1500382017468-9049fed747ef?ixlib=rb-4.0.3"
        />
    </Head>

    <div class="min-h-screen bg-white">
        <PublicNavigation :auth="auth" />

        <!-- Hero: simplified -->
        <section
            class="relative min-h-[70vh] flex items-center overflow-hidden"
        >
            <div class="absolute inset-0">
                <img
                    src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?ixlib=rb-4.0.3&auto=format&fit=crop&w=2400&q=70"
                    alt="Bohol beachfront and land properties"
                    class="w-full h-full object-cover"
                />
                <div class="absolute inset-0 bg-black/45"></div>
            </div>

            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <h1
                    class="text-4xl md:text-6xl font-bold text-white leading-tight"
                >
                    Find prime land for sale in Bohol
                </h1>
                <p class="mt-4 text-lg md:text-xl text-white/90 max-w-3xl">
                    Buy or sell land with verified listings, licensed brokers,
                    and complete transparency.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <Link
                        :href="route('public.properties')"
                        class="bg-accent-600 text-white hover:bg-accent-700 px-7 py-3 rounded-lg font-semibold flex items-center gap-2"
                    >
                        <BuildingOfficeIcon class="w-5 h-5" /> Browse land
                        listings
                    </Link>
                    <Link
                        :href="route('seller-requests.create')"
                        class="bg-white/90 text-primary-700 hover:bg-white px-7 py-3 rounded-lg font-semibold border border-white/20"
                    >
                        Sell your land
                    </Link>
                </div>

                <div class="mt-6 flex flex-wrap gap-4 text-white/90 text-sm">
                    <div class="flex items-center gap-2">
                        <CheckCircleIcon class="w-4 h-4 text-accent-300" />
                        Verified land titles
                    </div>
                    <div class="flex items-center gap-2">
                        <CheckCircleIcon class="w-4 h-4 text-accent-300" />
                        Licensed land brokers
                    </div>
                    <div class="flex items-center gap-2">
                        <CheckCircleIcon class="w-4 h-4 text-accent-300" />
                        Bohol expertise
                    </div>
                </div>
            </div>
        </section>

        <!-- Top Performing Broker Section -->
        <section class="py-20 bg-white" v-if="topBrokers && topBrokers.length">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <div class="inline-block mb-3">
                        <span
                            class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-50 border border-yellow-200 rounded-full text-yellow-800 font-medium"
                        >
                            <TrophyIcon class="w-5 h-5" />
                            Top Performer
                        </span>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-bold text-neutral-900">
                        This Month's Leading Broker
                    </h2>
                </div>

                <!-- Unique Card Design -->
                <div class="max-w-4xl mx-auto">
                    <div
                        class="bg-neutral-900 rounded-3xl overflow-hidden shadow-2xl"
                    >
                        <div class="grid md:grid-cols-2 gap-0">
                            <!-- Left: Photo Section -->
                            <div
                                class="relative bg-gradient-to-br from-yellow-400 via-yellow-500 to-orange-500 p-8 md:p-12 flex items-center justify-center"
                            >
                                <div class="relative">
                                    <!-- Large Avatar -->
                                    <div class="relative">
                                        <UserAvatar
                                            v-if="topBrokers[0]"
                                            :user="topBrokers[0]"
                                            size="2xl"
                                            bg-color="primary"
                                            class="w-56 h-56 md:w-64 md:h-64 border-8 border-white/20 shadow-2xl backdrop-blur"
                                        />
                                        <!-- Verified Badge -->
                                        <div
                                            class="absolute -bottom-4 -right-4 bg-white rounded-2xl p-3 shadow-xl"
                                        >
                                            <CheckBadgeIcon
                                                class="w-8 h-8 text-green-500"
                                            />
                                        </div>
                                    </div>

                                    <!-- Decorative Elements -->
                                    <div
                                        class="absolute -top-6 -left-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"
                                    ></div>
                                    <div
                                        class="absolute -bottom-6 -right-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"
                                    ></div>
                                </div>
                            </div>

                            <!-- Right: Info Section -->
                            <div
                                class="p-8 md:p-12 bg-white flex flex-col justify-center"
                            >
                                <!-- Rank Badge -->
                                <div class="mb-6">
                                    <span
                                        class="inline-block px-6 py-2 bg-yellow-400 text-neutral-900 font-bold rounded-full text-sm tracking-wide"
                                    >
                                        #1 RANKED
                                    </span>
                                </div>

                                <!-- Name & Firm -->
                                <h3
                                    class="text-3xl font-bold text-neutral-900 mb-2"
                                >
                                    {{ topBrokers[0].name }}
                                </h3>
                                <p
                                    class="text-neutral-600 mb-8 text-lg"
                                    v-if="topBrokers[0].brokerage_firm_name"
                                >
                                    {{ topBrokers[0].brokerage_firm_name }}
                                </p>

                                <!-- Stats -->
                                <div class="grid grid-cols-2 gap-4 mb-8">
                                    <div
                                        class="border-l-4 border-yellow-400 pl-4"
                                    >
                                        <div
                                            class="text-4xl font-bold text-neutral-900 mb-1"
                                        >
                                            {{
                                                topBrokers[0]
                                                    .finalized_transactions_count ||
                                                topBrokers[0].total_sales ||
                                                0
                                            }}
                                        </div>
                                        <div
                                            class="text-sm text-neutral-600 font-medium"
                                        >
                                            Land Sales
                                        </div>
                                    </div>
                                    <div
                                        class="border-l-4 border-primary-500 pl-4"
                                    >
                                        <div
                                            class="text-4xl font-bold text-neutral-900 mb-1"
                                        >
                                            {{
                                                topBrokers[0].active_listings ||
                                                0
                                            }}
                                        </div>
                                        <div
                                            class="text-sm text-neutral-600 font-medium"
                                        >
                                            Active Listings
                                        </div>
                                    </div>
                                </div>

                                <!-- CTA -->
                                <Link
                                    :href="
                                        route('brokers.show', topBrokers[0].id)
                                    "
                                    class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-neutral-900 text-white font-semibold rounded-xl hover:bg-neutral-800 transition-all group"
                                >
                                    <span>View Full Profile</span>
                                    <ArrowRightIcon
                                        class="w-5 h-5 group-hover:translate-x-1 transition-transform"
                                    />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Link to All Brokers -->
                <div class="text-center mt-12">
                    <Link
                        :href="route('brokers.index')"
                        class="inline-flex items-center gap-2 text-neutral-600 hover:text-neutral-900 font-medium text-base group"
                    >
                        <span>Explore all our brokers</span>
                        <ArrowRightIcon
                            class="w-4 h-4 group-hover:translate-x-1 transition-transform"
                        />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Featured properties: compact -->
        <section class="py-14 bg-neutral-50" v-if="featuredProperties.length">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4"
                >
                    <div>
                        <h2 class="text-2xl font-bold text-neutral-900">
                            Featured land for sale
                        </h2>
                        <p class="text-sm text-neutral-600 mt-1">
                            {{ filteredProperties.length }} of
                            {{ featuredProperties.length }} properties
                        </p>
                    </div>
                </div>

                <!-- Property Grid -->
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <div
                        v-for="prop in filteredProperties"
                        :key="prop.id"
                        class="card overflow-hidden group"
                    >
                        <div class="relative h-44 overflow-hidden">
                            <img
                                :src="
                                    getImageUrl(
                                        prop.main_image ||
                                            (Array.isArray(prop.images)
                                                ? prop.images[0]
                                                : prop.images
                                                ? JSON.parse(
                                                      prop.images || '[]'
                                                  )[0]
                                                : null)
                                    )
                                "
                                :alt="prop.title || 'Property image'"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div class="absolute inset-0 bg-black/10"></div>
                            <div
                                class="absolute top-3 left-3 bg-white/90 text-xs font-semibold px-2 py-1 rounded"
                            >
                                {{ formatPropertyType(prop.type) }}
                            </div>
                        </div>
                        <div class="p-4">
                            <h3
                                class="text-base font-semibold text-neutral-900 line-clamp-1"
                            >
                                {{ prop.title }}
                            </h3>
                            <p
                                class="text-xs text-neutral-600 mt-1 flex items-center gap-1"
                            >
                                <MapPinIcon class="w-4 h-4" />
                                {{
                                    [prop.barangay, prop.municipality]
                                        .filter(Boolean)
                                        .join(", ")
                                }}
                            </p>
                            <div class="mt-3 flex items-center justify-between">
                                <div class="text-primary-700 font-bold text-lg">
                                    {{ formatCurrency(prop.total_price || 0) }}
                                </div>
                                <Link
                                    :href="
                                        route(
                                            'public.properties.show',
                                            prop.slug
                                        )
                                    "
                                    class="text-primary-600 hover:text-primary-700 text-sm font-medium"
                                    >View</Link
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Results Message -->
                <div
                    v-if="filteredProperties.length === 0"
                    class="text-center py-12"
                >
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 bg-neutral-100 rounded-full mb-4"
                    >
                        <FunnelIcon class="w-8 h-8 text-neutral-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-neutral-900 mb-2">
                        No land found
                    </h3>
                    <p class="text-neutral-600 mb-4">
                        Try adjusting your filters or browse all listings
                    </p>
                    <Link
                        :href="route('public.properties')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition"
                    >
                        Browse All Land
                        <ArrowRightIcon class="w-4 h-4" />
                    </Link>
                </div>

                <!-- View All CTA -->
                <div
                    v-if="filteredProperties.length > 0"
                    class="text-center mt-8"
                >
                    <Link
                        :href="route('public.properties')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-semibold shadow-md hover:shadow-lg"
                    >
                        View All Properties
                        <ArrowRightIcon class="w-5 h-5" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="py-16 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-neutral-900">
                    Start in minutes
                </h2>
                <p class="text-neutral-600 mt-2">
                    Browse land listings or list your land—our team will guide
                    you end-to-end.
                </p>
                <div
                    class="mt-6 flex flex-col sm:flex-row gap-3 justify-center"
                >
                    <Link
                        :href="route('public.properties')"
                        class="btn-primary px-8 py-4 text-lg"
                        >Browse Land</Link
                    >
                    <Link
                        :href="route('seller-requests.create')"
                        class="btn-secondary px-8 py-4 text-lg"
                        >Sell Your Land</Link
                    >
                </div>
            </div>
        </section>

        <PublicFooter />
    </div>
</template>

<style scoped></style>
