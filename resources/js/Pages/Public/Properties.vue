<script setup>
import { Link, Head, router } from "@inertiajs/vue3";
import { reactive, watch } from "vue";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import PublicFooter from "@/Components/PublicFooter.vue";
import ModernInput from "@/Components/ModernInput.vue";
import ModernButton from "@/Components/ModernButton.vue";
import Pagination from "@/Components/Pagination.vue";
import {
    MagnifyingGlassIcon,
    MapPinIcon,
    BuildingOfficeIcon,
    StarIcon,
    BoltIcon,
    BeakerIcon,
    UserIcon,
    EyeIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    auth: Object,
    properties: Object,
    types: Array,
    municipalities: Array,
    filters: Object,
});

const form = reactive({
    search: props.filters.search || "",
    type: props.filters.type || "",
    municipality: props.filters.municipality || "",
    min_price: props.filters.min_price || "",
    max_price: props.filters.max_price || "",
    utilities: props.filters.utilities || false,
    featured: props.filters.featured || false,
});

const search = () => {
    router.get(route("public.properties"), form, {
        preserveState: true,
        replace: true,
    });
};

const formatPropertyType = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1);
};

// Auto-search when filters change
watch(
    form,
    () => {
        search();
    },
    { deep: true }
);
</script>

<template>
    <Head title="Properties - GeoCasa Bohol" />

    <div
        class="min-h-screen bg-gradient-to-br from-primary-50/30 via-white to-accent-50/30"
    >
        <!-- Navigation -->
        <PublicNavigation :auth="auth" current-route="public.properties" />

        <!-- Hero Section -->
        <section class="relative py-20 overflow-hidden">
            <!-- Background Elements -->
            <div
                class="absolute inset-0 bg-gradient-to-br from-primary-50 via-white to-accent-50"
            ></div>
            <div
                class="absolute top-10 left-10 w-64 h-64 bg-primary-200/20 rounded-full blur-3xl"
            ></div>
            <div
                class="absolute bottom-10 right-10 w-80 h-80 bg-accent-200/20 rounded-full blur-3xl"
            ></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h1
                        class="text-4xl md:text-6xl font-bold text-neutral-900 mb-6"
                    >
                        Discover Your Perfect
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-accent-600"
                        >
                            Property
                        </span>
                    </h1>
                    <p class="text-xl text-neutral-600 max-w-3xl mx-auto">
                        Browse through {{ properties.total }} premium properties
                        across Bohol's most desirable locations
                    </p>
                </div>

                <!-- Search & Filters -->
                <div class="card p-8 max-w-6xl mx-auto">
                    <form @submit.prevent="search" class="space-y-6">
                        <!-- Main Search -->
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <ModernInput
                                    v-model="form.search"
                                    type="text"
                                    placeholder="Search by title, location, or description..."
                                    class="text-lg"
                                >
                                    <template #icon>
                                        <MagnifyingGlassIcon class="w-5 h-5" />
                                    </template>
                                </ModernInput>
                            </div>
                            <ModernButton
                                type="submit"
                                class="px-8 py-4 text-lg"
                            >
                                <MagnifyingGlassIcon class="w-5 h-5" />
                                Search
                            </ModernButton>
                        </div>

                        <!-- Advanced Filters -->
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                        >
                            <select v-model="form.type" class="modern-select">
                                <option value="">All Property Types</option>
                                <option
                                    v-for="type in types"
                                    :key="type"
                                    :value="type"
                                >
                                    {{ formatPropertyType(type) }}
                                </option>
                            </select>

                            <select
                                v-model="form.municipality"
                                class="modern-select"
                            >
                                <option value="">All Municipalities</option>
                                <option
                                    v-for="municipality in municipalities"
                                    :key="municipality"
                                    :value="municipality"
                                >
                                    {{ municipality }}
                                </option>
                            </select>

                            <div class="flex gap-2">
                                <ModernInput
                                    v-model="form.min_price"
                                    type="number"
                                    placeholder="Min Price"
                                    class="flex-1"
                                />
                                <ModernInput
                                    v-model="form.max_price"
                                    type="number"
                                    placeholder="Max Price"
                                    class="flex-1"
                                />
                            </div>

                            <div class="flex items-center justify-center gap-6">
                                <label
                                    class="flex items-center gap-2 cursor-pointer"
                                >
                                    <input
                                        v-model="form.utilities"
                                        type="checkbox"
                                        class="modern-checkbox"
                                    />
                                    <span
                                        class="text-sm font-medium text-neutral-700"
                                        >Utilities</span
                                    >
                                </label>
                                <label
                                    class="flex items-center gap-2 cursor-pointer"
                                >
                                    <input
                                        v-model="form.featured"
                                        type="checkbox"
                                        class="modern-checkbox"
                                    />
                                    <span
                                        class="text-sm font-medium text-neutral-700"
                                        >Featured</span
                                    >
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Properties Grid -->
        <section class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold text-neutral-900">
                        Available Properties
                        <span class="text-neutral-500 text-lg font-normal">
                            ({{ properties.total }} found)
                        </span>
                    </h2>
                </div>

                <div
                    v-if="properties.data.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
                >
                    <div
                        v-for="property in properties.data"
                        :key="property.id"
                        class="card overflow-hidden group hover:shadow-soft-xl transition-all duration-300 hover:-translate-y-1"
                    >
                        <!-- Property Image -->
                        <div class="relative h-56 overflow-hidden">
                            <img
                                :src="property.main_image"
                                :alt="property.title"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />

                            <!-- Badges -->
                            <div
                                class="absolute top-4 left-4 flex flex-col gap-2"
                            >
                                <div
                                    v-if="property.is_featured"
                                    class="bg-gradient-to-r from-accent-500 to-accent-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-soft"
                                >
                                    ⭐ Featured
                                </div>
                                <div
                                    class="bg-primary-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-soft"
                                >
                                    {{ formatPropertyType(property.type) }}
                                </div>
                            </div>
                        </div>

                        <!-- Property Details -->
                        <div class="p-6">
                            <h3
                                class="text-xl font-bold text-neutral-900 mb-2 line-clamp-2"
                            >
                                {{ property.title }}
                            </h3>

                            <div
                                class="flex items-center gap-2 text-neutral-600 mb-4"
                            >
                                <MapPinIcon class="w-4 h-4 flex-shrink-0" />
                                <span class="text-sm line-clamp-1">{{
                                    property.full_address
                                }}</span>
                            </div>

                            <div class="flex justify-between items-center mb-4">
                                <div
                                    class="text-3xl font-bold text-primary-600"
                                >
                                    {{ property.formatted_total_price }}
                                </div>
                                <div
                                    class="text-sm text-neutral-500 bg-neutral-100 px-3 py-1 rounded-full"
                                >
                                    {{ property.formatted_area }}
                                </div>
                            </div>

                            <div class="flex justify-between items-center mb-4">
                                <div
                                    class="text-lg font-semibold text-accent-600"
                                >
                                    {{ property.formatted_price_per_sqm }}/sqm
                                </div>
                                <div
                                    class="text-sm text-neutral-600 font-medium"
                                >
                                    {{ property.municipality }}
                                </div>
                            </div>

                            <!-- Utilities -->
                            <div class="flex items-center gap-4 mb-6">
                                <div class="flex items-center gap-1">
                                    <BoltIcon class="w-4 h-4 text-accent-500" />
                                    <span class="text-xs text-neutral-600">
                                        {{
                                            property.has_electricity
                                                ? "Electricity"
                                                : "No Power"
                                        }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <BeakerIcon
                                        class="w-4 h-4 text-primary-500"
                                    />
                                    <span class="text-xs text-neutral-600">
                                        {{
                                            property.has_water
                                                ? "Water"
                                                : "No Water"
                                        }}
                                    </span>
                                </div>
                            </div>

                            <!-- Broker Info -->
                            <div
                                class="flex items-center justify-between pt-4 border-t border-neutral-100"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 bg-gradient-to-br from-primary-400 to-accent-500 rounded-full flex items-center justify-center"
                                    >
                                        <UserIcon class="w-4 h-4 text-white" />
                                    </div>
                                    <div>
                                        <div
                                            class="text-sm font-semibold text-neutral-900"
                                        >
                                            {{
                                                property.broker?.name ||
                                                "GeoCasa Bohol"
                                            }}
                                        </div>
                                        <div class="text-xs text-neutral-500">
                                            Licensed Broker
                                        </div>
                                    </div>
                                </div>

                                <Link
                                    :href="
                                        route(
                                            'public.properties.show',
                                            property.slug
                                        )
                                    "
                                    class="btn-primary-sm flex items-center gap-2"
                                >
                                    <EyeIcon class="w-4 h-4" />
                                    View Details
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Properties Found -->
                <div v-else class="text-center py-16">
                    <div
                        class="w-24 h-24 bg-neutral-100 rounded-3xl flex items-center justify-center mx-auto mb-6"
                    >
                        <BuildingOfficeIcon
                            class="w-12 h-12 text-neutral-400"
                        />
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900 mb-4">
                        No Properties Found
                    </h3>
                    <p class="text-neutral-600 mb-8 max-w-md mx-auto">
                        We couldn't find any properties matching your criteria.
                        Try adjusting your filters or search terms.
                    </p>
                    <ModernButton
                        @click="
                            form = {
                                search: '',
                                type: '',
                                municipality: '',
                                min_price: '',
                                max_price: '',
                                utilities: false,
                                featured: false,
                            }
                        "
                    >
                        Clear All Filters
                    </ModernButton>
                </div>

                <!-- Pagination -->
                <div v-if="properties.data.length > 0" class="mt-12">
                    <Pagination :links="properties.links" />
                </div>
            </div>
        </section>

        <!-- Footer -->
        <PublicFooter />
    </div>
</template>
