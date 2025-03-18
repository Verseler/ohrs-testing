<script setup lang="ts">
import { onBeforeUnmount, ref } from "vue";
import { Button } from "@/Components/ui/button";
import { XIcon } from "lucide-vue-next";

interface Props {
    triggerText?: string;
    triggerClass?: string;
    contentClass?: string;
    disabled?: boolean;
    showClose?: boolean;
    closeOnClickOutside?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    triggerText: "Click to show",
    triggerClass: "inline-block",
    contentClass: "",
    disabled: false,
    showClose: true,
    closeOnClickOutside: true,
});

const emit = defineEmits<{
    (e: "open"): void;
    (e: "close"): void;
}>();

const isOpen = ref(false);

// Handle click outside
const handleClickOutside = (event: MouseEvent) => {
    if (!props.closeOnClickOutside) return;

    const target = event.target as HTMLElement;
    if (!target.closest(".relative.inline-block")) {
        close();
    }
};

const open = () => {
    if (props.disabled) return;

    isOpen.value = true;
    emit("open");

    if (props.closeOnClickOutside) {
        setTimeout(() => {
            document.addEventListener("click", handleClickOutside);
        });
    }
};

const close = () => {
    isOpen.value = false;
    emit("close");
    document.removeEventListener("click", handleClickOutside);
};

// Clean up event listener
onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <div class="relative inline-block">
        <!-- Trigger -->
        <div
            v-show="!isOpen"
            @click="open"
            :class="[
                'cursor-pointer transition-colors',
                triggerClass,
                disabled
                    ? 'cursor-not-allowed opacity-50'
                    : 'hover:bg-muted/50',
            ]"
        >
            <slot name="trigger">
                <Button variant="ghost" size="sm" :disabled="disabled">
                    {{ triggerText }}
                </Button>
            </slot>
        </div>

        <!-- Content -->
        <div
            v-show="isOpen"
            class="flex items-center animate-in gap-x-2 fade-in-0 zoom-in-95"
        >
            <div :class="contentClass">
                <slot name="content" />
            </div>

            <!-- Close Button -->
            <div v-if="showClose" @click="close">
                <slot name="close-trigger">
                    <Button variant="ghost" size="icon" class="w-6 h-6">
                        <XIcon class="w-4 h-4" />
                    </Button>
                </slot>
            </div>
        </div>
    </div>
</template>
