<script setup lang="ts">
import { cn } from "@/lib/utils";
import { PrimitiveProps } from "reka-ui";
import { type MessageVariants, messageVariants } from ".";
import { HtmlHTMLAttributes } from "vue";

interface Props extends PrimitiveProps {
    severity?: MessageVariants["severity"];
    class?: HtmlHTMLAttributes["class"];
    size?: MessageVariants["size"];
}

const props = withDefaults(defineProps<Props>(), {
    as: "message",
});
</script>

<template>
    <div :class="cn(messageVariants({ severity, size }), props.class)">
        <slot />
    </div>
</template>
