<script setup lang="ts">
import { cn } from '@/lib/utils'
import { PinInputInput, type PinInputInputProps, useForwardProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<PinInputInputProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props
  return delegated
})

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <PinInputInput v-bind="forwardedProps" :class="cn('relative text-center focus:outline-none focus:ring-2 focus:ring-neutral-950 focus:relative focus:z-10 flex h-9 w-9 items-center justify-center border-y border-r border-neutral-200 text-sm transition-all first:rounded-l-md first:border-l last:rounded-r-md dark:focus:ring-neutral-300 dark:border-neutral-800', props.class)" />
</template>
