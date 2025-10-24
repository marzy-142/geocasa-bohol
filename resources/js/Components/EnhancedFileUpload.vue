<script setup>
import { ref, computed, watch } from "vue";
import {
    CloudArrowUpIcon,
    DocumentIcon,
    XMarkIcon,
    EyeIcon,
    TrashIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    modelValue: [File, Array],
    label: String,
    accept: {
        type: String,
        default: ".pdf,.jpg,.jpeg,.png,.doc,.docx",
    },
    maxFiles: {
        type: Number,
        default: 1,
    },
    maxSize: {
        type: Number,
        default: 10 * 1024 * 1024, // 10MB
    },
    error: String,
    helper: String,
    required: {
        type: Boolean,
        default: false,
    },
    preview: {
        type: Boolean,
        default: true,
    },
    dragDrop: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["update:modelValue", "validation-error"]);

const isDragOver = ref(false);
const uploadRef = ref(null);
const previews = ref([]);

// Convert single file to array for consistent handling
const files = computed({
    get() {
        if (Array.isArray(props.modelValue)) {
            return props.modelValue;
        }
        return props.modelValue ? [props.modelValue] : [];
    },
    set(newFiles) {
        emit(
            "update:modelValue",
            props.maxFiles === 1 ? newFiles[0] || null : newFiles
        );
    },
});

// Generate previews for uploaded files
const generatePreviews = (fileList) => {
    previews.value = [];

    Array.from(fileList).forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            previews.value.push({
                file,
                url: e.target.result,
                type: file.type,
                size: file.size,
                name: file.name,
            });
        };
        reader.readAsDataURL(file);
    });
};

// Watch for changes in files
watch(
    files,
    (newFiles) => {
        if (newFiles && newFiles.length > 0) {
            generatePreviews(newFiles);
        } else {
            previews.value = [];
        }
    },
    { immediate: true }
);

const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const validateFile = (file) => {
    const errors = [];

    // Check file size
    if (file.size > props.maxSize) {
        errors.push(
            `File size must not exceed ${formatFileSize(props.maxSize)}`
        );
    }

    // Check file type
    const acceptedTypes = props.accept.split(",").map((type) => type.trim());
    const fileExtension = "." + file.name.split(".").pop().toLowerCase();

    if (!acceptedTypes.includes(fileExtension)) {
        errors.push(
            `File type ${fileExtension} is not allowed. Accepted types: ${props.accept}`
        );
    }

    return errors;
};

const handleFileSelect = (event) => {
    const selectedFiles = Array.from(event.target.files);
    processFiles(selectedFiles);
};

const handleDrop = (event) => {
    event.preventDefault();
    isDragOver.value = false;

    const droppedFiles = Array.from(event.dataTransfer.files);
    processFiles(droppedFiles);
};

const handleDragOver = (event) => {
    event.preventDefault();
    isDragOver.value = true;
};

const handleDragLeave = () => {
    isDragOver.value = false;
};

const processFiles = (newFiles) => {
    const validFiles = [];
    const errors = [];

    newFiles.forEach((file) => {
        const fileErrors = validateFile(file);
        if (fileErrors.length === 0) {
            validFiles.push(file);
        } else {
            errors.push(`${file.name}: ${fileErrors.join(", ")}`);
        }
    });

    if (errors.length > 0) {
        emit("validation-error", errors.join("; "));
        return;
    }

    // Combine with existing files (respect maxFiles limit)
    const currentFiles = files.value || [];
    const combinedFiles = [...currentFiles, ...validFiles].slice(
        0,
        props.maxFiles
    );

    files.value = combinedFiles;
};

const removeFile = (index) => {
    const newFiles = [...files.value];
    newFiles.splice(index, 1);
    files.value = newFiles;
};

const openFileDialog = () => {
    uploadRef.value?.click();
};

const getFileIcon = (fileType) => {
    if (fileType.startsWith("image/")) return "🖼️";
    if (fileType === "application/pdf") return "📄";
    if (fileType.includes("word")) return "📝";
    return "📎";
};

const isImage = (fileType) => {
    return fileType.startsWith("image/");
};
</script>

<template>
    <div class="space-y-4">
        <!-- Label -->
        <label v-if="label" class="block text-sm font-medium text-neutral-700">
            {{ label }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </label>

        <!-- Upload Area -->
        <div
            :class="[
                'relative border-2 border-dashed rounded-xl transition-all duration-200 cursor-pointer',
                isDragOver
                    ? 'border-primary-400 bg-primary-50'
                    : error
                    ? 'border-red-300 bg-red-50'
                    : 'border-neutral-300 hover:border-primary-400 hover:bg-primary-50',
                dragDrop ? 'cursor-pointer' : '',
            ]"
            @click="openFileDialog"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDrop"
        >
            <input
                ref="uploadRef"
                type="file"
                :accept="accept"
                :multiple="maxFiles > 1"
                class="hidden"
                @change="handleFileSelect"
            />

            <!-- Upload Content -->
            <div class="px-6 py-8 text-center">
                <CloudArrowUpIcon
                    :class="[
                        'mx-auto h-12 w-12 mb-4',
                        isDragOver ? 'text-primary-600' : 'text-neutral-400',
                    ]"
                />

                <div class="space-y-2">
                    <p
                        :class="[
                            'text-lg font-medium',
                            isDragOver
                                ? 'text-primary-600'
                                : 'text-neutral-900',
                        ]"
                    >
                        {{ isDragOver ? "Drop files here" : "Upload files" }}
                    </p>

                    <p class="text-sm text-neutral-500">
                        {{
                            dragDrop
                                ? "Drag and drop files here, or click to select"
                                : "Click to select files"
                        }}
                    </p>

                    <p class="text-xs text-neutral-400">
                        Accepted formats: {{ accept }} (max
                        {{ formatFileSize(maxSize) }})
                        {{ maxFiles > 1 ? `, up to ${maxFiles} files` : "" }}
                    </p>
                </div>
            </div>
        </div>

        <!-- File Previews -->
        <div v-if="previews.length > 0 && preview" class="space-y-3">
            <h4 class="text-sm font-medium text-neutral-700">
                Uploaded Files:
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="(preview, index) in previews"
                    :key="index"
                    class="relative bg-white border border-neutral-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200"
                >
                    <!-- File Preview -->
                    <div class="flex items-start space-x-3">
                        <!-- Image Preview -->
                        <div v-if="isImage(preview.type)" class="flex-shrink-0">
                            <img
                                :src="preview.url"
                                :alt="preview.name"
                                class="w-16 h-16 object-cover rounded-lg border border-neutral-200"
                            />
                        </div>

                        <!-- File Icon -->
                        <div
                            v-else
                            class="flex-shrink-0 w-16 h-16 bg-neutral-100 rounded-lg flex items-center justify-center"
                        >
                            <span class="text-2xl">{{
                                getFileIcon(preview.type)
                            }}</span>
                        </div>

                        <!-- File Info -->
                        <div class="flex-1 min-w-0">
                            <p
                                class="text-sm font-medium text-neutral-900 truncate"
                            >
                                {{ preview.name }}
                            </p>
                            <p class="text-xs text-neutral-500">
                                {{ formatFileSize(preview.size) }}
                            </p>

                            <!-- File Actions -->
                            <div class="flex space-x-2 mt-2">
                                <button
                                    type="button"
                                    @click.stop="
                                        window.open(preview.url, '_blank')
                                    "
                                    class="text-xs text-primary-600 hover:text-primary-700 flex items-center space-x-1"
                                >
                                    <EyeIcon class="w-3 h-3" />
                                    <span>Preview</span>
                                </button>

                                <button
                                    type="button"
                                    @click.stop="removeFile(index)"
                                    class="text-xs text-red-600 hover:text-red-700 flex items-center space-x-1"
                                >
                                    <TrashIcon class="w-3 h-3" />
                                    <span>Remove</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div
            v-if="error"
            class="flex items-start space-x-2 text-sm text-red-600"
        >
            <ExclamationTriangleIcon class="w-4 h-4 flex-shrink-0 mt-0.5" />
            <span>{{ error }}</span>
        </div>

        <!-- Helper Text -->
        <p v-else-if="helper" class="text-sm text-neutral-500">
            {{ helper }}
        </p>
    </div>
</template>


