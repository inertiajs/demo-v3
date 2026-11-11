<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

defineProps<{
    timestamp: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Navigation' },
    { title: 'Links & Methods' },
];

const eventLog = ref<string[]>([]);

function log(message: string) {
    eventLog.value.unshift(`${new Date().toLocaleTimeString()} - ${message}`);
    if (eventLog.value.length > 10) {
        eventLog.value.pop();
    }
}

function manualVisit(method: string) {
    const fn = router[method as keyof typeof router] as (
        ...args: unknown[]
    ) => void;
    if (method === 'delete') {
        fn('/features/navigation/links', {
            preserveScroll: true,
            onSuccess: () => log(`router.${method}() completed`),
        });
    } else {
        fn(
            '/features/navigation/links',
            { demo: true },
            {
                preserveScroll: true,
                onSuccess: () => log(`router.${method}() completed`),
            },
        );
    }
}
</script>

<template>
    <Head title="Links & Methods" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Links & Methods"
                docs="the-basics/links"
                controller="app/Http/Controllers/Feature/NavigationController.php#L13"
            >
                The &lt;Link&gt; component and programmatic router visits.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Server Timestamp -->
                <FeatureCard
                    info-card
                    class="lg:col-span-2"
                    title="Server Response"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground"
                            >Server timestamp:</span
                        >
                        <Badge variant="outline">{{ timestamp }}</Badge>
                    </div>
                </FeatureCard>

                <!-- Link Component -->
                <FeatureCard
                    title="<Link> Component"
                    description="Client-side navigation that avoids full page reloads."
                >
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <h4 class="text-sm font-semibold">
                                GET Link (default)
                            </h4>
                            <Link
                                href="/features/navigation/links"
                                class="text-sm text-primary underline underline-offset-2"
                            >
                                Reload this page via &lt;Link&gt;
                            </Link>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm font-semibold">
                                POST as button
                            </h4>
                            <Link
                                href="/features/navigation/links"
                                method="post"
                                as="button"
                                preserve-scroll
                                class="inline-flex items-center rounded-md bg-primary px-3 py-1.5 text-sm text-primary-foreground hover:bg-primary/90"
                            >
                                POST Request
                            </Link>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm font-semibold">PUT as button</h4>
                            <Link
                                href="/features/navigation/links"
                                method="put"
                                as="button"
                                preserve-scroll
                                class="inline-flex items-center rounded-md bg-primary px-3 py-1.5 text-sm text-primary-foreground hover:bg-primary/90"
                            >
                                PUT Request
                            </Link>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm font-semibold">
                                DELETE as button
                            </h4>
                            <Link
                                href="/features/navigation/links"
                                method="delete"
                                as="button"
                                preserve-scroll
                                class="inline-flex items-center rounded-md bg-destructive px-3 py-1.5 text-sm text-destructive-foreground hover:bg-destructive/90"
                            >
                                DELETE Request
                            </Link>
                        </div>

                        <div v-if="$page.props.queryMethod" class="space-y-2">
                            <h4
                                class="flex items-center gap-2 text-sm font-semibold"
                            >
                                QUERY as button
                                <span
                                    class="shrink-0 rounded bg-indigo-500/10 px-2 py-1 text-[10px] leading-none font-semibold text-indigo-500"
                                    >v3.10</span
                                >
                            </h4>
                            <Link
                                href="/features/navigation/links"
                                method="query"
                                as="button"
                                :data="{ search: 'Inertia' }"
                                preserve-scroll
                                class="inline-flex items-center rounded-md bg-primary px-3 py-1.5 text-sm text-primary-foreground hover:bg-primary/90"
                            >
                                QUERY Request
                            </Link>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm font-semibold">
                                Link with data
                            </h4>
                            <Link
                                href="/features/navigation/links"
                                method="post"
                                as="button"
                                :data="{ name: 'Inertia' }"
                                preserve-scroll
                                class="inline-flex items-center rounded-md border border-black/10 bg-background px-3 py-1.5 text-sm hover:bg-accent dark:border-white/10"
                            >
                                POST with payload
                            </Link>
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm font-semibold">
                                Link with custom headers
                            </h4>
                            <Link
                                href="/features/navigation/links"
                                :headers="{ 'X-Custom-Header': 'demo' }"
                                class="text-sm text-primary underline underline-offset-2"
                            >
                                GET with custom header
                            </Link>
                        </div>
                    </div>
                </FeatureCard>

                <!-- Manual Visits -->
                <FeatureCard
                    title="Programmatic Visits"
                    description="Navigate using router.get(), router.post(), etc."
                >
                    <div class="space-y-4">
                        <div class="flex flex-wrap gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="manualVisit('get')"
                                >router.get()</Button
                            >
                            <Button
                                variant="outline"
                                size="sm"
                                @click="manualVisit('post')"
                                >router.post()</Button
                            >
                            <Button
                                variant="outline"
                                size="sm"
                                @click="manualVisit('put')"
                                >router.put()</Button
                            >
                            <Button
                                variant="outline"
                                size="sm"
                                @click="manualVisit('patch')"
                                >router.patch()</Button
                            >
                            <Button
                                variant="outline"
                                size="sm"
                                @click="manualVisit('delete')"
                                >router.delete()</Button
                            >
                            <Button
                                v-if="$page.props.queryMethod"
                                variant="outline"
                                size="sm"
                                @click="manualVisit('query')"
                                >router.query()</Button
                            >
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-sm font-semibold">
                                router.reload()
                            </h4>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="
                                    router.reload({
                                        onSuccess: () =>
                                            log('router.reload() completed'),
                                    })
                                "
                            >
                                Reload current page
                            </Button>
                        </div>

                        <div
                            class="mt-4 rounded-md border border-black/10 p-3 dark:border-white/10"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <h4 class="text-sm font-semibold">Event Log</h4>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="eventLog = []"
                                    >Clear</Button
                                >
                            </div>
                            <div v-if="eventLog.length" class="space-y-1">
                                <div
                                    v-for="(entry, i) in eventLog"
                                    :key="i"
                                    class="rounded bg-muted px-2 py-1 font-mono text-xs"
                                >
                                    {{ entry }}
                                </div>
                            </div>
                            <p v-else class="text-xs text-muted-foreground">
                                Click a button to see events.
                            </p>
                        </div>
                    </div>
                </FeatureCard>
            </div>
        </div>
    </AppLayout>
</template>
