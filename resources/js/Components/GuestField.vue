<script setup lang="ts">
import { Users } from "lucide-vue-next";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/Components/ui/popover";
import ValueAdjuster from "./ValueAdjuster.vue";
import { Separator } from "./ui/separator";

type GuestFieldProps = {
    female: number;
    male: number;
    increaseFemale: () => void;
    decreaseFemale: () => void;
    increaseMale: () => void;
    decreaseMale: () => void;
    errorTotalGuests: boolean;
    errorTotalFemales: boolean;
    errorTotalMales: boolean;
};

const {
    male,
    female,
    decreaseFemale,
    decreaseMale,
    increaseFemale,
    increaseMale,
} = defineProps<GuestFieldProps>();
</script>

<template>
    <Popover>
        <PopoverTrigger class="w-full">
            <div
                as="button"
                class="flex items-center px-4 py-2 hover:bg-primary-50 gap-x-3"
            >
                <Users
                    class="size-5 text-primary-500"
                    :class="{
                        'text-red-500':
                            errorTotalGuests ||
                            errorTotalMales ||
                            errorTotalFemales,
                    }"
                />
                <div>
                    <p
                        class="font-bold text-primary-500 text-start"
                        :class="{
                            'text-red-500':
                                errorTotalGuests ||
                                errorTotalMales ||
                                errorTotalFemales,
                        }"
                    >
                        Guests
                    </p>
                    <p class="text-sm leading-none text-neutral-700">
                        <span
                            :class="{
                                'text-red-500': errorTotalMales,
                            }"
                        >
                            {{ male }} Male </span
                        >,

                        <span
                            :class="{
                                'text-red-500': errorTotalFemales,
                            }"
                            >{{ female }} Female</span
                        >
                    </p>
                </div>
            </div>
        </PopoverTrigger>
        <PopoverContent>
            <p>Set total guests</p>
            <Separator class="mt-2 mb-4" />
            <div class="mb-2">
                <p class="text-sm text-neutral-600 ms-1.5 mb-1">Male</p>
                <ValueAdjuster
                    :value="male"
                    v-on:increase="increaseMale"
                    v-on:decrease="decreaseMale"
                    :disable-decrease="male <= 0"
                    size="sm"
                    end
                />
            </div>
            <div>
                <p class="text-sm text-neutral-600 ms-1.5 mb-1">Female</p>
                <ValueAdjuster
                    :value="female"
                    v-on:increase="increaseFemale"
                    v-on:decrease="decreaseFemale"
                       :disable-decrease="female <= 0"
                    size="sm"
                    end
                />
            </div>
        </PopoverContent>
    </Popover>
</template>
