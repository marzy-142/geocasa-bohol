<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import { ref, computed } from "vue";
import {
    UserIcon,
    ArrowLeftIcon,
    ChatBubbleLeftRightIcon,
    SpeakerWaveIcon,
    EnvelopeIcon,
    BellIcon,
    PaperAirplaneIcon,
    UsersIcon,
    EyeIcon,
    TrashIcon,
    PencilIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    MegaphoneIcon,
    DocumentTextIcon,
    CalendarIcon,
    TagIcon,
    FunnelIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    PlusIcon,
    ArrowPathIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    brokers: Array,
    communications: Array,
    templates: Array,
    stats: Object,
    filters: Object,
});

// Communication form
const communicationForm = useForm({
    type: "notification",
    title: "",
    message: "",
    recipients: [],
    priority: "normal",
    scheduled_at: null,
    template_id: null,
    tags: [],
    attachments: [],
});

// Template form
const templateForm = useForm({
    name: "",
    type: "notification",
    subject: "",
    content: "",
    variables: [],
});

// Modal states
const showCommunicationModal = ref(false);
const showTemplateModal = ref(false);
const showMessageDetails = ref(false);
const selectedMessage = ref(null);
const showBulkActions = ref(false);
const selectedCommunications = ref([]);

// Filter states
const searchQuery = ref(props.filters?.search || "");
const selectedType = ref(props.filters?.type || "");
const selectedStatus = ref(props.filters?.status || "");
const selectedPriority = ref(props.filters?.priority || "");
const dateRange = ref({
    start: props.filters?.date_start || "",
    end: props.filters?.date_end || "",
});

// Communication types
const communicationTypes = {
    notification: {
        name: "Notification",
        icon: BellIcon,
        color: "blue",
        description: "System notifications and alerts",
    },
    announcement: {
        name: "Announcement",
        icon: MegaphoneIcon,
        color: "green",
        description: "General announcements and updates",
    },
    email: {
        name: "Email",
        icon: EnvelopeIcon,
        color: "purple",
        description: "Direct email messages",
    },
    sms: {
        name: "SMS",
        icon: ChatBubbleLeftRightIcon,
        color: "yellow",
        description: "Text messages",
    },
    alert: {
        name: "Alert",
        icon: ExclamationTriangleIcon,
        color: "red",
        description: "Important alerts and warnings",
    },
};

// Priority levels
const priorityLevels = {
    low: { name: "Low", color: "gray", class: "bg-gray-100 text-gray-800" },
    normal: {
        name: "Normal",
        color: "blue",
        class: "bg-blue-100 text-blue-800",
    },
    high: {
        name: "High",
        color: "yellow",
        class: "bg-yellow-100 text-yellow-800",
    },
    urgent: { name: "Urgent", color: "red", class: "bg-red-100 text-red-800" },
};

// Status types
const statusTypes = {
    draft: { name: "Draft", color: "gray", class: "bg-gray-100 text-gray-800" },
    scheduled: {
        name: "Scheduled",
        color: "blue",
        class: "bg-blue-100 text-blue-800",
    },
    sent: {
        name: "Sent",
        color: "green",
        class: "bg-green-100 text-green-800",
    },
    failed: { name: "Failed", color: "red", class: "bg-red-100 text-red-800" },
};

// Computed properties
const filteredCommunications = computed(() => {
    let filtered = props.communications || [];

    if (searchQuery.value) {
        filtered = filtered.filter(
            (comm) =>
                comm.title
                    .toLowerCase()
                    .includes(searchQuery.value.toLowerCase()) ||
                comm.message
                    .toLowerCase()
                    .includes(searchQuery.value.toLowerCase())
        );
    }

    if (selectedType.value) {
        filtered = filtered.filter((comm) => comm.type === selectedType.value);
    }

    if (selectedStatus.value) {
        filtered = filtered.filter(
            (comm) => comm.status === selectedStatus.value
        );
    }

    if (selectedPriority.value) {
        filtered = filtered.filter(
            (comm) => comm.priority === selectedPriority.value
        );
    }

    return filtered;
});

const selectedBrokers = computed(() => {
    return (
        props.brokers?.filter((broker) =>
            communicationForm.recipients.includes(broker.id)
        ) || []
    );
});

const canSendCommunication = computed(() => {
    return (
        communicationForm.title &&
        communicationForm.message &&
        communicationForm.recipients.length > 0
    );
});

// Methods
const openCommunicationModal = (type = "notification") => {
    communicationForm.reset();
    communicationForm.type = type;
    showCommunicationModal.value = true;
};

const closeCommunicationModal = () => {
    showCommunicationModal.value = false;
    communicationForm.reset();
};

const openTemplateModal = () => {
    templateForm.reset();
    showTemplateModal.value = true;
};

const closeTemplateModal = () => {
    showTemplateModal.value = false;
    templateForm.reset();
};

const viewMessageDetails = (message) => {
    selectedMessage.value = message;
    showMessageDetails.value = true;
};

const closeMessageDetails = () => {
    showMessageDetails.value = false;
    selectedMessage.value = null;
};

const toggleBrokerSelection = (brokerId) => {
    const index = communicationForm.recipients.indexOf(brokerId);
    if (index > -1) {
        communicationForm.recipients.splice(index, 1);
    } else {
        communicationForm.recipients.push(brokerId);
    }
};

const selectAllBrokers = () => {
    communicationForm.recipients =
        props.brokers?.map((broker) => broker.id) || [];
};

const clearAllBrokers = () => {
    communicationForm.recipients = [];
};

const applyTemplate = (template) => {
    communicationForm.title = template.subject;
    communicationForm.message = template.content;
    communicationForm.type = template.type;
};

const sendCommunication = () => {
    communicationForm.post(route("admin.brokers.communications.send"), {
        onSuccess: () => {
            closeCommunicationModal();
        },
    });
};

const saveDraft = () => {
    communicationForm.post(route("admin.brokers.communications.draft"), {
        onSuccess: () => {
            closeCommunicationModal();
        },
    });
};

const scheduleMessage = () => {
    if (!communicationForm.scheduled_at) {
        alert("Please select a schedule date and time");
        return;
    }

    communicationForm.post(route("admin.brokers.communications.schedule"), {
        onSuccess: () => {
            closeCommunicationModal();
        },
    });
};

const saveTemplate = () => {
    templateForm.post(route("admin.brokers.communications.templates.store"), {
        onSuccess: () => {
            closeTemplateModal();
        },
    });
};

const deleteCommunication = (communicationId) => {
    if (confirm("Are you sure you want to delete this communication?")) {
        // Implementation for deleting communication
        console.log("Deleting communication:", communicationId);
    }
};

const resendCommunication = (communicationId) => {
    // Implementation for resending communication
    console.log("Resending communication:", communicationId);
};

const toggleCommunicationSelection = (communicationId) => {
    const index = selectedCommunications.value.indexOf(communicationId);
    if (index > -1) {
        selectedCommunications.value.splice(index, 1);
    } else {
        selectedCommunications.value.push(communicationId);
    }
};

const bulkDelete = () => {
    if (selectedCommunications.value.length === 0) return;

    if (
        confirm(
            `Are you sure you want to delete ${selectedCommunications.value.length} communications?`
        )
    ) {
        // Implementation for bulk delete
        console.log("Bulk deleting:", selectedCommunications.value);
        selectedCommunications.value = [];
        showBulkActions.value = false;
    }
};

const applyFilters = () => {
    // Implementation for applying filters
    console.log("Applying filters");
};

const clearFilters = () => {
    searchQuery.value = "";
    selectedType.value = "";
    selectedStatus.value = "";
    selectedPriority.value = "";
    dateRange.value = { start: "", end: "" };
};

const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const formatDateTime = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getDeliveryRate = (communication) => {
    if (!communication.total_recipients) return 0;
    return Math.round(
        (communication.delivered_count / communication.total_recipients) * 100
    );
};

const getReadRate = (communication) => {
    if (!communication.delivered_count) return 0;
    return Math.round(
        (communication.read_count / communication.delivered_count) * 100
    );
};
</script>

<template>
    <ModernDashboardLayout>
        <Head title="Broker Communications" />

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <ModernButton
                    variant="ghost"
                    :href="route('admin.brokers.index')"
                >
                    <ArrowLeftIcon class="w-5 h-5" />
                    Back to Brokers
                </ModernButton>
            </div>

            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900 mb-2">
                        Broker Communications
                    </h1>
                    <p class="text-gray-600">
                        Send notifications, announcements, and messages to
                        brokers
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <ModernButton variant="outline" @click="openTemplateModal">
                        <DocumentTextIcon class="w-5 h-5" />
                        Manage Templates
                    </ModernButton>
                    <ModernButton
                        variant="primary"
                        @click="openCommunicationModal"
                    >
                        <PlusIcon class="w-5 h-5" />
                        New Communication
                    </ModernButton>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Total Sent
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ stats?.total_sent || 0 }}
                        </p>
                        <p class="text-sm text-blue-600 mt-1">
                            This month: {{ stats?.monthly_sent || 0 }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center"
                    >
                        <PaperAirplaneIcon class="w-6 h-6 text-blue-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Delivery Rate
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ stats?.delivery_rate || 0 }}%
                        </p>
                        <p class="text-sm text-green-600 mt-1">
                            {{ stats?.delivered_count || 0 }} delivered
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center"
                    >
                        <CheckCircleIcon class="w-6 h-6 text-green-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Read Rate
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ stats?.read_rate || 0 }}%
                        </p>
                        <p class="text-sm text-purple-600 mt-1">
                            {{ stats?.read_count || 0 }} opened
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center"
                    >
                        <EyeIcon class="w-6 h-6 text-purple-600" />
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Active Brokers
                        </p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ stats?.active_brokers || 0 }}
                        </p>
                        <p class="text-sm text-indigo-600 mt-1">
                            {{ stats?.total_brokers || 0 }} total
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center"
                    >
                        <UsersIcon class="w-6 h-6 text-indigo-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
                <div class="flex items-center gap-2">
                    <ModernButton
                        variant="ghost"
                        size="sm"
                        @click="clearFilters"
                    >
                        Clear All
                    </ModernButton>
                    <ModernButton
                        variant="outline"
                        size="sm"
                        @click="applyFilters"
                    >
                        <FunnelIcon class="w-4 h-4" />
                        Apply Filters
                    </ModernButton>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Search</label
                    >
                    <div class="relative">
                        <MagnifyingGlassIcon
                            class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search communications..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        />
                    </div>
                </div>

                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Type</label
                    >
                    <select
                        v-model="selectedType"
                        class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">All Types</option>
                        <option
                            v-for="(config, type) in communicationTypes"
                            :key="type"
                            :value="type"
                        >
                            {{ config.name }}
                        </option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Status</label
                    >
                    <select
                        v-model="selectedStatus"
                        class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">All Status</option>
                        <option
                            v-for="(config, status) in statusTypes"
                            :key="status"
                            :value="status"
                        >
                            {{ config.name }}
                        </option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Priority</label
                    >
                    <select
                        v-model="selectedPriority"
                        class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">All Priorities</option>
                        <option
                            v-for="(config, priority) in priorityLevels"
                            :key="priority"
                            :value="priority"
                        >
                            {{ config.name }}
                        </option>
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Date Range</label
                    >
                    <div class="flex gap-2">
                        <input
                            v-model="dateRange.start"
                            type="date"
                            class="flex-1 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 text-sm"
                        />
                        <input
                            v-model="dateRange.end"
                            type="date"
                            class="flex-1 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500 text-sm"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Communications List -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    Communications History
                </h2>

                <!-- Bulk Actions -->
                <div
                    v-if="selectedCommunications.length > 0"
                    class="flex items-center gap-3"
                >
                    <span class="text-sm text-gray-600">
                        {{ selectedCommunications.length }} selected
                    </span>
                    <ModernButton
                        variant="outline"
                        size="sm"
                        @click="bulkDelete"
                    >
                        <TrashIcon class="w-4 h-4" />
                        Delete Selected
                    </ModernButton>
                </div>
            </div>

            <div v-if="filteredCommunications.length" class="space-y-4">
                <div
                    v-for="communication in filteredCommunications"
                    :key="communication.id"
                    class="border border-gray-100 rounded-xl p-6 hover:bg-gray-50 transition-colors"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4 flex-1">
                            <!-- Selection Checkbox -->
                            <input
                                type="checkbox"
                                :checked="
                                    selectedCommunications.includes(
                                        communication.id
                                    )
                                "
                                @change="
                                    toggleCommunicationSelection(
                                        communication.id
                                    )
                                "
                                class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            />

                            <!-- Communication Icon -->
                            <div
                                :class="[
                                    'w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0',
                                    `bg-${
                                        communicationTypes[communication.type]
                                            ?.color
                                    }-100`,
                                ]"
                            >
                                <component
                                    :is="
                                        communicationTypes[communication.type]
                                            ?.icon || ChatBubbleLeftRightIcon
                                    "
                                    :class="[
                                        'w-6 h-6',
                                        `text-${
                                            communicationTypes[
                                                communication.type
                                            ]?.color
                                        }-600`,
                                    ]"
                                />
                            </div>

                            <!-- Communication Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="font-semibold text-gray-900">
                                        {{ communication.title }}
                                    </h3>
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                            statusTypes[communication.status]
                                                ?.class,
                                        ]"
                                    >
                                        {{
                                            statusTypes[communication.status]
                                                ?.name
                                        }}
                                    </span>
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                            priorityLevels[
                                                communication.priority
                                            ]?.class,
                                        ]"
                                    >
                                        {{
                                            priorityLevels[
                                                communication.priority
                                            ]?.name
                                        }}
                                    </span>
                                </div>

                                <p
                                    class="text-sm text-gray-600 mb-3 line-clamp-2"
                                >
                                    {{ communication.message }}
                                </p>

                                <div
                                    class="flex items-center gap-6 text-sm text-gray-500"
                                >
                                    <div class="flex items-center gap-1">
                                        <UsersIcon class="w-4 h-4" />
                                        <span
                                            >{{
                                                communication.total_recipients ||
                                                0
                                            }}
                                            recipients</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <CheckCircleIcon class="w-4 h-4" />
                                        <span
                                            >{{
                                                getDeliveryRate(communication)
                                            }}% delivered</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <EyeIcon class="w-4 h-4" />
                                        <span
                                            >{{ getReadRate(communication) }}%
                                            read</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <CalendarIcon class="w-4 h-4" />
                                        <span>{{
                                            formatDateTime(
                                                communication.created_at
                                            )
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2 ml-4">
                            <ModernButton
                                variant="ghost"
                                size="sm"
                                @click="viewMessageDetails(communication)"
                            >
                                <EyeIcon class="w-4 h-4" />
                            </ModernButton>

                            <ModernButton
                                v-if="communication.status === 'failed'"
                                variant="ghost"
                                size="sm"
                                @click="resendCommunication(communication.id)"
                            >
                                <ArrowPathIcon class="w-4 h-4" />
                            </ModernButton>

                            <ModernButton
                                variant="ghost"
                                size="sm"
                                @click="deleteCommunication(communication.id)"
                            >
                                <TrashIcon class="w-4 h-4" />
                            </ModernButton>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12">
                <ChatBubbleLeftRightIcon
                    class="w-12 h-12 text-gray-400 mx-auto mb-3"
                />
                <p class="text-gray-500">No communications found</p>
                <ModernButton
                    variant="outline"
                    class="mt-4"
                    @click="openCommunicationModal"
                >
                    Send Your First Communication
                </ModernButton>
            </div>
        </div>

        <!-- Communication Modal -->
        <div
            v-if="showCommunicationModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            @click="closeCommunicationModal"
        >
            <div
                class="bg-white rounded-2xl p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">
                        New Communication
                    </h2>
                    <button
                        @click="closeCommunicationModal"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <form @submit.prevent="sendCommunication" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Communication Type -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Communication Type
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        v-for="(
                                            config, type
                                        ) in communicationTypes"
                                        :key="type"
                                        type="button"
                                        @click="communicationForm.type = type"
                                        :class="[
                                            'p-3 border-2 rounded-lg text-left transition-colors',
                                            communicationForm.type === type
                                                ? `border-${config.color}-500 bg-${config.color}-50`
                                                : 'border-gray-200 hover:border-gray-300',
                                        ]"
                                    >
                                        <div
                                            class="flex items-center gap-2 mb-1"
                                        >
                                            <component
                                                :is="config.icon"
                                                :class="[
                                                    'w-5 h-5',
                                                    communicationForm.type ===
                                                    type
                                                        ? `text-${config.color}-600`
                                                        : 'text-gray-400',
                                                ]"
                                            />
                                            <span
                                                :class="[
                                                    'font-medium',
                                                    communicationForm.type ===
                                                    type
                                                        ? `text-${config.color}-900`
                                                        : 'text-gray-900',
                                                ]"
                                            >
                                                {{ config.name }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-600">
                                            {{ config.description }}
                                        </p>
                                    </button>
                                </div>
                            </div>

                            <!-- Title -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Title
                                </label>
                                <input
                                    v-model="communicationForm.title"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Enter communication title..."
                                    required
                                />
                            </div>

                            <!-- Message -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Message
                                </label>
                                <textarea
                                    v-model="communicationForm.message"
                                    rows="6"
                                    class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Enter your message..."
                                    required
                                ></textarea>
                            </div>

                            <!-- Priority & Schedule -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Priority
                                    </label>
                                    <select
                                        v-model="communicationForm.priority"
                                        class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                    >
                                        <option
                                            v-for="(
                                                config, priority
                                            ) in priorityLevels"
                                            :key="priority"
                                            :value="priority"
                                        >
                                            {{ config.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Schedule (Optional)
                                    </label>
                                    <input
                                        v-model="communicationForm.scheduled_at"
                                        type="datetime-local"
                                        class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Recipients -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Recipients ({{
                                        selectedBrokers.length
                                    }}
                                    selected)
                                </label>
                                <div class="flex gap-2">
                                    <ModernButton
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        @click="selectAllBrokers"
                                    >
                                        Select All
                                    </ModernButton>
                                    <ModernButton
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        @click="clearAllBrokers"
                                    >
                                        Clear All
                                    </ModernButton>
                                </div>
                            </div>

                            <div
                                class="border border-gray-300 rounded-lg p-4 max-h-80 overflow-y-auto"
                            >
                                <div class="space-y-2">
                                    <label
                                        v-for="broker in brokers"
                                        :key="broker.id"
                                        class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="
                                                communicationForm.recipients.includes(
                                                    broker.id
                                                )
                                            "
                                            @change="
                                                toggleBrokerSelection(broker.id)
                                            "
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                        />
                                        <div
                                            class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0"
                                        >
                                            <img
                                                v-if="
                                                    broker.profile_picture_url
                                                "
                                                :src="
                                                    broker.profile_picture_url
                                                "
                                                :alt="broker.name"
                                                class="w-full h-full object-cover"
                                            />
                                            <UserIcon
                                                v-else
                                                class="w-4 h-4 text-gray-400"
                                            />
                                        </div>
                                        <div class="flex-1">
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ broker.name }}
                                            </p>
                                            <p class="text-xs text-gray-600">
                                                {{ broker.email }}
                                            </p>
                                        </div>
                                        <span
                                            :class="[
                                                'text-xs px-2 py-1 rounded-md',
                                                broker.status === 'active'
                                                    ? 'bg-green-100 text-green-800'
                                                    : broker.status ===
                                                      'suspended'
                                                    ? 'bg-yellow-100 text-yellow-800'
                                                    : 'bg-red-100 text-red-800',
                                            ]"
                                        >
                                            {{ broker.status }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Templates -->
                    <div v-if="templates?.length">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Use Template (Optional)
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <button
                                v-for="template in templates"
                                :key="template.id"
                                type="button"
                                @click="applyTemplate(template)"
                                class="p-3 border border-gray-200 rounded-lg text-left hover:border-primary-500 hover:bg-primary-50 transition-colors"
                            >
                                <h4 class="font-medium text-gray-900">
                                    {{ template.name }}
                                </h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ template.subject }}
                                </p>
                                <span
                                    :class="[
                                        'inline-block mt-2 text-xs px-2 py-1 rounded-md',
                                        `bg-${
                                            communicationTypes[template.type]
                                                ?.color
                                        }-100 text-${
                                            communicationTypes[template.type]
                                                ?.color
                                        }-800`,
                                    ]"
                                >
                                    {{
                                        communicationTypes[template.type]?.name
                                    }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div
                        class="flex items-center justify-end gap-3 pt-4 border-t"
                    >
                        <ModernButton
                            type="button"
                            variant="ghost"
                            @click="closeCommunicationModal"
                        >
                            Cancel
                        </ModernButton>
                        <ModernButton
                            type="button"
                            variant="outline"
                            @click="saveDraft"
                            :disabled="
                                !communicationForm.title ||
                                !communicationForm.message
                            "
                        >
                            Save Draft
                        </ModernButton>
                        <ModernButton
                            v-if="communicationForm.scheduled_at"
                            type="button"
                            variant="primary"
                            @click="scheduleMessage"
                            :disabled="
                                !canSendCommunication ||
                                communicationForm.processing
                            "
                        >
                            {{
                                communicationForm.processing
                                    ? "Scheduling..."
                                    : "Schedule"
                            }}
                        </ModernButton>
                        <ModernButton
                            v-else
                            type="submit"
                            variant="primary"
                            :disabled="
                                !canSendCommunication ||
                                communicationForm.processing
                            "
                        >
                            {{
                                communicationForm.processing
                                    ? "Sending..."
                                    : "Send Now"
                            }}
                        </ModernButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Message Details Modal -->
        <div
            v-if="showMessageDetails && selectedMessage"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
            @click="closeMessageDetails"
        >
            <div
                class="bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">
                        Communication Details
                    </h2>
                    <button
                        @click="closeMessageDetails"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="space-y-6">
                    <!-- Message Info -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            {{ selectedMessage.title }}
                        </h3>
                        <div class="flex items-center gap-3 mb-4">
                            <span
                                :class="[
                                    'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                    statusTypes[selectedMessage.status]?.class,
                                ]"
                            >
                                {{ statusTypes[selectedMessage.status]?.name }}
                            </span>
                            <span
                                :class="[
                                    'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
                                    priorityLevels[selectedMessage.priority]
                                        ?.class,
                                ]"
                            >
                                {{
                                    priorityLevels[selectedMessage.priority]
                                        ?.name
                                }}
                            </span>
                            <span class="text-sm text-gray-600">
                                {{ formatDateTime(selectedMessage.created_at) }}
                            </span>
                        </div>
                        <p class="text-gray-700 whitespace-pre-wrap">
                            {{ selectedMessage.message }}
                        </p>
                    </div>

                    <!-- Delivery Stats -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <p class="text-2xl font-bold text-blue-600">
                                {{ selectedMessage.total_recipients || 0 }}
                            </p>
                            <p class="text-sm text-blue-800">Recipients</p>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <p class="text-2xl font-bold text-green-600">
                                {{ getDeliveryRate(selectedMessage) }}%
                            </p>
                            <p class="text-sm text-green-800">Delivered</p>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <p class="text-2xl font-bold text-purple-600">
                                {{ getReadRate(selectedMessage) }}%
                            </p>
                            <p class="text-sm text-purple-800">Read</p>
                        </div>
                    </div>

                    <!-- Recipients List -->
                    <div v-if="selectedMessage.recipients">
                        <h4 class="font-medium text-gray-900 mb-3">
                            Recipients
                        </h4>
                        <div
                            class="max-h-40 overflow-y-auto border border-gray-200 rounded-lg"
                        >
                            <div
                                v-for="recipient in selectedMessage.recipients"
                                :key="recipient.id"
                                class="flex items-center justify-between p-3 border-b border-gray-100 last:border-b-0"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden"
                                    >
                                        <img
                                            v-if="recipient.profile_picture_url"
                                            :src="recipient.profile_picture_url"
                                            :alt="recipient.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <UserIcon
                                            v-else
                                            class="w-4 h-4 text-gray-400"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ recipient.name }}
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            {{ recipient.email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        :class="[
                                            'text-xs px-2 py-1 rounded-md',
                                            recipient.delivery_status ===
                                            'delivered'
                                                ? 'bg-green-100 text-green-800'
                                                : recipient.delivery_status ===
                                                  'failed'
                                                ? 'bg-red-100 text-red-800'
                                                : 'bg-yellow-100 text-yellow-800',
                                        ]"
                                    >
                                        {{
                                            recipient.delivery_status ||
                                            "pending"
                                        }}
                                    </span>
                                    <span
                                        v-if="recipient.read_at"
                                        class="text-xs text-green-600"
                                    >
                                        Read
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>
