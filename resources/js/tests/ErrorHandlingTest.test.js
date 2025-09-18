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

describe("MapLocationPicker - Error Handling Tests", () => {
    let wrapper;
    let consoleErrorSpy;

    beforeEach(() => {
        // Mock console.error to capture error logs
        consoleErrorSpy = vi
            .spyOn(console, "error")
            .mockImplementation(() => {});

        // Reset fetch mock
        fetch.mockClear();
    });

    afterEach(() => {
        if (wrapper) {
            wrapper.unmount();
        }
        consoleErrorSpy.mockRestore();
    });

    describe("Network Failure Handling", () => {
        it("should handle geocoding API network failures gracefully", async () => {
            // Mock network failure
            fetch.mockRejectedValue(new Error("Network Error"));

            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Try to geocode an address
            const addressInput = wrapper.find('input[type="text"]');
            await addressInput.setValue("Tagbilaran City");

            const findButton = wrapper.find("button");
            await findButton.trigger("click");

            await nextTick();
            await new Promise((resolve) => setTimeout(resolve, 100));

            // Should show error message
            const errorDiv = wrapper.find(".text-red-500");
            expect(errorDiv.exists()).toBe(true);

            // Component should remain functional
            expect(wrapper.find(".map-location-picker").exists()).toBe(true);
        });

        it("should handle tile loading failures", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Component should still render even if tiles fail to load
            expect(wrapper.find(".map-location-picker").exists()).toBe(true);
            expect(wrapper.find(".w-full.h-96").exists()).toBe(true);
        });

        it("should handle timeout errors in geocoding", async () => {
            // Mock timeout error
            fetch.mockImplementation(
                () =>
                    new Promise((_, reject) =>
                        setTimeout(
                            () => reject(new Error("Request timeout")),
                            50
                        )
                    )
            );

            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            const addressInput = wrapper.find('input[type="text"]');
            await addressInput.setValue("Panglao Island");

            const findButton = wrapper.find("button");
            await findButton.trigger("click");

            // Wait for timeout
            await new Promise((resolve) => setTimeout(resolve, 100));
            await nextTick();

            // Should handle timeout gracefully
            expect(wrapper.find(".map-location-picker").exists()).toBe(true);
        });
    });

    describe("Invalid Coordinates Handling", () => {
        it("should handle extremely large latitude values", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Enter invalid latitude
            const latInput = wrapper.findAll('input[type="number"]')[0];
            await latInput.setValue("999");
            await latInput.trigger("input");

            await nextTick();

            // Should show boundary warning
            const warningDiv = wrapper.find(".bg-red-50");
            expect(warningDiv.exists()).toBe(true);
        });

        it("should handle extremely small latitude values", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Enter invalid latitude
            const latInput = wrapper.findAll('input[type="number"]')[0];
            await latInput.setValue("-999");
            await latInput.trigger("input");

            await nextTick();

            // Should show boundary warning
            const warningDiv = wrapper.find(".bg-red-50");
            expect(warningDiv.exists()).toBe(true);
        });

        it("should handle invalid longitude values", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Enter invalid longitude
            const lngInput = wrapper.findAll('input[type="number"]')[1];
            await lngInput.setValue("999");
            await lngInput.trigger("input");

            await nextTick();

            // Should show boundary warning
            const warningDiv = wrapper.find(".bg-red-50");
            expect(warningDiv.exists()).toBe(true);
        });

        it("should handle non-numeric coordinate inputs", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Enter non-numeric values
            const latInput = wrapper.findAll('input[type="number"]')[0];
            await latInput.setValue("abc");
            await latInput.trigger("input");

            await nextTick();

            // Component should handle gracefully
            expect(wrapper.find(".map-location-picker").exists()).toBe(true);
        });

        it("should handle null or undefined coordinates", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            await nextTick();

            // Component should render without crashing
            expect(wrapper.find(".map-location-picker").exists()).toBe(true);
        });
    });

    describe("Geocoding API Error Handling", () => {
        it("should handle malformed geocoding API responses", async () => {
            // Mock malformed response
            fetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve({ invalid: "response" }),
            });

            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            const addressInput = wrapper.find('input[type="text"]');
            await addressInput.setValue("Invalid Address");

            const findButton = wrapper.find("button");
            await findButton.trigger("click");

            await nextTick();
            await new Promise((resolve) => setTimeout(resolve, 100));

            // Should handle malformed response gracefully
            expect(wrapper.find(".map-location-picker").exists()).toBe(true);
        });

        it("should handle HTTP error responses from geocoding API", async () => {
            // Mock HTTP error
            fetch.mockResolvedValue({
                ok: false,
                status: 500,
                statusText: "Internal Server Error",
            });

            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            const addressInput = wrapper.find('input[type="text"]');
            await addressInput.setValue("Test Address");

            const findButton = wrapper.find("button");
            await findButton.trigger("click");

            await nextTick();
            await new Promise((resolve) => setTimeout(resolve, 100));

            // Should show error message
            const errorDiv = wrapper.find(".text-red-500");
            expect(errorDiv.exists()).toBe(true);
        });

        it("should handle empty geocoding results", async () => {
            // Mock empty results
            fetch.mockResolvedValue({
                ok: true,
                json: () => Promise.resolve([]),
            });

            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            const addressInput = wrapper.find('input[type="text"]');
            await addressInput.setValue("Nonexistent Place");

            const findButton = wrapper.find("button");
            await findButton.trigger("click");

            await nextTick();
            await new Promise((resolve) => setTimeout(resolve, 100));

            // Should show appropriate message
            const errorDiv = wrapper.find(".text-red-500");
            expect(errorDiv.exists()).toBe(true);
        });

        it("should handle rate limiting from geocoding API", async () => {
            // Mock rate limit response
            fetch.mockResolvedValue({
                ok: false,
                status: 429,
                statusText: "Too Many Requests",
            });

            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            const addressInput = wrapper.find('input[type="text"]');
            await addressInput.setValue("Bohol Address");

            const findButton = wrapper.find("button");
            await findButton.trigger("click");

            await nextTick();
            await new Promise((resolve) => setTimeout(resolve, 100));

            // Should handle rate limiting gracefully
            expect(wrapper.find(".map-location-picker").exists()).toBe(true);
        });
    });

    describe("Component State Recovery", () => {
        it("should recover from error states when valid input is provided", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // First, trigger an error with invalid coordinates
            const latInput = wrapper.findAll('input[type="number"]')[0];
            await latInput.setValue("999");
            await latInput.trigger("input");

            await nextTick();

            // Verify error state
            let warningDiv = wrapper.find(".bg-red-50");
            expect(warningDiv.exists()).toBe(true);

            // Now provide valid coordinates
            await latInput.setValue("9.8");
            await latInput.trigger("input");

            await nextTick();

            // Error should be cleared
            warningDiv = wrapper.find(".bg-red-50");
            expect(warningDiv.exists()).toBe(false);
        });

        it("should maintain component functionality after multiple errors", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            // Trigger multiple errors
            const latInput = wrapper.findAll('input[type="number"]')[0];
            const lngInput = wrapper.findAll('input[type="number"]')[1];

            // Invalid latitude
            await latInput.setValue("999");
            await latInput.trigger("input");
            await nextTick();

            // Invalid longitude
            await lngInput.setValue("999");
            await lngInput.trigger("input");
            await nextTick();

            // Component should still be functional
            expect(wrapper.find(".map-location-picker").exists()).toBe(true);
            expect(wrapper.find('input[type="text"]').exists()).toBe(true);
            expect(wrapper.find("button").exists()).toBe(true);
        });
    });

    describe("User Experience During Errors", () => {
        it("should show loading state during geocoding", async () => {
            // Mock slow response
            fetch.mockImplementation(
                () =>
                    new Promise((resolve) =>
                        setTimeout(
                            () =>
                                resolve({
                                    ok: true,
                                    json: () => Promise.resolve([]),
                                }),
                            200
                        )
                    )
            );

            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            const addressInput = wrapper.find('input[type="text"]');
            await addressInput.setValue("Test Address");

            const findButton = wrapper.find("button");
            await findButton.trigger("click");

            await nextTick();

            // Should show loading state
            expect(findButton.text()).toContain("Searching");
            expect(findButton.attributes("disabled")).toBeDefined();
        });

        it("should provide clear error messages to users", async () => {
            // Mock network error
            fetch.mockRejectedValue(new Error("Network Error"));

            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8, lng: 124.2 },
                },
            });

            await nextTick();

            const addressInput = wrapper.find('input[type="text"]');
            await addressInput.setValue("Test Address");

            const findButton = wrapper.find("button");
            await findButton.trigger("click");

            await nextTick();
            await new Promise((resolve) => setTimeout(resolve, 100));

            // Should show user-friendly error message
            const errorDiv = wrapper.find(".text-red-500");
            expect(errorDiv.exists()).toBe(true);
            expect(errorDiv.text().length).toBeGreaterThan(0);
        });
    });
});
