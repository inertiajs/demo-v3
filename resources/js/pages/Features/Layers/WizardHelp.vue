<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import layers from '@/wayfinder/routes/features/layers';
</script>

<template>
    <div class="space-y-4">
        <p class="text-sm text-muted-foreground">
            This slideover declares a key of its own, so opening it from a step
            stacks it on top of the wizard rather than replacing it.
        </p>

        <p class="text-sm text-muted-foreground">
            Jumping to a step from here answers with the wizard's key. That
            rewrites the wizard underneath and closes this layer on the way.
        </p>

        <div class="flex flex-col gap-2 border-t pt-4">
            <Button variant="outline" size="sm" as-child>
                <Link :href="layers.wizard.step('details')"
                    >Jump back to step one</Link
                >
            </Button>
            <Button variant="outline" size="sm" as-child>
                <Link :href="layers.wizard.step('review')"
                    >Jump ahead to review</Link
                >
            </Button>
        </div>
    </div>
</template>
