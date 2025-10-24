<script setup>
import { ref } from "vue";
import Modal from "./Modal.vue";
import { XMarkIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close"]);

const close = () => {
    emit("close");
};
</script>

<template>
    <Modal :show="show" max-width="4xl" @close="close">
        <div class="bg-white">
            <!-- Header -->
            <div
                class="flex items-center justify-between border-b border-gray-200 px-6 py-4"
            >
                <h2 class="text-xl font-semibold text-gray-900">
                    Terms of Service
                </h2>
                <button
                    @click="close"
                    class="rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    aria-label="Close modal"
                >
                    <XMarkIcon class="h-5 w-5" />
                </button>
            </div>

            <!-- Content -->
            <div class="max-h-96 overflow-y-auto px-6 py-4">
                <div class="prose prose-sm max-w-none">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        1. Acceptance of Terms
                    </h3>
                    <p class="text-gray-700 mb-4">
                        By accessing and using GeoCasa Bohol, you accept and
                        agree to be bound by the terms and provision of this
                        agreement.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        2. Use License
                    </h3>
                    <p class="text-gray-700 mb-4">
                        Permission is granted to temporarily download one copy
                        of the materials on GeoCasa Bohol's website for
                        personal, non-commercial transitory viewing only.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        3. Disclaimer
                    </h3>
                    <p class="text-gray-700 mb-4">
                        The materials on GeoCasa Bohol's website are provided on
                        an 'as is' basis. GeoCasa Bohol makes no warranties,
                        expressed or implied, and hereby disclaims and negates
                        all other warranties including without limitation,
                        implied warranties or conditions of merchantability,
                        fitness for a particular purpose, or non-infringement of
                        intellectual property or other violation of rights.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        4. Limitations
                    </h3>
                    <p class="text-gray-700 mb-4">
                        In no event shall GeoCasa Bohol or its suppliers be
                        liable for any damages (including, without limitation,
                        damages for loss of data or profit, or due to business
                        interruption) arising out of the use or inability to use
                        the materials on GeoCasa Bohol's website, even if
                        GeoCasa Bohol or a GeoCasa Bohol authorized
                        representative has been notified orally or in writing of
                        the possibility of such damage.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        5. Privacy Policy
                    </h3>
                    <p class="text-gray-700 mb-4">
                        Your privacy is important to us. Please review our
                        Privacy Policy, which also governs your use of the
                        website, to understand our practices.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        6. Governing Law
                    </h3>
                    <p class="text-gray-700 mb-4">
                        These terms and conditions are governed by and construed
                        in accordance with the laws of the Philippines and you
                        irrevocably submit to the exclusive jurisdiction of the
                        courts in that State or location.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        7. Contact Information
                    </h3>
                    <p class="text-gray-700 mb-4">
                        If you have any questions about these Terms of Service,
                        please contact us at legal@geocasabohol.com.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-200 px-6 py-4">
                <div class="flex justify-end">
                    <button
                        @click="close"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </Modal>
</template>
