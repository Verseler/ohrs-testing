<script setup lang="ts">
import { computed, ref, watch } from "vue";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogFooter,
    DialogTitle,
    DialogDescription,
} from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";
import { LoaderCircle } from "lucide-vue-next";
import { Separator } from "@/Components/ui/separator";
import { useForm, usePage } from "@inertiajs/vue3";
import { PageProps } from "@/types";
import { Gender } from "@/Pages/Guest/guest.types";
import BedAvailableItem from "@/Components/BedAvailability/BedAvailableItem.vue";
import { Label } from "@/Components/ui/label";
import { InputError } from "@/Components/ui/input";
import InputDate from "@/Components/ui/input/InputDate.vue";
import { formatDate } from "@/lib/utils";

type AvailableBedInRoom = {
    id: number;
    name: string;
    eligible_gender: Gender;
    beds_count: number;
};

type BedAvailabilityCheckerProps = {
    hostelId: number;
};

const { hostelId } = defineProps<BedAvailabilityCheckerProps>();

const page = usePage<PageProps>();

const availableRooms = computed(() => {
    const response = page.props.response_data as AvailableBedInRoom[];
    return response || null;
});

const isDialogOpen = ref(false);

watch(isDialogOpen, () => {
    if (isDialogOpen.value === false) {
        resetState();
    }
});

const form = useForm({
    hostel_id: hostelId,
    check_in_date: undefined,
    check_out_date: undefined,
});

function searchAvailableBeds() {
    form.get(route("room.checkAvailableBeds"), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function closeDialog() {
    isDialogOpen.value = false;
    resetState();
}

function resetState() {
    page.props.response_data = undefined;
    form.check_in_date = undefined;
    form.check_out_date = undefined;
}
</script>

<template>
    <div>
        <!-- Dialog Trigger -->
        <Button
            @click="isDialogOpen = true"
            variant="outline"
            class="border-primary-500 text-primary-500 hover:text-primary-600 hover:bg-primary-50"
        >
            Available Beds
        </Button>

        <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
            <DialogContent class="sm:max-w-[700px]">
                <DialogHeader>
                    <DialogTitle>Available Beds</DialogTitle>
                    <DialogDescription>
                        Select dates to view available beds in each room.
                    </DialogDescription>
                </DialogHeader>

                <!-- Date Selection -->
                <div class="grid py-1 gap-x-2 sm:grid-cols-2">
                    <div>
                        <Label for="check-in-date"> Check-in Date </Label>
                        <InputDate
                            id="check-in-date"
                            v-model="form.check_in_date"
                            :min="formatDate(new Date())"
                            :invalid="!!form.errors.check_in_date"
                        />
                        <InputError v-if="!!form.errors.check_in_date">
                            {{ form.errors.check_in_date }}
                        </InputError>
                    </div>

                    <div>
                        <Label for="check-out-date"> Check-out Date </Label>
                        <InputDate
                            id="check-out-date"
                            v-model="form.check_out_date"
                            :min="form.check_in_date"
                            :disabled="!form.check_in_date"
                            :invalid="!!form.errors.check_out_date"
                        />
                        <InputError>
                            {{ form.errors.check_out_date }}
                        </InputError>
                    </div>
                </div>

                <Button
                    @click="searchAvailableBeds"
                    :disabled="!form.check_in_date || !form.check_out_date"
                    class="w-full mb-4 sm:w-auto"
                    size="lg"
                >
                    <LoaderCircle
                        v-if="form.processing"
                        class="mr-2 size-4 animate-spin"
                    />
                    Check Availability
                </Button>

                <Separator />

                <!-- Results Section -->
                <div class="max-h-[400px] overflow-y-auto pr-1">
                    <h3 class="mb-2 text-lg font-semibold">Available bed/s</h3>
                    <div
                        v-if="form.processing"
                        class="flex justify-center py-8"
                    >
                        <LoaderCircle
                            class="w-8 h-8 animate-spin text-muted-foreground"
                        />
                    </div>

                    <template v-else>
                        <div
                            v-if="availableRooms && availableRooms.length > 0"
                            class="space-y-4 overflow-y-auto"
                        >
                            <BedAvailableItem
                                v-for="room in availableRooms"
                                :key="room.id"
                                :name="room.name"
                                :eligibleGender="room.eligible_gender"
                                :availableBeds="room.beds_count"
                            />
                        </div>
                        <p
                            v-else
                            class="py-2 text-sm italic text-center text-neutral-500"
                        >
                            No available beds for the selected dates.
                        </p>
                    </template>
                </div>

                <DialogFooter class="mt-4">
                    <Button variant="outline" @click="closeDialog">
                        Close
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
