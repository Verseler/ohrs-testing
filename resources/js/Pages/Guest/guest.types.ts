import type { Reservation, StayDetails } from '@/Pages/Admin/Reservation/reservation.types';
import type { Bed } from '@/Pages/Admin/Room/room.types';

export type Gender = 'any' | 'male' | 'female';

export type Guest = {
    id: number;
    first_name: string;
    last_name: string;
    gender: Omit<Gender, 'any'>;
    office_id: number;
    office: string;
    reservation_id: number;
    reservation: Reservation;
    stay_details: StayDetails;
    created_at: string;
    updated_at: string;
}

export type GuestsFilters = {
    gender: Gender | undefined;
    search: string | undefined;
    sort_by: string | undefined;
    sort_order: 'asc' | 'desc';
}
