<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3
                class="text-lg font-semibold text-gray-900 flex items-center gap-2"
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
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                    ></path>
                </svg>
                Deal Progress
            </h3>
            <span
                :class="[
                    'px-3 py-1 text-xs font-semibold rounded-full',
                    progressPercentage === 100
                        ? 'bg-green-100 text-green-700'
                        : progressPercentage >= 70
                        ? 'bg-blue-100 text-blue-700'
                        : progressPercentage >= 40
                        ? 'bg-yellow-100 text-yellow-700'
                        : 'bg-gray-100 text-gray-700',
                ]"
            >
                {{ progressPercentage }}% Complete
            </span>
        </div>

        <!-- Progress Bar -->
        <div class="mb-6">
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div
                    :class="[
                        'h-full rounded-full transition-all duration-500',
                        progressPercentage === 100
                            ? 'bg-green-500'
                            : progressPercentage >= 70
                            ? 'bg-blue-500'
                            : progressPercentage >= 40
                            ? 'bg-yellow-500'
                            : 'bg-indigo-500',
                    ]"
                    :style="{ width: progressPercentage + '%' }"
                ></div>
            </div>
        </div>

        <!-- Stages -->
        <div class="space-y-3">
            <div
                v-for="stage in stages"
                :key="stage.id"
                :class="[
                    'flex items-center gap-3 p-3 rounded-lg transition-colors',
                    stage.status === 'completed'
                        ? 'bg-green-50'
                        : stage.status === 'current'
                        ? 'bg-indigo-50 ring-2 ring-indigo-200'
                        : 'bg-gray-50',
                ]"
            >
                <!-- Icon -->
                <div
                    :class="[
                        'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center',
                        stage.status === 'completed'
                            ? 'bg-green-500 text-white'
                            : stage.status === 'current'
                            ? 'bg-indigo-500 text-white'
                            : 'bg-gray-300 text-gray-500',
                    ]"
                >
                    <svg
                        v-if="stage.status === 'completed'"
                        class="w-5 h-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    <svg
                        v-else-if="stage.status === 'current'"
                        class="w-5 h-5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <circle cx="10" cy="10" r="3" />
                    </svg>
                    <span v-else class="text-sm font-semibold">{{
                        stage.number
                    }}</span>
                </div>

                <!-- Content -->
                <div class="flex-1">
                    <p
                        :class="[
                            'text-sm font-medium',
                            stage.status === 'completed'
                                ? 'text-green-900'
                                : stage.status === 'current'
                                ? 'text-indigo-900'
                                : 'text-gray-500',
                        ]"
                    >
                        {{ stage.title }}
                    </p>
                    <p v-if="stage.date" class="text-xs text-gray-500 mt-0.5">
                        {{ formatStageDate(stage.date) }}
                    </p>
                </div>

                <!-- Badge -->
                <div v-if="stage.status === 'current'" class="flex-shrink-0">
                    <span
                        class="px-2 py-1 bg-indigo-100 text-indigo-700 text-xs font-medium rounded"
                    >
                        Current
                    </span>
                </div>
            </div>
        </div>

        <!-- Next Action (if available) -->
        <div
            v-if="nextAction"
            class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg"
        >
            <div class="flex items-start gap-3">
                <svg
                    class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    ></path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-900">Next Action</p>
                    <p class="text-sm text-blue-700 mt-1">{{ nextAction }}</p>
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

const stages = computed(() => {
    const stageList = [
        {
            id: 1,
            number: 1,
            title: "Inquiry Received",
            status: "completed",
            date: props.inquiry.created_at,
        },
        {
            id: 2,
            number: 2,
            title: "Initial Contact",
            status: props.inquiry.contacted_at ? "completed" : "pending",
            date: props.inquiry.contacted_at,
        },
        {
            id: 3,
            number: 3,
            title: "Viewing Scheduled",
            status: props.inquiry.scheduled_at ? "completed" : "pending",
            date: props.inquiry.scheduled_at,
        },
        {
            id: 4,
            number: 4,
            title: "Offer Made",
            status: props.transaction ? "completed" : "pending",
            date: props.transaction?.created_at,
        },
        {
            id: 5,
            number: 5,
            title: "Negotiation",
            status:
                props.transaction &&
                [
                    "negotiation",
                    "offer_accepted",
                    "contract_signed",
                    "finalized",
                ].includes(props.transaction.status)
                    ? "completed"
                    : "pending",
            date:
                props.transaction?.status === "negotiation"
                    ? props.transaction.updated_at
                    : null,
        },
        {
            id: 6,
            number: 6,
            title: "Contract Signed",
            status:
                props.transaction &&
                ["contract_signed", "finalized"].includes(
                    props.transaction.status
                )
                    ? "completed"
                    : "pending",
            date: props.transaction?.contract_date,
        },
        {
            id: 7,
            number: 7,
            title: "Finalized",
            status:
                props.transaction?.status === "finalized"
                    ? "completed"
                    : "pending",
            date: props.transaction?.closing_date,
        },
    ];

    // Mark the first non-completed stage as current
    const firstPending = stageList.findIndex((s) => s.status === "pending");
    if (firstPending !== -1) {
        stageList[firstPending].status = "current";
    }

    return stageList;
});

const progressPercentage = computed(() => {
    const completedStages = stages.value.filter(
        (s) => s.status === "completed"
    ).length;
    const totalStages = stages.value.length;
    return Math.round((completedStages / totalStages) * 100);
});

const nextAction = computed(() => {
    const currentStage = stages.value.find((s) => s.status === "current");

    if (!currentStage) {
        return null;
    }

    const actions = {
        "Initial Contact":
            "Reach out to the client to discuss their interest and answer questions.",
        "Viewing Scheduled":
            "Schedule a property viewing appointment with the client.",
        "Offer Made":
            "Mark the inquiry as won to create a transaction and move to the offer stage.",
        Negotiation: "Work with the client on price negotiations and terms.",
        "Contract Signed": "Prepare and execute the purchase agreement.",
        Finalized: "Complete the final paperwork and close the deal.",
    };

    return actions[currentStage.title] || null;
});

const formatStageDate = (dateString) => {
    if (!dateString) return null;
    try {
        const date = parseISO(dateString);
        return format(date, "MMM dd, yyyy");
    } catch (e) {
        return dateString;
    }
};
</script>
