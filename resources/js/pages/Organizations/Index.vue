<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Search } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import organizationRoutes from '@/wayfinder/routes/organizations';
import type { App, Inertia } from '@/wayfinder/types';

type Paginated<T> = {
    data: T[];
    meta: {
        links: { url: string | null; label: string; active: boolean }[];
    };
};

const props = defineProps<
    Omit<Inertia.Pages.Organizations.Index, 'organizations' | 'filters'> & {
        organizations: Paginated<
            Pick<App.Models.Organization, 'id' | 'name'> & {
                contacts_count: number;
            }
        >;
        filters: { search: string };
    }
>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'CRM' },
    { title: 'Organizations', href: organizationRoutes.index().url },
];

const search = ref(props.filters.search ?? '');
let searchTimeout: ReturnType<typeof setTimeout>;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.visit(organizationRoutes.index().url, {
            data: { search: value || undefined },
            only: ['organizations', 'filters'],
            preserveState: true,
        });
    }, 300);
});
</script>

<template>
    <Head title="Organizations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4">
            <h1 class="text-2xl font-semibold tracking-tight">Organizations</h1>

            <!-- Search -->
            <div class="relative max-w-sm">
                <Search
                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    placeholder="Search organizations..."
                    class="pl-9"
                />
            </div>

            <!-- Organization List -->
            <div class="space-y-2">
                <Link
                    v-for="org in props.organizations.data"
                    :key="org.id"
                    :href="organizationRoutes.show(org.id).url"
                    class="flex items-center gap-4 rounded-lg bg-muted/30 p-4 hover:bg-muted/50"
                >
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/10"
                    >
                        <Building2 class="size-5 shrink-0 text-primary" />
                    </div>
                    <div class="flex-1">
                        <span class="font-medium">{{ org.name }}</span>
                    </div>
                    <Badge variant="outline">
                        {{ org.contacts_count }}
                        {{ org.contacts_count === 1 ? 'contact' : 'contacts' }}
                    </Badge>
                </Link>
            </div>

            <div
                v-if="props.organizations.data.length === 0"
                class="flex flex-col items-center justify-center py-12 text-center"
            >
                <p class="text-muted-foreground">No organizations found.</p>
            </div>

            <!-- Pagination Links -->
            <div
                v-if="props.organizations.meta.links.length > 3"
                class="flex items-center justify-center gap-1"
            >
                <template
                    v-for="link in props.organizations.meta.links"
                    :key="link.label"
                >
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-md px-3 py-1 text-sm hover:bg-accent"
                        :class="{
                            'bg-primary text-primary-foreground hover:bg-primary/90':
                                link.active,
                        }"
                        preserve-state
                    >
                        <!-- eslint-disable-next-line vue/no-v-html -->
                        <span v-html="link.label" />
                    </Link>
                    <span
                        v-else
                        class="rounded-md px-3 py-1 text-sm text-muted-foreground"
                    >
                        <!-- eslint-disable-next-line vue/no-v-html -->
                        <span v-html="link.label" />
                    </span>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
