<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from "vue";
import { Link, usePage, useForm, router } from "@inertiajs/vue3";
import { useAccessibility } from "@/Composables/useAccessibility";
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
import GeoCasaLogo from "@/Components/GeoCasaLogo.vue";
import FlashMessage from "@/Components/FlashMessage.vue";
import UserAvatar from "@/Components/UserAvatar.vue";

const page = usePage();
const { isKeyboardUser, announce, generateId } = useAccessibility();

const user = computed(() => page.props.auth?.user);
const userRole = computed(() => {
    return page.props.auth?.user?.role || "client";
});
const notifications = computed(() => page.props.notifications || []);

const sidebarOpen = ref(true);
const mobileMenuOpen = ref(false);
const showMobileSearch = ref(false);

// Sidebar scroll position preservation
const sidebarScrollRef = ref(null);
const savedScrollPosition = ref(0);

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
        // Get CSRF token
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        if (!csrfToken) {
            throw new Error("CSRF token not found");
        }

        const response = await fetch(route("search"), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({ query: query.trim() }),
            credentials: "same-origin",
        });

        if (!response.ok) {
            if (response.status === 419) {
                throw new Error("Session expired. Please refresh the page.");
            }
            throw new Error("Search failed");
        }

        const data = await response.json();
        searchResults.value = data.results || [];
        showSearchResults.value = true;
    } catch (error) {
        searchError.value = error.message || "Search failed. Please try again.";
        searchResults.value = [];
        showSearchResults.value = false;
        console.error("Search error:", error);
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
    if (result.type === "property" && result.slug) {
        window.location.href = route("broker.properties.show", result.slug);
    } else if (result.type === "client" && result.id) {
        window.location.href = route("clients.show", result.id);
    } else if (result.type === "transaction" && result.id) {
        window.location.href = route("transactions.show", result.id);
    }

    // Clear search after navigation
    clearSearch();
};

// Preserve sidebar scroll position using localStorage
const SIDEBAR_SCROLL_KEY = "sidebar_scroll_position";

const saveSidebarScroll = () => {
    if (sidebarScrollRef.value) {
        const scrollTop = sidebarScrollRef.value.scrollTop;
        localStorage.setItem(SIDEBAR_SCROLL_KEY, scrollTop.toString());
        savedScrollPosition.value = scrollTop;
    }
};

const restoreSidebarScroll = () => {
    const savedScroll = localStorage.getItem(SIDEBAR_SCROLL_KEY);
    if (savedScroll && sidebarScrollRef.value) {
        const scrollValue = parseInt(savedScroll, 10);
        // Use requestAnimationFrame to ensure DOM is ready
        requestAnimationFrame(() => {
            if (sidebarScrollRef.value) {
                sidebarScrollRef.value.scrollTop = scrollValue;
            }
        });
    }
};

// Add scroll event listener to save position
const handleSidebarScroll = () => {
    saveSidebarScroll();
};

// Watch for sidebar ref to become available
watch(sidebarScrollRef, (newVal) => {
    if (newVal) {
        // Restore scroll when ref is available
        restoreSidebarScroll();
        // Add scroll listener
        newVal.addEventListener("scroll", handleSidebarScroll, {
            passive: true,
        });
    }
});

// Add keyboard event listener
onMounted(() => {
    document.addEventListener("keydown", handleKeydown);
    // Add touch event listeners for mobile swipe gestures
    document.addEventListener("touchstart", handleTouchStart, {
        passive: true,
    });
    document.addEventListener("touchend", handleTouchEnd, { passive: true });

    // Restore scroll position on mount with multiple attempts
    setTimeout(() => restoreSidebarScroll(), 50);
    setTimeout(() => restoreSidebarScroll(), 200);
    setTimeout(() => restoreSidebarScroll(), 500);
});

onUnmounted(() => {
    document.removeEventListener("keydown", handleKeydown);
    document.removeEventListener("touchstart", handleTouchStart);
    document.removeEventListener("touchend", handleTouchEnd);

    // Remove scroll listener
    if (sidebarScrollRef.value) {
        sidebarScrollRef.value.removeEventListener(
            "scroll",
            handleSidebarScroll
        );
    }
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
        current:
            (route().current("admin.dashboard") &&
                !route().current("admin.reports.dashboard")) ||
            route().current("broker.dashboard") ||
            route().current("client.dashboard"),
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
                        name: "Client Assignments",
                        href: route("admin.client-assignments"),
                        icon: DocumentDuplicateIcon,
                        current: route().current("admin.client-assignments"),
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
                        name: "Analytics Hub",
                        href: route("admin.reports.dashboard"),
                        icon: ChartBarIcon,
                        current:
                            route().current("admin.reports.dashboard") ||
                            route().current("admin.analytics.*") ||
                            route().current("admin.activity.*"),
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
                        name: "My Properties",
                        href: route("broker.properties.index"),
                        icon: BuildingOfficeIcon,
                        current: route().current("broker.properties.index"),
                    },
                    {
                        name: "Add Property",
                        href: route("broker.properties.create"),
                        icon: PlusIcon,
                        current: route().current("broker.properties.create"),
                    },
                    {
                        name: "Property Renewals",
                        href: route("broker.properties.renewals"),
                        icon: ClockIcon,
                        current: route().current("broker.properties.renewals"),
                    },
                ],
            },
            {
                title: "Client Management",
                items: [
                    {
                        name: "My Clients",
                        href: route("clients.index"),
                        icon: UserGroupIcon,
                        current: route().current("clients.*"),
                    },
                    {
                        name: "Assigned Requests",
                        href: route("seller-requests.index"),
                        icon: DocumentDuplicateIcon,
                        current: route().current("seller-requests.*"),
                    },
                    {
                        name: "Inquiries",
                        href: route("inquiries.index"),
                        icon: ChatBubbleLeftRightIcon,
                        current: route().current("inquiries.*"),
                    },
                ],
            },
            {
                title: "Business Operations",
                items: [
                    {
                        name: "Transactions",
                        href: route("transactions.index"),
                        icon: CurrencyDollarIcon,
                        current: route().current("transactions.*"),
                    },
                    {
                        name: "Messages",
                        href: route("conversations.index"),
                        icon: ChatBubbleBottomCenterTextIcon,
                        current: route().current("conversations.*"),
                    },
                ],
            },
            {
                title: "Analytics & Reports",
                items: [
                    {
                        name: "Analytics",
                        href: route("broker.analytics"),
                        icon: ChartBarIcon,
                        current: route().current("broker.analytics"),
                    },
                    {
                        name: "Reports",
                        href: route("broker.reports"),
                        icon: DocumentTextIcon,
                        current: route().current("broker.reports"),
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
                        href: route("client.properties"),
                        icon: BuildingOfficeIcon,
                        current: route().current("client.properties"),
                    },
                    {
                        name: "Saved Properties",
                        href: route("client.properties.saved"),
                        icon: HeartIcon,
                        current: route().current("client.properties.saved"),
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
                        name: "My Broker",
                        href: route("client.broker"),
                        icon: UserGroupIcon,
                        current: route().current("client.broker"),
                    },
                    {
                        name: "Messages",
                        href: route("conversations.index"),
                        icon: ChatBubbleBottomCenterTextIcon,
                        current: route().current("conversations.*"),
                    },
                    {
                        name: "List My Land for Sale",
                        href: route("client.seller-requests.create"),
                        icon: HomeModernIcon,
                        current: route().current(
                            "client.seller-requests.create"
                        ),
                    },
                    {
                        name: "My Listing Requests",
                        href: route("client.seller-requests.index"),
                        icon: ClipboardDocumentListIcon,
                        current:
                            route().current("client.seller-requests.index") ||
                            route().current("client.seller-requests.show"),
                    },
                ],
            },
        ];
    }
});
</script>

<template>
    <div class="min-h-screen bg-white overflow-x-hidden">
        <!-- Flash Messages -->
        <FlashMessage />

        <!-- Skip to main content link -->
        <a
            href="#main-content"
            class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-lg focus:shadow-lg"
        >
            Skip to main content
        </a>

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
                :aria-expanded="sidebarOpen"
                :aria-hidden="!sidebarOpen"
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
                        <GeoCasaLogo
                            :size="sidebarOpen ? 'medium' : 'small'"
                            variant="default"
                            :show-text="sidebarOpen"
                        />
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
                <nav
                    ref="sidebarScrollRef"
                    class="flex-1 px-6 py-6 overflow-y-auto sidebar-scroll"
                >
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
                                preserve-scroll
                                :class="[
                                    'sidebar-link group flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 relative',
                                    'hover:bg-neutral-100 hover:text-neutral-900',
                                    isKeyboardUser
                                        ? 'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2'
                                        : 'focus:outline-none',
                                    item.current
                                        ? 'bg-primary-50 text-primary-700 border border-primary-200 shadow-card font-semibold'
                                        : 'text-neutral-600',
                                    !sidebarOpen && 'lg:justify-center lg:px-4',
                                ]"
                                :title="!sidebarOpen ? item.name : ''"
                                :aria-current="
                                    item.current ? 'page' : undefined
                                "
                                :aria-label="
                                    !sidebarOpen ? item.name : undefined
                                "
                                role="menuitem"
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
                                            ? 'text-primary-600'
                                            : 'text-neutral-400 group-hover:text-neutral-600',
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
                            <UserAvatar
                                v-if="user"
                                :user="user"
                                size="sm"
                                class="shadow-card"
                            />
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
                        <!-- Account Settings -->
                        <Link
                            :href="route('account.settings')"
                            :class="[
                                'group flex items-center gap-4 px-4 py-3 text-sm font-medium rounded-2xl transition-all duration-200 relative',
                                'hover:bg-neutral-50 hover:text-neutral-900 hover:scale-[1.02]',
                                'focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2',
                                'active:scale-[0.98]',
                                !sidebarOpen && 'lg:justify-center lg:px-4',
                                route().current('account.settings')
                                    ? 'bg-primary-50 text-primary-700 border border-primary-100 font-semibold'
                                    : 'text-neutral-600',
                            ]"
                            :title="!sidebarOpen ? 'Account Settings' : ''"
                        >
                            <Cog6ToothIcon
                                :class="[
                                    'w-5 h-5 flex-shrink-0 transition-all duration-200',
                                    route().current('account.settings')
                                        ? 'text-primary-600 scale-110'
                                        : 'text-neutral-400 group-hover:text-neutral-600 group-hover:scale-110',
                                ]"
                            />
                            <span
                                v-if="sidebarOpen"
                                class="lg:block transition-all duration-200 group-hover:translate-x-0.5"
                            >
                                Account Settings
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
                class="bg-white/80 backdrop-blur-xl border-b border-neutral-100 sticky top-0 z-40"
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

                    <!-- Right side actions -->
                    <div class="flex items-center gap-4 ml-auto">
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
                            <UserAvatar
                                v-if="user"
                                :user="user"
                                size="md"
                                class="shadow-card"
                            />
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main id="main-content" class="p-6 lg:p-8 min-h-screen" role="main">
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

/* Screen reader only content */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.sr-only:focus {
    position: static;
    width: auto;
    height: auto;
    padding: inherit;
    margin: inherit;
    overflow: visible;
    clip: auto;
    white-space: normal;
}

/* Focus styles for keyboard users */
.focus\:ring-2:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
    box-shadow: 0 0 0 2px var(--tw-ring-color);
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .bg-primary-50 {
        background-color: #dbeafe;
    }

    .text-primary-600 {
        color: #1d4ed8;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .transition-all,
    .transition-transform,
    .transition-opacity,
    .animate-pulse,
    .animate-spin,
    .animate-fade-in {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
