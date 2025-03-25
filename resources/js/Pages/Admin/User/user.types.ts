import type { UserRole } from '@/types';

export type UserFilters = {
    region_id: number;
    role: UserRole;
    search: string;
    sort_by: string;
    sort_order: 'asc' | 'desc';
}
