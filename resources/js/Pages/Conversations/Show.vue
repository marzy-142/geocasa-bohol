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
                            {{ conversation.participants.length }} participants
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
                    class="flex items-start space-x-3"
                >
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div
                            class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-xs"
                        >
                            {{ getInitials(message.sender?.name) }}
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-2 mb-1">
                            <span
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                {{ message.sender?.name }}
                            </span>
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ formatTime(message.created_at) }}
                            </span>
                            <span
                                v-if="message.is_edited"
                                class="text-xs text-gray-400 italic"
                            >
                                (edited)
                            </span>
                        </div>

                        <div
                            :class="[
                                'inline-block px-4 py-2 rounded-lg max-w-md',
                                message.sender_id === page.props.auth.user.id
                                    ? 'bg-blue-600 text-white ml-auto'
                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white',
                            ]"
                        >
                            <p class="text-sm whitespace-pre-wrap">
                                {{ message.content }}
                            </p>
                        </div>

                        <!-- Attachments -->
                        <div
                            v-if="
                                message.attachments &&
                                message.attachments.length > 0
                            "
                            class="mt-2 space-y-2"
                        >
                            <div
                                v-for="attachment in message.attachments"
                                :key="attachment.id"
                                class="flex items-center space-x-2 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg"
                            >
                                <PaperClipIcon class="h-4 w-4 text-gray-400" />
                                <a
                                    :href="attachment.url"
                                    target="_blank"
                                    class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                >
                                    {{ attachment.name }}
                                </a>
                                <span class="text-xs text-gray-500"
                                    >({{
                                        formatFileSize(attachment.size)
                                    }})</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Input -->
            <div
                class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900"
            >
                <form @submit.prevent="sendMessage" class="space-y-3">
                    <div class="flex space-x-3">
                        <textarea
                            v-model="newMessage"
                            ref="messageInput"
                            @keydown.enter.exact.prevent="sendMessage"
                            @keydown.enter.shift.exact="newMessage += '\n'"
                            placeholder="Type your message... (Shift+Enter for new line)"
                            rows="3"
                            class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            :disabled="sending"
                        ></textarea>
                        <div class="flex flex-col space-y-2">
                            <button
                                type="submit"
                                :disabled="!newMessage.trim() || sending"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center justify-center"
                            >
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
                    </div>
                </form>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from "vue";
import { router, Link, usePage } from "@inertiajs/vue3";
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
    conversation: Object,
    messages: Array,
});

const page = usePage();

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

const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
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
    window.Echo.private(`conversation.${props.conversation.id}`).listen(
        "MessageSent",
        (e) => {
            messages.value.push(e.message);
            scrollToBottom();
        }
    );
});
</script>
