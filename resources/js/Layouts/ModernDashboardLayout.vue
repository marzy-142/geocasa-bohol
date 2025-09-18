<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link, usePage, useForm } from "@inertiajs/vue3";
import {
    HomeIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
    UsersIcon, // Add this import
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
    SunIcon,
    ChatBubbleLeftRightIcon,
    ChatBubbleBottomCenterTextIcon,
    CurrencyDollarIcon,
    HeartIcon,
    PlusIcon,
    ExclamationTriangleIcon,
    HomeModernIcon,
    ClockIcon,
    PresentationChartBarIcon, // Add for Broker Analytics
    UserPlusIcon, // Add for Client Assignments
    DocumentDuplicateIcon, // Add for Seller Requests
    DocumentCheckIcon, // Add this line for Broker Approvals
} from "@heroicons/vue/24/outline";
import NotificationDropdown from "@/Components/NotificationDropdown.vue";
import NotificationToast from "@/Components/NotificationToast.vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const userRole = computed(() => {
    return page.props.auth?.user?.role || "client";
    h;
});
const notifications = computed(() => page.props.notifications || []);

const sidebarOpen = ref(true);
const mobileMenuOpen = ref(false);
const showMobileSearch = ref(false);

// Search functionality
const searchQuery = ref("");
const searchResults = ref([]);
const searchLoading = ref(false);
const searchError = ref(null);
const showSearchResults = ref(false);
const searchInputRef = ref(null);

const logoutForm = useForm({});

const logout = () => {
    logoutForm.post(route("logout"));
};

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

// Keyboard navigation support
const handleKeydown = (event) => {
    // ESC key closes mobile menu
    if (event.key === "Escape" && mobileMenuOpen.value) {
        toggleMobileMenu();
    }
    // Ctrl/Cmd + B toggles sidebar
    if ((event.ctrlKey || event.metaKey) && event.key === "b") {
        event.preventDefault();
        toggleSidebar();
    }
};

// Touch and swipe support for mobile
let touchStartX = 0;
let touchEndX = 0;

const handleTouchStart = (event) => {
    touchStartX = event.changedTouches[0].screenX;
};

const handleTouchEnd = (event) => {
    touchEndX = event.changedTouches[0].screenX;
    handleSwipeGesture();
};

const handleSwipeGesture = () => {
    const swipeThreshold = 50;
    const swipeDistance = touchEndX - touchStartX;

    // Swipe right to open mobile menu (only on mobile)
    if (
        swipeDistance > swipeThreshold &&
        !mobileMenuOpen.value &&
        window.innerWidth < 1024
    ) {
        toggleMobileMenu();
    }
    // Swipe left to close mobile menu
    else if (swipeDistance < -swipeThreshold && mobileMenuOpen.value) {
        toggleMobileMenu();
    }
};

// Search functionality
let searchTimeout = null;

const performSearch = async (query) => {
    if (!query.trim()) {
        searchResults.value = [];
        showSearchResults.value = false;
        return;
    }

    searchLoading.value = true;
    searchError.value = null;

    try {
        const response = await fetch(route("search"), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({ query: query.trim() }),
        });

        if (!response.ok) {
            throw new Error("Search failed");
        }

        const data = await response.json();
        searchResults.value = data.results || [];
        showSearchResults.value = true;
    } catch (error) {
        searchError.value = "Search failed. Please try again.";
        searchResults.value = [];
        showSearchResults.value = false;
    } finally {
        searchLoading.value = false;
    }
};

const handleSearchInput = (event) => {
    const query = event.target.value;
    searchQuery.value = query;

    // Clear existing timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    // Debounce search with 300ms delay
    searchTimeout = setTimeout(() => {
        performSearch(query);
    }, 300);
};

const clearSearch = () => {
    searchQuery.value = "";
    searchResults.value = [];
    showSearchResults.value = false;
    searchError.value = null;
};

const handleSearchKeydown = (event) => {
    if (event.key === "Escape") {
        clearSearch();
        searchInputRef.value?.blur();
    }
};

const handleResultClick = (result) => {
    // Navigate to the result
    if (result.type === "property") {
        window.location.href = route("broker.properties.show", result.slug);
    } else if (result.type === "client") {
        window.location.href = route("clients.show", result.id);
    } else if (result.type === "transaction") {
        window.location.href = route("transactions.show", result.id);
    }
};

// Add keyboard event listener
onMounted(() => {
    document.addEventListener("keydown", handleKeydown);
    // Add touch event listeners for mobile swipe gestures
    document.addEventListener("touchstart", handleTouchStart, {
        passive: true,
    });
    document.addEventListener("touchend", handleTouchEnd, { passive: true });
});

onUnmounted(() => {
    document.removeEventListener("keydown", handleKeydown);
    document.removeEventListener("touchstart", handleTouchStart);
    document.removeEventListener("touchend", handleTouchEnd);
});

const navigationSections = computed(() => {
    const dashboard = {
        name: "Dashboard",
        href:
            userRole.value === "admin"
                ? route("admin.dashboard")
                : userRole.value === "broker"
                ? route("broker.dashboard")
                : route("client.dashboard"),
        icon: HomeIcon,
        current: route().current("*.dashboard"),
    };

    if (userRole.value === "admin") {
        return [
            {
                title: "Overview",
                items: [dashboard],
            },
            {
                title: "User Management",
                items: [
                    {
                        name: "Users",
                        href: route("admin.users.index"),
                        icon: UserGroupIcon,
                        current: route().current("admin.users.*"),
                    },
                    {
                        name: "Brokers",
                        href: route("admin.brokers.index"),
                        icon: UserGroupIcon,
                        current: route().current("admin.brokers.*"),
                    },
                    {
                        name: "Broker Approvals",
                        href: route("admin.broker-approvals.index"),
                        icon: DocumentCheckIcon,
                        current: route().current("admin.broker-approvals.*"),
                    },
                ],
            },
            {
                title: "Business Operations",
                items: [
                    {
                        name: "Properties",
                        href: route("admin.properties.index"),
                        icon: HomeModernIcon,
                        current: route().current("admin.properties.*"),
                    },
                    {
                        name: "Seller Requests",
                        href: route("seller-requests.index"),
                        icon: DocumentDuplicateIcon,
                        current: route().current("seller-requests.*"),
                    },
                    {
                        name: "Inquiries",
                        href: route("admin.reports.inquiries"),
                        icon: DocumentTextIcon,
                        current: route().current("admin.reports.inquiries"),
                    },
                    {
                        name: "Transactions",
                        href: route("admin.transactions.index"),
                        icon: CurrencyDollarIcon,
                        current: route().current("admin.transactions.*"),
                    },
                ],
            },
            {
                title: "Analytics & Reports",
                items: [
                    {
                        name: "Broker Analytics",
                        href: route("admin.reports.brokers"),
                        icon: PresentationChartBarIcon,
                        current: route().current("admin.reports.brokers"),
                    },
                    {
                        name: "Reports",
                        href: route("admin.reports.dashboard"),
                        icon: ChartBarIcon,
                        current: route().current("admin.reports.dashboard"),
                    },
                    {
                        name: "Activity Audit",
                        href: route("admin.activity.index"),
                        icon: ClipboardDocumentListIcon,
                        current: route().current("admin.activity.*"),
                    },
                    {
                        name: "Compliance",
                        href: route("admin.compliance.index"),
                        icon: ExclamationTriangleIcon,
                        current: route().current("admin.compliance.*"),
                    },
                ],
            },
        ];
    } else if (userRole.value === "broker") {
        return [
            {
                title: "Overview",
                items: [dashboard],
            },
            {
                title: "Property Management",
                items: [
                    {
                        name: "Properties",
                        href: route("broker.properties.index"),
                        icon: BuildingOfficeIcon,
                        current:
                            route().current("broker.properties.*") &&
                            !route().current("broker.properties.renewals"),
                    },
                    {
                        name: "Renewals",
                        href: route("broker.properties.renewals"),
                        icon: ClockIcon,
                        current: route().current("broker.properties.renewals"),
                    },
                    {
                        name: "Transactions",
                        href: route("transactions.index"),
                        icon: CurrencyDollarIcon,
                        current: route().current("transactions.*"),
                    },
                ],
            },
            {
                title: "Client Relations",
                items: [
                    {
                        name: "Clients",
                        href: route("clients.index"),
                        icon: UserGroupIcon,
                        current: route().current("clients.*"),
                    },
                    {
                        name: "Inquiries",
                        href: route("inquiries.index"),
                        icon: ChatBubbleLeftRightIcon,
                        current: route().current("inquiries.*"),
                    },
                    {
                        name: "Messages",
                        href: route("conversations.index"),
                        icon: ChatBubbleBottomCenterTextIcon,
                        current: route().current("conversations.*"),
                    },
                ],
            },
        ];
    } else {
        return [
            {
                title: "Overview",
                items: [dashboard],
            },
            {
                title: "Property Search",
                items: [
                    {
                        name: "Browse Properties",
                        href: route("public.properties"),
                        icon: BuildingOfficeIcon,
                        current: route().current("public.properties"),
                    },
                    {
                        name: "Saved Properties",
                        href: "#",
                        icon: HeartIcon,
                        current: false,
                    },
                ],
            },
            {
                title: "My Activity",
                items: [
                    {
                        name: "My Inquiries",
                        href: route("client.inquiries.index"),
                        icon: ChatBubbleLeftRightIcon,
                        current: route().current("client.inquiries.*"),
                    },
                    {
                        name: "Messages",
                        href: route("conversations.index"),
                        icon: ChatBubbleBottomCenterTextIcon,
                        current: route().current("conversations.*"),
                    },
                    {
                        name: "Sell Property",
                        href: route("seller-requests.create"),
                        icon: PlusIcon,
                        current: route().current("seller-requests.create"),
                    },
                ],
            },
        ];
    }
});
</script>

<template>
    <div class="min-h-screen bg-white overflow-x-hidden">
        <!-- Mobile menu overlay -->
        <Transition
            enter-active-class="transition-opacity ease-linear duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-0 z-40 lg:hidden"
                @click="toggleMobileMenu"
            >
                <div
                    class="fixed inset-0 bg-neutral-900 bg-opacity-50 backdrop-blur-sm"
                ></div>
            </div>
        </Transition>

        <!-- Sidebar -->
        <Transition
            enter-active-class="transition-transform ease-out duration-300"
            enter-from-class="-translate-x-full lg:translate-x-0"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform ease-in duration-300"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full lg:translate-x-0"
        >
            <div
                v-show="mobileMenuOpen || true"
                :class="[
                    'fixed inset-y-0 left-0 z-50 flex flex-col bg-gradient-sidebar border-r border-neutral-100 shadow-soft-lg transition-all duration-300 ease-in-out',
                    sidebarOpen ? 'w-80' : 'w-20',
                    'lg:translate-x-0',
                    mobileMenuOpen
                        ? 'translate-x-0'
                        : '-translate-x-full lg:translate-x-0',
                ]"
                role="navigation"
                :aria-label="`Main navigation - ${
                    sidebarOpen ? 'expanded' : 'collapsed'
                }`"
                aria-expanded="true"
            >
                <!-- Sidebar header with GeoCasa Bohol branding -->
                <div
                    class="flex items-center justify-between h-20 px-6 border-b border-neutral-50"
                >
                    <Link
                        href="/"
                        :class="[
                            'flex items-center gap-4 transition-all duration-300',
                            !sidebarOpen && 'lg:justify-center',
                        ]"
                    >
                        <!-- GeoCasa Bohol Logo -->
                        <div class="relative">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-primary-500 to-accent-500 rounded-2xl flex items-center justify-center shadow-soft"
                            >
                                <MapPinIcon class="w-6 h-6 text-white" />
                            </div>
                            <div
                                class="absolute -bottom-1 -right-1 w-4 h-4 bg-coconut-400 rounded-full border-2 border-white flex items-center justify-center"
                            >
                                <SunIcon class="w-2 h-2 text-coconut-800" />
                            </div>
                        </div>
                        <div
                            v-if="sidebarOpen"
                            class="lg:block transition-opacity duration-200"
                        >
                            <div class="text-xl font-bold text-neutral-900">
                                <span class="text-primary-600">Geo</span
                                ><span class="text-accent-600">Casa</span>
                            </div>
                            <div
                                class="text-sm font-medium text-coconut-600 -mt-1"
                            >
                                Bohol
                            </div>
                        </div>
                    </Link>

                    <!-- Desktop sidebar toggle button -->
                    <button
                        @click="toggleSidebar"
                        class="hidden lg:block p-2 rounded-xl hover:bg-neutral-50 transition-colors group"
                        :title="
                            sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'
                        "
                        :aria-label="
                            sidebarOpen ? 'Collapse sidebar' : 'Expand sidebar'
                        "
                        :aria-expanded="sidebarOpen"
                        type="button"
                    >
                        <ChevronLeftIcon
                            v-if="sidebarOpen"
                            class="w-5 h-5 text-neutral-400 group-hover:text-neutral-600 transition-colors"
                            aria-hidden="true"
                        />
                        <ChevronRightIcon
                            v-else
                            class="w-5 h-5 text-neutral-400 group-hover:text-neutral-600 transition-colors"
                            aria-hidden="true"
                        />
                    </button>

                    <!-- Mobile close button -->
                    <button
                        @click="toggleMobileMenu"
                        class="lg:hidden p-2 rounded-xl hover:bg-neutral-50 transition-colors"
                    >
                        <XMarkIcon class="w-5 h-5 text-neutral-500" />
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-6 py-6 overflow-y-auto sidebar-scroll">
                    <div
                        v-for="(section, sectionIndex) in navigationSections"
                        :key="section.title"
                        :class="[
                            'mb-8',
                            sectionIndex > 0 &&
                                'border-t border-neutral-100 pt-6',
                        ]"
                    >
                        <!-- Section Header -->
                        <div
                            v-if="sidebarOpen"
                            class="px-4 mb-4 transition-all duration-200"
                        >
                            <h3
                                class="text-xs font-semibold text-neutral-400 uppercase tracking-wider"
                            >
                                {{ section.title }}
                            </h3>
                        </div>

                        <!-- Section Items -->
                        <div class="space-y-1">
                            <Link
                                v-for="item in section.items"
                                :key="item.name"
                                :href="item.href"
                                :class="[
                                    'group flex items-center gap-4 px-4 py-3.5 text-sm font-medium rounded-2xl transition-all duration-200 relative',
                                    'hover:bg-primary-50 hover:text-primary-700 hover:shadow-soft hover:scale-[1.02]',
                                    'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
                                    'active:scale-[0.98]',
                                    item.current
                                        ? 'bg-gradient-to-r from-primary-50 to-primary-100/50 text-primary-700 shadow-soft border border-primary-200/50 font-semibold'
                                        : 'text-neutral-600 hover:bg-gradient-to-r hover:from-primary-50 hover:to-transparent',
                                    !sidebarOpen && 'lg:justify-center lg:px-4',
                                ]"
                                :title="!sidebarOpen ? item.name : ''"
                            >
                                <!-- Active indicator -->
                                <div
                                    v-if="item.current"
                                    class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-primary-500 rounded-r-full"
                                ></div>

                                <component
                                    :is="item.icon"
                                    :class="[
                                        'w-5 h-5 flex-shrink-0 transition-all duration-200',
                                        item.current
                                            ? 'text-primary-600 scale-110'
                                            : 'text-neutral-400 group-hover:text-primary-600 group-hover:scale-110',
                                    ]"
                                />
                                <span
                                    v-if="sidebarOpen"
                                    class="lg:block transition-all duration-200 group-hover:translate-x-0.5"
                                >
                                    {{ item.name }}
                                </span>

                                <!-- Hover effect -->
                                <div
                                    class="absolute inset-0 rounded-2xl bg-gradient-to-r from-primary-500/0 to-primary-500/0 group-hover:from-primary-500/5 group-hover:to-transparent transition-all duration-200"
                                ></div>
                            </Link>
                        </div>
                    </div>
                </nav>

                <!-- User section -->
                <div
                    class="border-t border-neutral-100 bg-gradient-to-b from-neutral-50/50 to-white p-6"
                >
                    <!-- User Profile Card -->
                    <div
                        v-if="sidebarOpen"
                        class="mb-6 p-4 bg-white rounded-2xl shadow-soft border border-neutral-100 transition-all duration-200 hover:shadow-soft-md"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-primary-400 to-accent-400 rounded-2xl flex items-center justify-center text-white font-medium text-sm shadow-soft"
                            >
                                {{ user?.name?.charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div
                                    class="text-sm font-semibold text-neutral-900 truncate"
                                >
                                    {{ user?.name }}
                                </div>
                                <div
                                    class="text-xs text-neutral-500 capitalize"
                                >
                                    {{ userRole }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Section -->
                    <div class="space-y-2 mb-4">
                        <!-- Notification Settings -->
                        <Link
                            :href="route('notifications.settings')"
                            :class="[
                                'group flex items-center gap-4 px-4 py-3 text-sm font-medium rounded-2xl transition-all duration-200 relative',
                                'hover:bg-neutral-50 hover:text-neutral-900 hover:scale-[1.02]',
                                'focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2',
                                'active:scale-[0.98]',
                                !sidebarOpen && 'lg:justify-center lg:px-4',
                                route().current('notifications.settings')
                                    ? 'bg-primary-50 text-primary-700 border border-primary-100 font-semibold'
                                    : 'text-neutral-600',
                            ]"
                            :title="!sidebarOpen ? 'Notification Settings' : ''"
                        >
                            <Cog6ToothIcon
                                :class="[
                                    'w-5 h-5 flex-shrink-0 transition-all duration-200',
                                    route().current('notifications.settings')
                                        ? 'text-primary-600 scale-110'
                                        : 'text-neutral-400 group-hover:text-neutral-600 group-hover:scale-110',
                                ]"
                            />
                            <span
                                v-if="sidebarOpen"
                                class="lg:block transition-all duration-200 group-hover:translate-x-0.5"
                            >
                                Settings
                            </span>
                            <div
                                class="absolute inset-0 rounded-2xl bg-gradient-to-r from-neutral-500/0 to-neutral-500/0 group-hover:from-neutral-500/5 group-hover:to-transparent transition-all duration-200"
                            ></div>
                        </Link>

                        <!-- Logout -->
                        <button
                            @click="logout"
                            :class="[
                                'group flex items-center gap-4 px-4 py-3 text-sm font-medium rounded-2xl transition-all duration-200 w-full relative',
                                'hover:bg-red-50 hover:text-red-600 hover:scale-[1.02]',
                                'focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2',
                                'active:scale-[0.98]',
                                'text-neutral-600',
                                !sidebarOpen && 'lg:justify-center lg:px-4',
                            ]"
                            :title="!sidebarOpen ? 'Logout' : ''"
                        >
                            <ArrowRightOnRectangleIcon
                                class="w-5 h-5 flex-shrink-0 text-neutral-400 group-hover:text-red-500 group-hover:scale-110 transition-all duration-200"
                            />
                            <span
                                v-if="sidebarOpen"
                                class="lg:block transition-all duration-200 group-hover:translate-x-0.5"
                            >
                                Logout
                            </span>
                            <div
                                class="absolute inset-0 rounded-2xl bg-gradient-to-r from-red-500/0 to-red-500/0 group-hover:from-red-500/5 group-hover:to-transparent transition-all duration-200"
                            ></div>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Main content area -->
        <div
            :class="[
                'min-h-screen transition-all duration-300 ease-in-out',
                sidebarOpen ? 'lg:ml-80' : 'lg:ml-20',
            ]"
        >
            <!-- Top header -->
            <header
                class="bg-white/80 backdrop-blur-xl border-b border-neutral-100 sticky top-0 z-40 relative"
            >
                <div
                    class="flex items-center justify-between h-20 px-6 lg:px-8"
                >
                    <!-- Mobile menu button -->
                    <button
                        @click="toggleMobileMenu"
                        class="lg:hidden p-3 rounded-xl text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all duration-200 active:scale-95 touch-manipulation"
                        :aria-label="
                            mobileMenuOpen
                                ? 'Close navigation menu'
                                : 'Open navigation menu'
                        "
                        :aria-expanded="mobileMenuOpen"
                        type="button"
                    >
                        <Transition
                            enter-active-class="transition-transform duration-200"
                            enter-from-class="rotate-180 scale-75"
                            enter-to-class="rotate-0 scale-100"
                            leave-active-class="transition-transform duration-200"
                            leave-from-class="rotate-0 scale-100"
                            leave-to-class="rotate-180 scale-75"
                            mode="out-in"
                        >
                            <XMarkIcon
                                v-if="mobileMenuOpen"
                                key="close"
                                class="w-6 h-6 transition-transform duration-200"
                                aria-hidden="true"
                            />
                            <Bars3Icon
                                v-else
                                key="menu"
                                class="w-6 h-6 transition-transform duration-200"
                                aria-hidden="true"
                            />
                        </Transition>
                    </button>

                    <!-- Mobile search button -->
                    <button
                        @click="showMobileSearch = !showMobileSearch"
                        class="md:hidden p-3 rounded-xl text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all duration-200 active:scale-95 touch-manipulation"
                        aria-label="Toggle search"
                        type="button"
                    >
                        <MagnifyingGlassIcon class="w-6 h-6" />
                    </button>

                    <!-- Mobile search bar -->
                    <div
                        v-if="showMobileSearch"
                        class="absolute top-full left-0 right-0 p-4 bg-white border-b border-neutral-100 md:hidden z-30"
                    >
                        <div class="relative">
                            <MagnifyingGlassIcon
                                class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400"
                            />
                            <input
                                id="mobile-search"
                                name="mobile-search"
                                type="text"
                                v-model="searchQuery"
                                @input="handleSearchInput"
                                @keydown="handleSearchKeydown"
                                placeholder="Search properties, clients, or transactions..."
                                class="w-full pl-12 pr-4 py-3 bg-neutral-50 border-0 rounded-2xl text-sm placeholder-neutral-500 focus:bg-white focus:ring-2 focus:ring-primary-500 transition-all duration-200"
                                :class="{ 'pr-10': searchLoading }"
                            />

                            <!-- Loading spinner -->
                            <div
                                v-if="searchLoading"
                                class="absolute right-4 top-1/2 transform -translate-y-1/2"
                            >
                                <div
                                    class="animate-spin rounded-full h-4 w-4 border-2 border-primary-500 border-t-transparent"
                                ></div>
                            </div>
                        </div>

                        <!-- Mobile search results -->
                        <div
                            v-if="showSearchResults || searchError"
                            class="mt-2 bg-white rounded-2xl shadow-xl border border-neutral-200 max-h-64 overflow-y-auto"
                        >
                            <!-- Error state -->
                            <div
                                v-if="searchError"
                                class="p-4 text-center text-red-600 text-sm"
                            >
                                {{ searchError }}
                            </div>

                            <!-- No results -->
                            <div
                                v-else-if="
                                    searchResults.length === 0 &&
                                    searchQuery.trim()
                                "
                                class="p-4 text-center text-neutral-500 text-sm"
                            >
                                No results found for "{{ searchQuery }}"
                            </div>

                            <!-- Results -->
                            <div v-else class="py-2">
                                <div
                                    v-for="result in searchResults"
                                    :key="`mobile-${result.type}-${result.id}`"
                                    @click="handleResultClick(result)"
                                    class="px-4 py-3 hover:bg-neutral-50 cursor-pointer border-b border-neutral-100 last:border-b-0 transition-colors duration-150"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <HomeModernIcon
                                                v-if="
                                                    result.type === 'property'
                                                "
                                                class="w-5 h-5 text-primary-500"
                                            />
                                            <UsersIcon
                                                v-else-if="
                                                    result.type === 'client'
                                                "
                                                class="w-5 h-5 text-accent-500"
                                            />
                                            <CurrencyDollarIcon
                                                v-else-if="
                                                    result.type ===
                                                    'transaction'
                                                "
                                                class="w-5 h-5 text-green-500"
                                            />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div
                                                class="text-sm font-medium text-neutral-900 truncate"
                                            >
                                                {{ result.title }}
                                            </div>
                                            <div
                                                class="text-xs text-neutral-500 truncate"
                                            >
                                                {{ result.subtitle }}
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-primary-100 text-primary-800':
                                                        result.type ===
                                                        'property',
                                                    'bg-accent-100 text-accent-800':
                                                        result.type ===
                                                        'client',
                                                    'bg-green-100 text-green-800':
                                                        result.type ===
                                                        'transaction',
                                                }"
                                            >
                                                {{ result.type }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop search bar -->
                    <div class="hidden md:flex flex-1 max-w-lg mx-8">
                        <div class="relative w-full">
                            <MagnifyingGlassIcon
                                class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400"
                            />
                            <input
                                ref="searchInputRef"
                                id="global-search"
                                name="search"
                                type="text"
                                v-model="searchQuery"
                                @input="handleSearchInput"
                                @keydown="handleSearchKeydown"
                                placeholder="Search properties, clients, or transactions..."
                                class="w-full pl-12 pr-4 py-3 bg-neutral-50 border-0 rounded-2xl text-sm placeholder-neutral-500 focus:bg-white focus:ring-2 focus:ring-primary-500 transition-all duration-200"
                                :class="{ 'pr-10': searchLoading }"
                            />

                            <!-- Loading spinner -->
                            <div
                                v-if="searchLoading"
                                class="absolute right-4 top-1/2 transform -translate-y-1/2"
                            >
                                <div
                                    class="animate-spin rounded-full h-4 w-4 border-2 border-primary-500 border-t-transparent"
                                ></div>
                            </div>

                            <!-- Search results dropdown -->
                            <div
                                v-if="showSearchResults || searchError"
                                class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-neutral-200 max-h-96 overflow-y-auto z-50"
                            >
                                <!-- Error state -->
                                <div
                                    v-if="searchError"
                                    class="p-4 text-center text-red-600 text-sm"
                                >
                                    {{ searchError }}
                                </div>

                                <!-- No results -->
                                <div
                                    v-else-if="
                                        searchResults.length === 0 &&
                                        searchQuery.trim()
                                    "
                                    class="p-4 text-center text-neutral-500 text-sm"
                                >
                                    No results found for "{{ searchQuery }}"
                                </div>

                                <!-- Results -->
                                <div v-else class="py-2">
                                    <div
                                        v-for="result in searchResults"
                                        :key="`${result.type}-${result.id}`"
                                        @click="handleResultClick(result)"
                                        class="px-4 py-3 hover:bg-neutral-50 cursor-pointer border-b border-neutral-100 last:border-b-0 transition-colors duration-150"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0">
                                                <HomeModernIcon
                                                    v-if="
                                                        result.type ===
                                                        'property'
                                                    "
                                                    class="w-5 h-5 text-primary-500"
                                                />
                                                <UsersIcon
                                                    v-else-if="
                                                        result.type === 'client'
                                                    "
                                                    class="w-5 h-5 text-accent-500"
                                                />
                                                <CurrencyDollarIcon
                                                    v-else-if="
                                                        result.type ===
                                                        'transaction'
                                                    "
                                                    class="w-5 h-5 text-green-500"
                                                />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div
                                                    class="text-sm font-medium text-neutral-900 truncate"
                                                >
                                                    {{ result.title }}
                                                </div>
                                                <div
                                                    class="text-xs text-neutral-500 truncate"
                                                >
                                                    {{ result.subtitle }}
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                    :class="{
                                                        'bg-primary-100 text-primary-800':
                                                            result.type ===
                                                            'property',
                                                        'bg-accent-100 text-accent-800':
                                                            result.type ===
                                                            'client',
                                                        'bg-green-100 text-green-800':
                                                            result.type ===
                                                            'transaction',
                                                    }"
                                                >
                                                    {{ result.type }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right side actions -->
                    <div class="flex items-center gap-4">
                        <!-- Notifications -->
                        <NotificationDropdown :notifications="notifications" />

                        <!-- User menu -->
                        <div
                            class="flex items-center gap-3 pl-4 border-l border-neutral-200"
                        >
                            <div class="text-right hidden sm:block">
                                <div
                                    class="text-sm font-medium text-neutral-900"
                                >
                                    {{ user?.name }}
                                </div>
                                <div
                                    class="text-xs text-neutral-500 capitalize"
                                >
                                    {{ userRole }} (Auth:
                                    {{ $page.props.auth.user?.role || "none" }})
                                    [{{ navigationSections.length }} sections]
                                </div>
                            </div>
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-primary-400 to-accent-400 rounded-2xl flex items-center justify-center text-white font-medium text-sm shadow-soft"
                            >
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

        <!-- Notification Toast -->
        <NotificationToast ref="notificationToast" />
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
