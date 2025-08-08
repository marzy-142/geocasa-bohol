<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const form = useForm({
    seller_name: "",
    seller_email: "",
    seller_phone: "",
    seller_address: "",
    property_title: "",
    property_description: "",
    asking_price: "",
    property_area: "",
    area_unit: "sqm",
    property_location: "",
    property_address: "",
    city: "",
    state: "Bohol",
    zip_code: "",
    latitude: "",
    longitude: "",
    property_type: "residential",
    features: [],
    uploaded_images: [],
});

const availableFeatures = [
    "Swimming Pool",
    "Garden",
    "Garage",
    "Balcony",
    "Terrace",
    "Air Conditioning",
    "Furnished",
    "Security System",
    "Elevator",
    "Parking Space",
    "Storage Room",
    "Laundry Room",
    "Fireplace",
    "Walk-in Closet",
    "Study Room",
    "Maid's Room",
    "Guest Room",
];

const imageFiles = ref([]);

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files);
    imageFiles.value = files;
    form.uploaded_images = files;
};

const removeImage = (index) => {
    imageFiles.value.splice(index, 1);
    form.uploaded_images = imageFiles.value;
};

const toggleFeature = (feature) => {
    const index = form.features.indexOf(feature);
    if (index > -1) {
        form.features.splice(index, 1);
    } else {
        form.features.push(feature);
    }
};

const submit = () => {
    form.post(route("seller-requests.store"));
};
</script>

<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <div class="text-center">
                            <h2 class="text-3xl font-semibold text-gray-900">
                                List Your Property
                            </h2>
                            <p class="mt-4 text-lg text-gray-600">
                                Submit your property details and we'll help you
                                get it listed on our platform
                            </p>
                        </div>
                    </div>

                    <div class="p-6 sm:px-20">
                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- Seller Information -->
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Your Information
                                </h3>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <div>
                                        <label
                                            for="seller_name"
                                            class="block text-sm font-medium text-gray-700"
                                            >Full Name *</label
                                        >
                                        <input
                                            v-model="form.seller_name"
                                            type="text"
                                            id="seller_name"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.seller_name"
                                            class="text-red-600 text-sm mt-1"
                                        >
                                            {{ form.errors.seller_name }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="seller_email"
                                            class="block text-sm font-medium text-gray-700"
                                            >Email Address *</label
                                        >
                                        <input
                                            v-model="form.seller_email"
                                            type="email"
                                            id="seller_email"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.seller_email"
                                            class="text-red-600 text-sm mt-1"
                                        >
                                            {{ form.errors.seller_email }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="seller_phone"
                                            class="block text-sm font-medium text-gray-700"
                                            >Phone Number</label
                                        >
                                        <input
                                            v-model="form.seller_phone"
                                            type="tel"
                                            id="seller_phone"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        />
                                        <div
                                            v-if="form.errors.seller_phone"
                                            class="text-red-600 text-sm mt-1"
                                        >
                                            {{ form.errors.seller_phone }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="seller_address"
                                            class="block text-sm font-medium text-gray-700"
                                            >Your Address</label
                                        >
                                        <textarea
                                            v-model="form.seller_address"
                                            id="seller_address"
                                            rows="3"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        ></textarea>
                                        <div
                                            v-if="form.errors.seller_address"
                                            class="text-red-600 text-sm mt-1"
                                        >
                                            {{ form.errors.seller_address }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Basic Information -->
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Property Information
                                </h3>
                                <div class="space-y-6">
                                    <div>
                                        <label
                                            for="property_title"
                                            class="block text-sm font-medium text-gray-700"
                                            >Property Title *</label
                                        >
                                        <input
                                            v-model="form.property_title"
                                            type="text"
                                            id="property_title"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="e.g., Beautiful 3-Bedroom House in Tagbilaran"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.property_title"
                                            class="text-red-600 text-sm mt-1"
                                        >
                                            {{ form.errors.property_title }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="property_description"
                                            class="block text-sm font-medium text-gray-700"
                                            >Property Description *</label
                                        >
                                        <textarea
                                            v-model="form.property_description"
                                            id="property_description"
                                            rows="4"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="Describe your property in detail..."
                                            required
                                        ></textarea>
                                        <div
                                            v-if="
                                                form.errors.property_description
                                            "
                                            class="text-red-600 text-sm mt-1"
                                        >
                                            {{
                                                form.errors.property_description
                                            }}
                                        </div>
                                    </div>

                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 gap-6"
                                    >
                                        <div>
                                            <label
                                                for="property_type"
                                                class="block text-sm font-medium text-gray-700"
                                                >Property Type *</label
                                            >
                                            <select
                                                v-model="form.property_type"
                                                id="property_type"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                required
                                            >
                                                <option value="residential">
                                                    Residential
                                                </option>
                                                <option value="commercial">
                                                    Commercial
                                                </option>
                                                <option value="agricultural">
                                                    Agricultural
                                                </option>
                                                <option value="industrial">
                                                    Industrial
                                                </option>
                                                <option value="recreational">
                                                    Recreational
                                                </option>
                                            </select>
                                            <div
                                                v-if="form.errors.property_type"
                                                class="text-red-600 text-sm mt-1"
                                            >
                                                {{ form.errors.property_type }}
                                            </div>
                                        </div>

                                        <div>
                                            <label
                                                for="asking_price"
                                                class="block text-sm font-medium text-gray-700"
                                                >Asking Price *</label
                                            >
                                            <div
                                                class="mt-1 relative rounded-md shadow-sm"
                                            >
                                                <div
                                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                                >
                                                    <span
                                                        class="text-gray-500 sm:text-sm"
                                                        >₱</span
                                                    >
                                                </div>
                                                <input
                                                    v-model="form.asking_price"
                                                    type="number"
                                                    id="asking_price"
                                                    step="0.01"
                                                    class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                    placeholder="0.00"
                                                    required
                                                />
                                            </div>
                                            <div
                                                v-if="form.errors.asking_price"
                                                class="text-red-600 text-sm mt-1"
                                            >
                                                {{ form.errors.asking_price }}
                                            </div>
                                        </div>

                                        <div>
                                            <label
                                                for="property_area"
                                                class="block text-sm font-medium text-gray-700"
                                                >Property Area *</label
                                            >
                                            <div
                                                class="mt-1 flex rounded-md shadow-sm"
                                            >
                                                <input
                                                    v-model="form.property_area"
                                                    type="number"
                                                    id="property_area"
                                                    step="0.01"
                                                    class="flex-1 block w-full border-gray-300 rounded-l-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                    placeholder="0.00"
                                                    required
                                                />
                                                <select
                                                    v-model="form.area_unit"
                                                    class="border-gray-300 rounded-r-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                >
                                                    <option value="sqm">
                                                        sqm
                                                    </option>
                                                    <option value="acres">
                                                        acres
                                                    </option>
                                                    <option value="hectares">
                                                        hectares
                                                    </option>
                                                </select>
                                            </div>
                                            <div
                                                v-if="form.errors.property_area"
                                                class="text-red-600 text-sm mt-1"
                                            >
                                                {{ form.errors.property_area }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Location -->
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Property Location
                                </h3>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <div>
                                        <label
                                            for="property_location"
                                            class="block text-sm font-medium text-gray-700"
                                            >General Location *</label
                                        >
                                        <input
                                            v-model="form.property_location"
                                            type="text"
                                            id="property_location"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="e.g., Tagbilaran City, Bohol"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.property_location"
                                            class="text-red-600 text-sm mt-1"
                                        >
                                            {{ form.errors.property_location }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="property_address"
                                            class="block text-sm font-medium text-gray-700"
                                            >Full Address *</label
                                        >
                                        <input
                                            v-model="form.property_address"
                                            type="text"
                                            id="property_address"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="Street address, barangay"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.property_address"
                                            class="text-red-600 text-sm mt-1"
                                        >
                                            {{ form.errors.property_address }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="city"
                                            class="block text-sm font-medium text-gray-700"
                                            >City/Municipality *</label
                                        >
                                        <input
                                            v-model="form.city"
                                            type="text"
                                            id="city"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.city"
                                            class="text-red-600 text-sm mt-1"
                                        >
                                            {{ form.errors.city }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="state"
                                            class="block text-sm font-medium text-gray-700"
                                            >Province/State *</label
                                        >
                                        <input
                                            v-model="form.state"
                                            type="text"
                                            id="state"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.state"
                                            class="text-red-600 text-sm mt-1"
                                        >
                                            {{ form.errors.state }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="zip_code"
                                            class="block text-sm font-medium text-gray-700"
                                            >ZIP Code</label
                                        >
                                        <input
                                            v-model="form.zip_code"
                                            type="text"
                                            id="zip_code"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        />
                                        <div
                                            v-if="form.errors.zip_code"
                                            class="text-red-600 text-sm mt-1"
                                        >
                                            {{ form.errors.zip_code }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Features -->
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Property Features
                                </h3>
                                <div
                                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3"
                                >
                                    <label
                                        v-for="feature in availableFeatures"
                                        :key="feature"
                                        class="flex items-center"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="feature"
                                            @change="toggleFeature(feature)"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                        <span
                                            class="ml-2 text-sm text-gray-700"
                                            >{{ feature }}</span
                                        >
                                    </label>
                                </div>
                            </div>

                            <!-- Property Images -->
                            <div>
                                <h3
                                    class="text-lg font-medium text-gray-900 mb-4"
                                >
                                    Property Images
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label
                                            for="images"
                                            class="block text-sm font-medium text-gray-700"
                                            >Upload Images</label
                                        >
                                        <input
                                            type="file"
                                            id="images"
                                            multiple
                                            accept="image/*"
                                            @change="handleImageUpload"
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                        />
                                        <p class="mt-1 text-sm text-gray-500">
                                            Upload up to 10 images (JPEG, PNG,
                                            JPG, GIF, max 2MB each)
                                        </p>
                                    </div>

                                    <!-- Image Preview -->
                                    <div
                                        v-if="imageFiles.length > 0"
                                        class="grid grid-cols-2 md:grid-cols-4 gap-4"
                                    >
                                        <div
                                            v-for="(file, index) in imageFiles"
                                            :key="index"
                                            class="relative"
                                        >
                                            <img
                                                :src="URL.createObjectURL(file)"
                                                :alt="`Preview ${index + 1}`"
                                                class="w-full h-24 object-cover rounded-lg"
                                            />
                                            <button
                                                type="button"
                                                @click="removeImage(index)"
                                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-center pt-6">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="form.processing"
                                        >Submitting Request...</span
                                    >
                                    <span v-else
                                        >Submit Property Listing Request</span
                                    >
                                </button>
                            </div>

                            <!-- Disclaimer -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600">
                                    <strong>Note:</strong> By submitting this
                                    form, you agree that the information
                                    provided is accurate and complete. Our team
                                    will review your request and contact you
                                    within 2-3 business days. There is no fee
                                    for submitting this request, but standard
                                    commission rates will apply if your property
                                    is successfully listed and sold.
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
