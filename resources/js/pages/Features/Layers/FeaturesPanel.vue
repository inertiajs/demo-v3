<script setup lang="ts">
import {
    InfiniteScroll,
    useLayer,
    usePage,
    usePoll,
    WhenVisible,
} from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';

defineProps<{
    tick: string;
    openedAt: string;
    feed: { id: number; at: string }[];
    groups: { recent: string[]; starred: string[] };
    contacts: { data: { id: number; first_name: string; last_name: string }[] };
    onDemand?: {
        generatedAt: string;
        contacts: { id: number; name: string }[];
    };
    farBelow?: { generatedAt: string };
}>();

const page = usePage();
const layer = useLayer();
const polling = ref(false);

const poll = usePoll(
    2000,
    { only: ['tick', 'feed', 'groups'] },
    { autoStart: false },
);

onUnmounted(() => poll.stop());

function togglePolling() {
    if (polling.value) {
        poll.stop();
    } else {
        poll.start();
    }

    polling.value = !polling.value;
}
</script>

<template>
    <div class="space-y-6">
        <dl class="grid grid-cols-3 gap-y-2 text-sm">
            <dt class="text-muted-foreground">This layer</dt>
            <dd class="col-span-2 font-mono text-xs break-all">
                {{ layer.id }}
            </dd>
            <dt class="text-muted-foreground">Layer url</dt>
            <dd class="col-span-2 font-mono text-xs break-all">
                {{ page.url }}
            </dd>
        </dl>

        <section class="space-y-3 border-t pt-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-medium">usePoll</h3>
                <Button
                    :variant="polling ? 'outline' : 'default'"
                    size="sm"
                    @click="togglePolling"
                >
                    {{ polling ? 'Stop polling' : 'Start polling' }}
                </Button>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="text-muted-foreground">Layer refreshed at</span>
                <Badge as="span" variant="secondary" class="font-mono">{{
                    tick
                }}</Badge>
            </div>
            <p class="text-xs text-muted-foreground">
                <code>usePoll()</code> called in here polls this layer's own
                url. The timestamp on the page underneath never moves.
            </p>
        </section>

        <section class="space-y-3 border-t pt-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-medium">Merged props</h3>
                <Button
                    variant="outline"
                    size="sm"
                    @click="layer.reload({ only: ['feed', 'groups'] })"
                >
                    Ask again
                </Button>
            </div>
            <p class="text-xs text-muted-foreground">
                <code>Inertia::merge()</code> appends to the list;
                <code>Inertia::deepMerge()</code> appends inside each key of a
                nested one. Both merge into this layer's props.
            </p>
            <ul tabindex="0" class="max-h-24 space-y-1 overflow-y-auto">
                <li
                    v-for="entry in feed"
                    :key="entry.id"
                    class="rounded bg-muted px-2 py-1 font-mono text-xs"
                >
                    {{ entry.id }} at {{ entry.at }}
                </li>
            </ul>
            <dl class="grid grid-cols-3 gap-y-1 text-xs">
                <dt class="text-muted-foreground">groups.recent</dt>
                <dd class="col-span-2 font-mono break-all">
                    {{ groups.recent.join(', ') }}
                </dd>
                <dt class="text-muted-foreground">groups.starred</dt>
                <dd class="col-span-2 font-mono break-all">
                    {{ groups.starred.join(', ') }}
                </dd>
            </dl>
        </section>

        <section class="space-y-3 border-t pt-4">
            <h3 class="text-sm font-medium">A once prop</h3>
            <div class="flex items-center gap-2 text-sm">
                <span class="text-muted-foreground">Opened at</span>
                <Badge as="span" variant="outline" class="font-mono">{{
                    openedAt
                }}</Badge>
            </div>
            <p class="text-xs text-muted-foreground">
                <code>Inertia::once()</code> is asked for once and kept. Reload
                this layer as often as you like — the timestamp above stays
                where it was while the one under <code>usePoll</code> moves.
            </p>
        </section>

        <section class="space-y-3 border-t pt-4">
            <h3 class="text-sm font-medium">An optional prop, on demand</h3>
            <Button
                variant="outline"
                size="sm"
                @click="layer.reload({ only: ['onDemand'] })"
            >
                Load it
            </Button>
            <div v-if="onDemand" class="space-y-1">
                <p class="text-xs text-muted-foreground">
                    Generated at {{ onDemand.generatedAt }}
                </p>
                <ul class="space-y-1">
                    <li
                        v-for="contact in onDemand.contacts"
                        :key="contact.id"
                        class="rounded bg-muted px-2 py-1 text-xs"
                    >
                        {{ contact.name }}
                    </li>
                </ul>
            </div>
            <p v-else class="text-xs text-muted-foreground">
                <code>Inertia::optional()</code> means it is absent until asked
                for. The reload goes through <code>useLayer()</code>, so it
                lands here.
            </p>
        </section>

        <section class="space-y-3 border-t pt-4">
            <h3 class="text-sm font-medium">Infinite scroll</h3>
            <p class="text-xs text-muted-foreground">
                <code>Inertia::scroll()</code> paginated into a box of its own.
                The observer watches the box it is in, and each page merges into
                this layer's props rather than the page's.
            </p>
            <div tabindex="0" class="max-h-40 overflow-y-auto rounded border">
                <InfiniteScroll data="contacts" :buffer="80" preserve-url>
                    <div
                        v-for="contact in contacts.data"
                        :key="contact.id"
                        class="border-b px-3 py-2 text-xs last:border-b-0"
                    >
                        {{ contact.first_name }} {{ contact.last_name }}
                    </div>
                    <template #loading>
                        <div class="px-3 py-2 text-xs text-muted-foreground">
                            Loading…
                        </div>
                    </template>
                </InfiniteScroll>
            </div>
            <p class="text-xs text-muted-foreground">
                Loaded {{ contacts.data.length }} so far.
            </p>
        </section>

        <section class="space-y-3 border-t pt-4">
            <h3 class="text-sm font-medium">WhenVisible</h3>
            <p class="text-xs text-muted-foreground">
                Keep scrolling this panel. The observer watches an element
                inside the dialog, and the request it fires belongs to the
                layer.
            </p>
            <div class="h-72 rounded border border-dashed"></div>
            <WhenVisible data="farBelow">
                <template #fallback>
                    <Skeleton class="h-4 w-40" />
                </template>
                <p class="text-sm">
                    Loaded when it came into view, at
                    <span class="font-mono">{{ farBelow?.generatedAt }}</span>
                </p>
            </WhenVisible>
        </section>
    </div>
</template>
