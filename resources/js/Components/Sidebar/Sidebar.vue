<script setup lang="ts">
import { computed, ref } from "vue";
import { router, usePage, Link } from "@inertiajs/vue3";
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import SidebarNavLink from "@/Components/Sidebar/SidebarNavLink.vue";
import Button from "@/Components/ui/button/Button.vue";
import {
    Bed,
    ChartColumnIncreasing,
    Hotel,
    LogOut,
    CalendarCheck,
    CalendarClock,
    Users,
    FileDown,
    ShieldUser,
} from "lucide-vue-next";
import type { NavItem } from "@/Components/Sidebar/sidebar.type";
import type { PageProps } from "@/types";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";
import NotificationLinkButton from "@/Components/NotificationLinkButton.vue";
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuItem,
} from "@/Components/ui/sidebar";

const page = usePage<PageProps>();

const isSuperAdmin = computed(
    () => page.props.auth.user.role === "super_admin"
);

const nav = ref<Array<NavItem>>([
    {
        heading: "ANALYTICS",
        items: [
            {
                label: "Dashboard",
                route: "dashboard",
                path: "/dashboard",
                icon: ChartColumnIncreasing,
                accessible: true,
            },
            {
                label: "Generate Report",
                route: "reports",
                path: "/reports",
                icon: FileDown,
                accessible: true,
            },
        ],
    },
    {
        heading: "MANAGEMENT",
        items: [
            {
                label: "Waiting List",
                route: "reservation.waitingList",
                path: "/waiting-list",
                icon: CalendarClock,
                accessible: true,
            },
            {
                label: "Reservation",
                route: "reservation.list",
                path: "/reservations",
                icon: CalendarCheck,
                accessible: true,
            },
            {
                label: "Room",
                route: "room.list",
                path: "/rooms",
                icon: Bed,
                accessible: true,
            },
            {
                label: "Guest",
                route: "guest.list",
                path: "/guests",
                icon: Users,
                accessible: true,
            },
            {
                label: "Office",
                route: "office.list",
                path: "/offices",
                icon: Hotel,
                accessible: isSuperAdmin.value,
            },
            {
                label: "Users",
                route: "user.list",
                path: "/users",
                icon: ShieldUser,
                accessible: isSuperAdmin.value,
            },
        ],
    },
]);

const logoutConfirmation = ref(false);

function showLogoutConfirmation() {
    logoutConfirmation.value = true;
}

function handleLogout() {
    router.visit(route("logout"), { method: "post" });
}
</script>

<template>
    <Sidebar>
        <SidebarHeader>
            <Link href="/">
                <ApplicationLogo class="mx-auto size-28" />
                <p class="text-[1.1rem] font-bold text-center">
                    <span class="text-yellow-200">H</span>ostel
                    <span class="text-yellow-200">R</span>eservation
                    <span class="text-yellow-200">S</span>ystem
                </p>
            </Link>
        </SidebarHeader>

        <SidebarContent class="pt-6">
            <SidebarGroup v-for="navSection of nav" :key="navSection.heading">
                <SidebarGroupLabel class="text-neutral-200">
                    {{ navSection.heading }}
                </SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem
                            v-for="item in navSection.items"
                            :key="item.label"
                        >
                            <SidebarNavLink
                                v-if="item.accessible"
                                :href="route(item.route)"
                                :icon="item.icon"
                                :active="$page.url.includes(item.path)"
                            >
                                {{ item.label }}
                            </SidebarNavLink>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter class="flex flex-row justify-between">
            <div class="flex items-center justify-between gap-x-2">
                <Avatar>
                    <AvatarFallback>V</AvatarFallback>
                </Avatar>
                <div class="flex flex-col leading-none text-white">
                    <span aria-label="user-name" class="text-xs">
                        {{ page.props.auth.user.name }}
                    </span>
                    <span
                        aria-label="user-role"
                        class="text-[0.60rem] text-neutral-200 capitalize"
                    >
                        {{
                            page.props?.auth?.user?.role === "super_admin"
                                ? "Super Admin"
                                : "Admin"
                        }}
                    </span>
                </div>
            </div>

            <div class="flex space-x-1">
                <NotificationLinkButton
                    class="text-white bg-transparent hover:text-neutral-300"
                />
                <Button
                    @click="showLogoutConfirmation"
                    size="icon"
                    class="shadow-none hover:bg-primary-700 hover:text-neutral-100"
                >
                    <LogOut />
                </Button>
            </div>
        </SidebarFooter>

        <Alert
            :open="logoutConfirmation"
            @update:open="logoutConfirmation = $event"
            :onConfirm="handleLogout"
            title="Are you sure you want to logout?"
            severity="danger"
            confirm-label="Logout"
        />
    </Sidebar>
</template>
