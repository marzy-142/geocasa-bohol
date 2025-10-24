// Test the password validation logic
const calculatePasswordStrength = (password) => {
    let score = 0;
    let feedback = [];

    if (!password) return { score: 0, label: "", feedback: [] };

    // Length check
    if (password.length >= 12) score += 1;
    else feedback.push("At least 12 characters");

    // Lowercase check
    if (/[a-z]/.test(password)) score += 1;
    else feedback.push("Lowercase letters");

    // Uppercase check
    if (/[A-Z]/.test(password)) score += 1;
    else feedback.push("Uppercase letters");

    // Number check
    if (/\d/.test(password)) score += 1;
    else feedback.push("Numbers");

    // Special character check - include underscore and other common special chars
    if (/[@$!%*?&_\-+=\[\]{}|\\:";'<>.,\/~`]/.test(password)) score += 1;
    else feedback.push("Special characters (@$!%*?&_ etc.)");

    const labels = [
        "Very Weak",
        "Weak",
        "Fair",
        "Good",
        "Strong",
        "Very Strong",
    ];
    const colors = [
        "text-red-500",
        "text-orange-500",
        "text-yellow-500",
        "text-blue-500",
        "text-green-500",
        "text-green-600",
    ];

    // Only show "Very Strong" if all requirements are met AND no feedback items
    const finalScore =
        feedback.length > 0 ? Math.min(score, 4) : Math.min(score, 5);

    const result = {
        score: finalScore,
        label: labels[finalScore],
        color: colors[finalScore],
        feedback,
    };

    return result;
};

// Test with the problematic password
const testPassword = "password12345_P";
console.log("Testing password:", testPassword);
console.log("Length:", testPassword.length);
console.log("Has lowercase:", /[a-z]/.test(testPassword));
console.log("Has uppercase:", /[A-Z]/.test(testPassword));
console.log("Has numbers:", /\d/.test(testPassword));
console.log("Has special chars:", /[@$!%*?&_\-+=\[\]{}|\\:";'<>.,\/~`]/.test(testPassword));

const result = calculatePasswordStrength(testPassword);
console.log("Result:", result);
console.log("Should show error:", result.feedback.length > 0);









