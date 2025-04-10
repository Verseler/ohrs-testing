import type { Guest } from '@/Pages/Guest/guest.types';
import type { Reservation } from '@/Pages/Admin/Reservation/reservation.types';
import type { User } from '@/types';

export type PaymentOption = "full" | "custom";

export type Payment = {
    id: number;
    amount: number;
    or_number: string;
    or_date: Date;
    reservation_id: number;
    created_at: string;
    updated_at: string;
}


export type PaymentExemption = {
    id: number;
    price: number;
    reason: string;
    reservation_id: number;
    reservation: Reservation;
    guest_id: number;
    guest: Guest;
    user_id: number;
    user: User;
    created_at: string;
    updated_at: string;
}
