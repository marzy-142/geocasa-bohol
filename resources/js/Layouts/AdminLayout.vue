<script setup>
import { computed, ref } from "vue";
import { usePage, useForm } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import {
    Bars3Icon,
    XMarkIcon,
    HomeIcon,
    UsersIcon,
    BuildingOfficeIcon,
    DocumentTextIcon,
    CreditCardIcon,
    ClipboardDocumentListIcon,
    ChartBarIcon,
    ExclamationTriangleIcon,
    BellIcon,
    UserGroupIcon,
} from "@heroicons/vue/24/outline";
import NotificationDropdown from "@/Components/NotificationDropdown.vue";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const sidebarOpen = ref(false);

const logoutForm = useForm({});

const logout = () => {
    logoutForm.post(route("logout"));
};

const navigation = [
    {
        name: "Dashboard",
        href: "/admin/dashboard",
        icon: HomeIcon,
        current: false,
    },
    {
        name: "Brokers",
        href: "/admin/brokers",
        icon: UsersIcon,
        current: false,
    },
    {
        name: "Client Assignments",
        href: "/admin/client-assignments",
        icon: UserGroupIcon,
        current: false,
    },
    {
        name: "Broker Analytics",
        href: "/admin/broker-analytics-page",
        icon: ChartBarIcon,
        current: false,
    },
    {
        name: "Properties",
        href: "/admin/properties",
        icon: BuildingOfficeIcon,
        current: false,
    },
    {
        name: "Inquiries",
        href: "/inquiries",
        icon: DocumentTextIcon,
        current: false,
    },
    {
        name: "Transactions",
        href: "/transactions",
        icon: CreditCardIcon,
        current: false,
    },
    {
        name: "Seller Requests",
        href: "/seller-requests",
        icon: ClipboardDocumentListIcon,
        current: false,
    },
    {
        name: "Compliance",
        href: "/admin/compliance",
        icon: ExclamationTriangleIcon,
        current: false,
    },
    {
        name: "Reports",
        href: "/admin/reports",
        icon: ChartBarIcon,
        current: false,
    },
];

const reportsNavigation = [
    { name: "Overview", href: "/admin/reports", current: false },
    { name: "Brokers", href: "/admin/reports/brokers", current: false },
    { name: "Properties", href: "/admin/reports/properties", current: false },
    { name: "Inquiries", href: "/admin/reports/inquiries", current: false },
    { name: "Compliance", href: "/admin/reports/compliance", current: false },
];

const isReportsPage = computed(() => {
    return page.url.startsWith("/admin/reports");
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile sidebar -->
        <div
            v-show="sidebarOpen"
            class="relative z-50 lg:hidden"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-gray-900/80"></div>
            <div class="fixed inset-0 flex">
                <div class="relative mr-16 flex w-full max-w-xs flex-1">
                    <div
                        class="absolute left-full top-0 flex w-16 justify-center pt-5"
                    >
                        <button
                            type="button"
                            class="-m-2.5 p-2.5"
                            @click="sidebarOpen = false"
                        >
                            <span class="sr-only">Close sidebar</span>
                            <XMarkIcon
                                class="h-6 w-6 text-white"
                                aria-hidden="true"
                            />
                        </button>
                    </div>
                    <div
                        class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-2"
                    >
                        <div class="flex h-16 shrink-0 items-center">
                            <Link href="/" class="flex items-center space-x-3">
                                <div
                                    class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center"
                                >
                                    <svg
                                        class="w-5 h-5 text-white"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <h1 class="text-lg font-bold text-gray-900">
                                        GeoCasa Bohol
                                    </h1>
                                    <p class="text-xs text-gray-500 -mt-1">
                                        Admin Panel
                                    </p>
                                </div>
                            </Link>
                        </div>
                        <nav class="flex flex-1 flex-col">
                            <ul
                                role="list"
                                class="flex flex-1 flex-col gap-y-7"
                            >
                                <li>
                                    <ul role="list" class="-mx-2 space-y-1">
                                        <li
                                            v-for="item in navigation"
                                            :key="item.name"
                                        >
                                            <Link
                                                :href="item.href"
                                                :class="[
                                                    item.current
                                                        ? 'bg-gray-50 text-blue-600'
                                                        : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50',
                                                    'group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold',
                                                ]"
                                            >
                                                <component
                                                    :is="item.icon"
                                                    :class="[
                                                        item.current
                                                            ? 'text-blue-600'
                                                            : 'text-gray-400 group-hover:text-blue-600',
                                                        'h-6 w-6 shrink-0',
                                                    ]"
                                                    aria-hidden="true"
                                                />
                                                {{ item.name }}
                                            </Link>
                                        </li>
                                    </ul>
                                </li>
                                <li v-if="isReportsPage">
                                    <div
                                        class="text-xs font-semibold leading-6 text-gray-400"
                                    >
                                        Reports
                                    </div>
                                    <ul
                                        role="list"
                                        class="-mx-2 mt-2 space-y-1"
                                    >
                                        <li
                                            v-for="item in reportsNavigation"
                                            :key="item.name"
                                        >
                                            <Link
                                                :href="item.href"
                                                :class="[
                                                    item.current
                                                        ? 'bg-gray-50 text-blue-600'
                                                        : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50',
                                                    'group flex gap-x-3 rounded-md p-2 pl-8 text-sm leading-6 font-semibold',
                                                ]"
                                            >
                                                {{ item.name }}
                                            </Link>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div
            class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col"
        >
            <div
                class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white px-6"
            >
                <div class="flex h-16 shrink-0 items-center">
                    <Link href="/" class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center"
                        >
                            <svg
                                class="w-5 h-5 text-white"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900">
                                GeoCasa Bohol
                            </h1>
                            <p class="text-xs text-gray-500 -mt-1">
                                Admin Panel
                            </p>
                        </div>
                    </Link>
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                <li v-for="item in navigation" :key="item.name">
                                    <Link
                                        :href="item.href"
                                        :class="[
                                            item.current
                                                ? 'bg-gray-50 text-blue-600'
                                                : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50',
                                            'group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold',
                                        ]"
                                    >
                                        <component
                                            :is="item.icon"
                                            :class="[
                                                item.current
                                                    ? 'text-blue-600'
                                                    : 'text-gray-400 group-hover:text-blue-600',
                                                'h-6 w-6 shrink-0',
                                            ]"
                                            aria-hidden="true"
                                        />
                                        {{ item.name }}
                                    </Link>
                                </li>
                            </ul>
                        </li>
                        <li v-if="isReportsPage">
                            <div
                                class="text-xs font-semibold leading-6 text-gray-400"
                            >
                                Reports
                            </div>
                            <ul role="list" class="-mx-2 mt-2 space-y-1">
                                <li
                                    v-for="item in reportsNavigation"
                                    :key="item.name"
                                >
                                    <Link
                                        :href="item.href"
                                        :class="[
                                            item.current
                                                ? 'bg-gray-50 text-blue-600'
                                                : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50',
                                            'group flex gap-x-3 rounded-md p-2 pl-8 text-sm leading-6 font-semibold',
                                        ]"
                                    >
                                        {{ item.name }}
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="lg:pl-72">
            <!-- Top navigation -->
            <div
                class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8"
            >
                <button
                    type="button"
                    class="-m-2.5 p-2.5 text-gray-700 lg:hidden"
                    @click="sidebarOpen = true"
                >
                    <span class="sr-only">Open sidebar</span>
                    <Bars3Icon class="h-6 w-6" aria-hidden="true" />
                </button>

                <div
                    class="h-6 w-px bg-gray-200 lg:hidden"
                    aria-hidden="true"
                ></div>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1"></div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- Notifications -->
                        <NotificationDropdown
                            v-if="$page.props.auth.user"
                            :notifications="$page.props.notifications || []"
                        />

                        <!-- Profile dropdown -->
                        <div class="relative">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center"
                                    >
                                        <span
                                            class="text-sm font-medium text-gray-700"
                                            >{{ user?.name?.charAt(0) }}</span
                                        >
                                    </div>
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        {{ user?.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">Admin</p>
                                </div>
                                <button
                                    @click="logout"
                                    class="ml-3 text-sm text-gray-500 hover:text-red-600"
                                >
                                    Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <main class="py-10">
                <div class="px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
