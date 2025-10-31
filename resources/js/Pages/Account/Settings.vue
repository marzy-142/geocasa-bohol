<script setup>
import { ref, computed } from "vue";
import { Head, useForm, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    user: Object,
    additionalData: Object,
});

const page = usePage();
const activeTab = ref("profile");

// Profile Form
const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone || "",
    address: props.user.address || "",
    bio: props.user.bio || "",
});

const updateProfile = () => {
    profileForm.patch(route("account.update-profile"), {
        preserveScroll: true,
    });
};

// Password Form
const passwordForm = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updatePassword = () => {
    passwordForm.patch(route("account.update-password"), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

// Avatar Form
const avatarForm = useForm({
    avatar: null,
});

const avatarPreview = ref(null);

const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        avatarForm.avatar = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const updateAvatar = () => {
    avatarForm.post(route("account.update-avatar"), {
        preserveScroll: true,
        onSuccess: () => {
            avatarPreview.value = null;
            // Force page reload to update avatar globally with cache busting
            router.reload({ only: ["auth"] });
        },
    });
};

const deleteAvatar = () => {
    if (confirm("Are you sure you want to remove your profile picture?")) {
        avatarForm.delete(route("account.delete-avatar"), {
            preserveScroll: true,
            onSuccess: () => {
                // Force page reload to update avatar globally
                router.reload({ only: ["auth"] });
            },
        });
    }
};

// Notification Preferences Form
const notificationForm = useForm({
    email_notifications:
        props.user.notification_preferences?.email_notifications ?? true,
    sms_notifications:
        props.user.notification_preferences?.sms_notifications ?? false,
    push_notifications:
        props.user.notification_preferences?.push_notifications ?? true,
    notify_new_inquiry:
        props.user.notification_preferences?.notify_new_inquiry ?? true,
    notify_status_update:
        props.user.notification_preferences?.notify_status_update ?? true,
    notify_new_message:
        props.user.notification_preferences?.notify_new_message ?? true,
    notify_transaction_update:
        props.user.notification_preferences?.notify_transaction_update ?? true,
    notify_payment_reminder:
        props.user.notification_preferences?.notify_payment_reminder ?? true,
});

const updateNotifications = () => {
    notificationForm.patch(route("account.update-notifications"), {
        preserveScroll: true,
    });
};

// Privacy Settings Form
const privacyForm = useForm({
    profile_visibility:
        props.user.privacy_settings?.profile_visibility || "public",
    show_email: props.user.privacy_settings?.show_email ?? true,
    show_phone: props.user.privacy_settings?.show_phone ?? true,
    allow_messages: props.user.privacy_settings?.allow_messages ?? true,
});

const updatePrivacy = () => {
    privacyForm.patch(route("account.update-privacy"), {
        preserveScroll: true,
    });
};

// Professional Profile Form (Broker Only)
const professionalProfileForm = useForm({
    bio: props.user.bio || "",
    specializations: props.user.specializations || [],
    service_areas: props.user.service_areas || [],
    website: props.user.website || "",
    facebook: props.user.facebook || "",
    linkedin: props.user.linkedin || "",
    availability_status: props.user.availability_status || "available",
});

const updateProfessionalProfile = () => {
    professionalProfileForm.patch(
        route("account.update-professional-profile"),
        {
            preserveScroll: true,
        }
    );
};

// Property specializations options
const specializationOptions = [
    { value: "residential", label: "Residential Properties" },
    { value: "commercial", label: "Commercial Properties" },
    { value: "agricultural", label: "Agricultural Land" },
    { value: "industrial", label: "Industrial Properties" },
    { value: "lot", label: "Vacant Lots" },
    { value: "beach_resort", label: "Beach & Resort Properties" },
    { value: "investment", label: "Investment Properties" },
    { value: "luxury", label: "Luxury Properties" },
];

// Bohol municipalities for service areas
const boholMunicipalities = [
    "Tagbilaran City",
    "Baclayon",
    "Balilihan",
    "Batuan",
    "Bilar",
    "Buenavista",
    "Calape",
    "Candijay",
    "Carmen",
    "Catigbian",
    "Clarin",
    "Corella",
    "Cortes",
    "Dagohoy",
    "Danao",
    "Dauis",
    "Dimiao",
    "Duero",
    "Garcia Hernandez",
    "Guindulman",
    "Inabanga",
    "Jagna",
    "Jetafe",
    "Lila",
    "Loay",
    "Loboc",
    "Loon",
    "Mabini",
    "Maribojoc",
    "Panglao",
    "Pilar",
    "President Carlos P. Garcia",
    "Sagbayan",
    "San Isidro",
    "San Miguel",
    "Sevilla",
    "Sierra Bullones",
    "Sikatuna",
    "Talibon",
    "Trinidad",
    "Tubigon",
    "Ubay",
    "Valencia",
    "Well",
];

const toggleSpecialization = (value) => {
    const index = professionalProfileForm.specializations.indexOf(value);
    if (index > -1) {
        professionalProfileForm.specializations.splice(index, 1);
    } else {
        professionalProfileForm.specializations.push(value);
    }
};

const toggleServiceArea = (municipality) => {
    const index = professionalProfileForm.service_areas.indexOf(municipality);
    if (index > -1) {
        professionalProfileForm.service_areas.splice(index, 1);
    } else {
        professionalProfileForm.service_areas.push(municipality);
    }
};

// Deactivate Account Form
const deactivateForm = useForm({
    password: "",
    reason: "",
});

const deactivateAccount = () => {
    if (
        confirm(
            "Are you sure you want to deactivate your account? You can reactivate it later."
        )
    ) {
        deactivateForm.post(route("account.deactivate"), {
            preserveScroll: true,
        });
    }
};

// Delete Account Form
const deleteForm = useForm({
    password: "",
    confirmation: "",
});

const deleteAccount = () => {
    if (
        confirm(
            "⚠️ WARNING: This action is PERMANENT and cannot be undone. Are you absolutely sure?"
        )
    ) {
        deleteForm.delete(route("account.delete"), {
            preserveScroll: true,
        });
    }
};

const avatarUrl = computed(() => {
    if (avatarPreview.value) return avatarPreview.value;
    if (props.user.avatar) return `/storage/${props.user.avatar}`;
    return null;
});

const getInitials = (name) => {
    return name
        .split(" ")
        .map((word) => word[0])
        .join("")
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <Head title="Account Settings" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Account Settings
                    </h1>
                    <p class="mt-2 text-gray-600">
                        Manage your account preferences and security
                    </p>
                </div>

                <!-- Success Message -->
                <div
                    v-if="$page.props.flash.success"
                    class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg"
                >
                    {{ $page.props.flash.success }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Sidebar Navigation -->
                    <div class="lg:col-span-1">
                        <nav
                            class="bg-white rounded-lg shadow-sm p-4 space-y-1"
                        >
                            <button
                                @click="activeTab = 'profile'"
                                :class="[
                                    activeTab === 'profile'
                                        ? 'bg-blue-50 text-blue-700 font-semibold'
                                        : 'text-gray-700 hover:bg-gray-50',
                                    'w-full text-left px-4 py-3 rounded-lg transition-colors',
                                ]"
                            >
                                <div class="flex items-center">
                                    <svg
                                        class="w-5 h-5 mr-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                        />
                                    </svg>
                                    Profile
                                </div>
                            </button>

                            <button
                                @click="activeTab = 'security'"
                                :class="[
                                    activeTab === 'security'
                                        ? 'bg-blue-50 text-blue-700 font-semibold'
                                        : 'text-gray-700 hover:bg-gray-50',
                                    'w-full text-left px-4 py-3 rounded-lg transition-colors',
                                ]"
                            >
                                <div class="flex items-center">
                                    <svg
                                        class="w-5 h-5 mr-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        />
                                    </svg>
                                    Security
                                </div>
                            </button>

                            <button
                                @click="activeTab = 'notifications'"
                                :class="[
                                    activeTab === 'notifications'
                                        ? 'bg-blue-50 text-blue-700 font-semibold'
                                        : 'text-gray-700 hover:bg-gray-50',
                                    'w-full text-left px-4 py-3 rounded-lg transition-colors',
                                ]"
                            >
                                <div class="flex items-center">
                                    <svg
                                        class="w-5 h-5 mr-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                        />
                                    </svg>
                                    Notifications
                                </div>
                            </button>

                            <button
                                @click="activeTab = 'privacy'"
                                :class="[
                                    activeTab === 'privacy'
                                        ? 'bg-blue-50 text-blue-700 font-semibold'
                                        : 'text-gray-700 hover:bg-gray-50',
                                    'w-full text-left px-4 py-3 rounded-lg transition-colors',
                                ]"
                            >
                                <div class="flex items-center">
                                    <svg
                                        class="w-5 h-5 mr-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                        />
                                    </svg>
                                    Privacy
                                </div>
                            </button>

                            <!-- Professional Profile Tab (Broker Only) -->
                            <button
                                v-if="user.role === 'broker'"
                                @click="activeTab = 'professional'"
                                :class="[
                                    activeTab === 'professional'
                                        ? 'bg-blue-50 text-blue-700 font-semibold'
                                        : 'text-gray-700 hover:bg-gray-50',
                                    'w-full text-left px-4 py-3 rounded-lg transition-colors',
                                ]"
                            >
                                <div class="flex items-center">
                                    <svg
                                        class="w-5 h-5 mr-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        />
                                    </svg>
                                    Professional Profile
                                </div>
                            </button>

                            <button
                                @click="activeTab = 'danger'"
                                :class="[
                                    activeTab === 'danger'
                                        ? 'bg-red-50 text-red-700 font-semibold'
                                        : 'text-gray-700 hover:bg-gray-50',
                                    'w-full text-left px-4 py-3 rounded-lg transition-colors',
                                ]"
                            >
                                <div class="flex items-center">
                                    <svg
                                        class="w-5 h-5 mr-3"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                        />
                                    </svg>
                                    Danger Zone
                                </div>
                            </button>
                        </nav>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-3">
                        <!-- Profile Tab -->
                        <div
                            v-show="activeTab === 'profile'"
                            class="bg-white rounded-lg shadow-sm p-6"
                        >
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                                Profile Information
                            </h2>

                            <!-- Avatar Section -->
                            <div class="mb-8 pb-8 border-b border-gray-200">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-4"
                                    >Profile Picture</label
                                >
                                <div class="flex items-center space-x-6">
                                    <!-- Avatar Display -->
                                    <div class="relative">
                                        <div
                                            v-if="avatarUrl"
                                            class="w-24 h-24 rounded-full overflow-hidden border-4 border-gray-200"
                                        >
                                            <img
                                                :src="avatarUrl"
                                                alt="Avatar"
                                                class="w-full h-full object-cover"
                                            />
                                        </div>
                                        <div
                                            v-else
                                            class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center border-4 border-gray-200"
                                        >
                                            <span
                                                class="text-2xl font-bold text-white"
                                                >{{
                                                    getInitials(user.name)
                                                }}</span
                                            >
                                        </div>
                                    </div>

                                    <!-- Avatar Actions -->
                                    <div class="flex-1">
                                        <input
                                            type="file"
                                            id="avatar"
                                            accept="image/*"
                                            @change="handleAvatarChange"
                                            class="hidden"
                                        />
                                        <div class="flex space-x-3">
                                            <label
                                                for="avatar"
                                                class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                                            >
                                                <svg
                                                    class="w-4 h-4 mr-2"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                    />
                                                </svg>
                                                Change Photo
                                            </label>

                                            <button
                                                v-if="avatarPreview"
                                                @click="updateAvatar"
                                                :disabled="
                                                    avatarForm.processing
                                                "
                                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                                            >
                                                <svg
                                                    v-if="
                                                        !avatarForm.processing
                                                    "
                                                    class="w-4 h-4 mr-2"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M5 13l4 4L19 7"
                                                    />
                                                </svg>
                                                <svg
                                                    v-else
                                                    class="animate-spin w-4 h-4 mr-2"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <circle
                                                        class="opacity-25"
                                                        cx="12"
                                                        cy="12"
                                                        r="10"
                                                        stroke="currentColor"
                                                        stroke-width="4"
                                                    ></circle>
                                                    <path
                                                        class="opacity-75"
                                                        fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                    ></path>
                                                </svg>
                                                {{
                                                    avatarForm.processing
                                                        ? "Uploading..."
                                                        : "Upload"
                                                }}
                                            </button>

                                            <button
                                                v-if="
                                                    user.avatar &&
                                                    !avatarPreview
                                                "
                                                @click="deleteAvatar"
                                                class="inline-flex items-center px-4 py-2 bg-white border border-red-300 rounded-lg text-sm font-medium text-red-700 hover:bg-red-50 transition-colors"
                                            >
                                                <svg
                                                    class="w-4 h-4 mr-2"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    />
                                                </svg>
                                                Remove
                                            </button>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">
                                            JPG, PNG or GIF. Max size 2MB.
                                        </p>
                                        <p
                                            v-if="avatarForm.errors.avatar"
                                            class="mt-1 text-sm text-red-600"
                                        >
                                            {{ avatarForm.errors.avatar }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Form -->
                            <form
                                @submit.prevent="updateProfile"
                                class="space-y-6"
                            >
                                <div>
                                    <label
                                        for="name"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Full Name</label
                                    >
                                    <input
                                        id="name"
                                        v-model="profileForm.name"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required
                                    />
                                    <p
                                        v-if="profileForm.errors.name"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ profileForm.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="email"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Email Address</label
                                    >
                                    <input
                                        id="email"
                                        v-model="profileForm.email"
                                        type="email"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required
                                    />
                                    <p
                                        v-if="profileForm.errors.email"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ profileForm.errors.email }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="phone"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Phone Number</label
                                    >
                                    <input
                                        id="phone"
                                        v-model="profileForm.phone"
                                        type="tel"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="+63 XXX XXX XXXX"
                                    />
                                    <p
                                        v-if="profileForm.errors.phone"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ profileForm.errors.phone }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="address"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Address</label
                                    >
                                    <input
                                        id="address"
                                        v-model="profileForm.address"
                                        type="text"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Street, City, Province"
                                    />
                                    <p
                                        v-if="profileForm.errors.address"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ profileForm.errors.address }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="bio"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Bio</label
                                    >
                                    <textarea
                                        id="bio"
                                        v-model="profileForm.bio"
                                        rows="4"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Tell us about yourself..."
                                    ></textarea>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ profileForm.bio?.length || 0 }}/1000
                                        characters
                                    </p>
                                    <p
                                        v-if="profileForm.errors.bio"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ profileForm.errors.bio }}
                                    </p>
                                </div>

                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="profileForm.processing"
                                        class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                                    >
                                        <svg
                                            v-if="!profileForm.processing"
                                            class="w-4 h-4 mr-2"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            class="animate-spin w-4 h-4 mr-2"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            ></circle>
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        {{
                                            profileForm.processing
                                                ? "Saving..."
                                                : "Save Changes"
                                        }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Security Tab -->
                        <div
                            v-show="activeTab === 'security'"
                            class="bg-white rounded-lg shadow-sm p-6"
                        >
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                                Security Settings
                            </h2>

                            <form
                                @submit.prevent="updatePassword"
                                class="space-y-6"
                            >
                                <div
                                    class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6"
                                >
                                    <div class="flex">
                                        <svg
                                            class="w-5 h-5 text-blue-600 mr-3 mt-0.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        <div>
                                            <h3
                                                class="text-sm font-semibold text-blue-900"
                                            >
                                                Password Requirements
                                            </h3>
                                            <ul
                                                class="mt-2 text-sm text-blue-800 space-y-1"
                                            >
                                                <li>
                                                    • At least 8 characters long
                                                </li>
                                                <li>
                                                    • Contains uppercase and
                                                    lowercase letters
                                                </li>
                                                <li>
                                                    • Contains at least one
                                                    number
                                                </li>
                                                <li>
                                                    • Contains at least one
                                                    special character
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="current_password"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Current Password</label
                                    >
                                    <input
                                        id="current_password"
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required
                                    />
                                    <p
                                        v-if="
                                            passwordForm.errors.current_password
                                        "
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{
                                            passwordForm.errors.current_password
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="password"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >New Password</label
                                    >
                                    <input
                                        id="password"
                                        v-model="passwordForm.password"
                                        type="password"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required
                                    />
                                    <p
                                        v-if="passwordForm.errors.password"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ passwordForm.errors.password }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="password_confirmation"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Confirm New Password</label
                                    >
                                    <input
                                        id="password_confirmation"
                                        v-model="
                                            passwordForm.password_confirmation
                                        "
                                        type="password"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required
                                    />
                                </div>

                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="passwordForm.processing"
                                        class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                                    >
                                        <svg
                                            v-if="!passwordForm.processing"
                                            class="w-4 h-4 mr-2"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            class="animate-spin w-4 h-4 mr-2"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            ></circle>
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        {{
                                            passwordForm.processing
                                                ? "Updating..."
                                                : "Update Password"
                                        }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Notifications Tab -->
                        <div
                            v-show="activeTab === 'notifications'"
                            class="bg-white rounded-lg shadow-sm p-6"
                        >
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                                Notification Preferences
                            </h2>

                            <form
                                @submit.prevent="updateNotifications"
                                class="space-y-8"
                            >
                                <!-- Notification Channels -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 mb-4"
                                    >
                                        Notification Channels
                                    </h3>
                                    <div class="space-y-4">
                                        <label
                                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                        >
                                            <div class="flex items-center">
                                                <svg
                                                    class="w-5 h-5 text-gray-600 mr-3"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                                    />
                                                </svg>
                                                <div>
                                                    <p
                                                        class="font-medium text-gray-900"
                                                    >
                                                        Email Notifications
                                                    </p>
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        Receive notifications
                                                        via email
                                                    </p>
                                                </div>
                                            </div>
                                            <input
                                                v-model="
                                                    notificationForm.email_notifications
                                                "
                                                type="checkbox"
                                                class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                            />
                                        </label>

                                        <label
                                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                        >
                                            <div class="flex items-center">
                                                <svg
                                                    class="w-5 h-5 text-gray-600 mr-3"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                                                    />
                                                </svg>
                                                <div>
                                                    <p
                                                        class="font-medium text-gray-900"
                                                    >
                                                        Push Notifications
                                                    </p>
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        Receive push
                                                        notifications in browser
                                                    </p>
                                                </div>
                                            </div>
                                            <input
                                                v-model="
                                                    notificationForm.push_notifications
                                                "
                                                type="checkbox"
                                                class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                            />
                                        </label>

                                        <label
                                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors opacity-50"
                                        >
                                            <div class="flex items-center">
                                                <svg
                                                    class="w-5 h-5 text-gray-600 mr-3"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                                    />
                                                </svg>
                                                <div>
                                                    <p
                                                        class="font-medium text-gray-900"
                                                    >
                                                        SMS Notifications
                                                    </p>
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        Receive notifications
                                                        via SMS (Coming soon)
                                                    </p>
                                                </div>
                                            </div>
                                            <input
                                                v-model="
                                                    notificationForm.sms_notifications
                                                "
                                                type="checkbox"
                                                disabled
                                                class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                            />
                                        </label>
                                    </div>
                                </div>

                                <!-- Notification Types -->
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900 mb-4"
                                    >
                                        Notification Types
                                    </h3>
                                    <div class="space-y-4">
                                        <label
                                            v-if="user.role === 'broker'"
                                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                        >
                                            <div>
                                                <p
                                                    class="font-medium text-gray-900"
                                                >
                                                    New Inquiries
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    When you receive a new
                                                    property inquiry
                                                </p>
                                            </div>
                                            <input
                                                v-model="
                                                    notificationForm.notify_new_inquiry
                                                "
                                                type="checkbox"
                                                class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                            />
                                        </label>

                                        <label
                                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                        >
                                            <div>
                                                <p
                                                    class="font-medium text-gray-900"
                                                >
                                                    Status Updates
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    When inquiry or transaction
                                                    status changes
                                                </p>
                                            </div>
                                            <input
                                                v-model="
                                                    notificationForm.notify_status_update
                                                "
                                                type="checkbox"
                                                class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                            />
                                        </label>

                                        <label
                                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                        >
                                            <div>
                                                <p
                                                    class="font-medium text-gray-900"
                                                >
                                                    New Messages
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    When you receive a new
                                                    message
                                                </p>
                                            </div>
                                            <input
                                                v-model="
                                                    notificationForm.notify_new_message
                                                "
                                                type="checkbox"
                                                class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                            />
                                        </label>

                                        <label
                                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                        >
                                            <div>
                                                <p
                                                    class="font-medium text-gray-900"
                                                >
                                                    Transaction Updates
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    Updates on your property
                                                    transactions
                                                </p>
                                            </div>
                                            <input
                                                v-model="
                                                    notificationForm.notify_transaction_update
                                                "
                                                type="checkbox"
                                                class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                            />
                                        </label>

                                        <label
                                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                        >
                                            <div>
                                                <p
                                                    class="font-medium text-gray-900"
                                                >
                                                    Payment Reminders
                                                </p>
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    Reminders for upcoming
                                                    payments
                                                </p>
                                            </div>
                                            <input
                                                v-model="
                                                    notificationForm.notify_payment_reminder
                                                "
                                                type="checkbox"
                                                class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                            />
                                        </label>
                                    </div>
                                </div>

                                <div class="flex justify-end pt-4 border-t">
                                    <button
                                        type="submit"
                                        :disabled="notificationForm.processing"
                                        class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                                    >
                                        <svg
                                            v-if="!notificationForm.processing"
                                            class="w-4 h-4 mr-2"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            class="animate-spin w-4 h-4 mr-2"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            ></circle>
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        {{
                                            notificationForm.processing
                                                ? "Saving..."
                                                : "Save Preferences"
                                        }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Privacy Tab -->
                        <div
                            v-show="activeTab === 'privacy'"
                            class="bg-white rounded-lg shadow-sm p-6"
                        >
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                                Privacy Settings
                            </h2>

                            <form
                                @submit.prevent="updatePrivacy"
                                class="space-y-6"
                            >
                                <div>
                                    <label
                                        for="profile_visibility"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                        >Profile Visibility</label
                                    >
                                    <select
                                        id="profile_visibility"
                                        v-model="privacyForm.profile_visibility"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="public">
                                            Public - Anyone can see your profile
                                        </option>
                                        <option value="contacts">
                                            Contacts Only - Only your contacts
                                            can see
                                        </option>
                                        <option value="private">
                                            Private - Only you can see your
                                            profile
                                        </option>
                                    </select>
                                </div>

                                <div class="space-y-4">
                                    <h3
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        Contact Information Visibility
                                    </h3>

                                    <label
                                        class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                    >
                                        <div>
                                            <p
                                                class="font-medium text-gray-900"
                                            >
                                                Show Email Address
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                Display your email in your
                                                public profile
                                            </p>
                                        </div>
                                        <input
                                            v-model="privacyForm.show_email"
                                            type="checkbox"
                                            class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                        />
                                    </label>

                                    <label
                                        class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                    >
                                        <div>
                                            <p
                                                class="font-medium text-gray-900"
                                            >
                                                Show Phone Number
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                Display your phone in your
                                                public profile
                                            </p>
                                        </div>
                                        <input
                                            v-model="privacyForm.show_phone"
                                            type="checkbox"
                                            class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                        />
                                    </label>
                                </div>

                                <div class="space-y-4">
                                    <h3
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        Communication Preferences
                                    </h3>

                                    <label
                                        class="flex items-center justify-between p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                    >
                                        <div>
                                            <p
                                                class="font-medium text-gray-900"
                                            >
                                                Allow Direct Messages
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                Let other users send you
                                                messages
                                            </p>
                                        </div>
                                        <input
                                            v-model="privacyForm.allow_messages"
                                            type="checkbox"
                                            class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                        />
                                    </label>
                                </div>

                                <div class="flex justify-end pt-4 border-t">
                                    <button
                                        type="submit"
                                        :disabled="privacyForm.processing"
                                        class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                                    >
                                        <svg
                                            v-if="!privacyForm.processing"
                                            class="w-4 h-4 mr-2"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            class="animate-spin w-4 h-4 mr-2"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            ></circle>
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        {{
                                            privacyForm.processing
                                                ? "Saving..."
                                                : "Save Settings"
                                        }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Professional Profile Tab (Broker Only) -->
                        <div
                            v-if="user.role === 'broker'"
                            v-show="activeTab === 'professional'"
                            class="bg-white rounded-lg shadow-sm p-6"
                        >
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                                Professional Profile
                            </h2>
                            <p class="text-gray-600 mb-6">
                                Enhance your broker profile to attract more
                                clients
                            </p>

                            <form
                                @submit.prevent="updateProfessionalProfile"
                                class="space-y-8"
                            >
                                <!-- Bio Section -->
                                <div>
                                    <label
                                        for="bio"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Professional Bio
                                        <span class="text-gray-500 font-normal"
                                            >(Optional)</span
                                        >
                                    </label>
                                    <textarea
                                        id="bio"
                                        v-model="professionalProfileForm.bio"
                                        rows="4"
                                        maxlength="1000"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Tell clients about your experience, expertise, and what makes you stand out as a broker..."
                                    ></textarea>
                                    <div class="flex justify-between mt-1">
                                        <p class="text-sm text-gray-500">
                                            This will be displayed on your
                                            public profile in the Broker
                                            Directory
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{
                                                professionalProfileForm.bio
                                                    ?.length || 0
                                            }}/1000
                                        </p>
                                    </div>
                                    <p
                                        v-if="
                                            professionalProfileForm.errors.bio
                                        "
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ professionalProfileForm.errors.bio }}
                                    </p>
                                </div>

                                <!-- Specializations Section -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-3"
                                    >
                                        Property Specializations
                                        <span class="text-gray-500 font-normal"
                                            >(Select all that apply)</span
                                        >
                                    </label>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-3"
                                    >
                                        <label
                                            v-for="option in specializationOptions"
                                            :key="option.value"
                                            class="flex items-center p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="option.value"
                                                :checked="
                                                    professionalProfileForm.specializations.includes(
                                                        option.value
                                                    )
                                                "
                                                @change="
                                                    toggleSpecialization(
                                                        option.value
                                                    )
                                                "
                                                class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                            />
                                            <span class="ml-3 text-gray-900">{{
                                                option.label
                                            }}</span>
                                        </label>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">
                                        {{
                                            professionalProfileForm
                                                .specializations.length
                                        }}
                                        selected
                                    </p>
                                    <p
                                        v-if="
                                            professionalProfileForm.errors
                                                .specializations
                                        "
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{
                                            professionalProfileForm.errors
                                                .specializations
                                        }}
                                    </p>
                                </div>

                                <!-- Service Areas Section -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-3"
                                    >
                                        Service Areas in Bohol
                                        <span class="text-gray-500 font-normal"
                                            >(Select municipalities you
                                            serve)</span
                                        >
                                    </label>
                                    <div
                                        class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50"
                                    >
                                        <div
                                            class="grid grid-cols-2 md:grid-cols-3 gap-2"
                                        >
                                            <label
                                                v-for="municipality in boholMunicipalities"
                                                :key="municipality"
                                                class="flex items-center p-2 hover:bg-white rounded cursor-pointer transition-colors"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :value="municipality"
                                                    :checked="
                                                        professionalProfileForm.service_areas.includes(
                                                            municipality
                                                        )
                                                    "
                                                    @change="
                                                        toggleServiceArea(
                                                            municipality
                                                        )
                                                    "
                                                    class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                                                />
                                                <span
                                                    class="ml-2 text-sm text-gray-900"
                                                    >{{ municipality }}</span
                                                >
                                            </label>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500">
                                        {{
                                            professionalProfileForm
                                                .service_areas.length
                                        }}
                                        municipality/municipalities selected
                                    </p>
                                    <p
                                        v-if="
                                            professionalProfileForm.errors
                                                .service_areas
                                        "
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{
                                            professionalProfileForm.errors
                                                .service_areas
                                        }}
                                    </p>
                                </div>

                                <!-- Online Presence Section -->
                                <div class="space-y-4">
                                    <h3
                                        class="text-lg font-medium text-gray-900"
                                    >
                                        Online Presence
                                    </h3>

                                    <div>
                                        <label
                                            for="website"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Website URL
                                            <span
                                                class="text-gray-500 font-normal"
                                                >(Optional)</span
                                            >
                                        </label>
                                        <input
                                            id="website"
                                            v-model="
                                                professionalProfileForm.website
                                            "
                                            type="url"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="https://yourwebsite.com"
                                        />
                                        <p
                                            v-if="
                                                professionalProfileForm.errors
                                                    .website
                                            "
                                            class="mt-1 text-sm text-red-600"
                                        >
                                            {{
                                                professionalProfileForm.errors
                                                    .website
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            for="facebook"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Facebook Profile/Page
                                            <span
                                                class="text-gray-500 font-normal"
                                                >(Optional)</span
                                            >
                                        </label>
                                        <input
                                            id="facebook"
                                            v-model="
                                                professionalProfileForm.facebook
                                            "
                                            type="url"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="https://facebook.com/yourprofile"
                                        />
                                        <p
                                            v-if="
                                                professionalProfileForm.errors
                                                    .facebook
                                            "
                                            class="mt-1 text-sm text-red-600"
                                        >
                                            {{
                                                professionalProfileForm.errors
                                                    .facebook
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            for="linkedin"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            LinkedIn Profile
                                            <span
                                                class="text-gray-500 font-normal"
                                                >(Optional)</span
                                            >
                                        </label>
                                        <input
                                            id="linkedin"
                                            v-model="
                                                professionalProfileForm.linkedin
                                            "
                                            type="url"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            placeholder="https://linkedin.com/in/yourprofile"
                                        />
                                        <p
                                            v-if="
                                                professionalProfileForm.errors
                                                    .linkedin
                                            "
                                            class="mt-1 text-sm text-red-600"
                                        >
                                            {{
                                                professionalProfileForm.errors
                                                    .linkedin
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Availability Status Section -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-3"
                                    >
                                        Availability Status
                                    </label>
                                    <div class="space-y-2">
                                        <label
                                            class="flex items-start p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                            :class="{
                                                'ring-2 ring-green-500 bg-green-50':
                                                    professionalProfileForm.availability_status ===
                                                    'available',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                v-model="
                                                    professionalProfileForm.availability_status
                                                "
                                                value="available"
                                                class="mt-1 w-4 h-4 text-green-600 focus:ring-2 focus:ring-green-500"
                                            />
                                            <div class="ml-3">
                                                <p
                                                    class="font-medium text-gray-900"
                                                >
                                                    🟢 Available
                                                </p>
                                                <p
                                                    class="text-sm text-gray-600"
                                                >
                                                    Actively taking on new
                                                    clients
                                                </p>
                                            </div>
                                        </label>

                                        <label
                                            class="flex items-start p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                            :class="{
                                                'ring-2 ring-yellow-500 bg-yellow-50':
                                                    professionalProfileForm.availability_status ===
                                                    'limited',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                v-model="
                                                    professionalProfileForm.availability_status
                                                "
                                                value="limited"
                                                class="mt-1 w-4 h-4 text-yellow-600 focus:ring-2 focus:ring-yellow-500"
                                            />
                                            <div class="ml-3">
                                                <p
                                                    class="font-medium text-gray-900"
                                                >
                                                    🟡 Limited Availability
                                                </p>
                                                <p
                                                    class="text-sm text-gray-600"
                                                >
                                                    Taking select clients only
                                                </p>
                                            </div>
                                        </label>

                                        <label
                                            class="flex items-start p-4 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors"
                                            :class="{
                                                'ring-2 ring-red-500 bg-red-50':
                                                    professionalProfileForm.availability_status ===
                                                    'unavailable',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                v-model="
                                                    professionalProfileForm.availability_status
                                                "
                                                value="unavailable"
                                                class="mt-1 w-4 h-4 text-red-600 focus:ring-2 focus:ring-red-500"
                                            />
                                            <div class="ml-3">
                                                <p
                                                    class="font-medium text-gray-900"
                                                >
                                                    🔴 Unavailable
                                                </p>
                                                <p
                                                    class="text-sm text-gray-600"
                                                >
                                                    Not accepting new clients at
                                                    this time
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                    <p
                                        v-if="
                                            professionalProfileForm.errors
                                                .availability_status
                                        "
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{
                                            professionalProfileForm.errors
                                                .availability_status
                                        }}
                                    </p>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex justify-end pt-4 border-t">
                                    <button
                                        type="submit"
                                        :disabled="
                                            professionalProfileForm.processing
                                        "
                                        class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 transition-colors"
                                    >
                                        <svg
                                            v-if="
                                                !professionalProfileForm.processing
                                            "
                                            class="w-4 h-4 mr-2"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                        <svg
                                            v-else
                                            class="animate-spin w-4 h-4 mr-2"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            ></circle>
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        {{
                                            professionalProfileForm.processing
                                                ? "Saving..."
                                                : "Update Professional Profile"
                                        }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Danger Zone Tab -->
                        <div
                            v-show="activeTab === 'danger'"
                            class="bg-white rounded-lg shadow-sm p-6"
                        >
                            <h2 class="text-2xl font-bold text-red-600 mb-6">
                                Danger Zone
                            </h2>

                            <!-- Deactivate Account -->
                            <div
                                class="border border-red-200 rounded-lg p-6 mb-6"
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-900 mb-2"
                                >
                                    Deactivate Account
                                </h3>
                                <p class="text-sm text-gray-600 mb-4">
                                    Temporarily deactivate your account. You can
                                    reactivate it anytime by logging in again.
                                </p>

                                <form
                                    @submit.prevent="deactivateAccount"
                                    class="space-y-4"
                                >
                                    <div>
                                        <label
                                            for="deactivate_password"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                            >Confirm Password</label
                                        >
                                        <input
                                            id="deactivate_password"
                                            v-model="deactivateForm.password"
                                            type="password"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                            required
                                        />
                                        <p
                                            v-if="
                                                deactivateForm.errors.password
                                            "
                                            class="mt-1 text-sm text-red-600"
                                        >
                                            {{ deactivateForm.errors.password }}
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            for="deactivate_reason"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                            >Reason (Optional)</label
                                        >
                                        <textarea
                                            id="deactivate_reason"
                                            v-model="deactivateForm.reason"
                                            rows="3"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                            placeholder="Help us improve by telling us why..."
                                        ></textarea>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="deactivateForm.processing"
                                        class="inline-flex items-center px-6 py-3 bg-yellow-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-yellow-700 disabled:opacity-50 transition-colors"
                                    >
                                        <svg
                                            class="w-4 h-4 mr-2"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            />
                                        </svg>
                                        {{
                                            deactivateForm.processing
                                                ? "Deactivating..."
                                                : "Deactivate Account"
                                        }}
                                    </button>
                                </form>
                            </div>

                            <!-- Delete Account -->
                            <div
                                class="border border-red-300 bg-red-50 rounded-lg p-6"
                            >
                                <h3
                                    class="text-lg font-semibold text-red-900 mb-2"
                                >
                                    Delete Account Permanently
                                </h3>
                                <p class="text-sm text-red-800 mb-4">
                                    ⚠️ <strong>Warning:</strong> This action is
                                    irreversible. All your data will be
                                    permanently deleted and cannot be recovered.
                                </p>

                                <form
                                    @submit.prevent="deleteAccount"
                                    class="space-y-4"
                                >
                                    <div>
                                        <label
                                            for="delete_password"
                                            class="block text-sm font-medium text-red-900 mb-2"
                                            >Confirm Password</label
                                        >
                                        <input
                                            id="delete_password"
                                            v-model="deleteForm.password"
                                            type="password"
                                            class="w-full px-4 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                            required
                                        />
                                        <p
                                            v-if="deleteForm.errors.password"
                                            class="mt-1 text-sm text-red-600"
                                        >
                                            {{ deleteForm.errors.password }}
                                        </p>
                                    </div>

                                    <div>
                                        <label
                                            for="delete_confirmation"
                                            class="block text-sm font-medium text-red-900 mb-2"
                                        >
                                            Type
                                            <code
                                                class="px-2 py-1 bg-red-100 rounded text-red-900"
                                                >DELETE</code
                                            >
                                            to confirm
                                        </label>
                                        <input
                                            id="delete_confirmation"
                                            v-model="deleteForm.confirmation"
                                            type="text"
                                            class="w-full px-4 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                            placeholder="Type DELETE"
                                            required
                                        />
                                        <p
                                            v-if="
                                                deleteForm.errors.confirmation
                                            "
                                            class="mt-1 text-sm text-red-600"
                                        >
                                            {{ deleteForm.errors.confirmation }}
                                        </p>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="
                                            deleteForm.processing ||
                                            deleteForm.confirmation !== 'DELETE'
                                        "
                                        class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                    >
                                        <svg
                                            class="w-4 h-4 mr-2"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                        {{
                                            deleteForm.processing
                                                ? "Deleting..."
                                                : "Delete Account Permanently"
                                        }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
