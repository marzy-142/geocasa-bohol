<script setup>
import { MapPinIcon, SunIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    size: {
        type: String,
        default: "medium", // 'small', 'medium', 'large'
        validator: (value) => ["small", "medium", "large"].includes(value),
    },
    variant: {
        type: String,
        default: "default", // 'default', 'light'
        validator: (value) => ["default", "light"].includes(value),
    },
    showText: {
        type: Boolean,
        default: true,
    },
    layout: {
        type: String,
        default: "horizontal", // 'horizontal', 'vertical'
        validator: (value) => ["horizontal", "vertical"].includes(value),
    },
});

const sizeClasses = {
    small: {
        container: "w-10 h-10",
        icon: "w-5 h-5",
        accent: "w-3 h-3",
        accentIcon: "w-1.5 h-1.5",
        text: "text-xl",
        subtext: "text-xs",
    },
    medium: {
        container: "w-12 h-12",
        icon: "w-6 h-6",
        accent: "w-4 h-4",
        accentIcon: "w-2 h-2",
        text: "text-2xl",
        subtext: "text-sm",
    },
    large: {
        container: "w-16 h-16",
        icon: "w-8 h-8",
        accent: "w-5 h-5",
        accentIcon: "w-2.5 h-2.5",
        text: "text-3xl",
        subtext: "text-base",
    },
};

const variantClasses = {
    default: {
        container: "bg-gradient-to-br from-primary-500 to-accent-500",
        icon: "text-white",
        accent: "bg-coconut-400 border-white",
        accentIcon: "text-coconut-800",
        primaryText: "text-primary-600",
        accentText: "text-accent-600",
        subtext: "text-coconut-600",
    },
    light: {
        container: "bg-white/10 backdrop-blur-sm",
        icon: "text-white",
        accent: "bg-coconut-400 border-white",
        accentIcon: "text-coconut-800",
        primaryText: "text-white",
        accentText: "text-coconut-200",
        subtext: "text-white/80",
    },
};
</script>

<template>
    <div
        :class="[
            'flex items-center',
            layout === 'vertical' ? 'flex-col gap-2' : 'gap-4',
        ]"
    >
        <!-- Logo Icon -->
        <div class="relative">
            <div
                :class="[
                    sizeClasses[size].container,
                    variantClasses[variant].container,
                    'rounded-2xl flex items-center justify-center shadow-soft',
                ]"
            >
                <MapPinIcon
                    :class="[
                        sizeClasses[size].icon,
                        variantClasses[variant].icon,
                    ]"
                />
            </div>
            <!-- Accent Dot -->
            <div
                :class="[
                    sizeClasses[size].accent,
                    variantClasses[variant].accent,
                    'absolute -bottom-1 -right-1 rounded-full border-2 flex items-center justify-center',
                ]"
            >
                <SunIcon
                    :class="[
                        sizeClasses[size].accentIcon,
                        variantClasses[variant].accentIcon,
                    ]"
                />
            </div>
        </div>

        <!-- Logo Text -->
        <div
            v-if="showText"
            :class="[layout === 'vertical' ? 'text-center' : 'text-left']"
        >
            <div :class="[sizeClasses[size].text, 'font-bold']">
                <span :class="variantClasses[variant].primaryText">Geo</span
                ><span :class="variantClasses[variant].accentText">Casa</span>
            </div>
            <div
                :class="[
                    sizeClasses[size].subtext,
                    variantClasses[variant].subtext,
                    'font-medium -mt-1',
                ]"
            >
                Bohol
            </div>
        </div>
    </div>
</template>
