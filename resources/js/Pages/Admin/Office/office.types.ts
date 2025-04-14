export type Office = {
    id: number;
    name: string;
    has_hostel: boolean;
    hostel_name?: string | undefined;
    created_at: string;
    updated_at: string;
}

export type OfficeFilters = {
    search: string | undefined;
    sort_by: string | undefined;
    sort_order: 'asc' | 'desc';
}


