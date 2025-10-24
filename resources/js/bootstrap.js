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
console.log("🔍 Environment variables check:", {
    VITE_REVERB_APP_KEY: import.meta.env.VITE_REVERB_APP_KEY
        ? "✅ Set"
        : "❌ Missing",
    VITE_REVERB_HOST: import.meta.env.VITE_REVERB_HOST
        ? "✅ Set"
        : "❌ Missing",
    VITE_REVERB_PORT: import.meta.env.VITE_REVERB_PORT
        ? "✅ Set"
        : "❌ Missing",
    VITE_REVERB_SCHEME: import.meta.env.VITE_REVERB_SCHEME
        ? "✅ Set"
        : "❌ Missing",
});

if (!import.meta.env.VITE_REVERB_APP_KEY) {
    console.error(
        "❌ VITE_REVERB_APP_KEY is not defined. Real-time features disabled."
    );
    window.Echo = null;
} else {
    // Initialize Echo with error handling
    try {
        window.Echo = new Echo({
            broadcaster: "reverb",
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST || "127.0.0.1",
            wsPort: parseInt(import.meta.env.VITE_REVERB_PORT) || 8080,
            wssPort: parseInt(import.meta.env.VITE_REVERB_PORT) || 8080,
            forceTLS:
                (import.meta.env.VITE_REVERB_SCHEME || "http") === "https",
            enabledTransports: ["ws", "wss"],
            auth: {
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            },
            authEndpoint: "/broadcasting/auth",
            cluster: import.meta.env.VITE_REVERB_APP_CLUSTER || "mt1",
            disableStats: true,
            enableLogging: true,
        });

        console.log("✅ Echo initialized successfully");
    } catch (error) {
        console.error("❌ Failed to initialize Echo:", error);
        window.Echo = null;
    }
}

// Add debugging for Echo connection (only if Echo is available)
if (window.Echo) {
    window.Echo.connector.pusher.connection.bind("connected", () => {
        console.log("✅ Echo WebSocket connected successfully");
    });

    window.Echo.connector.pusher.connection.bind("error", (error) => {
        console.error("❌ Echo WebSocket connection error:", error);
        console.error("🔍 Error details:", {
            type: error.type,
            error: error.error,
            data: error.error?.data,
            code: error.error?.data?.code,
            message: error.error?.data?.message,
        });
        // If WebSocket fails, disable Echo to prevent further errors
        if (error.type === "WebSocketError") {
            console.warn(
                "⚠️ WebSocket connection failed, disabling real-time features"
            );
            window.Echo.disconnect();
            window.Echo = null;
        }

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
}

console.log("🚀 Bootstrap loaded with Laravel Reverb real-time features");
