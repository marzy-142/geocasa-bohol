<template>
    <ModernDashboardLayout>
        <Head title="Property Renewals - GeoCasa Bohol" />

        <!-- Header Section -->
        <div
            class="mb-8 bg-gradient-to-r from-orange-600 to-red-600 rounded-3xl p-8 text-white shadow-xl"
        >
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm"
                        >
                            <span class="text-2xl">⏰</span>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold mb-1">
                                Property Renewals
                            </h1>
                            <p class="text-orange-100">
                                Manage your expiring and expired property
                                listings
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div
                        class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-2"
                    >
                        <div class="text-2xl font-bold">
                            {{ stats.expiring_soon }}
                        </div>
                        <div class="text-sm text-orange-100">Expiring Soon</div>
                    </div>
                    <div
                        class="bg-white/10 backdrop-blur-sm rounded-2xl px-4 py-2"
                    >
                        <div class="text-2xl font-bold">
                            {{ stats.expired }}
                        </div>
                        <div class="text-sm text-orange-100">Expired</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="mb-6">
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-2"
            >
                <div class="flex space-x-1">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'flex-1 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200',
                            activeTab === tab.key
                                ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-md'
                                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50',
                        ]"
                    >
                        <div class="flex items-center justify-center gap-2">
                            <component :is="tab.icon" class="w-4 h-4" />
                            <span>{{ tab.label }}</span>
                            <span
                                v-if="tab.count > 0"
                                :class="[
                                    'px-2 py-0.5 rounded-full text-xs font-bold',
                                    activeTab === tab.key
                                        ? 'bg-white/20 text-white'
                                        : 'bg-slate-200 text-slate-700',
                                ]"
                            >
                                {{ tab.count }}
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Properties List -->
        <div class="space-y-4">
            <!-- Expiring Soon Properties -->
            <div v-if="activeTab === 'expiring'">
                <div v-if="expiring_properties.length > 0" class="space-y-4">
                    <div
                        v-for="property in expiring_properties"
                        :key="property.id"
                        class="bg-white rounded-xl border border-amber-200 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <h3
                                            class="text-lg font-semibold text-slate-900"
                                        >
                                            {{ property.title }}
                                        </h3>
                                        <span
                                            class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-sm font-medium"
                                        >
                                            Expires in
                                            {{
                                                property.days_until_expiry
                                            }}
                                            days
                                        </span>
                                    </div>
                                    <p
                                        class="text-slate-600 mb-2 flex items-center gap-1"
                                    >
                                        <MapPinIcon class="w-4 h-4" />
                                        {{ property.address }},
                                        {{ property.municipality }}
                                    </p>
                                    <div
                                        class="flex items-center gap-4 text-sm text-slate-500"
                                    >
                                        <span>{{
                                            property.formatted_total_price
                                        }}</span>
                                        <span>{{
                                            property.formatted_area
                                        }}</span>
                                        <span
                                            >Listed
                                            {{
                                                property.created_at_human
                                            }}</span
                                        >
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button
                                        @click="renewProperty(property.id)"
                                        :disabled="
                                            renewingProperties.includes(
                                                property.id
                                            )
                                        "
                                        class="bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
                                    >
                                        <ArrowPathIcon
                                            :class="[
                                                'w-4 h-4',
                                                renewingProperties.includes(
                                                    property.id
                                                )
                                                    ? 'animate-spin'
                                                    : '',
                                            ]"
                                        />
                                        {{
                                            renewingProperties.includes(
                                                property.id
                                            )
                                                ? "Renewing..."
                                                : "Renew Now"
                                        }}
                                    </button>
                                    <Link
                                        :href="
                                            route(
                                                'broker.properties.edit',
                                                property.id
                                            )
                                        "
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
                                    >
                                        <PencilIcon class="w-4 h-4" />
                                        Edit
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12">
                    <div
                        class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4"
                    >
                        <CheckCircleIcon class="w-8 h-8 text-green-600" />
                    </div>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">
                        All properties are up to date!
                    </h3>
                    <p class="text-slate-600">
                        No properties are expiring in the next 30 days.
                    </p>
                </div>
            </div>

            <!-- Expired Properties -->
            <div v-if="activeTab === 'expired'">
                <div v-if="expired_properties.length > 0" class="space-y-4">
                    <div
                        v-for="property in expired_properties"
                        :key="property.id"
                        class="bg-white rounded-xl border border-red-200 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <h3
                                            class="text-lg font-semibold text-slate-900"
                                        >
                                            {{ property.title }}
                                        </h3>
                                        <span
                                            class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium"
                                        >
                                            Expired
                                            {{
                                                property.days_since_expiry
                                            }}
                                            days ago
                                        </span>
                                        <span
                                            class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-sm font-medium"
                                        >
                                            Hidden from public
                                        </span>
                                    </div>
                                    <p
                                        class="text-slate-600 mb-2 flex items-center gap-1"
                                    >
                                        <MapPinIcon class="w-4 h-4" />
                                        {{ property.address }},
                                        {{ property.municipality }}
                                    </p>
                                    <div
                                        class="flex items-center gap-4 text-sm text-slate-500"
                                    >
                                        <span>{{
                                            property.formatted_total_price
                                        }}</span>
                                        <span>{{
                                            property.formatted_area
                                        }}</span>
                                        <span
                                            >Expired on
                                            {{
                                                property.expired_at_human
                                            }}</span
                                        >
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button
                                        @click="renewProperty(property.id)"
                                        :disabled="
                                            renewingProperties.includes(
                                                property.id
                                            )
                                        "
                                        class="bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
                                    >
                                        <ArrowPathIcon
                                            :class="[
                                                'w-4 h-4',
                                                renewingProperties.includes(
                                                    property.id
                                                )
                                                    ? 'animate-spin'
                                                    : '',
                                            ]"
                                        />
                                        {{
                                            renewingProperties.includes(
                                                property.id
                                            )
                                                ? "Renewing..."
                                                : "Renew & Reactivate"
                                        }}
                                    </button>
                                    <Link
                                        :href="
                                            route(
                                                'broker.properties.edit',
                                                property.id
                                            )
                                        "
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
                                    >
                                        <PencilIcon class="w-4 h-4" />
                                        Edit
                                    </Link>
                                    <button
                                        @click="confirmDelete(property)"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12">
                    <div
                        class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4"
                    >
                        <CheckCircleIcon class="w-8 h-8 text-green-600" />
                    </div>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">
                        No expired properties!
                    </h3>
                    <p class="text-slate-600">
                        All your properties are active and up to date.
                    </p>
                </div>
            </div>

            <!-- Recently Renewed Properties -->
            <div v-if="activeTab === 'renewed'">
                <div v-if="renewed_properties.length > 0" class="space-y-4">
                    <div
                        v-for="property in renewed_properties"
                        :key="property.id"
                        class="bg-white rounded-xl border border-green-200 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <h3
                                            class="text-lg font-semibold text-slate-900"
                                        >
                                            {{ property.title }}
                                        </h3>
                                        <span
                                            class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium"
                                        >
                                            Renewed
                                            {{ property.renewed_at_human }}
                                        </span>
                                    </div>
                                    <p
                                        class="text-slate-600 mb-2 flex items-center gap-1"
                                    >
                                        <MapPinIcon class="w-4 h-4" />
                                        {{ property.address }},
                                        {{ property.municipality }}
                                    </p>
                                    <div
                                        class="flex items-center gap-4 text-sm text-slate-500"
                                    >
                                        <span>{{
                                            property.formatted_total_price
                                        }}</span>
                                        <span>{{
                                            property.formatted_area
                                        }}</span>
                                        <span
                                            >Expires
                                            {{
                                                property.expires_at_human
                                            }}</span
                                        >
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <Link
                                        :href="
                                            route(
                                                'broker.properties.show',
                                                property.id
                                            )
                                        "
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2"
                                    >
                                        <EyeIcon class="w-4 h-4" />
                                        View
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12">
                    <div
                        class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4"
                    >
                        <ClockIcon class="w-8 h-8 text-slate-400" />
                    </div>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">
                        No recent renewals
                    </h3>
                    <p class="text-slate-600">
                        Properties you've renewed in the last 30 days will
                        appear here.
                    </p>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center"
                    >
                        <ExclamationTriangleIcon class="w-5 h-5 text-red-600" />
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">
                        Delete Property
                    </h3>
                </div>
                <p class="text-slate-600 mb-6">
                    Are you sure you want to delete "{{
                        propertyToDelete?.title
                    }}"? This action cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 text-slate-600 hover:text-slate-900 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteProperty"
                        :disabled="deleting"
                        class="bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                    >
                        {{ deleting ? "Deleting..." : "Delete Property" }}
                    </button>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { ref, computed } from "vue";
import {
    ClockIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    MapPinIcon,
    ArrowPathIcon,
    PencilIcon,
    TrashIcon,
    EyeIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    expiring_properties: Array,
    expired_properties: Array,
    renewed_properties: Array,
    stats: Object,
});

const activeTab = ref("expiring");
const renewingProperties = ref([]);
const showDeleteModal = ref(false);
const propertyToDelete = ref(null);
const deleting = ref(false);

const tabs = computed(() => [
    {
        key: "expiring",
        label: "Expiring Soon",
        icon: ClockIcon,
        count: props.expiring_properties.length,
    },
    {
        key: "expired",
        label: "Expired",
        icon: ExclamationTriangleIcon,
        count: props.expired_properties.length,
    },
    {
        key: "renewed",
        label: "Recently Renewed",
        icon: CheckCircleIcon,
        count: props.renewed_properties.length,
    },
]);

const renewProperty = async (propertyId) => {
    renewingProperties.value.push(propertyId);

    try {
        await router.post(
            route("broker.properties.renew", propertyId),
            {},
            {
                onSuccess: () => {
                    // Property will be removed from current list via page refresh
                },
                onError: (errors) => {
                    console.error("Renewal failed:", errors);
                    alert("Failed to renew property. Please try again.");
                },
                onFinish: () => {
                    renewingProperties.value = renewingProperties.value.filter(
                        (id) => id !== propertyId
                    );
                },
            }
        );
    } catch (error) {
        console.error("Renewal error:", error);
        renewingProperties.value = renewingProperties.value.filter(
            (id) => id !== propertyId
        );
    }
};

const confirmDelete = (property) => {
    propertyToDelete.value = property;
    showDeleteModal.value = true;
};

const deleteProperty = async () => {
    if (!propertyToDelete.value) return;

    deleting.value = true;

    try {
        await router.delete(
            route("broker.properties.destroy", propertyToDelete.value.id),
            {
                onSuccess: () => {
                    showDeleteModal.value = false;
                    propertyToDelete.value = null;
                },
                onError: (errors) => {
                    console.error("Delete failed:", errors);
                    alert("Failed to delete property. Please try again.");
                },
                onFinish: () => {
                    deleting.value = false;
                },
            }
        );
    } catch (error) {
        console.error("Delete error:", error);
        deleting.value = false;
    }
};
</script>
