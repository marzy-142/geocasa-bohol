<template>
    <div class="chart-container relative">
        <canvas
            ref="chartCanvas"
            :width="width"
            :height="height"
            class="w-full h-full"
        ></canvas>
        <div
            v-if="loading"
            class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75"
        >
            <div class="flex items-center space-x-2">
                <div
                    class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"
                ></div>
                <span class="text-sm text-gray-600">Loading chart...</span>
            </div>
        </div>
        <div
            v-if="error"
            class="absolute inset-0 flex items-center justify-center bg-red-50"
        >
            <div class="text-center">
                <ExclamationTriangleIcon
                    class="h-8 w-8 text-red-500 mx-auto mb-2"
                />
                <p class="text-sm text-red-600">{{ error }}</p>
                <button
                    @click="retry"
                    class="mt-2 text-xs text-red-600 hover:text-red-800 underline"
                >
                    Retry
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from "vue";
import Chart from "chart.js/auto";
import { ExclamationTriangleIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    type: {
        type: String,
        required: true,
        validator: (value) =>
            ["line", "bar", "pie", "doughnut"].includes(value),
    },
    data: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
    width: {
        type: Number,
        default: 400,
    },
    height: {
        type: Number,
        default: 200,
    },
});

const chartCanvas = ref(null);
const chartInstance = ref(null);
const loading = ref(true);
const error = ref(null);

const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: "top",
        },
        tooltip: {
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            titleColor: "white",
            bodyColor: "white",
            borderColor: "rgba(255, 255, 255, 0.1)",
            borderWidth: 1,
        },
    },
    scales:
        props.type === "pie" || props.type === "doughnut"
            ? {}
            : {
                  x: {
                      grid: {
                          color: "rgba(0, 0, 0, 0.05)",
                      },
                      ticks: {
                          color: "#6B7280",
                      },
                  },
                  y: {
                      grid: {
                          color: "rgba(0, 0, 0, 0.05)",
                      },
                      ticks: {
                          color: "#6B7280",
                      },
                  },
              },
};

const createChart = async () => {
    loading.value = true;
    error.value = null;
    try {
        await nextTick();

        if (!chartCanvas.value) {
            throw new Error("Chart canvas not found");
        }

        // Destroy existing chart
        if (chartInstance.value) {
            chartInstance.value.destroy();
        }

        const mergedOptions = { ...defaultOptions, ...props.options };

        chartInstance.value = new Chart(chartCanvas.value, {
            type: props.type,
            data: props.data,
            options: mergedOptions,
        });
    } catch (err) {
        console.error("Chart creation error:", err);
        error.value = err.message || "Failed to create chart";
    } finally {
        // Always stop showing the loading overlay, even if Chart.js silently fails
        loading.value = false;
        // Failsafe: in case rendering is deferred, clear loading shortly after
        setTimeout(() => {
            loading.value = false;
        }, 500);
    }
};

const retry = () => {
    createChart();
};

// Watch for data changes
watch(
    () => props.data,
    () => {
        if (chartInstance.value) {
            chartInstance.value.data = props.data;
            chartInstance.value.update();
        }
    },
    { deep: true }
);

watch(
    () => props.options,
    () => {
        if (chartInstance.value) {
            chartInstance.value.options = {
                ...defaultOptions,
                ...props.options,
            };
            chartInstance.value.update();
        }
    },
    { deep: true }
);

onMounted(() => {
    createChart();
});

onUnmounted(() => {
    if (chartInstance.value) {
        chartInstance.value.destroy();
    }
});
</script>

<style scoped>
.chart-container {
    position: relative;
    width: 100%;
    height: 100%;
}
</style>
