<script setup lang="ts">
import { TableCell, TableRow } from '@/Components/ui/table';

</script>

<template>
    <TableRow class="border-none">
        <TableCell class="text-lg font-medium text-neutral-800">
            <slot />
        </TableCell>
    </TableRow>
</template>
