<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import CodeBlock from '@/components/CodeBlock.vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import layers from '@/wayfinder/routes/features/layers';
import type { Inertia } from '@/wayfinder/types';

defineProps<Inertia.Pages.Features.Layers.Stacked>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Layers' },
    { title: 'Stacked Layers' },
];

const page = usePage();
</script>

<template>
    <Head title="Stacked Layers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Stacked Layers"
                docs="the-basics/manual-visits#layers"
                controller="app/Http/Controllers/Feature/LayerController.php#L99"
            >
                A layer opened from inside another stacks on top of it. Open an
                organization, pick one of its contacts, then add a note — three
                tiers deep, each with its own props, its own errors and its own
                place in history.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard title="Start the stack" badge="v3.7">
                    <template #description>
                        Level 1 is a slideover. Level 2 is a modal opened from
                        inside it. Level 3 is a local layer opened from inside
                        that.
                    </template>
                    <ul class="divide-y">
                        <li
                            v-for="organization in organizations"
                            :key="organization.id"
                            class="flex items-center justify-between gap-3 py-2"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">
                                    {{ organization.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ organization.contacts_count }} contacts
                                </p>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link
                                    :href="layers.organization(organization.id)"
                                    data-test="open-organization"
                                    >Open</Link
                                >
                            </Button>
                        </li>
                    </ul>
                </FeatureCard>

                <FeatureCard title="What the stack does">
                    <div class="space-y-3 text-sm text-muted-foreground">
                        <p>
                            <strong class="text-foreground">Escape</strong>
                            closes the top layer only. The one beneath stays
                            open, and stays modal.
                        </p>
                        <p>
                            <strong class="text-foreground">Back</strong> steps
                            down the stack one layer at a time, because each
                            routed layer pushed a history entry of its own.
                        </p>
                        <p>
                            <strong class="text-foreground">The address</strong>
                            belongs to the topmost layer that has one. The local
                            layer at level 3 has no url, so it leaves the
                            address on the modal beneath it.
                        </p>
                        <p>
                            Closing a layer in the middle of the stack takes
                            everything above it with it.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard
                    info-card
                    title="The open stack"
                    class="lg:col-span-2"
                >
                    <template #description>
                        <code class="text-xs">usePage().layers</code> on the
                        base page, updating live as you go deeper.
                    </template>
                    <div class="mb-3 flex items-center gap-2">
                        <Badge variant="secondary"
                            >{{ page.layers?.length ?? 0 }} open</Badge
                        >
                        <span class="font-mono text-xs text-muted-foreground">{{
                            page.url
                        }}</span>
                    </div>
                    <CodeBlock
                        :code="
                            JSON.stringify(
                                (page.layers ?? []).map((layer, index) => ({
                                    level: index + 1,
                                    component: layer.component,
                                    key: layer.key,
                                    url: layer.url,
                                    local: layer.local ?? false,
                                })),
                                null,
                                2,
                            )
                        "
                    />
                </FeatureCard>
            </div>
        </div>
    </AppLayout>
</template>
