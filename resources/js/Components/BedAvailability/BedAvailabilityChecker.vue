<script setup lang="ts">
import { ref, watch } from "vue";
import Dialog from "@/Components/ui/dialog/Dialog.vue";
import DialogContent from "@/Components/ui/dialog/DialogContent.vue";
import DialogHeader from "@/Components/ui/dialog/DialogHeader.vue";
import DialogFooter from "@/Components/ui/dialog/DialogFooter.vue";
import DialogTitle from "@/Components/ui/dialog/DialogTitle.vue";
import DialogDescription from "@/Components/ui/dialog/DialogDescription.vue";
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
import axios from "axios";

type AvailableBedInRoom = {
    id: number;
    name: string;
    eligible_gender: Gender;
    eligible_gender_schedules: Gender[];
    beds_count: number;
};

type BedAvailabilityCheckerProps = {
    hostelId: number;
};

const { hostelId } = defineProps<BedAvailabilityCheckerProps>();

const page = usePage<PageProps>();

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

const loading = ref(false);
const errorMessage = ref<string | null>(null);
const availableBedInRooms = ref<AvailableBedInRoom[]>([]);
const days = ref<string[]>([]);

async function searchAvailableBeds() {
    if (!form.check_in_date || !form.check_out_date) return;

    loading.value = true;
    errorMessage.value = null;
    availableBedInRooms.value = [];

try {
    const response = await axios.get(route('room.checkAvailableBeds'), {
        params: {
            hostel_id: form.hostel_id,
            check_in_date: form.check_in_date,
            check_out_date: form.check_out_date
        }
    });

    if (response.data.success) {
        availableBedInRooms.value = response.data.data?.available_beds_in_rooms as AvailableBedInRoom[];
        days.value = response.data.data?.days as string[];
    } else {
        errorMessage.value = response.data.message;
    }
} catch (error) {
    errorMessage.value = 'An error occurred while searching';
} finally {
    loading.value = false;
}
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
            <DialogContent class="sm:max-w-[850px]">
                <DialogHeader>
                    <DialogTitle>Available Beds</DialogTitle>
                    <DialogDescription>
                        Select dates to view available beds in each room.
                    </DialogDescription>
                </DialogHeader>

                <!-- Date Selection -->
                <div class="grid gap-x-2 sm:grid-cols-2">
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
                        <InputError v-if="!!form.errors.check_out_date">
                            {{ form.errors.check_out_date }}
                        </InputError>
                    </div>
                </div>

                <Button
                    @click="searchAvailableBeds"
                    :disabled="!form.check_in_date || !form.check_out_date || loading"
                    class="mb-4 w-full sm:w-auto"
                    size="lg"
                >
                    <LoaderCircle
                        v-if="loading"
                        class="mr-2 animate-spin size-4"
                    />
                    Check Availability
                </Button>

                <Separator />

                <!-- Results Section -->
                <div class="max-h-[400px] overflow-y-auto pr-1">
                    <div
                        v-if="loading"
                        class="flex justify-center py-8"
                    >
                        <LoaderCircle
                            class="w-8 h-8 animate-spin text-muted-foreground"
                        />
                    </div>

                    <template v-else>
                        <div
                            v-if="availableBedInRooms && availableBedInRooms.length > 0"
                            class="overflow-y-auto space-y-6"
                        >
                            <BedAvailableItem
                                v-for="room in availableBedInRooms"
                                :key="room.id"
                                :name="room.name"
                                :eligibleGender="room.eligible_gender"
                                :eligibleGenderSchedules="
                                    room.eligible_gender_schedules
                                "
                                :availableBeds="room.beds_count"
                                :days="days"
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

                <DialogFooter class="mt-1">
                    <Button variant="outline" @click="closeDialog">
                        Close
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
