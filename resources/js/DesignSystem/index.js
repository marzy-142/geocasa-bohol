/**
 * GeoCasa Bohol Design System
 * Centralized design tokens and component configurations
 */

export const designTokens = {
    // Color Palette
    colors: {
        // Primary Colors
        primary: {
            50: "#eff6ff",
            100: "#dbeafe",
            200: "#bfdbfe",
            300: "#93c5fd",
            400: "#60a5fa",
            500: "#3b82f6", // Main primary
            600: "#2563eb",
            700: "#1d4ed8",
            800: "#1e40af",
            900: "#1e3a8a",
        },

        // Secondary Colors
        secondary: {
            50: "#f0fdf4",
            100: "#dcfce7",
            200: "#bbf7d0",
            300: "#86efac",
            400: "#4ade80",
            500: "#22c55e", // Main secondary
            600: "#16a34a",
            700: "#15803d",
            800: "#166534",
            900: "#14532d",
        },

        // Accent Colors
        accent: {
            50: "#faf5ff",
            100: "#f3e8ff",
            200: "#e9d5ff",
            300: "#d8b4fe",
            400: "#c084fc",
            500: "#a855f7", // Main accent
            600: "#9333ea",
            700: "#7c3aed",
            800: "#6b21a8",
            900: "#581c87",
        },

        // Neutral Colors
        neutral: {
            50: "#fafafa",
            100: "#f5f5f5",
            200: "#e5e5e5",
            300: "#d4d4d4",
            400: "#a3a3a3",
            500: "#737373",
            600: "#525252",
            700: "#404040",
            800: "#262626",
            900: "#171717",
        },

        // Status Colors
        success: {
            50: "#f0fdf4",
            500: "#22c55e",
            600: "#16a34a",
        },

        warning: {
            50: "#fffbeb",
            500: "#f59e0b",
            600: "#d97706",
        },

        error: {
            50: "#fef2f2",
            500: "#ef4444",
            600: "#dc2626",
        },

        info: {
            50: "#eff6ff",
            500: "#3b82f6",
            600: "#2563eb",
        },
    },

    // Typography
    typography: {
        fontFamily: {
            sans: ["Inter", "system-ui", "sans-serif"],
            mono: ["JetBrains Mono", "monospace"],
        },

        fontSize: {
            xs: ["0.75rem", { lineHeight: "1rem" }],
            sm: ["0.875rem", { lineHeight: "1.25rem" }],
            base: ["1rem", { lineHeight: "1.5rem" }],
            lg: ["1.125rem", { lineHeight: "1.75rem" }],
            xl: ["1.25rem", { lineHeight: "1.75rem" }],
            "2xl": ["1.5rem", { lineHeight: "2rem" }],
            "3xl": ["1.875rem", { lineHeight: "2.25rem" }],
            "4xl": ["2.25rem", { lineHeight: "2.5rem" }],
        },

        fontWeight: {
            normal: "400",
            medium: "500",
            semibold: "600",
            bold: "700",
        },
    },

    // Spacing
    spacing: {
        xs: "0.25rem", // 4px
        sm: "0.5rem", // 8px
        md: "1rem", // 16px
        lg: "1.5rem", // 24px
        xl: "2rem", // 32px
        "2xl": "3rem", // 48px
        "3xl": "4rem", // 64px
    },

    // Border Radius
    borderRadius: {
        none: "0",
        sm: "0.25rem", // 4px
        md: "0.5rem", // 8px
        lg: "0.75rem", // 12px
        xl: "1rem", // 16px
        "2xl": "1.5rem", // 24px
        full: "9999px",
    },

    // Shadows
    shadows: {
        sm: "0 1px 2px 0 rgb(0 0 0 / 0.05)",
        md: "0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)",
        lg: "0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)",
        xl: "0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)",
    },

    // Transitions
    transitions: {
        fast: "150ms ease-in-out",
        normal: "200ms ease-in-out",
        slow: "300ms ease-in-out",
    },
};

// Component Variants
export const componentVariants = {
    button: {
        primary: {
            base: "inline-flex items-center justify-center font-medium transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2",
            background:
                "bg-primary-500 hover:bg-primary-600 focus:ring-primary-500",
            text: "text-white",
            shadow: "shadow-sm hover:shadow-md",
        },
        secondary: {
            base: "inline-flex items-center justify-center font-medium transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2",
            background:
                "bg-primary-50 hover:bg-primary-100 focus:ring-primary-500",
            text: "text-primary-700",
            border: "border border-primary-200 hover:border-primary-300",
            shadow: "shadow-sm hover:shadow-md",
        },
        outline: {
            base: "inline-flex items-center justify-center font-medium transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2",
            background: "bg-white hover:bg-gray-50 focus:ring-primary-500",
            text: "text-gray-700",
            border: "border border-gray-300 hover:border-gray-400",
            shadow: "shadow-sm hover:shadow-md",
        },
        ghost: {
            base: "inline-flex items-center justify-center font-medium transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2",
            background: "bg-transparent hover:bg-gray-50 focus:ring-gray-500",
            text: "text-gray-700",
        },
        danger: {
            base: "inline-flex items-center justify-center font-medium transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2",
            background: "bg-error-500 hover:bg-error-600 focus:ring-error-500",
            text: "text-white",
            shadow: "shadow-sm hover:shadow-md",
        },
    },

    card: {
        base: "bg-white rounded-xl border border-gray-200 shadow-sm",
        elevated:
            "bg-white rounded-xl border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-200",
        flat: "bg-white rounded-xl",
    },

    input: {
        base: "block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 disabled:bg-gray-50 disabled:text-gray-500",
        error: "block w-full rounded-lg border border-error-300 px-3 py-2 text-sm placeholder-gray-400 focus:border-error-500 focus:outline-none focus:ring-1 focus:ring-error-500",
    },

    badge: {
        base: "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium",
        success: "bg-success-100 text-success-800",
        warning: "bg-warning-100 text-warning-800",
        error: "bg-error-100 text-error-800",
        info: "bg-info-100 text-info-800",
        neutral: "bg-gray-100 text-gray-800",
    },
};

// Size Variants
export const sizeVariants = {
    button: {
        xs: "px-2 py-1 text-xs",
        sm: "px-3 py-1.5 text-sm",
        md: "px-4 py-2 text-sm",
        lg: "px-6 py-3 text-base",
        xl: "px-8 py-4 text-lg",
    },

    card: {
        sm: "p-4",
        md: "p-6",
        lg: "p-8",
    },

    input: {
        sm: "px-2 py-1 text-sm",
        md: "px-3 py-2 text-sm",
        lg: "px-4 py-3 text-base",
    },
};

// Utility Functions
export const getVariantClasses = (component, variant, size = "md") => {
    const variantConfig = componentVariants[component]?.[variant];
    const sizeConfig = sizeVariants[component]?.[size];

    if (!variantConfig) {
        console.warn(
            `Variant "${variant}" not found for component "${component}"`
        );
        return "";
    }

    const classes = [
        variantConfig.base,
        variantConfig.background,
        variantConfig.text,
        variantConfig.border,
        variantConfig.shadow,
        sizeConfig,
    ].filter(Boolean);

    return classes.join(" ");
};

export const getStatusColor = (status) => {
    const statusColors = {
        success: "text-success-600 bg-success-50 border-success-200",
        warning: "text-warning-600 bg-warning-50 border-warning-200",
        error: "text-error-600 bg-error-50 border-error-200",
        info: "text-info-600 bg-info-50 border-info-200",
        neutral: "text-gray-600 bg-gray-50 border-gray-200",
    };

    return statusColors[status] || statusColors.neutral;
};

export const getStatusBadgeClass = (status) => {
    const badgeClasses = {
        active: "bg-success-100 text-success-800",
        pending: "bg-warning-100 text-warning-800",
        completed: "bg-success-100 text-success-800",
        cancelled: "bg-error-100 text-error-800",
        new: "bg-info-100 text-info-800",
        contacted: "bg-warning-100 text-warning-800",
        scheduled: "bg-purple-100 text-purple-800",
        closed: "bg-gray-100 text-gray-800",
        available: "bg-success-100 text-success-800",
        reserved: "bg-warning-100 text-warning-800",
        sold: "bg-error-100 text-error-800",
        under_negotiation: "bg-blue-100 text-blue-800",
        off_market: "bg-gray-100 text-gray-800",
    };

    return badgeClasses[status] || "bg-gray-100 text-gray-800";
};

export default {
    designTokens,
    componentVariants,
    sizeVariants,
    getVariantClasses,
    getStatusColor,
    getStatusBadgeClass,
};
