<script setup lang="ts">
withDefaults(defineProps<{ notice?: string }>(), { notice: '' });
</script>

<template>
    <div class="space-y-4">
        <p
            v-if="notice"
            data-test="layer-notice"
            class="rounded-md border border-primary/30 bg-primary/5 px-3 py-2 text-xs text-primary"
        >
            {{ notice }}
        </p>
        <slot />
    </div>
</template>
