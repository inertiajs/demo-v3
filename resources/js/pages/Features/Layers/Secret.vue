<script setup lang="ts">
import { useLayer, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const page = usePage();
const layer = useLayer();
</script>

<template>
    <div class="space-y-4">
        <p class="text-sm text-muted-foreground">
            Ask for this layer while the gate is locked and you take a full-page
            detour through the prompt first. It still opens here, over the
            detours page you started from, which is underneath with its state
            intact.
        </p>

        <p class="text-xs text-muted-foreground">
            This layer's url is <code>{{ page.url }}</code
            >. Back closes it, and the prompt is never a step it can land on:
            the return consumed that entry.
        </p>

        <div class="flex justify-end border-t pt-4">
            <Button variant="ghost" size="sm" @click="layer.close()"
                >Close</Button
            >
        </div>
    </div>
</template>
