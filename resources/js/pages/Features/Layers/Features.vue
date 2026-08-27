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

defineProps<{ baseLoadedAt: string }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Layers' },
    { title: 'Data in a Layer' },
];

const page = usePage();
</script>

<template>
    <Head title="Data in a Layer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Data in a Layer"
                docs="the-basics/manual-visits#requests-made-inside-a-layer-belong-to-it"
                controller="app/Http/Controllers/Feature/LayerController.php#L203"
            >
                Polling, when-visible, infinite scroll, merged props, once props
                and optional props all work inside a layer, and every one of
                them targets the layer rather than the page underneath. This
                page exists to prove it: nothing the panel does should touch
                anything below.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard title="Open the panel" badge="v3.7">
                    <template #description>
                        Then poll, scroll and load props inside it while
                        watching this page for movement.
                    </template>
                    <div class="space-y-4">
                        <div class="flex flex-wrap gap-2">
                            <Button size="sm" as-child>
                                <Link :href="layers.features.panel()"
                                    >Open</Link
                                >
                            </Button>
                            <Button variant="outline" size="sm" as-child>
                                <Link
                                    :href="layers.features.panel()"
                                    data-test="prefetched-open"
                                    prefetch
                                >
                                    Open (prefetched on hover)
                                </Link>
                            </Button>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            <code class="text-xs">prefetch</code> on a link that
                            opens a layer caches the layer response on hover, so
                            the second one opens without a round trip.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard info-card title="This page must not move">
                    <template #description>
                        Rendered once, when you landed here.
                    </template>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground"
                            >Base rendered at</span
                        >
                        <Badge
                            as="span"
                            variant="secondary"
                            class="font-mono"
                            >{{ baseLoadedAt }}</Badge
                        >
                    </div>
                    <p class="mt-3 text-xs text-muted-foreground">
                        A poll running in the layer refreshes the layer's props.
                        If this timestamp changes while the panel is polling,
                        the poll is hitting the wrong tier.
                    </p>
                </FeatureCard>

                <FeatureCard title="What the panel is doing">
                    <div class="space-y-3 text-sm text-muted-foreground">
                        <p>
                            <strong class="text-foreground">usePoll</strong> and
                            <strong class="text-foreground"
                                >&lt;WhenVisible&gt;</strong
                            >
                            read the layer they are rendered in, so their
                            requests carry the layer's url and their props land
                            on it.
                        </p>
                        <p>
                            <strong class="text-foreground"
                                >&lt;InfiniteScroll&gt;</strong
                            >
                            watches the scroll container it sits in — a box
                            inside the dialog, not the window — and each page
                            merges into the layer's scroll prop.
                        </p>
                        <p>
                            <strong class="text-foreground"
                                >Merged, once and optional props</strong
                            >
                            all merge into the layer's props, because the
                            partial reload naming them was made from inside it.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard info-card title="Live page state">
                    <template #description>
                        The base's own props, which the panel never writes to.
                    </template>
                    <CodeBlock
                        :code="
                            JSON.stringify(
                                {
                                    url: page.url,
                                    open: page.layers?.length ?? 0,
                                    baseLoadedAt: page.props.baseLoadedAt,
                                },
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
