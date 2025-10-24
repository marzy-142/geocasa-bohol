import { ref, onMounted, onUnmounted, nextTick } from "vue";

/**
 * Composable for performance optimizations
 */
export function usePerformance() {
    const isLowEndDevice = ref(false);
    const prefersReducedMotion = ref(false);
    const connectionType = ref("unknown");

    // Detect device capabilities
    const detectDeviceCapabilities = () => {
        // Check for low-end device indicators
        const memory = navigator.deviceMemory || 4;
        const cores = navigator.hardwareConcurrency || 4;
        const connection =
            navigator.connection ||
            navigator.mozConnection ||
            navigator.webkitConnection;

        isLowEndDevice.value =
            memory < 4 ||
            cores < 4 ||
            (connection && connection.effectiveType === "slow-2g");

        // Check for reduced motion preference
        prefersReducedMotion.value = window.matchMedia(
            "(prefers-reduced-motion: reduce)"
        ).matches;

        // Check connection type
        if (connection) {
            connectionType.value = connection.effectiveType || "unknown";
        }
    };

    // Debounce function
    const debounce = (func, wait, immediate = false) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    };

    // Throttle function
    const throttle = (func, limit) => {
        let inThrottle;
        return function executedFunction(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => (inThrottle = false), limit);
            }
        };
    };

    // Intersection Observer for lazy loading
    const createIntersectionObserver = (callback, options = {}) => {
        const defaultOptions = {
            root: null,
            rootMargin: "50px",
            threshold: 0.1,
        };

        const observerOptions = { ...defaultOptions, ...options };

        return new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    callback(entry);
                }
            });
        }, observerOptions);
    };

    // Virtual scrolling helper
    const createVirtualScroll = (
        container,
        itemHeight,
        totalItems,
        visibleItems
    ) => {
        const scrollTop = ref(0);
        const startIndex = ref(0);
        const endIndex = ref(Math.min(visibleItems, totalItems));

        const updateVisibleItems = () => {
            const newStartIndex = Math.floor(scrollTop.value / itemHeight);
            const newEndIndex = Math.min(
                newStartIndex + visibleItems + 1,
                totalItems
            );

            startIndex.value = newStartIndex;
            endIndex.value = newEndIndex;
        };

        const handleScroll = throttle((event) => {
            scrollTop.value = event.target.scrollTop;
            updateVisibleItems();
        }, 16); // ~60fps

        const setupVirtualScroll = () => {
            container.addEventListener("scroll", handleScroll);
            updateVisibleItems();
        };

        const cleanup = () => {
            container.removeEventListener("scroll", handleScroll);
        };

        return {
            scrollTop,
            startIndex,
            endIndex,
            setupVirtualScroll,
            cleanup,
        };
    };

    // Image optimization
    const optimizeImage = (src, options = {}) => {
        const {
            width,
            height,
            quality = 80,
            format = "webp",
            lazy = true,
        } = options;

        // Create optimized image URL
        let optimizedSrc = src;

        if (width || height) {
            const params = new URLSearchParams();
            if (width) params.set("w", width);
            if (height) params.set("h", height);
            if (quality) params.set("q", quality);
            if (format) params.set("f", format);

            optimizedSrc = `${src}?${params.toString()}`;
        }

        return {
            src: optimizedSrc,
            loading: lazy ? "lazy" : "eager",
            decoding: "async",
        };
    };

    // Bundle splitting helper
    const loadComponent = async (importFn) => {
        try {
            const module = await importFn();
            return module.default || module;
        } catch (error) {
            console.error("Failed to load component:", error);
            return null;
        }
    };

    // Memory management
    const createMemoryManager = () => {
        const observers = new Set();
        const timers = new Set();
        const eventListeners = new Map();

        const addObserver = (observer) => {
            observers.add(observer);
            return observer;
        };

        const addTimer = (timer) => {
            timers.add(timer);
            return timer;
        };

        const addEventListener = (element, event, handler, options) => {
            element.addEventListener(event, handler, options);
            const key = `${element}-${event}`;
            if (!eventListeners.has(key)) {
                eventListeners.set(key, []);
            }
            eventListeners.get(key).push({ handler, options });
        };

        const cleanup = () => {
            // Disconnect observers
            observers.forEach((observer) => {
                if (observer.disconnect) observer.disconnect();
            });
            observers.clear();

            // Clear timers
            timers.forEach((timer) => {
                if (timer.clearTimeout) clearTimeout(timer);
                if (timer.clearInterval) clearInterval(timer);
            });
            timers.clear();

            // Remove event listeners
            eventListeners.forEach((listeners, key) => {
                const [element, event] = key.split("-");
                listeners.forEach(({ handler, options }) => {
                    element.removeEventListener(event, handler, options);
                });
            });
            eventListeners.clear();
        };

        return {
            addObserver,
            addTimer,
            addEventListener,
            cleanup,
        };
    };

    // Performance monitoring
    const createPerformanceMonitor = () => {
        const metrics = ref({
            renderTime: 0,
            memoryUsage: 0,
            networkLatency: 0,
        });

        const measureRenderTime = async (renderFn) => {
            const start = performance.now();
            await renderFn();
            const end = performance.now();
            metrics.value.renderTime = end - start;
        };

        const measureMemoryUsage = () => {
            if ("memory" in performance) {
                metrics.value.memoryUsage =
                    performance.memory.usedJSHeapSize / 1024 / 1024; // MB
            }
        };

        const measureNetworkLatency = async (url) => {
            const start = performance.now();
            try {
                await fetch(url, { method: "HEAD" });
                const end = performance.now();
                metrics.value.networkLatency = end - start;
            } catch (error) {
                console.warn("Network latency measurement failed:", error);
            }
        };

        return {
            metrics,
            measureRenderTime,
            measureMemoryUsage,
            measureNetworkLatency,
        };
    };

    // Animation optimization
    const createOptimizedAnimation = (element, keyframes, options = {}) => {
        const defaultOptions = {
            duration: 300,
            easing: "ease-out",
            fill: "forwards",
        };

        const animationOptions = { ...defaultOptions, ...options };

        // Respect reduced motion preference
        if (prefersReducedMotion.value) {
            animationOptions.duration = 0;
        }

        // Use requestAnimationFrame for better performance
        const animate = () => {
            return new Promise((resolve) => {
                const animation = element.animate(keyframes, animationOptions);
                animation.onfinish = () => resolve(animation);
            });
        };

        return animate;
    };

    // Setup performance monitoring
    onMounted(() => {
        detectDeviceCapabilities();
    });

    return {
        isLowEndDevice,
        prefersReducedMotion,
        connectionType,
        debounce,
        throttle,
        createIntersectionObserver,
        createVirtualScroll,
        optimizeImage,
        loadComponent,
        createMemoryManager,
        createPerformanceMonitor,
        createOptimizedAnimation,
    };
}

/**
 * Composable for smooth animations
 */
export function useAnimations() {
    const { prefersReducedMotion, createOptimizedAnimation } = usePerformance();

    // Fade in animation
    const fadeIn = (element, duration = 300) => {
        return createOptimizedAnimation(
            element,
            [{ opacity: 0 }, { opacity: 1 }],
            { duration }
        );
    };

    // Slide up animation
    const slideUp = (element, duration = 300) => {
        return createOptimizedAnimation(
            element,
            [
                { transform: "translateY(20px)", opacity: 0 },
                { transform: "translateY(0)", opacity: 1 },
            ],
            { duration }
        );
    };

    // Scale animation
    const scale = (element, from = 0.8, to = 1, duration = 300) => {
        return createOptimizedAnimation(
            element,
            [
                { transform: `scale(${from})`, opacity: 0 },
                { transform: `scale(${to})`, opacity: 1 },
            ],
            { duration }
        );
    };

    // Stagger animation for lists
    const stagger = async (elements, animationFn, delay = 100) => {
        const promises = elements.map((element, index) => {
            return new Promise((resolve) => {
                setTimeout(() => {
                    animationFn(element).then(resolve);
                }, index * delay);
            });
        });

        return Promise.all(promises);
    };

    // Parallax effect
    const createParallax = (element, speed = 0.5) => {
        const handleScroll = () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -speed;
            element.style.transform = `translateY(${rate}px)`;
        };

        const throttledScroll = throttle(handleScroll, 16);
        window.addEventListener("scroll", throttledScroll);

        return () => {
            window.removeEventListener("scroll", throttledScroll);
        };
    };

    return {
        fadeIn,
        slideUp,
        scale,
        stagger,
        createParallax,
    };
}
