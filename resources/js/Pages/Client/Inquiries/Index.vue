<template>
    <Head title="My Inquiries" />
    
    <ModernDashboardLayout>
        <!-- Enhanced Header Section -->
        <div class="bg-gradient-to-r from-primary-500 to-accent-500 text-white p-8 rounded-3xl mb-8 shadow-soft-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-3 text-white">My Inquiries</h1>
                    <p class="text-primary-100 text-lg">
                        Track and manage your property inquiries
                    </p>
                </div>
                <div class="flex items-center space-x-6">
                    <!-- Status Indicators -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                        <div class="flex items-center space-x-6 text-sm font-medium">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-coconut-400 rounded-full mr-3 animate-pulse"></div>
                                <span class="text-white">{{ newInquiriesCount }} New</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-emerald-400 rounded-full mr-3"></div>
                                <span class="text-white">{{ respondedInquiriesCount }} Responded</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <!-- Enhanced Search & Filter Section -->
            <div class="bg-white rounded-3xl shadow-soft-lg p-8 border border-neutral-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-primary-100 rounded-2xl flex items-center justify-center">
                        <MagnifyingGlassIcon class="w-5 h-5 text-primary-600" />
                    </div>
                    <h2 class="text-xl font-bold text-neutral-900">
                        Search & Filter Inquiries
                    </h2>
                </div>
                <form @submit.prevent="search" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">Search</label>
                        <TextInput
                            v-model="form.search"
                            placeholder="Search inquiries..."
                            class="w-full rounded-2xl border-neutral-200 focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">Status</label>
                        <SelectInput
                            v-model="form.status"
                            class="w-full rounded-2xl border-neutral-200 focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">All Status</option>
                            <option value="new">New</option>
                            <option value="contacted">Contacted</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="completed">Completed</option>
                            <option value="closed">Closed</option>
                        </SelectInput>
                    </div>
                    <div class="flex items-end space-x-3">
                        <button 
                            type="submit" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-2xl shadow-soft transition-all duration-200 hover:scale-105 flex items-center justify-center gap-2"
                        >
                            <MagnifyingGlassIcon class="w-5 h-5" />
                            Search
                        </button>
                        <button 
                            @click="clearFilters" 
                            type="button" 
                            class="px-6 py-3 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-semibold rounded-2xl transition-all duration-200 hover:scale-105"
                        >
                            Clear
                        </button>
                    </div>
                </form>
            </div>

            <!-- Enhanced Inquiries List -->
            <div class="bg-white rounded-3xl shadow-soft-lg border border-neutral-100">
                <div class="p-8">
                    <div v-if="inquiries.data.length === 0" class="text-center py-16">
                        <div class="text-neutral-400 text-lg mb-6">
                            <div class="w-20 h-20 bg-neutral-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                                <ChatBubbleLeftRightIcon class="w-10 h-10 text-neutral-400" />
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-neutral-900 mb-3">No inquiries found</h3>
                        <p class="text-neutral-500 text-lg">You haven't made any property inquiries yet.</p>
                        <Link
                            :href="route('public.properties')"
                            class="inline-flex items-center gap-2 mt-6 px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-2xl shadow-soft transition-all duration-200 hover:scale-105"
                        >
                            <HomeModernIcon class="w-5 h-5" />
                            Browse Properties
                        </Link>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div
                            v-for="inquiry in inquiries.data"
                            :key="inquiry.id"
                            class="bg-white border border-neutral-100 rounded-3xl p-8 hover:shadow-soft-lg transition-all duration-300 hover:scale-105 group"
                        >
                            <div class="flex justify-between items-start mb-6">
                                <div class="flex-1">
                                    <h4 class="text-xl font-bold text-neutral-900 mb-3 group-hover:text-primary-600 transition-colors line-clamp-2">
                                        {{ inquiry.property?.title || 'Property Inquiry' }}
                                    </h4>
                                    <p class="text-base text-neutral-600 mb-3 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ inquiry.property?.location || 'Location not specified' }}
                                    </p>
                                </div>
                                <span
                                    :class="getStatusClass(inquiry.status)"
                                    class="inline-flex items-center px-4 py-2 rounded-2xl text-sm font-semibold"
                                >
                                    {{ getStatusLabel(inquiry.status) }}
                                </span>
                            </div>
                                
                            <div class="space-y-4 mb-6">
                                <div class="flex items-center text-base">
                                    <span class="text-neutral-500 w-24 font-medium">Date:</span>
                                    <span class="text-neutral-900 font-semibold">{{ formatDate(inquiry.created_at) }}</span>
                                </div>
                                <div class="flex items-center text-base">
                                    <span class="text-neutral-500 w-24 font-medium">Price:</span>
                                    <span class="text-neutral-900 font-bold text-lg text-primary-600">₱{{ inquiry.property?.price?.toLocaleString() || 'N/A' }}</span>
                                </div>
                                <div v-if="inquiry.message" class="text-base">
                                    <span class="text-neutral-500 font-medium">Message:</span>
                                    <p class="text-neutral-700 mt-2 line-clamp-3 bg-neutral-50 p-4 rounded-2xl">{{ inquiry.message }}</p>
                                </div>
                            </div>
                            
                            <div v-if="inquiry.response" class="bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-2xl p-6 mb-6 border border-emerald-200">
                                <h4 class="font-bold text-emerald-900 mb-3 flex items-center gap-2">
                                    <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                                    Broker Response:
                                </h4>
                                <p class="text-emerald-800 leading-relaxed">{{ inquiry.response }}</p>
                            </div>
                            
                            <div class="flex gap-3">
                                <Link
                                    :href="route('client.inquiries.show', inquiry.id)"
                                    class="flex-1 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white text-center py-3 px-6 rounded-2xl text-sm font-semibold transition-all duration-200 hover:scale-105 shadow-soft flex items-center justify-center gap-2"
                                >
                                    View Details
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </Link>
                                <Link
                                    :href="route('public.properties.show', inquiry.property?.slug)"
                                    class="flex-1 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-center py-3 px-6 rounded-2xl text-sm font-semibold transition-all duration-200 hover:scale-105"
                                >
                                    View Property
                                </Link>
                            </div>
                            <div class="text-xs text-neutral-400 mt-4 text-center">
                                ID: #{{ inquiry.id }}
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="inquiries.links && inquiries.links.length > 3" class="mt-8">
                            <div class="bg-white rounded-3xl shadow-soft-lg border border-neutral-100 p-6">
                                <div class="flex items-center justify-between">
                                    <div class="text-base text-neutral-600 font-medium">
                                        Showing {{ inquiries.from }} to {{ inquiries.to }} of {{ inquiries.total }} results
                                    </div>
                                    <div class="flex space-x-2">
                                        <Link
                                            v-for="link in inquiries.links"
                                            :key="link.label"
                                            :href="link.url"
                                            :class="[
                                                'px-4 py-2 text-sm font-semibold rounded-2xl transition-all duration-200',
                                                link.active
                                                    ? 'bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-soft'
                                                    : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200 hover:scale-105'
                                            ]"
                                            v-html="link.label"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import ModernDashboardLayout from '@/Layouts/ModernDashboardLayout.vue'
import TextInput from '@/Components/TextInput.vue'
import SelectInput from '@/Components/SelectInput.vue'
import { MagnifyingGlassIcon, ChatBubbleLeftRightIcon, HomeModernIcon } from '@heroicons/vue/24/outline'
import { reactive, computed } from 'vue'

const props = defineProps({
    inquiries: Object,
    filters: Object,
    client: Object
})

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || ''
})

// Computed properties for status indicators
const newInquiriesCount = computed(() => {
    return props.inquiries.data.filter(inquiry => inquiry.status === 'new').length
})

const respondedInquiriesCount = computed(() => {
    return props.inquiries.data.filter(inquiry => 
        ['contacted', 'scheduled', 'completed'].includes(inquiry.status)
    ).length
})

const search = () => {
    router.get(route('client.inquiries.index'), form, {
        preserveState: true,
        replace: true
    })
}

const clearFilters = () => {
    form.search = ''
    form.status = ''
    search()
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getStatusClass = (status) => {
    const classes = {
        'pending': 'bg-gradient-to-r from-amber-100 to-amber-200 text-amber-800 border border-amber-200',
        'responded': 'bg-gradient-to-r from-emerald-100 to-emerald-200 text-emerald-800 border border-emerald-200',
        'closed': 'bg-gradient-to-r from-neutral-100 to-neutral-200 text-neutral-800 border border-neutral-200'
    }
    return classes[status] || 'bg-gradient-to-r from-neutral-100 to-neutral-200 text-neutral-800 border border-neutral-200'
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