<template>
    <div>
        <FileUpload
            v-model="file"
            label="Upload 360° Photo (JPG only)"
            accept=".jpg,.jpeg"
            :maxSize="10 * 1024 * 1024"
            :required="false"
        />

        <div v-if="imageUrl" class="mt-6">
            <VirtualTourViewer360 :imageUrl="imageUrl" />
        </div>
    </div>
</template>

<script>
import { ref, watch } from "vue";
import FileUpload from "./FileUpload.vue";
import VirtualTourViewer360 from "./VirtualTourViewer360.vue";

export default {
    name: "Property360Uploader",
    components: {
        FileUpload,
        VirtualTourViewer360,
    },
    setup() {
        const file = ref(null);
        const imageUrl = ref("");

        watch(file, (newFile) => {
            if (newFile && newFile instanceof File) {
                imageUrl.value = URL.createObjectURL(newFile);
            } else {
                imageUrl.value = "";
            }
        });

        return { file, imageUrl };
    },
};
</script>

<style scoped>
.mt-6 {
    margin-top: 1.5rem;
}
</style>
