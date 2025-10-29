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
                    Find verified property in Bohol
                </h1>
                <p class="mt-4 text-lg md:text-xl text-white/90 max-w-3xl">
                    Licensed local brokers. Transparent process. Real properties
                    you can trust.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <Link
                        :href="route('public.properties')"
                        class="bg-accent-600 text-white hover:bg-accent-700 px-7 py-3 rounded-lg font-semibold flex items-center gap-2"
                    >
                        <BuildingOfficeIcon class="w-5 h-5" /> Browse properties
                    </Link>
                    <Link
                        :href="route('seller-requests.create')"
                        class="bg-white/90 text-primary-700 hover:bg-white px-7 py-3 rounded-lg font-semibold border border-white/20"
                    >
                        List your property
                    </Link>
                </div>

                <div class="mt-6 flex flex-wrap gap-4 text-white/90 text-sm">
                    <div class="flex items-center gap-2">
                        <CheckCircleIcon class="w-4 h-4 text-accent-300" />
                        Verified listings
                    </div>
                    <div class="flex items-center gap-2">
                        <CheckCircleIcon class="w-4 h-4 text-accent-300" />
                        Licensed brokers
                    </div>
                    <div class="flex items-center gap-2">
                        <CheckCircleIcon class="w-4 h-4 text-accent-300" />
                        Local expertise
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick benefits: 3 items -->
        <section class="py-14 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="card p-6">
                        <div
                            class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center mb-4"
                        >
                            <MapPinIcon class="w-6 h-6 text-primary-600" />
                        </div>
                        <h3 class="font-semibold text-neutral-900">
                            Prime locations
                        </h3>
                        <p class="text-neutral-600 mt-1">
                            Beachfront, mountain view, and urban lots across
                            Bohol.
                        </p>
                    </div>
                    <div class="card p-6">
                        <div
                            class="w-10 h-10 rounded-xl bg-accent-50 flex items-center justify-center mb-4"
                        >
                            <CheckCircleIcon class="w-6 h-6 text-accent-600" />
                        </div>
                        <h3 class="font-semibold text-neutral-900">
                            Verified process
                        </h3>
                        <p class="text-neutral-600 mt-1">
                            Title checks, due diligence, and guided
                            transactions.
                        </p>
                    </div>
                    <div class="card p-6">
                        <div
                            class="w-10 h-10 rounded-xl bg-warning-50 flex items-center justify-center mb-4"
                        >
                            <UserGroupIcon class="w-6 h-6 text-warning-600" />
                        </div>
                        <h3 class="font-semibold text-neutral-900">
                            Local experts
                        </h3>
                        <p class="text-neutral-600 mt-1">
                            Work with PRC-licensed brokers who know the market.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured properties: compact -->
        <section class="py-14 bg-neutral-50" v-if="featuredProperties.length">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-neutral-900">
                        Featured properties
                    </h2>
                    <Link
                        :href="route('public.properties')"
                        class="text-primary-600 hover:text-primary-700 font-medium"
                        >See all</Link
                    >
                </div>

                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <div
                        v-for="prop in featuredProperties.slice(0, 3)"
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
            </div>
        </section>

        <!-- Final CTA -->
        <section class="py-16 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-neutral-900">
                    Start in minutes
                </h2>
                <p class="text-neutral-600 mt-2">
                    Browse listings or list your property—our team will guide
                    you end-to-end.
                </p>
                <div
                    class="mt-6 flex flex-col sm:flex-row gap-3 justify-center"
                >
                    <Link
                        :href="route('public.properties')"
                        class="btn-primary px-8 py-4 text-lg"
                        >Browse Properties</Link
                    >
                    <Link
                        :href="route('seller-requests.create')"
                        class="btn-secondary px-8 py-4 text-lg"
                        >List Your Property</Link
                    >
                </div>
            </div>
        </section>

        <PublicFooter />
    </div>
</template>
