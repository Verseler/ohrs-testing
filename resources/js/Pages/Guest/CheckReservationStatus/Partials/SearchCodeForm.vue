<script setup lang="ts">
import { Button } from "@/Components/ui/button";
import { Input, InputError } from "@/Components/ui/input";
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
        class="p-5 mb-3 bg-white border border-gray-200 rounded-lg shadow-sm"
    >
        <div class="flex gap-3">
            <Input
                id="reservation-code"
                v-model="model"
                type="text"
                placeholder="Enter your code or name"
                class="h-12 shadow-none border-neutral-400"
                :invalid="!!error"
                @keyup.enter="submit"
                autocomplete="off"
            />
            <div class="self-end">
                <Button
                    type="submit"
                    :disabled="loading"
                    class="w-full h-12 text-base"
                >
                    <SearchIcon v-if="!loading" class="font-bold md:mr-1 size-4" />
                    <!-- Loading circle animation -->
                    <span
                        v-if="loading"
                        class="inline-block w-4 h-4 border-2 border-white rounded-full md:mr-2 animate-spin border-t-transparent"
                    />
                    <span class="hidden md:block">Search</span>
                </Button>
            </div>
        </div>
        <InputError v-if="!!error">{{ error }}</InputError>
    </form>
</template>
