<script setup lang="ts">
import { Head, router, useLayer, usePage, useRemember } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import CodeBlock from '@/components/CodeBlock.vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import layers from '@/wayfinder/routes/features/layers';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Layers' },
    { title: 'Inside a Layer' },
];

const page = usePage();
const self = useLayer();
const log = ref<string[]>([]);
const draft = useRemember({ text: '' }, 'draft');

function record(entry: string) {
    log.value.unshift(entry);
    log.value = log.value.slice(0, 10);
}

function openPanel() {
    const handle = router.layer(layers.inside.panel().url);

    handle.onClose(() => record('the panel closed'));
}

onUnmounted(
    self.on('ping', (payload, childId) =>
        record(`${childId} said ${JSON.stringify(payload)}`),
    ),
);

onUnmounted(
    router.on('flash', (event) => {
        const message = (event.detail.flash as { message?: string }).message;

        if (message) {
            record(message);
        }
    }),
);
</script>

<template>
    <Head title="Inside a Layer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Inside a Layer"
                docs="the-basics/manual-visits#the-layer-from-the-inside"
                controller="app/Http/Controllers/Feature/LayerController.php#L167"
            >
                <code class="text-xs">useLayer()</code> hands a layer's own code
                the layer it is rendered in — the router bound to it, plus the
                handle for closing it, listening to the layers it opens and
                emitting to whatever opened it. A layer is a tier of its own, so
                what Inertia keeps per page it keeps per layer too: its own
                title, its own layout, its own remembered input and scroll
                positions.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard title="Open the panel" badge="v3.7">
                    <template #description>
                        Everything interesting happens inside the layer. Open it
                        and work through its buttons.
                    </template>
                    <div class="space-y-4">
                        <Button size="sm" @click="openPanel"
                            >Open the panel</Button
                        >
                        <CodeBlock
                            code="
                                const handle = router.layer('/features/layers/inside/panel')

                                handle.onClose(() => …)

                                // useLayer() works on a page too: it hears what
                                // the layers this page opened emit to it.
                                useLayer().on('ping', (payload, childId) => …)
                            "
                        />
                    </div>
                </FeatureCard>

                <FeatureCard title="What the layer can do to itself">
                    <div class="space-y-3 text-sm text-muted-foreground">
                        <p>
                            <code class="text-foreground"
                                >layer.get(…, { only })</code
                            >
                            asks for the layer's own props again, leaving this
                            page untouched.
                        </p>
                        <p>
                            <code class="text-foreground"
                                >layer.replaceProp()</code
                            >
                            changes a prop on the layer without a request.
                        </p>
                        <p>
                            <code class="text-foreground">layer.layer()</code>
                            opens a child the panel owns, so the child's events
                            arrive at the panel rather than here.
                        </p>
                        <p>
                            <code class="text-foreground">layer.close()</code>
                            dismisses it from the inside;
                            <code class="text-foreground"
                                >Inertia::close()</code
                            >
                            does the same from the server.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard title="What the layer owns">
                    <template #description>
                        Everything Inertia keeps for a page, it keeps per tier —
                        so the panel's copy of each is not this page's.
                    </template>
                    <div class="space-y-3">
                        <Input
                            id="page-draft"
                            v-model="draft.text"
                            placeholder="Type something on the page…"
                        />
                        <p class="text-xs text-muted-foreground">
                            This page remembers that under
                            <code>'draft'</code>. The panel uses the same key,
                            and gets a bag of its own filed under the layer —
                            type in both and neither ever sees the other.
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Nothing about <code>useRemember</code> or a
                            <code>scroll-region</code> shows until you leave and
                            return, so the gesture is: close the panel with
                            Escape, then press the browser's forward button. The
                            layer comes back exactly as you left it. Reload
                            instead and both are gone — this rides on the
                            history entry, not on storage.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard
                    info-card
                    title="What reached this page"
                    class="lg:col-span-2"
                >
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
                        Open the panel and emit something.
                    </p>
                    <p class="mt-3 text-xs text-muted-foreground">
                        An event reaches its emitter's immediate owner and goes
                        no further — the child's pings land on the panel, and
                        only what the panel emits lands here. Open layers:
                        {{ page.layers?.length ?? 0 }}.
                    </p>
                </FeatureCard>
            </div>
        </div>
    </AppLayout>
</template>
