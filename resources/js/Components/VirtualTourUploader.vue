<template>
    <div class="virtual-tour-uploader">
        <!-- Simple Toggle with Visual Feedback -->
        <div
            class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-6 mb-6"
        >
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div
                        class="w-16 h-16 rounded-full flex items-center justify-center text-3xl"
                        :class="
                            isEnabled
                                ? 'bg-purple-100 animate-pulse'
                                : 'bg-gray-100'
                        "
                    >
                        {{ isEnabled ? "🌟" : "📸" }}
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        Add 360° Virtual Tour
                    </h3>
                    <p class="text-gray-600 mb-4">
                        Let buyers explore your property like they're really
                        there!
                        <strong>Just use your phone camera</strong> - no special
                        equipment needed.
                    </p>

                    <!-- Big, friendly toggle button -->
                    <button
                        type="button"
                        @click="toggleVirtualTour"
                        class="group relative inline-flex items-center px-6 py-3 rounded-lg font-semibold transition-all"
                        :class="
                            isEnabled
                                ? 'bg-gradient-to-r from-purple-600 to-blue-600 text-white shadow-lg hover:shadow-xl'
                                : 'bg-white border-2 border-gray-300 text-gray-700 hover:border-purple-400'
                        "
                    >
                        <span class="text-2xl mr-2">{{
                            isEnabled ? "✓" : "+"
                        }}</span>
                        <span class="text-lg">{{
                            isEnabled
                                ? "Virtual Tour Enabled"
                                : "Add Virtual Tour"
                        }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Upload Area (only show when enabled) -->
        <div v-if="isEnabled" class="space-y-6">
            <!-- Visual Guide -->
            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <div class="text-2xl">💡</div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-blue-900 mb-2">
                            Quick Tips:
                        </h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>✓ Take 4-6 photos from different spots</li>
                            <li>✓ Use your phone's "Panorama" mode</li>
                            <li>✓ Stand still, turn in a circle slowly</li>
                            <li>
                                ✓ Best time: Morning or afternoon (good light)
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Current Images Display (if any) -->
            <div v-if="localExistingImages.length > 0" class="space-y-3">
                <h4 class="font-semibold text-gray-900 flex items-center">
                    <span class="text-green-500 mr-2">✓</span>
                    Your Virtual Tour Photos ({{ localExistingImages.length }})
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <div
                        v-for="(image, index) in localExistingImages"
                        :key="`existing-${index}`"
                        class="relative group"
                    >
                        <img
                            :src="getImageUrl(image)"
                            :alt="`Photo ${index + 1}`"
                            class="w-full h-32 object-cover rounded-lg border-2 border-gray-200"
                        />
                        <div
                            class="absolute bottom-2 left-2 bg-black/70 text-white text-xs px-2 py-1 rounded"
                        >
                            Photo {{ index + 1 }}
                        </div>
                        <button
                            type="button"
                            @click="removeExisting(index)"
                            class="absolute top-2 right-2 bg-red-500 text-white w-8 h-8 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center font-bold hover:bg-red-600 z-10 focus:outline-none focus:ring-2 focus:ring-red-300"
                            title="Remove this photo"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>

            <!-- Drag & Drop Upload Area -->
            <div
                @drop.prevent="handleDrop"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                class="border-3 border-dashed rounded-xl p-8 transition-all"
                :class="
                    isDragging
                        ? 'border-purple-500 bg-purple-50'
                        : 'border-gray-300 bg-gray-50 hover:bg-gray-100'
                "
            >
                <div class="text-center">
                    <!-- Big icon -->
                    <div
                        class="mx-auto w-20 h-20 mb-4 rounded-full flex items-center justify-center"
                        :class="
                            isDragging
                                ? 'bg-purple-100 text-purple-600'
                                : 'bg-gray-100 text-gray-400'
                        "
                    >
                        <svg
                            class="w-10 h-10"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                            />
                        </svg>
                    </div>

                    <!-- Simple instructions -->
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">
                        {{
                            isDragging
                                ? "Drop your photos here!"
                                : "Add Your Photos"
                        }}
                    </h4>
                    <p class="text-gray-600 mb-4">
                        Drag and drop your photos here, or
                    </p>

                    <!-- Big browse button -->
                    <label
                        for="virtual-tour-files"
                        class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg cursor-pointer transition-colors text-lg"
                    >
                        <svg
                            class="w-5 h-5 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"
                            />
                        </svg>
                        Choose Photos from Phone
                    </label>
                    <input
                        id="virtual-tour-files"
                        ref="fileInput"
                        type="file"
                        multiple
                        accept="image/*"
                        capture="environment"
                        class="hidden"
                        @change="handleFileSelect"
                    />

                    <p class="text-xs text-gray-500 mt-3">
                        Supports: JPG, PNG. Up to 10 photos. Max 10MB each.
                    </p>
                </div>
            </div>

            <!-- Preview New Uploads -->
            <div v-if="newImagePreviews.length > 0" class="space-y-3">
                <h4 class="font-semibold text-gray-900 flex items-center">
                    <span class="text-blue-500 mr-2 animate-bounce">📤</span>
                    Ready to Upload ({{ newImagePreviews.length }})
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <div
                        v-for="(preview, index) in newImagePreviews"
                        :key="`new-${index}`"
                        class="relative group"
                    >
                        <img
                            :src="preview"
                            :alt="`New photo ${index + 1}`"
                            class="w-full h-32 object-cover rounded-lg border-2 border-blue-300"
                        />
                        <div
                            class="absolute bottom-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded font-semibold"
                        >
                            NEW {{ index + 1 }}
                        </div>
                        <button
                            type="button"
                            @click="removeNew(index)"
                            class="absolute top-2 right-2 bg-red-500 text-white w-8 h-8 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center font-bold hover:bg-red-600"
                            title="Remove this photo"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>

            <!-- Help Section (Collapsible) -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button
                    type="button"
                    @click="showHelp = !showHelp"
                    class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100 flex items-center justify-between transition-colors"
                >
                    <span class="font-semibold text-gray-700 flex items-center">
                        <span class="text-xl mr-2">❓</span>
                        How do I take 360° photos with my phone?
                    </span>
                    <svg
                        class="w-5 h-5 text-gray-500 transition-transform"
                        :class="{ 'rotate-180': showHelp }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </button>

                <div v-if="showHelp" class="p-4 bg-white space-y-4">
                    <!-- Step by step guide -->
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold"
                            >
                                1
                            </div>
                            <div class="flex-1">
                                <h5 class="font-semibold text-gray-900">
                                    Open your Camera app
                                </h5>
                                <p class="text-sm text-gray-600">
                                    Find "Panorama" or "Pano" mode (most phones
                                    have this)
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold"
                            >
                                2
                            </div>
                            <div class="flex-1">
                                <h5 class="font-semibold text-gray-900">
                                    Stand in the middle of the area
                                </h5>
                                <p class="text-sm text-gray-600">
                                    Pick a good spot that shows the property
                                    clearly
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold"
                            >
                                3
                            </div>
                            <div class="flex-1">
                                <h5 class="font-semibold text-gray-900">
                                    Take the photo
                                </h5>
                                <p class="text-sm text-gray-600">
                                    Slowly turn in a complete circle. Keep the
                                    phone steady!
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center font-bold"
                            >
                                4
                            </div>
                            <div class="flex-1">
                                <h5 class="font-semibold text-gray-900">
                                    Repeat from different spots
                                </h5>
                                <p class="text-sm text-gray-600">
                                    Take 4-6 photos from different locations to
                                    show the whole property
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold"
                            >
                                ✓
                            </div>
                            <div class="flex-1">
                                <h5 class="font-semibold text-gray-900">
                                    Upload here!
                                </h5>
                                <p class="text-sm text-gray-600">
                                    Come back to this page and upload your
                                    photos
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Video tutorial link -->
                    <div
                        class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="text-2xl">🎥</span>
                                <div>
                                    <h5 class="font-semibold text-gray-900">
                                        Watch Video Tutorial
                                    </h5>
                                    <p class="text-xs text-gray-600">
                                        See how it's done in 2 minutes
                                    </p>
                                </div>
                            </div>
                            <a
                                href="https://www.youtube.com/results?search_query=how+to+take+panorama+photo"
                                target="_blank"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-semibold transition-colors"
                            >
                                Watch Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success message -->
            <div
                v-if="successMessage"
                class="bg-green-50 border-2 border-green-200 rounded-lg p-4 flex items-center space-x-3"
            >
                <span class="text-2xl">✓</span>
                <span class="text-green-800 font-medium">{{
                    successMessage
                }}</span>
            </div>

            <!-- Error message -->
            <div
                v-if="errorMessage"
                class="bg-red-50 border-2 border-red-200 rounded-lg p-4 flex items-start space-x-3"
            >
                <span class="text-2xl">⚠️</span>
                <div class="flex-1">
                    <p class="text-red-800 font-medium">{{ errorMessage }}</p>
                    <p class="text-sm text-red-600 mt-1">
                        Need help? Contact support or watch the tutorial above.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    existingImages: {
        type: Array,
        default: () => [],
    },
    maxImages: {
        type: Number,
        default: 10,
    },
    maxFileSize: {
        type: Number,
        default: 10 * 1024 * 1024, // 10MB
    },
});

const emit = defineEmits([
    "update:modelValue",
    "images-changed",
    "existing-removed",
]);

const isEnabled = ref(props.modelValue);
const isDragging = ref(false);
const newImageFiles = ref([]);
const newImagePreviews = ref([]);
const showHelp = ref(false);
const successMessage = ref("");
const errorMessage = ref("");
const fileInput = ref(null);
const localExistingImages = ref(
    Array.isArray(props.existingImages) ? [...props.existingImages] : []
);

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        isEnabled.value = newVal;
    }
);

// Keep local copy of existing images for optimistic UI updates
watch(
    () => props.existingImages,
    (arr) => {
        localExistingImages.value = Array.isArray(arr) ? [...arr] : [];
    },
    { immediate: true }
);

const toggleVirtualTour = () => {
    isEnabled.value = !isEnabled.value;
    emit("update:modelValue", isEnabled.value);

    if (isEnabled.value) {
        successMessage.value =
            "🌟 Virtual Tour enabled! Now add your photos below.";
        setTimeout(() => {
            successMessage.value = "";
        }, 3000);
    } else {
        // Clear everything when disabled
        newImageFiles.value = [];
        newImagePreviews.value = [];
        emit("images-changed", []);
    }
};

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    processFiles(files);
};

const handleDrop = (event) => {
    isDragging.value = false;
    const files = Array.from(event.dataTransfer.files);
    processFiles(files);
};

const processFiles = (files) => {
    errorMessage.value = "";
    successMessage.value = "";

    // Filter image files only
    const imageFiles = files.filter((file) => file.type.startsWith("image/"));

    if (imageFiles.length === 0) {
        errorMessage.value = "Please select image files only (JPG, PNG, etc.)";
        return;
    }

    // Check total count
    const totalImages =
        props.existingImages.length +
        newImageFiles.value.length +
        imageFiles.length;

    if (totalImages > props.maxImages) {
        errorMessage.value = `You can only upload up to ${props.maxImages} photos total. Remove some existing photos first.`;
        return;
    }

    // Check file sizes
    const oversizedFiles = imageFiles.filter(
        (file) => file.size > props.maxFileSize
    );
    if (oversizedFiles.length > 0) {
        errorMessage.value = `Some files are too large. Maximum size is ${Math.round(
            props.maxFileSize / 1024 / 1024
        )}MB per photo.`;
        return;
    }

    // Add files
    imageFiles.forEach((file) => {
        newImageFiles.value.push(file);

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            newImagePreviews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });

    // Emit files to parent
    emit("images-changed", newImageFiles.value);

    successMessage.value = `✓ ${imageFiles.length} photo${
        imageFiles.length > 1 ? "s" : ""
    } added! Don't forget to save the property.`;
    setTimeout(() => {
        successMessage.value = "";
    }, 4000);

    // Reset file input
    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

const removeNew = (index) => {
    newImageFiles.value.splice(index, 1);
    newImagePreviews.value.splice(index, 1);
    emit("images-changed", newImageFiles.value);
};

const removeExisting = (index) => {
    // Optimistic UI: remove from local list immediately
    const removed = localExistingImages.value[index];
    localExistingImages.value.splice(index, 1);
    // Emit index so parent can mark actual path for backend removal
    emit("existing-removed", index);
    successMessage.value =
        "Marked for removal. It will be deleted after you save.";
    setTimeout(() => (successMessage.value = ""), 2500);
};

const getImageUrl = (input) => {
    if (!input) return "";

    // Handle File/Blob (should use previews elsewhere, but guard anyway)
    if (typeof File !== "undefined" && input instanceof File) {
        try {
            return URL.createObjectURL(input);
        } catch {
            return "";
        }
    }

    // If it's an array, try first valid child
    if (Array.isArray(input)) {
        for (const item of input.flat()) {
            const u = getImageUrl(item);
            if (u) return u;
        }
        return "";
    }

    // If it's an object, look for common keys
    if (typeof input === "object") {
        const keys = [
            "url",
            "full_url",
            "src",
            "path",
            "storage_path",
            "filename",
            "name",
        ];
        for (const k of keys) {
            const v = input[k];
            if (typeof v === "string" && v.trim()) {
                return getImageUrl(v.trim());
            }
        }
        return "";
    }

    // Expect string below
    if (typeof input !== "string") return "";
    let path = input.trim();
    if (!path) return "";

    if (path.startsWith("data:") || path.startsWith("blob:")) return path;
    if (path.startsWith("http://") || path.startsWith("https://")) return path;
    if (path.startsWith("/storage/")) return path;
    if (path.startsWith("storage/")) return `/${path}`;
    // Common subdirs from backend
    if (path.startsWith("properties/") || path.startsWith("public/")) {
        path = path.replace(/^public\//, "");
        return `/storage/${path}`;
    }
    return `/storage/${path}`;
};
</script>

<style scoped>
.border-3 {
    border-width: 3px;
}

@keyframes bounce {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

.animate-bounce {
    animation: bounce 2s infinite;
}

.rotate-180 {
    transform: rotate(180deg);
}
</style>
