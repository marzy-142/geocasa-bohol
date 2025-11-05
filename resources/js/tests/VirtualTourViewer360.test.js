// Panorama Feature Test Script
// Tests the VirtualTourViewer360 component functionality

import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import VirtualTourViewer360 from "@/Components/VirtualTourViewer360.vue";

describe("VirtualTourViewer360 Component", () => {
    let wrapper;

    const mockImageUrl = "/storage/properties/virtual-tours/test-panorama.jpg";

    beforeEach(() => {
        // Mock image loading
        global.Image = class MockImage {
            constructor() {
                setTimeout(() => {
                    if (this.onload) this.onload();
                }, 100);
            }
        };

        wrapper = mount(VirtualTourViewer360, {
            props: {
                imageUrl: mockImageUrl,
            },
        });
    });

    describe("Image Loading", () => {
        it("should display loading state initially", () => {
            expect(wrapper.find(".loading-state").exists()).toBe(true);
            expect(wrapper.find(".spinner").exists()).toBe(true);
            expect(wrapper.text()).toContain("Loading virtual tour...");
        });

        it("should display no image state when imageUrl is empty", async () => {
            const wrapperNoImage = mount(VirtualTourViewer360, {
                props: { imageUrl: "" },
            });

            expect(wrapperNoImage.find(".no-image-state").exists()).toBe(true);
            expect(wrapperNoImage.text()).toContain(
                "No virtual tour available"
            );
        });

        it("should display error state on image load failure", async () => {
            await wrapper.vm.onImageError();
            await wrapper.vm.$nextTick();

            expect(wrapper.find(".error-state").exists()).toBe(true);
            expect(wrapper.text()).toContain("Unable to load tour");
        });

        it("should display image after successful load", async () => {
            await wrapper.vm.onImageLoad();
            await wrapper.vm.$nextTick();

            expect(wrapper.find(".image-display").exists()).toBe(true);
            expect(wrapper.find(".tour-image").exists()).toBe(true);
        });
    });

    describe("Navigation Controls", () => {
        beforeEach(async () => {
            await wrapper.vm.onImageLoad();
            await wrapper.vm.$nextTick();
        });

        it("should have zoom in and zoom out controls", () => {
            const controls = wrapper.findAll(".control-btn");
            expect(controls.length).toBeGreaterThanOrEqual(3); // Zoom In, Zoom Out, Reset
        });

        it("should handle zoom in functionality", async () => {
            const initialScale = wrapper.vm.scale;
            await wrapper.vm.zoomIn();

            expect(wrapper.vm.scale).toBeGreaterThan(initialScale);
            expect(wrapper.vm.scale).toBeLessThanOrEqual(4); // max zoom limit
        });

        it("should handle zoom out functionality", async () => {
            wrapper.vm.scale = 2; // Set initial zoom
            const initialScale = wrapper.vm.scale;
            await wrapper.vm.zoomOut();

            expect(wrapper.vm.scale).toBeLessThan(initialScale);
            expect(wrapper.vm.scale).toBeGreaterThanOrEqual(1); // min zoom limit
        });

        it("should reset view correctly", async () => {
            // Set some transformations
            wrapper.vm.scale = 2;
            wrapper.vm.translateX = 100;
            wrapper.vm.translateY = 50;

            await wrapper.vm.resetView();

            expect(wrapper.vm.scale).toBe(1);
            expect(wrapper.vm.translateX).toBe(0);
            expect(wrapper.vm.translateY).toBe(0);
        });
    });

    describe("Pan/Drag Functionality", () => {
        beforeEach(async () => {
            await wrapper.vm.onImageLoad();
            await wrapper.vm.$nextTick();
        });

        it("should start dragging on mouse down when zoomed", async () => {
            wrapper.vm.scale = 2; // Zoom in first

            const imageContainer = wrapper.find(".image-container");
            await imageContainer.trigger("mousedown", {
                clientX: 100,
                clientY: 100,
            });

            expect(wrapper.vm.isDragging).toBe(true);
        });

        it("should not start dragging when not zoomed", async () => {
            const imageContainer = wrapper.find(".image-container");
            await imageContainer.trigger("mousedown", {
                clientX: 100,
                clientY: 100,
            });

            expect(wrapper.vm.isDragging).toBe(false);
        });

        it("should update position during drag", async () => {
            wrapper.vm.scale = 2;
            wrapper.vm.isDragging = true;
            wrapper.vm.dragStartX = 100;
            wrapper.vm.dragStartY = 100;

            const imageContainer = wrapper.find(".image-container");
            await imageContainer.trigger("mousemove", {
                clientX: 150,
                clientY: 120,
            });

            expect(wrapper.vm.translateX).toBe(50);
            expect(wrapper.vm.translateY).toBe(20);
        });

        it("should stop dragging on mouse up", async () => {
            wrapper.vm.isDragging = true;

            const imageContainer = wrapper.find(".image-container");
            await imageContainer.trigger("mouseup");

            expect(wrapper.vm.isDragging).toBe(false);
        });
    });

    describe("Touch Controls", () => {
        beforeEach(async () => {
            await wrapper.vm.onImageLoad();
            await wrapper.vm.$nextTick();
        });

        it("should handle touch start for single touch", async () => {
            wrapper.vm.scale = 2;

            const imageContainer = wrapper.find(".image-container");
            await imageContainer.trigger("touchstart", {
                touches: [{ clientX: 100, clientY: 100 }],
            });

            expect(wrapper.vm.isDragging).toBe(true);
        });

        it("should handle pinch to zoom with two fingers", async () => {
            const imageContainer = wrapper.find(".image-container");

            // Start pinch
            await imageContainer.trigger("touchstart", {
                touches: [
                    { clientX: 100, clientY: 100 },
                    { clientX: 200, clientY: 100 },
                ],
            });

            expect(wrapper.vm.initialPinchDistance).toBeDefined();
            expect(wrapper.vm.initialScale).toBe(wrapper.vm.scale);
        });
    });

    describe("Mouse Wheel Zoom", () => {
        beforeEach(async () => {
            await wrapper.vm.onImageLoad();
            await wrapper.vm.$nextTick();
        });

        it("should zoom in on wheel up", async () => {
            const initialScale = wrapper.vm.scale;
            const imageContainer = wrapper.find(".image-container");

            await imageContainer.trigger("wheel", { deltaY: -100 });

            expect(wrapper.vm.scale).toBeGreaterThan(initialScale);
        });

        it("should zoom out on wheel down", async () => {
            wrapper.vm.scale = 2;
            const initialScale = wrapper.vm.scale;
            const imageContainer = wrapper.find(".image-container");

            await imageContainer.trigger("wheel", { deltaY: 100 });

            expect(wrapper.vm.scale).toBeLessThan(initialScale);
        });

        it("should reset position when zooming out to minimum", async () => {
            wrapper.vm.scale = 1.1;
            wrapper.vm.translateX = 50;
            wrapper.vm.translateY = 30;

            const imageContainer = wrapper.find(".image-container");
            await imageContainer.trigger("wheel", { deltaY: 100 });

            expect(wrapper.vm.scale).toBe(1);
            expect(wrapper.vm.translateX).toBe(0);
            expect(wrapper.vm.translateY).toBe(0);
        });
    });

    describe("Image URL Resolution", () => {
        it("should handle absolute URLs correctly", () => {
            const wrapper = mount(VirtualTourViewer360, {
                props: { imageUrl: "https://example.com/image.jpg" },
            });

            expect(wrapper.vm.resolvedImageUrl).toBe(
                "https://example.com/image.jpg"
            );
        });

        it("should handle storage URLs correctly", () => {
            const wrapper = mount(VirtualTourViewer360, {
                props: { imageUrl: "/storage/test.jpg" },
            });

            expect(wrapper.vm.resolvedImageUrl).toBe("/storage/test.jpg");
        });

        it("should resolve relative paths to storage", () => {
            const wrapper = mount(VirtualTourViewer360, {
                props: { imageUrl: "test.jpg" },
            });

            expect(wrapper.vm.resolvedImageUrl).toBe(
                "/storage/properties/virtual-tours/test.jpg"
            );
        });
    });

    describe("Error Handling", () => {
        it("should provide retry functionality on load error", async () => {
            await wrapper.vm.onImageError();
            await wrapper.vm.$nextTick();

            const retryBtn = wrapper.find(".retry-btn");
            expect(retryBtn.exists()).toBe(true);

            // Mock successful retry
            wrapper.vm.onImageLoad = vi.fn();
            await retryBtn.trigger("click");

            expect(wrapper.vm.loadError).toBe(false);
            expect(wrapper.vm.loading).toBe(true);
        });

        it("should handle timeout on slow loading", async () => {
            vi.useFakeTimers();

            const wrapper = mount(VirtualTourViewer360, {
                props: { imageUrl: "slow-loading-image.jpg" },
            });

            // Fast forward timeout
            vi.advanceTimersByTime(5000);
            await flushPromises();

            expect(wrapper.vm.loadError).toBe(true);
            expect(wrapper.vm.loading).toBe(false);

            vi.useRealTimers();
        });
    });

    describe("Performance and Transitions", () => {
        beforeEach(async () => {
            await wrapper.vm.onImageLoad();
            await wrapper.vm.$nextTick();
        });

        it("should apply smooth transitions when not dragging", () => {
            const expectedStyle = wrapper.vm.imageStyle;
            expect(expectedStyle.transition).toContain(
                "transform 0.3s ease-out"
            );
        });

        it("should disable transitions during dragging", () => {
            wrapper.vm.isDragging = true;
            const expectedStyle = wrapper.vm.imageStyle;
            expect(expectedStyle.transition).toBe("none");
        });

        it("should update transform style correctly", () => {
            wrapper.vm.scale = 1.5;
            wrapper.vm.translateX = 10;
            wrapper.vm.translateY = 20;

            const expectedStyle = wrapper.vm.imageStyle;
            expect(expectedStyle.transform).toBe(
                "scale(1.5) translate(10px, 20px)"
            );
        });
    });

    describe("Accessibility and UX", () => {
        beforeEach(async () => {
            await wrapper.vm.onImageLoad();
            await wrapper.vm.$nextTick();
        });

        it("should show help tooltip initially", () => {
            expect(wrapper.vm.showHelp).toBe(true);
            expect(wrapper.find(".help-tooltip").exists()).toBe(true);
        });

        it("should hide help tooltip after interaction", async () => {
            await wrapper.vm.zoomIn();
            expect(wrapper.vm.showHelp).toBe(false);
        });

        it("should have proper aria labels and alt text", () => {
            const image = wrapper.find(".tour-image");
            expect(image.attributes("alt")).toBe("Property Virtual Tour");
        });

        it("should prevent default drag behavior on image", () => {
            const image = wrapper.find(".tour-image");
            expect(image.attributes("draggable")).toBe("false");
        });
    });
});
