<script setup>
// Image URL handler - matches the Properties page logic
const getImageUrl = (img, isVirtualTour = false) => {
    if (!img) return "";

    let cleanImage = String(img).trim();

    // If already a full URL, return as-is
    if (cleanImage.startsWith("http://") || cleanImage.startsWith("https://")) {
        return cleanImage;
    }

    // If already starts with /storage/, return as-is to prevent duplication
    if (cleanImage.startsWith("/storage/")) {
        return cleanImage;
    }

    // Remove any leading slashes to prevent double slashes
    cleanImage = cleanImage.replace(/^\/+/, "");

    // Check for existing path segments to prevent duplication
    if (cleanImage.includes("properties/virtual-tours/")) {
        return `/storage/${cleanImage}`;
    } else if (cleanImage.includes("properties/images/")) {
        return `/storage/${cleanImage}`;
    }

    // Determine the correct path based on context
    if (
        isVirtualTour ||
        cleanImage.includes("virtual") ||
        cleanImage.includes("tour")
    ) {
        return `/storage/properties/virtual-tours/${cleanImage}`;
    } else {
        return `/storage/properties/images/${cleanImage}`;
    }
};

const onImageError = (e) => {
    e.target.style.display = "none";
};
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
    ArrowLeftIcon,
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
        class="min-h-screen bg-gradient-to-br from-neutral-50 via-white to-blue-50"
    >
        <PublicNavigation :auth="auth" />

        <!-- Back Button Navigation -->
        <div
            class="bg-white border-b border-neutral-200 sticky top-0 z-40 shadow-sm"
        >
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <Link
                    :href="route('brokers.index')"
                    class="inline-flex items-center gap-2 text-neutral-600 hover:text-blue-600 transition-colors duration-200 font-medium group"
                >
                    <ArrowLeftIcon
                        class="w-5 h-5 transition-transform group-hover:-translate-x-1"
                    />
                    <span>Back to Broker Directory</span>
                </Link>
            </div>
        </div>

        <!-- Hero/Profile Header -->
        <section
            class="relative bg-gradient-to-r from-blue-700 to-accent-700 text-white py-16 shadow-lg"
        >
            <div
                class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center gap-10"
            >
                <!-- Profile Image & Badge -->
                <div class="relative flex-shrink-0 mb-8 md:mb-0">
                    <div
                        class="w-36 h-36 md:w-44 md:h-44 rounded-full overflow-hidden border-4 border-white shadow-2xl bg-white"
                    >
                        <UserAvatar
                            v-if="broker"
                            :user="broker"
                            size="2xl"
                            bg-color="blue"
                            class="w-full h-full"
                        />
                    </div>
                    <span
                        class="absolute -bottom-3 -right-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-500 text-white shadow-lg border-2 border-white"
                    >
                        <CheckBadgeIcon class="w-4 h-4 mr-1" /> Verified
                    </span>
                </div>

                <!-- Main Info -->
                <div class="flex-1 text-center md:text-left">
                    <h1
                        class="text-4xl md:text-5xl font-extrabold mb-2 tracking-tight drop-shadow-lg"
                    >
                        {{ broker.name }}
                    </h1>
                    <p
                        v-if="broker.brokerage_firm_name"
                        class="text-lg md:text-2xl text-blue-100 mb-3 font-medium"
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
                                >{{ broker.city }}, {{ broker.province }}</span
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
                            'inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold shadow-md',
                            getAvailabilityColor(broker.availability_status),
                        ]"
                    >
                        {{ getAvailabilityText(broker.availability_status) }}
                    </div>
                </div>
                <!-- Contact Button removed as requested -->
            </div>
        </section>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                <!-- Left/Main Column -->
                <div class="lg:col-span-2 space-y-10">
                    <!-- About Section -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h2
                            class="text-2xl font-extrabold text-blue-900 mb-4 flex items-center gap-2"
                        >
                            <BriefcaseIcon class="w-6 h-6 text-blue-400" />
                            About
                        </h2>
                        <p
                            v-if="broker.bio"
                            class="text-gray-700 leading-relaxed whitespace-pre-wrap text-lg"
                        >
                            {{ broker.bio }}
                        </p>
                        <p v-else class="text-gray-400 italic">
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
                        <h2
                            class="text-2xl font-extrabold text-blue-900 mb-4 flex items-center gap-2"
                        >
                            <ChartBarIcon class="w-6 h-6 text-blue-400" />
                            Specializations
                        </h2>
                        <div class="flex flex-wrap gap-3">
                            <span
                                v-for="spec in broker.specializations"
                                :key="spec"
                                class="inline-flex items-center px-4 py-2 text-sm font-semibold bg-blue-50 text-blue-700 border border-blue-100 rounded-full shadow-sm"
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
                        <h2
                            class="text-2xl font-extrabold text-blue-900 mb-4 flex items-center gap-2"
                        >
                            <MapPinIcon class="w-6 h-6 text-purple-400" />
                            Service Areas
                        </h2>
                        <div class="flex flex-wrap gap-3">
                            <span
                                v-for="area in broker.service_areas"
                                :key="area"
                                class="inline-flex items-center px-4 py-2 text-sm font-semibold bg-purple-50 text-purple-700 border border-purple-100 rounded-full shadow-sm"
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
                        <h2
                            class="text-2xl font-extrabold text-blue-900 mb-6 flex items-center gap-2"
                        >
                            <HomeIcon class="w-6 h-6 text-blue-400" />
                            Active Listings
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <Link
                                v-for="property in broker.properties"
                                :key="property.id"
                                :href="
                                    property.slug
                                        ? route(
                                              'public.properties.show',
                                              property.slug
                                          )
                                        : '#'
                                "
                                :aria-disabled="!property.slug"
                                :class="[
                                    'group bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl overflow-hidden transition-all border border-blue-100',
                                    property.slug
                                        ? 'hover:shadow-xl hover:border-blue-300'
                                        : 'opacity-95 pointer-events-none',
                                ]"
                            >
                                <div
                                    class="h-48 bg-gray-200 overflow-hidden relative"
                                >
                                    <template
                                        v-if="
                                            property.images &&
                                            property.images.length > 0 &&
                                            property.images[0]
                                        "
                                    >
                                        <img
                                            :src="
                                                getImageUrl(property.images[0])
                                            "
                                            :alt="property.title"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                            @error="onImageError"
                                        />
                                    </template>
                                    <div
                                        v-else
                                        class="w-full h-full flex items-center justify-center bg-gray-100"
                                    >
                                        <HomeIcon
                                            class="w-16 h-16 text-gray-400"
                                        />
                                        <p
                                            class="absolute bottom-2 text-xs text-red-500 break-all px-2"
                                        >
                                            {{
                                                JSON.stringify(property.images)
                                            }}
                                        </p>
                                    </div>
                                    <span
                                        v-if="property.status === 'sold'"
                                        class="absolute top-3 right-3 bg-green-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow"
                                        >SOLD</span
                                    >
                                </div>
                                <div class="p-4">
                                    <h3
                                        class="font-semibold text-blue-900 mb-1 group-hover:text-blue-700 transition-colors text-lg"
                                    >
                                        {{ property.title }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mb-2">
                                        {{
                                            property.full_address ||
                                            property.municipality
                                        }}
                                    </p>
                                    <p class="text-lg font-bold text-blue-700">
                                        {{
                                            property.formatted_total_price ||
                                            formatPrice(property.total_price)
                                        }}
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
                            <p class="text-gray-600 font-medium">
                                +{{
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
                    <div
                        class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl shadow-lg p-6 border border-blue-100"
                    >
                        <h3
                            class="text-lg font-extrabold text-blue-900 mb-4 flex items-center gap-2"
                        >
                            <ChartBarIcon class="w-5 h-5 text-blue-400" />
                            Statistics
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"
                                    >Active Listings</span
                                >
                                <span
                                    class="text-2xl font-bold text-blue-700"
                                    >{{ broker.active_listings }}</span
                                >
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600"
                                    >Total Listings</span
                                >
                                <span
                                    class="text-2xl font-bold text-blue-900"
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
                        <h3
                            class="text-lg font-extrabold text-blue-900 mb-4 flex items-center gap-2"
                        >
                            <EnvelopeIcon class="w-5 h-5 text-blue-400" />
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
                            class="mt-6 pt-6 border-t border-blue-100"
                        >
                            <p class="text-sm text-blue-700 mb-3 font-semibold">
                                Connect on Social Media
                            </p>
                            <div class="flex gap-3">
                                <a
                                    v-if="broker.facebook"
                                    :href="broker.facebook"
                                    target="_blank"
                                    class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg text-center hover:bg-blue-700 transition-colors font-semibold shadow"
                                >
                                    Facebook
                                </a>
                                <a
                                    v-if="broker.linkedin"
                                    :href="broker.linkedin"
                                    target="_blank"
                                    class="flex-1 bg-blue-800 text-white px-4 py-2 rounded-lg text-center hover:bg-blue-900 transition-colors font-semibold shadow"
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
