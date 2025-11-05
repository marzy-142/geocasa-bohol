<template>
    <div class="virtual-tour-viewer">
        <div class="image-viewer-container">
            <div v-if="!imageUrl" class="no-image-state">
                <svg
                    class="w-20 h-20 text-gray-300 mb-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    ></path>
                </svg>
                <p class="text-lg font-medium text-gray-600">
                    No virtual tour available
                </p>
                <p class="text-sm text-gray-500 mt-2">
                    This property doesn't have a 360° tour yet
                </p>
            </div>

            <div v-else class="image-display relative">
                <div ref="psvContainer" class="psv-container"></div>

                <!-- Loading overlay -->
                <div
                    v-if="loading"
                    class="loading-state absolute inset-0 flex flex-col items-center justify-center bg-white/70"
                >
                    <div class="spinner"></div>
                    <p class="loading-text">Loading virtual tour...</p>
                </div>

                <!-- Error overlay -->
                <div
                    v-if="loadError"
                    class="error-state absolute inset-0 flex flex-col items-center justify-center bg-white"
                >
                    <svg
                        class="w-16 h-16 text-red-400 mb-3"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        ></path>
                    </svg>
                    <p class="error-title">Unable to load tour</p>
                    <p class="error-message">Please try again</p>
                    <button @click="retryLoad" class="retry-btn">
                        Try Again
                    </button>
                </div>
                <div v-if="showHelp" class="help-tooltip">
                    <button @click="showHelp = false" class="help-close">
                        ✕
                    </button>
                    <div class="help-content">
                        <p class="help-text">
                            <strong>Drag</strong> to look around •
                            <strong>Scroll / pinch</strong> to zoom
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Viewer } from "@photo-sphere-viewer/core";
import "@photo-sphere-viewer/core/index.css";
import { AutorotatePlugin } from "@photo-sphere-viewer/autorotate-plugin";
import { GyroscopePlugin } from "@photo-sphere-viewer/gyroscope-plugin";
export default {
    name: "VirtualTourViewer360",
    props: {
        imageUrl: {
            type: String,
            default: "",
        },
    },
    data() {
        return {
            loading: true,
            loadError: false,
            showHelp: true,
            viewer: null,
            _loadTimeoutId: null,
        };
    },
    computed: {
        resolvedImageUrl() {
            if (!this.imageUrl) return "";
            const clean = this.imageUrl.trim();

            if (clean.startsWith("http://") || clean.startsWith("https://")) {
                return clean;
            }

            if (clean.startsWith("/storage/")) {
                return clean;
            }

            const cleanPath = clean.replace(/^\/+/, "");
            if (cleanPath.includes("properties/virtual-tours/")) {
                return `/storage/${cleanPath}`;
            }

            return `/storage/properties/virtual-tours/${cleanPath}`;
        },
    },
    mounted() {
        // Delay initialization to ensure DOM is fully ready
        setTimeout(() => {
            this.beginLoad();
        }, 100);
    },
    watch: {
        imageUrl() {
            this.beginLoad();
        },
    },
    methods: {
        beginLoad() {
            if (!this.imageUrl) {
                this.loading = false;
                this.loadError = false;
                return;
            }
            this.loading = true;
            this.loadError = false;
            if (this._loadTimeoutId) {
                clearTimeout(this._loadTimeoutId);
            }
            // Set a reasonable timeout for panorama loading
            this._loadTimeoutId = setTimeout(() => {
                if (this.loading) {
                    this.loadError = true;
                    this.loading = false;
                }
            }, 15000); // 15s timeout

            // Wait a moment for DOM to be ready
            this.$nextTick(() => {
                // Verify container exists and is visible
                if (!this.$refs.psvContainer) {
                    console.error("PSV container not found in DOM");
                    this.onImageError();
                    return;
                }

                const containerRect =
                    this.$refs.psvContainer.getBoundingClientRect();
                if (containerRect.width === 0 || containerRect.height === 0) {
                    console.warn(
                        "PSV container has zero dimensions, retrying..."
                    );
                    // Retry after a short delay
                    setTimeout(() => this.beginLoad(), 200);
                    return;
                }

                const url = this.resolvedImageUrl;
                console.log("Initializing viewer with URL:", url);

                try {
                    if (this.viewer) {
                        this.viewer
                            .setPanorama(url, { showLoader: true })
                            .then(() => {
                                this.onImageLoad();
                            })
                            .catch((err) => {
                                console.error("setPanorama error:", err);
                                this.onImageError();
                            });
                    } else {
                        this.viewer = new Viewer({
                            container: this.$refs.psvContainer,
                            panorama: url,
                            touchmoveTwoFingers: false,
                            mousewheel: true,
                            keyboard: true,
                            defaultYaw: 0,
                            defaultPitch: 0,
                            defaultZoomLvl: 0,
                            minFov: 30,
                            maxFov: 90,
                            fisheye: false,
                            navbar: [
                                "zoom",
                                "gyroscope",
                                "autorotate",
                                "fullscreen",
                            ],
                            plugins: [
                                [
                                    AutorotatePlugin,
                                    {
                                        autostartDelay: 2000,
                                        autostartOnIdle: true,
                                    },
                                ],
                                [GyroscopePlugin, { absolutePosition: true }],
                            ],
                        });
                        // PSV v5.x uses addEventListener for events
                        this.viewer.addEventListener("ready", this.onImageLoad);
                        this.viewer.addEventListener(
                            "panorama-load-failed",
                            (e) => {
                                console.error("Panorama load failed:", e);
                                this.onImageError(e);
                            }
                        );
                    }
                } catch (e) {
                    console.error("Viewer initialization error:", e);
                    this.onImageError();
                }
            });
        },
        onImageLoad() {
            this.loading = false;
            this.loadError = false;
            if (this._loadTimeoutId) {
                clearTimeout(this._loadTimeoutId);
                this._loadTimeoutId = null;
            }
            // Auto-dismiss help tooltip after 5 seconds
            setTimeout(() => {
                this.showHelp = false;
            }, 5000);
        },
        onImageError(e) {
            this.loading = false;
            this.loadError = true;
            if (this._loadTimeoutId) {
                clearTimeout(this._loadTimeoutId);
                this._loadTimeoutId = null;
            }
        },
        retryLoad() {
            this.loadError = false;
            this.loading = true;
            this.beginLoad();
        },
    },
    beforeUnmount() {
        if (this.viewer) {
            try {
                this.viewer.destroy();
            } catch (_) {}
            this.viewer = null;
        }
    },
};
</script>

<style scoped>
.virtual-tour-viewer {
    width: 100%;
    min-height: 600px;
    position: relative;
}

.image-viewer-container {
    width: 100%;
    height: 600px;
    background: #f3f4f6;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
}

.no-image-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    text-align: center;
    padding: 3rem;
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    z-index: 20;
}

.spinner {
    width: 56px;
    height: 56px;
    border: 5px solid rgba(255, 255, 255, 0.2);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.loading-text {
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    margin-top: 1.5rem;
}

.error-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    text-align: center;
    padding: 3rem;
    z-index: 20;
}

.error-title {
    color: #dc2626;
    font-weight: 700;
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
}

.error-message {
    color: #6b7280;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
}

.retry-btn {
    background: #667eea;
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.retry-btn:hover {
    background: #5568d3;
    transform: translateY(-1px);
}

.image-display {
    width: 100%;
    height: 100%;
    position: relative;
}

.psv-container {
    width: 100%;
    height: 100%;
    min-height: 600px;
    background: #000;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

/* Photo Sphere Viewer fills the container; custom buttons removed */

.help-tooltip {
    position: absolute;
    top: 1.5rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 15;
    animation: slideDown 0.3s ease-out;
}

.help-content {
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(12px);
    color: white;
    padding: 1rem 1.5rem;
    padding-right: 3rem;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    position: relative;
}

.help-text {
    font-size: 0.95rem;
    font-weight: 500;
    white-space: nowrap;
}

.help-close {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    transition: all 0.2s;
}

.help-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}

@media (max-width: 768px) {
    .image-viewer-container {
        height: 400px;
    }

    .psv-container {
        min-height: 400px;
    }

    .help-tooltip {
        top: 1rem;
        left: 1rem;
        right: 1rem;
        transform: none;
    }

    .help-content {
        padding-right: 2.5rem;
    }
}
</style>
