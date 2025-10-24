<template>
    <ModernDashboardLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2
                        class="text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        Messages
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Manage your conversations and communications
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                    >
                        {{ unreadCount }} unread
                    </span>
                </div>
            </div>
        </template>

        <div
            class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-200px)]"
        >
            <!-- Conversations List -->
            <div
                class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
            >
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Conversations
                        </h3>
                        <button
                            @click="showArchived = !showArchived"
                            class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            {{ showArchived ? "Show Active" : "Show Archived" }}
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="relative">
                        <MagnifyingGlassIcon
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search conversations..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                </div>

                <div
                    class="overflow-y-auto h-full"
                    style="max-height: calc(100vh - 350px)"
                >
                    <div
                        v-if="filteredConversations.length === 0"
                        class="p-6 text-center text-gray-500 dark:text-gray-400"
                    >
                        <ChatBubbleLeftRightIcon
                            class="mx-auto h-12 w-12 mb-3 opacity-50"
                        />
                        <p>No conversations found</p>
                    </div>

                    <div
                        v-for="conversation in filteredConversations"
                        :key="conversation.id"
                        @click="selectConversation(conversation)"
                        :class="[
                            'p-4 border-b border-gray-100 dark:border-gray-700 cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-gray-700',
                            selectedConversation?.id === conversation.id
                                ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700'
                                : '',
                        ]"
                    >
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-sm"
                                >
                                    {{ getConversationInitials(conversation) }}
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h4
                                        class="text-sm font-medium text-gray-900 dark:text-white truncate"
                                    >
                                        {{ getConversationTitle(conversation) }}
                                    </h4>
                                    <div class="flex items-center space-x-2">
                                        <span
                                            v-if="conversation.unread_count > 0"
                                            class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full"
                                        >
                                            {{ conversation.unread_count }}
                                        </span>
                                        <span
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                formatTime(
                                                    conversation.last_message_at
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-400 truncate mt-1"
                                >
                                    {{
                                        conversation.last_message?.content ||
                                        "No messages yet"
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div
                class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col"
            >
                <template v-if="selectedConversation">
                    <!-- Conversation Header -->
                    <div
                        class="p-4 border-b border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-white"
                                >
                                    {{
                                        getConversationTitle(
                                            selectedConversation
                                        )
                                    }}
                                </h3>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{
                                        selectedConversation.participants_count ||
                                        selectedConversation.participants
                                            ?.length ||
                                        0
                                    }}
                                    participants
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="toggleArchive(selectedConversation)"
                                    :class="[
                                        'px-3 py-1 text-xs font-medium rounded-full transition-colors',
                                        selectedConversation.is_archived
                                            ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800'
                                            : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-200 dark:hover:bg-yellow-800',
                                    ]"
                                >
                                    {{
                                        selectedConversation.is_archived
                                            ? "Unarchive"
                                            : "Archive"
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Area -->
                    <div
                        ref="messagesContainer"
                        class="flex-1 overflow-y-auto p-4 space-y-4"
                        style="max-height: calc(100vh - 400px)"
                    >
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            :class="[
                                'flex',
                                message.sender_id === $page.props.auth.user.id
                                    ? 'justify-end'
                                    : 'justify-start',
                            ]"
                        >
                            <div
                                :class="[
                                    'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
                                    message.sender_id ===
                                    $page.props.auth.user.id
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white',
                                ]"
                            >
                                <p class="text-sm">{{ message.content }}</p>
                                <p
                                    :class="[
                                        'text-xs mt-1',
                                        message.sender_id ===
                                        $page.props.auth.user.id
                                            ? 'text-blue-100'
                                            : 'text-gray-500 dark:text-gray-400',
                                    ]"
                                >
                                    {{ formatTime(message.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div
                        class="p-4 border-t border-gray-200 dark:border-gray-700"
                    >
                        <form
                            @submit.prevent="sendMessage"
                            class="flex space-x-2"
                        >
                            <input
                                v-model="newMessage"
                                type="text"
                                placeholder="Type your message..."
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :disabled="sending"
                            />
                            <button
                                type="submit"
                                :disabled="!newMessage.trim() || sending"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <PaperAirplaneIcon class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </template>

                <!-- No Conversation Selected -->
                <template v-else>
                    <div class="flex-1 flex items-center justify-center">
                        <div class="text-center">
                            <ChatBubbleLeftRightIcon
                                class="mx-auto h-16 w-16 text-gray-400 mb-4"
                            />
                            <h3
                                class="text-lg font-medium text-gray-900 dark:text-white mb-2"
                            >
                                Select a conversation
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                Choose a conversation from the list to start
                                messaging
                            </p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch, onUnmounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    ChatBubbleLeftRightIcon,
    MagnifyingGlassIcon,
    PaperAirplaneIcon,
} from "@heroicons/vue/24/outline";
import { formatDistanceToNow } from "date-fns";

const props = defineProps({
    conversations: {
        type: Object,
        default: () => ({ data: [] }),
    },
    selectedConversationId: {
        type: [String, Number],
        default: null,
    },
    messages: {
        type: Array,
        default: () => [],
    },
    totalUnread: {
        type: Number,
        default: 0,
    },
});

const page = usePage();

const selectedConversation = ref(null);
const messages = ref(props.messages || []);
const newMessage = ref("");
const sending = ref(false);
const searchQuery = ref("");
const showArchived = ref(false);
const messagesContainer = ref(null);
let echoListener = null;

const unreadCount = computed(() => {
    const conversationsData = props.conversations.data || [];
    return conversationsData.reduce(
        (count, conv) => count + (conv.unread_count || 0),
        0
    );
});

const filteredConversations = computed(() => {
    const conversationsData = props.conversations.data || [];
    let filtered = conversationsData.filter((conv) => {
        const matchesSearch =
            !searchQuery.value ||
            getConversationTitle(conv)
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase());
        const matchesArchived = showArchived.value
            ? conv.is_archived
            : !conv.is_archived;
        return matchesSearch && matchesArchived;
    });

    // Sort conversations by last message date (newest first)
    return filtered.sort((a, b) => {
        return new Date(b.last_message_at) - new Date(a.last_message_at);
    });
});

const selectConversation = (conversation) => {
    selectedConversation.value = conversation;
    router.get(
        route("conversations.show", conversation.id),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                setupEchoListener();
            },
        }
    );
};

const sendMessage = async () => {
    if (
        !newMessage.value.trim() ||
        !selectedConversation.value ||
        sending.value
    )
        return;

    sending.value = true;

    try {
        await router.post(
            route("conversations.send-message", selectedConversation.value.id),
            {
                content: newMessage.value,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    newMessage.value = "";
                    scrollToBottom();
                },
            }
        );
    } catch (error) {
        console.error("Error sending message:", error);
    } finally {
        sending.value = false;
    }
};

const toggleArchive = async (conversation) => {
    const endpoint = conversation.is_archived
        ? "conversations.unarchive"
        : "conversations.archive";

    try {
        await router.post(
            route(endpoint, conversation.id),
            {},
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    } catch (error) {
        console.error("Error toggling archive status:", error);
    }
};

const getConversationTitle = (conversation) => {
    if (conversation.title) return conversation.title;

    if (conversation.type === "inquiry" && conversation.inquiry) {
        return `Inquiry: ${
            conversation.inquiry.property?.title || "Property Inquiry"
        }`;
    }

    if (conversation.type === "transaction" && conversation.transaction) {
        return `Transaction: ${
            conversation.transaction.property?.title || "Property Transaction"
        }`;
    }

    // If it's a direct conversation, show participant names
    if (
        conversation.participant_users &&
        conversation.participant_users.length > 0
    ) {
        return conversation.participant_users
            .filter((participant) => participant.id !== page.props.auth.user.id)
            .map((participant) => participant.name)
            .join(", ");
    }

    // Fallback to participants array if participant_users is not available
    if (conversation.participants && conversation.participants.length > 0) {
        return conversation.participants
            .filter((participant) => participant.id !== page.props.auth.user.id)
            .map((participant) => participant.name)
            .join(", ");
    }

    return "General Conversation";
};

const getConversationInitials = (conversation) => {
    const title = getConversationTitle(conversation);
    return title
        .split(" ")
        .map((word) => word[0])
        .join("")
        .substring(0, 2)
        .toUpperCase();
};

const formatTime = (timestamp) => {
    if (!timestamp) return "";
    return formatDistanceToNow(new Date(timestamp), { addSuffix: true });
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop =
                messagesContainer.value.scrollHeight;
        }
    });
};

const setupEchoListener = () => {
    // Remove existing listener if any
    if (echoListener) {
        echoListener.stopListening();
    }

    // Set up new listener for the selected conversation
    if (selectedConversation.value && window.Echo) {
        echoListener = window.Echo.private(
            `conversation.${selectedConversation.value.id}`
        ).listen("MessageSent", (e) => {
            messages.value.push(e.message);
            scrollToBottom();
        });
    }
};

watch(
    () => props.messages,
    (newMessages) => {
        messages.value = newMessages || [];
        scrollToBottom();
    },
    { immediate: true }
);

watch(
    () => props.selectedConversationId,
    (newId) => {
        if (newId) {
            const conversation = props.conversations.data.find(
                (c) => c.id === newId
            );
            if (conversation) {
                selectedConversation.value = conversation;
                setupEchoListener();
            }
        }
    },
    { immediate: true }
);

onMounted(() => {
    // Check user authentication status
    console.log("User:", window.Laravel?.user || "Not logged in");
    // Or check Inertia page props
    console.log("Auth user:", page.props?.auth?.user);

    scrollToBottom();
    setupEchoListener();
});

onUnmounted(() => {
    // Clean up Echo listener when component is destroyed
    if (echoListener) {
        echoListener.stopListening();
    }
});
</script>
