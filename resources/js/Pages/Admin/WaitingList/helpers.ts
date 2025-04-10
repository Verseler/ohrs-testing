import { Bed } from '@/Pages/Admin/Room/room.types';

export function roomScheduledEligibleGender(bed: Bed) {
    return bed?.room?.eligible_gender_schedules?.[0]?.eligible_gender;
}
