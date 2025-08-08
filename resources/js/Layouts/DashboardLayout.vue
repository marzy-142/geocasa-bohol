<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    HomeIcon, 
    BuildingOfficeIcon, 
    UserGroupIcon, 
    DocumentTextIcon,
    CreditCardIcon,
    BellIcon,
    MagnifyingGlassIcon,
    Bars3Icon,
    XMarkIcon,
    ChevronLeftIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const notifications = computed(() => page.props.notifications || []);

const sidebarOpen = ref(true);
const mobileMenuOpen = ref(false);

const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: HomeIcon, current: false },
    { name: 'Properties', href: '/properties', icon: BuildingOfficeIcon, current: false },
    { name: 'Clients', href: '/clients', icon: UserGroupIcon, current: false },
    { name: 'Inquiries', href: '/inquiries', icon: DocumentTextIcon, current: false },
    { name: 'Transactions', href: '/transactions', icon: CreditCardIcon, current: false },
];

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};
</script>

<template>
    <div class="min-h-screen bg-coconut-100">
        <!-- Mobile menu overlay -->
        <div 
            v-if="mobileMenuOpen" 
            class="fixed inset-0 z-50 lg:hidden"
            @click="toggleMobileMenu"
        >
            <div class="fixed inset-0 bg-dark-900/50"></div>
        </div>

        <!-- Sidebar -->
        <div 
            :class="[
                'fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-soft-lg transform transition-transform duration-150 ease-in-out lg:translate-x-0',
                mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                !sidebarOpen && 'lg:w-20'
            ]"
        >
            <!-- Sidebar header -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-coconut-200">
                <Link 
                    href="/" 
                    :class="[
                        'flex items-center gap-3 text-xl font-serif font-bold text-visayan-500 transition-all duration-150',
                        !sidebarOpen && 'lg:justify-center'
                    ]"
                >
                    <div class="w-8 h-8 bg-visayan-500 rounded-lg flex items-center justify-center text-white text-sm font-bold">
                        GC
                    </div>
                    <span v-if="sidebarOpen" class="lg:block">GeoCasa Bohol</span>
                </Link>
                
                <!-- Mobile close button -->
                <button 
                    @click="toggleMobileMenu"
                    class="lg:hidden p-2 rounded-lg hover:bg-coconut-200 transition-colors"
                >
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'sidebar-link',
                        item.current && 'active',
                        !sidebarOpen && 'lg:justify-center lg:px-4'
                    ]"
                >
                    <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
                    <span v-if="sidebarOpen" class="lg:block">{{ item.name }}</span>
                </Link>
            </nav>

            <!-- Sidebar toggle button (desktop) -->
            <div class="hidden lg:block p-4 border-t border-coconut-200">
                <button
                    @click="toggleSidebar"
                    class="w-full flex items-center justify-center p-2 rounded-lg hover:bg-coconut-200 transition-colors"
                >
                    <ChevronLeftIcon v-if="sidebarOpen" class="w-5 h-5" />
                    <ChevronRightIcon v-else class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Main content -->
        <div 
            :class="[
                'lg:pl-72 transition-all duration-150 ease-in-out',
                !sidebarOpen && 'lg:pl-20'
            ]"
        >
            <!-- Top navbar -->
            <header class="bg-white shadow-soft border-b border-coconut-200">
                <div class="flex items-center justify-between h-16 px-6">
                    <!-- Mobile menu button -->
                    <button
                        @click="toggleMobileMenu"
                        class="lg:hidden p-2 rounded-lg hover:bg-coconut-200 transition-colors"
                    >
                        <Bars3Icon class="w-5 h-5" />
                    </button>

                    <!-- Search bar -->
                    <div class="flex-1 max-w-lg mx-4">
                        <div class="relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-dark-400" />
                            <input
                                type="text"
                                placeholder="Search properties, clients..."
                                class="w-full pl-10 pr-4 py-2 bg-coconut-100 border-0 rounded-2xl focus:ring-2 focus:ring-visayan-500 focus:bg-white transition-all duration-150"
                            />
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center gap-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 rounded-2xl hover:bg-coconut-200 transition-colors relative">
                                <BellIcon class="w-5 h-5 text-dark-600" />
                                <span 
                                    v-if="notifications.length > 0"
                                    class="absolute -top-1 -right-1 w-4 h-4 bg-sand-500 text-dark-900 text-xs rounded-full flex items-center justify-center font-medium"
                                >
                                    {{ notifications.length }}
                                </span>
                            </button>
                        </div>

                        <!-- Profile -->
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-visayan-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                {{ user?.name?.charAt(0) || 'U' }}
                            </div>
                            <div v-if="user" class="hidden sm:block">
                                <p class="text-sm font-medium text-dark-900">{{ user.name }}</p>
                                <p class="text-xs text-dark-500 capitalize">{{ user.role || 'User' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>