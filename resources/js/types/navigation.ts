import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';

export type BreadcrumbItem = {
    title: string;
    href?: string;
};

export type NavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
    badge?: string;
};

export type NavGroup = {
    title: string;
    icon?: LucideIcon;
    items: NavItem[];
};

export type NavSection = {
    label: string;
    groups: NavGroup[];
};
