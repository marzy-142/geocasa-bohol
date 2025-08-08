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
                    '"Crimson Pro"',
                    "ui-serif",
                    "Georgia",
                    "Cambria",
                    '"Times New Roman"',
                    "Times",
                    "serif",
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
                // GeoCasa Bohol Brand Colors
                primary: {
                    50: "#f0f9ff",
                    100: "#e0f2fe",
                    200: "#bae6fd",
                    300: "#7dd3fc",
                    400: "#38bdf8",
                    500: "#0077b6", // Ocean blue
                    600: "#0369a1",
                    700: "#0284c7",
                    800: "#075985",
                    900: "#0c4a6e",
                },
                accent: {
                    50: "#f0fdf4",
                    100: "#dcfce7",
                    200: "#bbf7d0",
                    300: "#86efac",
                    400: "#4ade80",
                    500: "#00a86b", // Tropical green
                    600: "#16a34a",
                    700: "#15803d",
                    800: "#166534",
                    900: "#14532d",
                },
                coconut: {
                    50: "#fefefe",
                    100: "#faf9f6",
                    200: "#f5f4f0",
                    300: "#f0ede8",
                    400: "#e8e3db",
                    500: "#d4c5b0", // Coconut brown
                    600: "#c4b299",
                    700: "#b09d82",
                    800: "#9c886b",
                    900: "#8a7354",
                },
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
            },
            boxShadow: {
                soft: "0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)",
                "soft-md":
                    "0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)",
                "soft-lg":
                    "0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)",
                "soft-xl":
                    "0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)",
            },
            borderRadius: {
                xl: "0.75rem",
                "2xl": "1rem",
                "3xl": "1.5rem",
            },
            spacing: {
                18: "4.5rem",
                88: "22rem",
            },
            animation: {
                "fade-in": "fadeIn 0.5s ease-in-out",
                "slide-up": "slideUp 0.3s ease-out",
                "scale-in": "scaleIn 0.2s ease-out",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
                slideUp: {
                    "0%": { transform: "translateY(10px)", opacity: "0" },
                    "100%": { transform: "translateY(0)", opacity: "1" },
                },
                scaleIn: {
                    "0%": { transform: "scale(0.95)", opacity: "0" },
                    "100%": { transform: "scale(1)", opacity: "1" },
                },
            },
        },
    },

    plugins: [forms],
};
