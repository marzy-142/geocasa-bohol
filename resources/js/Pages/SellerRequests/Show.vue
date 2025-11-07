<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, onMounted, onBeforeUnmount } from "vue";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import ModernButton from "@/Components/ModernButton.vue";
import Modal from "@/Components/Modal.vue";
import { ref } from "vue";
import {
    BuildingOfficeIcon,
    MapPinIcon,
    CurrencyDollarIcon,
    CalendarIcon,
    ClockIcon,
    UserIcon,
    PhoneIcon,
    EnvelopeIcon,
    CheckCircleIcon,
    XCircleIcon,
    PencilIcon,
    TrashIcon,
    EyeIcon,
    DocumentTextIcon,
    PhotoIcon,
    PaperClipIcon,
    ArrowDownTrayIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    XMarkIcon,
    ArrowsPointingOutIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    sellerRequest: Object,
    brokers: Array,
    canManage: Boolean,
});

// Debug logs removed for production polish

// Forms
const approveForm = useForm({
    status: "approved",
    assigned_broker_id: "",
    admin_notes: "",
});

const rejectForm = useForm({
    status: "rejected",
    rejection_reason: "",
    admin_notes: "",
});

const deleteForm = useForm({});

// Modal states
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const showDeleteModal = ref(false);
const showAssignModal = ref(false);
// Image viewer modal
const showImageModal = ref(false);
const imageIndex = ref(0);

// Computed properties
const statusColor = computed(() => {
    const colors = {
        pending: "bg-yellow-100 text-yellow-800 border-yellow-200",
        under_review: "bg-blue-100 text-blue-800 border-blue-200",
        assigned: "bg-indigo-100 text-indigo-800 border-indigo-200",
        approved: "bg-green-100 text-green-800 border-green-200",
        rejected: "bg-red-100 text-red-800 border-red-200",
        listed: "bg-emerald-100 text-emerald-800 border-emerald-200",
    };
    return (
        colors[props.sellerRequest.status] ||
        "bg-gray-100 text-gray-800 border-gray-200"
    );
});

const statusIcon = computed(() => {
    const s = props.sellerRequest.status;
    if (s === "approved" || s === "listed" || s === "assigned")
        return CheckCircleIcon;
    if (s === "rejected") return XCircleIcon;
    if (s === "pending" || s === "under_review") return ClockIcon;
    return ClockIcon;
});

const page = usePage();
const userRole = computed(() => page.props.auth.user.role);

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
    }).format(price);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatArea = (area) => {
    if (!area || area === 0) return "Area not specified";
    return `${area} sqm`;
};

const formatLocation = (request) => {
    const parts = [];

    if (request.barangay) parts.push(request.barangay);
    if (request.municipality) parts.push(request.municipality);
    if (request.city) parts.push(request.city);
    if (request.province) parts.push(request.province);

    if (parts.length === 0) {
        return request.address || "Location not specified";
    }

    return parts.join(", ");
};

const formatPropertyAddress = (request) => {
    const parts = [];

    if (request.barangay) parts.push(request.barangay);
    if (request.municipality) parts.push(request.municipality);
    if (request.city) parts.push(request.city);
    if (request.province) parts.push(request.province);

    return parts.length > 0 ? parts.join(", ") : "Address not specified";
};

// Normalized/fallback fields to align with Create.vue submissions
const contactName = computed(
    () =>
        props.sellerRequest.name ||
        props.sellerRequest.seller_name ||
        props.sellerRequest.contact_name ||
        "Not specified"
);
const contactEmail = computed(
    () =>
        props.sellerRequest.email ||
        props.sellerRequest.seller_email ||
        props.sellerRequest.contact_email ||
        "Not specified"
);
const contactPhone = computed(
    () =>
        props.sellerRequest.phone ||
        props.sellerRequest.seller_phone ||
        props.sellerRequest.contact_phone ||
        null
);

const normalizedLotArea = computed(() => {
    const sr = props.sellerRequest || {};
    return sr.lot_area_sqm ?? sr.lot_area ?? sr.property_area ?? null;
});

const propertyTypes = computed(() => {
    const t = props.sellerRequest?.property_type;
    if (!t) return [];
    return Array.isArray(t) ? t : [t];
});
const customPropertyType = computed(
    () => props.sellerRequest?.custom_property_type || null
);

// Utilities
const utilities = computed(() => ({
    road_access: !!props.sellerRequest?.road_access,
    water_source: !!props.sellerRequest?.water_source,
    electricity_available: !!props.sellerRequest?.electricity_available,
    internet_available: !!props.sellerRequest?.internet_available,
}));

// Documents (computed later using robust parser)
// Placeholders (will be redefined below after parsers)
let propertyDocuments = computed(() => []);
let ownershipDocuments = computed(() => []);

// Preferred broker
const preferredBrokerId = computed(
    () => props.sellerRequest?.preferred_broker_id ?? null
);
const preferredBrokerName = computed(() => {
    if (!preferredBrokerId.value || !Array.isArray(props.brokers)) return null;
    const b = props.brokers.find((x) => x.id === preferredBrokerId.value);
    return b?.name || `Broker #${preferredBrokerId.value}`;
});

// Coordinates & maps
const hasCoordinates = computed(
    () =>
        props.sellerRequest?.coordinates_lat != null &&
        props.sellerRequest?.coordinates_lng != null
);
const mapsUrl = computed(() =>
    hasCoordinates.value
        ? `https://www.google.com/maps?q=${props.sellerRequest.coordinates_lat},${props.sellerRequest.coordinates_lng}`
        : null
);

// Helper to resolve file URLs
const fileUrl = (path) => {
    if (!path) return "#";
    if (typeof path !== "string") return "#";
    // Normalize leading 'storage/' (without slash) or duplicate '/storage/storage'
    let p = path.trim();
    // Remove any leading ./
    p = p.replace(/^\.\/?/, "");
    // If it already starts with /storage/ or http just return
    if (p.startsWith("http")) return p;
    if (p.startsWith("/storage/")) return p;
    if (p.startsWith("storage/"))
        return "/storage/" + p.substring("storage/".length);
    if (p.startsWith("/")) return p; // assume absolute served path
    return "/storage/" + p;
};

// Combine images from uploaded_images and images for compatibility
const parseImageSource = (val) => {
    if (!val) return [];
    // Already array
    if (Array.isArray(val)) return val.filter(Boolean);
    // JSON encoded string (single or double-encoded)
    if (typeof val === "string") {
        const trimmed = val.trim();
        // Handle accidental double encoding like "[\"path\"]"
        let attempt = trimmed;
        try {
            const first = JSON.parse(attempt);
            if (Array.isArray(first)) return first.filter(Boolean);
            // If first parse gives a string that itself looks like JSON array
            if (typeof first === "string" && first.startsWith("[")) {
                try {
                    const second = JSON.parse(first);
                    if (Array.isArray(second)) return second.filter(Boolean);
                } catch (e2) {
                    /* ignore */
                }
            }
        } catch (e) {
            // Not JSON - treat as single path
            if (attempt.length > 0) return [attempt];
        }
        return [];
    }
    // Object of keyed paths
    if (typeof val === "object") {
        return Object.values(val).filter(
            (v) => typeof v === "string" && v.length > 0
        );
    }
    return [];
};

const dedupe = (arr) => Array.from(new Set(arr));

const allImages = computed(() => {
    const sources = [
        parseImageSource(props.sellerRequest?.uploaded_images),
        parseImageSource(props.sellerRequest?.images),
        parseImageSource(props.sellerRequest?.property_images), // legacy safeguard
    ];
    return dedupe(sources.flat().filter(Boolean));
});

const currentImage = computed(() =>
    allImages.value.length ? allImages.value[imageIndex.value] : null
);
const openImageModal = (idx) => {
    if (!allImages.value.length) return;
    imageIndex.value = Math.min(Math.max(idx, 0), allImages.value.length - 1);
    showImageModal.value = true;
};
const closeImageModal = () => {
    showImageModal.value = false;
};
const nextImage = () => {
    if (!allImages.value.length) return;
    imageIndex.value = (imageIndex.value + 1) % allImages.value.length;
};
const prevImage = () => {
    if (!allImages.value.length) return;
    imageIndex.value =
        (imageIndex.value - 1 + allImages.value.length) %
        allImages.value.length;
};

// Keyboard navigation for lightbox (accessibility + UX)
const handleKey = (e) => {
    if (!showImageModal.value) return;
    if (e.key === "Escape") {
        closeImageModal();
    } else if (e.key === "ArrowRight") {
        nextImage();
    } else if (e.key === "ArrowLeft") {
        prevImage();
    }
};

onMounted(() => {
    window.addEventListener("keydown", handleKey);
});

onBeforeUnmount(() => {
    window.removeEventListener("keydown", handleKey);
});

// Swipe gesture support for lightbox (mobile usability)
const touchStartX = ref(null);
const touchStartY = ref(null);
const onTouchStart = (e) => {
    if (!showImageModal.value || !e.changedTouches?.length) return;
    const t = e.changedTouches[0];
    touchStartX.value = t.screenX;
    touchStartY.value = t.screenY;
};
const onTouchEnd = (e) => {
    if (
        !showImageModal.value ||
        touchStartX.value == null ||
        !e.changedTouches?.length
    )
        return;
    const t = e.changedTouches[0];
    const dx = t.screenX - touchStartX.value;
    const dy = t.screenY - touchStartY.value;
    // Horizontal swipe threshold, ignore mostly vertical drags
    if (Math.abs(dx) > 50 && Math.abs(dy) < 80) {
        if (dx < 0) {
            nextImage();
        } else {
            prevImage();
        }
    }
    touchStartX.value = null;
    touchStartY.value = null;
};

// Removed tab system for simplified single-page layout

// Re-define Documents using robust parser (supports arrays, JSON strings, and objects)
propertyDocuments = computed(() =>
    parseImageSource(props.sellerRequest?.property_documents)
);
ownershipDocuments = computed(() =>
    parseImageSource(props.sellerRequest?.ownership_documents)
);

// Derive enhanced metadata for documents
const enhanceDocs = (docs) =>
    docs.map((orig) => {
        const url = fileUrl(orig);
        const name = (() => {
            try {
                const base = orig.split("?")[0].split("#")[0];
                const part =
                    base.substring(base.lastIndexOf("/") + 1) || "document";
                return decodeURIComponent(part);
            } catch {
                return "document";
            }
        })();
        const ext = (
            name.includes(".") ? name.split(".").pop() : ""
        ).toLowerCase();
        const type = [
            "jpg",
            "jpeg",
            "png",
            "gif",
            "webp",
            "bmp",
            "tiff",
        ].includes(ext)
            ? "image"
            : ["pdf"].includes(ext)
            ? "pdf"
            : ["doc", "docx", "odt", "rtf"].includes(ext)
            ? "doc"
            : "file";
        return { original: orig, url, name, ext, type };
    });

const propertyDocsEnhanced = computed(() =>
    enhanceDocs(propertyDocuments.value)
);
const ownershipDocsEnhanced = computed(() =>
    enhanceDocs(ownershipDocuments.value)
);

const iconForDoc = (doc) => {
    switch (doc.type) {
        case "image":
            return PhotoIcon;
        case "pdf":
            return DocumentTextIcon;
        case "doc":
            return DocumentTextIcon;
        default:
            return PaperClipIcon;
    }
};

// Actions
const approveRequest = () => {
    approveForm.post(
        route("seller-requests.update-status", props.sellerRequest.id),
        {
            onStart: () => {
                console.log(
                    "Approving seller request",
                    props.sellerRequest.id,
                    { payload: approveForm }
                );
            },
            onSuccess: () => {
                showApproveModal.value = false;
                approveForm.reset();
                console.log("Approve success");
            },
            onError: (errors) => {
                console.error("Approve errors", errors);
                try {
                    alert("Approval failed: " + JSON.stringify(errors));
                } catch (e) {
                    /* ignore */
                }
            },
            onFinish: () => {
                approveForm.processing = false;
                console.log("Approve finished");
            },
        }
    );
};

const rejectRequest = () => {
    rejectForm.post(
        route("seller-requests.update-status", props.sellerRequest.id),
        {
            onStart: () => {
                console.log(
                    "Rejecting seller request",
                    props.sellerRequest.id,
                    { payload: rejectForm }
                );
            },
            onSuccess: () => {
                showRejectModal.value = false;
                rejectForm.reset();
                console.log("Reject success");
            },
            onError: (errors) => {
                console.error("Reject errors", errors);
                try {
                    alert("Rejection failed: " + JSON.stringify(errors));
                } catch (e) {
                    /* ignore */
                }
            },
            onFinish: () => {
                rejectForm.processing = false;
                console.log("Reject finished");
            },
        }
    );
};

const deleteRequest = () => {
    deleteForm.delete(
        route("seller-requests.destroy", props.sellerRequest.id),
        {
            onSuccess: () => {
                showDeleteModal.value = false;
            },
        }
    );
};
</script>

<template>
    <Head :title="`Seller Request - ${sellerRequest.property_title}`" />
    <ModernDashboardLayout>
        <div class="space-y-8">
            <!-- Header Section -->
            <div
                class="rounded-2xl border border-neutral-200 bg-white shadow-sm"
            >
                <div class="p-6">
                    <!-- Back Button -->
                    <div class="mb-8">
                        <Link
                            :href="route('seller-requests.index')"
                            class="inline-flex items-center gap-3 text-neutral-600 hover:text-coconut-600 transition-all duration-300 group"
                        >
                            <div
                                class="p-2 rounded-xl bg-white shadow-sm group-hover:shadow-md group-hover:scale-105 transition-all duration-300"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 19l-7-7 7-7"
                                    />
                                </svg>
                            </div>
                            <span class="font-medium"
                                >Back to Seller Requests</span
                            >
                        </Link>
                    </div>

                    <div
                        class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6"
                    >
                        <div class="flex items-start gap-6">
                            <div
                                class="w-16 h-16 rounded-xl bg-neutral-100 flex items-center justify-center"
                            >
                                <BuildingOfficeIcon
                                    class="w-8 h-8 text-neutral-500"
                                />
                            </div>
                            <div>
                                <div
                                    class="flex items-center gap-2 mb-2 text-sm text-neutral-500"
                                >
                                    <span>{{
                                        formatDate(
                                            sellerRequest.created_at
                                        ).split(",")[0]
                                    }}</span>
                                    <span v-if="sellerRequest.reviewed_at"
                                        >• Reviewed</span
                                    >
                                </div>
                                <h1
                                    class="text-3xl font-semibold tracking-tight text-neutral-900 mb-3 leading-tight"
                                >
                                    {{ sellerRequest.property_title }}
                                </h1>
                                <div class="flex flex-wrap items-center gap-3">
                                    <div
                                        class="flex items-center gap-2 px-3 py-1 rounded-md bg-neutral-100"
                                    >
                                        <MapPinIcon
                                            class="w-4 h-4 text-neutral-500"
                                        />
                                        <span
                                            class="text-sm font-medium truncate max-w-[13rem]"
                                            :title="
                                                formatLocation(sellerRequest)
                                            "
                                            >{{
                                                formatLocation(sellerRequest)
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        :class="[
                                            'inline-flex items-center gap-1 px-3 py-1 rounded-md text-xs font-medium border',
                                            statusColor,
                                        ]"
                                    >
                                        <component
                                            :is="statusIcon"
                                            class="w-4 h-4"
                                        />
                                        {{
                                            sellerRequest.status
                                                .replace("_", " ")
                                                .toUpperCase()
                                        }}
                                    </div>
                                    <div
                                        v-if="preferredBrokerName"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-md bg-neutral-100 text-neutral-600 text-xs font-medium"
                                    >
                                        <UserIcon class="w-4 h-4" />
                                        {{ preferredBrokerName }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-4">
                            <Link :href="route('seller-requests.index')">
                                <ModernButton
                                    variant="outline"
                                    size="md"
                                    class="shadow-lg hover:shadow-xl transition-all duration-300"
                                    >← Back to List</ModernButton
                                >
                            </Link>
                            <!-- Broker actions: Only show if assigned to this broker and status is pending/under_review/assigned -->
                            <template
                                v-if="
                                    userRole === 'broker' &&
                                    sellerRequest.assigned_broker_id ===
                                        page.props.auth.user.id &&
                                    (sellerRequest.status === 'pending' ||
                                        sellerRequest.status ===
                                            'under_review' ||
                                        sellerRequest.status === 'assigned')
                                "
                            >
                                <ModernButton
                                    @click="showApproveModal = true"
                                    variant="primary"
                                    size="md"
                                    class="flex items-center gap-3 shadow-lg hover:shadow-xl transition-all duration-300"
                                >
                                    <CheckCircleIcon class="w-5 h-5" />Approve
                                </ModernButton>
                                <ModernButton
                                    @click="showRejectModal = true"
                                    variant="danger"
                                    size="md"
                                    class="flex items-center gap-3 shadow-lg hover:shadow-xl transition-all duration-300"
                                >
                                    <XCircleIcon class="w-5 h-5" />Reject
                                </ModernButton>
                            </template>
                            <!-- Admin actions: assign, edit, delete (if needed, keep as before) -->
                            <template v-if="userRole === 'admin'">
                                <ModernButton
                                    v-if="
                                        sellerRequest.status === 'pending' ||
                                        sellerRequest.status === 'under_review'
                                    "
                                    @click="showApproveModal = true"
                                    variant="primary"
                                    size="sm"
                                    class="flex items-center gap-2"
                                >
                                    <CheckCircleIcon class="w-4 h-4" />Approve
                                </ModernButton>
                                <ModernButton
                                    v-if="
                                        sellerRequest.status === 'pending' ||
                                        sellerRequest.status === 'under_review'
                                    "
                                    @click="showRejectModal = true"
                                    variant="danger"
                                    size="sm"
                                    class="flex items-center gap-2"
                                >
                                    <XCircleIcon class="w-4 h-4" />Reject
                                </ModernButton>
                                <ModernButton
                                    @click="showAssignModal = true"
                                    variant="primary"
                                    size="sm"
                                    class="flex items-center gap-2"
                                >
                                    <UserIcon class="w-4 h-4" />Assign Broker
                                </ModernButton>
                                <Link
                                    :href="
                                        route(
                                            'seller-requests.edit',
                                            sellerRequest.id
                                        )
                                    "
                                    ><ModernButton
                                        variant="outline"
                                        size="sm"
                                        class="flex items-center gap-2"
                                        ><PencilIcon
                                            class="w-4 h-4"
                                        />Edit</ModernButton
                                    ></Link
                                >
                                <ModernButton
                                    @click="showDeleteModal = true"
                                    variant="danger"
                                    size="sm"
                                    class="flex items-center gap-2"
                                    ><TrashIcon
                                        class="w-4 h-4"
                                    />Delete</ModernButton
                                >
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Single Column Layout -->
            <div class="space-y-8 max-w-5xl mx-auto">
                <!-- Sticky summary bar for quick metrics (appears after scroll) -->
                <div
                    class="sticky top-0 z-30 -mx-4 px-4 py-3 bg-white/90 backdrop-blur border-b border-neutral-200 flex items-center gap-4 shadow-sm"
                    v-if="sellerRequest"
                >
                    <h2
                        class="text-sm font-semibold text-neutral-800 truncate max-w-[12rem]"
                    >
                        {{ sellerRequest.property_title }}
                    </h2>
                    <div
                        class="flex items-center gap-2 text-xs text-neutral-600"
                    >
                        <span>{{ formatLocation(sellerRequest) }}</span>
                        <span
                            >•
                            {{ formatPrice(sellerRequest.asking_price) }}</span
                        >
                        <span v-if="normalizedLotArea"
                            >• {{ formatArea(normalizedLotArea) }}</span
                        >
                    </div>
                    <div
                        :class="[
                            'ml-auto inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium border',
                            statusColor,
                        ]"
                    >
                        <component :is="statusIcon" class="w-4 h-4" />
                        {{
                            sellerRequest.status.replace("_", " ").toUpperCase()
                        }}
                    </div>
                </div>

                <!-- Overview -->
                <div
                    class="bg-white rounded-2xl border border-neutral-200 shadow-sm"
                >
                    <div
                        class="p-6 border-b border-neutral-200 flex items-center gap-3"
                    >
                        <BuildingOfficeIcon class="w-6 h-6 text-neutral-600" />
                        <h2 class="text-xl font-semibold text-neutral-900">
                            Overview
                        </h2>
                    </div>
                    <div class="p-6 space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-4 md:col-span-2">
                                <div
                                    class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                                >
                                    <label
                                        class="text-xs font-semibold text-neutral-500 uppercase tracking-wider"
                                        >Description</label
                                    >
                                    <p
                                        class="text-neutral-700 mt-3 leading-relaxed"
                                    >
                                        {{
                                            sellerRequest.property_description ||
                                            "No description provided."
                                        }}
                                    </p>
                                </div>
                                <div
                                    v-if="
                                        propertyTypes.length ||
                                        customPropertyType
                                    "
                                    class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                                >
                                    <label
                                        class="text-xs font-semibold text-neutral-500 uppercase tracking-wider"
                                        >Property Type(s)</label
                                    >
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <span
                                            v-for="type in propertyTypes"
                                            :key="type"
                                            class="px-3 py-1 rounded-full bg-neutral-200/60 text-neutral-700 text-xs font-medium"
                                            >{{
                                                type.replaceAll("_", " ")
                                            }}</span
                                        >
                                        <span
                                            v-if="customPropertyType"
                                            class="px-3 py-1 rounded-full bg-neutral-200/60 text-neutral-700 text-xs font-medium"
                                            >{{ customPropertyType }}</span
                                        >
                                    </div>
                                </div>
                                <div
                                    v-if="
                                        sellerRequest.features &&
                                        sellerRequest.features.length
                                    "
                                    class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                                >
                                    <label
                                        class="text-xs font-semibold text-neutral-500 uppercase tracking-wider"
                                        >Features</label
                                    >
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <span
                                            v-for="feature in sellerRequest.features"
                                            :key="feature"
                                            class="px-3 py-1 rounded-full bg-neutral-200/60 text-neutral-700 text-xs font-medium"
                                            >{{ feature }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div
                                    class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                                >
                                    <div
                                        class="text-xs text-neutral-500 font-semibold uppercase"
                                    >
                                        Asking Price
                                    </div>
                                    <div
                                        class="text-lg font-semibold mt-1 text-neutral-900"
                                    >
                                        {{
                                            formatPrice(
                                                sellerRequest.asking_price
                                            )
                                        }}
                                    </div>
                                </div>
                                <div
                                    class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                                    v-if="normalizedLotArea"
                                >
                                    <div
                                        class="text-xs text-neutral-500 font-semibold uppercase"
                                    >
                                        Lot Area
                                    </div>
                                    <div
                                        class="text-lg font-semibold mt-1 text-neutral-900"
                                    >
                                        {{ formatArea(normalizedLotArea) }}
                                    </div>
                                </div>
                                <div
                                    class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                                >
                                    <div
                                        class="text-xs text-neutral-500 font-semibold uppercase"
                                    >
                                        Location
                                    </div>
                                    <div
                                        class="text-sm font-medium mt-1 text-neutral-800"
                                    >
                                        {{ formatLocation(sellerRequest) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div
                                class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                            >
                                <div
                                    class="text-xs text-neutral-500 font-semibold uppercase"
                                >
                                    Title Type
                                </div>
                                <div class="text-sm text-neutral-800 mt-1">
                                    {{ sellerRequest.title_type || "—" }}
                                </div>
                            </div>
                            <div
                                class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                            >
                                <div
                                    class="text-xs text-neutral-500 font-semibold uppercase"
                                >
                                    Title Number
                                </div>
                                <div class="text-sm text-neutral-800 mt-1">
                                    {{ sellerRequest.title_number || "—" }}
                                </div>
                            </div>
                            <div
                                class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                            >
                                <div
                                    class="text-xs text-neutral-500 font-semibold uppercase"
                                >
                                    Zoning
                                </div>
                                <div class="text-sm text-neutral-800 mt-1">
                                    {{
                                        sellerRequest.zoning_classification ||
                                        "—"
                                    }}
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                        >
                            <label
                                class="text-xs font-semibold text-neutral-500 uppercase tracking-wider"
                                >Utilities & Access</label
                            >
                            <div class="flex flex-wrap gap-2 mt-3">
                                <span
                                    :class="[
                                        'px-2.5 py-1 rounded-full text-[11px] font-medium border',
                                        utilities.road_access
                                            ? 'bg-green-50 text-green-700 border-green-200'
                                            : 'bg-neutral-100 text-neutral-500 border-neutral-200',
                                    ]"
                                    >Road
                                    {{
                                        utilities.road_access ? "✓" : "—"
                                    }}</span
                                >
                                <span
                                    :class="[
                                        'px-2.5 py-1 rounded-full text-[11px] font-medium border',
                                        utilities.water_source
                                            ? 'bg-green-50 text-green-700 border-green-200'
                                            : 'bg-neutral-100 text-neutral-500 border-neutral-200',
                                    ]"
                                    >Water
                                    {{
                                        utilities.water_source ? "✓" : "—"
                                    }}</span
                                >
                                <span
                                    :class="[
                                        'px-2.5 py-1 rounded-full text-[11px] font-medium border',
                                        utilities.electricity_available
                                            ? 'bg-green-50 text-green-700 border-green-200'
                                            : 'bg-neutral-100 text-neutral-500 border-neutral-200',
                                    ]"
                                    >Electricity
                                    {{
                                        utilities.electricity_available
                                            ? "✓"
                                            : "—"
                                    }}</span
                                >
                                <span
                                    :class="[
                                        'px-2.5 py-1 rounded-full text-[11px] font-medium border',
                                        utilities.internet_available
                                            ? 'bg-green-50 text-green-700 border-green-200'
                                            : 'bg-neutral-100 text-neutral-500 border-neutral-200',
                                    ]"
                                    >Internet
                                    {{
                                        utilities.internet_available ? "✓" : "—"
                                    }}</span
                                >
                            </div>
                        </div>
                        <div
                            v-if="
                                sellerRequest.nearby_landmarks || hasCoordinates
                            "
                            class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                        >
                            <label
                                class="text-xs font-semibold text-neutral-500 uppercase tracking-wider"
                                >Location Extras</label
                            >
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-3"
                            >
                                <div v-if="sellerRequest.nearby_landmarks">
                                    <div
                                        class="text-[11px] text-neutral-500 font-semibold uppercase"
                                    >
                                        Nearby Landmarks
                                    </div>
                                    <div class="text-sm text-neutral-700 mt-1">
                                        {{ sellerRequest.nearby_landmarks }}
                                    </div>
                                </div>
                                <div v-if="hasCoordinates">
                                    <div
                                        class="text-[11px] text-neutral-500 font-semibold uppercase"
                                    >
                                        Coordinates
                                    </div>
                                    <div class="text-sm text-neutral-700 mt-1">
                                        {{ sellerRequest.coordinates_lat }},
                                        {{ sellerRequest.coordinates_lng }}
                                        <span v-if="mapsUrl">
                                            ·
                                            <a
                                                :href="mapsUrl"
                                                target="_blank"
                                                class="text-coconut-600 hover:underline"
                                                >Maps</a
                                            ></span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media -->
                <div
                    v-if="allImages.length"
                    class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6"
                >
                    <div class="flex items-center gap-3 mb-6">
                        <EyeIcon class="w-6 h-6 text-neutral-600" />
                        <h2 class="text-2xl font-semibold text-neutral-900">
                            Property Images
                        </h2>
                    </div>

                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                    >
                        <button
                            v-for="(image, index) in allImages"
                            :key="index + '-' + image"
                            type="button"
                            @click="openImageModal(index)"
                            class="relative aspect-square bg-neutral-100 rounded-xl overflow-hidden group cursor-pointer border border-neutral-200 focus:outline-none focus:ring-2 focus:ring-neutral-400"
                        >
                            <img
                                :src="fileUrl(image)"
                                :alt="`Property image ${index + 1}`"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                @error="
                                    (e) => {
                                        console.warn(
                                            'Image failed to load, attempting fallback',
                                            image
                                        );
                                        if (image.startsWith('/storage/')) {
                                            const raw = image.replace(
                                                '/storage/',
                                                ''
                                            );
                                            e.target.src = '/storage/' + raw;
                                        } else if (
                                            !image.startsWith('http') &&
                                            !image.startsWith('/')
                                        ) {
                                            e.target.src = '/storage/' + image;
                                        }
                                        e.target.onerror = () => {
                                            e.target.classList.add(
                                                'opacity-40'
                                            );
                                            e.target.alt = 'Missing image';
                                        };
                                    }
                                "
                            />
                            <span
                                class="absolute bottom-2 right-2 px-2 py-0.5 text-[10px] font-medium rounded bg-black/50 text-white opacity-0 group-hover:opacity-100 transition"
                                >View</span
                            >
                        </button>
                    </div>
                    <div
                        v-if="!allImages.length"
                        class="text-neutral-500 text-sm mt-4"
                    >
                        No images were submitted.
                    </div>
                    <div v-else class="mt-6 text-xs text-neutral-500">
                        Displaying {{ allImages.length }} image(s) sourced from
                        {{
                            sellerRequest.uploaded_images
                                ? "uploaded_images"
                                : ""
                        }}{{
                            sellerRequest.uploaded_images &&
                            sellerRequest.images
                                ? " + "
                                : ""
                        }}{{ sellerRequest.images ? "images" : "" }} fields.
                    </div>
                </div>

                <!-- Image Lightbox Modal -->
                <teleport to="body">
                    <transition name="fade">
                        <div
                            v-if="showImageModal"
                            class="fixed inset-0 z-[120] flex items-center justify-center bg-black/85"
                            @touchstart.passive="onTouchStart"
                            @touchend.passive="onTouchEnd"
                        >
                            <button
                                @click="closeImageModal"
                                class="absolute top-4 right-4 p-2 rounded-full bg-white/10 hover:bg-white/20 text-white transition"
                                aria-label="Close image viewer"
                            >
                                <XMarkIcon class="w-6 h-6" />
                            </button>
                            <button
                                @click="prevImage"
                                class="absolute left-4 md:left-8 p-2 rounded-md bg-white/10 hover:bg-white/20 text-white transition"
                                aria-label="Previous image"
                            >
                                <ChevronLeftIcon class="w-6 h-6" />
                            </button>
                            <div class="max-w-6xl w-full px-4 md:px-8">
                                <div
                                    class="relative rounded-xl overflow-hidden shadow-2xl shadow-black/60 bg-neutral-900/40 border border-white/10"
                                >
                                    <img
                                        :src="fileUrl(currentImage)"
                                        :alt="
                                            'Viewing image ' + (imageIndex + 1)
                                        "
                                        class="w-full max-h-[80vh] object-contain"
                                    />
                                    <div
                                        class="absolute bottom-0 inset-x-0 bg-black/60 p-3 flex items-center justify-between text-white text-xs"
                                    >
                                        <div>
                                            Image {{ imageIndex + 1 }} /
                                            {{ allImages.length }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a
                                                :href="fileUrl(currentImage)"
                                                target="_blank"
                                                class="px-2 py-1 bg-white/10 hover:bg-white/20 rounded"
                                                >Open</a
                                            >
                                            <button
                                                @click="closeImageModal"
                                                class="px-2 py-1 bg-white/10 hover:bg-white/20 rounded"
                                            >
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button
                                @click="nextImage"
                                class="absolute right-4 md:right-8 p-2 rounded-md bg-white/10 hover:bg-white/20 text-white transition"
                                aria-label="Next image"
                            >
                                <ChevronRightIcon class="w-6 h-6" />
                            </button>
                        </div>
                    </transition>
                </teleport>

                <!-- Supporting Documents -->
                <div
                    v-if="
                        propertyDocsEnhanced.length ||
                        ownershipDocsEnhanced.length
                    "
                    class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6"
                >
                    <div class="flex items-center gap-4 mb-6">
                        <DocumentTextIcon class="w-7 h-7 text-neutral-600" />
                        <h2 class="text-2xl font-semibold text-neutral-900">
                            Supporting Documents
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Property Docs -->
                        <div
                            v-if="propertyDocsEnhanced.length"
                            class="bg-neutral-50 rounded-xl p-5 border border-neutral-200 flex flex-col"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="text-sm font-medium text-neutral-700"
                                >
                                    Property Documents
                                </div>
                                <div class="text-xs text-neutral-500">
                                    {{ propertyDocsEnhanced.length }} file(s)
                                </div>
                            </div>
                            <ul class="divide-y divide-neutral-200">
                                <li
                                    v-for="(doc, idx) in propertyDocsEnhanced"
                                    :key="'prop-' + idx"
                                    class="py-3 flex items-start gap-4 group"
                                >
                                    <component
                                        :is="iconForDoc(doc)"
                                        class="w-5 h-5 mt-0.5 text-neutral-600"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <a
                                                :href="doc.url"
                                                target="_blank"
                                                class="font-medium text-neutral-800 hover:text-neutral-900 hover:underline break-all"
                                            >
                                                {{ doc.name }}
                                            </a>
                                            <span
                                                class="px-1.5 py-0.5 rounded bg-neutral-100 text-neutral-600 text-[10px] font-medium uppercase border border-neutral-200"
                                            >
                                                {{ doc.ext || "FILE" }}
                                            </span>
                                        </div>
                                        <div
                                            class="text-xs text-neutral-500 mt-1"
                                        >
                                            <span
                                                class="font-mono truncate"
                                                :title="doc.original"
                                                >{{ doc.original }}</span
                                            >
                                        </div>
                                    </div>
                                    <a
                                        :href="doc.url"
                                        download
                                        class="p-2 rounded-md hover:bg-neutral-100 text-neutral-700 border border-neutral-200 transition"
                                        :title="'Download ' + doc.name"
                                    >
                                        <ArrowDownTrayIcon class="w-4 h-4" />
                                    </a>
                                </li>
                            </ul>
                            <div
                                v-if="
                                    propertyDocsEnhanced.some(
                                        (d) => d.type === 'image'
                                    )
                                "
                                class="mt-3 text-xs text-neutral-500"
                            >
                                Images open directly; other files may download
                                or open externally.
                            </div>
                        </div>
                        <!-- Ownership Docs -->
                        <div
                            v-if="ownershipDocsEnhanced.length"
                            class="bg-neutral-50 rounded-xl p-5 border border-neutral-200 flex flex-col"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="text-sm font-medium text-neutral-700"
                                >
                                    Ownership Documents
                                </div>
                                <div class="text-xs text-neutral-500">
                                    {{ ownershipDocsEnhanced.length }} file(s)
                                </div>
                            </div>
                            <ul class="divide-y divide-neutral-200">
                                <li
                                    v-for="(doc, idx) in ownershipDocsEnhanced"
                                    :key="'own-' + idx"
                                    class="py-3 flex items-start gap-4 group"
                                >
                                    <component
                                        :is="iconForDoc(doc)"
                                        class="w-5 h-5 mt-0.5 text-neutral-600"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <a
                                                :href="doc.url"
                                                target="_blank"
                                                class="font-medium text-neutral-800 hover:text-neutral-900 hover:underline break-all"
                                            >
                                                {{ doc.name }}
                                            </a>
                                            <span
                                                class="px-1.5 py-0.5 rounded bg-neutral-100 text-neutral-600 text-[10px] font-medium uppercase border border-neutral-200"
                                            >
                                                {{ doc.ext || "FILE" }}
                                            </span>
                                        </div>
                                        <div
                                            class="text-xs text-neutral-500 mt-1"
                                        >
                                            <span
                                                class="font-mono truncate"
                                                :title="doc.original"
                                                >{{ doc.original }}</span
                                            >
                                        </div>
                                    </div>
                                    <a
                                        :href="doc.url"
                                        download
                                        class="p-2 rounded-md hover:bg-neutral-100 text-neutral-700 border border-neutral-200 transition"
                                        :title="'Download ' + doc.name"
                                    >
                                        <ArrowDownTrayIcon class="w-4 h-4" />
                                    </a>
                                </li>
                            </ul>
                            <div
                                v-if="
                                    ownershipDocsEnhanced.some(
                                        (d) => d.type === 'image'
                                    )
                                "
                                class="mt-3 text-xs text-neutral-500"
                            >
                                Images open directly; other formats
                                download/open externally.
                            </div>
                        </div>
                    </div>
                    <div
                        class="mt-4 text-xs text-neutral-500"
                        v-if="
                            propertyDocsEnhanced.length +
                                ownershipDocsEnhanced.length >
                            0
                        "
                    >
                        Total files:
                        {{
                            propertyDocsEnhanced.length +
                            ownershipDocsEnhanced.length
                        }}
                    </div>
                </div>

                <!-- Seller Information -->
                <div
                    class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6"
                >
                    <div class="flex items-center gap-3 mb-6">
                        <UserIcon class="w-6 h-6 text-neutral-600" />
                        <h2 class="text-xl font-semibold text-neutral-900">
                            Seller Information
                        </h2>
                    </div>

                    <div class="space-y-6">
                        <div
                            class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                        >
                            <label
                                class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                >Name</label
                            >
                            <p class="text-xl font-bold text-neutral-900 mt-2">
                                {{ contactName }}
                            </p>
                        </div>

                        <div
                            class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                        >
                            <label
                                class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                >Email</label
                            >
                            <div
                                class="text-neutral-700 flex items-center gap-3 mt-2"
                            >
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <EnvelopeIcon
                                        class="w-5 h-5 text-green-600"
                                    />
                                </div>
                                <span class="font-medium">{{
                                    contactEmail
                                }}</span>
                            </div>
                        </div>

                        <div
                            v-if="contactPhone"
                            class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                        >
                            <label
                                class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                >Phone</label
                            >
                            <div
                                class="text-neutral-700 flex items-center gap-3 mt-2"
                            >
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <PhoneIcon class="w-5 h-5 text-blue-600" />
                                </div>
                                <span class="font-medium">{{
                                    contactPhone
                                }}</span>
                            </div>
                        </div>

                        <div
                            v-if="sellerRequest.address"
                            class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                        >
                            <label
                                class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                >Address</label
                            >
                            <p
                                class="text-neutral-700 mt-2 font-medium leading-relaxed"
                            >
                                {{ sellerRequest.address }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Broker Preference -->
                <div
                    v-if="
                        preferredBrokerId && !sellerRequest.assigned_broker_id
                    "
                    class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6"
                >
                    <div class="flex items-center gap-3 mb-6">
                        <UserIcon class="w-6 h-6 text-neutral-600" />
                        <h2 class="text-xl font-semibold text-neutral-900">
                            Preferred Broker
                        </h2>
                    </div>
                    <div
                        class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                    >
                        <div class="text-sm text-neutral-600">
                            The seller selected this broker during submission.
                        </div>
                        <div class="text-neutral-900 font-semibold mt-2">
                            {{
                                preferredBrokerName ||
                                "Broker #" + preferredBrokerId
                            }}
                        </div>
                    </div>
                </div>

                <!-- Request Timeline -->
                <div
                    class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6"
                >
                    <div class="flex items-center gap-3 mb-6">
                        <CalendarIcon class="w-6 h-6 text-neutral-600" />
                        <h2 class="text-xl font-semibold text-neutral-900">
                            Request Timeline
                        </h2>
                    </div>

                    <div class="space-y-6">
                        <div
                            class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                        >
                            <label
                                class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                >Submitted</label
                            >
                            <p class="text-neutral-700 mt-2 font-medium">
                                {{ formatDate(sellerRequest.created_at) }}
                            </p>
                        </div>

                        <div
                            v-if="sellerRequest.reviewed_at"
                            class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                        >
                            <label
                                class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                >Reviewed</label
                            >
                            <p class="text-neutral-700 mt-2 font-medium">
                                {{ formatDate(sellerRequest.reviewed_at) }}
                            </p>
                        </div>

                        <div
                            v-if="sellerRequest.listed_at"
                            class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                        >
                            <label
                                class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                >Listed</label
                            >
                            <p class="text-neutral-700 mt-2 font-medium">
                                {{ formatDate(sellerRequest.listed_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Admin Notes -->
                <div
                    v-if="
                        sellerRequest.admin_notes ||
                        sellerRequest.rejection_reason
                    "
                    class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6"
                >
                    <div class="flex items-center gap-3 mb-6">
                        <PencilIcon class="w-6 h-6 text-neutral-600" />
                        <h2 class="text-xl font-semibold text-neutral-900">
                            Admin Notes
                        </h2>
                    </div>

                    <div class="space-y-6">
                        <div
                            v-if="sellerRequest.admin_notes"
                            class="bg-neutral-50 rounded-xl p-5 border border-neutral-200"
                        >
                            <label
                                class="text-sm font-semibold text-neutral-600 uppercase tracking-wider"
                                >Notes</label
                            >
                            <div
                                class="text-neutral-700 mt-4 p-4 bg-neutral-100 rounded-lg border border-neutral-200 leading-relaxed"
                            >
                                {{ sellerRequest.admin_notes }}
                            </div>
                        </div>

                        <div
                            v-if="sellerRequest.rejection_reason"
                            class="bg-neutral-50 rounded-xl p-5 border border-red-200"
                        >
                            <label
                                class="text-sm font-semibold text-red-600 uppercase tracking-wider"
                                >Rejection Reason</label
                            >
                            <div
                                class="text-red-700 mt-4 p-4 bg-red-50 rounded-lg border border-red-200 leading-relaxed"
                            >
                                {{ sellerRequest.rejection_reason }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Consents -->
                <div
                    v-if="
                        sellerRequest.marketing_consent != null ||
                        sellerRequest.terms_accepted != null
                    "
                    class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6 mb-8"
                >
                    <div class="flex items-center gap-3 mb-6">
                        <CheckCircleIcon class="w-6 h-6 text-neutral-600" />
                        <h2 class="text-xl font-semibold text-neutral-900">
                            Consents
                        </h2>
                    </div>
                    <div class="space-y-4">
                        <div
                            class="bg-neutral-50 rounded-xl p-4 border border-neutral-200 flex items-center justify-between"
                        >
                            <div class="text-neutral-700 font-medium">
                                Marketing Updates
                            </div>
                            <div
                                :class="[
                                    'px-3 py-1 rounded-full text-sm font-semibold border',
                                    sellerRequest.marketing_consent
                                        ? 'bg-green-50 text-green-700 border-green-200'
                                        : 'bg-neutral-50 text-neutral-500 border-neutral-200',
                                ]"
                            >
                                {{
                                    sellerRequest.marketing_consent
                                        ? "Yes"
                                        : "No"
                                }}
                            </div>
                        </div>
                        <div
                            class="bg-neutral-50 rounded-xl p-4 border border-neutral-200 flex items-center justify-between"
                        >
                            <div class="text-neutral-700 font-medium">
                                Terms Accepted
                            </div>
                            <div
                                :class="[
                                    'px-3 py-1 rounded-full text-sm font-semibold border',
                                    sellerRequest.terms_accepted
                                        ? 'bg-green-50 text-green-700 border-green-200'
                                        : 'bg-neutral-50 text-neutral-500 border-neutral-200',
                                ]"
                            >
                                {{
                                    sellerRequest.terms_accepted ? "Yes" : "No"
                                }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <Modal :show="showApproveModal" @close="showApproveModal = false">
            <div class="p-8 bg-gradient-to-br from-white to-green-50">
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="p-3 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl shadow-lg"
                    >
                        <CheckCircleIcon class="w-8 h-8 text-white" />
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900">
                        Approve Seller Request
                    </h3>
                </div>

                <form @submit.prevent="approveRequest" class="space-y-6">
                    <div
                        v-if="userRole === 'admin'"
                        class="bg-white rounded-2xl p-6 shadow-lg border border-neutral-100"
                    >
                        <label
                            class="block text-sm font-semibold text-neutral-700 mb-3 uppercase tracking-wider"
                        >
                            Assign Broker
                        </label>
                        <select
                            v-model="approveForm.assigned_broker_id"
                            class="w-full px-4 py-3 border-2 border-neutral-200 rounded-xl focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 bg-white shadow-sm"
                            required
                        >
                            <option value="">Select a broker...</option>
                            <option
                                v-for="broker in brokers"
                                :key="broker.id"
                                :value="broker.id"
                            >
                                {{ broker.name }}
                            </option>
                        </select>
                    </div>

                    <div
                        v-if="userRole === 'admin'"
                        class="bg-white rounded-2xl p-6 shadow-lg border border-neutral-100"
                    >
                        <label
                            class="block text-sm font-semibold text-neutral-700 mb-3 uppercase tracking-wider"
                        >
                            Admin Notes (Optional)
                        </label>
                        <textarea
                            v-model="approveForm.admin_notes"
                            rows="4"
                            class="w-full px-4 py-3 border-2 border-neutral-200 rounded-xl focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 bg-white shadow-sm resize-none"
                            placeholder="Add any notes for the broker..."
                        ></textarea>
                    </div>

                    <div
                        class="flex justify-end gap-4 pt-6 border-t border-neutral-200"
                    >
                        <ModernButton
                            type="button"
                            @click="showApproveModal = false"
                            variant="outline"
                            size="lg"
                            class="shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            Cancel
                        </ModernButton>
                        <ModernButton
                            type="submit"
                            variant="primary"
                            size="lg"
                            :disabled="approveForm.processing"
                            class="shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            {{
                                approveForm.processing
                                    ? "Approving..."
                                    : "Approve Request"
                            }}
                        </ModernButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Reject Modal -->
        <Modal :show="showRejectModal" @close="showRejectModal = false">
            <div class="p-8 bg-gradient-to-br from-white to-red-50">
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="p-3 bg-gradient-to-br from-red-500 to-pink-500 rounded-2xl shadow-lg"
                    >
                        <XCircleIcon class="w-8 h-8 text-white" />
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900">
                        Reject Seller Request
                    </h3>
                </div>

                <form @submit.prevent="rejectRequest" class="space-y-6">
                    <div
                        class="bg-white rounded-2xl p-6 shadow-lg border border-red-100"
                    >
                        <label
                            class="block text-sm font-semibold text-neutral-700 mb-3 uppercase tracking-wider"
                        >
                            Rejection Reason *
                        </label>
                        <textarea
                            v-model="rejectForm.rejection_reason"
                            rows="4"
                            class="w-full px-4 py-3 border-2 border-red-200 rounded-xl focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 bg-white shadow-sm resize-none"
                            placeholder="Please provide a clear reason for rejection..."
                            required
                        ></textarea>
                    </div>

                    <div
                        v-if="userRole === 'admin'"
                        class="bg-white rounded-2xl p-6 shadow-lg border border-neutral-100"
                    >
                        <label
                            class="block text-sm font-semibold text-neutral-700 mb-3 uppercase tracking-wider"
                        >
                            Admin Notes (Optional)
                        </label>
                        <textarea
                            v-model="rejectForm.admin_notes"
                            rows="3"
                            class="w-full px-4 py-3 border-2 border-neutral-200 rounded-xl focus:ring-4 focus:ring-red-500/20 focus:border-red-500 transition-all duration-300 bg-white shadow-sm resize-none"
                            placeholder="Additional internal notes..."
                        ></textarea>
                    </div>

                    <div
                        class="flex justify-end gap-4 pt-6 border-t border-neutral-200"
                    >
                        <ModernButton
                            type="button"
                            @click="showRejectModal = false"
                            variant="outline"
                            size="lg"
                            class="shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            Cancel
                        </ModernButton>
                        <ModernButton
                            type="submit"
                            variant="danger"
                            size="lg"
                            :disabled="rejectForm.processing"
                            class="shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            {{
                                rejectForm.processing
                                    ? "Rejecting..."
                                    : "Reject Request"
                            }}
                        </ModernButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-8 bg-gradient-to-br from-white to-red-50">
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="p-3 bg-gradient-to-br from-red-500 to-pink-500 rounded-2xl shadow-lg"
                    >
                        <TrashIcon class="w-8 h-8 text-white" />
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900">
                        Delete Seller Request
                    </h3>
                </div>

                <div
                    class="bg-white rounded-2xl p-6 shadow-lg border border-red-100 mb-6"
                >
                    <p class="text-neutral-600 text-lg leading-relaxed">
                        Are you sure you want to delete this seller request?
                        This action cannot be undone and all associated data
                        will be permanently removed.
                    </p>
                </div>

                <div
                    class="flex justify-end gap-4 pt-6 border-t border-neutral-200"
                >
                    <ModernButton
                        @click="showDeleteModal = false"
                        variant="outline"
                        size="lg"
                        class="shadow-lg hover:shadow-xl transition-all duration-300"
                    >
                        Cancel
                    </ModernButton>
                    <ModernButton
                        @click="deleteRequest"
                        variant="danger"
                        size="lg"
                        :disabled="deleteForm.processing"
                        class="shadow-lg hover:shadow-xl transition-all duration-300"
                    >
                        {{
                            deleteForm.processing
                                ? "Deleting..."
                                : "Delete Request"
                        }}
                    </ModernButton>
                </div>
            </div>
        </Modal>
    </ModernDashboardLayout>
</template>
