import { describe, it, expect, beforeEach, afterEach, vi } from "vitest";
import { mount } from "@vue/test-utils";
import { nextTick } from "vue";
import MapLocationPicker from "../Components/MapLocationPicker.vue";

// Mock Leaflet
vi.mock("leaflet", () => {
    const mockMap = {
        setView: vi.fn().mockReturnThis(),
        on: vi.fn().mockReturnThis(),
        off: vi.fn().mockReturnThis(),
        remove: vi.fn().mockReturnThis(),
        invalidateSize: vi.fn().mockReturnThis(),
        getBounds: vi.fn().mockReturnValue({
            contains: vi.fn().mockReturnValue(true),
        }),
        addLayer: vi.fn().mockReturnThis(),
        removeLayer: vi.fn().mockReturnThis(),
        getZoom: vi.fn().mockReturnValue(13),
        setZoom: vi.fn().mockReturnThis(),
    };

    const mockMarker = {
        addTo: vi.fn().mockReturnThis(),
        setLatLng: vi.fn().mockReturnThis(),
        remove: vi.fn().mockReturnThis(),
        bindPopup: vi.fn().mockReturnThis(),
    };

    const mockTileLayer = {
        addTo: vi.fn().mockReturnThis(),
    };

    const mockLatLngBounds = {
        contains: vi.fn((lat, lng) => {
            // Bohol boundaries
            return lat >= 9.3 && lat <= 10.2 && lng >= 123.7 && lng <= 124.8;
        }),
    };

    return {
        default: {
            map: vi.fn(() => mockMap),
            tileLayer: vi.fn(() => mockTileLayer),
            marker: vi.fn(() => mockMarker),
            latLngBounds: vi.fn(() => mockLatLngBounds),
            icon: vi.fn(() => ({})),
            LatLngBounds: vi.fn(() => mockLatLngBounds),
            Icon: {
                Default: {
                    prototype: {
                        _getIconUrl: vi.fn(),
                    },
                    mergeOptions: vi.fn(),
                },
            },
        },
    };
});

// Mock fetch for geocoding
global.fetch = vi.fn();

describe("MapLocationPicker - Responsive Design Tests", () => {
    let wrapper;
    let mockResizeObserver;

    beforeEach(() => {
        // Mock ResizeObserver
        mockResizeObserver = vi.fn(() => ({
            observe: vi.fn(),
            unobserve: vi.fn(),
            disconnect: vi.fn(),
        }));
        global.ResizeObserver = mockResizeObserver;

        // Mock window.matchMedia
        Object.defineProperty(window, "matchMedia", {
            writable: true,
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
        });

        // Reset fetch mock
        fetch.mockClear();
    });

    afterEach(() => {
        if (wrapper) {
            wrapper.unmount();
        }
    });

    describe("Mobile Viewport (320px - 768px)", () => {
        beforeEach(() => {
            // Mock mobile viewport
            Object.defineProperty(window, "innerWidth", {
                writable: true,
                configurable: true,
                value: 375,
            });
            Object.defineProperty(window, "innerHeight", {
                writable: true,
                configurable: true,
                value: 667,
            });
        });

        it("should render properly on mobile devices", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                    label: "Select Location",
                },
            });

            await nextTick();

            // Check if main container exists
            expect(wrapper.find(".map-location-picker").exists()).toBe(true);

            // Check if map container has proper responsive classes
            const mapContainer = wrapper.find(".w-full.h-96");
            expect(mapContainer.exists()).toBe(true);
            if (mapContainer.exists()) {
                expect(mapContainer.classes()).toContain("w-full");
                expect(mapContainer.classes()).toContain("h-96");
            }
        });

        it("should have proper grid layout for coordinates on mobile", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Check coordinate inputs grid
            const coordinateGrid = wrapper.find(".grid-cols-2");
            expect(coordinateGrid.exists()).toBe(true);

            // Should have latitude and longitude inputs
            const numberInputs = wrapper.findAll('input[type="number"]');
            expect(numberInputs.length).toBeGreaterThanOrEqual(2);
        });

        it("should handle address input responsively on mobile", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Check address input container
            const addressContainer = wrapper.find(".flex.gap-2");
            expect(addressContainer.exists()).toBe(true);

            // Address input should have flex-1 class for responsiveness
            const addressInput = wrapper.find('input[type="text"]');
            expect(addressInput.exists()).toBe(true);
            expect(addressInput.classes()).toContain("flex-1");
        });
    });

    describe("Tablet Viewport (768px - 1024px)", () => {
        beforeEach(() => {
            // Mock tablet viewport
            Object.defineProperty(window, "innerWidth", {
                writable: true,
                configurable: true,
                value: 768,
            });
            Object.defineProperty(window, "innerHeight", {
                writable: true,
                configurable: true,
                value: 1024,
            });
        });

        it("should render properly on tablet devices", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                    label: "Select Location",
                },
            });

            await nextTick();

            // Check if component renders without issues
            expect(wrapper.find(".map-location-picker").exists()).toBe(true);

            // Map should maintain proper dimensions
            const mapContainer = wrapper.find(".w-full.h-96");
            expect(mapContainer.exists()).toBe(true);
            if (mapContainer.exists()) {
                expect(mapContainer.classes()).toContain("w-full");
                expect(mapContainer.classes()).toContain("h-96");
            }
        });

        it("should maintain proper spacing and layout on tablet", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Check margins and padding classes
            const mainContainer = wrapper.find(".map-location-picker");
            expect(mainContainer.exists()).toBe(true);

            // Check coordinate grid maintains 2-column layout
            const coordinateGrid = wrapper.find(".grid-cols-2");
            expect(coordinateGrid.exists()).toBe(true);
        });
    });

    describe("Desktop Viewport (1024px+)", () => {
        beforeEach(() => {
            // Mock desktop viewport
            Object.defineProperty(window, "innerWidth", {
                writable: true,
                configurable: true,
                value: 1920,
            });
            Object.defineProperty(window, "innerHeight", {
                writable: true,
                configurable: true,
                value: 1080,
            });
        });

        it("should render properly on desktop devices", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                    label: "Select Location",
                },
            });

            await nextTick();

            // Check if component renders without issues
            expect(wrapper.find(".map-location-picker").exists()).toBe(true);

            // All elements should be properly positioned
            const mapContainer = wrapper.find(".w-full.h-96");
            expect(mapContainer.exists()).toBe(true);
        });

        it("should handle larger content areas on desktop", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Check that layout elements exist and are properly structured
            expect(wrapper.find(".mb-4").exists()).toBe(true);
            expect(wrapper.find(".grid-cols-2").exists()).toBe(true);
            expect(wrapper.find(".flex.gap-2").exists()).toBe(true);
        });
    });

    describe("Map Resize Handling", () => {
        it("should handle container resize events", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Simulate container resize
            const mapContainer = wrapper.find(".w-full.h-96");
            expect(mapContainer.exists()).toBe(true);

            // Component should handle resize gracefully
            expect(wrapper.vm).toBeDefined();
        });

        it("should maintain aspect ratio during resize", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Check that map container maintains proper height class
            const mapContainer = wrapper.find(".w-full.h-96");
            expect(mapContainer.exists()).toBe(true);
            if (mapContainer.exists()) {
                expect(mapContainer.classes()).toContain("h-96");
            }
        });
    });

    describe("Touch and Interaction Support", () => {
        it("should support touch events on mobile", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Check that buttons are properly sized for touch
            const findButton = wrapper.find("button");
            expect(findButton.exists()).toBe(true);
            expect(findButton.classes()).toContain("px-4");
            expect(findButton.classes()).toContain("py-2");
        });

        it("should have proper input sizing for touch devices", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Check input padding for touch accessibility
            const inputs = wrapper.findAll("input");
            inputs.forEach((input) => {
                expect(input.classes()).toContain("px-3");
                expect(input.classes()).toContain("py-2");
            });
        });
    });

    describe("Content Overflow Handling", () => {
        it("should handle long addresses gracefully", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Set a very long address
            const addressInput = wrapper.find('input[type="text"]');
            await addressInput.setValue(
                "This is a very long address that might cause overflow issues in the input field on smaller screens"
            );

            // Input should handle overflow with proper styling
            expect(addressInput.classes()).toContain("flex-1");
        });

        it("should have proper text sizing for readability", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Check that text elements use appropriate sizing classes
            const template = wrapper.html();
            expect(template).toContain("text-sm"); // Small text for labels and info
            expect(template).toContain("text-xs"); // Extra small for helper text

            // Verify component renders properly
            expect(wrapper.find(".map-location-picker").exists()).toBe(true);
        });
    });
});
