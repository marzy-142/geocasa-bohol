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
        <div class="mb-4 relative" style="z-index: 1000">
            <div class="flex gap-2">
                <div class="flex-1 relative">
                    <input
                        v-model="addressInput"
                        type="text"
                        placeholder="Enter location (e.g., Cabantian Hills, Guindulman, Bohol)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        @keyup.enter="geocodeAddress"
                        @input="onAddressInput"
                        @focus="showResults = searchResults.length > 0"
                    />

                    <!-- Search Results Dropdown -->
                    <div
                        v-if="showResults && searchResults.length > 0"
                        class="absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-64 overflow-y-auto"
                        style="z-index: 10000"
                    >
                        <div
                            v-for="(result, index) in searchResults"
                            :key="index"
                            @click="selectSearchResult(result)"
                            class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors"
                        >
                            <div class="flex items-start gap-2">
                                <svg
                                    class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <div
                                        class="font-medium text-gray-900 truncate"
                                    >
                                        {{ result.name }}
                                    </div>
                                    <div class="text-sm text-gray-500 truncate">
                                        {{ result.display_name }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{
                                            result.source === "google"
                                                ? "📍 Google Maps"
                                                : "🗺️ OpenStreetMap"
                                        }}
                                        · {{ result.type }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button
                    @click="geocodeAddress"
                    :disabled="!addressInput || isGeocoding"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors whitespace-nowrap"
                >
                    {{ isGeocoding ? "Searching..." : "Search" }}
                </button>
            </div>
            <div
                v-if="geocodeError"
                class="text-sm mt-1"
                :class="
                    geocodeError.includes('Found')
                        ? 'text-amber-600'
                        : 'text-red-500'
                "
            >
                {{ geocodeError }}
            </div>
            <p class="text-xs text-gray-500 mt-1">
                💡 Try: "Cabantian Hills", "Barangay Cabantian, Guindulman", or
                click the map
            </p>
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
    googleApiKey: {
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

// Complete list of Bohol municipalities and city (including common spelling variants)
const BOHOL_MUNICIPALITY_NAMES = [
    "Tagbilaran City",
    "Alburquerque",
    "Alicia",
    "Anda",
    "Antequera",
    "Baclayon",
    "Balilihan",
    "Batuan",
    "Bien Unido",
    "Bilar",
    "Buenavista",
    "Calape",
    "Candijay",
    "Carmen",
    "Catigbian",
    "Clarin",
    "Corella",
    "Cortes",
    "Dagohoy",
    "Danao",
    "Dauis",
    "Dimiao",
    "Duero",
    "Garcia Hernandez",
    "Guindulman",
    "Inabanga",
    "Jagna",
    "Getafe",
    "Jetafe",
    "Lila",
    "Loay",
    "Loboc",
    "Loon",
    "Mabini",
    "Maribojoc",
    "Panglao",
    "Pilar",
    "President Carlos P. Garcia",
    "Sagbayan",
    "San Isidro",
    "San Miguel",
    "Sevilla",
    "Sierra Bullones",
    "Sikatuna",
    "Talibon",
    "Trinidad",
    "Tubigon",
    "Ubay",
    "Valencia",
];

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
const searchResults = ref([]);
const showResults = ref(false);

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

// Auto-search as user types (debounced)
let searchTimeout;
const onAddressInput = () => {
    clearTimeout(searchTimeout);
    geocodeError.value = "";

    if (addressInput.value.trim().length < 3) {
        searchResults.value = [];
        showResults.value = false;
        return;
    }

    searchTimeout = setTimeout(() => {
        geocodeAddressWithResults();
    }, 500);
};

// Select a result from dropdown
const selectSearchResult = (result) => {
    setLocation(result.lat, result.lng);
    reverseGeocodedAddress.value = result.display_name;
    addressInput.value = result.name;
    showResults.value = false;

    if (map.value) {
        map.value.setView([result.lat, result.lng], 16);
    }
};

// Enhanced geocoding with Google Places API fallback
const geocodeAddressWithResults = async () => {
    if (!addressInput.value.trim()) return;

    isGeocoding.value = true;
    geocodeError.value = "";
    searchResults.value = [];

    try {
        const raw = addressInput.value.trim();
        // Handle partial inputs like "Buenavista, C" by dropping very short trailing fragments
        const partsByComma = raw
            .split(",")
            .map((p) => p.trim())
            .filter(Boolean);
        const hasShortTrailing =
            partsByComma.length > 1 &&
            partsByComma[partsByComma.length - 1].length < 3;
        const searchTerm = hasShortTrailing
            ? partsByComma.slice(0, -1).join(", ")
            : raw;
        let allResults = [];

        // PRIORITY 1: Try Google Places API if key is available
        if (props.googleApiKey) {
            const googleResults = await searchGooglePlaces(searchTerm);
            allResults.push(...googleResults);
        }

        // PRIORITY 2: Enhanced Nominatim search with multiple strategies
        const nominatimResults = await searchNominatim(searchTerm);
        allResults.push(...nominatimResults);

        // Filter and sort results by relevance to Bohol
        const boholResults = allResults
            .filter((result) => {
                const lat = parseFloat(result.lat);
                const lng = parseFloat(result.lng);
                return (
                    lat >= BOHOL_BOUNDS.south &&
                    lat <= BOHOL_BOUNDS.north &&
                    lng >= BOHOL_BOUNDS.west &&
                    lng <= BOHOL_BOUNDS.east
                );
            })
            .sort((a, b) => {
                // Prioritize Google results
                if (a.source === "google" && b.source !== "google") return -1;
                if (a.source !== "google" && b.source === "google") return 1;

                // Then by distance from Bohol center
                const distA = getDistance(
                    a.lat,
                    a.lng,
                    BOHOL_CENTER.lat,
                    BOHOL_CENTER.lng
                );
                const distB = getDistance(
                    b.lat,
                    b.lng,
                    BOHOL_CENTER.lat,
                    BOHOL_CENTER.lng
                );
                return distA - distB;
            });

        searchResults.value = boholResults.slice(0, 5); // Top 5 results
        showResults.value = boholResults.length > 0;

        if (boholResults.length === 0) {
            geocodeError.value = hasShortTrailing
                ? `Keep typing… (try completing the municipality, e.g., "${partsByComma[0]}, Carmen")`
                : `"${raw}" not found in Bohol. Try different keywords or click the map.`;
        }
    } catch (error) {
        console.error("Geocoding error:", error);
        geocodeError.value = "Search error. Please try again.";
    } finally {
        isGeocoding.value = false;
    }
};

// Search using Google Places API
const searchGooglePlaces = async (searchTerm) => {
    if (!props.googleApiKey) return [];

    try {
        // Use Google Geocoding API (simpler than Places)
        const query = encodeURIComponent(`${searchTerm}, Bohol, Philippines`);
        const response = await fetch(
            `https://maps.googleapis.com/maps/api/geocode/json?address=${query}&key=${props.googleApiKey}&region=ph&bounds=9.3,123.5|10.2,124.5`
        );
        const data = await response.json();

        if (data.status === "OK" && data.results) {
            return data.results.map((result) => ({
                lat: result.geometry.location.lat,
                lng: result.geometry.location.lng,
                name: result.address_components[0]?.long_name || searchTerm,
                display_name: result.formatted_address,
                type: result.types[0]?.replace(/_/g, " ") || "location",
                source: "google",
                importance: 1.0,
            }));
        }
    } catch (error) {
        console.error("Google Places error:", error);
    }

    return [];
};

// Enhanced Nominatim search with multiple strategies
const searchNominatim = async (searchTerm) => {
    const results = [];
    const queries = [];

    // Build structured understanding of the term
    const byComma = searchTerm
        .split(",")
        .map((p) => p.trim())
        .filter(Boolean);
    const firstToken = byComma[0] || searchTerm;
    const secondToken = byComma.length > 1 ? byComma[1] : "";
    const secondIsShort = secondToken && secondToken.length < 3;

    // Strategy 1: Full search with Bohol context
    queries.push(`${searchTerm}, Bohol, Philippines`);

    // Strategy 2: Search with just Philippines (for less common places)
    queries.push(`${searchTerm}, Philippines`);

    // Strategy 3: If multi-word (space-separated), try the first significant word
    const spaceParts = firstToken.split(/\s+/).filter(Boolean);
    if (spaceParts.length > 0) {
        queries.push(`${spaceParts[0]}, Bohol, Philippines`);
        if (spaceParts.length > 1) {
            queries.push(`${spaceParts.slice(-1)[0]}, Bohol, Philippines`);
        }
    }

    // Strategy 4: If user typed a short trailing municipality hint like ", C",
    // try municipalities that start with that hint (e.g., Carmen, Candijay, Calape)
    if (secondIsShort) {
        const hint = secondToken.toLowerCase();
        BOHOL_MUNICIPALITY_NAMES.filter((m) => m.toLowerCase().startsWith(hint))
            .slice(0, 5)
            .forEach((m) => {
                queries.push(`${firstToken}, ${m}, Bohol`);
            });
    }

    // Strategy 5: Try all municipalities as context (cap the total queries later)
    BOHOL_MUNICIPALITY_NAMES.forEach((municipality) => {
        queries.push(`${firstToken}, ${municipality}, Bohol`);
    });

    try {
        // Search with all strategies in parallel
        const responses = await Promise.all(
            queries.slice(0, 8).map(async (query) => {
                // Limit to 5 concurrent requests
                const encoded = encodeURIComponent(query);
                // Constrain search to Bohol viewbox and PH country to improve precision
                const viewbox = `${BOHOL_BOUNDS.west},${BOHOL_BOUNDS.north},${BOHOL_BOUNDS.east},${BOHOL_BOUNDS.south}`;
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&countrycodes=ph&bounded=1&viewbox=${viewbox}&q=${encoded}&limit=5&addressdetails=1`,
                    {
                        headers: {
                            "User-Agent": "GeoCasa-Bohol/1.0",
                        },
                    }
                );
                return response.json();
            })
        );

        // Flatten and deduplicate results
        const seenLocations = new Set();
        responses.forEach((data) => {
            if (data && Array.isArray(data)) {
                data.forEach((result) => {
                    const locationKey = `${parseFloat(result.lat).toFixed(
                        4
                    )},${parseFloat(result.lon).toFixed(4)}`;
                    if (!seenLocations.has(locationKey)) {
                        seenLocations.add(locationKey);
                        results.push({
                            lat: parseFloat(result.lat),
                            lng: parseFloat(result.lon),
                            name:
                                result.name ||
                                result.display_name.split(",")[0],
                            display_name: result.display_name,
                            type:
                                result.type || result.addresstype || "location",
                            source: "osm",
                            importance: parseFloat(result.importance || 0.5),
                        });
                    }
                });
            }
        });
    } catch (error) {
        console.error("Nominatim search error:", error);
    }

    return results;
};

// Calculate distance between two points (Haversine formula)
const getDistance = (lat1, lng1, lat2, lng2) => {
    const R = 6371; // Earth's radius in km
    const dLat = ((lat2 - lat1) * Math.PI) / 180;
    const dLng = ((lng2 - lng1) * Math.PI) / 180;
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos((lat1 * Math.PI) / 180) *
            Math.cos((lat2 * Math.PI) / 180) *
            Math.sin(dLng / 2) *
            Math.sin(dLng / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
};

// Main geocode function (for button click)
const geocodeAddress = async () => {
    await geocodeAddressWithResults();

    // Auto-select first result if available
    if (searchResults.value.length > 0) {
        selectSearchResult(searchResults.value[0]);
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

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
        const picker = e.target.closest(".map-location-picker");
        if (!picker) {
            showResults.value = false;
        }
    });
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
