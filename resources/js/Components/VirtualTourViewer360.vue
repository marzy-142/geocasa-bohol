<template>
    <div class="virtual-tour-viewer-360">
        <div ref="viewerContainer" class="viewer-container"></div>
        <div v-if="!imageUrl" class="placeholder">
            <p>No 360° image provided.</p>
        </div>
        <div v-if="loading && imageUrl && !loadError" class="loading-overlay">
            <div class="spinner" aria-label="Loading 360 image"></div>
        </div>
        <div v-if="loadError" class="error-overlay">
            <p>Failed to load 360° image.</p>
        </div>
    </div>
</template>

<script>
// To use this component, install Photo Sphere Viewer:
// npm install @photo-sphere-viewer/core
import { Viewer } from "@photo-sphere-viewer/core";
import "@photo-sphere-viewer/core/index.css";

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
            loading: false,
            loadError: false,
            viewer: null,
            _loadTimeout: null,
        };
    },
    mounted() {
        if (this.imageUrl) {
            this.initViewer();
        }
    },
    watch: {
        imageUrl(newVal) {
            if (newVal) {
                this.initViewer();
            }
        },
    },
    methods: {
        resolvePanoramaUrl(url) {
            if (!url || typeof url !== 'string') return '';
            const clean = url.trim();
            if (clean.startsWith('http://') || clean.startsWith('https://') || clean.startsWith('data:') || clean.startsWith('blob:')) {
                return clean;
            }
            // Normalize relative paths to absolute to avoid base path issues
            const origin = typeof window !== 'undefined' ? window.location.origin : '';
            return origin + '/' + clean.replace(/^\/+/, '');
        },
        initViewer() {
            if (!this.imageUrl) return;
            // Update existing viewer
            if (this.viewer) {
                try { console.debug('Loading 360 panorama:', this.imageUrl); } catch (_) {}
                this.startLoadingWatchdog();
                this.loading = true;
                this.loadError = false;
                const panoUrl = this.resolvePanoramaUrl(this.imageUrl);
                Promise.resolve(this.viewer.setPanorama(panoUrl))
                    .then(() => {
                        this.clearLoadingWatchdog();
                        this.loading = false;
                        this.loadError = false;
                    })
                    .catch((err) => {
                        this.clearLoadingWatchdog();
                        this.loading = false;
                        this.loadError = true;
                        console.error("Failed to load 360° panorama:", err);
                    });
                return;
            }

            // Create viewer first time
            try {
                console.debug('Initializing 360 viewer with:', this.imageUrl);
                this.loading = true;
                this.loadError = false;
                this.startLoadingWatchdog();
                const panoUrl = this.resolvePanoramaUrl(this.imageUrl);
                
                const viewer = new Viewer({
                    container: this.$refs.viewerContainer,
                    panorama: panoUrl,
                    navbar: ["zoom", "fullscreen"],
                });

                // Verify viewer was created successfully before binding events
                if (!viewer || typeof viewer.on !== 'function') {
                    throw new Error('Viewer initialization failed - invalid viewer instance');
                }

                this.viewer = viewer;

                // Bind lifecycle events for robust state handling
                this.viewer.on('ready', () => {
                    this.clearLoadingWatchdog();
                    this.loading = false;
                    this.loadError = false;
                });
                this.viewer.on('panorama-load-progress', () => {
                    this.loading = true;
                });
                this.viewer.on('panorama-loaded', () => {
                    this.clearLoadingWatchdog();
                    this.loading = false;
                    this.loadError = false;
                });
                this.viewer.on('panorama-load-failed', (e, err) => {
                    this.clearLoadingWatchdog();
                    this.loading = false;
                    this.loadError = true;
                    console.error('360 panorama load failed', err || e);
                });
            } catch (err) {
                console.error('Failed to initialize 360 viewer:', err);
                this.clearLoadingWatchdog();
                this.loading = false;
                this.loadError = true;
            }
        },
        startLoadingWatchdog() {
            this.clearLoadingWatchdog();
            // Failsafe: if loading takes too long, show error so UI isn't stuck
            this._loadTimeout = setTimeout(() => {
                if (this.loading) {
                    this.loading = false;
                    this.loadError = true;
                    console.error('360 panorama load timed out');
                }
            }, 15000); // 15s timeout
        },
        clearLoadingWatchdog() {
            if (this._loadTimeout) {
                clearTimeout(this._loadTimeout);
                this._loadTimeout = null;
            }
        }
    },
    beforeUnmount() {
        if (this.viewer) {
            this.viewer.destroy();
        }
        this.clearLoadingWatchdog();
    },
};
</script>

<style scoped>
.virtual-tour-viewer-360 {
    width: 100%;
    min-height: 300px;
    position: relative;
}
.viewer-container {
    width: 100%;
    height: 300px;
    background: #222;
    border-radius: 8px;
    overflow: hidden;
}
.placeholder {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #888;
    background: #f5f5f5;
    border-radius: 8px;
}
.loading-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.25);
}
.spinner {
    width: 36px;
    height: 36px;
    border: 3px solid rgba(255, 255, 255, 0.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.9s linear infinite;
}
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
.error-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #b91c1c;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #fecaca;
    border-radius: 8px;
}
</style>
