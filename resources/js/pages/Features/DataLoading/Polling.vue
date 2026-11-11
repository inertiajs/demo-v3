<script setup lang="ts">
import { Head, usePoll } from '@inertiajs/vue3';
import { computed, onUnmounted, reactive, ref } from 'vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

defineProps<{
    currentTime: string;
    randomNumber: number;
    contactCount: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Data Loading' },
    { title: 'Polling' },
];

type PollMode = 'overlap' | 'cancel' | 'rest';

const pollMode = ref<PollMode>('overlap');
const pollCount = ref(0);
const inFlight = ref(0);

const requestOptions = {
    onStart: () => inFlight.value++,
    onFinish: () => {
        inFlight.value = Math.max(0, inFlight.value - 1);
        pollCount.value++;
    },
};

const polls: Record<PollMode, ReturnType<typeof usePoll>> = {
    overlap: usePoll(2000, requestOptions, {
        autoStart: false,
        mode: 'overlap',
    }),
    cancel: usePoll(2000, requestOptions, { autoStart: false, mode: 'cancel' }),
    rest: usePoll(2000, requestOptions, { autoStart: false, mode: 'rest' }),
};

const isPolling = computed(() => polls[pollMode.value].polling.value);

function stopAll() {
    Object.values(polls).forEach((p) => p.stop());
}

function togglePolling() {
    if (isPolling.value) {
        stopAll();
    } else {
        polls[pollMode.value].start();
    }
}

function setMode(mode: PollMode) {
    if (pollMode.value === mode) return;

    const wasPolling = isPolling.value;
    if (wasPolling) {
        stopAll();
    }

    pollMode.value = mode;

    if (wasPolling) {
        polls[mode].start();
    }
}

type PollBackground = 'throttle' | 'pause' | 'continue';

const backgroundRequests = reactive<
    Record<PollBackground, { total: number; hidden: number }>
>({
    throttle: { total: 0, hidden: 0 },
    pause: { total: 0, hidden: 0 },
    continue: { total: 0, hidden: 0 },
});

function backgroundPoll(background: PollBackground) {
    return usePoll(
        1000,
        {
            only: ['currentTime'],
            onStart: () => {
                backgroundRequests[background].total++;

                if (document.hidden) {
                    backgroundRequests[background].hidden++;
                }
            },
        },
        { autoStart: false, background },
    );
}

const backgroundPolls: Record<PollBackground, ReturnType<typeof usePoll>> = {
    throttle: backgroundPoll('throttle'),
    pause: backgroundPoll('pause'),
    continue: backgroundPoll('continue'),
};

const isBackgroundPolling = computed(
    () => backgroundPolls.throttle.polling.value,
);

function toggleBackgroundPolling() {
    if (isBackgroundPolling.value) {
        Object.values(backgroundPolls).forEach((p) => p.stop());

        return;
    }

    Object.values(backgroundRequests).forEach((requests) => {
        requests.total = 0;
        requests.hidden = 0;
    });

    Object.values(backgroundPolls).forEach((p) => p.start());
}

const backgrounds: {
    value: PollBackground;
    description: string;
}[] = [
    {
        value: 'throttle',
        description: 'Default. Polls at 10% of the rate while hidden.',
    },
    {
        value: 'pause',
        description: 'Stops while hidden and resumes when you come back.',
    },
    {
        value: 'continue',
        description: 'Keeps polling at the full rate.',
    },
];

onUnmounted(() => stopAll());

const modes: { value: PollMode; label: string; description: string }[] = [
    {
        value: 'overlap',
        label: 'overlap',
        description: 'Default. Requests fire every interval, may stack up.',
    },
    {
        value: 'cancel',
        label: 'cancel',
        description: 'Aborts the in-flight request before starting the next.',
    },
    {
        value: 'rest',
        label: 'rest',
        description: 'Waits the interval after each response. No overlap.',
    },
];
</script>

<template>
    <Head title="Polling" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Polling"
                docs="data-props/polling"
                controller="app/Http/Controllers/Feature/DataLoadingController.php#L114"
            >
                Periodic data refreshing with
                <code class="text-xs">usePoll()</code>. The
                <code class="text-xs">mode</code> option controls how concurrent
                requests are handled when responses take longer than the
                interval.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Controls -->
                <FeatureCard title="Polling Controls">
                    <template #description>
                        Server sleeps 500-3000ms per request so you can see the
                        modes diverge.
                    </template>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <Button
                                @click="togglePolling"
                                :variant="isPolling ? 'destructive' : 'default'"
                            >
                                {{
                                    isPolling ? 'Stop Polling' : 'Start Polling'
                                }}
                            </Button>
                            <Badge
                                :variant="isPolling ? 'default' : 'secondary'"
                            >
                                {{ isPolling ? 'Active' : 'Stopped' }}
                            </Badge>
                        </div>

                        <div class="space-y-2">
                            <p
                                class="flex items-center gap-2 text-sm font-medium"
                            >
                                Mode
                                <span
                                    class="shrink-0 rounded bg-indigo-500/10 px-2 py-1 text-[10px] leading-none font-semibold text-indigo-500"
                                    >v3.2</span
                                >
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <Button
                                    v-for="m in modes"
                                    :key="m.value"
                                    size="sm"
                                    :variant="
                                        pollMode === m.value
                                            ? 'default'
                                            : 'outline'
                                    "
                                    @click="setMode(m.value)"
                                >
                                    {{ m.label }}
                                </Button>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{
                                    modes.find((m) => m.value === pollMode)
                                        ?.description
                                }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                Poll count:
                                <strong class="tabular-nums">{{
                                    pollCount
                                }}</strong>
                            </div>
                            <div>
                                In flight:
                                <strong class="tabular-nums">{{
                                    inFlight
                                }}</strong>
                            </div>
                        </div>

                        <div
                            class="rounded-lg border border-black/5 bg-neutral-50/80 p-3 font-mono text-xs dark:border-white/5 dark:bg-neutral-900/80"
                        >
                            <p>
                                <strong>Tip:</strong> With
                                <code>overlap</code> and a slow server, watch
                                <em>In flight</em> climb past 1.
                                <code>cancel</code> keeps it at 1 by aborting,
                                <code>rest</code> keeps it at 1 by waiting the
                                interval after each response.
                            </p>
                        </div>
                    </div>
                </FeatureCard>

                <!-- Live Data -->
                <FeatureCard
                    info-card
                    title="Live Data"
                    description="These values refresh from the server on each poll cycle."
                >
                    <div class="space-y-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium"
                                    >Server Time</span
                                >
                                <Badge
                                    variant="outline"
                                    class="font-mono text-xs"
                                    >{{ currentTime }}</Badge
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium"
                                    >Random Number</span
                                >
                                <Badge
                                    variant="secondary"
                                    class="tabular-nums"
                                    >{{ randomNumber }}</Badge
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium"
                                    >Contact Count</span
                                >
                                <Badge
                                    variant="secondary"
                                    class="tabular-nums"
                                    >{{ contactCount }}</Badge
                                >
                            </div>
                        </div>
                    </div>
                </FeatureCard>

                <FeatureCard
                    class="lg:col-span-2"
                    title="Background Tabs"
                    badge="v3.9"
                >
                    <template #description>
                        The <code class="text-xs">background</code> option
                        controls polling while the tab is hidden. Start all
                        three, switch to another tab for a few seconds, then
                        come back.
                    </template>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <Button
                                @click="toggleBackgroundPolling"
                                :variant="
                                    isBackgroundPolling
                                        ? 'destructive'
                                        : 'default'
                                "
                            >
                                {{
                                    isBackgroundPolling
                                        ? 'Stop Background Polls'
                                        : 'Start Background Polls'
                                }}
                            </Button>
                            <span class="text-xs text-muted-foreground"
                                >Each poll runs every second.</span
                            >
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div
                                v-for="b in backgrounds"
                                :key="b.value"
                                class="space-y-1 rounded-lg border border-black/5 p-3 dark:border-white/5"
                            >
                                <code class="text-sm font-semibold">{{
                                    b.value
                                }}</code>
                                <p class="text-xs text-muted-foreground">
                                    {{ b.description }}
                                </p>
                                <p class="pt-1 text-sm">
                                    Total:
                                    <strong
                                        class="tabular-nums"
                                        :data-test="`total-${b.value}`"
                                        >{{
                                            backgroundRequests[b.value].total
                                        }}</strong
                                    >
                                    · While hidden:
                                    <strong
                                        class="tabular-nums"
                                        :data-test="`hidden-${b.value}`"
                                        >{{
                                            backgroundRequests[b.value].hidden
                                        }}</strong
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                </FeatureCard>
            </div>
        </div>
    </AppLayout>
</template>
