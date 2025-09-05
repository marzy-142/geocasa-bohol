<template>
    <button
        @click="startConversation"
        :disabled="loading"
        :class="[
            'inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-colors',
            variant === 'primary'
                ? 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2',
            'disabled:opacity-50 disabled:cursor-not-allowed',
        ]"
    >
        <ChatBubbleBottomCenterTextIcon v-if="!loading" class="h-4 w-4 mr-2" />
        <div
            v-else
            class="animate-spin rounded-full h-4 w-4 border-b-2 border-current mr-2"
        ></div>
        {{ loading ? "Starting..." : "Start Conversation" }}
    </button>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { ChatBubbleBottomCenterTextIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    type: {
        type: String,
        required: true,
        validator: (value) => ["inquiry", "transaction"].includes(value),
    },
    id: {
        type: Number,
        required: true,
    },
    variant: {
        type: String,
        default: "primary",
        validator: (value) => ["primary", "secondary"].includes(value),
    },
});

const loading = ref(false);

const startConversation = async () => {
    if (loading.value) return;

    loading.value = true;

    try {
        const endpoint =
            props.type === "inquiry"
                ? route("conversations.create-inquiry", props.id)
                : route("conversations.create-transaction", props.id);

        await router.post(
            endpoint,
            {},
            {
                onSuccess: (page) => {
                    // Redirect to the conversation
                    if (page.props.conversation) {
                        router.visit(
                            route(
                                "conversations.show",
                                page.props.conversation.id
                            )
                        );
                    }
                },
                onError: (errors) => {
                    console.error("Failed to create conversation:", errors);
                },
            }
        );
    } finally {
        loading.value = false;
    }
};
</script>
