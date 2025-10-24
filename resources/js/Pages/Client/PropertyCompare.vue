<template>
    <Head title="Compare Properties - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-neutral-900">Compare Properties</h1>
                    <p class="text-neutral-600 mt-1">
                        Side-by-side comparison of {{ properties.length }} properties
                    </p>
                </div>
                <Link
                    :href="route('client.properties')"
                    class="flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium"
                >
                    <ArrowLeftIcon class="w-4 h-4" />
                    Back to Properties
                </Link>
            </div>
        </div>

        <!-- Comparison Table -->
        <div v-if="properties.length > 0" class="bg-white border border-neutral-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-neutral-50 border-b border-neutral-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-neutral-900 w-48 sticky left-0 bg-neutral-50 z-10">
                                Feature
                            </th>
                            <th
                                v-for="property in properties"
                                :key="property.id"
                                class="px-6 py-4 text-center text-sm font-semibold text-neutral-900 min-w-[300px]"
                            >
                                <div class="space-y-2">
                                    <div class="aspect-video overflow-hidden rounded-lg bg-neutral-100">
                                        <img
                                            v-if="property.main_image"
                                            :src="property.main_image"
                                            :alt="property.title"
                                            class="w-full h-full object-cover"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <BuildingOfficeIcon class="w-12 h-12 text-neutral-300" />
                                        </div>
                                    </div>
                                    <h3 class="font-semibold text-neutral-900 line-clamp-2">
                                        {{ property.title }}
                                    </h3>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200">
                        <!-- Price -->
                        <tr class="hover:bg-neutral-50">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 sticky left-0 bg-white z-10">
                                Total Price
                            </td>
                            <td
                                v-for="property in properties"
                                :key="`price-${property.id}`"
                                class="px-6 py-4 text-center"
                            >
                                <div class="text-lg font-bold text-blue-600">
                                    {{ formatCurrency(property.total_price) }}
                                </div>
                            </td>
                        </tr>

                        <!-- Price per sqm -->
                        <tr class="hover:bg-neutral-50">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 sticky left-0 bg-white z-10">
                                Price per sqm
                            </td>
                            <td
                                v-for="property in properties"
                                :key="`price-sqm-${property.id}`"
                                class="px-6 py-4 text-center text-sm text-neutral-700"
                            >
                                {{ formatCurrency(property.price_per_sqm) }}/sqm
                            </td>
                        </tr>

                        <!-- Area -->
                        <tr class="hover:bg-neutral-50">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 sticky left-0 bg-white z-10">
                                Lot Area
                            </td>
                            <td
                                v-for="property in properties"
                                :key="`area-${property.id}`"
                                class="px-6 py-4 text-center text-sm text-neutral-700"
                            >
                                {{ property.area }} sqm
                            </td>
                        </tr>

                        <!-- Property Type -->
                        <tr class="hover:bg-neutral-50">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 sticky left-0 bg-white z-10">
                                Property Type
                            </td>
                            <td
                                v-for="property in properties"
                                :key="`type-${property.id}`"
                                class="px-6 py-4 text-center text-sm text-neutral-700"
                            >
                                {{ formatPropertyType(property.type) }}
                            </td>
                        </tr>

                        <!-- Location -->
                        <tr class="hover:bg-neutral-50">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 sticky left-0 bg-white z-10">
                                Location
                            </td>
                            <td
                                v-for="property in properties"
                                :key="`location-${property.id}`"
                                class="px-6 py-4 text-center text-sm text-neutral-700"
                            >
                                {{ property.municipality }}, {{ property.province }}
                            </td>
                        </tr>

                        <!-- Status -->
                        <tr class="hover:bg-neutral-50">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 sticky left-0 bg-white z-10">
                                Status
                            </td>
                            <td
                                v-for="property in properties"
                                :key="`status-${property.id}`"
                                class="px-6 py-4 text-center"
                            >
                                <span :class="getStatusClass(property.status)" class="inline-flex px-3 py-1 rounded-full text-xs font-semibold">
                                    {{ property.status }}
                                </span>
                            </td>
                        </tr>

                        <!-- Featured -->
                        <tr class="hover:bg-neutral-50">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 sticky left-0 bg-white z-10">
                                Featured
                            </td>
                            <td
                                v-for="property in properties"
                                :key="`featured-${property.id}`"
                                class="px-6 py-4 text-center"
                            >
                                <span v-if="property.is_featured" class="text-yellow-600">
                                    <StarIcon class="w-5 h-5 inline fill-current" />
                                </span>
                                <span v-else class="text-neutral-400">—</span>
                            </td>
                        </tr>

                        <!-- Actions -->
                        <tr class="bg-neutral-50">
                            <td class="px-6 py-4 text-sm font-medium text-neutral-900 sticky left-0 bg-neutral-50 z-10">
                                Actions
                            </td>
                            <td
                                v-for="property in properties"
                                :key="`actions-${property.id}`"
                                class="px-6 py-4 text-center"
                            >
                                <div class="flex flex-col gap-2">
                                    <Link
                                        :href="route('public.properties.show', property.id)"
                                        class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors"
                                    >
                                        View Details
                                    </Link>
                                    <button
                                        @click="toggleSave(property.id)"
                                        :class="[
                                            isSaved(property.id)
                                                ? 'bg-red-50 text-red-600 border-red-200'
                                                : 'bg-white text-neutral-600 border-neutral-300',
                                            'border px-4 py-2 rounded-lg text-sm font-medium hover:shadow-md transition-all'
                                        ]"
                                    >
                                        <HeartIcon :class="[isSaved(property.id) ? 'fill-current' : '', 'w-4 h-4 inline mr-1']" />
                                        {{ isSaved(property.id) ? 'Saved' : 'Save' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white border border-neutral-200 rounded-lg p-12 text-center">
            <BuildingOfficeIcon class="w-16 h-16 text-neutral-300 mx-auto mb-4" />
            <h3 class="text-lg font-semibold text-neutral-900 mb-2">No Properties to Compare</h3>
            <p class="text-neutral-600 mb-6">Select at least 2 properties to compare them side by side.</p>
            <Link
                :href="route('client.properties')"
                class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors"
            >
                Browse Properties
                <ArrowRightIcon class="w-4 h-4" />
            </Link>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import {
    BuildingOfficeIcon,
    StarIcon,
    HeartIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    properties: {
        type: Array,
        default: () => [],
    },
    savedPropertyIds: {
        type: Array,
        default: () => [],
    },
});

const savedIds = ref([...props.savedPropertyIds]);

const formatCurrency = (value) => {
    if (!value) return "₱0";
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const formatPropertyType = (type) => {
    if (!type) return "—";
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const getStatusClass = (status) => {
    const statusLower = (status || "").toLowerCase();
    switch (statusLower) {
        case "available":
        case "active":
            return "bg-green-100 text-green-700";
        case "sold":
            return "bg-red-100 text-red-700";
        case "under_contract":
        case "pending":
            return "bg-yellow-100 text-yellow-700";
        default:
            return "bg-neutral-100 text-neutral-700";
    }
};

const isSaved = (propertyId) => {
    return savedIds.value.includes(propertyId);
};

const toggleSave = (propertyId) => {
    if (isSaved(propertyId)) {
        router.post(
            route("client.properties.unsave", propertyId),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    const index = savedIds.value.indexOf(propertyId);
                    if (index > -1) {
                        savedIds.value.splice(index, 1);
                    }
                },
            }
        );
    } else {
        router.post(
            route("client.properties.save", propertyId),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    savedIds.value.push(propertyId);
                },
            }
        );
    }
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-clamp: 2;
}
</style>
