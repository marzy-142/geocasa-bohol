import { onMounted, onUnmounted, watch } from 'vue';

export function useFocusTrap(containerRef, isActive) {
    let previouslyFocusedElement = null;
    
    const FOCUSABLE_ELEMENTS = [
        'a[href]',
        'area[href]',
        'input:not([disabled]):not([type="hidden"])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        'button:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
        '[contenteditable]'
    ].join(', ');
    
    const getFocusableElements = () => {
        if (!containerRef.value) return [];
        return Array.from(containerRef.value.querySelectorAll(FOCUSABLE_ELEMENTS))
            .filter(el => !el.hasAttribute('disabled') && el.offsetParent !== null);
    };
    
    const handleKeyDown = (event) => {
        if (!isActive.value || event.key !== 'Tab') return;
        
        const focusableElements = getFocusableElements();
        if (focusableElements.length === 0) return;
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
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
    
    const trapFocus = () => {
        if (!containerRef.value) return;
        
        // Save currently focused element
        previouslyFocusedElement = document.activeElement;
        
        // Focus first focusable element
        const focusableElements = getFocusableElements();
        if (focusableElements.length > 0) {
            focusableElements[0].focus();
        }
        
        // Add event listener
        document.addEventListener('keydown', handleKeyDown);
    };
    
    const releaseFocus = () => {
        // Remove event listener
        document.removeEventListener('keydown', handleKeyDown);
        
        // Restore focus to previously focused element
        if (previouslyFocusedElement && previouslyFocusedElement.focus) {
            previouslyFocusedElement.focus();
        }
        
        previouslyFocusedElement = null;
    };
    
    // Watch for changes in isActive
    watch(isActive, (newValue) => {
        if (newValue) {
            // Small delay to ensure DOM is updated
            setTimeout(trapFocus, 10);
        } else {
            releaseFocus();
        }
    });
    
    // Cleanup on unmount
    onUnmounted(() => {
        releaseFocus();
    });
    
    return {
        trapFocus,
        releaseFocus
    };
}
