<script setup lang="ts">
import { Head, router, useHttp, useProp } from '@inertiajs/vue3';
import { Heart, PartyPopper, Rocket, ThumbsUp } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import CodeBlock from '@/components/CodeBlock.vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { addToast } from '@/composables/useFlashToast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import {
    addReaction,
    burstTicks,
    castVote,
} from '@/wayfinder/App/Http/Controllers/Feature/LivePropsController';

type Framework = 'vue' | 'react' | 'svelte';
type Reaction = 'thumbsUp' | 'party' | 'heart' | 'rocket';

const props = defineProps<{
    visitor: string;
    votes: Record<Framework, number>;
    yourVote: Framework | null;
    reactions: Record<Reaction, number>;
    ticks: number;
    slowTicks: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Data Loading' },
    { title: 'Live Props' },
];

const frameworks: { value: Framework; label: string }[] = [
    { value: 'vue', label: 'Vue' },
    { value: 'react', label: 'React' },
    { value: 'svelte', label: 'Svelte' },
];

const reactionOptions: {
    value: Reaction;
    label: string;
    icon: typeof ThumbsUp;
}[] = [
    { value: 'thumbsUp', label: 'thumbs up', icon: ThumbsUp },
    { value: 'party', label: 'party popper', icon: PartyPopper },
    { value: 'heart', label: 'heart', icon: Heart },
    { value: 'rocket', label: 'rocket', icon: Rocket },
];

const { loading: votesLoading } = useProp('votes');

const totalVotes = computed(() =>
    Object.values(props.votes).reduce((sum, count) => sum + count, 0),
);

function votePercentage(framework: Framework) {
    return totalVotes.value === 0
        ? 0
        : Math.round((props.votes[framework] / totalVotes.value) * 100);
}

const voteRequest = useHttp({ framework: '', toOthers: false });
const reactionRequest = useHttp({ reaction: '' });
const burstRequest = useHttp({});

const skipThisTab = ref(false);

function vote(framework: Framework) {
    voteRequest.framework = framework;
    voteRequest.toOthers = skipThisTab.value;
    voteRequest.submit(castVote());
}

function react(reaction: Reaction) {
    reactionRequest.reaction = reaction;
    reactionRequest.submit(addReaction());
}

function sendBurst() {
    tickEvents.value = 0;
    refreshes.value.ticks = 0;
    refreshes.value.slowTicks = 0;
    burstRequest.submit(burstTicks());
}

// Counted per prop from the partial reloads Inertia starts for live events
const refreshes = ref<Record<string, number>>({
    votes: 0,
    reactions: 0,
    ticks: 0,
    slowTicks: 0,
});

const tickEvents = ref(0);

type LogEntry = {
    id: number;
    at: string;
    event: string;
    summary: string | null;
    props: string[];
    carried: boolean;
    mine: boolean;
};

type EventPayload = {
    visitor?: string;
    framework?: Framework;
    reaction?: Reaction;
} | null;

function summarize(payload: EventPayload) {
    if (payload?.framework) {
        const label = frameworks.find((f) => f.value === payload.framework);

        return `${payload.visitor} voted ${label?.label}`;
    }

    if (payload?.reaction) {
        const option = reactionOptions.find(
            (r) => r.value === payload.reaction,
        );

        return `${payload.visitor} sent a ${option?.label}`;
    }

    return null;
}

const log = ref<LogEntry[]>([]);
let nextLogId = 0;

function carriesValues(payload: unknown) {
    const envelope = (payload as { __inertia?: { props?: object } } | null)
        ?.__inertia;

    return envelope?.props !== undefined;
}

let stopListening: VoidFunction[] = [];

onMounted(() => {
    stopListening = [
        router.on('live', (event) => {
            const { event: name, props: liveProps, payload } = event.detail;

            if (name.endsWith('CounterTicked')) {
                tickEvents.value++;
            }

            const summary = summarize(payload as EventPayload);
            const mine = (payload as EventPayload)?.visitor === props.visitor;

            log.value = [
                {
                    id: nextLogId++,
                    at: new Date().toLocaleTimeString(),
                    event: name.split('\\').pop() ?? name,
                    summary,
                    props: liveProps,
                    carried: carriesValues(payload),
                    mine,
                },
                ...log.value,
            ].slice(0, 8);

            // Notice what other visitors did, while the refresh goes ahead as usual
            if (summary && !mine) {
                addToast(summary);
            }
        }),

        router.on('start', (event) => {
            const { method, only } = event.detail.visit;

            if (method !== 'get') {
                return;
            }

            only.filter((prop) => prop in refreshes.value).forEach(
                (prop) => refreshes.value[prop]++,
            );
        }),
    ];
});

onUnmounted(() => stopListening.forEach((stop) => stop()));
</script>

<template>
    <Head title="Live Props" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Live Props"
                docs="data-props/live-props"
                controller="app/Http/Controllers/Feature/LivePropsController.php#L25"
            >
                Props that refresh themselves when a broadcast event fires,
                delivered here over Laravel Reverb. Every visitor listens on the
                same public channel, so open this page in a second tab, or just
                watch other visitors' votes come in.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard title="Shared Poll" badge="v3.11">
                    <template #description>
                        A vote broadcasts <code class="text-xs">VoteCast</code>,
                        and every page on the channel reloads its
                        <code class="text-xs">votes</code> and
                        <code class="text-xs">yourVote</code> props. The latter
                        is resolved for each visitor, which a value carried by
                        the event could not be.
                    </template>
                    <div class="space-y-4">
                        <div class="space-y-3">
                            <div
                                v-for="framework in frameworks"
                                :key="framework.value"
                                class="space-y-1"
                            >
                                <div
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="font-medium">{{
                                        framework.label
                                    }}</span>
                                    <span
                                        class="text-muted-foreground tabular-nums"
                                        :data-test="`votes-${framework.value}`"
                                        >{{ votes[framework.value] }}
                                        {{
                                            votes[framework.value] === 1
                                                ? 'vote'
                                                : 'votes'
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="h-2 overflow-hidden rounded-full bg-muted"
                                >
                                    <div
                                        class="h-full rounded-full bg-primary transition-all duration-500"
                                        :style="{
                                            width: `${votePercentage(framework.value)}%`,
                                        }"
                                    />
                                </div>
                            </div>
                        </div>

                        <p class="text-sm" data-test="your-vote">
                            You are <strong>{{ visitor }}</strong
                            >.
                            <template v-if="yourVote">
                                Your vote:
                                <strong>{{
                                    frameworks.find((f) => f.value === yourVote)
                                        ?.label
                                }}</strong
                                >. Voting again moves it.
                            </template>
                            <template v-else>You haven't voted yet.</template>
                        </p>

                        <div class="flex flex-wrap items-center gap-2">
                            <Button
                                v-for="framework in frameworks"
                                :key="framework.value"
                                size="sm"
                                :variant="
                                    yourVote === framework.value
                                        ? 'default'
                                        : 'outline'
                                "
                                @click="vote(framework.value)"
                            >
                                Vote {{ framework.label }}
                            </Button>
                            <Badge v-if="votesLoading" variant="secondary"
                                >Refreshing...</Badge
                            >
                        </div>

                        <div class="flex items-center gap-3">
                            <Button
                                size="sm"
                                :variant="skipThisTab ? 'default' : 'outline'"
                                @click="skipThisTab = !skipThisTab"
                            >
                                Skip this tab: {{ skipThisTab ? 'On' : 'Off' }}
                            </Button>
                            <p class="text-xs text-muted-foreground">
                                Broadcasts with
                                <code>toOthers()</code>, so only other tabs and
                                visitors refresh.
                            </p>
                        </div>

                        <CodeBlock
                            code="
                            'votes' => Inertia::live(
                                fn () => $this->votes(),
                                on: VoteCast::class,
                            ),

                            'yourVote' => Inertia::live(
                                fn () => $this->voteOf($visitor),
                                on: VoteCast::class,
                            ),
                        "
                        />
                    </div>
                </FeatureCard>

                <FeatureCard title="Broadcast Values">
                    <template #description>
                        <code class="text-xs">ReactionAdded</code> carries the
                        new counts with
                        <code class="text-xs">Inertia::broadcastProps()</code>,
                        so pages write them in without making a request.
                    </template>
                    <div class="space-y-4">
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-for="reaction in reactionOptions"
                                :key="reaction.value"
                                variant="outline"
                                class="tabular-nums"
                                :aria-label="`Send a ${reaction.label}`"
                                @click="react(reaction.value)"
                            >
                                <component :is="reaction.icon" />
                                <span
                                    :data-test="`reactions-${reaction.value}`"
                                    >{{ reactions[reaction.value] }}</span
                                >
                            </Button>
                        </div>

                        <p class="text-sm">
                            Requests made for
                            <code class="text-xs">reactions</code>:
                            <strong
                                class="tabular-nums"
                                data-test="reaction-requests"
                                >{{ refreshes.reactions }}</strong
                            >
                        </p>

                        <CodeBlock
                            code="
                            public function broadcastWith(): array
                            {
                                return Inertia::broadcastProps([
                                    'reactions' => $this->reactions,
                                ]);
                            }
                        "
                        />
                    </div>
                </FeatureCard>

                <FeatureCard title="Throttling">
                    <template #description>
                        Each live prop refreshes at most once per second by
                        default. Events that arrive in between are collected
                        into the next request.
                    </template>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <Button @click="sendBurst">Send 20 Events</Button>
                            <span class="text-xs text-muted-foreground"
                                >One every 200ms, for everyone on the
                                page.</span
                            >
                        </div>

                        <p class="text-sm">
                            Events received:
                            <strong
                                class="tabular-nums"
                                data-test="tick-events"
                                >{{ tickEvents }}</strong
                            >
                        </p>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div
                                class="space-y-1 rounded-lg border border-black/5 p-3 dark:border-white/5"
                            >
                                <code class="text-sm font-semibold">ticks</code>
                                <p class="text-xs text-muted-foreground">
                                    Default throttle of one second.
                                </p>
                                <p class="pt-1 text-sm">
                                    Value:
                                    <strong class="tabular-nums">{{
                                        ticks
                                    }}</strong>
                                    · Requests:
                                    <strong class="tabular-nums">{{
                                        refreshes.ticks
                                    }}</strong>
                                </p>
                            </div>
                            <div
                                class="space-y-1 rounded-lg border border-black/5 p-3 dark:border-white/5"
                            >
                                <code class="text-sm font-semibold"
                                    >slowTicks</code
                                >
                                <p class="text-xs text-muted-foreground">
                                    Its own throttle of three seconds.
                                </p>
                                <p class="pt-1 text-sm">
                                    Value:
                                    <strong class="tabular-nums">{{
                                        slowTicks
                                    }}</strong>
                                    · Requests:
                                    <strong class="tabular-nums">{{
                                        refreshes.slowTicks
                                    }}</strong>
                                </p>
                            </div>
                        </div>

                        <CodeBlock
                            code="
                            'slowTicks' => Inertia::live(
                                fn () => $this->ticks(),
                                on: CounterTicked::class,
                                throttle: 3000,
                            ),
                        "
                        />
                    </div>
                </FeatureCard>

                <FeatureCard title="Live Events">
                    <template #description>
                        Inertia fires a <code class="text-xs">live</code> event
                        before each refresh, with the props, channel, event name
                        and payload. This page listens to notice what other
                        visitors did, and the refresh goes ahead as usual.
                        Returning <code class="text-xs">false</code>
                        would cancel it.
                    </template>
                    <div class="space-y-4">
                        <p class="text-sm text-muted-foreground">
                            Vote or react from another browser, or a private
                            window, to see a notice pop up.
                        </p>

                        <CodeBlock
                            code="
                            router.on('live', (event) => {
                              const { visitor, framework } = event.detail.payload

                              notify(`${visitor} voted ${framework}`)
                            })
                        "
                        />
                    </div>
                </FeatureCard>

                <FeatureCard
                    info-card
                    class="lg:col-span-2"
                    title="Event Log"
                    description="Broadcast events this tab received, newest first."
                >
                    <ul class="space-y-1 font-mono text-xs">
                        <li
                            v-for="entry in log"
                            :key="entry.id"
                            class="flex flex-wrap items-center gap-2"
                        >
                            <span class="text-muted-foreground">{{
                                entry.at
                            }}</span>
                            <span class="font-semibold">{{ entry.event }}</span>
                            <span v-if="entry.summary">{{
                                entry.summary
                            }}</span>
                            <span class="text-muted-foreground">{{
                                entry.props.join(', ')
                            }}</span>
                            <Badge v-if="entry.mine">you</Badge>
                            <Badge v-if="entry.carried" variant="secondary"
                                >carried values</Badge
                            >
                        </li>
                        <li
                            v-if="log.length === 0"
                            class="text-muted-foreground"
                        >
                            Nothing yet. Vote, react, or send a burst.
                        </li>
                    </ul>
                </FeatureCard>
            </div>
        </div>
    </AppLayout>
</template>
