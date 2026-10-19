<script setup lang="ts">
import { Deferred, Link, useForm, useLayer, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import contactNotes from '@/wayfinder/routes/contacts/notes';
import layers from '@/wayfinder/routes/features/layers';

const props = defineProps<{
    contact: {
        id: number;
        first_name: string;
        last_name: string;
        email: string | null;
        phone: string | null;
        is_favorite: boolean;
        organization?: { id: number; name: string };
    };
    notes?: { id: number; body: string; user: { name: string } }[];
}>();

const page = usePage();
const layer = useLayer();
const form = useForm({ refuse: false });

type CardProps = { contact: { is_favorite: boolean } };

function toggleFavorite() {
    form.optimistic<CardProps>((current) => ({
        contact: {
            ...current.contact,
            is_favorite: !current.contact.is_favorite,
        },
    })).post(layers.dialogs.favorite(props.contact.id).url, {
        preserveScroll: true,
    });
}

function addNote() {
    layer.layer({
        component: 'Features/Layers/NoteComposer',
        props: {
            dialog: {
                title: 'Add a note',
                description: `A note for ${props.contact.first_name}.`,
                size: 'sm',
            },
            contact: `${props.contact.first_name} ${props.contact.last_name}`,
            action: contactNotes.store(props.contact.id).url,
        },
    });
}
</script>

<template>
    <div class="space-y-5">
        <dl class="grid grid-cols-3 gap-y-2 text-sm">
            <dt class="text-muted-foreground">Email</dt>
            <dd class="col-span-2 break-words">{{ contact.email ?? '—' }}</dd>
            <dt class="text-muted-foreground">Phone</dt>
            <dd class="col-span-2">{{ contact.phone ?? '—' }}</dd>
            <dt class="text-muted-foreground">Organization</dt>
            <dd class="col-span-2">{{ contact.organization?.name ?? '—' }}</dd>
        </dl>

        <div>
            <p class="mb-2 text-xs font-medium text-muted-foreground">
                Notes, loaded with a deferred prop the layer requests for itself
            </p>
            <Deferred data="notes">
                <template #fallback>
                    <div class="space-y-2">
                        <Skeleton class="h-4 w-full" />
                        <Skeleton class="h-4 w-2/3" />
                    </div>
                </template>
                <ul class="space-y-2">
                    <li
                        v-for="note in notes"
                        :key="note.id"
                        class="rounded bg-muted px-2 py-1 text-xs"
                    >
                        {{ note.body }}
                    </li>
                    <li
                        v-if="!notes?.length"
                        class="text-xs text-muted-foreground"
                    >
                        No notes yet.
                    </li>
                </ul>
            </Deferred>
            <Button variant="outline" size="sm" class="mt-3" @click="addNote">
                Add a note
            </Button>
        </div>

        <div class="space-y-3 border-t pt-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-sm">Favorite</span>
                    <Badge
                        as="span"
                        data-test="favorite-state"
                        :variant="contact.is_favorite ? 'default' : 'outline'"
                        >{{ contact.is_favorite ? 'yes' : 'no' }}</Badge
                    >
                </div>
                <div class="flex items-center gap-2">
                    <input
                        id="refuse"
                        v-model="form.refuse"
                        type="checkbox"
                        class="size-4 rounded border"
                    />
                    <label for="refuse" class="text-xs">Make it fail</label>
                    <Button variant="outline" size="sm" @click="toggleFavorite">
                        Toggle
                    </Button>
                </div>
            </div>
            <InputError :message="form.errors.refuse" />
            <p class="text-xs text-muted-foreground">
                <code>useForm().optimistic()</code> in here patches
                <em>this layer's</em> props before the request goes out, and the
                server's answer lands here too. Tick the box and the refusal
                rolls it back, with the error on this layer rather than the page
                underneath.
            </p>
        </div>

        <p class="text-xs text-muted-foreground">
            <code>usePage()</code> in here resolves this layer, not the page
            underneath:
            <Badge as="span" variant="outline" class="font-mono text-[10px]">{{
                page.url
            }}</Badge>
        </p>

        <div class="flex flex-wrap justify-between gap-2">
            <Button variant="outline" size="sm" as-child>
                <Link :href="layers.dialogs.edit(contact.id)">
                    Edit in a slideover
                </Link>
            </Button>
            <Button variant="ghost" size="sm" @click="layer.close()">
                Close from useLayer()
            </Button>
        </div>
    </div>
</template>
