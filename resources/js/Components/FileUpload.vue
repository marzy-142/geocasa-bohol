<script setup>
import { ref, computed } from 'vue';
import { 
    DocumentArrowUpIcon,
    XMarkIcon,
    DocumentIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    modelValue: {
        type: [File, null],
        default: null
    },
    label: {
        type: String,
        required: true
    },
    accept: {
        type: String,
        default: '*'
    },
    error: {
        type: String,
        default: null
    },
    required: {
        type: Boolean,
        default: false
    },
    maxSize: {
        type: Number,
        default: 5 * 1024 * 1024 // 5MB default
    }
});

const emit = defineEmits(['update:modelValue', 'validation-error']);

const fileInput = ref(null);
const isDragOver = ref(false);
const validationError = ref(null);

const selectedFile = computed(() => props.modelValue);

// Get allowed file types from accept prop
const allowedTypes = computed(() => {
    if (props.accept === '*') return [];
    return props.accept.split(',').map(type => type.trim().toLowerCase());
});

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const validateFile = (file) => {
    validationError.value = null;
    
    // Check file size
    if (file.size > props.maxSize) {
        const error = `File size (${formatFileSize(file.size)}) exceeds maximum allowed size of ${formatFileSize(props.maxSize)}`;
        validationError.value = error;
        emit('validation-error', error);
        return false;
    }
    
    // Check file type
    if (allowedTypes.value.length > 0) {
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        const mimeType = file.type.toLowerCase();
        
        const isValidType = allowedTypes.value.some(type => {
            if (type.startsWith('.')) {
                return type === fileExtension;
            } else if (type.includes('/')) {
                return mimeType === type || mimeType.startsWith(type.replace('*', ''));
            }
            return false;
        });
        
        if (!isValidType) {
            const error = `File type not allowed. Please upload: ${allowedTypes.value.join(', ')}`;
            validationError.value = error;
            emit('validation-error', error);
            return false;
        }
    }
    
    return true;
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file && validateFile(file)) {
        emit('update:modelValue', file);
    } else if (!file) {
        emit('update:modelValue', null);
    }
};

const handleDrop = (event) => {
    event.preventDefault();
    isDragOver.value = false;
    
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const file = files[0];
        if (validateFile(file)) {
            emit('update:modelValue', file);
        }
    }
};

const handleDragOver = (event) => {
    event.preventDefault();
    isDragOver.value = true;
};

const handleDragLeave = () => {
    isDragOver.value = false;
};

const removeFile = () => {
    validationError.value = null;
    emit('update:modelValue', null);
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

// Clear validation error when file is removed or changed
const currentError = computed(() => {
    return props.error || validationError.value;
});
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
                    : selectedFile 
                        ? 'border-green-300 bg-green-50' 
                        : currentError 
                            ? 'border-red-300 bg-red-50' 
                            : 'border-neutral-300 hover:border-primary-300 hover:bg-primary-50'
            ]"
        >
            <!-- Hidden File Input -->
            <input
                ref="fileInput"
                type="file"
                :accept="accept"
                @change="handleFileSelect"
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            />

            <!-- Upload Content -->
            <div v-if="!selectedFile" class="text-center">
                <DocumentArrowUpIcon 
                    :class="[
                        'w-12 h-12 mx-auto mb-4',
                        currentError ? 'text-red-400' : 'text-neutral-400'
                    ]" 
                />
                <div class="text-sm text-neutral-600 mb-2">
                    <span class="font-medium text-primary-600">Click to upload</span>
                    or drag and drop
                </div>
                <div class="text-xs text-neutral-500">
                    {{ accept === '*' ? 'Any file type' : allowedTypes.map(t => t.replace('.', '').toUpperCase()).join(', ') }} 
                    (Max {{ formatFileSize(maxSize) }})
                </div>
            </div>

            <!-- Selected File Display -->
            <div v-else class="flex items-center gap-4">
                <div class="flex-shrink-0">
                    <DocumentIcon class="w-10 h-10 text-green-600" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-neutral-900 truncate">
                        {{ selectedFile.name }}
                    </p>
                    <p class="text-xs text-neutral-500">
                        {{ formatFileSize(selectedFile.size) }}
                    </p>
                </div>
                <button
                    @click.stop="removeFile"
                    type="button"
                    class="flex-shrink-0 p-1 text-neutral-400 hover:text-red-500 transition-colors"
                >
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Error Message -->
        <div v-if="currentError" class="flex items-start gap-2 p-3 bg-red-50 border border-red-200 rounded-lg">
            <ExclamationTriangleIcon class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
            <p class="text-sm text-red-600">
                {{ currentError }}
            </p>
        </div>

        <!-- Help Text -->
        <p v-if="!currentError && !selectedFile" class="text-xs text-neutral-500">
            Upload your document in {{ allowedTypes.length > 0 ? allowedTypes.map(t => t.replace('.', '').toUpperCase()).join(', ') : 'supported' }} format
        </p>
    </div>
</template>