<script setup lang="ts">
type PaginatorInfoProps = {
    total: number | null;
    from: number | null;
    to: number | null;
    current_page: number;
};

const { total, from, to, current_page } = defineProps<PaginatorInfoProps>();
</script>

<template>
    <div class="px-2 mx-3 font-medium text-neutral-500">
        <span v-if="total && total > 0" class="hidden text-xs sm:block">
            {{ from }} to {{ to }} of
            {{ total }}
        </span>

        <!-- Mobile screen -->
        <span v-if="total && total > 0" class="block sm:hidden">
            Page {{ current_page }} of {{ total }}
        </span>
    </div>
</template>
