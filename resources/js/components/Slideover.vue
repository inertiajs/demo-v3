<script setup lang="ts">
import { Layer } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

const props = withDefaults(
    defineProps<{
        open: boolean;
        index: number;
        isTop: boolean;
        type: 'routed' | 'local';
        close: () => void;
        done: () => void;
        title?: string;
        description?: string;
        size?: 'sm' | 'md' | 'lg' | 'xl';
    }>(),
    { size: 'md' },
);

const width = computed(
    () =>
        ({
            sm: 'max-w-sm',
            md: 'max-w-md',
            lg: 'max-w-xl',
            xl: 'max-w-3xl',
        })[props.size],
);
</script>

<template>
    <Layer
        :open="open"
        :index="index"
        :is-top="isTop"
        :type="type"
        :close="close"
        :done="done"
        :label="title"
        class="layer-slideover my-0 mr-0 ml-auto flex h-full max-h-full w-full flex-col overflow-hidden rounded-none border-0 border-l bg-card p-0 text-card-foreground shadow-2xl"
        :class="width"
        @click.self="close()"
    >
        <header
            class="flex items-start justify-between gap-4 border-b px-5 py-4"
        >
            <div class="space-y-1">
                <h2
                    class="flex items-center gap-2 font-semibold tracking-tight"
                >
                    <slot name="title">{{ title }}</slot>
                </h2>
                <p v-if="description" class="text-sm text-muted-foreground">
                    {{ description }}
                </p>
            </div>
            <Button
                variant="ghost"
                size="icon"
                class="-mt-1 -mr-2 shrink-0"
                @click="close()"
            >
                <X class="size-4" />
                <span class="sr-only">Close</span>
            </Button>
        </header>

        <div class="flex-1 overflow-y-auto px-5 py-4">
            <slot />
        </div>
    </Layer>
</template>

<style>
.layer-slideover::backdrop {
    background-color: rgb(0 0 0 / 0.5);
}

/* Server-rendered until showModal() runs on hydration, and a dialog that is not modal yet is laid
   out at the top of the page. Place it where the modal one lands so a cold-opened layer does not
   jump once the JS arrives. The shadow stands in for the backdrop, which modal dialogs alone get. */
.layer-slideover:not(:modal) {
    position: fixed;
    inset: 0;
    box-shadow: 0 0 0 100vmax rgb(0 0 0 / 0.5);
}

.layer-slideover {
    animation: layer-slideover-in 260ms cubic-bezier(0.32, 0.72, 0, 1);
}

.layer-slideover[data-layer-closing='true'] {
    animation: layer-slideover-out 200ms ease-in forwards;
}

@media (prefers-reduced-motion: reduce) {
    .layer-slideover,
    .layer-slideover[data-layer-closing='true'] {
        animation: none;
    }
}

@keyframes layer-slideover-in {
    from {
        transform: translateX(100%);
    }
}

@keyframes layer-slideover-out {
    to {
        transform: translateX(100%);
    }
}
</style>
