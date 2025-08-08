<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModernButton from '@/Components/ModernButton.vue';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <Head title="Email Verification - GeoCasa Bohol" />
    
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-accent-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-accent-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-neutral-900 mb-2">Verify Your Email</h2>
                <p class="text-neutral-600 text-sm leading-relaxed">
                    Thanks for signing up! Please verify your email address to get started with GeoCasa Bohol.
                </p>
            </div>

            <!-- Status Message -->
            <div v-if="verificationLinkSent" class="bg-green-50 border border-green-200 rounded-xl p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-green-700 text-sm font-medium">Verification Link Sent!</p>
                        <p class="text-green-600 text-xs mt-1">
                            A new verification link has been sent to your email address.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8 border border-neutral-100">
                <!-- Email Instructions -->
                <div class="mb-6 text-center">
                    <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <p class="text-neutral-600 text-sm">
                        We've sent a verification link to your email. Click the link to activate your account.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <ModernButton
                        type="submit"
                        variant="primary"
                        size="lg"
                        class="w-full"
                        :loading="form.processing"
                        :disabled="form.processing"
                    >
                        Resend Verification Email
                    </ModernButton>
                </form>

                <!-- Actions -->
                <div class="mt-6 flex items-center justify-between">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-neutral-600 hover:text-neutral-700 text-sm font-medium transition-colors duration-200"
                    >
                        Use Different Account
                    </Link>
                    
                    <a href="mailto:" class="text-primary-600 hover:text-primary-700 text-sm font-medium transition-colors duration-200">
                        Need Help?
                    </a>
                </div>
            </div>

            <!-- Help Section -->
            <div class="bg-neutral-50 rounded-xl p-6 border border-neutral-200">
                <h3 class="text-neutral-900 font-medium text-sm mb-2">Didn't receive the email?</h3>
                <ul class="text-neutral-600 text-xs space-y-1">
                    <li>• Check your spam or junk folder</li>
                    <li>• Make sure you entered the correct email address</li>
                    <li>• Try resending the verification email</li>
                </ul>
            </div>

            <!-- Footer -->
            <div class="text-center">
                <p class="text-neutral-500 text-xs">
                    © 2024 GeoCasa Bohol. Your trusted real estate partner in Bohol.
                </p>
            </div>
        </div>
    </div>
</template>
