<script setup lang="ts">
import { Link, useLayer } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import layers from '@/wayfinder/routes/features/layers';

const props = defineProps<{
    organization: { id: number; name: string; contacts_count?: number };
    contacts: {
        id: number;
        first_name: string;
        last_name: string;
        email: string | null;
    }[];
}>();

const layer = useLayer();
</script>

<template>
    <div class="space-y-4">
        <p class="text-sm text-muted-foreground">
            Level 1. Every link in here is a link inside a layer, so it opens
            over this one rather than replacing the page underneath.
        </p>

        <ul class="divide-y">
            <li
                v-for="contact in contacts"
                :key="contact.id"
                class="flex items-center justify-between gap-3 py-2"
            >
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium">
                        {{ contact.first_name }} {{ contact.last_name }}
                    </p>
                    <p class="truncate text-xs text-muted-foreground">
                        {{ contact.email ?? 'No email' }}
                    </p>
                </div>
                <Button variant="outline" size="sm" as-child>
                    <Link
                        :href="
                            layers.organization.contact([
                                props.organization.id,
                                contact.id,
                            ])
                        "
                        data-test="open-contact"
                        >Open</Link
                    >
                </Button>
            </li>
        </ul>

        <div class="flex justify-end border-t pt-4">
            <Button variant="ghost" size="sm" @click="layer.close()"
                >Close this level</Button
            >
        </div>
    </div>
</template>
