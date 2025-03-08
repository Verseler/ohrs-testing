<script setup lang="ts">
import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { Avatar, AvatarFallback } from "@/Components/ui/avatar";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import SidebarHeading from "@/Components/Sidebar/SidebarHeading.vue";
import SidebarNavLink from "@/Components/Sidebar/SidebarNavLink.vue";
import Button from "@/Components/ui/button/Button.vue";
import { Bed, ChartColumnIncreasing, Hotel, LogOut } from "lucide-vue-next";
import { capitalized } from "@/lib/utils";
import type { NavItem } from "./sidebar.type";
import { SharedData } from "@/types";
import Alert from "@/Components/ui/alert-dialog/Alert.vue";

const page = usePage<SharedData>();

const nav = ref<Array<NavItem>>([
    {
        heading: "ANALYTICS",
        items: [
            {
                label: "Dashboard",
                route: "dashboard",
                path: "/dashboard",
                icon: ChartColumnIncreasing,
            },
        ],
    },
    {
        heading: "MANAGEMENT",
        items: [
            // {
            //     label: "Reservation",
            //     route: "reservation.list",
            //     path: "/reservations",
            //     icon: "pi pi-book",
            // },
            {
                label: "Room",
                route: "room.list",
                path: "/rooms",
                icon: Bed,
            },
            {
                label: "Office",
                route: "office.list",
                path: "/offices",
                icon: Hotel,
            },
            //     {
            //         label: "Guest",
            //         route: "guest.list",
            //         path: "/guests",
            //         icon: "pi pi-users",
            //     },
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
    <div
        class="flex fixed z-10 flex-col w-72 min-h-screen border-r border-r-primary-600 bg-primary-500"
    >
        <div
            class="flex flex-col gap-x-2 items-center px-6 pt-4 text-white shrink-0"
        >
            <ApplicationLogo class="size-28" />
            <p class="text-[1.2rem] font-bold">
                <span class="text-yellow-200">H</span>ostel
                <span class="text-yellow-200">R</span>eservation
                <span class="text-yellow-200">S</span>ystem
            </p>
        </div>

        <div class="overflow-y-auto mt-12">
            <ul class="px-4 space-y-8 list-none">
                <li v-for="navSection of nav" :key="navSection.heading">
                    <SidebarHeading>{{ navSection.heading }}</SidebarHeading>
                    <ul class="overflow-hidden space-y-1.5 list-none">
                        <li
                            v-for="navItem of navSection.items"
                            :key="navItem.path"
                        >
                            <SidebarNavLink
                                :href="route(navItem.route)"
                                :icon="navItem.icon"
                                :active="$page.url.includes(navItem.path)"
                            >
                                {{ navItem.label }}
                            </SidebarNavLink>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

        <div
            class="flex gap-x-2 justify-between items-center p-2 mt-auto bg-primary-600"
        >
            <div class="flex gap-x-2 justify-between items-center">
                <Avatar>
                    <AvatarFallback>V</AvatarFallback>
                </Avatar>
                <div class="flex flex-col leading-none text-white">
                    <span aria-label="user-name" class="text-sm">
                        {{ page.props.auth.user.name }}
                    </span>
                    <span
                        aria-label="user-role"
                        class="text-[0.65rem] text-neutral-200"
                    >
                        {{ capitalized(page.props.auth.user.role ?? "Admin") }}
                    </span>
                </div>
            </div>

            <Button
                @click="showLogoutConfirmation"
                size="icon"
                class="shadow-none hover:bg-primary-700 hover:text-neutral-100"
            >
                <LogOut />
            </Button>
        </div>

        <Alert
            :open="logoutConfirmation"
            @update:open="logoutConfirmation = $event"
            :onConfirm="handleLogout"
            title="Are you sure you want to logout?"
            severity="danger"
            confirm-label="Logout"
        />
    </div>
</template>
