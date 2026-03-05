<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, onUnmounted } from 'vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { addToast } from '@/composables/useFlashToast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Error Handling' },
    { title: 'Network Errors' },
];

const eventLog = ref<string[]>([]);
const interceptEnabled = ref(false);
let removeListener: (() => void) | null = null;

function log(message: string) {
    eventLog.value.unshift(`${new Date().toLocaleTimeString()} - ${message}`);
    if (eventLog.value.length > 15) eventLog.value.pop();
}

function toggleIntercept() {
    if (interceptEnabled.value) {
        removeListener?.();
        removeListener = null;
        interceptEnabled.value = false;
        log('Global networkError listener removed');
    } else {
        removeListener = router.on('networkError', (event) => {
            event.preventDefault();
            log(`Intercepted network error, default behavior prevented`);
            addToast(
                'Network error intercepted. Default behavior was prevented.',
                'warning',
            );
        });
        interceptEnabled.value = true;
        log('Global networkError listener registered');
    }
}

onUnmounted(() => {
    removeListener?.();
});
</script>

<template>
    <Head title="Network Errors" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Network Errors"
                docs="advanced/events#network-error"
                controller="app/Http/Controllers/Feature/NetworkErrorController.php#L31"
            >
                Handle network failures with
                <code class="text-xs">onNetworkError</code> callback and
                <code class="text-xs">router.on('networkError')</code>.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard
                    title="When Network Errors Occur"
                    description="Network errors fire when a request fails due to connectivity issues or when page component resolution fails."
                >
                    <div class="space-y-3">
                        <div
                            class="space-y-2 rounded-lg border border-black/5 bg-neutral-50/80 p-3 font-mono text-xs dark:border-white/5 dark:bg-neutral-900/80"
                        >
                            <p>Common causes of network errors:</p>
                            <ul class="list-inside list-disc space-y-1">
                                <li>No internet connection</li>
                                <li>DNS resolution failure</li>
                                <li>Server unreachable / timeout</li>
                                <li>CORS errors</li>
                                <li>Request aborted by browser</li>
                            </ul>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Network errors are different from HTTP exceptions.
                            The server never responded at all.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard title="Error Interception">
                    <template #description>
                        Toggle global
                        <code class="text-xs">networkError</code> listener.
                    </template>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <Button
                                :variant="
                                    interceptEnabled ? 'default' : 'outline'
                                "
                                size="sm"
                                @click="toggleIntercept"
                            >
                                {{ interceptEnabled ? 'Disable' : 'Enable' }}
                                Interception
                            </Button>
                            <Badge
                                :variant="
                                    interceptEnabled ? 'default' : 'secondary'
                                "
                            >
                                {{ interceptEnabled ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            When active,
                            <code>event.preventDefault()</code> stops the
                            default network error behavior.
                        </p>

                        <div
                            class="rounded-lg border border-black/5 bg-neutral-50/80 p-3 font-mono text-xs dark:border-white/5 dark:bg-neutral-900/80"
                        >
                            <p>
                                <strong>To test:</strong> Open DevTools →
                                Network → set throttling to "Offline", then try
                                navigating.
                            </p>
                        </div>
                    </div>
                </FeatureCard>

                <FeatureCard info-card class="lg:col-span-2" title="Event Log">
                    <template #header-action>
                        <Button variant="ghost" size="sm" @click="eventLog = []"
                            >Clear</Button
                        >
                    </template>
                    <div
                        v-if="eventLog.length"
                        class="max-h-48 space-y-1 overflow-y-auto"
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
                        Enable interception and simulate an offline state to see
                        events.
                    </p>
                </FeatureCard>

                <FeatureCard
                    info-card
                    class="lg:col-span-2"
                    title="API Reference"
                >
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div
                            class="rounded-lg border border-black/5 bg-neutral-50/80 p-3 font-mono text-xs dark:border-white/5 dark:bg-neutral-900/80"
                        >
                            <p class="font-semibold">Per-visit callback:</p>
                            <pre class="mt-1">
router.get('/url', {}, {
  onNetworkError: (error) => {
    // Return false to prevent default
    return false
  },
})</pre
                            >
                        </div>
                        <div
                            class="rounded-lg border border-black/5 bg-neutral-50/80 p-3 font-mono text-xs dark:border-white/5 dark:bg-neutral-900/80"
                        >
                            <p class="font-semibold">Global event:</p>
                            <pre class="mt-1">
router.on('networkError', (event) => {
  console.log(event.detail.exception)
  event.preventDefault()
})</pre
                            >
                        </div>
                    </div>
                </FeatureCard>
            </div>
        </div>
    </AppLayout>
</template>
