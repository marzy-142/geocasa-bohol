<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3
            class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2"
        >
            <svg
                class="w-5 h-5 text-indigo-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                ></path>
            </svg>
            Complete Timeline
        </h3>

        <div class="relative">
            <!-- Timeline Line -->
            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

            <!-- Timeline Events -->
            <div class="space-y-6">
                <div
                    v-for="(event, index) in timelineEvents"
                    :key="index"
                    class="relative flex gap-4"
                >
                    <!-- Icon -->
                    <div
                        :class="[
                            'relative z-10 flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center',
                            event.type === 'success'
                                ? 'bg-green-100 text-green-600'
                                : event.type === 'info'
                                ? 'bg-blue-100 text-blue-600'
                                : event.type === 'warning'
                                ? 'bg-yellow-100 text-yellow-600'
                                : 'bg-gray-100 text-gray-600',
                        ]"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                v-if="event.icon === 'mail'"
                                fill-rule="evenodd"
                                d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884zM18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"
                                clip-rule="evenodd"
                            />
                            <path
                                v-else-if="event.icon === 'phone'"
                                d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"
                            />
                            <path
                                v-else-if="event.icon === 'calendar'"
                                fill-rule="evenodd"
                                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                clip-rule="evenodd"
                            />
                            <path
                                v-else-if="event.icon === 'check'"
                                fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"
                            />
                            <path
                                v-else-if="event.icon === 'document'"
                                fill-rule="evenodd"
                                d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                clip-rule="evenodd"
                            />
                            <path
                                v-else-if="event.icon === 'star'"
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                            />
                            <circle v-else cx="10" cy="10" r="3" />
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 pb-6">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ event.title }}
                                </p>
                                <p
                                    v-if="event.description"
                                    class="text-sm text-gray-600 mt-1"
                                >
                                    {{ event.description }}
                                </p>
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ formatEventDate(event.date) }}
                                </p>
                            </div>
                            <span
                                v-if="event.badge"
                                :class="[
                                    'px-2 py-1 text-xs font-medium rounded',
                                    event.badgeClass ||
                                        'bg-gray-100 text-gray-700',
                                ]"
                            >
                                {{ event.badge }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { format, parseISO } from "date-fns";

const props = defineProps({
    inquiry: {
        type: Object,
        required: true,
    },
    transaction: {
        type: Object,
        default: null,
    },
});

const timelineEvents = computed(() => {
    const events = [];

    // 1. Inquiry Received
    events.push({
        title: "Inquiry Received",
        description: `${props.inquiry.name} expressed interest in ${
            props.inquiry.property?.title || "the property"
        }`,
        date: props.inquiry.created_at,
        icon: "mail",
        type: "info",
        badge: "Inquiry",
        badgeClass: "bg-blue-100 text-blue-700",
    });

    // 2. First Contact (if exists)
    if (props.inquiry.contacted_at) {
        events.push({
            title: "First Contact Made",
            description: "Broker reached out to the client",
            date: props.inquiry.contacted_at,
            icon: "phone",
            type: "info",
        });
    }

    // 3. Broker Responded (if exists)
    if (props.inquiry.responded_at) {
        events.push({
            title: "Broker Responded",
            description: props.inquiry.broker_response
                ? props.inquiry.broker_response.substring(0, 100) +
                  (props.inquiry.broker_response.length > 100 ? "..." : "")
                : null,
            date: props.inquiry.responded_at,
            icon: "document",
            type: "info",
        });
    }

    // 4. Viewing Scheduled (if exists)
    if (props.inquiry.scheduled_at) {
        events.push({
            title: "Viewing Scheduled",
            description: "Property viewing appointment set",
            date: props.inquiry.scheduled_at,
            icon: "calendar",
            type: "info",
            badge: "Scheduled",
            badgeClass: "bg-purple-100 text-purple-700",
        });
    }

    // 5. Inquiry Completed (if marked as won)
    if (
        props.inquiry.status === "completed" ||
        props.inquiry.status === "in_transaction"
    ) {
        if (props.inquiry.completion_outcome === "won") {
            events.push({
                title: "Inquiry Won! 🎉",
                description:
                    props.inquiry.completion_notes ||
                    "Client decided to proceed with the purchase",
                date: props.inquiry.updated_at,
                icon: "check",
                type: "success",
                badge: "Won",
                badgeClass: "bg-green-100 text-green-700",
            });
        }
    }

    // === TRANSACTION EVENTS ===
    if (props.transaction) {
        // 6. Transaction Created
        events.push({
            title: "Transaction Created",
            description: `Transaction #${props.transaction.transaction_number} initiated automatically`,
            date: props.transaction.created_at,
            icon: "star",
            type: "success",
            badge: "Transaction",
            badgeClass: "bg-indigo-100 text-indigo-700",
        });

        // 7. Offer Made (if different from creation)
        if (
            props.transaction.offer_date &&
            props.transaction.offer_date !== props.transaction.created_at
        ) {
            events.push({
                title: "Offer Made",
                description: props.transaction.offered_price
                    ? `Offered: ₱${Number(
                          props.transaction.offered_price
                      ).toLocaleString()}`
                    : null,
                date: props.transaction.offer_date,
                icon: "document",
                type: "info",
                badge: "Offer",
                badgeClass: "bg-yellow-100 text-yellow-700",
            });
        }

        // 8. Agreement Reached (if exists)
        if (props.transaction.acceptance_date) {
            events.push({
                title: "Offer Accepted",
                description: props.transaction.final_price
                    ? `Agreed Price: ₱${Number(
                          props.transaction.final_price
                      ).toLocaleString()}`
                    : null,
                date: props.transaction.acceptance_date,
                icon: "check",
                type: "success",
                badge: "Accepted",
                badgeClass: "bg-green-100 text-green-700",
            });
        }

        // 9. Contract Signed (if exists)
        if (props.transaction.contract_date) {
            events.push({
                title: "Contract Signed",
                description: "Purchase agreement executed",
                date: props.transaction.contract_date,
                icon: "document",
                type: "success",
                badge: "Contract",
                badgeClass: "bg-green-100 text-green-700",
            });
        }

        // 10. Transaction Finalized (if completed)
        if (
            props.transaction.status === "finalized" &&
            props.transaction.finalized_date
        ) {
            events.push({
                title: "Transaction Finalized! 🎊",
                description: "Deal successfully closed",
                date: props.transaction.finalized_date,
                icon: "star",
                type: "success",
                badge: "Completed",
                badgeClass: "bg-green-100 text-green-700",
            });
        }
    }

    // Sort events by date (most recent last)
    return events.sort((a, b) => new Date(a.date) - new Date(b.date));
});

const formatEventDate = (dateString) => {
    try {
        const date = parseISO(dateString);
        return format(date, "MMM dd, yyyy • h:mm a");
    } catch (e) {
        return dateString;
    }
};
</script>
