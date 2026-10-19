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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Layers' },
    { title: 'Swapping Layers' },
];

const page = usePage();
</script>

<template>
    <Head title="Swapping Layers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Swapping Layers"
                docs="the-basics/responses#layer-responses"
                controller="app/Http/Controllers/Feature/LayerController.php#L271"
            >
                A layer's <code class="text-xs">key</code> is its identity. A
                response whose key names a layer already open supersedes that
                layer where it stands — rewriting it rather than stacking
                another one, and closing anything sitting above it on the way.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard title="A wizard in one modal" badge="v3.7">
                    <template #description>
                        Three urls, one key, one layer. Walk the steps and watch
                        the stack stay at one.
                    </template>
                    <div class="space-y-4">
                        <Button size="sm" as-child>
                            <Link :href="layers.wizard.step('details')"
                                >Start the wizard</Link
                            >
                        </Button>
                        <CodeBlock
                            code="
                                return Inertia::render('Features/Layers/WizardStep', [...])
                                    ->layer(base: '/features/layers/wizard', key: 'wizard');
                            "
                        />
                        <p class="text-xs text-muted-foreground">
                            Every step declares the same key, so stepping
                            forward rewrites the modal in place. The address
                            still moves, so back walks the steps in reverse.
                        </p>
                        <p class="text-xs text-muted-foreground">
                            The step links carry
                            <code class="text-xs">view-transition</code>. A
                            layer's content is captured as a boundary of its
                            own, so the browser transitions the modal rather
                            than folding the whole document into one snapshot —
                            the page underneath never flickers.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard title="Superseding a layer beneath">
                    <div class="space-y-3 text-sm text-muted-foreground">
                        <p>
                            From inside the wizard you can open the help
                            slideover. It carries a different key, so it stacks
                            on top instead of replacing the wizard.
                        </p>
                        <p>
                            Jumping to a step from inside that help slideover
                            answers with the wizard's key. The wizard two levels
                            down is rewritten and the help layer above it is
                            closed on the way — a link four modals deep can name
                            an earlier level and bring the stack back down to
                            it.
                        </p>
                        <p>
                            An ordinary page response replaces the whole stack.
                            That is how the "leave the wizard" link inside the
                            modal navigates away from it.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard
                    info-card
                    title="The open stack"
                    class="lg:col-span-2"
                >
                    <div class="mb-3 flex items-center gap-2">
                        <Badge variant="secondary"
                            >{{ page.layers?.length ?? 0 }} open</Badge
                        >
                    </div>
                    <CodeBlock
                        :code="
                            JSON.stringify(
                                (page.layers ?? []).map((layer) => ({
                                    key: layer.key,
                                    component: layer.component,
                                    url: layer.url,
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
