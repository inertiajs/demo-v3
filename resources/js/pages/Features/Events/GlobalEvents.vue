<script lang="ts">
import { ref } from 'vue';

// Lives at module scope so the log survives the remount on history navigation
const eventLog = ref<string[]>([]);
</script>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import CodeBlock from '@/components/CodeBlock.vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Events & Lifecycle' },
    { title: 'Global Events' },
];

const removers: (() => void)[] = [];

function log(event: string, detail?: string) {
    eventLog.value.unshift(
        `${new Date().toLocaleTimeString()} - ${event}${detail ? ': ' + detail : ''}`,
    );
    if (eventLog.value.length > 20) eventLog.value.pop();
}

onMounted(() => {
    removers.push(
        router.on('before', (event) => {
            log('before', event.detail.visit.url.toString());
        }),
        router.on('start', (event) => {
            log('start', event.detail.visit.url.toString());
        }),
        router.on('progress', (event) => {
            log('progress', `${event.detail.progress?.percentage}%`);
        }),
        router.on('success', (event) => {
            log('success', event.detail.page.url);
        }),
        router.on('error', (event) => {
            log('error', JSON.stringify(event.detail.errors));
        }),
        router.on('finish', () => {
            log('finish');
        }),
        router.on('navigate', (event) => {
            const { type, cached } = event.detail;

            log(
                `navigate (${type}${cached ? ', cached' : ''})`,
                event.detail.page.url,
            );
        }),
        router.on('flash', (event) => {
            log('flash', JSON.stringify(event.detail.flash));
        }),
    );
});

onUnmounted(() => {
    removers.forEach((remove) => remove());
});
</script>

<template>
    <Head title="Global Events" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Global Events"
                docs="advanced/events#registering-listeners"
                controller="app/Http/Controllers/Feature/EventController.php#L12"
            >
                Router event system with
                <code class="text-xs">router.on()</code>. Fires on every Inertia
                request.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard
                    title="Trigger Events"
                    description="Perform actions and watch the event log update in real time."
                >
                    <div class="space-y-4">
                        <div class="flex flex-wrap gap-2">
                            <Button size="sm" @click="router.reload()">
                                Reload Page
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="
                                    router.post(
                                        '/features/events/global-events/action',
                                        {},
                                        { preserveScroll: true },
                                    )
                                "
                            >
                                POST Action
                            </Button>
                            <Link
                                href="/features/events/global-events?step=2"
                                class="inline-flex items-center rounded-md border border-black/10 bg-background px-3 py-1.5 text-sm hover:bg-accent dark:border-white/10"
                            >
                                Visit With Query
                            </Link>
                            <Link
                                href="/features/events/visit-callbacks"
                                class="inline-flex items-center rounded-md border border-black/10 bg-background px-3 py-1.5 text-sm hover:bg-accent dark:border-white/10"
                            >
                                Navigate Away
                            </Link>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Each action triggers a sequence of events: before →
                            start → success/error → finish → navigate. The
                            progress event only fires during file uploads.
                        </p>
                        <p
                            class="flex items-start gap-2 text-xs text-muted-foreground"
                        >
                            <span
                                class="shrink-0 rounded bg-indigo-500/10 px-2 py-1 text-[10px] leading-none font-semibold text-indigo-500"
                                >v3.9</span
                            >
                            <span>
                                The navigate event's
                                <code>detail.type</code> tells you how the page
                                was reached: <code>initial</code> on page load,
                                <code>visit</code> for visits that add a history
                                entry, and <code>history</code> for back and
                                forward.
                            </span>
                        </p>

                        <CodeBlock
                            title="Cleanup:"
                            code="
                                const remove = router.on('before', (e) => {
                                  console.log(e.detail.visit.url)
                                })

                                // Later: deregister the listener
                                remove()
                            "
                        />
                    </div>
                </FeatureCard>

                <FeatureCard
                    info-card
                    title="Available Events"
                    description="All events fired by the Inertia router."
                >
                    <div class="grid grid-cols-2 gap-2">
                        <Badge variant="outline" class="justify-center"
                            >before</Badge
                        >
                        <Badge variant="outline" class="justify-center"
                            >start</Badge
                        >
                        <Badge variant="outline" class="justify-center"
                            >progress</Badge
                        >
                        <Badge variant="outline" class="justify-center"
                            >success</Badge
                        >
                        <Badge variant="outline" class="justify-center"
                            >error</Badge
                        >
                        <Badge variant="outline" class="justify-center"
                            >finish</Badge
                        >
                        <Badge variant="outline" class="justify-center"
                            >navigate</Badge
                        >
                        <Badge variant="outline" class="justify-center"
                            >flash</Badge
                        >
                        <Badge variant="outline" class="justify-center"
                            >httpException</Badge
                        >
                        <Badge variant="outline" class="justify-center"
                            >networkError</Badge
                        >
                        <Badge variant="outline" class="justify-center"
                            >prefetching</Badge
                        >
                        <Badge variant="outline" class="justify-center"
                            >prefetched</Badge
                        >
                    </div>
                </FeatureCard>

                <FeatureCard info-card class="lg:col-span-2" title="Event Log">
                    <template #description
                        >Events captured by
                        <code class="text-xs">router.on()</code> listeners
                        registered on this page.</template
                    >
                    <template #header-action>
                        <Button variant="ghost" size="sm" @click="eventLog = []"
                            >Clear</Button
                        >
                    </template>
                    <div
                        v-if="eventLog.length"
                        class="max-h-64 space-y-1 overflow-y-auto"
                    >
                        <div
                            v-for="(entry, i) in eventLog"
                            :key="i"
                            class="rounded bg-muted px-2 py-1 font-mono text-xs"
                        >
                            {{ entry }}
                        </div>
                    </div>
                    <p v-else class="text-xs text-muted-foreground">
                        Trigger an action to see events.
                    </p>
                </FeatureCard>
            </div>
        </div>
    </AppLayout>
</template>
