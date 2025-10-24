<template>
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">
                    Engagement Metrics
                </h3>
                <span
                    :class="getEngagementLevelClass(engagementLevel)"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                >
                    {{ engagementLevel }}
                </span>
            </div>
        </div>

        <div class="px-6 py-4 space-y-6">
            <!-- Overall Score -->
            <div>
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="text-gray-600">Overall Score</span>
                    <span class="font-medium text-gray-900"
                        >{{ engagementScore }}/100</span
                    >
                </div>
                <div class="bg-gray-200 rounded-full h-3">
                    <div
                        :style="{ width: engagementScore + '%' }"
                        :class="getScoreBarClass(engagementScore)"
                        class="h-3 rounded-full transition-all duration-500"
                    ></div>
                </div>
            </div>

            <!-- Metrics Grid -->
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">
                        {{ totalInteractions }}
                    </div>
                    <div class="text-sm text-gray-600">Interactions</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">
                        {{ loginCount }}
                    </div>
                    <div class="text-sm text-gray-600">Logins</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">
                        {{ responseTime }}
                    </div>
                    <div class="text-sm text-gray-600">Avg Response</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">
                        {{ satisfactionScore }}
                    </div>
                    <div class="text-sm text-gray-600">Satisfaction</div>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div v-if="recentActivity.length > 0">
                <h4 class="text-sm font-medium text-gray-900 mb-3">
                    Recent Activity
                </h4>
                <div class="space-y-2">
                    <div
                        v-for="activity in recentActivity.slice(0, 5)"
                        :key="activity.id"
                        class="flex items-center text-sm"
                    >
                        <div
                            :class="getActivityIconClass(activity.type)"
                            class="w-6 h-6 rounded-full flex items-center justify-center mr-3"
                        >
                            <svg
                                class="w-3 h-3 text-white"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    v-if="activity.type === 'login'"
                                    fill-rule="evenodd"
                                    d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z"
                                    clip-rule="evenodd"
                                />
                                <path
                                    v-else-if="activity.type === 'approval'"
                                    fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"
                                />
                                <path
                                    v-else-if="activity.type === 'document'"
                                    fill-rule="evenodd"
                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                                    clip-rule="evenodd"
                                />
                                <path
                                    v-else
                                    fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <span class="text-gray-900">{{
                                activity.description
                            }}</span>
                            <span class="text-gray-500 ml-2">{{
                                formatTimeAgo(activity.created_at)
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Engagement Tips -->
            <div v-if="engagementScore < 70" class="bg-blue-50 rounded-lg p-4">
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
                        <h4 class="text-sm font-medium text-blue-800">
                            Engagement Tips
                        </h4>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li v-if="loginCount < 3">
                                    Log in regularly to stay updated
                                </li>
                                <li v-if="totalInteractions < 5">
                                    Respond to broker messages promptly
                                </li>
                                <li v-if="satisfactionScore < 4">
                                    Provide feedback to improve service
                                </li>
                                <li>
                                    Review transaction updates as they come in
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    engagementScore: {
        type: Number,
        default: 0,
    },
    totalInteractions: {
        type: Number,
        default: 0,
    },
    loginCount: {
        type: Number,
        default: 0,
    },
    responseTime: {
        type: Number,
        default: 0,
    },
    satisfactionScore: {
        type: Number,
        default: 0,
    },
    recentActivity: {
        type: Array,
        default: () => [],
    },
});

const engagementLevel = computed(() => {
    if (props.engagementScore >= 90) return "Very High";
    if (props.engagementScore >= 70) return "High";
    if (props.engagementScore >= 50) return "Medium";
    return "Low";
});

const getEngagementLevelClass = (level) => {
    const classes = {
        "Very High": "bg-green-100 text-green-800",
        High: "bg-blue-100 text-blue-800",
        Medium: "bg-yellow-100 text-yellow-800",
        Low: "bg-red-100 text-red-800",
    };
    return classes[level] || "bg-gray-100 text-gray-800";
};

const getScoreBarClass = (score) => {
    if (score >= 90) return "bg-green-500";
    if (score >= 70) return "bg-blue-500";
    if (score >= 50) return "bg-yellow-500";
    return "bg-red-500";
};

const getActivityIconClass = (type) => {
    const classes = {
        login: "bg-blue-500",
        approval: "bg-green-500",
        document: "bg-purple-500",
        message: "bg-yellow-500",
        default: "bg-gray-500",
    };
    return classes[type] || classes.default;
};

const formatTimeAgo = (date) => {
    const now = new Date();
    const activityDate = new Date(date);
    const diffInMinutes = Math.floor((now - activityDate) / (1000 * 60));

    if (diffInMinutes < 1) return "Just now";
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`;

    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours}h ago`;

    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `${diffInDays}d ago`;

    return activityDate.toLocaleDateString();
};
</script>
