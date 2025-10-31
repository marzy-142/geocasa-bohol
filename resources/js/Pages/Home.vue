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
    if (clean.startsWith("http://") || clean.startsWith("https://"))
        return clean;
    if (clean.startsWith("/storage/")) return clean;

    // normalize to avoid double slashes
    clean = clean.replace(/^\/+/, "");

    if (
        clean.includes("properties/virtual-tours/") ||
        clean.includes("properties/images/")
    ) {
        return `/storage/${clean}`;
    }

    // default to images folder; most main_image filenames are bare names
    if (
        isVirtualTour ||
        clean.toLowerCase().includes("virtual") ||
        clean.toLowerCase().includes("tour")
    ) {
        return `/storage/properties/virtual-tours/${clean}`;
    }
    return `/storage/properties/images/${clean}`;
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

        <!-- Top Performing Brokers Section -->
        <section class="py-16 bg-white" v-if="topBrokers && topBrokers.length">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <div
                        class="inline-flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-full mb-3"
                    >
                        <TrophyIcon class="w-6 h-6 text-yellow-600" />
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-neutral-900">
                        Top Performing Land Brokers
                    </h2>
                    <p class="text-neutral-600">
                        Meet our most trusted PRC-licensed land brokers
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <div
                        v-for="(broker, index) in topBrokers.slice(0, 3)"
                        :key="broker.id"
                        class="relative bg-white border border-neutral-200 rounded-xl shadow-sm hover:shadow-md transition p-6"
                    >
                        <div
                            class="absolute top-4 right-4 px-2 py-0.5 text-xs font-semibold rounded-full text-white"
                            :class="{
                                'bg-yellow-500': index === 0,
                                'bg-gray-400': index === 1,
                                'bg-orange-500': index === 2,
                            }"
                        >
                            #{{ index + 1 }}
                        </div>

                        <div class="flex flex-col items-center text-center">
                            <div class="relative mb-4">
                                <UserAvatar
                                    v-if="broker"
                                    :user="broker"
                                    size="2xl"
                                    bg-color="primary"
                                    class="w-24 h-24 border-4 border-white shadow ring-2"
                                    :class="{
                                        'ring-yellow-400/40': index === 0,
                                        'ring-gray-400/40': index === 1,
                                        'ring-orange-400/40': index === 2,
                                    }"
                                />
                                <div
                                    class="absolute -bottom-2 -right-2 bg-green-500 rounded-full p-1.5 shadow border-2 border-white"
                                >
                                    <CheckBadgeIcon
                                        class="w-4 h-4 text-white"
                                    />
                                </div>
                            </div>

                            <h3 class="text-lg font-bold text-neutral-900">
                                {{ broker.name }}
                            </h3>
                            <p
                                class="text-sm text-neutral-600 mb-4"
                                v-if="broker.brokerage_firm_name"
                            >
                                {{ broker.brokerage_firm_name }}
                            </p>

                            <div class="w-full space-y-2 mb-5">
                                <div
                                    class="flex items-center justify-between p-2 bg-neutral-50 rounded-md border border-neutral-200"
                                >
                                    <span class="text-xs text-neutral-600"
                                        >Land Sales</span
                                    >
                                    <span
                                        class="text-base font-semibold text-neutral-900"
                                        >{{
                                            broker.finalized_transactions_count ||
                                            broker.total_sales ||
                                            0
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex items-center justify-between p-2 bg-neutral-50 rounded-md border border-neutral-200"
                                >
                                    <span class="text-xs text-neutral-600"
                                        >Active Land Listings</span
                                    >
                                    <span
                                        class="text-base font-semibold text-neutral-900"
                                        >{{ broker.active_listings || 0 }}</span
                                    >
                                </div>
                            </div>

                            <Link
                                :href="route('brokers.show', broker.id)"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-neutral-300 text-neutral-800 hover:bg-neutral-50 transition"
                            >
                                <span>View Profile</span>
                                <ArrowRightIcon class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-8">
                    <Link
                        :href="route('brokers.index')"
                        class="inline-flex items-center gap-2 text-primary-600 hover:text-primary-700 font-medium"
                    >
                        View All Brokers
                        <ArrowRightIcon class="w-5 h-5" />
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
                    <Link
                        :href="route('public.properties')"
                        class="inline-flex items-center gap-2 text-primary-600 hover:text-primary-700 font-medium"
                    >
                        <FunnelIcon class="w-4 h-4" />
                        Advanced Filters
                    </Link>
                </div>

                <!-- Quick Filters -->
                <div class="mb-6 flex flex-col sm:flex-row gap-3">
                    <div class="flex-1">
                        <label
                            class="block text-xs font-medium text-neutral-700 mb-2"
                            >Price Range</label
                        >
                        <div class="flex flex-wrap gap-2">
                            <button
                                @click="selectedPriceRange = 'all'"
                                :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                                    selectedPriceRange === 'all'
                                        ? 'bg-primary-600 text-white'
                                        : 'bg-white text-neutral-700 border border-neutral-300 hover:bg-neutral-50',
                                ]"
                            >
                                All Prices
                            </button>
                            <button
                                @click="selectedPriceRange = 'under-1m'"
                                :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                                    selectedPriceRange === 'under-1m'
                                        ? 'bg-primary-600 text-white'
                                        : 'bg-white text-neutral-700 border border-neutral-300 hover:bg-neutral-50',
                                ]"
                            >
                                Under ₱1M
                            </button>
                            <button
                                @click="selectedPriceRange = '1m-5m'"
                                :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                                    selectedPriceRange === '1m-5m'
                                        ? 'bg-primary-600 text-white'
                                        : 'bg-white text-neutral-700 border border-neutral-300 hover:bg-neutral-50',
                                ]"
                            >
                                ₱1M - ₱5M
                            </button>
                            <button
                                @click="selectedPriceRange = '5m-10m'"
                                :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                                    selectedPriceRange === '5m-10m'
                                        ? 'bg-primary-600 text-white'
                                        : 'bg-white text-neutral-700 border border-neutral-300 hover:bg-neutral-50',
                                ]"
                            >
                                ₱5M - ₱10M
                            </button>
                            <button
                                @click="selectedPriceRange = 'over-10m'"
                                :class="[
                                    'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                                    selectedPriceRange === 'over-10m'
                                        ? 'bg-primary-600 text-white'
                                        : 'bg-white text-neutral-700 border border-neutral-300 hover:bg-neutral-50',
                                ]"
                            >
                                Over ₱10M
                            </button>
                        </div>
                    </div>

                    <div class="sm:w-48">
                        <label
                            class="block text-xs font-medium text-neutral-700 mb-2"
                            >Sort By</label
                        >
                        <select
                            v-model="selectedSort"
                            class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        >
                            <option value="featured">Featured</option>
                            <option value="newest">Newest First</option>
                            <option value="price-low">
                                Price: Low to High
                            </option>
                            <option value="price-high">
                                Price: High to Low
                            </option>
                        </select>
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
                    v-if="
                        filteredProperties.length > 0 &&
                        featuredProperties.length > 6
                    "
                    class="text-center mt-8"
                >
                    <Link
                        :href="route('public.properties')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-white border-2 border-primary-600 text-primary-600 rounded-lg hover:bg-primary-50 transition font-medium"
                    >
                        View All {{ featuredProperties.length }} Properties
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
