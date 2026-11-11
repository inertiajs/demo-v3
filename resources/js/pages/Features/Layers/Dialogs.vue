<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import CodeBlock from '@/components/CodeBlock.vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import layers from '@/wayfinder/routes/features/layers';
import type { Inertia } from '@/wayfinder/types';

const props = defineProps<Inertia.Pages.Features.Layers.Dialogs>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Layers' },
    { title: 'Modals & Slideovers' },
];

const page = usePage();
const scratch = ref('');
const clicks = ref(0);
const log = ref<string[]>([]);

function openFromCode(variant: 'modal' | 'slideover') {
    const contact = props.contacts[0];
    const url =
        variant === 'modal'
            ? layers.dialogs.contact(contact.id).url
            : layers.dialogs.edit(contact.id).url;

    const handle = router.layer(url);

    handle.onClose(() => log.value.unshift(`${variant} closed`));
}
</script>

<template>
    <Head title="Modals & Slideovers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Modals & Slideovers"
                docs="the-basics/manual-visits#layers"
                controller="app/Http/Controllers/Feature/LayerController.php#L32"
            >
                A response that chains
                <code class="text-xs">->layer()</code> renders over the page it
                was opened from instead of replacing it. This app's shell reads
                a <code class="text-xs">dialog</code> prop to pick between
                <code class="text-xs">Modal.vue</code> and
                <code class="text-xs">Slideover.vue</code>, two components built
                on <code class="text-xs">&lt;Layer&gt;</code> that are yours to
                copy.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard title="Routed layers" badge="v3.7">
                    <template #description>
                        An ordinary <code class="text-xs">&lt;Link&gt;</code>.
                        The address moves to the layer, and the page underneath
                        stays exactly where it was.
                    </template>
                    <ul class="divide-y">
                        <li
                            v-for="contact in contacts"
                            :key="contact.id"
                            class="flex items-center justify-between gap-3 py-2"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">
                                    {{ contact.first_name }}
                                    {{ contact.last_name }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ contact.email ?? 'No email' }}
                                </p>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <Button variant="outline" size="sm" as-child>
                                    <Link
                                        :href="
                                            layers.dialogs.contact(contact.id)
                                        "
                                        >Modal</Link
                                    >
                                </Button>
                                <Button variant="outline" size="sm" as-child>
                                    <Link
                                        :href="layers.dialogs.edit(contact.id)"
                                        >Slideover</Link
                                    >
                                </Button>
                            </div>
                        </li>
                    </ul>
                </FeatureCard>

                <FeatureCard title="Opened from code">
                    <template #description>
                        <code class="text-xs">router.layer(url)</code> opens the
                        same layer and hands back a handle you can listen to.
                    </template>
                    <div class="space-y-4">
                        <div class="flex flex-wrap gap-2">
                            <Button size="sm" @click="openFromCode('modal')"
                                >Open modal</Button
                            >
                            <Button
                                size="sm"
                                variant="outline"
                                @click="openFromCode('slideover')"
                                >Open slideover</Button
                            >
                        </div>
                        <CodeBlock
                            code="
                                const handle = router.layer('/features/layers/dialogs/1')

                                handle.onClose(() => console.log('closed'))
                            "
                        />
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

                <FeatureCard title="The page beneath is still here">
                    <template #description>
                        A layer is not a navigation. Type something, click a few
                        times, then open a layer and close it again.
                    </template>
                    <div class="space-y-3">
                        <Input
                            id="scratch"
                            v-model="scratch"
                            placeholder="Type something…"
                        />
                        <Button variant="outline" size="sm" @click="clicks++">
                            Clicked {{ clicks }} times
                        </Button>
                        <p class="text-xs text-muted-foreground">
                            Scroll position, component state and playing media
                            all survive, because the page underneath is never
                            unmounted.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard title="Layer URLs are real URLs">
                    <template #description>
                        Open one in a new tab. The layer declares
                        <code class="text-xs">base:</code>, so the client
                        fetches this page and renders it underneath.
                    </template>
                    <div class="space-y-3">
                        <a
                            :href="layers.dialogs.contact(contacts[0].id).url"
                            target="_blank"
                            class="text-sm underline underline-offset-4"
                        >
                            {{ layers.dialogs.contact(contacts[0].id).url }}
                        </a>
                        <CodeBlock
                            code="
                                return Inertia::render('Features/Layers/ContactCard', [...])
                                    ->layer(base: '/features/layers/dialogs');
                            "
                        />
                        <p class="text-xs text-muted-foreground">
                            Back closes the layer and lands on the base. Forward
                            opens it again.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard
                    info-card
                    title="Live page state"
                    class="lg:col-span-2"
                >
                    <template #description>
                        <code class="text-xs">usePage()</code> on the base
                        resolves the composite page, including the open stack.
                    </template>
                    <CodeBlock
                        :code="
                            JSON.stringify(
                                {
                                    url: page.url,
                                    open: page.layers?.length ?? 0,
                                    stack: (page.layers ?? []).map((layer) => ({
                                        component: layer.component,
                                        url: layer.url,
                                        key: layer.key,
                                    })),
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
