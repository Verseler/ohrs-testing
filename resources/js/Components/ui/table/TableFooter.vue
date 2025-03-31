<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>
<template>
  <tfoot :class="cn('border-t flex justify-center py-1.5 bg-neutral-100/50 font-medium [&>tr]:last:border-b-0 dark:bg-neutral-800/50', props.class)">
    <slot />
  </tfoot>
</template>
