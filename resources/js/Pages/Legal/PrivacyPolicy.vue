<template>
    <div class="min-h-screen bg-gradient-to-br from-neutral-50 to-coconut-50">
        <Head title="Privacy Policy - GeoCasa Bohol" />

        <!-- Navigation -->
        <PublicNavigation :auth="auth" />

        <!-- Header -->
        <section class="py-20 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1
                        class="text-4xl md:text-5xl font-bold text-neutral-900 mb-6"
                    >
                        Privacy Policy
                    </h1>
                    <p class="text-xl text-neutral-600 max-w-2xl mx-auto">
                        Your privacy is important to us. This policy explains
                        how we collect, use, and protect your information.
                    </p>
                    <div class="mt-6 text-sm text-neutral-500">
                        Last updated: {{ lastUpdated }}
                    </div>
                </div>
            </div>
        </section>

        <!-- Content -->
        <section class="py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-2xl shadow-soft p-8 md:p-12">
                    <!-- Table of Contents -->
                    <div class="mb-12 p-6 bg-neutral-50 rounded-xl">
                        <h2 class="text-lg font-semibold text-neutral-900 mb-4">
                            Table of Contents
                        </h2>
                        <nav class="space-y-2">
                            <a
                                href="#overview"
                                class="block text-primary-600 hover:text-primary-700 transition-colors"
                                >1. Overview</a
                            >
                            <a
                                href="#information-collected"
                                class="block text-primary-600 hover:text-primary-700 transition-colors"
                                >2. Information We Collect</a
                            >
                            <a
                                href="#how-we-use"
                                class="block text-primary-600 hover:text-primary-700 transition-colors"
                                >3. How We Use Your Information</a
                            >
                            <a
                                href="#information-sharing"
                                class="block text-primary-600 hover:text-primary-700 transition-colors"
                                >4. Information Sharing and Disclosure</a
                            >
                            <a
                                href="#data-security"
                                class="block text-primary-600 hover:text-primary-700 transition-colors"
                                >5. Data Security</a
                            >
                            <a
                                href="#data-retention"
                                class="block text-primary-600 hover:text-primary-700 transition-colors"
                                >6. Data Retention</a
                            >
                            <a
                                href="#your-rights"
                                class="block text-primary-600 hover:text-primary-700 transition-colors"
                                >7. Your Rights and Choices</a
                            >
                            <a
                                href="#cookies"
                                class="block text-primary-600 hover:text-primary-700 transition-colors"
                                >8. Cookies and Tracking</a
                            >
                            <a
                                href="#third-party"
                                class="block text-primary-600 hover:text-primary-700 transition-colors"
                                >9. Third-Party Services</a
                            >
                            <a
                                href="#children"
                                class="block text-primary-600 hover:text-primary-700 transition-colors"
                                >10. Children's Privacy</a
                            >
                            <a
                                href="#changes"
                                class="block text-primary-600 hover:text-primary-700 transition-colors"
                                >11. Changes to This Policy</a
                            >
                            <a
                                href="#contact"
                                class="block text-primary-600 hover:text-primary-700 transition-colors"
                                >12. Contact Us</a
                            >
                        </nav>
                    </div>

                    <!-- Privacy Policy Content -->
                    <div class="prose prose-lg max-w-none">
                        <section id="overview" class="mb-8">
                            <h2
                                class="text-2xl font-bold text-neutral-900 mb-4"
                            >
                                1. Overview
                            </h2>
                            <p class="text-neutral-700 mb-4">
                                GeoCasa Bohol ("we," "our," or "us") is
                                committed to protecting your privacy and
                                ensuring the security of your personal
                                information. This Privacy Policy explains how we
                                collect, use, disclose, and safeguard your
                                information when you use our real estate
                                platform.
                            </p>
                            <p class="text-neutral-700">
                                By using our services, you consent to the
                                collection and use of your information as
                                described in this policy.
                            </p>
                        </section>

                        <section id="information-collected" class="mb-8">
                            <h2
                                class="text-2xl font-bold text-neutral-900 mb-4"
                            >
                                2. Information We Collect
                            </h2>

                            <h3
                                class="text-xl font-semibold text-neutral-900 mb-3"
                            >
                                Personal Information
                            </h3>
                            <p class="text-neutral-700 mb-4">
                                We collect information you provide directly to
                                us, including:
                            </p>
                            <ul
                                class="list-disc list-inside text-neutral-700 space-y-2 mb-6"
                            >
                                <li>Name, email address, and phone number</li>
                                <li>
                                    Professional licensing information (for
                                    brokers)
                                </li>
                                <li>Business permits and documentation</li>
                                <li>Property information and images</li>
                                <li>Communication preferences</li>
                                <li>Transaction history and preferences</li>
                            </ul>

                            <h3
                                class="text-xl font-semibold text-neutral-900 mb-3"
                            >
                                Automatically Collected Information
                            </h3>
                            <ul
                                class="list-disc list-inside text-neutral-700 space-y-2 mb-4"
                            >
                                <li>IP address and device information</li>
                                <li>Browser type and version</li>
                                <li>Usage patterns and navigation data</li>
                                <li>Location information (with permission)</li>
                                <li>
                                    Cookies and similar tracking technologies
                                </li>
                            </ul>
                        </section>

                        <section id="how-we-use" class="mb-8">
                            <h2
                                class="text-2xl font-bold text-neutral-900 mb-4"
                            >
                                3. How We Use Your Information
                            </h2>
                            <p class="text-neutral-700 mb-4">
                                We use your information to:
                            </p>
                            <ul
                                class="list-disc list-inside text-neutral-700 space-y-2 mb-4"
                            >
                                <li>Provide and maintain our services</li>
                                <li>Verify broker licenses and credentials</li>
                                <li>
                                    Facilitate property transactions and
                                    communications
                                </li>
                                <li>
                                    Send important notifications and updates
                                </li>
                                <li>
                                    Improve our platform and user experience
                                </li>
                                <li>
                                    Prevent fraud and ensure platform security
                                </li>
                                <li>Comply with legal obligations</li>
                                <li>Provide customer support</li>
                            </ul>
                        </section>

                        <section id="information-sharing" class="mb-8">
                            <h2
                                class="text-2xl font-bold text-neutral-900 mb-4"
                            >
                                4. Information Sharing and Disclosure
                            </h2>
                            <p class="text-neutral-700 mb-4">
                                We may share your information in the following
                                circumstances:
                            </p>
                            <ul
                                class="list-disc list-inside text-neutral-700 space-y-2 mb-4"
                            >
                                <li>
                                    <strong>With Other Users:</strong> Property
                                    listings and broker profiles are publicly
                                    visible
                                </li>
                                <li>
                                    <strong>Service Providers:</strong>
                                    Third-party vendors who assist in platform
                                    operations
                                </li>
                                <li>
                                    <strong>Legal Requirements:</strong> When
                                    required by law or to protect our rights
                                </li>
                                <li>
                                    <strong>Business Transfers:</strong> In
                                    connection with mergers or acquisitions
                                </li>
                                <li>
                                    <strong>Consent:</strong> When you
                                    explicitly consent to sharing
                                </li>
                            </ul>
                            <p class="text-neutral-700">
                                We do not sell your personal information to
                                third parties for marketing purposes.
                            </p>
                        </section>

                        <section id="data-security" class="mb-8">
                            <h2
                                class="text-2xl font-bold text-neutral-900 mb-4"
                            >
                                5. Data Security
                            </h2>
                            <p class="text-neutral-700 mb-4">
                                We implement appropriate technical and
                                organizational measures to protect your
                                information:
                            </p>
                            <ul
                                class="list-disc list-inside text-neutral-700 space-y-2 mb-4"
                            >
                                <li>
                                    Encryption of data in transit and at rest
                                </li>
                                <li>
                                    Regular security assessments and updates
                                </li>
                                <li>
                                    Access controls and authentication measures
                                </li>
                                <li>Secure file upload and storage systems</li>
                                <li>
                                    Regular backup and disaster recovery
                                    procedures
                                </li>
                            </ul>
                        </section>

                        <section id="data-retention" class="mb-8">
                            <h2
                                class="text-2xl font-bold text-neutral-900 mb-4"
                            >
                                6. Data Retention
                            </h2>
                            <p class="text-neutral-700 mb-4">
                                We retain your information for as long as
                                necessary to:
                            </p>
                            <ul
                                class="list-disc list-inside text-neutral-700 space-y-2 mb-4"
                            >
                                <li>Provide our services to you</li>
                                <li>Comply with legal obligations</li>
                                <li>Resolve disputes and enforce agreements</li>
                                <li>
                                    Maintain business records as required by law
                                </li>
                            </ul>
                        </section>

                        <section id="your-rights" class="mb-8">
                            <h2
                                class="text-2xl font-bold text-neutral-900 mb-4"
                            >
                                7. Your Rights and Choices
                            </h2>
                            <p class="text-neutral-700 mb-4">
                                You have the right to:
                            </p>
                            <ul
                                class="list-disc list-inside text-neutral-700 space-y-2 mb-4"
                            >
                                <li>
                                    Access and review your personal information
                                </li>
                                <li>
                                    Correct inaccurate or incomplete information
                                </li>
                                <li>
                                    Request deletion of your information
                                    (subject to legal requirements)
                                </li>
                                <li>Opt-out of marketing communications</li>
                                <li>Request data portability</li>
                                <li>Object to certain processing activities</li>
                            </ul>
                            <p class="text-neutral-700">
                                To exercise these rights, please contact us
                                using the information provided below.
                            </p>
                        </section>

                        <section id="cookies" class="mb-8">
                            <h2
                                class="text-2xl font-bold text-neutral-900 mb-4"
                            >
                                8. Cookies and Tracking
                            </h2>
                            <p class="text-neutral-700 mb-4">
                                We use cookies and similar technologies to:
                            </p>
                            <ul
                                class="list-disc list-inside text-neutral-700 space-y-2 mb-4"
                            >
                                <li>Remember your preferences and settings</li>
                                <li>Analyze platform usage and performance</li>
                                <li>
                                    Provide personalized content and
                                    recommendations
                                </li>
                                <li>
                                    Ensure platform security and prevent fraud
                                </li>
                            </ul>
                            <p class="text-neutral-700">
                                You can control cookie settings through your
                                browser preferences.
                            </p>
                        </section>

                        <section id="third-party" class="mb-8">
                            <h2
                                class="text-2xl font-bold text-neutral-900 mb-4"
                            >
                                9. Third-Party Services
                            </h2>
                            <p class="text-neutral-700 mb-4">
                                Our platform may integrate with third-party
                                services for:
                            </p>
                            <ul
                                class="list-disc list-inside text-neutral-700 space-y-2 mb-4"
                            >
                                <li>Payment processing</li>
                                <li>Map and location services</li>
                                <li>Analytics and performance monitoring</li>
                                <li>Communication and messaging</li>
                            </ul>
                            <p class="text-neutral-700">
                                These services have their own privacy policies,
                                and we encourage you to review them.
                            </p>
                        </section>

                        <section id="children" class="mb-8">
                            <h2
                                class="text-2xl font-bold text-neutral-900 mb-4"
                            >
                                10. Children's Privacy
                            </h2>
                            <p class="text-neutral-700 mb-4">
                                Our services are not intended for individuals
                                under 18 years of age. We do not knowingly
                                collect personal information from children. If
                                we become aware that we have collected
                                information from a child, we will take steps to
                                delete it promptly.
                            </p>
                        </section>

                        <section id="changes" class="mb-8">
                            <h2
                                class="text-2xl font-bold text-neutral-900 mb-4"
                            >
                                11. Changes to This Policy
                            </h2>
                            <p class="text-neutral-700 mb-4">
                                We may update this Privacy Policy from time to
                                time. We will notify you of any material changes
                                by posting the new policy on our platform and
                                updating the "Last Updated" date. Your continued
                                use of our services after such changes
                                constitutes acceptance of the updated policy.
                            </p>
                        </section>

                        <section id="contact" class="mb-8">
                            <h2
                                class="text-2xl font-bold text-neutral-900 mb-4"
                            >
                                12. Contact Us
                            </h2>
                            <p class="text-neutral-700 mb-4">
                                If you have any questions about this Privacy
                                Policy or our data practices, please contact us:
                            </p>
                            <div class="bg-neutral-50 p-6 rounded-xl">
                                <p class="text-neutral-700 mb-2">
                                    <strong
                                        >GeoCasa Bohol - Privacy Officer</strong
                                    >
                                </p>
                                <p class="text-neutral-700 mb-2">
                                    Email: privacy@geocasabohol.com
                                </p>
                                <p class="text-neutral-700 mb-2">
                                    Phone: +63 123 456 7890
                                </p>
                                <p class="text-neutral-700">
                                    Address: Tagbilaran City, Bohol, Philippines
                                </p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <PublicFooter />
    </div>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import PublicNavigation from "@/Components/PublicNavigation.vue";
import PublicFooter from "@/Components/PublicFooter.vue";
import { computed } from "vue";

defineProps({
    auth: Object,
});

const lastUpdated = computed(() => {
    return new Date().toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
});
</script>
