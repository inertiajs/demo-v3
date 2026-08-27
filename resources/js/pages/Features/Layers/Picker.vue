<script setup lang="ts">
import { useLayer } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    contacts: { id: number; first_name: string; last_name: string }[];
}>();

const layer = useLayer();

function pick(name: string) {
    layer.emit('picked', name);
    layer.close();
}
</script>

<template>
    <div class="space-y-3">
        <p class="text-sm text-muted-foreground">
            Choosing one emits it to whatever opened this layer, then closes.
        </p>

        <ul class="divide-y">
            <li v-for="contact in contacts" :key="contact.id" class="py-1">
                <Button
                    variant="ghost"
                    size="sm"
                    class="w-full justify-start"
                    @click="pick(`${contact.first_name} ${contact.last_name}`)"
                >
                    {{ contact.first_name }} {{ contact.last_name }}
                </Button>
            </li>
        </ul>
    </div>
</template>
