<script setup>
import { computed } from "vue";
import { CheckCircleIcon } from "@heroicons/vue/24/solid";
import { CheckCircleIcon as CheckCircleOutlineIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    currentStep: {
        type: Number,
        default: 1,
    },
    totalSteps: {
        type: Number,
        default: 4,
    },
    steps: {
        type: Array,
        default: () => [
            {
                id: 1,
                title: "Basic Information",
                description: "Personal details",
            },
            {
                id: 2,
                title: "Professional Details",
                description: "License & experience",
            },
            { id: 3, title: "Documents", description: "Upload credentials" },
            { id: 4, title: "Review & Submit", description: "Final review" },
        ],
    },
    showDescription: {
        type: Boolean,
        default: true,
    },
});

const progressPercentage = computed(() => {
    return ((props.currentStep - 1) / (props.totalSteps - 1)) * 100;
});

const getStepStatus = (stepId) => {
    if (stepId < props.currentStep) return "completed";
    if (stepId === props.currentStep) return "current";
    return "upcoming";
};

const getStepClasses = (stepId) => {
    const status = getStepStatus(stepId);

    switch (status) {
        case "completed":
            return {
                circle: "bg-primary-600 text-white border-primary-600",
                line: "bg-primary-600",
                text: "text-primary-600",
                title: "font-semibold text-neutral-900",
            };
        case "current":
            return {
                circle: "bg-primary-600 text-white border-primary-600 ring-4 ring-primary-100",
                line: "bg-primary-200",
                text: "text-primary-600",
                title: "font-semibold text-primary-600",
            };
        default:
            return {
                circle: "bg-white text-neutral-400 border-neutral-300",
                line: "bg-neutral-200",
                text: "text-neutral-400",
                title: "font-medium text-neutral-500",
            };
    }
};
</script>

<template>
    <div class="w-full">
        <!-- Progress Bar -->
        <div class="relative">
            <!-- Background Line -->
            <div
                class="absolute top-6 left-1/2 transform -translate-x-1/2 h-0.5 bg-neutral-200"
                :style="{
                    width:
                        steps.length === 2
                            ? '50%'
                            : steps.length === 3
                            ? '66.666%'
                            : steps.length === 4
                            ? '75%'
                            : '100%',
                }"
            ></div>

            <!-- Progress Line -->
            <div
                class="absolute top-6 left-1/2 transform -translate-x-1/2 h-0.5 bg-primary-600 transition-all duration-500 ease-out"
                :style="{
                    width:
                        steps.length === 2
                            ? `${progressPercentage * 0.5}%`
                            : steps.length === 3
                            ? `${progressPercentage * 0.666}%`
                            : steps.length === 4
                            ? `${progressPercentage * 0.75}%`
                            : `${progressPercentage}%`,
                }"
            ></div>

            <!-- Steps -->
            <div class="relative flex justify-center">
                <div class="flex items-start justify-center w-full">
                    <div
                        v-for="step in steps"
                        :key="step.id"
                        class="flex flex-col items-center relative"
                        :style="{
                            width:
                                steps.length === 2
                                    ? '50%'
                                    : steps.length === 3
                                    ? '33.333%'
                                    : steps.length === 4
                                    ? '25%'
                                    : 'auto',
                        }"
                    >
                        <!-- Step Circle -->
                        <div class="relative z-10">
                            <div
                                :class="[
                                    'w-12 h-12 rounded-full border-2 flex items-center justify-center transition-all duration-300',
                                    getStepClasses(step.id).circle,
                                ]"
                            >
                                <CheckCircleIcon
                                    v-if="
                                        getStepStatus(step.id) === 'completed'
                                    "
                                    class="w-6 h-6"
                                />
                                <span v-else class="text-sm font-semibold">
                                    {{ step.id }}
                                </span>
                            </div>
                        </div>

                        <!-- Step Content -->
                        <div class="mt-4 text-center px-2">
                            <h3
                                :class="[
                                    'text-sm transition-colors duration-300 leading-tight',
                                    getStepClasses(step.id).title,
                                ]"
                            >
                                {{ step.title }}
                            </h3>
                            <p
                                v-if="showDescription"
                                :class="[
                                    'text-xs mt-1 transition-colors duration-300 leading-tight',
                                    getStepClasses(step.id).text,
                                ]"
                            >
                                {{ step.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Percentage (Optional) -->
        <div class="mt-4 text-center">
            <span class="text-sm text-neutral-500">
                Step {{ currentStep }} of {{ totalSteps }} ({{
                    Math.round(progressPercentage)
                }}% complete)
            </span>
        </div>
    </div>
</template>
