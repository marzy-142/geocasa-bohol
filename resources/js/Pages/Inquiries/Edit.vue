<template>
    <AppLayout title="Edit Inquiry">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Inquiry
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Contact Information -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Contact Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        for="name"
                                        class="block text-sm font-medium text-gray-700"
                                        >Name *</label
                                    >
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{
                                            'border-red-500': form.errors.name,
                                        }"
                                        required
                                    />
                                    <div
                                        v-if="form.errors.name"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="email"
                                        class="block text-sm font-medium text-gray-700"
                                        >Email *</label
                                    >
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{
                                            'border-red-500': form.errors.email,
                                        }"
                                        required
                                    />
                                    <div
                                        v-if="form.errors.email"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.email }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="phone"
                                        class="block text-sm font-medium text-gray-700"
                                        >Phone</label
                                    >
                                    <input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{
                                            'border-red-500': form.errors.phone,
                                        }"
                                    />
                                    <div
                                        v-if="form.errors.phone"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.phone }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="inquiry_type"
                                        class="block text-sm font-medium text-gray-700"
                                        >Inquiry Type *</label
                                    >
                                    <select
                                        id="inquiry_type"
                                        v-model="form.inquiry_type"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{
                                            'border-red-500':
                                                form.errors.inquiry_type,
                                        }"
                                        required
                                    >
                                        <option value="">Select Type</option>
                                        <option value="general">General</option>
                                        <option value="viewing">Viewing</option>
                                        <option value="purchase">
                                            Purchase
                                        </option>
                                        <option value="information">
                                            Information
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.inquiry_type"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.inquiry_type }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property & Client Selection -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Property & Client
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        for="property_id"
                                        class="block text-sm font-medium text-gray-700"
                                        >Property *</label
                                    >
                                    <select
                                        id="property_id"
                                        v-model="form.property_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{
                                            'border-red-500':
                                                form.errors.property_id,
                                        }"
                                        required
                                    >
                                        <option value="">
                                            Select Property
                                        </option>
                                        <option
                                            v-for="property in properties"
                                            :key="property.id"
                                            :value="property.id"
                                        >
                                            {{ property.title }} -
                                            {{ property.municipality }}
                                            <span v-if="property.broker"
                                                >({{
                                                    property.broker.name
                                                }})</span
                                            >
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.property_id"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.property_id }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="client_id"
                                        class="block text-sm font-medium text-gray-700"
                                        >Existing Client (Optional)</label
                                    >
                                    <select
                                        id="client_id"
                                        v-model="form.client_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{
                                            'border-red-500':
                                                form.errors.client_id,
                                        }"
                                    >
                                        <option value="">
                                            Select Client (Optional)
                                        </option>
                                        <option
                                            v-for="client in clients"
                                            :key="client.id"
                                            :value="client.id"
                                        >
                                            {{ client.name }} -
                                            {{ client.email }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.client_id"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.client_id }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="border-b border-gray-200 pb-6">
                            <label
                                for="message"
                                class="block text-sm font-medium text-gray-700"
                                >Message *</label
                            >
                            <textarea
                                id="message"
                                v-model="form.message"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{
                                    'border-red-500': form.errors.message,
                                }"
                                placeholder="Enter inquiry message..."
                                required
                            ></textarea>
                            <div
                                v-if="form.errors.message"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ form.errors.message }}
                            </div>
                        </div>

                        <!-- Status & Response Information -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Status & Response
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        for="status"
                                        class="block text-sm font-medium text-gray-700"
                                        >Status *</label
                                    >
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{
                                            'border-red-500':
                                                form.errors.status,
                                        }"
                                        required
                                    >
                                        <option value="new">New</option>
                                        <option value="contacted">
                                            Contacted
                                        </option>
                                        <option value="scheduled">
                                            Scheduled
                                        </option>
                                        <option value="completed">
                                            Completed
                                        </option>
                                        <option value="closed">Closed</option>
                                    </select>
                                    <div
                                        v-if="form.errors.status"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.status }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="scheduled_at"
                                        class="block text-sm font-medium text-gray-700"
                                        >Scheduled Date (Optional)</label
                                    >
                                    <input
                                        id="scheduled_at"
                                        v-model="form.scheduled_at"
                                        type="datetime-local"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{
                                            'border-red-500':
                                                form.errors.scheduled_at,
                                        }"
                                    />
                                    <div
                                        v-if="form.errors.scheduled_at"
                                        class="mt-2 text-sm text-red-600"
                                    >
                                        {{ form.errors.scheduled_at }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label
                                    for="broker_response"
                                    class="block text-sm font-medium text-gray-700"
                                    >Broker Response</label
                                >
                                <textarea
                                    id="broker_response"
                                    v-model="form.broker_response"
                                    rows="4"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    :class="{
                                        'border-red-500':
                                            form.errors.broker_response,
                                    }"
                                    placeholder="Response to client..."
                                ></textarea>
                                <div
                                    v-if="form.errors.broker_response"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ form.errors.broker_response }}
                                </div>
                            </div>

                            <div class="mt-4">
                                <label
                                    for="broker_notes"
                                    class="block text-sm font-medium text-gray-700"
                                    >Internal Notes</label
                                >
                                <textarea
                                    id="broker_notes"
                                    v-model="form.broker_notes"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    :class="{
                                        'border-red-500':
                                            form.errors.broker_notes,
                                    }"
                                    placeholder="Internal notes (not visible to client)..."
                                ></textarea>
                                <div
                                    v-if="form.errors.broker_notes"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    {{ form.errors.broker_notes }}
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div
                            class="flex items-center justify-end space-x-4 pt-6"
                        >
                            <Link
                                :href="route('inquiries.show', inquiry.id)"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                            >
                                <span v-if="form.processing">Updating...</span>
                                <span v-else>Update Inquiry</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    inquiry: Object,
    properties: Array,
    clients: Array,
});

const form = useForm({
    name: props.inquiry.name,
    email: props.inquiry.email,
    phone: props.inquiry.phone || "",
    message: props.inquiry.message,
    inquiry_type: props.inquiry.inquiry_type,
    property_id: props.inquiry.property_id,
    client_id: props.inquiry.client_id || "",
    status: props.inquiry.status,
    broker_notes: props.inquiry.broker_notes || "",
    broker_response: props.inquiry.broker_response || "",
    scheduled_at: props.inquiry.scheduled_at
        ? props.inquiry.scheduled_at.slice(0, 16)
        : "",
});

const submit = () => {
    form.put(route("inquiries.update", props.inquiry.id));
};
</script>
