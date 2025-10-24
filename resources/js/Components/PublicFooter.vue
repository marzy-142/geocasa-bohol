<template>
    <footer class="bg-neutral-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="relative">
                            <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-accent-500 rounded-2xl flex items-center justify-center shadow-soft">
                                <MapPinIcon class="w-6 h-6 text-white" />
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-coconut-400 rounded-full border-2 border-neutral-900 flex items-center justify-center">
                                <SunIcon class="w-2 h-2 text-coconut-800" />
                            </div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">
                                <span class="text-primary-400">Geo</span><span class="text-accent-400">Casa</span>
                            </div>
                            <div class="text-sm font-medium text-coconut-400 -mt-1">Bohol</div>
                        </div>
                    </div>
                    <p class="text-neutral-300 mb-6 max-w-md">
                        Your trusted partner in finding the perfect real estate opportunities across the beautiful island of Bohol. Licensed brokers, verified properties, secure transactions.
                    </p>
                    <div class="flex gap-4">
                        <a href="mailto:info@geocasabohol.com" class="w-10 h-10 bg-neutral-800 rounded-xl flex items-center justify-center hover:bg-primary-600 transition-colors cursor-pointer group">
                            <EnvelopeIcon class="w-5 h-5 text-neutral-400 group-hover:text-white" />
                        </a>
                        <a href="tel:+631234567890" class="w-10 h-10 bg-neutral-800 rounded-xl flex items-center justify-center hover:bg-primary-600 transition-colors cursor-pointer group">
                            <PhoneIcon class="w-5 h-5 text-neutral-400 group-hover:text-white" />
                        </a>
                        <div class="w-10 h-10 bg-neutral-800 rounded-xl flex items-center justify-center hover:bg-primary-600 transition-colors cursor-pointer group">
                            <GlobeAltIcon class="w-5 h-5 text-neutral-400 group-hover:text-white" />
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-white">Quick Links</h3>
                    <ul class="space-y-3">
                        <li>
                            <Link :href="route('home')" class="text-neutral-300 hover:text-primary-400 transition-colors flex items-center gap-2">
                                <HomeIcon class="w-4 h-4" />
                                Home
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('public.properties')" class="text-neutral-300 hover:text-primary-400 transition-colors flex items-center gap-2">
                                <BuildingOfficeIcon class="w-4 h-4" />
                                Properties
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('seller-requests.create')" class="text-neutral-300 hover:text-primary-400 transition-colors flex items-center gap-2">
                                <PlusCircleIcon class="w-4 h-4" />
                                Sell Property
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('leaderboard.index')" class="text-neutral-300 hover:text-primary-400 transition-colors flex items-center gap-2">
                                <TrophyIcon class="w-4 h-4" />
                                Leaderboard
                            </Link>
                        </li>
                        <li>
                            <Link :href="route('register')" class="text-neutral-300 hover:text-primary-400 transition-colors flex items-center gap-2">
                                <UserPlusIcon class="w-4 h-4" />
                                Join as Broker
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Contact & Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-white">Contact Info</h3>
                    <ul class="space-y-3 text-neutral-300">
                        <li class="flex items-center gap-3">
                            <MapPinIcon class="w-4 h-4 text-primary-400 flex-shrink-0" />
                            <span class="text-sm">Tagbilaran City, Bohol, Philippines</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <PhoneIcon class="w-4 h-4 text-primary-400 flex-shrink-0" />
                            <a href="tel:+631234567890" class="text-sm hover:text-primary-400 transition-colors">+63 123 456 7890</a>
                        </li>
                        <li class="flex items-center gap-3">
                            <EnvelopeIcon class="w-4 h-4 text-primary-400 flex-shrink-0" />
                            <a href="mailto:info@geocasabohol.com" class="text-sm hover:text-primary-400 transition-colors">info@geocasabohol.com</a>
                        </li>
                        <li class="flex items-center gap-3">
                            <ClockIcon class="w-4 h-4 text-primary-400 flex-shrink-0" />
                            <span class="text-sm">Mon-Sat 8:00 AM - 6:00 PM</span>
                        </li>
                    </ul>

                    <!-- Trust Badges -->
                    <div class="mt-6 pt-6 border-t border-neutral-800">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2 text-xs text-neutral-400">
                                <ShieldCheckIcon class="w-4 h-4 text-accent-400" />
                                <span>Licensed Real Estate Brokers</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-neutral-400">
                                <CheckBadgeIcon class="w-4 h-4 text-accent-400" />
                                <span>Verified Property Listings</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-neutral-800 mt-12 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-center md:text-left">
                        <p class="text-neutral-400 text-sm">
                            &copy; {{ currentYear }} GeoCasa Bohol. All rights reserved.
                        </p>
                        <p class="text-neutral-500 text-xs mt-1">
                            Connecting dreams with reality in the heart of Bohol.
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-6 text-xs text-neutral-400">
                        <button
                            @click="openPrivacyModal"
                            class="hover:text-primary-400 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Privacy Policy
                        </button>
                        <button
                            @click="openTermsModal"
                            class="hover:text-primary-400 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            Terms of Service
                        </button>
                        <a href="#" class="hover:text-primary-400 transition-colors">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Modals -->
    <TermsOfServiceModal :show="showTermsModal" @close="closeTermsModal" />
    <PrivacyPolicyModal :show="showPrivacyModal" @close="closePrivacyModal" />
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import TermsOfServiceModal from './TermsOfServiceModal.vue';
import PrivacyPolicyModal from './PrivacyPolicyModal.vue';
import { 
    MapPinIcon, 
    BuildingOfficeIcon, 
    HomeIcon,
    PhoneIcon,
    EnvelopeIcon,
    ClockIcon,
    GlobeAltIcon,
    SunIcon,
    PlusCircleIcon,
    TrophyIcon,
    UserPlusIcon,
    ShieldCheckIcon,
    CheckBadgeIcon
} from '@heroicons/vue/24/outline';

const currentYear = computed(() => new Date().getFullYear());

const showTermsModal = ref(false);
const showPrivacyModal = ref(false);

const openTermsModal = () => {
    showTermsModal.value = true;
};

const closeTermsModal = () => {
    showTermsModal.value = false;
};

const openPrivacyModal = () => {
    showPrivacyModal.value = true;
};

const closePrivacyModal = () => {
    showPrivacyModal.value = false;
};
</script>