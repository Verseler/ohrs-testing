<script setup lang="ts">
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";

type Item = {
    value: string | number;
    label: string;
};

type SelectField = {
    items: Item[];
    label: string;
    placeholder: string;
};

const { items, label, placeholder } = defineProps<SelectField>();

const model = defineModel<string | number | null>();
</script>

<template>
    <Select v-model="model">
        <SelectTrigger class="md:w-40">
            <SelectValue :placeholder="placeholder" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup>
                <SelectLabel>{{ label }}</SelectLabel>
                <SelectItem
                    v-for="item in items"
                    :key="item.value"
                    :value="item.value"
                    class="capitalize"
                >
                    {{ item.label }}
                </SelectItem>
            </SelectGroup>
        </SelectContent>
    </Select>
</template>
