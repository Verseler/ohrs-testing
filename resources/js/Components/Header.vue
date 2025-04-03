<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Button } from "@/Components/ui/button";
import { Link } from "@inertiajs/vue3";
import { User } from "@/types";
import Inplace from "@/Components/Inplace.vue";
import { AlignJustify, X, ScanSearch, HouseIcon } from "lucide-vue-next";
import LinkButton from "@/Components/LinkButton.vue";

type HeaderProps = {
    canLogin: boolean;
    user: User | null;
};

const { canLogin, user } = defineProps<HeaderProps>();
</script>

<template>
    <header
        class="flex items-center justify-between w-full p-2 bg-white md:px-4"
    >
        <Link href="/">
            <div class="flex items-center gap-x-2">
                <ApplicationLogo />
                <p class="hidden text-xl font-bold md:block text-primary-500">
                    Online Hostel Reservation System
                </p>
            </div>
        </Link>

        <div class="inline-flex items-center gap-2">
            <nav class="flex items-center gap-x-2">
                <Link :href="route('reservation.checkStatusForm')">
                    <Button class="px-3">
                        <ScanSearch />
                        <span class="hidden md:block">Reservation</span>Status
                    </Button>
                </Link>

                <LinkButton href="/">
                    <HouseIcon />
                </LinkButton>

                <Inplace>
                    <template #trigger>
                        <Button
                            variant="outline"
                            class="text-green-500 border-green-500 hover:bg-green-50 hover:text-green-600"
                        >
                            <AlignJustify />
                        </Button>
                    </template>
                    <template #content>
                        <template v-if="canLogin">
                            <Link v-if="user" :href="route('dashboard')">
                                <Button class="px-6">Dashboard</Button>
                            </Link>
                        </template>

                        <Link v-else :href="route('login')">
                            <Button class="px-6">Log in</Button>
                        </Link>
                    </template>
                    <template #close-trigger>
                        <Button
                            variant="outline"
                            class="text-red-500 border-red-500 hover:bg-red-50 hover:text-red-600"
                        >
                            <X />
                        </Button>
                    </template>
                </Inplace>
            </nav>
        </div>
    </header>
</template>
