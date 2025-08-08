<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <PublicNavigation :auth="$page.props.auth" />

        <!-- Breadcrumb -->
        <div class="bg-white border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <Link
                                :href="route('home')"
                                class="text-gray-500 hover:text-gray-700"
                                >Home</Link
                            >
                        </li>
                        <li>
                            <svg
                                class="flex-shrink-0 h-5 w-5 text-gray-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </li>
                        <li>
                            <Link
                                :href="route('public.properties')"
                                class="text-gray-500 hover:text-gray-700"
                                >Properties</Link
                            >
                        </li>
                        <li>
                            <svg
                                class="flex-shrink-0 h-5 w-5 text-gray-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </li>
                        <li class="text-gray-900 font-medium">
                            {{ property.title }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Property Details -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Property Images -->
                    <div
                        class="bg-white rounded-lg shadow-md overflow-hidden mb-6"
                    >
                        <div class="relative h-96">
                            <img
                                :src="currentImage"
                                :alt="property.title"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-if="property.is_featured"
                                class="absolute top-4 left-4"
                            >
                                <span
                                    class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-medium"
                                >
                                    Featured Property
                                </span>
                            </div>
                        </div>

                        <!-- Image Thumbnails -->
                        <div
                            v-if="property.images && property.images.length > 1"
                            class="p-4"
                        >
                            <div class="flex space-x-2 overflow-x-auto">
                                <button
                                    v-for="(image, index) in property.images"
                                    :key="index"
                                    @click="currentImage = getImageUrl(image)"
                                    class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2"
                                    :class="
                                        currentImage === getImageUrl(image)
                                            ? 'border-blue-500'
                                            : 'border-gray-200'
                                    "
                                >
                                    <img
                                        :src="getImageUrl(image)"
                                        :alt="`Image ${index + 1}`"
                                        class="w-full h-full object-cover"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Information -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h1
                                    class="text-3xl font-bold text-gray-900 mb-2"
                                >
                                    {{ property.title }}
                                </h1>
                                <p class="text-gray-600 flex items-center">
                                    <svg
                                        class="w-5 h-5 mr-2"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    {{ property.full_address }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-blue-600">
                                    {{ property.formatted_total_price }}
                                </div>
                                <div class="text-gray-600">
                                    {{ property.formatted_price_per_sqm }}/sqm
                                </div>
                            </div>
                        </div>

                        <!-- Property Type & Status -->
                        <div class="flex space-x-4 mb-6">
                            <span
                                class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium"
                            >
                                {{ formatPropertyType(property.type) }}
                            </span>
                            <span
                                class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium"
                            >
                                Available
                            </span>
                        </div>

                        <!-- Key Details -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    {{ property.formatted_area }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    Total Area
                                </div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    {{ property.title_type || "N/A" }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    Title Type
                                </div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    {{
                                        property.zoning_classification || "N/A"
                                    }}
                                </div>
                                <div class="text-sm text-gray-600">Zoning</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">
                                    {{ property.municipality }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    Municipality
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-3"
                            >
                                Description
                            </h3>
                            <p class="text-gray-700 leading-relaxed">
                                {{ property.description }}
                            </p>
                        </div>

                        <!-- Features & Utilities -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h3
                                    class="text-lg font-semibold text-gray-900 mb-3"
                                >
                                    Utilities & Access
                                </h3>
                                <ul class="space-y-2">
                                    <li class="flex items-center">
                                        <svg
                                            class="w-5 h-5 mr-3"
                                            :class="
                                                property.electricity_available
                                                    ? 'text-green-500'
                                                    : 'text-gray-400'
                                            "
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707z"
                                            />
                                        </svg>
                                        <span
                                            :class="
                                                property.electricity_available
                                                    ? 'text-gray-900'
                                                    : 'text-gray-500'
                                            "
                                        >
                                            Electricity
                                            {{
                                                property.electricity_available
                                                    ? "Available"
                                                    : "Not Available"
                                            }}
                                        </span>
                                    </li>
                                    <li class="flex items-center">
                                        <svg
                                            class="w-5 h-5 mr-3"
                                            :class="
                                                property.water_source
                                                    ? 'text-blue-500'
                                                    : 'text-gray-400'
                                            "
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span
                                            :class="
                                                property.water_source
                                                    ? 'text-gray-900'
                                                    : 'text-gray-500'
                                            "
                                        >
                                            Water Source
                                            {{
                                                property.water_source
                                                    ? "Available"
                                                    : "Not Available"
                                            }}
                                        </span>
                                    </li>
                                    <li class="flex items-center">
                                        <svg
                                            class="w-5 h-5 mr-3"
                                            :class="
                                                property.road_access
                                                    ? 'text-green-500'
                                                    : 'text-gray-400'
                                            "
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span
                                            :class="
                                                property.road_access
                                                    ? 'text-gray-900'
                                                    : 'text-gray-500'
                                            "
                                        >
                                            Road Access
                                            {{
                                                property.road_access
                                                    ? "Available"
                                                    : "Limited"
                                            }}
                                        </span>
                                    </li>
                                    <li class="flex items-center">
                                        <svg
                                            class="w-5 h-5 mr-3"
                                            :class="
                                                property.internet_available
                                                    ? 'text-purple-500'
                                                    : 'text-gray-400'
                                            "
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span
                                            :class="
                                                property.internet_available
                                                    ? 'text-gray-900'
                                                    : 'text-gray-500'
                                            "
                                        >
                                            Internet
                                            {{
                                                property.internet_available
                                                    ? "Available"
                                                    : "Not Available"
                                            }}
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <div
                                v-if="
                                    property.nearby_landmarks &&
                                    property.nearby_landmarks.length > 0
                                "
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-900 mb-3"
                                >
                                    Nearby Landmarks
                                </h3>
                                <ul class="space-y-1">
                                    <li
                                        v-for="landmark in property.nearby_landmarks"
                                        :key="landmark"
                                        class="text-gray-700"
                                    >
                                        • {{ landmark }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Map -->
                        <div v-if="property.google_maps_link" class="mb-6">
                            <h3
                                class="text-lg font-semibold text-gray-900 mb-3"
                            >
                                Location
                            </h3>
                            <a
                                :href="property.google_maps_link"
                                target="_blank"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                View on Google Maps
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Broker Information -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Listed by
                        </h3>
                        <div class="flex items-center mb-4">
                            <div
                                class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-lg"
                            >
                                {{ property.broker.name.charAt(0) }}
                            </div>
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">
                                    {{ property.broker.name }}
                                </div>
                                <div class="text-sm text-gray-600">
                                    Licensed Broker
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex items-center">
                                <svg
                                    class="w-4 h-4 mr-2"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"
                                    />
                                    <path
                                        d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"
                                    />
                                </svg>
                                {{ property.broker.email }}
                            </div>
                        </div>
                    </div>

                    <!-- Inquiry Form -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Interested in this property?
                        </h3>

                        <div
                            v-if="$page.props.flash.success"
                            class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded"
                        >
                            {{ $page.props.flash.success }}
                        </div>

                        <form @submit.prevent="submitInquiry" class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Full Name *</label
                                >
                                <input
                                    v-model="inquiryForm.name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Your full name"
                                />
                                <div
                                    v-if="errors.name"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ errors.name }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Email Address *</label
                                >
                                <input
                                    v-model="inquiryForm.email"
                                    type="email"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="your.email@example.com"
                                />
                                <div
                                    v-if="errors.email"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ errors.email }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Phone Number</label
                                >
                                <input
                                    v-model="inquiryForm.phone"
                                    type="tel"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="+63 123 456 7890"
                                />
                                <div
                                    v-if="errors.phone"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ errors.phone }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Message *</label
                                >
                                <textarea
                                    v-model="inquiryForm.message"
                                    required
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :placeholder="`I'm interested in ${property.title}. Please provide more information.`"
                                ></textarea>
                                <div
                                    v-if="errors.message"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ errors.message }}
                                </div>
                            </div>

                            <button
                                type="submit"
                                :disabled="processing"
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50"
                            >
                                <span v-if="processing">Sending...</span>
                                <span v-else>Send Inquiry</span>
                            </button>
                        </form>

                        <div class="mt-4 text-xs text-gray-500">
                            By submitting this form, you agree to be contacted
                            by the broker regarding this property.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <PublicFooter />
    </div>
</template>

<script setup>
import { ref } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import PublicNavigation from '@/Components/PublicNavigation.vue';
import PublicFooter from '@/Components/PublicFooter.vue';

const props = defineProps({
    property: Object,
    errors: Object,
});

const currentImage = ref(props.property.main_image);

const inquiryForm = useForm({
    name: "",
    email: "",
    phone: "",
    message: `I'm interested in ${props.property.title}. Please provide more information about this property.`,
});

const submitInquiry = () => {
    inquiryForm.post(route("public.inquiries.store", props.property.slug), {
        preserveScroll: true,
        onSuccess: () => {
            inquiryForm.reset("name", "email", "phone");
            inquiryForm.message = `I'm interested in ${props.property.title}. Please provide more information about this property.`;
        },
    });
};

const getImageUrl = (image) => {
    return image.startsWith("http") ? image : `/storage/${image}`;
};

const formatPropertyType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};
</script>
