import { mount } from "@vue/test-utils";
import { describe, it, expect, beforeEach, vi } from "vitest";
import VirtualTourViewer from "@/Components/VirtualTourViewer.vue";

// Mock Modal component
const MockModal = {
    name: 'Modal',
    props: ['show'],
    emits: ['close'],
    template: '<div v-if="show" class="modal-mock"><slot /></div>'
};

// Mock SecondaryButton component
const MockSecondaryButton = {
    name: 'SecondaryButton',
    template: '<button class="secondary-button-mock"><slot /></button>'
};

describe("VirtualTourViewer", () => {
    let wrapper;

    const mockTourImages = [
        { url: "/storage/tours/sample-360.jpg", thumbnail: "/storage/tours/sample-360-thumb.jpg" }
    ];
    
    const mockHotspots = [
        {
            id: 1,
            x: 50,
            y: 30,
            title: "Living Room",
            description: "Spacious living area",
            image_index: 0
        },
        { 
            id: 2,
            x: 70, 
            y: 60, 
            title: "Kitchen", 
            description: "Modern kitchen",
            image_index: 0
        },
    ];

    beforeEach(() => {
        wrapper = mount(VirtualTourViewer, {
            props: {
                tourImages: mockTourImages,
                hotspots: mockHotspots,
            },
            global: {
                components: {
                    Modal: MockModal,
                    SecondaryButton: MockSecondaryButton
                }
            }
        });
    });

    it("renders virtual tour viewer when tour images are provided", () => {
        expect(wrapper.find(".virtual-tour-viewer").exists()).toBe(true);
        expect(wrapper.find(".panorama-container").exists()).toBe(true);
    });

    it("displays hotspots correctly", () => {
        const hotspots = wrapper.findAll(".hotspot");
        expect(hotspots).toHaveLength(2);
    });

    it("shows hotspot details on click", async () => {
        const firstHotspot = wrapper.find(".hotspot");
        await firstHotspot.trigger("click");

        // Check if modal is shown (component uses Modal component)
        expect(wrapper.vm.showHotspotModal).toBe(true);
        expect(wrapper.vm.selectedHotspot.title).toBe("Living Room");
    });

    it("handles zoom controls", async () => {
        const zoomButtons = wrapper.findAll('.zoom-btn');
        
        expect(zoomButtons.length).toBeGreaterThanOrEqual(2);
        
        const initialZoom = wrapper.vm.zoom;
        await zoomButtons[0].trigger("click"); // zoom in
        expect(wrapper.vm.zoom).toBeGreaterThan(initialZoom);
    });

    it("handles pan controls", async () => {
        const panoramaImage = wrapper.find(".panorama-image");

        await panoramaImage.trigger("mousedown", { clientX: 100, clientY: 100 });
        expect(wrapper.vm.isDragging).toBe(true);
        
        await panoramaImage.trigger("mouseup");
        expect(wrapper.vm.isDragging).toBe(false);
    });
});
