<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import Button from "@/Components/ui/button/Button.vue";
import type { SharedData } from "@/types";


defineProps({
    canLogin: {
        type: Boolean,
    },
});

const page = usePage<SharedData>();
</script>

<template>
    <Head title="Hostel Reservation" />

    <div class="w-full min-h-screen">
        <header class="container flex items-center justify-between p-2 mx-auto">
            <Link href="/"><ApplicationLogo /></Link>

            <div class="inline-flex items-center gap-2">
                <nav v-if="canLogin">
                    <Link
                        v-if="page.props.auth.user"
                        :href="route('dashboard')"
                    >
                        <Button class="px-6">Dashboard</Button>
                    </Link>

                    <template v-else>
                        <Link :href="route('login')">
                            <Button class="px-6">Log in</Button>
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="mt-6">
            <!-- Main Content -->
        </main>
    </div>
</template>
