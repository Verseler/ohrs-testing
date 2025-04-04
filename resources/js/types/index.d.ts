import { Office } from '@/Pages/Admin/Office/office.types';
import type { UserIcon } from 'lucide-vue-next';

export type UserRole = 'admin' | 'super_admin';

export interface User {
    id: number;
    name: string;
    role: UserRole;
    office_id: number;
    office: Office;
    email_verified_at?: string;
}

export interface Flash {
    success?: string | null;
    error?: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    flash: Flash,
    unreadNotificationCount: number
};

export type LaravelPagination<T> = {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: {
      url: string | null;
      label: string;
      active: boolean;
    }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
  };


  export type Severity = 'primary' | 'success' | 'warning' | 'danger' | 'info' | 'secondary'

  export type LucideIcon = typeof UserIcon;
