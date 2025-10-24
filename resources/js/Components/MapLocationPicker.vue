<template>
    <div class="map-location-picker">
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ label || "Select Location on Map" }}
            </label>
            <p class="text-xs text-gray-500 mb-3">
                Click on the map to set coordinates, or enter address below for
                automatic positioning
            </p>
        </div>

        <!-- Address Input with Geocoding -->
        <div class="mb-4">
            <div class="flex gap-2">
                <input
                    v-model="addressInput"
                    type="text"
                    placeholder="Enter address in Bohol (e.g., Panglao, Bohol)"
                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    @keyup.enter="geocodeAddress"
                />
                <button
                    @click="geocodeAddress"
                    :disabled="!addressInput || isGeocoding"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
                >
                    {{ isGeocoding ? "Searching..." : "Find" }}
                </button>
            </div>
            <div v-if="geocodeError" class="text-red-500 text-sm mt-1">
                {{ geocodeError }}
            </div>
        </div>

        <!-- Map Container -->
        <div class="relative">
            <div
                ref="mapContainer"
                class="w-full h-96 border border-gray-300 rounded-lg overflow-hidden"
                :class="{ 'border-red-500': error }"
            ></div>

            <!-- Loading Overlay -->
            <div
                v-if="isLoading"
                class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center rounded-lg"
            >
                <div class="text-center">
                    <div
                        class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"
                    ></div>
                    <p class="text-sm text-gray-600">Loading map...</p>
                </div>
            </div>
        </div>

        <!-- Coordinate Display -->
        <div class="mt-4 grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Latitude
                </label>
                <input
                    v-model="displayLat"
                    type="number"
                    step="any"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :class="{ 'border-red-500': error }"
                    @input="updateCoordinatesFromInput"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Longitude
                </label>
                <input
                    v-model="displayLng"
                    type="number"
                    step="any"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :class="{ 'border-red-500': error }"
                    @input="updateCoordinatesFromInput"
                />
            </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="text-red-500 text-sm mt-2">
            {{ error }}
        </div>

        <!-- Location Info -->
        <div
            v-if="selectedLocation"
            class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg"
        >
            <div class="flex items-center gap-2 mb-2">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <span class="text-sm font-medium text-green-800"
                    >Location Selected</span
                >
            </div>
            <p class="text-sm text-green-700">
                Coordinates: {{ selectedLocation.lat.toFixed(6) }},
                {{ selectedLocation.lng.toFixed(6) }}
            </p>
            <p
                v-if="reverseGeocodedAddress"
                class="text-sm text-green-700 mt-1"
            >
                Address: {{ reverseGeocodedAddress }}
            </p>
        </div>

        <!-- Bohol Boundary Warning -->
        <div
            v-if="boundaryWarning"
            class="mt-4 p-3 bg-red-50 border border-red-500 rounded-lg"
        >
            <div class="flex items-center gap-2 mb-2">
                <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                <span class="text-sm font-medium text-red-800"
                    >Location Error</span
                >
            </div>
            <p class="text-sm text-red-700">
                {{ boundaryWarning }}
            </p>
            <p class="text-sm text-red-600 mt-1">
                Please select a location within Bohol province boundaries.
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from "vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

// Fix for default markers in Leaflet
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl:
        "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png",
    iconUrl:
        "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png",
    shadowUrl:
        "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png",
});

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ lat: null, lng: null }),
    },
    label: {
        type: String,
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue", "locationSelected"]);

// Bohol boundaries (approximate)
const BOHOL_BOUNDS = {
    north: 10.2,
    south: 9.3,
    east: 124.5,
    west: 123.5,
};

// Bohol center coordinates
const BOHOL_CENTER = { lat: 9.8349, lng: 124.1436 };

// Reactive data
const mapContainer = ref(null);
const map = ref(null);
const marker = ref(null);
const isLoading = ref(true);
const selectedLocation = ref(
    props.modelValue.lat && props.modelValue.lng ? props.modelValue : null
);
const displayLat = ref(props.modelValue.lat || "");
const displayLng = ref(props.modelValue.lng || "");
const addressInput = ref("");
const isGeocoding = ref(false);
const geocodeError = ref("");
const reverseGeocodedAddress = ref("");
const boundaryWarning = ref("");

// Initialize map
const initMap = async () => {
    try {
        await nextTick();

        if (!mapContainer.value) {
            console.error("Map container not found");
            return;
        }

        // Create map
        map.value = L.map(mapContainer.value).setView(
            [
                selectedLocation.value?.lat || BOHOL_CENTER.lat,
                selectedLocation.value?.lng || BOHOL_CENTER.lng,
            ],
            selectedLocation.value ? 15 : 10
        );

        // Add tile layer
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors",
            maxZoom: 19,
        }).addTo(map.value);

        // Add Bohol boundary rectangle (visual reference)
        const boholBounds = L.latLngBounds(
            [BOHOL_BOUNDS.south, BOHOL_BOUNDS.west],
            [BOHOL_BOUNDS.north, BOHOL_BOUNDS.east]
        );

        L.rectangle(boholBounds, {
            color: "#3b82f6",
            weight: 2,
            fillOpacity: 0.1,
            dashArray: "5, 5",
        }).addTo(map.value);

        // Add existing marker if coordinates exist
        if (selectedLocation.value) {
            addMarker(selectedLocation.value.lat, selectedLocation.value.lng);
        }

        // Handle map clicks
        map.value.on("click", handleMapClick);

        isLoading.value = false;
    } catch (error) {
        console.error("Error initializing map:", error);
        isLoading.value = false;
    }
};

// Handle map click
const handleMapClick = (e) => {
    const { lat, lng } = e.latlng;
    setLocation(lat, lng);
    reverseGeocode(lat, lng);
};

// Add or update marker
const addMarker = (lat, lng) => {
    if (!map.value) return;

    if (marker.value) {
        map.value.removeLayer(marker.value);
    }

    marker.value = L.marker([lat, lng]).addTo(map.value);
    marker.value.bindPopup(`Location: ${lat.toFixed(4)}, ${lng.toFixed(4)}`);
};

// Set location and validate
const setLocation = (lat, lng) => {
    const location = { lat: parseFloat(lat), lng: parseFloat(lng) };

    // Validate coordinates
    if (isNaN(location.lat) || isNaN(location.lng)) {
        return;
    }

    selectedLocation.value = location;
    displayLat.value = location.lat.toString();
    displayLng.value = location.lng.toString();

    // Check Bohol boundaries
    const isWithinBohol = checkBoholBoundaries(location.lat, location.lng);

    // Update marker only if map is initialized
    if (map.value) {
        addMarker(location.lat, location.lng);
    }

    // Only emit updates if within Bohol boundaries
    if (isWithinBohol) {
        emit("update:modelValue", location);
        emit("locationSelected", location);
    }
};

// Check if coordinates are within Bohol boundaries
const checkBoholBoundaries = (lat, lng) => {
    const isWithinBohol =
        lat >= BOHOL_BOUNDS.south &&
        lat <= BOHOL_BOUNDS.north &&
        lng >= BOHOL_BOUNDS.west &&
        lng <= BOHOL_BOUNDS.east;

    if (!isWithinBohol) {
        boundaryWarning.value = "Selected location is outside Bohol province";
    } else {
        boundaryWarning.value = "";
    }

    return isWithinBohol;
};

// Update coordinates from manual input
const updateCoordinatesFromInput = () => {
    const lat = parseFloat(displayLat.value);
    const lng = parseFloat(displayLng.value);

    if (!isNaN(lat) && !isNaN(lng)) {
        setLocation(lat, lng);

        // Center map on new coordinates
        if (map.value) {
            map.value.setView([lat, lng], 15);
        }
    }
};

// Geocode address
const geocodeAddress = async () => {
    if (!addressInput.value.trim()) return;

    isGeocoding.value = true;
    geocodeError.value = "";

    try {
        // Using Nominatim API for geocoding
        const query = encodeURIComponent(
            `${addressInput.value}, Bohol, Philippines`
        );
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${query}&limit=1`
        );
        const data = await response.json();

        if (data && data.length > 0) {
            const result = data[0];
            const lat = parseFloat(result.lat);
            const lng = parseFloat(result.lon);

            setLocation(lat, lng);

            // Center map on found location
            if (map.value) {
                map.value.setView([lat, lng], 15);
            }

            reverseGeocodedAddress.value = result.display_name;
        } else {
            geocodeError.value =
                "Address not found. Please try a different search term.";
        }
    } catch (error) {
        console.error("Geocoding error:", error);
        geocodeError.value = "Error searching for address. Please try again.";
    } finally {
        isGeocoding.value = false;
    }
};

// Reverse geocode coordinates
const reverseGeocode = async (lat, lng) => {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
        );
        const data = await response.json();

        if (data && data.display_name) {
            reverseGeocodedAddress.value = data.display_name;
        }
    } catch (error) {
        console.error("Reverse geocoding error:", error);
    }
};

// Watch for prop changes
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue && newValue.lat && newValue.lng) {
            selectedLocation.value = newValue;
            displayLat.value = newValue.lat.toString();
            displayLng.value = newValue.lng.toString();

            if (map.value) {
                addMarker(newValue.lat, newValue.lng);
                map.value.setView([newValue.lat, newValue.lng], 15);
            }
        }
    },
    { deep: true }
);

// Expose methods for testing
defineExpose({
    setLocation,
    checkBoholBoundaries,
});

// Initialize on mount
onMounted(() => {
    initMap();
});
</script>

<style scoped>
.map-location-picker {
    @apply w-full;
}

/* Ensure Leaflet controls are properly styled */
:deep(.leaflet-control-zoom) {
    @apply shadow-lg;
}

:deep(.leaflet-popup-content) {
    @apply text-sm;
}
</style>
