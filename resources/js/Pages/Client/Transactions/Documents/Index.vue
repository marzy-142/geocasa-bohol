<template>
    <ModernDashboardLayout>
        <Head title="Transaction Documents" />
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-4">
                            <li>
                                <Link
                                    :href="
                                        route('client.transactions.dashboard')
                                    "
                                    class="text-gray-400 hover:text-gray-500"
                                >
                                    <svg
                                        class="flex-shrink-0 h-5 w-5"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"
                                        />
                                    </svg>
                                    <span class="sr-only">Home</span>
                                </Link>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg
                                        class="flex-shrink-0 h-5 w-5 text-gray-300"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <Link
                                        :href="
                                            route(
                                                'client.transactions.show',
                                                transaction.id
                                            )
                                        "
                                        class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700"
                                    >
                                        Transaction #{{
                                            transaction.transaction_number
                                        }}
                                    </Link>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg
                                        class="flex-shrink-0 h-5 w-5 text-gray-300"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                    <span
                                        class="ml-4 text-sm font-medium text-gray-500"
                                        >Documents</span
                                    >
                                </div>
                            </li>
                        </ol>
                    </nav>

                    <div class="mt-4">
                        <h1 class="text-3xl font-bold text-gray-900">
                            Document Management
                        </h1>
                        <p class="mt-2 text-gray-600">
                            Upload and manage documents for
                            {{ transaction.property.title }}
                        </p>
                    </div>
                </div>

                <!-- Document Upload Form -->
                <div class="mb-8 bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Upload Documents
                        </h3>
                    </div>

                    <form
                        @submit.prevent="uploadDocuments"
                        class="px-6 py-4 space-y-6"
                    >
                        <!-- File Upload Area -->
                        <div
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors"
                        >
                            <input
                                ref="fileInput"
                                type="file"
                                multiple
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
                                @change="handleFileSelect"
                                class="hidden"
                            />

                            <div
                                v-if="selectedFiles.length === 0"
                                @click="$refs.fileInput.click()"
                                class="cursor-pointer"
                            >
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
                                    <label class="cursor-pointer">
                                        <span
                                            class="mt-2 block text-sm font-medium text-gray-900"
                                        >
                                            Drop files here or click to upload
                                        </span>
                                        <span
                                            class="mt-1 block text-sm text-gray-500"
                                        >
                                            PDF, DOC, DOCX, XLS, XLSX, JPG, PNG
                                            up to 10MB each
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="(file, index) in selectedFiles"
                                    :key="index"
                                    class="flex items-center justify-between bg-gray-50 rounded-lg p-3"
                                >
                                    <div class="flex items-center">
                                        <svg
                                            class="h-8 w-8 text-blue-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                        <div class="ml-3">
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ file.name }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ formatFileSize(file.size) }}
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeFile(index)"
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    @click="$refs.fileInput.click()"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    Add more files
                                </button>
                            </div>
                        </div>

                        <!-- Document Category -->
                        <div>
                            <label
                                for="category"
                                class="block text-sm font-medium text-gray-700"
                                >Document Category</label
                            >
                            <select
                                id="category"
                                v-model="uploadForm.category"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Select a category</option>
                                <option
                                    v-for="(
                                        category, key
                                    ) in documentCategories"
                                    :key="key"
                                    :value="key"
                                >
                                    {{ category.name }}
                                    <span
                                        v-if="category.required"
                                        class="text-red-500"
                                        >*</span
                                    >
                                </option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label
                                for="description"
                                class="block text-sm font-medium text-gray-700"
                                >Description (Optional)</label
                            >
                            <textarea
                                id="description"
                                v-model="uploadForm.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Add a description for these documents..."
                            ></textarea>
                        </div>

                        <!-- Upload Button -->
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="
                                    selectedFiles.length === 0 ||
                                    !uploadForm.category ||
                                    uploading
                                "
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg
                                    v-if="uploading"
                                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
                                {{
                                    uploading
                                        ? "Uploading..."
                                        : `Upload ${selectedFiles.length} File${
                                              selectedFiles.length !== 1
                                                  ? "s"
                                                  : ""
                                          }`
                                }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Document Categories -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div
                        v-for="(category, key) in documentCategories"
                        :key="key"
                        class="bg-white shadow rounded-lg"
                    >
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ category.name }}
                                    <span
                                        v-if="category.required"
                                        class="text-red-500 ml-1"
                                        >*</span
                                    >
                                </h3>
                                <span
                                    v-if="category.uploaded"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                >
                                    Uploaded
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ category.description }}
                            </p>
                        </div>

                        <div class="px-6 py-4">
                            <div
                                v-if="
                                    documents.by_category[key] &&
                                    documents.by_category[key].length > 0
                                "
                                class="space-y-3"
                            >
                                <div
                                    v-for="document in documents.by_category[
                                        key
                                    ]"
                                    :key="document.id"
                                    class="flex items-center justify-between bg-gray-50 rounded-lg p-3"
                                >
                                    <div class="flex items-center">
                                        <svg
                                            class="h-8 w-8 text-blue-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                        <div class="ml-3">
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ document.filename }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{
                                                    formatFileSize(
                                                        document.file_size
                                                    )
                                                }}
                                                •
                                                {{
                                                    formatDate(
                                                        document.uploaded_at
                                                    )
                                                }}
                                            </p>
                                            <p
                                                v-if="document.description"
                                                class="text-sm text-gray-600 mt-1"
                                            >
                                                {{ document.description }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button
                                            @click="
                                                downloadDocument(document.id)
                                            "
                                            class="text-blue-600 hover:text-blue-800"
                                        >
                                            <svg
                                                class="h-5 w-5"
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
                                                class="h-5 w-5"
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

                            <div v-else class="text-center py-8">
                                <svg
                                    class="mx-auto h-12 w-12 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                                <h3
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    No documents
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Upload documents for this category.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Tips -->
                <div class="mt-8 bg-blue-50 rounded-lg p-6">
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
                            <h3 class="text-sm font-medium text-blue-800">
                                Document Upload Tips
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>
                                        Ensure all documents are clear and
                                        legible
                                    </li>
                                    <li>
                                        Upload documents in PDF format when
                                        possible
                                    </li>
                                    <li>
                                        Keep file sizes under 10MB for faster
                                        uploads
                                    </li>
                                    <li>
                                        Required documents must be uploaded
                                        before transaction completion
                                    </li>
                                    <li>
                                        Contact your broker if you have
                                        questions about required documents
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, reactive } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";

const props = defineProps({
    transaction: Object,
    documents: Object,
    documentCategories: Object,
});

const selectedFiles = ref([]);
const uploading = ref(false);

const uploadForm = reactive({
    category: "",
    description: "",
});

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    selectedFiles.value = [...selectedFiles.value, ...files];
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
};

const uploadDocuments = async () => {
    if (selectedFiles.value.length === 0 || !uploadForm.category) return;

    uploading.value = true;

    try {
        const formData = new FormData();
        selectedFiles.value.forEach((file) => {
            formData.append("files[]", file);
        });
        formData.append("category", uploadForm.category);
        if (uploadForm.description) {
            formData.append("description", uploadForm.description);
        }

        await router.post(
            route("client.transactions.documents.store", props.transaction.id),
            formData,
            {
                onSuccess: () => {
                    selectedFiles.value = [];
                    uploadForm.category = "";
                    uploadForm.description = "";
                    document.querySelector('input[type="file"]').value = "";
                },
            }
        );
    } catch (error) {
        console.error("Upload error:", error);
    } finally {
        uploading.value = false;
    }
};

const downloadDocument = (documentId) => {
    window.open(
        route("client.transactions.documents.download", [
            props.transaction.id,
            documentId,
        ]),
        "_blank"
    );
};

const deleteDocument = async (documentId) => {
    if (confirm("Are you sure you want to delete this document?")) {
        try {
            await router.delete(
                route("client.transactions.documents.destroy", [
                    props.transaction.id,
                    documentId,
                ])
            );
        } catch (error) {
            console.error("Delete error:", error);
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

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>
