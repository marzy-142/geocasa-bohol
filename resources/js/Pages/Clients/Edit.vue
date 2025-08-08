<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-3xl font-bold text-gray-900">
                                    Edit Client
                                </h2>
                                <p class="text-gray-600">
                                    Update {{ client.name }}'s information
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <Link
                                    :href="route('clients.show', client.id)"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    View Client
                                </Link>
                                <Link
                                    :href="route('clients.index')"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    ← Back to Clients
                                </Link>
                            </div>
                        </div>

                        <form @submit.prevent="submit">
                            <!-- Basic Information -->
                            <div class="mb-8">
                                <h3
                                    class="text-xl font-semibold text-gray-900 mb-4"
                                >
                                    Basic Information
                                </h3>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <div>
                                        <label
                                            for="name"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Full Name *
                                        </label>
                                        <input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500': errors.name,
                                            }"
                                            required
                                        />
                                        <div
                                            v-if="errors.name"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.name }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="email"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Email Address *
                                        </label>
                                        <input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500': errors.email,
                                            }"
                                            required
                                        />
                                        <div
                                            v-if="errors.email"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.email }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="phone"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Phone Number *
                                        </label>
                                        <input
                                            id="phone"
                                            v-model="form.phone"
                                            type="tel"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500': errors.phone,
                                            }"
                                            required
                                        />
                                        <div
                                            v-if="errors.phone"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.phone }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="status"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Status *
                                        </label>
                                        <select
                                            id="status"
                                            v-model="form.status"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500': errors.status,
                                            }"
                                            required
                                        >
                                            <option
                                                v-for="status in statuses"
                                                :key="status"
                                                :value="status"
                                            >
                                                {{ formatStatus(status) }}
                                            </option>
                                        </select>
                                        <div
                                            v-if="errors.status"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.status }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="source"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Source *
                                        </label>
                                        <select
                                            id="source"
                                            v-model="form.source"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500': errors.source,
                                            }"
                                            required
                                        >
                                            <option
                                                v-for="source in sources"
                                                :key="source"
                                                :value="source"
                                            >
                                                {{ formatSource(source) }}
                                            </option>
                                        </select>
                                        <div
                                            v-if="errors.source"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.source }}
                                        </div>
                                    </div>

                                    <div v-if="canAssignBroker">
                                        <label
                                            for="broker_id"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Assigned Broker
                                        </label>
                                        <select
                                            id="broker_id"
                                            v-model="form.broker_id"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500':
                                                    errors.broker_id,
                                            }"
                                        >
                                            <option value="">Unassigned</option>
                                            <option
                                                v-for="broker in brokers"
                                                :key="broker.id"
                                                :value="broker.id"
                                            >
                                                {{ broker.name }}
                                            </option>
                                        </select>
                                        <div
                                            v-if="errors.broker_id"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.broker_id }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="mb-8">
                                <h3
                                    class="text-xl font-semibold text-gray-900 mb-4"
                                >
                                    Contact Information
                                </h3>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <div class="md:col-span-2">
                                        <label
                                            for="address"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Address
                                        </label>
                                        <textarea
                                            id="address"
                                            v-model="form.address"
                                            rows="2"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500':
                                                    errors.address,
                                            }"
                                        ></textarea>
                                        <div
                                            v-if="errors.address"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.address }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="city"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            City
                                        </label>
                                        <input
                                            id="city"
                                            v-model="form.city"
                                            type="text"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500': errors.city,
                                            }"
                                        />
                                        <div
                                            v-if="errors.city"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.city }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="zip_code"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Zip Code
                                        </label>
                                        <input
                                            id="zip_code"
                                            v-model="form.zip_code"
                                            type="text"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500':
                                                    errors.zip_code,
                                            }"
                                        />
                                        <div
                                            v-if="errors.zip_code"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.zip_code }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Preferences -->
                            <div class="mb-8">
                                <h3
                                    class="text-xl font-semibold text-gray-900 mb-4"
                                >
                                    Property Preferences
                                </h3>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <div>
                                        <label
                                            for="budget_min"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Minimum Budget (₱)
                                        </label>
                                        <input
                                            id="budget_min"
                                            v-model="form.budget_min"
                                            type="number"
                                            step="0.01"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500':
                                                    errors.budget_min,
                                            }"
                                        />
                                        <div
                                            v-if="errors.budget_min"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.budget_min }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="budget_max"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Maximum Budget (₱)
                                        </label>
                                        <input
                                            id="budget_max"
                                            v-model="form.budget_max"
                                            type="number"
                                            step="0.01"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500':
                                                    errors.budget_max,
                                            }"
                                        />
                                        <div
                                            v-if="errors.budget_max"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.budget_max }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="preferred_location"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Preferred Location
                                        </label>
                                        <select
                                            id="preferred_location"
                                            v-model="form.preferred_location"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500':
                                                    errors.preferred_location,
                                            }"
                                        >
                                            <option value="">
                                                Any Location
                                            </option>
                                            <option
                                                v-for="municipality in municipalities"
                                                :key="municipality"
                                                :value="municipality"
                                            >
                                                {{ municipality }}
                                            </option>
                                        </select>
                                        <div
                                            v-if="errors.preferred_location"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.preferred_location }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="preferred_area_min"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Min Area (sqm)
                                        </label>
                                        <input
                                            id="preferred_area_min"
                                            v-model="form.preferred_area_min"
                                            type="number"
                                            step="0.01"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500':
                                                    errors.preferred_area_min,
                                            }"
                                        />
                                        <div
                                            v-if="errors.preferred_area_min"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.preferred_area_min }}
                                        </div>
                                    </div>

                                    <div>
                                        <label
                                            for="preferred_area_max"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Max Area (sqm)
                                        </label>
                                        <input
                                            id="preferred_area_max"
                                            v-model="form.preferred_area_max"
                                            type="number"
                                            step="0.01"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500':
                                                    errors.preferred_area_max,
                                            }"
                                        />
                                        <div
                                            v-if="errors.preferred_area_max"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.preferred_area_max }}
                                        </div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label
                                            for="preferred_features"
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Preferred Features
                                        </label>
                                        <textarea
                                            id="preferred_features"
                                            v-model="form.preferred_features"
                                            rows="3"
                                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{
                                                'border-red-500':
                                                    errors.preferred_features,
                                            }"
                                        ></textarea>
                                        <div
                                            v-if="errors.preferred_features"
                                            class="text-red-500 text-sm mt-1"
                                        >
                                            {{ errors.preferred_features }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="mb-8">
                                <h3
                                    class="text-xl font-semibold text-gray-900 mb-4"
                                >
                                    Additional Information
                                </h3>
                                <div>
                                    <label
                                        for="notes"
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Notes
                                    </label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="4"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        :class="{
                                            'border-red-500': errors.notes,
                                        }"
                                    ></textarea>
                                    <div
                                        v-if="errors.notes"
                                        class="text-red-500 text-sm mt-1"
                                    >
                                        {{ errors.notes }}
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end space-x-4">
                                <Link
                                    :href="route('clients.show', client.id)"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="processing"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    {{
                                        processing
                                            ? "Updating..."
                                            : "Update Client"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    client: Object,
    sources: Array,
    statuses: Array,
    municipalities: Array,
    brokers: Array,
    errors: Object,
});

const page = usePage();
const processing = ref(false);

const canAssignBroker = computed(() => {
    return page.props.auth.user.role === "admin";
});

const form = useForm({
    name: props.client.name,
    email: props.client.email,
    phone: props.client.phone,
    address: props.client.address,
    city: props.client.city,
    zip_code: props.client.zip_code,
    status: props.client.status,
    source: props.client.source,
    broker_id: props.client.broker_id,
    budget_min: props.client.budget_min,
    budget_max: props.client.budget_max,
    preferred_location: props.client.preferred_location,
    preferred_area_min: props.client.preferred_area_min,
    preferred_area_max: props.client.preferred_area_max,
    preferred_features: props.client.preferred_features,
    notes: props.client.notes,
});

const formatStatus = (status) => {
    return status.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatSource = (source) => {
    return source.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const submit = () => {
    processing.value = true;
    form.put(route("clients.update", props.client.id), {
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>
