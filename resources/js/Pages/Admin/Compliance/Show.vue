<template>
    <ModernDashboardLayout>
        <div class="space-y-6">
                <!-- Header -->
                    <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                            <Link
                        :href="route('compliance.index')"
                        class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
                            >
                        <ArrowLeftIcon class="w-4 h-4 mr-1" />
                        Back to Compliance
                            </Link>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Compliance Report Details
                            </h1>
                        <p class="text-gray-600">
                            Report #{{ report.id }} -
                            {{ reportTypes[report.report_type] }}
                            </p>
                    </div>
                        </div>
                        <div class="flex space-x-3">
                    <button
                        @click="showStatusModal = true"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <PencilIcon class="w-4 h-4 mr-2" />
                        Update Status
                    </button>
                    <Link
                        :href="route('investigations.dashboard')"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <MagnifyingGlassIcon class="w-4 h-4 mr-2" />
                        Investigation Dashboard
                    </Link>
                    </div>
                </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                    <!-- Report Information -->
                        <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3
                                class="text-lg leading-6 font-medium text-gray-900 mb-4"
                            >
                                Report Information
                                </h3>

                                <dl
                                    class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2"
                                >
                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Report Type
                                        </dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ reportTypes[report.report_type] }}
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
                                                :class="
                                                    getSeverityBadgeClass(
                                                        report.severity
                                                    )
                                                "
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ severities[report.severity] }}
                                            </span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                        Status
                                        </dt>
                                    <dd class="mt-1">
                                        <span
                                            :class="
                                                getStatusBadgeClass(
                                                    report.status
                                                )
                                            "
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        >
                                            {{ statuses[report.status] }}
                                        </span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Reported By
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                        {{ report.reporter?.name || "Unknown" }}
                                        </dd>
                                    </div>

                                <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                        Reported At
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                        {{ formatDate(report.reported_at) }}
                                        </dd>
                                    </div>

                                <div>
                                        <dt
                                            class="text-sm font-medium text-gray-500"
                                        >
                                            Assigned To
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                        {{
                                            report.assigned_admin?.name ||
                                            "Unassigned"
                                        }}
                                        </dd>
                                    </div>
                                </dl>

                                <div class="mt-6">
                                <dt class="text-sm font-medium text-gray-500">
                                        Description
                                    </dt>
                                    <dd
                                    class="mt-1 text-sm text-gray-900 whitespace-pre-wrap"
                                    >
                                        {{ report.description }}
                                    </dd>
                                </div>
                            </div>
                        </div>

                        <!-- Evidence -->
                        <div
                        v-if="report.evidence && report.evidence.length > 0"
                            class="bg-white shadow rounded-lg"
                        >
                        <div class="px-4 py-5 sm:p-6">
                            <h3
                                class="text-lg leading-6 font-medium text-gray-900 mb-4"
                            >
                                    Evidence
                                </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div
                                    v-for="(item, index) in report.evidence"
                                    :key="index"
                                    class="border border-gray-200 rounded-lg p-4"
                                >
                                    <div class="flex items-center space-x-3">
                                        <DocumentTextIcon
                                            class="h-8 w-8 text-gray-400"
                                        />
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-sm font-medium text-gray-900 truncate"
                                            >
                                                {{
                                                    item.name ||
                                                    `Evidence ${index + 1}`
                                                }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ item.type || "Document" }}
                                </p>
                            </div>
                        </div>
                            </div>
                            </div>
                        </div>
                    </div>

                    <!-- Investigation Logs -->
                        <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3
                                    class="text-lg leading-6 font-medium text-gray-900"
                                >
                                    Investigation Timeline
                                </h3>
                                <button
                                    @click="showAddNoteModal = true"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <PlusIcon class="w-4 h-4 mr-2" />
                                    Add Note
                                </button>
                        </div>

                            <div
                                v-if="investigationLogs.length === 0"
                                class="text-center py-8"
                            >
                                <ClockIcon
                                    class="mx-auto h-12 w-12 text-gray-400"
                                />
                                <h3
                                    class="mt-2 text-sm font-medium text-gray-900"
                                >
                                    No investigation activities
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Investigation activities will appear here as
                                    they occur.
                                        </p>
                                    </div>

                            <div v-else class="flow-root">
                            <ul class="-mb-8">
                                <li
                                        v-for="(
                                            log, index
                                        ) in investigationLogs"
                                    :key="log.id"
                                >
                                    <div class="relative pb-8">
                                            <div
                                            v-if="
                                                index !==
                                                investigationLogs.length - 1
                                            "
                                            class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                            ></div>
                                            <div
                                                class="relative flex space-x-3"
                                            >
                                            <div>
                                                <span
                                                    :class="
                                                        getActivityIconClass(
                                                            log.action_type
                                                        )
                                                    "
                                                        class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                                >
                                                    <component
                                                        :is="
                                                            getActivityIcon(
                                                                log.action_type
                                                            )
                                                        "
                                                            class="h-5 w-5"
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
                                                                    log
                                                                        .investigator
                                                                        ?.name ||
                                                                    "System"
                                                            }}</span
                                                        >
                                                        {{
                                                                getActivityDescription(
                                                                    log.action_type
                                                            )
                                                        }}
                                                    </p>
                                                    <p
                                                            v-if="
                                                                log.description
                                                            "
                                                            class="mt-1 text-sm text-gray-500"
                                                    >
                                                            {{
                                                                log.description
                                                            }}
                                                    </p>
                                                    <div
                                                            v-if="log.metadata"
                                                            class="mt-2 text-xs text-gray-400"
                                                        >
                                                            <span
                                                        v-if="
                                                                log.metadata
                                                                        .interviewee_type
                                                                "
                                                                >Interviewed:
                                                                {{
                                                                    log.metadata
                                                                        .interviewee_type
                                                                }}</span
                                                                >
                                                                    <span
                                                        v-if="
                                                                    log.metadata
                                                                        .duration_minutes
                                                                "
                                                                class="ml-2"
                                                                >Duration:
                                                                {{
                                                                    log.metadata
                                                                        .duration_minutes
                                                                }}min</span
                                                        >
                                                            <span
                                                                v-if="
                                                                    log.metadata
                                                                        .evidence_type
                                                                "
                                                                class="ml-2"
                                                                >Evidence:
                                                                {{
                                                                    log.metadata
                                                                        .evidence_type
                                                                }}</span
                                                            >
                                                    </div>
                                                </div>
                                                <div
                                                    class="text-right text-sm whitespace-nowrap text-gray-500"
                                                >
                                                        {{
                                                            formatDate(
                                                                log.action_taken_at
                                                            )
                                                        }}
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

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3
                                class="text-lg leading-6 font-medium text-gray-900 mb-4"
                            >
                                Quick Actions
                            </h3>
                            <div class="space-y-3">
                                <button
                                    @click="startInvestigation"
                                    :disabled="report.status === 'under_review'"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <MagnifyingGlassIcon class="w-4 h-4 mr-2" />
                                    Start Investigation
                                </button>

                                <button
                                    @click="showCollectEvidenceModal = true"
                                    :disabled="report.status !== 'under_review'"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <DocumentTextIcon class="w-4 h-4 mr-2" />
                                    Collect Evidence
                                </button>

                                <button
                                    @click="showConductInterviewModal = true"
                                    :disabled="report.status !== 'under_review'"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <ChatBubbleLeftRightIcon
                                        class="w-4 h-4 mr-2"
                                    />
                                    Conduct Interview
                                </button>

                                        <button
                                    @click="showEscalateModal = true"
                                    :disabled="report.status !== 'under_review'"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <ArrowUpIcon class="w-4 h-4 mr-2" />
                                    Escalate Investigation
                                        </button>

                                        <button
                                    @click="showResolveModal = true"
                                    :disabled="report.status !== 'under_review'"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <CheckCircleIcon class="w-4 h-4 mr-2" />
                                    Resolve Investigation
                                        </button>
                                    </div>
                    </div>
                </div>

                    <!-- Report Details -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3
                                class="text-lg leading-6 font-medium text-gray-900 mb-4"
                            >
                                Report Details
                            </h3>
                            <dl class="space-y-3">
                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Report ID
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        #{{ report.id }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Reportable Item
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{
                                            report.reportable?.title ||
                                            report.reportable?.name ||
                                            "Unknown Item"
                                        }}
                                    </dd>
                                </div>

                                <div v-if="report.admin_notes">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Admin Notes
                                    </dt>
                                    <dd
                                        class="mt-1 text-sm text-gray-900 whitespace-pre-wrap"
                                    >
                                        {{ report.admin_notes }}
                                    </dd>
                                        </div>

                                <div v-if="report.resolution_notes">
                                    <dt
                                        class="text-sm font-medium text-gray-500"
                                    >
                                        Resolution Notes
                                    </dt>
                                    <dd
                                        class="mt-1 text-sm text-gray-900 whitespace-pre-wrap"
                                    >
                                        {{ report.resolution_notes }}
                                    </dd>
                                            </div>
                            </dl>
                                        </div>
                                    </div>
                </div>
            </div>
        </div>

        <!-- Update Status Modal -->
        <Modal :show="showStatusModal" @close="showStatusModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Update Report Status
                </h3>
                <form @submit.prevent="updateStatus">
                    <div class="space-y-4">
                        <div>
                                        <label
                                class="block text-sm font-medium text-gray-700"
                                >Status</label
                            >
                            <select
                                v-model="statusUpdate.status"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                required
                            >
                                <option value="">Select Status</option>
                                <option
                                    v-for="(label, value) in statuses"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Admin Notes</label
                                        >
                                        <textarea
                                v-model="statusUpdate.admin_notes"
                                            rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Add admin notes..."
                                        ></textarea>
                                    </div>

                        <div
                            v-if="
                                statusUpdate.status === 'resolved' ||
                                statusUpdate.status === 'dismissed'
                            "
                        >
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Resolution Notes</label
                            >
                            <textarea
                                v-model="statusUpdate.resolution_notes"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Add resolution notes..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                                        <button
                                            type="button"
                            @click="showStatusModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        >
                                            Cancel
                                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Update Status
                        </button>
                                    </div>
                                </form>
                    </div>
        </Modal>

        <!-- Add Note Modal -->
        <Modal :show="showAddNoteModal" @close="showAddNoteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Add Investigation Note
                </h3>
                <form @submit.prevent="addNote">
                    <div class="space-y-4">
                                    <div>
                                        <label
                                class="block text-sm font-medium text-gray-700"
                                >Note</label
                                        >
                                        <textarea
                                v-model="noteForm.note"
                                            rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Add investigation note..."
                                required
                                        ></textarea>
                                    </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Priority</label
                            >
                            <select
                                v-model="noteForm.priority"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                            >
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                                        <button
                                            type="button"
                            @click="showAddNoteModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        >
                                            Cancel
                                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Add Note
                        </button>
                                    </div>
                                </form>
                    </div>
        </Modal>

        <!-- Collect Evidence Modal -->
        <Modal
            :show="showCollectEvidenceModal"
            @close="showCollectEvidenceModal = false"
        >
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Collect Evidence
                </h3>
                                <form @submit.prevent="collectEvidence">
                    <div class="space-y-4">
                                    <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Description</label
                            >
                            <textarea
                                v-model="evidenceForm.description"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Describe the evidence collected..."
                                required
                            ></textarea>
                                        </div>

                                        <div>
                                            <label
                                class="block text-sm font-medium text-gray-700"
                                                >Evidence Type</label
                                            >
                                            <select
                                v-model="evidenceForm.evidence_type"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                                required
                            >
                                <option value="">Select Type</option>
                                <option value="document">Document</option>
                                <option value="screenshot">Screenshot</option>
                                <option value="testimony">Testimony</option>
                                <option value="physical">
                                    Physical Evidence
                                                </option>
                                <option value="digital">
                                    Digital Evidence
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Source</label
                            >
                            <input
                                v-model="evidenceForm.source"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Evidence source..."
                            />
                                        </div>
                                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                                        <button
                            type="button"
                            @click="showCollectEvidenceModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Cancel
                                        </button>
                                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        >
                            Collect Evidence
                                        </button>
                                    </div>
                                </form>
                    </div>
        </Modal>

        <!-- Conduct Interview Modal -->
        <Modal
            :show="showConductInterviewModal"
            @close="showConductInterviewModal = false"
        >
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Conduct Interview
                </h3>
                                <form @submit.prevent="conductInterview">
                    <div class="space-y-4">
                                    <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Description</label
                            >
                            <textarea
                                v-model="interviewForm.description"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Describe the interview..."
                                required
                            ></textarea>
                                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Interviewee Type</label
                            >
                            <select
                                v-model="interviewForm.interviewee_type"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                required
                            >
                                <option value="">Select Type</option>
                                <option value="reporter">Reporter</option>
                                <option value="subject">Subject</option>
                                <option value="witness">Witness</option>
                                <option value="other">Other</option>
                            </select>
                                            </div>

                                        <div>
                                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Interview Method</label
                                            >
                                            <select
                                v-model="interviewForm.interview_method"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                                required
                            >
                                <option value="">Select Method</option>
                                <option value="in_person">In Person</option>
                                <option value="phone">Phone</option>
                                <option value="email">Email</option>
                                <option value="video_call">Video Call</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Duration (minutes)</label
                            >
                            <input
                                v-model="interviewForm.duration_minutes"
                                type="number"
                                min="1"
                                max="480"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Duration in minutes..."
                            />
                                        </div>

                                        <div>
                                            <label
                                class="block text-sm font-medium text-gray-700"
                                                >Key Findings</label
                                            >
                                            <textarea
                                v-model="interviewForm.key_findings"
                                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Key findings from the interview..."
                                            ></textarea>
                                        </div>

                        <div class="flex items-center">
                            <input
                                v-model="interviewForm.follow_up_required"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label class="ml-2 block text-sm text-gray-900"
                                >Follow-up required</label
                            >
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                                        <button
                            type="button"
                            @click="showConductInterviewModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Cancel
                                        </button>
                                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        >
                            Record Interview
                                        </button>
                                    </div>
                                </form>
                    </div>
        </Modal>

        <!-- Escalate Investigation Modal -->
        <Modal :show="showEscalateModal" @close="showEscalateModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Escalate Investigation
                </h3>
                <form @submit.prevent="escalateInvestigation">
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Reason for Escalation</label
                            >
                            <textarea
                                v-model="escalateForm.reason"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Explain why this investigation needs to be escalated..."
                                required
                            ></textarea>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >New Severity</label
                            >
                            <select
                                v-model="escalateForm.new_severity"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                            >
                                <option value="">Keep Current</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>

                                    <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Urgency Level</label
                            >
                            <select
                                v-model="escalateForm.urgency_level"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                required
                            >
                                <option value="">Select Urgency</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showEscalateModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            Escalate Investigation
                        </button>
                                            </div>
                </form>
                                        </div>
        </Modal>

        <!-- Resolve Investigation Modal -->
        <Modal :show="showResolveModal" @close="showResolveModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Resolve Investigation
                </h3>
                <form @submit.prevent="resolveInvestigation">
                    <div class="space-y-4">
                                        <div>
                                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Resolution Notes</label
                                            >
                                            <textarea
                                v-model="resolveForm.resolution_notes"
                                                rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Provide detailed resolution notes..."
                                                required
                                            ></textarea>
                                        </div>

                                        <div>
                                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Resolution Type</label
                                            >
                                            <select
                                v-model="resolveForm.resolution_type"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                                required
                            >
                                <option value="">Select Type</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="unfounded">Unfounded</option>
                                <option value="inconclusive">
                                    Inconclusive
                                                </option>
                                            </select>
                                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Actions Taken</label
                            >
                            <textarea
                                v-model="resolveForm.actions_taken"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Describe actions taken..."
                            ></textarea>
                                    </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Preventive Measures</label
                            >
                            <textarea
                                v-model="resolveForm.preventive_measures"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Preventive measures implemented..."
                            ></textarea>
                        </div>

                        <div class="flex items-center">
                            <input
                                v-model="resolveForm.follow_up_required"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            />
                            <label class="ml-2 block text-sm text-gray-900"
                                >Follow-up required</label
                            >
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                                        <button
                                            type="button"
                            @click="showResolveModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        >
                                            Cancel
                                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        >
                            Resolve Investigation
                        </button>
                                    </div>
                                </form>
                    </div>
        </Modal>
    </ModernDashboardLayout>
</template>

<script setup>
import { ref, reactive } from "vue";
import { Link, router } from "@inertiajs/vue3";
import ModernDashboardLayout from "@/Layouts/ModernDashboardLayout.vue";
import Modal from "@/Components/Modal.vue";
import {
    ArrowLeftIcon,
    PencilIcon,
    MagnifyingGlassIcon,
    DocumentTextIcon,
    ChatBubbleLeftRightIcon,
    ArrowUpIcon,
    CheckCircleIcon,
    PlusIcon,
    ClockIcon,
    EyeIcon,
    ExclamationCircleIcon,
} from "@heroicons/vue/24/outline";

// Props
const props = defineProps({
    report: Object,
    investigationLogs: Array,
    reportTypes: Object,
    severities: Object,
    statuses: Object,
});

// Reactive data
const showStatusModal = ref(false);
const showAddNoteModal = ref(false);
const showCollectEvidenceModal = ref(false);
const showConductInterviewModal = ref(false);
const showEscalateModal = ref(false);
const showResolveModal = ref(false);

const statusUpdate = reactive({
    status: props.report.status,
    admin_notes: props.report.admin_notes || "",
    resolution_notes: props.report.resolution_notes || "",
});

const noteForm = reactive({
    note: "",
    priority: "medium",
});

const evidenceForm = reactive({
    description: "",
    evidence_type: "",
    source: "",
});

const interviewForm = reactive({
    description: "",
    interviewee_type: "",
    interview_method: "",
    duration_minutes: null,
    key_findings: "",
    follow_up_required: false,
});

const escalateForm = reactive({
    reason: "",
    new_severity: "",
    urgency_level: "",
});

const resolveForm = reactive({
    resolution_notes: "",
    resolution_type: "",
    actions_taken: "",
    preventive_measures: "",
    follow_up_required: false,
});

// Methods
const getSeverityBadgeClass = (severity) => {
    const classes = {
        low: "bg-green-100 text-green-800",
        medium: "bg-yellow-100 text-yellow-800",
        high: "bg-orange-100 text-orange-800",
        critical: "bg-red-100 text-red-800",
    };
    return classes[severity] || "bg-gray-100 text-gray-800";
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

const getActivityIconClass = (actionType) => {
    const classes = {
        investigation_started: "bg-blue-100 text-blue-600",
        evidence_collected: "bg-green-100 text-green-600",
        interview_conducted: "bg-yellow-100 text-yellow-600",
        note_added: "bg-gray-100 text-gray-600",
        investigation_escalated: "bg-red-100 text-red-600",
        investigation_resolved: "bg-green-100 text-green-600",
    };
    return classes[actionType] || "bg-gray-100 text-gray-600";
};

const getActivityIcon = (actionType) => {
    const icons = {
        investigation_started: MagnifyingGlassIcon,
        evidence_collected: DocumentTextIcon,
        interview_conducted: ChatBubbleLeftRightIcon,
        note_added: PlusIcon,
        investigation_escalated: ArrowUpIcon,
        investigation_resolved: CheckCircleIcon,
    };
    return icons[actionType] || EyeIcon;
};

const getActivityDescription = (actionType) => {
    const descriptions = {
        investigation_started: "started investigation",
        evidence_collected: "collected evidence",
        interview_conducted: "conducted interview",
        note_added: "added note",
        investigation_escalated: "escalated investigation",
        investigation_resolved: "resolved investigation",
    };
    return descriptions[actionType] || "performed action";
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const updateStatus = () => {
    router.patch(
        route("compliance.update-status", props.report.id),
        statusUpdate,
        {
            onSuccess: () => {
                showStatusModal.value = false;
            },
        }
    );
};

const startInvestigation = () => {
    router.post(
        route("compliance.start-investigation", props.report.id),
        {},
        {
            onSuccess: () => {
                // Refresh the page to show updated status
                router.reload();
            },
        }
    );
};

const addNote = () => {
    router.post(route("compliance.add-note", props.report.id), noteForm, {
        onSuccess: () => {
            showAddNoteModal.value = false;
            noteForm.note = "";
            noteForm.priority = "medium";
            router.reload();
        },
    });
};

const collectEvidence = () => {
    router.post(
        route("compliance.collect-evidence", props.report.id),
        evidenceForm,
        {
            onSuccess: () => {
                showCollectEvidenceModal.value = false;
                Object.keys(evidenceForm).forEach((key) => {
                    evidenceForm[key] = "";
                });
                router.reload();
            },
        }
    );
};

const conductInterview = () => {
    router.post(
        route("compliance.conduct-interview", props.report.id),
        interviewForm,
        {
            onSuccess: () => {
                showConductInterviewModal.value = false;
                Object.keys(interviewForm).forEach((key) => {
                    if (typeof interviewForm[key] === "boolean") {
                        interviewForm[key] = false;
                    } else {
                        interviewForm[key] = "";
                    }
                });
                router.reload();
            },
        }
    );
};

const escalateInvestigation = () => {
    router.post(route("compliance.escalate", props.report.id), escalateForm, {
            onSuccess: () => {
                showEscalateModal.value = false;
            Object.keys(escalateForm).forEach((key) => {
                escalateForm[key] = "";
            });
            router.reload();
            },
    });
};

const resolveInvestigation = () => {
    router.post(
        route("compliance.resolve-investigation", props.report.id),
        resolveForm,
        {
            onSuccess: () => {
                showResolveModal.value = false;
                Object.keys(resolveForm).forEach((key) => {
                    if (typeof resolveForm[key] === "boolean") {
                        resolveForm[key] = false;
                    } else {
                        resolveForm[key] = "";
                    }
                });
                router.reload();
            },
        }
    );
};
</script>
