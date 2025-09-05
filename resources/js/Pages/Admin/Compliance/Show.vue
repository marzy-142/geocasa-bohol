<template>
    <ModernDashboardLayout>
        <Head :title="`Compliance Report #${report.id}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <Link
                                :href="route('admin.compliance.index')"
                                class="text-sm text-gray-500 hover:text-gray-700 mb-2 inline-flex items-center"
                            >
                                <ArrowLeftIcon class="h-4 w-4 mr-1" />
                                Back to Compliance Reports
                            </Link>
                            <h1 class="text-3xl font-bold text-gray-900">
                                Compliance Report #{{ report.id }}
                            </h1>
                            <p class="mt-2 text-gray-600">
                                {{ report.description }}
                            </p>
                        </div>
                        <div class="flex space-x-3">
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :class="getSeverityBadgeClass(report.severity)"
                            >
                                {{
                                    severities[report.severity] ||
                                    report.severity
                                }}
                            </span>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                :class="getStatusBadgeClass(report.status)"
                            >
                                {{ statuses[report.status] || report.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Report Details -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Report Details
                                </h3>
                            </div>
                            <div class="p-6">
                                <dl
                                    class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2"
                                >
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Report Type
                                        </dt>
                                        <dd class="mt-1">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="
                                                    getReportTypeBadgeClass(
                                                        report.report_type
                                                    )
                                                "
                                            >
                                                {{
                                                    reportTypes[
                                                        report.report_type
                                                    ] || report.report_type
                                                }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Severity
                                        </dt>
                                        <dd class="mt-1">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="
                                                    getSeverityBadgeClass(
                                                        report.severity
                                                    )
                                                "
                                            >
                                                {{
                                                    severities[
                                                        report.severity
                                                    ] || report.severity
                                                }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Reported Item
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{
                                                report.reportable_type
                                                    .split("\\")
                                                    .pop()
                                            }}
                                            #{{ report.reportable_id }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Reported Date
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ formatDate(report.reported_at) }}
                                        </dd>
                                    </div>
                                    <div v-if="report.reporter">
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Reported By
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ report.reporter.name }} ({{
                                                report.reporter.email
                                            }})
                                        </dd>
                                    </div>
                                    <div v-else>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Reported By
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800"
                                            >
                                                <CpuChipIcon
                                                    class="h-3 w-3 mr-1"
                                                />
                                                System Generated
                                            </span>
                                        </dd>
                                    </div>
                                    <div v-if="report.assigned_admin">
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Assigned To
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ report.assigned_admin.name }}
                                        </dd>
                                    </div>
                                    <div v-if="report.reviewed_at">
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Reviewed Date
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ formatDate(report.reviewed_at) }}
                                        </dd>
                                    </div>
                                    <div v-if="report.resolved_at">
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Resolved Date
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ formatDate(report.resolved_at) }}
                                        </dd>
                                    </div>
                                </dl>

                                <div class="mt-6">
                                    <dt
                                        class="text-sm font-medium text-gray-500 mb-2"
                                    >
                                        Description
                                    </dt>
                                    <dd
                                        class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg"
                                    >
                                        {{ report.description }}
                                    </dd>
                                </div>
                            </div>
                        </div>

                        <!-- Evidence -->
                        <div
                            v-if="
                                report.evidence &&
                                Object.keys(report.evidence).length > 0
                            "
                            class="bg-white shadow rounded-lg"
                        >
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Evidence
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div
                                        v-for="(value, key) in report.evidence"
                                        :key="key"
                                    >
                                        <dt
                                            class="text-sm font-medium text-gray-500 capitalize"
                                        >
                                            {{ key.replace("_", " ") }}
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <div
                                                v-if="Array.isArray(value)"
                                                class="space-y-1"
                                            >
                                                <span
                                                    v-for="item in value"
                                                    :key="item"
                                                    class="inline-block bg-red-100 text-red-800 px-2 py-1 rounded text-xs mr-2 mb-1"
                                                >
                                                    {{ item }}
                                                </span>
                                            </div>
                                            <div
                                                v-else-if="
                                                    typeof value === 'object'
                                                "
                                                class="bg-gray-50 p-3 rounded"
                                            >
                                                <pre class="text-xs">{{
                                                    JSON.stringify(
                                                        value,
                                                        null,
                                                        2
                                                    )
                                                }}</pre>
                                            </div>
                                            <div v-else>{{ value }}</div>
                                        </dd>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Notes -->
                        <div
                            v-if="report.admin_notes"
                            class="bg-white shadow rounded-lg"
                        >
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Admin Notes
                                </h3>
                            </div>
                            <div class="p-6">
                                <p
                                    class="text-sm text-gray-900 bg-blue-50 p-4 rounded-lg"
                                >
                                    {{ report.admin_notes }}
                                </p>
                            </div>
                        </div>

                        <!-- Resolution Notes -->
                        <div
                            v-if="report.resolution_notes"
                            class="bg-white shadow rounded-lg"
                        >
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Resolution Notes
                                </h3>
                            </div>
                            <div class="p-6">
                                <p
                                    class="text-sm text-gray-900 bg-green-50 p-4 rounded-lg"
                                >
                                    {{ report.resolution_notes }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Quick Actions -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Actions
                                </h3>
                            </div>
                            <div class="p-6 space-y-3">
                                <button
                                    v-if="report.status === 'pending'"
                                    @click="startInvestigation"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <EyeIcon class="h-4 w-4 mr-2" />
                                    Start Investigation
                                </button>

                                <button
                                    v-if="report.status === 'under_review'"
                                    @click="showEvidenceModal = true"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <DocumentIcon class="h-4 w-4 mr-2" />
                                    Collect Evidence
                                </button>

                                <button
                                    v-if="report.status === 'under_review'"
                                    @click="showInterviewModal = true"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                                >
                                    <UserIcon class="h-4 w-4 mr-2" />
                                    Conduct Interview
                                </button>

                                <button
                                    v-if="
                                        ['pending', 'under_review'].includes(
                                            report.status
                                        )
                                    "
                                    @click="showResolveModal = true"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    <CheckCircleIcon class="h-4 w-4 mr-2" />
                                    Resolve
                                </button>

                                <button
                                    v-if="report.status === 'under_review'"
                                    @click="showEscalateModal = true"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    <ArrowTrendingUpIcon class="h-4 w-4 mr-2" />
                                    Escalate
                                </button>

                                <button
                                    v-if="
                                        ['pending', 'under_review'].includes(
                                            report.status
                                        )
                                    "
                                    @click="showDismissModal = true"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <XMarkIcon class="h-4 w-4 mr-2" />
                                    Dismiss
                                </button>

                                <button
                                    @click="showNotesModal = true"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <PencilIcon class="h-4 w-4 mr-2" />
                                    Add Notes
                                </button>
                            </div>
                        </div>

                        <!-- Related Item Info -->
                        <div
                            v-if="report.reportable"
                            class="bg-white shadow rounded-lg"
                        >
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Related Item
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="text-sm">
                                    <div class="font-medium text-gray-900 mb-2">
                                        {{
                                            report.reportable_type
                                                .split("\\")
                                                .pop()
                                        }}
                                        #{{ report.reportable_id }}
                                    </div>

                                    <!-- Property specific info -->
                                    <div
                                        v-if="
                                            report.reportable_type.includes(
                                                'Property'
                                            ) && report.reportable
                                        "
                                    >
                                        <p class="text-gray-600 mb-1">
                                            <strong>Title:</strong>
                                            {{ report.reportable.title }}
                                        </p>
                                        <p class="text-gray-600 mb-1">
                                            <strong>Price:</strong> ₱{{
                                                formatPrice(
                                                    report.reportable.price
                                                )
                                            }}
                                        </p>
                                        <p class="text-gray-600 mb-1">
                                            <strong>Location:</strong>
                                            {{ report.reportable.city }}
                                        </p>
                                        <p class="text-gray-600">
                                            <strong>Type:</strong>
                                            {{
                                                report.reportable.property_type
                                            }}
                                        </p>
                                    </div>

                                    <!-- Inquiry specific info -->
                                    <div
                                        v-else-if="
                                            report.reportable_type.includes(
                                                'Inquiry'
                                            ) && report.reportable
                                        "
                                    >
                                        <p class="text-gray-600 mb-1">
                                            <strong>Subject:</strong>
                                            {{ report.reportable.subject }}
                                        </p>
                                        <p class="text-gray-600 mb-1">
                                            <strong>Status:</strong>
                                            {{ report.reportable.status }}
                                        </p>
                                        <p class="text-gray-600">
                                            <strong>Created:</strong>
                                            {{
                                                formatDate(
                                                    report.reportable.created_at
                                                )
                                            }}
                                        </p>
                                    </div>

                                    <!-- User specific info -->
                                    <div
                                        v-else-if="
                                            report.reportable_type.includes(
                                                'User'
                                            ) && report.reportable
                                        "
                                    >
                                        <p class="text-gray-600 mb-1">
                                            <strong>Name:</strong>
                                            {{ report.reportable.name }}
                                        </p>
                                        <p class="text-gray-600 mb-1">
                                            <strong>Email:</strong>
                                            {{ report.reportable.email }}
                                        </p>
                                        <p class="text-gray-600 mb-1">
                                            <strong>Role:</strong>
                                            {{ report.reportable.role }}
                                        </p>
                                        <p class="text-gray-600">
                                            <strong>Joined:</strong>
                                            {{
                                                formatDate(
                                                    report.reportable.created_at
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Investigation Timeline -->
                <div
                    v-if="investigationLogs && investigationLogs.length > 0"
                    class="bg-white shadow rounded-lg"
                >
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            Investigation Timeline
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li
                                    v-for="(log, index) in investigationLogs"
                                    :key="log.id"
                                >
                                    <div class="relative pb-8">
                                        <span
                                            v-if="
                                                index !==
                                                investigationLogs.length - 1
                                            "
                                            class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                            aria-hidden="true"
                                        ></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span
                                                    class="h-8 w-8 rounded-full text-white flex items-center justify-center ring-8 ring-white"
                                                    :class="
                                                        getActivityIconClass(
                                                            log.action_type
                                                        )
                                                    "
                                                >
                                                    <component
                                                        :is="
                                                            getActivityIcon(
                                                                log.action_type
                                                            )
                                                        "
                                                        class="h-4 w-4"
                                                    />
                                                </span>
                                            </div>
                                            <div
                                                class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4"
                                            >
                                                <div>
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        <span
                                                            class="font-medium text-gray-900"
                                                            >{{
                                                                log.investigator
                                                                    .name
                                                            }}</span
                                                        >
                                                        {{
                                                            log.action_type.replace(
                                                                "_",
                                                                " "
                                                            )
                                                        }}
                                                    </p>
                                                    <p
                                                        class="mt-1 text-sm text-gray-600"
                                                    >
                                                        {{ log.description }}
                                                    </p>
                                                    <div
                                                        v-if="
                                                            log.metadata &&
                                                            Object.keys(
                                                                log.metadata
                                                            ).length > 0
                                                        "
                                                        class="mt-2"
                                                    >
                                                        <div
                                                            class="bg-gray-50 rounded-md p-3"
                                                        >
                                                            <h5
                                                                class="text-xs font-medium text-gray-700 mb-1"
                                                            >
                                                                Details:
                                                            </h5>
                                                            <div
                                                                class="text-xs text-gray-600"
                                                            >
                                                                <div
                                                                    v-for="(
                                                                        value,
                                                                        key
                                                                    ) in log.metadata"
                                                                    :key="key"
                                                                    class="flex justify-between"
                                                                >
                                                                    <span
                                                                        class="font-medium"
                                                                        >{{
                                                                            key.replace(
                                                                                "_",
                                                                                " "
                                                                            )
                                                                        }}:</span
                                                                    >
                                                                    <span>{{
                                                                        value
                                                                    }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        v-if="
                                                            log.evidence_files &&
                                                            log.evidence_files
                                                                .length > 0
                                                        "
                                                        class="mt-2"
                                                    >
                                                        <h5
                                                            class="text-xs font-medium text-gray-700 mb-1"
                                                        >
                                                            Evidence Files:
                                                        </h5>
                                                        <div
                                                            class="flex flex-wrap gap-1"
                                                        >
                                                            <span
                                                                v-for="file in log.evidence_files"
                                                                :key="file"
                                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                                            >
                                                                {{ file }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div
                                                    class="text-right text-sm whitespace-nowrap text-gray-500"
                                                >
                                                    <time
                                                        :datetime="
                                                            log.action_taken_at
                                                        "
                                                        >{{
                                                            formatDate(
                                                                log.action_taken_at
                                                            )
                                                        }}</time
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resolve Modal -->
        <TransitionRoot as="template" :show="showResolveModal">
            <Dialog
                as="div"
                class="relative z-10"
                @close="showResolveModal = false"
            >
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div
                        class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                    >
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                            >
                                <form @submit.prevent="resolveReport">
                                    <div>
                                        <div
                                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100"
                                        >
                                            <CheckCircleIcon
                                                class="h-6 w-6 text-green-600"
                                            />
                                        </div>
                                        <div class="mt-3 text-center sm:mt-5">
                                            <DialogTitle
                                                as="h3"
                                                class="text-base font-semibold leading-6 text-gray-900"
                                            >
                                                Resolve Report
                                            </DialogTitle>
                                            <div class="mt-2">
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    Mark this compliance report
                                                    as resolved and add
                                                    resolution notes.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5">
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                            >Resolution Notes</label
                                        >
                                        <textarea
                                            v-model="
                                                resolveForm.resolution_notes
                                            "
                                            rows="4"
                                            required
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Describe how this issue was resolved..."
                                        ></textarea>
                                    </div>
                                    <div
                                        class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3"
                                    >
                                        <button
                                            type="submit"
                                            :disabled="resolveForm.processing"
                                            class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 sm:col-start-2 disabled:opacity-50"
                                        >
                                            <span v-if="resolveForm.processing"
                                                >Resolving...</span
                                            >
                                            <span v-else>Resolve Report</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="showResolveModal = false"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Dismiss Modal -->
        <TransitionRoot as="template" :show="showDismissModal">
            <Dialog
                as="div"
                class="relative z-10"
                @close="showDismissModal = false"
            >
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div
                        class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                    >
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                            >
                                <form @submit.prevent="dismissReport">
                                    <div>
                                        <div
                                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100"
                                        >
                                            <XMarkIcon
                                                class="h-6 w-6 text-gray-600"
                                            />
                                        </div>
                                        <div class="mt-3 text-center sm:mt-5">
                                            <DialogTitle
                                                as="h3"
                                                class="text-base font-semibold leading-6 text-gray-900"
                                            >
                                                Dismiss Report
                                            </DialogTitle>
                                            <div class="mt-2">
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    Mark this compliance report
                                                    as dismissed. This action
                                                    indicates the report was
                                                    reviewed but no action was
                                                    needed.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5">
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                            >Dismissal Reason (Optional)</label
                                        >
                                        <textarea
                                            v-model="dismissForm.admin_notes"
                                            rows="3"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Explain why this report is being dismissed..."
                                        ></textarea>
                                    </div>
                                    <div
                                        class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3"
                                    >
                                        <button
                                            type="submit"
                                            :disabled="dismissForm.processing"
                                            class="inline-flex w-full justify-center rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600 sm:col-start-2 disabled:opacity-50"
                                        >
                                            <span v-if="dismissForm.processing"
                                                >Dismissing...</span
                                            >
                                            <span v-else>Dismiss Report</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="showDismissModal = false"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Notes Modal -->
        <TransitionRoot as="template" :show="showNotesModal">
            <Dialog
                as="div"
                class="relative z-10"
                @close="showNotesModal = false"
            >
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div
                        class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                    >
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                            >
                                <form @submit.prevent="updateNotes">
                                    <div>
                                        <div
                                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100"
                                        >
                                            <PencilIcon
                                                class="h-6 w-6 text-blue-600"
                                            />
                                        </div>
                                        <div class="mt-3 text-center sm:mt-5">
                                            <DialogTitle
                                                as="h3"
                                                class="text-base font-semibold leading-6 text-gray-900"
                                            >
                                                Update Admin Notes
                                            </DialogTitle>
                                            <div class="mt-2">
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    Add or update administrative
                                                    notes for this compliance
                                                    report.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5">
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                            >Admin Notes</label
                                        >
                                        <textarea
                                            v-model="notesForm.admin_notes"
                                            rows="4"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Add administrative notes..."
                                        ></textarea>
                                    </div>
                                    <div
                                        class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3"
                                    >
                                        <button
                                            type="submit"
                                            :disabled="notesForm.processing"
                                            class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2 disabled:opacity-50"
                                        >
                                            <span v-if="notesForm.processing"
                                                >Updating...</span
                                            >
                                            <span v-else>Update Notes</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="showNotesModal = false"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Evidence Collection Modal -->
        <TransitionRoot as="template" :show="showEvidenceModal">
            <Dialog
                as="div"
                class="relative z-10"
                @close="showEvidenceModal = false"
            >
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div
                        class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                    >
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                            >
                                <form @submit.prevent="collectEvidence">
                                    <div>
                                        <div
                                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100"
                                        >
                                            <DocumentIcon
                                                class="h-6 w-6 text-blue-600"
                                            />
                                        </div>
                                        <div class="mt-3 text-center sm:mt-5">
                                            <DialogTitle
                                                as="h3"
                                                class="text-base font-semibold leading-6 text-gray-900"
                                            >
                                                Collect Evidence
                                            </DialogTitle>
                                            <div class="mt-2">
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    Document evidence related to
                                                    this compliance report.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 space-y-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-2"
                                                >Evidence Type</label
                                            >
                                            <select
                                                v-model="
                                                    evidenceForm.evidence_type
                                                "
                                                required
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="">
                                                    Select evidence type
                                                </option>
                                                <option value="document">
                                                    Document
                                                </option>
                                                <option value="screenshot">
                                                    Screenshot
                                                </option>
                                                <option value="communication">
                                                    Communication
                                                </option>
                                                <option
                                                    value="witness_statement"
                                                >
                                                    Witness Statement
                                                </option>
                                                <option value="other">
                                                    Other
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-2"
                                                >Description</label
                                            >
                                            <textarea
                                                v-model="
                                                    evidenceForm.description
                                                "
                                                rows="3"
                                                required
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="Describe the evidence and its relevance..."
                                            ></textarea>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3"
                                    >
                                        <button
                                            type="submit"
                                            :disabled="evidenceForm.processing"
                                            class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 sm:col-start-2 disabled:opacity-50"
                                        >
                                            <span v-if="evidenceForm.processing"
                                                >Collecting...</span
                                            >
                                            <span v-else>Collect Evidence</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="showEvidenceModal = false"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Interview Modal -->
        <TransitionRoot as="template" :show="showInterviewModal">
            <Dialog
                as="div"
                class="relative z-10"
                @close="showInterviewModal = false"
            >
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div
                        class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                    >
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                            >
                                <form @submit.prevent="conductInterview">
                                    <div>
                                        <div
                                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-purple-100"
                                        >
                                            <UserIcon
                                                class="h-6 w-6 text-purple-600"
                                            />
                                        </div>
                                        <div class="mt-3 text-center sm:mt-5">
                                            <DialogTitle
                                                as="h3"
                                                class="text-base font-semibold leading-6 text-gray-900"
                                            >
                                                Conduct Interview
                                            </DialogTitle>
                                            <div class="mt-2">
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    Record an interview related
                                                    to this compliance report.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 space-y-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-2"
                                                >Interviewee Type</label
                                            >
                                            <select
                                                v-model="
                                                    interviewForm.interviewee_type
                                                "
                                                required
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="">
                                                    Select interviewee type
                                                </option>
                                                <option value="reporter">
                                                    Reporter
                                                </option>
                                                <option value="subject">
                                                    Subject of Report
                                                </option>
                                                <option value="witness">
                                                    Witness
                                                </option>
                                                <option value="expert">
                                                    Expert/Consultant
                                                </option>
                                                <option value="other">
                                                    Other
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-2"
                                                >Interview Summary</label
                                            >
                                            <textarea
                                                v-model="interviewForm.summary"
                                                rows="4"
                                                required
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="Summarize the key points discussed..."
                                            ></textarea>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-2"
                                                >Key Findings</label
                                            >
                                            <textarea
                                                v-model="interviewForm.findings"
                                                rows="3"
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="Document important findings..."
                                            ></textarea>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3"
                                    >
                                        <button
                                            type="submit"
                                            :disabled="interviewForm.processing"
                                            class="inline-flex w-full justify-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600 sm:col-start-2 disabled:opacity-50"
                                        >
                                            <span
                                                v-if="interviewForm.processing"
                                                >Recording...</span
                                            >
                                            <span v-else>Record Interview</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="showInterviewModal = false"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Escalate Modal -->
        <TransitionRoot as="template" :show="showEscalateModal">
            <Dialog
                as="div"
                class="relative z-10"
                @close="showEscalateModal = false"
            >
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div
                        class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
                    >
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100"
                            leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        >
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                            >
                                <form @submit.prevent="escalateInvestigation">
                                    <div>
                                        <div
                                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100"
                                        >
                                            <ArrowTrendingUpIcon
                                                class="h-6 w-6 text-red-600"
                                            />
                                        </div>
                                        <div class="mt-3 text-center sm:mt-5">
                                            <DialogTitle
                                                as="h3"
                                                class="text-base font-semibold leading-6 text-gray-900"
                                            >
                                                Escalate Investigation
                                            </DialogTitle>
                                            <div class="mt-2">
                                                <p
                                                    class="text-sm text-gray-500"
                                                >
                                                    Escalate this case to higher
                                                    authority or specialized
                                                    team.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 space-y-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-2"
                                                >Escalation Reason</label
                                            >
                                            <textarea
                                                v-model="
                                                    escalateForm.escalation_reason
                                                "
                                                rows="4"
                                                required
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                placeholder="Explain why this case needs to be escalated..."
                                            ></textarea>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-2"
                                                >Priority Level</label
                                            >
                                            <select
                                                v-model="
                                                    escalateForm.priority_level
                                                "
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="high">
                                                    High
                                                </option>
                                                <option value="critical">
                                                    Critical
                                                </option>
                                                <option value="urgent">
                                                    Urgent
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3"
                                    >
                                        <button
                                            type="submit"
                                            :disabled="escalateForm.processing"
                                            class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 sm:col-start-2 disabled:opacity-50"
                                        >
                                            <span v-if="escalateForm.processing"
                                                >Escalating...</span
                                            >
                                            <span v-else>Escalate Case</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="showEscalateModal = false"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import {
    ArrowLeftIcon,
    EyeIcon,
    CheckCircleIcon,
    XMarkIcon,
    PencilIcon,
    CpuChipIcon,
    DocumentIcon,
    UserIcon,
    ArrowTrendingUpIcon,
} from "@heroicons/vue/24/outline";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";

const props = defineProps({
    report: Object,
    reportTypes: Object,
    severities: Object,
    statuses: Object,
    investigationLogs: Array,
});

// Reactive data
const showResolveModal = ref(false);
const showDismissModal = ref(false);
const showNotesModal = ref(false);
const showEvidenceModal = ref(false);
const showInterviewModal = ref(false);
const showEscalateModal = ref(false);

// Forms
const resolveForm = useForm({
    status: "resolved",
    resolution_notes: "",
});

const dismissForm = useForm({
    status: "dismissed",
    admin_notes: "",
});

const notesForm = useForm({
    admin_notes: props.report.admin_notes || "",
});

const evidenceForm = useForm({
    evidence_type: "",
    description: "",
    files: [],
});

const interviewForm = useForm({
    interviewee_type: "",
    interviewee_id: "",
    summary: "",
    findings: "",
});

const escalateForm = useForm({
    escalation_reason: "",
    priority_level: "high",
});

// Methods
const updateStatus = (status) => {
    router.patch(
        route("admin.compliance.update-status", props.report.id),
        {
            status: status,
        },
        {
            preserveScroll: true,
        }
    );
};

const resolveReport = () => {
    resolveForm.patch(
        route("admin.compliance.update-status", props.report.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                showResolveModal.value = false;
                resolveForm.reset();
            },
        }
    );
};

const dismissReport = () => {
    dismissForm.patch(
        route("admin.compliance.update-status", props.report.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                showDismissModal.value = false;
                dismissForm.reset();
            },
        }
    );
};

const updateNotes = () => {
    notesForm.patch(route("admin.compliance.update-status", props.report.id), {
        preserveScroll: true,
        onSuccess: () => {
            showNotesModal.value = false;
        },
    });
};

const startInvestigation = () => {
    useForm({}).post(
        route("admin.compliance.investigation.start", props.report.id),
        {
            preserveScroll: true,
        }
    );
};

const collectEvidence = () => {
    evidenceForm.post(
        route("admin.compliance.investigation.evidence", props.report.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                showEvidenceModal.value = false;
                evidenceForm.reset();
            },
        }
    );
};

const conductInterview = () => {
    interviewForm.post(
        route("admin.compliance.investigation.interview", props.report.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                showInterviewModal.value = false;
                interviewForm.reset();
            },
        }
    );
};

const escalateInvestigation = () => {
    escalateForm.post(
        route("admin.compliance.investigation.escalate", props.report.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                showEscalateModal.value = false;
                escalateForm.reset();
            },
        }
    );
};

const resolveInvestigation = () => {
    resolveForm.post(
        route("admin.compliance.investigation.resolve", props.report.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                showResolveModal.value = false;
                resolveForm.reset();
            },
        }
    );
};

const getActivityIcon = (actionType) => {
    const icons = {
        evidence_collected: DocumentIcon,
        interview_conducted: UserIcon,
        status_changed: EyeIcon,
        note_added: PencilIcon,
        escalated: ArrowTrendingUpIcon,
        resolved: CheckCircleIcon,
    };
    return icons[actionType] || DocumentIcon;
};

const getActivityIconClass = (actionType) => {
    const classes = {
        evidence_collected: "bg-blue-500",
        interview_conducted: "bg-purple-500",
        status_changed: "bg-yellow-500",
        note_added: "bg-gray-500",
        escalated: "bg-red-500",
        resolved: "bg-green-500",
    };
    return classes[actionType] || "bg-gray-500";
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

const formatPrice = (price) => {
    return new Intl.NumberFormat("en-PH").format(price);
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        under_review: "bg-blue-100 text-blue-800",
        resolved: "bg-green-100 text-green-800",
        dismissed: "bg-gray-100 text-gray-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getSeverityBadgeClass = (severity) => {
    const classes = {
        low: "bg-green-100 text-green-800",
        medium: "bg-yellow-100 text-yellow-800",
        high: "bg-orange-100 text-orange-800",
        critical: "bg-red-100 text-red-800",
    };
    return classes[severity] || "bg-gray-100 text-gray-800";
};

const getReportTypeBadgeClass = (type) => {
    const classes = {
        suspicious_content: "bg-red-100 text-red-800",
        price_anomaly: "bg-orange-100 text-orange-800",
        spam_pattern: "bg-yellow-100 text-yellow-800",
        duplicate_listing: "bg-blue-100 text-blue-800",
        contact_bypass: "bg-purple-100 text-purple-800",
        incomplete_listing: "bg-gray-100 text-gray-800",
        excessive_activity: "bg-red-100 text-red-800",
        incomplete_profile: "bg-yellow-100 text-yellow-800",
    };
    return classes[type] || "bg-gray-100 text-gray-800";
};
</script>
