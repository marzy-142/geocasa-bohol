<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { useFormatters } from "@/Composables/useFormatters";
import {
    HeartIcon,
    ChatBubbleLeftRightIcon,
    MagnifyingGlassIcon,
    PlusIcon,
    ArrowRightIcon,
    HomeModernIcon,
    ClipboardDocumentListIcon,
    UserGroupIcon,
    MapPinIcon,
    StarIcon,
    ExclamationCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    stats: Object,
    recommendedProperties: Array,
    broker: Object,
    recentActivity: Array,
    recentSellerRequests: Array,
    isFirstLogin: Boolean,
});

const { formatCurrency, formatRelativeTime } = useFormatters();

const getStatusColor = (status) => {
    const colors = {
        pending: "bg-yellow-100 text-yellow-800 border-yellow-200",
        assigned: "bg-blue-100 text-blue-800 border-blue-200",
        under_review: "bg-purple-100 text-purple-800 border-purple-200",
        converted: "bg-green-100 text-green-800 border-green-200",
        new: "bg-yellow-100 text-yellow-800 border-yellow-200",
        contacted: "bg-blue-100 text-blue-800 border-blue-200",
    };
    return colors[status] || "bg-gray-100 text-gray-800 border-gray-200";
};
</script>

<template>
    <Head title="Dashboard" />

    <ModernDashboardLayout>
        <!-- Background Pattern for entire page -->
        <div class="fixed inset-0 -z-10 opacity-[0.02]">
            <div
                class="absolute inset-0"
                style="
                    background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%239C92AC&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');
                "
            ></div>
        </div>

        <!-- Hero Section with Background -->
        <div
            class="relative -mx-6 lg:-mx-8 -mt-6 lg:-mt-8 mb-10 overflow-hidden rounded-b-3xl"
        >
            <!-- Background Image with Overlay -->
            <div class="absolute inset-0 z-0">
                <img
                    src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=2000&auto=format&fit=crop"
                    alt="Bohol Landscape"
                    class="w-full h-full object-cover"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-r from-blue-900/90 via-blue-800/85 to-transparent"
                ></div>
                <div
                    class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-white"
                ></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 px-6 lg:px-8 py-16">
                <h1
                    class="text-5xl font-extrabold text-white mb-3 drop-shadow-lg"
                >
                    {{ isFirstLogin ? "Welcome" : "Welcome Back" }}
                </h1>
                <p class="text-xl text-blue-50 drop-shadow-md">
                    {{
                        isFirstLogin
                            ? "Let's help you find your perfect property"
                            : "Here's what's happening with your land search"
                    }}
                </p>
            </div>
        </div>

        <!-- Quick Actions - Clean and Professional -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <Link
                :href="route('client.properties')"
                class="bg-white border-2 border-gray-200 rounded-xl p-8 hover:border-blue-500 hover:shadow-lg transition-all duration-300 group h-full"
            >
                <MagnifyingGlassIcon
                    class="w-10 h-10 mb-4 text-gray-700 group-hover:text-blue-600 transition-colors"
                />
                <h3 class="text-xl font-bold mb-2 text-gray-900">
                    Browse Land
                </h3>
                <p class="text-gray-600">Find your perfect lot</p>
            </Link>

            <Link
                :href="route('client.properties.saved')"
                class="bg-white border-2 border-gray-200 rounded-xl p-8 hover:border-blue-500 hover:shadow-lg transition-all duration-300 group h-full"
            >
                <HeartIcon
                    class="w-10 h-10 mb-4 text-gray-700 group-hover:text-blue-600 transition-colors"
                />
                <h3 class="text-xl font-bold mb-2 text-gray-900">Saved Land</h3>
                <p class="text-gray-600">
                    {{ stats.savedProperties }} favorites
                </p>
            </Link>

            <Link
                :href="route('client.seller-requests.create')"
                class="bg-white border-2 border-gray-200 rounded-xl p-8 hover:border-blue-500 hover:shadow-lg transition-all duration-300 group h-full"
            >
                <PlusIcon
                    class="w-10 h-10 mb-4 text-gray-700 group-hover:text-blue-600 transition-colors"
                />
                <h3 class="text-xl font-bold mb-2 text-gray-900">
                    List My Land
                </h3>
                <p class="text-gray-600">Sell your property</p>
            </Link>
        </div>

        <!-- Key Metrics with improved visual design -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- Active Inquiries -->
            <div
                class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-all duration-300 flex flex-col h-full"
            >
                <div class="flex items-center justify-between mb-5">
                    <div
                        class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center"
                    >
                        <ChatBubbleLeftRightIcon
                            class="w-6 h-6 text-gray-700"
                        />
                    </div>
                    <span
                        v-if="stats.pendingInquiries > 0"
                        class="px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded"
                    >
                        {{ stats.pendingInquiries }} pending
                    </span>
                </div>
                <p class="text-3xl font-bold text-gray-900 mb-2">
                    {{ stats.activeInquiries }}
                </p>
                <p class="text-sm text-gray-600 mb-4">Active Inquiries</p>
                <Link
                    :href="route('client.inquiries.index')"
                    class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium group mt-auto"
                >
                    View all
                    <ArrowRightIcon
                        class="w-4 h-4 group-hover:translate-x-1 transition-transform"
                    />
                </Link>
            </div>

            <!-- Saved Properties -->
            <div
                class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-all duration-300 flex flex-col h-full"
            >
                <div class="flex items-center justify-between mb-5">
                    <div
                        class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center"
                    >
                        <HeartIcon class="w-6 h-6 text-gray-700" />
                    </div>
                </div>
                <p class="text-3xl font-bold text-gray-900 mb-2">
                    {{ stats.savedProperties }}
                </p>
                <p class="text-sm text-gray-600 mb-4">Saved Properties</p>
                <Link
                    :href="route('client.properties.saved')"
                    class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium group mt-auto"
                >
                    View favorites
                    <ArrowRightIcon
                        class="w-4 h-4 group-hover:translate-x-1 transition-transform"
                    />
                </Link>
            </div>

            <!-- Broker Card -->
            <div
                v-if="broker"
                class="bg-gray-900 text-white rounded-xl p-6 hover:shadow-md transition-all duration-300 flex flex-col h-full"
            >
                <div class="flex items-center gap-3 mb-5">
                    <div
                        class="w-12 h-12 bg-white/10 rounded-lg flex items-center justify-center"
                    >
                        <UserGroupIcon class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Your Broker</p>
                        <p class="font-bold">{{ broker.name }}</p>
                    </div>
                </div>
                <Link
                    :href="route('client.broker')"
                    class="inline-flex items-center gap-2 text-sm text-white hover:text-gray-300 font-medium group mt-auto"
                >
                    Contact broker
                    <ArrowRightIcon
                        class="w-4 h-4 group-hover:translate-x-1 transition-transform"
                    />
                </Link>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Recommended Properties -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Featured Properties with better header -->
                <div
                    v-if="
                        recommendedProperties &&
                        recommendedProperties.length > 0
                    "
                >
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-1">
                                Featured Land
                            </h2>
                            <p class="text-sm text-gray-600">
                                Handpicked properties for you
                            </p>
                        </div>
                        <Link
                            :href="route('client.properties')"
                            class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium group"
                        >
                            View all
                            <ArrowRightIcon
                                class="w-4 h-4 group-hover:translate-x-1 transition-transform"
                            />
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <Link
                            v-for="property in recommendedProperties.slice(
                                0,
                                4
                            )"
                            :key="property.id"
                            :href="
                                route('client.properties.show', property.slug)
                            "
                            class="bg-white border-2 border-gray-100 rounded-2xl overflow-hidden hover:border-blue-200 hover:shadow-2xl transition-all duration-300 group h-full flex flex-col"
                        >
                            <div
                                class="relative aspect-video overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100"
                            >
                                <img
                                    v-if="property.main_image"
                                    :src="property.main_image"
                                    :alt="property.title"
                                    loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                />
                                <div
                                    v-else
                                    class="w-full h-full flex items-center justify-center"
                                >
                                    <HomeModernIcon
                                        class="w-16 h-16 text-gray-300"
                                    />
                                </div>
                                <div
                                    v-if="property.is_featured"
                                    class="absolute top-4 left-4"
                                >
                                    <span
                                        class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-white text-xs font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 shadow-lg"
                                    >
                                        <StarIcon class="w-3.5 h-3.5" />
                                        Featured
                                    </span>
                                </div>
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                ></div>
                            </div>

                            <div class="p-5 flex-1 flex flex-col">
                                <h3
                                    class="font-bold text-lg text-gray-900 mb-3 line-clamp-1 group-hover:text-blue-600 transition-colors"
                                >
                                    {{ property.title }}
                                </h3>
                                <div
                                    class="flex items-center text-sm text-gray-600 mb-4"
                                >
                                    <MapPinIcon
                                        class="w-4 h-4 mr-1.5 flex-shrink-0"
                                    />
                                    <span class="line-clamp-1"
                                        >{{ property.municipality }},
                                        Bohol</span
                                    >
                                </div>

                                <div
                                    class="flex items-center justify-between pt-4 border-t border-gray-100"
                                    :class="'mt-auto'"
                                >
                                    <div>
                                        <div
                                            class="text-xl font-extrabold text-blue-600"
                                        >
                                            {{
                                                formatCurrency(
                                                    property.total_price
                                                )
                                            }}
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 font-medium mt-0.5"
                                        >
                                            {{
                                                formatCurrency(
                                                    property.price_per_sqm
                                                )
                                            }}/sqm
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div
                                            class="text-sm font-bold text-gray-900"
                                        >
                                            {{ property.area }} sqm
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 font-medium mt-0.5"
                                        >
                                            Lot Area
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Right Column - Recent Activity -->
            <div class="space-y-6">
                <!-- Recent Activity -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">
                        Recent Activity
                    </h3>

                    <div
                        v-if="recentActivity && recentActivity.length > 0"
                        class="space-y-4"
                    >
                        <div
                            v-for="activity in recentActivity.slice(0, 5)"
                            :key="activity.id"
                            class="pb-4 border-b border-gray-100 last:border-b-0 last:pb-0"
                        >
                            <p class="text-sm font-semibold text-gray-900">
                                {{ activity.title }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ activity.date }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <div
                            class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3"
                        >
                            <ExclamationCircleIcon
                                class="w-6 h-6 text-gray-400"
                            />
                        </div>
                        <p class="text-sm text-gray-500">No recent activity</p>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-clamp: 1;
}
</style>
