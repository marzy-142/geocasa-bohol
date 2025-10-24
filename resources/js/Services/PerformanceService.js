/**
 * Performance Monitoring Service
 * Tracks and optimizes application performance
 */

export class PerformanceService {
    constructor() {
        this.metrics = new Map();
        this.observers = new Map();
        this.thresholds = {
            slowQuery: 1000, // 1 second
            slowRender: 100, // 100ms
            memoryWarning: 50 * 1024 * 1024, // 50MB
        };
    }

    /**
     * Start performance measurement
     */
    startMeasurement(name, type = "general") {
        const startTime = performance.now();
        const startMemory = this.getMemoryUsage();

        return {
            name,
            type,
            startTime,
            startMemory,
            end: () => this.endMeasurement(name, startTime, startMemory, type),
        };
    }

    /**
     * End performance measurement
     */
    endMeasurement(name, startTime, startMemory, type) {
        const endTime = performance.now();
        const endMemory = this.getMemoryUsage();
        const duration = endTime - startTime;
        const memoryDelta =
            endMemory.usedJSHeapSize - startMemory.usedJSHeapSize;

        const measurement = {
            name,
            type,
            duration,
            memoryDelta,
            timestamp: Date.now(),
            isSlow: duration > this.thresholds.slowRender,
            memoryWarning:
                endMemory.usedJSHeapSize > this.thresholds.memoryWarning,
        };

        this.recordMetric(measurement);
        this.notifyObservers(measurement);

        return measurement;
    }

    /**
     * Record a performance metric
     */
    recordMetric(metric) {
        const key = `${metric.type}_${metric.name}`;
        if (!this.metrics.has(key)) {
            this.metrics.set(key, []);
        }

        const metrics = this.metrics.get(key);
        metrics.push(metric);

        // Keep only last 100 measurements
        if (metrics.length > 100) {
            metrics.shift();
        }
    }

    /**
     * Get performance statistics
     */
    getStats(type = null) {
        const stats = {};

        for (const [key, metrics] of this.metrics.entries()) {
            const [metricType, name] = key.split("_", 2);

            if (type && metricType !== type) continue;

            if (!stats[metricType]) {
                stats[metricType] = {};
            }

            const durations = metrics.map((m) => m.duration);
            const memoryDeltas = metrics.map((m) => m.memoryDelta);

            stats[metricType][name] = {
                count: metrics.length,
                avgDuration: this.average(durations),
                minDuration: Math.min(...durations),
                maxDuration: Math.max(...durations),
                avgMemoryDelta: this.average(memoryDeltas),
                slowCount: metrics.filter((m) => m.isSlow).length,
                memoryWarnings: metrics.filter((m) => m.memoryWarning).length,
            };
        }

        return stats;
    }

    /**
     * Get slow operations
     */
    getSlowOperations() {
        const slowOps = [];

        for (const [key, metrics] of this.metrics.entries()) {
            const slowMetrics = metrics.filter((m) => m.isSlow);
            if (slowMetrics.length > 0) {
                slowOps.push({
                    key,
                    count: slowMetrics.length,
                    avgDuration: this.average(
                        slowMetrics.map((m) => m.duration)
                    ),
                    latest: slowMetrics[slowMetrics.length - 1],
                });
            }
        }

        return slowOps.sort((a, b) => b.avgDuration - a.avgDuration);
    }

    /**
     * Monitor component render performance
     */
    monitorComponent(componentName, renderFunction) {
        return async (...args) => {
            const measurement = this.startMeasurement(componentName, "render");
            try {
                const result = await renderFunction(...args);
                return result;
            } finally {
                measurement.end();
            }
        };
    }

    /**
     * Monitor API call performance
     */
    monitorApiCall(apiName, apiCall) {
        return async (...args) => {
            const measurement = this.startMeasurement(apiName, "api");
            try {
                const result = await apiCall(...args);
                return result;
            } finally {
                measurement.end();
            }
        };
    }

    /**
     * Get memory usage
     */
    getMemoryUsage() {
        if (performance.memory) {
            return {
                usedJSHeapSize: performance.memory.usedJSHeapSize,
                totalJSHeapSize: performance.memory.totalJSHeapSize,
                jsHeapSizeLimit: performance.memory.jsHeapSizeLimit,
            };
        }
        return { usedJSHeapSize: 0, totalJSHeapSize: 0, jsHeapSizeLimit: 0 };
    }

    /**
     * Check if memory usage is high
     */
    isMemoryHigh() {
        const memory = this.getMemoryUsage();
        return memory.usedJSHeapSize > this.thresholds.memoryWarning;
    }

    /**
     * Subscribe to performance events
     */
    subscribe(eventType, callback) {
        if (!this.observers.has(eventType)) {
            this.observers.set(eventType, []);
        }
        this.observers.get(eventType).push(callback);
    }

    /**
     * Unsubscribe from performance events
     */
    unsubscribe(eventType, callback) {
        if (this.observers.has(eventType)) {
            const callbacks = this.observers.get(eventType);
            const index = callbacks.indexOf(callback);
            if (index > -1) {
                callbacks.splice(index, 1);
            }
        }
    }

    /**
     * Notify observers
     */
    notifyObservers(measurement) {
        const eventType = measurement.isSlow ? "slow" : "normal";

        if (this.observers.has(eventType)) {
            this.observers.get(eventType).forEach((callback) => {
                try {
                    callback(measurement);
                } catch (error) {
                    console.error("Performance observer error:", error);
                }
            });
        }
    }

    /**
     * Calculate average
     */
    average(numbers) {
        return numbers.reduce((sum, num) => sum + num, 0) / numbers.length;
    }

    /**
     * Clear all metrics
     */
    clearMetrics() {
        this.metrics.clear();
    }

    /**
     * Export metrics for analysis
     */
    exportMetrics() {
        const data = {};
        for (const [key, metrics] of this.metrics.entries()) {
            data[key] = metrics;
        }
        return data;
    }
}

// Create singleton instance
export const performanceService = new PerformanceService();

// Monitor page load performance
if (typeof window !== "undefined") {
    window.addEventListener("load", () => {
        const loadTime = performance.now();
        performanceService.recordMetric({
            name: "page_load",
            type: "navigation",
            duration: loadTime,
            memoryDelta: 0,
            timestamp: Date.now(),
            isSlow: loadTime > 3000, // 3 seconds
            memoryWarning: false,
        });
    });
}

export default performanceService;

