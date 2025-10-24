<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import ModernInput from "@/Components/ModernInput.vue";
import ModernButton from "@/Components/ModernButton.vue";
import FileUpload from "@/Components/FileUpload.vue";
import GeoCasaLogo from "@/Components/GeoCasaLogo.vue";
import {
    UserIcon,
    EnvelopeIcon,
    LockClosedIcon,
    EyeIcon,
    EyeSlashIcon,
    UserGroupIcon,
    BriefcaseIcon,
    ArrowRightIcon,
    ArrowLeftIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
    mode: {
        type: String,
        default: "login", // 'login' or 'register'
    },
});

const currentMode = ref(props.mode);

const loginForm = useForm({
    email: "",
    password: "",
    remember: false,
});

const registerForm = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "buyer",
    phone: "",
    prc_id: "",
    prc_id_file: null,
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const isLogin = computed(() => currentMode.value === "login");
const isRegister = computed(() => currentMode.value === "register");

const switchMode = (mode) => {
    currentMode.value = mode;
};

const submitLogin = () => {
    loginForm.post(route("login"), {
        onFinish: () => loginForm.reset("password"),
    });
};

const submitRegister = () => {
    registerForm.post(route("register"), {
        onFinish: () => registerForm.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head :title="`${isLogin ? 'Login' : 'Register'} - GeoCasa Bohol`" />

    <div class="min-h-screen flex bg-gray-50">
        <!-- Left Side - Branding -->
        <div
            class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-slate-800 via-slate-700 to-slate-600 relative overflow-hidden"
        >
            <!-- Subtle background elements -->
            <div class="absolute inset-0 bg-black/20"></div>
            <div
                class="absolute top-20 left-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"
            ></div>
            <div
                class="absolute bottom-20 right-20 w-96 h-96 bg-white/3 rounded-full blur-3xl"
            ></div>

            <div
                class="relative flex flex-col justify-center px-12 text-white z-10"
            >
                <!-- Standardized Logo -->
                <div class="mb-12">
                    <GeoCasaLogo
                        size="large"
                        variant="light"
                        :show-text="true"
                        layout="horizontal"
                    />
                </div>
                <!-- Simple Logo -->
                <div class="flex items-center gap-4 mb-12">
                    <div class="relative">
                        <div
                            class="w-14 h-14 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center"
                        >
                            <MapPinIcon class="w-7 h-7 text-white" />
                        </div>
                        <div
                            class="absolute -bottom-1 -right-1 w-5 h-5 bg-amber-400 rounded-full border-2 border-white flex items-center justify-center"
                        >
                            <SunIcon class="w-2.5 h-2.5 text-amber-800" />
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-white">
                            GeoCasa <span class="text-amber-200">Bohol</span>
                        </div>
                    </div>
                </div>

                <h1 class="text-3xl font-bold mb-4 leading-tight">
                    {{ isLogin ? "Welcome Back" : "Join Our Community" }}
                </h1>
                <p class="text-lg text-white/80 mb-8 leading-relaxed">
                    {{
                        isLogin
                            ? "Continue your real estate journey in Bohol's premier marketplace."
                            : "Start your real estate journey in Bohol's beautiful locations."
                    }}
                </p>

                <!-- Simple Features -->
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 bg-amber-400 rounded-full"></div>
                        <span class="text-white/80 text-sm"
                            >Premium property listings</span
                        >
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 bg-amber-400 rounded-full"></div>
                        <span class="text-white/80 text-sm"
                            >Licensed broker network</span
                        >
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 bg-amber-400 rounded-full"></div>
                        <span class="text-white/80 text-sm"
                            >Advanced search tools</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Auth Forms -->
        <div
            class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-16 bg-white"
        >
            <div class="mx-auto w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden mb-8 flex justify-center">
                    <GeoCasaLogo
                        size="medium"
                        variant="default"
                        :show-text="true"
                        layout="horizontal"
                    />
                </div>
                <!-- Mode Toggle -->
                <div class="flex bg-gray-100 rounded-xl p-1 mb-8">
                    <button
                        @click="switchMode('login')"
                        :class="[
                            'flex-1 py-2.5 px-4 text-sm font-medium rounded-lg transition-all duration-300',
                            isLogin
                                ? 'bg-white text-slate-800 shadow-sm'
                                : 'text-slate-600 hover:text-slate-800',
                        ]"
                    >
                        Sign In
                    </button>
                    <button
                        @click="switchMode('register')"
                        :class="[
                            'flex-1 py-2.5 px-4 text-sm font-medium rounded-lg transition-all duration-300',
                            isRegister
                                ? 'bg-white text-slate-800 shadow-sm'
                                : 'text-slate-600 hover:text-slate-800',
                        ]"
                    >
                        Sign Up
                    </button>
                </div>

                <!-- Form Container with Animation -->
                <div class="relative overflow-hidden">
                    <!-- Login Form -->
                    <div
                        :class="[
                            'transition-all duration-500 ease-in-out',
                            isLogin
                                ? 'translate-x-0 opacity-100'
                                : '-translate-x-full opacity-0 absolute inset-0',
                        ]"
                    >
                        <div class="text-center mb-6">
                            <h2 class="text-2xl font-bold text-slate-800 mb-1">
                                Welcome Back
                            </h2>
                            <p class="text-slate-600 text-sm">
                                Sign in to your account
                            </p>
                        </div>

                        <!-- Status Message -->
                        <div
                            v-if="status"
                            class="mb-6 p-3 bg-blue-50 border border-blue-200 rounded-lg"
                        >
                            <p class="text-sm text-blue-700">{{ status }}</p>
                        </div>

                        <form @submit.prevent="submitLogin" class="space-y-4">
                            <ModernInput
                                v-model="loginForm.email"
                                :icon="EnvelopeIcon"
                                type="email"
                                label="Email Address"
                                placeholder="Enter your email"
                                :error="loginForm.errors.email"
                                required
                            />

                            <div class="relative">
                                <ModernInput
                                    v-model="loginForm.password"
                                    :icon="LockClosedIcon"
                                    :type="showPassword ? 'text' : 'password'"
                                    label="Password"
                                    placeholder="Enter your password"
                                    :error="loginForm.errors.password"
                                    required
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-9 text-slate-400 hover:text-slate-600"
                                >
                                    <EyeIcon
                                        v-if="!showPassword"
                                        class="w-4 h-4"
                                    />
                                    <EyeSlashIcon v-else class="w-4 h-4" />
                                </button>
                            </div>

                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <label class="flex items-center gap-2">
                                    <input
                                        v-model="loginForm.remember"
                                        type="checkbox"
                                        class="w-4 h-4 text-slate-600 bg-gray-50 border-gray-300 rounded focus:ring-slate-500"
                                    />
                                    <span class="text-slate-600"
                                        >Remember me</span
                                    >
                                </label>

                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-slate-600 hover:text-slate-800"
                                >
                                    Forgot password?
                                </Link>
                            </div>

                            <ModernButton
                                type="submit"
                                variant="primary"
                                size="lg"
                                :loading="loginForm.processing"
                                class="w-full bg-slate-800 hover:bg-slate-700 focus:ring-slate-500"
                            >
                                Sign In
                            </ModernButton>
                        </form>
                    </div>

                    <!-- Register Form -->
                    <div
                        :class="[
                            'transition-all duration-500 ease-in-out',
                            isRegister
                                ? 'translate-x-0 opacity-100'
                                : 'translate-x-full opacity-0 absolute inset-0',
                        ]"
                    >
                        <div class="text-center mb-6">
                            <h2 class="text-2xl font-bold text-slate-800 mb-1">
                                Create Account
                            </h2>
                            <p class="text-slate-600 text-sm">
                                Join our real estate community
                            </p>
                        </div>

                        <form
                            @submit.prevent="submitRegister"
                            class="space-y-4"
                        >
                            <ModernInput
                                v-model="registerForm.name"
                                :icon="UserIcon"
                                type="text"
                                label="Full Name"
                                placeholder="Enter your full name"
                                :error="registerForm.errors.name"
                                required
                            />

                            <ModernInput
                                v-model="registerForm.email"
                                :icon="EnvelopeIcon"
                                type="email"
                                label="Email Address"
                                placeholder="Enter your email"
                                :error="registerForm.errors.email"
                                required
                            />

                            <!-- Simple Role Selection -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-medium text-slate-700"
                                    >Account Type</label
                                >
                                <div class="grid grid-cols-1 gap-2">
                                    <label class="relative cursor-pointer">
                                        <input
                                            v-model="registerForm.role"
                                            type="radio"
                                            value="client"
                                            class="sr-only"
                                        />
                                        <div
                                            :class="[
                                                'p-3 border rounded-lg transition-all duration-200',
                                                registerForm.role === 'client'
                                                    ? 'border-slate-400 bg-slate-50'
                                                    : 'border-gray-200 hover:border-gray-300',
                                            ]"
                                        >
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    :class="[
                                                        'w-4 h-4 rounded-full border-2 flex items-center justify-center',
                                                        registerForm.role ===
                                                        'client'
                                                            ? 'border-slate-600 bg-slate-600'
                                                            : 'border-gray-300',
                                                    ]"
                                                >
                                                    <div
                                                        v-if="
                                                            registerForm.role ===
                                                            'client'
                                                        "
                                                        class="w-1.5 h-1.5 bg-white rounded-full"
                                                    ></div>
                                                </div>
                                                <UserGroupIcon
                                                    class="w-4 h-4 text-slate-600"
                                                />
                                                <div>
                                                    <div
                                                        class="font-medium text-slate-800 text-sm"
                                                    >
                                                        Regular User
                                                    </div>
                                                    <div
                                                        class="text-xs text-slate-600"
                                                    >
                                                        Browse and inquire about
                                                        properties
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="relative cursor-pointer">
                                        <input
                                            v-model="registerForm.role"
                                            type="radio"
                                            value="broker"
                                            class="sr-only"
                                        />
                                        <div
                                            :class="[
                                                'p-3 border rounded-lg transition-all duration-200',
                                                registerForm.role === 'broker'
                                                    ? 'border-slate-400 bg-slate-50'
                                                    : 'border-gray-200 hover:border-gray-300',
                                            ]"
                                        >
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    :class="[
                                                        'w-4 h-4 rounded-full border-2 flex items-center justify-center',
                                                        registerForm.role ===
                                                        'broker'
                                                            ? 'border-slate-600 bg-slate-600'
                                                            : 'border-gray-300',
                                                    ]"
                                                >
                                                    <div
                                                        v-if="
                                                            registerForm.role ===
                                                            'broker'
                                                        "
                                                        class="w-1.5 h-1.5 bg-white rounded-full"
                                                    ></div>
                                                </div>
                                                <BriefcaseIcon
                                                    class="w-4 h-4 text-amber-600"
                                                />
                                                <div>
                                                    <div
                                                        class="font-medium text-slate-800 text-sm"
                                                    >
                                                        Real Estate Broker
                                                    </div>
                                                    <div
                                                        class="text-xs text-slate-600"
                                                    >
                                                        List and sell properties
                                                        (requires verification)
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Broker Credentials -->
                            <div
                                v-if="registerForm.role === 'broker'"
                                class="space-y-4 p-4 bg-gray-50 rounded-lg border"
                            >
                                <h3
                                    class="text-sm font-medium text-slate-800 flex items-center gap-2"
                                >
                                    <BriefcaseIcon class="w-4 h-4" />
                                    Professional Credentials
                                </h3>

                                <ModernInput
                                    v-model="registerForm.prc_id"
                                    type="text"
                                    label="PRC License Number"
                                    placeholder="Enter your PRC license number"
                                    :error="registerForm.errors.prc_id"
                                    required
                                />

                                <FileUpload
                                    v-model="registerForm.prc_id_file"
                                    label="PRC License Document"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    :error="registerForm.errors.prc_id_file"
                                    required
                                />

                                <div
                                    class="bg-blue-50 border border-blue-200 rounded-lg p-3"
                                >
                                    <p class="text-xs text-blue-800">
                                        Your broker application will be reviewed
                                        by our admin team.
                                    </p>
                                </div>
                            </div>

                            <div class="relative">
                                <ModernInput
                                    v-model="registerForm.business_permit"
                                    type="text"
                                    label="Business Permit Number"
                                    placeholder="Enter your business permit number"
                                    :error="registerForm.errors.business_permit"
                                    required
                                />

                                <FileUpload
                                    v-model="registerForm.business_permit_file"
                                    label="Business Permit Document"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    :error="
                                        registerForm.errors.business_permit_file
                                    "
                                    required
                                />

                                <div
                                    class="bg-blue-50 border border-blue-200 rounded-lg p-3"
                                >
                                    <p class="text-xs text-blue-800">
                                        Your broker application will be reviewed
                                        by our admin team.
                                    </p>
                                </div>
                            </div>

                            <div class="relative">
                                <ModernInput
                                    v-model="registerForm.password"
                                    :icon="LockClosedIcon"
                                    :type="showPassword ? 'text' : 'password'"
                                    label="Password"
                                    placeholder="Create a password"
                                    :error="registerForm.errors.password"
                                    required
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-9 text-slate-400 hover:text-slate-600"
                                >
                                    <EyeIcon
                                        v-if="!showPassword"
                                        class="w-4 h-4"
                                    />
                                    <EyeSlashIcon v-else class="w-4 h-4" />
                                </button>
                            </div>

                            <div class="relative">
                                <ModernInput
                                    v-model="registerForm.password_confirmation"
                                    :icon="LockClosedIcon"
                                    :type="
                                        showPasswordConfirmation
                                            ? 'text'
                                            : 'password'
                                    "
                                    label="Confirm Password"
                                    placeholder="Confirm your password"
                                    :error="
                                        registerForm.errors
                                            .password_confirmation
                                    "
                                    required
                                />
                                <button
                                    type="button"
                                    @click="
                                        showPasswordConfirmation =
                                            !showPasswordConfirmation
                                    "
                                    class="absolute right-3 top-9 text-slate-400 hover:text-slate-600"
                                >
                                    <EyeIcon
                                        v-if="!showPasswordConfirmation"
                                        class="w-4 h-4"
                                    />
                                    <EyeSlashIcon v-else class="w-4 h-4" />
                                </button>
                            </div>

                            <ModernButton
                                type="submit"
                                variant="primary"
                                size="lg"
                                :loading="registerForm.processing"
                                class="w-full bg-slate-800 hover:bg-slate-700 focus:ring-slate-500"
                            >
                                Create Account
                            </ModernButton>
                        </form>
                    </div>
                </div>

                <!-- Back to Home -->
                <div class="mt-6 text-center">
                    <Link
                        :href="route('home')"
                        class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-1"
                    >
                        ← Back to Home
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
