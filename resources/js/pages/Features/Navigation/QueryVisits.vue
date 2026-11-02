<script setup lang="ts">
import {
    Deferred,
    Head,
    Link,
    router,
    useForm,
    usePage,
} from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import CodeBlock from '@/components/CodeBlock.vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    filters: {
        search: string;
        organizations: number[];
        favorites: boolean;
    };
    organizations: { id: number; name: string }[];
    contacts: {
        id: number;
        name: string;
        organization: string | null;
        isFavorite: boolean;
    }[];
    matches?: number;
    lastRequest: {
        method: string;
        body: string | null;
        at: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Navigation' },
    { title: 'QUERY Visits' },
];

const url = '/features/navigation/query-visits';

const page = usePage();

const form = useForm({
    search: props.filters.search,
    organizations: props.filters.organizations,
    favorites: props.filters.favorites,
});

// QUERY visits preserve state, so keep the fields in sync with what the server applied
watch(
    () => props.filters,
    (filters) => {
        form.search = filters.search;
        form.organizations = filters.organizations;
        form.favorites = filters.favorites;
    },
);

const formattedBody = computed(() =>
    props.lastRequest.body
        ? JSON.stringify(JSON.parse(props.lastRequest.body), null, 2)
        : null,
);

function toggleOrganization(id: number) {
    form.organizations = form.organizations.includes(id)
        ? form.organizations.filter((organization) => organization !== id)
        : [...form.organizations, id];
}

function search() {
    form.query(url, { preserveScroll: true });
}

function reloadResults() {
    router.reload({ only: ['contacts', 'matches', 'lastRequest'] });
}
</script>

<template>
    <Head title="QUERY Visits" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="QUERY Visits"
                docs="the-basics/manual-visits"
                controller="app/Http/Controllers/Feature/NavigationController.php#L114"
            >
                The HTTP <code class="text-xs">QUERY</code> method is safe and
                repeatable like <code class="text-xs">GET</code>, but sends its
                data in the request body. A good fit for searches and filters
                that don't fit in a query string. Laravel supports
                <code class="text-xs">QUERY</code> routes as of version 13.35.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2 lg:items-start">
                <FeatureCard title="Filters" badge="v3.10">
                    <template #description>
                        Submitted with
                        <code class="text-xs">form.query()</code>. The URL stays
                        the same and no history entry is added.
                    </template>
                    <form class="space-y-4" @submit.prevent="search">
                        <div class="space-y-2">
                            <p class="text-sm font-medium">Saved search</p>
                            <Button variant="outline" size="sm" as-child>
                                <Link
                                    :href="url"
                                    method="query"
                                    :data="{ favorites: true }"
                                    preserve-scroll
                                    >Favorite contacts</Link
                                >
                            </Button>
                            <p class="text-xs text-muted-foreground">
                                A
                                <code>&lt;Link method="query"&gt;</code> sends
                                its <code>data</code> as the request body too.
                            </p>
                        </div>

                        <div
                            class="space-y-2 border-t border-black/5 pt-4 dark:border-white/5"
                        >
                            <Label for="query-search">Search</Label>
                            <Input
                                id="query-search"
                                v-model="form.search"
                                placeholder="Name or email"
                            />
                        </div>

                        <div class="space-y-2">
                            <p class="text-sm font-medium">Organizations</p>
                            <div class="flex flex-wrap gap-1.5">
                                <Button
                                    v-for="organization in organizations"
                                    :key="organization.id"
                                    type="button"
                                    size="sm"
                                    class="h-7 px-2 text-xs"
                                    :variant="
                                        form.organizations.includes(
                                            organization.id,
                                        )
                                            ? 'default'
                                            : 'outline'
                                    "
                                    @click="toggleOrganization(organization.id)"
                                >
                                    {{ organization.name }}
                                </Button>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                v-model="form.favorites"
                                class="size-4 rounded border"
                            />
                            Favorites only
                        </label>

                        <div class="flex flex-wrap items-center gap-2">
                            <Button
                                type="submit"
                                size="sm"
                                :disabled="form.processing"
                            >
                                Search
                            </Button>
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="url">Clear</Link>
                            </Button>
                        </div>
                    </form>
                </FeatureCard>

                <FeatureCard
                    info-card
                    title="Last Request"
                    description="What the server received for the latest request."
                >
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-sm">
                            <Badge data-test="request-method">{{
                                lastRequest.method
                            }}</Badge>
                            <code class="text-xs">{{ page.url }}</code>
                            <span
                                class="ml-auto font-mono text-xs text-muted-foreground"
                                >{{ lastRequest.at }}</span
                            >
                        </div>
                        <CodeBlock
                            v-if="formattedBody"
                            title="Request body:"
                            :code="formattedBody"
                            data-test="request-body"
                        />
                        <p v-else class="text-xs text-muted-foreground">
                            No request body. Submit the filters to make a QUERY
                            visit.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard class="lg:col-span-2" title="Results">
                    <template #header-action>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="reloadResults"
                        >
                            Reload Results
                        </Button>
                    </template>
                    <template #description>
                        A page rendered by a QUERY visit remembers its body.
                        Reloading the results and loading the deferred match
                        count send the same filters again.
                    </template>
                    <div class="space-y-3">
                        <p class="text-sm">
                            Matches:
                            <Deferred data="matches">
                                <template #fallback>
                                    <span class="text-muted-foreground"
                                        >Counting...</span
                                    >
                                </template>
                                <strong data-test="matches">{{
                                    matches
                                }}</strong>
                            </Deferred>
                        </p>
                        <div
                            v-if="contacts.length"
                            class="divide-y divide-black/5 rounded-lg border border-black/5 dark:divide-white/5 dark:border-white/5"
                        >
                            <div
                                v-for="contact in contacts"
                                :key="contact.id"
                                class="flex items-center justify-between px-3 py-2 text-sm"
                                data-test="contact"
                            >
                                <span>
                                    {{ contact.name }}
                                    <span
                                        v-if="contact.isFavorite"
                                        class="text-amber-500"
                                        >★</span
                                    >
                                </span>
                                <span class="text-xs text-muted-foreground">{{
                                    contact.organization ?? 'No organization'
                                }}</span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">
                            No contacts match these filters.
                        </p>
                    </div>
                </FeatureCard>
            </div>
        </div>
    </AppLayout>
</template>
