/**
 * Loading State Management Service
 * Centralized loading state management with optimistic updates
 */

export class LoadingStateService {
    constructor() {
        this.states = new Map();
        this.observers = new Map();
        this.optimisticUpdates = new Map();
    }

    /**
     * Set loading state
     */
    setLoading(key, isLoading, metadata = {}) {
        const previousState = this.states.get(key);
        const newState = {
            isLoading,
            timestamp: Date.now(),
            metadata: {
                ...metadata,
                retryCount: metadata.retryCount || 0,
                error: null,
            },
        };

        this.states.set(key, newState);
        this.notifyObservers(key, newState, previousState);
    }

    /**
     * Get loading state
     */
    getLoading(key) {
        return (
            this.states.get(key) || {
                isLoading: false,
                timestamp: 0,
                metadata: {},
            }
        );
    }

    /**
     * Check if any loading is active
     */
    isAnyLoading() {
        for (const state of this.states.values()) {
            if (state.isLoading) return true;
        }
        return false;
    }

    /**
     * Get all loading states
     */
    getAllLoadingStates() {
        const result = {};
        for (const [key, state] of this.states.entries()) {
            result[key] = state;
        }
        return result;
    }

    /**
     * Set error state
     */
    setError(key, error, metadata = {}) {
        const state = this.states.get(key) || {
            isLoading: false,
            timestamp: 0,
            metadata: {},
        };
        state.isLoading = false;
        state.metadata.error = error;
        state.metadata.retryCount = (state.metadata.retryCount || 0) + 1;
        state.metadata = { ...state.metadata, ...metadata };

        this.states.set(key, state);
        this.notifyObservers(key, state, null);
    }

    /**
     * Clear error state
     */
    clearError(key) {
        const state = this.states.get(key);
        if (state) {
            state.metadata.error = null;
            this.states.set(key, state);
            this.notifyObservers(key, state, null);
        }
    }

    /**
     * Set success state
     */
    setSuccess(key, data = null, metadata = {}) {
        const state = this.states.get(key) || {
            isLoading: false,
            timestamp: 0,
            metadata: {},
        };
        state.isLoading = false;
        state.metadata.error = null;
        state.metadata.data = data;
        state.metadata = { ...state.metadata, ...metadata };

        this.states.set(key, state);
        this.notifyObservers(key, state, null);
    }

    /**
     * Set optimistic update
     */
    setOptimisticUpdate(key, updateData, rollbackData) {
        this.optimisticUpdates.set(key, {
            updateData,
            rollbackData,
            timestamp: Date.now(),
        });
    }

    /**
     * Get optimistic update
     */
    getOptimisticUpdate(key) {
        return this.optimisticUpdates.get(key);
    }

    /**
     * Clear optimistic update
     */
    clearOptimisticUpdate(key) {
        this.optimisticUpdates.delete(key);
    }

    /**
     * Rollback optimistic update
     */
    rollbackOptimisticUpdate(key) {
        const update = this.optimisticUpdates.get(key);
        if (update) {
            this.clearOptimisticUpdate(key);
            return update.rollbackData;
        }
        return null;
    }

    /**
     * Subscribe to loading state changes
     */
    subscribe(key, callback) {
        if (!this.observers.has(key)) {
            this.observers.set(key, []);
        }
        this.observers.get(key).push(callback);
    }

    /**
     * Unsubscribe from loading state changes
     */
    unsubscribe(key, callback) {
        if (this.observers.has(key)) {
            const callbacks = this.observers.get(key);
            const index = callbacks.indexOf(callback);
            if (index > -1) {
                callbacks.splice(index, 1);
            }
        }
    }

    /**
     * Notify observers
     */
    notifyObservers(key, newState, previousState) {
        if (this.observers.has(key)) {
            this.observers.get(key).forEach((callback) => {
                try {
                    callback(newState, previousState);
                } catch (error) {
                    console.error("Loading state observer error:", error);
                }
            });
        }
    }

    /**
     * Create loading state manager for a specific key
     */
    createManager(key) {
        return {
            setLoading: (isLoading, metadata) =>
                this.setLoading(key, isLoading, metadata),
            setError: (error, metadata) => this.setError(key, error, metadata),
            setSuccess: (data, metadata) =>
                this.setSuccess(key, data, metadata),
            clearError: () => this.clearError(key),
            getState: () => this.getLoading(key),
            subscribe: (callback) => this.subscribe(key, callback),
            unsubscribe: (callback) => this.unsubscribe(key, callback),
            setOptimistic: (updateData, rollbackData) =>
                this.setOptimisticUpdate(key, updateData, rollbackData),
            clearOptimistic: () => this.clearOptimisticUpdate(key),
            rollback: () => this.rollbackOptimisticUpdate(key),
        };
    }

    /**
     * Clear all states
     */
    clearAll() {
        this.states.clear();
        this.optimisticUpdates.clear();
    }

    /**
     * Clear old states (older than specified time)
     */
    clearOldStates(maxAge = 5 * 60 * 1000) {
        // 5 minutes
        const now = Date.now();
        for (const [key, state] of this.states.entries()) {
            if (now - state.timestamp > maxAge && !state.isLoading) {
                this.states.delete(key);
            }
        }
    }
}

// Create singleton instance
export const loadingStateService = new LoadingStateService();

// Auto-cleanup old states every 5 minutes
setInterval(() => {
    loadingStateService.clearOldStates();
}, 5 * 60 * 1000);

export default loadingStateService;

