<script setup lang="ts">
import { Head, router, useLayer, usePage } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import CodeBlock from '@/components/CodeBlock.vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import layers from '@/wayfinder/routes/features/layers';
import type { Inertia } from '@/wayfinder/types';

const props = defineProps<Inertia.Pages.Features.Layers.Local>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Layers' },
    { title: 'Local Layers' },
];

const page = usePage();
const self = useLayer();
const picked = ref<string | null>(null);
const log = ref<string[]>([]);

// A layer emits to whatever owns it. These were opened from the page, so the page is the owner and
// useLayer() here is the handle they reach.
onUnmounted(
    self.on('confirmed', (id) => {
        record(`confirmed for contact ${id}`);
        router.post(
            layers.local.favorite(id as number).url,
            {},
            { preserveScroll: true },
        );
    }),
);

onUnmounted(
    self.on('picked', (name) => {
        picked.value = name as string;
        record(`picked ${name}`);
    }),
);

function record(entry: string) {
    log.value.unshift(entry);
    log.value = log.value.slice(0, 8);
}

function confirmFavorite(contact: (typeof props.contacts)[number]) {
    const handle = router.layer({
        component: 'Features/Layers/Confirm',
        props: {
            dialog: {
                title: contact.is_favorite ? 'Remove favorite' : 'Add favorite',
                description: 'A local layer. Nothing was fetched to open it.',
                size: 'sm',
            },
            contact: contact.id,
            message: `${contact.first_name} ${contact.last_name} will be ${
                contact.is_favorite ? 'removed from' : 'added to'
            } your favorites.`,
        },
    });

    handle.onClose(() => record('the confirm layer closed'));
}

function openPicker() {
    router.layer({
        component: 'Features/Layers/Picker',
        props: {
            dialog: {
                title: 'Pick a contact',
                description: 'A local layer rendered as a slideover.',
                variant: 'slideover',
                size: 'sm',
            },
            contacts: props.contacts,
        },
    });
}
</script>

<template>
    <Head title="Local Layers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Local Layers"
                docs="the-basics/manual-visits#local-layers"
                controller="app/Http/Controllers/Feature/LayerController.php#L151"
            >
                Name a component instead of a url and the client composes the
                layer itself, without asking the server for anything. It has no
                address, but it is still a history step: back closes it and
                forward brings it back.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard title="A confirmation prompt" badge="v3.7">
                    <template #description>
                        Nothing about "are you sure?" deserves a url. The handle
                        hears what the prompt decided.
                    </template>
                    <ul class="divide-y">
                        <li
                            v-for="contact in contacts"
                            :key="contact.id"
                            class="flex items-center justify-between gap-3 py-2"
                        >
                            <div class="flex min-w-0 items-center gap-2">
                                <p class="truncate text-sm font-medium">
                                    {{ contact.first_name }}
                                    {{ contact.last_name }}
                                </p>
                                <Badge
                                    v-if="contact.is_favorite"
                                    variant="secondary"
                                    class="text-[10px]"
                                    >favorite</Badge
                                >
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="confirmFavorite(contact)"
                            >
                                {{ contact.is_favorite ? 'Remove' : 'Add' }}
                            </Button>
                        </li>
                    </ul>
                </FeatureCard>

                <FeatureCard title="A picker that answers back">
                    <template #description>
                        The same mechanism as a slideover, without a route.
                    </template>
                    <div class="space-y-4">
                        <Button size="sm" @click="openPicker"
                            >Open the picker</Button
                        >
                        <p class="text-sm">
                            Picked:
                            <span class="font-medium">{{
                                picked ?? 'nothing yet'
                            }}</span>
                        </p>
                        <CodeBlock
                            code="
                                router.layer({
                                  component: 'Features/Layers/Picker',
                                  props: { contacts },
                                })

                                // The picker emits to its owner, which is this page.
                                useLayer().on('picked', (name) => …)
                            "
                        />
                    </div>
                </FeatureCard>

                <FeatureCard title="What a local layer gives up">
                    <div class="space-y-3 text-sm text-muted-foreground">
                        <p>
                            <strong class="text-foreground">No url.</strong> The
                            address stays on whatever is underneath, so a local
                            layer is never something you can link someone to.
                        </p>
                        <p>
                            <strong class="text-foreground"
                                >Dropped on a reload.</strong
                            >
                            The server knows nothing about it, so refreshing the
                            page leaves it behind.
                        </p>
                        <p>
                            <strong class="text-foreground"
                                >Still a history step.</strong
                            >
                            Open one and press back — it closes. Press forward
                            and it comes back.
                        </p>
                        <p>
                            The shell is told
                            <code>type: "local"</code>, which is how the little
                            badge in the header of these dialogs gets there.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard info-card title="Handle events">
                    <template #header-action>
                        <Button variant="ghost" size="sm" @click="log = []"
                            >Clear</Button
                        >
                    </template>
                    <div v-if="log.length" class="space-y-1">
                        <div
                            v-for="(entry, i) in log"
                            :key="i"
                            class="rounded bg-muted px-2 py-1 font-mono text-xs"
                        >
                            {{ entry }}
                        </div>
                    </div>
                    <p v-else class="text-xs text-muted-foreground">
                        Open a local layer to see its handle report back.
                    </p>
                    <p class="mt-3 text-xs text-muted-foreground">
                        Open layers: {{ page.layers?.length ?? 0 }} · address:
                        <code>{{ page.url }}</code>
                    </p>
                </FeatureCard>
            </div>
        </div>
    </AppLayout>
</template>
