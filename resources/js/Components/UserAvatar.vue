<template>
    <div
        :class="[
            'rounded-full flex items-center justify-center font-semibold overflow-hidden flex-shrink-0',
            sizeClasses,
            !avatarUrl ? bgColorClass : '',
        ]"
        :style="
            avatarUrl
                ? {
                      backgroundImage: `url(${avatarUrl})`,
                      backgroundSize: 'cover',
                      backgroundPosition: 'center',
                  }
                : {}
        "
    >
        <!-- Show initials only if no avatar -->
        <span v-if="!avatarUrl" :class="textSizeClass">
            {{ initials }}
        </span>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    size: {
        type: String,
        default: "md", // xs, sm, md, lg, xl, 2xl
        validator: (value) =>
            ["xs", "sm", "md", "lg", "xl", "2xl"].includes(value),
    },
    bgColor: {
        type: String,
        default: "primary", // primary, gray, blue, green, red, etc.
    },
});

// Compute avatar URL with cache busting
const avatarUrl = computed(() => {
    if (props.user?.avatar) {
        // If avatar_url is provided (with cache buster), use it
        if (props.user.avatar_url) {
            return props.user.avatar_url;
        }
        // Otherwise, construct URL with current timestamp
        return `/storage/${props.user.avatar}?v=${Date.now()}`;
    }
    return null;
});

// Get initials from name
const initials = computed(() => {
    if (!props.user?.name) return "?";

    const nameParts = props.user.name.trim().split(" ");
    if (nameParts.length === 1) {
        return nameParts[0].charAt(0).toUpperCase();
    }
    return (
        nameParts[0].charAt(0) + nameParts[nameParts.length - 1].charAt(0)
    ).toUpperCase();
});

// Size classes
const sizeClasses = computed(() => {
    const sizes = {
        xs: "w-6 h-6",
        sm: "w-8 h-8",
        md: "w-10 h-10",
        lg: "w-12 h-12",
        xl: "w-16 h-16",
        "2xl": "w-20 h-20",
    };
    return sizes[props.size] || sizes.md;
});

// Text size classes
const textSizeClass = computed(() => {
    const sizes = {
        xs: "text-xs",
        sm: "text-xs",
        md: "text-sm",
        lg: "text-base",
        xl: "text-lg",
        "2xl": "text-xl",
    };
    return sizes[props.size] || sizes.md;
});

// Background color classes
const bgColorClass = computed(() => {
    const colors = {
        primary: "bg-primary-600 text-white",
        gray: "bg-gray-500 text-white",
        blue: "bg-blue-600 text-white",
        green: "bg-green-600 text-white",
        red: "bg-red-600 text-white",
        purple: "bg-purple-600 text-white",
        indigo: "bg-indigo-600 text-white",
        yellow: "bg-yellow-500 text-white",
        pink: "bg-pink-600 text-white",
    };
    return colors[props.bgColor] || colors.primary;
});
</script>

<style scoped>
/* Ensure smooth loading of images */
div[style*="backgroundImage"] {
    transition: background-image 0.3s ease-in-out;
}
</style>
