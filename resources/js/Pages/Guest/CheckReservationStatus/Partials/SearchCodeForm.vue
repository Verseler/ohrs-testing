<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import { Input, InputError } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { SearchIcon } from "lucide-vue-next";

type SearchCodeFormProps = {
    error: string;
    loading: boolean;
    submit: () => void;
};

const { error, loading, submit } = defineProps<SearchCodeFormProps>();

const model = defineModel<string>();
</script>

<template>
    <form
        @submit.prevent="submit"
        class="p-6 mb-8 bg-white rounded-lg border border-gray-200 shadow-sm"
    >
        <div class="flex flex-col gap-3 pb-2 sm:flex-row">
            <div class="flex-1">
                <Label
                    for="reservation-code"
                    class="block mb-1 text-sm font-medium text-gray-700"
                >
                    Reservation Code
                </Label>
                <Input
                    id="reservation-code"
                    v-model="model"
                    type="text"
                    placeholder="Enter your code (e.g., RES123XX)"
                    class="h-12 shadow-none border-neutral-400"
                    :invalid="!!error"
                    @keyup.enter="submit"
                />

            </div>
            <div class="self-end">
                <Button
                    type="submit"
                    :disabled="loading"
                    class="w-full h-12 text-base"
                >
                    <SearchIcon v-if="!loading" class="mr-2 font-bold size-4" />
                    <span
                        v-if="loading"
                        class="inline-block mr-2 w-4 h-4 rounded-full border-2 border-white animate-spin border-t-transparent"
                    ></span>
                    {{ loading ? "Checking..." : "Check Status" }}
                </Button>
            </div>
        </div>
        <InputError v-if="!!error">{{ error }}</InputError>
    </form>
</template>
