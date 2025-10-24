/**
 * Data Loading Service
 * Handles optimized data loading with caching and error handling
 */

export class DataLoadingService {
    constructor() {
        this.cache = new Map();
        this.loadingStates = new Map();
        this.retryAttempts = new Map();
        this.maxRetries = 3;
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
    }

    /**
     * Load data with caching and error handling
     */
    async loadData(key, loader, options = {}) {
        const {
            cache = true,
            retry = true,
            timeout = 10000,
            fallback = null,
        } = options;

        // Check cache first
        if (cache && this.cache.has(key)) {
            const cached = this.cache.get(key);
            if (Date.now() - cached.timestamp < this.cacheTimeout) {
                return cached.data;
            }
            this.cache.delete(key);
        }

        // Check if already loading
        if (this.loadingStates.has(key)) {
            return this.loadingStates.get(key);
        }

        // Create loading promise
        const loadingPromise = this._executeWithRetry(
            loader,
            key,
            retry,
            timeout,
            fallback
        );
        this.loadingStates.set(key, loadingPromise);

        try {
            const result = await loadingPromise;

            // Cache successful result
            if (cache && result !== null) {
                this.cache.set(key, {
                    data: result,
                    timestamp: Date.now(),
                });
            }

            return result;
        } catch (error) {
            console.error(`Failed to load data for key: ${key}`, error);
            throw error;
        } finally {
            this.loadingStates.delete(key);
        }
    }

    /**
     * Execute loader with retry logic
     */
    async _executeWithRetry(loader, key, retry, timeout, fallback) {
        const attempts = this.retryAttempts.get(key) || 0;

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), timeout);

            const result = await loader(controller.signal);
            clearTimeout(timeoutId);

            // Reset retry attempts on success
            this.retryAttempts.delete(key);
            return result;
        } catch (error) {
            if (error.name === "AbortError") {
                throw new Error(`Request timeout for key: ${key}`);
            }

            if (retry && attempts < this.maxRetries) {
                this.retryAttempts.set(key, attempts + 1);
                const delay = Math.pow(2, attempts) * 1000; // Exponential backoff

                await new Promise((resolve) => setTimeout(resolve, delay));
                return this._executeWithRetry(
                    loader,
                    key,
                    retry,
                    timeout,
                    fallback
                );
            }

            // Return fallback if available
            if (fallback !== null) {
                console.warn(`Using fallback data for key: ${key}`);
                return fallback;
            }

            throw error;
        }
    }

    /**
     * Preload data for better performance
     */
    async preloadData(dataKeys) {
        const promises = dataKeys.map(({ key, loader, options }) =>
            this.loadData(key, loader, options).catch((error) => {
                console.warn(`Preload failed for ${key}:`, error);
                return null;
            })
        );

        return Promise.allSettled(promises);
    }

    /**
     * Clear cache
     */
    clearCache(pattern = null) {
        if (pattern) {
            const regex = new RegExp(pattern);
            for (const key of this.cache.keys()) {
                if (regex.test(key)) {
                    this.cache.delete(key);
                }
            }
        } else {
            this.cache.clear();
        }
    }

    /**
     * Get cache statistics
     */
    getCacheStats() {
        const now = Date.now();
        let validEntries = 0;
        let expiredEntries = 0;

        for (const [key, value] of this.cache.entries()) {
            if (now - value.timestamp < this.cacheTimeout) {
                validEntries++;
            } else {
                expiredEntries++;
            }
        }

        return {
            totalEntries: this.cache.size,
            validEntries,
            expiredEntries,
            loadingStates: this.loadingStates.size,
        };
    }

    /**
     * Clean expired cache entries
     */
    cleanExpiredCache() {
        const now = Date.now();
        for (const [key, value] of this.cache.entries()) {
            if (now - value.timestamp >= this.cacheTimeout) {
                this.cache.delete(key);
            }
        }
    }
}

// Create singleton instance
export const dataLoadingService = new DataLoadingService();

// Auto-cleanup expired cache every 5 minutes
setInterval(() => {
    dataLoadingService.cleanExpiredCache();
}, 5 * 60 * 1000);

export default dataLoadingService;

