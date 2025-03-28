<script setup lang="ts">
import BackLink from "@/Components/BackLink.vue";
import PageHeader from "@/Components/PageHeader.vue";
import Button from "@/Components/ui/button/Button.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import type { Notification } from "@/Pages/Admin/Notification/notification.types";
import { LaravelPagination } from "@/types";
import { Head, router, usePoll } from "@inertiajs/vue3";
import { Bell, CheckIcon, RefreshCwIcon } from "lucide-vue-next";
import NotificationListItem from "@/Pages/Admin/Notification/Partials/NotificationListItem.vue";
import LinkButton from "@/Components/LinkButton.vue";
import { watch } from "vue";

usePoll(5000);

type NotificationListProps = {
    notifications: LaravelPagination<Notification>;
    unreadCount: number;
};

const { notifications, unreadCount } = defineProps<NotificationListProps>();

function markAsRead(id: string) {
    router.put(
        route("notification.markAsRead", {
            id: id,
        })
    );
}

// Mark all notifications as read
const markAllAsRead = (): void => {
    router.put(route("notification.markAllAsRead"));
};
</script>

<template>
    <Head title="Notifications" />

    <AuthenticatedLayout>
        <div class="flex items-center justify-between my-2">
            <PageHeader class="!my-0">
                <template #icon><Bell /></template>
                <template #title>Notifications</template>
            </PageHeader>

            <BackLink />
        </div>

        <div>
            <!-- Page header -->
            <div class="flex items-center justify-between my-3">
                <div>
                    <span class="text-gray-600">You have </span>
                    <span class="font-medium">{{ unreadCount }} unread</span>
                    <span class="text-gray-600"> notifications</span>
                </div>

                <div class="space-x-2">
                    <Button
                        v-if="unreadCount > 1"
                        @click="markAllAsRead"
                        class="bg-primary-500 hover:bg-primary-600"
                    >
                        <CheckIcon class="mr-1 size-4" />
                        Mark all as read
                    </Button>
                </div>
            </div>

            <div class="space-y-3">
                <NotificationListItem
                    v-for="notification in notifications.data"
                    :notification="notification"
                    @mark-as-read="markAsRead"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
