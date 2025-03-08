export type Office = {
    id: number;
    name: string;
    has_hostel: boolean;
    created_at: string;
    updated_at: string;
}

export type Filters = {
    search: string | undefined;
    sort_by: string | undefined;
    sort_order: 'asc' | 'desc';
}
