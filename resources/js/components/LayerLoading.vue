<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Skeleton } from '@/components/ui/skeleton';
import { Spinner } from '@/components/ui/spinner';

const page = usePage();

// This renders as the page at its own url, so the url is where it reads what it should look like.
const variant = computed(
    () =>
        new URLSearchParams(page.url.split('?')[1] ?? '').get('loading') ??
        'shell',
);
</script>

<template>
    <div
        v-if="variant === 'plain'"
        data-test="layer-loading"
        class="flex min-h-screen flex-col bg-background"
        aria-hidden="true"
    >
        <!-- Along the top, not the middle: a centred dialog sits in the top layer, so anything
             behind it in the middle of the screen is a spinner nobody ever sees. -->
        <div class="flex h-14 items-center gap-2 border-b px-4 text-sm">
            <Spinner />
            Loading the page underneath…
        </div>
    </div>

    <div
        v-else-if="variant === 'shell'"
        data-test="layer-loading"
        class="flex min-h-screen gap-2 bg-background p-2"
        aria-hidden="true"
    >
        <div class="hidden w-64 shrink-0 flex-col gap-2 p-2 md:flex">
            <Skeleton class="h-8 w-40" />
            <Skeleton class="mt-4 h-4 w-24" />
            <Skeleton v-for="row in 5" :key="row" class="h-8 w-full" />
        </div>
        <div
            class="flex flex-1 flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm"
        >
            <Skeleton class="h-5 w-48" />
            <Skeleton class="h-8 w-72" />
            <div class="grid flex-1 gap-4 lg:grid-cols-2">
                <Skeleton v-for="card in 4" :key="card" class="h-40 w-full" />
            </div>
        </div>
    </div>

    <!-- Nothing at all, which is a base like any other: the layer is then the only thing in the
         document until the real page lands. -->
    <template v-else-if="variant === 'none'" />
</template>
