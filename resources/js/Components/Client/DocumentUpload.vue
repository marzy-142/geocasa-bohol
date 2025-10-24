<template>
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                Document Management
            </h3>
            <p class="mt-1 text-sm text-gray-600">
                Upload and manage documents for this transaction
            </p>
        </div>

        <div class="px-6 py-4 space-y-6">
            <!-- Upload Area -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Upload Documents
                </label>
                <div
                    @drop="handleDrop"
                    @dragover.prevent
                    @dragenter.prevent
                    :class="
                        isDragOver
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-gray-300'
                    "
                    class="relative border-2 border-dashed rounded-lg p-6 hover:border-gray-400 transition-colors"
                >
                    <div class="text-center">
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            stroke="currentColor"
                            fill="none"
                            viewBox="0 0 48 48"
                        >
                            <path
                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        <div class="mt-4">
                            <label for="file-upload" class="cursor-pointer">
                                <span
                                    class="mt-2 block text-sm font-medium text-gray-900"
                                >
                                    {{
                                        isDragOver
                                            ? "Drop files here"
                                            : "Choose files to upload"
                                    }}
                                </span>
                                <span class="mt-1 block text-sm text-gray-500">
                                    or drag and drop files here
                                </span>
                                <input
                                    id="file-upload"
                                    name="file-upload"
                                    type="file"
                                    class="sr-only"
                                    multiple
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                                    @change="handleFileSelect"
                                />
                            </label>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            PDF, DOC, DOCX, JPG, PNG up to 10MB each
                        </p>
                    </div>
                </div>
            </div>

            <!-- Upload Progress -->
            <div v-if="uploadingFiles.length > 0" class="space-y-3">
                <h4 class="text-sm font-medium text-gray-900">
                    Uploading Files
                </h4>
                <div
                    v-for="file in uploadingFiles"
                    :key="file.id"
                    class="bg-gray-50 rounded-lg p-4"
                >
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg
                                    class="w-5 h-5 text-gray-400"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ file.name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ formatFileSize(file.size) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div
                                v-if="file.status === 'uploading'"
                                class="flex items-center"
                            >
                                <div
                                    class="w-16 bg-gray-200 rounded-full h-2 mr-3"
                                >
                                    <div
                                        class="bg-blue-600 h-2 rounded-full"
                                        :style="{ width: file.progress + '%' }"
                                    ></div>
                                </div>
                                <span class="text-xs text-gray-500"
                                    >{{ file.progress }}%</span
                                >
                            </div>
                            <div
                                v-else-if="file.status === 'success'"
                                class="flex items-center text-green-600"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span class="text-xs">Uploaded</span>
                            </div>
                            <div
                                v-else-if="file.status === 'error'"
                                class="flex items-center text-red-600"
                            >
                                <svg
                                    class="w-5 h-5 mr-2"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span class="text-xs">Error</span>
                            </div>
                        </div>
                    </div>
                    <p v-if="file.error" class="text-xs text-red-600 mt-1">
                        {{ file.error }}
                    </p>
                </div>
            </div>

            <!-- Document Categories -->
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-3">
                    Required Documents
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="category in documentCategories"
                        :key="category.id"
                        :class="getCategoryStatusClass(category.status)"
                        class="border rounded-lg p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div
                                    :class="
                                        getCategoryIconClass(category.status)
                                    "
                                    class="w-8 h-8 rounded-full flex items-center justify-center mr-3"
                                >
                                    <svg
                                        class="w-4 h-4 text-white"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            v-if="
                                                category.status === 'completed'
                                            "
                                            fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                        <path
                                            v-else-if="
                                                category.status === 'required'
                                            "
                                            fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd"
                                        />
                                        <path
                                            v-else
                                            fill-rule="evenodd"
                                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <h5
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ category.name }}
                                    </h5>
                                    <p class="text-xs text-gray-500">
                                        {{ category.description }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span
                                    :class="
                                        getCategoryStatusBadgeClass(
                                            category.status
                                        )
                                    "
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                >
                                    {{
                                        category.status === "completed"
                                            ? "Complete"
                                            : category.status === "required"
                                            ? "Required"
                                            : "Optional"
                                    }}
                                </span>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ category.uploadedCount }}/{{
                                        category.requiredCount
                                    }}
                                    files
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Uploaded Documents -->
            <div v-if="uploadedDocuments.length > 0">
                <h4 class="text-sm font-medium text-gray-900 mb-3">
                    Uploaded Documents
                </h4>
                <div class="space-y-2">
                    <div
                        v-for="document in uploadedDocuments"
                        :key="document.id"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg
                                    class="w-5 h-5 text-gray-400"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ document.name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ formatFileSize(document.size) }} •
                                    {{ formatDate(document.uploaded_at) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span
                                :class="getDocumentStatusClass(document.status)"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            >
                                {{ document.status }}
                            </span>
                            <button
                                @click="downloadDocument(document.id)"
                                class="text-blue-600 hover:text-blue-800"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                            </button>
                            <button
                                @click="deleteDocument(document.id)"
                                class="text-red-600 hover:text-red-800"
                            >
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Tips -->
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg
                            class="h-5 w-5 text-blue-400"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-blue-800">
                            Document Tips
                        </h4>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>
                                    Ensure all documents are clear and legible
                                </li>
                                <li>
                                    PDF format is preferred for official
                                    documents
                                </li>
                                <li>
                                    Keep file sizes under 10MB for faster
                                    uploads
                                </li>
                                <li>
                                    Upload documents as soon as they become
                                    available
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    transactionId: {
        type: [String, Number],
        required: true,
    },
    uploadedDocuments: {
        type: Array,
        default: () => [],
    },
    documentCategories: {
        type: Array,
        default: () => [
            {
                id: 1,
                name: "Identity Documents",
                description: "Valid ID, passport, or driver's license",
                status: "required",
                uploadedCount: 0,
                requiredCount: 1,
            },
            {
                id: 2,
                name: "Financial Documents",
                description: "Bank statements, income proof, tax returns",
                status: "required",
                uploadedCount: 0,
                requiredCount: 3,
            },
            {
                id: 3,
                name: "Property Documents",
                description: "Title, tax declaration, survey plans",
                status: "required",
                uploadedCount: 0,
                requiredCount: 2,
            },
            {
                id: 4,
                name: "Additional Documents",
                description: "Any other relevant documents",
                status: "optional",
                uploadedCount: 0,
                requiredCount: 0,
            },
        ],
    },
});

const emit = defineEmits(["documents-uploaded"]);

const isDragOver = ref(false);
const uploadingFiles = ref([]);

const handleDrop = (event) => {
    event.preventDefault();
    isDragOver.value = false;

    const files = Array.from(event.dataTransfer.files);
    handleFiles(files);
};

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    handleFiles(files);
};

const handleFiles = async (files) => {
    for (const file of files) {
        if (validateFile(file)) {
            await uploadFile(file);
        }
    }
};

const validateFile = (file) => {
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = [
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "image/jpeg",
        "image/png",
        "image/gif",
    ];

    if (file.size > maxSize) {
        alert(`File ${file.name} is too large. Maximum size is 10MB.`);
        return false;
    }

    if (!allowedTypes.includes(file.type)) {
        alert(`File ${file.name} is not a supported format.`);
        return false;
    }

    return true;
};

const uploadFile = async (file) => {
    const fileId = Date.now() + Math.random();
    const uploadFile = {
        id: fileId,
        name: file.name,
        size: file.size,
        progress: 0,
        status: "uploading",
        error: null,
    };

    uploadingFiles.value.push(uploadFile);

    try {
        const formData = new FormData();
        formData.append("file", file);
        formData.append("transaction_id", props.transactionId);

        // Simulate upload progress
        const progressInterval = setInterval(() => {
            uploadFile.progress += Math.random() * 20;
            if (uploadFile.progress >= 100) {
                uploadFile.progress = 100;
                clearInterval(progressInterval);
            }
        }, 200);

        // In a real implementation, this would make an actual API call
        await new Promise((resolve) => setTimeout(resolve, 2000)); // Simulate upload

        uploadFile.status = "success";
        uploadFile.progress = 100;

        emit("documents-uploaded", {
            file,
            transactionId: props.transactionId,
        });

        // Remove from uploading list after a delay
        setTimeout(() => {
            const index = uploadingFiles.value.findIndex(
                (f) => f.id === fileId
            );
            if (index > -1) {
                uploadingFiles.value.splice(index, 1);
            }
        }, 2000);
    } catch (error) {
        uploadFile.status = "error";
        uploadFile.error = error.message || "Upload failed";
    }
};

const downloadDocument = (documentId) => {
    // In a real implementation, this would trigger a download
    console.log("Download document:", documentId);
};

const deleteDocument = async (documentId) => {
    if (confirm("Are you sure you want to delete this document?")) {
        try {
            // In a real implementation, this would make an API call
            await router.delete(
                route("client.transactions.documents.destroy", documentId)
            );
        } catch (error) {
            console.error("Error deleting document:", error);
        }
    }
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getCategoryStatusClass = (status) => {
    const classes = {
        completed: "border-green-200 bg-green-50",
        required: "border-yellow-200 bg-yellow-50",
        optional: "border-gray-200 bg-gray-50",
    };
    return classes[status] || "border-gray-200 bg-gray-50";
};

const getCategoryIconClass = (status) => {
    const classes = {
        completed: "bg-green-500",
        required: "bg-yellow-500",
        optional: "bg-gray-500",
    };
    return classes[status] || "bg-gray-500";
};

const getCategoryStatusBadgeClass = (status) => {
    const classes = {
        completed: "bg-green-100 text-green-800",
        required: "bg-yellow-100 text-yellow-800",
        optional: "bg-gray-100 text-gray-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getDocumentStatusClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        approved: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
        under_review: "bg-blue-100 text-blue-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};
</script>
