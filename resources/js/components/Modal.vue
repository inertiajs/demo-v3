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
            md: 'max-w-lg',
            lg: 'max-w-2xl',
            xl: 'max-w-4xl',
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
        class="layer-modal m-auto flex max-h-[calc(100%-4rem)] w-[calc(100%-2rem)] flex-col overflow-hidden rounded-xl border bg-card p-0 text-card-foreground shadow-2xl"
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

        <div class="min-h-0 flex-auto overflow-y-auto px-5 py-4">
            <slot />
        </div>
    </Layer>
</template>

<style>
.layer-modal::backdrop {
    background-color: rgb(0 0 0 / 0.5);
}

/* Server-rendered until showModal() runs on hydration, and a dialog that is not modal yet is laid
   out at the top of the page. Place it where the modal one lands so a cold-opened layer does not
   jump once the JS arrives. The shadow stands in for the backdrop, which modal dialogs alone get. */
.layer-modal:not(:modal) {
    position: fixed;
    inset: 0;
    box-shadow: 0 0 0 100vmax rgb(0 0 0 / 0.5);
}

.layer-modal {
    animation: layer-modal-in 200ms ease-out;
    transition:
        transform 200ms ease,
        opacity 200ms ease;
}

.layer-modal[data-layer-top='false'] {
    transform: scale(0.96) translateY(-10px);
    opacity: 0.85;
}

.layer-modal[data-layer-closing='true'] {
    animation: layer-modal-out 160ms ease-in forwards;
}

@media (prefers-reduced-motion: reduce) {
    .layer-modal,
    .layer-modal[data-layer-top='false'],
    .layer-modal[data-layer-closing='true'] {
        animation: none;
        transition: none;
    }
}

@keyframes layer-modal-in {
    from {
        opacity: 0;
        transform: translateY(10px) scale(0.97);
    }
}

@keyframes layer-modal-out {
    to {
        opacity: 0;
        transform: translateY(6px) scale(0.98);
    }
}
</style>
