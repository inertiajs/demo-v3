<script setup lang="ts">
import { useLayer } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const layer = useLayer();
</script>

<template>
    <div class="space-y-4">
        <p class="text-sm text-muted-foreground">
            The panel opens this layer with <code>layer.layer()</code>, which
            makes the panel its owner. What it emits arrives there and goes no
            further.
        </p>

        <div class="flex flex-col gap-2">
            <Button
                variant="outline"
                size="sm"
                @click="layer.emit('ping', { from: 'child' })"
            >
                Emit to my owner
            </Button>
            <Button variant="ghost" size="sm" @click="layer.close()">
                Close myself
            </Button>
        </div>
    </div>
</template>
