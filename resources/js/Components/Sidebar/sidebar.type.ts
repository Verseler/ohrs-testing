import { User } from 'lucide-vue-next'

export type LucidIconType = typeof User;

export type NavItem = {
    heading?: string,
    items: [
        {
            label: string,
            route: string,
            path: string,
            icon: LucidIconType,
        },
    ],
};
