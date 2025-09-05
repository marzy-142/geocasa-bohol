import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Laravel Reverb Configuration
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

// Get CSRF token safely with fallback
const getCSRFToken = () => {
    return (
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") ||
        document.querySelector('input[name="_token"]')?.value ||
        window.Laravel?.csrfToken ||
        ""
    );
};

const csrfToken = getCSRFToken();

if (!csrfToken) {
    console.warn("⚠️ CSRF token not found. WebSocket authentication may fail.");
}

// Check if required environment variables are available
if (!import.meta.env.VITE_REVERB_APP_KEY) {
    console.error(
        "❌ VITE_REVERB_APP_KEY is not defined. Please check your .env file."
    );
}

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "http") === "https",
    enabledTransports: ["ws", "wss"],
    auth: {
        headers: {
            "X-CSRF-TOKEN": csrfToken,
            Accept: "application/json",
            "Content-Type": "application/json",
        },
    },
    authEndpoint: "/broadcasting/auth",
    // Add retry logic for failed connections
    cluster: import.meta.env.VITE_REVERB_APP_CLUSTER,
});

// Enhanced connection event listeners
window.Echo.connector.pusher.connection.bind("connected", () => {
    console.log("✅ Reverb connected successfully");
});

window.Echo.connector.pusher.connection.bind("error", (error) => {
    console.error("❌ Reverb connection error:", error);
    // Log additional debugging info
    console.log("CSRF Token:", csrfToken ? "Present" : "Missing");
    console.log("Auth endpoint:", "/broadcasting/auth");
});

window.Echo.connector.pusher.connection.bind("disconnected", () => {
    console.log("🔌 Reverb disconnected");
});

window.Echo.connector.pusher.connection.bind("unavailable", () => {
    console.error("❌ Reverb connection unavailable");
});

// Add authentication error handling
window.Echo.connector.pusher.connection.bind("pusher:error", (error) => {
    if (error.error && error.error.data && error.error.data.code === 4004) {
        console.error(
            "❌ WebSocket authentication failed (403). Check if user is logged in and CSRF token is valid."
        );
    }
});

console.log("🚀 Bootstrap loaded with Laravel Reverb real-time features");
