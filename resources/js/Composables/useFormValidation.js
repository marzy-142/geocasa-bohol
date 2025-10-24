import { ref, reactive, computed, watch, nextTick } from "vue";

export function useFormValidation(initialData = {}, validationRules = {}) {
    // Form data
    const form = reactive({ ...initialData });

    // Validation state
    const errors = ref({});
    const touched = ref({});
    const isValidating = ref(false);
    const hasBeenSubmitted = ref(false);

    // Validation rules
    const rules = reactive(validationRules);

    // Computed properties
    const isValid = computed(() => {
        return Object.keys(errors.value).length === 0;
    });

    const hasErrors = computed(() => {
        return Object.keys(errors.value).length > 0;
    });

    const touchedFields = computed(() => {
        return Object.keys(touched.value).filter((key) => touched.value[key]);
    });

    const isDirty = computed(() => {
        return touchedFields.value.length > 0;
    });

    // Validation functions
    const validateField = (field, value = form[field]) => {
        const fieldRules = rules[field];
        if (!fieldRules) return { isValid: true, errors: [] };

        const fieldErrors = [];

        // Required validation
        if (
            fieldRules.required &&
            (!value || (typeof value === "string" && !value.trim()))
        ) {
            fieldErrors.push(
                fieldRules.requiredMessage ||
                    `${formatFieldName(field)} is required`
            );
        }

        // Skip other validations if field is empty and not required
        if (!value && !fieldRules.required) {
            return { isValid: true, errors: [] };
        }

        // Email validation
        if (
            fieldRules.email &&
            value &&
            !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
        ) {
            fieldErrors.push(
                fieldRules.emailMessage || "Please enter a valid email address"
            );
        }

        // Min length validation
        if (
            fieldRules.minLength &&
            value &&
            value.length < fieldRules.minLength
        ) {
            fieldErrors.push(
                fieldRules.minLengthMessage ||
                    `Must be at least ${fieldRules.minLength} characters long`
            );
        }

        // Max length validation
        if (
            fieldRules.maxLength &&
            value &&
            value.length > fieldRules.maxLength
        ) {
            fieldErrors.push(
                fieldRules.maxLengthMessage ||
                    `Must not exceed ${fieldRules.maxLength} characters`
            );
        }

        // Pattern validation
        if (fieldRules.pattern && value && !fieldRules.pattern.test(value)) {
            fieldErrors.push(fieldRules.patternMessage || "Invalid format");
        }

        // Custom validation function
        if (
            fieldRules.validator &&
            typeof fieldRules.validator === "function"
        ) {
            const customResult = fieldRules.validator(value, form);
            if (customResult !== true) {
                fieldErrors.push(
                    typeof customResult === "string"
                        ? customResult
                        : "Invalid value"
                );
            }
        }

        // Phone validation - Updated to support Philippine mobile numbers
        if (fieldRules.phone && value) {
            const cleanPhone = value.replace(/[\s\-\(\)]/g, "");

            // Allow Philippine mobile (09xxxxxxxxx or 9xxxxxxxxx), international (+639xxxxxxxxx), or other international formats
            const phonePattern =
                /^(\+?63[0-9]{10}|0?9[0-9]{9}|\+?[1-9][0-9]{7,14})$/;
            const isValid = phonePattern.test(cleanPhone);

            if (!isValid) {
                fieldErrors.push(
                    fieldRules.phoneMessage ||
                        "Please enter a valid phone number"
                );
            }
        }

        // URL validation
        if (fieldRules.url && value) {
            try {
                new URL(value);
            } catch {
                fieldErrors.push(
                    fieldRules.urlMessage || "Please enter a valid URL"
                );
            }
        }

        // Numeric validation
        if (fieldRules.numeric && value && !/^\d+(\.\d+)?$/.test(value)) {
            fieldErrors.push(
                fieldRules.numericMessage || "Must be a valid number"
            );
        }

        // Min/Max value validation
        if (
            fieldRules.min !== undefined &&
            value &&
            parseFloat(value) < fieldRules.min
        ) {
            fieldErrors.push(
                fieldRules.minMessage || `Must be at least ${fieldRules.min}`
            );
        }

        if (
            fieldRules.max !== undefined &&
            value &&
            parseFloat(value) > fieldRules.max
        ) {
            fieldErrors.push(
                fieldRules.maxMessage || `Must not exceed ${fieldRules.max}`
            );
        }

        return {
            isValid: fieldErrors.length === 0,
            errors: fieldErrors,
        };
    };

    const validateForm = async () => {
        isValidating.value = true;
        const newErrors = {};

        for (const field in rules) {
            const validation = validateField(field);
            if (!validation.isValid) {
                newErrors[field] = validation.errors[0]; // Take first error
            }
        }

        errors.value = newErrors;
        isValidating.value = false;

        return {
            isValid: Object.keys(newErrors).length === 0,
            errors: newErrors,
        };
    };

    // Field management
    const setFieldValue = (field, value) => {
        form[field] = value;

        // Real-time validation for touched fields
        if (touched.value[field] || hasBeenSubmitted.value) {
            nextTick(() => {
                const validation = validateField(field, value);
                if (validation.isValid) {
                    clearFieldError(field);
                } else {
                    setFieldError(field, validation.errors[0]);
                }
            });
        }
    };

    const setFieldError = (field, error) => {
        console.log(`setFieldError called with field: ${field}, error:`, error);
        
        // Check if we're accidentally setting an 'error' key
        if (field === 'error') {
            console.log('CRITICAL: Attempting to set field named "error"!');
            console.trace('Stack trace for error field setting:');
        }
        
        errors.value = { ...errors.value, [field]: error };
        
        console.log('Errors after setFieldError:', errors.value);
    };

    const clearFieldError = (field) => {
        const { [field]: removed, ...rest } = errors.value;
        errors.value = rest;
    };

    const clearAllErrors = () => {
        errors.value = {};
    };

    const setFieldTouched = (field, isTouched = true) => {
        touched.value = { ...touched.value, [field]: isTouched };
    };

    const setAllTouched = () => {
        const allTouched = {};
        Object.keys(rules).forEach((field) => {
            allTouched[field] = true;
        });
        touched.value = allTouched;
    };

    // Server error handling
    const setServerErrors = (serverErrors) => {
        errors.value = { ...errors.value, ...serverErrors };

        // Mark fields with server errors as touched
        Object.keys(serverErrors).forEach((field) => {
            setFieldTouched(field, true);
        });
    };

    // Auto-fix functionality
    const autoFixErrors = () => {
        const fixes = {};

        Object.keys(errors.value).forEach((field) => {
            const value = form[field];
            const fieldRules = rules[field];

            if (!fieldRules) return;

            // Auto-fix email format
            if (fieldRules.email && value && typeof value === "string") {
                const trimmed = value.trim().toLowerCase();
                if (trimmed !== value) {
                    fixes[field] = trimmed;
                }
            }

            // Auto-fix phone format
            if (fieldRules.phone && value && typeof value === "string") {
                const cleaned = value.replace(/[^\d\+]/g, "");
                if (cleaned !== value && cleaned.length >= 10) {
                    fixes[field] = cleaned;
                }
            }

            // Auto-trim text fields
            if (typeof value === "string" && value !== value.trim()) {
                fixes[field] = value.trim();
            }
        });

        // Apply fixes
        Object.keys(fixes).forEach((field) => {
            setFieldValue(field, fixes[field]);
        });

        return fixes;
    };

    // Utility functions
    const formatFieldName = (field) => {
        return field
            .replace(/[_-]/g, " ")
            .replace(/([a-z])([A-Z])/g, "$1 $2")
            .split(" ")
            .map(
                (word) =>
                    word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
            )
            .join(" ");
    };

    const reset = (newData = initialData) => {
        Object.keys(form).forEach((key) => {
            delete form[key];
        });
        Object.assign(form, newData);

        errors.value = {};
        touched.value = {};
        hasBeenSubmitted.value = false;
    };

    const submit = async (submitFn) => {
        hasBeenSubmitted.value = true;
        setAllTouched();

        const validation = await validateForm();

        if (validation.isValid) {
            try {
                return await submitFn(form);
            } catch (error) {
                // Handle server validation errors
                if (error.response?.data?.errors) {
                    setServerErrors(error.response.data.errors);
                }
                throw error;
            }
        } else {
            throw new Error("Form validation failed");
        }
    };

    // Watch for form changes to provide real-time validation
    Object.keys(rules).forEach((field) => {
        watch(
            () => form[field],
            (newValue) => {
                if (touched.value[field] || hasBeenSubmitted.value) {
                    const validation = validateField(field, newValue);
                    if (validation.isValid) {
                        clearFieldError(field);
                    } else {
                        setFieldError(field, validation.errors[0]);
                    }
                }
            }
        );
    });

    return {
        // Form data
        form,

        // Validation state
        errors: computed(() => errors.value),
        touched: computed(() => touched.value),
        isValidating: computed(() => isValidating.value),
        isValid,
        hasErrors,
        isDirty,
        hasBeenSubmitted: computed(() => hasBeenSubmitted.value),

        // Methods
        validateField,
        validateForm,
        setFieldValue,
        setFieldError,
        clearFieldError,
        clearAllErrors,
        setFieldTouched,
        setAllTouched,
        setServerErrors,
        autoFixErrors,
        reset,
        submit,

        // Utility
        formatFieldName,
    };
}
