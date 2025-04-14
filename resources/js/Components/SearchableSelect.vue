<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue'
import { CheckIcon, ChevronDown, X } from 'lucide-vue-next'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList
} from '@/Components/ui/command'
import {
  Popover,
  PopoverContent,
  PopoverTrigger
} from '@/Components/ui/popover'
import { Button } from '@/Components/ui/button'
import { cn } from '@/lib/utils'

type Option = {
  value: string | number
  label: string
}

type Props = {
  modelValue: string | number | undefined
  options: Option[]
  placeholder?: string
  searchPlaceholder?: string
  emptyMessage?: string
  disabled?: boolean
  fullWidth?: boolean
  label?: string
  error?: string
  clearable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Select an option',
  searchPlaceholder: 'Search...',
  emptyMessage: 'No results found.',
  disabled: false,
  fullWidth: true,
  clearable: false
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number | null): void
  (e: 'change', value: string | number | null): void
}>()

const open = ref(false)
const searchQuery = ref('')
const selectedOption = ref<Option | null>(null)

// Find the initially selected option
const initializeSelectedOption = () => {
  if (props.modelValue) {
    const option = props.options.find(opt => opt.value === props.modelValue)
    if (option) {
      selectedOption.value = option
    }
  } else {
    selectedOption.value = null
  }
}

// Filter options based on search query
const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options

  const query = searchQuery.value.toLowerCase()
  return props.options.filter(option =>
    option.label.toLowerCase().includes(query)
  )
})

// Handle selection
const selectOption = (option: Option) => {
  selectedOption.value = option
  emit('update:modelValue', option.value)
  emit('change', option.value)
  open.value = false
  searchQuery.value = ''
}

// Clear selection
const clearSelection = () => {
  selectedOption.value = null
  emit('update:modelValue', null)
  emit('change', null)
}

// Reset search when popover closes
watch(open, (isOpen) => {
  if (!isOpen) {
    nextTick(() => {
      searchQuery.value = ''
    })
  }
})

// Watch for external model changes
watch(() => props.modelValue, () => {
  initializeSelectedOption()
}, { immediate: true })

// Watch for options changes
watch(() => props.options, () => {
  initializeSelectedOption()
}, { deep: true })
</script>

<template>
  <div :class="{ 'w-full': fullWidth }">
    <div v-if="label" class="text-sm font-medium mb-2">{{ label }}</div>

    <Popover v-model:open="open">
      <PopoverTrigger as-child class="px-3">
        <Button
          variant="outline"
          role="combobox"
          :aria-expanded="open"
          :class="[
            'relative justify-between',
            fullWidth ? 'w-full' : 'w-[200px]',
            error ? 'border-destructive' : '',
            disabled ? 'opacity-50 cursor-not-allowed' : ''
          ]"
          :disabled="disabled"
        >
          <span v-if="selectedOption" class="truncate">
            {{ selectedOption.label }}
          </span>
          <span v-else class="text-neutral-500 font-normal">
            {{ placeholder }}
          </span>
          <div class="flex items-center">
            <X
              v-if="clearable && selectedOption"
              class="mr-1 h-4 w-4 shrink-0 opacity-50 hover:opacity-100"
              @click.stop="clearSelection"
            />
            <ChevronDown class="-m-1 text-neutral-500 h-4 w-4 shrink-0 opacity-50" />
          </div>
        </Button>
      </PopoverTrigger>
      <PopoverContent
        class="p-0"
        :class="fullWidth ? 'w-[var(--radix-popover-trigger-width)]' : 'w-[200px]'"
      >
        <Command>
          <CommandInput
            v-model="searchQuery"
            :placeholder="searchPlaceholder"
            class="border-none focus:ring-0 px-1"
          />
          <CommandList class="min-w-80">
            <CommandEmpty>{{ emptyMessage }}</CommandEmpty>
            <CommandGroup>
              <CommandItem
                v-for="option in filteredOptions"
                :key="option.value"
                :value="option.value"
                @select="selectOption(option)"
                class="px-1 font-normal"
              >
                <CheckIcon
                  :class="cn(
                    'mr-2 h-4 w-4',
                    selectedOption?.value === option.value
                      ? 'opacity-100'
                      : 'opacity-0'
                  )"
                />
                {{ option.label }}
              </CommandItem>
            </CommandGroup>
          </CommandList>
        </Command>
      </PopoverContent>
    </Popover>

    <div v-if="error" class="text-sm text-destructive mt-1">
      {{ error }}
    </div>
  </div>
</template>
