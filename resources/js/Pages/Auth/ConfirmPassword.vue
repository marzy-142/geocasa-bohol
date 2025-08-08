<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ModernInput from '@/Components/ModernInput.vue';
import ModernButton from '@/Components/ModernButton.vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Confirm Password - GeoCasa Bohol" />
    
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-white to-accent-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-accent-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-neutral-900 mb-2">Confirm Password</h2>
                <p class="text-neutral-600 text-sm leading-relaxed">
                    This is a secure area. Please confirm your password before continuing.
                </p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8 border border-neutral-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <ModernInput
                        id="password"
                        type="password"
                        label="Current Password"
                        placeholder="Enter your current password"
                        v-model="form.password"
                        :error="form.errors.password"
                        required
                        autofocus
                        autocomplete="current-password"
                        icon="lock"
                    />

                    <ModernButton
                        type="submit"
                        variant="primary"
                        size="lg"
                        class="w-full"
                        :loading="form.processing"
                        :disabled="form.processing"
                    >
                        Confirm
                    </ModernButton>
                </form>

                <!-- Security Notice -->
                <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-amber-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <p class="text-amber-700 text-sm font-medium">Security Check</p>
                            <p class="text-amber-600 text-xs mt-1">
                                We need to verify your identity to protect your account and data.
                            </p>
                        </div>
                    </div>
                </div>
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
