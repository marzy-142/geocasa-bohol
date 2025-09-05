<script setup>
import { ref, computed } from "vue";
import {
    DocumentArrowUpIcon,
    XMarkIcon,
    DocumentIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    modelValue: {
        type: [File, Array, null],
        default: null,
    },
    label: {
        type: String,
        required: true,
    },
    accept: {
        type: String,
        default: "*",
    },
    error: {
        type: String,
        default: null,
    },
    required: {
        type: Boolean,
        default: false,
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    maxSize: {
        type: Number,
        default: 5 * 1024 * 1024, // 5MB default
    },
});

const emit = defineEmits(["update:modelValue", "validation-error"]);

const fileInput = ref(null);
const isDragOver = ref(false);
const validationError = ref(null);

const selectedFiles = computed(() => {
    if (props.multiple) {
        return Array.isArray(props.modelValue) ? props.modelValue : [];
    }
    return props.modelValue ? [props.modelValue] : [];
});

// Get allowed file types from accept prop
const allowedTypes = computed(() => {
    if (props.accept === "*") return [];
    return props.accept.split(",").map((type) => type.trim().toLowerCase());
});

const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const validateFile = (file) => {
    validationError.value = null;

    // Check file size
    if (file.size > props.maxSize) {
        const error = `File size (${formatFileSize(
            file.size
        )}) exceeds maximum allowed size of ${formatFileSize(props.maxSize)}`;
        validationError.value = error;
        emit("validation-error", error);
        return false;
    }

    // Check file type
    if (allowedTypes.value.length > 0) {
        const fileExtension = "." + file.name.split(".").pop().toLowerCase();
        const mimeType = file.type.toLowerCase();

        const isValidType = allowedTypes.value.some((type) => {
            if (type.startsWith(".")) {
                return type === fileExtension;
            } else if (type.includes("/")) {
                return (
                    mimeType === type ||
                    mimeType.startsWith(type.replace("*", ""))
                );
            }
            return false;
        });

        if (!isValidType) {
            const error = `File type not allowed. Please upload: ${allowedTypes.value.join(
                ", "
            )}`;
            validationError.value = error;
            emit("validation-error", error);
            return false;
        }
    }

    return true;
};

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);

    if (props.multiple) {
        const validFiles = files.filter((file) => validateFile(file));
        emit("update:modelValue", validFiles);
    } else {
        const file = files[0];
        if (file && validateFile(file)) {
            emit("update:modelValue", file);
        } else if (!file) {
            emit("update:modelValue", null);
        }
    }
};

const handleDrop = (event) => {
    event.preventDefault();
    isDragOver.value = false;

    const files = Array.from(event.dataTransfer.files);

    if (props.multiple) {
        const validFiles = files.filter((file) => validateFile(file));
        emit("update:modelValue", validFiles);
    } else {
        const file = files[0];
        if (file && validateFile(file)) {
            emit("update:modelValue", file);
        }
    }
};

const removeFile = (index = null) => {
    validationError.value = null;

    if (props.multiple) {
        if (index !== null) {
            const newFiles = [...selectedFiles.value];
            newFiles.splice(index, 1);
            emit("update:modelValue", newFiles);
        } else {
            emit("update:modelValue", []);
        }
    } else {
        emit("update:modelValue", null);
    }

    if (fileInput.value) {
        fileInput.value.value = "";
    }
};

const handleDragOver = (event) => {
    event.preventDefault();
    isDragOver.value = true;
};

const handleDragLeave = () => {
    isDragOver.value = false;
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

// Clear validation error when file is removed or changed
const currentError = computed(() => {
    return props.error || validationError.value;
});

// Helper function to provide suggestions based on error type
const getErrorSuggestions = (errorMessage) => {
    if (!errorMessage) return []
    
    const suggestions = []
    const message = errorMessage.toLowerCase()
    
    if (message.includes('format') || message.includes('mimes')) {
        suggestions.push('Convert your file to JPG, PNG, or PDF format')
        suggestions.push('Ensure the file extension matches the actual file type')
    }
    
    if (message.includes('size') || message.includes('exceed')) {
        suggestions.push('Compress your image using online tools or image editors')
        suggestions.push('Try taking a new photo with lower resolution')
        suggestions.push('Use PDF format for documents to reduce file size')
    }
    
    if (message.includes('invalid') || message.includes('corrupted')) {
        suggestions.push('Try uploading a different file')
        suggestions.push('Ensure the file is not corrupted or damaged')
        suggestions.push('Take a new clear photo of your document')
    }
    
    if (message.includes('upload failed') || message.includes('server error')) {
        suggestions.push('Check your internet connection and try again')
        suggestions.push('Wait a moment and retry the upload')
        suggestions.push('Contact support if the problem persists')
    }
    
    return suggestions
}
</script>

<template>
    <div class="space-y-2">
        <!-- Label -->
        <label class="block text-sm font-semibold text-neutral-700">
            {{ label }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </label>

        <!-- File Upload Area -->
        <div
            @drop="handleDrop"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @click="triggerFileInput"
            :class="[
                'relative border-2 border-dashed rounded-2xl p-6 transition-all duration-200 cursor-pointer',
                isDragOver
                    ? 'border-primary-400 bg-primary-50'
                    : selectedFiles.length > 0
                    ? 'border-green-300 bg-green-50'
                    : currentError
                    ? 'border-red-300 bg-red-50'
                    : 'border-neutral-300 hover:border-primary-300 hover:bg-primary-50',
            ]"
        >
            <!-- Hidden File Input -->
            <input
                ref="fileInput"
                type="file"
                :accept="accept"
                :multiple="multiple"
                @change="handleFileSelect"
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            />

            <!-- Upload Content -->
            <div v-if="selectedFiles.length === 0" class="text-center">
                <DocumentArrowUpIcon
                    :class="[
                        'w-12 h-12 mx-auto mb-4',
                        currentError ? 'text-red-400' : 'text-neutral-400',
                    ]"
                />
                <div class="text-sm text-neutral-600 mb-2">
                    <span class="font-medium text-primary-600"
                        >Click to upload</span
                    >
                    or drag and drop
                </div>
                <div class="text-xs text-neutral-500">
                    {{
                        accept === "*"
                            ? "Any file type"
                            : allowedTypes
                                  .map((t) => t.replace(".", "").toUpperCase())
                                  .join(", ")
                    }}
                    (Max {{ formatFileSize(maxSize) }}){{
                        multiple ? ", Multiple files allowed" : ""
                    }}
                </div>
            </div>

            <!-- Selected Files Display -->
            <div v-else class="space-y-2">
                <div
                    v-for="(file, index) in selectedFiles"
                    :key="index"
                    class="flex items-center gap-4 p-2 bg-white rounded-lg border"
                >
                    <div class="flex-shrink-0">
                        <DocumentIcon class="w-8 h-8 text-primary-500" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div
                            class="text-sm font-medium text-neutral-900 truncate"
                        >
                            {{ file.name }}
                        </div>
                        <div class="text-xs text-neutral-500">
                            {{ formatFileSize(file.size) }}
                        </div>
                    </div>
                    <button
                        type="button"
                        @click.stop="removeFile(multiple ? index : null)"
                        class="flex-shrink-0 p-1 text-neutral-400 hover:text-red-500 transition-colors"
                    >
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                </div>

                <div v-if="multiple" class="text-center pt-2">
                    <button
                        type="button"
                        @click.stop="triggerFileInput"
                        class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                    >
                        Add more files
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div
            v-if="currentError"
            class="flex items-center gap-2 text-sm text-red-600"
        >
            <ExclamationTriangleIcon class="w-4 h-4 flex-shrink-0" />
            <span>{{ currentError }}</span>
        </div>
    </div>
    <!-- Enhanced error display -->
    <div v-if="error || validationError" class="mt-2">
        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
            <div class="flex items-start">
                <ExclamationTriangleIcon class="w-5 h-5 text-red-400 mt-0.5 mr-2 flex-shrink-0" />
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-800 mb-1">
                        File Upload Error
                    </p>
                    <p class="text-sm text-red-700">
                        {{ error || validationError }}
                    </p>
                    
                    <!-- Helpful suggestions based on error type -->
                    <div v-if="getErrorSuggestions(error || validationError)" class="mt-2">
                        <p class="text-xs font-medium text-red-800 mb-1">Suggestions:</p>
                        <ul class="text-xs text-red-700 list-disc list-inside space-y-1">
                            <li v-for="suggestion in getErrorSuggestions(error || validationError)" :key="suggestion">
                                {{ suggestion }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
