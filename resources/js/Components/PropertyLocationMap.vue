<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from "vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import iconUrl from "leaflet/dist/images/marker-icon.png";
import iconRetinaUrl from "leaflet/dist/images/marker-icon-2x.png";
import shadowUrl from "leaflet/dist/images/marker-shadow.png";

// Fix default icon paths when bundling with Vite
L.Icon.Default.mergeOptions({
    iconUrl,
    iconRetinaUrl,
    shadowUrl,
});

const props = defineProps({
    lat: { type: [Number, String, null], default: null },
    lng: { type: [Number, String, null], default: null },
    zoom: { type: Number, default: 9 },
    height: { type: String, default: "520px" },
    showControls: { type: Boolean, default: true },
    enableGeolocate: { type: Boolean, default: true },
    readonly: { type: Boolean, default: false },
    enableSearch: { type: Boolean, default: false },
    defaultCenter: {
        type: Array,
        default: () => [9.634, 123.853], // Bohol approx
    },
    // Fit the map to Bohol bounds when no coordinates are provided
    fitBoholBounds: { type: Boolean, default: true },
    // Optional custom location hint to refine search queries
    searchRegionHint: { type: String, default: "Bohol Philippines" },
});

const emit = defineEmits(["update:lat", "update:lng", "location-selected"]);

const mapEl = ref(null);
const map = ref(null);
const marker = ref(null);
const currentZoom = ref(props.zoom);
// Search state
const searchTerm = ref("");
const searching = ref(false);
const searchResults = ref([]);
const searchError = ref("");
const lastQueryController = ref(null);

const getLat = () => (props.lat !== null ? Number(props.lat) : null);
const getLng = () => (props.lng !== null ? Number(props.lng) : null);

// Approximate geographic bounds that cover the whole of Bohol (including Panglao)
// southWest [lat, lng], northEast [lat, lng]
const boholBounds = L.latLngBounds([9.45, 123.5], [10.25, 124.7]);

const setMarker = (lat, lng) => {
    if (!map.value) return;
    if (marker.value) {
        marker.value.setLatLng([lat, lng]);
    } else {
        marker.value = L.marker([lat, lng], {
            draggable: !props.readonly,
        }).addTo(map.value);
        if (!props.readonly) {
            marker.value.on("dragend", (e) => {
                const pos = e.target.getLatLng();
                emit("update:lat", Number(pos.lat.toFixed(6)));
                emit("update:lng", Number(pos.lng.toFixed(6)));
                emit("location-selected", {
                    lat: Number(pos.lat.toFixed(6)),
                    lng: Number(pos.lng.toFixed(6)),
                });
            });
        }
    }
};

const centerMap = (lat, lng, zoom = 15) => {
    if (!map.value) return;
    map.value.setView([lat, lng], zoom);
};

const onMapClick = (e) => {
    if (props.readonly) return;
    const { lat, lng } = e.latlng;
    const lt = Number(lat.toFixed(6));
    const lg = Number(lng.toFixed(6));
    setMarker(lt, lg);
    emit("update:lat", lt);
    emit("update:lng", lg);
    emit("location-selected", { lat: lt, lng: lg });
};

// Perform remote geocoding search using Nominatim
const performSearch = async () => {
    if (!props.enableSearch) return;
    const term = searchTerm.value.trim();
    searchError.value = "";
    searchResults.value = [];
    if (term.length < 3) {
        return; // require minimal length
    }
    // Abort previous
    if (lastQueryController.value) {
        lastQueryController.value.abort();
    }
    const controller = new AbortController();
    lastQueryController.value = controller;
    searching.value = true;
    try {
        const url = `https://nominatim.openstreetmap.org/search?format=json&limit=5&q=${encodeURIComponent(
            term + " " + props.searchRegionHint
        )}`;
        const response = await fetch(url, {
            headers: { "User-Agent": "GeoCasaBohol/1.0 (PropertyLocationMap)" },
            signal: controller.signal,
        });
        if (!response.ok) throw new Error("Search failed");
        const data = await response.json();
        searchResults.value = data.map((r) => ({
            display_name: r.display_name,
            lat: Number(Number(r.lat).toFixed(6)),
            lng: Number(Number(r.lon).toFixed(6)),
        }));
        if (searchResults.value.length === 0) {
            searchError.value = "No matches found";
        }
    } catch (e) {
        if (e.name !== "AbortError") {
            searchError.value = "Search error";
        }
    } finally {
        searching.value = false;
    }
};

const selectResult = (result) => {
    setMarker(result.lat, result.lng);
    centerMap(result.lat, result.lng, 15);
    emit("update:lat", result.lat);
    emit("update:lng", result.lng);
    emit("location-selected", {
        lat: result.lat,
        lng: result.lng,
        name: result.display_name,
    });
    // collapse suggestions
    searchResults.value = [];
};

// Debounce search input
let debounceTimer = null;
watch(searchTerm, (val) => {
    if (!props.enableSearch) return;
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => performSearch(), 400);
});

const geolocate = () => {
    if (!props.enableGeolocate || !navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lt = Number(pos.coords.latitude.toFixed(6));
            const lg = Number(pos.coords.longitude.toFixed(6));
            setMarker(lt, lg);
            centerMap(lt, lg, 15);
            emit("update:lat", lt);
            emit("update:lng", lg);
            emit("location-selected", { lat: lt, lng: lg });
        },
        () => {
            // ignore error silently
        },
        { enableHighAccuracy: true, maximumAge: 10000 }
    );
};

const resetView = () => {
    if (!map.value) return;
    if (props.fitBoholBounds) {
        map.value.fitBounds(boholBounds, { padding: [20, 20] });
    } else {
        map.value.setView(props.defaultCenter, props.zoom);
    }
};

onMounted(async () => {
    await nextTick();
    if (!mapEl.value) return;
    map.value = L.map(mapEl.value, {
        center: props.defaultCenter,
        zoom: currentZoom.value,
        zoomControl: false,
    });
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors",
        maxZoom: 19,
    }).addTo(map.value);

    map.value.on("click", onMapClick);

    const lt = getLat();
    const lg = getLng();
    if (lt !== null && lg !== null) {
        setMarker(lt, lg);
        centerMap(lt, lg, 15);
    } else if (props.fitBoholBounds) {
        // Show a fuller coverage of the area by fitting the whole province
        map.value.fitBounds(boholBounds, { padding: [20, 20] });
    }
});

onBeforeUnmount(() => {
    if (map.value) {
        map.value.off();
        map.value.remove();
    }
});

// Watch for external changes to lat/lng and update the map marker
watch(
    () => [props.lat, props.lng],
    ([newLat, newLng]) => {
        const lt = newLat !== null ? Number(newLat) : null;
        const lg = newLng !== null ? Number(newLng) : null;
        if (lt !== null && lg !== null && map.value) {
            setMarker(lt, lg);
            centerMap(lt, lg, 15);
        }
    }
);
</script>

<template>
    <div class="relative w-full" :style="{ height }">
        <div
            ref="mapEl"
            class="w-full h-full rounded-lg overflow-hidden border border-neutral-200"
        ></div>
        <div
            v-if="showControls"
            class="absolute top-3 left-3 right-3 z-[1000] flex flex-col gap-2 pointer-events-none"
        >
            <div v-if="enableSearch" class="pointer-events-auto max-w-md">
                <div
                    class="bg-white/95 backdrop-blur rounded-md shadow border border-neutral-200 p-2"
                >
                    <input
                        type="text"
                        v-model="searchTerm"
                        placeholder="Search location (e.g. Dimiao, Panglao)"
                        class="w-full px-3 py-2 text-sm rounded border border-neutral-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <div v-if="searching" class="text-xs text-gray-500 mt-1">
                        Searching…
                    </div>
                    <div v-if="searchError" class="text-xs text-red-600 mt-1">
                        {{ searchError }}
                    </div>
                    <ul
                        v-if="searchResults.length"
                        class="mt-2 space-y-1 max-h-48 overflow-y-auto"
                    >
                        <li
                            v-for="r in searchResults"
                            :key="r.display_name + r.lat + r.lng"
                            @click="selectResult(r)"
                            class="text-xs px-2 py-1 rounded cursor-pointer hover:bg-blue-50 border border-neutral-200"
                        >
                            {{ r.display_name }}
                        </li>
                    </ul>
                </div>
            </div>
            <div
                class="ml-auto flex flex-row gap-2 pointer-events-auto justify-end"
            >
                <button
                    type="button"
                    class="px-3 py-2 rounded-md bg-white/90 shadow hover:bg-white text-sm border border-neutral-200"
                    @click="resetView"
                >
                    Reset
                </button>
                <button
                    v-if="enableGeolocate"
                    type="button"
                    class="px-3 py-2 rounded-md bg-white/90 shadow hover:bg-white text-sm border border-neutral-200"
                    @click="geolocate"
                >
                    My Location
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Ensure Leaflet controls appear above other content */
:deep(.leaflet-control-container) {
    z-index: 1000;
}
</style>
