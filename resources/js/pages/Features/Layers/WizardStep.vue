<script setup lang="ts">
import { Link, useLayer } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import layers from '@/wayfinder/routes/features/layers';

const props = defineProps<{
    step: string;
    steps: string[];
    blurb: string;
}>();

const layer = useLayer();
const index = computed(() => props.steps.indexOf(props.step));
const next = computed(() => props.steps[index.value + 1] ?? null);
const previous = computed(() => props.steps[index.value - 1] ?? null);
</script>

<template>
    <div class="space-y-5">
        <ol class="flex gap-2 text-xs">
            <li
                v-for="(name, i) in steps"
                :key="name"
                class="rounded px-2 py-1 capitalize"
                :class="
                    i === index
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted text-muted-foreground'
                "
            >
                {{ name }}
            </li>
        </ol>

        <p class="text-sm">{{ blurb }}</p>

        <p class="text-xs text-muted-foreground">
            The stack is still one layer deep. Stepping is an ordinary link to
            another url that happens to answer with the same key, and it carries
            <code>view-transition</code>, so the browser animates this layer
            alone while the page underneath sits still.
        </p>

        <div class="flex flex-wrap items-center gap-2 border-t pt-4">
            <Button v-if="previous" variant="outline" size="sm" as-child>
                <Link :href="layers.wizard.step(previous)" view-transition
                    >Back</Link
                >
            </Button>
            <Button v-if="next" size="sm" as-child>
                <Link :href="layers.wizard.step(next)" view-transition
                    >Next</Link
                >
            </Button>
            <Button v-else size="sm" @click="layer.close()">Finish</Button>
            <Button variant="ghost" size="sm" as-child>
                <Link :href="layers.wizard.help()">What is a key?</Link>
            </Button>
            <Button variant="ghost" size="sm" class="ml-auto" as-child>
                <Link :href="layers.local()">Leave the wizard</Link>
            </Button>
        </div>
    </div>
</template>
