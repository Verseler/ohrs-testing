<script setup lang="ts">
import SelectField from "@/Components/SelectField.vue";
import { Button } from "@/Components/ui/button";
import { Input, InputError } from "@/Components/ui/input";
import { SearchIcon } from "lucide-vue-next";

type SearchCodeFormProps = {
    error: string;
    loading: boolean;
    submit: () => void;
    hostels: { value: number; label: string }[];
};

const { error, loading, submit, hostels } = defineProps<SearchCodeFormProps>();

const code = defineModel<string>('search');
const hostelId = defineModel<number | null>('hostelId');
</script>

<template>
    <form
        @submit.prevent="submit"
        class="p-5 mb-3 bg-white rounded-lg border border-gray-200 shadow-sm"
    >
        <div class="flex gap-2">
            <Input
                id="reservation-code"
                v-model="code"
                type="text"
                placeholder="Enter your code or name"
                class="h-12 shadow-none border-neutral-400"
                :invalid="!!error"
                @keyup.enter="submit"
                autocomplete="off"
            />

            <SelectField
                :items="hostels"
                label="Hostel"
                placeholder="Select Hostel"
                v-model="hostelId"
                trigger-class="h-12 shadow-none min-w-44 border-neutral-400"
            />

            <Button
                type="submit"
                :disabled="loading"
                class="w-32 h-12 text-base"
            >
                <SearchIcon v-if="!loading" class="font-bold md:mr-1 size-4" />
                <!-- Loading circle animation -->
                <span
                    v-if="loading"
                    class="inline-block rounded-full border-2 border-white animate-spin md:mr-1 size-4 border-t-transparent"
                />
                <span class="hidden md:block">Search</span>
            </Button>
        </div>
        <InputError v-if="!!error">{{ error }}</InputError>
    </form>
</template>
