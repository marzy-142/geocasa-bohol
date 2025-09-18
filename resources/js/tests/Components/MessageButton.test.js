import { mount } from "@vue/test-utils";
import { describe, it, expect, vi } from "vitest";
import MessageButton from "@/Components/MessageButton.vue";

// Mock Inertia router
vi.mock("@inertiajs/vue3", () => ({
    router: {
        visit: vi.fn(),
        post: vi.fn(),
        get: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
    },
}));

const { router } = await import("@inertiajs/vue3");

describe("MessageButton", () => {
    it("renders message button with correct text", () => {
        const wrapper = mount(MessageButton, {
            props: {
                type: "inquiry",
                id: 1,
            },
        });

        expect(wrapper.text()).toContain("Start Conversation");
        expect(wrapper.find("button").exists()).toBe(true);
    });

    it("calls router.post when clicked", async () => {
        const wrapper = mount(MessageButton, {
            props: {
                type: "inquiry",
                id: 1,
            },
        });

        await wrapper.find("button").trigger("click");

        expect(router.post).toHaveBeenCalledWith(
            "/conversations.create-inquiry",
            {},
            expect.objectContaining({
                onSuccess: expect.any(Function),
            })
        );
    });
});
