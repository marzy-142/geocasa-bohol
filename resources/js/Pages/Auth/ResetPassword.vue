<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ModernInput from '@/Components/ModernInput.vue';
import ModernButton from '@/Components/ModernButton.vue';
import { MapPinIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Password - GeoCasa Bohol" />
    
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- GeoCasa Bohol Logo -->
            <div class="text-center">
                <div class="flex items-center justify-center gap-3 mb-6">
                    <div class="relative">
                        <div class="w-12 h-12 bg-teal-500 rounded-2xl flex items-center justify-center">
                            <MapPinIcon class="w-7 h-7 text-white" />
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-orange-400 rounded-full"></div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">
                            <span class="text-teal-600">Geo</span><span class="text-green-600">Casa</span>
                        </div>
                        <div class="text-sm text-gray-500 -mt-1">Bohol</div>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Reset Password</h2>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Create a new secure password for your GeoCasa Bohol account.
                </p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <ModernInput
                        id="email"
                        type="email"
                        label="Email Address"
                        v-model="form.email"
                        :error="form.errors.email"
                        required
                        autofocus
                        autocomplete="username"
                        icon="mail"
                        readonly
                    />

                    <ModernInput
                        id="password"
                        type="password"
                        label="New Password"
                        placeholder="Enter your new password"
                        v-model="form.password"
                        :error="form.errors.password"
                        required
                        autocomplete="new-password"
                        icon="lock"
                        helper="Password must be at least 8 characters long"
                    />

                    <ModernInput
                        id="password_confirmation"
                        type="password"
                        label="Confirm New Password"
                        placeholder="Confirm your new password"
                        v-model="form.password_confirmation"
                        :error="form.errors.password_confirmation"
                        required
                        autocomplete="new-password"
                        icon="lock"
                    />

                    <ModernButton
                        type="submit"
                        variant="primary"
                        size="lg"
                        class="w-full bg-teal-600 hover:bg-teal-700 focus:ring-teal-500"
                        :loading="form.processing"
                        :disabled="form.processing"
                    >
                        Reset Password
                    </ModernButton>
                </form>

                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <p class="text-gray-600 text-sm">
                        Remember your password?
                        <a :href="route('login')" class="text-teal-600 hover:text-teal-700 font-medium transition-colors duration-200">
                            Back to Login
                        </a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center">
                <p class="text-gray-500 text-xs">
                    © 2024 GeoCasa Bohol. Your trusted real estate partner in Bohol.
                </p>
            </div>
        </div>
    </div>
</template>
