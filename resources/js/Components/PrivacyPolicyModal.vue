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
                    Privacy Policy
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
                        1. Information We Collect
                    </h3>
                    <p class="text-gray-700 mb-4">
                        We collect information you provide directly to us, such
                        as when you create an account, make a purchase, or
                        contact us for support. This may include your name,
                        email address, phone number, and other contact
                        information.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        2. How We Use Your Information
                    </h3>
                    <p class="text-gray-700 mb-4">
                        We use the information we collect to provide, maintain,
                        and improve our services, process transactions, send you
                        technical notices and support messages, and communicate
                        with you about products, services, and events.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        3. Information Sharing
                    </h3>
                    <p class="text-gray-700 mb-4">
                        We do not sell, trade, or otherwise transfer your
                        personal information to third parties without your
                        consent, except as described in this policy or as
                        required by law.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        4. Data Security
                    </h3>
                    <p class="text-gray-700 mb-4">
                        We implement appropriate security measures to protect
                        your personal information against unauthorized access,
                        alteration, disclosure, or destruction.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        5. Cookies and Tracking
                    </h3>
                    <p class="text-gray-700 mb-4">
                        We use cookies and similar tracking technologies to
                        enhance your experience on our website, analyze usage
                        patterns, and deliver personalized content.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        6. Your Rights
                    </h3>
                    <p class="text-gray-700 mb-4">
                        You have the right to access, update, or delete your
                        personal information. You may also opt out of certain
                        communications from us.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        7. Changes to This Policy
                    </h3>
                    <p class="text-gray-700 mb-4">
                        We may update this Privacy Policy from time to time. We
                        will notify you of any changes by posting the new
                        Privacy Policy on this page.
                    </p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        8. Contact Us
                    </h3>
                    <p class="text-gray-700 mb-4">
                        If you have any questions about this Privacy Policy,
                        please contact us at privacy@geocasabohol.com.
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
