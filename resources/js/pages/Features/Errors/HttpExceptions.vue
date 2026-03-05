<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onUnmounted } from 'vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import {
    httpException403,
    httpException404,
    httpException500,
    httpExceptionUnhandled,
} from '@/wayfinder/App/Http/Controllers/Feature/NetworkErrorController';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Error Handling' },
    { title: 'HTTP Exceptions' },
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
        log('Global httpException listener removed');
    } else {
        removeListener = router.on('httpException', (event) => {
            event.preventDefault();
            log(
                `Intercepted HTTP ${event.detail.response.status}. Default error modal suppressed.`,
            );
        });
        interceptEnabled.value = true;
        log('Global httpException listener registered');
    }
}

function triggerUnhandledError() {
    router.get(
        httpExceptionUnhandled.url(),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onHttpException: (response) => {
                log(`onHttpException callback fired: HTTP ${response.status}`);
                if (interceptEnabled.value) {
                    return false;
                }
            },
        },
    );
}

onUnmounted(() => {
    removeListener?.();
});
</script>

<template>
    <Head title="HTTP Exceptions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="HTTP Exceptions"
                docs="advanced/error-handling"
                controller="app/Http/Controllers/Feature/NetworkErrorController.php#L11"
            >
                Errors handled by
                <code class="text-xs">handleExceptionsUsing()</code> render as
                full Inertia pages. Unhandled errors can be intercepted
                client-side with <code class="text-xs">onHttpException</code>.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard
                    title="Server-Handled Errors"
                    description="These status codes are handled by handleExceptionsUsing() and render the ErrorPage component. Use your browser's back button to return."
                >
                    <div class="flex flex-wrap gap-2">
                        <Button as-child variant="outline" size="sm">
                            <Link :href="httpException403.url()">
                                403 Forbidden
                            </Link>
                        </Button>
                        <Button as-child variant="outline" size="sm">
                            <Link :href="httpException404.url()">
                                404 Not Found
                            </Link>
                        </Button>
                        <Button as-child variant="destructive" size="sm">
                            <Link :href="httpException500.url()">
                                500 Server Error
                            </Link>
                        </Button>
                    </div>
                </FeatureCard>

                <FeatureCard
                    title="Client-Side Interception"
                    description="Errors NOT handled server-side trigger the httpException event. Enable interception to suppress the default error modal and stay on this page."
                >
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
                        <Button
                            variant="outline"
                            size="sm"
                            @click="triggerUnhandledError"
                        >
                            418 I'm a Teapot
                        </Button>
                        <p class="text-xs text-muted-foreground">
                            Without interception, this shows the default error
                            modal. With interception enabled,
                            <code>event.preventDefault()</code> suppresses it.
                        </p>
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
                        Enable interception and trigger an error to see events.
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
                            <p class="font-semibold">Server-side (handled):</p>
                            <pre class="mt-1 overflow-x-auto">
Inertia::handleExceptionsUsing(
  function (ExceptionResponse $r) {
    return $r->render('ErrorPage', [
      'status' => $r->statusCode(),
    ])->withSharedData();
  }
);</pre
                            >
                        </div>
                        <div
                            class="rounded-lg border border-black/5 bg-neutral-50/80 p-3 font-mono text-xs dark:border-white/5 dark:bg-neutral-900/80"
                        >
                            <p class="font-semibold">
                                Client-side (intercepted):
                            </p>
                            <pre class="mt-1 overflow-x-auto">
router.on('httpException', (event) => {
  console.log(event.detail.response)
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
