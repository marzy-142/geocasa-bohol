class NotificationService {
    constructor() {
        this.permission = Notification.permission;
        this.isSupported = "Notification" in window;
    }

    /**
     * Request permission for browser notifications
     */
    async requestPermission() {
        if (!this.isSupported) {
            throw new Error("Browser notifications are not supported");
        }

        if (this.permission === "granted") {
            return true;
        }

        const permission = await Notification.requestPermission();
        this.permission = permission;
        return permission === "granted";
    }

    /**
     * Show a browser notification
     */
    show(title, options = {}) {
        if (!this.isSupported || this.permission !== "granted") {
            return null;
        }

        const defaultOptions = {
            icon: "/favicon.ico",
            badge: "/favicon.ico",
            tag: "geocasa-notification",
            requireInteraction: false,
            ...options,
        };

        return new Notification(title, defaultOptions);
    }

    /**
     * Show notification for new inquiry
     */
    showNewInquiry(inquiry) {
        return this.show("New Property Inquiry", {
            body: `New inquiry for ${inquiry.property_title} from ${inquiry.client_name}`,
            icon: "/favicon.ico",
            tag: `inquiry-${inquiry.inquiry_id}`,
            data: {
                type: "inquiry",
                id: inquiry.inquiry_id,
                url: `/inquiries/${inquiry.inquiry_id}`,
            },
        });
    }

    /**
     * Show notification for transaction update
     */
    showTransactionUpdate(transaction) {
        return this.show("Transaction Update", {
            body: `Transaction status updated to ${transaction.new_status.replace(
                "_",
                " "
            )} for ${transaction.property_title}`,
            icon: "/favicon.ico",
            tag: `transaction-${transaction.transaction_id}`,
            data: {
                type: "transaction",
                id: transaction.transaction_id,
                url: `/transactions/${transaction.transaction_id}`,
            },
        });
    }

    /**
     * Show notification for new message
     */
    showNewMessage(message) {
        return this.show("New Message", {
            body: `New message from ${message.sender_name} in ${message.conversation_title}`,
            icon: "/favicon.ico",
            tag: `message-${message.message_id}`,
            data: {
                type: "message",
                id: message.conversation_id,
                url: `/conversations/${message.conversation_id}`,
            },
        });
    }

    /**
     * Show notification for seller request
     */
    showSellerRequest(request) {
        return this.show("New Seller Request", {
            body: `New seller request from ${request.client_name}`,
            icon: "/favicon.ico",
            tag: `seller-request-${request.seller_request_id}`,
            data: {
                type: "seller_request",
                id: request.seller_request_id,
                url: `/seller-requests/${request.seller_request_id}`,
            },
        });
    }

    /**
     * Handle notification click events
     */
    setupClickHandlers() {
        // This would be handled by the service worker in a real implementation
        // For now, we'll handle it in the main thread
        document.addEventListener("click", (event) => {
            if (event.target.tagName === "NOTIFICATION") {
                const data = event.target.data;
                if (data && data.url) {
                    window.location.href = data.url;
                }
            }
        });
    }

    /**
     * Check if notifications are enabled in user preferences
     */
    async areNotificationsEnabled() {
        try {
            const response = await window.axios.get("/notifications/settings");
            return response.data.browser_notifications;
        } catch (error) {
            console.error("Failed to check notification preferences:", error);
            return false;
        }
    }

    /**
     * Initialize the notification service
     */
    async init() {
        if (!this.isSupported) {
            console.warn("Browser notifications are not supported");
            return false;
        }

        const enabled = await this.areNotificationsEnabled();
        if (!enabled) {
            return false;
        }

        if (this.permission === "default") {
            await this.requestPermission();
        }

        this.setupClickHandlers();
        return this.permission === "granted";
    }
}

export default new NotificationService();
