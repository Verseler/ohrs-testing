export type Region = {
    id: number;
    name: string;
    created_at: string;
    updated_at: string;
}

export type Office = {
    id: number;
    region_id: number;
    region?: Region;
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


