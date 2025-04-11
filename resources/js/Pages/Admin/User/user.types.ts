import type { UserRole } from '@/types';

export type UserFilters = {
    role: UserRole;
    search: string;
    sort_by: string;
    sort_order: 'asc' | 'desc';
}
