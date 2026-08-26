<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    ContactRound,
    Building2,
    FormInput,
    LayoutGrid,
    Navigation,
    Database,
    Zap,
    Layers,
    PanelRightOpen,
    Radio,
    AlertTriangle,
    Wifi,
    Save,
} from 'lucide-vue-next';
import NavFooter from '@/components/NavFooter.vue';
import NavSection from '@/components/NavSection.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem, type NavSection as NavSectionType } from '@/types';
import { dashboard } from '@/wayfinder/routes';
import AppLogo from './AppLogo.vue';

const crmNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Organizations',
        href: '/organizations',
        icon: Building2,
    },
];

const crmSection: NavSectionType = {
    label: 'CRM demo',
    groups: [
        {
            title: 'Contacts',
            icon: ContactRound,
            items: [
                { title: 'All Contacts', href: '/contacts' },
                { title: 'Create Contact', href: '/contacts/create' },
            ],
        },
    ],
};

const featuresSection: NavSectionType = {
    label: 'Kitchen Sink',
    groups: [
        {
            title: 'Forms',
            icon: FormInput,
            items: [
                { title: 'useForm', href: '/features/forms/use-form' },
                {
                    title: 'Form Component',
                    href: '/features/forms/form-component',
                },
                { title: 'File Uploads', href: '/features/forms/file-uploads' },
                { title: 'Validation', href: '/features/forms/validation' },
                { title: 'Precognition', href: '/features/forms/precognition' },
                {
                    title: 'Optimistic Updates',
                    href: '/features/forms/optimistic-updates',
                    badge: 'v3',
                },
                {
                    title: 'useFormContext',
                    href: '/features/forms/use-form-context',
                },
                { title: 'Dotted Keys', href: '/features/forms/dotted-keys' },
                { title: 'Wayfinder', href: '/features/forms/wayfinder' },
            ],
        },
        {
            title: 'Navigation',
            icon: Navigation,
            items: [
                {
                    title: 'Links & Methods',
                    href: '/features/navigation/links',
                },
                {
                    title: 'Preserve State',
                    href: '/features/navigation/preserve-state',
                },
                {
                    title: 'Preserve Scroll',
                    href: '/features/navigation/preserve-scroll',
                },
                {
                    title: 'View Transitions',
                    href: '/features/navigation/view-transitions',
                },
                {
                    title: 'History Management',
                    href: '/features/navigation/history-management',
                },
                {
                    title: 'Async Requests',
                    href: '/features/navigation/async-requests',
                },
                {
                    title: 'Client-Side Visits',
                    href: '/features/navigation/manual-visits',
                },
                {
                    title: 'Redirects',
                    href: '/features/navigation/redirects',
                },
                {
                    title: 'Scroll Management',
                    href: '/features/navigation/scroll-management',
                },
                {
                    title: 'Instant Visits',
                    href: '/features/navigation/instant-visits',
                    badge: 'v3',
                },
                {
                    title: 'URL Fragments',
                    href: '/features/navigation/url-fragments',
                    badge: 'v3',
                },
            ],
        },
        {
            title: 'Data Loading',
            icon: Database,
            items: [
                {
                    title: 'Deferred Props',
                    href: '/features/data-loading/deferred-props',
                },
                {
                    title: 'Partial Reloads',
                    href: '/features/data-loading/partial-reloads',
                },
                {
                    title: 'Infinite Scroll',
                    href: '/features/data-loading/infinite-scroll',
                },
                {
                    title: 'When Visible',
                    href: '/features/data-loading/when-visible',
                },
                { title: 'Polling', href: '/features/data-loading/polling' },
                {
                    title: 'Prop Merging',
                    href: '/features/data-loading/prop-merging',
                },
                {
                    title: 'Once Props',
                    href: '/features/data-loading/once-props',
                },
                {
                    title: 'Optional Props',
                    href: '/features/data-loading/optional-props',
                },
            ],
        },
        {
            title: 'Prefetching',
            icon: Zap,
            items: [
                {
                    title: 'Link Prefetch',
                    href: '/features/prefetching/link-prefetch',
                },
                {
                    title: 'Stale While Revalidate',
                    href: '/features/prefetching/stale-while-revalidate',
                },
                {
                    title: 'Manual Prefetch',
                    href: '/features/prefetching/manual-prefetch',
                },
                {
                    title: 'Cache Management',
                    href: '/features/prefetching/cache-management',
                },
            ],
        },
        {
            title: 'State Management',
            icon: Save,
            items: [
                { title: 'Remember', href: '/features/state/remember' },
                { title: 'Flash Data', href: '/features/state/flash-data' },
                { title: 'Shared Props', href: '/features/state/shared-props' },
            ],
        },
        {
            title: 'Layouts & Head',
            icon: Layers,
            items: [
                {
                    title: 'Persistent Layouts',
                    href: '/features/layouts/persistent-layouts',
                },
                {
                    title: 'Nested Layouts',
                    href: '/features/layouts/nested-layouts',
                },
                { title: 'Head Component', href: '/features/layouts/head' },
                {
                    title: 'Layout Props',
                    href: '/features/layouts/layout-props',
                    badge: 'v3',
                },
            ],
        },
        {
            title: 'Layers',
            icon: PanelRightOpen,
            items: [
                {
                    title: 'Modals & Slideovers',
                    href: '/features/layers/dialogs',
                    badge: 'v3.7',
                },
                {
                    title: 'Stacked Layers',
                    href: '/features/layers/stacked',
                    badge: 'v3.7',
                },
                {
                    title: 'Local Layers',
                    href: '/features/layers/local',
                    badge: 'v3.7',
                },
                {
                    title: 'Inside a Layer',
                    href: '/features/layers/inside',
                    badge: 'v3.7',
                },
                {
                    title: 'Swapping Layers',
                    href: '/features/layers/wizard',
                    badge: 'v3.7',
                },
                {
                    title: 'Detours',
                    href: '/features/layers/detours',
                    badge: 'v3.7',
                },
                {
                    title: 'Cold Opens',
                    href: '/features/layers/cold',
                    badge: 'v3.7',
                },
                {
                    title: 'Data in a Layer',
                    href: '/features/layers/features',
                    badge: 'v3.7',
                },
            ],
        },
        {
            title: 'Events & Lifecycle',
            icon: Radio,
            items: [
                {
                    title: 'Global Events',
                    href: '/features/events/global-events',
                },
                {
                    title: 'Inertia.once',
                    href: '/features/events/once-events',
                    badge: 'v3.2',
                },
                {
                    title: 'Visit Callbacks',
                    href: '/features/events/visit-callbacks',
                },
                { title: 'Progress Bar', href: '/features/events/progress' },
                {
                    title: 'Location Event',
                    href: '/features/events/location-event',
                    badge: 'v3.6',
                },
            ],
        },
        {
            title: 'Error Handling',
            icon: AlertTriangle,
            items: [
                {
                    title: 'HTTP Exceptions',
                    href: '/features/errors/http-exceptions',
                    badge: 'v3',
                },
                {
                    title: 'Network Errors',
                    href: '/features/errors/network-errors',
                    badge: 'v3',
                },
            ],
        },
        {
            title: 'HTTP',
            icon: Wifi,
            items: [
                {
                    title: 'useHttp',
                    href: '/features/http/use-http',
                    badge: 'v3',
                },
            ],
        },
    ],
};

const footerNavItems: NavItem[] = [
    {
        title: 'Documentation',
        href: 'https://inertiajs.com/docs/v3/getting-started',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavSection :section="crmSection" :items="crmNavItems" />
            <NavSection :section="featuresSection" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
