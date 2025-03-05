export interface User {
    id: number;
    name: string;
    email: string;
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
    flash: Flash
};


export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    flash: Flash
}

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

  export type Office = {
        id: number;
        name: string;
        created_at: Date | string;
        updated_at: Date | string;
  }
