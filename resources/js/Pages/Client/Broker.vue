<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, reactive } from "vue";
import { useToast } from "@/Composables/useToast";
import { useFocusTrap } from "@/Composables/useFocusTrap";
import {
    UserGroupIcon,
    StarIcon,
    PhoneIcon,
    EnvelopeIcon,
    MapPinIcon,
    ClockIcon,
    CalendarIcon,
    ChatBubbleLeftRightIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    VideoCameraIcon,
    DocumentTextIcon,
    PlusIcon,
    ArrowRightIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    broker: Object,
    client: Object,
    recentInquiries: Array,
    scheduledMeetings: Array,
    activeConversation: Object,
    brokerStats: Object,
});

// Toast notifications
const toast = useToast();

// UI state
const showMeetingForm = ref(false);
const activeTab = ref("inquiries");

// Modal refs for focus trap
const meetingModalRef = ref(null);

// Focus trap
useFocusTrap(meetingModalRef, showMeetingForm);

// Forms
const meetingFormData = useForm({
    title: "",
    date: "",
    time: "",
    location: "",
    notes: "",
    property_id: null,
    inquiry_id: null,
    type: "consultation",
});

// Methods
const startConversation = () => {
    // Create/find conversation without sending a message
    // User will type their own message on the conversation page
    useForm({}).post(route("client.broker.message"));
};

const scheduleMeeting = () => {
    if (
        !meetingFormData.title.trim() ||
        !meetingFormData.date ||
        !meetingFormData.time
    ) {
        toast.warning("Missing Information", "Please fill in all required fields.");
        return;
    }

    meetingFormData.post(route("client.broker.meeting"), {
        onSuccess: () => {
            showMeetingForm.value = false;
            meetingFormData.reset();
            toast.success("Meeting Scheduled", "Your meeting has been scheduled successfully!");
            // Reload page to show new meeting
            window.location.reload();
        },
        onError: () => {
            toast.error("Failed to Schedule", "Unable to schedule meeting. Please try again.");
        },
    });
};

const cancelMeeting = (meetingId) => {
    if (!confirm('Are you sure you want to cancel this meeting?')) return;
    
    useForm({}).delete(route('client.broker.meeting.cancel', meetingId), {
        onSuccess: () => {
            toast.success("Meeting Cancelled", "The meeting has been cancelled.");
            window.location.reload();
        },
        onError: () => {
            toast.error("Failed to Cancel", "Unable to cancel meeting.");
        },
    });
};

const getStatusColor = (status) => {
    const colors = {
        confirmed: "bg-green-100 text-green-800",
        pending: "bg-yellow-100 text-yellow-800",
        cancelled: "bg-red-100 text-red-800",
    };
    return colors[status] || "bg-gray-100 text-gray-800";
};

const getStatusLabel = (status) => {
    const labels = {
        confirmed: "Confirmed",
        pending: "Pending",
        cancelled: "Cancelled",
    };
    return labels[status] || status;
};

const getMeetingTypeIcon = (type) => {
    const icons = {
        viewing: "🏠",
        document_review: "📄",
        consultation: "💬",
        virtual: "📹",
    };
    return icons[type] || "📅";
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const formatTime = (time) => {
    return new Date(`2000-01-01T${time}`).toLocaleTimeString("en-US", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true,
    });
};
</script>

<template>
    <Head title="My Broker - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Simple Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Broker</h1>
            <p class="text-gray-600">Your dedicated real estate professional</p>
        </div>

        <!-- Clean Broker Layout -->
        <div v-if="broker" class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Broker Profile Card - Simplified -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <!-- Broker Avatar -->
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-3">
                            <span class="text-2xl font-bold text-white">{{
                                broker.name.charAt(0)
                            }}</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-1">
                            {{ broker.name }}
                        </h2>
                        <p class="text-sm text-gray-500 mb-4">Real Estate Broker</p>
                    </div>

                    <!-- Contact Information -->
                    <div class="space-y-3 mb-6 pb-6 border-b border-gray-200">
                        <div class="flex items-center gap-3 text-gray-700">
                            <PhoneIcon class="w-4 h-4 text-gray-400" />
                            <span class="text-sm">{{
                                broker.phone || "Not provided"
                            }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-gray-700">
                            <EnvelopeIcon class="w-4 h-4 text-gray-400" />
                            <span class="text-sm">{{ broker.email }}</span>
                        </div>
                        <div
                            v-if="broker.office_address"
                            class="flex items-center gap-3 text-gray-700"
                        >
                            <MapPinIcon class="w-4 h-4 text-gray-400" />
                            <span class="text-sm">{{
                                broker.office_address
                            }}</span>
                        </div>
                    </div>

                    <!-- Broker Stats -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">
                            Performance
                        </h3>
                        <div class="space-y-2.5 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Experience</span>
                                <span class="font-medium text-gray-900"
                                    >{{
                                        broker.years_experience || 0
                                    }}
                                    years</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Properties Sold</span>
                                <span class="font-medium text-gray-900"
                                    >{{ brokerStats?.properties_sold || 0 }}</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Active Clients</span>
                                <span class="font-medium text-gray-900"
                                    >{{ brokerStats?.active_clients || 0 }}</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Response Rate</span>
                                <span class="font-medium text-gray-900"
                                    >{{ brokerStats?.response_rate || 0 }}%</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="space-y-2">
                        <Link
                            v-if="activeConversation"
                            :href="route('conversations.show', activeConversation.id)"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                        >
                            <ChatBubbleLeftRightIcon class="w-4 h-4" />
                            View Conversation
                        </Link>
                        <button
                            v-else
                            @click="startConversation"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
                        >
                            <ChatBubbleLeftRightIcon class="w-4 h-4" />
                            Start Conversation
                        </button>
                        <button
                            @click="showMeetingForm = true"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg border border-gray-300 transition-colors"
                        >
                            <CalendarIcon class="w-4 h-4" />
                            Schedule Meeting
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3">
                <!-- Tab Navigation -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="flex border-b border-gray-200">
                        <button
                            @click="activeTab = 'inquiries'"
                            :class="
                                activeTab === 'inquiries'
                                    ? 'border-blue-600 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'
                            "
                            class="px-6 py-3 text-sm font-medium border-b-2 transition-colors"
                        >
                            Recent Inquiries
                        </button>
                        <button
                            @click="activeTab = 'meetings'"
                            :class="
                                activeTab === 'meetings'
                                    ? 'border-blue-600 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'
                            "
                            class="px-6 py-3 text-sm font-medium border-b-2 transition-colors"
                        >
                            Scheduled Meetings
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div>
                    <!-- Inquiries Tab -->
                    <div v-if="activeTab === 'inquiries'" class="space-y-4">
                        <div v-if="recentInquiries.length > 0">
                            <div
                                v-for="inquiry in recentInquiries"
                                :key="inquiry.id"
                                class="bg-white rounded-xl shadow-soft-lg border border-neutral-100 p-6 hover:shadow-xl transition-all duration-200"
                            >
                                <div
                                    class="flex items-start justify-between mb-4"
                                >
                                    <div>
                                        <h4
                                            class="font-semibold text-neutral-900 mb-1"
                                        >
                                            {{
                                                inquiry.property?.title ||
                                                "Property Inquiry"
                                            }}
                                        </h4>
                                        <p class="text-sm text-neutral-600">
                                            {{ inquiry.message }}
                                        </p>
                                    </div>
                                    <span
                                        :class="getStatusColor(inquiry.status)"
                                        class="px-3 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ getStatusLabel(inquiry.status) }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center justify-between text-sm text-neutral-500"
                                >
                                    <span>{{ inquiry.created_at }}</span>
                                    <Link
                                        :href="
                                            route(
                                                'client.inquiries.show',
                                                inquiry.id
                                            )
                                        "
                                        class="text-green-600 hover:text-green-700 font-medium flex items-center gap-1"
                                    >
                                        View Details
                                        <ArrowRightIcon class="w-4 h-4" />
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <div
                                class="w-16 h-16 bg-neutral-100 rounded-2xl flex items-center justify-center mx-auto mb-4"
                            >
                                <ChatBubbleLeftRightIcon
                                    class="w-8 h-8 text-neutral-400"
                                />
                            </div>
                            <h3
                                class="text-lg font-semibold text-neutral-900 mb-2"
                            >
                                No inquiries yet
                            </h3>
                            <p class="text-neutral-500 mb-4">
                                Start a conversation with your broker
                            </p>
                            <button
                                @click="showMessageForm = true"
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors"
                            >
                                Send Message
                            </button>
                        </div>
                    </div>

                    <!-- Meetings Tab -->
                    <div v-if="activeTab === 'meetings'" class="space-y-4">
                        <div v-if="scheduledMeetings.length > 0">
                            <div
                                v-for="meeting in scheduledMeetings"
                                :key="meeting.id"
                                class="bg-white rounded-xl shadow-soft-lg border border-neutral-100 p-6 hover:shadow-xl transition-all duration-200"
                            >
                                <div
                                    class="flex items-start justify-between mb-4"
                                >
                                    <div class="flex items-start gap-4">
                                        <div
                                            class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center"
                                        >
                                            <span class="text-2xl">{{
                                                getMeetingTypeIcon(meeting.type)
                                            }}</span>
                                        </div>
                                        <div>
                                            <h4
                                                class="font-semibold text-neutral-900 mb-1"
                                            >
                                                {{ meeting.title }}
                                            </h4>
                                            <p class="text-sm text-neutral-600">
                                                {{ meeting.location }}
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        :class="getStatusColor(meeting.status)"
                                        class="px-3 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ getStatusLabel(meeting.status) }}
                                    </span>
                                </div>
                                <div class="space-y-3">
                                    <div
                                        class="flex items-center gap-4 text-sm text-neutral-500"
                                    >
                                        <div class="flex items-center gap-1">
                                            <CalendarIcon class="w-4 h-4" />
                                            <span>{{
                                                formatDate(meeting.date)
                                            }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <ClockIcon class="w-4 h-4" />
                                            <span>{{
                                                formatTime(meeting.time)
                                            }}</span>
                                        </div>
                                    </div>
                                    <div v-if="meeting.property" class="text-sm text-neutral-600">
                                        <strong>Property:</strong> {{ meeting.property.title }}
                                    </div>
                                    <div v-if="meeting.notes" class="text-sm text-neutral-600">
                                        <strong>Notes:</strong> {{ meeting.notes }}
                                    </div>
                                    <div v-if="meeting.status === 'pending'" class="flex gap-2 pt-2">
                                        <button
                                            @click="cancelMeeting(meeting.id)"
                                            class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-medium transition-colors"
                                        >
                                            Cancel Meeting
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <div
                                class="w-16 h-16 bg-neutral-100 rounded-2xl flex items-center justify-center mx-auto mb-4"
                            >
                                <CalendarIcon
                                    class="w-8 h-8 text-neutral-400"
                                />
                            </div>
                            <h3
                                class="text-lg font-semibold text-neutral-900 mb-2"
                            >
                                No meetings scheduled
                            </h3>
                            <p class="text-neutral-500 mb-4">
                                Schedule a meeting with your broker
                            </p>
                            <button
                                @click="showMeetingForm = true"
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors"
                            >
                                Schedule Meeting
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- No Broker State -->
        <div v-else class="text-center py-16">
            <div
                class="w-24 h-24 bg-neutral-100 rounded-3xl flex items-center justify-center mx-auto mb-6"
            >
                <UserGroupIcon class="w-12 h-12 text-neutral-400" />
            </div>
            <h2 class="text-3xl font-bold text-neutral-900 mb-4">
                No Broker Assigned
            </h2>
            <p class="text-neutral-600 mb-8 max-w-md mx-auto">
                A dedicated broker will be assigned to you soon. In the
                meantime, you can browse properties and create inquiries.
            </p>
            <div class="flex items-center justify-center gap-4">
                <Link
                    :href="route('client.properties')"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors"
                >
                    Browse Properties
                </Link>
                <Link
                    :href="route('client.inquiries.index')"
                    class="bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-6 py-3 rounded-lg font-medium transition-colors"
                >
                    View Inquiries
                </Link>
            </div>
        </div>

        <!-- Message Modal Removed - Users go directly to conversation page -->

        <!-- Meeting Modal -->
        <div
            v-if="showMeetingForm"
            class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
            @click.self="showMeetingForm = false"
        >
            <div ref="meetingModalRef" class="bg-white rounded-2xl p-6 w-full max-w-md">
                <h3 class="text-xl font-bold text-neutral-900 mb-4">
                    Schedule Meeting
                </h3>
                <form @submit.prevent="scheduleMeeting" class="space-y-4">
                    <div>
                        <label
                            class="block text-sm font-semibold text-neutral-700 mb-2"
                            >Meeting Title</label
                        >
                        <input
                            v-model="meetingFormData.title"
                            type="text"
                            class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:border-green-500 focus:ring-green-500 focus:outline-none"
                            placeholder="e.g., Property Viewing"
                            required
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-semibold text-neutral-700 mb-2"
                                >Date</label
                            >
                            <input
                                v-model="meetingFormData.date"
                                type="date"
                                class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:border-green-500 focus:ring-green-500 focus:outline-none"
                                required
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-semibold text-neutral-700 mb-2"
                                >Time</label
                            >
                            <input
                                v-model="meetingFormData.time"
                                type="time"
                                class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:border-green-500 focus:ring-green-500 focus:outline-none"
                                required
                            />
                        </div>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-semibold text-neutral-700 mb-2"
                            >Meeting Type</label
                        >
                        <select
                            v-model="meetingFormData.type"
                            class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:border-green-500 focus:ring-green-500 focus:outline-none"
                        >
                            <option value="consultation">Consultation</option>
                            <option value="viewing">Property Viewing</option>
                            <option value="document_review">Document Review</option>
                            <option value="virtual">Virtual Meeting</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-semibold text-neutral-700 mb-2"
                            >Location</label
                        >
                        <input
                            v-model="meetingFormData.location"
                            type="text"
                            class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:border-green-500 focus:ring-green-500 focus:outline-none"
                            placeholder="e.g., Property Address or Virtual Meeting"
                            required
                        />
                    </div>
                    <div>
                        <label
                            class="block text-sm font-semibold text-neutral-700 mb-2"
                            >Notes (Optional)</label
                        >
                        <textarea
                            v-model="meetingFormData.notes"
                            rows="3"
                            class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:border-green-500 focus:ring-green-500 focus:outline-none"
                            placeholder="Any additional notes..."
                        ></textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="showMeetingForm = false"
                            class="flex-1 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 py-3 px-4 rounded-xl font-medium transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-xl font-medium transition-colors"
                        >
                            Schedule Meeting
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<style scoped>
.shadow-soft-lg {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>
