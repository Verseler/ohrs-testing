import { User } from 'lucide-vue-next'

export type LucidIconType = typeof User;

export type NavItem = {
    heading?: string,
    items: Item[],
};

type  Item = {
    label: string,
    route: string,
    path: string,
    icon: LucidIconType,
};
