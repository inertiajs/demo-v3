<script setup lang="ts">
import { useLayer } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const props = defineProps<{ contact: number; message: string }>();

const layer = useLayer();

function confirm() {
    layer.emit('confirmed', props.contact);
    layer.close();
}
</script>

<template>
    <div class="space-y-4">
        <p class="text-sm">{{ message }}</p>

        <div class="flex justify-end gap-2">
            <Button variant="ghost" size="sm" @click="layer.close()"
                >Cancel</Button
            >
            <Button size="sm" @click="confirm">Confirm</Button>
        </div>
    </div>
</template>
