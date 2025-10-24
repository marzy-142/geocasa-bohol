<script setup>
const props = defineProps({
    size: {
        type: String,
        default: "medium", // 'small', 'medium', 'large'
        validator: (value) => ["small", "medium", "large"].includes(value),
    },
    variant: {
        type: String,
        default: "default", // 'default', 'light', 'monochrome'
        validator: (value) =>
            ["default", "light", "monochrome"].includes(value),
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
    logoSrc: {
        type: String,
        default: "/images/logo.png", // Default logo path
    },
});

const sizeClasses = {
    small: {
        container: "w-8 h-8",
        logo: "w-8 h-8",
        text: "text-lg",
        subtext: "text-xs",
    },
    medium: {
        container: "w-10 h-10",
        logo: "w-10 h-10",
        text: "text-xl",
        subtext: "text-sm",
    },
    large: {
        container: "w-12 h-12",
        logo: "w-12 h-12",
        text: "text-2xl",
        subtext: "text-base",
    },
};

const variantClasses = {
    default: {
        primaryText: "text-slate-800",
        accentText: "text-emerald-600",
        subtext: "text-slate-600",
    },
    light: {
        primaryText: "text-white",
        accentText: "text-emerald-200",
        subtext: "text-white/90",
    },
    monochrome: {
        primaryText: "text-slate-800",
        accentText: "text-slate-600",
        subtext: "text-slate-500",
    },
};
</script>

<template>
    <div
        :class="[
            'flex items-center',
            layout === 'vertical' ? 'flex-col gap-2' : 'gap-3',
        ]"
    >
        <!-- Photo Logo -->
        <div class="relative">
            <div
                :class="[
                    sizeClasses[size].container,
                    'flex items-center justify-center',
                ]"
            >
                <!-- Dynamic logo source -->
                <img
                    :class="[
                        sizeClasses[size].logo,
                        'object-contain rounded-lg',
                    ]"
                    :src="logoSrc"
                    alt="GeoCasa Bohol Logo"
                    loading="lazy"
                />
            </div>
        </div>

        <!-- Logo Text -->
        <div
            v-if="showText"
            :class="[layout === 'vertical' ? 'text-center' : 'text-left']"
        >
            <div :class="[sizeClasses[size].text, 'font-bold tracking-tight']">
                <span :class="variantClasses[variant].primaryText">Geo</span
                ><span :class="variantClasses[variant].accentText">Casa</span>
            </div>
            <div
                :class="[
                    sizeClasses[size].subtext,
                    variantClasses[variant].subtext,
                    'font-medium -mt-0.5 tracking-wide',
                ]"
            >
                Bohol
            </div>
        </div>
    </div>
</template>
