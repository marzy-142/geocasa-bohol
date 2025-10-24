<template>
    <div class="relative w-full h-full">
        <div ref="mapContainer" class="w-full h-full rounded-lg"></div>
        
        <!-- Loading Overlay -->
        <div v-if="isLoading" class="absolute inset-0 bg-white/80 flex items-center justify-center rounded-lg">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-3"></div>
                <p class="text-sm text-neutral-600">Loading map...</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    properties: {
        type: Array,
        required: true
    },
    center: {
        type: Array,
        default: () => [9.8349, 124.1433] // Bohol center
    },
    zoom: {
        type: Number,
        default: 10
    }
});

const emit = defineEmits(['property-click']);

const mapContainer = ref(null);
const map = ref(null);
const markers = ref([]);
const isLoading = ref(true);

// Fix Leaflet default marker icons
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

// Custom marker icons
const createCustomIcon = (price, isFeatured = false) => {
    const color = isFeatured ? '#EAB308' : '#2563EB';
    const formattedPrice = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price).replace('PHP', '₱');
    
    return L.divIcon({
        className: 'custom-marker',
        html: `
            <div class="relative">
                <div class="bg-white rounded-lg shadow-lg px-3 py-2 border-2 transform -translate-x-1/2 hover:scale-110 transition-transform cursor-pointer" style="border-color: ${color};">
                    <div class="text-xs font-bold whitespace-nowrap" style="color: ${color};">
                        ${formattedPrice}
                    </div>
                </div>
                <div class="absolute left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-8 border-r-8 border-t-8 border-transparent" style="border-top-color: ${color};"></div>
            </div>
        `,
        iconSize: [100, 40],
        iconAnchor: [50, 40],
    });
};

const initMap = () => {
    if (!mapContainer.value) return;
    
    // Create map
    map.value = L.map(mapContainer.value).setView(props.center, props.zoom);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map.value);
    
    // Add markers
    updateMarkers();
    
    isLoading.value = false;
};

const updateMarkers = () => {
    if (!map.value) return;
    
    // Clear existing markers
    markers.value.forEach(marker => marker.remove());
    markers.value = [];
    
    // Add new markers
    const bounds = [];
    
    props.properties.forEach(property => {
        if (property.coordinates_lat && property.coordinates_lng) {
            const lat = parseFloat(property.coordinates_lat);
            const lng = parseFloat(property.coordinates_lng);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                const icon = createCustomIcon(property.total_price, property.is_featured);
                
                const marker = L.marker([lat, lng], { icon })
                    .addTo(map.value)
                    .bindPopup(`
                        <div class="p-2">
                            <h3 class="font-bold text-sm mb-1">${property.title}</h3>
                            <p class="text-xs text-gray-600 mb-2">${property.municipality}, ${property.province}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-blue-600 font-bold text-sm">₱${new Intl.NumberFormat().format(property.total_price)}</span>
                                <span class="text-xs text-gray-500">${property.area} sqm</span>
                            </div>
                        </div>
                    `);
                
                marker.on('click', () => {
                    emit('property-click', property);
                });
                
                markers.value.push(marker);
                bounds.push([lat, lng]);
            }
        }
    });
    
    // Fit bounds if there are markers
    if (bounds.length > 0) {
        map.value.fitBounds(bounds, { padding: [50, 50] });
    }
};

// Watch for property changes
watch(() => props.properties, () => {
    updateMarkers();
}, { deep: true });

onMounted(() => {
    setTimeout(initMap, 100);
});

onUnmounted(() => {
    if (map.value) {
        map.value.remove();
    }
});
</script>

<style>
.custom-marker {
    background: transparent;
    border: none;
}

.leaflet-popup-content-wrapper {
    border-radius: 8px;
}

.leaflet-popup-content {
    margin: 0;
}
</style>
