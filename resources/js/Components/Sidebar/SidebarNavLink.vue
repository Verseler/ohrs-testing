<script setup lang="ts">
import { cn } from "@/lib/utils";
import { Link } from "@inertiajs/vue3";
import type { LucidIconType } from "@/Components/Sidebar/sidebar.type";

type SidebarNavLinkProps = {
    icon: LucidIconType;
    active: boolean;
    href: string;
};

defineProps<SidebarNavLinkProps>();
</script>

<template>
    <Link
        :href="href"
        :class="
            cn(
                'flex items-center px-4 py-2 transition-colors duration-150 rounded cursor-pointer p-ripple gap-x-2',
                active
                    ? 'bg-neutral-50 text-[#10b981]'
                    : 'text-white hover:bg-primary-600'
            )
        "
    >
        <icon class="size-4" />
        <span
            :class="
                cn('font-medium', active ? 'text-primary-500' : 'text-white')
            "
        >
            <slot />
        </span>
    </Link>
</template>
