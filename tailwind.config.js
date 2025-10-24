import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: [
                    "Figtree",
                    "ui-sans-serif",
                    "system-ui",
                    "-apple-system",
                    "BlinkMacSystemFont",
                    '"Segoe UI"',
                    "Roboto",
                    '"Helvetica Neue"',
                    "Arial",
                    '"Noto Sans"',
                    "sans-serif",
                    '"Apple Color Emoji"',
                    '"Segoe UI Emoji"',
                    '"Segoe UI Symbol"',
                    '"Noto Color Emoji"',
                ],
                serif: [
                    '"Inter"',
                    "ui-sans-serif",
                    "system-ui",
                    "-apple-system",
                    "BlinkMacSystemFont",
                    '"Segoe UI"',
                    "Roboto",
                    '"Helvetica Neue"',
                    "Arial",
                    '"Noto Sans"',
                    "sans-serif",
                ],
            },
            fontSize: {
                xs: [
                    "0.75rem",
                    { lineHeight: "1rem", letterSpacing: "0.025em" },
                ],
                sm: [
                    "0.875rem",
                    { lineHeight: "1.25rem", letterSpacing: "0.025em" },
                ],
                base: ["1rem", { lineHeight: "1.5rem", letterSpacing: "0" }],
                lg: [
                    "1.125rem",
                    { lineHeight: "1.75rem", letterSpacing: "-0.025em" },
                ],
                xl: [
                    "1.25rem",
                    { lineHeight: "1.75rem", letterSpacing: "-0.025em" },
                ],
                "2xl": [
                    "1.5rem",
                    { lineHeight: "2rem", letterSpacing: "-0.025em" },
                ],
                "3xl": [
                    "1.875rem",
                    { lineHeight: "2.25rem", letterSpacing: "-0.025em" },
                ],
                "4xl": [
                    "2.25rem",
                    { lineHeight: "2.5rem", letterSpacing: "-0.025em" },
                ],
                "5xl": ["3rem", { lineHeight: "1", letterSpacing: "-0.025em" }],
                "6xl": [
                    "3.75rem",
                    { lineHeight: "1", letterSpacing: "-0.025em" },
                ],
                "7xl": [
                    "4.5rem",
                    { lineHeight: "1", letterSpacing: "-0.025em" },
                ],
                "8xl": ["6rem", { lineHeight: "1", letterSpacing: "-0.025em" }],
                "9xl": ["8rem", { lineHeight: "1", letterSpacing: "-0.025em" }],
            },
            letterSpacing: {
                tighter: "-0.05em",
                tight: "-0.025em",
                normal: "0",
                wide: "0.025em",
                wider: "0.05em",
                widest: "0.1em",
            },
            colors: {
                // Professional Real Estate Brand Colors - Trust, Growth, Balance
                primary: {
                    50: "#f0f9ff",
                    100: "#e0f2fe",
                    200: "#bae6fd",
                    300: "#7dd3fc",
                    400: "#38bdf8",
                    500: "#0ea5e9", // Primary brand color - Trust blue
                    600: "#0284c7",
                    700: "#0369a1",
                    800: "#075985",
                    900: "#0c4a6e",
                },
                accent: {
                    50: "#f0fdf4",
                    100: "#dcfce7",
                    200: "#bbf7d0",
                    300: "#86efac",
                    400: "#4ade80",
                    500: "#22c55e", // Success/positive actions - Growth green
                    600: "#16a34a",
                    700: "#15803d",
                    800: "#166534",
                    900: "#14532d",
                },
                warning: {
                    50: "#fffbeb",
                    100: "#fef3c7",
                    200: "#fde68a",
                    300: "#fcd34d",
                    400: "#fbbf24",
                    500: "#f59e0b", // Warning/attention - Balanced amber
                    600: "#d97706",
                    700: "#b45309",
                    800: "#92400e",
                    900: "#78350f",
                },
                error: {
                    50: "#fef2f2",
                    100: "#fee2e2",
                    200: "#fecaca",
                    300: "#fca5a5",
                    400: "#f87171",
                    500: "#ef4444",
                    600: "#dc2626",
                    700: "#b91c1c",
                    800: "#991b1b",
                    900: "#7f1d1d",
                },
                // Professional neutral palette
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
                // Keep slate as alias for backward compatibility
                slate: {
                    50: "#f8fafc",
                    100: "#f1f5f9",
                    200: "#e2e8f0",
                    300: "#cbd5e1",
                    400: "#94a3b8",
                    500: "#64748b",
                    600: "#475569",
                    700: "#334155",
                    800: "#1e293b",
                    900: "#0f172a",
                },
                // Real estate specific colors
                property: {
                    50: "#fef7f0",
                    100: "#fdeee0",
                    200: "#fbd9c1",
                    300: "#f8c19e",
                    400: "#f4a261",
                    500: "#e76f51", // Property accent
                    600: "#d62828",
                    700: "#ba181b",
                    800: "#a4161a",
                    900: "#8b1538",
                },
            },
            boxShadow: {
                // Professional shadow system
                soft: "0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px 0 rgba(0, 0, 0, 0.04)",
                "soft-md":
                    "0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.04)",
                "soft-lg":
                    "0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04)",
                "soft-xl":
                    "0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.04)",
                professional:
                    "0 2px 8px 0 rgba(0, 0, 0, 0.06), 0 1px 3px 0 rgba(0, 0, 0, 0.04)",
                "professional-md":
                    "0 4px 12px 0 rgba(0, 0, 0, 0.08), 0 2px 4px 0 rgba(0, 0, 0, 0.06)",
                "professional-lg":
                    "0 8px 24px 0 rgba(0, 0, 0, 0.1), 0 4px 8px 0 rgba(0, 0, 0, 0.06)",
                card: "0 1px 3px 0 rgba(0, 0, 0, 0.06), 0 1px 2px 0 rgba(0, 0, 0, 0.04)",
                "card-hover":
                    "0 4px 12px 0 rgba(0, 0, 0, 0.08), 0 2px 4px 0 rgba(0, 0, 0, 0.06)",
                modal: "0 25px 50px -12px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05)",
            },
            borderRadius: {
                // Standardized border radius system
                sm: "0.25rem", // 4px - small elements
                md: "0.375rem", // 6px - medium elements
                lg: "0.5rem", // 8px - buttons, inputs
                xl: "0.75rem", // 12px - cards, containers
                "2xl": "1rem", // 16px - large cards
                "3xl": "1.5rem", // 24px - hero sections
                full: "9999px", // fully rounded
            },
            spacing: {
                18: "4.5rem",
                88: "22rem",
            },
            animation: {
                // Professional, subtle animations
                "fade-in": "fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1)",
                "slide-up": "slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1)",
                "slide-down": "slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1)",
                "scale-in": "scaleIn 0.2s cubic-bezier(0.4, 0, 0.2, 1)",
                "slide-in-right":
                    "slideInRight 0.3s cubic-bezier(0.4, 0, 0.2, 1)",
                "slide-in-left":
                    "slideInLeft 0.3s cubic-bezier(0.4, 0, 0.2, 1)",
                "pulse-subtle": "pulseSubtle 3s ease-in-out infinite",
                "bounce-subtle": "bounceSubtle 1.5s ease-in-out infinite",
                shimmer: "shimmer 2s ease-in-out infinite",
                float: "float 6s ease-in-out infinite",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
                slideUp: {
                    "0%": { transform: "translateY(8px)", opacity: "0" },
                    "100%": { transform: "translateY(0)", opacity: "1" },
                },
                slideDown: {
                    "0%": { transform: "translateY(-8px)", opacity: "0" },
                    "100%": { transform: "translateY(0)", opacity: "1" },
                },
                slideInRight: {
                    "0%": { transform: "translateX(8px)", opacity: "0" },
                    "100%": { transform: "translateX(0)", opacity: "1" },
                },
                slideInLeft: {
                    "0%": { transform: "translateX(-8px)", opacity: "0" },
                    "100%": { transform: "translateX(0)", opacity: "1" },
                },
                scaleIn: {
                    "0%": { transform: "scale(0.96)", opacity: "0" },
                    "100%": { transform: "scale(1)", opacity: "1" },
                },
                pulseSubtle: {
                    "0%, 100%": { opacity: "1" },
                    "50%": { opacity: "0.85" },
                },
                bounceSubtle: {
                    "0%, 100%": { transform: "translateY(0)" },
                    "50%": { transform: "translateY(-1px)" },
                },
                shimmer: {
                    "0%": { transform: "translateX(-100%)" },
                    "100%": { transform: "translateX(100%)" },
                },
                float: {
                    "0%, 100%": { transform: "translateY(0px)" },
                    "50%": { transform: "translateY(-3px)" },
                },
            },
        },
    },

    plugins: [forms],
};
