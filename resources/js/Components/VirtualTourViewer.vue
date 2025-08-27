<template>
    <div class="virtual-tour-viewer" ref="viewerContainer">
        <!-- Loading State -->
        <div v-if="loading" class="loading-overlay">
            <div class="loading-spinner"></div>
            <p class="loading-text">Loading Virtual Tour...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-state">
            <div class="error-icon">⚠️</div>
            <p class="error-message">{{ error }}</p>
            <button @click="retryLoad" class="retry-button">Retry</button>
        </div>

        <!-- Virtual Tour Display -->
        <div v-else class="tour-display">
            <!-- Main 360 Image Container -->
            <div class="panorama-container" ref="panoramaContainer">
                <img 
                    ref="panoramaImage"
                    :src="currentImage"
                    class="panorama-image"
                    @load="onImageLoad"
                    @error="onImageError"
                    @mousedown="startDrag"
                    @mousemove="onDrag"
                    @mouseup="endDrag"
                    @mouseleave="endDrag"
                    @wheel="onZoom"
                    draggable="false"
                />
                
                <!-- Hotspots -->
                <div 
                    v-for="hotspot in currentHotspots" 
                    :key="hotspot.id"
                    class="hotspot"
                    :style="getHotspotStyle(hotspot)"
                    @click="onHotspotClick(hotspot)"
                    :title="hotspot.title"
                >
                    <div class="hotspot-pulse"></div>
                    <div class="hotspot-icon">📍</div>
                </div>
            </div>

            <!-- Navigation Controls -->
            <div class="tour-controls">
                <!-- Image Navigation -->
                <div class="image-navigation" v-if="tourImages.length > 1">
                    <button 
                        v-for="(image, index) in tourImages" 
                        :key="index"
                        @click="switchImage(index)"
                        :class="['nav-thumb', { active: currentImageIndex === index }]"
                        :title="`View ${index + 1}`"
                    >
                        <img :src="image.thumbnail || image.url" :alt="`View ${index + 1}`" />
                    </button>
                </div>

                <!-- Zoom Controls -->
                <div class="zoom-controls">
                    <button @click="zoomIn" class="zoom-btn" title="Zoom In">🔍+</button>
                    <button @click="zoomOut" class="zoom-btn" title="Zoom Out">🔍-</button>
                    <button @click="resetView" class="zoom-btn" title="Reset View">🏠</button>
                </div>

                <!-- Fullscreen Toggle -->
                <button @click="toggleFullscreen" class="fullscreen-btn" title="Toggle Fullscreen">
                    {{ isFullscreen ? '🗗' : '🗖' }}
                </button>
            </div>
        </div>

        <!-- Hotspot Info Modal -->
        <Modal :show="showHotspotModal" @close="closeHotspotModal">
            <div class="hotspot-modal-content">
                <h3>{{ selectedHotspot?.title }}</h3>
                <p>{{ selectedHotspot?.description }}</p>
                <div v-if="selectedHotspot?.image" class="hotspot-image">
                    <img :src="selectedHotspot.image" :alt="selectedHotspot.title" />
                </div>
                <div class="modal-actions">
                    <SecondaryButton @click="closeHotspotModal">Close</SecondaryButton>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
import Modal from './Modal.vue';
import SecondaryButton from './SecondaryButton.vue';

export default {
    name: 'VirtualTourViewer',
    components: {
        Modal,
        SecondaryButton
    },
    props: {
        tourImages: {
            type: Array,
            required: true,
            default: () => []
        },
        hotspots: {
            type: Array,
            default: () => []
        },
        autoRotate: {
            type: Boolean,
            default: false
        },
        rotationSpeed: {
            type: Number,
            default: 0.5
        }
    },
    data() {
        return {
            loading: true,
            error: null,
            currentImageIndex: 0,
            isDragging: false,
            dragStart: { x: 0, y: 0 },
            rotation: { x: 0, y: 0 },
            zoom: 1,
            minZoom: 0.5,
            maxZoom: 3,
            isFullscreen: false,
            showHotspotModal: false,
            selectedHotspot: null,
            autoRotateInterval: null
        };
    },
    computed: {
        currentImage() {
            return this.tourImages[this.currentImageIndex]?.url || '';
        },
        currentHotspots() {
            return this.hotspots.filter(hotspot => 
                hotspot.image_index === this.currentImageIndex
            );
        }
    },
    mounted() {
        this.initializeViewer();
        if (this.autoRotate) {
            this.startAutoRotation();
        }
    },
    beforeUnmount() {
        this.stopAutoRotation();
        document.removeEventListener('fullscreenchange', this.onFullscreenChange);
    },
    methods: {
        initializeViewer() {
            if (this.tourImages.length === 0) {
                this.error = 'No virtual tour images available';
                this.loading = false;
                return;
            }
            
            document.addEventListener('fullscreenchange', this.onFullscreenChange);
            this.loading = false;
        },
        
        onImageLoad() {
            this.loading = false;
            this.error = null;
        },
        
        onImageError() {
            this.error = 'Failed to load virtual tour image';
            this.loading = false;
        },
        
        retryLoad() {
            this.error = null;
            this.loading = true;
            // Force image reload
            this.$refs.panoramaImage.src = this.currentImage + '?t=' + Date.now();
        },
        
        startDrag(event) {
            this.isDragging = true;
            this.dragStart = { x: event.clientX, y: event.clientY };
            this.stopAutoRotation();
        },
        
        onDrag(event) {
            if (!this.isDragging) return;
            
            const deltaX = event.clientX - this.dragStart.x;
            const deltaY = event.clientY - this.dragStart.y;
            
            this.rotation.x += deltaY * 0.5;
            this.rotation.y += deltaX * 0.5;
            
            // Limit vertical rotation
            this.rotation.x = Math.max(-90, Math.min(90, this.rotation.x));
            
            this.updateImageTransform();
            this.dragStart = { x: event.clientX, y: event.clientY };
        },
        
        endDrag() {
            this.isDragging = false;
            if (this.autoRotate) {
                this.startAutoRotation();
            }
        },
        
        onZoom(event) {
            event.preventDefault();
            const delta = event.deltaY > 0 ? -0.1 : 0.1;
            this.zoom = Math.max(this.minZoom, Math.min(this.maxZoom, this.zoom + delta));
            this.updateImageTransform();
        },
        
        zoomIn() {
            this.zoom = Math.min(this.maxZoom, this.zoom + 0.2);
            this.updateImageTransform();
        },
        
        zoomOut() {
            this.zoom = Math.max(this.minZoom, this.zoom - 0.2);
            this.updateImageTransform();
        },
        
        resetView() {
            this.rotation = { x: 0, y: 0 };
            this.zoom = 1;
            this.updateImageTransform();
        },
        
        updateImageTransform() {
            if (this.$refs.panoramaImage) {
                const transform = `scale(${this.zoom}) rotateX(${this.rotation.x}deg) rotateY(${this.rotation.y}deg)`;
                this.$refs.panoramaImage.style.transform = transform;
            }
        },
        
        switchImage(index) {
            if (index >= 0 && index < this.tourImages.length) {
                this.currentImageIndex = index;
                this.resetView();
            }
        },
        
        getHotspotStyle(hotspot) {
            return {
                left: `${hotspot.x_position}%`,
                top: `${hotspot.y_position}%`,
                transform: 'translate(-50%, -50%)'
            };
        },
        
        onHotspotClick(hotspot) {
            this.selectedHotspot = hotspot;
            this.showHotspotModal = true;
        },
        
        closeHotspotModal() {
            this.showHotspotModal = false;
            this.selectedHotspot = null;
        },
        
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                this.$refs.viewerContainer.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        },
        
        onFullscreenChange() {
            this.isFullscreen = !!document.fullscreenElement;
        },
        
        startAutoRotation() {
            this.autoRotateInterval = setInterval(() => {
                if (!this.isDragging) {
                    this.rotation.y += this.rotationSpeed;
                    this.updateImageTransform();
                }
            }, 50);
        },
        
        stopAutoRotation() {
            if (this.autoRotateInterval) {
                clearInterval(this.autoRotateInterval);
                this.autoRotateInterval = null;
            }
        }
    }
};
</script>

<style scoped>
.virtual-tour-viewer {
    position: relative;
    width: 100%;
    height: 500px;
    background: #000;
    border-radius: 8px;
    overflow: hidden;
    user-select: none;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    z-index: 10;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #333;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 16px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    font-size: 16px;
    margin: 0;
}

.error-state {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    text-align: center;
    padding: 20px;
}

.error-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.error-message {
    font-size: 16px;
    margin-bottom: 16px;
}

.retry-button {
    padding: 8px 16px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.retry-button:hover {
    background: #0056b3;
}

.tour-display {
    position: relative;
    width: 100%;
    height: 100%;
}

.panorama-container {
    position: relative;
    width: 100%;
    height: calc(100% - 80px);
    overflow: hidden;
    cursor: grab;
}

.panorama-container:active {
    cursor: grabbing;
}

.panorama-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.1s ease;
    transform-origin: center;
}

.hotspot {
    position: absolute;
    width: 24px;
    height: 24px;
    cursor: pointer;
    z-index: 5;
}

.hotspot-pulse {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 40px;
    height: 40px;
    background: rgba(0, 123, 255, 0.3);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    animation: pulse 2s infinite;
}

.hotspot-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 20px;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5));
}

@keyframes pulse {
    0% {
        transform: translate(-50%, -50%) scale(0.8);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.5);
        opacity: 0;
    }
}

.tour-controls {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 80px;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 16px;
}

.image-navigation {
    display: flex;
    gap: 8px;
    flex: 1;
    overflow-x: auto;
    padding: 8px 0;
}

.nav-thumb {
    width: 60px;
    height: 40px;
    border: 2px solid transparent;
    border-radius: 4px;
    overflow: hidden;
    cursor: pointer;
    transition: border-color 0.2s;
    flex-shrink: 0;
}

.nav-thumb.active {
    border-color: #007bff;
}

.nav-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.zoom-controls {
    display: flex;
    gap: 8px;
    margin: 0 16px;
}

.zoom-btn, .fullscreen-btn {
    width: 40px;
    height: 40px;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.2s;
}

.zoom-btn:hover, .fullscreen-btn:hover {
    background: rgba(0, 0, 0, 0.8);
}

.hotspot-modal-content {
    padding: 24px;
    max-width: 500px;
}

.hotspot-modal-content h3 {
    margin: 0 0 16px 0;
    font-size: 20px;
    color: #333;
}

.hotspot-modal-content p {
    margin: 0 0 16px 0;
    line-height: 1.6;
    color: #666;
}

.hotspot-image {
    margin: 16px 0;
}

.hotspot-image img {
    width: 100%;
    max-height: 300px;
    object-fit: cover;
    border-radius: 4px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 24px;
}

/* Fullscreen styles */
.virtual-tour-viewer:fullscreen {
    height: 100vh;
    border-radius: 0;
}

.virtual-tour-viewer:fullscreen .panorama-container {
    height: calc(100vh - 80px);
}

/* Responsive design */
@media (max-width: 768px) {
    .virtual-tour-viewer {
        height: 300px;
    }
    
    .tour-controls {
        height: 60px;
        padding: 0 8px;
    }
    
    .nav-thumb {
        width: 50px;
        height: 30px;
    }
    
    .zoom-btn, .fullscreen-btn {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }
    
    .zoom-controls {
        margin: 0 8px;
    }
}
</style>