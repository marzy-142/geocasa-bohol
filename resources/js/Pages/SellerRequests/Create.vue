<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from "vue";
import { useForm, Head } from "@inertiajs/vue3";
import { useFormValidation } from "@/Composables/useFormValidation.js";
import { useSmartValidation } from "@/Composables/useSmartValidation.js";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import PublicFooter from "@/Components/PublicFooter.vue";
import ModernButton from "@/Components/ModernButton.vue";
import FormField from "@/Components/FormField.vue";
import ValidationSummary from "@/Components/ValidationSummary.vue";
import ValidationError from "@/Components/ValidationError.vue";
import {
    CheckCircleIcon,
    PhotoIcon,
    MapPinIcon,
    HomeIcon,
    UserIcon,
    DocumentTextIcon,
    DocumentIcon,
    CurrencyDollarIcon,
    XMarkIcon,
    ShieldCheckIcon,
    ClockIcon,
    StarIcon,
    ExclamationTriangleIcon,
    CheckIcon,
    XCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    availableFeatures: Array,
    availableBrokers: Array,
    municipalities: Array,
    auth: Object,
});

// Initialize form validation with enhanced rules - FIXED: Match backend field names
const validationRules = {
    name: {
        required: true,
        minLength: 2,
        maxLength: 255,
        pattern: /^[a-zA-Z\s\-\.]+$/,
        requiredMessage: "Full name is required",
        minLengthMessage: "Name must be at least 2 characters long",
        patternMessage:
            "Name can only contain letters, spaces, hyphens, and periods",
    },
    email: {
        required: true,
        email: true,
        custom: (value) => {
            // Check for temporary email domains
            const tempDomains = [
                "10minutemail.com",
                "guerrillamail.com",
                "mailinator.com",
                "tempmail.org",
                "throwaway.email",
                "temp-mail.org",
            ];
            const domain = value.split("@")[1]?.toLowerCase();
            if (tempDomains.includes(domain)) {
                return "Please use a permanent email address. Temporary email services are not allowed.";
            }
            return null;
        },
        requiredMessage: "Email address is required",
        emailMessage: "Please enter a valid email address",
    },
    phone: {
        required: true,
        custom: (value) => {
            const phoneDigits = value.replace(/[^0-9]/g, "");
            if (phoneDigits.length < 10 || phoneDigits.length > 15) {
                return "Phone number must be between 10 and 15 digits";
            }
            return null;
        },
        requiredMessage: "Phone number is required",
        phoneMessage: "Please enter a valid phone number",
    },
    address: {
        required: true,
        minLength: 10,
        maxLength: 500,
        requiredMessage: "Current address is required",
        minLengthMessage: "Please provide a complete address",
    },
    property_title: {
        required: true,
        minLength: 5,
        maxLength: 255,
        custom: (value) => {
            // Check for spam words
            const spamWords = [
                "buy now",
                "click here",
                "limited time",
                "act fast",
                "guaranteed",
            ];
            const lowerValue = value.toLowerCase();
            for (const spamWord of spamWords) {
                if (lowerValue.includes(spamWord)) {
                    return "Property title contains promotional language that is not allowed.";
                }
            }
            return null;
        },
        requiredMessage: "Property title is required",
        minLengthMessage: "Title must be at least 5 characters long",
    },
    property_description: {
        required: true,
        minLength: 20,
        maxLength: 2000,
        custom: (value) => {
            // Check for spam words
            const spamWords = [
                "buy now",
                "click here",
                "limited time",
                "act fast",
                "guaranteed",
            ];
            const lowerValue = value.toLowerCase();
            for (const spamWord of spamWords) {
                if (lowerValue.includes(spamWord)) {
                    return "Property description contains promotional language that is not allowed.";
                }
            }
            return null;
        },
        requiredMessage: "Property description is required",
        minLengthMessage: "Description must be at least 20 characters long",
    },
    property_type: {
        required: true,
        custom: (value) => {
            const validTypes = [
                "residential_lot",
                "agricultural_land",
                "commercial_lot",
                "industrial_lot",
                "beachfront",
                "mountain_view",
                "rice_field",
                "coconut_plantation",
                "subdivision_lot",
                "titled_land",
                "tax_declared",
            ];
            if (!validTypes.includes(value)) {
                return "Please select a valid property type.";
            }
            return null;
        },
        requiredMessage: "Property type is required",
    },
    asking_price: {
        required: true,
        numeric: true,
        min: 1000,
        max: 1000000000,
        custom: (value, formData) => {
            // Since GeoCasa exclusively deals with land properties,
            // we'll use a general minimum price validation
            const price = parseFloat(value);
            const minPrice = 50000; // Minimum price for land properties

            if (price < minPrice) {
                return `The asking price seems unusually low for land property. Please verify the amount.`;
            }
            return null;
        },
        requiredMessage: "Asking price is required",
        numericMessage: "Price must be a valid number",
        minMessage: "Asking price must be at least ₱1,000",
        maxMessage:
            "Asking price seems unreasonably high. Please verify the amount.",
    },
    lot_area: {
        numeric: true,
        min: 1,
        max: 1000000,
        custom: (value, formData) => {
            if (!value) return null; // Optional field

            // Since GeoCasa exclusively deals with land properties,
            // we'll use a general minimum area validation
            const area = parseFloat(value);
            const minArea = 100; // Minimum area for land properties in square meters

            if (area < minArea) {
                return `The lot area seems unusually small for land property. Please verify the measurement.`;
            }
            return null;
        },
        numericMessage: "Lot area must be a valid number",
        minMessage: "Lot area must be at least 1 square meter",
        maxMessage:
            "Lot area seems unreasonably large. Please verify the measurement.",
    },
    // Location rules aligned to backend
    municipality: {
        required: true,
        maxLength: 100,
        requiredMessage: "Municipality is required",
    },
    barangay: {
        required: false,
        maxLength: 100,
    },
    terms_accepted: {
        required: true,
        accepted: true,
        requiredMessage: "You must accept the terms and conditions",
    },
};

// Email validation helper function
const isValidEmail = (email) => {
    if (!email) return false;

    // Enhanced email validation regex
    const emailRegex =
        /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;

    if (!emailRegex.test(email)) {
        return false;
    }

    // Additional checks for common invalid patterns
    if (email.includes("..") || email.startsWith(".") || email.endsWith(".")) {
        return false;
    }

    // Check for minimum domain requirements
    const parts = email.split("@");
    if (parts.length !== 2 || parts[0].length === 0 || parts[1].length < 3) {
        return false;
    }

    // Check for valid domain structure
    if (parts.length === 2 && !parts[1].includes(".")) {
        return false;
    }

    return true;
};

const {
    form: validationForm,
    errors,
    isValid,
    hasErrors,
    validateForm,
    validateField: validateFieldComposable,
    setFieldValue,
    setFieldTouched,
    setFieldError,
    clearFieldError,
    setServerErrors,
    autoFixErrors,
    submit: submitWithValidation,
} = useFormValidation(
    {
        name: "",
        email: "",
        phone: "",
        address: "",
        property_title: "",
        property_description: "",
        property_type: "",
        asking_price: "",
        municipality: "",
        barangay: "",
        lot_area: "",
        title_type: "",
        features: [],
        zoning_classification: "",
        road_access: false,
        water_source: false,
        electricity_available: false,
        internet_available: false,
        uploaded_images: [],
        additional_notes: "",
        marketing_consent: false,
        newsletter_consent: false,
        terms_accepted: false,
        broker_selection_method: "auto",
        preferred_broker_id: null,
    },
    validationRules
);

const form = useForm(validationForm);

// Initialize smart validation
const { getSmartSuggestions, getQuickActions } = useSmartValidation();

// Form initialization complete

// Broker selection
const brokerSearch = ref("");
const filteredBrokers = computed(() => {
    if (!props.availableBrokers) return [];
    if (!brokerSearch.value) return props.availableBrokers;

    const search = brokerSearch.value.toLowerCase();
    return props.availableBrokers.filter(
        (broker) =>
            broker.name.toLowerCase().includes(search) ||
            broker.location.toLowerCase().includes(search) ||
            (broker.firm && broker.firm.toLowerCase().includes(search))
    );
});

const imageFiles = ref([]);
const propertyDocuments = ref([]);
const ownershipDocuments = ref([]);
const currentStep = ref(1);
const totalSteps = 5; // Updated to 5 steps (added broker selection)
const isSubmitting = ref(false);
const submissionStatus = ref(null); // 'success', 'error', or null
const submissionMessage = ref("");

// Enhanced step validation with detailed error tracking
const getStepValidationErrors = (step) => {
    const errors = [];

    switch (step) {
        case 1:
            const step1Fields = ["name", "email", "phone", "address"];
            step1Fields.forEach((field) => {
                if (
                    !validationForm[field] ||
                    validationForm[field].trim() === ""
                ) {
                    errors.push(
                        `${
                            field.charAt(0).toUpperCase() + field.slice(1)
                        } is required`
                    );
                } else if (errors.value && errors.value[field]) {
                    errors.push(errors.value[field]);
                }
            });
            break;

        case 2:
            const step2Fields = [
                "property_title",
                "property_description",
                "property_type",
                "asking_price",
            ];
            step2Fields.forEach((field) => {
                if (
                    !validationForm[field] ||
                    validationForm[field].toString().trim() === ""
                ) {
                    const fieldName = field
                        .replace("_", " ")
                        .replace(/\b\w/g, (l) => l.toUpperCase());
                    errors.push(`${fieldName} is required`);
                } else if (errors.value && errors.value[field]) {
                    errors.push(errors.value[field]);
                }
            });
            break;

        case 3:
            const step3Fields = ["municipality", "address"];
            step3Fields.forEach((field) => {
                if (
                    !validationForm[field] ||
                    validationForm[field].toString().trim() === ""
                ) {
                    const fieldName = field
                        .replace("_", " ")
                        .replace(/\b\w/g, (l) => l.toUpperCase());
                    errors.push(`${fieldName} is required`);
                } else if (errors.value && errors.value[field]) {
                    errors.push(errors.value[field]);
                }
            });

            break;

        case 4:
            // Broker selection validation
            if (
                validationForm.broker_selection_method === "manual" &&
                !validationForm.preferred_broker_id
            ) {
                errors.push("Please select a broker to continue");
            }
            break;

        case 5:
            // Images validation
            if (!imageFiles.value || imageFiles.value.length === 0) {
                errors.push("At least one property image is required");
            }
            break;
    }

    return errors;
};

// Enhanced canProceed with detailed validation feedback
const canProceedWithFeedback = computed(() => {
    const currentStepErrors = getStepValidationErrors(currentStep.value);
    return {
        canProceed: currentStepErrors.length === 0,
        errors: currentStepErrors,
        errorCount: currentStepErrors.length,
    };
});

// Form validation with enhanced logic - FIXED: Updated field names
const isStep1Valid = computed(() => {
    const step1Fields = ["name", "email", "phone", "address"];
    const isValid = step1Fields.every((field) => {
        const hasValue =
            validationForm[field] && validationForm[field].trim() !== "";

        // Use the composable's validateField function to ensure proper validation
        const validation = validateFieldComposable(
            field,
            validationForm[field]
        );

        // Log validation results to catch any 'error' key being set
        if (
            validation &&
            validation.errors &&
            validation.errors.includes("error")
        ) {
            console.log(
                `CRITICAL: validateFieldComposable returned 'error' for field ${field}:`,
                validation
            );
        }

        const hasError =
            !validation.isValid || (errors.value && errors.value[field]);

        // Debug logging for step 1 validation
        if (!hasValue || hasError) {
            console.log(`Step 1 field ${field} validation:`, {
                hasValue,
                validation,
                hasError,
                fieldValue: validationForm[field],
                errorValue: errors.value[field],
            });
        }

        return hasValue && !hasError;
    });
    return isValid;
});

const isStep2Valid = computed(() => {
    const step2Fields = [
        "property_title",
        "property_description",
        "property_type",
        "asking_price",
    ];
    const isValid = step2Fields.every((field) => {
        const hasValue =
            validationForm[field] &&
            validationForm[field].toString().trim() !== "";

        // Use the composable's validateField function to ensure proper validation
        const validation = validateFieldComposable(
            field,
            validationForm[field]
        );
        const hasError =
            !validation.isValid || (errors.value && errors.value[field]);

        return hasValue && !hasError;
    });
    return isValid;
});

const isStep3Valid = computed(() => {
    const step3Fields = ["municipality", "address"];
    const isValid = step3Fields.every((field) => {
        const hasValue =
            validationForm[field] &&
            validationForm[field].toString().trim() !== "";

        // Use the composable's validateField function to ensure proper validation
        const validation = validateFieldComposable(
            field,
            validationForm[field]
        );
        const hasError =
            !validation.isValid || (errors.value && errors.value[field]);

        return hasValue && !hasError;
    });
    // Step 3 should not require terms acceptance; validate it on final step instead
    return isValid;
});

const canProceed = computed(() => {
    switch (currentStep.value) {
        case 1:
            return isStep1Valid.value;
        case 2:
            return isStep2Valid.value;
        case 3:
            return isStep3Valid.value;
        case 4:
            // Broker selection step - always allow proceeding
            // Auto-assignment doesn't require selection, manual does
            if (validationForm.broker_selection_method === "manual") {
                return validationForm.preferred_broker_id !== null;
            }
            return true; // Auto-assignment always valid
        case 5:
            // Final step - check all previous steps and images
            const canSubmit =
                isStep1Valid.value &&
                isStep2Valid.value &&
                isStep3Valid.value &&
                imageFiles.value.length > 0;
            return canSubmit;
        default:
            return false;
    }
});

const getStepValidationClass = (step) => {
    if (submissionStatus.value === "error") {
        return "bg-red-500 text-white shadow-lg";
    }

    // Enhanced validation functions for better error tracking
    const validateAllSteps = () => {
        console.log("validateAllSteps called");
        // Clear existing errors first
        errors.value = {};

        // Validate all steps comprehensively
        const allErrors = {};

        console.log("Validating form data:", validationForm);

        // Step 1 validation
        if (!validationForm.name || validationForm.name.trim() === "") {
            allErrors.name = ["Full name is required"];
            console.log("Name validation failed");
        }
        if (!validationForm.email || validationForm.email.trim() === "") {
            allErrors.email = ["Email address is required"];
            console.log("Email validation failed - empty");
        } else if (!isValidEmail(validationForm.email)) {
            allErrors.email = ["Please enter a valid email address"];
            console.log("Email validation failed - invalid format");
        }
        if (!validationForm.phone || validationForm.phone.trim() === "") {
            allErrors.phone = ["Phone number is required"];
            console.log("Phone validation failed");
        }
        if (!validationForm.address || validationForm.address.trim() === "") {
            allErrors.address = ["Property address is required"];
            console.log("Address validation failed");
        }

        // Step 2 validation
        if (
            !validationForm.property_title ||
            validationForm.property_title.trim() === ""
        ) {
            allErrors.property_title = ["Property title is required"];
            console.log("Property title validation failed");
        }
        if (
            !validationForm.property_description ||
            validationForm.property_description.trim() === ""
        ) {
            allErrors.property_description = [
                "Property description is required",
            ];
            console.log("Property description validation failed");
        }
        if (
            !validationForm.property_type ||
            validationForm.property_type.trim() === ""
        ) {
            allErrors.property_type = ["Property type is required"];
            console.log("Property type validation failed");
        }
        if (!validationForm.asking_price || validationForm.asking_price <= 0) {
            allErrors.asking_price = ["Valid asking price is required"];
            console.log("Asking price validation failed");
        }

        // Step 3 validation
        if (
            !validationForm.municipality ||
            validationForm.municipality.trim() === ""
        ) {
            allErrors.municipality = ["Municipality is required"];
            console.log("Municipality validation failed");
        }
        if (!validationForm.address || validationForm.address.trim() === "") {
            allErrors.address = ["Complete address is required"];
            console.log("Address validation failed (step 3)");
        }

        console.log("All validation errors found:", allErrors);

        // Update errors
        errors.value = allErrors;

        console.log("Errors after setting:", errors.value);

        // Return validation status
        return Object.keys(allErrors).length === 0;
    };

    // Auto-fix common validation issues
    const autoFixErrors = () => {
        // Auto-fix email format
        if (validationForm.email && validationForm.email.includes(" ")) {
            validationForm.email = validationForm.email.replace(/\s+/g, "");
        }

        // Auto-fix phone number format
        if (validationForm.phone) {
            validationForm.phone = validationForm.phone.replace(
                /[^\d+()-\s]/g,
                ""
            );
        }

        // Auto-fix asking price
        if (
            validationForm.asking_price &&
            typeof validationForm.asking_price === "string"
        ) {
            const numericPrice = parseFloat(
                validationForm.asking_price.replace(/[^\d.]/g, "")
            );
            if (!isNaN(numericPrice)) {
                validationForm.asking_price = numericPrice;
            }
        }

        // Re-validate after auto-fix
        validateAllSteps();

        submissionStatus.value = "success";
        submissionMessage.value =
            "Auto-fix applied. Please review the changes and continue.";

        setTimeout(() => {
            submissionStatus.value = null;
            submissionMessage.value = "";
        }, 3000);
    };

    // Clear all errors
    const clearErrors = () => {
        errors.value = {};
        submissionStatus.value = null;
        submissionMessage.value = "";
    };
    return "bg-primary-500 text-white shadow-lg";
};

const getStepIcon = (step) => {
    const icons = {
        1: UserIcon,
        2: DocumentTextIcon,
        3: MapPinIcon,
        4: PhotoIcon,
    };
    return icons[step] || HomeIcon;
};

const createImageUrl = (file) => {
    if (
        typeof window !== "undefined" &&
        window.URL &&
        window.URL.createObjectURL
    ) {
        return window.URL.createObjectURL(file);
    }
    return "";
};

const nextStep = async () => {
    // Enhanced validation check with detailed feedback
    const validationFeedback = canProceedWithFeedback.value;

    if (!validationFeedback.canProceed) {
        // Prevent navigation and show specific errors
        submissionStatus.value = "error";
        submissionMessage.value = `Please complete the following required fields:\n• ${validationFeedback.errors.join(
            "\n• "
        )}`;

        // Focus on the first invalid field for better UX
        const firstErrorField = getFirstInvalidField(currentStep.value);
        if (firstErrorField) {
            setTimeout(() => {
                const fieldElement = document.querySelector(
                    `[name="${firstErrorField}"], #${firstErrorField}`
                );
                if (fieldElement) {
                    fieldElement.focus();
                    fieldElement.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
                }
            }, 100);
        }

        return false;
    }

    // Clear any previous error messages
    submissionStatus.value = null;
    submissionMessage.value = "";

    if (currentStep.value < totalSteps) {
        currentStep.value++;
        // Auto-save progress silently
        saveDraft(false);
    }
};

// Helper function to get the first invalid field in current step
const getFirstInvalidField = (step) => {
    switch (step) {
        case 1:
            const step1Fields = ["name", "email", "phone", "address"];
            return step1Fields.find(
                (field) =>
                    !validationForm[field] ||
                    validationForm[field].trim() === "" ||
                    errors.value[field]
            );
        case 2:
            const step2Fields = [
                "property_title",
                "property_description",
                "asking_price",
            ];
            return step2Fields.find(
                (field) =>
                    !validationForm[field] ||
                    validationForm[field].toString().trim() === "" ||
                    errors.value[field]
            );
        case 3:
            const step3Fields = ["municipality", "address"];
            const invalidField = step3Fields.find(
                (field) =>
                    !validationForm[field] ||
                    validationForm[field].toString().trim() === "" ||
                    errors.value[field]
            );
            if (invalidField) return invalidField;
            break;
    }
    return null;
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const handleImageUpload = async (event) => {
    const files = Array.from(event.target.files);

    // Enhanced validation with better user feedback
    if (imageFiles.value.length + files.length > 10) {
        showNotification("error", "You can upload maximum 10 images");
        return;
    }

    // Validate total size
    const totalSize = [...imageFiles.value, ...files].reduce(
        (sum, file) => sum + file.size,
        0
    );
    if (totalSize > 50 * 1024 * 1024) {
        // 50MB total
        showNotification("error", "Total image size cannot exceed 50MB");
        return;
    }

    // Enhanced file validation with optimized image dimension checking
    const validFiles = [];
    const invalidFiles = [];
    const imageValidationPromises = [];

    for (const file of files) {
        // Size validation
        if (file.size > 5 * 1024 * 1024) {
            invalidFiles.push(`${file.name}: File too large (max 5MB)`);
            continue;
        }

        // Type validation
        if (
            ![
                "image/jpeg",
                "image/png",
                "image/jpg",
                "image/gif",
                "image/webp",
            ].includes(file.type)
        ) {
            invalidFiles.push(
                `${file.name}: Invalid format (JPEG, PNG, GIF, WebP only)`
            );
            continue;
        }

        // Create promise for image dimension validation
        const validationPromise = new Promise((resolve) => {
            const img = new Image();
            img.onload = function () {
                if (this.width < 300 || this.height < 300) {
                    invalidFiles.push(
                        `${file.name}: Image too small (minimum 300x300px)`
                    );
                    resolve(null);
                } else if (this.width > 4000 || this.height > 4000) {
                    invalidFiles.push(
                        `${file.name}: Image too large (maximum 4000x4000px)`
                    );
                    resolve(null);
                } else {
                    resolve(file);
                }
                // Clean up object URL to prevent memory leaks
                URL.revokeObjectURL(this.src);
            };
            img.onerror = function () {
                invalidFiles.push(`${file.name}: Invalid image file`);
                URL.revokeObjectURL(this.src);
                resolve(null);
            };
            img.src = URL.createObjectURL(file);
        });

        imageValidationPromises.push(validationPromise);
    }

    // Wait for all validations to complete
    try {
        const validationResults = await Promise.all(imageValidationPromises);
        const successfulFiles = validationResults.filter(
            (file) => file !== null
        );

        // Show validation results
        if (invalidFiles.length > 0) {
            showNotification(
                "error",
                `Some files were rejected:\n${invalidFiles.join("\n")}`
            );
        }

        if (successfulFiles.length > 0) {
            imageFiles.value.push(...successfulFiles);
            form.uploaded_images = [...imageFiles.value];
            showNotification(
                "success",
                `${successfulFiles.length} image(s) uploaded successfully`
            );
        }
    } catch (error) {
        showNotification("error", "Error processing images. Please try again.");
        console.error("Image validation error:", error);
    }
};

const removeImage = (index) => {
    const removedFile = imageFiles.value[index];
    imageFiles.value.splice(index, 1);
    form.uploaded_images = [...imageFiles.value];
    showNotification("info", `Removed ${removedFile.name}`);
};

const handlePropertyDocumentUpload = (event) => {
    const files = Array.from(event.target.files);

    // Enhanced validation
    if (propertyDocuments.value.length + files.length > 10) {
        showNotification(
            "error",
            "You can upload maximum 10 property documents"
        );
        return;
    }

    // Validate total size
    const totalSize = [...propertyDocuments.value, ...files].reduce(
        (sum, file) => sum + file.size,
        0
    );
    if (totalSize > 100 * 1024 * 1024) {
        // 100MB total
        showNotification("error", "Total document size cannot exceed 100MB");
        return;
    }

    const validFiles = [];
    const invalidFiles = [];

    files.forEach((file) => {
        if (file.size > 10 * 1024 * 1024) {
            invalidFiles.push(`${file.name}: File too large (max 10MB)`);
            return;
        }

        const allowedTypes = [
            "application/pdf",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "image/jpeg",
            "image/png",
            "image/jpg",
        ];

        if (!allowedTypes.includes(file.type)) {
            invalidFiles.push(
                `${file.name}: Invalid format (PDF, DOC, DOCX, JPEG, PNG only)`
            );
            return;
        }

        validFiles.push(file);
    });

    // Show validation results
    if (invalidFiles.length > 0) {
        showNotification(
            "error",
            `Some files were rejected:\n${invalidFiles.join("\n")}`
        );
    }

    if (validFiles.length > 0) {
        propertyDocuments.value.push(...validFiles);
        form.property_documents = [...propertyDocuments.value];
        showNotification(
            "success",
            `${validFiles.length} document(s) uploaded successfully`
        );
    }
};

const removePropertyDocument = (index) => {
    const removedFile = propertyDocuments.value[index];
    propertyDocuments.value.splice(index, 1);
    form.property_documents = [...propertyDocuments.value];
    showNotification("info", `Removed ${removedFile.name}`);
};

const handleOwnershipDocumentUpload = (event) => {
    const files = Array.from(event.target.files);

    // Enhanced validation
    if (ownershipDocuments.value.length + files.length > 5) {
        showNotification(
            "error",
            "You can upload maximum 5 ownership documents"
        );
        return;
    }

    // Validate total size
    const totalSize = [...ownershipDocuments.value, ...files].reduce(
        (sum, file) => sum + file.size,
        0
    );
    if (totalSize > 50 * 1024 * 1024) {
        // 50MB total
        showNotification(
            "error",
            "Total ownership document size cannot exceed 50MB"
        );
        return;
    }

    const validFiles = [];
    const invalidFiles = [];

    files.forEach((file) => {
        if (file.size > 10 * 1024 * 1024) {
            invalidFiles.push(`${file.name}: File too large (max 10MB)`);
            return;
        }

        const allowedTypes = [
            "application/pdf",
            "image/jpeg",
            "image/png",
            "image/jpg",
        ];

        if (!allowedTypes.includes(file.type)) {
            invalidFiles.push(
                `${file.name}: Invalid format (PDF, JPEG, PNG only)`
            );
            return;
        }

        validFiles.push(file);
    });

    // Show validation results
    if (invalidFiles.length > 0) {
        showNotification(
            "error",
            `Some files were rejected:\n${invalidFiles.join("\n")}`
        );
    }

    if (validFiles.length > 0) {
        ownershipDocuments.value.push(...validFiles);
        form.ownership_documents = [...ownershipDocuments.value];
        showNotification(
            "success",
            `${validFiles.length} ownership document(s) uploaded successfully`
        );
    }
};

const removeOwnershipDocument = (index) => {
    const removedFile = ownershipDocuments.value[index];
    ownershipDocuments.value.splice(index, 1);
    form.ownership_documents = [...ownershipDocuments.value];
    showNotification("info", `Removed ${removedFile.name}`);
};

// Enhanced notification system
const showNotification = (type, message) => {
    // Create a notification element if it doesn't exist
    let notificationContainer = document.getElementById(
        "notification-container"
    );
    if (!notificationContainer) {
        notificationContainer = document.createElement("div");
        notificationContainer.id = "notification-container";
        notificationContainer.className = "fixed top-4 right-4 z-50 space-y-2";
        document.body.appendChild(notificationContainer);
    }

    const notification = document.createElement("div");
    const typeClasses = {
        success: "bg-green-500 text-white",
        error: "bg-red-500 text-white",
        warning: "bg-yellow-500 text-black",
        info: "bg-blue-500 text-white",
    };

    notification.className = `${typeClasses[type]} px-4 py-2 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full opacity-0`;

    // Create notification content safely to prevent XSS
    const notificationContent = document.createElement("div");
    notificationContent.className = "flex items-center justify-between";

    const messageSpan = document.createElement("span");
    messageSpan.className = "text-sm";
    messageSpan.textContent = message; // Use textContent to prevent XSS

    const closeButton = document.createElement("button");
    closeButton.className = "ml-2 text-lg font-bold";
    closeButton.textContent = "×";
    closeButton.addEventListener("click", () => {
        notification.remove();
    });

    notificationContent.appendChild(messageSpan);
    notificationContent.appendChild(closeButton);
    notification.appendChild(notificationContent);

    notificationContainer.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove("translate-x-full", "opacity-0");
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add("translate-x-full", "opacity-0");
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const toggleFeature = (feature) => {
    const index = form.features.indexOf(feature);
    if (index > -1) {
        form.features.splice(index, 1);
        showNotification("info", `Removed feature: ${feature}`);
    } else {
        if (form.features.length < 20) {
            form.features.push(feature);
            showNotification("success", `Added feature: ${feature}`);
        } else {
            showNotification("error", "You can select maximum 20 features");
        }
    }
};

const formatPrice = (value) => {
    if (!value) return "";
    return new Intl.NumberFormat("en-PH").format(value);
};

// Enhanced draft saving with better error handling - silent mode for better UX
const saveDraft = async (showNotificationFlag = false) => {
    try {
        const draftData = {
            ...validationForm,
            currentStep: currentStep.value,
            timestamp: new Date().toISOString(),
            imageCount: imageFiles.value.length,
            documentCount:
                propertyDocuments.value.length +
                ownershipDocuments.value.length,
        };

        localStorage.setItem("seller_request_draft", JSON.stringify(draftData));

        // No notifications for draft saves to avoid confusing users
        // Draft saving happens silently in the background
    } catch (error) {
        console.error("Failed to save draft:", error);
        // Only show error notifications for critical failures
        showNotification(
            "warning",
            "Failed to save draft - please ensure you have enough storage space"
        );
    }
};

// Enhanced draft loading
const loadDraft = () => {
    try {
        const savedDraft = localStorage.getItem("seller_request_draft");
        if (savedDraft) {
            const draftData = JSON.parse(savedDraft);

            // Restore form data
            Object.keys(draftData).forEach((key) => {
                if (validationForm.hasOwnProperty(key)) {
                    validationForm[key] = draftData[key];
                }
            });

            // Restore current step
            if (draftData.currentStep) {
                currentStep.value = draftData.currentStep;
            }

            showNotification("success", "Previous draft loaded successfully");
            return true;
        }
    } catch (error) {
        console.error("Failed to load draft:", error);
        showNotification("error", "Failed to load saved draft");
    }
    return false;
};

// Clear draft after successful submission
const clearDraft = () => {
    try {
        localStorage.removeItem("seller_request_draft");
    } catch (error) {
        console.error("Failed to clear draft:", error);
    }
};

const submitForm = async () => {
    console.log("submitForm called - starting submission process");

    // CRITICAL: Prevent submission if not on final step
    if (currentStep.value !== totalSteps) {
        console.log("Not on final step:", currentStep.value, "of", totalSteps);
        submissionStatus.value = "error";
        submissionMessage.value =
            "You must complete all steps before submitting the form.";
        return false;
    }

    // CRITICAL: Comprehensive validation check - HALT on ANY failure
    const validationResults = {
        step1: isStep1Valid.value,
        step2: isStep2Valid.value,
        step3: isStep3Valid.value,
        canProceed: canProceed.value,
        hasErrors: !isValid.value,
    };

    console.log("Validation results:", validationResults);
    console.log("Current errors (raw):", errors.value);
    console.log("Current errors (keys):", Object.keys(errors.value));
    console.log("Current errors (stringified):", JSON.stringify(errors.value));
    console.log("isValid from composable:", isValid.value);

    // Check if there are any errors in the errors object
    if (Object.keys(errors.value).length > 0) {
        console.log("FOUND ERRORS IN VALIDATION:");
        Object.keys(errors.value).forEach((key) => {
            console.log(`Error key: ${key}, value:`, errors.value[key]);
            if (key === "error") {
                console.log('CRITICAL: Found "error" key in errors object!');
                console.log("Error array content:", errors.value[key]);
                console.trace("Stack trace for error key:");
            }
        });

        // If there's an 'error' key, we need to stop submission
        if (errors.value.error) {
            console.log("Stopping submission due to error key");
            submissionStatus.value = "error";
            submissionMessage.value =
                "Validation errors found. Please check the form.";
            return false;
        }
    }

    // Skip the hasErrors check since we're using isValid instead
    // The individual step validations are already comprehensive

    // HALT EXECUTION: Check each validation condition
    if (!validationResults.step1) {
        console.log("Step 1 validation failed");
        submissionStatus.value = "error";
        submissionMessage.value =
            "Step 1 contains validation errors. Please review and fix all required fields.";
        currentStep.value = 1; // Navigate back to failed step
        return false;
    }

    if (!validationResults.step2) {
        console.log("Step 2 validation failed");
        submissionStatus.value = "error";
        submissionMessage.value =
            "Step 2 contains validation errors. Please review and fix all required fields.";
        currentStep.value = 2; // Navigate back to failed step
        return false;
    }

    if (!validationResults.step3) {
        console.log("Step 3 validation failed");
        submissionStatus.value = "error";
        submissionMessage.value =
            "Step 3 contains validation errors. Please review and fix all required fields.";
        currentStep.value = 3; // Navigate back to failed step
        return false;
    }

    if (!validationResults.canProceed) {
        console.log("canProceed is false");
        submissionStatus.value = "error";
        submissionMessage.value =
            "Form validation failed. Please check all fields and try again.";
        return false;
    }

    // Skip the hasErrors check - we've already validated each step individually
    console.log("All validations passed, proceeding with submission");

    // CRITICAL: Pre-submission validation with progress tracking
    isSubmitting.value = true;
    submissionStatus.value = null;
    submissionMessage.value = "";

    // Show progress notification
    showNotification("info", "Preparing your submission...");

    try {
        // Validate entire form first - HALT on failure
        const validation = await validateForm();

        if (!validation.isValid) {
            submissionStatus.value = "error";
            submissionMessage.value =
                "Form validation failed. Please fix the following errors:";
            isSubmitting.value = false;

            // Show specific validation errors
            if (
                validation.errors &&
                Object.keys(validation.errors).length > 0
            ) {
                const errorMessages = Object.values(validation.errors).flat();
                submissionMessage.value += "\n• " + errorMessages.join("\n• ");
            }
            return false;
        }

        // CRITICAL: Check for required fields - FIXED: Updated field names
        const missingFields = requiredFields.filter(
            (field) =>
                !validationForm[field] ||
                validationForm[field].toString().trim() === ""
        );

        if (missingFields.length > 0) {
            submissionStatus.value = "error";
            submissionMessage.value = `Missing required fields: ${missingFields.join(
                ", "
            )}. Please complete all required information.`;
            isSubmitting.value = false;
            return false;
        }

        // CRITICAL: Validate file uploads
        if (imageFiles.value.length === 0) {
            submissionStatus.value = "error";
            submissionMessage.value =
                "At least one property image is required. Please upload images before submitting.";
            isSubmitting.value = false;
            return false;
        }

        // Try auto-fixing common errors - but don't proceed if fixes are needed
        const fixes = autoFixErrors();
        if (Object.keys(fixes).length > 0) {
            submissionStatus.value = "warning";
            submissionMessage.value =
                "Auto-fixed some formatting issues. Please review the changes and submit again.";
            isSubmitting.value = false;
            return false;
        }

        // Prepare form data for submission
        const submissionData = { ...validationForm };

        // Add image files to form data
        if (imageFiles.value.length > 0) {
            submissionData.uploaded_images = imageFiles.value;
        }

        // Add property documents if any
        if (propertyDocuments.value.length > 0) {
            submissionData.property_documents = propertyDocuments.value;
        }

        // Add ownership documents if any
        if (ownershipDocuments.value.length > 0) {
            submissionData.ownership_documents = ownershipDocuments.value;
        }

        // Update the form with the submission data
        Object.keys(submissionData).forEach((key) => {
            form[key] = submissionData[key];
        });

        // Show submission progress
        showNotification("info", "Submitting your property request...");

        // CRITICAL: Use form.post with enhanced error handling
        form.post(route("seller-requests.store"), {
            onStart: () => {
                submissionStatus.value = "submitting";
                submissionMessage.value = "Submitting your property request...";
            },
            onSuccess: (response) => {
                submissionStatus.value = "success";
                submissionMessage.value =
                    "Your property has been submitted successfully! We will review it within 24-48 hours and send you a confirmation email.";

                // Clear the draft and form
                clearDraft();

                // Show success notification
                showNotification(
                    "success",
                    "Property submitted successfully! Check your email for confirmation."
                );

                // Scroll to top to show success message
                window.scrollTo({ top: 0, behavior: "smooth" });

                // Reset form after successful submission
                setTimeout(() => {
                    // Reset to step 1
                    currentStep.value = 1;

                    // Clear all form data
                    Object.keys(validationForm).forEach((key) => {
                        if (typeof validationForm[key] === "boolean") {
                            validationForm[key] = false;
                        } else if (Array.isArray(validationForm[key])) {
                            validationForm[key] = [];
                        } else {
                            validationForm[key] = "";
                        }
                    });

                    // Reset file uploads
                    imageFiles.value = [];
                    propertyDocuments.value = [];
                    ownershipDocuments.value = [];

                    // Reset form object
                    form.reset();
                }, 3000);
            },
            onError: (errors) => {
                console.log("=== FORM SUBMISSION ERROR DETAILS ===");
                console.log("Raw errors object:", errors);
                console.log("Error keys:", Object.keys(errors));
                console.log("Error values:", Object.values(errors));
                console.log(
                    "Stringified errors:",
                    JSON.stringify(errors, null, 2)
                );

                submissionStatus.value = "error";

                // Enhanced error handling with user-friendly messages
                if (errors && Object.keys(errors).length > 0) {
                    const errorMessages = [];
                    const fieldErrorMap = {};

                    Object.entries(errors).forEach(([field, messages]) => {
                        console.log(
                            `Processing error for field '${field}':`,
                            messages
                        );

                        const fieldMessages = Array.isArray(messages)
                            ? messages
                            : [messages];
                        fieldErrorMap[field] = fieldMessages;

                        fieldMessages.forEach((message) => {
                            // Make error messages more user-friendly
                            const friendlyMessage = message
                                .replace(/The\s+/i, "")
                                .replace(/field\s+/i, "")
                                .replace(/_/g, " ")
                                .replace(/\b\w/g, (l) => l.toUpperCase());
                            errorMessages.push(friendlyMessage);
                        });
                    });

                    console.log("Processed field error map:", fieldErrorMap);
                    console.log("User-friendly error messages:", errorMessages);

                    // Set server errors for form validation
                    setServerErrors(fieldErrorMap);

                    submissionMessage.value = `Please fix the following errors:\n• ${errorMessages.join(
                        "\n• "
                    )}`;

                    // Show error notification
                    showNotification(
                        "error",
                        `Submission failed: ${errorMessages.length} error(s) found`
                    );

                    // Navigate to the step with the first error
                    const firstErrorField = Object.keys(errors)[0];
                    if (firstErrorField) {
                        const step1Fields = [
                            "name",
                            "email",
                            "phone",
                            "address",
                        ];
                        const step2Fields = [
                            "property_title",
                            "property_description",
                            "asking_price",
                        ];
                        const step3Fields = ["municipality", "address"];
                        const step5Fields = [
                            "uploaded_images",
                            "terms_accepted",
                            "marketing_consent",
                            "newsletter_consent",
                        ];

                        if (step1Fields.includes(firstErrorField)) {
                            currentStep.value = 1;
                        } else if (step2Fields.includes(firstErrorField)) {
                            currentStep.value = 2;
                        } else if (step3Fields.includes(firstErrorField)) {
                            currentStep.value = 3;
                        } else if (step5Fields.includes(firstErrorField)) {
                            currentStep.value = 5;
                        }

                        // Scroll to the error field
                        setTimeout(() => {
                            const fieldElement = document.querySelector(
                                `[name="${firstErrorField}"], #${firstErrorField}`
                            );
                            if (fieldElement) {
                                fieldElement.scrollIntoView({
                                    behavior: "smooth",
                                    block: "center",
                                });
                                fieldElement.focus();
                            }
                        }, 300);
                    }
                } else {
                    submissionMessage.value =
                        "An unexpected error occurred. Please try again or contact support if the problem persists.";
                    showNotification(
                        "error",
                        "Submission failed due to an unexpected error"
                    );
                }
            },
            onFinish: () => {
                isSubmitting.value = false;
                showNotification("info", "Submission process completed");
            },
        });
    } catch (error) {
        submissionStatus.value = "error";

        // Enhanced error handling for different error types
        let errorMessage = "An unexpected error occurred. Please try again.";

        if (
            error.name === "NetworkError" ||
            error.message.includes("network")
        ) {
            errorMessage =
                "Network error: Please check your internet connection and try again.";
        } else if (error.name === "ValidationError") {
            errorMessage =
                "Validation error: Please check your form data and try again.";
        } else if (error.response) {
            if (error.response.status === 422) {
                errorMessage =
                    "Validation failed: Please check your form data.";
            } else if (error.response.status === 413) {
                errorMessage =
                    "Files too large: Please reduce file sizes and try again.";
            } else if (error.response.status === 500) {
                errorMessage =
                    "Server error: Please try again later or contact support.";
            } else if (error.response.data && error.response.data.message) {
                errorMessage = `Server error: ${error.response.data.message}`;
            }
        } else if (error.message) {
            errorMessage = `Error: ${error.message}`;
        }

        submissionMessage.value = errorMessage;
        showNotification("error", errorMessage);

        isSubmitting.value = false;

        console.error("Form submission error:", error);
    }
};

// Enhanced mounted lifecycle with optimized auto-save
onMounted(() => {
    // Load draft if available (notification is handled inside loadDraft function)
    const hasDraft = loadDraft();

    // Optimized auto-save with debouncing and change detection
    let lastSavedData = JSON.stringify(validationForm);
    let autoSaveTimeout = null;
    let isInitialLoad = true; // Flag to prevent auto-save on initial load

    const debouncedAutoSave = () => {
        // Skip auto-save during initial load
        if (isInitialLoad) {
            isInitialLoad = false;
            return;
        }

        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            const currentData = JSON.stringify(validationForm);
            // Only save if data has actually changed and form has meaningful content
            if (
                currentData !== lastSavedData &&
                Object.values(validationForm).some(
                    (value) => value && value.toString().trim() !== ""
                )
            ) {
                saveDraft(false); // Silent save - no notification
                lastSavedData = currentData;
            }
        }, 5000); // Increased debounce to 5 seconds for better UX
    };

    // Watch for form changes and trigger debounced auto-save
    const stopWatching = watch(validationForm, debouncedAutoSave, {
        deep: true,
    });

    // Fallback auto-save every 5 minutes (silent saves)
    const autoSaveInterval = setInterval(() => {
        const currentData = JSON.stringify(validationForm);
        if (
            currentData !== lastSavedData &&
            Object.values(validationForm).some(
                (value) => value && value.toString().trim() !== ""
            )
        ) {
            saveDraft(false); // Silent save - no notification
            lastSavedData = currentData;
        }
    }, 300000); // 5 minutes

    // Add beforeunload event to warn about unsaved changes
    const handleBeforeUnload = (event) => {
        if (
            Object.values(validationForm).some(
                (value) => value && value.toString().trim() !== ""
            )
        ) {
            event.preventDefault();
            event.returnValue =
                "You have unsaved changes. Are you sure you want to leave?";
            return event.returnValue;
        }
    };

    window.addEventListener("beforeunload", handleBeforeUnload);

    // Cleanup on unmount
    onUnmounted(() => {
        clearInterval(autoSaveInterval);
        clearTimeout(autoSaveTimeout);
        stopWatching();
        window.removeEventListener("beforeunload", handleBeforeUnload);
    });
});

// Error handling methods
const clearErrors = () => {
    errors.value = {};
    submissionStatus.value = null;
    submissionMessage.value = "";
};

const validateAllSteps = async () => {
    // Clear previous status
    submissionStatus.value = null;
    submissionMessage.value = "";

    // Validate each step
    const step1Valid = await validateStep(1);
    const step2Valid = await validateStep(2);
    const step3Valid = await validateStep(3);

    if (step1Valid && step2Valid && step3Valid) {
        submissionStatus.value = "success";
        submissionMessage.value =
            "All form validation passed! You can now submit your request.";
    } else {
        submissionStatus.value = "error";
        submissionMessage.value =
            "Some validation issues were found. Please check the form status above.";
    }
};

// Helper function to format field names for display
const formatFieldName = (field) => {
    const fieldMap = {
        name: "Full Name",
        email: "Email Address",
        phone: "Phone Number",
        address: "Complete Address",
        property_title: "Property Title",
        property_description: "Property Description",
        asking_price: "Asking Price",
        uploaded_images: "Property Images",
        municipality: "Municipality",
        barangay: "Barangay",
        lot_area: "Lot Area",
        title_type: "Title Type",
        zoning_classification: "Zoning Classification",
        property_type: "Property Type",
        terms_accepted: "Terms Accepted",
        additional_notes: "Additional Notes",
        marketing_consent: "Marketing Consent",
        newsletter_consent: "Newsletter Consent",
    };
    return (
        fieldMap[field] ||
        field.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase())
    );
};

// Define required fields at component level for reuse
const requiredFields = [
    "name",
    "email",
    "phone",
    "address",
    "property_title",
    "property_description",
    "property_type",
    "asking_price",
    "lot_area",
    "municipality",
    "terms_accepted",
];

// Enhanced field validation with specific error messages
const validateField = (field, value) => {
    const errors = [];

    // Check if field is required
    const isRequired = requiredFields.includes(field);

    if (isRequired && (!value || value.toString().trim() === "")) {
        return {
            isValid: false,
            errors: [`${getFieldLabel(field)} is required`],
        };
    }

    // Skip validation for empty optional fields
    if (!value || value.toString().trim() === "") {
        return { isValid: true, errors: [] };
    }

    // Field-specific validation with detailed error messages
    switch (field) {
        case "email":
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                errors.push(
                    "Please enter a valid email address (e.g., user@example.com)"
                );
            }
            break;

        case "phone":
            const phoneRegex = /^(\+63|0)[0-9]{10}$/;
            if (!phoneRegex.test(value.replace(/\s+/g, ""))) {
                errors.push(
                    "Please enter a valid Philippine phone number (e.g., +63 912 345 6789)"
                );
            }
            break;

        case "name":
            if (value.length < 2) {
                errors.push("Name must be at least 2 characters long");
            }
            if (!/^[a-zA-Z\s\-\.]+$/.test(value)) {
                errors.push(
                    "Name can only contain letters, spaces, hyphens, and periods"
                );
            }
            break;

        case "address":
            if (value.length < 10) {
                errors.push(
                    "Please provide a complete address (at least 10 characters)"
                );
            }
            break;

        case "property_title":
            if (value.length < 5) {
                errors.push(
                    "Property title must be at least 5 characters long"
                );
            }
            if (value.length > 100) {
                errors.push("Property title cannot exceed 100 characters");
            }
            break;

        case "property_description":
            if (value.length < 20) {
                errors.push(
                    "Property description must be at least 20 characters long"
                );
            }
            if (value.length > 2000) {
                errors.push(
                    "Property description cannot exceed 2000 characters"
                );
            }
            break;

        case "asking_price":
        case "price":
            const priceNum = parseFloat(value);
            if (isNaN(priceNum) || priceNum <= 0) {
                errors.push("Please enter a valid price greater than 0");
            }
            if (priceNum < 50000) {
                errors.push("Minimum price is ₱50,000");
            }
            if (priceNum > 1000000000) {
                errors.push("Maximum price is ₱1,000,000,000");
            }
            break;

        case "lot_area":
            const areaNum = parseFloat(value);
            if (isNaN(areaNum) || areaNum <= 0) {
                errors.push("Please enter a valid lot area greater than 0");
            }
            if (areaNum < 50) {
                errors.push("Minimum lot area is 50 square meters");
            }
            if (areaNum > 1000000) {
                errors.push("Maximum lot area is 1,000,000 square meters");
            }
            break;

        case "floor_area":
            if (value) {
                const floorAreaNum = parseFloat(value);
                if (isNaN(floorAreaNum) || floorAreaNum <= 0) {
                    errors.push(
                        "Please enter a valid floor area greater than 0"
                    );
                }
                if (floorAreaNum > parseFloat(validationForm.lot_area || 0)) {
                    errors.push("Floor area cannot be larger than lot area");
                }
            }
            break;

        case "bedrooms":
        case "bathrooms":
            if (value) {
                const num = parseInt(value);
                if (isNaN(num) || num < 0) {
                    errors.push(`Please enter a valid number of ${field}`);
                }
                if (num > 50) {
                    errors.push(`Maximum number of ${field} is 50`);
                }
            }
            break;

        case "parking_spaces":
            if (value) {
                const num = parseInt(value);
                if (isNaN(num) || num < 0) {
                    errors.push(
                        "Please enter a valid number of parking spaces"
                    );
                }
                if (num > 20) {
                    errors.push("Maximum number of parking spaces is 20");
                }
            }
            break;

        case "city":
            if (value.length < 2) {
                errors.push("City name must be at least 2 characters long");
            }
            if (!/^[a-zA-Z\s\-\.]+$/.test(value)) {
                errors.push(
                    "City name can only contain letters, spaces, hyphens, and periods"
                );
            }
            break;

        case "postal_code":
            if (!/^[0-9]{4}$/.test(value)) {
                errors.push("Postal code must be exactly 4 digits");
            }
            break;
    }

    return {
        isValid: errors.length === 0,
        errors: errors,
    };
};

// Enhanced field value setter with immediate validation
const enhancedSetFieldValue = (field, value) => {
    // Use the original setFieldValue from useFormValidation
    setFieldValue(field, value);

    // Mark field as touched immediately when user starts typing
    setFieldTouched(field, true);

    // Trigger immediate validation for better UX
    const validation = validateField(field, value);
    if (validation.isValid) {
        clearFieldError(field);
    } else {
        setFieldError(field, validation.errors[0]);
    }
};

// Enhanced blur handler for immediate validation feedback with better error handling
const handleFieldBlur = (field) => {
    setFieldTouched(field, true);

    // Get current field value
    const value = validationForm[field];

    // Validate field immediately on blur using our enhanced validation
    const validation = validateField(field, value);

    if (!validation.isValid && validation.errors.length > 0) {
        // Set the first error message
        setFieldError(field, validation.errors[0]);

        // For required fields that are empty, show additional feedback
        if (
            requiredFields.includes(field) &&
            (!value || value.toString().trim() === "")
        ) {
            showNotification(
                "error",
                `${getFieldLabel(field)} is required`,
                3000
            );
        }
    } else {
        // Clear any existing errors for this field
        clearFieldError(field);
    }

    // Also run the composable validation for consistency
    validateFieldComposable(field, value);
};

// Enhanced focus handler to clear errors when user starts editing
const handleFieldFocus = (field) => {
    // Don't clear errors immediately, but prepare for real-time validation
    setFieldTouched(field, true);
};

// Enhanced error handling methods
const handleFieldSuggestion = (fieldName, suggestion) => {
    enhancedSetFieldValue(fieldName, suggestion);
    // Re-validate the field after applying suggestion
    validateField(fieldName, suggestion);
};

const handleFieldQuickAction = (fieldName, action) => {
    if (action.type === "clear") {
        enhancedSetFieldValue(fieldName, "");
    } else if (action.type === "format" && action.value) {
        enhancedSetFieldValue(fieldName, action.value);
    }
    // Re-validate the field after quick action
    validateField(fieldName, validationForm[fieldName]);
};
</script>

<template>
    <Head title="Sell Your Property - GeoCasa Bohol" />

    <!-- Public Navigation -->
    <PublicNavigation :auth="auth" current-route="seller-requests.create" />

    <!-- Main Content -->
    <main class="min-h-screen bg-gradient-to-br from-neutral-50 to-neutral-100">
        <!-- Hero Section (Full Width) -->
        <section class="relative py-16 lg:py-20 overflow-hidden mb-12">
            <div
                class="absolute inset-0 bg-gradient-to-r from-primary-600/10 to-accent-600/10"
            ></div>
            <div
                class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center"
            >
                <h1
                    class="text-3xl md:text-5xl lg:text-6xl font-bold text-neutral-900 mb-6"
                >
                    Sell Your
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-accent-600"
                    >
                        Property
                    </span>
                    in Bohol
                </h1>
                <p
                    class="text-lg md:text-xl text-neutral-600 max-w-3xl mx-auto"
                >
                    List your property with GeoCasa Bohol and reach qualified
                    buyers
                </p>
            </div>
        </section>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <!-- Enhanced Submission Status -->
            <div v-if="submissionStatus" class="mb-8">
                <!-- Success Message -->
                <div
                    v-if="submissionStatus === 'success'"
                    class="p-6 rounded-2xl border bg-green-50 border-green-200"
                >
                    <div class="flex items-start gap-4">
                        <CheckCircleIcon
                            class="w-8 h-8 text-green-600 flex-shrink-0 mt-1"
                        />
                        <div class="flex-1">
                            <h3
                                class="text-lg font-semibold text-green-800 mb-2"
                            >
                                Success!
                            </h3>
                            <p class="text-green-700 whitespace-pre-line">
                                {{ submissionMessage }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div
                    v-else-if="submissionStatus === 'error'"
                    class="p-6 rounded-2xl border bg-red-50 border-red-200"
                >
                    <div class="flex items-start gap-4">
                        <ExclamationTriangleIcon
                            class="w-8 h-8 text-red-600 flex-shrink-0 mt-1"
                        />
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-red-800 mb-2">
                                Submission Failed
                            </h3>
                            <p class="text-red-700 whitespace-pre-line mb-4">
                                {{ submissionMessage }}
                            </p>

                            <!-- Detailed Error Information -->
                            <div
                                v-if="Object.keys(errors).length > 0"
                                class="bg-red-100 rounded-lg p-4 mb-4"
                            >
                                <h4
                                    class="text-sm font-semibold text-red-800 mb-3"
                                >
                                    Specific Issues Found:
                                </h4>
                                <ul class="space-y-2">
                                    <li
                                        v-for="(errorMessages, field) in errors"
                                        :key="field"
                                        class="flex items-start gap-2 text-sm text-red-700"
                                    >
                                        <span
                                            class="font-medium capitalize min-w-0 flex-shrink-0"
                                        >
                                            {{ formatFieldName(field) }}:
                                        </span>
                                        <span class="flex-1">
                                            <span
                                                v-if="
                                                    Array.isArray(errorMessages)
                                                "
                                                >{{
                                                    errorMessages.join(", ")
                                                }}</span
                                            >
                                            <span v-else>{{
                                                errorMessages
                                            }}</span>
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Action Buttons for Error Recovery -->
                            <div class="flex flex-wrap gap-3">
                                <button
                                    @click="clearErrors"
                                    class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-800 text-sm font-medium rounded-lg transition-colors"
                                >
                                    Clear Errors
                                </button>
                                <button
                                    @click="validateAllSteps"
                                    class="px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm font-medium rounded-lg transition-colors"
                                >
                                    Re-validate Form
                                </button>
                                <button
                                    @click="autoFixErrors"
                                    class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-sm font-medium rounded-lg transition-colors"
                                >
                                    Auto-fix Issues
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning Message -->
                <div
                    v-else-if="submissionStatus === 'warning'"
                    class="p-6 rounded-2xl border bg-yellow-50 border-yellow-200"
                >
                    <div class="flex items-start gap-4">
                        <ExclamationTriangleIcon
                            class="w-8 h-8 text-yellow-600 flex-shrink-0 mt-1"
                        />
                        <div class="flex-1">
                            <h3
                                class="text-lg font-semibold text-yellow-800 mb-2"
                            >
                                Warning
                            </h3>
                            <p class="text-yellow-700 whitespace-pre-line">
                                {{ submissionMessage }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validation Summary -->
            <ValidationSummary
                v-if="hasErrors && submissionStatus === 'error'"
                :errors="errors"
                :format-field-name="formatFieldName"
                @auto-fix="autoFixErrors"
                class="mb-8"
            />

            <!-- Benefits Banner -->
            <div
                class="bg-white border border-neutral-200 p-8 mb-12 rounded-xl shadow-sm"
            >
                <div class="grid md:grid-cols-3 gap-8 text-center">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-14 h-14 bg-primary-100 rounded-xl flex items-center justify-center mb-4"
                        >
                            <ShieldCheckIcon class="w-7 h-7 text-primary-600" />
                        </div>
                        <h3 class="text-lg font-bold mb-2 text-neutral-900">
                            Licensed Brokers
                        </h3>
                        <p class="text-sm text-neutral-600">
                            Work with verified professionals
                        </p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div
                            class="w-14 h-14 bg-accent-100 rounded-xl flex items-center justify-center mb-4"
                        >
                            <ClockIcon class="w-7 h-7 text-accent-600" />
                        </div>
                        <h3 class="text-lg font-bold mb-2 text-neutral-900">
                            Quick Process
                        </h3>
                        <p class="text-sm text-neutral-600">
                            Listed within 24-48 hours
                        </p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div
                            class="w-14 h-14 bg-primary-100 rounded-xl flex items-center justify-center mb-4"
                        >
                            <StarIcon class="w-7 h-7 text-primary-600" />
                        </div>
                        <h3 class="text-lg font-bold mb-2 text-neutral-900">
                            Premium Exposure
                        </h3>
                        <p class="text-sm text-neutral-600">
                            Maximum visibility to buyers
                        </p>
                    </div>
                </div>
            </div>

            <!-- Progress Indicator -->
            <div class="mb-12">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-neutral-600"
                        >Step {{ currentStep }} of {{ totalSteps }}</span
                    >
                    <span class="text-sm text-neutral-500"
                        >{{ Math.round((currentStep / totalSteps) * 100) }}%
                        Complete</span
                    >
                </div>
                <div class="w-full bg-neutral-200 rounded-full h-2 mb-8">
                    <div
                        class="bg-gradient-to-r from-primary-500 to-accent-500 h-2 rounded-full transition-all duration-500 ease-out"
                        :style="{
                            width: `${(currentStep / totalSteps) * 100}%`,
                        }"
                    ></div>
                </div>

                <!-- Step Icons -->
                <div class="flex justify-between items-center">
                    <div
                        v-for="step in totalSteps"
                        :key="step"
                        class="flex flex-col items-center"
                    >
                        <div
                            :class="[
                                'w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-300',
                                step < currentStep
                                    ? 'bg-green-500 text-white shadow-lg'
                                    : step === currentStep
                                    ? getStepValidationClass(step)
                                    : 'bg-neutral-200 text-neutral-400',
                            ]"
                        >
                            <CheckIcon
                                v-if="step < currentStep"
                                class="w-5 h-5"
                            />
                            <ExclamationTriangleIcon
                                v-else-if="
                                    step === currentStep &&
                                    submissionStatus === 'error'
                                "
                                class="w-5 h-5"
                            />
                            <component
                                v-else
                                :is="getStepIcon(step)"
                                class="w-5 h-5"
                            />
                        </div>
                        <span
                            :class="[
                                'text-xs mt-2 font-medium transition-colors',
                                step <= currentStep
                                    ? 'text-primary-600'
                                    : 'text-neutral-400',
                            ]"
                        >
                            {{
                                ["Contact", "Property", "Location", "Images"][
                                    step - 1
                                ]
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <form @submit.prevent="submitForm" @input="saveDraft">
                    <!-- Step 1: Contact Information -->
                    <div v-if="currentStep === 1" class="form-section">
                        <div class="form-section-header">
                            <UserIcon class="form-section-icon" />
                            <h2 class="form-section-title">
                                Contact Information
                            </h2>
                        </div>

                        <div class="form-grid-2">
                            <FormField
                                id="name"
                                v-model="validationForm.name"
                                label="Full Name"
                                type="text"
                                placeholder="Enter your full name"
                                :error="errors.name"
                                :required="true"
                                help-text="This will be used to contact you about your property"
                                @update:model-value="
                                    enhancedSetFieldValue('name', $event)
                                "
                                @blur="handleFieldBlur('name')"
                                @focus="handleFieldFocus('name')"
                                @apply-suggestion="
                                    handleFieldSuggestion('name', $event)
                                "
                                @quick-action="
                                    handleFieldQuickAction('name', $event)
                                "
                            />

                            <FormField
                                id="email"
                                v-model="validationForm.email"
                                label="Email Address"
                                type="email"
                                placeholder="your.email@example.com"
                                :error="errors.email"
                                :required="true"
                                help-text="We'll send updates about your property listing here"
                                @update:model-value="
                                    enhancedSetFieldValue('email', $event)
                                "
                                @blur="handleFieldBlur('email')"
                                @focus="handleFieldFocus('email')"
                                @apply-suggestion="
                                    handleFieldSuggestion('email', $event)
                                "
                                @quick-action="
                                    handleFieldQuickAction('email', $event)
                                "
                            />

                            <FormField
                                id="phone"
                                v-model="validationForm.phone"
                                label="Phone Number"
                                type="tel"
                                placeholder="+63 XXX XXX XXXX"
                                :error="errors.phone"
                                :required="true"
                                help-text="For quick communication about your property"
                                @update:model-value="
                                    enhancedSetFieldValue('phone', $event)
                                "
                                @blur="handleFieldBlur('phone')"
                                @focus="handleFieldFocus('phone')"
                                @apply-suggestion="
                                    handleFieldSuggestion('phone', $event)
                                "
                                @quick-action="
                                    handleFieldQuickAction('phone', $event)
                                "
                            />

                            <FormField
                                id="address"
                                v-model="validationForm.address"
                                label="Current Address"
                                type="text"
                                placeholder="Your current address"
                                :error="errors.address"
                                :required="true"
                                help-text="Your current residential address"
                                @update:model-value="
                                    enhancedSetFieldValue('address', $event)
                                "
                                @blur="handleFieldBlur('address')"
                                @focus="handleFieldFocus('address')"
                            />
                        </div>
                    </div>

                    <!-- Step 2: Property Details -->
                    <div v-if="currentStep === 2" class="form-section">
                        <div class="form-section-header">
                            <DocumentTextIcon class="form-section-icon" />
                            <h2 class="form-section-title">Property Details</h2>
                        </div>

                        <div class="space-y-6">
                            <FormField
                                id="property_title"
                                v-model="validationForm.property_title"
                                label="Property Title"
                                type="text"
                                placeholder="e.g., Beautiful 3-Bedroom House in Tagbilaran"
                                :error="errors.property_title"
                                :required="true"
                                help-text="Create an attractive title that highlights your property's best features"
                                :show-character-count="true"
                                :max-length="200"
                                @update:model-value="
                                    enhancedSetFieldValue(
                                        'property_title',
                                        $event
                                    )
                                "
                                @blur="handleFieldBlur('property_title')"
                                @focus="handleFieldFocus('property_title')"
                            />

                            <FormField
                                id="property_description"
                                v-model="validationForm.property_description"
                                label="Property Description"
                                type="textarea"
                                placeholder="Describe your property in detail... Include features, condition, nearby amenities, etc."
                                :error="errors.property_description"
                                :required="true"
                                help-text="Provide a detailed description to attract potential buyers"
                                :show-character-count="true"
                                :max-length="2000"
                                :rows="5"
                                @update:model-value="
                                    enhancedSetFieldValue(
                                        'property_description',
                                        $event
                                    )
                                "
                                @blur="handleFieldBlur('property_description')"
                                @focus="
                                    handleFieldFocus('property_description')
                                "
                            />

                            <FormField
                                id="property_type"
                                v-model="validationForm.property_type"
                                label="Property Type"
                                type="select"
                                :error="errors.property_type"
                                :required="true"
                                help-text="Select the type of land property you're selling"
                                @update:model-value="
                                    enhancedSetFieldValue(
                                        'property_type',
                                        $event
                                    )
                                "
                                @blur="handleFieldBlur('property_type')"
                                @focus="handleFieldFocus('property_type')"
                            >
                                <option value="">Select Property Type</option>
                                <option value="residential_lot">
                                    Residential Lot
                                </option>
                                <option value="agricultural_land">
                                    Agricultural Land
                                </option>
                                <option value="commercial_lot">
                                    Commercial Lot
                                </option>
                                <option value="industrial_lot">
                                    Industrial Lot
                                </option>
                                <option value="beachfront">Beachfront</option>
                                <option value="mountain_view">
                                    Mountain View
                                </option>
                                <option value="rice_field">Rice Field</option>
                                <option value="coconut_plantation">
                                    Coconut Plantation
                                </option>
                                <option value="subdivision_lot">
                                    Subdivision Lot
                                </option>
                            </FormField>

                            <div class="form-grid-3">
                                <FormField
                                    id="asking_price"
                                    v-model="validationForm.asking_price"
                                    label="Asking Price (₱)"
                                    type="number"
                                    placeholder="5000000"
                                    :error="errors.asking_price"
                                    :required="true"
                                    help-text="Enter your desired selling price"
                                    @update:model-value="
                                        enhancedSetFieldValue(
                                            'asking_price',
                                            $event
                                        )
                                    "
                                    @blur="handleFieldBlur('asking_price')"
                                    @focus="handleFieldFocus('asking_price')"
                                >
                                    <template
                                        #suffix
                                        v-if="validationForm.asking_price"
                                    >
                                        <div
                                            class="text-sm text-neutral-600 mt-1"
                                        >
                                            ₱{{
                                                formatPrice(
                                                    validationForm.asking_price
                                                )
                                            }}
                                        </div>
                                    </template>
                                </FormField>

                                <FormField
                                    id="lot_area"
                                    v-model="validationForm.lot_area"
                                    label="Lot Area (sqm)"
                                    type="number"
                                    placeholder="120"
                                    :error="errors.lot_area"
                                    help-text="Total lot area in square meters"
                                    @update:model-value="
                                        enhancedSetFieldValue(
                                            'lot_area',
                                            $event
                                        )
                                    "
                                    @blur="handleFieldBlur('lot_area')"
                                    @focus="handleFieldFocus('lot_area')"
                                />

                                <!-- Title Type -->
                                <FormField
                                    id="title_type"
                                    v-model="validationForm.title_type"
                                    label="Title Type"
                                    type="text"
                                    placeholder="e.g., Titled, Tax Declared, Mother Title"
                                    :error="errors.title_type"
                                    help-text="Enter the type of land title"
                                    @update:model-value="
                                        enhancedSetFieldValue(
                                            'title_type',
                                            $event
                                        )
                                    "
                                    @blur="handleFieldBlur('title_type')"
                                    @focus="handleFieldFocus('title_type')"
                                />

                                <!-- Zoning Classification -->
                                <FormField
                                    id="zoning_classification"
                                    v-model="
                                        validationForm.zoning_classification
                                    "
                                    label="Zoning Classification"
                                    type="text"
                                    placeholder="e.g., Residential, Agricultural, Commercial"
                                    :error="errors.zoning_classification"
                                    help-text="Enter the zoning classification of the property"
                                    @update:model-value="
                                        enhancedSetFieldValue(
                                            'zoning_classification',
                                            $event
                                        )
                                    "
                                    @blur="
                                        handleFieldBlur('zoning_classification')
                                    "
                                    @focus="
                                        handleFieldFocus(
                                            'zoning_classification'
                                        )
                                    "
                                />
                            </div>

                            <!-- Utilities & Access Section -->
                            <div class="mt-6">
                                <h3
                                    class="text-base font-semibold text-gray-900 mb-4"
                                >
                                    Utilities & Access
                                </h3>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                >
                                    <!-- Road Access -->
                                    <label
                                        class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="validationForm.road_access"
                                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        />
                                        <div class="flex-1">
                                            <span
                                                class="font-medium text-gray-900"
                                                >Road Access</span
                                            >
                                            <p class="text-sm text-gray-500">
                                                Property has road access
                                            </p>
                                        </div>
                                    </label>

                                    <!-- Water Source -->
                                    <label
                                        class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="
                                                validationForm.water_source
                                            "
                                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        />
                                        <div class="flex-1">
                                            <span
                                                class="font-medium text-gray-900"
                                                >Water Source</span
                                            >
                                            <p class="text-sm text-gray-500">
                                                Water source available
                                            </p>
                                        </div>
                                    </label>

                                    <!-- Electricity -->
                                    <label
                                        class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="
                                                validationForm.electricity_available
                                            "
                                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        />
                                        <div class="flex-1">
                                            <span
                                                class="font-medium text-gray-900"
                                                >Electricity</span
                                            >
                                            <p class="text-sm text-gray-500">
                                                Electricity available
                                            </p>
                                        </div>
                                    </label>

                                    <!-- Internet -->
                                    <label
                                        class="flex items-center space-x-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="
                                                validationForm.internet_available
                                            "
                                            class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        />
                                        <div class="flex-1">
                                            <span
                                                class="font-medium text-gray-900"
                                                >Internet</span
                                            >
                                            <p class="text-sm text-gray-500">
                                                Internet available
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Location Details -->
                    <div v-if="currentStep === 3" class="form-section">
                        <div class="form-section-header">
                            <MapPinIcon class="form-section-icon" />
                            <h2 class="form-section-title">Location Details</h2>
                        </div>

                        <div class="space-y-6">
                            <div class="form-grid-2">
                                <FormField
                                    id="municipality"
                                    v-model="validationForm.municipality"
                                    label="Municipality"
                                    type="text"
                                    placeholder="e.g., Tagbilaran City, Panglao, Dauis"
                                    :error="errors.municipality"
                                    :required="true"
                                    help-text="Enter the municipality where your property is located"
                                    @update:model-value="
                                        enhancedSetFieldValue(
                                            'municipality',
                                            $event
                                        )
                                    "
                                    @blur="handleFieldBlur('municipality')"
                                    @focus="handleFieldFocus('municipality')"
                                />

                                <FormField
                                    id="barangay"
                                    v-model="validationForm.barangay"
                                    label="Barangay"
                                    type="text"
                                    placeholder="e.g., Poblacion, Tawala"
                                    :error="errors.barangay"
                                    help-text="Enter the barangay (optional)"
                                    @update:model-value="
                                        enhancedSetFieldValue(
                                            'barangay',
                                            $event
                                        )
                                    "
                                    @blur="handleFieldBlur('barangay')"
                                    @focus="handleFieldFocus('barangay')"
                                />
                            </div>

                            <FormField
                                id="address"
                                v-model="validationForm.address"
                                label="Complete Address"
                                type="textarea"
                                placeholder="Enter the full address of your property"
                                :error="errors.address"
                                :required="true"
                                help-text="Provide detailed address information"
                                :rows="3"
                                @update:model-value="
                                    enhancedSetFieldValue('address', $event)
                                "
                                @blur="handleFieldBlur('address')"
                                @focus="handleFieldFocus('address')"
                            />
                        </div>
                    </div>

                    <!-- Step 4: Broker Selection -->
                    <div v-if="currentStep === 4" class="space-y-8">
                        <div class="flex items-center gap-3 mb-6">
                            <UserIcon class="w-6 h-6 text-primary-600" />
                            <h2 class="text-2xl font-bold text-neutral-900">
                                Choose Your Broker
                            </h2>
                        </div>

                        <!-- Broker Selection Method -->
                        <div
                            class="bg-white p-6 rounded-xl shadow-sm border border-neutral-200"
                        >
                            <h3
                                class="text-lg font-semibold mb-4 text-neutral-900"
                            >
                                How would you like to proceed?
                            </h3>

                            <div class="space-y-4">
                                <!-- Auto-assign (Recommended) -->
                                <label
                                    class="flex items-start p-5 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:bg-neutral-50"
                                    :class="{
                                        'border-primary-500 bg-primary-50 shadow-md':
                                            validationForm.broker_selection_method ===
                                            'auto',
                                        'border-neutral-200':
                                            validationForm.broker_selection_method !==
                                            'auto',
                                    }"
                                >
                                    <input
                                        type="radio"
                                        v-model="
                                            validationForm.broker_selection_method
                                        "
                                        value="auto"
                                        class="mt-1 text-primary-600 focus:ring-primary-500"
                                    />
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="font-semibold text-neutral-900"
                                                >Auto-assign (Recommended)</span
                                            >
                                            <span
                                                class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full"
                                            >
                                                Fastest
                                            </span>
                                        </div>
                                        <p
                                            class="text-sm text-neutral-600 mt-1"
                                        >
                                            We'll match you with the best
                                            available broker based on location,
                                            expertise, and current workload.
                                        </p>
                                        <div
                                            class="mt-3 flex items-center gap-4 text-sm"
                                        >
                                            <div
                                                class="flex items-center text-green-600"
                                            >
                                                <CheckCircleIcon
                                                    class="w-4 h-4 mr-1"
                                                />
                                                Immediate assignment
                                            </div>
                                            <div
                                                class="flex items-center text-green-600"
                                            >
                                                <CheckCircleIcon
                                                    class="w-4 h-4 mr-1"
                                                />
                                                Fair distribution
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                <!-- Manual selection -->
                                <label
                                    class="flex items-start p-5 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:bg-neutral-50"
                                    :class="{
                                        'border-primary-500 bg-primary-50 shadow-md':
                                            validationForm.broker_selection_method ===
                                            'manual',
                                        'border-neutral-200':
                                            validationForm.broker_selection_method !==
                                            'manual',
                                    }"
                                >
                                    <input
                                        type="radio"
                                        v-model="
                                            validationForm.broker_selection_method
                                        "
                                        value="manual"
                                        class="mt-1 text-primary-600 focus:ring-primary-500"
                                    />
                                    <div class="ml-4 flex-1">
                                        <div
                                            class="font-semibold text-neutral-900"
                                        >
                                            Choose Your Broker
                                        </div>
                                        <p
                                            class="text-sm text-neutral-600 mt-1"
                                        >
                                            Select from our verified brokers if
                                            you have a preference.
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Broker Selection (shown only if manual) -->
                        <div
                            v-if="
                                validationForm.broker_selection_method ===
                                'manual'
                            "
                            class="bg-white p-6 rounded-xl shadow-sm border border-neutral-200 animate-fadeIn"
                        >
                            <h3
                                class="text-lg font-semibold mb-4 text-neutral-900"
                            >
                                Select Your Preferred Broker
                            </h3>

                            <!-- Search/Filter -->
                            <div class="mb-6">
                                <input
                                    type="text"
                                    v-model="brokerSearch"
                                    placeholder="Search by name, location, or firm..."
                                    class="w-full px-4 py-3 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                />
                            </div>

                            <!-- Broker List -->
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto"
                            >
                                <div
                                    v-for="broker in filteredBrokers"
                                    :key="broker.id"
                                    @click="
                                        validationForm.preferred_broker_id =
                                            broker.id
                                    "
                                    class="p-4 border-2 rounded-xl cursor-pointer transition-all duration-200 hover:shadow-md"
                                    :class="{
                                        'border-primary-500 bg-primary-50':
                                            validationForm.preferred_broker_id ===
                                            broker.id,
                                        'border-neutral-200 hover:border-primary-300':
                                            validationForm.preferred_broker_id !==
                                            broker.id,
                                    }"
                                >
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <div class="flex-1">
                                            <div
                                                class="font-semibold text-neutral-900"
                                            >
                                                {{ broker.name }}
                                            </div>
                                            <div
                                                class="text-sm text-neutral-600 mt-0.5"
                                            >
                                                {{ broker.firm }}
                                            </div>
                                            <div
                                                class="flex items-center gap-2 mt-2 text-sm text-neutral-500"
                                            >
                                                <MapPinIcon class="w-4 h-4" />
                                                <span>{{
                                                    broker.location
                                                }}</span>
                                                <span>•</span>
                                                <span
                                                    >{{
                                                        broker.experience
                                                    }}
                                                    years</span
                                                >
                                            </div>
                                            <div
                                                class="flex items-center gap-3 mt-3 text-xs"
                                            >
                                                <span
                                                    class="px-2 py-1 bg-neutral-100 text-neutral-700 rounded"
                                                >
                                                    {{ broker.active_listings }}
                                                    listings
                                                </span>
                                                <span
                                                    class="px-2 py-1 rounded font-medium"
                                                    :class="
                                                        broker.workload < 5
                                                            ? 'bg-green-100 text-green-700'
                                                            : 'bg-orange-100 text-orange-700'
                                                    "
                                                >
                                                    {{ broker.availability }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            v-if="
                                                validationForm.preferred_broker_id ===
                                                broker.id
                                            "
                                            class="ml-3 text-primary-500"
                                        >
                                            <CheckCircleIcon class="w-6 h-6" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="filteredBrokers.length === 0"
                                class="text-center py-12 text-neutral-500"
                            >
                                <UserIcon
                                    class="w-12 h-12 mx-auto mb-3 text-neutral-400"
                                />
                                <p>No brokers found matching your search.</p>
                            </div>

                            <div
                                v-if="
                                    validationForm.broker_selection_method ===
                                        'manual' &&
                                    !validationForm.preferred_broker_id
                                "
                                class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-2"
                            >
                                <ExclamationTriangleIcon
                                    class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"
                                />
                                <p class="text-sm text-amber-800">
                                    Please select a broker to continue.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Images and Features -->
                    <div v-if="currentStep === 5" class="space-y-8">
                        <div class="flex items-center gap-3 mb-6">
                            <PhotoIcon class="w-6 h-6 text-primary-600" />
                            <h2 class="text-2xl font-bold text-neutral-900">
                                Images & Features
                            </h2>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="form-label">Property Images</label>
                            <div
                                class="border-2 border-dashed border-neutral-300 rounded-2xl p-8 text-center hover:border-primary-400 transition-colors"
                            >
                                <PhotoIcon
                                    class="w-12 h-12 text-neutral-400 mx-auto mb-4"
                                />
                                <p class="text-neutral-600 mb-4">
                                    Drag and drop images here, or
                                    <label
                                        class="text-primary-600 hover:text-primary-700 cursor-pointer font-medium"
                                    >
                                        browse files
                                        <input
                                            type="file"
                                            multiple
                                            accept="image/*"
                                            @change="handleImageUpload"
                                            class="hidden"
                                        />
                                    </label>
                                </p>
                                <p class="text-sm text-neutral-500">
                                    Maximum 10 images, 5MB each. Supported: JPG,
                                    PNG, GIF
                                </p>
                            </div>

                            <!-- Image Preview -->
                            <div
                                v-if="imageFiles.length > 0"
                                class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6"
                            >
                                <div
                                    v-for="(file, index) in imageFiles"
                                    :key="index"
                                    class="relative group"
                                >
                                    <img
                                        :src="createImageUrl(file)"
                                        :alt="`Property image ${index + 1}`"
                                        class="w-full h-24 object-cover rounded-xl"
                                    />
                                    <button
                                        type="button"
                                        @click="removeImage(index)"
                                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Property Features -->
                        <div
                            v-if="
                                availableFeatures &&
                                availableFeatures.length > 0
                            "
                        >
                            <label class="form-label"
                                >Property Features (Select up to 20)</label
                            >
                            <div
                                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3"
                            >
                                <button
                                    v-for="feature in availableFeatures"
                                    :key="feature"
                                    type="button"
                                    @click="toggleFeature(feature)"
                                    :class="[
                                        'px-4 py-2 rounded-xl text-sm font-medium transition-all',
                                        form.features.includes(feature)
                                            ? 'bg-primary-100 text-primary-700 border-2 border-primary-300'
                                            : 'bg-neutral-100 text-neutral-700 border-2 border-transparent hover:bg-neutral-200',
                                    ]"
                                >
                                    {{ feature }}
                                </button>
                            </div>
                            <p class="text-sm text-neutral-500 mt-2">
                                Selected: {{ form.features.length }}/20
                            </p>
                        </div>

                        <!-- Property Documents -->
                        <div>
                            <label class="form-label"
                                >Property Documents (Optional)</label
                            >
                            <p class="text-sm text-neutral-600 mb-4">
                                Upload relevant property documents such as floor
                                plans, surveys, or permits
                            </p>
                            <div
                                class="border-2 border-dashed border-neutral-300 rounded-2xl p-6 text-center hover:border-primary-400 transition-colors"
                            >
                                <DocumentIcon
                                    class="w-10 h-10 text-neutral-400 mx-auto mb-3"
                                />
                                <p class="text-neutral-600 mb-3">
                                    <label
                                        class="text-primary-600 hover:text-primary-700 cursor-pointer font-medium"
                                    >
                                        Choose property documents
                                        <input
                                            type="file"
                                            multiple
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                            @change="
                                                handlePropertyDocumentUpload
                                            "
                                            class="hidden"
                                        />
                                    </label>
                                </p>
                                <p class="text-sm text-neutral-500">
                                    Maximum 10 files, 10MB each. Supported: PDF,
                                    DOC, DOCX, JPG, PNG
                                </p>
                            </div>

                            <!-- Property Documents Preview -->
                            <div
                                v-if="propertyDocuments.length > 0"
                                class="mt-4 space-y-2"
                            >
                                <div
                                    v-for="(doc, index) in propertyDocuments"
                                    :key="index"
                                    class="flex items-center justify-between p-3 bg-neutral-50 rounded-lg"
                                >
                                    <div class="flex items-center gap-3">
                                        <DocumentIcon
                                            class="w-5 h-5 text-neutral-500"
                                        />
                                        <span
                                            class="text-sm text-neutral-700"
                                            >{{ doc.name }}</span
                                        >
                                        <span class="text-xs text-neutral-500"
                                            >({{
                                                formatFileSize(doc.size)
                                            }})</span
                                        >
                                    </div>
                                    <button
                                        type="button"
                                        @click="removePropertyDocument(index)"
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Ownership Documents -->
                        <div>
                            <label class="form-label"
                                >Ownership Documents (Optional)</label
                            >
                            <p class="text-sm text-neutral-600 mb-4">
                                Upload ownership verification documents such as
                                title, tax declaration, or deed of sale
                            </p>
                            <div
                                class="border-2 border-dashed border-neutral-300 rounded-2xl p-6 text-center hover:border-primary-400 transition-colors"
                            >
                                <DocumentIcon
                                    class="w-10 h-10 text-neutral-400 mx-auto mb-3"
                                />
                                <p class="text-neutral-600 mb-3">
                                    <label
                                        class="text-primary-600 hover:text-primary-700 cursor-pointer font-medium"
                                    >
                                        Choose ownership documents
                                        <input
                                            type="file"
                                            multiple
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            @change="
                                                handleOwnershipDocumentUpload
                                            "
                                            class="hidden"
                                        />
                                    </label>
                                </p>
                                <p class="text-sm text-neutral-500">
                                    Maximum 5 files, 10MB each. Supported: PDF,
                                    JPG, PNG
                                </p>
                            </div>

                            <!-- Ownership Documents Preview -->
                            <div
                                v-if="ownershipDocuments.length > 0"
                                class="mt-4 space-y-2"
                            >
                                <div
                                    v-for="(doc, index) in ownershipDocuments"
                                    :key="index"
                                    class="flex items-center justify-between p-3 bg-neutral-50 rounded-lg"
                                >
                                    <div class="flex items-center gap-3">
                                        <DocumentIcon
                                            class="w-5 h-5 text-neutral-500"
                                        />
                                        <span
                                            class="text-sm text-neutral-700"
                                            >{{ doc.name }}</span
                                        >
                                        <span class="text-xs text-neutral-500"
                                            >({{
                                                formatFileSize(doc.size)
                                            }})</span
                                        >
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeOwnershipDocument(index)"
                                        class="text-red-500 hover:text-red-700"
                                    >
                                        <XMarkIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Consent & Terms -->
                        <div
                            class="bg-white p-6 rounded-xl shadow-sm border border-neutral-200"
                        >
                            <h3
                                class="text-lg font-semibold mb-4 text-neutral-900"
                            >
                                Consent & Terms
                            </h3>
                            <div class="space-y-3">
                                <label class="flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        v-model="
                                            validationForm.marketing_consent
                                        "
                                        class="mt-1 rounded text-primary-600 focus:ring-primary-500"
                                    />
                                    <span class="text-neutral-700">
                                        I agree to receive updates about my
                                        listing and related services.
                                    </span>
                                </label>
                                <label class="flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        v-model="
                                            validationForm.newsletter_consent
                                        "
                                        class="mt-1 rounded text-primary-600 focus:ring-primary-500"
                                    />
                                    <span class="text-neutral-700">
                                        Subscribe me to the GeoCasa newsletter.
                                    </span>
                                </label>
                                <label class="flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        v-model="validationForm.terms_accepted"
                                        class="mt-1 rounded text-primary-600 focus:ring-primary-500"
                                    />
                                    <span class="text-neutral-700">
                                        <strong class="text-red-600">*</strong>
                                        I have read and agree to the
                                        <a
                                            href="/terms"
                                            target="_blank"
                                            class="text-primary-600 hover:underline font-medium"
                                            >Terms and Conditions</a
                                        >
                                        and
                                        <a
                                            href="/privacy"
                                            target="_blank"
                                            class="text-primary-600 hover:underline font-medium"
                                            >Privacy Policy</a
                                        >.
                                    </span>
                                </label>
                                <ValidationError
                                    v-if="errors.terms_accepted"
                                    :message="errors.terms_accepted"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="step-navigation">
                        <ModernButton
                            v-if="currentStep > 1"
                            type="button"
                            variant="ghost"
                            @click="prevStep"
                            class="step-button step-button-secondary flex items-center gap-2"
                        >
                            ← Previous
                        </ModernButton>
                        <div v-else></div>

                        <div class="flex gap-4">
                            <ModernButton
                                v-if="currentStep < totalSteps"
                                type="button"
                                variant="primary"
                                @click="nextStep"
                                :disabled="!canProceed"
                                :class="[
                                    'step-button step-button-primary flex items-center gap-2 transition-all duration-200',
                                    !canProceed
                                        ? 'opacity-50 cursor-not-allowed'
                                        : 'hover:shadow-lg',
                                ]"
                                :title="
                                    !canProceed
                                        ? `Complete required fields to continue: ${canProceedWithFeedback.errors.join(
                                              ', '
                                          )}`
                                        : 'Continue to next step'
                                "
                            >
                                <span>Next</span>
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    ></path>
                                </svg>
                            </ModernButton>

                            <ModernButton
                                v-else
                                type="submit"
                                variant="primary"
                                :disabled="!canProceed || isSubmitting"
                                :loading="isSubmitting"
                                :class="[
                                    'step-button step-button-primary flex items-center gap-2 transition-all duration-200',
                                    !canProceed || isSubmitting
                                        ? 'opacity-50 cursor-not-allowed'
                                        : 'hover:shadow-lg',
                                ]"
                                :title="
                                    !canProceed
                                        ? `Complete required fields to submit: ${canProceedWithFeedback.errors.join(
                                              ', '
                                          )}`
                                        : 'Submit your property listing'
                                "
                            >
                                <CheckCircleIcon class="w-5 h-5" />
                                <span>Submit Property</span>
                            </ModernButton>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Help Section -->
            <div
                class="mt-12 bg-white border border-neutral-200 p-6 rounded-2xl shadow-sm"
            >
                <h3 class="text-lg font-semibold text-neutral-900 mb-4">
                    Need Help?
                </h3>
                <div class="grid md:grid-cols-2 gap-6 text-sm text-neutral-600">
                    <div>
                        <p class="mb-2"><strong>What happens next?</strong></p>
                        <ul class="space-y-1 text-neutral-600">
                            <li>
                                • Your submission will be reviewed within 24-48
                                hours
                            </li>
                            <li>
                                • We'll verify property details and
                                documentation
                            </li>
                            <li>
                                • Once approved, your property goes live on our
                                platform
                            </li>
                            <li>
                                • Licensed brokers will start marketing your
                                property
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p class="mb-2"><strong>Contact Support</strong></p>
                        <p>
                            Email:
                            <a
                                href="mailto:support@geocasabohol.com"
                                class="text-primary-600 hover:text-primary-700"
                                >support@geocasabohol.com</a
                            >
                        </p>
                        <p>
                            Phone:
                            <a
                                href="tel:+631234567890"
                                class="text-primary-600 hover:text-primary-700"
                                >+63 123 456 7890</a
                            >
                        </p>
                        <p class="mt-2 text-xs text-neutral-500">
                            Available Mon-Sat, 8:00 AM - 6:00 PM
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Public Footer -->
    <PublicFooter />

    <!-- Enhanced Notification Container -->
    <div
        id="notification-container"
        class="fixed top-4 right-4 z-50 space-y-2 max-w-sm"
    >
        <!-- Notifications will be dynamically inserted here -->
    </div>

    <!-- Loading Overlay -->
    <div
        v-if="isSubmitting"
        class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center"
    >
        <div class="bg-white rounded-2xl p-8 shadow-2xl max-w-sm mx-4">
            <div class="text-center">
                <div
                    class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500 mx-auto mb-4"
                ></div>
                <h3 class="text-lg font-semibold text-neutral-900 mb-2">
                    Submitting Your Property
                </h3>
                <p class="text-sm text-neutral-600">
                    Please wait while we process your request...
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.form-group {
    @apply space-y-2;
}

.form-label {
    @apply block text-sm font-medium text-neutral-700;
}

.form-input {
    @apply w-full px-4 py-3 border border-neutral-200 rounded-2xl text-neutral-900 placeholder-neutral-500 
           focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200
           hover:border-neutral-300;
}

.form-select {
    @apply w-full px-4 py-3 border border-neutral-200 rounded-2xl text-neutral-900 
           focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-200
           hover:border-neutral-300 bg-white;
}

.form-error {
    @apply text-red-600 text-sm;
}

.tropical-gradient {
    background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 50%, #10b981 100%);
}

.card {
    @apply bg-white rounded-2xl shadow-soft border border-neutral-100;
}

.shadow-soft {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05),
        0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

.bohol-pattern {
    background-image: radial-gradient(
            circle at 25% 25%,
            rgba(14, 165, 233, 0.05) 0%,
            transparent 50%
        ),
        radial-gradient(
            circle at 75% 75%,
            rgba(16, 185, 129, 0.05) 0%,
            transparent 50%
        );
}
</style>
