<script setup>
import { Head } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import { ref } from "vue";
import {
    UserIcon,
    ArrowLeftIcon,
    ChartBarIcon,
    UserGroupIcon,
    BuildingOfficeIcon,
    CreditCardIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    broker: Object,
    performanceMetrics: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' })
        .format(value || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>

<template>
    <ModernDashboardLayout>
        <Head :title="`${broker.name} - Broker Profile`" />

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <ModernButton
                    variant="ghost"
                    :href="route('admin.approved-brokers.index')"
                    :icon="ArrowLeftIcon"
                >
                    Back to Brokers
                </ModernButton>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <div class="flex items-start gap-6">
                    <!-- Broker Avatar -->
                    <div class="w-20 h-20 bg-primary-100 rounded-2xl flex items-center justify-center">
                        <UserIcon class="w-10 h-10 text-primary-600" />
                    </div>

                    <!-- Broker Info -->
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 mb-2">
                            {{ broker.name }}
                        </h1>
                        <div class="flex items-center gap-6 text-sm text-gray-600 mb-2">
                            <div>{{ broker.email }}</div>
                            <div>Approved: {{ formatDate(broker.approved_at) }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Approved Broker
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
                    <CreditCardIcon class="w-5 h-5 text-primary-500" />
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ performanceMetrics.total_sales }}
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500">Commission</h3>
                    <ChartBarIcon class="w-5 h-5 text-accent-500" />
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ formatCurrency(performanceMetrics.total_commission) }}
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500">Avg. Days to Close</h3>
                    <ClockIcon class="w-5 h-5 text-coconut-500" />
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ performanceMetrics.avg_days_to_close || 'N/A' }}
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-500">Client Satisfaction</h3>
                    <StarIcon class="w-5 h-5 text-yellow-500" />
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ performanceMetrics.client_satisfaction || 'N/A' }}
                </div>
            </div>
        </div>

        <!-- Tabs for different sections -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <a
                        href="#"
                        class="border-primary-500 text-primary-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        Overview
                    </a>
                    <a
                        href="#"
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        Properties
                    </a>
                    <a
                        href="#"
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        Clients
                    </a>
                    <a
                        href="#"
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        Transactions
                    </a>
                    <a
                        href="#"
                        class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        Documents
                    </a>
                </nav>
            </div>
            
            <!-- Tab content -->
            <div class="p-6">
                <!-- Recent Activity -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                    <div class="space-y-4">
                        <!-- Activity items would go here -->
                        <div class="text-sm text-gray-500">No recent activity to display</div>
                    </div>
                </div>
                
                <!-- Recent Transactions -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Transactions</h3>
                    <div v-if="broker.transactions && broker.transactions.length > 0" class="space-y-4">
                        <div v-for="transaction in broker.transactions" :key="transaction.id" class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="font-medium text-gray-900">{{ transaction.property?.title || 'Unknown Property' }}</div>
                                    <div class="text-sm text-gray-500">{{ formatDate(transaction.created_at) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium text-gray-900">{{ formatCurrency(transaction.final_price) }}</div>
                                    <div class="text-sm text-gray-500">Commission: {{ formatCurrency(transaction.commission_amount) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-500">No transactions to display</div>
                </div>
            </div>
        </div>
    </ModernDashboardLayout>
</template>