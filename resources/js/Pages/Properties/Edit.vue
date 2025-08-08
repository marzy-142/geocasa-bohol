<template>
    <ModernDashboardLayout>
        <!-- Header Section -->
        <div
            class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg mb-6"
        >
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold">Edit Property</h2>
                    <p class="text-blue-100">
                        GeoCasa Bohol - {{ property.title }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <Link
                        :href="route('properties.show', property.slug)"
                        class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors"
                    >
                        View Property
                    </Link>
                    <Link
                        :href="route('properties.index')"
                        class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors"
                    >
                        Back to Properties
                    </Link>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" enctype="multipart/form-data">
            <!-- Basic Information -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Basic Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="title" value="Property Title" />
                        <TextInput
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autofocus
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="type" value="Property Type" />
                        <select
                            id="type"
                            v-model="form.type"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="">Select Type</option>
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="agricultural">Agricultural</option>
                            <option value="industrial">Industrial</option>
                            <option value="beachfront">Beachfront</option>
                            <option value="mountain_view">Mountain View</option>
                            <option value="rice_field">Rice Field</option>
                            <option value="coconut_plantation">
                                Coconut Plantation
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.type" />
                    </div>

                    <div class="md:col-span-2">
                        <InputLabel for="description" value="Description" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        ></textarea>
                        <InputError
                            class="mt-2"
                            :message="form.errors.description"
                        />
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Location Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="municipality" value="Municipality" />
                        <select
                            id="municipality"
                            v-model="form.municipality"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="">Select Municipality</option>
                            <option
                                v-for="municipality in municipalities"
                                :key="municipality"
                                :value="municipality"
                            >
                                {{ municipality }}
                            </option>
                        </select>
                        <InputError
                            class="mt-2"
                            :message="form.errors.municipality"
                        />
                    </div>

                    <div>
                        <InputLabel for="barangay" value="Barangay" />
                        <TextInput
                            id="barangay"
                            v-model="form.barangay"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.barangay"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <InputLabel for="address" value="Complete Address" />
                        <TextInput
                            id="address"
                            v-model="form.address"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.address"
                        />
                    </div>
                </div>
            </div>

            <!-- Pricing & Area -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Pricing & Area
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <InputLabel for="lot_area_sqm" value="Lot Area (sqm)" />
                        <TextInput
                            id="lot_area_sqm"
                            v-model="form.lot_area_sqm"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                            required
                            @input="calculateTotalPrice"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.lot_area_sqm"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="price_per_sqm"
                            value="Price per sqm (₱)"
                        />
                        <TextInput
                            id="price_per_sqm"
                            v-model="form.price_per_sqm"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                            required
                            @input="calculateTotalPrice"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.price_per_sqm"
                        />
                    </div>

                    <div>
                        <InputLabel for="total_price" value="Total Price (₱)" />
                        <TextInput
                            id="total_price"
                            v-model="form.total_price"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full bg-gray-100"
                            readonly
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.total_price"
                        />
                    </div>
                </div>
            </div>

            <!-- Legal Documents -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Legal Documents
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="title_type" value="Title Type" />
                        <select
                            id="title_type"
                            v-model="form.title_type"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">Select Title Type</option>
                            <option value="titled">Titled</option>
                            <option value="tax_declared">Tax Declared</option>
                            <option value="mother_title">Mother Title</option>
                            <option value="cct">CCT</option>
                        </select>
                        <InputError
                            class="mt-2"
                            :message="form.errors.title_type"
                        />
                    </div>

                    <div>
                        <InputLabel for="title_number" value="Title Number" />
                        <TextInput
                            id="title_number"
                            v-model="form.title_number"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.title_number"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="tax_declaration_number"
                            value="Tax Declaration Number"
                        />
                        <TextInput
                            id="tax_declaration_number"
                            v-model="form.tax_declaration_number"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.tax_declaration_number"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="zoning_classification"
                            value="Zoning Classification"
                        />
                        <TextInput
                            id="zoning_classification"
                            v-model="form.zoning_classification"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.zoning_classification"
                        />
                    </div>
                </div>
            </div>

            <!-- GPS Coordinates -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    GPS Coordinates
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel for="coordinates_lat" value="Latitude" />
                        <TextInput
                            id="coordinates_lat"
                            v-model="form.coordinates_lat"
                            type="number"
                            step="0.000001"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.coordinates_lat"
                        />
                    </div>

                    <div>
                        <InputLabel for="coordinates_lng" value="Longitude" />
                        <TextInput
                            id="coordinates_lng"
                            v-model="form.coordinates_lng"
                            type="number"
                            step="0.000001"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.coordinates_lng"
                        />
                    </div>
                </div>
            </div>

            <!-- Utilities & Access -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Utilities & Access
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="flex items-center">
                        <Checkbox
                            id="road_access"
                            v-model:checked="form.road_access"
                            name="road_access"
                        />
                        <InputLabel
                            for="road_access"
                            value="Road Access"
                            class="ml-2"
                        />
                    </div>

                    <div class="flex items-center">
                        <Checkbox
                            id="electricity_available"
                            v-model:checked="form.electricity_available"
                            name="electricity_available"
                        />
                        <InputLabel
                            for="electricity_available"
                            value="Electricity"
                            class="ml-2"
                        />
                    </div>

                    <div class="flex items-center">
                        <Checkbox
                            id="water_source"
                            v-model:checked="form.water_source"
                            name="water_source"
                        />
                        <InputLabel
                            for="water_source"
                            value="Water Source"
                            class="ml-2"
                        />
                    </div>

                    <div class="flex items-center">
                        <Checkbox
                            id="internet_available"
                            v-model:checked="form.internet_available"
                            name="internet_available"
                        />
                        <InputLabel
                            for="internet_available"
                            value="Internet"
                            class="ml-2"
                        />
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Additional Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <InputLabel
                            for="nearby_landmarks"
                            value="Nearby Landmarks (comma separated)"
                        />
                        <textarea
                            id="nearby_landmarks"
                            v-model="nearbyLandmarksText"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            placeholder="School, Hospital, Market, etc."
                        ></textarea>
                        <InputError
                            class="mt-2"
                            :message="form.errors.nearby_landmarks"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="google_maps_link"
                            value="Google Maps Link"
                        />
                        <TextInput
                            id="google_maps_link"
                            v-model="form.google_maps_link"
                            type="url"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.google_maps_link"
                        />
                    </div>

                    <div>
                        <InputLabel for="status" value="Status" />
                        <select
                            id="status"
                            v-model="form.status"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                            <option value="under_negotiation">
                                Under Negotiation
                            </option>
                            <option value="off_market">Off Market</option>
                        </select>
                        <InputError
                            class="mt-2"
                            :message="form.errors.status"
                        />
                    </div>

                    <div class="flex items-center">
                        <Checkbox
                            id="is_featured"
                            v-model:checked="form.is_featured"
                            name="is_featured"
                        />
                        <InputLabel
                            for="is_featured"
                            value="Featured Property"
                            class="ml-2"
                        />
                    </div>
                </div>
            </div>

            <!-- Current Images -->
            <div
                v-if="property.images && property.images.length > 0"
                class="bg-white rounded-lg shadow-sm p-6 mb-6"
            >
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Current Images
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div
                        v-for="(image, index) in property.images"
                        :key="index"
                        class="relative"
                    >
                        <img
                            :src="asset('storage/' + image)"
                            :alt="`Property Image ${index + 1}`"
                            class="w-full h-32 object-cover rounded-lg"
                        />
                        <button
                            type="button"
                            @click="removeImage(index)"
                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>

            <!-- New Images -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Add New Images
                </h3>
                <div>
                    <InputLabel
                        for="images"
                        value="Upload New Images (Multiple files allowed)"
                    />
                    <input
                        id="images"
                        type="file"
                        multiple
                        accept="image/*"
                        @change="handleImageUpload"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    />
                    <InputError class="mt-2" :message="form.errors.new_images" />
                    <p class="mt-1 text-sm text-gray-500">
                        Select additional images to add to your property.
                        Supported formats: JPG, PNG, GIF
                    </p>
                </div>
            </div>

            <!-- New Documents -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">
                    Add New Documents
                </h3>
                <div>
                    <InputLabel
                        for="documents"
                        value="Upload New Documents (PDF files)"
                    />
                    <input
                        id="documents"
                        type="file"
                        multiple
                        accept=".pdf"
                        @change="handleDocumentUpload"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    />
                    <InputError class="mt-2" :message="form.errors.new_documents" />
                    <p class="mt-1 text-sm text-gray-500">
                        Upload additional legal documents (PDF format only)
                    </p>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-center justify-end space-x-4">
                    <Link
                        :href="route('properties.show', property.slug)"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition-colors"
                    >
                        Cancel
                    </Link>
                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600"
                    >
                        Update Property
                    </PrimaryButton>
                </div>
            </div>
        </form>

        <!-- Bohol Inspiration Section -->
        <div
            class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6 border border-green-100"
        >
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                    🏝️ Showcase Bohol's Paradise
                </h3>
                <p class="text-gray-600 text-sm">
                    Update your property listing to highlight the unique beauty
                    and investment potential of Bohol. Help buyers discover
                    their perfect piece of paradise.
                </p>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";

const props = defineProps({
    property: Object,
});

const municipalities = [
    "Tagbilaran City",
    "Alburquerque",
    "Alicia",
    "Anda",
    "Antequera",
    "Baclayon",
    "Balilihan",
    "Batuan",
    "Bien Unido",
    "Bilar",
    "Buenavista",
    "Calape",
    "Candijay",
    "Carmen",
    "Catigbian",
    "Clarin",
    "Corella",
    "Cortes",
    "Dagohoy",
    "Danao",
    "Dauis",
    "Dimiao",
    "Duero",
    "Garcia Hernandez",
    "Getafe",
    "Guindulman",
    "Inabanga",
    "Jagna",
    "Jetafe",
    "Lila",
    "Loay",
    "Loboc",
    "Loon",
    "Mabini",
    "Maribojoc",
    "Panglao",
    "Pilar",
    "President Carlos P. Garcia",
    "Sagbayan",
    "San Isidro",
    "San Miguel",
    "Sevilla",
    "Sierra Bullones",
    "Sikatuna",
    "Talibon",
    "Trinidad",
    "Tubigon",
    "Ubay",
    "Valencia",
];

const nearbyLandmarksText = ref("");

const form = useForm({
    title: props.property.title,
    description: props.property.description,
    type: props.property.type,
    municipality: props.property.municipality,
    barangay: props.property.barangay,
    address: props.property.address,
    lot_area_sqm: props.property.lot_area_sqm,
    price_per_sqm: props.property.price_per_sqm,
    total_price: props.property.total_price,
    title_type: props.property.title_type,
    title_number: props.property.title_number,
    tax_declaration_number: props.property.tax_declaration_number,
    zoning_classification: props.property.zoning_classification,
    coordinates_lat: props.property.coordinates_lat,
    coordinates_lng: props.property.coordinates_lng,
    road_access: props.property.road_access,
    electricity_available: props.property.electricity_available,
    water_source: props.property.water_source,
    internet_available: props.property.internet_available,
    nearby_landmarks: props.property.nearby_landmarks || [],
    google_maps_link: props.property.google_maps_link,
    status: props.property.status,
    is_featured: props.property.is_featured,
    new_images: [], // Changed from 'images'
    new_documents: [], // Changed from 'documents'
    remove_images: [],
    remove_documents: [], // Add this field for document removal
});

onMounted(() => {
    if (
        props.property.nearby_landmarks &&
        Array.isArray(props.property.nearby_landmarks)
    ) {
        nearbyLandmarksText.value = props.property.nearby_landmarks.join(", ");
    }
});

const calculateTotalPrice = () => {
    const area = parseFloat(form.lot_area_sqm) || 0;
    const pricePerSqm = parseFloat(form.price_per_sqm) || 0;
    form.total_price = (area * pricePerSqm).toFixed(2);
};

const handleImageUpload = (event) => {
    form.new_images = Array.from(event.target.files); // Changed from 'images'
};

const handleDocumentUpload = (event) => {
    form.new_documents = Array.from(event.target.files); // Changed from 'documents'
};

const removeImage = (index) => {
    if (confirm("Are you sure you want to remove this image?")) {
        form.remove_images.push(props.property.images[index]);
        props.property.images.splice(index, 1);
    }
};

const asset = (path) => {
    return `/${path}`;
};

watch(nearbyLandmarksText, (newValue) => {
    form.nearby_landmarks = newValue
        .split(",")
        .map((item) => item.trim())
        .filter((item) => item);
});

const submit = () => {
    form.post(route("properties.update", props.property.slug), {
        _method: "put",
    });
};
</script>
