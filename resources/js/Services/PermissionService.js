/**
 * Centralized Permission Service
 * Provides consistent permission checking across all components
 */
export class PermissionService {
    /**
     * Check if user can create properties
     */
    static canCreateProperty(user) {
        return user?.role === "broker" && user?.is_approved === true;
    }

    /**
     * Check if user can edit a specific property
     */
    static canEditProperty(user, property) {
        if (!user || !property) return false;

        // Admins can edit any property
        if (user.role === "admin") return true;

        // Brokers can only edit their own properties
        return (
            user.role === "broker" &&
            user.is_approved === true &&
            property.broker_id === user.id
        );
    }

    /**
     * Check if user can delete a specific property
     */
    static canDeleteProperty(user, property) {
        return this.canEditProperty(user, property);
    }

    /**
     * Check if user can manage clients
     */
    static canManageClients(user) {
        return user?.role === "broker" && user?.is_approved === true;
    }

    /**
     * Check if user can edit a specific client
     */
    static canEditClient(user, client) {
        if (!user || !client) return false;

        // Admins can edit any client
        if (user.role === "admin") return true;

        // Brokers can only edit their own clients
        return (
            user.role === "broker" &&
            user.is_approved === true &&
            client.broker_id === user.id
        );
    }

    /**
     * Check if user can manage seller requests
     */
    static canManageSellerRequests(user) {
        return user?.role === "broker" && user?.is_approved === true;
    }

    /**
     * Check if user can assign brokers to seller requests
     */
    static canAssignBrokers(user) {
        return user?.role === "admin";
    }

    /**
     * Check if user can view transactions
     */
    static canViewTransactions(user) {
        return user?.role === "broker" && user?.is_approved === true;
    }

    /**
     * Check if user can manage inquiries
     */
    static canManageInquiries(user) {
        return user?.role === "broker" && user?.is_approved === true;
    }

    /**
     * Check if user can access admin features
     */
    static isAdmin(user) {
        return user?.role === "admin";
    }

    /**
     * Check if user is an approved broker
     */
    static isApprovedBroker(user) {
        return user?.role === "broker" && user?.is_approved === true;
    }

    /**
     * Check if user can feature properties
     */
    static canFeatureProperty(user) {
        return (
            user?.role === "admin" ||
            (user?.role === "broker" && user?.is_approved === true)
        );
    }

    /**
     * Get user's accessible routes based on role and permissions
     */
    static getAccessibleRoutes(user) {
        if (!user) return [];

        const routes = [];

        // Common routes for all authenticated users
        routes.push("dashboard");

        if (user.role === "admin") {
            routes.push(
                "admin.dashboard",
                "admin.users.*",
                "admin.brokers.*",
                "admin.properties.*",
                "admin.reports.*",
                "admin.analytics.*",
                "admin.compliance.*",
                "admin.activity.*"
            );
        } else if (user.role === "broker" && user.is_approved) {
            routes.push(
                "broker.dashboard",
                "broker.properties.*",
                "clients.*",
                "seller-requests.*",
                "inquiries.*",
                "transactions.*",
                "conversations.*"
            );
        } else if (user.role === "client") {
            routes.push("client.dashboard", "public.properties.*");
        }

        return routes;
    }

    /**
     * Check if user can access a specific route
     */
    static canAccessRoute(user, routeName) {
        const accessibleRoutes = this.getAccessibleRoutes(user);

        // Check for exact match
        if (accessibleRoutes.includes(routeName)) return true;

        // Check for wildcard matches
        return accessibleRoutes.some((route) => {
            if (route.includes("*")) {
                const pattern = route.replace("*", ".*");
                const regex = new RegExp(`^${pattern}$`);
                return regex.test(routeName);
            }
            return false;
        });
    }
}

export default PermissionService;

