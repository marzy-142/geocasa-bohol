<script setup>
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { reactive, computed, ref } from "vue";
import LoadingSkeleton from "@/Components/LoadingSkeleton.vue";
import EmptyState from "@/Components/EmptyState.vue";
import ErrorState from "@/Components/ErrorState.vue";
import { useFormatters } from "@/Composables/useFormatters";
import {
    MagnifyingGlassIcon,
    ChatBubbleLeftRightIcon,
    BuildingOfficeIcon,
    StarIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    PlusIcon,
    FunnelIcon,
    XMarkIcon,
    EyeIcon,
    ArrowRightIcon,
    CalendarIcon,
    UserGroupIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    inquiries: Object,
    filters: Object,
    client: Object,
    broker: Object,
});

const form = reactive({
    search: props.filters.search || "",
    status: props.filters.status || "",
});

const showFilters = ref(false);
const selectedInquiries = ref([]);
const isLoading = ref(false);
const error = ref(null);

// Formatters
const { formatRelativeTime } = useFormatters();

// Computed properties
const newInquiriesCount = computed(() => {
    return props.inquiries.data.filter((inquiry) => inquiry.status === "new")
        .length;
});

const respondedInquiriesCount = computed(() => {
    return props.inquiries.data.filter((inquiry) =>
        ["contacted", "scheduled", "completed"].includes(inquiry.status)
    ).length;
});

const activeInquiriesCount = computed(() => {
    return props.inquiries.data.filter((inquiry) =>
        ["new", "contacted", "scheduled"].includes(inquiry.status)
    ).length;
});

// Methods
const search = () => {
    router.get(route("client.inquiries.index"), form, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    form.search = "";
    form.status = "";
    search();
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const retryLoad = () => {
    error.value = null;
    router.reload();
};

const getStatusClass = (status) => {
    const classes = {
        new: "bg-blue-100 text-blue-800 border-blue-200",
        contacted: "bg-yellow-100 text-yellow-800 border-yellow-200",
        scheduled: "bg-purple-100 text-purple-800 border-purple-200",
        completed: "bg-green-100 text-green-800 border-green-200",
        closed: "bg-gray-100 text-gray-800 border-gray-200",
    };
    return classes[status] || "bg-gray-100 text-gray-800 border-gray-200";
};

const getStatusLabel = (status) => {
    const labels = {
        new: "New",
        contacted: "Contacted",
        scheduled: "Scheduled",
        completed: "Completed",
        closed: "Closed",
    };
    return labels[status] || "Unknown";
};

const getStatusIcon = (status) => {
    const icons = {
        new: InformationCircleIcon,
        contacted: ChatBubbleLeftRightIcon,
        scheduled: CalendarIcon,
        completed: CheckCircleIcon,
        closed: XMarkIcon,
    };
    return icons[status] || InformationCircleIcon;
};

const getPriorityColor = (inquiry) => {
    const daysSinceCreated = Math.floor(
        (new Date() - new Date(inquiry.created_at)) / (1000 * 60 * 60 * 24)
    );

    if (daysSinceCreated > 7) return "text-red-600";
    if (daysSinceCreated > 3) return "text-yellow-600";
    return "text-green-600";
};

const toggleInquirySelection = (inquiryId) => {
    const index = selectedInquiries.value.indexOf(inquiryId);
    if (index > -1) {
        selectedInquiries.value.splice(index, 1);
    } else {
        selectedInquiries.value.push(inquiryId);
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    })
        .format(value)
        .replace("PHP", "")
        .trim();
};
</script>

<template>
    <Head title="My Inquiries - GeoCasa Bohol" />

    <ModernDashboardLayout>
        <!-- Header Section -->
        <div class="bg-white border border-neutral-200 rounded-lg p-6 mb-6">
            <div
                class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
            >
                <div>
                    <h1 class="text-2xl font-semibold mb-2 text-neutral-900">
                        My Property Inquiries
                    </h1>
                    <p class="text-neutral-600 text-base">
                        Track and manage your property inquiries with brokers
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="showFilters = !showFilters"
                        class="bg-white border border-neutral-300 text-neutral-700 hover:bg-neutral-50 px-4 py-2 rounded-md font-medium transition-colors flex items-center gap-2"
                    >
                        <FunnelIcon class="w-4 h-4" />
                        {{ showFilters ? "Hide" : "Show" }} Filters
                    </button>
                    <Link
                        :href="route('client.inquiries.create')"
                        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-md font-medium transition-colors flex items-center gap-2"
                    >
                        <PlusIcon class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <ErrorState
            v-if="error"
            type="error"
            :title="error.title || 'Unable to load inquiries'"
            :description="error.message || 'Please try again or contact support if the problem persists.'"
            @retry="retryLoad"
        />

        <!-- Loading State -->
        <div v-else-if="isLoading" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <LoadingSkeleton v-for="n in 4" :key="n" type="stats-card" />
            </div>
            <LoadingSkeleton type="card" class="h-96" />
        </div>

        <!-- Content -->
        <template v-else>
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-600 mb-1">
                                Total Inquiries
                            </p>
                            <p class="text-2xl font-semibold text-neutral-900">
                                {{ inquiries.total || 0 }}
                            </p>
                            <p class="text-sm text-neutral-500">All time</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center"
                        >
                            <ChatBubbleLeftRightIcon
                                class="w-6 h-6 text-blue-600"
                            />
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-600 mb-1">
                                New Inquiries
                            </p>
                            <p class="text-3xl font-bold text-blue-600">
                                {{ newInquiriesCount }}
                            </p>
                            <p class="text-sm text-neutral-500">
                                Awaiting response
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center"
                        >
                            <InformationCircleIcon class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-600 mb-1">
                                Active Inquiries
                            </p>
                            <p class="text-3xl font-bold text-green-600">
                                {{ activeInquiriesCount }}
                            </p>
                            <p class="text-sm text-neutral-500">In progress</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center"
                        >
                            <CheckCircleIcon class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-neutral-200 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-neutral-600 mb-1">
                                Responded
                            </p>
                            <p class="text-3xl font-bold text-purple-600">
                                {{ respondedInquiriesCount }}
                            </p>
                            <p class="text-sm text-neutral-500">Broker responded</p>
                        </div>
                        <div
                            class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center"
                        >
                            <ChatBubbleLeftRightIcon
                                class="w-6 h-6 text-purple-600"
                            />
                        </div>
                    </div>
                </div>
            </div>

        <!-- Filters Section -->
        <div
            v-if="showFilters"
            class="bg-white rounded-2xl shadow-soft-lg border border-neutral-100 p-6 mb-8"
        >
            <div class="flex items-center justify-between mb-6">
                <h2
                    class="text-xl font-bold text-neutral-900 flex items-center gap-2"
                >
                    <FunnelIcon class="w-5 h-5" />
                    Search & Filter Inquiries
                </h2>
                <button
                    @click="clearFilters"
                    class="text-neutral-500 hover:text-neutral-700 text-sm font-medium flex items-center gap-1"
                >
                    <XMarkIcon class="w-4 h-4" />
                    Clear All
                </button>
            </div>

            <form
                @submit.prevent="search"
                class="grid grid-cols-1 md:grid-cols-3 gap-6"
            >
                <div>
                    <label
                        class="block text-sm font-semibold text-neutral-700 mb-2"
                        >Search Inquiries</label
                    >
                    <div class="relative">
                        <MagnifyingGlassIcon
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400"
                        />
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Search by property title or message..."
                            class="w-full pl-10 pr-4 py-3 border border-neutral-200 rounded-2xl focus:border-primary-500 focus:ring-primary-500 focus:outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label
                        class="block text-sm font-semibold text-neutral-700 mb-2"
                        >Status</label
                    >
                    <select
                        v-model="form.status"
                        class="w-full px-4 py-3 border border-neutral-200 rounded-2xl focus:border-primary-500 focus:ring-primary-500 focus:outline-none"
                    >
                        <option value="">All Status</option>
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                <div class="flex items-end gap-3">
                    <button
                        type="submit"
                        class="flex-1 bg-primary-600 hover:bg-primary-700 text-white py-3 px-4 rounded-2xl font-semibold transition-colors flex items-center justify-center gap-2"
                    >
                        <MagnifyingGlassIcon class="w-5 h-5" />
                        Search
                    </button>
                    <button
                        @click="clearFilters"
                        type="button"
                        class="px-4 py-3 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-2xl font-semibold transition-colors"
                    >
                        Clear
                    </button>
                </div>
            </form>
        </div>

        <!-- Inquiries List -->
        <div
            class="bg-white rounded-2xl shadow-soft-lg border border-neutral-100"
        >
            <div class="p-6 border-b border-neutral-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-neutral-900">
                        {{ inquiries.data.length }} Inquiries Found
                    </h2>
                    <div
                        v-if="selectedInquiries.length > 0"
                        class="flex items-center gap-2"
                    >
                        <span class="text-sm text-neutral-600"
                            >{{ selectedInquiries.length }} selected</span
                        >
                        <button
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                        >
                            Archive Selected
                        </button>
                    </div>
                </div>
            </div>

            <div
                v-if="inquiries.data.length > 0"
                class="divide-y divide-neutral-200"
            >
                <div
                    v-for="inquiry in inquiries.data"
                    :key="inquiry.id"
                    class="p-6 hover:bg-neutral-50 transition-colors"
                >
                    <div class="flex items-start gap-4">
                        <!-- Selection Checkbox -->
                        <div class="flex items-center pt-1">
                            <input
                                :id="`inquiry-${inquiry.id}`"
                                type="checkbox"
                                :value="inquiry.id"
                                @change="toggleInquirySelection(inquiry.id)"
                                class="w-4 h-4 text-primary-600 border-neutral-300 rounded focus:ring-primary-500"
                            />
                        </div>

                        <!-- Property Image -->
                        <div
                            class="w-20 h-20 bg-neutral-200 rounded-xl overflow-hidden flex-shrink-0"
                        >
                            <img
                                :src="
                                    inquiry.property?.main_image ||
                                    '/images/placeholder-property.jpg'
                                "
                                :alt="inquiry.property?.title"
                                class="w-full h-full object-cover"
                            />
                        </div>

                        <!-- Inquiry Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-neutral-900 mb-1"
                                    >
                                        {{
                                            inquiry.property?.title ||
                                            "Property Inquiry"
                                        }}
                                    </h3>
                                    <p class="text-sm text-neutral-600 mb-2">
                                        {{
                                            inquiry.property?.municipality ||
                                            "Location not specified"
                                        }}
                                    </p>
                                    <p
                                        class="text-sm text-neutral-500 line-clamp-2"
                                    >
                                        {{ inquiry.message }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        :class="getStatusClass(inquiry.status)"
                                        class="px-3 py-1 rounded-full text-xs font-medium border"
                                    >
                                        {{ getStatusLabel(inquiry.status) }}
                                    </span>
                                    <component
                                        :is="getStatusIcon(inquiry.status)"
                                        class="w-5 h-5 text-neutral-400"
                                    />
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div
                                    class="flex items-center gap-4 text-sm text-neutral-500"
                                >
                                    <div class="flex items-center gap-1">
                                        <ClockIcon class="w-4 h-4" />
                                        <span :title="formatDate(inquiry.created_at)">{{
                                            formatRelativeTime(inquiry.created_at)
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="inquiry.property?.total_price"
                                        class="flex items-center gap-1"
                                    >
                                        <span
                                            class="font-medium text-neutral-700"
                                        >
                                            {{
                                                formatCurrency(
                                                    inquiry.property.total_price
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        v-if="inquiry.broker"
                                        class="flex items-center gap-1"
                                    >
                                        <UserGroupIcon class="w-4 h-4" />
                                        <span>{{ inquiry.broker.name }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="
                                            route(
                                                'client.inquiries.show',
                                                inquiry.id
                                            )
                                        "
                                        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1"
                                    >
                                        <EyeIcon class="w-4 h-4" />
                                        View Details
                                    </Link>
                                    <Link
                                        :href="route('client.broker')"
                                        class="bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-1"
                                    >
                                        <ChatBubbleLeftRightIcon
                                            class="w-4 h-4"
                                        />
                                        Message Broker
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <div
                    class="w-20 h-20 bg-neutral-100 rounded-3xl flex items-center justify-center mx-auto mb-6"
                >
                    <ChatBubbleLeftRightIcon
                        class="w-10 h-10 text-neutral-400"
                    />
                </div>
                <h3 class="text-2xl font-bold text-neutral-900 mb-4">
                    No inquiries found
                </h3>
                <p class="text-neutral-600 mb-8 max-w-md mx-auto">
                    You haven't made any property inquiries yet. Start by
                    browsing properties and creating your first inquiry.
                </p>
                <div class="flex items-center justify-center gap-4">
                    <button
                        @click="clearFilters"
                        class="bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-6 py-3 rounded-lg font-medium transition-colors"
                    >
                        Clear Filters
                    </button>
                    <Link
                        :href="route('client.properties')"
                        class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center gap-2"
                    >
                        <BuildingOfficeIcon class="w-5 h-5" />
                        Browse Properties
                    </Link>
                </div>
            </div>
        </div>

            <!-- Pagination -->
            <div v-if="inquiries.links && inquiries.links.length > 3" class="mt-8">
                <nav class="flex items-center justify-center">
                    <div class="flex items-center space-x-2">
                        <Link
                            v-for="link in inquiries.links"
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                                link.active
                                    ? 'bg-primary-600 text-white'
                                    : 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100',
                            ]"
                        >
                            <span v-html="link.label"></span>
                        </Link>
                    </div>
                </nav>
            </div>
        </template>
    </ModernDashboardLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-clamp: 2; /* Standard property for compatibility */
}

.shadow-soft-lg {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>
