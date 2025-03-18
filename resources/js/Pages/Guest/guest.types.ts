export type Gender = 'any' | 'male' | 'female';

export type Guest = {
    id: number;
    first_name: string;
    last_name: string;
    phone: number;
    gender: Omit<Gender, 'any'>;
    office_id: number;
    reservation_id: number;
    created_at: string;
    updated_at: string;
}
