<script setup>
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { 
    ClockIcon,
    CheckCircleIcon,
    DocumentCheckIcon,
    ShieldCheckIcon,
    ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

const handleLogout = () => {
    router.post('/logout')
}

const verificationSteps = [
    {
        title: 'Application Submitted',
        description: 'Your broker registration has been received',
        status: 'completed',
        icon: CheckCircleIcon
    },
    {
        title: 'Document Review',
        description: 'Our team is verifying your credentials',
        status: 'current',
        icon: DocumentCheckIcon
    },
    {
        title: 'Account Activation',
        description: 'Full dashboard access will be granted',
        status: 'pending',
        icon: ShieldCheckIcon
    }
]
</script>

<template>
    <Head title="Pending Approval - GeoCasa Bohol" />

    <div class="min-h-screen bg-gray-50 py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <div class="w-16 h-16 bg-amber-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <ClockIcon class="w-8 h-8 text-white" />
                </div>
                <h1 class="text-3xl font-semibold text-gray-900 mb-3">
                    Account Under Review
                </h1>
                <p class="text-gray-600 text-lg">
                    Your broker application is being reviewed by our team
                </p>
            </div>

            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
                <div class="flex items-center mb-6">
                    <div class="w-3 h-3 bg-amber-500 rounded-full mr-3"></div>
                    <span class="text-sm font-medium text-amber-700 uppercase tracking-wide">In Progress</span>
                </div>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Verification in Progress</h2>
                <p class="text-gray-600">
                    Estimated processing time: 1-2 business days
                </p>
            </div>

            <!-- Verification Steps -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Verification Process</h3>
                <div class="space-y-6">
                    <div
                        v-for="(step, index) in verificationSteps"
                        :key="step.title"
                        class="flex items-start"
                    >
                        <div 
                            :class="[
                                'w-8 h-8 rounded-full flex items-center justify-center mr-4 mt-0.5',
                                step.status === 'completed' ? 'bg-green-100 text-green-600' :
                                step.status === 'current' ? 'bg-amber-100 text-amber-600' :
                                'bg-gray-100 text-gray-400'
                            ]"
                        >
                            <component :is="step.icon" class="w-4 h-4" />
                        </div>
                        <div class="flex-1">
                            <h4 
                                :class="[
                                    'font-medium mb-1',
                                    step.status === 'pending' ? 'text-gray-500' : 'text-gray-900'
                                ]"
                            >
                                {{ step.title }}
                            </h4>
                            <p 
                                :class="[
                                    'text-sm',
                                    step.status === 'pending' ? 'text-gray-400' : 'text-gray-600'
                                ]"
                            >
                                {{ step.description }}
                            </p>
                        </div>
                        <div v-if="step.status === 'completed'" class="text-green-600 text-sm font-medium">
                            Complete
                        </div>
                        <div v-else-if="step.status === 'current'" class="text-amber-600 text-sm font-medium">
                            In Progress
                        </div>
                    </div>
                </div>
            </div>

            <!-- What's Being Reviewed -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">What We're Reviewing</h3>
                <div class="space-y-3">
                    <div class="flex items-center text-gray-700">
                        <div class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-3"></div>
                        <span>Professional license verification</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <div class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-3"></div>
                        <span>Identity document validation</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <div class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-3"></div>
                        <span>Background compliance check</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <div class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-3"></div>
                        <span>Business registration details</span>
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Need Help?</h3>
                <p class="text-gray-600 mb-4">
                    Contact our support team if you have questions about your application.
                </p>
                <a 
                    href="mailto:support@geocasa.com" 
                    class="text-blue-600 hover:text-blue-700 font-medium text-sm"
                >
                    support@geocasa.com
                </a>
            </div>

            <!-- Sign Out -->
            <div class="text-center">
                <button
                    @click="handleLogout"
                    type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                >
                    <ArrowRightOnRectangleIcon class="w-4 h-4 mr-2" />
                    Sign Out
                </button>
            </div>
        </div>
    </div>
</template>
