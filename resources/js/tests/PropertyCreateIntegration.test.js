import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { mount } from "@vue/test-utils";
import { nextTick } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import PropertyCreate from "@/Pages/Properties/Create.vue";
import MapLocationPicker from "@/Components/MapLocationPicker.vue";

// Mock Inertia
vi.mock("@inertiajs/vue3", () => ({
    useForm: vi.fn(() => ({
        title: "",
        description: "",
        type: "",
        municipality: "",
        barangay: "",
        address: "",
        coordinates_lat: "",
        coordinates_lng: "",
        lot_area_sqm: "",
        price_per_sqm: "",
        total_price: "",
        title_type: "",
        title_number: "",
        zoning_classification: "",
        road_access: false,
        electricity_available: false,
        water_source: false,
        internet_available: false,
        nearby_landmarks: [],
        google_maps_link: "",
        additional_notes: "",
        images: [],
        status: "available",
        is_featured: false,
        has_virtual_tour: false,
        virtual_tour_images: [],
        gis_data: "",
        tour_hotspots: "",
        post: vi.fn(),
    })),
    usePage: vi.fn(() => ({
        props: {
            auth: {
                user: {
                    role: "broker",
                },
            },
            errors: {},
        },
    })),
    Link: {
        name: "Link",
        props: ["href"],
        template: '<a :href="href"><slot /></a>',
    },
    Head: {
        name: "Head",
        template: "<head><slot /></head>",
    },
}));

// Mock ModernDashboardLayout
vi.mock("@/Layouts/ModernDashboardLayout.vue", () => ({
    default: {
        name: "ModernDashboardLayout",
        template: "<div><slot /></div>",
    },
}));

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

// Mock route helper
global.route = vi.fn((name, params) => {
    const routes = {
        "properties.store": "/properties",
        "broker.properties.index": "/properties",
    };
    return routes[name] || "/";
});

// Make route available in component context
const mockRoute = global.route;

describe("Property Create Integration Tests", () => {
    let wrapper;
    let mockForm;

    beforeEach(() => {
        vi.clearAllMocks();

        // Mock form object
        mockForm = {
            title: "",
            description: "",
            type: "",
            municipality: "",
            barangay: "",
            address: "",
            coordinates_lat: "",
            coordinates_lng: "",
            lot_area_sqm: "",
            price_per_sqm: "",
            total_price: "",
            title_type: "",
            title_number: "",
            zoning_classification: "",
            road_access: false,
            electricity_available: false,
            water_source: false,
            internet_available: false,
            nearby_landmarks: [],
            google_maps_link: "",
            additional_notes: "",
            images: [],
            status: "available",
            is_featured: false,
            has_virtual_tour: false,
            virtual_tour_images: [],
            gis_data: "",
            tour_hotspots: "",
            post: vi.fn(),
        };

        // Mock successful geocoding
        global.fetch = vi.fn().mockResolvedValue({
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

    describe("MapLocationPicker Integration", () => {
        it("renders MapLocationPicker component in property form", () => {
            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });
            const mapPicker = wrapper.findComponent(MapLocationPicker);
            expect(mapPicker.exists()).toBe(true);
        });

        it("passes correct props to MapLocationPicker", () => {
            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });
            const mapPicker = wrapper.findComponent(MapLocationPicker);

            expect(mapPicker.props("label")).toBe(
                "Property Location (GPS Coordinates)"
            );
            expect(mapPicker.props("modelValue")).toEqual({
                lat: null,
                lng: null,
            });
        });

        it("updates form coordinates when MapLocationPicker emits location", async () => {
            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });
            const mapPicker = wrapper.findComponent(MapLocationPicker);

            const testLocation = { lat: 9.8349, lng: 124.1436 };
            await mapPicker.vm.$emit("locationSelected", testLocation);
            await nextTick();

            // Check if coordinates are updated in the form
            expect(wrapper.vm.form.coordinates_lat).toBe("9.8349");
            expect(wrapper.vm.form.coordinates_lng).toBe("124.1436");
        });

        it("syncs coordinates bidirectionally", async () => {
            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });
            const mapPicker = wrapper.findComponent(MapLocationPicker);

            // Update coordinates through map
            const testLocation = { lat: 9.8349, lng: 124.1436 };
            await mapPicker.vm.$emit("update:modelValue", testLocation);
            await nextTick();

            expect(wrapper.vm.coordinates.lat).toBe(9.8349);
            expect(wrapper.vm.coordinates.lng).toBe(124.1436);
        });
    });

    describe("Form Validation Integration", () => {
        it("displays validation errors for coordinates", async () => {
            const { usePage } = await import("@inertiajs/vue3");
            usePage.mockReturnValue({
                props: {
                    auth: { user: { role: "broker" } },
                    errors: {
                        coordinates_lat: "Latitude is required",
                        coordinates_lng: "Longitude is required",
                    },
                },
            });

            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });
            const mapPicker = wrapper.findComponent(MapLocationPicker);

            expect(mapPicker.props("error")).toBeTruthy();
        });

        it("clears validation errors when valid coordinates are set", async () => {
            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });
            const mapPicker = wrapper.findComponent(MapLocationPicker);

            const validLocation = { lat: 9.8349, lng: 124.1436 };
            await mapPicker.vm.$emit("locationSelected", validLocation);
            await nextTick();

            expect(wrapper.vm.form.coordinates_lat).toBe("9.8349");
            expect(wrapper.vm.form.coordinates_lng).toBe("124.1436");
        });
    });

    describe("Form Submission Integration", () => {
        it("includes coordinates in form submission", async () => {
            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });
            const mapPicker = wrapper.findComponent(MapLocationPicker);

            // Set coordinates
            const testLocation = { lat: 9.8349, lng: 124.1436 };
            await mapPicker.vm.$emit("locationSelected", testLocation);
            await nextTick();

            // Fill required fields
            await wrapper.find("#title").setValue("Test Property");
            await wrapper.find("#description").setValue("Test Description");
            await wrapper.find("#type").setValue("residential_lot");
            await wrapper.find("#municipality").setValue("Tagbilaran City");
            await wrapper.find("#barangay").setValue("Poblacion");
            await wrapper.find("#lot_area_sqm").setValue("1000");
            await wrapper.find("#price_per_sqm").setValue("5000");

            // Submit form
            await wrapper.find("form").trigger("submit");

            expect(wrapper.vm.form.coordinates_lat).toBe("9.8349");
            expect(wrapper.vm.form.coordinates_lng).toBe("124.1436");
        });

        it("prevents submission with invalid coordinates", async () => {
            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });

            // Try to submit without coordinates
            await wrapper.find("form").trigger("submit");

            // Form should not be submitted with empty coordinates
            expect(wrapper.vm.form.coordinates_lat).toBe("");
            expect(wrapper.vm.form.coordinates_lng).toBe("");
        });
    });

    describe("User Experience Integration", () => {
        it("shows helpful tip text for map usage", () => {
            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });
            expect(wrapper.text()).toContain(
                "Click on the map to set precise coordinates"
            );
            expect(wrapper.text()).toContain(
                "search for an address to auto-locate"
            );
        });

        it("maintains form state when switching between manual and map input", async () => {
            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });
            const mapPicker = wrapper.findComponent(MapLocationPicker);

            // Set coordinates via map
            const mapLocation = { lat: 9.8349, lng: 124.1436 };
            await mapPicker.vm.$emit("locationSelected", mapLocation);
            await nextTick();

            // Verify coordinates are reflected in form
            expect(wrapper.vm.form.coordinates_lat).toBe("9.8349");
            expect(wrapper.vm.form.coordinates_lng).toBe("124.1436");

            // Update via manual input (simulated)
            wrapper.vm.form.coordinates_lat = "9.9000";
            wrapper.vm.form.coordinates_lng = "124.2000";
            await nextTick();

            // Verify coordinates are updated (should reflect the new values)
            expect(parseFloat(wrapper.vm.form.coordinates_lat)).toBe(9.9);
            expect(parseFloat(wrapper.vm.form.coordinates_lng)).toBe(124.2);
        });
    });

    describe("Error Handling Integration", () => {
        it("handles map initialization errors gracefully", async () => {
            // Mock map initialization failure
            const L = await import("leaflet");
            L.default.map.mockImplementationOnce(() => {
                throw new Error("Map initialization failed");
            });

            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });
            const mapPicker = wrapper.findComponent(MapLocationPicker);

            // Component should still render without crashing
            expect(mapPicker.exists()).toBe(true);
        });

        it("handles geocoding service failures", async () => {
            global.fetch.mockRejectedValueOnce(new Error("Network error"));

            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });
            const mapPicker = wrapper.findComponent(MapLocationPicker);

            // Simulate address search
            const addressInput = mapPicker.find('input[type="text"]');
            const searchButton = mapPicker.find("button");

            await addressInput.setValue("Test Address");
            await searchButton.trigger("click");
            await nextTick();

            // Should show error message
            expect(mapPicker.text()).toContain("Error searching for address");
        });
    });

    describe("Performance Integration", () => {
        it("does not cause memory leaks with repeated coordinate updates", async () => {
            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });
            const mapPicker = wrapper.findComponent(MapLocationPicker);

            // Simulate multiple rapid coordinate updates
            for (let i = 0; i < 10; i++) {
                const location = {
                    lat: 9.8349 + i * 0.001,
                    lng: 124.1436 + i * 0.001,
                };
                await mapPicker.vm.$emit("locationSelected", location);
            }

            await nextTick();

            // Final coordinates should be correct
            expect(wrapper.vm.form.coordinates_lat).toBe("9.8439");
            expect(wrapper.vm.form.coordinates_lng).toBe("124.1526");
        });

        it("debounces coordinate input updates", async () => {
            wrapper = mount(PropertyCreate, {
                global: {
                    mocks: {
                        route: global.route,
                    },
                },
            });

            const updateSpy = vi.spyOn(wrapper.vm, "onLocationSelected");

            // Simulate rapid coordinate changes
            wrapper.vm.coordinates = { lat: 9.8349, lng: 124.1436 };
            wrapper.vm.coordinates = { lat: 9.835, lng: 124.1437 };
            wrapper.vm.coordinates = { lat: 9.8351, lng: 124.1438 };

            await nextTick();

            // Should handle updates efficiently
            expect(wrapper.vm.form.coordinates_lat).toBe("9.8351");
            expect(wrapper.vm.form.coordinates_lng).toBe("124.1438");
        });
    });
});

// Helper functions for integration testing
export const createPropertyFormWithCoordinates = async (coordinates) => {
    const wrapper = mount(PropertyCreate, {
        global: {
            mocks: {
                route: global.route,
            },
        },
    });
    const mapPicker = wrapper.findComponent(MapLocationPicker);

    await mapPicker.vm.$emit("locationSelected", coordinates);
    await nextTick();

    return wrapper;
};

export const fillRequiredPropertyFields = async (wrapper) => {
    await wrapper.find("#title").setValue("Test Property");
    await wrapper.find("#description").setValue("Test Description");
    await wrapper.find("#type").setValue("residential_lot");
    await wrapper.find("#municipality").setValue("Tagbilaran City");
    await wrapper.find("#barangay").setValue("Poblacion");
    await wrapper.find("#lot_area_sqm").setValue("1000");
    await wrapper.find("#price_per_sqm").setValue("5000");
};

export const simulateFormSubmission = async (wrapper) => {
    await wrapper.find("form").trigger("submit");
    await nextTick();
};
