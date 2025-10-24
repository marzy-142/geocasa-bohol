import { ref, onMounted, onUnmounted } from "vue";

/**
 * Composable for managing accessibility features
 */
export function useAccessibility() {
    const isKeyboardUser = ref(false);
    const focusableElements = ref([]);
    const currentFocusIndex = ref(-1);

    // Detect if user is navigating with keyboard
    const handleKeydown = (event) => {
        if (event.key === "Tab") {
            isKeyboardUser.value = true;
        }
    };

    // Detect mouse usage to hide focus indicators
    const handleMouseDown = () => {
        isKeyboardUser.value = false;
    };

    // Get all focusable elements
    const getFocusableElements = (container = document) => {
        const focusableSelectors = [
            "button:not([disabled])",
            "input:not([disabled])",
            "select:not([disabled])",
            "textarea:not([disabled])",
            "a[href]",
            "area[href]",
            '[tabindex]:not([tabindex="-1"])',
            '[contenteditable="true"]',
        ].join(", ");

        return Array.from(container.querySelectorAll(focusableSelectors));
    };

    // Trap focus within a container
    const trapFocus = (container) => {
        const focusable = getFocusableElements(container);
        const firstElement = focusable[0];
        const lastElement = focusable[focusable.length - 1];

        const handleTabKey = (event) => {
            if (event.key !== "Tab") return;

            if (event.shiftKey) {
                // Shift + Tab
                if (document.activeElement === firstElement) {
                    event.preventDefault();
                    lastElement.focus();
                }
            } else {
                // Tab
                if (document.activeElement === lastElement) {
                    event.preventDefault();
                    firstElement.focus();
                }
            }
        };

        // Focus first element
        if (firstElement) {
            firstElement.focus();
        }

        container.addEventListener("keydown", handleTabKey);

        return () => {
            container.removeEventListener("keydown", handleTabKey);
        };
    };

    // Announce to screen readers
    const announce = (message, priority = "polite") => {
        const announcement = document.createElement("div");
        announcement.setAttribute("aria-live", priority);
        announcement.setAttribute("aria-atomic", "true");
        announcement.className = "sr-only";
        announcement.textContent = message;

        document.body.appendChild(announcement);

        // Remove after announcement
        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    };

    // Generate unique IDs for form elements
    const generateId = (prefix = "element") => {
        return `${prefix}-${Math.random().toString(36).substr(2, 9)}`;
    };

    // Create accessible button props
    const createButtonProps = (options = {}) => {
        const {
            id = generateId("button"),
            ariaLabel,
            ariaDescribedBy,
            ariaExpanded,
            ariaControls,
            ariaPressed,
            disabled = false,
            type = "button",
        } = options;

        return {
            id,
            type,
            disabled,
            "aria-label": ariaLabel,
            "aria-describedby": ariaDescribedBy,
            "aria-expanded": ariaExpanded,
            "aria-controls": ariaControls,
            "aria-pressed": ariaPressed,
            tabindex: disabled ? -1 : 0,
        };
    };

    // Create accessible form field props
    const createFormFieldProps = (options = {}) => {
        const {
            id = generateId("input"),
            name,
            label,
            required = false,
            invalid = false,
            describedBy,
            errorMessage,
        } = options;

        const errorId = invalid && errorMessage ? `${id}-error` : null;
        const descriptionId = describedBy ? `${id}-description` : null;

        return {
            id,
            name,
            required,
            "aria-invalid": invalid,
            "aria-describedby":
                [errorId, descriptionId].filter(Boolean).join(" ") || undefined,
            "aria-required": required,
        };
    };

    // Create accessible table props
    const createTableProps = (options = {}) => {
        const {
            caption,
            captionId = generateId("caption"),
            tableId = generateId("table"),
        } = options;

        return {
            id: tableId,
            role: "table",
            "aria-labelledby": caption ? captionId : undefined,
        };
    };

    // Create accessible modal props
    const createModalProps = (options = {}) => {
        const { id = generateId("modal"), title, describedBy } = options;

        return {
            id,
            role: "dialog",
            "aria-modal": "true",
            "aria-labelledby": title ? `${id}-title` : undefined,
            "aria-describedby": describedBy,
        };
    };

    // Handle escape key
    const handleEscape = (callback) => {
        const handleKeydown = (event) => {
            if (event.key === "Escape") {
                callback();
            }
        };

        document.addEventListener("keydown", handleKeydown);
        return () => document.removeEventListener("keydown", handleKeydown);
    };

    // Handle arrow key navigation
    const handleArrowKeys = (callback) => {
        const handleKeydown = (event) => {
            if (
                ["ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight"].includes(
                    event.key
                )
            ) {
                event.preventDefault();
                callback(event.key);
            }
        };

        document.addEventListener("keydown", handleKeydown);
        return () => document.removeEventListener("keydown", handleKeydown);
    };

    // Setup accessibility listeners
    onMounted(() => {
        document.addEventListener("keydown", handleKeydown);
        document.addEventListener("mousedown", handleMouseDown);
    });

    onUnmounted(() => {
        document.removeEventListener("keydown", handleKeydown);
        document.removeEventListener("mousedown", handleMouseDown);
    });

    return {
        isKeyboardUser,
        getFocusableElements,
        trapFocus,
        announce,
        generateId,
        createButtonProps,
        createFormFieldProps,
        createTableProps,
        createModalProps,
        handleEscape,
        handleArrowKeys,
    };
}

/**
 * Composable for managing focus
 */
export function useFocus() {
    const focusHistory = ref([]);
    const currentFocus = ref(null);

    const focus = (element) => {
        if (element && typeof element.focus === "function") {
            focusHistory.value.push(document.activeElement);
            element.focus();
            currentFocus.value = element;
        }
    };

    const focusPrevious = () => {
        if (focusHistory.value.length > 0) {
            const previousElement = focusHistory.value.pop();
            focus(previousElement);
        }
    };

    const focusFirst = (container) => {
        const focusable = container.querySelectorAll(
            'button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])'
        );
        if (focusable.length > 0) {
            focus(focusable[0]);
        }
    };

    const focusLast = (container) => {
        const focusable = container.querySelectorAll(
            'button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])'
        );
        if (focusable.length > 0) {
            focus(focusable[focusable.length - 1]);
        }
    };

    return {
        focus,
        focusPrevious,
        focusFirst,
        focusLast,
        currentFocus,
        focusHistory,
    };
}

/**
 * Composable for managing ARIA live regions
 */
export function useLiveRegion() {
    const createLiveRegion = (priority = "polite") => {
        const region = document.createElement("div");
        region.setAttribute("aria-live", priority);
        region.setAttribute("aria-atomic", "true");
        region.className = "sr-only";
        document.body.appendChild(region);

        const announce = (message) => {
            region.textContent = message;
        };

        const destroy = () => {
            if (region.parentNode) {
                region.parentNode.removeChild(region);
            }
        };

        return { announce, destroy };
    };

    return { createLiveRegion };
}
