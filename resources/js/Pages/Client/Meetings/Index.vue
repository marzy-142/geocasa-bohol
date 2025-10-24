<template>
    <Head title="My Meetings - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-neutral-900">My Meetings</h1>
            <p class="text-neutral-600 mt-1">
                View and manage your scheduled meetings
            </p>
        </div>

        <!-- Error State -->
        <ErrorState
            v-if="error"
            type="error"
            @retry="retryLoad"
        />

        <!-- Loading State -->
        <div v-else-if="isLoading" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <LoadingSkeleton v-for="n in 3" :key="n" type="stats-card" />
            </div>
            <LoadingSkeleton type="card" class="h-96" />
        </div>

        <!-- Content -->
        <template v-else>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-600 mb-1">
                                Upcoming Meetings
                            </p>
                            <p class="text-3xl font-bold text-blue-600">
                                {{ meetingSchedule.upcoming.length }}
                            </p>
                            <p class="text-sm text-neutral-500">Next 7 days</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center">
                            <CalendarIcon class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-600 mb-1">
                                This Week
                            </p>
                            <p class="text-3xl font-bold text-green-600">
                                {{ meetingSchedule.this_week.length }}
                            </p>
                            <p class="text-sm text-neutral-500">Scheduled</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center">
                            <CheckCircleIcon class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-600 mb-1">
                                Next Week
                            </p>
                            <p class="text-3xl font-bold text-purple-600">
                                {{ meetingSchedule.next_week.length }}
                            </p>
                            <p class="text-sm text-neutral-500">Upcoming</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center">
                            <BoltIcon class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Meetings -->
            <div class="bg-white border border-neutral-200 rounded-lg">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h3 class="text-lg font-semibold text-neutral-900">
                        Upcoming Meetings
                    </h3>
                </div>

                    <div
                        v-if="meetingSchedule.upcoming.length > 0"
                        class="divide-y divide-neutral-200"
                    >
                        <div
                            v-for="meeting in meetingSchedule.upcoming"
                            :key="meeting.id"
                            class="px-6 py-6"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center"
                                        >
                                            <svg
                                                class="h-6 w-6 text-blue-600"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="flex items-center">
                                            <h4
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    formatMeetingType(
                                                        meeting.type
                                                    )
                                                }}
                                            </h4>
                                            <span
                                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                            >
                                                {{
                                                    getTimeUntil(
                                                        meeting.scheduled_at
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-sm text-gray-500">
                                            <p>
                                                {{
                                                    formatDate(
                                                        meeting.scheduled_at
                                                    )
                                                }}
                                            </p>
                                            <p class="flex items-center mt-1">
                                                <svg
                                                    class="h-4 w-4 text-gray-400 mr-1"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                                    />
                                                </svg>
                                                {{ meeting.location }}
                                            </p>
                                        </div>
                                        <div
                                            v-if="meeting.notes"
                                            class="mt-2 text-sm text-gray-600"
                                        >
                                            {{ meeting.notes }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="text-right">
                                        <p
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{
                                                meeting.transaction.property
                                                    .title
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Transaction #{{
                                                meeting.transaction
                                                    .transaction_number
                                            }}
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <Link
                                            :href="
                                                route(
                                                    'client.meetings.show',
                                                    meeting.id
                                                )
                                            "
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        >
                                            View Details
                                        </Link>
                                        <Link
                                            :href="
                                                route(
                                                    'client.transactions.show',
                                                    meeting.transaction.id
                                                )
                                            "
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        >
                                            View Transaction
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="px-6 py-12 text-center">
                        <svg
                            class="mx-auto h-12 w-12 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            No upcoming meetings
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            You don't have any scheduled meetings at the moment.
                        </p>
                    </div>
                </div>

                <!-- This Week's Meetings -->
                <div
                    v-if="meetingSchedule.this_week.length > 0"
                    class="mt-8 bg-white shadow rounded-lg"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            This Week
                        </h3>
                    </div>

                    <div class="divide-y divide-gray-200">
                        <div
                            v-for="meeting in meetingSchedule.this_week"
                            :key="meeting.id"
                            class="px-6 py-4"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900">
                                            {{
                                                formatMeetingType(meeting.type)
                                            }}
                                        </p>
                                        <p class="text-gray-500">
                                            {{
                                                formatDate(meeting.scheduled_at)
                                            }}
                                            • {{ meeting.location }}
                                        </p>
                                    </div>
                                </div>
                                <Link
                                    :href="
                                        route(
                                            'client.meetings.show',
                                            meeting.id
                                        )
                                    "
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    View Details
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Week's Meetings -->
                <div
                    v-if="meetingSchedule.next_week.length > 0"
                    class="mt-8 bg-white shadow rounded-lg"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Next Week
                        </h3>
                    </div>

                    <div class="divide-y divide-gray-200">
                        <div
                            v-for="meeting in meetingSchedule.next_week"
                            :key="meeting.id"
                            class="px-6 py-4"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900">
                                            {{
                                                formatMeetingType(meeting.type)
                                            }}
                                        </p>
                                        <p class="text-gray-500">
                                            {{
                                                formatDate(meeting.scheduled_at)
                                            }}
                                            • {{ meeting.location }}
                                        </p>
                                    </div>
                                </div>
                                <Link
                                    :href="
                                        route(
                                            'client.meetings.show',
                                            meeting.id
                                        )
                                    "
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    View Details
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Meeting Tips -->
                <div class="mt-8 bg-blue-50 rounded-lg p-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-5 w-5 text-blue-400"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Meeting Preparation Tips
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>
                                        Arrive 10-15 minutes early for scheduled
                                        meetings
                                    </li>
                                    <li>
                                        Bring any required documents or
                                        identification
                                    </li>
                                    <li>
                                        Prepare questions about the property or
                                        transaction
                                    </li>
                                    <li>
                                        Contact your broker if you need to
                                        reschedule
                                    </li>
                                    <li>
                                        Check your email for any last-minute
                                        updates
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
        </template>
    </ModernDashboardLayout>
</template>

<script setup>
import { Link, Head } from "@inertiajs/vue3";
import { ref } from "vue";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import LoadingSkeleton from "@/Components/LoadingSkeleton.vue";
import EmptyState from "@/Components/EmptyState.vue";
import ErrorState from "@/Components/ErrorState.vue";
import { useFormatters } from "@/Composables/useFormatters";
import {
    CalendarIcon,
    ClockIcon,
    MapPinIcon,
    CheckCircleIcon,
    BoltIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    meetingSchedule: Object,
    client: Object,
});

const isLoading = ref(false);
const error = ref(null);

const { formatRelativeTime } = useFormatters();

const formatMeetingType = (type) => {
    return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-PH", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const retryLoad = () => {
    error.value = null;
    window.location.reload();
};

const getTimeUntil = (dateString) => {
    const now = new Date();
    const meetingDate = new Date(dateString);
    const diffInHours = Math.floor((meetingDate - now) / (1000 * 60 * 60));

    if (diffInHours < 1) {
        const diffInMinutes = Math.floor((meetingDate - now) / (1000 * 60));
        return `${diffInMinutes}m`;
    } else if (diffInHours < 24) {
        return `${diffInHours}h`;
    } else {
        const diffInDays = Math.floor(diffInHours / 24);
        return `${diffInDays}d`;
    }
};
</script>
