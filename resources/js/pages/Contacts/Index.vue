<script setup lang="ts">
import { Head, InfiniteScroll, Link, router } from '@inertiajs/vue3';
import { Heart, Plus, Search } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import contactRoutes from '@/wayfinder/routes/contacts';
import type { App, Inertia } from '@/wayfinder/types';

type CursorPaginated<T> = {
    data: T[];
    next_cursor: string | null;
    next_page_url: string | null;
    prev_cursor: string | null;
    prev_page_url: string | null;
};

const props = defineProps<
    Omit<Inertia.Pages.Contacts.Index, 'contacts' | 'filters'> & {
        contacts: CursorPaginated<
            App.Models.Contact & {
                organization?: Pick<
                    App.Models.Organization,
                    'id' | 'name'
                > | null;
            }
        >;
        filters: { search: string; favorite: boolean };
    }
>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'CRM' },
    { title: 'Contacts', href: contactRoutes.index().url },
];

const search = ref(props.filters.search ?? '');
const favoriteFilter = ref(props.filters.favorite);

let searchTimeout: ReturnType<typeof setTimeout>;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.visit(contactRoutes.index().url, {
            data: {
                search: value || undefined,
                favorite: favoriteFilter.value ? true : undefined,
            },
            only: ['contacts', 'filters'],
            reset: ['contacts'],
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
});

function toggleFavoriteFilter() {
    favoriteFilter.value = !favoriteFilter.value;
    router.visit(contactRoutes.index().url, {
        data: {
            search: search.value || undefined,
            favorite: favoriteFilter.value ? true : undefined,
        },
        only: ['contacts', 'filters'],
        reset: ['contacts'],
        preserveState: true,
    });
}
</script>

<template>
    <Head title="Contacts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold tracking-tight">Contacts</h1>
                <Button as-child>
                    <Link :href="contactRoutes.create().url">
                        <Plus class="size-4" />
                        Add Contact
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-3">
                <div class="relative max-w-sm flex-1">
                    <Search
                        class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        placeholder="Search contacts..."
                        class="pl-9"
                    />
                </div>
                <Button
                    variant="outline"
                    size="sm"
                    :class="{ 'bg-accent': favoriteFilter }"
                    @click="toggleFavoriteFilter"
                >
                    <Heart
                        class="size-4"
                        :class="{ 'fill-current text-red-500': favoriteFilter }"
                    />
                    Favorites
                </Button>
            </div>

            <!-- Contact List -->
            <InfiniteScroll
                data="contacts"
                :buffer="300"
                preserve-url
                class="space-y-2"
            >
                <Link
                    v-for="contact in props.contacts.data"
                    :key="contact.id"
                    :href="contactRoutes.show(contact.id).url"
                    prefetch="hover"
                    class="flex items-center gap-4 rounded-lg bg-muted/30 p-4 hover:bg-muted/50"
                >
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-medium text-primary"
                    >
                        {{ contact.first_name[0] }}{{ contact.last_name[0] }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium"
                                >{{ contact.first_name }}
                                {{ contact.last_name }}</span
                            >
                            <Heart
                                v-if="contact.is_favorite"
                                class="size-3 shrink-0 fill-red-500 text-red-500"
                            />
                        </div>
                        <div class="truncate text-sm text-muted-foreground">
                            {{ contact.email }}
                        </div>
                    </div>
                    <Badge v-if="contact.organization" variant="secondary">
                        {{ contact.organization.name }}
                    </Badge>
                </Link>

                <template #loading>
                    <div class="flex justify-center py-4">
                        <div class="text-sm text-muted-foreground">
                            Loading more contacts...
                        </div>
                    </div>
                </template>
            </InfiniteScroll>

            <div
                v-if="props.contacts.data.length === 0"
                class="flex flex-col items-center justify-center py-12 text-center"
            >
                <p class="text-muted-foreground">No contacts found.</p>
            </div>
        </div>
    </AppLayout>
</template>
