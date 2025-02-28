<script setup lang="ts">
import type { CheckboxRootEmits, CheckboxRootProps } from "reka-ui";
import { cn } from "@/lib/utils";
import { Check } from "lucide-vue-next";
import { CheckboxIndicator, CheckboxRoot, useForwardPropsEmits } from "reka-ui";
import { computed, type HTMLAttributes } from "vue";

const props = defineProps<
    CheckboxRootProps & { class?: HTMLAttributes["class"] }
>();
const emits = defineEmits<CheckboxRootEmits>();

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props;

    return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
    <CheckboxRoot
        v-bind="forwarded"
        :class="
            cn(
                'peer h-4 w-4 shrink-0 rounded-sm border  border-neutral-500 shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-primary-500 disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary-500 data-[state=checked]:text-neutral-50 dark:border-primary-400  dark:focus-visible:ring-neutral-300 dark:data-[state=checked]:bg-primary-500 dark:data-[state=checked]:text--primary-400',
                props.class
            )
        "
    >
        <CheckboxIndicator
            class="flex items-center justify-center w-full h-full text-current"
        >
            <slot>
                <Check class="w-4 h-4" />
            </slot>
        </CheckboxIndicator>
    </CheckboxRoot>
</template>
