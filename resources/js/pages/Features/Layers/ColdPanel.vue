<script setup lang="ts">
import { useLayer, usePage } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import layers from '@/wayfinder/routes/features/layers';

const props = defineProps<{ delay: number; loading: string }>();

const page = usePage();
const layer = useLayer();

const treatments = ['shell', 'plain', 'none'];
</script>

<template>
    <div class="space-y-5">
        <p class="text-sm text-muted-foreground">
            You landed on this layer's url, so the layer is all the server sent.
            The client is fetching the page underneath now, and drawing the
            placeholder its url resolves to until it arrives.
        </p>

        <dl class="grid grid-cols-3 gap-y-2 text-sm">
            <dt class="text-muted-foreground">Treatment</dt>
            <dd class="col-span-2">
                <Badge as="span" variant="secondary" class="font-mono">{{
                    props.loading
                }}</Badge>
            </dd>
            <dt class="text-muted-foreground">Base takes</dt>
            <dd class="col-span-2 font-mono">{{ props.delay }}ms</dd>
            <dt class="text-muted-foreground">Walking to</dt>
            <dd class="col-span-2 font-mono text-xs break-all">
                {{ page.layers?.[0]?.base }}
            </dd>
        </dl>

        <div class="space-y-2 border-t pt-4">
            <p class="text-xs font-medium text-muted-foreground">
                Try another treatment
            </p>
            <div class="flex flex-wrap gap-2">
                <Button
                    v-for="treatment in treatments"
                    :key="treatment"
                    :variant="
                        treatment === props.loading ? 'default' : 'outline'
                    "
                    size="sm"
                    as-child
                >
                    <a
                        :href="
                            layers.cold.panel({
                                query: {
                                    delay: props.delay,
                                    loading: treatment,
                                },
                            }).url
                        "
                        >{{ treatment }}</a
                    >
                </Button>
            </div>
            <p class="text-xs text-muted-foreground">
                Each one is a fresh load of this url, which is the only way to
                see the window again.
            </p>
        </div>

        <div class="flex justify-between gap-2 border-t pt-4">
            <Button variant="outline" size="sm" as-child>
                <a :href="layers.cold().url">Leave it and load the base</a>
            </Button>
            <Button variant="ghost" size="sm" @click="layer.close()"
                >Close</Button
            >
        </div>
    </div>
</template>
