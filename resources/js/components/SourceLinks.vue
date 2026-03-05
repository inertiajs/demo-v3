<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const REPO_BASE = 'https://github.com/inertiajs/demo-v3/blob/main/';
const DOCS_BASE = 'https://inertiajs.com/docs/v3/';

defineProps<{
    docs?: string;
    controller?: string;
}>();

const page = usePage();
const componentPath = `resources/js/pages/${page.component}.vue`;
</script>

<template>
    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
        <a
            v-if="docs"
            :href="DOCS_BASE + docs"
            target="_blank"
            class="inline-flex items-center gap-1 text-sm text-muted-foreground underline decoration-muted-foreground/50 hover:text-foreground hover:decoration-foreground"
            >Docs</a
        >
        <a
            :href="REPO_BASE + componentPath"
            target="_blank"
            class="inline-flex items-center gap-1 text-sm text-muted-foreground underline decoration-muted-foreground/50 hover:text-foreground hover:decoration-foreground"
            >Vue page</a
        >
        <a
            v-if="controller"
            :href="REPO_BASE + controller"
            target="_blank"
            class="inline-flex items-center gap-1 text-sm text-muted-foreground underline decoration-muted-foreground/50 hover:text-foreground hover:decoration-foreground"
            >Controller</a
        >
    </div>
</template>
