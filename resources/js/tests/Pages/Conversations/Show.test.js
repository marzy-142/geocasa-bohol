import { mount } from "@vue/test-utils";
import { describe, it, expect, vi } from "vitest";
import ConversationShow from "@/Pages/Conversations/Show.vue";

describe("ConversationShow", () => {
    const mockConversation = {
        id: 1,
        title: "Property Inquiry Discussion",
        type: "inquiry",
        participants: [1, 2],
        messages: [
            {
                id: 1,
                content: "Hello, I am interested in this property.",
                sender: { id: 1, name: "John Client" },
                created_at: "2024-01-15T10:00:00Z",
            },
        ],
    };

    const createWrapper = (props = {}) => {
        return mount(ConversationShow, {
            props: {
                conversation: mockConversation,
                messages: mockConversation.messages,
                ...props
            },
            global: {
                mocks: {
                    $page: {
                        props: {
                            auth: { user: { id: 1, name: 'Test User' } },
                            flash: {},
                            errors: {}
                        },
                        url: '/conversations/1',
                        component: 'Conversations/Show',
                        version: '1.0.0'
                    },
                    route: vi.fn((name) => {
                        const routes = {
                            'conversations.mark-read': '/conversations/mark-read',
                            'conversations.show': '/conversations/1',
                            'client.dashboard': '/client/dashboard',
                            'broker.dashboard': '/broker/dashboard'
                        };
                        const routeUrl = routes[name] || '/test-route';
                        
                        // Add current method to the returned object
                        const routeObj = {
                            toString: () => routeUrl,
                            current: vi.fn((pattern) => {
                                if (pattern === '*.dashboard') {
                                    return true;
                                }
                                return false;
                            })
                        };
                        
                        return name ? routeUrl : routeObj;
                    })
                }
            }
        });
    };

    it("displays conversation messages", () => {
        const wrapper = createWrapper();

        expect(wrapper.text()).toContain(
            "Hello, I am interested in this property."
        );
        expect(wrapper.text()).toContain("John Client");
    });

    it("shows message input form", () => {
        const wrapper = createWrapper();

        expect(wrapper.find("textarea").exists()).toBe(true);
        expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    });
});
