<script setup>
import { computed, ref } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import NotificationDropdown from "@/Components/NotificationDropdown.vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const userRole = computed(() => user.value?.role || "client");
const mobileMenuOpen = ref(false);

const logoutForm = useForm({});

const logout = () => {
    logoutForm.post(route("logout"));
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};
</script>

<template>
    <div class="min-h-screen bg-coconut-50">
        <!-- Navigation -->
        <nav class="bg-white/95 backdrop-blur-sm shadow-soft border-b border-coconut-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo and Brand -->
                    <div class="flex items-center">
                        <a href="/" class="flex items-center space-x-3 group">
                            <div class="w-8 h-8 bg-gradient-to-br from-visayan-500 to-visayan-600 rounded-xl flex items-center justify-center shadow-soft group-hover:shadow-soft-lg transition-all duration-150">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h1 class="text-xl font-serif font-bold text-dark-800 group-hover:text-visayan-600 transition-colors duration-150">
                                    GeoCasa Bohol
                                </h1>
                                <p class="text-xs text-dark-500 -mt-1">Real Estate</p>
                            </div>
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center space-x-1">
                        <!-- Notifications (only for authenticated users) -->
                        <NotificationDropdown
                            v-if="$page.props.auth.user"
                            :notifications="$page.props.notifications || []"
                        />

                        <!-- Client Navigation -->
                        <template v-if="userRole === 'client' || !user">
                            <a
                                href="/"
                                class="px-3 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                >Home</a
                            >
                            <a
                                href="/browse-properties"
                                class="px-3 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                >Properties</a
                            >
                            <a
                                href="/sell-property"
                                class="px-3 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                >Sell Property</a
                            >
                            <a
                                href="/leaderboard"
                                class="px-3 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                >Top Brokers</a
                            >
                            <template v-if="!user">
                                <a
                                    href="/login"
                                    class="px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                    >Login</a
                                >
                                <a
                                    href="/register"
                                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-visayan-500 to-visayan-600 text-white hover:from-visayan-600 hover:to-visayan-700 shadow-soft hover:shadow-soft-lg transition-all duration-150 font-medium"
                                    >Register</a
                                >
                            </template>
                            <template v-else>
                                <div class="flex items-center space-x-3 ml-4 pl-4 border-l border-coconut-200">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 bg-gradient-to-br from-sand-400 to-sand-500 rounded-full flex items-center justify-center">
                                            <span class="text-white text-sm font-medium">{{ user.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-dark-700">{{ user.name }}</span>
                                    </div>
                                    <button
                                        @click="logout"
                                        class="px-3 py-1.5 rounded-lg text-dark-500 hover:text-red-600 hover:bg-red-50 transition-all duration-150 text-sm font-medium"
                                    >
                                        Logout
                                    </button>
                                </div>
                            </template>
                        </template>

                        <!-- Broker Navigation -->
                        <template v-else-if="userRole === 'broker'">
                            <a
                                href="/broker/dashboard"
                                class="px-3 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                >Dashboard</a
                            >
                            <a
                                href="/properties"
                                class="px-3 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                >Properties</a
                            >
                            <a
                                href="/clients"
                                class="px-3 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                >Clients</a
                            >
                            <a
                                href="/inquiries"
                                class="px-3 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                >Inquiries</a
                            >
                            <div class="flex items-center space-x-3 ml-4 pl-4 border-l border-coconut-200">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gradient-to-br from-visayan-500 to-visayan-600 rounded-full flex items-center justify-center">
                                        <span class="text-white text-sm font-medium">{{ user.name.charAt(0).toUpperCase() }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-dark-700">{{ user.name }}</p>
                                        <p class="text-xs text-visayan-600 font-medium">Broker</p>
                                    </div>
                                </div>
                                <button
                                    @click="logout"
                                    class="px-3 py-1.5 rounded-lg text-dark-500 hover:text-red-600 hover:bg-red-50 transition-all duration-150 text-sm font-medium"
                                >
                                    Logout
                                </button>
                            </div>
                        </template>

                        <!-- Admin Navigation -->
                        <template v-else-if="userRole === 'admin'">
                            <a
                                href="/admin/dashboard"
                                class="px-3 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                >Dashboard</a
                            >
                            <a
                                href="/admin/brokers"
                                class="px-3 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                >Brokers</a
                            >
                            <a
                                href="/properties"
                                class="px-3 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                >Properties</a
                            >
                            <a
                                href="/transactions"
                                class="px-3 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium"
                                >Transactions</a
                            >
                            <div class="flex items-center space-x-3 ml-4 pl-4 border-l border-coconut-200">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gradient-to-br from-sand-500 to-sand-600 rounded-full flex items-center justify-center">
                                        <span class="text-white text-sm font-medium">{{ user.name.charAt(0).toUpperCase() }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-dark-700">{{ user.name }}</p>
                                        <p class="text-xs text-sand-600 font-medium">Admin</p>
                                    </div>
                                </div>
                                <button
                                    @click="logout"
                                    class="px-3 py-1.5 rounded-lg text-dark-500 hover:text-red-600 hover:bg-red-50 transition-all duration-150 text-sm font-medium"
                                >
                                    Logout
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center">
                        <button
                            @click="toggleMobileMenu"
                            class="p-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Navigation Menu -->
                <div v-if="mobileMenuOpen" class="md:hidden border-t border-coconut-200 py-4">
                    <div class="space-y-2">
                        <!-- Client Mobile Navigation -->
                        <template v-if="userRole === 'client' || !user">
                            <a href="/" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Home</a>
                            <a href="/browse-properties" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Properties</a>
                            <a href="/sell-property" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Sell Property</a>
                            <a href="/leaderboard" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Top Brokers</a>
                            <template v-if="!user">
                                <a href="/login" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Login</a>
                                <a href="/register" class="block px-4 py-2 rounded-xl bg-gradient-to-r from-visayan-500 to-visayan-600 text-white font-medium">Register</a>
                            </template>
                            <template v-else>
                                <div class="px-4 py-2 border-t border-coconut-200 mt-2 pt-4">
                                    <p class="text-sm font-medium text-dark-700 mb-2">{{ user.name }}</p>
                                    <button @click="logout" class="text-red-600 hover:text-red-700 text-sm font-medium">Logout</button>
                                </div>
                            </template>
                        </template>

                        <!-- Broker Mobile Navigation -->
                        <template v-else-if="userRole === 'broker'">
                            <a href="/broker/dashboard" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Dashboard</a>
                            <a href="/properties" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Properties</a>
                            <a href="/clients" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Clients</a>
                            <a href="/inquiries" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Inquiries</a>
                            <div class="px-4 py-2 border-t border-coconut-200 mt-2 pt-4">
                                <p class="text-sm font-medium text-dark-700">{{ user.name }}</p>
                                <p class="text-xs text-visayan-600 font-medium mb-2">Broker</p>
                                <button @click="logout" class="text-red-600 hover:text-red-700 text-sm font-medium">Logout</button>
                            </div>
                        </template>

                        <!-- Admin Mobile Navigation -->
                        <template v-else-if="userRole === 'admin'">
                            <a href="/admin/dashboard" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Dashboard</a>
                            <a href="/admin/brokers" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Brokers</a>
                            <a href="/properties" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Properties</a>
                            <a href="/transactions" class="block px-4 py-2 rounded-xl text-dark-600 hover:text-visayan-600 hover:bg-visayan-50 transition-all duration-150 font-medium">Transactions</a>
                            <div class="px-4 py-2 border-t border-coconut-200 mt-2 pt-4">
                                <p class="text-sm font-medium text-dark-700">{{ user.name }}</p>
                                <p class="text-xs text-sand-600 font-medium mb-2">Admin</p>
                                <button @click="logout" class="text-red-600 hover:text-red-700 text-sm font-medium">Logout</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Flash Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
            <div
                v-if="$page.props.flash.message"
                class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 text-green-800 px-6 py-4 rounded-2xl shadow-soft mb-4 flex items-center space-x-3"
            >
                <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="font-medium">{{ $page.props.flash.message }}</p>
            </div>

            <div
                v-if="$page.props.flash.error"
                class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 text-red-800 px-6 py-4 rounded-2xl shadow-soft mb-4 flex items-center space-x-3"
            >
                <div class="w-5 h-5 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="font-medium">{{ $page.props.flash.error }}</p>
            </div>
        </div>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white/80 backdrop-blur-sm border-t border-coconut-200 mt-16">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-visayan-500 to-visayan-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-serif font-bold text-dark-800">GeoCasa Bohol</h3>
                            <p class="text-sm text-dark-500">Your trusted real estate partner</p>
                        </div>
                    </div>
                    <p class="text-center text-dark-500 text-sm">
                        © 2024 GeoCasa Bohol Real Estate System. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
