import { vi } from "vitest";
import "@testing-library/jest-dom";

// Mock browser APIs
Object.defineProperty(window, "Notification", {
    value: vi.fn().mockImplementation(() => ({
        close: vi.fn(),
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
    })),
    configurable: true,
});

// Mock Notification static properties
Object.defineProperty(window.Notification, "permission", {
    value: "granted",
    configurable: true,
});

Object.defineProperty(window.Notification, "requestPermission", {
    value: vi.fn().mockResolvedValue("granted"),
    configurable: true,
});

// Mock other browser APIs that might be needed
Object.defineProperty(window, "matchMedia", {
    value: vi.fn().mockImplementation((query) => ({
        matches: false,
        media: query,
        onchange: null,
        addListener: vi.fn(),
        removeListener: vi.fn(),
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
        dispatchEvent: vi.fn(),
    })),
    configurable: true,
});

// Mock performance API
Object.defineProperty(global, "performance", {
    value: {
        now: vi.fn(() => Date.now()),
        mark: vi.fn(),
        measure: vi.fn(),
        getEntriesByType: vi.fn(() => []),
        memory: {
            usedJSHeapSize: 1000000,
            totalJSHeapSize: 2000000,
            jsHeapSizeLimit: 4000000,
        },
    },
    configurable: true,
});

// Mock fetch globally
global.fetch = vi.fn().mockResolvedValue({
    json: () => Promise.resolve([]),
});

// Mock ResizeObserver
global.ResizeObserver = vi.fn().mockImplementation(() => ({
    observe: vi.fn(),
    unobserve: vi.fn(),
    disconnect: vi.fn(),
}));

// Mock IntersectionObserver
global.IntersectionObserver = vi.fn().mockImplementation(() => ({
    observe: vi.fn(),
    unobserve: vi.fn(),
    disconnect: vi.fn(),
}));

// Mock URL and URLSearchParams
if (typeof URL === "undefined") {
    global.URL = class URL {
        constructor(url) {
            this.href = url;
            this.searchParams = new URLSearchParams(url.split("?")[1] || "");
        }
    };
}

if (typeof URLSearchParams === "undefined") {
    global.URLSearchParams = class URLSearchParams {
        constructor(search = "") {
            this.params = new Map();
            if (search) {
                search.split("&").forEach((param) => {
                    const [key, value] = param.split("=");
                    if (key)
                        this.params.set(
                            decodeURIComponent(key),
                            decodeURIComponent(value || "")
                        );
                });
            }
        }

        get(key) {
            return this.params.get(key) || null;
        }

        set(key, value) {
            this.params.set(key, value);
        }
    };
}

// Mock localStorage
Object.defineProperty(window, "localStorage", {
    value: {
        getItem: vi.fn(),
        setItem: vi.fn(),
        removeItem: vi.fn(),
        clear: vi.fn(),
    },
    configurable: true,
});

// Mock sessionStorage
Object.defineProperty(window, "sessionStorage", {
    value: {
        getItem: vi.fn(),
        setItem: vi.fn(),
        removeItem: vi.fn(),
        clear: vi.fn(),
    },
    writable: true,
});

// Mock Laravel Echo
Object.defineProperty(window, "Echo", {
    value: {
        private: vi.fn(() => ({
            listen: vi.fn(),
            stopListening: vi.fn(),
            leave: vi.fn(),
        })),
        channel: vi.fn(() => ({
            listen: vi.fn(),
            stopListening: vi.fn(),
            leave: vi.fn(),
        })),
        leave: vi.fn(),
    },
    writable: true,
});

// Mock HTMLDialogElement for Modal component
class MockHTMLDialogElement extends HTMLElement {
    constructor() {
        super();
        this.open = false;
    }

    showModal() {
        this.open = true;
    }

    close() {
        this.open = false;
    }
}

if (typeof window !== "undefined" && !window.HTMLDialogElement) {
    window.HTMLDialogElement = MockHTMLDialogElement;
}

// Mock Ziggy route helper
global.route = vi.fn((name, params) => {
    const routes = {
        "conversations.create-inquiry": "/conversations.create-inquiry",
        "conversations.create-transaction": "/conversations.create-transaction",
        "conversations.mark-read": "/conversations/mark-read",
        "conversations.show": "/conversations/1",
        "client.dashboard": "/client/dashboard",
        "broker.dashboard": "/broker/dashboard",
    };

    if (name) {
        return routes[name] || "/test-route";
    }

    // Return route object with current method when called without parameters
    return {
        current: vi.fn((pattern) => {
            if (pattern === "*.dashboard") {
                return true;
            }
            return false;
        }),
    };
});

// Mock Inertia.js with complete implementation
const mockPage = {
    props: {
        auth: { user: null },
        flash: {},
        errors: {},
    },
    url: "/test",
    component: "TestComponent",
    version: "1.0.0",
};

global.usePage = vi.fn(() => mockPage);

// Mock router with proper implementation
global.router = {
    visit: vi.fn(),
    get: vi.fn(),
    post: vi.fn((url, data, options) => {
        // Simulate successful response
        if (options && options.onSuccess) {
            options.onSuccess({ props: { conversation: { id: 1 } } });
        }
        return Promise.resolve();
    }),
    put: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
    reload: vi.fn(),
    replace: vi.fn(),
    remember: vi.fn(),
    restore: vi.fn(),
};

// Mock Inertia module
vi.mock("@inertiajs/vue3", () => ({
    router: global.router,
    usePage: global.usePage,
    useForm: vi.fn(() => ({
        data: {},
        errors: {},
        hasErrors: false,
        processing: false,
        progress: null,
        wasSuccessful: false,
        recentlySuccessful: false,
        post: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
        get: vi.fn(),
        submit: vi.fn(),
        reset: vi.fn(),
        clearErrors: vi.fn(),
        setError: vi.fn(),
        transform: vi.fn(),
        defaults: vi.fn(),
        cancel: vi.fn(),
    })),
    Head: { template: "<head></head>" },
    Link: { template: "<a></a>" },
}));
