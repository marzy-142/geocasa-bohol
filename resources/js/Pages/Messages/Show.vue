<template>
    <ModernDashboardLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('conversations.index')"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <ArrowLeftIcon class="h-5 w-5" />
                    </Link>
                    <div>
                        <h2
                            class="text-2xl font-bold text-gray-900 dark:text-white"
                        >
                            {{ getConversationTitle(conversation) }}
                        </h2>
                        <p
                            class="text-sm text-gray-600 dark:text-gray-400 mt-1"
                        >
                            {{ conversation.participants?.length || 0 }} participants
                            • {{ conversation.type }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button
                        @click="toggleArchive"
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                            conversation.is_archived
                                ? 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800'
                                : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-200 dark:hover:bg-yellow-800',
                        ]"
                    >
                        {{ conversation.is_archived ? "Unarchive" : "Archive" }}
                    </button>
                </div>
            </div>
        </template>

        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden h-[calc(100vh-200px)] flex flex-col"
        >
            <!-- Conversation Info -->
            <div
                v-if="conversation.inquiry || conversation.transaction"
                class="p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700"
            >
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div
                            :class="[
                                'w-12 h-12 rounded-lg flex items-center justify-center text-white font-semibold',
                                conversation.type === 'inquiry'
                                    ? 'bg-green-500'
                                    : 'bg-blue-500',
                            ]"
                        >
                            <BuildingOfficeIcon
                                v-if="conversation.type === 'inquiry'"
                                class="h-6 w-6"
                            />
                            <DocumentTextIcon v-else class="h-6 w-6" />
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            {{
                                conversation.inquiry?.property?.title ||
                                conversation.transaction?.property?.title
                            }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <span v-if="conversation.inquiry">
                                Inquiry from {{ conversation.inquiry.name }} •
                                {{
                                    formatTime(conversation.inquiry.created_at)
                                }}
                            </span>
                            <span v-else-if="conversation.transaction">
                                Transaction #{{
                                    conversation.transaction.transaction_number
                                }}
                                • {{ conversation.transaction.status }}
                            </span>
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <Link
                            v-if="conversation.inquiry"
                            :href="
                                route('inquiries.show', conversation.inquiry.id)
                            "
                            class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            View Inquiry
                        </Link>
                        <Link
                            v-else-if="conversation.transaction"
                            :href="
                                route(
                                    'transactions.show',
                                    conversation.transaction.id
                                )
                            "
                            class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            View Transaction
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div
                ref="messagesContainer"
                class="flex-1 overflow-y-auto p-6 space-y-4"
            >
                <div v-if="messages.length === 0" class="text-center py-12">
                    <ChatBubbleLeftRightIcon
                        class="mx-auto h-16 w-16 text-gray-400 mb-4"
                    />
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-white mb-2"
                    >
                        No messages yet
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Start the conversation by sending a message below
                    </p>
                </div>

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
                            'max-w-xs lg:max-w-md',
                            message.sender_id === $page.props.auth.user.id
                                ? 'order-1'
                                : 'order-2',
                        ]"
                    >
                        <!-- Message Bubble -->
                        <div
                            :class="[
                                'px-4 py-3 rounded-2xl shadow-sm',
                                message.sender_id === $page.props.auth.user.id
                                    ? 'bg-blue-600 text-white rounded-br-md'
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-bl-md',
                            ]"
                        >
                            <p class="text-sm leading-relaxed">
                                {{ message.content }}
                            </p>
                        </div>

                        <!-- Message Info -->
                        <div
                            :class="[
                                'flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400',
                                message.sender_id === $page.props.auth.user.id
                                    ? 'justify-end'
                                    : 'justify-start',
                            ]"
                        >
                            <span v-if="message.sender_id !== $page.props.auth.user.id">
                                {{ message.sender?.name || 'Unknown' }} •
                            </span>
                            <span class="ml-1">
                                {{ formatTime(message.created_at) }}
                            </span>
                        </div>
                    </div>

                    <!-- Avatar -->
                    <div
                        :class="[
                            'flex-shrink-0 mx-3',
                            message.sender_id === $page.props.auth.user.id
                                ? 'order-2'
                                : 'order-1',
                        ]"
                    >
                        <div
                            :class="[
                                'w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold',
                                message.sender_id === $page.props.auth.user.id
                                    ? 'bg-blue-600'
                                    : 'bg-gray-500',
                            ]"
                        >
                            {{ getInitials(message.sender?.name || $page.props.auth.user.name) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Input -->
            <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                <form @submit.prevent="sendMessage" class="flex space-x-4">
                    <div class="flex-1">
                        <input
                            ref="messageInput"
                            v-model="newMessage"
                            type="text"
                            placeholder="Type your message..."
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            :disabled="sending"
                            @keydown.enter.prevent="sendMessage"
                        />
                    </div>
                    <div class="flex items-end space-x-2">
                        <button
                            type="button"
                            class="p-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                        >
                            <PaperClipIcon class="h-5 w-5" />
                        </button>
                        <button
                            type="submit"
                            :disabled="!newMessage.trim() || sending"
                            class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center space-x-2"
                        >
                            <span v-if="!sending">Send</span>
                            <span v-else>Sending...</span>
                            <PaperAirplaneIcon
                                v-if="!sending"
                                class="h-4 w-4"
                            />
                            <div
                                v-else
                                class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
                            ></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from "vue";
import { router, Link } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    ArrowLeftIcon,
    ChatBubbleLeftRightIcon,
    PaperAirplaneIcon,
    PaperClipIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
} from "@heroicons/vue/24/outline";
import { formatDistanceToNow } from "date-fns";

const props = defineProps({
    conversation: {
        type: Object,
        required: true
    },
    messages: {
        type: Array,
        default: () => []
    },
});

const messages = ref(props.messages || []);
const newMessage = ref("");
const sending = ref(false);
const messagesContainer = ref(null);
const messageInput = ref(null);

const sendMessage = async () => {
    if (!newMessage.value.trim() || sending.value) return;

    sending.value = true;

    try {
        await router.post(
            route("conversations.send-message", props.conversation.id),
            {
                content: newMessage.value,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    newMessage.value = "";
                    scrollToBottom();
                    messageInput.value?.focus();
                },
            }
        );
    } finally {
        sending.value = false;
    }
};

const toggleArchive = async () => {
    const endpoint = props.conversation.is_archived
        ? "conversations.unarchive"
        : "conversations.archive";

    await router.post(
        route(endpoint, props.conversation.id),
        {},
        {
            preserveState: true,
        }
    );
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

    return "General Conversation";
};

const getInitials = (name) => {
    if (!name) return "?";
    return name
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

watch(
    () => props.messages,
    (newMessages) => {
        messages.value = newMessages || [];
        scrollToBottom();
    },
    { immediate: true }
);

onMounted(() => {
    scrollToBottom();
    messageInput.value?.focus();

    // Mark conversation as read
    router.post(
        route("conversations.mark-read", props.conversation.id),
        {},
        {
            preserveState: true,
        }
    );

    // Listen for new messages via Echo
    if (window.Echo && props.conversation.id) {
        window.Echo.private(`conversation.${props.conversation.id}`).listen(
            "MessageSent",
            (e) => {
                messages.value.push(e.message);
                scrollToBottom();
            }
        );
    }
});
</script>