<script setup>
import ModernDashboardLayout from '@/Layouts/ModernDashboardLayout.vue';
import DashboardCard from '@/Components/DashboardCard.vue';
import ModernButton from '@/Components/ModernButton.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    BuildingOfficeIcon, 
    HeartIcon,
    ChatBubbleLeftRightIcon,
    EyeIcon,
    MapPinIcon,
    StarIcon
} from '@heroicons/vue/24/outline';

defineProps({
    stats: {
        type: Object,
        default: () => ({
            savedProperties: 0,
            activeInquiries: 0,
            viewedProperties: 0,
            favoriteAreas: 0
        })
    },
    recentInquiries: {
        type: Array,
        default: () => []
    },
    recommendedProperties: {
        type: Array,
        default: () => []
    }
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value).replace('PHP', '').trim();
};
</script>

<template>
    <Head title="My Dashboard - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Welcome Section -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                        Welcome to Your Dashboard! 🏡
                    </h1>
                    <p class="text-neutral-600 text-lg">
                        Discover your dream property in beautiful Bohol and track your real estate journey.
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <Link :href="route('public.properties')" class="btn-outline">
                        <EyeIcon class="w-5 h-5" />
                        Browse Properties
                    </Link>
                    <Link :href="route('seller-requests.create')" class="btn-primary">
                        <BuildingOfficeIcon class="w-5 h-5" />
                        List Property
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="dashboard-grid mb-12">
            <DashboardCard
                title="Saved Properties"
                :value="stats.savedProperties.toString()"
                subtitle="Your favorites"
                :icon="HeartIcon"
                color="primary"
                :interactive="true"
            />
            
            <DashboardCard
                title="Active Inquiries"
                :value="stats.activeInquiries.toString()"
                subtitle="Pending responses"
                :icon="ChatBubbleLeftRightIcon"
                color="accent"
                :interactive="true"
            />
            
            <DashboardCard
                title="Properties Viewed"
                :value="stats.viewedProperties.toString()"
                subtitle="This month"
                :icon="EyeIcon"
                color="coconut"
                :interactive="true"
            />
            
            <DashboardCard
                title="Favorite Areas"
                :value="stats.favoriteAreas.toString()"
                subtitle="Locations of interest"
                :icon="MapPinIcon"
                color="neutral"
                :interactive="true"
            />
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Recent Inquiries -->
            <div class="card p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-neutral-900">Recent Inquiries</h2>
                    <Link href="#" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                        View All
                    </Link>
                </div>
                
                <div v-if="recentInquiries.length > 0" class="space-y-4">
                    <div 
                        v-for="inquiry in recentInquiries.slice(0, 3)" 
                        :key="inquiry.id"
                        class="flex items-center gap-4 p-4 bg-neutral-50 rounded-2xl"
                    >
                        <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                            <ChatBubbleLeftRightIcon class="w-5 h-5 text-primary-600" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-neutral-900">{{ inquiry.property.title }}</p>
                            <p class="text-xs text-neutral-500">
                                Status: {{ inquiry.status }} • {{ inquiry.created_at }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                {{ inquiry.status }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-8">
                    <ChatBubbleLeftRightIcon class="w-12 h-12 text-neutral-300 mx-auto mb-4" />
                    <p class="text-neutral-500 mb-4">No inquiries yet</p>
                    <Link :href="route('public.properties')" class="btn-primary">
                        Start Browsing Properties
                    </Link>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card p-8">
                <h2 class="text-xl font-bold text-neutral-900 mb-6">Quick Actions</h2>
                <div class="grid grid-cols-2 gap-4">
                    <Link :href="route('public.properties')" class="btn-secondary h-20 flex-col">
                        <BuildingOfficeIcon class="w-6 h-6 mb-2" />
                        <span class="text-sm">Browse Properties</span>
                    </Link>
                    <Link href="#" class="btn-secondary h-20 flex-col">
                        <HeartIcon class="w-6 h-6 mb-2" />
                        <span class="text-sm">Saved Properties</span>
                    </Link>
                    <Link :href="route('seller-requests.create')" class="btn-secondary h-20 flex-col">
                        <MapPinIcon class="w-6 h-6 mb-2" />
                        <span class="text-sm">Sell Property</span>
                    </Link>
                    <Link href="#" class="btn-secondary h-20 flex-col">
                        <ChatBubbleLeftRightIcon class="w-6 h-6 mb-2" />
                        <span class="text-sm">My Inquiries</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Recommended Properties -->
        <div v-if="recommendedProperties.length > 0" class="card p-8 mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-neutral-900">Recommended for You</h2>
                <Link :href="route('public.properties')" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                    View All Properties
                </Link>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="property in recommendedProperties.slice(0, 3)" 
                    :key="property.id"
                    class="bg-white border border-neutral-200 rounded-2xl overflow-hidden hover:shadow-lg transition-shadow"
                >
                    <div class="aspect-video bg-neutral-100 relative">
                        <img 
                            v-if="property.images && property.images[0]" 
                            :src="property.images[0]" 
                            :alt="property.title"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <BuildingOfficeIcon class="w-12 h-12 text-neutral-400" />
                        </div>
                        <div class="absolute top-4 right-4">
                            <button class="w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white transition-colors">
                                <HeartIcon class="w-4 h-4 text-neutral-600" />
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-neutral-900 mb-2">{{ property.title }}</h3>
                        <p class="text-sm text-neutral-600 mb-3">{{ property.address }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-primary-600">
                                ₱{{ formatCurrency(property.total_price) }}
                            </span>
                            <Link 
                                :href="route('public.properties.show', property.id)"
                                class="text-primary-600 hover:text-primary-800 text-sm font-medium"
                            >
                                View Details
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bohol Inspiration Section -->
        <div class="card p-8 tropical-gradient text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Find Your Paradise in Bohol</h2>
                    <p class="text-white/90 mb-4">
                        From pristine beaches to lush mountains, discover the perfect property that matches your dream lifestyle in Bohol.
                    </p>
                    <Link :href="route('public.properties')" class="btn-coconut">
                        Explore All Properties
                    </Link>
                </div>
                <div class="hidden lg:block">
                    <div class="w-32 h-32 bg-white/10 rounded-3xl backdrop-blur-sm flex items-center justify-center">
                        <span class="text-4xl">🏖️</span>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>