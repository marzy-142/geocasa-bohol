<script setup>
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import UserAvatar from "@/Components/UserAvatar.vue";
import {
    MapPinIcon,
    BriefcaseIcon,
    BuildingOfficeIcon,
    PhoneIcon,
    EnvelopeIcon,
    GlobeAltIcon,
    CheckBadgeIcon,
    ChatBubbleLeftRightIcon,
    HomeIcon,
    ChartBarIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    broker: Object,
    auth: Object,
});

// Contact form state
const showContactModal = ref(false);
const form = useForm({
    name: "",
    email: "",
    phone: "",
    message: "",
    inquiry_type: "general",
});

const submitInquiry = () => {
    form.post(route("brokers.inquiry", props.broker.id), {
        onSuccess: () => {
            form.reset();
            showContactModal.value = false;
        },
    });
};

// Get availability badge color
const getAvailabilityColor = (status) => {
    switch (status) {
        case "available":
            return "bg-green-100 text-green-800";
        case "limited":
            return "bg-yellow-100 text-yellow-800";
        case "unavailable":
            return "bg-red-100 text-red-800";
        default:
            return "bg-gray-100 text-gray-800";
    }
};

const getAvailabilityText = (status) => {
    switch (status) {
        case "available":
            return "Available for New Clients";
        case "limited":
            return "Limited Availability";
        case "unavailable":
            return "Currently Unavailable";
        default:
            return "Unknown";
    }
};

// Format price
const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 0,
    }).format(price);
};
</script>

<template>
    <Head :title="`${broker.name} - GeoCasa Bohol`" />

    <div
        class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50"
    >
        <PublicNavigation :auth="auth" />

        <!-- Hero Section -->
        <div
            class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-16"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <!-- Profile Image -->
                    <div class="relative">
                        <div
                            class="w-32 h-32 md:w-40 md:h-40 rounded-full overflow-hidden border-4 border-white shadow-2xl"
                        >
                            <UserAvatar
                                v-if="broker"
                                :user="broker"
                                size="2xl"
                                bg-color="blue"
                                class="w-full h-full"
                            />
                        </div>
                        <div class="absolute -bottom-2 -right-2">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500 text-white shadow-lg"
                            >
                                <CheckBadgeIcon class="w-4 h-4 mr-1" />
                                Verified
                            </span>
                        </div>
                    </div>

                    <!-- Broker Info -->
                    <div class="flex-1 text-center md:text-left">
                        <h1 class="text-4xl font-bold mb-2">
                            {{ broker.name }}
                        </h1>
                        <p
                            v-if="broker.brokerage_firm_name"
                            class="text-xl text-blue-100 mb-4"
                        >
                            {{ broker.brokerage_firm_name }}
                        </p>

                        <div
                            class="flex flex-wrap gap-4 justify-center md:justify-start mb-4"
                        >
                            <div
                                v-if="broker.city"
                                class="flex items-center text-blue-100"
                            >
                                <MapPinIcon class="w-5 h-5 mr-2" />
                                <span
                                    >{{ broker.city }},
                                    {{ broker.province }}</span
                                >
                            </div>
                            <div
                                v-if="broker.years_experience"
                                class="flex items-center text-blue-100"
                            >
                                <BriefcaseIcon class="w-5 h-5 mr-2" />
                                <span
                                    >{{ broker.years_experience }} years
                                    experience</span
                                >
                            </div>
                        </div>

                        <div
                            :class="[
                                'inline-flex items-center px-4 py-2 rounded-full text-sm font-medium',
                                getAvailabilityColor(
                                    broker.availability_status
                                ),
                            ]"
                        >
                            {{
                                getAvailabilityText(broker.availability_status)
                            }}
                        </div>
                    </div>

                    <!-- Contact Button -->
                    <div v-if="broker.accept_inquiries">
                        <button
                            @click="showContactModal = true"
                            class="bg-white text-blue-600 px-8 py-4 rounded-xl font-semibold hover:bg-gray-100 transition-all shadow-lg hover:shadow-xl flex items-center gap-2"
                        >
                            <ChatBubbleLeftRightIcon class="w-5 h-5" />
                            Contact Broker
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Main Info -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- About Section -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">
                            About
                        </h2>
                        <p
                            v-if="broker.bio"
                            class="text-gray-700 leading-relaxed whitespace-pre-wrap"
                        >
                            {{ broker.bio }}
                        </p>
                        <p v-else class="text-gray-500 italic">
                            No biography available.
                        </p>
                    </div>

                    <!-- Specializations -->
                    <div
                        v-if="
                            broker.specializations &&
                            broker.specializations.length > 0
                        "
                        class="bg-white rounded-2xl shadow-lg p-8"
                    >
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">
                            Specializations
                        </h2>
                        <div class="flex flex-wrap gap-3">
                            <span
                                v-for="spec in broker.specializations"
                                :key="spec"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium bg-blue-100 text-blue-800 rounded-full"
                            >
                                {{ spec }}
                            </span>
                        </div>
                    </div>

                    <!-- Service Areas -->
                    <div
                        v-if="
                            broker.service_areas &&
                            broker.service_areas.length > 0
                        "
                        class="bg-white rounded-2xl shadow-lg p-8"
                    >
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">
                            Service Areas
                        </h2>
                        <div class="flex flex-wrap gap-3">
                            <span
                                v-for="area in broker.service_areas"
                                :key="area"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium bg-purple-100 text-purple-800 rounded-full"
                            >
                                <MapPinIcon class="w-4 h-4 mr-1" />
                                {{ area }}
                            </span>
                        </div>
                    </div>

                    <!-- Active Listings -->
                    <div
                        v-if="broker.properties && broker.properties.length > 0"
                        class="bg-white rounded-2xl shadow-lg p-8"
                    >
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">
                            Active Listings
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Link
                                v-for="property in broker.properties"
                                :key="property.id"
                                :href="
                                    route('public.properties.show', property.id)
                                "
                                class="group bg-gray-50 rounded-xl overflow-hidden hover:shadow-lg transition-all"
                            >
                                <div class="h-48 bg-gray-200 overflow-hidden">
                                    <img
                                        v-if="
                                            property.images &&
                                            property.images.length > 0
                                        "
                                        :src="property.images[0]"
                                        :alt="property.title"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full flex items-center justify-center"
                                    >
                                        <HomeIcon
                                            class="w-16 h-16 text-gray-400"
                                        />
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h3
                                        class="font-semibold text-gray-900 mb-1 group-hover:text-blue-600 transition-colors"
                                    >
                                        {{ property.title }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mb-2">
                                        {{ property.location }}
                                    </p>
                                    <p class="text-lg font-bold text-blue-600">
                                        {{ formatPrice(property.price) }}
                                    </p>
                                </div>
                            </Link>
                        </div>

                        <div
                            v-if="
                                broker.active_listings >
                                broker.properties.length
                            "
                            class="mt-6 text-center"
                        >
                            <p class="text-gray-600">
                                +
                                {{
                                    broker.active_listings -
                                    broker.properties.length
                                }}
                                more listings
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Contact & Stats -->
                <div class="space-y-6">
                    <!-- Statistics Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h3
                            class="text-lg font-bold text-gray-900 mb-4 flex items-center"
                        >
                            <ChartBarIcon class="w-5 h-5 mr-2" />
                            Statistics
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"
                                    >Active Listings</span
                                >
                                <span
                                    class="text-2xl font-bold text-blue-600"
                                    >{{ broker.active_listings }}</span
                                >
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"
                                    >Total Listings</span
                                >
                                <span
                                    class="text-2xl font-bold text-gray-900"
                                    >{{ broker.total_listings }}</span
                                >
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"
                                    >Properties Sold</span
                                >
                                <span
                                    class="text-2xl font-bold text-green-600"
                                    >{{ broker.sold_properties }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">
                            Contact Information
                        </h3>
                        <div class="space-y-4">
                            <div
                                v-if="broker.prc_license_number"
                                class="flex items-start"
                            >
                                <CheckBadgeIcon
                                    class="w-5 h-5 text-green-600 mr-3 mt-0.5"
                                />
                                <div>
                                    <p class="text-sm text-gray-600">
                                        PRC License
                                    </p>
                                    <p class="font-medium text-gray-900">
                                        {{ broker.prc_license_number }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="
                                    broker.show_phone &&
                                    broker.office_contact_number
                                "
                                class="flex items-start"
                            >
                                <PhoneIcon
                                    class="w-5 h-5 text-blue-600 mr-3 mt-0.5"
                                />
                                <div>
                                    <p class="text-sm text-gray-600">Phone</p>
                                    <a
                                        :href="`tel:${broker.office_contact_number}`"
                                        class="font-medium text-blue-600 hover:text-blue-800"
                                    >
                                        {{ broker.office_contact_number }}
                                    </a>
                                </div>
                            </div>

                            <div
                                v-if="broker.show_email && broker.email"
                                class="flex items-start"
                            >
                                <EnvelopeIcon
                                    class="w-5 h-5 text-blue-600 mr-3 mt-0.5"
                                />
                                <div>
                                    <p class="text-sm text-gray-600">Email</p>
                                    <a
                                        :href="`mailto:${broker.email}`"
                                        class="font-medium text-blue-600 hover:text-blue-800 break-all"
                                    >
                                        {{ broker.email }}
                                    </a>
                                </div>
                            </div>

                            <div v-if="broker.website" class="flex items-start">
                                <GlobeAltIcon
                                    class="w-5 h-5 text-blue-600 mr-3 mt-0.5"
                                />
                                <div>
                                    <p class="text-sm text-gray-600">Website</p>
                                    <a
                                        :href="broker.website"
                                        target="_blank"
                                        class="font-medium text-blue-600 hover:text-blue-800 break-all"
                                    >
                                        {{ broker.website }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Social Links -->
                        <div
                            v-if="broker.facebook || broker.linkedin"
                            class="mt-6 pt-6 border-t border-gray-200"
                        >
                            <p class="text-sm text-gray-600 mb-3">
                                Connect on Social Media
                            </p>
                            <div class="flex gap-3">
                                <a
                                    v-if="broker.facebook"
                                    :href="broker.facebook"
                                    target="_blank"
                                    class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg text-center hover:bg-blue-700 transition-colors"
                                >
                                    Facebook
                                </a>
                                <a
                                    v-if="broker.linkedin"
                                    :href="broker.linkedin"
                                    target="_blank"
                                    class="flex-1 bg-blue-800 text-white px-4 py-2 rounded-lg text-center hover:bg-blue-900 transition-colors"
                                >
                                    LinkedIn
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contact CTA -->
                    <div
                        v-if="broker.accept_inquiries"
                        class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl shadow-lg p-6 text-white"
                    >
                        <h3 class="text-lg font-bold mb-2">
                            Interested in working together?
                        </h3>
                        <p class="text-blue-100 mb-4">
                            Send {{ broker.name.split(" ")[0] }} a message and
                            start your property journey today.
                        </p>
                        <button
                            @click="showContactModal = true"
                            class="w-full bg-white text-blue-600 px-6 py-3 rounded-xl font-semibold hover:bg-gray-100 transition-colors"
                        >
                            Send Message
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Modal -->
        <div
            v-if="showContactModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showContactModal = false"
        >
            <div
                class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto"
            >
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-900">
                            Contact {{ broker.name }}
                        </h3>
                        <button
                            @click="showContactModal = false"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg
                                class="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="submitInquiry" class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Your Name *</label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Your Email *</label
                            >
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p
                                v-if="form.errors.email"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Phone Number</label
                            >
                            <input
                                v-model="form.phone"
                                type="tel"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p
                                v-if="form.errors.phone"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Inquiry Type *</label
                            >
                            <select
                                v-model="form.inquiry_type"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="general">General Inquiry</option>
                                <option value="buying">Buying Property</option>
                                <option value="selling">
                                    Selling Property
                                </option>
                                <option value="consultation">
                                    Request Consultation
                                </option>
                            </select>
                            <p
                                v-if="form.errors.inquiry_type"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.inquiry_type }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Message *</label
                            >
                            <textarea
                                v-model="form.message"
                                required
                                rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Tell the broker about your needs..."
                            ></textarea>
                            <p
                                v-if="form.errors.message"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.message }}
                            </p>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button
                                type="button"
                                @click="showContactModal = false"
                                class="flex-1 px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? "Sending..."
                                        : "Send Message"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
