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
        logoColors: {
            g: "#1e293b", // slate-800
            c: "#059669", // emerald-600
            b: "#0891b2", // cyan-600
            house: "#f59e0b", // amber-500
        },
    },
    light: {
        primaryText: "text-white",
        accentText: "text-emerald-200",
        subtext: "text-white/90",
        logoColors: {
            g: "#ffffff",
            c: "#6ee7b7", // emerald-300
            b: "#67e8f9", // cyan-300
            house: "#fbbf24", // amber-400
        },
    },
    monochrome: {
        primaryText: "text-slate-800",
        accentText: "text-slate-600",
        subtext: "text-slate-500",
        logoColors: {
            g: "#1e293b",
            c: "#475569",
            b: "#64748b",
            house: "#64748b",
        },
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
        <!-- Typographic Logo -->
        <div class="relative">
            <div
                :class="[
                    sizeClasses[size].container,
                    'flex items-center justify-center',
                ]"
            >
                <!-- GCB Typographic Logo -->
                <svg
                    :class="sizeClasses[size].logo"
                    viewBox="0 0 100 100"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <!-- Letter 'G' for Geo -->
                    <g transform="translate(10, 25)">
                        <rect
                            x="0"
                            y="0"
                            width="18"
                            height="25"
                            :fill="variantClasses[variant].logoColors.g"
                            rx="2"
                        />
                        <rect
                            x="12"
                            y="8"
                            width="8"
                            height="9"
                            :fill="white"
                            rx="1"
                        />
                        <rect
                            x="8"
                            y="18"
                            width="12"
                            height="4"
                            :fill="white"
                            rx="1"
                        />
                    </g>

                    <!-- Letter 'C' for Casa -->
                    <g transform="translate(35, 25)">
                        <rect
                            x="0"
                            y="0"
                            width="18"
                            height="25"
                            :fill="variantClasses[variant].logoColors.c"
                            rx="2"
                        />
                        <rect
                            x="12"
                            y="4"
                            width="8"
                            height="17"
                            :fill="white"
                            rx="1"
                        />
                    </g>

                    <!-- Letter 'B' for Bohol -->
                    <g transform="translate(60, 25)">
                        <rect
                            x="0"
                            y="0"
                            width="12"
                            height="25"
                            :fill="variantClasses[variant].logoColors.b"
                            rx="2"
                        />
                        <circle cx="10" cy="8" r="6" :fill="white" />
                        <circle
                            cx="10"
                            cy="8"
                            r="3"
                            :fill="variantClasses[variant].logoColors.b"
                        />
                        <circle cx="10" cy="17" r="6" :fill="white" />
                        <circle
                            cx="10"
                            cy="17"
                            r="3"
                            :fill="variantClasses[variant].logoColors.b"
                        />
                    </g>

                    <!-- House Symbol (Below) -->
                    <g transform="translate(35, 60)">
                        <rect
                            x="0"
                            y="8"
                            width="15"
                            height="12"
                            :fill="variantClasses[variant].logoColors.house"
                            rx="1"
                        />
                        <polygon
                            points="0,8 7.5,0 15,8"
                            :fill="variantClasses[variant].logoColors.house"
                        />
                    </g>
                </svg>
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
