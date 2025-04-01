<script setup lang="ts">
import LinkButton from "@/Components/LinkButton.vue";
import { PageProps } from "@/types";
import { usePage } from "@inertiajs/vue3";
import { Bell } from "lucide-vue-next";
import { computed } from "vue";

const page = usePage<PageProps>();

const unreadCount = computed(() => page.props.unreadNotificationCount);
</script>

<template>
    <LinkButton
        :href="route('notification.list')"
        variant="link"
        size="icon"
        class="relative transition-colors rounded-full bg-neutral-50 text-neutral-500 hover:text-neutral-700"
    >
        <Bell />
        <div
            v-if="unreadCount"
            class="absolute grid text-[0.55rem] text-center text-white bg-red-500 rounded-full place-content-center min-w-3 px-0.5 h-3 top-1"
            :class="{
                '-right-1': unreadCount > 99,
                'right-0.5': unreadCount < 10,
            }"
        >
            {{ unreadCount > 99 ? "99+" : unreadCount }}
        </div>
    </LinkButton>
</template>
