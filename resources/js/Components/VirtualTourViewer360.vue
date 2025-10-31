<template>
    <div class="virtual-tour-viewer">
        <div class="image-viewer-container">
            <div v-if="!imageUrl" class="no-image-state">
                <svg class="w-20 h-20 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-lg font-medium text-gray-600">No virtual tour available</p>
                <p class="text-sm text-gray-500 mt-2">This property doesn't have a 360° tour yet</p>
            </div>

            <div v-else-if="loading" class="loading-state">
                <div class="spinner"></div>
                <p class="loading-text">Loading virtual tour...</p>
            </div>

            <div v-else-if="loadError" class="error-state">
                <svg class="w-16 h-16 text-red-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="error-title">Unable to load tour</p>
                <p class="error-message">Please try refreshing the page</p>
                <button @click="retryLoad" class="retry-btn">Try Again</button>
            </div>

            <div v-else class="image-display">
                <div 
                    ref="imageContainer"
                    class="image-container"
                    @mousedown="startDrag"
                    @mousemove="onDrag"
                    @mouseup="endDrag"
                    @mouseleave="endDrag"
                    @wheel="onWheel"
                    @touchstart="handleTouchStart"
                    @touchmove="handleTouchMove"
                    @touchend="handleTouchEnd"
                >
                    <img
                        :src="resolvedImageUrl"
                        alt="Property Virtual Tour"
                        class="tour-image"
                        :style="imageStyle"
                        @load="onImageLoad"
                        @error="onImageError"
                        draggable="false"
                    />
                </div>

                <div class="controls-overlay">
                    <div class="control-buttons">
                        <button @click="zoomIn" class="control-btn" title="Zoom In">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                            </svg>
                        </button>
                        <button @click="zoomOut" class="control-btn" title="Zoom Out">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM7 10h6"></path>
                            </svg>
                        </button>
                        <button @click="resetView" class="control-btn" title="Reset View">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div v-if="showHelp" class="help-tooltip">
                    <button @click="showHelp = false" class="help-close">✕</button>
                    <div class="help-content">
                        <p class="help-text">
                            <strong>👆 Drag</strong> to pan • <strong>🔍 Scroll</strong> to zoom
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
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
            scale: 1,
            translateX: 0,
            translateY: 0,
            isDragging: false,
            dragStartX: 0,
            dragStartY: 0,
            initialPinchDistance: null,
            initialScale: 1,
        };
    },
    computed: {
        resolvedImageUrl() {
            if (!this.imageUrl) return '';
            const clean = this.imageUrl.trim();
            
            if (clean.startsWith('http://') || clean.startsWith('https://')) {
                return clean;
            }
            
            if (clean.startsWith('/storage/')) {
                return clean;
            }
            
            const cleanPath = clean.replace(/^\/+/, '');
            if (cleanPath.includes('properties/virtual-tours/')) {
                return `/storage/${cleanPath}`;
            }
            
            return `/storage/properties/virtual-tours/${cleanPath}`;
        },
        imageStyle() {
            return {
                transform: `scale(${this.scale}) translate(${this.translateX}px, ${this.translateY}px)`,
                transition: this.isDragging ? 'none' : 'transform 0.3s ease-out',
            };
        },
    },
    mounted() {
        if (this.imageUrl) {
            this.loading = true;
            // Fallback timeout in case image never loads or errors
            setTimeout(() => {
                if (this.loading) {
                    this.loadError = true;
                    this.loading = false;
                }
            }, 5000); // 5 second timeout
        } else {
            this.loading = false;
        }
    },
    methods: {
        onImageLoad() {
            this.loading = false;
            this.loadError = false;
        },
        onImageError(e) {
            this.loading = false;
            this.loadError = true;
        },
        retryLoad() {
            this.loadError = false;
            this.loading = true;
            this.$nextTick(() => {
                const img = this.$el.querySelector('.tour-image');
                if (img) {
                    img.src = this.resolvedImageUrl;
                }
            });
        },
        zoomIn() {
            this.scale = Math.min(this.scale + 0.3, 4);
            this.showHelp = false;
        },
        zoomOut() {
            this.scale = Math.max(this.scale - 0.3, 1);
            if (this.scale === 1) {
                this.translateX = 0;
                this.translateY = 0;
            }
        },
        resetView() {
            this.scale = 1;
            this.translateX = 0;
            this.translateY = 0;
            this.showHelp = false;
        },
        startDrag(e) {
            if (this.scale <= 1) return;
            this.isDragging = true;
            this.dragStartX = e.clientX - this.translateX;
            this.dragStartY = e.clientY - this.translateY;
            this.showHelp = false;
        },
        onDrag(e) {
            if (!this.isDragging) return;
            e.preventDefault();
            this.translateX = e.clientX - this.dragStartX;
            this.translateY = e.clientY - this.dragStartY;
        },
        endDrag() {
            this.isDragging = false;
        },
        onWheel(e) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -0.1 : 0.1;
            const newScale = Math.max(1, Math.min(4, this.scale + delta));
            
            if (newScale === 1) {
                this.scale = 1;
                this.translateX = 0;
                this.translateY = 0;
            } else {
                this.scale = newScale;
            }
            this.showHelp = false;
        },
        handleTouchStart(e) {
            if (e.touches.length === 2) {
                this.initialPinchDistance = this.getPinchDistance(e.touches);
                this.initialScale = this.scale;
            } else if (e.touches.length === 1 && this.scale > 1) {
                this.isDragging = true;
                this.dragStartX = e.touches[0].clientX - this.translateX;
                this.dragStartY = e.touches[0].clientY - this.translateY;
            }
            this.showHelp = false;
        },
        handleTouchMove(e) {
            e.preventDefault();
            if (e.touches.length === 2 && this.initialPinchDistance) {
                const currentDistance = this.getPinchDistance(e.touches);
                const scaleChange = currentDistance / this.initialPinchDistance;
                this.scale = Math.max(1, Math.min(4, this.initialScale * scaleChange));
            } else if (e.touches.length === 1 && this.isDragging) {
                this.translateX = e.touches[0].clientX - this.dragStartX;
                this.translateY = e.touches[0].clientY - this.dragStartY;
            }
        },
        handleTouchEnd() {
            this.isDragging = false;
            this.initialPinchDistance = null;
            if (this.scale === 1) {
                this.translateX = 0;
                this.translateY = 0;
            }
        },
        getPinchDistance(touches) {
            const dx = touches[0].clientX - touches[1].clientX;
            const dy = touches[0].clientY - touches[1].clientY;
            return Math.sqrt(dx * dx + dy * dy);
        },
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

.image-container {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #1f2937;
    cursor: grab;
}

.image-container:active {
    cursor: grabbing;
}

.tour-image {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    user-select: none;
    pointer-events: none;
}

.controls-overlay {
    position: absolute;
    right: 1.5rem;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
}

.control-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    background: white;
    padding: 0.75rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.control-btn {
    width: 48px;
    height: 48px;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: #4b5563;
}

.control-btn:hover {
    background: #667eea;
    border-color: #667eea;
    color: white;
    transform: scale(1.05);
}

.control-btn:active {
    transform: scale(0.95);
}

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
    to { transform: rotate(360deg); }
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
    
    .controls-overlay {
        right: 1rem;
    }
    
    .control-btn {
        width: 40px;
        height: 40px;
    }
}
</style>