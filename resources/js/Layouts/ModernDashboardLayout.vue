<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, useForm } from '@inertiajs/vue3';
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
    ChevronRightIcon,
    Cog6ToothIcon,
    ArrowRightOnRectangleIcon,
    ChartBarIcon,
    MapPinIcon,
    ClipboardDocumentListIcon,
    SunIcon
} from '@heroicons/vue/24/outline';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const userRole = computed(() => {
    return page.props.auth?.user?.role || 'client';
});
const notifications = computed(() => page.props.notifications || []);

const sidebarOpen = ref(true);
const mobileMenuOpen = ref(false);

const logoutForm = useForm({});

const logout = () => {
    logoutForm.post(route('logout'));
};

// Dynamic navigation based on user role
const navigation = computed(() => {
    const baseNav = [
        { name: 'Dashboard', href: '/dashboard', icon: HomeIcon, current: false },
    ];

    if (userRole.value === 'broker') {
        return [
            ...baseNav,
            { name: 'My Properties', href: '/broker/properties', icon: BuildingOfficeIcon, current: false },
            { name: 'Clients', href: '/broker/clients', icon: UserGroupIcon, current: false },
            { name: 'Inquiries', href: '/inquiries', icon: DocumentTextIcon, current: false },
            { name: 'Transactions', href: '/transactions', icon: CreditCardIcon, current: false },
            { name: 'Seller Requests', href: '/seller-requests', icon: ClipboardDocumentListIcon, current: false },
            { name: 'Leaderboard', href: '/leaderboard', icon: ChartBarIcon, current: false },
        ];
    } else if (userRole.value === 'admin') {
        return [
            ...baseNav,
            { name: 'Manage Brokers', href: '/admin/brokers', icon: UserGroupIcon, current: false },
            { name: 'All Properties', href: '/admin/properties', icon: BuildingOfficeIcon, current: false },
            { name: 'Inquiries', href: '/inquiries', icon: DocumentTextIcon, current: false },
            { name: 'Transactions', href: '/transactions', icon: CreditCardIcon, current: false },
            { name: 'Seller Requests', href: '/seller-requests', icon: ClipboardDocumentListIcon, current: false },
            { name: 'Leaderboard', href: '/leaderboard', icon: ChartBarIcon, current: false },
        ];
    }

    return baseNav;
});

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const navigationItems = computed(() => {
    const baseItems = [
        { 
            name: 'Dashboard', 
            href: userRole.value === 'admin' ? route('admin.dashboard') : 
                  userRole.value === 'broker' ? route('broker.dashboard') : 
                  route('client.dashboard'), 
            icon: HomeIcon, 
            current: route().current('*.dashboard') 
        },
    ];

    if (userRole.value === 'admin') {
        return [
            ...baseItems,
            { name: 'Users', href: route('admin.users.index'), icon: UserGroupIcon, current: route().current('admin.users.*') },
            { name: 'Brokers', href: route('admin.brokers.index'), icon: BuildingOfficeIcon, current: route().current('admin.brokers.*') },
            { name: 'Properties', href: route('admin.properties.index'), icon: HomeModernIcon, current: route().current('admin.properties.*') },
            { name: 'Transactions', href: route('admin.transactions.index'), icon: CurrencyDollarIcon, current: route().current('admin.transactions.*') },
            { name: 'Reports', href: route('admin.reports.index'), icon: ChartBarIcon, current: route().current('admin.reports.*') },
        ];
    } else if (userRole.value === 'broker') {
        return [
            ...baseItems,
            { name: 'Properties', href: route('properties.index'), icon: BuildingOfficeIcon, current: route().current('properties.*') },
            { name: 'Clients', href: route('clients.index'), icon: UserGroupIcon, current: route().current('clients.*') },
            { name: 'Inquiries', href: route('inquiries.index'), icon: ChatBubbleLeftRightIcon, current: route().current('inquiries.*') },
            { name: 'Transactions', href: route('transactions.index'), icon: CurrencyDollarIcon, current: route().current('transactions.*') },
        ];
    } else {
        // Client navigation
        return [
            ...baseItems,
            { name: 'Browse Properties', href: route('public.properties'), icon: BuildingOfficeIcon, current: route().current('public.properties') },
            { name: 'My Inquiries', href: '#', icon: ChatBubbleLeftRightIcon, current: false },
            { name: 'Saved Properties', href: '#', icon: HeartIcon, current: false },
            { name: 'Sell Property', href: route('seller-requests.create'), icon: PlusIcon, current: route().current('seller-requests.create') },
        ];
    }
});
</script>

<template>
    <div class="min-h-screen bg-white">
        <!-- Mobile menu overlay -->
        <div 
            v-if="mobileMenuOpen" 
            class="fixed inset-0 z-50 lg:hidden"
            @click="toggleMobileMenu"
        >
            <div class="fixed inset-0 bg-neutral-900/20 backdrop-blur-sm"></div>
        </div>

        <!-- Sidebar -->
        <div 
            :class="[
                'fixed inset-y-0 left-0 z-50 bg-white/95 backdrop-blur-xl border-r border-neutral-100 transform transition-all duration-300 ease-in-out lg:translate-x-0',
                mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                sidebarOpen ? 'w-80' : 'lg:w-20 w-80'
            ]"
        >
            <!-- Sidebar header with GeoCasa Bohol branding -->
            <div class="flex items-center justify-between h-20 px-6 border-b border-neutral-50">
                <Link 
                    href="/" 
                    :class="[
                        'flex items-center gap-4 transition-all duration-300',
                        !sidebarOpen && 'lg:justify-center'
                    ]"
                >
                    <!-- GeoCasa Bohol Logo -->
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-accent-500 rounded-2xl flex items-center justify-center shadow-soft">
                            <MapPinIcon class="w-6 h-6 text-white" />
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-coconut-400 rounded-full border-2 border-white flex items-center justify-center">
                            <SunIcon class="w-2 h-2 text-coconut-800" />
                        </div>
                    </div>
                    <div v-if="sidebarOpen" class="lg:block">
                        <div class="text-xl font-bold text-neutral-900">
                            <span class="text-primary-600">Geo</span><span class="text-accent-600">Casa</span>
                        </div>
                        <div class="text-sm font-medium text-coconut-600 -mt-1">Bohol</div>
                    </div>
                </Link>
                
                <!-- Mobile close button -->
                <button 
                    @click="toggleMobileMenu"
                    class="lg:hidden p-2 rounded-xl hover:bg-neutral-50 transition-colors"
                >
                    <XMarkIcon class="w-5 h-5 text-neutral-500" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-6 py-8 space-y-2">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'group flex items-center gap-4 px-4 py-3.5 text-sm font-medium rounded-2xl transition-all duration-200',
                        'hover:bg-primary-50 hover:text-primary-700 hover:shadow-soft',
                        'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
                        item.current 
                            ? 'bg-primary-50 text-primary-700 shadow-soft border border-primary-100' 
                            : 'text-neutral-600',
                        !sidebarOpen && 'lg:justify-center lg:px-4'
                    ]"
                >
                    <component 
                        :is="item.icon" 
                        :class="[
                            'w-5 h-5 flex-shrink-0 transition-colors duration-200',
                            item.current ? 'text-primary-600' : 'text-neutral-400 group-hover:text-primary-600'
                        ]" 
                    />
                    <span 
                        v-if="sidebarOpen" 
                        class="lg:block transition-opacity duration-200"
                    >
                        {{ item.name }}
                    </span>
                </Link>
            </nav>

            <!-- User section -->
            <div class="border-t border-neutral-50 p-6">
                <!-- Settings -->
                <Link
                    href="/settings"
                    :class="[
                        'group flex items-center gap-4 px-4 py-3 text-sm font-medium text-neutral-600 rounded-2xl transition-all duration-200 mb-3',
                        'hover:bg-neutral-50 hover:text-neutral-900',
                        !sidebarOpen && 'lg:justify-center lg:px-4'
                    ]"
                >
                    <Cog6ToothIcon class="w-5 h-5 flex-shrink-0 text-neutral-400 group-hover:text-neutral-600" />
                    <span v-if="sidebarOpen" class="lg:block">Settings</span>
                </Link>

                <!-- Logout -->
                <button
                    @click="logout"
                    :class="[
                        'group flex items-center gap-4 px-4 py-3 text-sm font-medium text-neutral-600 rounded-2xl transition-all duration-200 w-full mb-4',
                        'hover:bg-red-50 hover:text-red-600',
                        !sidebarOpen && 'lg:justify-center lg:px-4'
                    ]"
                >
                    <ArrowRightOnRectangleIcon class="w-5 h-5 flex-shrink-0 text-neutral-400 group-hover:text-red-500" />
                    <span v-if="sidebarOpen" class="lg:block">Logout</span>
                </button>

                <!-- Sidebar toggle button (desktop) -->
                <div class="hidden lg:block pt-4 border-t border-neutral-50">
                    <button
                        @click="toggleSidebar"
                        class="w-full flex items-center justify-center p-3 rounded-2xl hover:bg-neutral-50 transition-colors group"
                    >
                        <ChevronLeftIcon 
                            v-if="sidebarOpen" 
                            class="w-4 h-4 text-neutral-400 group-hover:text-neutral-600 transition-colors" 
                        />
                        <ChevronRightIcon 
                            v-else 
                            class="w-4 h-4 text-neutral-400 group-hover:text-neutral-600 transition-colors" 
                        />
                    </button>
                </div>
            </div>
        </div>

        <!-- Main content area -->
        <div 
            :class="[
                'transition-all duration-300 ease-in-out',
                sidebarOpen ? 'lg:ml-80' : 'lg:ml-20'
            ]"
        >
            <!-- Top header -->
            <header class="bg-white/80 backdrop-blur-xl border-b border-neutral-100 sticky top-0 z-40">
                <div class="flex items-center justify-between h-20 px-6 lg:px-8">
                    <!-- Mobile menu button -->
                    <button
                        @click="toggleMobileMenu"
                        class="lg:hidden p-2 rounded-xl hover:bg-neutral-50 transition-colors"
                    >
                        <Bars3Icon class="w-6 h-6 text-neutral-600" />
                    </button>

                    <!-- Search bar -->
                    <div class="hidden md:flex flex-1 max-w-lg mx-8">
                        <div class="relative w-full">
                            <MagnifyingGlassIcon class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400" />
                            <input
                                type="text"
                                placeholder="Search properties, clients, or transactions..."
                                class="w-full pl-12 pr-4 py-3 bg-neutral-50 border-0 rounded-2xl text-sm placeholder-neutral-500 focus:bg-white focus:ring-2 focus:ring-primary-500 transition-all duration-200"
                            />
                        </div>
                    </div>

                    <!-- Right side actions -->
                    <div class="flex items-center gap-4">
                        <!-- Notifications -->
                        <NotificationDropdown :notifications="notifications" />
                        
                        <!-- User menu -->
                        <div class="flex items-center gap-3 pl-4 border-l border-neutral-200">
                            <div class="text-right hidden sm:block">
                                <div class="text-sm font-medium text-neutral-900">{{ user?.name }}</div>
                                <div class="text-xs text-neutral-500 capitalize">{{ userRole }}</div>
                            </div>
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-accent-400 rounded-2xl flex items-center justify-center text-white font-medium text-sm shadow-soft">
                                {{ user?.name?.charAt(0).toUpperCase() }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="p-6 lg:p-8 min-h-screen bohol-pattern">
                <div class="animate-fade-in">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for sidebar */
.sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}

.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 2px;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}
</style>