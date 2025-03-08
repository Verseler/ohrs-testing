<script setup lang="ts">
import { Input } from "@/Components/ui/input";
import { ref } from "vue";
import Button from "@/Components/ui/button/Button.vue";
import { Eye, EyeOff } from "lucide-vue-next";

type InputPasswordProps = {
    invalid?: boolean;
};

const { invalid = false } = defineProps<InputPasswordProps>();

const model = defineModel<string>();

const visibility = ref(true);

function toggle() {
    visibility.value = !visibility.value;
}
</script>

<template>
    <div class="relative">
        <Input
            :type="visibility ? 'password' : 'text'"
            v-model="model"
            :invalid="invalid"
            class="pr-8"
        />
        <Button
            @click="toggle"
            type="button"
            size="icon"
            variant="link"
            class="absolute top-0 right-0 text-neutral-400"
        >
            <Eye v-if="visibility" />
            <EyeOff v-else />
        </Button>
    </div>
</template>
