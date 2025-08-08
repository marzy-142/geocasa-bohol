<template>
    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-3xl font-bold text-gray-900">
                                    Add New Client
                                </h2>
                                <p class="text-gray-600">
                                    Register a new client in GeoCasa Bohol
                                </p>
                            </div>
                            <Link
                                :href="route('clients.index')"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                            >
                                ← Back to Clients
                            </Link>
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
                                            placeholder="e.g., Juan Dela Cruz"
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
                                            placeholder="e.g., juan@example.com"
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
                                            placeholder="e.g., +63 912 345 6789"
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
                                            <option value="">
                                                Select Source
                                            </option>
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
                                            placeholder="Complete address..."
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
                                            placeholder="e.g., Tagbilaran"
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
                                            placeholder="e.g., 6300"
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
                                            placeholder="e.g., 1000000"
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
                                            placeholder="e.g., 5000000"
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
                                            placeholder="e.g., 500"
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
                                            placeholder="e.g., 2000"
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
                                            placeholder="e.g., Beachfront, Mountain View, Near Road, With Utilities"
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
                                        placeholder="Additional notes about the client..."
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
                                    :href="route('clients.index')"
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
                                            ? "Creating..."
                                            : "Create Client"
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
import { ref } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    sources: Array,
    municipalities: Array,
    errors: Object,
});

const form = useForm({
    name: "",
    email: "",
    phone: "",
    address: "",
    city: "",
    zip_code: "",
    source: "",
    budget_min: "",
    budget_max: "",
    preferred_location: "",
    preferred_area_min: "",
    preferred_area_max: "",
    preferred_features: "",
    notes: "",
});

const processing = ref(false);

const formatSource = (source) => {
    return source.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const submit = () => {
    processing.value = true;
    form.post(route("clients.store"), {
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>
