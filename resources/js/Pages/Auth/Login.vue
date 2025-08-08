<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import ModernInput from "@/Components/ModernInput.vue";
import ModernButton from "@/Components/ModernButton.vue";
import {
    EnvelopeIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
    MapPinIcon,
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

    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- GeoCasa Bohol Logo -->
            <div class="text-center">
                <div class="flex items-center justify-center gap-4 mb-6">
                    <div class="relative">
                        <div class="w-12 h-12 bg-teal-500 rounded-2xl flex items-center justify-center shadow-lg">
                            <MapPinIcon class="w-7 h-7 text-white" />
                        </div>
                        <!-- Small orange dot -->
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-orange-400 rounded-full border-2 border-white"></div>
                    </div>
                    <div class="text-left">
                        <div class="text-3xl font-bold">
                            <span class="text-teal-600">Geo</span><span class="text-green-500">Casa</span>
                        </div>
                        <div class="text-sm text-gray-400 font-medium -mt-1">Bohol</div>
                    </div>
                </div>
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
            <div
                v-if="status"
                class="p-4 bg-blue-50 border border-blue-200 rounded-lg"
            >
                <p class="text-sm text-blue-700 text-center">
                    {{ status }}
                </p>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Email -->
                <div>
                    <ModernInput
                        v-model="form.email"
                        :icon="EnvelopeIcon"
                        type="email"
                        label="Email Address"
                        placeholder="Enter your email"
                        :error="form.errors.email"
                        required
                    />
                </div>

                <!-- Password -->
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
                        class="absolute right-4 top-11 text-gray-400 hover:text-gray-600 transition-colors duration-200"
                    >
                        <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                        <EyeSlashIcon v-else class="w-5 h-5" />
                    </button>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4 h-4 text-teal-600 bg-gray-50 border-gray-300 rounded focus:ring-teal-500 focus:ring-2"
                        />
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-teal-600 hover:text-teal-700 font-medium hover:underline"
                    >
                        Forgot password?
                    </Link>
                </div>

                <!-- Submit Button -->
                <div>
                    <ModernButton
                        type="submit"
                        variant="primary"
                        size="lg"
                        :loading="form.processing"
                        class="w-full"
                    >
                        Sign In
                    </ModernButton>
                </div>
            </form>

            <!-- Register Link -->
            <div class="text-center">
                <p class="text-gray-600">
                    Don't have an account?
                    <Link
                        :href="route('register')"
                        class="text-teal-600 hover:text-teal-700 font-semibold hover:underline"
                    >
                        Create one here
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

<style scoped>
@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}
</style>
