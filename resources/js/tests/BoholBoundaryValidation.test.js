import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { mount } from "@vue/test-utils";
import { nextTick } from "vue";
import MapLocationPicker from "@/Components/MapLocationPicker.vue";

// Mock Leaflet
const mockMap = {
    setView: vi.fn(),
    on: vi.fn(),
    removeLayer: vi.fn(),
    hasLayer: vi.fn(() => false),
};

const mockMarker = {
    addTo: vi.fn(() => mockMarker),
    bindPopup: vi.fn(() => mockMarker),
    openPopup: vi.fn(() => mockMarker),
    setLatLng: vi.fn(() => mockMarker),
};

const mockTileLayer = {
    addTo: vi.fn(),
};

const mockRectangle = {
    addTo: vi.fn(),
    remove: vi.fn(),
};

vi.mock("leaflet", () => ({
    default: {
        map: vi.fn(() => mockMap),
        tileLayer: vi.fn(() => mockTileLayer),
        marker: vi.fn(() => mockMarker),
        rectangle: vi.fn(() => mockRectangle),
        latLngBounds: vi.fn(() => ({
            contains: vi.fn((latlng) => {
                // Bohol boundaries: approximately 9.3° to 10.2° N, 123.5° to 124.5° E
                const lat = latlng.lat || latlng[0];
                const lng = latlng.lng || latlng[1];
                return (
                    lat >= 9.3 && lat <= 10.2 && lng >= 123.5 && lng <= 124.5
                );
            }),
        })),
        Icon: {
            Default: {
                prototype: { _getIconUrl: vi.fn() },
                mergeOptions: vi.fn(),
            },
        },
    },
}));

// Mock geocoding responses for Bohol locations
const mockBoholGeocodingResponses = {
    // Valid Bohol locations
    "Tagbilaran City": [
        {
            lat: "9.6496",
            lon: "123.8539",
            display_name: "Tagbilaran City, Bohol, Philippines",
        },
    ],
    Panglao: [
        {
            lat: "9.5833",
            lon: "123.7500",
            display_name: "Panglao, Bohol, Philippines",
        },
    ],
    Loboc: [
        {
            lat: "9.6333",
            lon: "124.0333",
            display_name: "Loboc, Bohol, Philippines",
        },
    ],
    "Chocolate Hills": [
        {
            lat: "9.9167",
            lon: "124.1667",
            display_name: "Chocolate Hills, Carmen, Bohol, Philippines",
        },
    ],
    Tubigon: [
        {
            lat: "9.9500",
            lon: "123.8167",
            display_name: "Tubigon, Bohol, Philippines",
        },
    ],
    Jagna: [
        {
            lat: "9.6500",
            lon: "124.3667",
            display_name: "Jagna, Bohol, Philippines",
        },
    ],

    // Invalid locations (outside Bohol)
    "Cebu City": [
        {
            lat: "10.3157",
            lon: "123.8854",
            display_name: "Cebu City, Cebu, Philippines",
        },
    ],
    Manila: [
        {
            lat: "14.5995",
            lon: "120.9842",
            display_name: "Manila, Metro Manila, Philippines",
        },
    ],
    Dumaguete: [
        {
            lat: "9.3063",
            lon: "123.3018",
            display_name: "Dumaguete, Negros Oriental, Philippines",
        },
    ],
};

describe("Bohol Boundary Validation Tests", () => {
    let wrapper;

    beforeEach(() => {
        vi.clearAllMocks();

        // Mock fetch for geocoding
        global.fetch = vi.fn().mockImplementation((url) => {
            const searchTerm = new URL(url).searchParams.get("q");
            // Extract the main location name from the encoded query
            const locationName = decodeURIComponent(searchTerm)
                .split(",")[0]
                .trim();
            const response = mockBoholGeocodingResponses[locationName] || [];
            return Promise.resolve({
                json: () => Promise.resolve(response),
            });
        });
    });

    afterEach(() => {
        if (wrapper) {
            wrapper.unmount();
        }
    });

    describe("Valid Bohol Coordinates", () => {
        const validBoholCoordinates = [
            { lat: 9.6496, lng: 123.8539, location: "Tagbilaran City Center" },
            { lat: 9.5833, lng: 123.75, location: "Panglao Island" },
            { lat: 9.6333, lng: 124.0333, location: "Loboc River" },
            { lat: 9.9167, lng: 124.1667, location: "Chocolate Hills" },
            { lat: 9.95, lng: 123.8167, location: "Tubigon Port" },
            { lat: 9.65, lng: 124.3667, location: "Jagna Bay" },
            { lat: 9.4, lng: 123.6, location: "Southern Bohol" },
            { lat: 10.1, lng: 124.4, location: "Northern Bohol" },
        ];

        validBoholCoordinates.forEach(({ lat, lng, location }) => {
            it(`accepts valid coordinates for ${location}`, async () => {
                wrapper = mount(MapLocationPicker, {
                    props: {
                        modelValue: { lat: null, lng: null },
                    },
                });

                await wrapper.vm.$nextTick();

                // Simulate coordinate selection
                await wrapper.vm.setLocation(lat, lng);
                await nextTick();

                // Should not show boundary error
                expect(wrapper.text()).not.toContain("outside Bohol province");
                expect(wrapper.text()).not.toContain("Invalid location");

                // Should emit valid coordinates
                const emitted = wrapper.emitted("update:modelValue");
                expect(emitted).toBeTruthy();
                expect(emitted[emitted.length - 1][0]).toEqual({ lat, lng });
            });
        });
    });

    describe("Invalid Coordinates (Outside Bohol)", () => {
        const invalidCoordinates = [
            { lat: 10.3157, lng: 123.8854, location: "Cebu City" },
            { lat: 14.5995, lng: 120.9842, location: "Manila" },
            { lat: 9.3063, lng: 123.3018, location: "Dumaguete" },
            { lat: 7.0731, lng: 125.6128, location: "Davao" },
            { lat: 16.4023, lng: 120.596, location: "Baguio" },
            { lat: 8.0, lng: 123.0, location: "South of Bohol" },
            { lat: 11.0, lng: 124.0, location: "North of Bohol" },
            { lat: 9.5, lng: 122.0, location: "West of Bohol" },
            { lat: 9.5, lng: 125.0, location: "East of Bohol" },
        ];

        invalidCoordinates.forEach(({ lat, lng, location }) => {
            it(`rejects coordinates outside Bohol boundaries for ${location}`, async () => {
                wrapper = mount(MapLocationPicker, {
                    props: {
                        modelValue: { lat: null, lng: null },
                    },
                });

                await wrapper.vm.$nextTick();

                // Simulate coordinate selection outside Bohol
                await wrapper.vm.setLocation(lat, lng);
                await nextTick();

                // Should show boundary error
                expect(wrapper.text()).toContain("outside Bohol province");

                // Should not emit invalid coordinates
                const emitted = wrapper.emitted("update:modelValue");
                if (emitted) {
                    // If emitted, should not contain the invalid coordinates
                    const lastEmitted = emitted[emitted.length - 1][0];
                    expect(lastEmitted).not.toEqual({ lat, lng });
                }
            });
        });
    });

    describe("Boundary Edge Cases", () => {
        it("accepts coordinates exactly on Bohol boundaries", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            const boundaryCoordinates = [
                { lat: 9.3, lng: 123.5 }, // Southwest corner
                { lat: 9.3, lng: 124.5 }, // Southeast corner
                { lat: 10.2, lng: 123.5 }, // Northwest corner
                { lat: 10.2, lng: 124.5 }, // Northeast corner
            ];

            for (const coords of boundaryCoordinates) {
                await wrapper.vm.setLocation(coords.lat, coords.lng);
                await nextTick();

                // Should accept boundary coordinates
                expect(wrapper.text()).not.toContain("outside Bohol province");
            }
        });

        it("rejects coordinates just outside Bohol boundaries", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            const outsideBoundaryCoordinates = [
                { lat: 9.29, lng: 123.5 }, // Just south
                { lat: 9.3, lng: 123.49 }, // Just west
                { lat: 10.21, lng: 124.5 }, // Just north
                { lat: 10.2, lng: 124.51 }, // Just east
            ];

            for (const coords of outsideBoundaryCoordinates) {
                await wrapper.vm.setLocation(coords.lat, coords.lng);
                await nextTick();

                // Should reject coordinates just outside boundaries
                expect(wrapper.text()).toContain("outside Bohol province");
            }
        });
    });

    describe("Geocoding Validation", () => {
        it("validates geocoded addresses within Bohol", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            const addressInput = wrapper.find('input[type="text"]');
            const searchButton = wrapper.find("button");

            // Test valid Bohol addresses
            const validAddresses = [
                "Tagbilaran City",
                "Panglao",
                "Loboc",
                "Chocolate Hills",
            ];

            for (const address of validAddresses) {
                await addressInput.setValue(address);
                await searchButton.trigger("click");
                await nextTick();

                // Should not show boundary error for valid Bohol addresses
                expect(wrapper.text()).not.toContain("outside Bohol province");
                expect(wrapper.text()).not.toContain("Invalid location");
            }
        });

        it("rejects geocoded addresses outside Bohol", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            const addressInput = wrapper.find('input[type="text"]');
            const searchButton = wrapper.find("button");

            // Test invalid addresses outside Bohol
            const invalidAddresses = ["Cebu City", "Manila", "Dumaguete"];

            for (const address of invalidAddresses) {
                await addressInput.setValue(address);
                await searchButton.trigger("click");
                await nextTick();

                // Should show boundary error for addresses outside Bohol
                expect(wrapper.text()).toContain("outside Bohol province");
            }
        });
    });

    describe("Municipality Validation", () => {
        const boholMunicipalities = [
            "Tagbilaran City",
            "Panglao",
            "Dauis",
            "Baclayon",
            "Loboc",
            "Sevilla",
            "Balilihan",
            "Catigbian",
            "Batuan",
            "Carmen",
            "Danao",
            "Sagbayan",
            "Tubigon",
            "Clarin",
            "Inabanga",
            "Buenavista",
            "Getafe",
            "Talibon",
            "Bien Unido",
            "Trinidad",
            "San Miguel",
            "Ubay",
            "Carlos P. Garcia",
            "Pilar",
            "Sierra Bullones",
            "Valencia",
            "Garcia Hernandez",
            "Jagna",
            "Duero",
            "Guindulman",
            "Anda",
            "Candijay",
            "Alburquerque",
            "Loay",
            "Loon",
            "Maribojoc",
            "Antequera",
            "Cortes",
            "Corella",
            "Sikatuna",
            "Dimiao",
            "Lila",
            "Bilar",
        ];

        it("validates coordinates against known Bohol municipalities", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            // Test coordinates for major municipalities
            const municipalityCoords = {
                "Tagbilaran City": { lat: 9.6496, lng: 123.8539 },
                Panglao: { lat: 9.5833, lng: 123.75 },
                Tubigon: { lat: 9.95, lng: 123.8167 },
                Jagna: { lat: 9.65, lng: 124.3667 },
            };

            for (const [municipality, coords] of Object.entries(
                municipalityCoords
            )) {
                await wrapper.vm.setLocation(coords.lat, coords.lng);
                await nextTick();

                // Should accept coordinates within known municipalities
                expect(wrapper.text()).not.toContain("outside Bohol province");
            }
        });
    });

    describe("Real-time Boundary Feedback", () => {
        it("provides immediate feedback when coordinates change", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            // Start with valid coordinates
            await wrapper.vm.setLocation(9.6496, 123.8539);
            await nextTick();
            expect(wrapper.text()).not.toContain("outside Bohol province");

            // Move to invalid coordinates
            await wrapper.vm.setLocation(10.3157, 123.8854);
            await nextTick();
            expect(wrapper.text()).toContain("outside Bohol province");

            // Move back to valid coordinates
            await wrapper.vm.setLocation(9.5833, 123.75);
            await nextTick();
            expect(wrapper.text()).not.toContain("outside Bohol province");
        });

        it("shows helpful suggestions for nearby valid locations", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            // Select coordinates just outside Bohol
            await wrapper.vm.setLocation(10.3157, 123.8854);
            await nextTick();

            // Should show error and suggestion
            expect(wrapper.text()).toContain("outside Bohol province");
            expect(wrapper.text()).toContain(
                "Please select a location within Bohol"
            );
        });
    });

    describe("Performance with Boundary Checking", () => {
        it("performs boundary validation efficiently", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            const startTime = performance.now();

            // Test multiple coordinate validations
            const testCoordinates = [
                { lat: 9.6496, lng: 123.8539 }, // Valid
                { lat: 10.3157, lng: 123.8854 }, // Invalid
                { lat: 9.5833, lng: 123.75 }, // Valid
                { lat: 14.5995, lng: 120.9842 }, // Invalid
                { lat: 9.9167, lng: 124.1667 }, // Valid
            ];

            for (const coords of testCoordinates) {
                await wrapper.vm.setLocation(coords.lat, coords.lng);
                await nextTick();
            }

            const endTime = performance.now();
            const duration = endTime - startTime;

            // Should complete validation quickly (< 100ms)
            expect(duration).toBeLessThan(100);
        });
    });

    describe("Integration with Map Boundaries", () => {
        it("displays Bohol boundary rectangle on map", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            await wrapper.vm.$nextTick();

            // Should create boundary rectangle
            const L = await import("leaflet");
            expect(L.default.rectangle).toHaveBeenCalled();
            expect(mockRectangle.addTo).toHaveBeenCalled();
        });

        it("highlights boundary violations visually", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            // Select invalid coordinates
            await wrapper.vm.setLocation(10.3157, 123.8854);
            await nextTick();

            // Should show visual error indication
            const errorElements = wrapper.findAll(
                ".text-red-500, .border-red-500, .bg-red-50"
            );
            expect(errorElements.length).toBeGreaterThan(0);
        });
    });
});

// Utility functions for boundary testing
export const isWithinBoholBounds = (lat, lng) => {
    return lat >= 9.3 && lat <= 10.2 && lng >= 123.5 && lng <= 124.5;
};

export const getBoholBoundaryInfo = () => {
    return {
        north: 10.2,
        south: 9.3,
        east: 124.5,
        west: 123.5,
        center: { lat: 9.75, lng: 124.0 },
    };
};

export const generateBoholTestCoordinates = (count = 10) => {
    const coordinates = [];
    const bounds = getBoholBoundaryInfo();

    for (let i = 0; i < count; i++) {
        const lat =
            bounds.south + Math.random() * (bounds.north - bounds.south);
        const lng = bounds.west + Math.random() * (bounds.east - bounds.west);
        coordinates.push({
            lat: parseFloat(lat.toFixed(4)),
            lng: parseFloat(lng.toFixed(4)),
        });
    }

    return coordinates;
};

export const generateInvalidCoordinates = (count = 5) => {
    const invalidCoords = [
        { lat: 10.3157, lng: 123.8854, location: "Cebu City" },
        { lat: 14.5995, lng: 120.9842, location: "Manila" },
        { lat: 9.3063, lng: 123.3018, location: "Dumaguete" },
        { lat: 7.0731, lng: 125.6128, location: "Davao" },
        { lat: 16.4023, lng: 120.596, location: "Baguio" },
    ];

    return invalidCoords.slice(0, count);
};
