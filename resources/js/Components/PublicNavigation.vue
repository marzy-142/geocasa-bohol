<script setup>
import { Link } from '@inertiajs/vue3';
import { 
    MapPinIcon, 
    SunIcon,
    Bars3Icon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    auth: Object,
    currentRoute: {
        type: String,
        default: ''
    }
});

const mobileMenuOpen = ref(false);
const isNavbarVisible = ref(true);
const lastScrollY = ref(0);
const scrollThreshold = 10; // Minimum scroll distance to trigger hide/show

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const handleScroll = () => {
    const currentScrollY = window.scrollY;
    
    // Don't hide navbar if we're at the top of the page
    if (currentScrollY < scrollThreshold) {
        isNavbarVisible.value = true;
        lastScrollY.value = currentScrollY;
        return;
    }
    
    // Hide navbar when scrolling down, show when scrolling up
    if (currentScrollY > lastScrollY.value && currentScrollY > scrollThreshold) {
        isNavbarVisible.value = false;
        // Close mobile menu when hiding navbar
        mobileMenuOpen.value = false;
    } else if (currentScrollY < lastScrollY.value) {
        isNavbarVisible.value = true;
    }
    
    lastScrollY.value = currentScrollY;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <header 
        :class="[
            'bg-white/95 backdrop-blur-xl border-b border-neutral-100 sticky top-0 z-50 transition-transform duration-300 ease-in-out',
            isNavbarVisible ? 'translate-y-0' : '-translate-y-full'
        ]"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <!-- Logo -->
                <Link :href="route('home')" class="flex items-center gap-4">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-accent-500 rounded-2xl flex items-center justify-center shadow-soft">
                            <MapPinIcon class="w-6 h-6 text-white" />
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-coconut-400 rounded-full border-2 border-white flex items-center justify-center">
                            <SunIcon class="w-2 h-2 text-coconut-800" />
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-neutral-900">
                            <span class="text-primary-600">Geo</span><span class="text-accent-600">Casa</span>
                        </div>
                        <div class="text-sm font-medium text-coconut-600 -mt-1">Bohol</div>
                    </div>
                </Link>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-8">
                    <Link 
                        :href="route('home')" 
                        :class="[
                            'font-medium transition-colors',
                            currentRoute === 'home' 
                                ? 'text-primary-600 font-semibold' 
                                : 'text-neutral-600 hover:text-primary-600'
                        ]"
                    >
                        Home
                    </Link>
                    <Link 
                        :href="route('public.properties')" 
                        :class="[
                            'font-medium transition-colors',
                            currentRoute === 'public.properties' 
                                ? 'text-primary-600 font-semibold' 
                                : 'text-neutral-600 hover:text-primary-600'
                        ]"
                    >
                        Properties
                    </Link>
                    <Link 
                        :href="route('seller-requests.create')" 
                        :class="[
                            'font-medium transition-colors',
                            currentRoute === 'seller-requests.create' 
                                ? 'text-primary-600 font-semibold' 
                                : 'text-neutral-600 hover:text-primary-600'
                        ]"
                    >
                        Sell Property
                    </Link>
                    <Link 
                        :href="route('leaderboard.index')" 
                        :class="[
                            'font-medium transition-colors',
                            currentRoute === 'leaderboard.index' 
                                ? 'text-primary-600 font-semibold' 
                                : 'text-neutral-600 hover:text-primary-600'
                        ]"
                    >
                        Leaderboard
                    </Link>
                </nav>

                <!-- Auth Buttons -->
                <div class="hidden md:flex items-center gap-4">
                    <template v-if="auth?.user">
                        <Link :href="route('dashboard')" class="btn-primary">
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('register')" class="btn-ghost">
                            Register
                        </Link>
                        <Link :href="route('login')" class="btn-primary">
                            Login
                        </Link>
                    </template>
                </div>

                <!-- Mobile Menu Button -->
                <button 
                    @click="toggleMobileMenu"
                    class="md:hidden p-2 rounded-xl text-neutral-600 hover:text-primary-600 hover:bg-primary-50 transition-colors"
                >
                    <Bars3Icon v-if="!mobileMenuOpen" class="w-6 h-6" />
                    <XMarkIcon v-else class="w-6 h-6" />
                </button>
            </div>

            <!-- Mobile Menu -->
            <div 
                v-if="mobileMenuOpen" 
                class="md:hidden border-t border-neutral-100 py-4 animate-fade-in"
            >
                <nav class="flex flex-col gap-4">
                    <Link 
                        :href="route('home')" 
                        :class="[
                            'px-4 py-2 rounded-xl font-medium transition-colors',
                            currentRoute === 'home' 
                                ? 'text-primary-600 bg-primary-50 font-semibold' 
                                : 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50'
                        ]"
                        @click="mobileMenuOpen = false"
                    >
                        Home
                    </Link>
                    <Link 
                        :href="route('public.properties')" 
                        :class="[
                            'px-4 py-2 rounded-xl font-medium transition-colors',
                            currentRoute === 'public.properties' 
                                ? 'text-primary-600 bg-primary-50 font-semibold' 
                                : 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50'
                        ]"
                        @click="mobileMenuOpen = false"
                    >
                        Properties
                    </Link>
                    <Link 
                        :href="route('seller-requests.create')" 
                        :class="[
                            'px-4 py-2 rounded-xl font-medium transition-colors',
                            currentRoute === 'seller-requests.create' 
                                ? 'text-primary-600 bg-primary-50 font-semibold' 
                                : 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50'
                        ]"
                        @click="mobileMenuOpen = false"
                    >
                        Sell Property
                    </Link>
                    <Link 
                        :href="route('leaderboard.index')" 
                        :class="[
                            'px-4 py-2 rounded-xl font-medium transition-colors',
                            currentRoute === 'leaderboard.index' 
                                ? 'text-primary-600 bg-primary-50 font-semibold' 
                                : 'text-neutral-600 hover:text-primary-600 hover:bg-primary-50'
                        ]"
                        @click="mobileMenuOpen = false"
                    >
                        Leaderboard
                    </Link>
                </nav>

                <!-- Mobile Auth Buttons -->
                <div class="flex flex-col gap-3 mt-6 pt-4 border-t border-neutral-100">
                    <template v-if="auth?.user">
                        <Link 
                            :href="route('dashboard')" 
                            class="btn-primary text-center"
                            @click="mobileMenuOpen = false"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link 
                            :href="route('register')" 
                            class="btn-ghost text-center"
                            @click="mobileMenuOpen = false"
                        >
                            Register
                        </Link>
                        <Link 
                            :href="route('login')" 
                            class="btn-primary text-center"
                            @click="mobileMenuOpen = false"
                        >
                            Login
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </header>
</template>