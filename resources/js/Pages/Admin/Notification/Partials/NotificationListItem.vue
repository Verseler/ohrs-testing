<script setup lang="ts">
import type { Notification } from "@/Pages/Admin/Notification/notification.types";
import { Bell, CheckIcon } from "lucide-vue-next";
import { formatDateTimeString } from "@/lib/utils";

interface NotificationListProps {
    notification: Notification;
}

const { notification } = defineProps<NotificationListProps>();

const emit = defineEmits<{ (event: "markAsRead", id: string): void }>();

const handleMarkAsRead = (): void => {
    emit("markAsRead", notification.id);
};
</script>

<template>
    <div
        class="transition-colors duration-150 rounded-lg"
        :class="notification.read_at ? 'bg-neutral-50' : 'bg-primary-50'"
    >
        <div class="flex p-4 sm:p-5">
            <!-- Notification icon -->
            <div class="mr-4">
                <div
                    class="flex items-center justify-center rounded-full size-10"
                    :class="
                        notification.read_at
                            ? 'bg-neutral-100'
                            : 'bg-primary-100'
                    "
                >
                    <Bell
                        class="size-4"
                        :class="
                            notification.read_at
                                ? 'text-neutral-500'
                                : 'text-primary-500'
                        "
                    />
                </div>
            </div>

            <!-- Notification content -->
            <div class="flex-1">
                <div
                    class="flex flex-col items-start justify-between md:flex-row"
                >
                    <div>
                        <h3
                            class="text-base font-medium capitalize"
                            :class="
                                notification.read_at
                                    ? 'text-neutral-600'
                                    : 'text-neutral-800'
                            "
                        >
                            {{ notification.type }}
                        </h3>

                        <p
                            class="mt-1 text-sm leading-none md:leading-normal"
                            :class="
                                notification.read_at
                                    ? 'text-neutral-500'
                                    : 'text-gray-600'
                            "
                        >
                            {{ notification.data.message }}
                        </p>
                    </div>

                    <div class="flex-shrink-0 ml-auto md:ml-4">
                        <span class="text-xs text-gray-500">
                            {{ formatDateTimeString(notification.created_at) }}
                        </span>
                    </div>
                </div>

                <button
                    v-if="!notification.read_at"
                    @click="handleMarkAsRead"
                    class="flex items-center ml-auto text-xs transition-colors hover:text-neutral-800 text-neutral-500"
                >
                    <CheckIcon class="w-3 h-3 mr-1" />
                    Mark as read
                </button>
            </div>
        </div>
    </div>
</template>
