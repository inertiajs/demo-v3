<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import CodeBlock from '@/components/CodeBlock.vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import layers from '@/wayfinder/routes/features/layers';
import type { Inertia } from '@/wayfinder/types';

defineProps<Inertia.Pages.Features.Layers.Detours>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Layers' },
    { title: 'Detours' },
];

const log = ref<string[]>([]);
const catchIt = ref(true);

const failures = [
    { label: '403', href: layers.detour.denied() },
    { label: '404', href: layers.detour.missing() },
    { label: '500', href: layers.detour.broken() },
];

// The page beneath a returned layer is the one the layer was asked for from, captured before the
// detour unlocked the gate. Re-read it on every landing so the badge is never the stale answer.
onMounted(() => router.reload({ only: ['confirmed'] }));

function tryFailing(label: string, href: { url: string }) {
    router
        .layer(href.url, {
            onHttpException: () => (catchIt.value ? false : undefined),
        })
        .onClose(() => log.value.unshift(`${label}: the open never landed`));
}
</script>

<template>
    <Head title="Detours" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Detours"
                docs="the-basics/redirects#detours"
                controller="app/Http/Controllers/Feature/LayerController.php#L317"
            >
                Sometimes the answer to a layer request is not the layer: a
                session expired, a password needs confirming. A redirect marked
                with <code class="text-xs">Inertia::interstitial()</code> keeps
                the pending capture alive, so finishing the detour opens the
                layer over the page you asked for it from — not over the prompt.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard title="Walk the detour" badge="v3.7">
                    <template #description>
                        Ask for a layer you are not allowed to see yet.
                    </template>
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 text-sm">
                            The gate is
                            <Badge
                                :variant="confirmed ? 'default' : 'secondary'"
                                >{{ confirmed ? 'unlocked' : 'locked' }}</Badge
                            >
                        </div>
                        <Button size="sm" as-child>
                            <Link :href="layers.detour()"
                                >Open the guarded layer</Link
                            >
                        </Button>
                        <p class="text-xs text-muted-foreground">
                            While the gate is locked, that link lands on a full
                            page instead. Complete it and the layer opens back
                            here, over this page.
                        </p>
                        <Button
                            v-if="confirmed"
                            variant="outline"
                            size="sm"
                            @click="
                                router.post(
                                    layers.detour.forget().url,
                                    {},
                                    { preserveScroll: true },
                                )
                            "
                        >
                            Lock it again
                        </Button>
                    </div>
                </FeatureCard>

                <FeatureCard title="How the capture survives">
                    <div class="space-y-3">
                        <CodeBlock
                            code="
                                if (! $request->session()->get('layers.confirmed')) {
                                    return redirect()
                                        ->route('features.layers.detour.confirm')
                                        ->interstitial();
                                }
                            "
                        />
                        <p class="text-xs text-muted-foreground">
                            Without the mark the client would take the prompt as
                            an ordinary landing, drop what it had captured, and
                            the layer would later open over the prompt page. The
                            mark says "this is a detour, hold on to it".
                        </p>
                        <p class="text-xs text-muted-foreground">
                            The capture is consumed by the return and never
                            resurrected: pressing back from the returned layer
                            does not land on the dead prompt.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard info-card title="Was this page fetched again?">
                    <template #description>
                        A reload kills the pending capture, and the client has
                        to walk to this page itself.
                    </template>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground"
                            >Rendered at</span
                        >
                        <Badge
                            as="span"
                            variant="secondary"
                            class="font-mono"
                            >{{ renderedAt }}</Badge
                        >
                    </div>
                    <p class="mt-3 text-xs text-muted-foreground">
                        Walk the detour and this stamp holds: the layer opened
                        over the page the client was already holding. Reload the
                        prompt before confirming and the capture is gone, so the
                        layer arrives cold and this page is fetched again —
                        which is what moves the stamp.
                    </p>
                </FeatureCard>

                <FeatureCard title="When the answer is an error">
                    <template #description>
                        A 403, a 404 or a 500 is not a layer either — but unlike
                        a detour, nothing is being held for later.
                    </template>
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <input
                                id="catch-it"
                                v-model="catchIt"
                                type="checkbox"
                                class="size-4 rounded border"
                            />
                            <label for="catch-it" class="text-sm"
                                >Catch it with
                                <code class="text-xs">onHttpException</code>
                            </label>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-for="failure in failures"
                                :key="failure.label"
                                variant="outline"
                                size="sm"
                                @click="tryFailing(failure.label, failure.href)"
                            >
                                Open a layer that {{ failure.label }}s
                            </Button>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Left unhandled, the server's error page is an
                            ordinary page response, and an ordinary page
                            response replaces the whole stack — the layer never
                            opens, and this page goes with it. Returning
                            <code class="text-xs">false</code> from
                            <code class="text-xs">onHttpException</code>
                            abandons the attempt instead, leaving this page
                            exactly where it was.
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Either way the handle's
                            <code class="text-xs">onClose()</code> fires, so a
                            caller waiting on the layer is never left holding a
                            handle to nothing.
                        </p>
                        <div v-if="log.length" class="space-y-1">
                            <div
                                v-for="(entry, i) in log"
                                :key="i"
                                class="rounded bg-muted px-2 py-1 font-mono text-xs"
                            >
                                {{ entry }}
                            </div>
                        </div>
                    </div>
                </FeatureCard>
            </div>
        </div>
    </AppLayout>
</template>
