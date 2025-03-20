import type { Office } from '@/Pages/Admin/Office/office.types';
import type { Reservation } from '@/Pages/Admin/Reservation/reservation.types';

export type Gender = 'any' | 'male' | 'female';

export type Guest = {
    id: number;
    first_name: string;
    last_name: string;
    phone: number;
    gender: Omit<Gender, 'any'>;
    office_id: number;
    office: Office;
    reservation_id: number;
    reservation: Reservation;
    created_at: string;
    updated_at: string;
}

export type GuestsFilters = {
    region_id: number;
    gender: Gender | undefined;
    search: string | undefined;
    sort_by: string | undefined;
    sort_order: 'asc' | 'desc';
}
