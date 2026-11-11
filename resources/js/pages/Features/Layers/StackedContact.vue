<script setup lang="ts">
import { useLayer, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import layers from '@/wayfinder/routes/features/layers';

const props = defineProps<{
    organization: { id: number; name: string };
    contact: {
        id: number;
        first_name: string;
        last_name: string;
        email: string | null;
        phone: string | null;
    };
    notes: { id: number; body: string; user: { name: string } }[];
}>();

const page = usePage();
const layer = useLayer();

function compose() {
    layer.layer({
        component: 'Features/Layers/NoteComposer',
        props: {
            dialog: {
                title: 'Add a note',
                description: 'Level 3 — a local layer with no url of its own.',
                size: 'sm',
            },
            contact: `${props.contact.first_name} ${props.contact.last_name}`,
            action: layers.organization.notes([
                props.organization.id,
                props.contact.id,
            ]).url,
        },
    });
}
</script>

<template>
    <div class="space-y-5">
        <p class="text-sm text-muted-foreground">
            Level 2, at {{ organization.name }}.
        </p>

        <dl class="grid grid-cols-3 gap-y-2 text-sm">
            <dt class="text-muted-foreground">Email</dt>
            <dd class="col-span-2">{{ contact.email ?? '—' }}</dd>
            <dt class="text-muted-foreground">Phone</dt>
            <dd class="col-span-2">{{ contact.phone ?? '—' }}</dd>
        </dl>

        <p
            v-if="page.flash.message"
            class="rounded border border-green-200 bg-green-50 px-2 py-1 text-xs text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
        >
            {{ page.flash.message }}
        </p>

        <div>
            <p class="mb-2 text-xs font-medium text-muted-foreground">Notes</p>
            <ul class="space-y-2">
                <li
                    v-for="note in notes"
                    :key="note.id"
                    class="rounded bg-muted px-2 py-1 text-xs"
                >
                    {{ note.body }}
                    <span class="text-muted-foreground"
                        >— {{ note.user.name }}</span
                    >
                </li>
                <li v-if="!notes.length" class="text-xs text-muted-foreground">
                    No notes yet.
                </li>
            </ul>
        </div>

        <p class="text-xs text-muted-foreground">
            The composer is opened with <code>layer.layer()</code>, so this
            layer owns it. It posts the note itself, and the redirect lands back
            on this layer's own url — which rewrites this layer where it stands
            and closes the composer above it.
        </p>

        <div class="flex justify-between gap-2 border-t pt-4">
            <Button size="sm" @click="compose">Add a note</Button>
            <Button variant="ghost" size="sm" @click="layer.close()"
                >Close this level</Button
            >
        </div>
    </div>
</template>
