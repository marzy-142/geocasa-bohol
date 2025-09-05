<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import ModernInput from "@/Components/ModernInput.vue";
import ModernButton from "@/Components/ModernButton.vue";
import GeoCasaLogo from "@/Components/GeoCasaLogo.vue";
import {
    EnvelopeIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
} from "@heroicons/vue/24/outline";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Login - GeoCasa Bohol" />

    <div
        class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8"
    >
        <div class="max-w-md w-full space-y-8">
            <!-- Standardized GeoCasa Bohol Logo -->
            <div class="flex justify-center items-center mb-8">
                <GeoCasaLogo
                    size="medium"
                    variant="default"
                    :show-text="true"
                    layout="horizontal"
                    class="mx-auto"
                />
            </div>

            <!-- Header -->
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                    Sign in to your account
                </h2>
                <p class="text-gray-600">
                    Welcome back! Please enter your details.
                </p>
            </div>

            <!-- Status Message -->
            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <ModernInput
                    v-model="form.email"
                    :icon="EnvelopeIcon"
                    type="email"
                    label="Email Address"
                    placeholder="Enter your email"
                    :error="form.errors.email"
                    required
                />

                <div class="relative">
                    <ModernInput
                        v-model="form.password"
                        :icon="LockClosedIcon"
                        :type="showPassword ? 'text' : 'password'"
                        label="Password"
                        placeholder="Enter your password"
                        :error="form.errors.password"
                        required
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-3 top-9 text-gray-400 hover:text-gray-600"
                    >
                        <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                        <EyeSlashIcon v-else class="w-5 h-5" />
                    </button>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="rounded border-gray-300 text-teal-600 shadow-sm focus:ring-teal-500"
                        />
                        <span class="ml-2 text-sm text-gray-600"
                            >Remember me</span
                        >
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-teal-600 hover:text-teal-700 hover:underline"
                    >
                        Forgot password?
                    </Link>
                </div>

                <ModernButton
                    type="submit"
                    variant="primary"
                    size="lg"
                    :loading="form.processing"
                    class="w-full"
                >
                    Sign In
                </ModernButton>
            </form>

            <!-- Register Link -->
            <div class="text-center">
                <p class="text-gray-600">
                    Don't have an account?
                    <Link
                        :href="route('register')"
                        class="text-teal-600 hover:text-teal-700 font-semibold hover:underline"
                    >
                        Sign up here
                    </Link>
                </p>
            </div>

            <!-- Back to Home -->
            <div class="text-center">
                <Link
                    :href="route('home')"
                    class="text-sm text-gray-500 hover:text-gray-700 hover:underline inline-flex items-center gap-1"
                >
                    ← Back to Home
                </Link>
            </div>
        </div>
    </div>
</template>
