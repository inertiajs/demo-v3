<!--
Displays a styled code block with automatic dedenting.

Usage with prop (simple cases, no HTML or mixed quotes):

    <CodeBlock code="
        router.visit('/url', {
          preserveScroll: true,
        })
    " />

Usage with textarea slot (code containing HTML tags, mixed quotes, etc.):

    <CodeBlock title="Example">
        <textarea>
            import { Form } from '@inertiajs/vue3'
            <Form ref="formRef" ...>
        </textarea>
    </CodeBlock>

The textarea trick works because Vue treats <textarea> as a raw text element
(per the HTML spec). Its content is never parsed as Vue template syntax, so
component tags like <Form>, <Link>, directives like @click, and interpolation
like {{ }} are all treated as plain text. The hidden textarea is rendered in
the DOM but invisible — we read its textContent to get the raw code string.
-->

<script setup lang="ts">
import { Check, Copy } from '@lucide/vue';
import { computed, ref, useSlots } from 'vue';

const props = defineProps<{
    code?: string;
    title?: string;
}>();

const slots = useSlots();
const slotRef = ref<HTMLElement | null>(null);
const slotCode = computed(() => slotRef.value?.textContent ?? '');
const copied = ref(false);

function dedent(raw: string) {
    const lines = raw.replace(/^\n/, '').replace(/\s+$/, '').split('\n');
    const indent = Math.min(
        ...lines
            .filter((l) => l.trim())
            .map((l) => l.match(/^(\s*)/)?.[1].length ?? 0),
    );

    return lines.map((l) => l.slice(indent)).join('\n');
}

function copy() {
    navigator.clipboard.writeText(dedent(props.code ?? slotCode.value));
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
}
</script>

<template>
    <div
        class="group relative rounded-lg border border-black/10 bg-neutral-50 p-3 font-mono text-xs dark:border-white/10 dark:bg-neutral-900/80"
    >
        <button
            type="button"
            class="absolute top-2 right-2 rounded p-1 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100 hover:text-foreground"
            @click="copy"
        >
            <Check v-if="copied" class="size-3.5" />
            <Copy v-else class="size-3.5" />
        </button>
        <p v-if="title" class="font-semibold">{{ title }}</p>
        <pre :class="{ 'mt-1': title }">{{ dedent(code ?? slotCode) }}</pre>
        <span v-if="slots.default" ref="slotRef" hidden><slot /></span>
    </div>
</template>
