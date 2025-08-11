<script setup>
import { ref, computed, onMounted } from "vue";
import { useForm, Head } from "@inertiajs/vue3";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import PublicFooter from "@/Components/PublicFooter.vue";
import ModernButton from "@/Components/ModernButton.vue";
import {
    CheckCircleIcon,
    PhotoIcon,
    MapPinIcon,
    HomeIcon,
    UserIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    XMarkIcon,
    ShieldCheckIcon,
    ClockIcon,
    StarIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    availableFeatures: Array,
    auth: Object,
});

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

const imageFiles = ref([]);
const currentStep = ref(1);
const totalSteps = 4;
const isSubmitting = ref(false);
const submissionStatus = ref(null); // 'success', 'error', or null
const submissionMessage = ref("");

// Form validation
const isStep1Valid = computed(() => {
    return form.seller_name && form.seller_email && form.seller_phone;
});

const isStep2Valid = computed(() => {
    return (
        form.property_title &&
        form.property_description &&
        form.asking_price &&
        form.property_area
    );
});

const isStep3Valid = computed(() => {
    return (
        form.property_location &&
        form.property_address &&
        form.city &&
        form.state
    );
});

const canProceed = computed(() => {
    switch (currentStep.value) {
        case 1:
            return isStep1Valid.value;
        case 2:
            return isStep2Valid.value;
        case 3:
            return isStep3Valid.value;
        case 4:
            return true;
        default:
            return false;
    }
});

const getStepIcon = (step) => {
    const icons = {
        1: UserIcon,
        2: DocumentTextIcon,
        3: MapPinIcon,
        4: PhotoIcon,
    };
    return icons[step] || HomeIcon;
};

const createImageUrl = (file) => {
    if (
        typeof window !== "undefined" &&
        window.URL &&
        window.URL.createObjectURL
    ) {
        return window.URL.createObjectURL(file);
    }
    return "";
};

const nextStep = () => {
    if (canProceed.value && currentStep.value < totalSteps) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files);

    // Validate file count
    if (imageFiles.value.length + files.length > 10) {
        alert("You can upload maximum 10 images");
        return;
    }

    // Validate file size and type
    const validFiles = files.filter((file) => {
        if (file.size > 5 * 1024 * 1024) {
            // 5MB
            alert(`${file.name} is too large. Maximum size is 5MB.`);
            return false;
        }
        if (
            !["image/jpeg", "image/png", "image/jpg", "image/gif"].includes(
                file.type
            )
        ) {
            alert(`${file.name} is not a valid image format.`);
            return false;
        }
        return true;
    });

    imageFiles.value.push(...validFiles);
    // Store actual File objects for form submission
    form.uploaded_images = [...imageFiles.value];
};

const removeImage = (index) => {
    imageFiles.value.splice(index, 1);
    form.uploaded_images = [...imageFiles.value];
};

const toggleFeature = (feature) => {
    const index = form.features.indexOf(feature);
    if (index > -1) {
        form.features.splice(index, 1);
    } else {
        if (form.features.length < 20) {
            form.features.push(feature);
        } else {
            alert("You can select maximum 20 features");
        }
    }
};

const formatPrice = (value) => {
    if (!value) return "";
    return new Intl.NumberFormat("en-PH").format(value);
};

const submitForm = () => {
    if (!canProceed.value) return;

    isSubmitting.value = true;
    submissionStatus.value = null;
    submissionMessage.value = "";

    // Create FormData for proper file upload
    const formData = new FormData();

    // Add all form fields
    Object.keys(form.data()).forEach((key) => {
        if (key === "uploaded_images") {
            // Handle image files separately
            imageFiles.value.forEach((file, index) => {
                formData.append(`uploaded_images[${index}]`, file);
            });
        } else if (key === "features") {
            // Handle array fields
            form.features.forEach((feature, index) => {
                formData.append(`features[${index}]`, feature);
            });
        } else {
            formData.append(key, form[key] || "");
        }
    });

    // Use router.post with FormData
    form.post(route("seller-requests.store"), {
        forceFormData: true,
        onSuccess: () => {
            submissionStatus.value = "success";
            submissionMessage.value =
                "Your property has been submitted successfully! We will review it within 24-48 hours.";
            // Clear the draft
            localStorage.removeItem("seller_request_draft");
            // Scroll to top to show success message
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
        onError: (errors) => {
            submissionStatus.value = "error";
            submissionMessage.value =
                "Please fix the following errors and try again:";
            console.error("Submission errors:", errors);
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

// Auto-save draft functionality
const saveDraft = () => {
    localStorage.setItem("seller_request_draft", JSON.stringify(form.data()));
};

const loadDraft = () => {
    const draft = localStorage.getItem("seller_request_draft");
    if (draft) {
        const draftData = JSON.parse(draft);
        Object.keys(draftData).forEach((key) => {
            if (form.hasOwnProperty(key)) {
                form[key] = draftData[key];
            }
        });
    }
};

onMounted(() => {
    loadDraft();
});
// Helper function to format field names for display
const formatFieldName = (field) => {
    const fieldMap = {
        seller_name: "Full Name",
        seller_email: "Email Address",
        seller_phone: "Phone Number",
        seller_address: "Current Address",
        property_title: "Property Title",
        property_description: "Property Description",
        asking_price: "Asking Price",
        property_area: "Property Area",
        area_unit: "Area Unit",
        property_location: "Property Location",
        property_address: "Property Address",
        city: "City",
        state: "State",
        zip_code: "ZIP Code",
        latitude: "Latitude",
        longitude: "Longitude",
        property_type: "Property Type",
        features: "Features",
        uploaded_images: "Property Images",
    };

    return (
        fieldMap[field] ||
        field.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase())
    );
};
</script>

<template>
    <Head title="Sell Your Property - GeoCasa Bohol" />

    <!-- Public Navigation -->
    <PublicNavigation :auth="auth" current-route="seller-requests.create" />

    <!-- Main Content -->
    <main class="min-h-screen bg-neutral-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Submission Status -->
            <div v-if="submissionStatus" class="mb-8">
                <div
                    :class="[
                        'p-4 rounded-2xl border',
                        submissionStatus === 'success'
                            ? 'bg-green-50 border-green-200 text-green-800'
                            : 'bg-red-50 border-red-200 text-red-800',
                    ]"
                >
                    <div class="flex items-start gap-3">
                        <CheckCircleIcon
                            v-if="submissionStatus === 'success'"
                            class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5"
                        />
                        <ExclamationTriangleIcon
                            v-else
                            class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5"
                        />
                        <div class="flex-1">
                            <h3 class="font-semibold mb-1">
                                {{
                                    submissionStatus === "success"
                                        ? "Success!"
                                        : "Validation Errors"
                                }}
                            </h3>
                            <p class="text-sm mb-2">{{ submissionMessage }}</p>

                            <!-- Show specific validation errors -->
                            <div
                                v-if="
                                    submissionStatus === 'error' &&
                                    Object.keys(form.errors).length > 0
                                "
                                class="mt-3"
                            >
                                <ul class="text-sm space-y-1">
                                    <li
                                        v-for="(error, field) in form.errors"
                                        :key="field"
                                        class="flex items-start gap-2"
                                    >
                                        <span
                                            class="w-1 h-1 bg-red-600 rounded-full mt-2 flex-shrink-0"
                                        ></span>
                                        <span
                                            ><strong
                                                >{{
                                                    formatFieldName(field)
                                                }}:</strong
                                            >
                                            {{ error }}</span
                                        >
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Header -->
            <div class="text-center mb-12">
                <div class="flex items-center justify-center gap-4 mb-6">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-primary-500 to-accent-500 rounded-3xl flex items-center justify-center shadow-lg"
                    >
                        <HomeIcon class="w-8 h-8 text-white" />
                    </div>
                </div>
                <h1 class="text-4xl font-bold text-neutral-900 mb-4">
                    Sell Your Property
                </h1>
                <p class="text-xl text-neutral-600 max-w-2xl mx-auto">
                    List your property with GeoCasa Bohol and reach qualified
                    buyers through our network of licensed brokers
                </p>
            </div>

            <!-- Benefits Banner -->
            <div
                class="bg-white border border-neutral-200 p-8 mb-12 rounded-3xl shadow-sm"
            >
                <div class="grid md:grid-cols-3 gap-8 text-center">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center mb-4"
                        >
                            <ShieldCheckIcon class="w-7 h-7 text-primary-600" />
                        </div>
                        <h3
                            class="text-lg font-bold mb-3 text-neutral-900 leading-tight"
                        >
                            Licensed Brokers
                        </h3>
                        <p class="text-sm text-neutral-600 leading-relaxed">
                            Work with verified, professional real estate brokers
                        </p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div
                            class="w-16 h-16 bg-accent-100 rounded-2xl flex items-center justify-center mb-4"
                        >
                            <ClockIcon class="w-7 h-7 text-accent-600" />
                        </div>
                        <h3
                            class="text-lg font-bold mb-3 text-neutral-900 leading-tight"
                        >
                            Quick Process
                        </h3>
                        <p class="text-sm text-neutral-600 leading-relaxed">
                            Get your property listed within 24-48 hours
                        </p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div
                            class="w-16 h-16 bg-coconut-100 rounded-2xl flex items-center justify-center mb-4"
                        >
                            <StarIcon class="w-7 h-7 text-coconut-600" />
                        </div>
                        <h3
                            class="text-lg font-bold mb-3 text-neutral-900 leading-tight"
                        >
                            Premium Exposure
                        </h3>
                        <p class="text-sm text-neutral-600 leading-relaxed">
                            Maximum visibility to qualified buyers
                        </p>
                    </div>
                </div>
            </div>

            <!-- Progress Indicator -->
            <div class="mb-12">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-neutral-600"
                        >Step {{ currentStep }} of {{ totalSteps }}</span
                    >
                    <span class="text-sm text-neutral-500"
                        >{{ Math.round((currentStep / totalSteps) * 100) }}%
                        Complete</span
                    >
                </div>
                <div class="w-full bg-neutral-200 rounded-full h-2 mb-8">
                    <div
                        class="bg-primary-500 h-2 rounded-full transition-all duration-500 ease-out"
                        :style="{
                            width: `${(currentStep / totalSteps) * 100}%`,
                        }"
                    ></div>
                </div>

                <!-- Step Icons -->
                <div class="flex justify-between items-center">
                    <div
                        v-for="step in totalSteps"
                        :key="step"
                        class="flex flex-col items-center"
                    >
                        <div
                            :class="[
                                'w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-300',
                                step <= currentStep
                                    ? 'bg-primary-500 text-white shadow-lg'
                                    : 'bg-neutral-200 text-neutral-400',
                            ]"
                        >
                            <component
                                :is="getStepIcon(step)"
                                class="w-5 h-5"
                            />
                        </div>
                        <span
                            :class="[
                                'text-xs mt-2 font-medium transition-colors',
                                step <= currentStep
                                    ? 'text-primary-600'
                                    : 'text-neutral-400',
                            ]"
                        >
                            {{
                                ["Contact", "Property", "Location", "Images"][
                                    step - 1
                                ]
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="card p-8 rounded-3xl shadow-soft">
                <form @submit.prevent="submitForm" @input="saveDraft">
                    <!-- Step 1: Contact Information -->
                    <div v-if="currentStep === 1" class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <UserIcon class="w-6 h-6 text-primary-600" />
                            <h2 class="text-2xl font-bold text-neutral-900">
                                Contact Information
                            </h2>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Full Name *</label>
                                <input
                                    v-model="form.seller_name"
                                    type="text"
                                    class="form-input"
                                    placeholder="Enter your full name"
                                    required
                                />
                                <div
                                    v-if="form.errors.seller_name"
                                    class="form-error"
                                >
                                    {{ form.errors.seller_name }}
                                </div>
                            </div>

                            <div>
                                <label class="form-label"
                                    >Email Address *</label
                                >
                                <input
                                    v-model="form.seller_email"
                                    type="email"
                                    class="form-input"
                                    placeholder="your.email@example.com"
                                    required
                                />
                                <div
                                    v-if="form.errors.seller_email"
                                    class="form-error"
                                >
                                    {{ form.errors.seller_email }}
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Phone Number *</label>
                                <input
                                    v-model="form.seller_phone"
                                    type="tel"
                                    class="form-input"
                                    placeholder="+63 XXX XXX XXXX"
                                    required
                                />
                                <div
                                    v-if="form.errors.seller_phone"
                                    class="form-error"
                                >
                                    {{ form.errors.seller_phone }}
                                </div>
                            </div>

                            <div>
                                <label class="form-label"
                                    >Current Address</label
                                >
                                <input
                                    v-model="form.seller_address"
                                    type="text"
                                    class="form-input"
                                    placeholder="Your current address"
                                />
                                <div
                                    v-if="form.errors.seller_address"
                                    class="form-error"
                                >
                                    {{ form.errors.seller_address }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Property Details -->
                    <div v-if="currentStep === 2" class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <DocumentTextIcon
                                class="w-6 h-6 text-primary-600"
                            />
                            <h2 class="text-2xl font-bold text-neutral-900">
                                Property Details
                            </h2>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="form-label"
                                    >Property Title *</label
                                >
                                <input
                                    v-model="form.property_title"
                                    type="text"
                                    class="form-input"
                                    placeholder="e.g., Beautiful 3-Bedroom House in Tagbilaran (minimum 10 characters)"
                                    required
                                />
                                <div
                                    v-if="form.errors.property_title"
                                    class="form-error"
                                >
                                    {{ form.errors.property_title }}
                                </div>
                                <div
                                    v-else
                                    class="text-xs text-neutral-500 mt-1"
                                >
                                    {{ form.property_title.length }}/10
                                    characters minimum
                                </div>
                            </div>

                            <div>
                                <label class="form-label"
                                    >Property Description *</label
                                >
                                <textarea
                                    v-model="form.property_description"
                                    class="form-input min-h-[120px]"
                                    placeholder="Describe your property in detail... (minimum 50 characters)"
                                    required
                                ></textarea>
                                <div
                                    v-if="form.errors.property_description"
                                    class="form-error"
                                >
                                    {{ form.errors.property_description }}
                                </div>
                                <div
                                    v-else
                                    class="text-xs text-neutral-500 mt-1"
                                >
                                    {{ form.property_description.length }}/50
                                    characters minimum
                                </div>
                            </div>

                            <div class="grid md:grid-cols-3 gap-6">
                                <div>
                                    <label class="form-label"
                                        >Asking Price (₱) *</label
                                    >
                                    <input
                                        v-model="form.asking_price"
                                        type="number"
                                        class="form-input"
                                        placeholder="5000000"
                                        required
                                    />
                                    <div
                                        v-if="form.asking_price"
                                        class="text-sm text-neutral-600 mt-1"
                                    >
                                        ₱{{ formatPrice(form.asking_price) }}
                                    </div>
                                    <div
                                        v-if="form.errors.asking_price"
                                        class="form-error"
                                    >
                                        {{ form.errors.asking_price }}
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label"
                                        >Property Area *</label
                                    >
                                    <input
                                        v-model="form.property_area"
                                        type="number"
                                        class="form-input"
                                        placeholder="150"
                                        required
                                    />
                                    <div
                                        v-if="form.errors.property_area"
                                        class="form-error"
                                    >
                                        {{ form.errors.property_area }}
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label">Area Unit</label>
                                    <select
                                        v-model="form.area_unit"
                                        class="form-input"
                                    >
                                        <option value="sqm">
                                            Square Meters
                                        </option>
                                        <option value="sqft">
                                            Square Feet
                                        </option>
                                        <option value="hectare">Hectare</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Property Type</label>
                                <select
                                    v-model="form.property_type"
                                    class="form-input"
                                >
                                    <option value="residential">
                                        Residential
                                    </option>
                                    <option value="commercial">
                                        Commercial
                                    </option>
                                    <option value="industrial">
                                        Industrial
                                    </option>
                                    <option value="agricultural">
                                        Agricultural
                                    </option>
                                    <option value="mixed-use">Mixed Use</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Location Details -->
                    <div v-if="currentStep === 3" class="space-y-6">
                        <div class="flex items-center gap-3 mb-6">
                            <MapPinIcon class="w-6 h-6 text-primary-600" />
                            <h2 class="text-2xl font-bold text-neutral-900">
                                Location Details
                            </h2>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="form-label"
                                    >Property Location/Landmark *</label
                                >
                                <input
                                    v-model="form.property_location"
                                    type="text"
                                    class="form-input"
                                    placeholder="e.g., Near Bohol Quality Mall, Tagbilaran City"
                                    required
                                />
                                <div
                                    v-if="form.errors.property_location"
                                    class="form-error"
                                >
                                    {{ form.errors.property_location }}
                                </div>
                            </div>

                            <div>
                                <label class="form-label"
                                    >Complete Address *</label
                                >
                                <textarea
                                    v-model="form.property_address"
                                    class="form-input"
                                    placeholder="Street, Barangay, Municipality/City"
                                    required
                                ></textarea>
                                <div
                                    v-if="form.errors.property_address"
                                    class="form-error"
                                >
                                    {{ form.errors.property_address }}
                                </div>
                            </div>

                            <div class="grid md:grid-cols-3 gap-6">
                                <div>
                                    <label class="form-label"
                                        >City/Municipality *</label
                                    >
                                    <input
                                        v-model="form.city"
                                        type="text"
                                        class="form-input"
                                        placeholder="Tagbilaran City"
                                        required
                                    />
                                    <div
                                        v-if="form.errors.city"
                                        class="form-error"
                                    >
                                        {{ form.errors.city }}
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label"
                                        >Province/State</label
                                    >
                                    <input
                                        v-model="form.state"
                                        type="text"
                                        class="form-input"
                                        value="Bohol"
                                        readonly
                                    />
                                </div>

                                <div>
                                    <label class="form-label">ZIP Code</label>
                                    <input
                                        v-model="form.zip_code"
                                        type="text"
                                        class="form-input"
                                        placeholder="6300"
                                    />
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="form-label"
                                        >Latitude (Optional)</label
                                    >
                                    <input
                                        v-model="form.latitude"
                                        type="number"
                                        step="any"
                                        class="form-input"
                                        placeholder="9.6496"
                                    />
                                </div>

                                <div>
                                    <label class="form-label"
                                        >Longitude (Optional)</label
                                    >
                                    <input
                                        v-model="form.longitude"
                                        type="number"
                                        step="any"
                                        class="form-input"
                                        placeholder="123.8547"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Images and Features -->
                    <div v-if="currentStep === 4" class="space-y-8">
                        <div class="flex items-center gap-3 mb-6">
                            <PhotoIcon class="w-6 h-6 text-primary-600" />
                            <h2 class="text-2xl font-bold text-neutral-900">
                                Images & Features
                            </h2>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="form-label">Property Images</label>
                            <div
                                class="border-2 border-dashed border-neutral-300 rounded-2xl p-8 text-center hover:border-primary-400 transition-colors"
                            >
                                <PhotoIcon
                                    class="w-12 h-12 text-neutral-400 mx-auto mb-4"
                                />
                                <p class="text-neutral-600 mb-4">
                                    Drag and drop images here, or
                                    <label
                                        class="text-primary-600 hover:text-primary-700 cursor-pointer font-medium"
                                    >
                                        browse files
                                        <input
                                            type="file"
                                            multiple
                                            accept="image/*"
                                            @change="handleImageUpload"
                                            class="hidden"
                                        />
                                    </label>
                                </p>
                                <p class="text-sm text-neutral-500">
                                    Maximum 10 images, 5MB each. Supported: JPG,
                                    PNG, GIF
                                </p>
                            </div>

                            <!-- Image Preview -->
                            <div
                                v-if="imageFiles.length > 0"
                                class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6"
                            >
                                <div
                                    v-for="(file, index) in imageFiles"
                                    :key="index"
                                    class="relative group"
                                >
                                    <img
                                        :src="createImageUrl(file)"
                                        :alt="`Property image ${index + 1}`"
                                        class="w-full h-24 object-cover rounded-xl"
                                    />
                                    <button
                                        type="button"
                                        @click="removeImage(index)"
                                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Property Features -->
                        <div
                            v-if="
                                availableFeatures &&
                                availableFeatures.length > 0
                            "
                        >
                            <label class="form-label"
                                >Property Features (Select up to 20)</label
                            >
                            <div
                                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3"
                            >
                                <button
                                    v-for="feature in availableFeatures"
                                    :key="feature"
                                    type="button"
                                    @click="toggleFeature(feature)"
                                    :class="[
                                        'px-4 py-2 rounded-xl text-sm font-medium transition-all',
                                        form.features.includes(feature)
                                            ? 'bg-primary-100 text-primary-700 border-2 border-primary-300'
                                            : 'bg-neutral-100 text-neutral-700 border-2 border-transparent hover:bg-neutral-200',
                                    ]"
                                >
                                    {{ feature }}
                                </button>
                            </div>
                            <p class="text-sm text-neutral-500 mt-2">
                                Selected: {{ form.features.length }}/20
                            </p>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div
                        class="flex justify-between items-center pt-8 border-t border-neutral-200"
                    >
                        <ModernButton
                            v-if="currentStep > 1"
                            type="button"
                            variant="ghost"
                            @click="prevStep"
                            class="flex items-center gap-2"
                        >
                            ← Previous
                        </ModernButton>
                        <div v-else></div>

                        <div class="flex gap-4">
                            <ModernButton
                                v-if="currentStep < totalSteps"
                                type="button"
                                variant="primary"
                                @click="nextStep"
                                :disabled="!canProceed"
                                class="flex items-center gap-2"
                            >
                                Next →
                            </ModernButton>

                            <ModernButton
                                v-else
                                type="submit"
                                variant="primary"
                                :disabled="!canProceed || isSubmitting"
                                :loading="isSubmitting"
                                class="flex items-center gap-2"
                            >
                                <CheckCircleIcon class="w-5 h-5" />
                                Submit Property
                            </ModernButton>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Help Section -->
            <div
                class="mt-12 bg-white border border-neutral-200 p-6 rounded-2xl shadow-sm"
            >
                <h3 class="text-lg font-semibold text-neutral-900 mb-4">
                    Need Help?
                </h3>
                <div class="grid md:grid-cols-2 gap-6 text-sm text-neutral-600">
                    <div>
                        <p class="mb-2"><strong>What happens next?</strong></p>
                        <ul class="space-y-1 text-neutral-600">
                            <li>
                                • Your submission will be reviewed within 24-48
                                hours
                            </li>
                            <li>
                                • We'll verify property details and
                                documentation
                            </li>
                            <li>
                                • Once approved, your property goes live on our
                                platform
                            </li>
                            <li>
                                • Licensed brokers will start marketing your
                                property
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p class="mb-2"><strong>Contact Support</strong></p>
                        <p>
                            Email:
                            <a
                                href="mailto:support@geocasabohol.com"
                                class="text-primary-600 hover:text-primary-700"
                                >support@geocasabohol.com</a
                            >
                        </p>
                        <p>
                            Phone:
                            <a
                                href="tel:+631234567890"
                                class="text-primary-600 hover:text-primary-700"
                                >+63 123 456 7890</a
                            >
                        </p>
                        <p class="mt-2 text-xs text-neutral-500">
                            Available Mon-Sat, 8:00 AM - 6:00 PM
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Public Footer -->
    <PublicFooter />
</template>

<style scoped>
.form-group {
    @apply space-y-2;
}

.form-label {
    @apply block text-sm font-medium text-neutral-700;
}

.form-input {
    @apply w-full px-4 py-3 border border-neutral-200 rounded-2xl text-neutral-900 placeholder-neutral-500 
           focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200
           hover:border-neutral-300;
}

.form-select {
    @apply w-full px-4 py-3 border border-neutral-200 rounded-2xl text-neutral-900 
           focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200
           hover:border-neutral-300 bg-white;
}

.form-error {
    @apply text-red-600 text-sm;
}

.tropical-gradient {
    background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 50%, #10b981 100%);
}

.card {
    @apply bg-white rounded-2xl shadow-soft border border-neutral-100;
}

.shadow-soft {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05),
        0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

.bohol-pattern {
    background-image: radial-gradient(
            circle at 25% 25%,
            rgba(14, 165, 233, 0.05) 0%,
            transparent 50%
        ),
        radial-gradient(
            circle at 75% 75%,
            rgba(16, 185, 129, 0.05) 0%,
            transparent 50%
        );
}
</style>
