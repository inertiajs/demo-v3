<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
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
    { title: 'Cold Opens' },
];

const delays = [1000, 3000, 6000];
const delay = ref(3000);

const treatments = [
    {
        loading: 'shell',
        label: 'A skeleton of the app',
        blurb: 'What the page underneath is about to look like.',
    },
    {
        loading: 'plain',
        label: 'A plain spinner',
        blurb: 'Enough to say the page is coming, without guessing at it.',
    },
    {
        loading: 'none',
        label: 'Nothing at all',
        blurb: 'What a shell that paints nothing leaves you with.',
    },
];

function href(loading: string) {
    return layers.cold.panel({ query: { delay: delay.value, loading } }).url;
}
</script>

<template>
    <Head title="Cold Opens" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Cold Opens"
                docs="the-basics/manual-visits#layers"
                controller="app/Http/Controllers/Feature/LayerController.php#L244"
            >
                Land on a layer's url directly and the layer is all the server
                sends. The client renders it straight away and then walks to the
                page it declared as its
                <code class="text-xs">base:</code>, which means there is a
                window — as long as that page takes to arrive — where a dialog
                is on screen with nothing behind it.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard title="Open one cold" badge="v3.7">
                    <template #description>
                        Pick how slow the page underneath should be, then choose
                        what the shell paints while it waits.
                    </template>
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm text-muted-foreground"
                                >Base takes</span
                            >
                            <Button
                                v-for="option in delays"
                                :key="option"
                                :variant="
                                    option === delay ? 'default' : 'outline'
                                "
                                size="sm"
                                @click="delay = option"
                            >
                                {{ option / 1000 }}s
                            </Button>
                        </div>

                        <ul class="divide-y">
                            <li
                                v-for="treatment in treatments"
                                :key="treatment.loading"
                                class="flex items-center justify-between gap-3 py-2"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">
                                        {{ treatment.label }}
                                    </p>
                                    <p
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        {{ treatment.blurb }}
                                    </p>
                                </div>
                                <Button variant="outline" size="sm" as-child>
                                    <a :href="href(treatment.loading)">Open</a>
                                </Button>
                            </li>
                        </ul>

                        <p class="text-xs text-muted-foreground">
                            These are plain
                            <code class="text-xs">&lt;a&gt;</code> links on
                            purpose. A <code class="text-xs">&lt;Link&gt;</code>
                            from here would open the layer over this page, which
                            is already on screen — there would be no window to
                            look at. A cold open is what happens when someone
                            pastes a layer url into a fresh tab.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard title="What you are watching">
                    <div class="space-y-3 text-sm text-muted-foreground">
                        <p>
                            <strong class="text-foreground">The layer</strong>
                            is server-rendered and on screen before anything
                            else, because it is the whole of what you asked for.
                        </p>
                        <p>
                            <strong class="text-foreground">The base</strong> is
                            a second request the client makes on its own, to the
                            url the layer named. Until it answers, the composite
                            page has no component of its own, and the app
                            renders no page — only the dialog.
                        </p>
                        <p>
                            <strong class="text-foreground"
                                >Closing early</strong
                            >
                            leaves the placeholder standing. Dismiss the layer
                            while the base is still in flight and the page
                            underneath is still the one being fetched, so what
                            was already drawn for it stays until the real one
                            arrives and takes its place.
                        </p>
                        <p>
                            Nothing here is specific to a slow server: the same
                            window opens on a slow connection, and it is exactly
                            as long as the page underneath takes.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard
                    title="How the placeholder is chosen"
                    class="lg:col-span-2"
                >
                    <template #description>
                        <code class="text-xs">createInertiaApp</code> names one
                        page to draw for the window, and it renders at the url
                        the client is walking to.
                    </template>
                    <div class="space-y-3">
                        <CodeBlock
                            code="
                                createInertiaApp({
                                  loading: LayerLoading,
                                })

                                // LayerLoading.vue — it renders at the base's url, so that is
                                // where it reads what to paint.
                                const variant = new URLSearchParams(page.url.split('?')[1] ?? '')
                                  .get('loading') ?? 'shell'
                            "
                        />
                        <p class="text-xs text-muted-foreground">
                            It renders as the base, at the base's own url, so it
                            is a page like any other — it can bring a layout,
                            and the real page swaps it out when the walk lands.
                            A page may also paint nothing, which is what the
                            third link above does.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard info-card title="This page" class="lg:col-span-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm text-muted-foreground"
                            >Landed at</span
                        >
                        <Badge
                            as="span"
                            variant="secondary"
                            class="font-mono"
                            >{{ baseLoadedAt }}</Badge
                        >
                        <span class="text-sm text-muted-foreground">
                            — instantly, because only the walk asks for a delay.
                        </span>
                    </div>
                </FeatureCard>
            </div>
        </div>
    </AppLayout>
</template>
