import { ref, computed } from "vue";

export function useSmartValidation() {
    const corrections = ref({});
    const suggestions = ref({});

    // Auto-correction functions
    const autoCorrections = {
        email: (value) => {
            if (!value) return value;

            let corrected = value.toLowerCase().trim();

            // Common domain corrections
            const domainCorrections = {
                "gmail.co": "gmail.com",
                "gmail.cm": "gmail.com",
                "gmial.com": "gmail.com",
                "yahoo.co": "yahoo.com",
                "yahoo.cm": "yahoo.com",
                "hotmail.co": "hotmail.com",
                "hotmail.cm": "hotmail.com",
                "outlook.co": "outlook.com",
                "outlook.cm": "outlook.com",
            };

            Object.keys(domainCorrections).forEach((wrong) => {
                if (corrected.includes(wrong)) {
                    corrected = corrected.replace(
                        wrong,
                        domainCorrections[wrong]
                    );
                }
            });

            return corrected;
        },

        phone: (value) => {
            if (!value) return value;

            // Remove all non-numeric characters except +
            let cleaned = value.replace(/[^\d\+]/g, "");

            // Add Philippines country code if missing and number looks local
            if (cleaned.length === 10 && cleaned.startsWith("9")) {
                cleaned = "+63" + cleaned;
            } else if (cleaned.length === 11 && cleaned.startsWith("09")) {
                cleaned = "+63" + cleaned.substring(1);
            }

            return cleaned;
        },

        name: (value) => {
            if (!value) return value;

            // Capitalize first letter of each word
            return value
                .toLowerCase()
                .split(" ")
                .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                .join(" ")
                .trim();
        },

        price: (value) => {
            if (!value) return value;

            // Remove currency symbols and commas, keep only numbers and decimal point
            return value.toString().replace(/[^\d.]/g, "");
        },

        text: (value) => {
            if (!value) return value;

            // Basic text cleanup - trim and normalize spaces
            return value.trim().replace(/\s+/g, " ");
        },
    };

    // Generate smart suggestions based on field type and current value
    const generateSuggestions = (fieldType, value, error) => {
        const suggestions = [];

        if (!value && !error) return suggestions;

        switch (fieldType) {
            case "email":
                suggestions.push(...generateEmailSuggestions(value, error));
                break;
            case "tel":
            case "phone":
                suggestions.push(...generatePhoneSuggestions(value, error));
                break;
            case "number":
                suggestions.push(...generateNumberSuggestions(value, error));
                break;
            case "text":
                suggestions.push(...generateTextSuggestions(value, error));
                break;
        }

        return suggestions;
    };

    const generateEmailSuggestions = (value, error) => {
        const suggestions = [];

        if (!value) return suggestions;

        // Missing @ symbol
        if (!value.includes("@")) {
            suggestions.push({
                value: value + "@gmail.com",
                label: `Add "@gmail.com"`,
                description: "Complete email address",
                type: "completion",
            });
        }

        // Missing domain extension
        if (value.includes("@") && !value.includes(".")) {
            suggestions.push({
                value: value + ".com",
                label: `Add ".com"`,
                description: "Complete domain",
                type: "completion",
            });
        }

        // Common typos
        const corrected = autoCorrections.email(value);
        if (corrected !== value) {
            suggestions.push({
                value: corrected,
                label: `Fix typo → ${corrected}`,
                description: "Correct common domain misspelling",
                type: "correction",
            });
        }

        // Remove spaces
        if (value.includes(" ")) {
            suggestions.push({
                value: value.replace(/\s+/g, ""),
                label: `Remove spaces`,
                description: "Email addresses cannot contain spaces",
                type: "correction",
            });
        }

        return suggestions;
    };

    const generatePhoneSuggestions = (value, error) => {
        const suggestions = [];

        if (!value) return suggestions;

        const corrected = autoCorrections.phone(value);
        if (corrected !== value) {
            suggestions.push({
                value: corrected,
                label: `Format phone → ${corrected}`,
                description: "Standard Philippine format",
                type: "correction",
            });
        }

        // Add country code
        if (value.length >= 10 && !value.startsWith("+")) {
            const withCountryCode = "+63" + value.replace(/^0/, "");
            suggestions.push({
                value: withCountryCode,
                label: `Add country code → ${withCountryCode}`,
                description: "Philippine mobile format",
                type: "enhancement",
            });
        }

        return suggestions;
    };

    const generateNumberSuggestions = (value, error) => {
        const suggestions = [];

        if (!value) return suggestions;

        // Extract numbers from mixed content
        const numbersOnly = value.toString().replace(/[^\d.]/g, "");
        if (numbersOnly !== value.toString() && numbersOnly) {
            suggestions.push({
                value: numbersOnly,
                label: `Extract numbers → ${numbersOnly}`,
                description: "Remove non-numeric characters",
                type: "correction",
            });
        }

        // Format as currency (for price fields)
        if (error && error.toLowerCase().includes("price")) {
            const formatted = parseFloat(numbersOnly).toLocaleString();
            suggestions.push({
                value: numbersOnly,
                label: `Format as ${formatted}`,
                description: "Standard price format",
                type: "formatting",
            });
        }

        return suggestions;
    };

    const generateTextSuggestions = (value, error) => {
        const suggestions = [];

        if (!value) return suggestions;

        // Trim whitespace
        const trimmed = value.trim();
        if (trimmed !== value) {
            suggestions.push({
                value: trimmed,
                label: `Trim spaces`,
                description: "Remove extra whitespace",
                type: "correction",
            });
        }

        // Capitalize names
        if (error && error.toLowerCase().includes("name")) {
            const capitalized = autoCorrections.name(value);
            if (capitalized !== value) {
                suggestions.push({
                    value: capitalized,
                    label: `Capitalize → ${capitalized}`,
                    description: "Proper name format",
                    type: "formatting",
                });
            }
        }

        return suggestions;
    };

    // Format examples for different field types
    const getFormatExamples = (fieldType) => {
        const examples = {
            email: [
                { format: "user@example.com", description: "Standard format" },
                {
                    format: "name.surname@domain.co.uk",
                    description: "With subdomain",
                },
            ],
            tel: [
                { format: "+639123456789", description: "Philippine mobile" },
                { format: "+1234567890", description: "International" },
            ],
            phone: [
                { format: "+639123456789", description: "Philippine mobile" },
                { format: "+1234567890", description: "International" },
            ],
            number: [
                { format: "1500000", description: "Whole number" },
                { format: "1500000.50", description: "With decimals" },
            ],
            price: [
                { format: "1500000", description: "Amount in PHP" },
                { format: "2500000.50", description: "With cents" },
            ],
        };

        return examples[fieldType] || [];
    };

    // Quick actions for common operations
    const getQuickActions = (fieldType, value) => {
        const actions = [];

        if (value && value !== value.trim()) {
            actions.push({
                label: "Trim spaces",
                action: "trim",
                value: value.trim(),
                icon: "SparklesIcon",
            });
        }

        if (fieldType === "email" && value) {
            actions.push({
                label: "Lowercase",
                action: "lowercase",
                value: value.toLowerCase(),
                icon: "SparklesIcon",
            });
        }

        if (fieldType === "text" && value && value.toLowerCase() === value) {
            actions.push({
                label: "Capitalize",
                action: "capitalize",
                value: autoCorrections.name(value),
                icon: "SparklesIcon",
            });
        }

        if (value) {
            actions.push({
                label: "Clear",
                action: "clear",
                value: "",
                icon: "XMarkIcon",
            });
        }

        return actions;
    };

    // Apply auto-correction
    const applyAutoCorrection = (fieldType, value) => {
        const correctionFn = autoCorrections[fieldType] || autoCorrections.text;
        return correctionFn(value);
    };

    // Validate and suggest
    const validateWithSuggestions = (
        fieldType,
        value,
        error,
        fieldName = ""
    ) => {
        const fieldSuggestions = generateSuggestions(fieldType, value, error);
        const formatExamples = getFormatExamples(fieldType);
        const quickActions = getQuickActions(fieldType, value);

        return {
            suggestions: fieldSuggestions,
            formatExamples,
            quickActions,
            autoCorrection: applyAutoCorrection(fieldType, value),
        };
    };

    // Store corrections for tracking
    const recordCorrection = (
        fieldName,
        originalValue,
        correctedValue,
        type
    ) => {
        corrections.value[fieldName] = {
            original: originalValue,
            corrected: correctedValue,
            type,
            timestamp: new Date(),
        };
    };

    // Get correction history
    const getCorrectionHistory = (fieldName) => {
        return corrections.value[fieldName] || null;
    };

    // Clear corrections
    const clearCorrections = (fieldName = null) => {
        if (fieldName) {
            delete corrections.value[fieldName];
        } else {
            corrections.value = {};
        }
    };

    return {
        // Methods
        generateSuggestions,
        getFormatExamples,
        getQuickActions,
        applyAutoCorrection,
        validateWithSuggestions,
        recordCorrection,
        getCorrectionHistory,
        clearCorrections,

        // Data
        corrections: computed(() => corrections.value),
        suggestions: computed(() => suggestions.value),

        // Auto-correction functions
        autoCorrections,
    };
}
