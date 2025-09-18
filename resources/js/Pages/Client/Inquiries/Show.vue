<template>
    <Head :title="`Inquiry - ${inquiry.property?.title || 'Property'}`" />
    
    <AuthenticatedLayout>
        <!-- Enhanced Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg p-6 text-white mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <Link
                        :href="route('client.inquiries.index')"
                        class="inline-flex items-center text-blue-100 hover:text-white text-sm font-medium mb-3 transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to My Inquiries
                    </Link>
                    <h1 class="text-3xl font-bold">Inquiry Details</h1>
                    <p class="text-blue-100 mt-2">
                        View your inquiry information and responses
                    </p>
                </div>
                <div class="flex space-x-2">
                    <span
                        :class="getStatusClass(inquiry.status)"
                        class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-white/20 text-white"
                    >
                        {{ getStatusLabel(inquiry.status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="py-6">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Property Information -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">Property Information</h3>
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Property ID: #{{ inquiry.property?.id }}
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-semibold text-gray-900 text-lg mb-3">{{ inquiry.property?.title || 'Property Title' }}</h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center text-gray-600">
                                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            {{ inquiry.property?.location || 'Location not specified' }}
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            {{ inquiry.property?.type || 'Not specified' }}
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            ₱{{ formatPrice(inquiry.property?.price) || 'Contact for price' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="inquiry.property?.user" class="bg-gray-50 rounded-lg p-6">
                                <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Broker Information
                                </h4>
                                <div class="space-y-2">
                                    <p class="font-medium text-gray-900">{{ inquiry.property.user.name }}</p>
                                    <p class="text-gray-600 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ inquiry.property.user.email }}
                                    </p>
                                    <p class="text-gray-600 flex items-center" v-if="inquiry.property.user.phone">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        {{ inquiry.property.user.phone }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-200" v-if="inquiry.property">
                            <Link
                                :href="route('public.properties.show', inquiry.property.slug)"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                                target="_blank"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                View Full Property Details
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Inquiry Details -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">Your Inquiry</h3>
                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.959 8.959 0 01-4.906-1.436L3 21l2.436-5.094A8.959 8.959 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                                </svg>
                                Inquiry #{{ inquiry.id }}
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2">
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.959 8.959 0 01-4.906-1.436L3 21l2.436-5.094A8.959 8.959 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                                        </svg>
                                        Your Message
                                    </h4>
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <p class="text-gray-900 leading-relaxed whitespace-pre-wrap">{{ inquiry.message }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                 <div class="bg-gray-50 rounded-lg p-6">
                                     <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                                         <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                         </svg>
                                         Status
                                     </h4>
                                     <span class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium"
                                           :class="{
                                               'bg-yellow-100 text-yellow-800 border border-yellow-200': inquiry.status === 'new',
                                               'bg-blue-100 text-blue-800 border border-blue-200': inquiry.status === 'contacted',
                                               'bg-purple-100 text-purple-800 border border-purple-200': inquiry.status === 'scheduled',
                                               'bg-green-100 text-green-800 border border-green-200': inquiry.status === 'completed',
                                               'bg-gray-100 text-gray-800 border border-gray-200': inquiry.status === 'closed'
                                           }">
                                         <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                             <circle cx="10" cy="10" r="3"></circle>
                                         </svg>
                                         {{ inquiry.status.charAt(0).toUpperCase() + inquiry.status.slice(1) }}
                                     </span>
                                 </div>
                                 
                                 <div class="bg-gray-50 rounded-lg p-6">
                                     <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                                         <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                         </svg>
                                         Timeline
                                     </h4>
                                     <div class="text-gray-600">
                                         <p class="text-sm">Submitted on</p>
                                         <p class="font-medium text-gray-900">{{ formatDate(inquiry.created_at) }}</p>
                                     </div>
                                 </div>
                                
                                <div v-if="inquiry.preferred_contact_method" class="bg-gray-50 rounded-lg p-6">
                                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        Contact Preference
                                    </h4>
                                    <p class="text-gray-900 capitalize">{{ inquiry.preferred_contact_method }}</p>
                                </div>
                                
                                <div v-if="inquiry.budget_range" class="bg-gray-50 rounded-lg p-6">
                                    <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Budget Range
                                    </h4>
                                    <p class="text-gray-900">{{ inquiry.budget_range }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Broker Response -->
                <div v-if="inquiry.response" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Broker Response</h3>
                        <div class="bg-blue-50 rounded-lg p-4">
                            <p class="text-gray-900 whitespace-pre-wrap">{{ inquiry.response }}</p>
                            <p class="text-sm text-gray-500 mt-2" v-if="inquiry.updated_at !== inquiry.created_at">
                                Responded on {{ formatDate(inquiry.updated_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Client Response Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Update Your Information</h3>
                        <form @submit.prevent="updateInquiry">
                            <div class="space-y-4">
                                <div>
                                    <label for="client_response" class="block text-sm font-medium text-gray-700 mb-1">
                                        Additional Message (Optional)
                                    </label>
                                    <textarea
                                        id="client_response"
                                        v-model="form.client_response"
                                        rows="4"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Add any additional information or questions..."
                                    ></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="preferred_contact_method" class="block text-sm font-medium text-gray-700 mb-1">
                                            Preferred Contact Method
                                        </label>
                                        <select
                                            id="preferred_contact_method"
                                            v-model="form.preferred_contact_method"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                            <option value="">Select method</option>
                                            <option value="email">Email</option>
                                            <option value="phone">Phone</option>
                                            <option value="both">Both</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="budget_range" class="block text-sm font-medium text-gray-700 mb-1">
                                            Budget Range
                                        </label>
                                        <input
                                            id="budget_range"
                                            v-model="form.budget_range"
                                            type="text"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="e.g., 5M - 7M"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end">
                                <PrimaryButton type="submit" :disabled="form.processing">
                                    Update Information
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
    inquiry: Object,
    client: Object
})

const form = useForm({
    client_response: props.inquiry.client_response || '',
    preferred_contact_method: props.inquiry.preferred_contact_method || '',
    budget_range: props.inquiry.budget_range || ''
})

const updateInquiry = () => {
    form.put(route('client.inquiries.update', props.inquiry.id), {
        onSuccess: () => {
            // Form will be reset automatically on success
        }
    })
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatPrice = (price) => {
    if (!price) return null
    return new Intl.NumberFormat('en-PH').format(price)
}

const getStatusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        responded: 'bg-blue-100 text-blue-800',
        closed: 'bg-gray-100 text-gray-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
    const labels = {
        pending: 'Pending',
        responded: 'Responded',
        closed: 'Closed'
    }
    return labels[status] || 'Unknown'
}
</script>