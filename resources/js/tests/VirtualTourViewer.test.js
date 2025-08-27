import { mount } from "@vue/test-utils";
import { describe, it, expect, beforeEach } from "vitest";
import VirtualTourViewer from "@/Components/VirtualTourViewer.vue";

describe("VirtualTourViewer", () => {
    let wrapper;

    const mockProperty = {
        virtual_tour_images: ["/storage/tours/sample-360.jpg"],
        tour_hotspots: [
            {
                x: 50,
                y: 30,
                title: "Living Room",
                description: "Spacious living area",
            },
            { x: 70, y: 60, title: "Kitchen", description: "Modern kitchen" },
        ],
    };

    beforeEach(() => {
        wrapper = mount(VirtualTourViewer, {
            props: {
                property: mockProperty,
            },
        });
    });

    it("renders virtual tour viewer when property has virtual tour", () => {
        expect(wrapper.find(".virtual-tour-container").exists()).toBe(true);
    });

    it("displays hotspots correctly", () => {
        const hotspots = wrapper.findAll(".hotspot");
        expect(hotspots).toHaveLength(2);
    });

    it("shows hotspot details on click", async () => {
        const firstHotspot = wrapper.find(".hotspot");
        await firstHotspot.trigger("click");

        expect(wrapper.find(".hotspot-details").exists()).toBe(true);
        expect(wrapper.text()).toContain("Living Room");
        expect(wrapper.text()).toContain("Spacious living area");
    });

    it("handles zoom controls", async () => {
        const zoomInBtn = wrapper.find('[data-testid="zoom-in"]');
        const zoomOutBtn = wrapper.find('[data-testid="zoom-out"]');

        expect(zoomInBtn.exists()).toBe(true);
        expect(zoomOutBtn.exists()).toBe(true);

        await zoomInBtn.trigger("click");
        // Test zoom functionality
    });

    it("handles pan controls", async () => {
        const tourImage = wrapper.find(".tour-image");

        await tourImage.trigger("mousedown", { clientX: 100, clientY: 100 });
        await tourImage.trigger("mousemove", { clientX: 150, clientY: 150 });
        await tourImage.trigger("mouseup");

        // Test pan functionality
    });
});
