import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { mount } from "@vue/test-utils";
import { nextTick } from "vue";
import MapLocationPicker from "@/Components/MapLocationPicker.vue";

// Mock Leaflet
const mockMap = {
    setView: vi.fn(),
    on: vi.fn(),
    removeLayer: vi.fn(),
};

const mockMarker = {
    addTo: vi.fn(() => mockMarker),
    bindPopup: vi.fn(() => mockMarker),
    openPopup: vi.fn(() => mockMarker),
};

const mockTileLayer = {
    addTo: vi.fn(),
};

const mockRectangle = {
    addTo: vi.fn(),
};

vi.mock("leaflet", () => ({
    default: {
        map: vi.fn(() => mockMap),
        tileLayer: vi.fn(() => mockTileLayer),
        marker: vi.fn(() => mockMarker),
        rectangle: vi.fn(() => mockRectangle),
        latLngBounds: vi.fn(() => ({})),
        Icon: {
            Default: {
                prototype: { _getIconUrl: vi.fn() },
                mergeOptions: vi.fn(),
            },
        },
    },
}));

// Mock fetch for geocoding
global.fetch = vi.fn();

describe("MapLocationPicker Component", () => {
    let wrapper;

    beforeEach(() => {
        vi.clearAllMocks();

        // Mock successful geocoding response
        global.fetch.mockResolvedValue({
            json: () =>
                Promise.resolve([
                    {
                        lat: "9.8349",
                        lon: "124.1436",
                        display_name: "Tagbilaran City, Bohol, Philippines",
                    },
                ]),
        });
    });

    afterEach(() => {
        if (wrapper) {
            wrapper.unmount();
        }
    });

    describe("Component Initialization", () => {
        it("renders without crashing", () => {
            wrapper = mount(MapLocationPicker);
            expect(wrapper.exists()).toBe(true);
        });

        it("displays default label when no label prop provided", () => {
            wrapper = mount(MapLocationPicker);
            expect(wrapper.text()).toContain("Select Location on Map");
        });

        it("displays custom label when label prop provided", () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    label: "Custom Location Label",
                },
            });
            expect(wrapper.text()).toContain("Custom Location Label");
        });

        it("initializes with empty coordinates", () => {
            wrapper = mount(MapLocationPicker);
            const coordinateInputs = wrapper.findAll('input[type="number"]');
            const latInput = coordinateInputs[0];
            const lngInput = coordinateInputs[1];
            expect(latInput.element.value).toBe("");
            expect(lngInput.element.value).toBe("");
        });

        it("initializes with provided coordinates", () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8349, lng: 124.1436 },
                },
            });
            const coordinateInputs = wrapper.findAll('input[type="number"]');
            const latInput = coordinateInputs[0];
            const lngInput = coordinateInputs[1];
            expect(latInput.element.value).toBe("9.8349");
            expect(lngInput.element.value).toBe("124.1436");
        });
    });

    describe("Address Search Functionality", () => {
        beforeEach(() => {
            wrapper = mount(MapLocationPicker);
        });

        it("renders address input field", () => {
            const addressInput = wrapper.find('input[type="text"]');
            expect(addressInput.exists()).toBe(true);
            expect(addressInput.attributes("placeholder")).toContain(
                "Enter address in Bohol"
            );
        });

        it("renders search button", () => {
            const searchButton = wrapper.find("button");
            expect(searchButton.exists()).toBe(true);
            expect(searchButton.text()).toBe("Find");
        });

        it("disables search button when no address input", async () => {
            const searchButton = wrapper.find("button");
            expect(searchButton.attributes("disabled")).toBeDefined();
        });

        it("enables search button when address input provided", async () => {
            const addressInput = wrapper.find('input[type="text"]');
            await addressInput.setValue("Panglao, Bohol");

            const searchButton = wrapper.find("button");
            expect(searchButton.attributes("disabled")).toBeUndefined();
        });

        it("performs geocoding on search button click", async () => {
            const addressInput = wrapper.find('input[type="text"]');
            const searchButton = wrapper.find("button");

            await addressInput.setValue("Panglao, Bohol");
            await searchButton.trigger("click");

            expect(global.fetch).toHaveBeenCalledWith(
                expect.stringContaining("nominatim.openstreetmap.org/search")
            );
        });

        it("performs geocoding on Enter key press", async () => {
            const addressInput = wrapper.find('input[type="text"]');

            await addressInput.setValue("Panglao, Bohol");
            await addressInput.trigger("keyup.enter");

            expect(global.fetch).toHaveBeenCalledWith(
                expect.stringContaining("nominatim.openstreetmap.org/search")
            );
        });

        it("shows loading state during geocoding", async () => {
            const addressInput = wrapper.find('input[type="text"]');
            const searchButton = wrapper.find("button");

            // Mock delayed response
            global.fetch.mockImplementationOnce(
                () =>
                    new Promise((resolve) =>
                        setTimeout(
                            () =>
                                resolve({
                                    json: () =>
                                        Promise.resolve([
                                            {
                                                lat: "9.8349",
                                                lon: "124.1436",
                                                display_name:
                                                    "Tagbilaran City, Bohol, Philippines",
                                            },
                                        ]),
                                }),
                            100
                        )
                    )
            );

            await addressInput.setValue("Tagbilaran, Bohol");
            await searchButton.trigger("click");

            expect(searchButton.text()).toBe("Searching...");
        });
    });

    describe("Coordinate Input Functionality", () => {
        beforeEach(() => {
            wrapper = mount(MapLocationPicker);
        });

        it("updates coordinates when manual input changes", async () => {
            const coordinateInputs = wrapper.findAll('input[type="number"]');
            const latInput = coordinateInputs[0];
            const lngInput = coordinateInputs[1];

            await latInput.setValue("9.8349");
            await lngInput.setValue("124.1436");

            expect(wrapper.emitted("update:modelValue")).toBeTruthy();
            expect(wrapper.emitted("locationSelected")).toBeTruthy();
        });

        it("validates coordinate format", async () => {
            const coordinateInputs = wrapper.findAll('input[type="number"]');
            const latInput = coordinateInputs[0];

            await latInput.setValue("invalid");

            // Should not emit invalid coordinates
            const emitted = wrapper.emitted("update:modelValue");
            if (emitted) {
                const lastEmit = emitted[emitted.length - 1][0];
                expect(lastEmit.lat).not.toBe("invalid");
            }
        });
    });

    describe("Bohol Boundary Validation", () => {
        beforeEach(() => {
            wrapper = mount(MapLocationPicker);
        });

        it("shows no warning for coordinates within Bohol", async () => {
            const coordinateInputs = wrapper.findAll('input[type="number"]');
            const latInput = coordinateInputs[0];
            const lngInput = coordinateInputs[1];

            // Coordinates within Bohol bounds
            await latInput.setValue("9.8349");
            await lngInput.setValue("124.1436");
            await nextTick();

            expect(wrapper.text()).not.toContain("outside Bohol province");
        });

        it("shows warning for coordinates outside Bohol", async () => {
            const coordinateInputs = wrapper.findAll('input[type="number"]');
            const latInput = coordinateInputs[0];
            const lngInput = coordinateInputs[1];

            // Coordinates outside Bohol bounds (Manila)
            await latInput.setValue("14.5995");
            await lngInput.setValue("120.9842");
            await nextTick();

            expect(wrapper.text()).toContain("outside Bohol province");
        });
    });

    describe("Location Selection Feedback", () => {
        beforeEach(() => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: 9.8349, lng: 124.1436 },
                },
            });
        });

        it("shows location selected confirmation", () => {
            expect(wrapper.text()).toContain("Location Selected");
            expect(wrapper.text()).toContain("9.834900, 124.143600");
        });

        it("displays coordinates with proper precision", () => {
            expect(wrapper.text()).toContain("9.834900");
            expect(wrapper.text()).toContain("124.143600");
        });
    });

    describe("Error Handling", () => {
        beforeEach(() => {
            wrapper = mount(MapLocationPicker);
        });

        it("handles geocoding API errors gracefully", async () => {
            global.fetch.mockRejectedValueOnce(new Error("Network error"));

            const addressInput = wrapper.find('input[type="text"]');
            const searchButton = wrapper.find("button");

            await addressInput.setValue("Test Address");
            await searchButton.trigger("click");
            await nextTick();

            expect(wrapper.text()).toContain("Error searching for address");
        });

        it("handles empty geocoding results", async () => {
            global.fetch.mockResolvedValueOnce({
                json: () => Promise.resolve([]),
            });

            const addressInput = wrapper.find('input[type="text"]');
            const searchButton = wrapper.find("button");

            await addressInput.setValue("Nonexistent Address");
            await searchButton.trigger("click");
            await nextTick();

            expect(wrapper.text()).toContain("Address not found");
        });

        it("displays error styling when error prop provided", () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    error: "Coordinate validation error",
                },
            });

            const mapContainer = wrapper.find(".w-full.h-96");
            expect(mapContainer.classes()).toContain("border-red-500");
        });
    });

    describe("Component Events", () => {
        beforeEach(() => {
            wrapper = mount(MapLocationPicker);
        });

        it("emits update:modelValue when coordinates change", async () => {
            const coordinateInputs = wrapper.findAll('input[type="number"]');
            const latInput = coordinateInputs[0];
            const lngInput = coordinateInputs[1];

            await latInput.setValue("9.8349");
            await lngInput.setValue("124.1436");

            const emitted = wrapper.emitted("update:modelValue");
            expect(emitted).toBeTruthy();
            expect(emitted[emitted.length - 1][0]).toEqual({
                lat: 9.8349,
                lng: 124.1436,
            });
        });

        it("emits locationSelected when coordinates are set", async () => {
            const coordinateInputs = wrapper.findAll('input[type="number"]');
            const latInput = coordinateInputs[0];
            const lngInput = coordinateInputs[1];

            await latInput.setValue("9.8349");
            await lngInput.setValue("124.1436");

            const emitted = wrapper.emitted("locationSelected");
            expect(emitted).toBeTruthy();
            expect(emitted[emitted.length - 1][0]).toEqual({
                lat: 9.8349,
                lng: 124.1436,
            });
        });
    });

    describe("Responsive Design", () => {
        it("applies responsive grid classes", () => {
            wrapper = mount(MapLocationPicker);
            const coordinateGrid = wrapper.find(".grid.grid-cols-2");
            expect(coordinateGrid.exists()).toBe(true);
        });

        it("applies responsive input styling", async () => {
            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            await wrapper.vm.$nextTick();

            // Check address input has flex-1 class
            const addressInput = wrapper.find('input[placeholder*="address"]');
            if (addressInput.exists()) {
                expect(addressInput.classes()).toContain("flex-1");
            }

            // Check coordinate inputs have w-full class
            const coordinateInputs = wrapper.findAll('input[type="number"]');
            coordinateInputs.forEach((input) => {
                expect(input.classes()).toContain("w-full");
            });
        });

        it("applies responsive classes on mobile devices", async () => {
            // Mock mobile viewport
            Object.defineProperty(window, "innerWidth", {
                writable: true,
                configurable: true,
                value: 375,
            });

            wrapper = mount(MapLocationPicker, {
                props: {
                    modelValue: { lat: null, lng: null },
                },
            });

            await wrapper.vm.$nextTick();

            // Check for mobile-responsive elements
            const mapContainer = wrapper.find(".w-full.h-96");
            const coordinateInputs = wrapper.findAll('input[type="number"]');

            expect(mapContainer.exists()).toBe(true);
            coordinateInputs.forEach((input) => {
                expect(input.classes()).toContain("w-full");
            });
        });
    });
});

// Integration test helpers
export const createMapLocationPickerWrapper = (props = {}) => {
    return mount(MapLocationPicker, {
        props: {
            modelValue: { lat: null, lng: null },
            ...props,
        },
    });
};

export const simulateMapClick = async (wrapper, lat, lng) => {
    const component = wrapper.vm;
    await component.handleMapClick({ latlng: { lat, lng } });
    await nextTick();
};

export const simulateAddressSearch = async (wrapper, address) => {
    const addressInput = wrapper.find('input[type="text"]');
    const searchButton = wrapper.find("button");

    await addressInput.setValue(address);
    await searchButton.trigger("click");
    await nextTick();
};
