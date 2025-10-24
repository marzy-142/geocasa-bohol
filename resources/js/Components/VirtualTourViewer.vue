<template>
    <div class="virtual-tour-wrapper">
        <VirtualTourViewer360
            v-if="currentImage"
            :key="currentImage"
            :imageUrl="currentImage"
        />

        <div
            v-if="tourImages.length > 1"
            class="mt-4 flex gap-2 overflow-x-auto p-2"
        >
            <button
                v-for="(image, index) in tourImages"
                :key="index"
                @click="currentImageIndex = index"
                :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                    currentImageIndex === index
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                ]"
            >
                View {{ index + 1 }}
            </button>
        </div>
    </div>
</template>

<script>
import VirtualTourViewer360 from "./VirtualTourViewer360.vue";

export default {
    name: "VirtualTourViewer",
    components: {
        VirtualTourViewer360,
    },
    props: {
        tourImages: {
            type: Array,
            default: () => [],
        },
        hotspots: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            currentImageIndex: 0,
        };
    },
    computed: {
        currentImage() {
            const url = this.tourImages[this.currentImageIndex]?.url || "";
            try { console.debug('360 currentImage selected:', url); } catch (_) {}
            return url;
        },
    },
};
</script>

<style scoped>
.virtual-tour-wrapper {
    width: 100%;
    min-height: 400px;
}
</style>
