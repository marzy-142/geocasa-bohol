<template>
    <ModernDashboardLayout>
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <Link
                    :href="route('client.dashboard')"
                    class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
                >
                    <ChevronLeftIcon class="w-4 h-4 mr-1" />
                    Back to Dashboard
                </Link>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    List Your Land for Sale
                </h1>
                <p class="text-gray-600">
                    Own land in Bohol? Submit your property details and get
                    matched with a professional broker who will help you sell
                    it.
                </p>
            </div>

            <!-- Form Card -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-8"
            >
                <form @submit.prevent="submit">
                    <!-- Land Information Section -->
                    <div class="mb-8">
                        <h2
                            class="text-xl font-semibold text-gray-900 mb-4 flex items-center"
                        >
                            <HomeModernIcon
                                class="w-6 h-6 mr-2 text-blue-600"
                            />
                            Land Information
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Municipality -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Municipality
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.municipality"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required
                                >
                                    <option value="">
                                        Select municipality
                                    </option>
                                    <option
                                        v-for="municipality in municipalities"
                                        :key="municipality"
                                        :value="municipality"
                                    >
                                        {{ municipality }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.municipality"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.municipality }}
                                </p>
                            </div>

                            <!-- Barangay -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Barangay <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.barangay"
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Barangay name"
                                    required
                                />
                                <p
                                    v-if="form.errors.barangay"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.barangay }}
                                </p>
                            </div>

                            <!-- Address -->
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Specific Location/Address
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.address"
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Street name, landmarks, or specific location details"
                                    required
                                />
                                <p
                                    v-if="form.errors.address"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.address }}
                                </p>
                            </div>

                            <!-- Lot Area -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Lot Area (sqm)
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.lot_area"
                                    type="number"
                                    step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0.00"
                                    required
                                />
                                <p
                                    v-if="form.errors.lot_area"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.lot_area }}
                                </p>
                            </div>

                            <!-- Price Expectation -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Price Expectation (₱)
                                </label>
                                <input
                                    v-model="form.price_expectation"
                                    type="number"
                                    step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="0.00"
                                />
                                <p class="mt-1 text-sm text-gray-500">
                                    Optional: Your expected selling price
                                </p>
                            </div>

                            <!-- Land Description -->
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Land Description
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Describe the land: terrain (flat/sloped), road access, utilities available, nearby landmarks, etc."
                                ></textarea>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ form.description?.length || 0 }}/2000
                                    characters
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="mb-8 pt-8 border-t border-gray-200">
                        <h2
                            class="text-xl font-semibold text-gray-900 mb-4 flex items-center"
                        >
                            <UserIcon class="w-6 h-6 mr-2 text-blue-600" />
                            Contact Information
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Contact Name -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Full Name
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.contact_name"
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required
                                />
                                <p
                                    v-if="form.errors.contact_name"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.contact_name }}
                                </p>
                            </div>

                            <!-- Contact Email -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.contact_email"
                                    type="email"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required
                                />
                                <p
                                    v-if="form.errors.contact_email"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.contact_email }}
                                </p>
                            </div>

                            <!-- Contact Phone -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Phone Number
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.contact_phone"
                                    type="tel"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="+63 XXX XXX XXXX"
                                    required
                                />
                                <p
                                    v-if="form.errors.contact_phone"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.contact_phone }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Images & Documents Section -->
                    <div class="mb-8 pt-8 border-t border-gray-200">
                        <h2
                            class="text-xl font-semibold text-gray-900 mb-4 flex items-center"
                        >
                            <PhotoIcon class="w-6 h-6 mr-2 text-blue-600" />
                            Photos & Documents
                        </h2>

                        <!-- Images Upload -->
                        <div class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Property Photos
                            </label>
                            <input
                                type="file"
                                @change="handleImageUpload"
                                accept="image/*"
                                multiple
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p class="mt-1 text-sm text-gray-500">
                                Upload up to 10 photos (max 5MB each)
                            </p>
                            <p
                                v-if="form.errors.images"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.images }}
                            </p>

                            <!-- Image Preview -->
                            <div
                                v-if="imagePreviews.length > 0"
                                class="mt-4 grid grid-cols-3 md:grid-cols-5 gap-4"
                            >
                                <div
                                    v-for="(preview, index) in imagePreviews"
                                    :key="index"
                                    class="relative group"
                                >
                                    <img
                                        :src="preview"
                                        class="w-full h-24 object-cover rounded-lg"
                                    />
                                    <button
                                        type="button"
                                        @click="removeImage(index)"
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Documents Upload -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Documents (Title, Tax Declaration, etc.)
                            </label>
                            <input
                                type="file"
                                @change="handleDocumentUpload"
                                accept=".pdf,.doc,.docx"
                                multiple
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p class="mt-1 text-sm text-gray-500">
                                Upload relevant documents (PDF, DOC, DOCX - max
                                10MB each)
                            </p>
                            <p
                                v-if="form.errors.documents"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.documents }}
                            </p>

                            <!-- Document List -->
                            <div
                                v-if="documentNames.length > 0"
                                class="mt-4 space-y-2"
                            >
                                <div
                                    v-for="(name, index) in documentNames"
                                    :key="index"
                                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                >
                                    <div class="flex items-center">
                                        <DocumentIcon
                                            class="w-5 h-5 text-gray-400 mr-2"
                                        />
                                        <span class="text-sm text-gray-700">{{
                                            name
                                        }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeDocument(index)"
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        <XMarkIcon class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div
                        class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200"
                    >
                        <Link
                            :href="route('client.dashboard')"
                            class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                        >
                            <span v-if="form.processing">
                                <svg
                                    class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Submitting...
                            </span>
                            <span v-else>Submit Request</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    ChevronLeftIcon,
    HomeModernIcon,
    UserIcon,
    PhotoIcon,
    DocumentIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    client: Object,
    municipalities: Array,
    propertyTypes: Array,
});

const form = useForm({
    property_type: "titled_land", // Fixed as titled_land
    address: "",
    municipality: "",
    barangay: "",
    lot_area: "",
    price_expectation: "",
    description: "",
    contact_name: props.client?.name || "",
    contact_email: props.client?.email || "",
    contact_phone: props.client?.phone || "",
    images: [],
    documents: [],
});

const imagePreviews = ref([]);
const documentNames = ref([]);

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files);
    form.images = files;

    // Create previews
    imagePreviews.value = [];
    files.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index) => {
    const newImages = Array.from(form.images);
    newImages.splice(index, 1);
    form.images = newImages;
    imagePreviews.value.splice(index, 1);
};

const handleDocumentUpload = (event) => {
    const files = Array.from(event.target.files);
    form.documents = files;
    documentNames.value = files.map((file) => file.name);
};

const removeDocument = (index) => {
    const newDocuments = Array.from(form.documents);
    newDocuments.splice(index, 1);
    form.documents = newDocuments;
    documentNames.value.splice(index, 1);
};

const submit = () => {
    form.post(route("client.seller-requests.store"), {
        preserveScroll: true,
    });
};
</script>
