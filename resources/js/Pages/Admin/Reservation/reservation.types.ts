import { Guest } from '@/Pages/Guest/guest.types';

export type ReservationStatus = 'pending' | 'canceled' | 'checked_in' | 'checked_out';

export type Reservation = {
    id: number;
    reservation_code: string;
    check_in_date: string;
    check_out_date: string;
    total_billing: number;
    remaining_balance: number;
    status: ReservationStatus;
    first_name: string;
    middle_initial: string | null;
    last_name: string;
    phone: string;
    email: string | null;
    employee_id: string;
    purpose_of_stay: string | null;
    guests: Guest[];
    guest_office_id: number;
    hostel_office_id: number;
}
