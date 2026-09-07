<script setup lang="ts">
import {
    Head,
    setLayoutProps,
    useLayer,
    usePage,
    useRemember,
} from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import LayerDemoLayout from '@/layouts/LayerDemoLayout.vue';
import layers from '@/wayfinder/routes/features/layers';

const props = defineProps<{ reloads: number; token: string }>();

defineOptions({ layout: LayerDemoLayout });

const page = usePage();
const layer = useLayer();
const heard = ref<string[]>([]);
const draft = useRemember({ text: '' }, 'draft');
const rows = Array.from({ length: 30 }, (_, i) => i + 1);

onUnmounted(
    layer.on('ping', (payload, childId) =>
        heard.value.unshift(`${childId} said ${JSON.stringify(payload)}`),
    ),
);

function reload() {
    layer.get(
        layers.inside.panel({ query: { reloads: props.reloads + 1 } }).url,
        {},
        { only: ['reloads', 'token'] },
    );
}

function bump() {
    layer.replaceProp('reloads', (current: unknown) => Number(current) + 10);
}

function openChild() {
    layer.layer(layers.inside.child().url);
}

function setNotice() {
    setLayoutProps({ notice: `Set by layer ${layer.id}.` }, layer.id);
}
</script>

<template>
    <Head :title="`Panel ${token}`" />

    <div class="space-y-5">
        <dl class="grid grid-cols-3 gap-y-2 text-sm">
            <dt class="text-muted-foreground">Reloads</dt>
            <dd class="col-span-2 font-mono">{{ reloads }}</dd>
            <dt class="text-muted-foreground">Token</dt>
            <dd class="col-span-2 font-mono">{{ token }}</dd>
            <dt class="text-muted-foreground">This layer</dt>
            <dd class="col-span-2 font-mono text-xs break-all">
                {{ layer.id }}
            </dd>
        </dl>

        <p class="text-xs text-muted-foreground">
            <code>usePage()</code> in here is this layer:
            <Badge as="span" variant="outline" class="font-mono text-[10px]">{{
                page.url
            }}</Badge>
        </p>

        <div class="grid gap-2 sm:grid-cols-2">
            <Button
                variant="outline"
                size="sm"
                data-test="layer-get"
                @click="reload"
            >
                layer.get(…, { only })
            </Button>
            <Button
                variant="outline"
                size="sm"
                data-test="layer-replace-prop"
                @click="bump"
            >
                layer.replaceProp()
            </Button>
            <Button
                variant="outline"
                size="sm"
                data-test="layer-child"
                @click="openChild"
            >
                layer.layer() a child
            </Button>
            <Button
                variant="outline"
                size="sm"
                data-test="layer-emit"
                @click="layer.emit('ping', { from: 'panel' })"
            >
                layer.emit() to the page
            </Button>
        </div>

        <div v-if="heard.length">
            <p class="mb-2 text-xs font-medium text-muted-foreground">
                Heard from the child
            </p>
            <div
                v-for="(entry, i) in heard"
                :key="i"
                class="rounded bg-muted px-2 py-1 font-mono text-xs"
            >
                {{ entry }}
            </div>
        </div>

        <section class="space-y-3 border-t pt-4">
            <h3 class="text-sm font-medium">Its own title</h3>
            <p class="text-xs text-muted-foreground">
                This layer declares a <code>&lt;Head&gt;</code>, so the browser
                tab reads <em>Panel {{ token }}</em> while it is open. Reload it
                above and the title follows the token. Close it and the page's
                title comes back.
            </p>
        </section>

        <section class="space-y-3 border-t pt-4">
            <h3 class="text-sm font-medium">Its own layout</h3>
            <Button
                variant="outline"
                size="sm"
                data-test="layer-layout-prop"
                @click="setNotice"
            >
                setLayoutProps(…, layer.id)
            </Button>
            <p class="text-xs text-muted-foreground">
                This component declares a <code>layout</code> of its own, which
                wraps it inside the dialog. Naming the layer writes to the
                layer's slot, so the banner lands at the top of this dialog and
                the page's own layout is left alone.
            </p>
        </section>

        <section class="space-y-3 border-t pt-4">
            <h3 class="text-sm font-medium">Its own remembered draft</h3>
            <Input
                id="layer-draft"
                v-model="draft.text"
                placeholder="Type something in the layer…"
            />
            <p class="text-xs text-muted-foreground">
                <code>useRemember(…, 'draft')</code> — the same key the page
                underneath uses, filed under this layer. Close this layer and
                press the browser's forward button: it comes back with what you
                typed, and the page's draft never moved. Open the panel again
                from the page instead and you get a new layer with an empty one.
            </p>
        </section>

        <section class="space-y-3 border-t pt-4">
            <h3 class="text-sm font-medium">Its own scroll position</h3>
            <div
                scroll-region
                tabindex="0"
                class="h-32 overflow-y-auto rounded border"
            >
                <div
                    v-for="row in rows"
                    :key="row"
                    class="border-b px-3 py-2 font-mono text-xs last:border-b-0"
                >
                    row {{ row }}
                </div>
            </div>
            <p class="text-xs text-muted-foreground">
                A <code>scroll-region</code> in a layer is restored under the
                layer too. Scroll it, close, press forward — it comes back where
                you left it, and the page underneath keeps its own position.
            </p>
        </section>

        <div class="flex flex-wrap justify-between gap-2 border-t pt-4">
            <Button
                variant="outline"
                size="sm"
                @click="layer.post(layers.inside.close().url)"
            >
                Close from the server
            </Button>
            <Button
                variant="ghost"
                size="sm"
                data-test="layer-close"
                @click="layer.close()"
            >
                layer.close()
            </Button>
        </div>
    </div>
</template>
