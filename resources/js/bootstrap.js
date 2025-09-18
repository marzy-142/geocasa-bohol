import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Laravel Reverb Configuration
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

// Get CSRF token safely with fallback
const getCSRFToken = () => {
    const metaToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
    const inputToken = document.querySelector('input[name="_token"]')?.value;
    const laravelToken = window.Laravel?.csrfToken;

    console.log("CSRF Token sources:", {
        metaToken: metaToken ? "found" : "not found",
        inputToken: inputToken ? "found" : "not found",
        laravelToken: laravelToken ? "found" : "not found",
    });

    return metaToken || inputToken || laravelToken || "";
};

const csrfToken = getCSRFToken();

if (!csrfToken) {
    console.warn("⚠️ CSRF token not found. WebSocket authentication may fail.");
} else {
    console.log("✅ CSRF token found for broadcasting auth");
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
    cluster: import.meta.env.VITE_REVERB_APP_CLUSTER,
});

// Add debugging for Echo connection
window.Echo.connector.pusher.connection.bind("connected", () => {
    console.log("✅ Echo WebSocket connected successfully");
});

window.Echo.connector.pusher.connection.bind("error", (error) => {
    console.error("❌ Echo WebSocket connection error:", error);
    if (error.error && error.error.data && error.error.data.code === 4004) {
        console.error(
            "🔐 Authentication failed - check CSRF token and user session"
        );
    }
});

window.Echo.connector.pusher.connection.bind("disconnected", () => {
    console.warn("⚠️ Echo WebSocket disconnected");
});

window.Echo.connector.pusher.connection.bind("unavailable", () => {
    console.warn("⚠️ Echo WebSocket unavailable");
});

// Handle authentication errors specifically
window.Echo.connector.pusher.connection.bind("pusher:error", (error) => {
    console.error("❌ Pusher error:", error);
    if (error.data && error.data.code === 4004) {
        console.error(
            "🔐 Broadcasting authentication failed - user may not be logged in or CSRF token is invalid"
        );
    }
});

console.log("🚀 Bootstrap loaded with Laravel Reverb real-time features");
