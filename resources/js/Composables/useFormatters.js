export function useFormatters() {
    const formatCurrency = (value) => {
        if (!value) return "₱0";
        return new Intl.NumberFormat("en-PH", {
            style: "currency",
            currency: "PHP",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value);
    };

    const formatDate = (date) => {
        return new Date(date).toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
        });
    };

    const formatRelativeTime = (date) => {
        if (!date) return "";
        
        const now = new Date();
        const past = new Date(date);
        const diffMs = now - past;
        const diffSecs = Math.floor(diffMs / 1000);
        const diffMins = Math.floor(diffSecs / 60);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);
        const diffWeeks = Math.floor(diffDays / 7);
        const diffMonths = Math.floor(diffDays / 30);
        const diffYears = Math.floor(diffDays / 365);

        if (diffSecs < 60) {
            return "Just now";
        } else if (diffMins < 60) {
            return `${diffMins} ${diffMins === 1 ? "minute" : "minutes"} ago`;
        } else if (diffHours < 24) {
            return `${diffHours} ${diffHours === 1 ? "hour" : "hours"} ago`;
        } else if (diffDays < 7) {
            return `${diffDays} ${diffDays === 1 ? "day" : "days"} ago`;
        } else if (diffWeeks < 4) {
            return `${diffWeeks} ${diffWeeks === 1 ? "week" : "weeks"} ago`;
        } else if (diffMonths < 12) {
            return `${diffMonths} ${diffMonths === 1 ? "month" : "months"} ago`;
        } else {
            return `${diffYears} ${diffYears === 1 ? "year" : "years"} ago`;
        }
    };

    const formatDateTime = (date) => {
        return new Date(date).toLocaleString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
            hour: "numeric",
            minute: "2-digit",
            hour12: true,
        });
    };

    const formatNumber = (value) => {
        return new Intl.NumberFormat("en-US").format(value);
    };

    const formatArea = (value) => {
        return `${formatNumber(value)} sqm`;
    };

    const formatPercentage = (value) => {
        return `${value.toFixed(1)}%`;
    };

    return {
        formatCurrency,
        formatDate,
        formatRelativeTime,
        formatDateTime,
        formatNumber,
        formatArea,
        formatPercentage,
    };
}
