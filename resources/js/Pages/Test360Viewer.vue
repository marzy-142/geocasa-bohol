<template>
    <div class="min-h-screen bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">
                    360° Photo Upload Test
                </h1>

                <Property360Uploader />

                <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                    <h2 class="text-lg font-semibold text-blue-900 mb-2">
                        Instructions:
                    </h2>
                    <ul class="list-disc list-inside text-blue-800 space-y-1">
                        <li>
                            Upload a 360-degree panoramic photo (JPG format)
                        </li>
                        <li>The viewer will instantly display your photo</li>
                        <li>Click and drag to navigate the 360-degree view</li>
                        <li>Use zoom controls and fullscreen button</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Property360Uploader from "@/Components/Property360Uploader.vue";

export default {
    name: "Test360Viewer",
    components: {
        Property360Uploader,
    },
};
</script>
