<script setup>
import ModernDashboardLayout from '@/Layouts/ModernDashboardLayout.vue';
import DashboardCard from '@/Components/DashboardCard.vue';
import ModernButton from '@/Components/ModernButton.vue';
import { Head } from '@inertiajs/vue3';
import { 
    UserGroupIcon, 
    ClockIcon,
    BuildingOfficeIcon,
    CreditCardIcon,
    CheckCircleIcon,
    XCircleIcon,
    EyeIcon,
    PlusIcon
} from '@heroicons/vue/24/outline';

// Mock data for demonstration
const stats = [
  {
    title: 'Total Brokers',
    value: '25',
    subtitle: 'Active brokers',
    icon: UserGroupIcon,
    color: 'primary',
    trend: { direction: 'up', value: '+3', label: 'new this month' }
  },
  {
    title: 'Pending Approvals',
    value: '3',
    subtitle: 'Awaiting review',
    icon: ClockIcon,
    color: 'accent',
    trend: { direction: 'down', value: '-2', label: 'from last week' }
  },
  {
    title: 'Total Properties',
    value: '150',
    subtitle: 'Listed properties',
    icon: BuildingOfficeIcon,
    color: 'coconut',
    trend: { direction: 'up', value: '+12', label: 'this month' }
  },
  {
    title: 'Total Transactions',
    value: '89',
    subtitle: 'Completed deals',
    icon: CreditCardIcon,
    color: 'neutral',
    trend: { direction: 'up', value: '+7', label: 'this month' }
  }
];

const pendingBrokers = [
  { id: 1, name: 'Alice Johnson', email: 'alice@example.com', applied: '2024-01-15' },
  { id: 2, name: 'Bob Wilson', email: 'bob@example.com', applied: '2024-01-14' },
  { id: 3, name: 'Carol Davis', email: 'carol@example.com', applied: '2024-01-13' }
];
</script>

<template>
  <Head title="Admin Dashboard - GeoCasa Bohol" />

  <ModernDashboardLayout>
    <!-- Welcome Section -->
    <div class="mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div>
          <h1 class="text-3xl font-bold text-neutral-900 mb-2">
            Admin Dashboard 🛡️
          </h1>
          <p class="text-neutral-600 text-lg">
            System overview and management for GeoCasa Bohol platform.
          </p>
        </div>
        <div class="flex items-center gap-4">
          <ModernButton variant="outline" :icon="EyeIcon">
            View Reports
          </ModernButton>
          <ModernButton variant="primary" :icon="PlusIcon">
            Add Broker
          </ModernButton>
        </div>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="dashboard-grid mb-12">
      <DashboardCard
        v-for="stat in stats"
        :key="stat.title"
        :title="stat.title"
        :value="stat.value"
        :subtitle="stat.subtitle"
        :icon="stat.icon"
        :color="stat.color"
        :trend="stat.trend"
        :interactive="true"
      />
    </div>

    <!-- Pending Broker Approvals -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
      <div class="card p-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-neutral-900">Pending Broker Approvals</h2>
          <span class="px-3 py-1 bg-accent-100 text-accent-700 rounded-full text-sm font-medium">
            {{ pendingBrokers.length }} pending
          </span>
        </div>
        <div class="space-y-4">
          <div 
            v-for="broker in pendingBrokers" 
            :key="broker.id" 
            class="flex items-center justify-between p-4 bg-neutral-50 rounded-2xl hover:bg-neutral-100 transition-colors"
          >
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gradient-to-br from-primary-400 to-accent-400 rounded-2xl flex items-center justify-center text-white font-medium text-sm shadow-soft">
                {{ broker.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h3 class="font-medium text-neutral-900">{{ broker.name }}</h3>
                <p class="text-sm text-neutral-600">{{ broker.email }}</p>
                <p class="text-xs text-neutral-500">Applied: {{ broker.applied }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <button class="p-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-xl transition-colors">
                <CheckCircleIcon class="w-5 h-5" />
              </button>
              <button class="p-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-xl transition-colors">
                <XCircleIcon class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- System Health -->
      <div class="card p-8">
        <h2 class="text-xl font-bold text-neutral-900 mb-6">System Health</h2>
        <div class="space-y-4">
          <div class="flex items-center justify-between p-4 bg-green-50 rounded-2xl">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 bg-green-500 rounded-full"></div>
              <span class="font-medium text-neutral-900">Database</span>
            </div>
            <span class="text-sm text-green-700 font-medium">Healthy</span>
          </div>
          <div class="flex items-center justify-between p-4 bg-green-50 rounded-2xl">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 bg-green-500 rounded-full"></div>
              <span class="font-medium text-neutral-900">API Services</span>
            </div>
            <span class="text-sm text-green-700 font-medium">Online</span>
          </div>
          <div class="flex items-center justify-between p-4 bg-green-50 rounded-2xl">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 bg-green-500 rounded-full"></div>
              <span class="font-medium text-neutral-900">File Storage</span>
            </div>
            <span class="text-sm text-green-700 font-medium">Available</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Bohol Admin Section -->
    <div class="card p-8 tropical-gradient text-white">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold mb-2">Manage Bohol Real Estate</h2>
          <p class="text-white/90 mb-4">
            Oversee the beautiful properties and trusted brokers across Bohol's stunning landscapes.
          </p>
          <ModernButton variant="coconut">
            View All Properties
          </ModernButton>
        </div>
        <div class="hidden lg:block">
          <div class="w-32 h-32 bg-white/10 rounded-3xl backdrop-blur-sm flex items-center justify-center">
            <span class="text-4xl">🏛️</span>
          </div>
        </div>
      </div>
    </div>
  </ModernDashboardLayout>
</template>