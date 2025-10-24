import { getCurrentInstance } from 'vue';

export function useToast() {
    const instance = getCurrentInstance();
    
    const showToast = (type, title, message, options = {}) => {
        // Try to find NotificationToast component in the app
        const root = instance?.appContext?.app?._instance;
        const notificationToast = findNotificationToast(root);
        
        if (notificationToast) {
            notificationToast.exposed.addNotification({
                type,
                title,
                message,
                ...options
            });
        } else {
            // Fallback to console if toast not available
            console.log(`[${type}] ${title}: ${message}`);
        }
    };
    
    const findNotificationToast = (vnode) => {
        if (!vnode) return null;
        
        // Check if current node is NotificationToast
        if (vnode.type?.name === 'NotificationToast' || vnode.type?.__name === 'NotificationToast') {
            return vnode;
        }
        
        // Check children
        if (vnode.subTree) {
            const result = findNotificationToast(vnode.subTree);
            if (result) return result;
        }
        
        // Check component children
        if (vnode.component) {
            const result = findNotificationToast(vnode.component);
            if (result) return result;
        }
        
        // Check array children
        if (Array.isArray(vnode.children)) {
            for (const child of vnode.children) {
                const result = findNotificationToast(child);
                if (result) return result;
            }
        }
        
        return null;
    };
    
    // Convenience methods
    const success = (title, message, options = {}) => {
        showToast('success', title, message, { ...options, autoDismiss: true });
    };
    
    const error = (title, message, options = {}) => {
        showToast('error', title, message, { ...options, autoDismiss: true });
    };
    
    const info = (title, message, options = {}) => {
        showToast('info', title, message, { ...options, autoDismiss: true });
    };
    
    const warning = (title, message, options = {}) => {
        showToast('warning', title, message, { ...options, autoDismiss: true });
    };
    
    return {
        showToast,
        success,
        error,
        info,
        warning
    };
}
