<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Button } from "@/Components/ui/button";
import { Link } from "@inertiajs/vue3";
import { User } from "@/types";
import Inplace from "./Inplace.vue";
import { AlignJustify, X, ScanSearch } from "lucide-vue-next";

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
                <p class="text-xl font-bold text-primary-500">
                    <span class="text-yellow-300">H</span>ostel
                    <span class="text-yellow-300">R</span>eservation
                    <span class="text-yellow-300">S</span>ystem
                </p>
            </div>
        </Link>

        <div class="inline-flex items-center gap-2">
            <nav class="flex items-center gap-x-2">
                <Link :href="route('reservation.checkStatusForm')">
                    <Button class="px-3"><ScanSearch /> Reservation Status</Button>
                </Link>

                <template v-if="canLogin">
                    <Link v-if="user" :href="route('dashboard')">
                        <Button class="px-6">Dashboard</Button>
                    </Link>

                    <template v-else>
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
                                <Link :href="route('login')">
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
                    </template>
                </template>
            </nav>
        </div>
    </header>
</template>
